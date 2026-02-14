<?php

	if(!$is_admin) {
		alert("잘못된 접근입니다.");
	}

	$title1 = "결제관리";
	$title2 = "결제이상건(FDS)";

	if(!$fr_date) { $fr_date = date("Ymd"); }
	if(!$to_date) { $to_date = date("Ymd"); }

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
		$sql_search = " where ".$adm_sql." and (pay_datetime BETWEEN '{$fr_dates} {$fr_time}' and '{$to_dates} {$to_time}')  and mb_6_name != '' ";
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

<style>
.fds-dashboard {
	margin: 10px 0;
	border: 1px solid #2a2e3a;
	border-radius: 4px;
	background: #1e2230;
	overflow: hidden;
}
.fds-dashboard-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 10px 16px;
	background: #171b27;
	border-bottom: 1px solid #2a2e3a;
}
.fds-dashboard-header h3 {
	margin: 0;
	font-size: 13px;
	font-weight: 600;
	color: #e4e6eb;
	letter-spacing: -0.3px;
}
.fds-dashboard-header h3 i {
	margin-right: 6px;
	color: #ff6b6b;
}
.fds-dashboard-header .fds-merchant-count {
	font-size: 11px;
	color: #8b8fa3;
}
.fds-dashboard-header .fds-merchant-count em {
	color: #ff6b6b;
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
	background: #252a3a;
	border: 1px solid #333849;
	border-radius: 4px;
	padding: 10px 12px;
	transition: border-color 0.15s, background 0.15s;
	cursor: pointer;
	text-decoration: none;
	color: inherit;
	min-height: 60px;
}
.fds-card:hover {
	border-color: #5a6080;
	background: #2c3148;
}
.fds-card.active {
	border-color: #694ecc;
	background: #2c2848;
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
.fds-rank-critical { color: #ff4444; }
.fds-rank-high { color: #ff8c00; }
.fds-rank-medium { color: #ffc107; }
.fds-rank-low { color: #8b8fa3; }
.fds-card-body {
	flex: 1;
	min-width: 0;
}
.fds-card-name {
	font-size: 13px;
	font-weight: 600;
	color: #e4e6eb;
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
	color: #8b8fa3;
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
.fds-card-cnt-critical { color: #ff4444; }
.fds-card-cnt-high { color: #ff8c00; }
.fds-card-cnt-medium { color: #ffc107; }
.fds-card-cnt-low { color: #a0a4b8; }
.fds-card-cnt-unit {
	font-size: 10px;
	color: #8b8fa3;
	margin-top: 1px;
}
.fds-badge-3m {
	display: inline-block;
	background: gold;
	color: #333;
	padding: 0px 5px;
	border-radius: 2px;
	font-size: 10px;
	font-weight: 700;
	line-height: 16px;
}
.fds-badge-5m {
	display: inline-block;
	background: orange;
	color: #fff;
	padding: 0px 5px;
	border-radius: 2px;
	font-size: 10px;
	font-weight: 700;
	line-height: 16px;
}
.fds-badge-both {
	display: inline-block;
	background: #ff4444;
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
	color: #ffdf2c;
	text-align: right;
	margin-top: 2px;
}
.fds-empty {
	padding: 30px;
	text-align: center;
	color: #8b8fa3;
	font-size: 13px;
}
@media (max-width: 768px) {
	.fds-card-grid {
		grid-template-columns: 1fr;
	}
}
</style>

<div class="index_menu">
	<ul class="shortcut">
		<li class="sc_current"><a>결제이상건(FDS)</a></li>
		<li class="sc_visit">
			<aside id="visit">
				<ul>
					<li>전체<span style="color:#fff;"><?php echo number_format($fds_row['fds_total']); ?>건</span></li>
					<li style="color:gold;">1회 300만↑<span><?php echo number_format($fds_row['fds_3m_only']); ?>건</span></li>
					<li style="color:orange;">동일카드 500만↑<span><?php echo number_format($fds_row['fds_5m_only']); ?>건</span></li>
					<li style="color:#ff6b6b;">중복감지<span><?php echo number_format($fds_row['fds_both']); ?>건</span></li>
					<li>이상거래 합계<span style="color:#ffff00;"><?php echo number_format($fds_row['fds_total_pay']); ?></span></li>
				</ul>
			</aside>
		</li>
	</ul>
</div>

<!-- 가맹점별 FDS 요약 대시보드 -->
<div class="fds-dashboard">
	<div class="fds-dashboard-header">
		<h3><i class="fa fa-exclamation-triangle"></i>가맹점별 이상건 현황</h3>
		<span class="fds-merchant-count">이상 가맹점 <em><?php echo $fds_merchant_total; ?></em>개<?php if($mb_6_name) { ?> &middot; <a href="./?<?php echo $fds_base_qstr; ?>" style="color:#694ecc; font-size:11px;">필터 해제</a><?php } ?></span>
	</div>
	<?php if($fds_merchant_total > 0) { ?>
	<div class="fds-card-grid">
		<?php
		$rank = 0;
		foreach($fds_merchants as $fm) {
			$rank++;
			$cnt = intval($fm['cnt']);
			// 위험도 등급 판정
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
					<span style="color:#6b7080;"><?php echo $last_time_short; ?></span>
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

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<input type="hidden" name="p" value="<?php echo $p; ?>">
	<div class="searchbox">
		<div class="midd">
			<ul>
				<li>
					<strong>일자</strong>
					<div>
						<div>
							<input type="text" name="fr_date" value="<?php echo $fr_date ?>" id="fr_date" class="frm_input" size="6" maxlength="10">
							<input type="text" id="fr_time" value="<?php echo $fr_time ?>" name="fr_time" class="frm_input timepicker" readonly placeholder="시간 선택" size="3" maxlength="8">
						</div>
						<span>~</span>
						<div>
							<input type="text" name="to_date" value="<?php echo $to_date ?>" id="to_date" class="frm_input" size="6" maxlength="10">
							<input type="text" id="to_time" value="<?php echo $to_time ?>" name="to_time" class="frm_input timepicker" readonly placeholder="시간 선택" size="3" maxlength="8">
						</div>
						<button type="submit" onclick="javascript:set_date('전체');" class="btn_b btn_b09"><span>전체</span></button>
					</div>
				</li>
				<li>
					<strong>단축</strong>
					<div>
						<button type="submit" onclick="javascript:set_date('오늘');" class="btn_b btn_b09"><span>오늘</span></button>
						<button type="submit" onclick="javascript:set_date('어제');" class="btn_b btn_b09"><span>어제</span></button>
						<button type="submit" onclick="javascript:set_date('이번달');" class="btn_b btn_b09"><span>이번달</span></button>
						<button type="submit" onclick="javascript:set_date('지난주');" class="btn_b btn_b09"><span>지난주</span></button>
						<button type="submit" onclick="javascript:set_date('지난달');" class="btn_b btn_b09"><span>지난달</span></button>
					</div>
				</li>
				<li>
					<strong>FDS유형</strong>
					<div>
						<div data-skin="radio">
							<label><input type="radio" name="fds_type" value="" <?php echo get_checked($fds_type, ""); ?> checked> 전체</label>
							<label><input type="radio" name="fds_type" value="3m" <?php echo get_checked($fds_type, "3m"); ?>> 1회 300만↑</label>
							<label><input type="radio" name="fds_type" value="5m" <?php echo get_checked($fds_type, "5m"); ?>> 동일카드 500만↑</label>
						</div>
					</div>
				</li>
				<li>
					<strong>검색</strong>
					<div>
						<div data-skin="radio">
							<label><input type="radio" name="sfl" value="pay_num" <?php echo get_checked($sfl, "pay_num"); ?> checked> 승번</label>
							<label><input type="radio" name="sfl" value="mb_6_name" <?php echo get_checked($sfl, "mb_6_name"); ?>> 가맹</label>
							<label><input type="radio" name="sfl" value="dv_tid" <?php echo get_checked($sfl, "dv_tid"); ?>> TID</label>
							<label><input type="radio" name="sfl" value="dv_tid_ori" <?php echo get_checked($sfl, "dv_tid_ori"); ?>> 본TID</label>
							<label><input type="radio" name="sfl" value="pay" <?php echo get_checked($sfl, "pay"); ?>> 금액</label>
							<label><input type="radio" name="sfl" value="pay_card_name" <?php echo get_checked($sfl, "pay_card_name"); ?>> 카드</label>
							<label><input type="radio" name="sfl" value="pay_card_num" <?php echo get_checked($sfl, "pay_card_num"); ?>> 카번</label>
						</div>
					</div>
				</li>
				<li>
					<strong>검색</strong>
					<div>
						<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input" size="7" placeholder="검색어입력" style="width:150px;">
						<button type="submit" class="btn_b btn_b02"><span>검색</span></button>
						<button type="button" class="btn_b btn_b06" id="xlsx"><span><i class="fa fa-file-excel-o" aria-hidden="true"></i> 엑셀출력</span></button>
					</div>
				</li>
			</ul>
		</div>
	</div>
</form>

<form action="./xlsx/payment.php" id="frm_xlsx" method="post">
<input type="hidden" name="xlsx_sql" value="<?php echo $xlsx_sql; ?>">
<input type="hidden" name="p" value="<?php echo $p; ?>">
</form>

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
					} else if($row['pg_name'] == "paysis") {
						$pg_name = "페이시스";
					} else if($row['pg_name'] == "stn") {
						$pg_name = "섹타나인";
					} else if($row['pg_name'] == "daou") {
						$pg_name = "다우";
					} else {
						$pg_name = "??";
					}
					if($row['dv_type'] == "1") {
						$dv_type = "단말기";
					} else if($row['dv_type'] == "2") {
						$dv_type = "수기";
					}
				?>
				<tr class='<?php echo $bgcolor; ?>'>
					<td class="center"><?php echo $num; ?></td>
					<td class="td_name">
						<span class="simptip-position-bottom simptip-movable half-arrow simptip-multiline simptip-black" data-tooltip="본　사 : <?php if($row['mb_1_name']) { echo $row['mb_1_name']. " / ".$row['mb_1_fee']; } ?>&#10;지　사 : <?php if($row['mb_2_name']) { echo $row['mb_2_name']. " / ".$row['mb_2_fee']; } ?>&#10;총　판 : <?php if($row['mb_3_name']) { echo $row['mb_3_name']. " / ".$row['mb_3_fee']; } ?>&#10;대리점 : <?php if($row['mb_4_name']) { echo $row['mb_4_name']. " / ".$row['mb_4_fee']; } ?>&#10;영업점 : <?php if($row['mb_5_name']) { echo $row['mb_5_name']. " / ".$row['mb_5_fee']; } ?>"><?php echo $row['mb_6_name']; ?></span>
						<?php if($fds_merchant_counts[$row['mb_6_name']] > 1) { ?>
						<span style="background:#ff4444; color:#fff; padding:0 5px; border-radius:10px; font-size:10px; margin-left:3px;"><?php echo $fds_merchant_counts[$row['mb_6_name']]; ?>건</span>
						<?php } ?>
					</td>
					<td style="text-align:center;">
						<?php if($row['pp_limit_3m']=='Y') { ?>
						<span style="background:gold; color:#333; padding:1px 6px; border-radius:3px; font-size:11px; font-weight:bold;">3M</span>
						<?php } ?>
						<?php if($row['pp_limit_5m']=='Y') { ?>
						<span style="background:orange; color:#fff; padding:1px 6px; border-radius:3px; font-size:11px; font-weight:bold;">5M</span>
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
	$qstr .= "&fds_type=".$fds_type;
	$qstr .= "&sfl=".$sfl;
	$qstr .= "&stx=".$stx;
	echo get_paging_news(G5_IS_MOBILE ? "5" : "5", $page, $total_page, '?' . $qstr . '&amp;page=');
?>
