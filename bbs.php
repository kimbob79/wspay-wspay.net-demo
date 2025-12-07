<?php
	if($t == "notice") {
		$title2 = "공지사항";
	} else if($t == "qa") {
		$title2 = "질문답변";
	}

	// 게시판 테이블명 설정
	$bo_table = $t;
	$write_table = $g5['write_prefix'] . $bo_table;

	// 게시판 설정 정보 가져오기
	$board = sql_fetch(" select * from {$g5['board_table']} where bo_table = '{$bo_table}' ");
	if(!$board['bo_table']) {
		alert('존재하지 않는 게시판입니다.');
	}

	// 분류 사용 안함으로 설정
	$board['bo_use_category'] = 0;

	if($v == "view") {
		include_once("./bbs_view.php");
	} else if($v == "write") {
		include_once("./bbs_write.php");
	} else if($v == "update") {
		include_once("./bbs_update.php");
	} else if($v == "delete") {
		include_once("./bbs_delete.php");
	} else {
		include_once("./bbs_list.php");
	}