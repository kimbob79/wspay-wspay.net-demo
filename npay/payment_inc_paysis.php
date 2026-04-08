<?php
//	error_reporting( E_ALL );
//	ini_set( "display_errors", 0 );


	if($payments == "paysis") { // 페이시스

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
			curl_setopt($ch, CURLOPT_POST, 18);
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

		$sid = $member_pg['pg_key1'];			// 페이시스 TID
		$apiKey = $member_pg['pg_key2'];		// 페이시스 apiKey
		$mbrNo = $member_pg['pg_key3'];			// 페이시스 상점ID

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

		$mbrRefNo			= $pg['pg_tid'].$mb_id.time();			//가맹점주문번호 (가맹점에서 생성한 중복되지 않는 번호)
		$paymethod			= "CARD";								//지불수단 (CARD 고정값)
		$cardNo				= $pay_cardnum;							//카드번호
		$expd				= $pay_YM;								//카드유효기간 (YYMM) (주의) 년/월 순서에 유의
		$amount				= $pay_price;							//결제금액 (공급가+부가세)
		$installment		= $pay_installment;						//할부개월 (0 ~ 24)
		$goodsName			= $pay_product;							//상품명 (특수문자 사용금지)
//		$timestamp = date("ymdHis");								//가맹점 시스템 시각 (yyMMddHHmmssSSS)
		if($pay_certify) {
			$keyinAuthType = "O";									//키인인가구분 (K: 비인증 | O: 구인증) ※ 경우 따라 카드사 특약 필요 비인증, 구인증 심사 여부를 영업사원에게 문의
		} else {
			$keyinAuthType = "K";
		}
		if(strlen($pay_certify) == 6) {
			$authType = 0;											// 생년월일 구인증용 인증타입 (0: 생년월일 | 1: 사업자번호) (주의) 구인증 사용 시 필수값
		} else {
			$authType = 1;											// 사업자등록번호
		}
		$regNo				= $pay_certify;							//구인증용 아이디 (생년월일(YYMMDD) | 사업자번호) (주의) 구인증 사용 시 필수값
		$passwd				= $pay_password;						//구인증용 카드 비밀번호 앞2자리  (주의) 구인증 사용 시 필수값
		$customerName		= $pay_pname;							//구매자명
		$customerTelNo		= $pay_phone;							//구매자연락처
		$customerEmail		= $pay_email;							//구매자이메일

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

		$sql_common = " ps_resultCode = '".$row['resultCode']."',
						ps_resultMessage = '".$row['resultMessage']."',
						ps_mbrNo = '".$row['data']['mbrNo']."',
						ps_mbrRefNo = '".$row['data']['mbrRefNo']."',
						ps_refNo = '".$row['data']['refNo']."',
						ps_tranDate = '".$row['data']['tranDate']."',
						ps_payType = '".$row['data']['payType']."',
						ps_tranTime = '".$row['data']['tranTime']."',
						ps_installment = '".$row['data']['installment']."',
						ps_amount = '".$row['data']['amount']."',
						ps_applNo = '".$row['data']['applNo']."',
						ps_issueCompanyNo = '".$row['data']['issueCompanyNo']."',
						ps_issueCompanyName = '".$row['data']['issueCompanyName']."',
						ps_issueCardName = '".$row['data']['issueCardName']."',
						ps_acqCompanyNo = '".$row['data']['acqCompanyNo']."',
						ps_acqCompanyName = '".$row['data']['acqCompanyName']."' ";

		$sql = " insert into pay_payment_paysis set ".$sql_common;
		sql_query($sql);


		$card_tid = $pg['pg_tid'].$mb_id; // TID
		$card_num = $row['data']['applNo']; // 승인번호
		$card_trxid = $row['data']['refNo']; // 거래ID
		$card_trackId = $row['data']['mbrRefNo']; // 주문번호
		$card_name = $row['data']['issueCompanyName']; // 카드명
		$tranDatetranTime = "20".$row['data']['tranDate'].$row['data']['tranTime'];
		$card_sdatetime = date("Y-m-d H:i:s", strtotime($tranDatetranTime)); // 승인일시
		$card_result_code = $row['resultCode']; // 결과코드
		$card_result_msg = $row['resultMessage']; // 결과메세지1
		$card_result_msg2 = $row['resultMessage']; // 결과메세지2
		$updatetime = G5_TIME_YMDHIS;

		if($card_result_code == '200') {
			$card_result_code = '0000';
		}

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