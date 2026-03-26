<?php
/**
 * Keyin 설정 AJAX 처리 (API 키 발급/재발급)
 * index.php를 경유하지 않고 직접 호출되어 순수 JSON만 반환
 */
include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

// POST만 허용
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST 요청만 허용됩니다.']);
    exit;
}

// 관리자 권한 체크
if(!$is_admin) {
    echo json_encode(['success' => false, 'message' => '관리자만 접근할 수 있습니다.']);
    exit;
}

$mb_id = isset($_POST['mb_id']) ? $_POST['mb_id'] : '';
$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
$target_mkc_id = isset($_POST['mkc_id']) ? (int)$_POST['mkc_id'] : 0;

if(!$mb_id || !$target_mkc_id) {
    echo json_encode(['success' => false, 'message' => '필수 파라미터가 누락되었습니다.']);
    exit;
}

$table_name = "g5_member_keyin_config";

// mkc_id 소유권 확인
$check_mkc = sql_fetch("SELECT mkc_id FROM {$table_name} WHERE mkc_id = '{$target_mkc_id}' AND mb_id = '" . sql_escape_string($mb_id) . "' AND mkc_status = 'active'");
if(!$check_mkc['mkc_id']) {
    echo json_encode(['success' => false, 'message' => '유효하지 않은 설정입니다.']);
    exit;
}

if($mode == 'api_key_issue' || $mode == 'api_key_regen') {
    if($mode == 'api_key_regen') {
        // 기존 키 revoke
        sql_query("UPDATE g5_keyin_api_keys SET kak_status = 'revoked', kak_revoked_at = NOW() WHERE mkc_id = '{$target_mkc_id}' AND kak_status = 'active'");
    }

    // 새 키 생성
    $new_key = 'ssp-' . bin2hex(random_bytes(30));
    $now = date("Y-m-d H:i:s");
    sql_query("INSERT INTO g5_keyin_api_keys (mb_id, mkc_id, kak_key, kak_name, kak_status, kak_issued_at) VALUES ('" . sql_escape_string($mb_id) . "', '{$target_mkc_id}', '{$new_key}', '자동 발급', 'active', '{$now}')");

    echo json_encode(['success' => true, 'api_key' => $new_key, 'message' => 'API 키가 발급되었습니다.']);
    exit;
}

echo json_encode(['success' => false, 'message' => '올바른 mode를 지정하세요.']);
