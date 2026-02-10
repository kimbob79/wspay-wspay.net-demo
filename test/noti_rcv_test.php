<?php
/**
 * 결제통보 수신 테스트
 *
 * 테스트 URL: https://your-domain.com/test/noti_rcv_test.php
 * 로그 저장: /logs/test/YYYY-MM-DD_noti.log
 */

// 로그 디렉토리 설정
$log_dir = dirname(__FILE__) . '/../logs/test';
if (!is_dir($log_dir)) {
    @mkdir($log_dir, 0755, true);
}

$log_file = $log_dir . '/' . date('Y-m-d') . '_noti.log';

// 요청 정보 수집
$request_time = date('Y-m-d H:i:s');
$request_method = $_SERVER['REQUEST_METHOD'];
$content_type = $_SERVER['CONTENT_TYPE'] ?? '';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$webhook_event = $_SERVER['HTTP_X_WEBHOOK_EVENT'] ?? '';
$remote_ip = $_SERVER['REMOTE_ADDR'] ?? '';

// POST body 읽기
$raw_body = file_get_contents('php://input');
$json_data = json_decode($raw_body, true);

// 로그 작성
$log_entry = "================================================================================\n";
$log_entry .= "[{$request_time}] 결제통보 수신\n";
$log_entry .= "--------------------------------------------------------------------------------\n";
$log_entry .= "Request Method  : {$request_method}\n";
$log_entry .= "Content-Type    : {$content_type}\n";
$log_entry .= "User-Agent      : {$user_agent}\n";
$log_entry .= "X-Webhook-Event : {$webhook_event}\n";
$log_entry .= "Remote IP       : {$remote_ip}\n";
$log_entry .= "--------------------------------------------------------------------------------\n";
$log_entry .= "Raw Body:\n";
$log_entry .= $raw_body . "\n";
$log_entry .= "--------------------------------------------------------------------------------\n";

if ($json_data) {
    $log_entry .= "Parsed JSON:\n";
    $log_entry .= json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    $log_entry .= "--------------------------------------------------------------------------------\n";

    // 주요 필드 추출
    $event = $json_data['event'] ?? '-';
    $mb_id = $json_data['merchant']['mb_id'] ?? '-';
    $mb_name = $json_data['merchant']['mb_name'] ?? '-';
    $trx_id = $json_data['transaction']['trx_id'] ?? '-';
    $order_no = $json_data['transaction']['order_number'] ?? '-';
    $amount = $json_data['transaction']['amount'] ?? 0;
    $approval_no = $json_data['transaction']['approval_number'] ?? '-';
    $card_name = $json_data['card']['card_name'] ?? '-';

    $log_entry .= "Summary:\n";
    $log_entry .= "  Event        : {$event}\n";
    $log_entry .= "  Merchant     : {$mb_name} ({$mb_id})\n";
    $log_entry .= "  Transaction  : {$trx_id}\n";
    $log_entry .= "  Order No     : {$order_no}\n";
    $log_entry .= "  Amount       : " . number_format($amount) . "원\n";
    $log_entry .= "  Approval No  : {$approval_no}\n";
    $log_entry .= "  Card         : {$card_name}\n";
} else {
    $log_entry .= "JSON Parse Error: " . json_last_error_msg() . "\n";
}

$log_entry .= "================================================================================\n\n";

// 파일에 로그 저장
file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);

// 성공 응답 반환 (HTTP 200)
header('Content-Type: application/json; charset=utf-8');
http_response_code(200);

echo json_encode([
    'status' => 'ok',
    'message' => '결제통보 수신 완료',
    'received_at' => $request_time,
    'event' => $webhook_event,
    'log_file' => basename($log_file)
], JSON_UNESCAPED_UNICODE);
