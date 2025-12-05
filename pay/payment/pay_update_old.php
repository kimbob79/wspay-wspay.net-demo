<?php
	include_once('./_common.php');

	if($_POST['mb_id'] != $member['mb_id']) {
		echo "잘못된 접근입니다.";
	} else {
		$mb_id = $_POST['mb_id']; // 터미널ID
	}

	$pd_price = preg_replace("/[^0-9]/","",$_POST['pd_price']); // 금액

	if($pd_price > $config['cf_1']) {
		echo $config['cf_1']."원 이하만 가능합니다.";
		exit;
	}

	// 고객정보
	$payerName = $_POST['payerName']; // 고객명
	$payerEmail = $_POST['payerEmail']; // 고객 이메일
	$payerTel1 = $_POST['payerTel1']; // 고객 휴대전화번호
	$payerTel2 = $_POST['payerTel2']; // 고객 휴대전화번호
	$payerTel3 = $_POST['payerTel3']; // 고객 휴대전화번호
	$payerTel = $payerTel1."-".$payerTel2."-".$payerTel3; // 고객 휴대전화번호

	// 카드정보
	$number = preg_replace("/[^0-9]/","",$_POST['number']); // 카드번호
	$expiry = $_POST['expiry1'].$_POST['expiry2']; // YYMM 2103 21년 03월
	$installment = $_POST['installment']; // 할구개월수 0 = 일시불, 12 = 12개월

	// 상품정보
	$pd_name = $_POST['pd_name']; // 상품명

	$card_num = $number; // 카드번호 암호화

	function pay($payerName, $payerEmail, $payerTel, $number, $expiry, $installment, $pd_name, $pd_price, $mb_id, $keydata, &$http_status, &$header = null) {

		$url = 'https://svcapi.mtouch.com/api/pay';

		// 상품정보
		$prodId = ""; // 
		$trxType = "ONTR"; // 결제번호
		$tmnId = $mb_id; // 터미널ID
		//$trackId = "WP_".date("Ymdhis")."_".rand(111,999); // 주문번호
		$trackId = $mb_id."_".date("Ymdhis").rand(1,9); // 주문번호
		$udf1 = ""; // 결제번호
		$udf2 = ""; // 결제번호

		$post_data = '
		{"pay":{
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
				"name":"제품구매",
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
			}
			,"metadata":{
				"authPw":"'.$authPw.'",
				"authDob":"'.$authDob.'",
				"cardAuth":"'.$cardAuth.'"
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
	/*
	if(!$is_admin) {
		$row_card = sql_fetch(" select count(card_num) as card_num from pay_payment_passgo where resultYN = '0' and authCd != '' and card_num = '{$card_num}' and date_format(datetime,'%Y-%m-%d') = date_format(NOW(),'%Y-%m-%d')");
		if($row_card['card_num'] > 0) {
			echo "중복결제 불가";
			exit;
		}
	}
	*/
	


	$mba = get_member($mb_id);


	if($mba['mb_3']) {
		$keydata = $mba['mb_3'];
	} else {
		$keydata = "pk_af14-15f3b4-7b5-c6486";
	}



	$ret = pay($payerName, $payerEmail, $payerTel, $number, $expiry, $installment, $pd_name, $pd_price, $mb_id, $keydata, $http_status);
	$row = json_decode($ret, true);

	//print_r($row); //전송결과 출력
	$sql = " insert into pay_payment_passgo
				set authCd = '{$row['pay']['authCd']}',

					mb_id = '{$mb_id}',
					mb_name = '{$mba['mb_name']}',
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
					cardAuth = '{$row['pay']['metadata']['cardAuth']}',
					authPw = '{$row['pay']['metadata']['authPw']}',
					authDob = '{$row['pay']['metadata']['authDob']}',
					userIp='{$_SERVER['REMOTE_ADDR']}',
					userAgent='{$_SERVER['HTTP_USER_AGENT']}',
					datetime = '".G5_TIME_YMDHIS."' ";
	sql_query($sql);
	/*
	if($row['result']['resultMsg']=='정상') {
		$insertId = sql_insert_id();
		MotorpaySend($insertId);
	}
	*/

	echo $row['result']['advanceMsg'];