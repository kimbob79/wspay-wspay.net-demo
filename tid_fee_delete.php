<?php
	include_once('./_common.php');

	if(!$is_admin) { alert("관리자만 접속 가능합니다."); }

//	print "<pre>"; print_r($_POST); print "</pre>";  exit;


	$sql = " delete from g5_device where dv_id = '{$dv_id}' ";
	sql_query($sql);

	alert("단말기 삭제완료", "./?p=tid_fee&membera=".$membera."&memberb=".$memberb."&memberc=".$memberc."&memberd=".$memberd."&membere=".$membere."&memberf=".$memberf."&mb_nick=".$mb_nick."&dv_tid=".$dv_tid."&page=".$page."&results=ok");