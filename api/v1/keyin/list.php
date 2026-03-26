<?php
/**
 * Keyin 공개 API - 결제 내역 조회 엔드포인트
 *
 * GET /api/v1/keyin/list.php?fr_date=20260301&to_date=20260326&status=approved&page=1&limit=20
 *
 * 요청 헤더:
 *   X-API-Key: {발급받은 API 키}
 *   X-TID: {터미널 ID}
 */

include_once('./_common.php');
include_once('./_auth.php');

// GET만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    keyin_error_response('METHOD_NOT_ALLOWED', 'GET 요청만 허용됩니다.', 405);
}

// ============================================
// 1. 인증
// ============================================
$auth = keyin_authenticate();
$kak = $auth['kak'];
$keyin = $auth['keyin'];

// Rate Limit 체크 (조회도 동일 적용)
if (!keyin_check_rate_limit($kak['kak_id'])) {
    keyin_error_response('RATE_LIMIT_EXCEEDED', '요청 빈도 제한을 초과했습니다. (분당 10건)', 429);
}

// ============================================
// 2. 파라미터 파싱
// ============================================
$mkc_id = $keyin['mkc_id'];
$fr_date = isset($_GET['fr_date']) ? preg_replace('/[^0-9]/', '', $_GET['fr_date']) : date('Ymd');
$to_date = isset($_GET['to_date']) ? preg_replace('/[^0-9]/', '', $_GET['to_date']) : date('Ymd');
$status = isset($_GET['status']) ? sql_escape_string(trim($_GET['status'])) : '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = min(100, max(1, intval($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;

// 날짜 형식 변환 (YYYYMMDD → YYYY-MM-DD)
$fr_dates = substr($fr_date, 0, 4) . '-' . substr($fr_date, 4, 2) . '-' . substr($fr_date, 6, 2);
$to_dates = substr($to_date, 0, 4) . '-' . substr($to_date, 4, 2) . '-' . substr($to_date, 6, 2);

// ============================================
// 3. 쿼리 조건 구성
// ============================================
$where = "mkc_id = '{$mkc_id}'";
$where .= " AND pk_created_at BETWEEN '{$fr_dates} 00:00:00' AND '{$to_dates} 23:59:59'";

if ($status && in_array($status, ['pending', 'approved', 'failed', 'cancelled'])) {
    $where .= " AND pk_status = '{$status}'";
}

// ============================================
// 4. 총 건수 조회
// ============================================
$count_row = sql_fetch("SELECT COUNT(*) as total FROM g5_payment_keyin WHERE {$where}");
$total = intval($count_row['total']);
$total_pages = $total > 0 ? ceil($total / $limit) : 0;

// ============================================
// 5. 데이터 조회
// ============================================
$items = [];
$result = sql_query("SELECT pk_id, pk_order_no, pk_amount, pk_status, pk_app_no, pk_app_date,
    pk_goods_name, pk_buyer_name, pk_buyer_phone, pk_card_no_masked, pk_installment,
    pk_pg_code, pk_pg_name, pk_card_issuer, pk_res_code, pk_res_msg,
    pk_cancel_amount, pk_cancel_date, pk_created_at
    FROM g5_payment_keyin
    WHERE {$where}
    ORDER BY pk_created_at DESC
    LIMIT {$offset}, {$limit}");

while ($row = sql_fetch_array($result)) {
    $items[] = [
        'order_no' => $row['pk_order_no'],
        'amount' => intval($row['pk_amount']),
        'status' => $row['pk_status'],
        'approval_number' => $row['pk_app_no'],
        'approved_at' => $row['pk_app_date'],
        'goods_name' => $row['pk_goods_name'],
        'buyer_name' => $row['pk_buyer_name'],
        'buyer_phone' => $row['pk_buyer_phone'],
        'card_no_masked' => $row['pk_card_no_masked'],
        'installment' => $row['pk_installment'],
        'pg' => $row['pk_pg_code'],
        'pg_name' => $row['pk_pg_name'],
        'card_issuer' => $row['pk_card_issuer'],
        'cancel_amount' => intval($row['pk_cancel_amount']),
        'cancel_date' => $row['pk_cancel_date'],
        'created_at' => $row['pk_created_at']
    ];
}

write_keyin_api_log('list', 'SUCCESS', [
    'kak_id' => $kak['kak_id'], 'mkc_id' => $mkc_id,
    'fr_date' => $fr_date, 'to_date' => $to_date, 'total' => $total
]);

keyin_json_response([
    'success' => true,
    'data' => [
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'total_pages' => $total_pages,
        'items' => $items
    ]
]);
