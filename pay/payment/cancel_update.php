<?php
	include_once('./_common.php');

	if($member['mb_adult'] == 1) {
		alert_close("취소불가 회원입니다.");
	}

	if($_POST['mb_id'] != $member['mb_id']) {
		alert("잘못된 접근입니다.");
	} else {
		$mb_id = $_POST['mb_id'];
	}


	$pm_id = $_POST['pm_id']; // 원거래 아이디


	$row_pay = sql_fetch(" select * from pay_payment_passgo where pm_id = '{$pm_id}' "); // 원거래


	if(date("Ymd") != substr($row_pay['creates'],0,8)) { // 당일결제만 취소가능
		alert_close("승인 당일에만 취소 가능합니다..");
		exit;
	}

	$mba = get_member($mb_id);

	$amount = $row_pay['amount']; // 취소금액
	$rootTrxId = $row_pay['trxId']; // 광원, 다날 원거래 거래번호
	$transaction_no = $row_pay['trxId']; // 웰컴 원거래 거래번호
	$mb_id = $mba['mb_id']; // 아이디
//	$keydata = "pk_af14-15f3b4-7b5-c6486";
	$keydata = $row_pay['keydata']; // 기존 결제한 키로 삭제 시도
	$payments = $row_pay['payments']; // PG사
	$welcommid = $row_pay['tmnId']; // PG사
	$welkeydata = $row_pay['keydata']; // PG사
	


	function refund_ko($amount, $rootTrxId, $mb_id, $keydata, &$http_status, &$header = null) {

		$url = 'https://svcapi.mtouch.com/api/refund';
	 
		// 상품정보
		$trxType = "ONTR"; // 결제번호
		//$trackId = "WP_".date("Ymdhis")."_".rand(111,999); // 주문번호
		$trackId = "WNK".$mb_id.time(); // 중복되면 안됩니다. 영문숫자로 구성 공백 및 특수문자 사용금지
		//$trackId = $mb_id."_".date("Ymdhis").rand(1,9); // 주문번호
		$tmnId = $mb_id; // 터미널ID
		$udf1 = ""; // 결제번호
		$udf2 = ""; // 결제번호


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

	function refund_danal($amount, $rootTrxId, $mb_id, $keydata, &$http_status, &$header = null) {

		$url = 'https://api.winglobalpay.com/api/refund';
	 
		// 상품정보
		$trxType = "ONTR"; // 결제번호
		//$trackId = "WP_".date("Ymdhis")."_".rand(111,999); // 주문번호
		$trackId = $mb_id."_".date("Ymdhis").rand(1,9); // 주문번호
		$tmnId = $mb_id; // 터미널ID
		$udf1 = ""; // 결제번호
		$udf2 = ""; // 결제번호


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
		curl_setopt($ch, CURLOPT_POST, count($postData));
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

	function makeMbrRefNo($prefix) {
		return uniqid($prefix);
	}

	function pintLog($msg, $path){
		date_default_timezone_set('Asia/Seoul');
		$datetime = date_create('now')->format('Y-m-d H:i:s.u');
		$msg = "[".$datetime."] ".$msg."\n";
		error_log ($msg, 3, $path);
	}


	if($payments == "welcom") {


		function current_millis() {
			list($usec, $sec) = explode(" ", microtime());
			return round(((float)$usec + (float)$sec) * 1000);
		}

		$mb_group_no = $member['mb_10'];

		if(!$mb_group_no) {
			echo "그룹지정이 되어있지 않습니다. 관리자에게 문의해주세요";
			exit;
		}

		//$mid = 'wel000695m';
		$mid = $welcommid;
		$pay_type = 'CREDIT_CARD';
		$pay_method = 'CREDIT_OLDAUTH_API'; // 비인증: CREDIT_UNAUTH_API, 구인증: CREDIT_OLDAUTH_API
		$order_no = "WNA".$mb_id.time(); // 중복되면 안됩니다. 영문숫자로 구성 공백 및 특수문자 사용금지
		$millis = current_millis();
		$card_no_noenc = $number; // 승인되지 않는 번호입니다. 실제 승인테스트할 카드번호로 변경해주세요.
		$card_pw_noenc = $authPw; // 카드비밀번호 앞 2자리입니다. 샘플값입니다.
		$card_holder_ymd_noenc = $authDob; // 71년3월8일 인 경우 샘플값입니다.
		//$apikey = "ed0c9a186fd57fc9e7e479b8fdeace63";
		$apikey = $welkeydata;
		//$iv = "f83401592ca45691b68ea5a370f5d37f";
		$iv = $config['cfv_1'.$mb_group_no];
		$cancel_reason = "승인취소";
		$transaction_type = 'CANCEL';
		$user_id = '';
		$ip_address = $_SERVER['REMOTE_ADDR'];

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
		$jsondata = serialize($row);
//		print_r($data_array);exit;

		$sql = " insert into pay_payment_passgo
					set payments = '{$payments}',
						authCd = '{$row_pay['authCd']}',
						resultYN = '1',
						mb_id = '{$mba['mb_id']}',
						mb_name = '{$mba['mb_name']}',
						payerName = '{$row_pay['payerName']}',
						payerEmail = '{$row_pay['payerEmail']}',
						payerTel = '{$row_pay['payerTel']}',
						cardnumber = '{$row_pay['cardnumber']}',
						expiry = '{$row_pay['expiry']}',

						last4 = '{$row_pay['last4']}',
						resultCd = '{$row['result_code']}',
						resultMsg = '{$row['result_message']}',
						advanceMsg = '{$row['result_message']}',
						creates = '{$row['cancel_ymdhms']}',
						cardId = '{$row_pay['cardId']}',
						installment = '{$row_pay['installment']}',
						bin = '{$row_pay['bin']}',
						issuer = '{$row_pay['issuer']}',
						cardType = '{$row_pay['cardType']}',
						acquirer = '{$row_pay['acquirer']}',
						prodId = '{$row_pay['prodId']}',
						productname = '{$row_pay['productname']}',
						qty = '{$row_pay['qty']}',
						price = '{$row_pay['price']}',
						descs = '{$row_pay['descs']}',
						trxId = '{$row['transaction_no']}',
						trxType = '',
						tmnId = '{$row['mid']}',
						trackId = '{$row['order_no']}',
						amount = '{$row['amount']}',
						udf1 = '',
						udf2 = '',
						cardAuth = '{$row_pay['cardAuth']}',
						authPw = '{$row_pay['authPw']}',
						authDob = '{$row_pay['authDob']}',
						userIp='{$_SERVER['REMOTE_ADDR']}',
						userAgent='{$_SERVER['HTTP_USER_AGENT']}',
						jsondata='{$jsondata}',
						datetime = '".G5_TIME_YMDHIS."' ";
		sql_query($sql);

		if($row['result_code'] == "0000") { // 취소완료시
			sql_query(" update pay_payment_passgo set resultYN = '2' where pm_id = '{$row_pay['pm_id']}' ");

			if($row['result_code'] == "0000") {
				alert_close_re("승인취소 완료", G5_URL."/payment/list.php");
			}

		} else {
			alert($row['result_message']);
		}

	} else if($payments == "danal") {

		$ret = refund_danal($amount, $rootTrxId, $mb_id, $keydata, $http_status);
		$row = json_decode($ret, true);
		$jsondata = serialize($row);

	//		print "<pre>"; print_r($row['result']); print "</pre>"; exit;


		if($row['result']['resultCd'] == "0000") { // 취소완료시
			sql_query(" update pay_payment_passgo set resultYN = '2' where pm_id = '{$row_pay['pm_id']}' ");


			$sql = " insert into pay_payment_passgo
						set payments = '{$payments}',
							authCd = '{$row_pay['authCd']}',
							resultYN = '1',
							mb_id = '{$mba['mb_id']}',
							mb_name = '{$mba['mb_name']}',
							payerName = '{$row_pay['payerName']}',
							payerEmail = '{$row_pay['payerEmail']}',
							payerTel = '{$row_pay['payerTel']}',
							cardnumber = '{$row_pay['cardnumber']}',
							expiry = '{$row_pay['expiry']}',

							last4 = '{$row_pay['last4']}',
							resultCd = '{$row['result']['resultCd']}',
							resultMsg = '{$row['result']['resultMsg']}',
							advanceMsg = '{$row['result']['advanceMsg']}',
							creates = '{$row['result']['create']}',
							cardId = '{$row_pay['cardId']}',
							installment = '{$row_pay['installment']}',
							bin = '{$row_pay['bin']}',
							issuer = '{$row_pay['issuer']}',
							cardType = '{$row_pay['cardType']}',
							acquirer = '{$row_pay['acquirer']}',
							prodId = '{$row_pay['prodId']}',
							productname = '{$row_pay['productname']}',
							qty = '{$row_pay['qty']}',
							price = '{$row_pay['price']}',
							descs = '{$row_pay['descs']}',
							trxId = '{$row['refund']['trxId']}',
							trxType = '{$row['refund']['trxType']}',
							tmnId = '{$row['refund']['tmnId']}',
							trackId = '{$row['refund']['trackId']}',
							amount = '{$row['refund']['amount']}',
							udf1 = '{$row['refund']['udf1']}',
							udf2 = '{$row['refund']['udf2']}',
							cardAuth = '{$row_pay['cardAuth']}',
							authPw = '{$row_pay['authPw']}',
							authDob = '{$row_pay['authDob']}',
							userIp='{$_SERVER['REMOTE_ADDR']}',
							userAgent='{$_SERVER['HTTP_USER_AGENT']}',
							datetime = '".G5_TIME_YMDHIS."' ";
			sql_query($sql);


			if($row['result']['resultCd'] == "0000") {
				alert_close_re($row['result']['advanceMsg'], G5_URL."/payment/list.php");
			}

		} else {
			alert("취소에 오류가 발생되었습니다.");
		}

	} else if($payments == "k1") {

		$ret = refund_ko($amount, $rootTrxId, $mb_id, $keydata, $http_status);
		$row = json_decode($ret, true);
		$jsondata = serialize($row);

	//	print "<pre>"; print_r($row['result']); print "</pre>"; exit;


		if($row['result']['resultCd'] == "0000") { // 취소완료시
			sql_query(" update pay_payment_passgo set resultYN = '2' where pm_id = '{$row_pay['pm_id']}' ");


			$sql = " insert into pay_payment_passgo
						set payments = '{$payments}',
							authCd = '{$row_pay['authCd']}',
							resultYN = '1',
							mb_id = '{$mba['mb_id']}',
							mb_name = '{$mba['mb_name']}',
							payerName = '{$row_pay['payerName']}',
							payerEmail = '{$row_pay['payerEmail']}',
							payerTel = '{$row_pay['payerTel']}',
							cardnumber = '{$row_pay['cardnumber']}',
							expiry = '{$row_pay['expiry']}',

							last4 = '{$row_pay['last4']}',
							resultCd = '{$row['result']['resultCd']}',
							resultMsg = '{$row['result']['resultMsg']}',
							advanceMsg = '{$row['result']['advanceMsg']}',
							creates = '{$row['result']['create']}',
							cardId = '{$row_pay['cardId']}',
							installment = '{$row_pay['installment']}',
							bin = '{$row_pay['bin']}',
							issuer = '{$row_pay['issuer']}',
							cardType = '{$row_pay['cardType']}',
							acquirer = '{$row_pay['acquirer']}',
							prodId = '{$row_pay['prodId']}',
							productname = '{$row_pay['productname']}',
							qty = '{$row_pay['qty']}',
							price = '{$row_pay['price']}',
							descs = '{$row_pay['descs']}',
							trxId = '{$row['refund']['trxId']}',
							trxType = '{$row['refund']['trxType']}',
							tmnId = '{$row['refund']['tmnId']}',
							trackId = '{$row['refund']['trackId']}',
							amount = '{$row['refund']['amount']}',
							udf1 = '{$row['refund']['udf1']}',
							udf2 = '{$row['refund']['udf2']}',
							cardAuth = '{$row_pay['cardAuth']}',
							authPw = '{$row_pay['authPw']}',
							authDob = '{$row_pay['authDob']}',
							userIp='{$_SERVER['REMOTE_ADDR']}',
							userAgent='{$_SERVER['HTTP_USER_AGENT']}',
							datetime = '".G5_TIME_YMDHIS."' ";
			sql_query($sql);


			if($row['result']['resultCd'] == "0000") {
				alert_close_re($row['result']['advanceMsg'], G5_URL."/payment/list.php");
			}

		} else {
			alert("취소에 오류가 발생되었습니다.");
		}

	} else if($payments == "k1b") {

		$ret = refund_ko($amount, $rootTrxId, $mb_id, $keydata, $http_status);
		$row = json_decode($ret, true);
		$jsondata = serialize($row);

	//	print "<pre>"; print_r($row['result']); print "</pre>"; exit;


		if($row['result']['resultCd'] == "0000") { // 취소완료시
			sql_query(" update pay_payment_passgo set resultYN = '2' where pm_id = '{$row_pay['pm_id']}' ");


			$sql = " insert into pay_payment_passgo
						set payments = '{$payments}',
							authCd = '{$row_pay['authCd']}',
							resultYN = '1',
							mb_id = '{$mba['mb_id']}',
							mb_name = '{$mba['mb_name']}',
							payerName = '{$row_pay['payerName']}',
							payerEmail = '{$row_pay['payerEmail']}',
							payerTel = '{$row_pay['payerTel']}',
							cardnumber = '{$row_pay['cardnumber']}',
							expiry = '{$row_pay['expiry']}',

							last4 = '{$row_pay['last4']}',
							resultCd = '{$row['result']['resultCd']}',
							resultMsg = '{$row['result']['resultMsg']}',
							advanceMsg = '{$row['result']['advanceMsg']}',
							creates = '{$row['result']['create']}',
							cardId = '{$row_pay['cardId']}',
							installment = '{$row_pay['installment']}',
							bin = '{$row_pay['bin']}',
							issuer = '{$row_pay['issuer']}',
							cardType = '{$row_pay['cardType']}',
							acquirer = '{$row_pay['acquirer']}',
							prodId = '{$row_pay['prodId']}',
							productname = '{$row_pay['productname']}',
							qty = '{$row_pay['qty']}',
							price = '{$row_pay['price']}',
							descs = '{$row_pay['descs']}',
							trxId = '{$row['refund']['trxId']}',
							trxType = '{$row['refund']['trxType']}',
							tmnId = '{$row['refund']['tmnId']}',
							trackId = '{$row['refund']['trackId']}',
							amount = '{$row['refund']['amount']}',
							udf1 = '{$row['refund']['udf1']}',
							udf2 = '{$row['refund']['udf2']}',
							cardAuth = '{$row_pay['cardAuth']}',
							authPw = '{$row_pay['authPw']}',
							authDob = '{$row_pay['authDob']}',
							userIp='{$_SERVER['REMOTE_ADDR']}',
							userAgent='{$_SERVER['HTTP_USER_AGENT']}',
							datetime = '".G5_TIME_YMDHIS."' ";
			sql_query($sql);


			if($row['result']['resultCd'] == "0000") {
				alert_close_re($row['result']['advanceMsg'], G5_URL."/payment/list.php");
			}

		} else {
			alert("취소에 오류가 발생되었습니다.");
		}
	} else if($payments == "paysis") {

		$API_BASE = $config['cf_7_subj'];	//TEST API URL
		$mbrNo = $row_pay['tmnId'];			// 가맹점번호
		$apiKey = $row_pay['keydata'];		// apiKey

		$mbrRefNoo = "WNP".$mb_id.time();		//가맹점주문번호 (가맹점에서 생성한 중복되지 않는 번호)
		$orgRefNo = $row_pay['trxId'];		//원거래번호 (승인응답 시 보관한 거래번호)
		$orgTranDate = substr($row_pay['creates'],2,6);	//원거래 승인일자 (승인응답 시 보관한 승인일자)
		$payType = $row_pay['cardType'];			//결제금액 (공급가+부가세)
		$paymethod = "CARD";					//상품명 (특수문자 사용금지)
		$amount = $row_pay['amount'];			// 원거래 금액
		$timestamp = date("ymdHis");			//가맹점 시스템 시각 (yyMMddHHmmssSSS)

		$mbrRefNo = $mbrRefNoo;														// 가맹점에서 나름대로 정한 중복되지 않는 주문번호
		$timestamp = makeTimestamp();												// 타임스탬프 (YYYYMMDDHHMI24SS 형식의 문자열)
		$signature = makeSignature($mbrNo,$mbrRefNo,$amount,$apiKey,$timestamp); 	// 결제 위변조 방지를 위한 파라미터 서명 값

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
		$jsondata = serialize($row);

		$creates = "20".$row['data']['tranDate'].$row['data']['tranTime'];

		if($row['resultCode'] == '200') {
			$row['resultCode'] = '0000';
		}

		$sql = " insert into pay_payment_passgo
					set payments = '{$payments}',
						authCd = '{$row_pay['authCd']}',
						resultYN = '1',
						mb_id = '{$mba['mb_id']}',
						mb_name = '{$mba['mb_name']}',
						payerName = '{$row_pay['payerName']}',
						payerEmail = '{$row_pay['payerEmail']}',
						payerTel = '{$row_pay['payerTel']}',
						cardnumber = '{$row_pay['cardnumber']}',
						expiry = '{$row_pay['expiry']}',

						last4 = '{$last4}',
						resultCd = '{$row['resultCode']}',
						resultMsg = '{$row['resultMessage']}',
						advanceMsg = '{$row['resultMessage']}',
						creates = '{$creates}',
						cardId = '',
						installment = '{$row['data']['installment']}',
						bin = '{$row['data']['issueCompanyNo']}',
						issuer = '{$row['data']['issueCompanyNo']}',
						cardType = '{$row['data']['payType']}',
						acquirer = '{$row['data']['issueCardName']}',
						prodId = '',
						productname = '{$goodsName}',
						qty = '',
						price = '{$row['data']['amount']}',
						descs = '{$goodsName}',
						trxId = '{$row['data']['refNo']}',
						trxType = '',
						tmnId = '{$row['data']['mbrNo']}',
						trackId = '{$row['data']['mbrRefNo']}',
						amount = '{$row['data']['amount']}',
						userIp='{$_SERVER['REMOTE_ADDR']}',
						userAgent='{$_SERVER['HTTP_USER_AGENT']}',
						jsondata='{$jsondata}',
						datetime = '".G5_TIME_YMDHIS."' ";
		sql_query($sql);


		if($row['resultCode'] == "0000") { // 취소완료시
			sql_query(" update pay_payment_passgo set resultYN = '2' where pm_id = '{$row_pay['pm_id']}' ");
			alert_close_re("승인취소 완료", G5_URL."/payment/list.php");

		} else {
//			alert("취소에 오류가 발생되었습니다.");
		}
	} else if($payments == "stn") {

		$API_BASE = $config['cf_7_subj'];	//TEST API URL
		$mbrNo = $row_pay['tmnId'];			// 가맹점번호
		$apiKey = $row_pay['keydata'];		// apiKey

		$mbrRefNoo = "WNS".$mb_id.time();		//가맹점주문번호 (가맹점에서 생성한 중복되지 않는 번호)
		$orgRefNo = $row_pay['trxId'];		//원거래번호 (승인응답 시 보관한 거래번호)
		$orgTranDate = substr($row_pay['creates'],2,6);	//원거래 승인일자 (승인응답 시 보관한 승인일자)
		$payType = $row_pay['cardType'];			//결제금액 (공급가+부가세)
		$paymethod = "CARD";					//상품명 (특수문자 사용금지)
		$amount = $row_pay['amount'];			// 원거래 금액
		$timestamp = date("ymdHis");			//가맹점 시스템 시각 (yyMMddHHmmssSSS)

		$mbrRefNo = $mbrRefNoo;														// 가맹점에서 나름대로 정한 중복되지 않는 주문번호
		$timestamp = makeTimestamp();												// 타임스탬프 (YYYYMMDDHHMI24SS 형식의 문자열)
		$signature = makeSignature($mbrNo,$mbrRefNo,$amount,$apiKey,$timestamp); 	// 결제 위변조 방지를 위한 파라미터 서명 값

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
		$jsondata = serialize($row);

		$creates = "20".$row['data']['tranDate'].$row['data']['tranTime'];

		if($row['resultCode'] == '200') {
			$row['resultCode'] = '0000';
		}

		$sql = " insert into pay_payment_passgo
					set payments = '{$payments}',
						authCd = '{$row_pay['authCd']}',
						resultYN = '1',
						mb_id = '{$mba['mb_id']}',
						mb_name = '{$mba['mb_name']}',
						payerName = '{$row_pay['payerName']}',
						payerEmail = '{$row_pay['payerEmail']}',
						payerTel = '{$row_pay['payerTel']}',
						cardnumber = '{$row_pay['cardnumber']}',
						expiry = '{$row_pay['expiry']}',

						last4 = '{$last4}',
						resultCd = '{$row['resultCode']}',
						resultMsg = '{$row['resultMessage']}',
						advanceMsg = '{$row['resultMessage']}',
						creates = '{$creates}',
						cardId = '',
						installment = '{$row['data']['installment']}',
						bin = '{$row['data']['issueCompanyNo']}',
						issuer = '{$row['data']['issueCompanyNo']}',
						cardType = '{$row['data']['payType']}',
						acquirer = '{$row['data']['issueCardName']}',
						prodId = '',
						productname = '{$goodsName}',
						qty = '',
						price = '{$row['data']['amount']}',
						descs = '{$goodsName}',
						trxId = '{$row['data']['refNo']}',
						trxType = '',
						tmnId = '{$row['data']['mbrNo']}',
						trackId = '{$row['data']['mbrRefNo']}',
						amount = '{$row['data']['amount']}',
						userIp='{$_SERVER['REMOTE_ADDR']}',
						userAgent='{$_SERVER['HTTP_USER_AGENT']}',
						jsondata='{$jsondata}',
						datetime = '".G5_TIME_YMDHIS."' ";
		sql_query($sql);


		if($row['resultCode'] == "0000") { // 취소완료시
			sql_query(" update pay_payment_passgo set resultYN = '2' where pm_id = '{$row_pay['pm_id']}' ");
			alert_close_re("승인취소 완료", G5_URL."/payment/list.php");

		} else {
//			alert("취소에 오류가 발생되었습니다.");
		}
	} else if($payments == "stnb") {

		$API_BASE = $config['cf_7_subj'];	//TEST API URL
		$mbrNo = $row_pay['tmnId'];			// 가맹점번호
		$apiKey = $row_pay['keydata'];		// apiKey

		$mbrRefNoo = "WNO".$mb_id.time();		//가맹점주문번호 (가맹점에서 생성한 중복되지 않는 번호)
		$orgRefNo = $row_pay['trxId'];		//원거래번호 (승인응답 시 보관한 거래번호)
		$orgTranDate = substr($row_pay['creates'],2,6);	//원거래 승인일자 (승인응답 시 보관한 승인일자)
		$payType = $row_pay['cardType'];			//결제금액 (공급가+부가세)
		$paymethod = "CARD";					//상품명 (특수문자 사용금지)
		$amount = $row_pay['amount'];			// 원거래 금액
		$timestamp = date("ymdHis");			//가맹점 시스템 시각 (yyMMddHHmmssSSS)

		$mbrRefNo = $mbrRefNoo;														// 가맹점에서 나름대로 정한 중복되지 않는 주문번호
		$timestamp = makeTimestamp();												// 타임스탬프 (YYYYMMDDHHMI24SS 형식의 문자열)
		$signature = makeSignature($mbrNo,$mbrRefNo,$amount,$apiKey,$timestamp); 	// 결제 위변조 방지를 위한 파라미터 서명 값

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
		$jsondata = serialize($row);

		$creates = "20".$row['data']['tranDate'].$row['data']['tranTime'];

		if($row['resultCode'] == '200') {
			$row['resultCode'] = '0000';
		}

		$sql = " insert into pay_payment_passgo
					set payments = '{$payments}',
						authCd = '{$row_pay['authCd']}',
						resultYN = '1',
						mb_id = '{$mba['mb_id']}',
						mb_name = '{$mba['mb_name']}',
						payerName = '{$row_pay['payerName']}',
						payerEmail = '{$row_pay['payerEmail']}',
						payerTel = '{$row_pay['payerTel']}',
						cardnumber = '{$row_pay['cardnumber']}',
						expiry = '{$row_pay['expiry']}',

						last4 = '{$last4}',
						resultCd = '{$row['resultCode']}',
						resultMsg = '{$row['resultMessage']}',
						advanceMsg = '{$row['resultMessage']}',
						creates = '{$creates}',
						cardId = '',
						installment = '{$row['data']['installment']}',
						bin = '{$row['data']['issueCompanyNo']}',
						issuer = '{$row['data']['issueCompanyNo']}',
						cardType = '{$row['data']['payType']}',
						acquirer = '{$row['data']['issueCardName']}',
						prodId = '',
						productname = '{$goodsName}',
						qty = '',
						price = '{$row['data']['amount']}',
						descs = '{$goodsName}',
						trxId = '{$row['data']['refNo']}',
						trxType = '',
						tmnId = '{$row['data']['mbrNo']}',
						trackId = '{$row['data']['mbrRefNo']}',
						amount = '{$row['data']['amount']}',
						userIp='{$_SERVER['REMOTE_ADDR']}',
						userAgent='{$_SERVER['HTTP_USER_AGENT']}',
						jsondata='{$jsondata}',
						datetime = '".G5_TIME_YMDHIS."' ";
		sql_query($sql);


		if($row['resultCode'] == "0000") { // 취소완료시
			sql_query(" update pay_payment_passgo set resultYN = '2' where pm_id = '{$row_pay['pm_id']}' ");
			alert_close_re("승인취소 완료", G5_URL."/payment/list.php");

		} else {
//			alert("취소에 오류가 발생되었습니다.");
		}
	}