<?php
/**
 * URL 결제 상세 조회 API
 *
 * @endpoint GET /api/v1/url_payment_detail.php
 * @version 1.0.0
 *
 * @description
 * 특정 URL 결제의 상세 정보를 조회하는 REST API입니다.
 *
 * @query
 * - api_key: 인증키 (필수)
 * - up_code: URL결제 코드 (up_code 또는 up_id 중 하나 필수)
 * - up_id: URL결제 ID (up_code 또는 up_id 중 하나 필수)
 *
 * @response
 * {
 *   "success": true/false,
 *   "message": "결과 메시지",
 *   "data": { ... }
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
    write_url_api_log('detail', $success ? 'RESPONSE' : 'ERROR', ['success' => $success, 'message' => $message]);

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
write_url_api_log('detail', 'REQUEST', $_GET);

// ========================================
// 파라미터 파싱
// ========================================
$up_code = isset($_GET['up_code']) ? trim($_GET['up_code']) : '';
$up_id = isset($_GET['up_id']) ? intval($_GET['up_id']) : 0;

// 필수값 체크
if (empty($up_code) && empty($up_id)) {
    json_response(false, 'up_code 또는 up_id 중 하나는 필수입니다.', null, 400);
}

// ========================================
// 조회
// ========================================
$where = '';
if (!empty($up_code)) {
    $where = "u.up_code = '" . sql_real_escape_string($up_code) . "'";
} else {
    $where = "u.up_id = '{$up_id}'";
}

$sql = "SELECT u.*,
               k.mkc_pg_name, k.mkc_type,
               COALESCE(k.mkc_pg_name, m.mpc_pg_name) as pg_name,
               COALESCE(k.mkc_type, m.mpc_type) as certi_type,
               pk.pk_app_no, pk.pk_app_date, pk.pk_card_no_masked, pk.pk_card_name
        FROM g5_url_payment u
        LEFT JOIN g5_member_keyin_config k ON u.mkc_id = k.mkc_id
        LEFT JOIN g5_manual_payment_config m ON k.mpc_id = m.mpc_id
        LEFT JOIN g5_payment_keyin pk ON u.pk_id = pk.pk_id
        WHERE {$where}";

$row = sql_fetch($sql);

if (!$row['up_id']) {
    json_response(false, 'URL결제를 찾을 수 없습니다.', null, 404);
}

// ========================================
// 응답 데이터 구성
// ========================================
$data = [
    'up_id' => $row['up_id'],
    'up_code' => $row['up_code'],
    'up_status' => $row['up_status'],
    'mb_id' => $row['mb_id'],
    'mkc_id' => $row['mkc_id'],
    'pg_name' => $row['pg_name'],
    'certi_type' => $row['certi_type'],

    'up_amount' => intval($row['up_amount']),
    'up_goods_name' => $row['up_goods_name'],
    'up_goods_desc' => $row['up_goods_desc'],

    'up_buyer_name' => $row['up_buyer_name'],
    'up_buyer_phone' => $row['up_buyer_phone'],
    'up_seller_name' => $row['up_seller_name'],
    'up_seller_phone' => $row['up_seller_phone'],

    'up_expire_datetime' => $row['up_expire_datetime'],
    'up_memo' => $row['up_memo'],

    'up_sms_sent' => $row['up_sms_sent'],
    'up_sms_count' => intval($row['up_sms_count']),
    'up_sms_sent_datetime' => $row['up_sms_sent_datetime'],

    'up_created_at' => $row['up_created_at'],
    'up_updated_at' => $row['up_updated_at'],

    'payment_url' => "https://" . ($_SERVER['HTTP_HOST'] ?? 'gnushop.xyz') . "/pay/" . $row['up_code']
];

// 결제 완료된 경우 결제 정보 추가
if ($row['up_status'] === 'used' && $row['pk_id']) {
    $data['payment_info'] = [
        'pk_id' => $row['pk_id'],
        'pk_app_no' => $row['pk_app_no'],
        'pk_app_date' => $row['pk_app_date'],
        'pk_card_no_masked' => $row['pk_card_no_masked'],
        'pk_card_name' => $row['pk_card_name'],
        'up_paid_datetime' => $row['up_paid_datetime']
    ];
}

// 계층 정보 (필요시)
$data['hierarchy'] = [
    'mb_1' => $row['up_mb_1'],
    'mb_2' => $row['up_mb_2'],
    'mb_3' => $row['up_mb_3'],
    'mb_4' => $row['up_mb_4'],
    'mb_5' => $row['up_mb_5'],
    'mb_6' => $row['up_mb_6'],
    'mb_6_name' => $row['up_mb_6_name']
];

json_response(true, '조회 완료', $data);
