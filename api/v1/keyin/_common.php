<?php
/**
 * Keyin 공개 API 공통 파일
 * - 로그인 체크 없이 DB 연결만 수행
 * - API 전용 로그 함수 정의
 */
error_reporting(0);
ini_set('display_errors', 0);
date_default_timezone_set('Asia/Seoul');

// CORS 헤더
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key, X-TID');

// OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// DB 연결 (Gnuboard 코어)
include_once(dirname(__FILE__) . '/../../../gnu_module/common.php');

/**
 * Keyin API 로그 기록
 * 경로: /logs/keyin/YYYY-MM-DD.log
 */
function write_keyin_api_log($api_name, $type, $data) {
    $log_dir = dirname(__FILE__) . '/../../../logs/keyin';

    if (!is_dir($log_dir)) {
        @mkdir($log_dir, 0755, true);
    }

    $log_file = $log_dir . '/' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s.') . sprintf('%03d', (microtime(true) - floor(microtime(true))) * 1000);
    $remote_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    $log_line = "[{$timestamp}] [{$remote_ip}] [{$api_name}] [{$type}] " . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";

    @file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
}

/**
 * JSON 응답 반환
 */
function keyin_json_response($data, $http_code = 200) {
    http_response_code($http_code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 에러 응답
 */
function keyin_error_response($error_code, $message, $http_code = 400) {
    write_keyin_api_log('system', 'ERROR', ['error_code' => $error_code, 'message' => $message]);
    keyin_json_response([
        'success' => false,
        'error_code' => $error_code,
        'message' => $message
    ], $http_code);
}
