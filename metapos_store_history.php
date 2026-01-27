<?php
/**
 * MetaPOS 매장 히스토리 조회 API
 */

include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

// 관리자 권한 체크
if(!$is_admin) {
	echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
	exit;
}

$st_uid = isset($_GET['st_uid']) ? sql_escape_string($_GET['st_uid']) : '';

if(!$st_uid) {
	echo json_encode(['success' => false, 'message' => '매장 ID가 필요합니다.']);
	exit;
}

// 히스토리 조회 (최근 50건)
$sql = "SELECT * FROM metapos_store_history WHERE st_uid = '{$st_uid}' ORDER BY created_at DESC LIMIT 50";
$result = sql_query($sql);

$data = [];
while($row = sql_fetch_array($result)) {
	$data[] = [
		'msh_id' => $row['msh_id'],
		'change_type' => $row['change_type'],
		'changed_fields' => $row['changed_fields'],
		'old_data' => $row['old_data'],
		'new_data' => $row['new_data'],
		'created_at' => $row['created_at']
	];
}

echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
