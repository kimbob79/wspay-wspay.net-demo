<?php
	error_reporting(E_ALL);
	ini_set("display_errors", 0);

	include_once('./_common.php');

//	print "<pre>"; print_r($_POST); print "</pre>";
//	exit;

	if($_POST['mb_id'] != $member['mb_id']) {
//		echo "잘못된 접근입니다. 결제자 아이디 다름";
//		exit;
	}


	$pg_id = trim($_POST['pg_id']);										// PG 아이디
	$payments = trim($_POST['pg_code']);								// PG 코드
	$mb_id = trim($_POST['mb_id']);										// 아이디
	$url_id = trim($_POST['url_id']);									// URL 아이디
	$pg_key1 = trim($_POST['pg_key1']);									// PG KEY1
	$pg_key2 = trim($_POST['pg_key2']);									// PG KEY2
	$pg_key3 = trim($_POST['pg_key3']);									// PG KEY3
	$pg_key4 = trim($_POST['pg_key4']);									// PG KEY4
	$pg_key5 = trim($_POST['pg_key5']);									// PG KEY5
	$pay_product = trim($_POST['pay_product']);							// 상품명
	$pay_price = preg_replace("/[^0-9]/","",$_POST['pay_price']);		// 결제금액
	$pay_pname = trim($_POST['pay_pname']);								// 카드주명
	$pay_phone = trim($_POST['pay_phone']);								// 휴대전화번호
	$pay_email = trim($_POST['pay_email']);								// 이메일
	$pay_cardnum = preg_replace("/[^0-9]/","",$_POST['pay_cardnum']);	// 카드번호
	$pay_installment = trim($_POST['pay_installment']);					// 할부
	$pay_MM = trim($_POST['pay_MM']);									// 유효기간 월
	$pay_YY = trim($_POST['pay_YY']);									// 유효기간 년
	$pay_MY = $pay_MM.$pay_YY;											// MMYY 0321 03월 21년 
	$pay_YM = $pay_YY.$pay_MM;											// YYMM 2103 21년 03월
	$pay_password = trim($_POST['pay_password']);						// 비밀번호
	$pay_certify = trim($_POST['pay_certify']);							// 생년월일
	$cardAuth = trim($_POST['cardAuth']);								// 구인증 true 비인증 false

	// 회원정보
	$mb = get_member($mb_id);

	// 회원 PG정보
	$member_pg = sql_fetch(" select * from pay_member_pg where mb_id = '".$mb_id."' and pg_id = '".$pg_id."' ");

	// PG정보
	$pg = sql_fetch(" select * from pay_pg where pg_id = '".$member_pg['pg_mid']."' ");


	// 중복결제
	if($member_pg['pg_overlap'] == '1') { // 중복결제가 불가능할때
		$row_card = sql_fetch(" select count(pay_cardnum) as pay_cardnum from pay_payment where card_yn = 'Y' and card_num != '' and pay_cardnum = '{$pay_cardnum}' and date_format(updatetime,'%Y-%m-%d') = date_format(NOW(),'%Y-%m-%d')");
		if($row_card['pay_cardnum'] > 0) {
			echo "중복결제 불가";
			exit;
		}
	}

	// 한도
	if($pay_price > $member_pg['pg_pay']) {
		echo "해당결제는 ".$member_pg['pg_pay']."원 이하만 가능합니다.";
		exit;
	}



	// 원본 저장
	$sql = " insert into pay_payment
				set payments = '".$payments."',
					mb_id = '".$mb_id."',
					url_id = '".$url_id."',
					pg_id = '".$member_pg['pg_id']."',
					pg_type = '".$pg['pg_certified']."',
					mb_name = '".$mb['mb_nick']."',
					pg_key1 = '".$pg_key1."',
					pg_key2 = '".$pg_key2."',
					pg_key3 = '".$pg_key3."',
					pg_key4 = '".$pg_key4."',
					pg_key5 = '".$pg_key5."',

					pay_product = '".$pay_product."',
					pay_price = '".$pay_price."',
					pay_pname = '".$pay_pname."',
					pay_phone = '".$pay_phone."',
					pay_email = '".$pay_email."',
					pay_cardnum = '".$pay_cardnum."',
					pay_installment = '".$pay_installment."',
					pay_MM = '".$pay_MM."',
					pay_YY = '".$pay_YY."',
					pay_password = '".$pay_password."',
					pay_certify = '".$pay_certify."',
					datetime = '".G5_TIME_YMDHIS."' ";
	sql_query($sql);

	$pay_id = sql_insert_id();



	include_once('./payment_inc_k1.php');
	include_once('./payment_inc_welcom.php');
	include_once('./payment_inc_danal.php');
	include_once('./payment_inc_paysis.php');
	include_once('./payment_inc_stn.php');
	include_once('./payment_inc_lucy.php');



