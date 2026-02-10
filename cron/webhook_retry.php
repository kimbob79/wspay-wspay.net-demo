<?php
/**
 * 웹훅 재시도 크론 스크립트
 *
 * 실행 주기: 1분마다 (권장)
 * 크론탭 예시: * * * * * php /path/to/cron/webhook_retry.php
 *
 * 동작:
 * 1. pending 상태 + retry_count < max_retry_count 인 건 조회
 * 2. 각 건에 대해 재시도 발송
 * 3. 성공/실패 상태 업데이트
 */

// CLI 실행 체크
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line.');
}

// 프레임워크 로드
$_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__) . '/..';
require_once $_SERVER['DOCUMENT_ROOT'] . '/_common.php';
require_once G5_PATH . '/lib/webhook.lib.php';

// 로그 설정
$log_dir = G5_DATA_PATH . '/logs/webhook';
if (!is_dir($log_dir)) {
    @mkdir($log_dir, 0755, true);
}
$log_file = $log_dir . '/' . date('Y-m-d') . '_cron.log';

function cron_log($message) {
    global $log_file;
    $log_entry = "[" . date('Y-m-d H:i:s') . "] {$message}\n";
    @file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    echo $log_entry;
}

// 시작 로그
cron_log("=== Webhook Retry Cron Started ===");

// 재시도 대상 조회
$pending_list = webhook_get_pending_list(50); // 한 번에 최대 50건
$total = count($pending_list);

cron_log("Pending webhooks: {$total}");

if ($total == 0) {
    cron_log("No pending webhooks. Exiting.");
    exit(0);
}

$success_count = 0;
$fail_count = 0;

foreach ($pending_list as $history) {
    $whh_id = $history['whh_id'];
    $mb_id = $history['mb_id'];
    $event_type = $history['whh_event_type'];
    $retry_count = $history['whh_retry_count'];

    cron_log("Processing whh_id={$whh_id}, mb_id={$mb_id}, event={$event_type}, retry={$retry_count}");

    $result = webhook_process_retry($history);

    if ($result) {
        $success_count++;
        cron_log("  -> SUCCESS");
    } else {
        $fail_count++;
        cron_log("  -> FAILED (will retry later if retries remain)");
    }

    // 과부하 방지 - 건당 100ms 대기
    usleep(100000);
}

cron_log("=== Webhook Retry Cron Completed ===");
cron_log("Total: {$total}, Success: {$success_count}, Failed: {$fail_count}");
