<?php
/**
 * 수기결제 내역 페이지
 * - g5_payment_keyin 테이블 조회
 * - 관리자: 전체 조회
 * - 가맹점: 자신의 내역만 조회
 */

$title1 = "수기결제";
$title2 = "수기결제 내역";

include_once('./_common.php');

// 수기결제 권한 체크
if(!$is_admin && $member['mb_mailling'] != '1') {
	alert("수기결제 권한이 없습니다.");
}

// 테이블 존재 여부 확인 및 생성
$table_name = "g5_payment_keyin";
$check_table = sql_query("SHOW TABLES LIKE '{$table_name}'");
if(sql_num_rows($check_table) == 0) {
	$create_sql = "CREATE TABLE `{$table_name}` (
		`pk_id` int(11) NOT NULL AUTO_INCREMENT,
		`pk_order_no` varchar(50) NOT NULL COMMENT '주문번호 (OID-YYMM-HHMM-SSRR)',
		`pk_merchant_oid` varchar(4) DEFAULT NULL COMMENT '가맹점 OID',
		`mb_id` varchar(50) NOT NULL COMMENT '가맹점 아이디',
		`mkc_id` int(11) DEFAULT NULL COMMENT 'Keyin 설정 ID',
		`pk_pg_code` varchar(20) NOT NULL COMMENT 'PG사 코드 (paysis 등)',
		`pk_pg_name` varchar(50) DEFAULT NULL COMMENT 'PG사 이름',
		`pk_mid` varchar(50) DEFAULT NULL COMMENT '상점 ID',
		`pk_auth_type` varchar(20) DEFAULT NULL COMMENT '인증 타입 (nonauth/auth)',
		`pk_amount` int(11) NOT NULL COMMENT '결제 금액',
		`pk_installment` varchar(2) DEFAULT '00' COMMENT '할부개월 (00=일시불)',
		`pk_goods_name` varchar(100) DEFAULT NULL COMMENT '상품명',
		`pk_buyer_name` varchar(50) DEFAULT NULL COMMENT '구매자명',
		`pk_buyer_phone` varchar(20) DEFAULT NULL COMMENT '구매자 연락처',
		`pk_card_issuer` varchar(50) DEFAULT NULL COMMENT '발급사명',
		`pk_card_acquirer` varchar(50) DEFAULT NULL COMMENT '매입사명',
		`pk_card_no_masked` varchar(20) DEFAULT NULL COMMENT '카드번호 마스킹',
		`pk_status` enum('pending','approved','failed','cancelled','partial_cancelled') DEFAULT 'pending' COMMENT '결제 상태',
		`pk_res_code` varchar(10) DEFAULT NULL COMMENT 'PG 응답코드',
		`pk_res_msg` varchar(200) DEFAULT NULL COMMENT 'PG 응답메시지',
		`pk_app_no` varchar(50) DEFAULT NULL COMMENT '승인번호',
		`pk_app_date` varchar(20) DEFAULT NULL COMMENT '승인일시 (PG 응답)',
		`pk_tid` varchar(100) DEFAULT NULL COMMENT 'PG 거래 ID',
		`pk_cancel_amount` int(11) DEFAULT 0 COMMENT '취소 금액',
		`pk_cancel_name` varchar(50) DEFAULT NULL COMMENT '취소자명',
		`pk_cancel_reason` varchar(200) DEFAULT NULL COMMENT '취소 사유',
		`pk_cancel_date` varchar(20) DEFAULT NULL COMMENT '취소 일시 (PG 응답)',
		`pk_mb_1` varchar(50) DEFAULT NULL,
		`pk_mb_2` varchar(50) DEFAULT NULL,
		`pk_mb_3` varchar(50) DEFAULT NULL,
		`pk_mb_4` varchar(50) DEFAULT NULL,
		`pk_mb_5` varchar(50) DEFAULT NULL,
		`pk_mb_6` varchar(50) DEFAULT NULL,
		`pk_mb_6_name` varchar(50) DEFAULT NULL COMMENT '가맹점명',
		`pk_request_data` longtext COMMENT '요청 데이터 JSON',
		`pk_response_data` longtext COMMENT '응답 데이터 JSON',
		`pk_operator_id` varchar(50) DEFAULT NULL COMMENT '결제 진행자 ID',
		`pk_memo` text COMMENT '관리 메모',
		`pk_created_at` datetime DEFAULT CURRENT_TIMESTAMP,
		`pk_updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (`pk_id`),
		KEY `idx_order_no` (`pk_order_no`),
		KEY `idx_mb_id` (`mb_id`),
		KEY `idx_status` (`pk_status`),
		KEY `idx_created_at` (`pk_created_at`),
		KEY `idx_app_no` (`pk_app_no`),
		KEY `idx_pg_code` (`pk_pg_code`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='수기결제 거래 내역'";
	sql_query($create_sql);
}

// 날짜 필터
if(!$fr_date) { $fr_date = date("Ymd"); }
if(!$to_date) { $to_date = date("Ymd"); }

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

// NOTI 상태 필터 (관리자 전용)
$noti_filter = isset($_GET['noti_filter']) ? $_GET['noti_filter'] : '';
if($is_admin && $noti_filter && in_array($noti_filter, ['noti_missing', 'payment_missing', 'normal'])) {
	// 승인 건만 대상
	$sql_search .= " AND p.pk_status = 'approved' AND p.pk_app_no IS NOT NULL AND p.pk_app_no != ''";

	if($noti_filter == 'noti_missing') {
		// PG NOTI 테이블에 레코드 없음
		$sql_search .= " AND (
			(p.pk_pg_code = 'paysis' AND NOT EXISTS (
				SELECT 1 FROM g5_payment_paysis noti WHERE noti.connCd='0005' AND noti.appNo = p.pk_app_no AND CAST(noti.amt AS SIGNED) = p.pk_amount
			))
			OR (p.pk_pg_code = 'stn' AND NOT EXISTS (
				SELECT 1 FROM g5_payment_stn noti WHERE noti.requestFlag='K' AND noti.applNo = p.pk_app_no AND CAST(noti.amount AS SIGNED) = p.pk_amount
			))
			OR (p.pk_pg_code = 'rootup' AND NOT EXISTS (
				SELECT 1 FROM g5_payment_routeup noti WHERE noti.module_type='1' AND noti.appr_num = p.pk_app_no AND CAST(noti.amount AS SIGNED) = p.pk_amount
			))
			OR (p.pk_pg_code = 'winglobal' AND NOT EXISTS (
				SELECT 1 FROM g5_payment_daou noti WHERE noti.appNo = p.pk_app_no AND CAST(noti.amt AS SIGNED) = p.pk_amount
			) AND NOT EXISTS (
				SELECT 1 FROM g5_payment_korpay noti WHERE noti.appNo = p.pk_app_no AND CAST(noti.amt AS SIGNED) = p.pk_amount
			) AND NOT EXISTS (
				SELECT 1 FROM g5_payment_danal noti WHERE noti.CARDAUTHNO = p.pk_app_no AND CAST(noti.AMOUNT AS SIGNED) = p.pk_amount
			))
		)";
	} else if($noti_filter == 'payment_missing') {
		// PG NOTI 있지만 g5_payment에 없음
		$sql_search .= " AND (
			(p.pk_pg_code IN ('paysis','stn','rootup') AND (
				(p.pk_pg_code = 'paysis' AND EXISTS (
					SELECT 1 FROM g5_payment_paysis noti WHERE noti.connCd='0005' AND noti.appNo = p.pk_app_no AND CAST(noti.amt AS SIGNED) = p.pk_amount
				))
				OR (p.pk_pg_code = 'stn' AND EXISTS (
					SELECT 1 FROM g5_payment_stn noti WHERE noti.requestFlag='K' AND noti.applNo = p.pk_app_no AND CAST(noti.amount AS SIGNED) = p.pk_amount
				))
				OR (p.pk_pg_code = 'rootup' AND EXISTS (
					SELECT 1 FROM g5_payment_routeup noti WHERE noti.module_type='1' AND noti.appr_num = p.pk_app_no AND CAST(noti.amount AS SIGNED) = p.pk_amount
				))
			) AND NOT EXISTS (
				SELECT 1 FROM g5_payment gp
				WHERE gp.pg_name IN ('paysis_keyin','stn_k','routeup_k')
				AND gp.pay_num = p.pk_app_no AND gp.pay = p.pk_amount
			))
			OR (p.pk_pg_code = 'winglobal' AND (
				EXISTS (SELECT 1 FROM g5_payment_daou noti WHERE noti.appNo = p.pk_app_no AND CAST(noti.amt AS SIGNED) = p.pk_amount)
				OR EXISTS (SELECT 1 FROM g5_payment_korpay noti WHERE noti.appNo = p.pk_app_no AND CAST(noti.amt AS SIGNED) = p.pk_amount)
				OR EXISTS (SELECT 1 FROM g5_payment_danal noti WHERE noti.CARDAUTHNO = p.pk_app_no AND CAST(noti.AMOUNT AS SIGNED) = p.pk_amount)
			) AND NOT EXISTS (
				SELECT 1 FROM g5_payment gp
				WHERE gp.pg_name IN ('daou','korpay','danal')
				AND gp.pay_num = p.pk_app_no AND gp.pay = p.pk_amount
			))
		)";
	} else if($noti_filter == 'normal') {
		// 정상: PG NOTI + g5_payment 모두 존재
		$sql_search .= " AND (
			(p.pk_pg_code IN ('paysis','stn','rootup') AND (
				(p.pk_pg_code = 'paysis' AND EXISTS (
					SELECT 1 FROM g5_payment_paysis noti WHERE noti.connCd='0005' AND noti.appNo = p.pk_app_no AND CAST(noti.amt AS SIGNED) = p.pk_amount
				))
				OR (p.pk_pg_code = 'stn' AND EXISTS (
					SELECT 1 FROM g5_payment_stn noti WHERE noti.requestFlag='K' AND noti.applNo = p.pk_app_no AND CAST(noti.amount AS SIGNED) = p.pk_amount
				))
				OR (p.pk_pg_code = 'rootup' AND EXISTS (
					SELECT 1 FROM g5_payment_routeup noti WHERE noti.module_type='1' AND noti.appr_num = p.pk_app_no AND CAST(noti.amount AS SIGNED) = p.pk_amount
				))
			) AND EXISTS (
				SELECT 1 FROM g5_payment gp
				WHERE gp.pg_name IN ('paysis_keyin','stn_k','routeup_k')
				AND gp.pay_num = p.pk_app_no AND gp.pay = p.pk_amount
			))
			OR (p.pk_pg_code = 'winglobal' AND (
				EXISTS (SELECT 1 FROM g5_payment_daou noti WHERE noti.appNo = p.pk_app_no AND CAST(noti.amt AS SIGNED) = p.pk_amount)
				OR EXISTS (SELECT 1 FROM g5_payment_korpay noti WHERE noti.appNo = p.pk_app_no AND CAST(noti.amt AS SIGNED) = p.pk_amount)
				OR EXISTS (SELECT 1 FROM g5_payment_danal noti WHERE noti.CARDAUTHNO = p.pk_app_no AND CAST(noti.AMOUNT AS SIGNED) = p.pk_amount)
			) AND EXISTS (
				SELECT 1 FROM g5_payment gp
				WHERE gp.pg_name IN ('daou','korpay','danal')
				AND gp.pay_num = p.pk_app_no AND gp.pay = p.pk_amount
			))
		)";
	}
}

// 검색어
if ($stx) {
	$sql_search .= " AND ( ";
	switch ($sfl) {
		case "pk_app_no" :
		case "pk_order_no" :
			$sql_search .= " (p.{$sfl} = '{$stx}') ";
			break;
		case "pk_card_no" :
			// 카드번호 앞4자리 또는 뒤4자리 검색
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

// 정렬
if ($sst)
	$sql_order = " ORDER BY p.{$sst} {$sod} ";
else
	$sql_order = " ORDER BY p.pk_created_at DESC ";

// 통계 조회 (p 별칭 사용)
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

$total_count = $stat['cnt'];
$page_count = "30";
$rows = $page_count ? $page_count : $config['cf_page_rows'];

$total_page = ceil($total_count / $rows);
if ($page < 1) $page = 1;
$from_record = ($page - 1) * $rows;

// 목록 조회 (keyin 설정의 취소 권한 포함)
$sql = "SELECT p.*, k.mkc_cancel_yn
        FROM {$table_name} p
        LEFT JOIN g5_member_keyin_config k ON p.mkc_id = k.mkc_id
        {$sql_search} {$sql_order} LIMIT {$from_record}, {$rows}";
$result = sql_query($sql);

// 관리자용 NOTI 상태 배치 체크
$rows_data = array();
$noti_check = array();

if($is_admin) {
	// 결과를 배열로 수집
	while($r = sql_fetch_array($result)) {
		$rows_data[] = $r;
	}

	// 승인 건의 승인번호를 PG별로 분류
	$pg_app_nos = array('paysis' => [], 'stn' => [], 'rootup' => [], 'winglobal' => []);
	$all_app_nos = array();
	foreach($rows_data as $r) {
		if($r['pk_status'] == 'approved' && $r['pk_app_no'] && isset($pg_app_nos[$r['pk_pg_code']])) {
			$pg_app_nos[$r['pk_pg_code']][] = "'" . sql_escape_string($r['pk_app_no']) . "'";
			$all_app_nos[] = "'" . sql_escape_string($r['pk_app_no']) . "'";
		}
	}

	// PG별 NOTI 존재 체크 (승인번호+금액 복합키)
	$noti_found = array();
	if(!empty($pg_app_nos['paysis'])) {
		$in = implode(',', $pg_app_nos['paysis']);
		$q = sql_query("SELECT appNo, amt FROM g5_payment_paysis WHERE connCd='0005' AND appNo IN ({$in})");
		while($nr = sql_fetch_array($q)) { $noti_found[$nr['appNo'].'_'.$nr['amt']] = true; }
	}
	if(!empty($pg_app_nos['stn'])) {
		$in = implode(',', $pg_app_nos['stn']);
		$q = sql_query("SELECT applNo, amount FROM g5_payment_stn WHERE requestFlag='K' AND applNo IN ({$in})");
		while($nr = sql_fetch_array($q)) { $noti_found[$nr['applNo'].'_'.$nr['amount']] = true; }
	}
	if(!empty($pg_app_nos['rootup'])) {
		$in = implode(',', $pg_app_nos['rootup']);
		$q = sql_query("SELECT appr_num, amount FROM g5_payment_routeup WHERE module_type='1' AND appr_num IN ({$in})");
		while($nr = sql_fetch_array($q)) { $noti_found[$nr['appr_num'].'_'.$nr['amount']] = true; }
	}
	if(!empty($pg_app_nos['winglobal'])) {
		$in = implode(',', $pg_app_nos['winglobal']);
		// 다우
		$q = sql_query("SELECT appNo, amt FROM g5_payment_daou WHERE appNo IN ({$in})");
		while($nr = sql_fetch_array($q)) { $noti_found[$nr['appNo'].'_'.$nr['amt']] = true; }
		// 코페이
		$q = sql_query("SELECT appNo, amt FROM g5_payment_korpay WHERE appNo IN ({$in})");
		while($nr = sql_fetch_array($q)) { $noti_found[$nr['appNo'].'_'.$nr['amt']] = true; }
		// 다날
		$q = sql_query("SELECT CARDAUTHNO, AMOUNT FROM g5_payment_danal WHERE CARDAUTHNO IN ({$in})");
		while($nr = sql_fetch_array($q)) { $noti_found[$nr['CARDAUTHNO'].'_'.$nr['AMOUNT']] = true; }
	}

	// g5_payment 존재 체크 (승인번호+금액 복합키)
	$payment_found = array();
	if(!empty($all_app_nos)) {
		$in = implode(',', array_unique($all_app_nos));
		$q = sql_query("SELECT pay_num, pay FROM g5_payment WHERE pg_name IN ('paysis_keyin','stn_k','routeup_k','daou','korpay','danal') AND pay_num IN ({$in})");
		while($pr = sql_fetch_array($q)) { $payment_found[$pr['pay_num'].'_'.$pr['pay']] = true; }
	}

	// 상태 맵 생성 (승인번호+금액 복합키)
	foreach($rows_data as $r) {
		$app = $r['pk_app_no'];
		$key = $app . '_' . $r['pk_amount'];
		if($r['pk_status'] == 'approved' && $app && in_array($r['pk_pg_code'], ['paysis','stn','rootup','winglobal'])) {
			$has_noti = isset($noti_found[$key]);
			$has_payment = isset($payment_found[$key]);
			if($has_noti && $has_payment) {
				$noti_check[$key] = 'normal';
			} else if(!$has_noti) {
				$noti_check[$key] = 'noti_missing';
			} else {
				$noti_check[$key] = 'payment_missing';
			}
		}
	}
}

include_once('./_head.php');
?>

<style>
.manual-list-header {
	background: linear-gradient(135deg, #393E46 0%, #4a5058 100%);
	border-radius: 8px;
	padding: 12px 16px;
	margin-bottom: 10px;
	box-shadow: 0 2px 8px rgba(57, 62, 70, 0.3);
}
.manual-list-header-top {
	display: flex;
	align-items: center;
	justify-content: space-between;
	flex-wrap: wrap;
	gap: 10px;
	margin-bottom: 10px;
}
.manual-list-header-bottom {
	display: flex;
	align-items: center;
	justify-content: flex-start;
}
.manual-list-title {
	color: #fff;
	font-size: 16px;
	font-weight: 600;
	display: flex;
	align-items: center;
	gap: 8px;
}
.manual-list-title i {
	font-size: 14px;
	opacity: 0.8;
}
.manual-list-stats {
	display: flex;
	flex-wrap: wrap;
	gap: 6px;
}
.manual-list-stat {
	display: inline-flex;
	align-items: center;
	background: rgba(255,255,255,0.12);
	border-radius: 4px;
	padding: 4px 10px;
	font-size: 12px;
	color: rgba(255,255,255,0.85);
	gap: 6px;
}
.manual-list-stat.approved {
	background: rgba(76,175,80,0.3);
}
.manual-list-stat.failed {
	background: rgba(244,67,54,0.3);
}
.manual-list-stat.cancelled {
	background: rgba(158,158,158,0.3);
}
.manual-list-stat.total {
	background: rgba(255,211,105,0.3);
	color: #FFD369;
	font-weight: 600;
}
.manual-list-stat span {
	color: #fff;
	font-weight: 600;
}
.manual-list-search {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 10px 12px;
	margin-bottom: 10px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.manual-list-search-row {
	display: flex;
	align-items: center;
	flex-wrap: wrap;
	gap: 8px;
}
.manual-list-search-group {
	display: flex;
	align-items: center;
	gap: 4px;
}
.manual-list-search-group input[type="text"],
.manual-list-search-group select {
	padding: 6px 8px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-size: 13px;
	background: #f8f9fa;
}
.manual-list-search-group input[type="text"] {
	width: 90px;
}
.manual-list-search-group select {
	min-width: 80px;
}
.manual-list-search-group input[type="text"]:focus,
.manual-list-search-group select:focus {
	outline: none;
	border-color: #FFD369;
	background: #fff;
}
.manual-list-search-group span {
	color: #999;
	font-size: 12px;
}
.date-btns {
	display: flex;
	gap: 3px;
}
.date-btns button {
	padding: 5px 8px;
	font-size: 11px;
	border: 1px solid #ddd;
	background: #f8f9fa;
	border-radius: 3px;
	cursor: pointer;
	color: #555;
	transition: all 0.15s;
}
.date-btns button:hover {
	background: #393E46;
	border-color: #393E46;
	color: #FFD369;
}
.search-divider {
	width: 1px;
	height: 24px;
	background: #e0e0e0;
	margin: 0 6px;
}
.filter-row {
	display: flex;
	align-items: center;
	gap: 8px;
}
.order-no {
	font-family: 'Consolas', 'Monaco', 'SF Mono', 'Menlo', monospace;
	font-size: 11px;
	letter-spacing: 0.3px;
}
.radio-group {
	display: flex;
	align-items: center;
	gap: 8px;
}
.radio-group label {
	display: flex;
	align-items: center;
	gap: 3px;
	font-size: 12px;
	color: #555;
	cursor: pointer;
}
.radio-group input[type="radio"] {
	margin: 0;
	accent-color: #FFD369;
}
.search-input-group {
	display: flex;
	align-items: center;
	gap: 4px;
}
.search-input-group input[type="text"] {
	width: 120px;
	padding: 6px 10px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-size: 13px;
}
.search-input-group input[type="text"]:focus {
	outline: none;
	border-color: #FFD369;
}
.btn-search {
	padding: 6px 12px;
	background: #393E46;
	color: #FFD369;
	border: none;
	border-radius: 4px;
	font-size: 12px;
	cursor: pointer;
	transition: background 0.15s;
}
.btn-search:hover {
	background: #4a5058;
}
.btn-excel {
	padding: 6px 10px;
	background: #2e7d32;
	color: #fff;
	border: none;
	border-radius: 4px;
	font-size: 12px;
	cursor: pointer;
	transition: background 0.15s;
}
.btn-excel:hover {
	background: #388e3c;
}
/* 상태 배지 */
.status-badge {
	display: inline-block;
	padding: 3px 8px;
	border-radius: 12px;
	font-size: 11px;
	font-weight: 600;
}
.status-badge.approved {
	background: #e8f5e9;
	color: #2e7d32;
}
.status-badge.failed {
	background: #ffebee;
	color: #c62828;
}
.status-badge.cancelled {
	background: #fafafa;
	color: #757575;
	text-decoration: line-through;
}
.status-badge.pending {
	background: #f5f5f5;
	color: #393E46;
}
/* 상태 툴팁 */
.status-wrapper {
	position: relative;
	display: inline-block;
}
.status-tooltip {
	position: absolute;
	bottom: 100%;
	left: 50%;
	transform: translateX(-50%);
	background: #333;
	color: #fff;
	padding: 8px 14px;
	border-radius: 6px;
	font-size: 11px;
	white-space: nowrap;
	z-index: 1000;
	opacity: 0;
	visibility: hidden;
	transition: all 0.2s ease;
	margin-bottom: 6px;
	text-align: center;
	line-height: 1.4;
	box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
.status-tooltip::after {
	content: '';
	position: absolute;
	top: 100%;
	left: 50%;
	transform: translateX(-50%);
	border: 6px solid transparent;
	border-top-color: #333;
}
.status-wrapper:hover .status-tooltip {
	opacity: 1;
	visibility: visible;
}
/* 영수증 버튼 */
.btn-receipt {
	padding: 4px 8px;
	background: #1565c0;
	color: #fff;
	border: none;
	border-radius: 3px;
	font-size: 11px;
	cursor: pointer;
	transition: background 0.15s;
	margin-right: 4px;
}
.btn-receipt:hover {
	background: #1976d2;
}
/* 인증타입 배지 */
.auth-badge {
	display: inline-block;
	padding: 2px 6px;
	border-radius: 3px;
	font-size: 10px;
	font-weight: 600;
}
.auth-badge.nonauth {
	background: #f5f5f5;
	color: #393E46;
}
.auth-badge.auth {
	background: #e3f2fd;
	color: #1565c0;
}
/* 신규결제 버튼 */
.btn-manual-module {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	padding: 10px 20px;
	background: linear-gradient(135deg, #393E46 0%, #4a5058 100%);
	color: #FFD369;
	border: none;
	border-radius: 8px;
	font-size: 14px;
	font-weight: 600;
	cursor: pointer;
	transition: all 0.2s ease;
	text-decoration: none;
	box-shadow: 0 2px 8px rgba(57, 62, 70, 0.3);
}
.btn-manual-module:hover {
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(255, 211, 105, 0.4);
	background: linear-gradient(135deg, #4a5058 0%, #5a6068 100%);
	color: #FFD369;
}
.btn-manual-module i {
	font-size: 16px;
}
/* 플로팅 신규결제 버튼 */
.floating-new-payment {
	position: fixed;
	right: 20px;
	bottom: 20px;
	z-index: 1000;
}
/* TOP 버튼 숨기기 */
#topBtn, .top-btn, .btn-top, #scrollToTop, .scroll-top {
	display: none !important;
}
.floating-new-payment a {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	width: 90px;
	height: 90px;
	background: linear-gradient(135deg, #FFD369 0%, #FFC107 100%);
	color: #333;
	border-radius: 50%;
	font-size: 13px;
	font-weight: 700;
	text-decoration: none;
	box-shadow: 0 4px 15px rgba(255, 193, 7, 0.5), 0 0 20px rgba(255, 211, 105, 0.3);
	border: 3px solid #fff;
	animation: pulse-glow 2s ease-in-out infinite;
}
@keyframes pulse-glow {
	0%, 100% {
		box-shadow: 0 4px 15px rgba(255, 193, 7, 0.5), 0 0 20px rgba(255, 211, 105, 0.3);
	}
	50% {
		box-shadow: 0 4px 20px rgba(255, 193, 7, 0.7), 0 0 30px rgba(255, 211, 105, 0.5);
	}
}
.floating-new-payment a:hover {
	background: linear-gradient(135deg, #ffe082 0%, #FFD369 100%);
	color: #333;
	transform: scale(1.1);
	box-shadow: 0 6px 25px rgba(255, 193, 7, 0.6), 0 0 35px rgba(255, 211, 105, 0.5);
}
.floating-new-payment i {
	font-size: 24px;
	margin-bottom: 4px;
}
/* 취소 버튼 */
.btn-cancel {
	padding: 4px 8px;
	background: #f44336;
	color: #fff;
	border: none;
	border-radius: 3px;
	font-size: 11px;
	cursor: pointer;
	transition: background 0.15s;
}
.btn-cancel:hover {
	background: #d32f2f;
}
.btn-cancel:disabled {
	background: #bdbdbd;
	cursor: not-allowed;
}
/* 테이블 행 색상 */
tr.row-cancelled {
	background: #fafafa !important;
}
tr.row-cancelled td {
	color: #9e9e9e;
}
tr.row-failed {
	background: #fff8f8 !important;
}
/* NOTI 상태 배지 */
.noti-badge {
	display: inline-block;
	padding: 2px 6px;
	border-radius: 3px;
	font-size: 11px;
	font-weight: 600;
	white-space: nowrap;
}
.noti-ok {
	background: #e8f5e9;
	color: #2e7d32;
}
.noti-miss {
	background: #ffebee;
	color: #c62828;
}
.noti-unreg {
	background: #fff3e0;
	color: #e65100;
}
@media (max-width: 768px) {
	.manual-list-header-top {
		flex-direction: row;
		align-items: center;
		justify-content: space-between;
	}
	.manual-list-header-bottom {
		flex-direction: column;
		align-items: flex-start;
	}
	.manual-list-stats {
		width: 100%;
		flex-wrap: wrap;
		gap: 4px;
	}
	.manual-list-stat {
		font-size: 10px;
		padding: 3px 6px;
	}
	.manual-list-search {
		padding: 8px;
	}
	.manual-list-search-row {
		flex-direction: column;
		align-items: stretch;
		gap: 6px;
	}
	.manual-list-search-group {
		justify-content: flex-start;
	}
	.manual-list-search-group.date-group {
		width: 100%;
	}
	.manual-list-search-group input[type="text"] {
		flex: 1;
		width: auto;
		min-width: 0;
	}
	.manual-list-search-group select {
		min-width: 0;
	}
	.filter-row {
		display: flex;
		gap: 4px;
		width: 100%;
	}
	.filter-row .manual-list-search-group {
		flex: 1;
	}
	.filter-row select {
		width: 100%;
		font-size: 12px;
		padding: 6px 4px;
	}
	.date-btns {
		width: 100%;
		flex-wrap: wrap;
	}
	.date-btns button {
		flex: 1;
		min-width: calc(33% - 4px);
		padding: 6px 4px;
		font-size: 11px;
	}
	.search-divider {
		display: none;
	}
	.radio-group {
		width: 100%;
		flex-wrap: wrap;
		gap: 4px 10px;
	}
	.radio-group label {
		font-size: 11px;
	}
	.search-input-group {
		width: 100%;
	}
	.search-input-group input[type="text"] {
		flex: 1;
		width: auto;
	}
	.btn-search {
		padding: 6px 16px;
	}
	.btn-excel {
		padding: 6px 10px;
	}
}
/* 취소 확인 모달 */
.cancel-modal-overlay {
	position: fixed;
	left: 0;
	right: 0;
	top: 0;
	bottom: 0;
	background: rgba(0,0,0,0.5);
	z-index: 10000;
	display: none;
	justify-content: center;
	align-items: center;
	padding: 15px;
}
.cancel-modal-overlay.show {
	display: flex;
}
.cancel-modal {
	width: 320px;
	background: #fff;
	border-radius: 12px;
	overflow: hidden;
	box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}
.cancel-modal-header {
	background: linear-gradient(135deg, #c62828 0%, #e53935 100%);
	padding: 16px 20px;
	text-align: center;
}
.cancel-modal-header h3 {
	color: #fff;
	font-size: 16px;
	font-weight: 600;
	margin: 0;
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 8px;
}
.cancel-modal-header h3 i {
	font-size: 18px;
}
.cancel-modal-body {
	padding: 20px;
}
.cancel-modal-info {
	background: #fafafa;
	border-radius: 8px;
	padding: 12px 14px;
	margin-bottom: 16px;
}
.cancel-modal-info .info-row {
	display: flex;
	justify-content: space-between;
	padding: 4px 0;
	font-size: 13px;
}
.cancel-modal-info .info-row .label {
	color: #666;
}
.cancel-modal-info .info-row .value {
	color: #333;
	font-weight: 600;
}
.cancel-modal-info .info-row .value.amount {
	color: #c62828;
	font-size: 15px;
}
.cancel-modal-message {
	text-align: center;
	padding: 10px 0;
	font-size: 14px;
	color: #333;
	line-height: 1.6;
}
.cancel-modal-message strong {
	color: #c62828;
}
.cancel-modal-footer {
	display: flex;
	gap: 8px;
	padding: 0 20px 20px;
}
.cancel-modal-footer .btn {
	flex: 1;
	padding: 12px;
	border: none;
	border-radius: 6px;
	font-size: 14px;
	font-weight: 600;
	cursor: pointer;
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 6px;
	transition: all 0.2s;
}
.cancel-modal-footer .btn-cancel-confirm {
	background: #c62828;
	color: #fff;
}
.cancel-modal-footer .btn-cancel-confirm:hover {
	background: #b71c1c;
}
.cancel-modal-footer .btn-cancel-close {
	background: #e0e0e0;
	color: #666;
}
.cancel-modal-footer .btn-cancel-close:hover {
	background: #d0d0d0;
}
/* 로딩 스피너 */
.loading-overlay {
	position: fixed;
	left: 0;
	right: 0;
	top: 0;
	bottom: 0;
	background: rgba(255,255,255,0.95);
	z-index: 10001;
	display: none;
	flex-direction: column;
	align-items: center;
	justify-content: center;
}
.loading-overlay.show {
	display: flex;
}
.loading-overlay .spinner {
	width: 50px;
	height: 50px;
	border: 4px solid #e0e0e0;
	border-top: 4px solid #c62828;
	border-radius: 50%;
	animation: spin 1s linear infinite;
}
@keyframes spin {
	0% { transform: rotate(0deg); }
	100% { transform: rotate(360deg); }
}
.loading-overlay .loading-text {
	margin-top: 20px;
	font-size: 14px;
	color: #666;
	font-weight: 500;
}
/* 취소 결과 모달 */
.cancel-result-modal {
	width: 320px;
	background: #fff;
	border-radius: 12px;
	box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}
.cancel-result-header {
	padding: 20px;
	text-align: center;
	border-radius: 12px 12px 0 0;
}
.cancel-result-header.success {
	background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%);
}
.cancel-result-header.fail {
	background: linear-gradient(135deg, #c62828 0%, #e53935 100%);
}
.cancel-result-header i {
	font-size: 40px;
	color: #fff;
	margin-bottom: 10px;
}
.cancel-result-header h3 {
	color: #fff;
	font-size: 18px;
	font-weight: 600;
	margin: 0;
}
.cancel-result-body {
	padding: 20px;
	text-align: center;
}
.cancel-result-body .message {
	font-size: 14px;
	color: #333;
	line-height: 1.6;
	margin-bottom: 15px;
}
.cancel-result-body .detail {
	background: #fafafa;
	border-radius: 8px;
	padding: 12px;
	font-size: 13px;
	color: #666;
}
.cancel-result-footer {
	padding: 0 20px 24px;
}
.cancel-result-footer .btn {
	width: 100%;
	padding: 0;
	height: 44px;
	border: none;
	border-radius: 8px;
	font-size: 14px;
	font-weight: 600;
	line-height: 44px;
	cursor: pointer;
	background: #393E46;
	color: #FFD369;
	transition: background 0.2s;
	box-sizing: border-box;
}
.cancel-result-footer .btn:hover {
	background: #4a5058;
}
</style>

<!-- 로딩 스피너 -->
<div class="loading-overlay" id="loadingOverlay">
	<div class="spinner"></div>
	<div class="loading-text">취소 처리중...</div>
</div>

<!-- 취소 결과 모달 -->
<div class="cancel-modal-overlay" id="cancelResultOverlay">
	<div class="cancel-result-modal">
		<div class="cancel-result-header" id="cancelResultHeader">
			<i class="fa fa-check-circle" id="cancelResultIcon"></i>
			<h3 id="cancelResultTitle">취소 완료</h3>
		</div>
		<div class="cancel-result-body">
			<div class="message" id="cancelResultMessage">거래가 정상적으로 취소되었습니다.</div>
			<div class="detail" id="cancelResultDetail"></div>
		</div>
		<div class="cancel-result-footer">
			<button type="button" class="btn" onclick="closeCancelResult()">확인</button>
		</div>
	</div>
</div>

<!-- 취소 확인 모달 -->
<div class="cancel-modal-overlay" id="cancelModalOverlay">
	<div class="cancel-modal">
		<div class="cancel-modal-header">
			<h3><i class="fa fa-exclamation-triangle"></i> 결제 취소</h3>
		</div>
		<div class="cancel-modal-body">
			<div class="cancel-modal-info">
				<div class="info-row">
					<span class="label">승인번호</span>
					<span class="value" id="cancelAppNo">-</span>
				</div>
				<div class="info-row">
					<span class="label">상품명</span>
					<span class="value" id="cancelGoodsName">-</span>
				</div>
				<div class="info-row">
					<span class="label">결제금액</span>
					<span class="value amount" id="cancelAmount">-</span>
				</div>
			</div>
			<div class="cancel-modal-message">
				이 거래를 <strong>취소</strong>하시겠습니까?<br>
				<small style="color:#999;">취소 후에는 복구할 수 없습니다.</small>
			</div>
		</div>
		<div class="cancel-modal-footer">
			<button type="button" class="btn btn-cancel-close" onclick="closeCancelModal()">
				<i class="fa fa-times"></i> 닫기
			</button>
			<button type="button" class="btn btn-cancel-confirm" onclick="confirmCancel()">
				<i class="fa fa-check"></i> 취소하기
			</button>
		</div>
	</div>
</div>

<?php if($is_admin) { ?>
<!-- ===== 관리자용 화면 (기존) ===== -->
<div class="manual-list-header">
	<div class="manual-list-header-top">
		<div class="manual-list-title">
			<i class="fa fa-list-alt"></i>
			수기결제 내역
		</div>
	</div>
	<div class="manual-list-header-bottom">
		<div class="manual-list-stats">
			<div class="manual-list-stat approved">
				승인 <span><?php echo number_format($stat['approved_count']); ?>건</span> / <span><?php echo number_format($stat['approved_amount']); ?>원</span>
			</div>
			<div class="manual-list-stat failed">
				실패 <span><?php echo number_format($stat['failed_count']); ?>건</span>
			</div>
			<div class="manual-list-stat cancelled">
				취소 <span><?php echo number_format($stat['cancelled_count']); ?>건</span> / <span><?php echo number_format($stat['cancelled_amount']); ?>원</span>
			</div>
			<?php if($stat['pending_count'] > 0) { ?>
			<div class="manual-list-stat">
				대기 <span><?php echo number_format($stat['pending_count']); ?>건</span>
			</div>
			<?php } ?>
			<div class="manual-list-stat total">
				전체 <span><?php echo number_format($total_count); ?>건</span>
			</div>
		</div>
	</div>
</div>

<form id="fsearch" name="fsearch" method="get">
<input type="hidden" name="p" value="<?php echo $p; ?>">
<div class="manual-list-search">
	<div class="manual-list-search-row">
		<div class="manual-list-search-group">
			<input type="text" name="fr_date" value="<?php echo $fr_date ?>" id="fr_date" placeholder="시작일">
			<span>~</span>
			<input type="text" name="to_date" value="<?php echo $to_date ?>" id="to_date" placeholder="종료일">
		</div>
		<div class="date-btns">
			<button type="submit" onclick="javascript:set_date('오늘');">오늘</button>
			<button type="submit" onclick="javascript:set_date('어제');">어제</button>
			<button type="submit" onclick="javascript:set_date('이번달');">이번달</button>
			<button type="submit" onclick="javascript:set_date('지난주');">지난주</button>
			<button type="submit" onclick="javascript:set_date('지난달');">지난달</button>
			<button type="submit" onclick="javascript:set_date('전체');">전체</button>
		</div>
		<div class="search-divider"></div>
		<div class="filter-row">
			<div class="manual-list-search-group">
				<select name="status_filter">
					<option value="">상태전체</option>
					<option value="approved" <?php if($status_filter == 'approved') echo 'selected'; ?>>승인</option>
					<option value="failed" <?php if($status_filter == 'failed') echo 'selected'; ?>>실패</option>
					<option value="cancelled" <?php if($status_filter == 'cancelled') echo 'selected'; ?>>취소</option>
					<option value="pending" <?php if($status_filter == 'pending') echo 'selected'; ?>>대기</option>
				</select>
			</div>
			<div class="manual-list-search-group">
				<select name="pg_filter">
					<option value="">PG전체</option>
					<option value="paysis" <?php if($pg_filter == 'paysis') echo 'selected'; ?>>페이시스</option>
					<option value="rootup" <?php if($pg_filter == 'rootup') echo 'selected'; ?>>루트업</option>
					<option value="stn" <?php if($pg_filter == 'stn') echo 'selected'; ?>>섹타나인</option>
				</select>
			</div>
			<div class="manual-list-search-group">
				<select name="auth_filter">
					<option value="">인증전체</option>
					<option value="nonauth" <?php if($auth_filter == 'nonauth') echo 'selected'; ?>>비인증</option>
					<option value="auth" <?php if($auth_filter == 'auth') echo 'selected'; ?>>구인증</option>
				</select>
			</div>
			<div class="manual-list-search-group">
				<select name="noti_filter">
					<option value="">NOTI전체</option>
					<option value="noti_missing" <?php if($noti_filter == 'noti_missing') echo 'selected'; ?>>NOTI미수신</option>
					<option value="payment_missing" <?php if($noti_filter == 'payment_missing') echo 'selected'; ?>>미등록</option>
					<option value="normal" <?php if($noti_filter == 'normal') echo 'selected'; ?>>정상</option>
				</select>
			</div>
		</div>
		<div class="search-divider"></div>
		<div class="radio-group">
			<label><input type="radio" name="sfl" value="pk_app_no" <?php echo get_checked($sfl, "pk_app_no"); ?> checked>승번</label>
			<label><input type="radio" name="sfl" value="pk_mb_6_name" <?php echo get_checked($sfl, "pk_mb_6_name"); ?>>가맹</label>
			<label><input type="radio" name="sfl" value="pk_order_no" <?php echo get_checked($sfl, "pk_order_no"); ?>>주문번호</label>
			<label><input type="radio" name="sfl" value="pk_goods_name" <?php echo get_checked($sfl, "pk_goods_name"); ?>>상품명</label>
			<label><input type="radio" name="sfl" value="pk_buyer_name" <?php echo get_checked($sfl, "pk_buyer_name"); ?>>구매자</label>
			<label><input type="radio" name="sfl" value="pk_card_no" <?php echo get_checked($sfl, "pk_card_no"); ?>>카드번호</label>
		</div>
		<div class="search-divider"></div>
		<div class="search-input-group">
			<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" placeholder="검색어">
			<button type="submit" class="btn-search">검색</button>
			<button type="button" class="btn-excel" onclick="downloadCsv()"><i class="fa fa-file-excel-o"></i> 엑셀</button>
		</div>
	</div>
</div>
</form>

<script>
function downloadCsv() {
	var params = new URLSearchParams(window.location.search);
	params.delete('p');
	var url = 'manual_payment_excel.php?' + params.toString();

	// iframe으로 다운로드 (로딩바 문제 해결)
	var iframe = document.createElement('iframe');
	iframe.style.display = 'none';
	iframe.src = url;
	document.body.appendChild(iframe);

	// 5초 후 iframe 제거
	setTimeout(function() {
		document.body.removeChild(iframe);
	}, 5000);
}
</script>

<div class="m_board_scroll">
	<div class="m_table_wrap" style="padding-bottom:115px;">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
				<tr>
					<th style="width:50px;">번호</th>
					<th>가맹점명</th>
					<th>주문번호</th>
					<th>상품명</th>
					<th>금액</th>
					<th>할부</th>
					<th>카드사</th>
					<th>구매자명</th>
					<th>구매자연락처</th>
					<th>상태</th>
					<th>승인번호</th>
					<th>요청일시</th>
					<th>취소일시</th>
					<th>PG</th>
					<th>NOTI</th>
					<th>인증</th>
					<th>응답메시지</th>
					<th>관리</th>
				</tr>
			</thead>
			<tbody>
				<?php
				if($total_count == 0) {
				?>
				<tr>
					<td colspan="18" class="center" style="padding: 40px 0; color: #999;">
						<i class="fa fa-inbox" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
						조회된 내역이 없습니다.
					</td>
				</tr>
				<?php
				} else {
					foreach($rows_data as $i => $row) {
						$num = number_format($total_count - ($page - 1) * $rows - $i);

						// 상태 표시
						$status_class = '';
						$status_text = '';
						$row_class = '';
						switch($row['pk_status']) {
							case 'approved':
								$status_class = 'approved';
								$status_text = '승인';
								break;
							case 'failed':
								$status_class = 'failed';
								$status_text = '실패';
								$row_class = 'row-failed';
								break;
							case 'cancelled':
								$status_class = 'cancelled';
								$status_text = '취소';
								$row_class = 'row-cancelled';
								break;
							case 'partial_cancelled':
								$status_class = 'cancelled';
								$status_text = '부분취소';
								$row_class = 'row-cancelled';
								break;
							case 'pending':
								$status_class = 'pending';
								$status_text = '대기';
								break;
						}

						// 인증타입 표시
						$auth_class = $row['pk_auth_type'] == 'auth' ? 'auth' : 'nonauth';
						$auth_text = $row['pk_auth_type'] == 'auth' ? '구인증' : '비인증';

						// 할부 표시
						if($row['pk_installment'] == '00' || $row['pk_installment'] == '0' || !$row['pk_installment']) {
							$installment_text = '일시불';
						} else {
							$installment_text = intval($row['pk_installment']) . '개월';
						}

						// PG사 이름
						$pg_name = $row['pk_pg_name'] ? $row['pk_pg_name'] : $row['pk_pg_code'];
				?>
				<tr class="<?php echo $row_class; ?>">
					<td class="center"><?php echo $num; ?></td>
					<td class="td_name"><?php echo htmlspecialchars($row['pk_mb_6_name']); ?></td>
					<td class="order-no"><?php echo htmlspecialchars($row['pk_order_no']); ?></td>
					<td><?php echo htmlspecialchars($row['pk_goods_name']); ?></td>
					<td class="right"><?php echo number_format($row['pk_amount']); ?></td>
					<td class="center"><?php echo $installment_text; ?></td>
					<td class="center">
						<?php if($row['pk_card_issuer']) { echo htmlspecialchars(str_replace('카드', '', $row['pk_card_issuer'])); } ?>
						<?php if($row['pk_card_no_masked']) { ?> <small style="color:#999;"><?php echo $row['pk_card_no_masked']; ?></small><?php } ?>
					</td>
					<td class="center"><?php echo $row['pk_buyer_name'] ? htmlspecialchars($row['pk_buyer_name']) : '-'; ?></td>
					<td class="center"><?php echo $row['pk_buyer_phone'] ? htmlspecialchars($row['pk_buyer_phone']) : '-'; ?></td>
					<td class="center">
						<?php
						// 툴팁에 표시할 상세 메시지 생성
						$tooltip_msg = '';
						if($row['pk_res_code'] || $row['pk_res_msg']) {
							if($row['pk_res_code']) $tooltip_msg .= '[' . $row['pk_res_code'] . '] ';
							if($row['pk_res_msg']) $tooltip_msg .= htmlspecialchars($row['pk_res_msg']);
						}
						if($row['pk_status'] == 'cancelled' && $row['pk_cancel_reason']) {
							$tooltip_msg .= ($tooltip_msg ? '<br>' : '') . '취소사유: ' . htmlspecialchars($row['pk_cancel_reason']);
						}
						?>
						<div class="status-wrapper">
							<span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
							<?php if($tooltip_msg) { ?>
							<div class="status-tooltip"><?php echo $tooltip_msg; ?></div>
							<?php } ?>
						</div>
					</td>
					<td class="center"><?php echo $row['pk_app_no'] ? $row['pk_app_no'] : '-'; ?></td>
					<td class="center"><?php echo $row['pk_created_at']; ?></td>
					<td class="center"><?php
						if($row['pk_status'] == 'cancelled' && $row['pk_cancel_date']) {
							// 14자리 형식(YYYYMMDDHHmmss)을 Y-m-d H:i:s로 변환
							$cancel_date = $row['pk_cancel_date'];
							if(strlen($cancel_date) == 14 && is_numeric($cancel_date)) {
								echo substr($cancel_date, 0, 4) . '-' . substr($cancel_date, 4, 2) . '-' . substr($cancel_date, 6, 2) . ' ' . substr($cancel_date, 8, 2) . ':' . substr($cancel_date, 10, 2) . ':' . substr($cancel_date, 12, 2);
							} else {
								echo $cancel_date;
							}
						} else {
							echo '-';
						}
					?></td>
					<td class="center"><?php echo $pg_name; ?></td>
					<td class="center">
					<?php
					if($row['pk_status'] == 'approved' && $row['pk_app_no'] && in_array($row['pk_pg_code'], ['paysis','stn','rootup','winglobal'])) {
						$noti_key = $row['pk_app_no'] . '_' . $row['pk_amount'];
						$noti_st = isset($noti_check[$noti_key]) ? $noti_check[$noti_key] : '';
						if($noti_st == 'normal') {
							echo '<span class="noti-badge noti-ok">정상</span>';
						} else if($noti_st == 'noti_missing') {
							echo '<span class="noti-badge noti-miss">NOTI미수신</span>';
						} else if($noti_st == 'payment_missing') {
							echo '<span class="noti-badge noti-unreg">미등록</span>';
						}
					} else {
						echo '-';
					}
					?>
					</td>
					<td class="center"><span class="auth-badge <?php echo $auth_class; ?>"><?php echo $auth_text; ?></span></td>
					<td style="max-width:150px; font-size:11px; color:#666;">
						<?php
						if($row['pk_res_code']) {
							echo '[' . $row['pk_res_code'] . '] ';
						}
						echo htmlspecialchars($row['pk_res_msg']);
						?>
					</td>
					<td class="center">
						<?php if($row['pk_status'] == 'approved') { ?>
						<button type="button" class="btn-receipt" onclick="openReceipt(<?php echo $row['pk_id']; ?>)">영수증</button>
						<?php if($is_admin || $row['mkc_cancel_yn'] == 'Y') { ?>
						<button type="button" class="btn-cancel" onclick="openCancelModal(<?php echo $row['pk_id']; ?>, '<?php echo $row['pk_app_no']; ?>', '<?php echo addslashes($row['pk_goods_name']); ?>', <?php echo $row['pk_amount']; ?>)">취소</button>
						<?php } ?>
						<?php } else if($row['pk_status'] == 'cancelled') { ?>
						<button type="button" class="btn-receipt" onclick="openReceipt(<?php echo $row['pk_id']; ?>)">영수증</button>
						<?php } else { ?>
						-
						<?php } ?>
					</td>
				</tr>
				<?php
					}
				}
				?>
			</tbody>
		</table>
	</div>
</div>

<?php
$qstr = "p=".$p;
$qstr .= "&fr_date=".$fr_date;
$qstr .= "&to_date=".$to_date;
$qstr .= "&status_filter=".$status_filter;
$qstr .= "&pg_filter=".$pg_filter;
$qstr .= "&auth_filter=".$auth_filter;
$qstr .= "&noti_filter=".$noti_filter;
$qstr .= "&sfl=".$sfl;
$qstr .= "&stx=".$stx;
echo get_paging_news(G5_IS_MOBILE ? "5" : "5", $page, $total_page, '?' . $qstr . '&amp;page=');
?>


<script>
var currentCancelPkId = null;
var currentCancelAmount = 0;

function openCancelModal(pk_id, app_no, goods_name, amount) {
	currentCancelPkId = pk_id;
	currentCancelAmount = amount;
	$('#cancelAppNo').text(app_no || '-');
	$('#cancelGoodsName').text(goods_name || '-');
	$('#cancelAmount').text(Number(amount).toLocaleString() + '원');
	$('#cancelModalOverlay').addClass('show');
}

function closeCancelModal() {
	$('#cancelModalOverlay').removeClass('show');
	currentCancelPkId = null;
}

function confirmCancel() {
	if(!currentCancelPkId) return;

	// 취소 전에 값 저장 (closeCancelModal에서 null로 초기화되므로)
	var pkId = currentCancelPkId;
	var cancelAmount = currentCancelAmount;

	closeCancelModal();

	// 로딩 스피너 표시
	$('#loadingOverlay').addClass('show');

	// AJAX로 취소 요청
	$.ajax({
		url: './manual_payment_api.php',
		type: 'POST',
		dataType: 'json',
		data: {
			action: 'cancel',
			pk_id: pkId,
			cancel_name: '관리자',
			cancel_reason: '관리자 취소',
			cancel_amount: cancelAmount
		},
		success: function(response) {
			$('#loadingOverlay').removeClass('show');

			if(response.success) {
				// 성공 결과 모달
				$('#cancelResultHeader').removeClass('fail').addClass('success');
				$('#cancelResultIcon').removeClass('fa-times-circle').addClass('fa-check-circle');
				$('#cancelResultTitle').text('취소 완료');
				$('#cancelResultMessage').text('거래가 정상적으로 취소되었습니다.');
				$('#cancelResultDetail').html(
					'취소금액: <strong>' + Number(response.data.cancel_amount).toLocaleString() + '원</strong>'
				);
			} else {
				// 실패 결과 모달
				$('#cancelResultHeader').removeClass('success').addClass('fail');
				$('#cancelResultIcon').removeClass('fa-check-circle').addClass('fa-times-circle');
				$('#cancelResultTitle').text('취소 실패');
				$('#cancelResultMessage').text(response.message || '취소 처리 중 오류가 발생했습니다.');
				$('#cancelResultDetail').text(response.error_code ? '[' + response.error_code + ']' : '');
			}
			$('#cancelResultOverlay').addClass('show');
		},
		error: function(xhr, status, error) {
			$('#loadingOverlay').removeClass('show');

			// 에러 결과 모달
			$('#cancelResultHeader').removeClass('success').addClass('fail');
			$('#cancelResultIcon').removeClass('fa-check-circle').addClass('fa-times-circle');
			$('#cancelResultTitle').text('취소 실패');
			$('#cancelResultMessage').text('서버 오류가 발생했습니다.');
			$('#cancelResultDetail').text(error);
			$('#cancelResultOverlay').addClass('show');
		}
	});
}

function closeCancelResult() {
	$('#cancelResultOverlay').removeClass('show');
	// 페이지 새로고침
	location.reload();
}

// ESC 키로 모달 닫기
$(document).keydown(function(e) {
	if(e.keyCode == 27) {
		closeCancelModal();
	}
});

// 오버레이 클릭 시 모달 닫기
$('#cancelModalOverlay').click(function(e) {
	if(e.target === this) {
		closeCancelModal();
	}
});

// 영수증 열기
function openReceipt(pk_id) {
	window.open('./receipt_keyin.php?pk_id=' + pk_id, 'receipt_keyin', 'width=360,height=700,scrollbars=yes');
}
</script>

<?php } else { ?>
<!-- ===== 가맹점용 화면 (관리자와 동일, 일부 컬럼 숨김) ===== -->
<div class="manual-list-header">
	<div class="manual-list-header-top">
		<div class="manual-list-title">
			<i class="fa fa-list-alt"></i>
			수기결제 내역
		</div>
	</div>
	<div class="manual-list-header-bottom">
		<div class="manual-list-stats">
			<div class="manual-list-stat approved">
				승인 <span><?php echo number_format($stat['approved_count']); ?>건</span> / <span><?php echo number_format($stat['approved_amount']); ?>원</span>
			</div>
			<div class="manual-list-stat failed">
				실패 <span><?php echo number_format($stat['failed_count']); ?>건</span>
			</div>
			<div class="manual-list-stat cancelled">
				취소 <span><?php echo number_format($stat['cancelled_count']); ?>건</span> / <span><?php echo number_format($stat['cancelled_amount']); ?>원</span>
			</div>
			<div class="manual-list-stat total">
				전체 <span><?php echo number_format($total_count); ?>건</span>
			</div>
		</div>
	</div>
</div>

<form id="fsearch" name="fsearch" method="get">
<input type="hidden" name="p" value="<?php echo $p; ?>">
<div class="manual-list-search">
	<div class="manual-list-search-row">
		<div class="manual-list-search-group">
			<input type="text" name="fr_date" value="<?php echo $fr_date ?>" id="fr_date" placeholder="시작일">
			<span>~</span>
			<input type="text" name="to_date" value="<?php echo $to_date ?>" id="to_date" placeholder="종료일">
		</div>
		<div class="date-btns">
			<button type="submit" onclick="javascript:set_date('오늘');">오늘</button>
			<button type="submit" onclick="javascript:set_date('어제');">어제</button>
			<button type="submit" onclick="javascript:set_date('이번달');">이번달</button>
			<button type="submit" onclick="javascript:set_date('지난주');">지난주</button>
			<button type="submit" onclick="javascript:set_date('지난달');">지난달</button>
			<button type="submit" onclick="javascript:set_date('전체');">전체</button>
		</div>
		<div class="search-divider"></div>
		<div class="manual-list-search-group">
			<select name="status_filter">
				<option value="">상태전체</option>
				<option value="approved" <?php if($status_filter == 'approved') echo 'selected'; ?>>승인</option>
				<option value="failed" <?php if($status_filter == 'failed') echo 'selected'; ?>>실패</option>
				<option value="cancelled" <?php if($status_filter == 'cancelled') echo 'selected'; ?>>취소</option>
			</select>
		</div>
		<div class="search-divider"></div>
		<div class="radio-group">
			<label><input type="radio" name="sfl" value="pk_app_no" <?php echo get_checked($sfl, "pk_app_no"); ?> checked>승인번호</label>
			<label><input type="radio" name="sfl" value="pk_goods_name" <?php echo get_checked($sfl, "pk_goods_name"); ?>>상품명</label>
			<label><input type="radio" name="sfl" value="pk_buyer_name" <?php echo get_checked($sfl, "pk_buyer_name"); ?>>구매자</label>
			<label><input type="radio" name="sfl" value="pk_card_no" <?php echo get_checked($sfl, "pk_card_no"); ?>>카드번호</label>
		</div>
		<div class="search-divider"></div>
		<div class="search-input-group">
			<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" placeholder="검색어">
			<button type="submit" class="btn-search">검색</button>
			<button type="button" class="btn-excel" onclick="downloadCsv()"><i class="fa fa-file-excel-o"></i> 엑셀</button>
		</div>
	</div>
</div>
</form>

<script>
function downloadCsv() {
	var params = new URLSearchParams(window.location.search);
	params.delete('p');
	var url = 'manual_payment_excel.php?' + params.toString();

	// iframe으로 다운로드 (로딩바 문제 해결)
	var iframe = document.createElement('iframe');
	iframe.style.display = 'none';
	iframe.src = url;
	document.body.appendChild(iframe);

	// 5초 후 iframe 제거
	setTimeout(function() {
		document.body.removeChild(iframe);
	}, 5000);
}
</script>

<div class="m_board_scroll">
	<div class="m_table_wrap" style="padding-bottom:115px;">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
				<tr>
					<th style="width:50px;">번호</th>
					<th>주문번호</th>
					<th>상품명</th>
					<th>금액</th>
					<th>할부</th>
					<th>카드사</th>
					<th>구매자명</th>
					<th>구매자연락처</th>
					<th>상태</th>
					<th>승인번호</th>
					<th>요청일시</th>
					<th>취소일시</th>
					<th>응답메시지</th>
					<th>관리</th>
				</tr>
			</thead>
			<tbody>
				<?php
				if($total_count == 0) {
				?>
				<tr>
					<td colspan="14" class="center" style="padding: 40px 0; color: #999;">
						<i class="fa fa-inbox" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
						조회된 내역이 없습니다.
					</td>
				</tr>
				<?php
				} else {
					// 결과를 다시 조회 (가맹점용)
					$result = sql_query($sql);
					for ($i=0; $row=sql_fetch_array($result); $i++) {
						$num = number_format($total_count - ($page - 1) * $rows - $i);

						// 상태 표시
						$status_class = '';
						$status_text = '';
						$row_class = '';
						switch($row['pk_status']) {
							case 'approved':
								$status_class = 'approved';
								$status_text = '승인';
								break;
							case 'failed':
								$status_class = 'failed';
								$status_text = '실패';
								$row_class = 'row-failed';
								break;
							case 'cancelled':
								$status_class = 'cancelled';
								$status_text = '취소';
								$row_class = 'row-cancelled';
								break;
							case 'partial_cancelled':
								$status_class = 'cancelled';
								$status_text = '부분취소';
								$row_class = 'row-cancelled';
								break;
							case 'pending':
								$status_class = 'pending';
								$status_text = '대기';
								break;
						}

						// 할부 표시
						if($row['pk_installment'] == '00' || $row['pk_installment'] == '0' || !$row['pk_installment']) {
							$installment_text = '일시불';
						} else {
							$installment_text = intval($row['pk_installment']) . '개월';
						}
				?>
				<tr class="<?php echo $row_class; ?>">
					<td class="center"><?php echo $num; ?></td>
					<td class="order-no"><?php echo htmlspecialchars($row['pk_order_no']); ?></td>
					<td><?php echo htmlspecialchars($row['pk_goods_name']); ?></td>
					<td class="right"><?php echo number_format($row['pk_amount']); ?></td>
					<td class="center"><?php echo $installment_text; ?></td>
					<td class="center">
						<?php if($row['pk_card_issuer']) { echo htmlspecialchars(str_replace('카드', '', $row['pk_card_issuer'])); } ?>
						<?php if($row['pk_card_no_masked']) { ?> <small style="color:#999;"><?php echo $row['pk_card_no_masked']; ?></small><?php } ?>
					</td>
					<td class="center"><?php echo $row['pk_buyer_name'] ? htmlspecialchars($row['pk_buyer_name']) : '-'; ?></td>
					<td class="center"><?php echo $row['pk_buyer_phone'] ? htmlspecialchars($row['pk_buyer_phone']) : '-'; ?></td>
					<td class="center">
						<?php
						// 툴팁에 표시할 상세 메시지 생성
						$tooltip_msg = '';
						if($row['pk_res_code'] || $row['pk_res_msg']) {
							if($row['pk_res_code']) $tooltip_msg .= '[' . $row['pk_res_code'] . '] ';
							if($row['pk_res_msg']) $tooltip_msg .= htmlspecialchars($row['pk_res_msg']);
						}
						if($row['pk_status'] == 'cancelled' && $row['pk_cancel_reason']) {
							$tooltip_msg .= ($tooltip_msg ? '<br>' : '') . '취소사유: ' . htmlspecialchars($row['pk_cancel_reason']);
						}
						?>
						<div class="status-wrapper">
							<span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
							<?php if($tooltip_msg) { ?>
							<div class="status-tooltip"><?php echo $tooltip_msg; ?></div>
							<?php } ?>
						</div>
					</td>
					<td class="center"><?php echo $row['pk_app_no'] ? $row['pk_app_no'] : '-'; ?></td>
					<td class="center"><?php echo $row['pk_created_at']; ?></td>
					<td class="center"><?php
						if($row['pk_status'] == 'cancelled' && $row['pk_cancel_date']) {
							// 14자리 형식(YYYYMMDDHHmmss)을 Y-m-d H:i:s로 변환
							$cancel_date = $row['pk_cancel_date'];
							if(strlen($cancel_date) == 14 && is_numeric($cancel_date)) {
								echo substr($cancel_date, 0, 4) . '-' . substr($cancel_date, 4, 2) . '-' . substr($cancel_date, 6, 2) . ' ' . substr($cancel_date, 8, 2) . ':' . substr($cancel_date, 10, 2) . ':' . substr($cancel_date, 12, 2);
							} else {
								echo $cancel_date;
							}
						} else {
							echo '-';
						}
					?></td>
					<td class="center" style="max-width:150px; word-break:break-all; font-size:11px;"><?php echo $row['pk_res_msg'] ? htmlspecialchars($row['pk_res_msg']) : '-'; ?></td>
					<td class="center">
						<?php if($row['pk_status'] == 'approved') { ?>
						<button type="button" class="btn-receipt" onclick="openReceipt(<?php echo $row['pk_id']; ?>)">영수증</button>
						<?php if($is_admin || $row['mkc_cancel_yn'] == 'Y') { ?>
						<button type="button" class="btn-cancel" onclick="openCancelModal(<?php echo $row['pk_id']; ?>, '<?php echo $row['pk_app_no']; ?>', '<?php echo addslashes($row['pk_goods_name']); ?>', <?php echo $row['pk_amount']; ?>)">취소</button>
						<?php } ?>
						<?php } else if($row['pk_status'] == 'cancelled') { ?>
						<button type="button" class="btn-receipt" onclick="openReceipt(<?php echo $row['pk_id']; ?>)">영수증</button>
						<?php } else { ?>
						-
						<?php } ?>
					</td>
				</tr>
				<?php
					}
				}
				?>
			</tbody>
		</table>
	</div>
</div>

<?php
$qstr = "p=".$p;
$qstr .= "&fr_date=".$fr_date;
$qstr .= "&to_date=".$to_date;
$qstr .= "&status_filter=".$status_filter;
$qstr .= "&sfl=".$sfl;
$qstr .= "&stx=".$stx;
echo get_paging_news(G5_IS_MOBILE ? "5" : "5", $page, $total_page, '?' . $qstr . '&amp;page=');
?>


<script>
var currentCancelPkId = null;
var currentCancelAmount = 0;

function openCancelModal(pk_id, app_no, goods_name, amount) {
	currentCancelPkId = pk_id;
	currentCancelAmount = amount;
	$('#cancelAppNo').text(app_no || '-');
	$('#cancelGoodsName').text(goods_name || '-');
	$('#cancelAmount').text(Number(amount).toLocaleString() + '원');
	$('#cancelModalOverlay').addClass('show');
}

function closeCancelModal() {
	$('#cancelModalOverlay').removeClass('show');
	currentCancelPkId = null;
}

function confirmCancel() {
	if(!currentCancelPkId) return;

	// 취소 전에 값 저장 (closeCancelModal에서 null로 초기화되므로)
	var pkId = currentCancelPkId;
	var cancelAmount = currentCancelAmount;

	closeCancelModal();

	// 로딩 스피너 표시
	$('#loadingOverlay').addClass('show');

	// AJAX로 취소 요청
	$.ajax({
		url: './manual_payment_api.php',
		type: 'POST',
		dataType: 'json',
		data: {
			action: 'cancel',
			pk_id: pkId,
			cancel_name: '가맹점',
			cancel_reason: '가맹점 취소',
			cancel_amount: cancelAmount
		},
		success: function(response) {
			$('#loadingOverlay').removeClass('show');

			if(response.success) {
				// 성공 결과 모달
				$('#cancelResultHeader').removeClass('fail').addClass('success');
				$('#cancelResultIcon').removeClass('fa-times-circle').addClass('fa-check-circle');
				$('#cancelResultTitle').text('취소 완료');
				$('#cancelResultMessage').text('거래가 정상적으로 취소되었습니다.');
				$('#cancelResultDetail').html(
					'취소금액: <strong>' + Number(response.data.cancel_amount).toLocaleString() + '원</strong>'
				);
			} else {
				// 실패 결과 모달
				$('#cancelResultHeader').removeClass('success').addClass('fail');
				$('#cancelResultIcon').removeClass('fa-check-circle').addClass('fa-times-circle');
				$('#cancelResultTitle').text('취소 실패');
				$('#cancelResultMessage').text(response.message || '취소 처리 중 오류가 발생했습니다.');
				$('#cancelResultDetail').text(response.error_code ? '[' + response.error_code + ']' : '');
			}
			$('#cancelResultOverlay').addClass('show');
		},
		error: function(xhr, status, error) {
			$('#loadingOverlay').removeClass('show');

			// 에러 결과 모달
			$('#cancelResultHeader').removeClass('success').addClass('fail');
			$('#cancelResultIcon').removeClass('fa-check-circle').addClass('fa-times-circle');
			$('#cancelResultTitle').text('취소 실패');
			$('#cancelResultMessage').text('서버 오류가 발생했습니다.');
			$('#cancelResultDetail').text(error);
			$('#cancelResultOverlay').addClass('show');
		}
	});
}

function closeCancelResult() {
	$('#cancelResultOverlay').removeClass('show');
	// 페이지 새로고침
	location.reload();
}

// ESC 키로 모달 닫기
$(document).keydown(function(e) {
	if(e.keyCode == 27) {
		closeCancelModal();
	}
});

// 오버레이 클릭 시 모달 닫기
$('#cancelModalOverlay').click(function(e) {
	if(e.target === this) {
		closeCancelModal();
	}
});

// 영수증 열기
function openReceipt(pk_id) {
	window.open('./receipt_keyin.php?pk_id=' + pk_id, 'receipt_keyin', 'width=360,height=700,scrollbars=yes');
}
</script>
<?php } ?>

<!-- 플로팅 신규결제 버튼 (관리자 또는 수기결제창 사용 회원만 표시) -->
<?php if($is_admin || $member['mb_keyin_popup'] == '1') { ?>
<div class="floating-new-payment">
	<a href="/?p=manual_payment_module">
		<i class="fa fa-credit-card"></i>
		신규결제
	</a>
</div>
<?php } ?>

<?php
include_once('./_tail.php');
?>
