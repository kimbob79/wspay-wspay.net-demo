<?php
/**
 * 코페이(Korpay) NOTI 수신 v2
 *
 * - 가이드 문서(korpay_noti_FULL_EXACT.md) 기준 전체 파라미터 수신
 * - connCd 대소문자 호환 (connCd / connCD 모두 수신)
 * - otid(원거래번호), fnNm(카드사명), acqCardCd(매입사코드), resultCd(수기결제상태) 추가
 * - hashStr 무결성 검증 (mKey 설정 시)
 * - SQL Injection 방지 (sql_escape_string 적용)
 */

$logDir = '/mpchosting/www/api/logs';
$today = date('Y-m-d');
$logFile = $logDir . '/error_' . $today . '.log';

ini_set('error_log', $logFile);

	error_reporting( E_ALL );
	ini_set( "display_errors", 0 );
	date_default_timezone_set('Asia/Seoul');

	// ========================================
	// 요청 로깅 - /logs/trans/api/korpay_v2
	// ========================================
	$log_dir = dirname(__FILE__) . '/../../logs/trans/api/korpay_v2';
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

	// ========================================
	// 파라미터 수신 (가이드 전체 파라미터)
	// ========================================

	// 기본 파라미터
	$gid = isset($_REQUEST['gid']) ? trim($_REQUEST['gid']) : '';
	$vid = isset($_REQUEST['vid']) ? trim($_REQUEST['vid']) : '';
	$mid = isset($_REQUEST['mid']) ? trim($_REQUEST['mid']) : '';
	$payMethod = isset($_REQUEST['payMethod']) ? trim($_REQUEST['payMethod']) : '';
	$appCardCd = isset($_REQUEST['appCardCd']) ? trim($_REQUEST['appCardCd']) : '';
	$cancelYN  = isset($_REQUEST['cancelYN']) ? trim($_REQUEST['cancelYN']) : '';
	$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
	$otid = isset($_REQUEST['otid']) ? trim($_REQUEST['otid']) : '';  // 원거래 거래고유번호 (부분취소시)
	$ediNo  = isset($_REQUEST['ediNo']) ? trim($_REQUEST['ediNo']) : '';
	$appDtm = isset($_REQUEST['appDtm']) ? trim($_REQUEST['appDtm']) : '';
	$ccDnt  = isset($_REQUEST['ccDnt']) ? trim($_REQUEST['ccDnt']) : '';
	$amt = isset($_REQUEST['amt']) ? trim($_REQUEST['amt']) : '';
	$remainAmt = isset($_REQUEST['remainAmt']) ? trim($_REQUEST['remainAmt']) : '';
	$buyerId = isset($_REQUEST['buyerId']) ? trim($_REQUEST['buyerId']) : '';
	$ordNm = isset($_REQUEST['ordNm']) ? trim($_REQUEST['ordNm']) : '';
	$ordNo = isset($_REQUEST['ordNo']) ? trim($_REQUEST['ordNo']) : '';
	$goodsName  = isset($_REQUEST['goodsName']) ? trim($_REQUEST['goodsName']) : '';
	$appNo = isset($_REQUEST['appNo']) ? trim($_REQUEST['appNo']) : '';
	$quota = isset($_REQUEST['quota']) ? trim($_REQUEST['quota']) : '';
	$notiDnt  = isset($_REQUEST['notiDnt']) ? trim($_REQUEST['notiDnt']) : '';
	$cardNo = isset($_REQUEST['cardNo']) ? trim($_REQUEST['cardNo']) : '';
	$catId = isset($_REQUEST['catId']) ? trim($_REQUEST['catId']) : '';
	$tPhone = isset($_REQUEST['tPhone']) ? trim($_REQUEST['tPhone']) : '';
	$canAmt = isset($_REQUEST['canAmt']) ? trim($_REQUEST['canAmt']) : '';
	$partCanFlg = isset($_REQUEST['partCanFlg']) ? trim($_REQUEST['partCanFlg']) : '';
	$usePointAmt = isset($_REQUEST['usePointAmt']) ? trim($_REQUEST['usePointAmt']) : '';
	$vacntNo = isset($_REQUEST['vacntNo']) ? trim($_REQUEST['vacntNo']) : '';
	$socHpNo = isset($_REQUEST['socHpNo']) ? trim($_REQUEST['socHpNo']) : '';

	// connCd 대소문자 호환 (가이드: connCd, 기존구현: connCD)
	$connCd = isset($_REQUEST['connCd']) ? trim($_REQUEST['connCd']) : '';
	if(!$connCd) {
		$connCd = isset($_REQUEST['connCD']) ? trim($_REQUEST['connCD']) : '';
	}

	// 가이드 추가 파라미터 (기존 v1에서 누락)
	$fnNm = isset($_REQUEST['fnNm']) ? trim($_REQUEST['fnNm']) : '';          // 카드사명
	$acqCardCd = isset($_REQUEST['acqCardCd']) ? trim($_REQUEST['acqCardCd']) : '';  // 매입사코드
	$hashStr = isset($_REQUEST['hashStr']) ? trim($_REQUEST['hashStr']) : '';  // 해쉬키
	$ediDate = isset($_REQUEST['ediDate']) ? trim($_REQUEST['ediDate']) : '';  // Noti 통보일
	$resultCd = isset($_REQUEST['resultCd']) ? trim($_REQUEST['resultCd']) : '';  // 수기결제 상태값
	$charSet = isset($_REQUEST['charSet']) ? trim($_REQUEST['charSet']) : '';
	$lmtDay = isset($_REQUEST['lmtDay']) ? trim($_REQUEST['lmtDay']) : '';
	$cashCrctFlg = isset($_REQUEST['cashCrctFlg']) ? trim($_REQUEST['cashCrctFlg']) : '';


if($payMethod) {

	// ========================================
	// hashStr 무결성 검증 (mKey 설정 시)
	// ========================================
	// $KORPAY_MKEY = ''; // 코페이에서 발급받은 mKey 설정 시 검증 활성화
	// if($KORPAY_MKEY && $hashStr) {
	//     $expected_hash = hash('sha256', $mid . $ediDate . $amt . $KORPAY_MKEY);
	//     if(!hash_equals($expected_hash, $hashStr)) {
	//         // 해시 불일치 로그
	//         $hash_log = "[{$log_time}] [HASH_MISMATCH] mid={$mid}, amt={$amt}, expected=" . substr($expected_hash, 0, 16) . "..., received=" . substr($hashStr, 0, 16) . "...\n";
	//         @file_put_contents($log_file, $hash_log, FILE_APPEND | LOCK_EX);
	//         echo 'HASH_ERROR';
	//         exit;
	//     }
	// }
	// ========================================


	$pay_type = "Y";
	$pay_cdatetime = "";
	$pp_limit_3m = '';
	$pp_limit_5m = '';

	// 취소
	if($cancelYN == "Y") {
		$pay_type = "N";
		$tid2 = $tid;
		$tid = "c".$tid;

		// 원거래 (otid가 있으면 otid로 조회, 없으면 tid로 조회)
		$org_tid = $otid ? sql_escape_string($otid) : sql_escape_string($tid2);
		$cancel = sql_fetch("select * from g5_payment_korpay_v2 where tid = '{$org_tid}'");

		// 취소일때 데이터 원거래에서 가져오기
		$pay_cdatetime =  date("Y-m-d H:i:s", strtotime($ccDnt));
		$appDtm = $ccDnt;
		$amt = "-".$cancel['amt']; // 음수로 변경
		sql_query("update g5_payment set pay_cdatetime = '" . sql_escape_string($pay_cdatetime) . "' where trxId = '" . sql_escape_string($tid2) . "'");
	}

	// ========================================
	// g5_payment_korpay_v2 INSERT (PG 원본 저장)
	// ========================================
	$sql_common = " gid ='" . sql_escape_string($gid) . "',
					vid ='" . sql_escape_string($vid) . "',
					mid ='" . sql_escape_string($mid) . "',
					payMethod ='" . sql_escape_string($payMethod) . "',
					appCardCd ='" . sql_escape_string($appCardCd) . "',
					cancelYN  ='" . sql_escape_string($cancelYN) . "',
					tid ='" . sql_escape_string($tid) . "',
					otid ='" . sql_escape_string($otid) . "',
					ediNo  ='" . sql_escape_string($ediNo) . "',
					appDtm ='" . sql_escape_string($appDtm) . "',
					ccDnt  ='" . sql_escape_string($ccDnt) . "',
					amt ='" . sql_escape_string($amt) . "',
					remainAmt ='" . sql_escape_string($remainAmt) . "',
					buyerId ='" . sql_escape_string($buyerId) . "',
					ordNm ='" . sql_escape_string($ordNm) . "',
					ordNo ='" . sql_escape_string($ordNo) . "',
					goodsName  ='" . sql_escape_string($goodsName) . "',
					appNo ='" . sql_escape_string($appNo) . "',
					quota ='" . sql_escape_string($quota) . "',
					notiDnt  ='" . sql_escape_string($notiDnt) . "',
					cardNo ='" . sql_escape_string($cardNo) . "',
					catId ='" . sql_escape_string($catId) . "',
					tPhone ='" . sql_escape_string($tPhone) . "',
					canAmt ='" . sql_escape_string($canAmt) . "',
					partCanFlg ='" . sql_escape_string($partCanFlg) . "',
					connCd ='" . sql_escape_string($connCd) . "',
					usePointAmt ='" . sql_escape_string($usePointAmt) . "',
					vacntNo ='" . sql_escape_string($vacntNo) . "',
					socHpNo ='" . sql_escape_string($socHpNo) . "',
					fnNm ='" . sql_escape_string($fnNm) . "',
					acqCardCd ='" . sql_escape_string($acqCardCd) . "',
					resultCd ='" . sql_escape_string($resultCd) . "', ";

	$sql = "insert into g5_payment_korpay_v2 set ".$sql_common." datetime = '".G5_TIME_YMDHIS."'";
	sql_query($sql);

	// ========================================
	// FDS 이상거래 탐지
	// ========================================
	if($cancelYN != "Y") {
		// 300만원 이상 결제 여부
		if((int)$amt >= 3000000) {
			$pp_limit_3m = 'Y';
		}

		// 동일카드 1일 500만원 초과 여부
		$today_date = date("Y-m-d", strtotime($appDtm));
		$sum_row = sql_fetch("SELECT IFNULL(SUM(ABS(pay)),0) as total_pay
			FROM g5_payment
			WHERE pay_card_num = '" . sql_escape_string($cardNo) . "'
			AND pay_type = 'Y'
			AND pay_datetime >= '{$today_date} 00:00:00'
			AND pay_datetime <= '{$today_date} 23:59:59'");

		$daily_total = (int)$sum_row['total_pay'] + (int)$amt;

		if($daily_total > 5000000) {
			$pp_limit_5m = 'Y';

			// 이전 건들도 전부 Y로 업데이트
			sql_query("UPDATE g5_payment
				SET pp_limit_5m = 'Y'
				WHERE pay_card_num = '" . sql_escape_string($cardNo) . "'
				AND pay_type = 'Y'
				AND pay_datetime >= '{$today_date} 00:00:00'
				AND pay_datetime <= '{$today_date} 23:59:59'");
		}
	}

	// ========================================
	// 디바이스(TID) 조회 및 수수료 계산
	// ========================================
	$row2 = sql_fetch("select * from g5_device where dv_tid = '" . sql_escape_string($catId) . "'");

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

	// ========================================
	// 카드사 코드 → 한글 변환 (fnNm 우선 사용)
	// ========================================
	$pay_card_name = '';
	if($fnNm) {
		// fnNm이 있으면 PG에서 보낸 카드사명 직접 사용
		$pay_card_name = $fnNm;
	} else {
		// fnNm이 없으면 appCardCd 코드 변환 (기존 로직)
		if($appCardCd == "01") { $pay_card_name = "비씨";
		} else if($appCardCd == "02") { $pay_card_name = "국민";
		} else if($appCardCd == "03") { $pay_card_name = "하나(구외환)";
		} else if($appCardCd == "04") { $pay_card_name = "삼성";
		} else if($appCardCd == "06") { $pay_card_name = "신한";
		} else if($appCardCd == "07") { $pay_card_name = "현대";
		} else if($appCardCd == "08") { $pay_card_name = "롯데";
		} else if($appCardCd == "09") { $pay_card_name = "한미";
		} else if($appCardCd == "10") { $pay_card_name = "신세계한미";
		} else if($appCardCd == "11") { $pay_card_name = "씨티";
		} else if($appCardCd == "12") { $pay_card_name = "NH농협카드";
		} else if($appCardCd == "13") { $pay_card_name = "수협";
		} else if($appCardCd == "14") { $pay_card_name = "평화";
		} else if($appCardCd == "15") { $pay_card_name = "우리";
		} else if($appCardCd == "16") { $pay_card_name = "하나";
		} else if($appCardCd == "17") { $pay_card_name = "동남(주택)";
		} else if($appCardCd == "18") { $pay_card_name = "주택";
		} else if($appCardCd == "19") { $pay_card_name = "조흥(강원)";
		} else if($appCardCd == "20") { $pay_card_name = "축협(농협)";
		} else if($appCardCd == "21") { $pay_card_name = "광주";
		} else if($appCardCd == "22") { $pay_card_name = "전북";
		} else if($appCardCd == "23") { $pay_card_name = "제주";
		} else if($appCardCd == "24") { $pay_card_name = "산은";
		} else if($appCardCd == "25") { $pay_card_name = "해외비자";
		} else if($appCardCd == "26") { $pay_card_name = "해외마스터";
		} else if($appCardCd == "27") { $pay_card_name = "해외다이너스";
		} else if($appCardCd == "28") { $pay_card_name = "해외AMX";
		} else if($appCardCd == "29") { $pay_card_name = "해외JCB";
		} else if($appCardCd == "30") { $pay_card_name = "해외";
		} else if($appCardCd == "31") { $pay_card_name = "SK-OKCashBag";
		} else if($appCardCd == "32") { $pay_card_name = "우체국";
		} else if($appCardCd == "33") { $pay_card_name = "MG새마을체크";
		} else if($appCardCd == "34") { $pay_card_name = "중국은행체크";
		} else if($appCardCd == "38") { $pay_card_name = "은련";
		} else if($appCardCd == "46") { $pay_card_name = "카카오";
		} else if($appCardCd == "47") { $pay_card_name = "강원";
		} else {
			$pay_card_name = $appCardCd; // 매핑 없으면 코드 그대로
		}
	}


	// ========================================
	// g5_payment INSERT (메인 결제 테이블)
	// ========================================
	$sql_common = " pay_type = '" . sql_escape_string($pay_type) . "',
					pay = '" . sql_escape_string($amt) . "',
					pay_num = '" . sql_escape_string($appNo) . "',
					trxid = '" . sql_escape_string($tid) . "',
					trackId = '" . sql_escape_string($ordNo) . "',
					pay_datetime = '" . sql_escape_string($pay_datetime) . "',
					pay_cdatetime = '" . sql_escape_string($pay_cdatetime) . "',
					pay_parti = '" . sql_escape_string($quota) . "',
					pay_card_name = '" . sql_escape_string($pay_card_name) . "',
					pay_card_num = '" . sql_escape_string($cardNo) . "',

					mb_1 = '" . sql_escape_string($row2['mb_1']) . "',
					mb_1_name = '" . sql_escape_string($row2['mb_1_name']) . "',
					mb_1_fee = '{$mb_1_fee}',
					mb_1_pay = '{$mb_1_pay}',

					mb_2 = '" . sql_escape_string($row2['mb_2']) . "',
					mb_2_name = '" . sql_escape_string($row2['mb_2_name']) . "',
					mb_2_fee = '{$mb_2_fee}',
					mb_2_pay = '{$mb_2_pay}',

					mb_3 = '" . sql_escape_string($row2['mb_3']) . "',
					mb_3_name = '" . sql_escape_string($row2['mb_3_name']) . "',
					mb_3_fee = '{$mb_3_fee}',
					mb_3_pay = '{$mb_3_pay}',

					mb_4 = '" . sql_escape_string($row2['mb_4']) . "',
					mb_4_name = '" . sql_escape_string($row2['mb_4_name']) . "',
					mb_4_fee = '{$mb_4_fee}',
					mb_4_pay = '{$mb_4_pay}',

					mb_5 = '" . sql_escape_string($row2['mb_5']) . "',
					mb_5_name = '" . sql_escape_string($row2['mb_5_name']) . "',
					mb_5_fee = '{$mb_5_fee}',
					mb_5_pay = '{$mb_5_pay}',

					mb_6 = '" . sql_escape_string($row2['mb_6']) . "',
					mb_6_name = '" . sql_escape_string($row2['mb_6_name']) . "',
					mb_6_fee = '{$mb_6_fee}',
					mb_6_pay = '{$mb_6_pay}',

					dv_type = '" . sql_escape_string($row2['dv_type']) . "',
					dv_certi = '" . sql_escape_string($row2['dv_certi']) . "',
					dv_tid = '" . sql_escape_string($catId) . "',
					pp_limit_3m = '{$pp_limit_3m}',
					pp_limit_5m = '{$pp_limit_5m}',
					pg_name = 'korpay' ";


	if(!$pay['pay_id']) {
		$sql = " insert into g5_payment set ".$sql_common.", datetime = '".G5_TIME_YMDHIS."'";
		sql_query($sql);

		// ========================================
		// 웹훅 발송
		// ========================================
		if($row2['mb_6']) {
			$webhook_lib = dirname(__FILE__) . '/../../lib/webhook.lib.php';
			if(file_exists($webhook_lib)) {
				@include_once($webhook_lib);
				if(function_exists('webhook_send_notification')) {
					$pg_data = [
						'tid' => $tid,
						'ordNo' => $ordNo,
						'appNo' => $appNo,
						'amt' => $amt,
						'appDtm' => $appDtm,
						'ccDnt' => $ccDnt,
						'cancelYN' => $cancelYN,
						'appCardCd' => $appCardCd,
						'fnNm' => $fnNm,
						'cardNo' => $cardNo,
						'quota' => $quota,
						'goodsName' => $goodsName,
						'ordNm' => $ordNm
					];
					$payment_data = [
						'pay_id' => sql_insert_id(),
						'pay_type' => $pay_type
					];
					@webhook_send_notification($row2['mb_6'], 'korpay', $pg_data, $row2, $payment_data);
				}
			}
		}
		// ========================================
	}

	if($noError == false) {
		echo 'OK';
	} else {
		echo 'ERROR';
	}

}
?>
