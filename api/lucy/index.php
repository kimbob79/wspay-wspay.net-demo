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

$mid  = $object['mid'];								// 상점아이디 - LUCY 상점아이디
$order_no  = $object['order_no'];					// 주문번호 - 가맹점 고유 주문번호(중복 불가). 예: 08913320250830195833
$pay_method  = $object['pay_method'];				// 결제수단 - 예: CARD_READER (단말기), MANUAL(수기), AUTH(인증) 등
$status  = $object['status'];						// 승인상태 - APPROVED(승인) / CANCELED(취소) / PARTIAL(부분취소)
$cat_id  = $object['cat_id'];						// 단말기ID - 카드단말(TID)
$auth_no  = $object['auth_no'];						// 승인번호 - 카드사 승인번호. 예: 0xxxxxx32
$tid  = $object['tid'];								// 내부 거래번호 - 당사 거래 TID(채널 기준). 예: kkay23951m0103...
$approve_amount  = $object['approve_amount'];		// 승인금액 - 승인된 결제금액(문자열 숫자). 예: 100
$cancel_amount  = $object['cancel_amount'];			// 취소금액 - 누적 취소금액. 승인 직후엔 0
$left_amount  = $object['left_amount'];				// 잔액 - 남은 금액 = approve_amount - cancel_amount
$card_no  = $object['card_no'];						// 카드번호(마스킹) - 예: 52881500****190*
$card_code  = $object['card_code'];					// 발급사 코드 - 예: 07
$card_name  = $object['card_name'];					// 발급사명 - 예: 현대
$quota  = $object['quota'];							// 할부개월 - 일시불 00, 할부 02,03…
$approve_date  = $object['approve_date'];			// 승인일자 - YYYYMMDD. 예: 20250830
$approve_time  = $object['approve_time'];			// 승인시간 - HHMMSS. 예: 195833
$cancel_date  = $object['cancel_date'];				// 취소일자 - 취소 시 YYYYMMDD, 없으면 빈 문자열
$cancel_time  = $object['cancel_time'];				// 취소시간 - 취소 시 HHMMSS, 없으면 빈 문자열
$reserved_index_1  = $object['reserved_index_1'];	// 예약메세지
$user_name  = $object['user_name'];					// 구매자명
$user_phone  = $object['user_phone'];				// 구매자 전화번호 - 구매자 전화번호(010xxxx0000)
$product_name  = $object['product_name'];			// 상품명

$amount = $approve_amount;


if ($mid) {


	/******** 타사이트로 전송 ************

	$urls = [
		'http://redpay.kr/api/lucy/index.php'
	];

	
	$data = array('mid' => $mid, 'order_no' => $order_no, 'pay_method' => $pay_method, 'status' => $status, 'cat_id' => $cat_id, 'auth_no' => $auth_no, 'tid' => $tid, 'approve_amount' => $approve_amount, 'cancel_amount' => $cancel_amount, 'left_amount' => $left_amount, 'card_no' => $card_no, 'card_code' => $card_code, 'card_name' => $card_name, 'quota' => $quota, 'approve_date' => $approve_date, 'approve_time' => $approve_time, 'cancel_date' => $cancel_date, 'cancel_time' => $cancel_time, 'reserved_index_1' => $reserved_index_1, 'user_name' => $user_name, 'user_phone' => $user_phone, 'product_name' => $product_name);

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

	******** 타사이트로 전송 ************/



	$pay_types = "Y";

	$pay_datetime = $approve_date." ".$approve_time;
	$pay_datetime =  date("Y-m-d H:i:s", strtotime($pay_datetime));


	// 취소
	if($status != 'APPROVED') {
		$pay_types = "N";
		$amount = "-".$cancel_amount;

		// 원거래
		$cancel = sql_fetch("select * from g5_payment_lucy where tid = '{$tid}'");
		$pay_datetime = $cancel['approve_date']." ".$cancel['approve_time'];
		$pay_cdatetime = $approve_date." ".$approve_time;
		// 취소일때 데이터 원거래에서 가져오기
		$pay_datetime =  date("Y-m-d H:i:s", strtotime($cancel_ymdhms));
		$pay_cdatetime =  date("Y-m-d H:i:s", strtotime($pay_cdatetime));

		sql_query("update g5_payment set pay_cdatetime = '{$pay_cdatetime}' where trxId = '{$tid}'");
	}

	$sql_common = " mid ='{$mid}',
					order_no ='{$order_no}',
					pay_method ='{$pay_method}',
					status ='{$status}',
					cat_id ='{$cat_id}',
					auth_no ='{$auth_no}',
					tid ='{$tid}',
					approve_amount ='{$approve_amount}',
					cancel_amount ='{$cancel_amount}',
					left_amount ='{$left_amount}',
					card_no ='{$card_no}',
					card_code ='{$card_code}',
					card_name ='{$card_name}',
					quota ='{$quota}',
					approve_date ='{$approve_date}',
					approve_time ='{$approve_time}',
					cancel_date ='{$cancel_date}',
					cancel_time ='{$cancel_time}',
					reserved_index_1 ='{$reserved_index_1}',
					user_name ='{$user_name}',
					user_phone ='{$user_phone}',
					product_name ='{$product_name}',";

	$sql = "insert into g5_payment_lucy set ".$sql_common." datetime = '".G5_TIME_YMDHIS."'";
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
					pay_num = '{$auth_no}',
					trxid = '{$tid}',
					trackId = '{$order_no}',
					pay_datetime = '{$pay_datetime}',
					pay_cdatetime = '{$pay_cdatetime}',
					pay_parti = '{$quota}',
					pay_card_name = '{$card_name}',
					pay_card_num =  '{$card_no}',

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
					pg_name = 'lucy' ";


	$pay = sql_fetch("select * from g5_payment where trxid = '{$tid}' and pay_num = '{$auth_no}'");

	if(!$pay['pay_id']) {
		$sql = " insert into g5_payment set ".$sql_common.", datetime = '".G5_TIME_YMDHIS."'";
		sql_query($sql);
	}

	print_r("OK");
	
	
	
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
	
}
?>