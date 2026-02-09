<?php
/**
 * 결제통보 테스트 발송
 */
include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

// 관리자만 접근
if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

$test_url = isset($_POST['test_url']) ? trim($_POST['test_url']) : '';
$event_type = isset($_POST['event_type']) ? trim($_POST['event_type']) : 'approval';

// URL 검증
if (!$test_url) {
    echo json_encode(['success' => false, 'message' => 'URL을 입력해주세요.']);
    exit;
}

if (!filter_var($test_url, FILTER_VALIDATE_URL)) {
    echo json_encode(['success' => false, 'message' => '올바른 URL 형식이 아닙니다.']);
    exit;
}

// 테스트 페이로드 생성
$test_payload = [
    'event' => $event_type == 'approval' ? 'payment.approved' : 'payment.cancelled',
    'version' => '1.0',
    'timestamp' => date('Y-m-d H:i:s'),
    'merchant' => [
        'mb_id' => 'test_merchant',
        'mb_name' => '테스트가맹점'
    ],
    'transaction' => [
        'trx_id' => 'TEST' . date('YmdHis') . rand(1000, 9999),
        'order_number' => 'TEST-ORDER-' . date('YmdHis'),
        'amount' => 10000,
        'approval_number' => '99999999',
        'approval_datetime' => date('Y-m-d H:i:s'),
        'cancel_datetime' => $event_type == 'cancel' ? date('Y-m-d H:i:s') : '',
        'installment' => '00',
        'device_type' => 'terminal'
    ],
    'card' => [
        'card_name' => '테스트카드',
        'card_number' => '1234****5678'
    ]
];

$json_payload = json_encode($test_payload, JSON_UNESCAPED_UNICODE);

// HTTP 요청 전송
$start_time = microtime(true);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $test_url,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $json_payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json; charset=utf-8',
        'X-Webhook-Event: ' . $event_type,
        'User-Agent: WsPay-Webhook/1.0 (Test)'
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false
]);

$response_body = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

$response_time = round((microtime(true) - $start_time) * 1000);

// 결과 반환
if ($curl_error) {
    echo json_encode([
        'success' => false,
        'message' => '전송 실패: ' . $curl_error,
        'http_code' => 0,
        'response_time' => $response_time,
        'response_body' => '',
        'payload' => $test_payload
    ], JSON_UNESCAPED_UNICODE);
} else {
    $is_success = ($http_code >= 200 && $http_code < 300);
    echo json_encode([
        'success' => $is_success,
        'message' => $is_success ? '전송 성공' : '전송 실패 (HTTP ' . $http_code . ')',
        'http_code' => $http_code,
        'response_time' => $response_time,
        'response_body' => $response_body,
        'payload' => $test_payload
    ], JSON_UNESCAPED_UNICODE);
}
