<?php
	if($payments == "welcom") { // 웰컴 구인증

		function current_millis() {
			return (int) (microtime(true) * 1000);
		}

		//$mid = 'wel000695m';
		$mid = $pg_key1;
		$pay_type = 'CREDIT_CARD';
		$pay_method = 'CREDIT_OLDAUTH_API'; // 비인증: CREDIT_UNAUTH_API, 구인증: CREDIT_OLDAUTH_API
		$order_no = $pg['pg_tid'].$mb_id.time(); // 중복되면 안됩니다. 영문숫자로 구성 공백 및 특수문자 사용금지
		$amount = $pay_price;
		$millis = current_millis();
		$card_no_noenc = $pay_cardnum; // 승인되지 않는 번호입니다. 실제 승인테스트할 카드번호로 변경해주세요.
		$card_pw_noenc = $pay_password; // 카드비밀번호 앞 2자리입니다. 샘플값입니다.
		$card_holder_ymd_noenc = $pay_certify; // 71년3월8일 인 경우 샘플값입니다.
		//$apikey = "ed0c9a186fd57fc9e7e479b8fdeace63";
		$apikey = $pg_key2;
		//$iv = "f83401592ca45691b68ea5a370f5d37f";
		$iv = $pg_key3;



		// 카드번호 암호화
		$card_no = bin2hex(openssl_encrypt($card_no_noenc, 'AES-128-CBC', hex2bin($apikey), OPENSSL_RAW_DATA,hex2bin($iv)));

		// 카드비밀번호 앞2자리 암호화
		$card_pw = bin2hex(openssl_encrypt($card_pw_noenc, 'AES-128-CBC', hex2bin($apikey), OPENSSL_RAW_DATA,hex2bin($iv)));

		// 카드소유자 생년월일 암호화
		$card_holder_ymd = bin2hex(openssl_encrypt($card_holder_ymd_noenc, 'AES-128-CBC', hex2bin($apikey), OPENSSL_RAW_DATA,hex2bin($iv)));

		// 검증값 생성
		$hash_value = hash("sha256", $mid.$pay_type.$pay_method.$order_no.$amount.$millis.$apikey);

		$card_expiry_ym = $pay_YM; // 2021년 07월
		$user_name = $pay_pname; // 카드주명
		$prodPrice = $pay_price; // 결제금액
		$prodName = $pay_product; // 상품명
		$card_sell_mm = sprintf('%02d', $pay_installment); // 일시불 00, 2개월 02, 12개월 12
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

		$sql_common = " wc_result_code = '".$row['result_code']."',
						wc_result_message = '".$row['result_message']."',
						wc_mid = '".$row['mid']."',
						wc_transaction_no = '".$row['transaction_no']."',
						wc_order_no = '".$row['order_no']."',
						wc_approval_no = '".$row['approval_no']."',
						wc_approval_ymdhms = '".$row['approval_ymdhms']."',
						wc_amount = '".$row['amount']."',
						wc_card_code = '".$row['card_code']."',
						wc_card_name = '".$row['card_name']."',
						wc_card_sell_mm = '".$row['card_sell_mm']."',
						wc_user_name = '".$row['user_name']."',
						wc_product_name = '".$row['product_name']."',
						wc_echo = '".$row['echo']."' ";

		$sql = " insert into pay_payment_welcom set ".$sql_common;
		sql_query($sql);


		$card_tid = $pg['pg_tid'].$mb_id; // TID
		$card_num = $row['approval_no']; // 승인번호
		$card_trxid = $row['transaction_no']; // 거래ID
		$card_trackId = $row['order_no']; // 주문번호
		$card_name = $row['card_name']; // 
		$card_sdatetime = date("Y-m-d H:i:s", strtotime($row['approval_ymdhms']));
		$card_result_code = $row['result_code'];
		$card_result_msg = $row['result_message'];
		$card_result_msg2 = $row['result_message'];
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