<?php
	include_once('./_common.php');

	if(!$is_admin) { alert("관리자만 접속 가능합니다."); }

//	print "<pre>"; print_r($_POST); print "</pre>";  exit;


	$sql = " delete from g5_sftp_member where sm_id = '{$sm_id}' ";
	sql_query($sql);

	alert("단말기 삭제완료", "./?p=sftp_member&page=".$page."&results=ok");