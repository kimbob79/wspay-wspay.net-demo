<?php
/**
 * 수기결제 통계 + NOTI 싱크 카운트 AJAX 엔드포인트
 * - manual_payment.php에서 비동기로 호출
 * - 통계(SUM/IF) + NOTI 카운트를 JSON으로 응답
 */

include_once('./_common.php');

// JSON 응답
header('Content-Type: application/json; charset=utf-8');

// 수기결제 권한 체크
if(!$is_admin && $member['mb_mailling'] != '1') {
	echo json_encode(['error' => '권한 없음']);
	exit;
}

$table_name = "g5_payment_keyin";

// 날짜 필터
$fr_date = isset($_GET['fr_date']) ? $_GET['fr_date'] : date("Ymd");
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date("Ymd");

$fr_dates = date("Y-m-d", strtotime($fr_date));
$to_dates = date("Y-m-d", strtotime($to_date));

// 접근 제어 SQL (p. 별칭 사용)
if($is_admin) {
	if(adm_sql_common) {
		$adm_sql = " p.pk_mb_1 IN (".adm_sql_common.")";
	} else {
		$adm_sql = " (1)";
	}
} else if($member['mb_level'] == 8) {
	$adm_sql = " p.pk_mb_1 = '{$member['mb_id']}'";
} else if($member['mb_level'] == 7) {
	$adm_sql = " p.pk_mb_2 = '{$member['mb_id']}'";
} else if($member['mb_level'] == 6) {
	$adm_sql = " p.pk_mb_3 = '{$member['mb_id']}'";
} else if($member['mb_level'] == 5) {
	$adm_sql = " p.pk_mb_4 = '{$member['mb_id']}'";
} else if($member['mb_level'] == 4) {
	$adm_sql = " p.pk_mb_5 = '{$member['mb_id']}'";
} else if($member['mb_level'] == 3) {
	$adm_sql = " p.mb_id = '{$member['mb_id']}'";
}

// 검색 조건
if ($fr_date == "all" && $to_date == "all") {
	$sql_search = " WHERE ".$adm_sql;
} else {
	$sql_search = " WHERE ".$adm_sql." AND (p.pk_created_at BETWEEN '{$fr_dates} 00:00:00' AND '{$to_dates} 23:59:59')";
}

// 상태 필터
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';
if($status_filter && in_array($status_filter, ['approved', 'failed', 'cancelled', 'pending'])) {
	$sql_search .= " AND p.pk_status = '{$status_filter}'";
}

// PG사 필터
$pg_filter = isset($_GET['pg_filter']) ? sql_escape_string($_GET['pg_filter']) : '';
if($pg_filter) {
	$sql_search .= " AND p.pk_pg_code = '{$pg_filter}'";
}

// 인증타입 필터
$auth_filter = isset($_GET['auth_filter']) ? sql_escape_string($_GET['auth_filter']) : '';
if($auth_filter && in_array($auth_filter, ['nonauth', 'auth'])) {
	$sql_search .= " AND p.pk_auth_type = '{$auth_filter}'";
}

// 검색어
$sfl = isset($_GET['sfl']) ? $_GET['sfl'] : '';
$stx = isset($_GET['stx']) ? $_GET['stx'] : '';
if ($stx) {
	$sql_search .= " AND ( ";
	switch ($sfl) {
		case "pk_app_no" :
		case "pk_order_no" :
			$sql_search .= " (p.{$sfl} = '{$stx}') ";
			break;
		case "pk_card_no" :
			$card_search = preg_replace('/[^0-9]/', '', $stx);
			if(strlen($card_search) == 4) {
				$sql_search .= " (p.pk_card_no_masked LIKE '{$card_search}%' OR p.pk_card_no_masked LIKE '%{$card_search}') ";
			} else {
				$sql_search .= " (p.pk_card_no_masked LIKE '%{$card_search}%') ";
			}
			break;
		case "pk_mb_6_name" :
		case "pk_goods_name" :
		case "pk_buyer_name" :
		default :
			$sql_search .= " (p.{$sfl} LIKE '%{$stx}%') ";
			break;
	}
	$sql_search .= " ) ";
}

// 통계 쿼리 실행
$sql = "SELECT
	COUNT(*) as cnt,
	SUM(p.pk_amount) as total_amount,
	SUM(IF(p.pk_status = 'approved', p.pk_amount, 0)) as approved_amount,
	COUNT(IF(p.pk_status = 'approved', 1, NULL)) as approved_count,
	SUM(IF(p.pk_status = 'failed', p.pk_amount, 0)) as failed_amount,
	COUNT(IF(p.pk_status = 'failed', 1, NULL)) as failed_count,
	SUM(IF(p.pk_status IN ('cancelled', 'partial_cancelled'), p.pk_cancel_amount, 0)) as cancelled_amount,
	COUNT(IF(p.pk_status IN ('cancelled', 'partial_cancelled'), 1, NULL)) as cancelled_count,
	COUNT(IF(p.pk_status = 'pending', 1, NULL)) as pending_count
	FROM {$table_name} p {$sql_search}";
$stat = sql_fetch($sql);

$response = array(
	'stat' => array(
		'cnt' => intval($stat['cnt']),
		'total_amount' => intval($stat['total_amount']),
		'approved_count' => intval($stat['approved_count']),
		'approved_amount' => intval($stat['approved_amount']),
		'failed_count' => intval($stat['failed_count']),
		'failed_amount' => intval($stat['failed_amount']),
		'cancelled_count' => intval($stat['cancelled_count']),
		'cancelled_amount' => intval($stat['cancelled_amount']),
		'pending_count' => intval($stat['pending_count'])
	)
);

// 관리자용 NOTI 싱크 현황 카운트 (PHP 배치 처리 방식)
if($is_admin) {
	// 최근 7일 고정 (성능)
	$noti_7days_ago = date("Y-m-d", strtotime("-7 days"));
	$noti_today = date("Y-m-d");
	$noti_base = " WHERE ".$adm_sql." AND (p.pk_created_at BETWEEN '{$noti_7days_ago} 00:00:00' AND '{$noti_today} 23:59:59')";
	$noti_base .= " AND p.pk_status = 'approved' AND p.pk_app_no IS NOT NULL AND p.pk_app_no != ''";

	// 1) 승인 건 전체 조회 (pk_app_no, pk_amount, pk_pg_code만)
	$keyin_rows = array();
	$pg_app_nos = array('paysis' => [], 'stn' => [], 'rootup' => [], 'winglobal' => []);
	$all_app_nos = array();

	$q = sql_query("SELECT p.pk_app_no, p.pk_amount, p.pk_pg_code FROM {$table_name} p {$noti_base}");
	while($r = sql_fetch_array($q)) {
		if(isset($pg_app_nos[$r['pk_pg_code']])) {
			$keyin_rows[] = $r;
			$pg_app_nos[$r['pk_pg_code']][] = "'" . sql_escape_string($r['pk_app_no']) . "'";
			$all_app_nos[] = "'" . sql_escape_string($r['pk_app_no']) . "'";
		}
	}

	// 2) PG별 NOTI 존재 여부 배치 IN 쿼리
	$noti_found = array();
	if(!empty($pg_app_nos['paysis'])) {
		$in = implode(',', array_unique($pg_app_nos['paysis']));
		$q = sql_query("SELECT appNo, amt FROM g5_payment_paysis WHERE connCd='0005' AND appNo IN ({$in})");
		while($nr = sql_fetch_array($q)) { $noti_found[$nr['appNo'].'_'.$nr['amt']] = true; }
	}
	if(!empty($pg_app_nos['stn'])) {
		$in = implode(',', array_unique($pg_app_nos['stn']));
		$q = sql_query("SELECT applNo, amount FROM g5_payment_stn WHERE requestFlag='K' AND applNo IN ({$in})");
		while($nr = sql_fetch_array($q)) { $noti_found[$nr['applNo'].'_'.$nr['amount']] = true; }
	}
	if(!empty($pg_app_nos['rootup'])) {
		$in = implode(',', array_unique($pg_app_nos['rootup']));
		$q = sql_query("SELECT appr_num, amount FROM g5_payment_routeup WHERE module_type='1' AND appr_num IN ({$in})");
		while($nr = sql_fetch_array($q)) { $noti_found[$nr['appr_num'].'_'.$nr['amount']] = true; }
	}
	if(!empty($pg_app_nos['winglobal'])) {
		$in = implode(',', array_unique($pg_app_nos['winglobal']));
		$q = sql_query("SELECT appNo, amt FROM g5_payment_daou WHERE appNo IN ({$in})");
		while($nr = sql_fetch_array($q)) { $noti_found[$nr['appNo'].'_'.$nr['amt']] = true; }
		$q = sql_query("SELECT appNo, amt FROM g5_payment_korpay WHERE appNo IN ({$in})");
		while($nr = sql_fetch_array($q)) { $noti_found[$nr['appNo'].'_'.$nr['amt']] = true; }
		$q = sql_query("SELECT CARDAUTHNO, AMOUNT FROM g5_payment_danal WHERE CARDAUTHNO IN ({$in})");
		while($nr = sql_fetch_array($q)) { $noti_found[$nr['CARDAUTHNO'].'_'.$nr['AMOUNT']] = true; }
	}

	// 3) g5_payment 존재 여부 배치 IN 쿼리
	$payment_found = array();
	if(!empty($all_app_nos)) {
		$in = implode(',', array_unique($all_app_nos));
		$q = sql_query("SELECT pay_num, pay FROM g5_payment WHERE pg_name IN ('paysis_keyin','stn_k','routeup_k','daou','korpay','danal') AND pay_num IN ({$in})");
		while($pr = sql_fetch_array($q)) { $payment_found[$pr['pay_num'].'_'.$pr['pay']] = true; }
	}

	// 4) PHP에서 비교하여 카운트
	$noti_missing = 0;
	$payment_missing = 0;
	foreach($keyin_rows as $r) {
		$key = $r['pk_app_no'] . '_' . $r['pk_amount'];
		$has_noti = isset($noti_found[$key]);
		$has_payment = isset($payment_found[$key]);

		if(!$has_noti) {
			$noti_missing++;
		} else if(!$has_payment) {
			$payment_missing++;
		}
	}

	$response['noti_sync'] = array(
		'noti_missing' => $noti_missing,
		'payment_missing' => $payment_missing
	);
}

echo json_encode($response);
