<?php
	error_reporting( E_ALL );
	ini_set( "display_errors", 1 );

	// ========================================
	// 요청 로깅 - /logs/trans/api/paysis
	// ========================================
	$log_dir = dirname(__FILE__) . '/../../logs/trans/api/paysis';
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

$gid = isset($_REQUEST['gid']) ? trim($_REQUEST['gid']) : '';							// 그룹 ID
$vid = isset($_REQUEST['vid']) ? trim($_REQUEST['vid']) : '';							// VAN ID
$mid = isset($_REQUEST['mid']) ? trim($_REQUEST['mid']) : '';							// 상점 ID 
$payMethod = isset($_REQUEST['payMethod']) ? trim($_REQUEST['payMethod']) : '';			// 결제수단 
$appCardCd = isset($_REQUEST['appCardCd']) ? trim($_REQUEST['appCardCd']) : '';			// 카드코드/은행코드/ 상품권사코드/ 휴대폰코드 
$cancelYN  = isset($_REQUEST['cancelYN']) ? trim($_REQUEST['cancelYN']) : '';			// 취소구분
$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';							// 거래고유번호
$ediNo  = isset($_REQUEST['ediNo']) ? trim($_REQUEST['ediNo']) : '';					// VAN거래고유번호
$appDtm = isset($_REQUEST['appDtm']) ? trim($_REQUEST['appDtm']) : '';					// 승인일
$ccDnt  = isset($_REQUEST['ccDnt']) ? trim($_REQUEST['ccDnt']) : '';					// 취소일 
$amt = isset($_REQUEST['amt']) ? trim($_REQUEST['amt']) : '';							// 금액 
$remainAmt = isset($_REQUEST['remainAmt']) ? trim($_REQUEST['remainAmt']) : '';			// 잔액 
$buyerId = isset($_REQUEST['buyerId']) ? trim($_REQUEST['buyerId']) : '';				// 구매자 ID 
$ordNm = isset($_REQUEST['ordNm']) ? trim($_REQUEST['ordNm']) : '';						// 구매자명 
$ordNo = isset($_REQUEST['ordNo']) ? trim($_REQUEST['ordNo']) : '';						// 주문번호 
$goodsName  = isset($_REQUEST['goodsName']) ? trim($_REQUEST['goodsName']) : '';		// 상품명 
$appNo = isset($_REQUEST['appNo']) ? trim($_REQUEST['appNo']) : '';						// 승인번호 
$quota = isset($_REQUEST['quota']) ? trim($_REQUEST['quota']) : '';						// 할부개월 
$notiDnt  = isset($_REQUEST['notiDnt']) ? trim($_REQUEST['notiDnt']) : '';			// Noti 통보일 
$cardNo = isset($_REQUEST['cardNo']) ? trim($_REQUEST['cardNo']) : '';					// 카드번호
$catId = isset($_REQUEST['catId']) ? trim($_REQUEST['catId']) : '';						// 단말기 CAT_ID
$tPhone = isset($_REQUEST['tPhone']) ? trim($_REQUEST['tPhone']) : '';					// phone 번호 입력 사항
$connCd = isset($_REQUEST['connCd']) ? trim($_REQUEST['connCd']) : '';					// 단말기/수기결제 구분 (0003: 오프라인, 0005: 수기결제)
$fnNm = isset($_REQUEST['fnNm']) ? trim($_REQUEST['fnNm']) : '';						// 카드사명 (수기결제용)
$cashCrctFlg = isset($_REQUEST['cashCrctFlg']) ? trim($_REQUEST['cashCrctFlg']) : '';	// 현금영수증 플래그
$acqCardCd = isset($_REQUEST['acqCardCd']) ? trim($_REQUEST['acqCardCd']) : '';			// 매입카드코드
$lmtDay = isset($_REQUEST['lmtDay']) ? trim($_REQUEST['lmtDay']) : '';					// 한도일
$hashStr = isset($_REQUEST['hashStr']) ? trim($_REQUEST['hashStr']) : '';				// 해시값
$ediDate = isset($_REQUEST['ediDate']) ? trim($_REQUEST['ediDate']) : '';				// EDI 날짜
$usePointAmt = isset($_REQUEST['usePointAmt']) ? trim($_REQUEST['usePointAmt']) : '';	// 포인트 사용금액
$authType = isset($_REQUEST['authType']) ? trim($_REQUEST['authType']) : '';			// 인증타입
$charSet = isset($_REQUEST['charSet']) ? trim($_REQUEST['charSet']) : '';				// 문자셋
$resultCd = isset($_REQUEST['resultCd']) ? trim($_REQUEST['resultCd']) : '';			// 결과코드
$cardType = isset($_REQUEST['cardType']) ? trim($_REQUEST['cardType']) : '';			// 카드타입
$resultMsg = isset($_REQUEST['resultMsg']) ? trim($_REQUEST['resultMsg']) : '';			// 결과메시지
$vacntNo = isset($_REQUEST['vacntNo']) ? trim($_REQUEST['vacntNo']) : '';				// 가상계좌번호
$socHpNo = isset($_REQUEST['socHpNo']) ? trim($_REQUEST['socHpNo']) : '';



if($payMethod) {

	/******** 외부 전송 코드 삭제됨 - API_EXTERNAL_TRANSMISSION_REMOVED.md 참고 ************/

	$pay_type = "Y";
	$pay_cdatetime = "";

	// 취소
	if($cancelYN == "Y") {
		$pay_type = "N";
		$tid2 = $tid;
		$tid = "c".$tid;

		// 원거래
		$cancel = sql_fetch("select * from g5_payment_paysis where tid = '{$tid2}'");

		// 취소일때 데이터 원거래에서 가져오기
		$pay_cdatetime =  date("Y-m-d H:i:s", strtotime($ccDnt));
		$appDtm = $ccDnt;
		/*
		$issuer = $cancel['issuer'];
		$acquirer = $cancel['acquirer'];
		$cardType = $cancel['cardType'];
		$bin = $cancel['bin'];
		$last4 = $cancel['last4'];
		$authCd = $cancel['authCd'];
		*/
		$amt = "-".$cancel['amt']; // 음수로 변경
		sql_query("update g5_payment set pay_cdatetime = '{$pay_cdatetime}' where trxId = '{$tid2}'");
	}

	$sql_common = " gid ='{$gid}',
					vid ='{$vid}',
					mid ='{$mid}',
					payMethod ='{$payMethod}',
					appCardCd ='{$appCardCd}',
					cancelYN  ='{$cancelYN }',
					tid ='{$tid}',
					ediNo  ='{$ediNo }',
					appDtm ='{$appDtm}',
					ccDnt  ='{$ccDnt }',
					amt ='{$amt}',
					remainAmt ='{$remainAmt}',
					buyerId ='{$buyerId}',
					ordNm ='{$ordNm}',
					ordNo ='{$ordNo}',
					goodsName  ='{$goodsName }',
					appNo ='{$appNo}',
					quota ='{$quota}',
					notiDnt  ='{$notiDnt }',
					cardNo ='{$cardNo}',
					catId ='{$catId}',
					tPhone ='{$tPhone}',
					connCd ='{$connCd}',
					fnNm ='{$fnNm}',
					cashCrctFlg ='{$cashCrctFlg}',
					acqCardCd ='{$acqCardCd}',
					lmtDay ='{$lmtDay}',
					hashStr ='{$hashStr}',
					ediDate ='{$ediDate}',
					usePointAmt ='{$usePointAmt}',
					authType ='{$authType}',
					charSet ='{$charSet}',
					resultCd ='{$resultCd}',
					cardType ='{$cardType}',
					resultMsg ='{$resultMsg}',
					vacntNo ='{$vacntNo}',
					socHpNo ='{$socHpNo}', ";

	$sql = "insert into g5_payment_paysis set ".$sql_common." datetime = '".G5_TIME_YMDHIS."'";
	sql_query($sql);
	$paysis_insert_id = sql_insert_id();

	// 동기화 상태 변수 초기화
	$sync_status = 'pending';
	$sync_message = '';

	// ========================================
	// 수기결제 (connCd = 0005)
	// ========================================
	if($connCd == '0005') {
		// 주문번호 앞 4자리에서 mkc_oid 추출
		$mkc_oid = substr($ordNo, 0, 4);

		// mkc_oid로 디바이스 및 수수료 정보 조회
		$row2 = sql_fetch("SELECT d.*, d.mb_6 as merchant_mb_id
			FROM g5_device d
			WHERE d.mb_6 = (
				SELECT a.mb_id
				FROM g5_member a
				JOIN g5_member_keyin_config b ON a.mb_id = b.mb_id
				WHERE b.mkc_oid = '{$mkc_oid}'
			)
			LIMIT 1");

		// 디바이스 조회 실패 체크
		if(!$row2['dv_id']) {
			$sync_status = 'failed';
			$sync_message = "mkc_oid '{$mkc_oid}' not found";
		}

		$mb_1_fee = $row2['mb_1_fee'];
		$mb_2_fee = $row2['mb_2_fee'];
		$mb_3_fee = $row2['mb_3_fee'];
		$mb_4_fee = $row2['mb_4_fee'];
		$mb_5_fee = $row2['mb_5_fee'];
		$mb_6_fee = $row2['mb_6_fee'];

		// 수수료 계산 (기존 오프라인 로직과 동일)
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

		$mb_1_pay = $amt * $row2['mb_1_fee'] /100;
		$mb_2_pay = $amt * $row2['mb_2_fee'] /100;
		$mb_3_pay = $amt * $row2['mb_3_fee'] /100;
		$mb_4_pay = $amt * $row2['mb_4_fee'] /100;
		$mb_5_pay = $amt * $row2['mb_5_fee'] /100;
		$mb_6_pay = $amt * $row2['mb_6_fee'] /100;
		$mb_6_pay = $amt - $mb_6_pay;

		$pay_datetime = date("Y-m-d H:i:s", strtotime($appDtm));

		// 수기결제는 fnNm을 카드사명으로 사용
		$pay_card_name = $fnNm;

		$sql_common_keyin = " pay_type = '{$pay_type}',
						pay = '{$amt}',
						pay_num = '{$appNo}',
						trxid = '{$tid}',
						trackId = '{$ordNo}',
						pay_datetime = '{$pay_datetime}',
						pay_cdatetime = '{$pay_cdatetime}',
						pay_parti = '{$quota}',
						pay_card_name = '{$pay_card_name}',
						pay_card_num = '{$cardNo}',

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
						dv_tid = '{$row2['dv_tid']}',
						dv_tid_ori = '',
						pg_name = 'paysis_keyin' ";

		$pay = sql_fetch("select * from g5_payment where trxid = '{$tid}' and pay_num = '{$appNo}'");

		if(!$pay['pay_id']) {
			if($sync_status != 'failed') {
				$sql = " insert into g5_payment set ".$sql_common_keyin.", datetime = '".G5_TIME_YMDHIS."'";
				$insert_result = sql_query($sql);
				if($insert_result) {
					$sync_status = 'success';
					$sync_message = '';
				} else {
					$sync_status = 'failed';
					$sync_message = 'g5_payment insert failed';
				}
			}
		} else {
			$sync_status = 'success';
			$sync_message = 'already exists';
		}
	}
	// ========================================
	// 오프라인 단말기 결제 (connCd = 0003 또는 기타)
	// ========================================
	else {
		$arraydata = explode(PHP_EOL, trim($config['cf_2']));
		$arraydata = array_map('trim', $arraydata);

		if(in_array($catId, $arraydata)) {
			$catId = substr($ordNo, 0, -10);
			$dv_tid_ori = $catId;
		}

		/*
		// tid 쪼개기
		if($catId == $config['cf_7']) {
			$catId = substr($ordNo, 0, -10);
			$dv_tid_ori = $config['cf_7'];
		}
		*/


		$row2 = sql_fetch("select * from g5_device where dv_tid = '{$catId}'");

		// 디바이스 조회 실패 체크
		if(!$row2['dv_id']) {
			$sync_status = 'failed';
			$sync_message = "device '{$catId}' not found";
		}

		$mb_1_fee = $row2['mb_1_fee'];
		$mb_2_fee = $row2['mb_2_fee'];
		$mb_3_fee = $row2['mb_3_fee'];
		$mb_4_fee = $row2['mb_4_fee'];
		$mb_5_fee = $row2['mb_5_fee'];
		$mb_6_fee = $row2['mb_6_fee'];


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



		$mb_1_pay = $amt * $row2['mb_1_fee'] /100;
		$mb_2_pay = $amt * $row2['mb_2_fee'] /100;
		$mb_3_pay = $amt * $row2['mb_3_fee'] /100;
		$mb_4_pay = $amt * $row2['mb_4_fee'] /100;
		$mb_5_pay = $amt * $row2['mb_5_fee'] /100;
		$mb_6_pay = $amt * $row2['mb_6_fee'] /100;
		$mb_6_pay = $amt - $mb_6_pay;




		$pay_datetime =  date("Y-m-d H:i:s", strtotime($appDtm));
	//	$calday =  date("Ymd", strtotime($trxDate));



		if($appCardCd == "01") { $appCardCd = "비씨";
		} else if($appCardCd == "02") { $appCardCd = "국민";
		} else if($appCardCd == "03") { $appCardCd = "하나(구외환)";
		} else if($appCardCd == "04") { $appCardCd = "삼성";
		} else if($appCardCd == "06") { $appCardCd = "신한";
		} else if($appCardCd == "07") { $appCardCd = "현대";
		} else if($appCardCd == "08") { $appCardCd = "롯데";
		} else if($appCardCd == "09") { $appCardCd = "한미";
		} else if($appCardCd == "10") { $appCardCd = "신세계한미";
		} else if($appCardCd == "11") { $appCardCd = "씨티";
		} else if($appCardCd == "12") { $appCardCd = "NH농협카드";
		} else if($appCardCd == "13") { $appCardCd = "수협";
		} else if($appCardCd == "14") { $appCardCd = "평화";
		} else if($appCardCd == "15") { $appCardCd = "우리";
		} else if($appCardCd == "16") { $appCardCd = "하나";
		} else if($appCardCd == "17") { $appCardCd = "동남(주택)";
		} else if($appCardCd == "18") { $appCardCd = "주택";
		} else if($appCardCd == "19") { $appCardCd = "조흥(강원)";
		} else if($appCardCd == "20") { $appCardCd = "축협(농협)";
		} else if($appCardCd == "21") { $appCardCd = "광주";
		} else if($appCardCd == "22") { $appCardCd = "전북";
		} else if($appCardCd == "23") { $appCardCd = "제주";
		} else if($appCardCd == "24") { $appCardCd = "산은";
		} else if($appCardCd == "25") { $appCardCd = "해외비자";
		} else if($appCardCd == "26") { $appCardCd = "해외마스터";
		} else if($appCardCd == "27") { $appCardCd = "해외다이너스";
		} else if($appCardCd == "28") { $appCardCd = "해외AMX";
		} else if($appCardCd == "29") { $appCardCd = "해외JCB";
		} else if($appCardCd == "30") { $appCardCd = "해외";
		} else if($appCardCd == "31") { $appCardCd = "SK-OKCashBag";
		} else if($appCardCd == "32") { $appCardCd = "우체국";
		} else if($appCardCd == "33") { $appCardCd = "MG새마을체크";
		} else if($appCardCd == "34") { $appCardCd = "중국은행체크";
		} else if($appCardCd == "38") { $appCardCd = "은련";
		} else if($appCardCd == "46") { $appCardCd = "카카오";
		} else if($appCardCd == "47") { $appCardCd = "강원"; }



		$sql_common = " pay_type = '{$pay_type}',
						pay = '{$amt}',
						pay_num = '{$appNo}',
						trxid = '{$tid}',
						trackId = '{$ordNo}',
						pay_datetime = '{$pay_datetime}',
						pay_cdatetime = '{$pay_cdatetime}',
						pay_parti = '{$quota}',
						pay_card_name = '{$appCardCd}',
						pay_card_num = '{$cardNo}',

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
						dv_tid = '{$catId}',
						dv_tid_ori = '{$dv_tid_ori}',
						pg_name = 'paysis' ";


	//	$pay = sql_fetch("select * from g5_payment where trxid = '{$tid}' and pay_num = '{$appNo}'");

		if(!$pay['pay_id']) {
			if($sync_status != 'failed') {
				$sql = " insert into g5_payment set ".$sql_common.", datetime = '".G5_TIME_YMDHIS."'";
				$insert_result = sql_query($sql);
				if($insert_result) {
					$sync_status = 'success';
					$sync_message = '';
				} else {
					$sync_status = 'failed';
					$sync_message = 'g5_payment insert failed';
				}
			}
		} else {
			$sync_status = 'success';
			$sync_message = 'already exists';
		}
	}

	// g5_payment_paysis 동기화 상태 업데이트
	if($paysis_insert_id) {
		$sync_message_escaped = sql_escape_string($sync_message);
		sql_query("UPDATE g5_payment_paysis SET sync_status = '{$sync_status}', sync_message = '{$sync_message_escaped}' WHERE pg_id = '{$paysis_insert_id}'");
	}

	if($noError == false) {
		echo 'OK';
	} else {
		echo 'ERROR';
	}

}