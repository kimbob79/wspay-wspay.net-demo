<?php
	if(!$is_admin) { alert("관리자만 접속 가능합니다."); }

	echo "삭제"
	exit;
	
	$mb = get_member($mb_id);

	if($level == "1") {
		$sql = " update g5_member set mb_level = '{$mb['mb_sex']}', mb_sex = '{$level}' where mb_id = '{$mb_id}' ";
	} else {
		$sql = " update g5_member set mb_level = '1', mb_sex = '{$level}' where mb_id = '{$mb_id}' ";
	}
	sql_query($sql);

	if($level == "1") { $title = "복구"; } else { $title = "삭제"; }

	alert($title."완료", "./?p=member&level=".$level."&sfl=".$sfl."&stx=".$stx."&page=".$page);