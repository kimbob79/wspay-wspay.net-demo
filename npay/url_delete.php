<?php
	include_once('./_common.php');

//	if(!$is_admin) { alert("관리자만 접속 가능합니다."); }

//	print "<pre>"; print_r($_POST); print "</pre>";  exit;

	$pm = sql_fetch(" select * from pay_payment_sms where pm_id = '{$pm_id}' ");
	
	if(!$is_admin) {
		if($pm['mb_id'] != $member['mb_id']) {
			alert("잘못된 접근입니다.아이디 불일치");
			exit;
		}
	}

	$sql = " delete from pay_payment_sms where pm_id = '{$pm_id}' ";
	sql_query($sql);

	alert("삭제완료", "./?p=url_list");