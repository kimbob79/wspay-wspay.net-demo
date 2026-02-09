<?php
/**
 * 결제통보 재전송
 */
include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

// 관리자만 접근
if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

$whh_id = isset($_POST['whh_id']) ? intval($_POST['whh_id']) : 0;

if (!$whh_id) {
    echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
    exit;
}

// 기존 이력 조회
$row = sql_fetch("SELECT * FROM g5_webhook_history WHERE whh_id = '{$whh_id}'");

if (!$row['whh_id']) {
    echo json_encode(['success' => false, 'message' => '존재하지 않는 이력입니다.']);
    exit;
}

$webhook_url = $row['whh_url'];
$payload = $row['whh_payload'];
$event_type = $row['whh_event_type'];

if (!$webhook_url || !$payload) {
    echo json_encode(['success' => false, 'message' => 'URL 또는 페이로드 정보가 없습니다.']);
    exit;
}

// HTTP 요청 전송
$start_time = microtime(true);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $webhook_url,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json; charset=utf-8',
        'X-Webhook-Event: ' . $event_type,
        'User-Agent: WsPay-Webhook/1.0 (Resend)'
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false
]);

$response_body = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

$response_time = round((microtime(true) - $start_time) * 1000);
$is_success = (!$curl_error && $http_code >= 200 && $http_code < 300);

// 새 이력 저장
$whh_status = $is_success ? 'success' : 'failed';
$error_message = $curl_error ?: ($is_success ? '' : 'HTTP ' . $http_code);

$sql = "INSERT INTO g5_webhook_history SET
    mb_id = '".sql_escape_string($row['mb_id'])."',
    pay_id = '".intval($row['pay_id'])."',
    whh_event_id = '".sql_escape_string($row['whh_event_id'])."',
    whh_event_type = '".sql_escape_string($event_type)."',
    whh_url = '".sql_escape_string($webhook_url)."',
    whh_payload = '".sql_escape_string($payload)."',
    whh_status = '{$whh_status}',
    whh_http_status = '{$http_code}',
    whh_response_body = '".sql_escape_string(substr($response_body, 0, 2000))."',
    whh_response_time = '{$response_time}',
    whh_error_message = '".sql_escape_string($error_message)."',
    whh_retry_count = 0,
    whh_max_retry_count = 0,
    whh_sent_datetime = NOW(),
    whh_completed_datetime = NOW()";
sql_query($sql);

// 결과 반환
if ($curl_error) {
    echo json_encode([
        'success' => false,
        'message' => '재전송 실패: ' . $curl_error,
        'http_code' => 0,
        'response_time' => $response_time
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success' => $is_success,
        'message' => $is_success ? '재전송 성공' : '재전송 실패 (HTTP ' . $http_code . ')',
        'http_code' => $http_code,
        'response_time' => $response_time,
        'response_body' => $response_body
    ], JSON_UNESCAPED_UNICODE);
}
