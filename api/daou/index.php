<?php
	error_reporting( E_ALL );
	ini_set( "display_errors", 1 );
	date_default_timezone_set('Asia/Seoul');

	// ========================================
	// 요청 로깅 - /logs/trans/api/daou
	// ========================================
	$log_dir = dirname(__FILE__) . '/../../logs/trans/api/daou';
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


$gid = isset($_REQUEST['gid']) ? trim($_REQUEST['gid']) : '';						//
$wTid = isset($_REQUEST['wTid']) ? trim($_REQUEST['wTid']) : '';					//
$cardNm = isset($_REQUEST['cardNm']) ? trim($_REQUEST['cardNm']) : '';				//
$cancelYN = isset($_REQUEST['cancelYN']) ? trim($_REQUEST['cancelYN']) : '';		//
$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';						//
$ediNo = isset($_REQUEST['ediNo']) ? trim($_REQUEST['ediNo']) : '';					//
$appDtm = isset($_REQUEST['appDtm']) ? trim($_REQUEST['appDtm']) : '';				//
$ccDnt = isset($_REQUEST['ccDnt']) ? trim($_REQUEST['ccDnt']) : '';					//
$amt = isset($_REQUEST['amt']) ? trim($_REQUEST['amt']) : '';						//
$ordNm = isset($_REQUEST['ordNm']) ? trim($_REQUEST['ordNm']) : '';					//
$goodsName = isset($_REQUEST['goodsName']) ? trim($_REQUEST['goodsName']) : '';		//
$appNo = isset($_REQUEST['appNo']) ? trim($_REQUEST['appNo']) : '';					//
$quota = isset($_REQUEST['quota']) ? trim($_REQUEST['quota']) : '';					//
$cardNo = isset($_REQUEST['cardNo']) ? trim($_REQUEST['cardNo']) : '';				//
$catId = isset($_REQUEST['catId']) ? trim($_REQUEST['catId']) : '';					//
$tmnId = isset($_REQUEST['tmnId']) ? trim($_REQUEST['tmnId']) : '';					//



if($catId) {


	/******** 외부 전송 코드 삭제됨 - API_EXTERNAL_TRANSMISSION_REMOVED.md 참고 ************/

	$pay_type = "Y";
	$pay_cdatetime = "";
	$pp_limit_3m = '';
	$pp_limit_5m = '';

	// 취소
	if($cancelYN == "Y") {
		$pay_type = "N";
		$tid2 = $tid;
		$tid = "c".$tid;

		// 원거래
		$cancel = sql_fetch("select * from g5_payment_daou where tid = '{$tid2}'");

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
//		$amt = $cancel['amt']; // 음수로 변경
		sql_query("update g5_payment set pay_cdatetime = '{$pay_cdatetime}' where trackId = '{$ediNo}' and pay_num = '{$appNo}'");
	}

	$sql_common = " gid ='{$gid}',
					wTid ='{$wTid}',
					cardNm ='{$cardNm}',
					cancelYN ='{$cancelYN}',
					tid ='{$tid}',
					ediNo ='{$ediNo}',
					appDtm ='{$appDtm}',
					ccDnt ='{$ccDnt}',
					amt ='{$amt}',
					ordNm ='{$ordNm}',
					goodsName ='{$goodsName}',
					appNo ='{$appNo}',
					quota ='{$quota}',
					cardNo ='{$cardNo}',
					catId ='{$catId}',
					tmnId ='{$tmnId}', ";

	$sql = "insert into g5_payment_daou set ".$sql_common." datetime = '".G5_TIME_YMDHIS."'";
	sql_query($sql);
	$daou_insert_id = sql_insert_id();

	// 동기화 상태 변수 초기화
	$sync_status = 'pending';
	$sync_message = '';

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
			WHERE pay_card_num = '{$cardNo}'
			AND pay_type = 'Y'
			AND pay_datetime >= '{$today_date} 00:00:00'
			AND pay_datetime <= '{$today_date} 23:59:59'");

		$daily_total = (int)$sum_row['total_pay'] + (int)$amt;

		if($daily_total > 5000000) {
			$pp_limit_5m = 'Y';

			// 이전 건들도 전부 Y로 업데이트
			sql_query("UPDATE g5_payment
				SET pp_limit_5m = 'Y'
				WHERE pay_card_num = '{$cardNo}'
				AND pay_type = 'Y'
				AND pay_datetime >= '{$today_date} 00:00:00'
				AND pay_datetime <= '{$today_date} 23:59:59'");
		}
	}

	/*
	$arraydata = explode(PHP_EOL, trim($config['cf_2']));
	$arraydata = array_map('trim', $arraydata);

	if(in_array($catId, $arraydata)) {
		$catId = substr($ordNo, 0, -10);
		$dv_tid_ori = $catId;
	}

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


	$sql_common = " pay_type = '{$pay_type}',
					pay = '{$amt}',
					pay_num = '{$appNo}',
					trxid = '{$tid}',
					trackId = '{$ediNo}',
					pay_datetime = '{$pay_datetime}',
					pay_cdatetime = '{$pay_cdatetime}',
					pay_parti = '{$quota}',
					pay_card_name = '{$cardNm}',
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
					pp_limit_3m = '{$pp_limit_3m}',
					pp_limit_5m = '{$pp_limit_5m}',
					pg_name = 'daou' ";


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

	// g5_payment_daou 동기화 상태 업데이트
	if($daou_insert_id) {
		$sync_message_escaped = sql_escape_string($sync_message);
		sql_query("UPDATE g5_payment_daou SET sync_status = '{$sync_status}', sync_message = '{$sync_message_escaped}' WHERE pg_id = '{$daou_insert_id}'");
	}

	// ========================================
	// 웹훅 발송 (하이브리드: 즉시 1회 시도, 실패시 크론이 재시도)
	// ========================================
	if($sync_status == 'success' && $row2['mb_6']) {
		$webhook_lib = dirname(__FILE__) . '/../../lib/webhook.lib.php';
		if(file_exists($webhook_lib)) {
			@include_once($webhook_lib);
			if(function_exists('webhook_send_notification')) {
				$pg_data = [
					'tid' => $tid,
					'ordNo' => $ediNo,
					'appNo' => $appNo,
					'amt' => $amt,
					'appDtm' => $appDtm,
					'ccDnt' => $ccDnt,
					'cancelYN' => $cancelYN,
					'appCardCd' => '',
					'cardNo' => $cardNo,
					'quota' => $quota,
					'fnNm' => $cardNm,
					'goodsName' => $goodsName,
					'ordNm' => $ordNm
				];
				$payment_data = [
					'pay_id' => sql_insert_id(),
					'pay_type' => $pay_type
				];
				@webhook_send_notification($row2['mb_6'], 'daou', $pg_data, $row2, $payment_data);
			}
		}
	}
	// ========================================

	echo 'OK';
}