<?php
/**
 * 가맹점 PG모듈 목록 조회 API
 *
 * @endpoint GET /api/v1/url_payment_pg_list.php
 * @version 1.0.0
 *
 * @description
 * 가맹점에 등록된 수기결제 PG모듈 목록을 조회하는 REST API입니다.
 * URL결제 생성 시 mkc_id를 지정하기 위해 사용합니다.
 *
 * @query
 * - mb_id: 가맹점 ID (필수)
 *
 * @response
 * {
 *   "success": true/false,
 *   "message": "결과 메시지",
 *   "data": {
 *     "mb_id": "가맹점 ID",
 *     "mb_nick": "가맹점명",
 *     "pg_modules": [
 *       {
 *         "mkc_id": "PG설정 ID",
 *         "pg_name": "PG명",
 *         "pg_code": "PG코드",
 *         "certi_type": "인증타입 (auth/nonauth)"
 *       }
 *     ]
 *   }
 * }
 */

error_reporting(E_ALL);
ini_set("display_errors", 0);
date_default_timezone_set('Asia/Seoul');

// CORS 헤더
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

// OPTIONS 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ========================================
// Gnuboard 프레임워크 로드 (로그인 체크 없이 DB만 연결)
// ========================================
include_once(dirname(__FILE__) . '/_common.php');

// ========================================
// 헬퍼 함수
// ========================================

function json_response($success, $message, $data = null, $http_code = 200) {
    http_response_code($http_code);

    $response = [
        'success' => $success,
        'message' => $message
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    // 로그 기록
    write_url_api_log('pg_list', $success ? 'RESPONSE' : 'ERROR', ['success' => $success, 'message' => $message]);

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// ========================================
// 요청 처리
// ========================================

// GET 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(false, 'GET 요청만 허용됩니다.', null, 405);
}

// 로그 기록 (요청)
write_url_api_log('pg_list', 'REQUEST', $_GET);

// ========================================
// 파라미터 파싱
// ========================================
$mb_id = isset($_GET['mb_id']) ? trim($_GET['mb_id']) : '';

// 필수값 체크
if (empty($mb_id)) {
    json_response(false, 'mb_id (가맹점 ID)는 필수입니다.', null, 400);
}

// ========================================
// 가맹점 정보 조회
// ========================================
$merchant = sql_fetch("SELECT mb_id, mb_nick, mb_level, mb_mailling
    FROM g5_member
    WHERE mb_id = '" . sql_real_escape_string($mb_id) . "'");

if (!$merchant['mb_id']) {
    json_response(false, '존재하지 않는 가맹점입니다.', ['mb_id' => $mb_id], 404);
}

// 가맹점 레벨 체크
if ($merchant['mb_level'] != 3) {
    json_response(false, '가맹점(레벨3)만 조회할 수 있습니다.', ['mb_level' => $merchant['mb_level']], 400);
}

// 수기결제 허용 체크
if ($merchant['mb_mailling'] != '1') {
    json_response(false, '수기결제가 허용되지 않은 가맹점입니다.', null, 400);
}

// ========================================
// PG모듈 목록 조회
// ========================================
$sql = "SELECT k.mkc_id,
               COALESCE(k.mkc_pg_name, m.mpc_pg_name) as pg_name,
               COALESCE(k.mkc_pg_code, m.mpc_pg_code) as pg_code,
               COALESCE(k.mkc_type, m.mpc_type) as certi_type
        FROM g5_member_keyin_config k
        LEFT JOIN g5_manual_payment_config m ON k.mpc_id = m.mpc_id
        WHERE k.mb_id = '" . sql_real_escape_string($mb_id) . "'
          AND k.mkc_use = 'Y'
          AND k.mkc_status = 'active'
        ORDER BY k.mkc_id ASC";

$result = sql_query($sql);
$pg_modules = [];

while ($row = sql_fetch_array($result)) {
    $pg_modules[] = [
        'mkc_id' => intval($row['mkc_id']),
        'pg_name' => $row['pg_name'],
        'pg_code' => $row['pg_code'],
        'certi_type' => $row['certi_type'],
        'certi_type_name' => $row['certi_type'] === 'auth' ? '구인증' : '비인증'
    ];
}

// ========================================
// 응답
// ========================================
json_response(true, '조회 완료', [
    'mb_id' => $merchant['mb_id'],
    'mb_nick' => $merchant['mb_nick'],
    'pg_count' => count($pg_modules),
    'pg_modules' => $pg_modules
]);
