<?php
	error_reporting(E_ALL);
	ini_set("display_errors", 0);

	include_once('./_common.php');

	// 데모 버전: 실제 PG 취소 차단
	echo "[데모] 데모 버전에서는 실제 취소가 불가합니다.";
	exit;

//	print "<pre>"; print_r($_POST); print "</pre>";
//	exit;

	if($_POST['mb_id'] != $member['mb_id']) {
		echo "잘못된 접근입니다. 결제자 아이디 다름";
	}


	$pay_id = trim($_POST['pay_id']);								// PG 아이디
	$mb_id = trim($_POST['mb_id']);									// 아이디

	// 회원정보
	$mb = get_member($mb_id);

	// 결제정보
	$pay = sql_fetch(" select * from pay_payment where pay_id = '".$pay_id."' ");

	if(!$is_admin) {
		if($pay['mb_id'] != $mb_id) {
			echo "결제 승인자와 승인 취소자가 다를 수 없습니다.";
		}
	}



	if($pay['payments'] == "k1") { // 광원 구인증


		function refund_ko($amount, $rootTrxId, $trackId, $keydata, &$http_status, &$header = null) {

			$url = 'https://svcapi.mtouch.com/api/refund';

			$trxType = "ONTR"; // 
			$udf1 = ""; //
			$udf2 = ""; //


			$post_data = '
			{"refund":{
				"rootTrxId":"'.$rootTrxId.'",
				"rootTrackId":"",
				"rootTrxDay":"",
				"authCd":"",
				"trxId":"",
				"trxType":"'.$trxType.'",
				"trackId":"'.$trackId.'",
				"amount":"'.$amount.'",
				"udf1":"'.$udf1.'",
				"udf2":"'.$udf2.'"
				}
			}';

			$Authorization = $keydata;


			$ch=curl_init();
			// user credencial
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_URL, $url);

			// post_data
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

			if (!is_null($header)) {
				curl_setopt($ch, CURLOPT_HEADER, true);
			}
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cache-Control: no-cache','Authorization: '.$Authorization, 'Content-Type: application/json'));

			curl_setopt($ch, CURLOPT_VERBOSE, true);
			
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			$response = curl_exec($ch);

			$body = null;
			// error
			if (!$response) {
				$body = curl_error($ch);
				// HostNotFound, No route to Host, etc  Network related error
				$http_status = -1;
				alert("통신오류가 발생하였습니다.(E1)\\n관리자에게 문의해주세요.");
				exit;
			} else {
				//parsing http status code
				$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

				if (!is_null($header)) {
					$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

					$header = substr($response, 0, $header_size);
					$body = substr($response, $header_size);
				} else {
					$body = $response;
				}
			}

			curl_close($ch);

			return $body;
		}

		$amount = $pay['pay_price'];
		$rootTrxId = $pay['card_trxid'];
		$trackId = $pay['card_trackId'];
		$keydata = $pay['pg_key2'];

		$ret = refund_ko($amount, $rootTrxId, $trackId, $keydata, $http_status);
		$row = json_decode($ret, true);


/*
	{
		"result": {
			"resultCd": "0000",
			"resultMsg": "정상",
			"advanceMsg": "승인취소",
			"create": "20170520095239"
		},
		"refund": {
			"rootTrxId": "T170520000403",
			"rootTrackId": "trmsg_201705200952_8444-08cbf8-058-23a2e",
			"rootTrxDay": "20170520",
			"authCd": "10095239",
			"trxId": "T170520000404",
			"trxType": "ONTR",
			"tmnId": "1046122895",
			"trackId": "trmsg_201705200952_0b97-6e8e27-753-c7dec",
			"amount": 5000,
			"udf1": "",
			"udf2": ""
		}
	}

*/

		$sql_common = " k1_resultCd = '".$row['result']['resultCd']."',
						k1_resultMsg = '".$row['result']['resultMsg']."',
						k1_advanceMsg = '".$row['result']['advanceMsg']."',
						k1_create = '".$row['result']['create']."',

						k1_rootTrxId = '".$row['refund']['rootTrxId']."',
						k1_rootTrackId = '".$row['refund']['rootTrackId']."',
						k1_rootTrxDay = '".$row['refund']['rootTrxDay']."',

						k1_authCd = '".$row['refund']['authCd']."',
						k1_trxId = '".$row['refund']['trxId']."',
						k1_trxType = '".$row['refund']['trxType']."',
						k1_tmnId = '".$row['refund']['tmnId']."',
						k1_trackId = '".$row['refund']['trackId']."',
						k1_amount = '".$row['refund']['amount']."',
						k1_udf1 = '".$row['pay']['udf1']."',
						k1_udf2 = '".$row['pay']['udf2']."' ";

		$sql = " insert into pay_payment_k1 set ".$sql_common;
		sql_query($sql);

		$card_rootTrxId = $row['refund']['rootTrxId'];
		$card_cdatetime = date("Y-m-d H:i:s", strtotime($row['result']['create']));
		$card_result_code = $row['result']['resultCd'];
		$card_result_msg = $row['result']['resultMsg'];
		$card_result_msg2 = $row['result']['advanceMsg'];
		$updatetime = G5_TIME_YMDHIS;

		if($card_result_code == '0000') {
			$card_yn = "C";
			echo $card_result_code;
		} else {
			$card_yn = "S";
			echo $card_result_msg2;
		}

		$sql = " UPDATE pay_payment
					set card_trxid_ori = '".$card_rootTrxId."',
						card_yn = '".$card_yn."',
						card_cdatetime = '".$card_cdatetime."',
						card_result_code = '".$card_result_code."',
						card_result_msg = '".$card_result_msg."',
						card_result_msg2 = '".$card_result_msg2."',
						updatetime = '".$updatetime."'
					WHERE pay_id = '".$pay_id."' ";
		sql_query($sql);
	}




	if($pay['payments'] == "k1b") { // 광원 구인증


		function refund_ko($amount, $rootTrxId, $trackId, $keydata, &$http_status, &$header = null) {

			$url = 'https://svcapi.mtouch.com/api/refund';

			$trxType = "ONTR"; // 
			$udf1 = ""; //
			$udf2 = ""; //


			$post_data = '
			{"refund":{
				"rootTrxId":"'.$rootTrxId.'",
				"rootTrackId":"",
				"rootTrxDay":"",
				"authCd":"",
				"trxId":"",
				"trxType":"'.$trxType.'",
				"trackId":"'.$trackId.'",
				"amount":"'.$amount.'",
				"udf1":"'.$udf1.'",
				"udf2":"'.$udf2.'"
				}
			}';

			$Authorization = $keydata;


			$ch=curl_init();
			// user credencial
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_URL, $url);

			// post_data
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

			if (!is_null($header)) {
				curl_setopt($ch, CURLOPT_HEADER, true);
			}
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cache-Control: no-cache','Authorization: '.$Authorization, 'Content-Type: application/json'));

			curl_setopt($ch, CURLOPT_VERBOSE, true);
			
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			$response = curl_exec($ch);

			$body = null;
			// error
			if (!$response) {
				$body = curl_error($ch);
				// HostNotFound, No route to Host, etc  Network related error
				$http_status = -1;
				alert("통신오류가 발생하였습니다.(E1)\\n관리자에게 문의해주세요.");
				exit;
			} else {
				//parsing http status code
				$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

				if (!is_null($header)) {
					$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

					$header = substr($response, 0, $header_size);
					$body = substr($response, $header_size);
				} else {
					$body = $response;
				}
			}

			curl_close($ch);

			return $body;
		}

		$amount = $pay['pay_price'];
		$rootTrxId = $pay['card_trxid'];
		$trackId = $pay['card_trackId'];
		$keydata = $pay['pg_key2'];

		$ret = refund_ko($amount, $rootTrxId, $trackId, $keydata, $http_status);
		$row = json_decode($ret, true);


/*
	{
		"result": {
			"resultCd": "0000",
			"resultMsg": "정상",
			"advanceMsg": "승인취소",
			"create": "20170520095239"
		},
		"refund": {
			"rootTrxId": "T170520000403",
			"rootTrackId": "trmsg_201705200952_8444-08cbf8-058-23a2e",
			"rootTrxDay": "20170520",
			"authCd": "10095239",
			"trxId": "T170520000404",
			"trxType": "ONTR",
			"tmnId": "1046122895",
			"trackId": "trmsg_201705200952_0b97-6e8e27-753-c7dec",
			"amount": 5000,
			"udf1": "",
			"udf2": ""
		}
	}

*/

		$sql_common = " k1_resultCd = '".$row['result']['resultCd']."',
						k1_resultMsg = '".$row['result']['resultMsg']."',
						k1_advanceMsg = '".$row['result']['advanceMsg']."',
						k1_create = '".$row['result']['create']."',

						k1_rootTrxId = '".$row['refund']['rootTrxId']."',
						k1_rootTrackId = '".$row['refund']['rootTrackId']."',
						k1_rootTrxDay = '".$row['refund']['rootTrxDay']."',

						k1_authCd = '".$row['refund']['authCd']."',
						k1_trxId = '".$row['refund']['trxId']."',
						k1_trxType = '".$row['refund']['trxType']."',
						k1_tmnId = '".$row['refund']['tmnId']."',
						k1_trackId = '".$row['refund']['trackId']."',
						k1_amount = '".$row['refund']['amount']."',
						k1_udf1 = '".$row['pay']['udf1']."',
						k1_udf2 = '".$row['pay']['udf2']."' ";

		$sql = " insert into pay_payment_k1 set ".$sql_common;
		sql_query($sql);

		$card_rootTrxId = $row['refund']['rootTrxId'];
		$card_cdatetime = date("Y-m-d H:i:s", strtotime($row['result']['create']));
		$card_result_code = $row['result']['resultCd'];
		$card_result_msg = $row['result']['resultMsg'];
		$card_result_msg2 = $row['result']['advanceMsg'];
		$updatetime = G5_TIME_YMDHIS;

		if($card_result_code == '0000') {
			$card_yn = "C";
			echo $card_result_code;
		} else {
			$card_yn = "S";
			echo $card_result_msg2;
		}

		$sql = " UPDATE pay_payment
					set card_trxid_ori = '".$card_rootTrxId."',
						card_yn = '".$card_yn."',
						card_cdatetime = '".$card_cdatetime."',
						card_result_code = '".$card_result_code."',
						card_result_msg = '".$card_result_msg."',
						card_result_msg2 = '".$card_result_msg2."',
						updatetime = '".$updatetime."'
					WHERE pay_id = '".$pay_id."' ";
		sql_query($sql);
	}

















	if($pay['payments'] == "welcom") {


		function current_millis() {
			return (int) (microtime(true) * 1000);
		}

		$pay_type = 'CREDIT_CARD';
		$transaction_type = 'CANCEL';
		$mid = $pay['pg_key1'];
		$user_id = '';
		$transaction_no = $pay['card_trxid'];
		$amount = $pay['pay_price'];
		$cancel_reason = "승인취소";
		$ip_address = $_SERVER['REMOTE_ADDR'];
	
		$millis = current_millis();
		$apikey = $pay['pg_key2'];

		// 검증값 생성
		$hash_value = hash("sha256", $mid.$transaction_type.$transaction_no.$amount.$millis.$apikey);

		$data = array(
			'pay_type' => $pay_type,
			'transaction_type' => $transaction_type,
			'mid' => $mid,
			'user_id' => $user_id,
			'transaction_no'=> $transaction_no,
			'amount'=>$amount,
			'cancel_reason' => $cancel_reason,
			'ip_address' => $ip_address,
			'millis'=>$millis,
			'hash_value'=>$hash_value
		);

		$payload = json_encode($data);

		// Prepare new cURL resource
		$ch = curl_init('https://payapi.welcomepayments.co.kr/api/payment/cancel');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

		// Set HTTP Header for POST request
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($payload))
		);

		// Submit the POST request
		$result = curl_exec($ch);

		// Close cURL session handle
		curl_close($ch);

		$row = json_decode($result, true);

		$card_result_msg2 = $row['result_message'];
		$card_result_code = $row['result_code'];

		$sql_common = " wc_result_code = '".$row['result_code']."',
						wc_result_message = '".$row['result_message']."',
						wc_order_no = '".$row['order_no']."',
						wc_amount = '".$row['amount']."',
						wc_cancel_amount = '".$row['cancel_amount']."',
						wc_cancel_ymdhms = '".$row['cancel_ymdhms']."',
						wc_transaction_no = '".$row['transaction_no']."' ";

		$sql = " insert into pay_payment_welcom set ".$sql_common;
		sql_query($sql);

		if($card_result_code == '0000') {
			$card_yn = "C";
			echo $card_result_code;
		} else {
			$card_yn = "S";
			echo $card_result_msg2;
		}

		$card_cdatetime = date("Y-m-d H:i:s", strtotime($row['cancel_ymdhms']));
		$updatetime = G5_TIME_YMDHIS;
	

		$sql = " UPDATE pay_payment
					set card_trxid_ori = '',
						card_yn = '".$card_yn."',
						card_cdatetime = '".$card_cdatetime."',
						card_result_code = '".$card_result_code."',
						card_result_msg = '".$card_result_msg."',
						card_result_msg2 = '".$card_result_msg2."',
						updatetime = '".$updatetime."'
					WHERE pay_id = '".$pay_id."' ";
		sql_query($sql);

	}






	if($pay['payments'] == "paysis") {

		// 원거래 가져오기
		$paysis = sql_fetch(" select * from pay_payment_paysis where ps_refNo = '".$pay['card_trxid']."' ");

		// 페이시스 결제 필요
		function httpPost($url,$params){
			$postData = '';
			foreach($params as $k => $v) {
				$postData .= $k . '='.urlencode($v).'&';
			}
			$postData = rtrim($postData, '&');
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded;utf-8'));
			curl_setopt($ch, CURLOPT_POST, 9);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			$output=curl_exec($ch); 
			curl_close($ch);
			return $output;
		}

		function makeSignature($mbrNo,$mbrRefNo,$amount,$apiKey,$timestamp) {		
			$message = $mbrNo ."|".$mbrRefNo."|".$amount."|".$apiKey."|".$timestamp;
			return hash("sha256", $message);	
		}


		function makeTimestamp() {
			return date('YmdHis') . generateRandomString();
		}


		function generateRandomString($length = 4) {
			return substr(str_shuffle(str_repeat($x='0123456789', ceil($length/strlen($x)) )),1,$length);
		}

		/*
		function makeMbrRefNo($prefix) {
			return uniqid($prefix);
		}

		function pintLog($msg, $path){
			date_default_timezone_set('Asia/Seoul');
			$datetime = date_create('now')->format('Y-m-d H:i:s.u');
			$msg = "[".$datetime."] ".$msg."\n";
			error_log ($msg, 3, $path);
		}
		*/

		$API_BASE = "https://relay.mainpay.co.kr";	// API URL

		$sid				= $pay['pg_key1'];								// 페이시스 TID
		$apiKey				= $pay['pg_key2'];								// 페이시스 apiKey
		$mbrNo				= $pay['pg_key3'];								// 페이시스 상점ID

		$mbrRefNo			= substr($pay['card_trackId'],0,-10).time();			// 가맹점주문번호 (가맹점에서 생성한 중복되지 않는 번호)
		$orgRefNo			= $pay['card_trxid'];									// 원거래번호 (승인응답 시 보관한 거래번호)
		$orgTranDate		= date("ymd", strtotime($pay['card_sdatetime']));		// 원거래 승인일자 (승인응답 시 보관한 승인일자)
		$payType			= $paysis['ps_refNo'];									// 결제타입 (K)
		$paymethod			= "CARD";												// 지불수단 (CARD 고정값)
		$amount				= $pay['pay_price'];									// 원거래 금액

		$timestamp = makeTimestamp();												// 타임스탬프 (YYYYMMDDHHMI24SS 형식의 문자열)
		$signature = makeSignature($mbrNo, $mbrRefNo, $amount, $apiKey, $timestamp); 	// 결제 위변조 방지를 위한 파라미터 서명 값

		$parameters = array(
			'mbrNo' => $mbrNo,
			'mbrRefNo' => $mbrRefNo,
			'orgRefNo' => $orgRefNo,
			'orgTranDate' => $orgTranDate,
			'payType' => $payType,
			'paymethod' => $paymethod,
			'amount' => $amount,
			'timestamp' => $timestamp,
			'signature' => $signature
		);
		
		$apiUrl = $API_BASE."/v1/api/payments/payment/cancel";
		$result = "";
		$result = httpPost($apiUrl, $parameters);

		$row = json_decode($result, true);

		$sql_common = " ps_resultCode = '".$row['resultCode']."',
						ps_resultMessage = '".$row['resultMessage']."',
						ps_mbrNo = '".$row['data']['mbrNo']."',
						ps_mbrRefNo = '".$row['data']['mbrRefNo']."',
						ps_refNo = '".$row['data']['refNo']."',
						ps_tranDate = '".$row['data']['tranDate']."',
						ps_tranTime = '".$row['data']['tranTime']."' ";

		$sql = " insert into pay_payment_paysis set ".$sql_common;
		sql_query($sql);


		$card_result_code = $row['resultCode']; // 결과코드
		$card_result_msg = $row['resultMessage']; // 결과메세지1
		$card_result_msg2 = $row['resultMessage']; // 결과메세지2
		$tranDatetranTime = "20".$row['data']['tranDate'].$row['data']['tranTime'];
		$card_cdatetime = date("Y-m-d H:i:s", strtotime($tranDatetranTime)); // 승인일시
		$updatetime = G5_TIME_YMDHIS;

		if($card_result_code == '200') {
			$card_result_code = '0000';
		}

		if($card_result_code == '0000') {
			$card_yn = "C";
			echo $card_result_code;
		} else {
			$card_yn = "S";
			echo $card_result_msg2;
		}

		$sql = " UPDATE pay_payment
					set card_yn = '".$card_yn."',
						card_cdatetime = '".$card_cdatetime."',
						card_result_code = '".$card_result_code."',
						card_result_msg = '".$card_result_msg."',
						card_result_msg2 = '".$card_result_msg2."',
						updatetime = '".$updatetime."'
					WHERE pay_id = '".$pay_id."' ";
		sql_query($sql);

	}





	if($pay['payments'] == "stn") {

		// 원거래 가져오기
		$paysis = sql_fetch(" select * from pay_payment_paysis where ps_refNo = '".$pay['card_trxid']."' ");

		// 페이시스 결제 필요
		function httpPost($url,$params){
			$postData = '';
			foreach($params as $k => $v) {
				$postData .= $k . '='.urlencode($v).'&';
			}
			$postData = rtrim($postData, '&');
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded;utf-8'));
			curl_setopt($ch, CURLOPT_POST, 9);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			$output=curl_exec($ch); 
			curl_close($ch);
			return $output;
		}

		function makeSignature($mbrNo,$mbrRefNo,$amount,$apiKey,$timestamp) {		
			$message = $mbrNo ."|".$mbrRefNo."|".$amount."|".$apiKey."|".$timestamp;
			return hash("sha256", $message);	
		}


		function makeTimestamp() {
			return date('YmdHis') . generateRandomString();
		}


		function generateRandomString($length = 4) {
			return substr(str_shuffle(str_repeat($x='0123456789', ceil($length/strlen($x)) )),1,$length);
		}

		/*
		function makeMbrRefNo($prefix) {
			return uniqid($prefix);
		}

		function pintLog($msg, $path){
			date_default_timezone_set('Asia/Seoul');
			$datetime = date_create('now')->format('Y-m-d H:i:s.u');
			$msg = "[".$datetime."] ".$msg."\n";
			error_log ($msg, 3, $path);
		}
		*/

		$API_BASE = "https://relay.mainpay.co.kr";	// API URL

		$mbrNo				= $pay['pg_key1'];								// 페이시스 상점ID
		$apiKey				= $pay['pg_key2'];								// 페이시스 apiKey

		$mbrRefNo			= substr($pay['card_trackId'],0,-10).time();			// 가맹점주문번호 (가맹점에서 생성한 중복되지 않는 번호)
		$orgRefNo			= $pay['card_trxid'];									// 원거래번호 (승인응답 시 보관한 거래번호)
		$orgTranDate		= date("ymd", strtotime($pay['card_sdatetime']));		// 원거래 승인일자 (승인응답 시 보관한 승인일자)
		$payType			= $paysis['ps_refNo'];									// 결제타입 (K)
		$paymethod			= "CARD";												// 지불수단 (CARD 고정값)
		$amount				= $pay['pay_price'];									// 원거래 금액

		$timestamp = makeTimestamp();												// 타임스탬프 (YYYYMMDDHHMI24SS 형식의 문자열)
		$signature = makeSignature($mbrNo, $mbrRefNo, $amount, $apiKey, $timestamp); 	// 결제 위변조 방지를 위한 파라미터 서명 값

		$parameters = array(
			'mbrNo' => $mbrNo,
			'mbrRefNo' => $mbrRefNo,
			'orgRefNo' => $orgRefNo,
			'orgTranDate' => $orgTranDate,
			'payType' => $payType,
			'paymethod' => $paymethod,
			'amount' => $amount,
			'timestamp' => $timestamp,
			'signature' => $signature
		);
		
		$apiUrl = $API_BASE."/v1/api/payments/payment/cancel";
		$result = "";
		$result = httpPost($apiUrl, $parameters);

		$row = json_decode($result, true);

		$sql_common = " stn_resultCode = '".$row['resultCode']."',
						stn_resultMessage = '".$row['resultMessage']."',
						stn_mbrNo = '".$row['data']['mbrNo']."',
						stn_mbrRefNo = '".$row['data']['mbrRefNo']."',
						stn_refNo = '".$row['data']['refNo']."',
						stn_tranDate = '".$row['data']['tranDate']."',
						stn_tranTime = '".$row['data']['tranTime']."' ";

		$sql = " insert into pay_payment_stn set ".$sql_common;
		sql_query($sql);


		$card_result_code = $row['resultCode']; // 결과코드
		$card_result_msg = $row['resultMessage']; // 결과메세지1
		$card_result_msg2 = $row['resultMessage']; // 결과메세지2
		$tranDatetranTime = "20".$row['data']['tranDate'].$row['data']['tranTime'];
		$card_cdatetime = date("Y-m-d H:i:s", strtotime($tranDatetranTime)); // 승인일시
		$updatetime = G5_TIME_YMDHIS;

		if($card_result_code == '200') {
			$card_result_code = '0000';
		}

		if($card_result_code == '0000') {
			$card_yn = "C";
			echo $card_result_code;
		} else {
			$card_yn = "S";
			echo $card_result_msg2;
		}

		$sql = " UPDATE pay_payment
					set card_yn = '".$card_yn."',
						card_cdatetime = '".$card_cdatetime."',
						card_result_code = '".$card_result_code."',
						card_result_msg = '".$card_result_msg."',
						card_result_msg2 = '".$card_result_msg2."',
						updatetime = '".$updatetime."'
					WHERE pay_id = '".$pay_id."' ";
		sql_query($sql);

	}








	if($pay['payments'] == "stnb") {

		// 원거래 가져오기
		$paysis = sql_fetch(" select * from pay_payment_paysis where ps_refNo = '".$pay['card_trxid']."' ");

		// 페이시스 결제 필요
		function httpPost($url,$params){
			$postData = '';
			foreach($params as $k => $v) {
				$postData .= $k . '='.urlencode($v).'&';
			}
			$postData = rtrim($postData, '&');
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded;utf-8'));
			curl_setopt($ch, CURLOPT_POST, 9);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			$output=curl_exec($ch); 
			curl_close($ch);
			return $output;
		}

		function makeSignature($mbrNo,$mbrRefNo,$amount,$apiKey,$timestamp) {		
			$message = $mbrNo ."|".$mbrRefNo."|".$amount."|".$apiKey."|".$timestamp;
			return hash("sha256", $message);	
		}


		function makeTimestamp() {
			return date('YmdHis') . generateRandomString();
		}


		function generateRandomString($length = 4) {
			return substr(str_shuffle(str_repeat($x='0123456789', ceil($length/strlen($x)) )),1,$length);
		}

		/*
		function makeMbrRefNo($prefix) {
			return uniqid($prefix);
		}

		function pintLog($msg, $path){
			date_default_timezone_set('Asia/Seoul');
			$datetime = date_create('now')->format('Y-m-d H:i:s.u');
			$msg = "[".$datetime."] ".$msg."\n";
			error_log ($msg, 3, $path);
		}
		*/

		$API_BASE = "https://relay.mainpay.co.kr";	// API URL

		$mbrNo				= $pay['pg_key1'];								// 페이시스 상점ID
		$apiKey				= $pay['pg_key2'];								// 페이시스 apiKey

		$mbrRefNo			= substr($pay['card_trackId'],0,-10).time();			// 가맹점주문번호 (가맹점에서 생성한 중복되지 않는 번호)
		$orgRefNo			= $pay['card_trxid'];									// 원거래번호 (승인응답 시 보관한 거래번호)
		$orgTranDate		= date("ymd", strtotime($pay['card_sdatetime']));		// 원거래 승인일자 (승인응답 시 보관한 승인일자)
		$payType			= $paysis['ps_refNo'];									// 결제타입 (K)
		$paymethod			= "CARD";												// 지불수단 (CARD 고정값)
		$amount				= $pay['pay_price'];									// 원거래 금액

		$timestamp = makeTimestamp();												// 타임스탬프 (YYYYMMDDHHMI24SS 형식의 문자열)
		$signature = makeSignature($mbrNo, $mbrRefNo, $amount, $apiKey, $timestamp); 	// 결제 위변조 방지를 위한 파라미터 서명 값

		$parameters = array(
			'mbrNo' => $mbrNo,
			'mbrRefNo' => $mbrRefNo,
			'orgRefNo' => $orgRefNo,
			'orgTranDate' => $orgTranDate,
			'payType' => $payType,
			'paymethod' => $paymethod,
			'amount' => $amount,
			'timestamp' => $timestamp,
			'signature' => $signature
		);
		
		$apiUrl = $API_BASE."/v1/api/payments/payment/cancel";
		$result = "";
		$result = httpPost($apiUrl, $parameters);

		$row = json_decode($result, true);

		$sql_common = " stn_resultCode = '".$row['resultCode']."',
						stn_resultMessage = '".$row['resultMessage']."',
						stn_mbrNo = '".$row['data']['mbrNo']."',
						stn_mbrRefNo = '".$row['data']['mbrRefNo']."',
						stn_refNo = '".$row['data']['refNo']."',
						stn_tranDate = '".$row['data']['tranDate']."',
						stn_tranTime = '".$row['data']['tranTime']."' ";

		$sql = " insert into pay_payment_stn set ".$sql_common;
		sql_query($sql);


		$card_result_code = $row['resultCode']; // 결과코드
		$card_result_msg = $row['resultMessage']; // 결과메세지1
		$card_result_msg2 = $row['resultMessage']; // 결과메세지2
		$tranDatetranTime = "20".$row['data']['tranDate'].$row['data']['tranTime'];
		$card_cdatetime = date("Y-m-d H:i:s", strtotime($tranDatetranTime)); // 승인일시
		$updatetime = G5_TIME_YMDHIS;

		if($card_result_code == '200') {
			$card_result_code = '0000';
		}

		if($card_result_code == '0000') {
			$card_yn = "C";
			echo $card_result_code;
		} else {
			$card_yn = "S";
			echo $card_result_msg2;
		}

		$sql = " UPDATE pay_payment
					set card_yn = '".$card_yn."',
						card_cdatetime = '".$card_cdatetime."',
						card_result_code = '".$card_result_code."',
						card_result_msg = '".$card_result_msg."',
						card_result_msg2 = '".$card_result_msg2."',
						updatetime = '".$updatetime."'
					WHERE pay_id = '".$pay_id."' ";
		sql_query($sql);

	}