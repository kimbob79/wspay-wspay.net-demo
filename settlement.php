<?php
	if (!defined('_GNUBOARD_')) { include_once("./error.php"); exit; } // 개별 페이지 접근 불가

	$title1 = "정산관리";
	$title2 = "실시간 결제내역";

	if($member['mb_level'] == 3) {
		include_once("./settlement_user.php");
	} else {
		include_once("./settlement_master.php");
	}