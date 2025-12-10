<?php
$logDir = '/mpchosting/www/api/logs';
$today = date('Y-m-d');
$logFile = $logDir . '/error_' . $today . '.log';

ini_set('error_log', $logFile);

	error_reporting( E_ALL );
	ini_set( "display_errors", 1 );

	include('./_common.php');

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
	*/

$gid = isset($_REQUEST['gid']) ? trim($_REQUEST['gid']) : '';							// 그룹 ID
$vid = isset($_REQUEST['vid']) ? trim($_REQUEST['vid']) : '';							// VAN ID
$mid = isset($_REQUEST['mid']) ? trim($_REQUEST['mid']) : '';							// 상점 ID 
$payMethod = isset($_REQUEST['payMethod']) ? trim($_REQUEST['payMethod']) : '';			// 결제수단 
$appCardCd = isset($_REQUEST['appCardCd']) ? trim($_REQUEST['appCardCd']) : '';			// 카드코드/은행코드/ 상품권사코드/ 휴대폰코드 
$cancelYN  = isset($_REQUEST['cancelYN']) ? trim($_REQUEST['cancelYN']) : '';			// 취소구분
$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';							// 거래고유번호
$ediNo  = isset($_REQUEST['ediNo']) ? trim($_REQUEST['ediNo']) : '';					// VAN거래고유번호
$appDtm = isset($_REQUEST['appDtm']) ? trim($_REQUEST['appDtm']) : '';					// 승인일
$ccDnt  = isset($_REQUEST['ccDnt']) ? trim($_REQUEST['ccDnt']) : '';					// 취소일 
$amt = isset($_REQUEST['amt']) ? trim($_REQUEST['amt']) : '';							// 금액 
$remainAmt = isset($_REQUEST['remainAmt']) ? trim($_REQUEST['remainAmt']) : '';			// 잔액 
$buyerId = isset($_REQUEST['buyerId']) ? trim($_REQUEST['buyerId']) : '';				// 구매자 ID 
$ordNm = isset($_REQUEST['ordNm']) ? trim($_REQUEST['ordNm']) : '';						// 구매자명 
$ordNo = isset($_REQUEST['ordNo']) ? trim($_REQUEST['ordNo']) : '';						// 주문번호 
$goodsName  = isset($_REQUEST['goodsName']) ? trim($_REQUEST['goodsName']) : '';		// 상품명 
$appNo = isset($_REQUEST['appNo']) ? trim($_REQUEST['appNo']) : '';						// 승인번호 
$quota = isset($_REQUEST['quota']) ? trim($_REQUEST['quota']) : '';						// 할부개월 
$notiDnt  = isset($_REQUEST['notiDnt']) ? trim($_REQUEST['notiDnt']) : '';			// Noti 통보일 
$cardNo = isset($_REQUEST['cardNo']) ? trim($_REQUEST['cardNo']) : '';					// 카드번호
$catId = isset($_REQUEST['catId']) ? trim($_REQUEST['catId']) : '';						// 단말기 CAT_ID
$tPhone = isset($_REQUEST['tPhone']) ? trim($_REQUEST['tPhone']) : '';					// phone 번호 입력 사항
$canAmt = isset($_REQUEST['canAmt']) ? trim($_REQUEST['canAmt']) : '';					// 취소금액
$partCanFlg = isset($_REQUEST['partCanFlg']) ? trim($_REQUEST['partCanFlg']) : '';		// 부분취소여부
$connCD = isset($_REQUEST['connCD']) ? trim($_REQUEST['connCD']) : '';					// 단말기/수기결제 구분
$usePointAmt = isset($_REQUEST['usePointAmt']) ? trim($_REQUEST['usePointAmt']) : '';	// 카드사 사용포인트
$vacntNo = isset($_REQUEST['vacntNo']) ? trim($_REQUEST['vacntNo']) : '';				// 가상계좌 번호
$socHpNo = isset($_REQUEST['socHpNo']) ? trim($_REQUEST['socHpNo']) : '';				// 휴대폰번호






if($payMethod) {


	/******** 외부 전송 코드 삭제됨 - API_EXTERNAL_TRANSMISSION_REMOVED.md 참고 ************/

	$pay_type = "Y";
	$pay_cdatetime = "";

	// 취소
	if($cancelYN == "Y") {
		$pay_type = "N";
		$tid2 = $tid;
		$tid = "c".$tid;

		// 원거래
		$cancel = sql_fetch("select * from g5_payment_korpay where tid = '{$tid2}'");

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
		$amt = "-".$cancel['amt']; // 음수로 변경
		sql_query("update g5_payment set pay_cdatetime = '{$pay_cdatetime}' where trxId = '{$tid2}'");
	}

	$sql_common = " gid ='{$gid}',
					vid ='{$vid}',
					mid ='{$mid}',
					payMethod ='{$payMethod}',
					appCardCd ='{$appCardCd}',
					cancelYN  ='{$cancelYN }',
					tid ='{$tid}',
					ediNo  ='{$ediNo }',
					appDtm ='{$appDtm}',
					ccDnt  ='{$ccDnt }',
					amt ='{$amt}',
					remainAmt ='{$remainAmt}',
					buyerId ='{$buyerId}',
					ordNm ='{$ordNm}',
					ordNo ='{$ordNo}',
					goodsName  ='{$goodsName }',
					appNo ='{$appNo}',
					quota ='{$quota}',
					notiDnt  ='{$notiDnt }',
					cardNo ='{$cardNo}',
					catId ='{$catId}',
					tPhone ='{$tPhone}',
					canAmt ='{$canAmt}',
					partCanFlg ='{$partCanFlg}',
					connCD ='{$connCD}',
					usePointAmt ='{$usePointAmt}',
					vacntNo ='{$vacntNo}',
					socHpNo ='{$socHpNo}', ";

	$sql = "insert into g5_payment_korpay set ".$sql_common." datetime = '".G5_TIME_YMDHIS."'";
	sql_query($sql);


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



	if($appCardCd == "01") { $appCardCd = "비씨";
	} else if($appCardCd == "02") { $appCardCd = "국민";
	} else if($appCardCd == "03") { $appCardCd = "하나(구외환)";
	} else if($appCardCd == "04") { $appCardCd = "삼성";
	} else if($appCardCd == "06") { $appCardCd = "신한";
	} else if($appCardCd == "07") { $appCardCd = "현대";
	} else if($appCardCd == "08") { $appCardCd = "롯데";
	} else if($appCardCd == "09") { $appCardCd = "한미";
	} else if($appCardCd == "10") { $appCardCd = "신세계한미";
	} else if($appCardCd == "11") { $appCardCd = "씨티";
	} else if($appCardCd == "12") { $appCardCd = "NH농협카드";
	} else if($appCardCd == "13") { $appCardCd = "수협";
	} else if($appCardCd == "14") { $appCardCd = "평화";
	} else if($appCardCd == "15") { $appCardCd = "우리";
	} else if($appCardCd == "16") { $appCardCd = "하나";
	} else if($appCardCd == "17") { $appCardCd = "동남(주택)";
	} else if($appCardCd == "18") { $appCardCd = "주택";
	} else if($appCardCd == "19") { $appCardCd = "조흥(강원)";
	} else if($appCardCd == "20") { $appCardCd = "축협(농협)";
	} else if($appCardCd == "21") { $appCardCd = "광주";
	} else if($appCardCd == "22") { $appCardCd = "전북";
	} else if($appCardCd == "23") { $appCardCd = "제주";
	} else if($appCardCd == "24") { $appCardCd = "산은";
	} else if($appCardCd == "25") { $appCardCd = "해외비자";
	} else if($appCardCd == "26") { $appCardCd = "해외마스터";
	} else if($appCardCd == "27") { $appCardCd = "해외다이너스";
	} else if($appCardCd == "28") { $appCardCd = "해외AMX";
	} else if($appCardCd == "29") { $appCardCd = "해외JCB";
	} else if($appCardCd == "30") { $appCardCd = "해외";
	} else if($appCardCd == "31") { $appCardCd = "SK-OKCashBag";
	} else if($appCardCd == "32") { $appCardCd = "우체국";
	} else if($appCardCd == "33") { $appCardCd = "MG새마을체크";
	} else if($appCardCd == "34") { $appCardCd = "중국은행체크";
	} else if($appCardCd == "38") { $appCardCd = "은련";
	} else if($appCardCd == "46") { $appCardCd = "카카오";
	} else if($appCardCd == "47") { $appCardCd = "강원"; }



	$sql_common = " pay_type = '{$pay_type}',
					pay = '{$amt}',
					pay_num = '{$appNo}',
					trxid = '{$tid}',
					trackId = '{$ordNo}',
					pay_datetime = '{$pay_datetime}',
					pay_cdatetime = '{$pay_cdatetime}',
					pay_parti = '{$quota}',
					pay_card_name = '{$appCardCd}',
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
					pg_name = 'korpay' ";


//	$pay = sql_fetch("select * from g5_payment where trxid = '{$tid}' and pay_num = '{$appNo}'");

	if(!$pay['pay_id']) {
		$sql = " insert into g5_payment set ".$sql_common.", datetime = '".G5_TIME_YMDHIS."'";
		sql_query($sql);
	}

	if($noError == false) {
		echo 'OK';
	} else {
		echo 'ERROR';
	}
	
	
	
	// 추가 API 호출
	/*
	$notification_url = 'https://api.wannapayments.kr/api/v1/payment/notification/korpay';
	
	$notification_data = array(
		'gid' => $gid,
		'vid' => $vid,
		'mid' => $mid,
		'payMethod' => $payMethod,
		'appCardCd' => $appCardCd,
		'cancelYN' => $cancelYN,
		'tid' => $tid,
		'ediNo' => $ediNo,
		'appDtm' => $appDtm,
		'ccDnt' => $ccDnt,
		'amt' => $amt,
		'remainAmt' => $remainAmt,
		'buyerId' => $buyerId,
		'ordNm' => $ordNm,
		'ordNo' => $ordNo,
		'goodsName' => $goodsName,
		'appNo' => $appNo,
		'quota' => $quota,
		'notiDnt' => $notiDnt,
		'cardNo' => $cardNo,
		'catId' => $catId,
		'tPhone' => $tPhone,
		'canAmt' => $canAmt,
		'partCanFlg' => $partCanFlg,
		'connCD' => $connCD,
		'usePointAmt' => $usePointAmt,
		'vacntNo' => $vacntNo,
		'socHpNo' => $socHpNo
	);

	try {
		$ch = curl_init();
		if ($ch === false) {
			throw new Exception('Failed to initialize cURL');
		}

		curl_setopt($ch, CURLOPT_URL, $notification_url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($notification_data, '', '&'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$notification_result = curl_exec($ch);
		
		if ($notification_result === false) {
			$error = curl_error($ch);
			$errno = curl_errno($ch);
			curl_close($ch);
			error_log("[Wanna API Error] errno: {$errno}, error: {$error}, data: " . json_encode($notification_data));
		} else {
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if ($http_code >= 400) {
				error_log("[Wanna API Error] HTTP Code: {$http_code}, Response: {$notification_result}, data: " . json_encode($notification_data));
			}else{
				error_log("korpay>>".$notification_result);
			}
			curl_close($ch);
		}
	} catch (Exception $e) {
		error_log("[Wanna API Exception] " . $e->getMessage() . ", data: " . json_encode($notification_data));
		if (isset($ch) && is_resource($ch)) {
			curl_close($ch);
		}
	}
	*/
}
?>