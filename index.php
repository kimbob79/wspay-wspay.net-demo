<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ini_set( "display_errors", 0);
 
	include_once('./_common.php');
	
//	echo "<span style='color:#fff'>".$member['mb_id']."_테스트</span>".time();

	if($is_guest) {
		if($_SERVER['SERVER_NAME'] == "www.cajung.com") {
			include_once("./login_go.php");
		} else if($_SERVER['SERVER_NAME'] == "cajung.com") {
			include_once("./login_go.php");
		} else {
			include_once("./login.php");
		}
	} else {


		if(!$fr_date) { $fr_date = date("Ymd"); }
		if(!$to_date) { $to_date = date("Ymd"); }
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

		include_once("./_head.php");
		include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
		if($t) {
			$write_table = $g5['write_prefix'] . $t; // 게시판 테이블 전체이름
		}
		if(is_file("./".$p.".php")){
			include_once("./".$p.".php");
		} else {
			echo "<div style='line-height:1000%; text-align:center;'>잘못된 접근입니다</div>";
		}
		include_once("./_tail.php");
	}
?>