<?php
include_once('./_common.php');

// 관리자만 접근
if (!$is_admin) {
    alert_close("관리자만 접근 가능합니다.");
}

$wh_id = isset($_GET['wh_id']) ? intval($_GET['wh_id']) : 0;

if (!$wh_id) {
    alert("잘못된 접근입니다.", "?p=webhook_config");
}

// 삭제
sql_query("DELETE FROM g5_member_webhook WHERE wh_id = '{$wh_id}'");

// 이력도 삭제 (선택사항 - 필요시 주석 해제)
// sql_query("DELETE FROM g5_webhook_history WHERE wh_id = '{$wh_id}'");

alert("결제통보 설정이 삭제되었습니다.", "?p=webhook_config");
