<?php

	$title1 = "결제관리";
	$title2 = "실시간 결제내역";

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

	$sql_common = " from g5_payment LEFT JOIN g5_member ON g5_payment.mb_6 = g5_member.mb_id ";

	if($is_admin) {

		if(adm_sql_common) {
			$adm_sql = " mb_1 IN (".adm_sql_common.")";
		} else {
			$adm_sql = " (1)";
		}

	} else if($member['mb_level'] == 8) {
		$adm_sql = " g5_payment.mb_1 = '{$member['mb_id']}'";
	} else if($member['mb_level'] == 7) {
		$adm_sql = " g5_payment.mb_2 = '{$member['mb_id']}'";
	} else if($member['mb_level'] == 6) {
		$adm_sql = " g5_payment.mb_3 = '{$member['mb_id']}'";
	} else if($member['mb_level'] == 5) {
		$adm_sql = " g5_payment.mb_4 = '{$member['mb_id']}'";
	} else if($member['mb_level'] == 4) {
		$adm_sql = " g5_payment.mb_5 = '{$member['mb_id']}'";
	} else if($member['mb_level'] == 3) {
		$adm_sql = " g5_payment.mb_6 = '{$member['mb_id']}'";
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
	/*
	if ($is_admin != 'super')
		$sql_search .= " and (gr_admin = '{$member['mb_id']}') ";
	*/


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
	
	if ($sst)
		$sql_order = " order by {$sst} {$sod} ";
	else
		$sql_order = " order by pay_datetime desc ";

	$sql = " select count(*) as cnt, sum(pay) as total_pay";

	$sql .= ", sum(if(dv_type = '2' and pay_type = 'Y', pay, null)) as sugis";
	$sql .= ", sum(if(dv_type = '2' and pay_type != 'Y', pay, null)) as sugic";
	$sql .= ", sum(if(dv_type = '1' and pay_type = 'Y', pay, null)) as dans";
	$sql .= ", sum(if(dv_type = '1' and pay_type != 'Y', pay, null)) as danc";
	$sql .= ", sum(mb_6_pay) as total_6_pay";
	$sql .= ", sum(if(pay_type = 'Y', pay, 0)) as total_Y_pay, sum(if(pay_type != 'Y', pay, 0)) as total_M_pay, count(if(pay_type = 'Y', 1, null)) as count_Y_pay, count(if(pay_type != 'Y', 1, null)) as count_M_pay {$sql_common} {$sql_search} {$sql_order} ";
//	echo $sql;
//	$sql = " select count(*) as cnt, sum(pay) as total_pay {$sql_common} {$sql_search} {$sql_order} ";
	$row = sql_fetch($sql);



	$sums = $row['sugis'] + $row['dans'] + $row['sugic'] + $row['danc'];


	$total_count = $row['cnt']; // 전체개수
	$total_Y_pay  = $row['total_Y_pay']; // 승인합산
	$total_M_pay  = $row['total_M_pay']; // 취소합산
	$count_Y_pay  = $row['count_Y_pay']; // 승인건수
	$count_M_pay  = $row['count_M_pay']; // 취소건수
	$total_pay = $total_Y_pay + $total_M_pay; // 전체매출합산
	$page_count = "30";
	if($page_count) {
		$rows = $page_count;
	} else {
		$rows = $config['cf_page_rows'];
	}
//if($_SERVER['REMOTE_ADDR']=='59.18.140.225') echo $sql;

	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함

	$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
	$xlsx_sql = "select * {$sql_common} {$sql_search} {$sql_order} ";
	$result = sql_query($sql);

//	echo $sql;
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
/* --- 헤더 --- */
.payment-header {
	background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
	border-radius: 8px;
	padding: 12px 16px;
	margin-bottom: 10px;
	box-shadow: 0 2px 8px rgba(26, 35, 126, 0.2);
}
.payment-header-top {
	display: flex;
	align-items: center;
	justify-content: space-between;
	flex-wrap: wrap;
	gap: 10px;
}
.payment-title {
	color: #fff;
	font-size: 16px;
	font-weight: 600;
	display: flex;
	align-items: center;
	gap: 8px;
}
.payment-title i { font-size: 14px; opacity: 0.8; }
.payment-stats {
	display: flex;
	flex-wrap: wrap;
	gap: 6px;
}
.payment-stat {
	display: inline-flex;
	align-items: center;
	background: rgba(255,255,255,0.12);
	border-radius: 4px;
	padding: 4px 10px;
	font-size: 12px;
	color: rgba(255,255,255,0.85);
	gap: 6px;
}
.payment-stat.cancel { background: rgba(239,83,80,0.25); }
.payment-stat.total { background: rgba(255,193,7,0.3); color: #fff; font-weight: 600; }
.payment-stat span { color: #fff; font-weight: 600; }

/* --- 검색 테이블 레이아웃 --- */
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
	color: #1a237e;
	font-weight: 700;
	font-size: 12px;
	white-space: nowrap;
	width: 52px;
	min-width: 52px;
	text-align: center;
	border-right: 2px solid #1a237e;
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
	border-color: #1a237e;
	background: #fff;
}
.ps-date-group .sep { color: #bbb; font-size: 12px; }
/* 날짜+시간 래퍼 (데스크탑: inline flex) */
.ps-datetime-row {
	display: flex;
	align-items: center;
	gap: 8px;
}
/* 시간 입력 */
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
	border-color: #1a237e;
	background: #fff;
}
.ps-time-group .sep { color: #bbb; font-size: 12px; }
.flatpickr-time input:hover,
.flatpickr-time .flatpickr-am-pm:hover,
.flatpickr-time input:focus,
.flatpickr-time .flatpickr-am-pm:focus { background: #e8eaf6; }
.flatpickr-time .numInputWrapper span.arrowUp:after { border-bottom-color: #1a237e; }
.flatpickr-time .numInputWrapper span.arrowDown:after { border-top-color: #1a237e; }
/* 세로 구분선 */
.ps-vdiv {
	width: 1px;
	height: 22px;
	background: #e0e0e0;
	margin: 0 2px;
	flex-shrink: 0;
}
/* 날짜 빠른버튼 */
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
	background: #1a237e;
	border-color: #1a237e;
	color: #fff;
}
/* 라디오 */
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
.ps-radio-group input[type="radio"] { margin: 0; accent-color: #1a237e; }
/* 검색 입력 + 버튼 */
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
	border-color: #1a237e;
}
.btn-search {
	padding: 5px 14px;
	background: #1a237e;
	color: #fff;
	border: none;
	border-radius: 4px;
	font-size: 12px;
	cursor: pointer;
	transition: background 0.15s;
}
.btn-search:hover { background: #283593; }
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
.ps-checkbox {
	display: flex;
	align-items: center;
	gap: 4px;
	font-size: 12px;
	color: #555;
	white-space: nowrap;
}
.ps-checkbox input { accent-color: #1a237e; }
/* 반응형 */
@media (max-width: 768px) {
	/* --- 헤더 모바일 --- */
	.payment-header { padding: 10px 12px; border-radius: 6px; margin-bottom: 8px; }
	.payment-header-top { flex-direction: column; align-items: flex-start; gap: 8px; }
	.payment-title { font-size: 15px; }
	.payment-stats { width: 100%; gap: 4px; }
	.payment-stat { padding: 3px 7px; font-size: 11px; }

	/* --- 검색박스 모바일 --- */
	.ps-box { border-radius: 6px; margin-bottom: 8px; }
	.ps-table, .ps-table thead, .ps-table tbody, .ps-table tr, .ps-table th, .ps-table td {
		display: block;
		width: 100%;
	}
	.ps-table th {
		border-right: none;
		border-bottom: none;
		background: #1a237e;
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

	/* ps-cell: 모바일에서 세로 배치 + 각 그룹 간격 */
	.ps-cell {
		flex-direction: column;
		align-items: stretch;
		gap: 8px;
	}
	.ps-vdiv { display: none; }

	/* 날짜 + 시간: 한 줄에 나란히 */
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

	/* 빠른 날짜버튼: 가로 스크롤 */
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
		border: 1px solid #c5cae9;
		background: #e8eaf6;
		color: #1a237e;
		font-weight: 500;
	}
	.ps-date-btns button:hover,
	.ps-date-btns button:active {
		background: #1a237e;
		border-color: #1a237e;
		color: #fff;
	}

	/* 라디오 그룹: 가로 스크롤 칩 */
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
		background: #1a237e;
		border-color: #1a237e;
		color: #fff;
	}
	.ps-radio-group input[type="radio"] {
		width: 0;
		height: 0;
		margin: 0;
		opacity: 0;
		position: absolute;
	}

	/* 검색 입력: 전체 너비 */
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
	.btn-search {
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

	/* 체크박스 */
	.ps-checkbox {
		font-size: 12px;
		padding: 4px 0;
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

<div class="payment-header">
	<div class="payment-header-top">
		<div class="payment-title">
			<i class="fa fa-credit-card"></i>
			실시간 결제내역
		</div>
		<div class="payment-stats">
			<div class="payment-stat">온라인 <span><?php echo number_format($row['sugis']); ?></span></div>
			<div class="payment-stat cancel">온라인취소 <span><?php echo number_format($row['sugic']); ?></span></div>
			<div class="payment-stat">오프라인 <span><?php echo number_format($row['dans']); ?></span></div>
			<div class="payment-stat cancel">오프라인취소 <span><?php echo number_format($row['danc']); ?></span></div>
			<div class="payment-stat total">합계 <span><?php echo number_format($sums); ?></span></div>
			<?php if($expansion=="y") { ?>
			<div class="payment-stat total">가맹점 <span><?php echo number_format($row['total_6_pay']); ?></span></div>
			<?php } ?>
		</div>
	</div>
</div>

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
			<th><i class="fa fa-search"></i> 검색</th>
			<td>
				<div class="ps-cell">
					<div class="ps-radio-group">
						<label><input type="radio" name="sfl" value="pay_num" <?php echo get_checked($sfl, "pay_num"); ?> checked>승번</label>
						<label><input type="radio" name="sfl" value="mb_6_name" <?php echo get_checked($sfl, "mb_6_name"); ?>>가맹</label>
						<label><input type="radio" name="sfl" value="dv_tid" <?php echo get_checked($sfl, "dv_tid"); ?>>TID</label>
						<label><input type="radio" name="sfl" value="dv_tid_ori" <?php echo get_checked($sfl, "dv_tid_ori"); ?>>MID</label>
						<label><input type="radio" name="sfl" value="pay" <?php echo get_checked($sfl, "pay"); ?>>금액</label>
						<label><input type="radio" name="sfl" value="pay_card_name" <?php echo get_checked($sfl, "pay_card_name"); ?>>카드</label>
						<label><input type="radio" name="sfl" value="pay_card_num" <?php echo get_checked($sfl, "pay_card_num"); ?>>카번</label>
					</div>
					<div class="ps-vdiv"></div>
					<div class="ps-search-input">
						<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" placeholder="검색어">
						<button type="submit" class="btn-search">검색</button>
						<button type="button" class="btn-excel" id="xlsx"><i class="fa fa-file-excel-o"></i> 엑셀</button>
					</div>
					<div class="ps-vdiv"></div>
					<label class="ps-checkbox">
						<input type="checkbox" name="expansion" id="expansion" value="y" <?php echo get_checked($expansion, "y"); ?>>
						정산금 확장
					</label>
				</div>
			</td>
		</tr>
	</table>
</div>
</form>

<form action="./xlsx/payment.php" id="frm_xlsx" method="post">
<input type="hidden" name="fr_date" value="<?php echo $fr_date; ?>">
<input type="hidden" name="to_date" value="<?php echo $to_date; ?>">
<input type="hidden" name="fr_time" value="<?php echo $fr_time; ?>">
<input type="hidden" name="to_time" value="<?php echo $to_time; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="pay_num" value="<?php echo $pay_num; ?>">
<input type="hidden" name="dv_tid" value="<?php echo $dv_tid; ?>">
<input type="hidden" name="mb_6_name" value="<?php echo $mb_6_name; ?>">
<input type="hidden" name="gname" value="<?php echo $gname; ?>">
<input type="hidden" name="l2" value="<?php echo $l2; ?>">
<input type="hidden" name="l3" value="<?php echo $l3; ?>">
<input type="hidden" name="l4" value="<?php echo $l4; ?>">
<input type="hidden" name="l5" value="<?php echo $l5; ?>">
<input type="hidden" name="l6" value="<?php echo $l6; ?>">
<input type="hidden" name="l7" value="<?php echo $l7; ?>">
</form>

<div class="m_board_scroll">
	<div class="m_table_wrap" style="padding-bottom:115px;">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
				<tr>
					<th style="width:50px;">번호</th>
					<?php if($member['mb_level'] > 3) { ?><th>가맹점명</th><?php } ?>
					<th>승인일시</th>
					<th>승인금액</th>
					<?php if($expansion == "y") { ?>
					<?php if($member['mb_level'] >= 8) { ?>
					<th>본사</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 7) { ?>
					<th>지사</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 6) { ?>
					<th>총판</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 5) { ?>
					<th>대리점</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 4) { ?>
					<th>영업점</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 3) { ?>
					<th>가맹점</th>
					<?php } ?>
					<?php } ?>

					<th>할부</th>
					<th>카드사</th>
					<th>카드번호</th>
					<th>영수증</th>
					<th>승인번호</th>
					<th>TID</th>
					<?php if($is_admin) { ?>
					<th>MID</th>
					<?php } ?>
					<th>구분</th>
					<th>결제종류</th>
					<?php /*
					<th>거래번호</th>
					<th>주문번호</th>
					*/ ?>
					<th>PG</th>
				</tr>
			</thead>
			<tbody>
				<?php
					for ($i=0; $row=sql_fetch_array($result); $i++) {
					$bgcolor = '#fff';
					$num = number_format($total_count - ($page - 1) * $rows - $i);

					if($row['pay_type'] == "Y" && $row['pay_cdatetime'] > '0000-00-00 00:00:00') {
						$pay_type = "승인";
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
					<?php if($member['mb_level'] > 3) { ?><td class="td_name"><?php if($is_admin) { ?><span class="simptip-position-bottom simptip-movable half-arrow simptip-multiline simptip-black" data-tooltip="본　사 : <?php if($row['mb_1_name']) { echo $row['mb_1_name']. " / ".$row['mb_1_fee']; } ?>&#10;지　사 : <?php if($row['mb_2_name']) { echo $row['mb_2_name']. " / ".$row['mb_2_fee']; } ?>&#10;총　판 : <?php if($row['mb_3_name']) { echo $row['mb_3_name']. " / ".$row['mb_3_fee']; } ?>&#10;대리점 : <?php if($row['mb_4_name']) { echo $row['mb_4_name']. " / ".$row['mb_4_fee']; } ?>&#10;영업점 : <?php if($row['mb_5_name']) { echo $row['mb_5_name']. " / ".$row['mb_5_fee']; } ?>"><?php } ?><?php echo $row['mb_6_name']; ?><?php if($is_admin) { ?></span><?php } ?></td><?php } ?>
					<td class="center"><?php echo $row['pay_datetime']; ?></td>
					<td class="right"><?php echo number_format($row['pay']); ?><?php /* if($row['pay_cdatetime'] > 0) { echo "<del>"; }  echo number_format($row['pay']); if($row['pay_cdatetime'] > 0) { echo "</del>"; } */?></td>
					<?php if($expansion == "y") { ?>
					<?php if($member['mb_level'] >= 8) { ?>
					<td class="left"><?php if($row['mb_1_fee'] > 0) { echo "<div style='float:left;background:#333; color:#fff; font-size:11px; font-weight:100; padding:0 3px;' title='".$row['mb_1_name']."'>".$row['mb_1_fee']."%</div><div style='float:right'>".number_format($row['mb_1_pay'])."</div>"; } ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 7) { ?>
					<td class="left"><?php if($row['mb_2_fee'] > 0) { echo "<div style='float:left;background:#333; color:#fff; font-size:11px; font-weight:100; padding:0 3px;' title='".$row['mb_2_name']."'>".$row['mb_2_fee']."%</div><div style='float:right'>".number_format($row['mb_2_pay'])."</div>"; } ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 6) { ?>
					<td class="left"><?php if($row['mb_3_fee'] > 0) { echo "<div style='float:left;background:#333; color:#fff; font-size:11px; font-weight:100; padding:0 3px;' title='".$row['mb_3_name']."'>".$row['mb_3_fee']."%</div><div style='float:right'>".number_format($row['mb_3_pay'])."</div>"; } ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 5) { ?>
					<td class="left"><?php if($row['mb_4_fee'] > 0) { echo "<div style='float:left;background:#333; color:#fff; font-size:11px; font-weight:100; padding:0 3px;' title='".$row['mb_4_name']."'>".$row['mb_4_fee']."%</div><div style='float:right'>".number_format($row['mb_4_pay'])."</div>"; } ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 4) { ?>
					<td class="left"><?php if($row['mb_5_fee'] > 0) { echo "<div style='float:left;background:#333; color:#fff; font-size:11px; font-weight:100; padding:0 3px;' title='".$row['mb_5_name']."'>".$row['mb_5_fee']."%</div><div style='float:right'>".number_format($row['mb_5_pay'])."</div>"; } ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 3) { ?>
					<td class="left"><?php if($row['mb_6_fee'] > 0) { echo "<div style='float:left;background:#333; color:#fff; font-size:11px; font-weight:100; padding:0 3px;' title='".$row['mb_6_name']."'>".$row['mb_6_fee']."%</div><div style='float:right'>".number_format($row['mb_6_pay'])."</div>"; } ?></td>
					<?php } ?>
					<?php } ?>

					<td style="text-align:center;"><?php echo $pay_parti; ?></td>
					<td style="text-align:center;"><?php echo mb_substr($pay_card_name,0,2); ?></td>
					<td style="text-align:center;"><?php echo $row['pay_card_num']; ?></td>
					<td style="text-align:center; min-width:145px;">
						<div class="buttons">
							<button  class="btn_b btn_b03" onclick="payment_copy('<?php echo $row['pay_id'];?>')" type="button">복사</button>
							<?php
								/*
								if($row['pg_name'] == "stn") {
									$tran_date = preg_replace("/[^0-9]/","",$row['pay_datetime']);
									$tran_date = substr($tran_date,2,6);
							?>
							<button  class="btn_b btn_b02" onclick="receiptPopup2('<?php echo $row['trxid'];?>', '<?php echo $tran_date;?>')" type="button">섹타나인영수증</button>
							<?php
								}
								*/
							?>
							<button  class="btn_b btn_b02" onclick="receiptPopup('<?php echo $row['pay_id'];?>', '<?php echo $row['pay_num'];?>')" type="button">영수증</button>
							<?php
								if($is_admin) {
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
							<button  class="btn_b btn_b01" onclick="recalculation('<?php echo $row['pay_id'];?>')" type="button"><?php echo $updatetime; ?></button>
							<?php } ?>
						</div>
					</td>
					<td style="text-align:center;"><?php echo $row['pay_num']; ?></td>
					<td style="text-align:center;"><?php echo $row['dv_tid']; ?></td>
					<?php if($is_admin) { ?>
					<td style="text-align:center;"><?php echo $row['dv_tid_ori']; ?></td>
					<?php } ?>
					<td style="text-align:center;"><?php echo $pay_type; ?></td>
					<td style="text-align:center;"><?php echo $dv_type; ?></td>
					
					<?php /*
					<td><?php echo $row['trxid']; ?></td>
					<td><?php echo $row['trackId']; ?></td>
					*/ ?>
					<td><?php echo $pg_name; ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>
<?php
	//http://cajung.com/new4/?p=payment&fr_date=20220906&to_date=20220906&sfl=mb_id&stx=
	$qstr = "p=".$p;
	$qstr .= "&fr_date=".$fr_date;
	$qstr .= "&to_date=".$to_date;
	$qstr .= "&fr_time=".$fr_time;
	$qstr .= "&to_time=".$to_time;
	$qstr .= "&expansion=".$expansion;
	$qstr .= "&sfl=".$sfl;
	$qstr .= "&stx=".$stx;
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

	// 날짜 버튼 클릭 시 시간 초기화
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

