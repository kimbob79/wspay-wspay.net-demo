<?php
	include('./_common.php');

	/*
	// 데이터 임시보관 루틴
	$indataFolder = './data/';
	$t = microtime(true);
	$micro = sprintf("%06d",($t - floor($t)) * 1000000);
	$d = new DateTime( date('Y-m-d H:i:s.'.$micro, $t) );
	$inTime = $d->format("YmdHisu");
	$post = '';
	foreach($_POST as $key => $value) {
		if($value) {
			if($post) $post .= '&';
			$post .= $key.'='.$value;
		}
	}
	$myFile = $indataFolder.$inTime.'.data';
	$bfh = fopen($myFile, 'a');
	fwrite($bfh, $post);
	fclose($bfh);
	// 데이터 임시보관 루틴 끝
	*/



	/*

	승인
	last4=1234&
	amount=1004&
	rootTrxId=&
	authCd=24005717&
	tmnId=TEST00001&
	trackId=20200218142719126570&
	bin=123456&
	cardType=%EC%B2%B4%ED%81%AC&
	trxType=PAY&
	acquirer=%EA%B5%AD%EB%AF%BC&
	trxId=T200218084212&
	issuer=%EA%B5%AD%EB%AF%BC&
	installment=00&
	mchtId=test&
	trxDate=20200218142719

	취소
	last4=1234&
	amount=1004&
	rootTrxId=T200218084212&
	authCd=24005717&
	tmnId=TEST00001&
	trackId=20200218143127051532&
	bin=123456&
	cardType=%EC%B2%B4%ED%81%AC&
	trxType=REFUND&
	acquirer=%EA%B5%AD%EB%AF%BC&
	trxId=T200218084219&
	issuer=%EA%B5%AD%EB%AF%BC&
	installment=00&
	mchtId=test&
	trxDate=20200218143219
	*/


	/*

	$mchtId = isset($_REQUEST['mchtId']) ? trim($_REQUEST['mchtId']) : ''; // 가맹점 ID
	$trxId = isset($_REQUEST['trxId']) ? trim($_REQUEST['trxId']) : ''; // 광원 거래번호
	$tmnId = isset($_REQUEST['tmnId']) ? trim($_REQUEST['tmnId']) : ''; // 터미널ID
	$trxDate = isset($_REQUEST['trxDate']) ? trim($_REQUEST['trxDate']) : ''; // 승인일시
	$trxType = isset($_REQUEST['trxType']) ? trim($_REQUEST['trxType']) : ''; // 거래구분
	$trackId = isset($_REQUEST['trackId']) ? trim($_REQUEST['trackId']) : ''; // 주문번호
	$authCd = isset($_REQUEST['authCd']) ? trim($_REQUEST['authCd']) : ''; // 승인번호
	$issuer = isset($_REQUEST['issuer']) ? trim($_REQUEST['issuer']) : ''; // 발행사
	$acquirer = isset($_REQUEST['acquirer']) ? trim($_REQUEST['acquirer']) : ''; // 매입사
	$cardType = isset($_REQUEST['cardType']) ? trim($_REQUEST['cardType']) : ''; // 카드종류
	$bin = isset($_REQUEST['bin']) ? trim($_REQUEST['bin']) : ''; // 카드번호(bin)
	$last4 = isset($_REQUEST['last4']) ? trim($_REQUEST['last4']) : ''; // 카드번호(last4)
	$installment = isset($_REQUEST['installment']) ? trim($_REQUEST['installment']) : ''; // 할부기간
	$amount = isset($_REQUEST['amount']) ? trim($_REQUEST['amount']) : ''; // 거래금액
	$rootTrxId = isset($_REQUEST['rootTrxId']) ? trim($_REQUEST['rootTrxId']) : ''; // 원거래번호


mchtId
trxId
tmnId
trxDate
trxType
trackId
authCd
issuer
acquirer
cardType
bin
last4
installment
amount
rootTrxId



	*/




	$mchtId = isset($_REQUEST['mchtId']) ? trim($_REQUEST['mchtId']) : ''; // 가맹점 ID
	$trxId = isset($_REQUEST['trxId']) ? trim($_REQUEST['trxId']) : ''; // 광원 거래번호
	$tmnId = isset($_REQUEST['tmnId']) ? trim($_REQUEST['tmnId']) : ''; // 터미널ID
	$trxDate = isset($_REQUEST['trxDate']) ? trim($_REQUEST['trxDate']) : ''; // 승인일시
	$trxType = isset($_REQUEST['trxType']) ? trim($_REQUEST['trxType']) : ''; // 거래구분
	$trackId = isset($_REQUEST['trackId']) ? trim($_REQUEST['trackId']) : ''; // 주문번호
	$authCd = isset($_REQUEST['authCd']) ? trim($_REQUEST['authCd']) : ''; // 승인번호
	$issuer = isset($_REQUEST['issuer']) ? trim($_REQUEST['issuer']) : ''; // 발행사
	$acquirer = isset($_REQUEST['acquirer']) ? trim($_REQUEST['acquirer']) : ''; // 매입사
	$cardType = isset($_REQUEST['cardType']) ? trim($_REQUEST['cardType']) : ''; // 카드종류
	$bin = isset($_REQUEST['bin']) ? trim($_REQUEST['bin']) : ''; // 카드번호(bin)
	$last4 = isset($_REQUEST['last4']) ? trim($_REQUEST['last4']) : ''; // 카드번호(last4)
	$installment = isset($_REQUEST['installment']) ? trim($_REQUEST['installment']) : ''; // 할부기간
	$amount = isset($_REQUEST['amount']) ? trim($_REQUEST['amount']) : ''; // 거래금액
	$rootTrxId = isset($_REQUEST['rootTrxId']) ? trim($_REQUEST['rootTrxId']) : ''; // 원거래번호


	$pay_type = "Y";
	$pay_cdatetime = "";

	// 취소
	if($trxType == "REFUND") {
		$pay_type = "N";

		// 원거래
		$cancel = sql_fetch("select * from g5_payment_k1 where trxId = '{$rootTrxId}'");

		// 취소일때 데이터 원거래에서 가져오기
		$pay_cdatetime =  date("Y-m-d H:i:s", strtotime($trxDate));
		$issuer = $cancel['issuer'];
		$acquirer = $cancel['acquirer'];
		$cardType = $cancel['cardType'];
		$bin = $cancel['bin'];
		$last4 = $cancel['last4'];
		$authCd = $cancel['authCd'];
//		$amount = "-".$cancel['amount'];
		$amount = "-".$amount;
		$trackId = $cancel['trackId'];
	}

	$sql_common = " mchtId='{$mchtId}',
					trxId='{$trxId}',
					tmnId='{$tmnId}',
					trxDate='{$trxDate}',
					trxType='{$trxType}',
					trackId='{$trackId}',
					authCd='{$authCd}',
					issuer='{$issuer}',
					acquirer='{$acquirer}',
					cardType='{$cardType}',
					bin='{$bin}',
					last4='{$last4}',
					installment='{$installment}',
					amount='{$amount}',
					rootTrxId='{$rootTrxId}', ";

	$sql = "insert into g5_payment_k1 set ".$sql_common." datetime = '".G5_TIME_YMDHIS."'";
	sql_query($sql);

	sql_query("update g5_payment set pay_cdatetime = '{$pay_cdatetime}' where trxId = '{$rootTrxId}'");


	$arraydata = explode(PHP_EOL, trim($config['cf_1']));
	$arraydata = array_map('trim', $arraydata);

	if(in_array($tmnId, $arraydata)) {
		$tmnId = substr($trackId, 0, -10);
		$dv_tid_ori = $tmnId;
	}



	/*
	// tid 쪼개기
	if($tmnId == $config['cf_1']) {
		$tmnId = substr($trackId, 0, -10);
		$dv_tid_ori = $config['cf_1'];
	} else if($tmnId == $config['cf_2']) {
		$tmnId = substr($trackId, 0, -10);
		$dv_tid_ori = $config['cf_2'];
	} else if($tmnId == $config['cf_3']) {
		$tmnId = substr($trackId, 0, -10);
		$dv_tid_ori = $config['cf_3'];
	} else if($tmnId == $config['cf_4']) {
		$tmnId = substr($trackId, 0, -10);
		$dv_tid_ori = $config['cf_4'];
	} else if($tmnId == $config['cf_5']) {
		$tmnId = substr($trackId, 0, -10);
		$dv_tid_ori = $config['cf_5'];
	} else if($tmnId == $config['cf_6']) {
		$tmnId = substr($trackId, 0, -10);
		$dv_tid_ori = $config['cf_6'];
	}
	*/

	$row2 = sql_fetch("select * from g5_device where dv_tid = '{$tmnId}'");


	$mb_1_fee = number_format($row2['mb_1_fee'], 2);
	$mb_2_fee = number_format($row2['mb_2_fee'], 2);
	$mb_3_fee = number_format($row2['mb_3_fee'], 2);
	$mb_4_fee = number_format($row2['mb_4_fee'], 2);
	$mb_5_fee = number_format($row2['mb_5_fee'], 2);
	$mb_6_fee = number_format($row2['mb_6_fee'], 2);


	if($row2['mb_5_fee'] > 0.001) {
		$row2['mb_5_fee'] = $row2['mb_6_fee'] - $row2['mb_5_fee'];
	} else {
		$row2['mb_5_fee'] = 0.00;
	}

	if($row2['mb_4_fee'] > 0.001) {
		$row2['mb_4_fee'] = $row2['mb_6_fee'] - $row2['mb_5_fee'] - $row2['mb_4_fee'];
	} else {
		$row2['mb_4_fee'] = 0.00;
	}

	if($row2['mb_3_fee'] > 0.001) {
		$row2['mb_3_fee'] = $row2['mb_6_fee'] - $row2['mb_5_fee'] - $row2['mb_4_fee'] - $row2['mb_3_fee'];
	} else {
		$row2['mb_3_fee'] = 0.00;
	}

	if($row2['mb_2_fee'] > 0.001) {
		$row2['mb_2_fee'] = $row2['mb_6_fee'] - $row2['mb_5_fee'] - $row2['mb_4_fee'] - $row2['mb_3_fee'] - $row2['mb_2_fee'];
	} else {
		$row2['mb_2_fee'] = 0.00;
	}

	$row2['mb_1_fee'] = $row2['mb_6_fee'] - $row2['mb_5_fee'] - $row2['mb_4_fee'] - $row2['mb_3_fee'] - $row2['mb_2_fee'] - $row2['mb_1_fee'];



	$mb_1_pay = $amount * $row2['mb_1_fee'] /100;
	$mb_2_pay = $amount * $row2['mb_2_fee'] /100;
	$mb_3_pay = $amount * $row2['mb_3_fee'] /100;
	$mb_4_pay = $amount * $row2['mb_4_fee'] /100;
	$mb_5_pay = $amount * $row2['mb_5_fee'] /100;
	$mb_6_pay = $amount * $row2['mb_6_fee'] /100;
	$mb_6_pay = $amount - $mb_6_pay;




	$pay_datetime =  date("Y-m-d H:i:s", strtotime($trxDate));
//	$calday =  date("Ymd", strtotime($trxDate));


	$sql_common = " pay_type = '{$pay_type}',
					pay = '{$amount}',
					pay_num = '{$authCd}',
					trxid = '{$trxId}',
					trackId = '{$trackId}',
					pay_datetime = '{$pay_datetime}',
					pay_cdatetime = '{$pay_cdatetime}',
					pay_parti = '{$installment}',
					pay_card_name = '{$issuer}',
					pay_card_num = '{$last4}',

					mb_1 = '{$row2['mb_1']}',
					mb_1_name = '{$row2['mb_1_name']}',
					mb_1_fee = '{$mb_1_fee}',
					mb_1_pay = '{$mb_1_pay}',

					mb_2 = '{$row2['mb_2']}',
					mb_2_name = '{$row2['mb_2_name']}',
					mb_2_fee = '{$mb_2_fee}',
					mb_2_pay = '{$mb_2_pay}',

					mb_3 = '{$row2['mb_3']}',
					mb_3_name = '{$row2['mb_3_name']}',
					mb_3_fee = '{$mb_3_fee}',
					mb_3_pay = '{$mb_3_pay}',

					mb_4 = '{$row2['mb_4']}',
					mb_4_name = '{$row2['mb_4_name']}',
					mb_4_fee = '{$mb_4_fee}',
					mb_4_pay = '{$mb_4_pay}',

					mb_5 = '{$row2['mb_5']}',
					mb_5_name = '{$row2['mb_5_name']}',
					mb_5_fee = '{$mb_5_fee}',
					mb_5_pay = '{$mb_5_pay}',

					mb_6 = '{$row2['mb_6']}',
					mb_6_name = '{$row2['mb_6_name']}',
					mb_6_fee = '{$mb_6_fee}',
					mb_6_pay = '{$mb_6_pay}',

					dv_type = '{$row2['dv_type']}',
					dv_certi = '{$row2['dv_certi']}',
					dv_tid = '{$tmnId}',
					dv_tid_ori = '{$dv_tid_ori}',
					pg_name = 'k1' ";


	$pay = sql_fetch("select * from g5_payment where trxid = '{$trxid}' and pay_num = '{$authCd}'");

	if(!$pay['pay_id']) {
		$sql = " insert into g5_payment set ".$sql_common.", datetime = '".G5_TIME_YMDHIS."'";
		sql_query($sql);
	}

	if($noError == false) {
		echo 'OK';
	} else {
		echo 'ERROR';
	}
	
	
	
	
	
	