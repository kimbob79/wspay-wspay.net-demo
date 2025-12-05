<?php
	error_reporting( E_ALL );
	ini_set( "display_errors", 1 );

	include('./_common.php');


$gid = isset($_REQUEST['gid']) ? trim($_REQUEST['gid']) : '';						//
$wTid = isset($_REQUEST['wTid']) ? trim($_REQUEST['wTid']) : '';					//
$cardNm = isset($_REQUEST['cardNm']) ? trim($_REQUEST['cardNm']) : '';				//
$cancelYN = isset($_REQUEST['cancelYN']) ? trim($_REQUEST['cancelYN']) : '';		//
$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';						//
$ediNo = isset($_REQUEST['ediNo']) ? trim($_REQUEST['ediNo']) : '';					//
$appDtm = isset($_REQUEST['appDtm']) ? trim($_REQUEST['appDtm']) : '';				//
$ccDnt = isset($_REQUEST['ccDnt']) ? trim($_REQUEST['ccDnt']) : '';					//
$amt = isset($_REQUEST['amt']) ? trim($_REQUEST['amt']) : '';						//
$ordNm = isset($_REQUEST['ordNm']) ? trim($_REQUEST['ordNm']) : '';					//
$goodsName = isset($_REQUEST['goodsName']) ? trim($_REQUEST['goodsName']) : '';		//
$appNo = isset($_REQUEST['appNo']) ? trim($_REQUEST['appNo']) : '';					//
$quota = isset($_REQUEST['quota']) ? trim($_REQUEST['quota']) : '';					//
$cardNo = isset($_REQUEST['cardNo']) ? trim($_REQUEST['cardNo']) : '';				//
$catId = isset($_REQUEST['catId']) ? trim($_REQUEST['catId']) : '';					//
$tmnId = isset($_REQUEST['tmnId']) ? trim($_REQUEST['tmnId']) : '';					//



if($catId) {


	/******** 카정으로 전송 ************/

	$data = array('gid' => $gid, 'wTid' => $wTid, 'cardNm' => $cardNm, 'cancelYN' => $cancelYN, 'tid' => $tid, 'ediNo' => $ediNo, 'appDtm' => $appDtm, 'ccDnt' => $ccDnt, 'amt' => $amt, 'ordNm' => $ordNm, 'goodsName' => $goodsName, 'appNo' => $appNo, 'quota' => $quota, 'cardNo' => $cardNo, 'catId' => $catId, 'tmnId' => $tmnId);

	$url = 'http://redpay.kr/api/daou/index.php';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);

	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 요청 결과를 문자열로 받음
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // curl이 첫 응답 시간에 대한 timeout
	curl_setopt($ch, CURLOPT_TIMEOUT, 60); // curl 전체 실행 시간에 대한 timeout
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 원격 서버의 인증서가 유효한지 검사하지 않음
	$result = curl_exec($ch); // 요청 결과
	curl_close($ch);

	/******** 카정으로 전송 ************/

	$pay_type = "Y";
	$pay_cdatetime = "";

	// 취소
	if($cancelYN == "Y") {
		$pay_type = "N";
		$tid2 = $tid;
		$tid = "c".$tid;

		// 원거래
		$cancel = sql_fetch("select * from g5_payment_daou where tid = '{$tid2}'");

		// 취소일때 데이터 원거래에서 가져오기
		$pay_cdatetime =  date("Y-m-d H:i:s", strtotime($ccDnt));
		$appDtm = $ccDnt;
		/*
		$issuer = $cancel['issuer'];
		$acquirer = $cancel['acquirer'];
		$cardType = $cancel['cardType'];
		$bin = $cancel['bin'];
		$last4 = $cancel['last4'];
		$authCd = $cancel['authCd'];
		*/
//		$amt = $cancel['amt']; // 음수로 변경
		sql_query("update g5_payment set pay_cdatetime = '{$pay_cdatetime}' where trackId = '{$ediNo}' and pay_num = '{$appNo}'");
	}

	$sql_common = " gid ='{$gid}',
					wTid ='{$wTid}',
					cardNm ='{$cardNm}',
					cancelYN ='{$cancelYN}',
					tid ='{$tid}',
					ediNo ='{$ediNo}',
					appDtm ='{$appDtm}',
					ccDnt ='{$ccDnt}',
					amt ='{$amt}',
					ordNm ='{$ordNm}',
					goodsName ='{$goodsName}',
					appNo ='{$appNo}',
					quota ='{$quota}',
					cardNo ='{$cardNo}',
					catId ='{$catId}',
					tmnId ='{$tmnId}', ";

	$sql = "insert into g5_payment_daou set ".$sql_common." datetime = '".G5_TIME_YMDHIS."'";
	sql_query($sql);


	/*
	$arraydata = explode(PHP_EOL, trim($config['cf_2']));
	$arraydata = array_map('trim', $arraydata);

	if(in_array($catId, $arraydata)) {
		$catId = substr($ordNo, 0, -10);
		$dv_tid_ori = $catId;
	}

	// tid 쪼개기
	if($catId == $config['cf_7']) {
		$catId = substr($ordNo, 0, -10);
		$dv_tid_ori = $config['cf_7'];
	}
	*/


	$row2 = sql_fetch("select * from g5_device where dv_tid = '{$catId}'");

	$mb_1_fee = $row2['mb_1_fee'];
	$mb_2_fee = $row2['mb_2_fee'];
	$mb_3_fee = $row2['mb_3_fee'];
	$mb_4_fee = $row2['mb_4_fee'];
	$mb_5_fee = $row2['mb_5_fee'];
	$mb_6_fee = $row2['mb_6_fee'];


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



	$mb_1_pay = $amt * $row2['mb_1_fee'] /100;
	$mb_2_pay = $amt * $row2['mb_2_fee'] /100;
	$mb_3_pay = $amt * $row2['mb_3_fee'] /100;
	$mb_4_pay = $amt * $row2['mb_4_fee'] /100;
	$mb_5_pay = $amt * $row2['mb_5_fee'] /100;
	$mb_6_pay = $amt * $row2['mb_6_fee'] /100;
	$mb_6_pay = $amt - $mb_6_pay;




	$pay_datetime =  date("Y-m-d H:i:s", strtotime($appDtm));
//	$calday =  date("Ymd", strtotime($trxDate));


	$sql_common = " pay_type = '{$pay_type}',
					pay = '{$amt}',
					pay_num = '{$appNo}',
					trxid = '{$tid}',
					trackId = '{$ediNo}',
					pay_datetime = '{$pay_datetime}',
					pay_cdatetime = '{$pay_cdatetime}',
					pay_parti = '{$quota}',
					pay_card_name = '{$cardNm}',
					pay_card_num = '{$cardNo}',

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
					dv_tid = '{$catId}',
					dv_tid_ori = '{$dv_tid_ori}',
					pg_name = 'daou' ";


//	$pay = sql_fetch("select * from g5_payment where trxid = '{$tid}' and pay_num = '{$appNo}'");

	if(!$pay['pay_id']) {
		$sql = " insert into g5_payment set ".$sql_common.", datetime = '".G5_TIME_YMDHIS."'";
		sql_query($sql);
	}

	echo 'OK';
}