<?php
	include_once('./_common.php');


	if($_POST['mb_id'] != $member['mb_id']) {
		echo "잘못된 접근입니다. 결제 아이디 다름";
	} else {
		$mb_id = $_POST['mb_id'];
	}

	$mba = get_member($mb_id);

	$pd_price = preg_replace("/[^0-9]/","",$_POST['pd_price']); // 금액

	// 고객정보
	$payments = $_POST['payments']; // PG사
	$payerName = $_POST['payerName']; // 고객명
	$payerEmail = $_POST['payerEmail']; // 고객 이메일
	$payerTel1 = $_POST['payerTel1']; // 고객 휴대전화번호
	$payerTel2 = $_POST['payerTel2']; // 고객 휴대전화번호
	$payerTel3 = $_POST['payerTel3']; // 고객 휴대전화번호
	$payerTel = $payerTel1."-".$payerTel2."-".$payerTel3; // 고객 휴대전화번호


	$cardAuth = $_POST['cardAuth']; // 인증유무
	$authPw = $_POST['authPw']; // 비번
	$authDob = $_POST['authDob']; // 생년월일

	if(strlen($authDob) == 6) {
		$authType = 0;
	} else {
		$authType = 1;
	}

	// 카드정보
	$number = preg_replace("/[^0-9]/","",$_POST['number']); // 카드번호
	$expiry = $_POST['expiry1'].$_POST['expiry2']; // YYMM 2103 21년 03월
	$installment = $_POST['installment']; // 할구개월수 0 = 일시불, 12 = 12개월

	// 상품정보
	$pd_name = $_POST['pd_name']; // 상품명

	$card_num = $number; // 카드번호 암호화


	// 중복결제
	if($config['cf_10'] == 'N') {
		$row_card = sql_fetch(" select count(card_num) as card_num from pay_payment_passgo where resultYN = '0' and authCd != '' and card_num = '{$card_num}' and date_format(datetime,'%Y-%m-%d') = date_format(NOW(),'%Y-%m-%d')");
		if($row_card['card_num'] > 0) {
			echo "중복결제 불가";
			exit;
		}
	}


	// 한도
	if($payments == "k1") {
		if($pd_price > $config['cf_1_subj']) {
			echo "해당결제는 ".$config['cf_1_subj']."원 이하만 가능합니다.";
			exit;
		}
	} else if($payments == "danal") {
		if($pd_price > $config['cf_2_subj']) {
			echo "해당결제는 ".$config['cf_2_subj']."원 이하만 가능합니다.";
			exit;
		}
	} else if($payments == "welcom") {
		if($pd_price > $config['cf_3_subj']) {
			echo "해당결제는 ".$config['cf_3_subj']."원 이하만 가능합니다.";
			exit;
		}
	}

	// 키
	if($payments == "k1") { // 광원 key
		if($mba['mb_3']) {
			$keydata = $mba['mb_3'];
		} else {
			echo "KEY가 없습니다.";
			exit;
		}
	} else if($payments == "k1b") { // 다날 key
		if($mba['mb_17']) {
			$keydata = $mba['mb_17'];
		} else {
			echo "KEY가 없습니다.";
			exit;
		}
	} else if($payments == "danal") { // 다날 key
		if($mba['mb_5']) {
			$keydata = $mba['mb_5'];
		} else {
			echo "KEY가 없습니다.";
			exit;
		}
	}







	// 광원 결제
	function pay_ko($payerName, $payerEmail, $payerTel, $number, $expiry, $cardAuth, $authPw, $authDob, $installment, $pd_name, $pd_price, $mb_id, $keydata, &$http_status, &$header = null) {

		$url = 'https://svcapi.mtouch.com/api/pay';

		$mba = get_member($mb_id);

		// 상품정보
		$prodId = ""; // 
		$trxType = "ONTR"; // 결제번호

//		$tmnId = $mba['mb_2']; // 터미널ID

		if($cardAuth == "true") {
			$tmnId = $mba['mb_2']; // 터미널ID
			$trackId = "WNK".$mb_id.time(); // 중복되면 안됩니다. 영문숫자로 구성 공백 및 특수문자 사용금지
		} else {
			$tmnId = $mba['mb_16']; // 터미널ID
			$trackId = "WNB".$mb_id.time(); // 중복되면 안됩니다. 영문숫자로 구성 공백 및 특수문자 사용금지
		}

		//$trackId = "WP_".date("Ymdhis")."_".rand(111,999); // 주문번호
//		$trackId = date("Ymdhis").rand(1,9); // 주문번호
		
		$udf1 = ""; // 결제번호
		$udf2 = ""; // 결제번호

		$post_data = '
		{
			"pay":{
				"payerName":"'.$payerName.'",
				"payerEmail":"'.$payerEmail.'",
				"payerTel":"'.$payerTel.'",
				"card":{
					"number":"'.$number.'",
					"expiry":"'.$expiry.'",
					"cvv":"",
					"installment":"'.$installment.'"
				},
				"product": {
					"prodId": "",
					"name": "'.$pd_name.'",
					"qty": 1,
					"price":"'.$pd_price.'",
					"desc": "'.$pd_name.'"
				},
				"trxId":"",
				"trxType":"ONTR",
				"tmnId":"'.$tmnId.'",
				"trackId":"'.$trackId.'",
				"amount":"'.$pd_price.'",
				"udf1":"'.$udf1.'",
				"udf2":"'.$udf2.'"
				,"metadata":{
					"cardAuth":"'.$cardAuth.'",
					"authPw":"'.$authPw.'",
					"authDob":"'.$authDob.'"
				}
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
			echo "통신오류가 발생하였습니다.(E1)\\n관리자에게 문의해주세요.";
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

	// 다날 결제
	function pay_danal($payerName, $payerEmail, $payerTel, $number, $expiry, $cardAuth, $authPw, $authDob, $installment, $pd_name, $pd_price, $mb_id, $keydata, &$http_status, &$header = null) {

		$url = 'https://api.winglobalpay.com/api/pay';

		// 상품정보
		$prodId = ""; // 
		$trxType = "ONTR"; // 결제번호

		$mba = get_member($mb_id);
		$tmnId = $mba['mb_4']; // 터미널ID

		//$trackId = "WP_".date("Ymdhis")."_".rand(111,999); // 주문번호
		$trackId = date("Ymdhis").rand(1,9); // 주문번호
		$udf1 = ""; // 결제번호
		$udf2 = ""; // 결제번호

		$post_data = '
		{
			"pay":{
				"payerName":"'.$payerName.'",
				"payerEmail":"'.$payerEmail.'",
				"payerTel":"'.$payerTel.'",
				"card":{
					"number":"'.$number.'",
					"expiry":"'.$expiry.'",
					"cvv":"",
					"installment":"'.$installment.'"
				},
				"products":[{
					"prodId":"",
					"name":"'.$pd_name.'",
					"qty":"1",
					"price":"'.$pd_price.'",
					"desc": "'.$pd_name.'"
				}],
				"trxId":"",
				"trxType":"ONTR",
				"tmnId":"'.$tmnId.'",
				"trackId":"'.$trackId.'",
				"amount":"'.$pd_price.'",
				"udf1":"'.$udf1.'",
				"udf2":"'.$udf2.'"
				,"metadata":{
					"cardAuth":"'.$cardAuth.'",
					"authPw":"'.$authPw.'",
					"authDob":"'.$authDob.'"
				}
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
			echo "통신오류가 발생하였습니다.(E1)\\n관리자에게 문의해주세요.";
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





	if($payments == "welcom") { // 웰컴

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
		$mid = $config['cf_1'.$mb_group_no.'_subj'];
		$pay_type = 'CREDIT_CARD';
		$pay_method = 'CREDIT_OLDAUTH_API'; // 비인증: CREDIT_UNAUTH_API, 구인증: CREDIT_OLDAUTH_API
		$order_no = "WNA".$mb_id.time(); // 중복되면 안됩니다. 영문숫자로 구성 공백 및 특수문자 사용금지
		$amount = $pd_price;
		$millis = current_millis();
		$card_no_noenc = $number; // 승인되지 않는 번호입니다. 실제 승인테스트할 카드번호로 변경해주세요.
		$card_pw_noenc = $authPw; // 카드비밀번호 앞 2자리입니다. 샘플값입니다.
		$card_holder_ymd_noenc = $authDob; // 71년3월8일 인 경우 샘플값입니다.
		//$apikey = "ed0c9a186fd57fc9e7e479b8fdeace63";
		$apikey = $config['cf_1'.$mb_group_no];
		//$iv = "f83401592ca45691b68ea5a370f5d37f";
		$iv = $config['cfv_1'.$mb_group_no];


		// 카드번호 암호화
		$card_no = bin2hex(openssl_encrypt($card_no_noenc, 'AES-128-CBC', hex2bin($apikey), OPENSSL_RAW_DATA,hex2bin($iv)));

		// 카드비밀번호 앞2자리 암호화
		$card_pw = bin2hex(openssl_encrypt($card_pw_noenc, 'AES-128-CBC', hex2bin($apikey), OPENSSL_RAW_DATA,hex2bin($iv)));

		// 카드소유자 생년월일 암호화
		$card_holder_ymd = bin2hex(openssl_encrypt($card_holder_ymd_noenc, 'AES-128-CBC', hex2bin($apikey), OPENSSL_RAW_DATA,hex2bin($iv)));


		// 검증값 생성
		$hash_value = hash("sha256", $mid.$pay_type.$pay_method.$order_no.$amount.$millis.$apikey);

		$card_expiry_ym = $expiry; // 2021년 07월
		$user_name = $payerName;
		$prodPrice = $pd_price;
		$prodName = $pd_name;
		$card_sell_mm = sprintf('%02d', $installment); // 일시불 00, 2개월 02, 12개월 12
		$data = array(
			'mid' => $mid,
			'pay_type' => $pay_type,
			'pay_method' => $pay_method,
			'card_no' => $card_no,
			'card_pw' => $card_pw,
			'card_holder_ymd' => $card_holder_ymd,
			'card_expiry_ym' => $card_expiry_ym,
			'order_no'=> $order_no,
			'user_name'=>$user_name,
			'amount'=>$prodPrice,
			'product_name'=>$prodName,
			'card_sell_mm'=>$card_sell_mm,
			'millis'=>$millis,
			'hash_value'=>$hash_value
		);

		$payload = json_encode($data);

		// Prepare new cURL resource
		$ch = curl_init('https://payapi.welcomepayments.co.kr/api/payment/approval');
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

		$last4 = substr($card_num, -4);
		$sql = " insert into pay_payment_passgo
					set payments = '{$payments}',
						authCd = '{$row['approval_no']}',

						mb_id = '{$mb_id}',
						mb_name = '{$mba['mb_nick']}',
						keydata = '{$apikey}',
						card_num = '{$card_num}',
						payerName = '{$payerName}',
						payerEmail = '{$payerEmail}',
						payerTel = '{$payerTel}',
						cardnumber = '{$number}',

						expiry = '{$expiry}',
						last4 = '{$last4}',
						resultCd = '{$row['result_code']}',
						resultMsg = '{$row['result_message']}',
						advanceMsg = '{$row['echo']}',
						creates = '{$row['approval_ymdhms']}',
						cardId = '',
						installment = '{$row['card_sell_mm']}',
						bin = '{$row['card_code']}',
						issuer = '{$row['card_code']}',
						cardType = '',
						acquirer = '{$row['card_name']}',
						prodId = '',
						productname = '{$row['product_name']}',
						qty = '',
						price = '{$row['amount']}',
						descs = '{$row['product_name']}',
						trxId = '{$row['transaction_no']}',
						trxType = '',
						tmnId = '{$row['mid']}',
						trackId = '{$row['order_no']}',
						amount = '{$row['amount']}',
						udf1 = '',
						udf2 = '',
						cardAuth = '{$cardAuth}',
						authPw = '{$authPw}',
						authDob = '{$authDob}',
						userIp='{$_SERVER['REMOTE_ADDR']}',
						userAgent='{$_SERVER['HTTP_USER_AGENT']}',
						jsondata='{$jsondata}',
						datetime = '".G5_TIME_YMDHIS."' ";
		sql_query($sql);

		if($row['result_code'] == '0000') {
			echo $row['result_code'];
		} else {
			echo $row['result_message'];
		}

	} else if($payments == "danal") { // 다날

		$ret = pay_danal($payerName, $payerEmail, $payerTel, $number, $expiry, $cardAuth, $authPw, $authDob, $installment, $pd_name, $pd_price, $mb_id, $keydata, $http_status);
		$row = json_decode($ret, true);
		$jsondata = serialize($row);

		$sql = " insert into pay_payment_passgo
					set payments = '{$payments}',
						authCd = '{$row['pay']['authCd']}',

						mb_id = '{$mb_id}',
						mb_name = '{$mba['mb_nick']}',
						keydata = '{$keydata}',
						card_num = '{$card_num}',
						payerName = '{$payerName}',
						payerEmail = '{$payerEmail}',
						payerTel = '{$payerTel}',
						cardnumber = '{$cardnumber}',
						expiry = '{$expiry}',

						last4 = '{$row['pay']['card']['last4']}',
						resultCd = '{$row['result']['resultCd']}',
						resultMsg = '{$row['result']['resultMsg']}',
						advanceMsg = '{$row['result']['advanceMsg']}',
						creates = '{$row['result']['create']}',
						cardId = '{$row['pay']['card']['cardId']}',
						installment = '{$row['pay']['card']['installment']}',
						bin = '{$row['pay']['card']['bin']}',
						issuer = '{$row['pay']['card']['issuer']}',
						cardType = '{$row['pay']['card']['cardType']}',
						acquirer = '{$row['pay']['card']['acquirer']}',
						prodId = '{$row['pay']['products'][0]['prodId']}',
						productname = '{$row['pay']['products'][0]['name']}',
						qty = '{$row['pay']['products'][0]['qty']}',
						price = '{$row['pay']['products'][0]['price']}',
						descs = '{$row['pay']['products'][0]['desc']}',
						trxId = '{$row['pay']['trxId']}',
						trxType = '{$row['pay']['trxType']}',
						tmnId = '{$row['pay']['tmnId']}',
						trackId = '{$row['pay']['trackId']}',
						amount = '{$row['pay']['amount']}',
						udf1 = '{$row['pay']['udf1']}',
						udf2 = '{$row['pay']['udf2']}',
						cardAuth = '{$cardAuth}',
						authPw = '{$authPw}',
						authDob = '{$authDob}',
						userIp='{$_SERVER['REMOTE_ADDR']}',
						userAgent='{$_SERVER['HTTP_USER_AGENT']}',
						jsondata='{$jsondata}',
						datetime = '".G5_TIME_YMDHIS."' ";
		sql_query($sql);

		if($row['result']['resultCd'] == '0000') {
			echo $row['result']['resultCd'];
		} else {
			echo $row['result']['advanceMsg'];
		}

	} else if($payments == "k1") { // 광원

		$ret = pay_ko($payerName, $payerEmail, $payerTel, $number, $expiry, $cardAuth, $authPw, $authDob, $installment, $pd_name, $pd_price, $mb_id, $keydata, $http_status);
		$row = json_decode($ret, true);
		$jsondata = serialize($row);

		$sql = " insert into pay_payment_passgo
					set payments = '{$payments}',
						authCd = '{$row['pay']['authCd']}',

						mb_id = '{$mb_id}',
						mb_name = '{$mba['mb_nick']}',
						keydata = '{$keydata}',
						card_num = '{$card_num}',
						payerName = '{$payerName}',
						payerEmail = '{$payerEmail}',
						payerTel = '{$payerTel}',
						cardnumber = '{$cardnumber}',
						expiry = '{$expiry}',

						last4 = '{$row['pay']['card']['last4']}',
						resultCd = '{$row['result']['resultCd']}',
						resultMsg = '{$row['result']['resultMsg']}',
						advanceMsg = '{$row['result']['advanceMsg']}',
						creates = '{$row['result']['create']}',
						cardId = '{$row['pay']['card']['cardId']}',
						installment = '{$row['pay']['card']['installment']}',
						bin = '{$row['pay']['card']['bin']}',
						issuer = '{$row['pay']['card']['issuer']}',
						cardType = '{$row['pay']['card']['cardType']}',
						acquirer = '{$row['pay']['card']['acquirer']}',
						prodId = '{$row['pay']['products'][0]['prodId']}',
						productname = '{$row['pay']['products'][0]['name']}',
						qty = '{$row['pay']['products'][0]['qty']}',
						price = '{$row['pay']['products'][0]['price']}',
						descs = '{$row['pay']['products'][0]['desc']}',
						trxId = '{$row['pay']['trxId']}',
						trxType = '{$row['pay']['trxType']}',
						tmnId = '{$row['pay']['tmnId']}',
						trackId = '{$row['pay']['trackId']}',
						amount = '{$row['pay']['amount']}',
						udf1 = '{$row['pay']['udf1']}',
						udf2 = '{$row['pay']['udf2']}',
						cardAuth = '{$cardAuth}',
						authPw = '{$authPw}',
						authDob = '{$authDob}',
						userIp='{$_SERVER['REMOTE_ADDR']}',
						userAgent='{$_SERVER['HTTP_USER_AGENT']}',
						jsondata='{$jsondata}',
						datetime = '".G5_TIME_YMDHIS."' ";
		sql_query($sql);

		if($row['result']['resultCd'] == '0000') {
			echo $row['result']['resultCd'];
		} else {
			echo $row['result']['advanceMsg'];
		}
	} else if($payments == "k1b") { // 광원

		$ret = pay_ko($payerName, $payerEmail, $payerTel, $number, $expiry, $cardAuth, $authPw, $authDob, $installment, $pd_name, $pd_price, $mb_id, $keydata, $http_status);
		$row = json_decode($ret, true);
		$jsondata = serialize($row);

		$sql = " insert into pay_payment_passgo
					set payments = '{$payments}',
						authCd = '{$row['pay']['authCd']}',

						mb_id = '{$mb_id}',
						mb_name = '{$mba['mb_nick']}',
						keydata = '{$keydata}',
						card_num = '{$card_num}',
						payerName = '{$payerName}',
						payerEmail = '{$payerEmail}',
						payerTel = '{$payerTel}',
						cardnumber = '{$cardnumber}',
						expiry = '{$expiry}',

						last4 = '{$row['pay']['card']['last4']}',
						resultCd = '{$row['result']['resultCd']}',
						resultMsg = '{$row['result']['resultMsg']}',
						advanceMsg = '{$row['result']['advanceMsg']}',
						creates = '{$row['result']['create']}',
						cardId = '{$row['pay']['card']['cardId']}',
						installment = '{$row['pay']['card']['installment']}',
						bin = '{$row['pay']['card']['bin']}',
						issuer = '{$row['pay']['card']['issuer']}',
						cardType = '{$row['pay']['card']['cardType']}',
						acquirer = '{$row['pay']['card']['acquirer']}',
						prodId = '{$row['pay']['products'][0]['prodId']}',
						productname = '{$row['pay']['products'][0]['name']}',
						qty = '{$row['pay']['products'][0]['qty']}',
						price = '{$row['pay']['products'][0]['price']}',
						descs = '{$row['pay']['products'][0]['desc']}',
						trxId = '{$row['pay']['trxId']}',
						trxType = '{$row['pay']['trxType']}',
						tmnId = '{$row['pay']['tmnId']}',
						trackId = '{$row['pay']['trackId']}',
						amount = '{$row['pay']['amount']}',
						udf1 = '{$row['pay']['udf1']}',
						udf2 = '{$row['pay']['udf2']}',
						cardAuth = '{$cardAuth}',
						authPw = '{$authPw}',
						authDob = '{$authDob}',
						userIp='{$_SERVER['REMOTE_ADDR']}',
						userAgent='{$_SERVER['HTTP_USER_AGENT']}',
						jsondata='{$jsondata}',
						datetime = '".G5_TIME_YMDHIS."' ";
		sql_query($sql);

		if($row['result']['resultCd'] == '0000') {
			echo $row['result']['resultCd'];
		} else {
			echo $row['result']['advanceMsg'];
		}
	} else if($payments == "paysis") { // 페이시스


		$API_BASE = $config['cf_7_subj'];	// API URL

		$sid = "wann00011m";
		$mbrNo = $config['cf_7'];			// 가맹점번호
		$apiKey = $config['cfv_7'];			// apiKey

		$sid = $mba['mb_11'];			// 페이시스 TID
		$mbrNo = $mba['mb_12'];			// 페이시스 상점ID
		$apiKey = $mba['mb_13'];		// 페이시스 apiKey

		/*
		mbrNo	섹타나인에서 부여한 가맹점 번호 (상점 아이디)
		mbrRefNo	가맹점주문번호 (가맹점에서 생성한 중복되지 않는 번호)
		paymethod	지불수단 (CARD 고정값)
		cardNo	카드번호
		expd	카드유효기간 (YYMM) (주의) 년/월 순서에 유의
		amount	결제금액 (공급가+부가세)
		installment	할부개월 (0 ~ 24)
		goodsName	상품명 (특수문자 사용금지)
		timestamp	가맹점 시스템 시각 (yyMMddHHmmssSSS)
		signature	결제 위변조 방지를 위한 파라미터 서명 값
		keyinAuthType	키인인가구분 (K: 비인증 | O: 구인증) ※ 경우 따라 카드사 특약 필요 비인증, 구인증 심사 여부를 영업사원에게 문의
		authType	구인증용 인증타입 (0: 생년월일 | 1: 사업자번호) (주의) 구인증 사용 시 필수값
		regNo	구인증용 아이디 (생년월일(YYMMDD) | 사업자번호) (주의) 구인증 사용 시 필수값
		passwd	구인증용 카드 비밀번호 앞2자리  (주의) 구인증 사용 시 필수값
		customerName	구매자명
		customerTelNo	구매자연락처
		customerEmail	구매자이메일
		*/

		//$mbrNo = $ㅁㅁㅁㅁ;					//섹타나인에서 부여한 가맹점 번호 (상점 아이디)
		$mbrRefNoo = "WNP".$mb_id.time();	//가맹점주문번호 (가맹점에서 생성한 중복되지 않는 번호)
		$paymethod = "CARD";				//지불수단 (CARD 고정값)
		$cardNo = $number;					//카드번호
		$expd = $expiry;					//카드유효기간 (YYMM) (주의) 년/월 순서에 유의
		$amount = $pd_price;				//결제금액 (공급가+부가세)
		//$installment = $installment;		//할부개월 (0 ~ 24)
		$goodsName = $pd_name;				//상품명 (특수문자 사용금지)
		$timestamp = date("ymdHis");		//가맹점 시스템 시각 (yyMMddHHmmssSSS)
		//$signature = $ㅁㅁㅁㅁ;				//결제 위변조 방지를 위한 파라미터 서명 값
		$keyinAuthType = "O";				//키인인가구분 (K: 비인증 | O: 구인증) ※ 경우 따라 카드사 특약 필요 비인증, 구인증 심사 여부를 영업사원에게 문의
		//$authType = $ㅁㅁㅁㅁ;				//구인증용 인증타입 (0: 생년월일 | 1: 사업자번호) (주의) 구인증 사용 시 필수값
		$regNo = $authDob;					//구인증용 아이디 (생년월일(YYMMDD) | 사업자번호) (주의) 구인증 사용 시 필수값
		$passwd = $authPw;					//구인증용 카드 비밀번호 앞2자리  (주의) 구인증 사용 시 필수값
		$customerName = $payerName;			//구매자명
		$customerTelNo = $payerTel;			//구매자연락처
		$customerEmail = $payerEmail;		//구매자이메일

		$mbrRefNo = $mbrRefNoo;														// 가맹점에서 나름대로 정한 중복되지 않는 주문번호
		$timestamp = makeTimestamp();												// 타임스탬프 (YYYYMMDDHHMI24SS 형식의 문자열)
		$signature = makeSignature($mbrNo,$mbrRefNo,$amount,$apiKey,$timestamp); 	// 결제 위변조 방지를 위한 파라미터 서명 값

		if($sid) {
			$parameters = array(
				'sid' => $sid,
				'mbrNo' => $mbrNo,
				'mbrRefNo' => $mbrRefNo,
				'paymethod' => $paymethod,
				'cardNo' => $cardNo,
				'expd' => $expd,
				'amount' => $amount,
				'installment' => $installment,
				'goodsName' => $goodsName,
				'timestamp' => $timestamp,
				'signature' => $signature,
				'keyinAuthType' => $keyinAuthType,
				'authType' => $authType,
				'regNo' => $regNo,
				'passwd' => $passwd,
				'customerName' => $customerName,
				'customerEmail' => $customerEmail
			);
		} else {
			$parameters = array(
				'mbrNo' => $mbrNo,
				'mbrRefNo' => $mbrRefNo,
				'paymethod' => $paymethod,
				'cardNo' => $cardNo,
				'expd' => $expd,
				'amount' => $amount,
				'installment' => $installment,
				'goodsName' => $goodsName,
				'timestamp' => $timestamp,
				'signature' => $signature,
				'keyinAuthType' => $keyinAuthType,
				'authType' => $authType,
				'regNo' => $regNo,
				'passwd' => $passwd,
				'customerName' => $customerName,
				'customerEmail' => $customerEmail
			);
		}
		
		/*=================================================================================================
		   API 호출
		  =================================================================================================*/
		$apiUrl = $API_BASE."/v1/api/payments/payment/card-keyin/trans";
		$result = "";
		$result = httpPost($apiUrl, $parameters);

		/*
		$row['resultCode']					응답코드 '200' 이면 성공, 이외는 거절
		$row['resultMessage']				응답메시지
		data	---------------- 2 Depth -------------------
		$row['data']['mbrNo']				가맹점번호
		$row['data']['mbrRefNo']			가맹점주문번호
		$row['data']['refNo']				거래번호 (거래 취소시 필요)
		$row['data']['tranDate']			거래일자 (거래 취소시 필요)
		$row['data']['payType']				결제타입 (거래 취소시 필요)
		$row['data']['tranTime']			거래시각
		$row['data']['amount']				결제금액
		$row['data']['applNo']				승인번호
		$row['data']['issueCompanyNo']		카드 발급사 코드(공통코드 참조)
		$row['data']['issueCompanyName']	카드발급사명
		$row['data']['issueCardName']		발급카드명
		$row['data']['acqCompanyNo']		카드 매입사 코드 (공통코드 참조)
		$row['data']['acqCompanyName']		카드매입사명
		*/

		$row = json_decode($result, true);
		$jsondata = serialize($row);
		$last4 = substr($card_num, -4);
		$creates = "20".$row['data']['tranDate'].$row['data']['tranTime'];

		if($row['resultCode'] == '200') {
			$row['resultCode'] = '0000';
		}


		$sql = " insert into pay_payment_passgo
					set payments = '{$payments}',
						authCd = '{$row['data']['applNo']}',

						mb_id = '{$mb_id}',
						mb_name = '{$mba['mb_nick']}',
						keydata = '{$apiKey}',
						card_num = '{$card_num}',
						payerName = '{$payerName}',
						payerEmail = '{$payerEmail}',
						payerTel = '{$payerTel}',
						cardnumber = '{$number}',

						expiry = '{$expiry}',
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

		if($row['resultCode'] == '0000') {
			echo $row['resultCode'];
		} else {
			echo $row['resultMessage'];
		}
	} else if($payments == "stn") { // 섹타나인


		$API_BASE = $config['cf_7_subj'];	// API URL

		$mbrNo = $mba['mb_18'];			// 페이시스 상점ID
		$apiKey = $mba['mb_19'];		// 페이시스 apiKey

		/*
		mbrNo	섹타나인에서 부여한 가맹점 번호 (상점 아이디)
		mbrRefNo	가맹점주문번호 (가맹점에서 생성한 중복되지 않는 번호)
		paymethod	지불수단 (CARD 고정값)
		cardNo	카드번호
		expd	카드유효기간 (YYMM) (주의) 년/월 순서에 유의
		amount	결제금액 (공급가+부가세)
		installment	할부개월 (0 ~ 24)
		goodsName	상품명 (특수문자 사용금지)
		timestamp	가맹점 시스템 시각 (yyMMddHHmmssSSS)
		signature	결제 위변조 방지를 위한 파라미터 서명 값
		keyinAuthType	키인인가구분 (K: 비인증 | O: 구인증) ※ 경우 따라 카드사 특약 필요 비인증, 구인증 심사 여부를 영업사원에게 문의
		authType	구인증용 인증타입 (0: 생년월일 | 1: 사업자번호) (주의) 구인증 사용 시 필수값
		regNo	구인증용 아이디 (생년월일(YYMMDD) | 사업자번호) (주의) 구인증 사용 시 필수값
		passwd	구인증용 카드 비밀번호 앞2자리  (주의) 구인증 사용 시 필수값
		customerName	구매자명
		customerTelNo	구매자연락처
		customerEmail	구매자이메일
		*/

		//$mbrNo = $ㅁㅁㅁㅁ;					//섹타나인에서 부여한 가맹점 번호 (상점 아이디)
		$mbrRefNoo = "WNS".$mb_id.time();	//가맹점주문번호 (가맹점에서 생성한 중복되지 않는 번호)
		$paymethod = "CARD";				//지불수단 (CARD 고정값)
		$cardNo = $number;					//카드번호
		$expd = $expiry;					//카드유효기간 (YYMM) (주의) 년/월 순서에 유의
		$amount = $pd_price;				//결제금액 (공급가+부가세)
		//$installment = $installment;		//할부개월 (0 ~ 24)
		$goodsName = $pd_name;				//상품명 (특수문자 사용금지)
		$timestamp = date("ymdHis");		//가맹점 시스템 시각 (yyMMddHHmmssSSS)
		//$signature = $ㅁㅁㅁㅁ;				//결제 위변조 방지를 위한 파라미터 서명 값
		$keyinAuthType = "O";				//키인인가구분 (K: 비인증 | O: 구인증) ※ 경우 따라 카드사 특약 필요 비인증, 구인증 심사 여부를 영업사원에게 문의
		//$authType = $ㅁㅁㅁㅁ;				//구인증용 인증타입 (0: 생년월일 | 1: 사업자번호) (주의) 구인증 사용 시 필수값
		$regNo = $authDob;					//구인증용 아이디 (생년월일(YYMMDD) | 사업자번호) (주의) 구인증 사용 시 필수값
		$passwd = $authPw;					//구인증용 카드 비밀번호 앞2자리  (주의) 구인증 사용 시 필수값
		$customerName = $payerName;			//구매자명
		$customerTelNo = $payerTel;			//구매자연락처
		$customerEmail = $payerEmail;		//구매자이메일

		$mbrRefNo = $mbrRefNoo;														// 가맹점에서 나름대로 정한 중복되지 않는 주문번호
		$timestamp = makeTimestamp();												// 타임스탬프 (YYYYMMDDHHMI24SS 형식의 문자열)
		$signature = makeSignature($mbrNo,$mbrRefNo,$amount,$apiKey,$timestamp); 	// 결제 위변조 방지를 위한 파라미터 서명 값

		if($sid) {
			$parameters = array(
				'sid' => $sid,
				'mbrNo' => $mbrNo,
				'mbrRefNo' => $mbrRefNo,
				'paymethod' => $paymethod,
				'cardNo' => $cardNo,
				'expd' => $expd,
				'amount' => $amount,
				'installment' => $installment,
				'goodsName' => $goodsName,
				'timestamp' => $timestamp,
				'signature' => $signature,
				'keyinAuthType' => $keyinAuthType,
				'authType' => $authType,
				'regNo' => $regNo,
				'passwd' => $passwd,
				'customerName' => $customerName,
				'customerTelNo' => $customerTelNo,
				'customerEmail' => $customerEmail
			);
		} else {
			$parameters = array(
				'mbrNo' => $mbrNo,
				'mbrRefNo' => $mbrRefNo,
				'paymethod' => $paymethod,
				'cardNo' => $cardNo,
				'expd' => $expd,
				'amount' => $amount,
				'installment' => $installment,
				'goodsName' => $goodsName,
				'timestamp' => $timestamp,
				'signature' => $signature,
				'keyinAuthType' => $keyinAuthType,
				'authType' => $authType,
				'regNo' => $regNo,
				'passwd' => $passwd,
				'customerName' => $customerName,
				'customerTelNo' => $customerTelNo,
				'customerEmail' => $customerEmail
			);
		}
		
		/*=================================================================================================
		   API 호출
		  =================================================================================================*/
		$apiUrl = $API_BASE."/v1/api/payments/payment/card-keyin/trans";
		$result = "";
		$result = httpPost($apiUrl, $parameters);

		/*
		$row['resultCode']					응답코드 '200' 이면 성공, 이외는 거절
		$row['resultMessage']				응답메시지
		data	---------------- 2 Depth -------------------
		$row['data']['mbrNo']				가맹점번호
		$row['data']['mbrRefNo']			가맹점주문번호
		$row['data']['refNo']				거래번호 (거래 취소시 필요)
		$row['data']['tranDate']			거래일자 (거래 취소시 필요)
		$row['data']['payType']				결제타입 (거래 취소시 필요)
		$row['data']['tranTime']			거래시각
		$row['data']['amount']				결제금액
		$row['data']['applNo']				승인번호
		$row['data']['issueCompanyNo']		카드 발급사 코드(공통코드 참조)
		$row['data']['issueCompanyName']	카드발급사명
		$row['data']['issueCardName']		발급카드명
		$row['data']['acqCompanyNo']		카드 매입사 코드 (공통코드 참조)
		$row['data']['acqCompanyName']		카드매입사명
		*/

		$row = json_decode($result, true);
		$jsondata = serialize($row);
		$last4 = substr($card_num, -4);
		$creates = "20".$row['data']['tranDate'].$row['data']['tranTime'];

		if($row['resultCode'] == '200') {
			$row['resultCode'] = '0000';
		}


		$sql = " insert into pay_payment_passgo
					set payments = '{$payments}',
						authCd = '{$row['data']['applNo']}',

						mb_id = '{$mb_id}',
						mb_name = '{$mba['mb_nick']}',
						keydata = '{$apiKey}',
						card_num = '{$card_num}',
						payerName = '{$payerName}',
						payerEmail = '{$payerEmail}',
						payerTel = '{$payerTel}',
						cardnumber = '{$number}',

						expiry = '{$expiry}',
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

		if($row['resultCode'] == '0000') {
			echo $row['resultCode'];
		} else {
			echo $row['resultMessage'];
		}
	} else if($payments == "stnb") { // 섹타나인


		$API_BASE = $config['cf_7_subj'];	// API URL

		$mbrNo = $mba['mb_22'];			// 페이시스 상점ID
		$apiKey = $mba['mb_23'];		// 페이시스 apiKey

		/*
		mbrNo	섹타나인에서 부여한 가맹점 번호 (상점 아이디)
		mbrRefNo	가맹점주문번호 (가맹점에서 생성한 중복되지 않는 번호)
		paymethod	지불수단 (CARD 고정값)
		cardNo	카드번호
		expd	카드유효기간 (YYMM) (주의) 년/월 순서에 유의
		amount	결제금액 (공급가+부가세)
		installment	할부개월 (0 ~ 24)
		goodsName	상품명 (특수문자 사용금지)
		timestamp	가맹점 시스템 시각 (yyMMddHHmmssSSS)
		signature	결제 위변조 방지를 위한 파라미터 서명 값
		keyinAuthType	키인인가구분 (K: 비인증 | O: 구인증) ※ 경우 따라 카드사 특약 필요 비인증, 구인증 심사 여부를 영업사원에게 문의
		authType	구인증용 인증타입 (0: 생년월일 | 1: 사업자번호) (주의) 구인증 사용 시 필수값
		regNo	구인증용 아이디 (생년월일(YYMMDD) | 사업자번호) (주의) 구인증 사용 시 필수값
		passwd	구인증용 카드 비밀번호 앞2자리  (주의) 구인증 사용 시 필수값
		customerName	구매자명
		customerTelNo	구매자연락처
		customerEmail	구매자이메일
		*/

		//$mbrNo = $ㅁㅁㅁㅁ;					//섹타나인에서 부여한 가맹점 번호 (상점 아이디)
		$mbrRefNoo = "WNO".$mb_id.time();	//가맹점주문번호 (가맹점에서 생성한 중복되지 않는 번호)
		$paymethod = "CARD";				//지불수단 (CARD 고정값)
		$cardNo = $number;					//카드번호
		$expd = $expiry;					//카드유효기간 (YYMM) (주의) 년/월 순서에 유의
		$amount = $pd_price;				//결제금액 (공급가+부가세)
		//$installment = $installment;		//할부개월 (0 ~ 24)
		$goodsName = $pd_name;				//상품명 (특수문자 사용금지)
		$timestamp = date("ymdHis");		//가맹점 시스템 시각 (yyMMddHHmmssSSS)
		//$signature = $ㅁㅁㅁㅁ;				//결제 위변조 방지를 위한 파라미터 서명 값
		$keyinAuthType = "K";				//키인인가구분 (K: 비인증 | O: 구인증) ※ 경우 따라 카드사 특약 필요 비인증, 구인증 심사 여부를 영업사원에게 문의
		//$authType = $ㅁㅁㅁㅁ;				//구인증용 인증타입 (0: 생년월일 | 1: 사업자번호) (주의) 구인증 사용 시 필수값
		//$regNo = $authDob;					//구인증용 아이디 (생년월일(YYMMDD) | 사업자번호) (주의) 구인증 사용 시 필수값
		//$passwd = $authPw;					//구인증용 카드 비밀번호 앞2자리  (주의) 구인증 사용 시 필수값
		$customerName = $payerName;			//구매자명
		$customerTelNo = $payerTel;			//구매자연락처
		$customerEmail = $payerEmail;		//구매자이메일

		$mbrRefNo = $mbrRefNoo;														// 가맹점에서 나름대로 정한 중복되지 않는 주문번호
		$timestamp = makeTimestamp();												// 타임스탬프 (YYYYMMDDHHMI24SS 형식의 문자열)
		$signature = makeSignature($mbrNo,$mbrRefNo,$amount,$apiKey,$timestamp); 	// 결제 위변조 방지를 위한 파라미터 서명 값

		if($sid) {
			$parameters = array(
				'sid' => $sid,
				'mbrNo' => $mbrNo,
				'mbrRefNo' => $mbrRefNo,
				'paymethod' => $paymethod,
				'cardNo' => $cardNo,
				'expd' => $expd,
				'amount' => $amount,
				'installment' => $installment,
				'goodsName' => $goodsName,
				'timestamp' => $timestamp,
				'signature' => $signature,
				'keyinAuthType' => $keyinAuthType,
				'authType' => $authType,
				'customerName' => $customerName,
				'customerTelNo' => $customerTelNo,
				'customerEmail' => $customerEmail
			);
		} else {
			$parameters = array(
				'mbrNo' => $mbrNo,
				'mbrRefNo' => $mbrRefNo,
				'paymethod' => $paymethod,
				'cardNo' => $cardNo,
				'expd' => $expd,
				'amount' => $amount,
				'installment' => $installment,
				'goodsName' => $goodsName,
				'timestamp' => $timestamp,
				'signature' => $signature,
				'keyinAuthType' => $keyinAuthType,
				'authType' => $authType,
				'customerName' => $customerName,
				'customerTelNo' => $customerTelNo,
				'customerEmail' => $customerEmail
			);
		}
		
		/*=================================================================================================
		   API 호출
		  =================================================================================================*/
		$apiUrl = $API_BASE."/v1/api/payments/payment/card-keyin/trans";
		$result = "";
		$result = httpPost($apiUrl, $parameters);

		/*
		$row['resultCode']					응답코드 '200' 이면 성공, 이외는 거절
		$row['resultMessage']				응답메시지
		data	---------------- 2 Depth -------------------
		$row['data']['mbrNo']				가맹점번호
		$row['data']['mbrRefNo']			가맹점주문번호
		$row['data']['refNo']				거래번호 (거래 취소시 필요)
		$row['data']['tranDate']			거래일자 (거래 취소시 필요)
		$row['data']['payType']				결제타입 (거래 취소시 필요)
		$row['data']['tranTime']			거래시각
		$row['data']['amount']				결제금액
		$row['data']['applNo']				승인번호
		$row['data']['issueCompanyNo']		카드 발급사 코드(공통코드 참조)
		$row['data']['issueCompanyName']	카드발급사명
		$row['data']['issueCardName']		발급카드명
		$row['data']['acqCompanyNo']		카드 매입사 코드 (공통코드 참조)
		$row['data']['acqCompanyName']		카드매입사명
		*/

		$row = json_decode($result, true);
		$jsondata = serialize($row);
		$last4 = substr($card_num, -4);
		$creates = "20".$row['data']['tranDate'].$row['data']['tranTime'];

		if($row['resultCode'] == '200') {
			$row['resultCode'] = '0000';
		}


		$sql = " insert into pay_payment_passgo
					set payments = '{$payments}',
						authCd = '{$row['data']['applNo']}',

						mb_id = '{$mb_id}',
						mb_name = '{$mba['mb_nick']}',
						keydata = '{$apiKey}',
						card_num = '{$card_num}',
						payerName = '{$payerName}',
						payerEmail = '{$payerEmail}',
						payerTel = '{$payerTel}',
						cardnumber = '{$number}',

						expiry = '{$expiry}',
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

		if($row['resultCode'] == '0000') {
			echo $row['resultCode'];
		} else {
			echo $row['resultMessage'];
		}
	}