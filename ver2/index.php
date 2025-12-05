<?php
	error_reporting( E_ALL );
	ini_set( "display_errors", 1 );

	include_once('./_common.php');
	
//	echo "<span style='color:#fff'>".$member['mb_id']."_테스트</span>".time();

	if($is_guest) {

		include_once("./login.php");

	} else {


		if(!$fr_date) { $fr_date = date("Ymd"); }
		if(!$to_date) { $to_date = date("Ymd"); }
		$config['cf_page_rows'] = "20";
		/*
		if(!$p && $is_admin) {
//			$p = "member_table";
			$p = "main";
		} else {
			if(!$p || $p == "main") {
				$p = "main";
			}
		}
		*/
		if(!$p || $p == "main") {
			$p = "main";
		}

	//	echo $p;

		if(is_file("./".$p.".php")){
			include_once("./".$p.".php");
		} else {
			include_once("./error.php");
		}
	}
?>