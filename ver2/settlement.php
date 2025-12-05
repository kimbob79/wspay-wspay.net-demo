<?php
	if (!defined('_GNUBOARD_')) { include_once("./error.php"); exit; } // 개별 페이지 접근 불가

	$title1 = "정산관리";
	$title2 = "실시간 결제내역";
	require_once("./_head.php");

	if($member['mb_level'] == 3) {
		include_once("./settlement1.php");
	} else {
		include_once("./settlement2.php");
	}
	include_once("./_tail.php");