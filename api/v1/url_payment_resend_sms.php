<?php
/**
 * URL 결제 SMS 재발송 API
 *
 * @endpoint POST /api/v1/url_payment_resend_sms.php
 * @version 1.0.0
 *
 * @description
 * 활성 상태의 URL 결제에 대해 SMS를 재발송하는 REST API입니다.
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
    write_url_api_log('resend_sms', $success ? 'RESPONSE' : 'ERROR', $response);

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * SMS 발송 함수 (알리고 API)
 */
function send_url_payment_sms($phone, $message) {
    $sms_url = "https://apis.aligo.in/send/";
    $sms = array(
        'user_id' => 'wspay',
        'key' => 'v5smv1ajl0s4xrx1e9db2luomlycrkqz',
        'sender' => '01073651990',
        'receiver' => preg_replace('/[^0-9]/', '', $phone),
        'msg' => $message,
        'msg_type' => (mb_strlen($message, 'UTF-8') > 80) ? 'LMS' : 'SMS',
        'testmode_yn' => 'N'
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sms_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $sms);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $ret = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return array(
            'success' => false,
            'message' => 'cURL Error: ' . $error,
            'msg_id' => '',
            'response' => ''
        );
    }

    curl_close($ch);

    $result = json_decode($ret, true);

    return array(
        'success' => (isset($result['result_code']) && intval($result['result_code']) > 0),
        'message' => $result['message'] ?? 'API 오류',
        'msg_id' => $result['msg_id'] ?? '',
        'response' => $ret
    );
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
write_url_api_log('resend_sms', 'REQUEST', $input);

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
if ($url_pay['up_status'] !== 'active') {
    $status_messages = [
        'used' => '이미 결제 완료된 URL입니다.',
        'cancelled' => '취소된 URL입니다.',
        'expired' => '만료된 URL입니다.'
    ];
    json_response(false, $status_messages[$url_pay['up_status']] ?? '활성 상태가 아닌 URL입니다.', [
        'up_status' => $url_pay['up_status']
    ], 400);
}

// 구매자 연락처 체크
if (empty($url_pay['up_buyer_phone'])) {
    json_response(false, '구매자 연락처가 없어 SMS를 발송할 수 없습니다.', null, 400);
}

// ========================================
// SMS 발송
// ========================================
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'gnushop.xyz';
$payment_url = "{$protocol}://{$host}/pay/{$url_pay['up_code']}";
$expire_text = date('Y-m-d H:i', strtotime($url_pay['up_expire_datetime']));

// 80자 이내 SMS
$message = "{$url_pay['up_seller_name']} " . number_format($url_pay['up_amount']) . "원\n";
$message .= "{$payment_url}";

$sms_result = send_url_payment_sms($url_pay['up_buyer_phone'], $message);

if ($sms_result['success']) {
    // SMS 발송 횟수 업데이트
    $sms_count = intval($url_pay['up_sms_count']) + 1;
    sql_query("UPDATE g5_url_payment
               SET up_sms_sent = 'Y',
                   up_sms_sent_datetime = NOW(),
                   up_sms_count = '{$sms_count}'
               WHERE up_id = '{$url_pay['up_id']}'");

    json_response(true, 'SMS가 발송되었습니다.', [
        'up_id' => $url_pay['up_id'],
        'up_code' => $url_pay['up_code'],
        'sms_count' => $sms_count,
        'buyer_phone' => $url_pay['up_buyer_phone']
    ]);
} else {
    json_response(false, 'SMS 발송에 실패했습니다: ' . $sms_result['message'], null, 500);
}
