<?php
	include_once('./_common.php');

	if(!$is_admin) { alert("관리자만 접속 가능합니다."); }

//	print "<pre>"; print_r($_POST); print "</pre>";  exit;


	$sql = " delete from g5_noti where nt_id = '{$nt_id}' ";
	sql_query($sql);

	goto_url("./?p=noti_list&page=".$page."&results=ok");