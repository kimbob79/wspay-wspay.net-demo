<?php
/**
 * API v1 공통 파일
 * 로그인 체크 없이 DB 연결만 수행
 */
error_reporting(E_ALL);
ini_set("display_errors", 0);
date_default_timezone_set('Asia/Seoul');

include_once(dirname(__FILE__) . '/../../gnu_module/common.php');

/**
 * URL 결제 API 로그 함수
 * @param string $api_name API 이름 (예: 'create', 'cancel', 'list' 등)
 * @param string $type 로그 타입 ('REQUEST', 'RESPONSE', 'ERROR')
 * @param mixed $data 로그 데이터
 */
function write_url_api_log($api_name, $type, $data) {
    $log_dir = dirname(__FILE__) . '/../../logs/url';

    // 디렉토리 없으면 생성
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $log_file = $log_dir . '/' . date('Y-m-d') . '.log';

    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'api' => $api_name,
        'type' => $type,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'data' => $data
    ];

    $log_line = '[' . date('Y-m-d H:i:s') . '] [' . $api_name . '] [' . $type . '] ' . json_encode($log_entry, JSON_UNESCAPED_UNICODE) . "\n";

    file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
}
