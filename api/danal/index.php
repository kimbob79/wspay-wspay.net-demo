<?php
	error_reporting( E_ALL );
	ini_set( "display_errors", 1 );
	date_default_timezone_set('Asia/Seoul');

	// ========================================
	// 요청 로깅 - /logs/trans/api/danal
	// ========================================
	$log_dir = dirname(__FILE__) . '/../../logs/trans/api/danal';
	if(!is_dir($log_dir)) {
		@mkdir($log_dir, 0755, true);
	}

	$log_file = $log_dir . '/' . date('Y-m-d') . '.log';
	$log_time = date('Y-m-d H:i:s');
	$log_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
	$log_method = $_SERVER['REQUEST_METHOD'] ?? 'unknown';

	// 요청 데이터 수집
	$log_data = [
		'timestamp' => $log_time,
		'ip' => $log_ip,
		'method' => $log_method,
		'GET' => $_GET,
		'POST' => $_POST,
		'REQUEST' => $_REQUEST,
		'raw_input' => file_get_contents('php://input')
	];

	// 로그 기록
	$log_entry = "[{$log_time}] [{$log_ip}] [{$log_method}]\n";
	$log_entry .= json_encode($log_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
	$log_entry .= str_repeat('-', 80) . "\n";

	@file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
	// ========================================

	include('./_common.php');

	/*

QUOTA=00&								// 할부개월수
TRANTIME=090328&						// 매출발생시간 HHmmss
CARDNAME=%EC%8B%A0%ED%95%9C&			// 카드사 명
TXTYPE=BILL&							// (승인)"BILL", (취소)"CANCEL"
TID=202306300903263976773400&			// 다날 거래 키
TRANDATE=20230630&						// 매출발생일자 yyyyMMdd
ORDERID=AXD_1688083406104&				// 오프라인 거래 고유번호
CAT_ID=WGP003209&						// CAT 단말 기기 터미널 ID
AMOUNT=1004&							// 승인금액(총 금액)
CPID=A010012968&						// CPID
CARDAUTHNO=99999999&					// 거래 승인 번호
O_TID&									// 다날 원거래 키
ITEMNAME=%ED%85%8C%EC%8A%A4%ED%8A%B8&	// CAT 단말 기기에서 설정한 상품 코드
CATID=WGP003209&						// CAT 단말 기기 터미널 ID
CARDNO=9999%2A%2A%2A%2A%2A%2A%2A%2A9999	// "카드번호(1111-11**-****-1111 마스킹 처리)


QUOTA=00&								// 할부개월수
TRANTIME=090328&						// 매출발생시간 HHmmss
CARDNAME=%EC%8B%A0%ED%95%9C&			// 카드사 명
TXTYPE=BILL&							// (승인)"BILL", (취소)"CANCEL"
TID=202306300903263976773400&			// 다날 거래 키
TRANDATE=20230630&						// 매출발생일자 yyyyMMdd
ORDERID=AXD_1688083406104&				// 오프라인 거래 고유번호
CAT_ID=WGP003209&						// CAT 단말 기기 터미널 ID
AMOUNT=1004&							// 승인금액(총 금액)
CPID=A010012968&						// CPID
CARDAUTHNO=99999999&					// 거래 승인 번호
O_TID&									// 다날 원거래 키
ITEMNAME=%ED%85%8C%EC%8A%A4%ED%8A%B8&	// CAT 단말 기기에서 설정한 상품 코드
CATID=WGP003209&						// CAT 단말 기기 터미널 ID
CARDNO=9999%2A%2A%2A%2A%2A%2A%2A%2A9999	// "카드번호(1111-11**-****-1111 마스킹 처리)

CPID	CPID
O_TID	다날 원거래 키
TID	다날 거래 키
ORDERID	오프라인 거래 고유번호
ITEMNAME	CAT 단말 기기에서 설정한 상품 코드
AMOUNT	승인금액(총 금액)
TRANDATE	승인 또는 취소 시 매출발생일자 (yyyyMMdd)
TRANTIME	승인 또는 취소 시 매출발생시간 (HHmmss)
CATID	CAT 단말 기기 터미널 ID
CARDNAME	카드사 명
CARDNO	"카드번호(1111-11**-****-1111 마스킹 처리)
삼성페이일 경우 전달되지 않음"
QUOTA	할부 개월 수 (일시불: "00", 할부: "02~"36")
CARDAUTHNO	거래 승인 번호
TXTYPE	(승인)"BILL", (취소)"CANCEL"
CAT_ID	CAT 단말 기기 터미널 ID


	http://mpc.icu/api/danal/index.php?QUOTA=00&TRANTIME=090328&CARDNAME=%EC%8B%A0%ED%95%9C&TXTYPE=BILL&TID=202306300903263976773400&TRANDATE=20230630&ORDERID=AXD_1688083406104&CAT_ID=WGP003209&AMOUNT=1004&CPID=A010012968&CARDAUTHNO=99999999&O_TID&ITEMNAME=%ED%85%8C%EC%8A%A4%ED%8A%B8&CATID=WGP003209&CARDNO=9999%2A%2A%2A%2A%2A%2A%2A%2A9999
	
	
	
	
	(총 금액)
	승인 또는 취소 시  (yyyyMMdd)
	승인 또는 취소 시  (HHmmss)
	
	
	"(1111-11**-****-1111 마스킹 처리)
삼성페이일 경우 전달되지 않음"
	 (일시불: "00", 할부: "02~"36")
	거래 
	
	

	*/

$QUOTA = isset($_REQUEST['QUOTA']) ? trim($_REQUEST['QUOTA']) : '';							// 할부개월수
$TRANTIME = isset($_REQUEST['TRANTIME']) ? trim($_REQUEST['TRANTIME']) : '';							// 매출발생시간 HHmmss
$CARDNAME = isset($_REQUEST['CARDNAME']) ? trim($_REQUEST['CARDNAME']) : '';							// 카드사 명
$TXTYPE = isset($_REQUEST['TXTYPE']) ? trim($_REQUEST['TXTYPE']) : '';							// (승인)"BILL", (취소)"CANCEL"
$TID = isset($_REQUEST['TID']) ? trim($_REQUEST['TID']) : '';							// 다날 거래 키
$TRANDATE = isset($_REQUEST['TRANDATE']) ? trim($_REQUEST['TRANDATE']) : '';							// 매출발생일자 yyyyMMdd
$ORDERID = isset($_REQUEST['ORDERID']) ? trim($_REQUEST['ORDERID']) : '';							// 오프라인 거래 고유번호
$CAT_ID = isset($_REQUEST['CAT_ID']) ? trim($_REQUEST['CAT_ID']) : '';							// CAT 단말 기기 터미널 ID
$AMOUNT = isset($_REQUEST['AMOUNT']) ? trim($_REQUEST['AMOUNT']) : '';							// 승인금액(총 금액)
$CPID = isset($_REQUEST['CPID']) ? trim($_REQUEST['CPID']) : '';							// CPID
$CARDAUTHNO = isset($_REQUEST['CARDAUTHNO']) ? trim($_REQUEST['CARDAUTHNO']) : '';							// 거래 승인 번호
$O_TID = isset($_REQUEST['O_TID']) ? trim($_REQUEST['O_TID']) : '';							// 다날 원거래 키
$ITEMNAME = isset($_REQUEST['ITEMNAME']) ? trim($_REQUEST['ITEMNAME']) : '';							// CAT 단말 기기에서 설정한 상품 코드
$CATID = isset($_REQUEST['CATID']) ? trim($_REQUEST['CATID']) : '';							// CAT 단말 기기 터미널 ID
$CARDNO = isset($_REQUEST['CARDNO']) ? trim($_REQUEST['CARDNO']) : '';							// 카드번호

$appDtm = $TRANDATE." ".$TRANTIME;
if($CPID) {

	/******** 외부 전송 코드 삭제됨 - API_EXTERNAL_TRANSMISSION_REMOVED.md 참고 ************/


	$pay_type = "Y";
	$pay_cdatetime = "";

	// 취소
	if($TXTYPE == "CANCEL") {
		$pay_type = "N";
		$tid2 = $TID;
		$tid = "c".$TID;
//		$AMOUNT = abs($AMOUNT); // 양수로 변경

		// 원거래
		$cancel = sql_fetch("select * from g5_payment_danal where tid = '{$tid2}'");

		// 취소일때 데이터 원거래에서 가져오기
		$pay_cdatetime =  date("Y-m-d H:i:s", strtotime($appDtm));
		/*
		$issuer = $cancel['issuer'];
		$acquirer = $cancel['acquirer'];
		$cardType = $cancel['cardType'];
		$bin = $cancel['bin'];
		$last4 = $cancel['last4'];
		$authCd = $cancel['authCd'];
		*/
		// 승인건 취소로 변경
		sql_query("update g5_payment set pay_cdatetime = '{$pay_cdatetime}' where trxId = '{$O_TID}'");
	}


	$sql_common = " CPID ='{$CPID}',
					O_TID ='{$O_TID}',
					TID ='{$TID}',
					ORDERID ='{$ORDERID}',
					ITEMNAME ='{$ITEMNAME}',
					AMOUNT ='{$AMOUNT}',
					TRANDATE ='{$TRANDATE}',
					TRANTIME ='{$TRANTIME}',
					CATID ='{$CATID}',
					CARDNAME ='{$CARDNAME}',
					CARDNO ='{$CARDNO}',
					QUOTA ='{$QUOTA}',
					CARDAUTHNO ='{$CARDAUTHNO}',
					TXTYPE ='{$TXTYPE}',
					CAT_ID ='{$CAT_ID}', ";

	$sql = "insert into g5_payment_danal set ".$sql_common." datetime = '".G5_TIME_YMDHIS."'";
	sql_query($sql);

	$row2 = sql_fetch("select * from g5_device where dv_tid = '{$CATID}'");

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



	$mb_1_pay = $AMOUNT * $row2['mb_1_fee'] /100;
	$mb_2_pay = $AMOUNT * $row2['mb_2_fee'] /100;
	$mb_3_pay = $AMOUNT * $row2['mb_3_fee'] /100;
	$mb_4_pay = $AMOUNT * $row2['mb_4_fee'] /100;
	$mb_5_pay = $AMOUNT * $row2['mb_5_fee'] /100;
	$mb_6_pay = $AMOUNT * $row2['mb_6_fee'] /100;
	$mb_6_pay = $AMOUNT - $mb_6_pay;




	$pay_datetime =  date("Y-m-d H:i:s", strtotime($appDtm));
//	$calday =  date("Ymd", strtotime($trxDate));


	$sql_common = " pay_type = '{$pay_type}',
					pay = '{$AMOUNT}',
					pay_num = '{$CARDAUTHNO}',
					trxid = '{$TID}',
					trackId = '{$ORDERID}',
					pay_datetime = '{$pay_datetime}',
					pay_cdatetime = '{$pay_cdatetime}',
					pay_parti = '{$QUOTA}',
					pay_card_name = '{$CARDNAME}',
					pay_card_num = '{$CARDNO}',

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
					dv_tid = '{$CATID}',
					pg_name = 'danal' ";


//	$pay = sql_fetch("select * from g5_payment where trxid = '{$tid}' and pay_num = '{$appNo}'");

	if(!$pay['pay_id']) {
		$sql = " insert into g5_payment set ".$sql_common.", datetime = '".G5_TIME_YMDHIS."'";
		sql_query($sql);
	}
}