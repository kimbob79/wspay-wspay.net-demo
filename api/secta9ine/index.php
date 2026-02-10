<?php
	error_reporting( E_ALL );
	ini_set( "display_errors", 1 );
	date_default_timezone_set('Asia/Seoul');

	// ========================================
	// 요청 로깅 - /logs/trans/api/secta9ine
	// ========================================
	$log_dir = dirname(__FILE__) . '/../../logs/trans/api/secta9ine';
	if(!is_dir($log_dir)) {
		@mkdir($log_dir, 0755, true);
	}

	$log_file = $log_dir . '/' . date('Y-m-d') . '.log';
	$log_time = date('Y-m-d H:i:s');
	$log_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
	$log_method = $_SERVER['REQUEST_METHOD'] ?? 'unknown';

	// 요청 데이터 수집
	$log_data = [
		'timestamp' => $log_time,
		'ip' => $log_ip,
		'method' => $log_method,
		'GET' => $_GET,
		'POST' => $_POST,
		'REQUEST' => $_REQUEST,
		'raw_input' => file_get_contents('php://input')
	];

	// 로그 기록
	$log_entry = "[{$log_time}] [{$log_ip}] [{$log_method}]\n";
	$log_entry .= json_encode($log_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
	$log_entry .= str_repeat('-', 80) . "\n";

	@file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
	// ========================================

	include('./_common.php');

$cmd = isset($_REQUEST['cmd']) ? trim($_REQUEST['cmd']) : '';
$paymethod = isset($_REQUEST['paymethod']) ? trim($_REQUEST['paymethod']) : '';
$payType = isset($_REQUEST['payType']) ? trim($_REQUEST['payType']) : '';
$requestFlag = isset($_REQUEST['requestFlag']) ? trim($_REQUEST['requestFlag']) : '';
$mbrRefNo = isset($_REQUEST['mbrRefNo']) ? trim($_REQUEST['mbrRefNo']) : '';
$mbrNo = isset($_REQUEST['mbrNo']) ? trim($_REQUEST['mbrNo']) : '';
$refNo = isset($_REQUEST['refNo']) ? trim($_REQUEST['refNo']) : '';
$tranDate = isset($_REQUEST['tranDate']) ? trim($_REQUEST['tranDate']) : '';
$tranTime = isset($_REQUEST['tranTime']) ? trim($_REQUEST['tranTime']) : '';
$orgRefNo = isset($_REQUEST['orgRefNo']) ? trim($_REQUEST['orgRefNo']) : '';
$orgTranDate = isset($_REQUEST['orgTranDate']) ? trim($_REQUEST['orgTranDate']) : '';
$vanCatId = isset($_REQUEST['vanCatId']) ? trim($_REQUEST['vanCatId']) : '';
$cardMerchNo = isset($_REQUEST['cardMerchNo']) ? trim($_REQUEST['cardMerchNo']) : '';
$applNo = isset($_REQUEST['applNo']) ? trim($_REQUEST['applNo']) : '';
$issueCompanyNo = isset($_REQUEST['issueCompanyNo']) ? trim($_REQUEST['issueCompanyNo']) : '';
$acqCompanyNo = isset($_REQUEST['acqCompanyNo']) ? trim($_REQUEST['acqCompanyNo']) : '';
$cardNo = isset($_REQUEST['cardNo']) ? trim($_REQUEST['cardNo']) : '';
$installNo = isset($_REQUEST['installNo']) ? trim($_REQUEST['installNo']) : '';
$goodsName = isset($_REQUEST['goodsName']) ? trim($_REQUEST['goodsName']) : '';
$amount = isset($_REQUEST['amount']) ? trim($_REQUEST['amount']) : '';
$customerName = isset($_REQUEST['customerName']) ? trim($_REQUEST['customerName']) : '';
$customerTelNo = isset($_REQUEST['customerTelNo']) ? trim($_REQUEST['customerTelNo']) : '';
$customerEmail = isset($_REQUEST['customerEmail']) ? trim($_REQUEST['customerEmail']) : '';
$sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
$retailerCode = isset($_REQUEST['retailerCode']) ? trim($_REQUEST['retailerCode']) : '';

$tranDatetranTime = "20".$tranDate.$tranTime;

if($paymethod) {



	/******** 외부 전송 코드 삭제됨 - API_EXTERNAL_TRANSMISSION_REMOVED.md 참고 ************/






	/******** MBR 노티전송 코드 삭제됨 - API_EXTERNAL_TRANSMISSION_REMOVED.md 참고 ************/






	// TID 노티전송
	/*
	$sql = "SELECT * FROM g5_noti WHERE nt_mbrno = '{$vanCatId}'";
	$url_row = sql_fetch($sql);

	if ($url_row['nt_id']) {
		
		$data = array('cmd' => $cmd, 'paymethod' => $paymethod, 'payType' => $payType, 'requestFlag' => $requestFlag, 'mbrRefNo' => $mbrRefNo, 'mbrNo' => $mbrNo, 'refNo' => $refNo, 'tranDate' => $tranDate, 'tranTime' => $tranTime, 'orgRefNo' => $orgRefNo, 'orgTranDate' => $orgTranDate, 'vanCatId' => $vanCatId, 'cardMerchNo' => $cardMerchNo, 'applNo' => $applNo, 'issueCompanyNo' => $issueCompanyNo, 'acqCompanyNo' => $acqCompanyNo, 'cardNo' => $cardNo, 'installNo' => $installNo, 'goodsName' => $goodsName, 'amount' => $amount, 'customerName' => $customerName, 'customerTelNo' => $customerTelNo, 'customerEmail' => $customerEmail, 'sid' => $sid, 'retailerCode' => $retailerCode);

		$url = $url_row['nt_url'];

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
		
		$sql = " update g5_noti set lastupdate = '".G5_TIME_YMDHIS."' where nt_id = '{$url_row['nt_id']}' ";
		sql_query($sql);

	}
	*/


/*
	if(in_array($mbrNo, $arraydata)) {
		$tid2 = substr($mbrRefNo, 0, -10);
		
		if($tid2 == "WNS1758156813" || $tid2 == "WNS1757565555") {

			$data = array('cmd' => $cmd, 'paymethod' => $paymethod, 'payType' => $payType, 'requestFlag' => $requestFlag, 'mbrRefNo' => $mbrRefNo, 'mbrNo' => $mbrNo, 'refNo' => $refNo, 'tranDate' => $tranDate, 'tranTime' => $tranTime, 'orgRefNo' => $orgRefNo, 'orgTranDate' => $orgTranDate, 'vanCatId' => $vanCatId, 'cardMerchNo' => $cardMerchNo, 'applNo' => $applNo, 'issueCompanyNo' => $issueCompanyNo, 'acqCompanyNo' => $acqCompanyNo, 'cardNo' => $cardNo, 'installNo' => $installNo, 'goodsName' => $goodsName, 'amount' => $amount, 'customerName' => $customerName, 'customerTelNo' => $customerTelNo, 'customerEmail' => $customerEmail, 'sid' => $sid, 'retailerCode' => $retailerCode);

			$url = 'https://www.salesbilling.co.kr:3636/api/wanna/noti/tran';

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
		}
	}
*/
	/******** 외부로 전송 ************/




	
	
	
	
	
	
	
	
	
	

	$pay_type = "Y";
	$pay_cdatetime = "";

	// 취소
	if($cmd != "0") {

		if($cmd == "2") {
			$pay_type = "B"; // 부분취소
		} else {
			$pay_type = "N";
		}

		// 원거래
		$cancel = sql_fetch("select * from g5_payment_stn where refNo = '{$orgRefNo}'");

		// 취소일때 데이터 원거래에서 가져오기
		$pay_cdatetime =  date("Y-m-d H:i:s", strtotime($tranDatetranTime));
		$appDtm = $ccDnt;
		/*
		$issuer = $cancel['issuer'];
		$acquirer = $cancel['acquirer'];
		$cardType = $cancel['cardType'];
		$bin = $cancel['bin'];
		$last4 = $cancel['last4'];
		$authCd = $cancel['authCd'];
		*/
		$amount = "-".$amount; // 음수로 변경
		sql_query("update g5_payment set pay_cdatetime = '{$pay_cdatetime}' where trxId = '{$orgRefNo}'");
	}

	$sql_common = " cmd ='{$cmd}',
					paymethod ='{$paymethod}',
					payType ='{$payType}',
					requestFlag ='{$requestFlag}',
					mbrRefNo ='{$mbrRefNo}',
					mbrNo ='{$mbrNo}',
					refNo ='{$refNo}',
					tranDate ='{$tranDate}',
					tranTime ='{$tranTime}',
					orgRefNo ='{$orgRefNo}',
					orgTranDate ='{$orgTranDate}',
					vanCatId ='{$vanCatId}',
					cardMerchNo ='{$cardMerchNo}',
					applNo ='{$applNo}',
					issueCompanyNo ='{$issueCompanyNo}',
					acqCompanyNo ='{$acqCompanyNo}',
					cardNo ='{$cardNo}',
					installNo ='{$installNo}',
					goodsName ='{$goodsName}',
					amount ='{$amount}',
					customerName ='{$customerName}',
					customerTelNo ='{$customerTelNo}',
					customerEmail ='{$customerEmail}',
					sid ='{$sid}',
					retailerCode ='{$retailerCode}', ";

	$sql = "insert into g5_payment_stn set ".$sql_common." datetime = '".G5_TIME_YMDHIS."'";
	sql_query($sql);
	$stn_insert_id = sql_insert_id();

	// 동기화 상태 변수 초기화
	$sync_status = 'pending';
	$sync_message = '';

	// ========================================
	// 수기결제 (requestFlag = 'K')
	// ========================================
	if($requestFlag == 'K') {
		// 1. mbrRefNo(주문번호) 앞 4자리(mkc_oid)로 Keyin 설정 조회
		// 대표가맹점/개별설정 상관없이 mkc_oid로 구분
		$mkc_oid = substr($mbrRefNo, 0, 4);
		$keyin_config = sql_fetch("SELECT * FROM g5_member_keyin_config
			WHERE mkc_use = 'Y' AND mkc_status = 'active' AND mkc_pg_code = 'stn'
			AND mkc_oid = '{$mkc_oid}'");

		$target_mb_id = $keyin_config['mb_id'] ?? '';

		// 2. 해당 가맹점의 디바이스 정보 조회
		$row2 = array();
		if($target_mb_id) {
			$row2 = sql_fetch("SELECT d.*, d.mb_6 as merchant_mb_id
				FROM g5_device d
				WHERE d.mb_6 = '{$target_mb_id}'
				LIMIT 1");
		}

		// 디바이스 조회 실패 체크
		if(!$row2['dv_id']) {
			$sync_status = 'failed';
			$sync_message = $target_mb_id ? "device not found for mb_id '{$target_mb_id}'" : "keyin config not found for mkc_oid '{$mkc_oid}'";
		}

		$dv_tid_ori = $mbrNo;
		$dv_tid_value = $row2['dv_tid']; // 수기결제는 디바이스 TID 사용
		$pg_name_value = 'stn_k'; // 수기결제 구분
		$dv_type_value = '2'; // 수기결제 타입
	}
	// ========================================
	// 오프라인 단말기 결제 (일반)
	// ========================================
	else {
		$dv_tid_ori = $mbrNo;

		/* mbr 분리 */
		$arraydata = explode(PHP_EOL, trim($config['cf_3']));
		$arraydata = array_map('trim', $arraydata);

		/* mbr을 tid로 */
		$arraydata2 = explode(PHP_EOL, trim($config['cf_4']));
		$arraydata2 = array_map('trim', $arraydata2);


		if($mbrNo == "114004") {

			$mbrNo = substr($mbrRefNo, 0, 13);
			$dv_tid_ori = "114004";

		} else if(in_array($mbrNo, $arraydata)) {

			$mbrNo = substr($mbrRefNo, 0, -10);

		} else if(in_array($mbrNo, $arraydata2)) {

			$mbrNo = $mbrNo;

		} else {
			$mbrNo = $vanCatId;
		}

		$row2 = sql_fetch("select * from g5_device where dv_tid = '{$mbrNo}'");

		// 디바이스 조회 실패 체크
		if(!$row2['dv_id']) {
			$sync_status = 'failed';
			$sync_message = "device '{$mbrNo}' not found";
		}

		$dv_tid_value = $mbrNo; // 일반결제는 mbrNo 사용
		$pg_name_value = 'stn'; // 일반결제
		$dv_type_value = $row2['dv_type']; // 디바이스 타입 사용
	}

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



	$mb_1_pay = $amount * $row2['mb_1_fee'] /100;
	$mb_2_pay = $amount * $row2['mb_2_fee'] /100;
	$mb_3_pay = $amount * $row2['mb_3_fee'] /100;
	$mb_4_pay = $amount * $row2['mb_4_fee'] /100;
	$mb_5_pay = $amount * $row2['mb_5_fee'] /100;
	$mb_6_pay = $amount * $row2['mb_6_fee'] /100;
	$mb_6_pay = $amount - $mb_6_pay;




	$pay_datetime =  date("Y-m-d H:i:s", strtotime($tranDatetranTime));
//	$calday =  date("Ymd", strtotime($trxDate));


	if($issueCompanyNo == "01") { $issueCompanyNo = "비씨카드";
	} else if($issueCompanyNo == "02") { $issueCompanyNo = "신한카드";
	} else if($issueCompanyNo == "03") { $issueCompanyNo = "삼성카드";
	} else if($issueCompanyNo == "04") { $issueCompanyNo = "현대카드";
	} else if($issueCompanyNo == "05") { $issueCompanyNo = "롯데카드";
	} else if($issueCompanyNo == "06") { $issueCompanyNo = "해외JCB카드";
	} else if($issueCompanyNo == "07") { $issueCompanyNo = "국민카드";
	} else if($issueCompanyNo == "08") { $issueCompanyNo = "하나카드(구외환)";
	} else if($issueCompanyNo == "09") { $issueCompanyNo = "해외카드";
	} else if($issueCompanyNo == "11") { $issueCompanyNo = "수협카드";
	} else if($issueCompanyNo == "12") { $issueCompanyNo = "농협카드";
	} else if($issueCompanyNo == "13") { $issueCompanyNo = "한미카드";
	} else if($issueCompanyNo == "15") { $issueCompanyNo = "씨티카드";
	} else if($issueCompanyNo == "21") { $issueCompanyNo = "신한카드";
	} else if($issueCompanyNo == "22") { $issueCompanyNo = "제주카드";
	} else if($issueCompanyNo == "23") { $issueCompanyNo = "광주카드";
	} else if($issueCompanyNo == "24") { $issueCompanyNo = "전북카드";
	} else if($issueCompanyNo == "26") { $issueCompanyNo = "신협카드";
	} else if($issueCompanyNo == "27") { $issueCompanyNo = "하나카드";
	} else if($issueCompanyNo == "30") { $issueCompanyNo = "신세계카드";
	} else if($issueCompanyNo == "31") { $issueCompanyNo = "우리카드";
	} else if($issueCompanyNo == "32") { $issueCompanyNo = "푸르미카드";
	} else if($issueCompanyNo == "33") { $issueCompanyNo = "꿈자람카드";
	} else if($issueCompanyNo == "34") { $issueCompanyNo = "온누리상품권";
	} else if($issueCompanyNo == "35") { $issueCompanyNo = "코나머니(해피기프트카드)";
	} else if($issueCompanyNo == "36") { $issueCompanyNo = "지드림카드"; }


	$sql_common = " pay_type = '{$pay_type}',
					pay = '{$amount}',
					pay_num = '{$applNo}',
					trxid = '{$refNo}',
					trackId = '{$mbrRefNo}',
					pay_datetime = '{$pay_datetime}',
					pay_cdatetime = '{$pay_cdatetime}',
					pay_parti = '{$installNo}',
					pay_card_name = '{$issueCompanyNo}',
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

					dv_type = '{$dv_type_value}',
					dv_certi = '{$row2['dv_certi']}',
					dv_tid = '{$dv_tid_value}',
					dv_tid_ori = '{$dv_tid_ori}',
					sftp_mbrno = '{$row2['sftp_mbrno']}',
					pg_name = '{$pg_name_value}' ";


//	$pay = sql_fetch("select * from g5_payment where trxid = '{$tid}' and pay_num = '{$appNo}'");

	if(!$pay['pay_id']) {
		if($sync_status != 'failed') {
			$sql = " insert into g5_payment set ".$sql_common.", datetime = '".G5_TIME_YMDHIS."'";
			$insert_result = sql_query($sql);
			if($insert_result) {
				$sync_status = 'success';
				$sync_message = '';
			} else {
				$sync_status = 'failed';
				$sync_message = 'g5_payment insert failed';
			}
		}
	} else {
		$sync_status = 'success';
		$sync_message = 'already exists';
	}

	// g5_payment_stn 동기화 상태 업데이트
	if($stn_insert_id) {
		$sync_message_escaped = sql_escape_string($sync_message);
		sql_query("UPDATE g5_payment_stn SET sync_status = '{$sync_status}', sync_message = '{$sync_message_escaped}' WHERE pg_id = '{$stn_insert_id}'");
	}

	// ========================================
	// 웹훅 발송 (하이브리드: 즉시 1회 시도, 실패시 크론이 재시도)
	// ========================================
	if($sync_status == 'success' && $row2['mb_6']) {
		$webhook_lib = dirname(__FILE__) . '/../../lib/webhook.lib.php';
		if(file_exists($webhook_lib)) {
			@include_once($webhook_lib);
			if(function_exists('webhook_send_notification')) {
				$pg_data = [
					'refNo' => $refNo,
					'mbrRefNo' => $mbrRefNo,
					'applNo' => $applNo,
					'amount' => $amount,
					'tranDate' => $tranDate,
					'tranTime' => $tranTime,
					'cmd' => $cmd,
					'issueCompanyNo' => $issueCompanyNo,
					'cardNo' => $cardNo,
					'installNo' => $installNo,
					'goodsName' => $goodsName,
					'customerName' => $customerName
				];
				$payment_data = [
					'pay_id' => sql_insert_id(),
					'pay_type' => $pay_type
				];
				@webhook_send_notification($row2['mb_6'], ($requestFlag == 'K') ? 'stn_k' : 'stn', $pg_data, $row2, $payment_data);
			}
		}
	}
	// ========================================

	$data = [
		"resultCode" => "0000",
		"message" => "정상",
		"mbrNo" => $mbrNo,
		"mbrRefNo" => $mbrRefNo,
		"refNo" => $refNo
	];
	$jsonString = json_encode($data, JSON_PRETTY_PRINT);
	echo $jsonString;

	// 추가 API 호출
	/*
	$notification_url = 'https://api.wannapayments.kr/api/v1/payment/notification/mainpay';
	
	$notification_data = array(
		'cmd' => $cmd,
		'paymethod' => $paymethod,
		'payType' => $payType,
		'requestFlag' => $requestFlag,
		'mbrRefNo' => $mbrRefNo,
		'mbrNo' => $mbrNo,
		'refNo' => $refNo,
		'tranDate' => $tranDate,
		'tranTime' => $tranTime,
		'orgRefNo' => $orgRefNo,
		'orgTranDate' => $orgTranDate,
		'vanCatId' => $vanCatId,
		'cardMerchNo' => $cardMerchNo,
		'applNo' => $applNo,
		'issueCompanyNo' => $issueCompanyNo,
		'acqCompanyNo' => $acqCompanyNo,
		'cardNo' => $cardNo,
		'installNo' => $installNo,
		'goodsName' => $goodsName,
		'amount' => $amount,
		'customerName' => $customerName,
		'customerTelNo' => $customerTelNo,
		'customerEmail' => $customerEmail,
		'sid' => $sid,
		'retailerCode' => $retailerCode
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
				error_log("secta9ine>>".$notification_result);
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