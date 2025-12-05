<?php
$logDir = '/mpchosting/www/api/logs';
$today = date('Y-m-d');
$logFile = $logDir . '/error_' . $today . '.log';

ini_set('error_log', $logFile);

	ini_set('allow_url_fopen', 'ON');
	include('./_common.php');


// Read the input stream
$body = file_get_contents("php://input");


// Decode the JSON object
$object = json_decode($body, true);

$mid  = $object['mid'];									//상점ID
$pay_type  = $object['pay_type'];						//결제구분 (AUTH 승인, CANCEL 취소, PART_CANCEL 부분취소)
$bank_code  = $object['bank_code'];						//카드/은행 코드 (CREDIT_CARD 신용카드, ACCNT 계좌이체, VACCNT 가상계좌)
$transaction_flag  = $object['transaction_flag'];		//거래부호
$order_no  = $object['order_no'];						//주문번호
$transaction_no  = $object['transaction_no'];			//거래번호
$approval_ymdhms  = $object['approval_ymdhms'];			//승인일시
$cancel_ymdhms  = $object['cancel_ymdhms'];				//취소일시
$amount  = $object['amount'];							//승인금액
$remain_amount  = $object['remain_amount'];				//잔액
$user_id  = $object['user_id'];							//회원ID
$user_name  = $object['user_name'];						//구매자명
$product_code  = $object['product_code'];				//상품코드
$product_name  = $object['product_name'];				//상품명
$approval_no  = $object['approval_no'];					//승인번호
$card_sell_mm  = $object['card_sell_mm'];				//할부개월
$account_no  = $object['account_no'];					//계좌번호
$deposit_ymdhms  = $object['deposit_ymdhms'];			//가상계좌 입금일시
$deposit_amount  = $object['deposit_amount'];			//가상계좌 입금금액
$deposit_name  = $object['deposit_name'];				//입금자명
$cash_seq  = $object['cash_seq'];						//현금영수증 일련번호
$cash_approval_no  = $object['cash_approval_no'];		//현금영수증 승인번호
$bank_name  = $object['bank_name'];						//카드/은행 이름
/*
C1 삼성카드
66 신한카드
67 현대카드
61 BC카드
62 KB국민카드
68 롯데카드
63 하나(외환)카드
82 하나카드
71 NH채움카드
53 씨티카드
73 수협카드
34 광주카드
35 제주카드
37 전북카드
75 우리카드
*/
$millis  = $object['millis'];								//NOTI 통보 밀리초
$hash_value  = $object['hash_value'];						//해쉬 값
$org_pg_seq_no  = $object['org_pg_seq_no'];					//상위PG 거래 고유번호
$org_pg_cancel_seq_no  = $object['org_pg_cancel_seq_no'];	//상위PG 취소거래 고유번호
$echo  = $object['echo'];									//결제 승인 요청시 설정된 에코 값


// 서버(DB)로부터 키값을 가져와야합니다.
if($mid == "kowelcome227") {
	$api_key = "5651b42b07c5340e6158df67f57f866c"; // 실제키값으로 변경필요.

} else if($mid == "wel000695m") {
	$api_key = "ed0c9a186fd57fc9e7e479b8fdeace63";
	// 웰컴 수기 뒤10자리를 뺀 나머지 오더번호를 가지고 tid 변환
	$product_code = substr($order_no, 0, -10);
	$dv_tid_ori = 'wel000695m';

} else if($mid == "wel000773m") {
	$api_key = "db9b6151906ca3bcc52def322ff4b707";
	$product_code = substr($order_no, 0, -10);
	$dv_tid_ori = 'wel000773m';

} else if($mid == "wel000774m") {
	$api_key = "4597d822980cb97144043e92b8691b09";
	$product_code = substr($order_no, 0, -10);
	$dv_tid_ori = 'wel000774m';

} else if($mid == "wel000775m") {
	$api_key = "c719316bfcfc3b355f4ee28d75d81dcb";
	$product_code = substr($order_no, 0, -10);
	$dv_tid_ori = 'wel000775m';

} else if($mid == "wel000776m") {
	$api_key = "2d478a9705508e47631d031f79e78ca0";
	$product_code = substr($order_no, 0, 13);
	$dv_tid_ori = 'wel000776m';

/*
-----------------------------------------
1.상호 : 원성페이먼츠
2.상점ID : wel000867m 스마트로(구인증_D+1)
3.apiKey : f0c618fcdc499f08ee46d2650be8a69f
4.ivValue : 7d9b262d50bdb745c255ba305801e799
-----------------------------------------
1.상호 : 원성페이먼츠
2.상점ID : wel000868m 스마트로(구인증_D+1)
3.apiKey : bf185a0e8fd4afcec4696b71a64cf293
4.ivValue : 4c78477ec7f9d1928ccaf6d0f7300a2d
-----------------------------------------
1.상호 : 원성페이먼츠
2.상점ID : wel000869m 스마트로(구인증_D+1)
3.apiKey : ec93749ea4bc9f65fd5a279593c7850a
4.ivValue : 9e577f4cc00d774427a85b3402c51dd6
-----------------------------------------
1.상호 : 원성페이먼츠
2.상점ID : wel000870m 스마트로(구인증_D+1)
3.apiKey : 03b1dad5e740b0a1dfbf3eb7f2f35d8c
4.ivValue : f7e4792dbb082c0b703a33bacead7902
-----------------------------------------
1.상호 : 원성페이먼츠
2.상점ID : wel000871m 스마트로(비인증_D+1)
3.apiKey : 8cb02a6765dbfe03354600e8804d87b8
4.ivValue : 70e79e0435381ff60e3fce18648cb557
-----------------------------------------
1.상호 : 원성페이먼츠
2.상점ID : wel000872m 스마트로(정기과금_D+1)
    * 몰 ID : welcome01p (정기결제 빌키등록용)
3.apiKey : cd97f45b04905dcc3597765fe40f6e04
4.ivValue : c9e4a28dcb024ced926db6c3eed6de35
-----------------------------------------
*/

} else if($mid == "wel000867m") { // 2024-5-16 구인증 추가
	$api_key = "f0c618fcdc499f08ee46d2650be8a69f";
	$product_code = substr($order_no, 0, -10);
	$dv_tid_ori = 'wel000867m';

} else if($mid == "wel000868m") { // 2024-5-16
	$api_key = "bf185a0e8fd4afcec4696b71a64cf293";
	$product_code = substr($order_no, 0, -10);
	$dv_tid_ori = 'wel000868m';

} else if($mid == "wel000869m") { // 2024-5-16
	$api_key = "ec93749ea4bc9f65fd5a279593c7850a";
	$product_code = substr($order_no, 0, -10);
	$dv_tid_ori = 'wel000869m';

} else if($mid == "wel000870m") { // 2024-5-16
	$api_key = "03b1dad5e740b0a1dfbf3eb7f2f35d8c";
	$product_code = substr($order_no, 0, -10);
	$dv_tid_ori = 'wel000870m';

} else if($mid == "wel000871m") { // 2024-5-16
	$api_key = "8cb02a6765dbfe03354600e8804d87b8";
	$product_code = substr($order_no, 0, -10);
	$dv_tid_ori = 'wel000871m';

} else if($mid == "wel000872m") { // 2024-5-16
	$api_key = "cd97f45b04905dcc3597765fe40f6e04";
	$product_code = substr($order_no, 0, -10);
	$dv_tid_ori = 'wel000872m';

} else if($mid == "wel000874m") { // 2024-5-16
	$api_key = "29bc6cd2d7a5928f3dcc1116a05e6294";
	$product_code = substr($order_no, 0, -10);
	$dv_tid_ori = 'wel000874m';

} else if($mid == "M2482591") { // 2024-5-16
	$api_key = "417b3506dde46ee61f831c6e7df7808f";
//	$product_code = substr($order_no, 0, -10);
//	$dv_tid_ori = 'M2482591';
}
// 검증값 생성
$hash_value_2 = hash("sha256", $mid.$order_no.$millis.$api_key);


if ($hash_value == $hash_value_2) {


	/******** 타사이트로 전송 ************/




	/*
	$data = array('mid' => $mid, 'pay_type' => $pay_type, 'bank_code' => $bank_code, 'transaction_flag' => $transaction_flag, 'order_no' => $order_no, 'transaction_no' => $transaction_no, 'approval_ymdhms' => $approval_ymdhms, 'cancel_ymdhms' => $cancel_ymdhms, 'amount' => $amount, 'remain_amount' => $remain_amount, 'user_id' => $user_id, 'user_name' => $user_name, 'product_code' => $product_code, 'product_name' => $product_name, 'approval_no' => $approval_no, 'card_sell_mm' => $card_sell_mm, 'account_no' => $account_no, 'deposit_ymdhms' => $deposit_ymdhms, 'deposit_amount' => $deposit_amount, 'deposit_name' => $deposit_name, 'cash_seq' => $cash_seq, 'cash_approval_no' => $cash_approval_no, 'bank_name' => $bank_name);

	//$url = 'https://noti.payvery.kr/api/v2/noti/welcome';
	//$url = 'http://noti.payvery.kr/api/v2/noti/welcome';
	$url = 'http://noti.payvery.kr/api/v1/noti/welcome';
	

	

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
	*/
//	'https://pgapi.thegoodpay.co.kr/api/webhooks/wanna',

	$urls = [
		'http://noti.payvery.kr/api/v2/noti/welcome',
		'http://redpay.kr/api/welcom/index.php',
		'https://pgapi.thegoodpay.co.kr/api/webhooks/wanna',
		'https://pay.wnapay.net/welcom.do'
	];

	
	$data = array('mid' => $mid, 'pay_type' => $pay_type, 'bank_code' => $bank_code, 'transaction_flag' => $transaction_flag, 'order_no' => $order_no, 'transaction_no' => $transaction_no, 'approval_ymdhms' => $approval_ymdhms, 'cancel_ymdhms' => $cancel_ymdhms, 'amount' => $amount, 'remain_amount' => $remain_amount, 'user_id' => $user_id, 'user_name' => $user_name, 'product_code' => $product_code, 'product_name' => $product_name, 'approval_no' => $approval_no, 'card_sell_mm' => $card_sell_mm, 'account_no' => $account_no, 'deposit_ymdhms' => $deposit_ymdhms, 'deposit_amount' => $deposit_amount, 'deposit_name' => $deposit_name, 'cash_seq' => $cash_seq, 'cash_approval_no' => $cash_approval_no, 'bank_name' => $bank_name);

	$mh = curl_multi_init();
	$handles = [];

	foreach ($urls as $url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_multi_add_handle($mh, $ch);
		$handles[] = $ch;
	}
	do {
		$status = curl_multi_exec($mh, $active);
		if ($active) {
			curl_multi_select($mh);
		}
	} while ($status === CURLM_CALL_MULTI_PERFORM || $active);
	$results = [];
	foreach ($handles as $ch) {
		$results[] = curl_multi_getcontent($ch);
	}
	foreach ($handles as $ch) {
		curl_multi_remove_handle($mh, $ch);
		curl_close($ch);
	}
	curl_multi_close($mh);

	/******** 타사이트로 전송 ************/











	$pay_types = "Y";


	$pay_datetime =  date("Y-m-d H:i:s", strtotime($approval_ymdhms));
//	$calday =  date("Ymd", strtotime($trxDate));


	// 취소
	if($cancel_ymdhms > 0) {
		$pay_types = "N";
		$amount = "-".$amount; // 음수로 변경

		// 원거래
		$cancel = sql_fetch("select * from g5_payment_welcom where trxid = '{$transaction_no}'");

		// 취소일때 데이터 원거래에서 가져오기
		$pay_datetime =  date("Y-m-d H:i:s", strtotime($cancel_ymdhms));
		$pay_cdatetime =  date("Y-m-d H:i:s", strtotime($cancel_ymdhms));

		sql_query("update g5_payment set pay_cdatetime = '{$pay_cdatetime}' where trxId = '{$transaction_no}'");
		$transaction_no = "C".$transaction_no;

	}

	$sql_common = " mid ='{$mid}',
					pay_type ='{$pay_type}',
					bank_code ='{$bank_code}',
					transaction_flag ='{$transaction_flag}',
					order_no ='{$order_no}',
					transaction_no ='{$transaction_no}',
					approval_ymdhms ='{$approval_ymdhms}',
					cancel_ymdhms ='{$cancel_ymdhms}',
					amount ='{$amount}',
					remain_amount ='{$remain_amount}',
					user_id ='{$user_id}',
					user_name ='{$user_name}',
					product_code ='{$product_code}',
					product_name ='{$product_name}',
					approval_no ='{$approval_no}',
					card_sell_mm ='{$card_sell_mm}',
					account_no ='{$account_no}',
					deposit_ymdhms ='{$deposit_ymdhms}',
					deposit_amount ='{$deposit_amount}',
					deposit_name ='{$deposit_name}',
					cash_seq ='{$cash_seq}',
					cash_approval_no ='{$cash_approval_no}',
					bank_name ='{$bank_name}',
					millis ='{$millis}',
					hash_value ='{$hash_value}',
					org_pg_seq_no ='{$org_pg_seq_no}',
					org_pg_cancel_seq_no ='{$org_pg_cancel_seq_no}',
					echo ='{$echo}', ";

	$sql = "insert into g5_payment_welcom set ".$sql_common." datetime = '".G5_TIME_YMDHIS."'";
	sql_query($sql);

	$row2 = sql_fetch("select * from g5_device where dv_tid = '{$product_code}'");

	$mb_1_fee = number_format($row2['mb_1_fee'], 2);
	$mb_2_fee = number_format($row2['mb_2_fee'], 2);
	$mb_3_fee = number_format($row2['mb_3_fee'], 2);
	$mb_4_fee = number_format($row2['mb_4_fee'], 2);
	$mb_5_fee = number_format($row2['mb_5_fee'], 2);
	$mb_6_fee = number_format($row2['mb_6_fee'], 2);


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



	$sql_common = " pay_type = '{$pay_types}',
					pay = '{$amount}',
					pay_num = '{$approval_no}',
					trxid = '{$transaction_no}',
					trackId = '{$order_no}',
					pay_datetime = '{$pay_datetime}',
					pay_cdatetime = '{$pay_cdatetime}',
					pay_parti = '{$card_sell_mm}',
					pay_card_name = '{$bank_name}',
					pay_card_num = '',

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
					dv_tid = '{$product_code}',
					dv_tid_ori = '{$dv_tid_ori}',
					pg_name = 'welcom' ";


	$pay = sql_fetch("select * from g5_payment where trxid = '{$transaction_no}' and pay_num = '{$approval_no}'");

	if(!$pay['pay_id']) {
		$sql = " insert into g5_payment set ".$sql_common.", datetime = '".G5_TIME_YMDHIS."'";
		sql_query($sql);
	}

	print_r("0000 SUCCESS");
	
	
	
	// 추가 API 호출
	/*
	$notification_url = 'https://api.wannapayments.kr/api/v1/payment/notification/welcomepayments';
	
	$notification_data = array(
		'mid' => $mid,
		'pay_type' => $pay_type,
		'bank_code' => $bank_code,
		'transaction_flag' => $transaction_flag,
		'order_no' => $order_no,
		'transaction_no' => $transaction_no,
		'approval_ymdhms' => $approval_ymdhms,
		'cancel_ymdhms' => $cancel_ymdhms,
		'amount' => $amount,
		'remain_amount' => $remain_amount,
		'user_id' => $user_id,
		'user_name' => $user_name,
		'product_code' => $product_code,
		'product_name' => $product_name,
		'approval_no' => $approval_no,
		'card_sell_mm' => $card_sell_mm,
		'account_no' => $account_no,
		'deposit_ymdhms' => $deposit_ymdhms,
		'deposit_amount' => $deposit_amount,
		'deposit_name' => $deposit_name,
		'cash_seq' => $cash_seq,
		'cash_approval_no' => $cash_approval_no,
		'bank_name' => $bank_name
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
				error_log("welcom>>".$notification_result);
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
	
} else {

	// 해쉬검증 실패
	// 검증 실패에 대한 내부 로직 수행
	print_r("9999 HASH_FAIL");
}
?>