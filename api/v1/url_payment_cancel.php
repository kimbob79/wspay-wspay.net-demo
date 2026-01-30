<?php
/**
 * URL 결제 취소 API
 *
 * @endpoint POST /api/v1/url_payment_cancel.php
 * @version 1.0.0
 *
 * @description
 * 미결제 상태의 URL 결제를 취소하는 REST API입니다.
 * (이미 결제 완료된 건은 취소 불가)
 *
 * @request
 * Content-Type: application/json
 *
 * @body
 * {
 *   "api_key": "인증키 (필수)",
 *   "up_code": "URL결제 코드 (up_code 또는 up_id 중 하나 필수)",
 *   "up_id": "URL결제 ID (up_code 또는 up_id 중 하나 필수)"
 * }
 *
 * @response
 * {
 *   "success": true/false,
 *   "message": "결과 메시지"
 * }
 */

error_reporting(E_ALL);
ini_set("display_errors", 0);
date_default_timezone_set('Asia/Seoul');

// CORS 헤더
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
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
    write_url_api_log('cancel', $success ? 'RESPONSE' : 'ERROR', $response);

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// ========================================
// 요청 처리
// ========================================

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'POST 요청만 허용됩니다.', null, 405);
}

// 요청 데이터 파싱
$input_raw = file_get_contents('php://input');
$input = json_decode($input_raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    $input = $_POST;
}

// 로그 기록 (요청)
write_url_api_log('cancel', 'REQUEST', $input);

// ========================================
// 파라미터 파싱
// ========================================
$up_code = isset($input['up_code']) ? trim($input['up_code']) : '';
$up_id = isset($input['up_id']) ? intval($input['up_id']) : 0;

// 필수값 체크
if (empty($up_code) && empty($up_id)) {
    json_response(false, 'up_code 또는 up_id 중 하나는 필수입니다.', null, 400);
}

// ========================================
// URL결제 조회
// ========================================
$where = '';
if (!empty($up_code)) {
    $where = "up_code = '" . sql_real_escape_string($up_code) . "'";
} else {
    $where = "up_id = '{$up_id}'";
}

$url_pay = sql_fetch("SELECT * FROM g5_url_payment WHERE {$where}");

if (!$url_pay['up_id']) {
    json_response(false, 'URL결제를 찾을 수 없습니다.', null, 404);
}

// ========================================
// 상태 체크
// ========================================
if ($url_pay['up_status'] === 'used') {
    json_response(false, '이미 결제 완료된 URL은 취소할 수 없습니다. 결제 취소는 별도로 진행해주세요.', [
        'up_status' => $url_pay['up_status'],
        'up_paid_datetime' => $url_pay['up_paid_datetime']
    ], 400);
}

if ($url_pay['up_status'] === 'cancelled') {
    json_response(false, '이미 취소된 URL결제입니다.', ['up_status' => $url_pay['up_status']], 400);
}

if ($url_pay['up_status'] === 'expired') {
    json_response(false, '이미 만료된 URL결제입니다.', ['up_status' => $url_pay['up_status']], 400);
}

// ========================================
// 취소 처리
// ========================================
$result = sql_query("UPDATE g5_url_payment
                     SET up_status = 'cancelled',
                         up_updated_at = NOW()
                     WHERE up_id = '{$url_pay['up_id']}'");

if (!$result) {
    json_response(false, '취소 처리 중 오류가 발생했습니다.', null, 500);
}

json_response(true, 'URL결제가 취소되었습니다.', [
    'up_id' => $url_pay['up_id'],
    'up_code' => $url_pay['up_code'],
    'previous_status' => $url_pay['up_status'],
    'current_status' => 'cancelled'
]);
