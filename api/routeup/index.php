<?php
	error_reporting( E_ALL );
	ini_set( "display_errors", 1 );
	date_default_timezone_set('Asia/Seoul');

	// ========================================
	// 요청 로깅 - /logs/trans/api/routeup
	// ========================================
	$log_dir = dirname(__FILE__) . '/../../logs/trans/api/routeup';
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
// JSON body 요청 처리
// ========================================
$raw_input = file_get_contents('php://input');
$json_data = json_decode($raw_input, true);

// JSON 데이터가 있으면 $_REQUEST에 병합
if(is_array($json_data)) {
	$_REQUEST = array_merge($_REQUEST, $json_data);
}

// 요청 파라미터 수집
$mid = isset($_REQUEST['mid']) ? trim($_REQUEST['mid']) : '';					// 가맹점 ID
$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';					// 단말기 ID
$trx_id = isset($_REQUEST['trx_id']) ? trim($_REQUEST['trx_id']) : '';			// 거래번호
$amount = isset($_REQUEST['amount']) ? trim($_REQUEST['amount']) : '';			// 거래금액
$ord_num = isset($_REQUEST['ord_num']) ? trim($_REQUEST['ord_num']) : '';		// 주문번호
$appr_num = isset($_REQUEST['appr_num']) ? trim($_REQUEST['appr_num']) : '';	// 승인번호
$item_name = isset($_REQUEST['item_name']) ? trim($_REQUEST['item_name']) : '';	// 상품명
$buyer_name = isset($_REQUEST['buyer_name']) ? trim($_REQUEST['buyer_name']) : '';		// 구매자명
$buyer_phone = isset($_REQUEST['buyer_phone']) ? trim($_REQUEST['buyer_phone']) : '';	// 구매자번호
$issuer = isset($_REQUEST['issuer']) ? trim($_REQUEST['issuer']) : '';			// 발급사명
$acquirer = isset($_REQUEST['acquirer']) ? trim($_REQUEST['acquirer']) : '';	// 매입사명
$issuer_code = isset($_REQUEST['issuer_code']) ? trim($_REQUEST['issuer_code']) : '';		// 발급사코드
$acquirer_code = isset($_REQUEST['acquirer_code']) ? trim($_REQUEST['acquirer_code']) : '';	// 매입사코드
$card_num = isset($_REQUEST['card_num']) ? trim($_REQUEST['card_num']) : '';	// 카드번호
$installment = isset($_REQUEST['installment']) ? trim($_REQUEST['installment']) : '';	// 할부기간
$trx_dttm = isset($_REQUEST['trx_dttm']) ? trim($_REQUEST['trx_dttm']) : '';	// 거래시간
$cxl_dttm = isset($_REQUEST['cxl_dttm']) ? trim($_REQUEST['cxl_dttm']) : '';	// 취소시간
$is_cancel = isset($_REQUEST['is_cancel']) ? trim($_REQUEST['is_cancel']) : '';	// 취소여부
$cxl_seq = isset($_REQUEST['cxl_seq']) ? trim($_REQUEST['cxl_seq']) : '';		// 취소회차
$ori_trx_id = isset($_REQUEST['ori_trx_id']) ? trim($_REQUEST['ori_trx_id']) : '';	// 원거래번호
$module_type = isset($_REQUEST['module_type']) ? trim($_REQUEST['module_type']) : '';	// 모듈타입
$timestamp = isset($_REQUEST['timestamp']) ? trim($_REQUEST['timestamp']) : '';	// 타임스탬프
$signature = isset($_REQUEST['signature']) ? trim($_REQUEST['signature']) : '';	// 무결성보장값
$temp = isset($_REQUEST['temp']) ? trim($_REQUEST['temp']) : '';				// 임시예약필드

// 응답 헤더 설정
header('Content-Type: application/json; charset=utf-8');

if($trx_id) {

	$pay_type = "Y";
	$pay_cdatetime = "";
	$original_amount = $amount;

	// 취소 처리
	if($is_cancel == "1") {
		$pay_type = "N";
		$trx_id_for_payment = "c" . $trx_id;

		// 원거래 조회
		$cancel = sql_fetch("SELECT * FROM g5_payment_routeup WHERE trx_id = '{$ori_trx_id}'");

		// 취소시간 설정
		$pay_cdatetime = date("Y-m-d H:i:s", strtotime($cxl_dttm));
		$amount = "-" . $amount; // 음수로 변경

		// 취소 거래의 pay_datetime을 취소일시로 설정 (실시간 결제내역에 최신으로 표시)
		$trx_dttm = $cxl_dttm;

		// 원거래 취소일시 업데이트
		sql_query("UPDATE g5_payment SET pay_cdatetime = '{$pay_cdatetime}' WHERE trxId = '{$ori_trx_id}'");
	} else {
		$trx_id_for_payment = $trx_id; 
	}

	// g5_payment_routeup 테이블에 저장
	$sql_common = " mid = '{$mid}',
					tid = '{$tid}',
					trx_id = '{$trx_id}',
					amount = '{$original_amount}',
					ord_num = '{$ord_num}',
					appr_num = '{$appr_num}',
					item_name = '{$item_name}',
					buyer_name = '{$buyer_name}',
					buyer_phone = '{$buyer_phone}',
					issuer = '{$issuer}',
					acquirer = '{$acquirer}',
					issuer_code = '{$issuer_code}',
					acquirer_code = '{$acquirer_code}',
					card_num = '{$card_num}',
					installment = '{$installment}',
					trx_dttm = '{$trx_dttm}',
					cxl_dttm = '{$cxl_dttm}',
					is_cancel = '{$is_cancel}',
					cxl_seq = '{$cxl_seq}',
					ori_trx_id = '{$ori_trx_id}',
					module_type = '{$module_type}',
					timestamp = '{$timestamp}',
					signature = '{$signature}',
					temp = '{$temp}', ";

	$sql = "INSERT INTO g5_payment_routeup SET " . $sql_common . " datetime = '" . G5_TIME_YMDHIS . "'";
	sql_query($sql);
	$routeup_insert_id = sql_insert_id();

	// 동기화 상태 변수 초기화
	$sync_status = 'pending';
	$sync_message = '';

	// ========================================
	// 수기결제 (module_type = 1)
	// ========================================
	if($module_type == '1') {
		// 1. MID로 Keyin 설정 조회 (개별설정 또는 대표가맹점설정)
		// 루트업: 개별설정은 mkc_mid, 대표설정은 mpc_rootup_mid
		$keyin_sql = "SELECT k.*, m.mpc_rootup_mid
			FROM g5_member_keyin_config k
			LEFT JOIN g5_manual_payment_config m ON k.mpc_id = m.mpc_id
			WHERE k.mkc_use = 'Y' AND k.mkc_status = 'active'
			AND (
				(k.mpc_id IS NULL AND k.mkc_mid = '{$mid}' AND k.mkc_mkey = '{$tid}')
				OR (k.mpc_id IS NOT NULL AND m.mpc_rootup_mid = '{$mid}')
			)";
		$keyin_result = sql_query($keyin_sql);
		$keyin_count = sql_num_rows($keyin_result);

		$keyin_config = null;
		$target_mb_id = '';

		if($keyin_count == 1) {
			// 개별설정 가맹점이거나 대표설정을 단독 사용하는 경우
			$keyin_config = sql_fetch_array($keyin_result);
			$target_mb_id = $keyin_config['mb_id'];
		} else if($keyin_count > 1) {
			// 대표가맹점 설정을 여러 가맹점이 공유하는 경우 → mkc_oid로 구분
			$mkc_oid = substr($ord_num, 0, 4);
			while($row = sql_fetch_array($keyin_result)) {
				if($row['mkc_oid'] == $mkc_oid) {
					$keyin_config = $row;
					$target_mb_id = $row['mb_id'];
					break;
				}
			}
		}

		// 2. 해당 가맹점의 디바이스 정보 조회
		$row2 = array();
		if($target_mb_id) {
			$row2 = sql_fetch("SELECT d.*, d.mb_6 as merchant_mb_id
				FROM g5_device d
				WHERE d.mb_6 = '{$target_mb_id}'
				LIMIT 1");
		}

		// 디바이스 조회 실패 체크
		if(!$row2['dv_id']) {
			$sync_status = 'failed';
			$sync_message = $target_mb_id ? "device not found for mb_id '{$target_mb_id}'" : "keyin config not found for mid '{$mid}'";
		}

		$mb_1_fee = $row2['mb_1_fee'];
		$mb_2_fee = $row2['mb_2_fee'];
		$mb_3_fee = $row2['mb_3_fee'];
		$mb_4_fee = $row2['mb_4_fee'];
		$mb_5_fee = $row2['mb_5_fee'];
		$mb_6_fee = $row2['mb_6_fee'];

		// 수수료 계산
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

		$mb_1_pay = $amount * $row2['mb_1_fee'] / 100;
		$mb_2_pay = $amount * $row2['mb_2_fee'] / 100;
		$mb_3_pay = $amount * $row2['mb_3_fee'] / 100;
		$mb_4_pay = $amount * $row2['mb_4_fee'] / 100;
		$mb_5_pay = $amount * $row2['mb_5_fee'] / 100;
		$mb_6_pay = $amount * $row2['mb_6_fee'] / 100;
		$mb_6_pay = $amount - $mb_6_pay;

		$pay_datetime = date("Y-m-d H:i:s", strtotime($trx_dttm));

		// 수기결제는 issuer를 카드사명으로 사용
		$pay_card_name = $issuer;

		$sql_common_keyin = " pay_type = '{$pay_type}',
						pay = '{$amount}',
						pay_num = '{$appr_num}',
						trxid = '{$trx_id_for_payment}',
						trackId = '{$ord_num}',
						pay_datetime = '{$pay_datetime}',
						pay_cdatetime = '{$pay_cdatetime}',
						pay_parti = '{$installment}',
						pay_card_name = '{$pay_card_name}',
						pay_card_num = '{$card_num}',

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

						dv_type = '2',
						dv_certi = '{$row2['dv_certi']}',
						dv_tid = '{$row2['dv_tid']}',
						dv_tid_ori = '',
						pg_name = 'routeup_k' ";

		$pay = sql_fetch("SELECT * FROM g5_payment WHERE trxid = '{$trx_id_for_payment}' AND pay_num = '{$appr_num}'");

		if(!$pay['pay_id']) {
			if($sync_status != 'failed') {
				$sql = " INSERT INTO g5_payment SET " . $sql_common_keyin . ", datetime = '" . G5_TIME_YMDHIS . "'";
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
	// 오프라인 단말기 결제 (module_type = 0 또는 기타)
	// ========================================
	else {
		// tid로 디바이스 조회
		$catId = $tid;
		$dv_tid_ori = '';

		$row2 = sql_fetch("SELECT * FROM g5_device WHERE dv_tid = '{$catId}'");

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

		// 수수료 계산
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

		$mb_1_pay = $amount * $row2['mb_1_fee'] / 100;
		$mb_2_pay = $amount * $row2['mb_2_fee'] / 100;
		$mb_3_pay = $amount * $row2['mb_3_fee'] / 100;
		$mb_4_pay = $amount * $row2['mb_4_fee'] / 100;
		$mb_5_pay = $amount * $row2['mb_5_fee'] / 100;
		$mb_6_pay = $amount * $row2['mb_6_fee'] / 100;
		$mb_6_pay = $amount - $mb_6_pay;

		$pay_datetime = date("Y-m-d H:i:s", strtotime($trx_dttm));

		// 발급사명을 카드사명으로 사용
		$pay_card_name = $issuer;

		$sql_common_payment = " pay_type = '{$pay_type}',
						pay = '{$amount}',
						pay_num = '{$appr_num}',
						trxid = '{$trx_id_for_payment}',
						trackId = '{$ord_num}',
						pay_datetime = '{$pay_datetime}',
						pay_cdatetime = '{$pay_cdatetime}',
						pay_parti = '{$installment}',
						pay_card_name = '{$pay_card_name}',
						pay_card_num = '{$card_num}',

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
						pg_name = 'routeup' ";

		$pay = sql_fetch("SELECT * FROM g5_payment WHERE trxid = '{$trx_id_for_payment}' AND pay_num = '{$appr_num}'");

		if(!$pay['pay_id']) {
			if($sync_status != 'failed') {
				$sql = " INSERT INTO g5_payment SET " . $sql_common_payment . ", datetime = '" . G5_TIME_YMDHIS . "'";
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

	// g5_payment_routeup 동기화 상태 업데이트
	if($routeup_insert_id) {
		$sync_message_escaped = sql_escape_string($sync_message);
		sql_query("UPDATE g5_payment_routeup SET sync_status = '{$sync_status}', sync_message = '{$sync_message_escaped}' WHERE pg_id = '{$routeup_insert_id}'");
	}

	// 성공 응답 (routeup 규격: HTTP 200, body: {})
	echo json_encode(new stdClass());

} else {
	// trx_id가 없으면 실패 응답
	http_response_code(400);
	echo json_encode(["message" => "trx_id is required"]);
}
?>