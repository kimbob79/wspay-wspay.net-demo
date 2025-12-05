<?php

	
	if($payments == "danal") { // 다날 구인증


		// 광원 결제
		function pay_danal($payerName, $payerEmail, $payerTel, $number, $expiry, $cardAuth, $authPw, $authDob, $installment, $pd_name, $pd_price, $tmnId, $keydata, $trackId, &$http_status, &$header = null) {

			$url = 'https://api.winglobalpay.com/api/pay';

			// 상품정보
			$prodId = ""; //
			$trxType = "ONTR"; // 결제번호
			
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
					"products": {
						"prodId": "",
						"name": "'.$pd_name.'",
						"qty": 1,
						"price":"'.$pd_price.'",
						"desc": "'.$pd_name.'"
					},
					"trxId":"",
					"trxType":"'.$trxType.'",
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

		$trackId = $pg['pg_tid'].$mb_id.time();

		$ret = pay_danal($pay_pname, $pay_email, $pay_phone, $pay_cardnum, $pay_YM, $cardAuth, $pay_password, $pay_certify, $pay_installment, $pay_product, $pay_price, $pg_key1, $pg_key2, $trackId, $http_status, $header);
		$row = json_decode($ret, true);

		$sql_common = " k1_resultCd = '".$row['result']['resultCd']."',
						k1_resultMsg = '".$row['result']['resultMsg']."',
						k1_advanceMsg = '".$row['result']['advanceMsg']."',
						k1_create = '".$row['result']['create']."',
						k1_authCd = '".$row['pay']['authCd']."',
						k1_cardId = '".$row['pay']['card']['cardId']."',
						k1_installment = '".$row['pay']['card']['installment']."',
						k1_last4 = '".$row['pay']['card']['last4']."',
						k1_issuer = '".$row['pay']['card']['issuer']."',
						k1_cardType = '".$row['pay']['card']['cardType']."',
						k1_prodId = '".$row['pay']['products'][0]['prodId']."',
						k1_name = '".$row['pay']['products'][0]['name']."',
						k1_qty = '".$row['pay']['products'][0]['qty']."',
						k1_price = '".$row['pay']['products'][0]['price']."',
						k1_desc = '".$row['pay']['products'][0]['desc']."',
						k1_trxId = '".$row['pay']['trxId']."',
						k1_trxType = '".$row['pay']['trxType']."',
						k1_tmnId = '".$row['pay']['tmnId']."',
						k1_trackId = '".$row['pay']['trackId']."',
						k1_amount = '".$row['pay']['amount']."',
						k1_udf1 = '".$row['pay']['udf1']."',
						k1_udf2 = '".$row['pay']['udf2']."' ";

		$sql = " insert into pay_payment_danal set ".$sql_common;
		sql_query($sql);

		$card_tid = $pg['pg_tid'].$mb_id;
		$card_num = $row['pay']['authCd'];
		$card_trxid = $row['pay']['trxId'];
		$card_trackId = $row['pay']['trackId'];
		$card_name = $row['pay']['card']['issuer'];
		$card_sdatetime = date("Y-m-d H:i:s", strtotime($row['result']['create']));
		$card_result_code = $row['result']['resultCd'];
		$card_result_msg = $row['result']['resultMsg'];
		$card_result_msg2 = $row['result']['advanceMsg'];
		$updatetime = G5_TIME_YMDHIS;


		if($card_result_code == '0000') {
			$card_yn = "Y";
			echo $card_result_code;
		} else {
			$card_yn = "S";
			echo $card_result_msg2;
		}

		$sql = " UPDATE pay_payment
					set card_tid = '".$card_tid."',
						card_yn = '".$card_yn."',
						card_num = '".$card_num."',
						card_trxid = '".$card_trxid."',
						card_trackId = '".$card_trackId."',
						card_name = '".$card_name."',
						card_sdatetime = '".$card_sdatetime."',
						card_result_code = '".$card_result_code."',
						card_result_msg = '".$card_result_msg."',
						card_result_msg2 = '".$card_result_msg2."',
						updatetime = '".$updatetime."'
					WHERE pay_id = '".$pay_id."' ";
		sql_query($sql);
	}


?>