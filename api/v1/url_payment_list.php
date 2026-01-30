<?php
/**
 * URL 결제 목록 조회 API
 *
 * @endpoint GET /api/v1/url_payment_list.php
 * @version 1.0.0
 *
 * @description
 * 가맹점의 URL 결제 목록을 조회하는 REST API입니다.
 *
 * @query
 * - api_key: 인증키 (필수)
 * - mb_id: 가맹점 ID (필수)
 * - status: 상태 필터 (선택) - active, used, expired, cancelled, all
 * - fr_date: 시작일 YYYYMMDD (선택)
 * - to_date: 종료일 YYYYMMDD (선택)
 * - page: 페이지 번호 (선택, 기본값: 1)
 * - limit: 페이지당 개수 (선택, 기본값: 20, 최대: 100)
 *
 * @response
 * {
 *   "success": true/false,
 *   "message": "결과 메시지",
 *   "data": {
 *     "total": "전체 개수",
 *     "page": "현재 페이지",
 *     "limit": "페이지당 개수",
 *     "total_pages": "전체 페이지 수",
 *     "items": [...]
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
    write_url_api_log('list', $success ? 'RESPONSE' : 'ERROR', ['success' => $success, 'message' => $message]);

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
write_url_api_log('list', 'REQUEST', $_GET);

// ========================================
// 파라미터 파싱
// ========================================
$mb_id = isset($_GET['mb_id']) ? trim($_GET['mb_id']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : 'all';
$fr_date = isset($_GET['fr_date']) ? preg_replace('/[^0-9]/', '', $_GET['fr_date']) : '';
$to_date = isset($_GET['to_date']) ? preg_replace('/[^0-9]/', '', $_GET['to_date']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 20;

// 필수값 체크
if (empty($mb_id)) {
    json_response(false, 'mb_id (가맹점 ID)는 필수입니다.', null, 400);
}

// 가맹점 존재 여부 확인
$merchant = sql_fetch("SELECT mb_id, mb_nick FROM g5_member WHERE mb_id = '" . sql_real_escape_string($mb_id) . "'");
if (!$merchant['mb_id']) {
    json_response(false, '존재하지 않는 가맹점입니다.', ['mb_id' => $mb_id], 404);
}

// ========================================
// 쿼리 조건 구성
// ========================================
$where = ["mb_id = '" . sql_real_escape_string($mb_id) . "'"];

// 상태 필터
$valid_statuses = ['active', 'used', 'expired', 'cancelled'];
if ($status !== 'all' && in_array($status, $valid_statuses)) {
    $where[] = "up_status = '" . sql_real_escape_string($status) . "'";
}

// 날짜 필터
if (!empty($fr_date) && strlen($fr_date) == 8) {
    $fr_dates = substr($fr_date, 0, 4) . '-' . substr($fr_date, 4, 2) . '-' . substr($fr_date, 6, 2);
    $where[] = "up_created_at >= '{$fr_dates} 00:00:00'";
}

if (!empty($to_date) && strlen($to_date) == 8) {
    $to_dates = substr($to_date, 0, 4) . '-' . substr($to_date, 4, 2) . '-' . substr($to_date, 6, 2);
    $where[] = "up_created_at <= '{$to_dates} 23:59:59'";
}

$where_sql = implode(' AND ', $where);

// ========================================
// 전체 개수 조회
// ========================================
$count_result = sql_fetch("SELECT COUNT(*) as cnt FROM g5_url_payment WHERE {$where_sql}");
$total = intval($count_result['cnt']);
$total_pages = ceil($total / $limit);
$offset = ($page - 1) * $limit;

// ========================================
// 목록 조회
// ========================================
$sql = "SELECT u.*, k.mkc_pg_name,
               COALESCE(k.mkc_pg_name, m.mpc_pg_name) as pg_name
        FROM g5_url_payment u
        LEFT JOIN g5_member_keyin_config k ON u.mkc_id = k.mkc_id
        LEFT JOIN g5_manual_payment_config m ON k.mpc_id = m.mpc_id
        WHERE {$where_sql}
        ORDER BY u.up_id DESC
        LIMIT {$offset}, {$limit}";

$result = sql_query($sql);
$items = [];

while ($row = sql_fetch_array($result)) {
    $items[] = [
        'up_id' => $row['up_id'],
        'up_code' => $row['up_code'],
        'up_status' => $row['up_status'],
        'up_amount' => intval($row['up_amount']),
        'up_goods_name' => $row['up_goods_name'],
        'up_buyer_name' => $row['up_buyer_name'],
        'up_buyer_phone' => $row['up_buyer_phone'],
        'up_seller_name' => $row['up_seller_name'],
        'up_expire_datetime' => $row['up_expire_datetime'],
        'up_paid_datetime' => $row['up_paid_datetime'],
        'up_sms_sent' => $row['up_sms_sent'],
        'up_created_at' => $row['up_created_at'],
        'pg_name' => $row['pg_name'],
        'payment_url' => "https://" . ($_SERVER['HTTP_HOST'] ?? 'gnushop.xyz') . "/pay/" . $row['up_code']
    ];
}

// ========================================
// 응답
// ========================================
json_response(true, '조회 완료', [
    'total' => $total,
    'page' => $page,
    'limit' => $limit,
    'total_pages' => $total_pages,
    'items' => $items
]);
