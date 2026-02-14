<?php

	if(!$is_admin) {
		alert("잘못된 접근입니다.");
	}

	$title1 = "결제관리";
	$title2 = "결제이상건(FDS)";

	if(!$fr_date) { $fr_date = date("Ymd"); }
	if(!$to_date) { $to_date = date("Ymd"); }

	if(!isset($fr_time) || $fr_time === '') { $fr_time = '0000'; }
	if(!isset($to_time) || $to_time === '') { $to_time = '2359'; }
	$fr_time = preg_replace('/[^0-9]/', '', $fr_time);
	$to_time = preg_replace('/[^0-9]/', '', $to_time);
	if(strlen($fr_time) != 4) $fr_time = '0000';
	if(strlen($to_time) != 4) $to_time = '2359';
	$fr_hour = substr($fr_time, 0, 2);
	$fr_min  = substr($fr_time, 2, 2);
	$to_hour = substr($to_time, 0, 2);
	$to_min  = substr($to_time, 2, 2);

	$fr_dates = date("Y-m-d", strtotime($fr_date));
	$to_dates = date("Y-m-d", strtotime($to_date));

	$sql_common = " from g5_payment ";

	if(adm_sql_common) {
		$adm_sql = " mb_1 IN (".adm_sql_common.")";
	} else {
		$adm_sql = " (1)";
	}

	if ($fr_date == "all" && $to_date == "all") {
		$sql_search = " where ".$adm_sql." and mb_6_name != '' ";
	} else {
		$sql_search = " where ".$adm_sql." and (pay_datetime BETWEEN '{$fr_dates} {$fr_hour}:{$fr_min}:00' and '{$to_dates} {$to_hour}:{$to_min}:59')  and mb_6_name != '' ";
	}

	if($pay_num) {
		$sql_search .= " and pay_num = '{$pay_num}' ";
	}

	if($dv_tid) {
		$sql_search .= " and (dv_tid = '{$dv_tid}') ";
	}

	if($mb_6_name) {
		$sql_search .= " and (mb_6_name = '{$mb_6_name}') ";
	}

	if($gname) { $sql_search .= " and level_company_name like '%{$gname}%' "; }

	if($l2) { $sql_search .= " and mb_pid2 = '{$l2}' "; }
	if($l3) { $sql_search .= " and mb_pid3 = '{$l3}' "; }
	if($l4) { $sql_search .= " and mb_pid4 = '{$l4}' "; }
	if($l5) { $sql_search .= " and mb_pid5 = '{$l5}' "; }
	if($l6) { $sql_search .= " and mb_pid6 = '{$l6}' "; }
	if($l7) { $sql_search .= " and mb_pid7 = '{$l7}' "; }

	if ($stx) {
		$sql_search .= " and ( ";
		switch ($sfl) {
			case "gr_id" :
			case "gr_admin" :
				$sql_search .= " ({$sfl} = '{$stx}') ";
				break;
			default :
				$sql_search .= " ({$sfl} like '%{$stx}%') ";
				break;
		}
		$sql_search .= " ) ";
	}

	// FDS 필터: pp_limit이 Y인 건만
	$sql_search .= " and (pp_limit_3m = 'Y' or pp_limit_5m = 'Y') ";

	// FDS 유형 필터 (GET param: fds_type)
	if($fds_type == '3m') {
		$sql_search .= " and pp_limit_3m = 'Y' ";
	} else if($fds_type == '5m') {
		$sql_search .= " and pp_limit_5m = 'Y' ";
	}

	if ($sst)
		$sql_order = " order by {$sst} {$sod} ";
	else
		$sql_order = " order by pay_datetime desc ";

	$sql = " select count(*) as cnt, sum(pay) as total_pay";
	$sql .= ", sum(if(pay_type = 'Y', pay, 0)) as total_Y_pay, sum(if(pay_type != 'Y', pay, 0)) as total_M_pay, count(if(pay_type = 'Y', 1, null)) as count_Y_pay, count(if(pay_type != 'Y', 1, null)) as count_M_pay {$sql_common} {$sql_search} {$sql_order} ";
	$row = sql_fetch($sql);

	$total_count = $row['cnt'];
	$total_Y_pay  = $row['total_Y_pay'];
	$total_M_pay  = $row['total_M_pay'];
	$count_Y_pay  = $row['count_Y_pay'];
	$count_M_pay  = $row['count_M_pay'];
	$total_pay = $total_Y_pay + $total_M_pay;
	$page_count = "30";
	if($page_count) {
		$rows = $page_count;
	} else {
		$rows = $config['cf_page_rows'];
	}

	$total_page  = ceil($total_count / $rows);
	if ($page < 1) $page = 1;
	$from_record = ($page - 1) * $rows;

	$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
	$xlsx_sql = "select * {$sql_common} {$sql_search} {$sql_order} ";
	$result = sql_query($sql);

	// FDS 유형별 카운트 (상단 요약용)
	$fds_sql = "SELECT
		COUNT(*) as fds_total,
		SUM(IF(pp_limit_3m='Y' AND pp_limit_5m='Y', 1, 0)) as fds_both,
		SUM(IF(pp_limit_3m='Y' AND (pp_limit_5m IS NULL OR pp_limit_5m != 'Y'), 1, 0)) as fds_3m_only,
		SUM(IF(pp_limit_5m='Y' AND (pp_limit_3m IS NULL OR pp_limit_3m != 'Y'), 1, 0)) as fds_5m_only,
		SUM(pay) as fds_total_pay
		{$sql_common} {$sql_search}";
	$fds_row = sql_fetch($fds_sql);

	// 가맹점별 이상건 요약 (상단 대시보드용)
	$fds_merchant_sql = "SELECT mb_6_name,
		COUNT(*) as cnt,
		SUM(pay) as total_pay,
		SUM(IF(pp_limit_3m='Y', 1, 0)) as cnt_3m,
		SUM(IF(pp_limit_5m='Y', 1, 0)) as cnt_5m,
		COUNT(DISTINCT pay_card_num) as card_cnt,
		MAX(pay_datetime) as last_time
		{$sql_common} {$sql_search}
		GROUP BY mb_6_name ORDER BY cnt DESC LIMIT 20";
	$fds_merchant_result = sql_query($fds_merchant_sql);
	$fds_merchants = [];
	$fds_merchant_counts = [];
	while($mr = sql_fetch_array($fds_merchant_result)) {
		$fds_merchants[] = $mr;
		$fds_merchant_counts[$mr['mb_6_name']] = $mr['cnt'];
	}
	$fds_merchant_total = count($fds_merchants);

	// 현재 검색 조건에서 mb_6_name 필터 URL 생성용
	$fds_base_qstr = "p=payment_fds&fr_date=".$fr_date."&to_date=".$to_date."&fds_type=".$fds_type;
	if($fr_time) $fds_base_qstr .= "&fr_time=".$fr_time;
	if($to_time) $fds_base_qstr .= "&to_time=".$to_time;
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
/* --- FDS 헤더 (레드/오렌지 경고 테마) --- */
.fds-header {
	background: linear-gradient(135deg, #b71c1c 0%, #d32f2f 100%);
	border-radius: 8px;
	padding: 12px 16px;
	margin-bottom: 10px;
	box-shadow: 0 2px 8px rgba(183, 28, 28, 0.2);
}
.fds-header-top {
	display: flex;
	align-items: center;
	justify-content: space-between;
	flex-wrap: wrap;
	gap: 10px;
}
.fds-title {
	color: #fff;
	font-size: 16px;
	font-weight: 600;
	display: flex;
	align-items: center;
	gap: 8px;
}
.fds-title i { font-size: 14px; opacity: 0.8; }
.fds-stats {
	display: flex;
	flex-wrap: wrap;
	gap: 6px;
}
.fds-stat {
	display: inline-flex;
	align-items: center;
	background: rgba(255,255,255,0.12);
	border-radius: 4px;
	padding: 4px 10px;
	font-size: 12px;
	color: rgba(255,255,255,0.85);
	gap: 6px;
}
.fds-stat.warn { background: rgba(255,193,7,0.35); }
.fds-stat.danger { background: rgba(255,87,34,0.35); }
.fds-stat.both { background: rgba(255,255,255,0.25); }
.fds-stat.total { background: rgba(255,193,7,0.3); color: #fff; font-weight: 600; }
.fds-stat span { color: #fff; font-weight: 600; }

/* --- 검색 테이블 레이아웃 (payment.php 패턴 동일) --- */
.ps-box {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	margin-bottom: 10px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.04);
	overflow: hidden;
}
.ps-table {
	width: 100%;
	border-collapse: collapse;
}
.ps-table th,
.ps-table td {
	padding: 8px 10px;
	vertical-align: middle;
	text-align: left;
	border-bottom: 1px solid #f0f0f0;
	font-size: 13px;
	overflow: visible !important;
}
.ps-table tr:last-child th,
.ps-table tr:last-child td {
	border-bottom: none;
}
.ps-table th {
	background: #f5f6fa;
	color: #b71c1c;
	font-weight: 700;
	font-size: 12px;
	white-space: nowrap;
	width: 52px;
	min-width: 52px;
	text-align: center;
	border-right: 2px solid #b71c1c;
}
.ps-table td { background: #fff; }
.ps-cell {
	display: flex;
	align-items: center;
	flex-wrap: wrap;
	gap: 8px;
}
/* 날짜 입력 */
.ps-date-group {
	display: flex;
	align-items: center;
	gap: 4px;
}
.ps-date-group input[type="text"] {
	width: 88px;
	padding: 5px 8px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-size: 13px;
	background: #f8f9fa;
	text-align: center;
}
.ps-date-group input[type="text"]:focus {
	outline: none;
	border-color: #b71c1c;
	background: #fff;
}
.ps-date-group .sep { color: #bbb; font-size: 12px; }
.ps-datetime-row {
	display: flex;
	align-items: center;
	gap: 8px;
}
.ps-time-group {
	display: flex;
	align-items: center;
	gap: 4px;
}
.ps-time-group input[type="text"] {
	width: 54px;
	padding: 5px 6px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-size: 13px;
	background: #f8f9fa;
	text-align: center;
	cursor: pointer;
}
.ps-time-group input[type="text"]:focus {
	outline: none;
	border-color: #b71c1c;
	background: #fff;
}
.ps-time-group .sep { color: #bbb; font-size: 12px; }
.flatpickr-time input:hover,
.flatpickr-time .flatpickr-am-pm:hover,
.flatpickr-time input:focus,
.flatpickr-time .flatpickr-am-pm:focus { background: #ffebee; }
.flatpickr-time .numInputWrapper span.arrowUp:after { border-bottom-color: #b71c1c; }
.flatpickr-time .numInputWrapper span.arrowDown:after { border-top-color: #b71c1c; }
.ps-vdiv {
	width: 1px;
	height: 22px;
	background: #e0e0e0;
	margin: 0 2px;
	flex-shrink: 0;
}
.ps-date-btns {
	display: flex;
	gap: 3px;
}
.ps-date-btns button {
	padding: 4px 8px;
	font-size: 11px;
	border: 1px solid #ddd;
	background: #f8f9fa;
	border-radius: 3px;
	cursor: pointer;
	color: #555;
	transition: all 0.15s;
	white-space: nowrap;
}
.ps-date-btns button:hover {
	background: #b71c1c;
	border-color: #b71c1c;
	color: #fff;
}
.ps-radio-group {
	display: flex;
	align-items: center;
	gap: 10px;
}
.ps-radio-group label {
	display: flex;
	align-items: center;
	gap: 3px;
	font-size: 12px;
	color: #555;
	cursor: pointer;
	white-space: nowrap;
}
.ps-radio-group input[type="radio"] { margin: 0; accent-color: #b71c1c; }
.ps-search-input {
	display: flex;
	align-items: center;
	gap: 4px;
}
.ps-search-input input[type="text"] {
	width: 120px;
	padding: 5px 10px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-size: 13px;
}
.ps-search-input input[type="text"]:focus {
	outline: none;
	border-color: #b71c1c;
}
.btn-fds-search {
	padding: 5px 14px;
	background: #b71c1c;
	color: #fff;
	border: none;
	border-radius: 4px;
	font-size: 12px;
	cursor: pointer;
	transition: background 0.15s;
}
.btn-fds-search:hover { background: #d32f2f; }
.btn-excel {
	padding: 5px 10px;
	background: #2e7d32;
	color: #fff;
	border: none;
	border-radius: 4px;
	font-size: 12px;
	cursor: pointer;
	transition: background 0.15s;
}
.btn-excel:hover { background: #388e3c; }

/* --- FDS 가맹점 대시보드 --- */
.fds-dashboard {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	margin-bottom: 10px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.04);
	overflow: hidden;
}
.fds-dashboard-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 10px 16px;
	background: #fafafa;
	border-bottom: 1px solid #e0e0e0;
}
.fds-dashboard-header h3 {
	margin: 0;
	font-size: 13px;
	font-weight: 600;
	color: #333;
	letter-spacing: -0.3px;
}
.fds-dashboard-header h3 i {
	margin-right: 6px;
	color: #b71c1c;
}
.fds-dashboard-header .fds-merchant-count {
	font-size: 11px;
	color: #888;
}
.fds-dashboard-header .fds-merchant-count em {
	color: #b71c1c;
	font-style: normal;
	font-weight: 600;
}
.fds-card-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
	gap: 8px;
	padding: 10px;
}
.fds-card {
	display: flex;
	align-items: stretch;
	background: #fff;
	border: 1px solid #e8e8e8;
	border-radius: 6px;
	padding: 10px 12px;
	transition: border-color 0.15s, box-shadow 0.15s;
	cursor: pointer;
	text-decoration: none;
	color: inherit;
	min-height: 60px;
}
.fds-card:hover {
	border-color: #d32f2f;
	box-shadow: 0 2px 8px rgba(211, 47, 47, 0.1);
}
.fds-card.active {
	border-color: #b71c1c;
	background: #fff5f5;
}
.fds-card-rank {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 28px;
	min-width: 28px;
	margin-right: 10px;
	font-size: 14px;
	font-weight: 700;
	border-radius: 3px;
}
.fds-rank-critical { color: #b71c1c; }
.fds-rank-high { color: #e65100; }
.fds-rank-medium { color: #f9a825; }
.fds-rank-low { color: #999; }
.fds-card-body {
	flex: 1;
	min-width: 0;
}
.fds-card-name {
	font-size: 13px;
	font-weight: 600;
	color: #333;
	margin-bottom: 4px;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}
.fds-card-meta {
	display: flex;
	align-items: center;
	gap: 6px;
	flex-wrap: wrap;
}
.fds-card-meta span {
	font-size: 11px;
	color: #888;
	white-space: nowrap;
}
.fds-card-stats {
	display: flex;
	flex-direction: column;
	align-items: flex-end;
	justify-content: center;
	margin-left: 10px;
	min-width: 60px;
}
.fds-card-cnt {
	font-size: 18px;
	font-weight: 700;
	line-height: 1.1;
}
.fds-card-cnt-critical { color: #b71c1c; }
.fds-card-cnt-high { color: #e65100; }
.fds-card-cnt-medium { color: #f9a825; }
.fds-card-cnt-low { color: #999; }
.fds-card-cnt-unit {
	font-size: 10px;
	color: #888;
	margin-top: 1px;
}
.fds-badge-3m {
	display: inline-block;
	background: #ffc107;
	color: #333;
	padding: 0px 5px;
	border-radius: 2px;
	font-size: 10px;
	font-weight: 700;
	line-height: 16px;
}
.fds-badge-5m {
	display: inline-block;
	background: #ff9800;
	color: #fff;
	padding: 0px 5px;
	border-radius: 2px;
	font-size: 10px;
	font-weight: 700;
	line-height: 16px;
}
.fds-badge-both {
	display: inline-block;
	background: #d32f2f;
	color: #fff;
	padding: 0px 5px;
	border-radius: 2px;
	font-size: 10px;
	font-weight: 700;
	line-height: 16px;
}
.fds-card-amount {
	font-size: 11px;
	font-weight: 500;
	color: #b71c1c;
	text-align: right;
	margin-top: 2px;
}
.fds-empty {
	padding: 30px;
	text-align: center;
	color: #999;
	font-size: 13px;
}

/* --- 테이블 FDS 뱃지 --- */
.fds-tag-3m {
	display: inline-block;
	background: #ffc107;
	color: #333;
	padding: 1px 6px;
	border-radius: 3px;
	font-size: 11px;
	font-weight: bold;
}
.fds-tag-5m {
	display: inline-block;
	background: #ff9800;
	color: #fff;
	padding: 1px 6px;
	border-radius: 3px;
	font-size: 11px;
	font-weight: bold;
}
.fds-merchant-badge {
	display: inline-block;
	background: #d32f2f;
	color: #fff;
	padding: 0 5px;
	border-radius: 10px;
	font-size: 10px;
	margin-left: 3px;
}

/* 반응형 */
@media (max-width: 768px) {
	.fds-header { padding: 10px 12px; border-radius: 6px; margin-bottom: 8px; }
	.fds-header-top { flex-direction: column; align-items: flex-start; gap: 8px; }
	.fds-title { font-size: 15px; }
	.fds-stats { width: 100%; gap: 4px; }
	.fds-stat { padding: 3px 7px; font-size: 11px; }

	.ps-box { border-radius: 6px; margin-bottom: 8px; }
	.ps-table, .ps-table thead, .ps-table tbody, .ps-table tr, .ps-table th, .ps-table td {
		display: block;
		width: 100%;
	}
	.ps-table th {
		border-right: none;
		border-bottom: none;
		background: #b71c1c;
		color: #fff;
		font-size: 11px;
		font-weight: 600;
		padding: 6px 12px;
		text-align: left;
		letter-spacing: 0.5px;
	}
	.ps-table th i { margin-right: 4px; }
	.ps-table td {
		padding: 8px 12px 10px;
		border-bottom: 1px solid #eee;
	}
	.ps-table tr:last-child td { border-bottom: none; }

	.ps-cell {
		flex-direction: column;
		align-items: stretch;
		gap: 8px;
	}
	.ps-vdiv { display: none; }

	.ps-datetime-row {
		display: flex;
		align-items: center;
		gap: 6px;
		width: 100%;
	}
	.ps-datetime-row .ps-vdiv {
		display: block;
		height: 20px;
		margin: 0;
	}
	.ps-date-group {
		flex: 0 1 auto;
		min-width: 0;
	}
	.ps-date-group input[type="text"] {
		width: 72px;
		min-width: 0;
		font-size: 13px;
		padding: 7px 2px;
	}
	.ps-time-group {
		flex-shrink: 0;
	}
	.ps-time-group input[type="text"] {
		width: 46px;
		font-size: 13px;
		padding: 7px 4px;
	}

	.ps-date-btns {
		display: flex;
		gap: 4px;
		overflow-x: auto;
		-webkit-overflow-scrolling: touch;
		scrollbar-width: none;
		padding-bottom: 2px;
	}
	.ps-date-btns::-webkit-scrollbar { display: none; }
	.ps-date-btns button {
		flex-shrink: 0;
		padding: 6px 12px;
		font-size: 12px;
		border-radius: 16px;
		border: 1px solid #ef9a9a;
		background: #ffebee;
		color: #b71c1c;
		font-weight: 500;
	}
	.ps-date-btns button:hover,
	.ps-date-btns button:active {
		background: #b71c1c;
		border-color: #b71c1c;
		color: #fff;
	}

	.ps-radio-group {
		display: flex;
		gap: 2px;
		overflow-x: auto;
		-webkit-overflow-scrolling: touch;
		scrollbar-width: none;
		padding-bottom: 2px;
	}
	.ps-radio-group::-webkit-scrollbar { display: none; }
	.ps-radio-group label {
		flex-shrink: 0;
		padding: 5px 10px;
		font-size: 12px;
		border: 1px solid #ddd;
		border-radius: 16px;
		background: #f8f9fa;
		transition: all 0.15s;
		gap: 3px;
	}
	.ps-radio-group label:has(input:checked) {
		background: #b71c1c;
		border-color: #b71c1c;
		color: #fff;
	}
	.ps-radio-group input[type="radio"] {
		width: 0;
		height: 0;
		margin: 0;
		opacity: 0;
		position: absolute;
	}

	.ps-search-input {
		display: flex;
		width: 100%;
		gap: 6px;
	}
	.ps-search-input input[type="text"] {
		flex: 1;
		min-width: 0;
		width: auto;
		padding: 8px 12px;
		font-size: 14px;
		border-radius: 6px;
	}
	.btn-fds-search {
		padding: 8px 16px;
		font-size: 13px;
		border-radius: 6px;
		flex-shrink: 0;
	}
	.btn-excel {
		padding: 8px 12px;
		font-size: 13px;
		border-radius: 6px;
		flex-shrink: 0;
	}

	.fds-card-grid {
		grid-template-columns: 1fr;
	}
}
/* 툴팁 */
[data-tooltip].simptip-multiline:after,
.simptip-position-bottom.simptip-multiline:after {
	min-height: 100px !important;
	height: auto !important;
	box-sizing: border-box !important;
	padding: 11px !important;
	white-space: pre-line !important;
}
.m_table_wrap { overflow: visible !important; }
.td_name { overflow: visible !important; }
table, tbody, tr, td { overflow: visible !important; }
</style>

<!-- 헤더 -->
<div class="fds-header">
	<div class="fds-header-top">
		<div class="fds-title">
			<i class="fa fa-exclamation-triangle"></i>
			결제이상건(FDS)
		</div>
		<div class="fds-stats">
			<div class="fds-stat">전체 <span><?php echo number_format($fds_row['fds_total']); ?>건</span></div>
			<div class="fds-stat warn">1회 300만↑ <span><?php echo number_format($fds_row['fds_3m_only']); ?>건</span></div>
			<div class="fds-stat danger">동일카드 500만↑ <span><?php echo number_format($fds_row['fds_5m_only']); ?>건</span></div>
			<div class="fds-stat both">중복감지 <span><?php echo number_format($fds_row['fds_both']); ?>건</span></div>
			<div class="fds-stat total">합계 <span><?php echo number_format($fds_row['fds_total_pay']); ?></span></div>
		</div>
	</div>
</div>

<!-- 검색 -->
<form id="fsearch" name="fsearch" method="get">
<input type="hidden" name="p" value="<?php echo $p; ?>">
<input type="hidden" name="fr_time" id="fr_time" value="<?php echo $fr_time; ?>">
<input type="hidden" name="to_time" id="to_time" value="<?php echo $to_time; ?>">
<div class="ps-box">
	<table class="ps-table">
		<tr>
			<th><i class="fa fa-calendar"></i> 기간</th>
			<td>
				<div class="ps-cell">
					<div class="ps-datetime-row">
						<div class="ps-date-group">
							<input type="text" name="fr_date" value="<?php echo $fr_date ?>" id="fr_date" placeholder="시작일">
							<span class="sep">~</span>
							<input type="text" name="to_date" value="<?php echo $to_date ?>" id="to_date" placeholder="종료일">
						</div>
						<div class="ps-vdiv"></div>
						<div class="ps-time-group">
							<input type="text" id="fp_fr_time" value="<?php echo $fr_hour; ?>:<?php echo $fr_min; ?>" readonly>
							<span class="sep">~</span>
							<input type="text" id="fp_to_time" value="<?php echo $to_hour; ?>:<?php echo $to_min; ?>" readonly>
						</div>
					</div>
					<div class="ps-vdiv"></div>
					<div class="ps-date-btns">
						<button type="submit" onclick="javascript:set_date('오늘');">오늘</button>
						<button type="submit" onclick="javascript:set_date('어제');">어제</button>
						<button type="submit" onclick="javascript:set_date('이번달');">이번달</button>
						<button type="submit" onclick="javascript:set_date('지난주');">지난주</button>
						<button type="submit" onclick="javascript:set_date('지난달');">지난달</button>
						<button type="submit" onclick="javascript:set_date('전체');">전체</button>
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<th><i class="fa fa-filter"></i> FDS</th>
			<td>
				<div class="ps-cell">
					<div class="ps-radio-group">
						<label><input type="radio" name="fds_type" value="" <?php echo get_checked($fds_type, ""); ?> checked>전체</label>
						<label><input type="radio" name="fds_type" value="3m" <?php echo get_checked($fds_type, "3m"); ?>>1회 300만↑</label>
						<label><input type="radio" name="fds_type" value="5m" <?php echo get_checked($fds_type, "5m"); ?>>동일카드 500만↑</label>
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<th><i class="fa fa-search"></i> 검색</th>
			<td>
				<div class="ps-cell">
					<div class="ps-radio-group">
						<label><input type="radio" name="sfl" value="pay_num" <?php echo get_checked($sfl, "pay_num"); ?> checked>승번</label>
						<label><input type="radio" name="sfl" value="mb_6_name" <?php echo get_checked($sfl, "mb_6_name"); ?>>가맹</label>
						<label><input type="radio" name="sfl" value="dv_tid" <?php echo get_checked($sfl, "dv_tid"); ?>>TID</label>
						<label><input type="radio" name="sfl" value="dv_tid_ori" <?php echo get_checked($sfl, "dv_tid_ori"); ?>>본TID</label>
						<label><input type="radio" name="sfl" value="pay" <?php echo get_checked($sfl, "pay"); ?>>금액</label>
						<label><input type="radio" name="sfl" value="pay_card_name" <?php echo get_checked($sfl, "pay_card_name"); ?>>카드</label>
						<label><input type="radio" name="sfl" value="pay_card_num" <?php echo get_checked($sfl, "pay_card_num"); ?>>카번</label>
					</div>
					<div class="ps-vdiv"></div>
					<div class="ps-search-input">
						<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" placeholder="검색어">
						<button type="submit" class="btn-fds-search">검색</button>
						<button type="button" class="btn-excel" id="xlsx"><i class="fa fa-file-excel-o"></i> 엑셀</button>
					</div>
				</div>
			</td>
		</tr>
	</table>
</div>
</form>

<form action="./xlsx/payment.php" id="frm_xlsx" method="post">
<input type="hidden" name="xlsx_sql" value="<?php echo $xlsx_sql; ?>">
<input type="hidden" name="p" value="<?php echo $p; ?>">
</form>

<!-- 가맹점별 FDS 요약 대시보드 -->
<div class="fds-dashboard">
	<div class="fds-dashboard-header">
		<h3><i class="fa fa-exclamation-triangle"></i>가맹점별 이상건 현황</h3>
		<span class="fds-merchant-count">이상 가맹점 <em><?php echo $fds_merchant_total; ?></em>개<?php if($mb_6_name) { ?> &middot; <a href="./?<?php echo $fds_base_qstr; ?>" style="color:#b71c1c; font-size:11px;">필터 해제</a><?php } ?></span>
	</div>
	<?php if($fds_merchant_total > 0) { ?>
	<div class="fds-card-grid">
		<?php
		$rank = 0;
		foreach($fds_merchants as $fm) {
			$rank++;
			$cnt = intval($fm['cnt']);
			if($cnt >= 10) {
				$rank_class = 'critical';
			} else if($cnt >= 5) {
				$rank_class = 'high';
			} else if($cnt >= 3) {
				$rank_class = 'medium';
			} else {
				$rank_class = 'low';
			}
			$is_active = ($mb_6_name == $fm['mb_6_name']) ? ' active' : '';
			$filter_url = "./?{$fds_base_qstr}&mb_6_name=".urlencode($fm['mb_6_name']);
			$last_time_short = substr($fm['last_time'], 5);
		?>
		<a href="<?php echo $filter_url; ?>" class="fds-card<?php echo $is_active; ?>">
			<div class="fds-card-rank fds-rank-<?php echo $rank_class; ?>"><?php echo $rank; ?></div>
			<div class="fds-card-body">
				<div class="fds-card-name"><?php echo $fm['mb_6_name']; ?></div>
				<div class="fds-card-meta">
					<?php if($fm['cnt_3m'] > 0 && $fm['cnt_5m'] > 0) { ?>
						<span class="fds-badge-both">3M+5M</span>
						<span class="fds-badge-3m"><?php echo $fm['cnt_3m']; ?></span>
						<span class="fds-badge-5m"><?php echo $fm['cnt_5m']; ?></span>
					<?php } else if($fm['cnt_3m'] > 0) { ?>
						<span class="fds-badge-3m">3M <?php echo $fm['cnt_3m']; ?></span>
					<?php } else if($fm['cnt_5m'] > 0) { ?>
						<span class="fds-badge-5m">5M <?php echo $fm['cnt_5m']; ?></span>
					<?php } ?>
					<span><i class="fa fa-credit-card" style="font-size:10px;"></i> <?php echo $fm['card_cnt']; ?>장</span>
					<span style="color:#aaa;"><?php echo $last_time_short; ?></span>
				</div>
			</div>
			<div class="fds-card-stats">
				<div class="fds-card-cnt fds-card-cnt-<?php echo $rank_class; ?>"><?php echo $cnt; ?></div>
				<div class="fds-card-cnt-unit">건</div>
				<div class="fds-card-amount"><?php echo number_format($fm['total_pay']); ?></div>
			</div>
		</a>
		<?php } ?>
	</div>
	<?php } else { ?>
	<div class="fds-empty">검색 조건에 해당하는 이상건이 없습니다.</div>
	<?php } ?>
</div>

<!-- 데이터 테이블 -->
<div class="m_board_scroll">
	<div class="m_table_wrap" style="padding-bottom:115px;">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
				<tr>
					<th style="width:50px;">번호</th>
					<th>가맹점명</th>
					<th>FDS</th>
					<th>승인일시</th>
					<th>승인금액</th>
					<th>할부</th>
					<th>카드사</th>
					<th>카드번호</th>
					<th>영수증</th>
					<th>승인번호</th>
					<th>TID</th>
					<th>본TID</th>
					<th>구분</th>
					<th>결제종류</th>
					<th>PG</th>
				</tr>
			</thead>
			<tbody>
				<?php
					for ($i=0; $row=sql_fetch_array($result); $i++) {
					$bgcolor = '#fff';
					$num = number_format($total_count - ($page - 1) * $rows - $i);

					if($row['pay_type'] == "Y" && $row['pay_cdatetime'] > '0000-00-00 00:00:00') {
						$pay_type = "승인취소";
						$bgcolor = 'cancel1';
					} else if($row['pay_type'] == "Y") {
						$pay_type = "승인";
					} else if($row['pay_type'] == "N") {
						$pay_type = "취소";
						$bgcolor = 'cancel2';
					} else if($row['pay_type'] == "B") {
						$pay_type = "부분취소";
						$bgcolor = 'cancel2';
					} else if($row['pay_type'] == "M") {
						$pay_type = "망취소";
					} else if($row['pay_type'] == "X") {
						$pay_type = "수동취소";
					}

					if($row['pay_parti'] < 1) {
						$pay_parti = "일시불";
					} else {
						$pay_parti = $row['pay_parti']."개월";
					}
					if($row['dv_type'] == "paysharp-a") {
						$dv_type = "더8AL APP";
					} else if($row['dv_type'] == "paysharp-t") {
						$dv_type = "더8AL M100";
					} else if($row['dv_type'] == "pg-korea") {
						$dv_type = "한국결제대행1";
					} else if($row['dv_type'] == "pg-korea2") {
						$dv_type = "한국결제대행2";
					} else if($row['dv_type'] == "thepayone") {
						$dv_type = "더페이원";
					}
					$pay_card_name =  str_replace("카드", "", $row['pay_card_name']);
					if($row['pg_name'] == "k1") {
						$pg_name = "광원";
					} else if($row['pg_name'] == "welcom") {
						$pg_name = "웰컴";
					} else if($row['pg_name'] == "korpay") {
						$pg_name = "코페이";
					} else if($row['pg_name'] == "danal") {
						$pg_name = "다날";
					} else if($row['pg_name'] == "paysis" || $row['pg_name'] == "paysis_keyin") {
						$pg_name = "페이시스";
					} else if($row['pg_name'] == "stn" || $row['pg_name'] == "stn_k") {
						$pg_name = "섹타나인";
					} else if($row['pg_name'] == "daou") {
						$pg_name = "다우";
					} else if($row['pg_name'] == "routeup" || $row['pg_name'] == "routeup_k" || $row['pg_name'] == "routeup_ke") {
						$pg_name = "루트업";
					} else {
						$pg_name = "??";
					}
					if($row['dv_type'] == "1") {
						$dv_type = "오프라인";
					} else if($row['dv_type'] == "2") {
						$dv_type = "온라인";
					}
				?>
				<tr class='<?php echo $bgcolor; ?>'>
					<td class="center"><?php echo $num; ?></td>
					<td class="td_name">
						<span class="simptip-position-bottom simptip-movable half-arrow simptip-multiline simptip-black" data-tooltip="본　사 : <?php if($row['mb_1_name']) { echo $row['mb_1_name']. " / ".$row['mb_1_fee']; } ?>&#10;지　사 : <?php if($row['mb_2_name']) { echo $row['mb_2_name']. " / ".$row['mb_2_fee']; } ?>&#10;총　판 : <?php if($row['mb_3_name']) { echo $row['mb_3_name']. " / ".$row['mb_3_fee']; } ?>&#10;대리점 : <?php if($row['mb_4_name']) { echo $row['mb_4_name']. " / ".$row['mb_4_fee']; } ?>&#10;영업점 : <?php if($row['mb_5_name']) { echo $row['mb_5_name']. " / ".$row['mb_5_fee']; } ?>"><?php echo $row['mb_6_name']; ?></span>
						<?php if($fds_merchant_counts[$row['mb_6_name']] > 1) { ?>
						<span class="fds-merchant-badge"><?php echo $fds_merchant_counts[$row['mb_6_name']]; ?>건</span>
						<?php } ?>
					</td>
					<td style="text-align:center;">
						<?php if($row['pp_limit_3m']=='Y') { ?>
						<span class="fds-tag-3m">3M</span>
						<?php } ?>
						<?php if($row['pp_limit_5m']=='Y') { ?>
						<span class="fds-tag-5m">5M</span>
						<?php } ?>
					</td>
					<td class="center"><?php echo $row['pay_datetime']; ?></td>
					<td class="right"><?php echo number_format($row['pay']); ?></td>
					<td style="text-align:center;"><?php echo $pay_parti; ?></td>
					<td style="text-align:center;"><?php echo mb_substr($pay_card_name,0,2); ?></td>
					<td style="text-align:center;"><?php echo $row['pay_card_num']; ?></td>
					<td style="text-align:center; min-width:145px;">
						<div class="buttons">
							<button  class="btn_b btn_b03" onclick="payment_copy('<?php echo $row['pay_id'];?>')" type="button">복사</button>
							<button  class="btn_b btn_b02" onclick="receiptPopup('<?php echo $row['pay_id'];?>', '<?php echo $row['pay_num'];?>')" type="button">영수증</button>
							<?php
								if($row['updatetime'] > 0) {
									$updatetime = "<span style='font-size:11px;'>".substr($row['updatetime'],5,11)."</span>";
								} else {
									$updatetime = "재정산";
								}
								if($row['memo'] > 0) {
									$memo_class = "btn_b03";
									$memo_count = $row['memo'];
								} else {
									$memo_class = "btn_b01";
									$memo_count = "";
								}
							?>
							<button  class="btn_b <?php echo $memo_class; ?>" onclick="payment_memo('<?php echo $row['pay_id'];?>')" type="button">메모 <?php echo $memo_count; ?></button>
							<button  class="btn_b btn_b05" onclick="recalculation('<?php echo $row['pay_id'];?>')" type="button"><?php echo $updatetime; ?></button>
							<button  class="btn_b btn_b06" onclick="noti('<?php echo $row['pay_id'];?>')" type="button">NOTI</button>
						</div>
					</td>
					<td style="text-align:center;"><?php echo $row['pay_num']; ?></td>
					<td style="text-align:center;"><?php echo $row['dv_tid']; ?></td>
					<td style="text-align:center;"><?php echo $row['dv_tid_ori']; ?></td>
					<td style="text-align:center;"><?php echo $pay_type; ?></td>
					<td style="text-align:center;"><?php echo $dv_type; ?></td>
					<td><?php echo $pg_name; ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>
<?php
	$qstr = "p=".$p;
	$qstr .= "&fr_date=".$fr_date;
	$qstr .= "&to_date=".$to_date;
	$qstr .= "&fr_time=".$fr_time;
	$qstr .= "&to_time=".$to_time;
	$qstr .= "&fds_type=".$fds_type;
	$qstr .= "&sfl=".$sfl;
	$qstr .= "&stx=".$stx;
	if($mb_6_name) $qstr .= "&mb_6_name=".$mb_6_name;
	echo get_paging_news(G5_IS_MOBILE ? "5" : "5", $page, $total_page, '?' . $qstr . '&amp;page=');
?>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
(function(){
	var frHidden = document.getElementById('fr_time');
	var toHidden = document.getElementById('to_time');

	function syncHidden(sel, val) {
		var hhmm = val.replace(':', '');
		if (sel === 'fr') frHidden.value = hhmm;
		else toHidden.value = hhmm;
	}

	var fpFr = flatpickr('#fp_fr_time', {
		enableTime: true,
		noCalendar: true,
		dateFormat: 'H:i',
		time_24hr: true,
		minuteIncrement: 10,
		disableMobile: true,
		defaultDate: '<?php echo $fr_hour; ?>:<?php echo $fr_min; ?>',
		onChange: function(selDates, dateStr) {
			syncHidden('fr', dateStr);
		}
	});

	var fpTo = flatpickr('#fp_to_time', {
		enableTime: true,
		noCalendar: true,
		dateFormat: 'H:i',
		time_24hr: true,
		minuteIncrement: 10,
		disableMobile: true,
		defaultDate: '<?php echo $to_hour; ?>:<?php echo $to_min; ?>',
		onChange: function(selDates, dateStr) {
			syncHidden('to', dateStr);
		}
	});

	var origSetDate = window.set_date;
	window.set_date = function(v){
		fpFr.setDate('00:00', true);
		fpTo.setDate('23:50', true);
		frHidden.value = '0000';
		toHidden.value = '2350';
		if(typeof origSetDate === 'function') origSetDate(v);
	};
})();
</script>
