<?php
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
$org_pg_seq_no  = $object['org_pg_seq_no'];				//상위PG 거래 고유번호
$org_pg_cancel_seq_no  = $object['org_pg_cancel_seq_no'];	//상위PG 취소거래 고유번호
$echo  = $object['echo'];									//결제 승인 요청시 설정된 에코 값


// 서버(DB)로부터 키값을 가져와야합니다.

if($mid == "kowelcome227") {
	$api_key = "5651b42b07c5340e6158df67f57f866c"; // 실제키값으로 변경필요.
} else if($mid == "kowelcome227") {
	$api_key = "wel000695m";
}


// 검증값 생성
$hash_value_2 = hash("sha256", $mid.$order_no.$millis.$api_key);


if ($hash_value == $hash_value_2) {


	/******** 타사이트로 전송 ************/
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
	/******** 타사이트로 전송 ************/


	$pay_types = "Y";

	// 취소
	if($cancel_ymdhms > 0) {
		$pay_types = "N";
		$amount = "-".$amount; // 음수로 변경

		// 원거래
		$cancel = sql_fetch("select * from g5_payment_welcom where trxid = '{$transaction_no}'");

		// 취소일때 데이터 원거래에서 가져오기
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


	$pay_datetime =  date("Y-m-d H:i:s", strtotime($approval_ymdhms));
//	$calday =  date("Ymd", strtotime($trxDate));


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
					pg_name = 'welcom' ";


	$pay = sql_fetch("select * from g5_payment where trxid = '{$transaction_no}' and pay_num = '{$approval_no}'");

	if(!$pay['pay_id']) {
		$sql = " insert into g5_payment set ".$sql_common.", datetime = '".G5_TIME_YMDHIS."'";
		sql_query($sql);
	}

	print_r("0000 SUCCESS");

} else {

	// 해쉬검증 실패
	// 검증 실패에 대한 내부 로직 수행
	print_r("9999 HASH_FAIL");
}