<?php
	// 에러표시
	error_reporting( E_ALL );
	ini_set( "display_errors", 1 );

	// POST 업데이트 내용
	//print "<pre>"; print_r($_POST); print "</pre>";  exit;

	include_once('./_common.php');

	if($is_guest) {

		include_once("./login.php");

	} else {

		if(!$fr_date) { $fr_date = G5_TIME_YMD; }
		if(!$to_date) { $to_date = G5_TIME_YMD; }


		// MENU
		if($p == "payment") {
			$page_title = "수기결제";
		} else if($p == "urlpayment") {
			$page_title = "URL결제";
		} else if($p == "list") {
			$page_title = "결제내역";
		} else if($p == "member_list") {
			$page_title = "회원관리";
		} else if($p == "member_form") {
			if($w == "u") {
				$page_title = "회원수정";
			} else {
				$page_title = "회원등록";
			}
		} else {
			$page_title = "HOME";
		}

		if(!$p || $p == "main") {
			$p = "main";
		}

		include_once("./_head.php");
		include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

		if(is_file("./".$p.".php")){
			include_once("./".$p.".php");
		} else {
			echo "<div style='line-height:1000%; text-align:center;'>잘못된 접근입니다</div>";
		}

		include_once("./_tail.php");
	}