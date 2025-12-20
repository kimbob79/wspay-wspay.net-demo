<?php
// 밴피 설정 여부 확인
$is_van_fee_member = ($member['mb_van_fee'] > 0) ? true : false;

if($is_admin) {
	if(adm_sql_common) {
		$adm_sql = " mb_1 IN (".adm_sql_common.")";
	} else {
		$adm_sql = " (1)";
	}
} else {
	if($member['mb_level'] == 7) {
		$groups = " mb_2 = '{$member['mb_id']}' ";
	} else if($member['mb_level'] == 6) {
		$groups = " mb_3 = '{$member['mb_id']}' ";
	} else if($member['mb_level'] == 5) {
		$groups = " mb_4 = '{$member['mb_id']}' ";
	} else if($member['mb_level'] == 4) {
		$groups = " mb_5 = '{$member['mb_id']}' ";
	} else if($member['mb_level'] == 3) {
		$groups = " mb_6 = '{$member['mb_id']}' ";
	}
}
$yoil = array("일","월","화","수","목","금","토");

// 이번달/지난달 계산
$d = mktime(0,0,0, date("m"), 1, date("Y"));
$prev_month = strtotime("-1 month", $d);
$pn = date("n", $prev_month);

// 오늘 결제현황 조회
$today = date("Y-m-d");
$sql_today = "SELECT
	count(if(pay_type = 'Y', 1, null)) as approve_cnt,
	count(if(pay_type = 'N', 1, null)) as cancel_cnt,
	sum(if(pay_type = 'Y', pay, 0)) as approve_pay,
	sum(if(pay_type = 'N', pay, 0)) as cancel_pay
	FROM g5_payment
	WHERE ".$adm_sql.$groups."
	AND pay_datetime BETWEEN '{$today} 00:00:00' AND '{$today} 23:59:59'";
$today_row = sql_fetch($sql_today);
$today_total = ($today_row['approve_pay'] ?: 0) + ($today_row['cancel_pay'] ?: 0);

// 밴피 회원용: 오늘 밴피 계산
$today_van_fee = 0;
$month_van_fee = 0;
if($is_van_fee_member) {
	$today_van_fee = (($today_row['approve_cnt'] ?: 0) - ($today_row['cancel_cnt'] ?: 0)) * $member['mb_van_fee'];
}

// 이번달 총액
$this_month = date("Y-m");
$sql_month = "SELECT
	count(if(pay_type = 'Y', 1, null)) as approve_cnt,
	count(if(pay_type = 'N', 1, null)) as cancel_cnt,
	sum(if(pay_type = 'Y', pay, 0)) as approve_pay,
	sum(if(pay_type = 'N', pay, 0)) as cancel_pay
	FROM g5_payment
	WHERE ".$adm_sql.$groups."
	AND SUBSTRING(pay_datetime,1,7) = '{$this_month}'";
$month_row = sql_fetch($sql_month);
$month_total = ($month_row['approve_pay'] ?: 0) + ($month_row['cancel_pay'] ?: 0);

// 밴피 회원용: 이번달 밴피 계산
if($is_van_fee_member) {
	$month_van_fee = (($month_row['approve_cnt'] ?: 0) - ($month_row['cancel_cnt'] ?: 0)) * $member['mb_van_fee'];
}
?>

<style>
/* 대시보드 스타일 */
.dashboard-container {
	display: flex;
	flex-direction: column;
	gap: 12px;
}
.dashboard-summary {
	display: grid;
	grid-template-columns: repeat(4, 1fr);
	gap: 10px;
}
.summary-card {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 16px;
	text-align: center;
}
.summary-card.primary {
	background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
	border: none;
	color: #fff;
}
.summary-card.success {
	background: linear-gradient(135deg, #2e7d32 0%, #388e3c 100%);
	border: none;
	color: #fff;
}
.summary-card .card-label {
	font-size: 11px;
	color: #888;
	margin-bottom: 6px;
}
.summary-card.primary .card-label,
.summary-card.success .card-label {
	color: rgba(255,255,255,0.8);
}
.summary-card .card-value {
	font-size: 20px;
	font-weight: 700;
	color: #333;
}
.summary-card.primary .card-value,
.summary-card.success .card-value {
	color: #fff;
}
.summary-card .card-sub {
	font-size: 10px;
	color: #999;
	margin-top: 4px;
}
.summary-card.primary .card-sub,
.summary-card.success .card-sub {
	color: rgba(255,255,255,0.7);
}

.dashboard-grid {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: 12px;
}
.dashboard-panel {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	overflow: hidden;
}
.panel-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 12px 14px;
	background: #f8f9fa;
	border-bottom: 1px solid #eee;
}
.panel-title {
	font-size: 13px;
	font-weight: 600;
	color: #333;
	display: flex;
	align-items: center;
	gap: 6px;
}
.panel-title i {
	color: #1a237e;
	font-size: 12px;
}
.panel-body {
	max-height: 400px;
	overflow-y: auto;
}
.panel-body::-webkit-scrollbar {
	width: 4px;
}
.panel-body::-webkit-scrollbar-thumb {
	background: #ccc;
	border-radius: 2px;
}

/* 테이블 오버라이드 */
.dashboard-panel .table_list {
	margin-top: 0;
	border: none;
	border-radius: 0;
	box-shadow: none;
}
.dashboard-panel .table_list th {
	background: #fafafa;
	font-size: 11px;
	padding: 8px 6px;
}
.dashboard-panel .table_list td {
	font-size: 11px;
	padding: 6px;
}
.dashboard-panel .table_list tr.today-row td {
	background: #1a237e !important;
	color: #fff !important;
	font-weight: 600;
}

@media (max-width: 991px) {
	.dashboard-summary {
		grid-template-columns: repeat(2, 1fr);
	}
	.dashboard-grid {
		grid-template-columns: 1fr;
	}
	.summary-card .card-value {
		font-size: 16px;
	}
}
</style>

<div class="dashboard-container">
	<!-- 요약 카드 -->
	<div class="dashboard-summary">
		<?php if($is_van_fee_member) { ?>
		<!-- 밴피 회원용 요약 카드 -->
		<div class="summary-card primary">
			<div class="card-label">오늘 승인</div>
			<div class="card-value"><?php echo number_format($today_row['approve_cnt'] ?: 0); ?>건</div>
			<div class="card-sub">&nbsp;</div>
		</div>
		<div class="summary-card" style="background:linear-gradient(135deg,#e53935 0%,#ef5350 100%);border:none;color:#fff;">
			<div class="card-label" style="color:rgba(255,255,255,0.8);">오늘 취소</div>
			<div class="card-value" style="color:#fff;"><?php echo number_format($today_row['cancel_cnt'] ?: 0); ?>건</div>
			<div class="card-sub" style="color:rgba(255,255,255,0.7);">&nbsp;</div>
		</div>
		<div class="summary-card success">
			<div class="card-label">오늘 밴피</div>
			<div class="card-value"><?php echo number_format($today_van_fee); ?>원</div>
			<div class="card-sub"><?php echo number_format(($today_row['approve_cnt'] ?: 0) - ($today_row['cancel_cnt'] ?: 0)); ?>건</div>
		</div>
		<div class="summary-card" style="background:linear-gradient(135deg,#7b1fa2 0%,#8e24aa 100%);border:none;color:#fff;">
			<div class="card-label" style="color:rgba(255,255,255,0.8);"><?php echo date("n"); ?>월 밴피</div>
			<div class="card-value" style="color:#fff;"><?php echo number_format($month_van_fee); ?>원</div>
			<div class="card-sub" style="color:rgba(255,255,255,0.7);"><?php echo number_format(($month_row['approve_cnt'] ?: 0) - ($month_row['cancel_cnt'] ?: 0)); ?>건</div>
		</div>
		<?php } else { ?>
		<!-- 일반 회원용 요약 카드 -->
		<div class="summary-card primary">
			<div class="card-label">오늘 결제</div>
			<div class="card-value"><?php echo number_format($today_total); ?>원</div>
			<div class="card-sub">승인 <?php echo number_format($today_row['approve_cnt'] ?: 0); ?>건 / 취소 <?php echo number_format($today_row['cancel_cnt'] ?: 0); ?>건</div>
		</div>
		<div class="summary-card success">
			<div class="card-label"><?php echo date("n"); ?>월 누적</div>
			<div class="card-value"><?php echo number_format($month_total); ?>원</div>
			<div class="card-sub">승인 <?php echo number_format($month_row['approve_pay'] ?: 0); ?> / 취소 <?php echo number_format($month_row['cancel_pay'] ?: 0); ?></div>
		</div>
		<div class="summary-card">
			<div class="card-label">오늘 승인</div>
			<div class="card-value" style="color:#2e7d32;"><?php echo number_format($today_row['approve_pay'] ?: 0); ?>원</div>
			<div class="card-sub"><?php echo number_format($today_row['approve_cnt'] ?: 0); ?>건</div>
		</div>
		<div class="summary-card">
			<div class="card-label">오늘 취소</div>
			<div class="card-value" style="color:#e53935;"><?php echo number_format($today_row['cancel_pay'] ?: 0); ?>원</div>
			<div class="card-sub"><?php echo number_format($today_row['cancel_cnt'] ?: 0); ?>건</div>
		</div>
		<?php } ?>
	</div>

	<!-- 패널 그리드 -->
	<div class="dashboard-grid">
		<!-- 이번달 결제현황 -->
		<div class="dashboard-panel">
			<div class="panel-header">
				<div class="panel-title"><i class="fa fa-calendar"></i><?php echo date("n"); ?>월 결제현황</div>
			</div>
			<div class="panel-body">
				<?php
					$month = date("Y-m");
					$today = date("Y-m-d");
					if($is_van_fee_member) {
						$sql = "SELECT
							date(pay_datetime) as date,
							count(if(pay_type = 'Y', 1, null)) as approve_cnt,
							count(if(pay_type = 'N', 1, null)) as cancel_cnt,
							sum(if(pay_type = 'Y', pay, 0)) as approve_pay,
							sum(if(pay_type = 'N', pay, 0)) as cancel_pay
						FROM g5_payment
						WHERE ".$adm_sql.$groups."
							AND SUBSTRING(pay_datetime,1,7) = '$month'
						GROUP BY date ORDER BY date DESC";
					} else {
						$sql = "SELECT
							date(pay_datetime) as date,
							sum(if(dv_type = '2' and pay_type = 'Y', pay, 0)) as sugis,
							sum(if(dv_type = '2' and pay_type = 'N', pay, 0)) as sugic,
							sum(if(dv_type = '1' and pay_type = 'Y', pay, 0)) as dans,
							sum(if(dv_type = '1' and pay_type = 'N', pay, 0)) as danc
						FROM g5_payment
						WHERE ".$adm_sql.$groups."
							AND SUBSTRING(pay_datetime,1,7) = '$month'
						GROUP BY date ORDER BY date DESC";
					}
					$result = sql_query($sql);
				?>
				<table class="table_list td_pd">
					<thead>
						<tr>
							<?php if($is_van_fee_member) { ?>
							<th>날짜</th>
							<th>승인</th>
							<th>취소</th>
							<th>밴피</th>
							<?php } else { ?>
							<th>날짜</th>
							<th>총승인</th>
							<th>온라인</th>
							<th>온라인취소</th>
							<th>오프라인</th>
							<th>오프라인취소</th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
						<?php
							for ($i=0; $row=sql_fetch_array($result); $i++) {
								$is_today = ($today == $row['date']);
								if($is_van_fee_member) {
									$day_van_fee = (($row['approve_cnt'] ?: 0) - ($row['cancel_cnt'] ?: 0)) * $member['mb_van_fee'];
								} else {
									$sums = $row['sugis'] + $row['dans'] + $row['sugic'] + $row['danc'];
								}
						?>
						<tr class="<?php echo $is_today ? 'today-row' : ''; ?>">
							<td><?php echo substr($row['date'],8,2); ?>/<?php echo $yoil[date('w', strtotime($row['date']))]; ?></td>
							<?php if($is_van_fee_member) { ?>
							<td style="text-align:right;font-weight:600;"><?php echo number_format($row['approve_cnt']); ?>건</td>
							<td style="text-align:right;color:#e53935;"><?php echo number_format($row['cancel_cnt']); ?>건</td>
							<td style="text-align:right;font-weight:600;color:#4caf50;"><?php echo number_format($day_van_fee); ?></td>
							<?php } else { ?>
							<td style="text-align:right;font-weight:600;"><?php echo number_format($sums); ?></td>
							<td style="text-align:right"><?php echo number_format($row['sugis']); ?></td>
							<td style="text-align:right"><?php echo number_format($row['sugic']); ?></td>
							<td style="text-align:right"><?php echo number_format($row['dans']); ?></td>
							<td style="text-align:right"><?php echo number_format($row['danc']); ?></td>
							<?php } ?>
						</tr>
						<?php } ?>
						<?php if($i == 0) { ?>
						<tr><td colspan="<?php echo $is_van_fee_member ? '4' : '6'; ?>" style="text-align:center;color:#999;padding:30px;">데이터가 없습니다</td></tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>

		<!-- 지난달 결제현황 -->
		<div class="dashboard-panel">
			<div class="panel-header">
				<div class="panel-title"><i class="fa fa-calendar-o"></i><?php echo $pn; ?>월 결제현황</div>
			</div>
			<div class="panel-body">
				<?php
					$month = date("Y-m", mktime(0, 0, 0, intval(date('m'))-1, intval(date('d')), intval(date('Y'))));
					if($is_van_fee_member) {
						$sql = "SELECT
							date(pay_datetime) as date,
							count(if(pay_type = 'Y', 1, null)) as approve_cnt,
							count(if(pay_type = 'N', 1, null)) as cancel_cnt,
							sum(if(pay_type = 'Y', pay, 0)) as approve_pay,
							sum(if(pay_type = 'N', pay, 0)) as cancel_pay
						FROM g5_payment
						WHERE ".$adm_sql.$groups."
							AND SUBSTRING(pay_datetime,1,7) = '$month'
						GROUP BY date ORDER BY date DESC";
					} else {
						$sql = "SELECT
							date(pay_datetime) as date,
							sum(if(dv_type = '2' and pay_type = 'Y', pay, 0)) as sugis,
							sum(if(dv_type = '2' and pay_type = 'N', pay, 0)) as sugic,
							sum(if(dv_type = '1' and pay_type = 'Y', pay, 0)) as dans,
							sum(if(dv_type = '1' and pay_type = 'N', pay, 0)) as danc
						FROM g5_payment
						WHERE ".$adm_sql.$groups."
							AND SUBSTRING(pay_datetime,1,7) = '$month'
						GROUP BY date ORDER BY date DESC";
					}
					$result = sql_query($sql);
				?>
				<table class="table_list td_pd">
					<thead>
						<tr>
							<?php if($is_van_fee_member) { ?>
							<th>날짜</th>
							<th>승인</th>
							<th>취소</th>
							<th>밴피</th>
							<?php } else { ?>
							<th>날짜</th>
							<th>총승인</th>
							<th>온라인</th>
							<th>온라인취소</th>
							<th>오프라인</th>
							<th>오프라인취소</th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
						<?php
							for ($i=0; $row=sql_fetch_array($result); $i++) {
								if($is_van_fee_member) {
									$day_van_fee = (($row['approve_cnt'] ?: 0) - ($row['cancel_cnt'] ?: 0)) * $member['mb_van_fee'];
								} else {
									$sums = $row['sugis'] + $row['dans'] + $row['sugic'] + $row['danc'];
								}
						?>
						<tr>
							<td><?php echo substr($row['date'],8,2); ?>/<?php echo $yoil[date('w', strtotime($row['date']))]; ?></td>
							<?php if($is_van_fee_member) { ?>
							<td style="text-align:right;font-weight:600;"><?php echo number_format($row['approve_cnt']); ?>건</td>
							<td style="text-align:right;color:#e53935;"><?php echo number_format($row['cancel_cnt']); ?>건</td>
							<td style="text-align:right;font-weight:600;color:#4caf50;"><?php echo number_format($day_van_fee); ?></td>
							<?php } else { ?>
							<td style="text-align:right;font-weight:600;"><?php echo number_format($sums); ?></td>
							<td style="text-align:right"><?php echo number_format($row['sugis']); ?></td>
							<td style="text-align:right"><?php echo number_format($row['sugic']); ?></td>
							<td style="text-align:right"><?php echo number_format($row['dans']); ?></td>
							<td style="text-align:right"><?php echo number_format($row['danc']); ?></td>
							<?php } ?>
						</tr>
						<?php } ?>
						<?php if($i == 0) { ?>
						<tr><td colspan="<?php echo $is_van_fee_member ? '4' : '6'; ?>" style="text-align:center;color:#999;padding:30px;">데이터가 없습니다</td></tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>

		<!-- 월별 결제현황 -->
		<div class="dashboard-panel">
			<div class="panel-header">
				<div class="panel-title"><i class="fa fa-bar-chart"></i>월별 결제현황</div>
			</div>
			<div class="panel-body">
				<?php
					if($member['mb_level'] == 10) {
						if(adm_sql_common) {
							$adm_sql2 = " mb_1 IN (".adm_sql_common.")";
						} else {
							$adm_sql2 = " (1)";
						}
					} else if($member['mb_level'] == 8) {
						$adm_sql2 = " mb_1 = '{$member['mb_id']}'";
					} else if($member['mb_level'] == 7) {
						$adm_sql2 = " mb_2 = '{$member['mb_id']}'";
					} else if($member['mb_level'] == 6) {
						$adm_sql2 = " mb_3 = '{$member['mb_id']}'";
					} else if($member['mb_level'] == 5) {
						$adm_sql2 = " mb_4 = '{$member['mb_id']}'";
					} else if($member['mb_level'] == 4) {
						$adm_sql2 = " mb_5 = '{$member['mb_id']}'";
					} else if($member['mb_level'] == 3) {
						$adm_sql2 = " mb_6 = '{$member['mb_id']}'";
					}

					if($is_van_fee_member) {
						$sql = "SELECT DATE_FORMAT(`pay_datetime`,'%Y-%m') m,
							count(if(pay_type = 'Y', 1, null)) as approve_cnt,
							count(if(pay_type != 'Y', 1, null)) as cancel_cnt,
							sum(if(pay_type = 'Y', pay, 0)) as approve_pay,
							sum(if(pay_type != 'Y', pay, 0)) as cancel_pay
						FROM g5_payment
						WHERE ".$adm_sql2." AND mb_6 != ''
						GROUP BY m ORDER BY m DESC";
					} else {
						$sql = "SELECT DATE_FORMAT(`pay_datetime`,'%Y-%m') m,
							sum(if(dv_type = '2' and pay_type = 'Y', pay, 0)) as sugis,
							sum(if(dv_type = '2' and pay_type != 'Y', pay, 0)) as sugic,
							sum(if(dv_type != '2' and pay_type = 'Y', pay, 0)) as dans,
							sum(if(dv_type != '2' and pay_type != 'Y', pay, 0)) as danc
						FROM g5_payment
						WHERE ".$adm_sql2." AND mb_6 != ''
						GROUP BY m ORDER BY m DESC";
					}
					$result = sql_query($sql);
				?>
				<table class="table_list td_pd">
					<thead>
						<tr>
							<?php if($is_van_fee_member) { ?>
							<th>월</th>
							<th>승인</th>
							<th>취소</th>
							<th>밴피</th>
							<?php } else { ?>
							<th>월</th>
							<th>총승인</th>
							<th>온라인</th>
							<th>온라인취소</th>
							<th>오프라인</th>
							<th>오프라인취소</th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
						<?php
							for ($i = 0; $mons = sql_fetch_array($result); $i++) {
								$month_str = substr($mons['m'],2,5);
								if($is_van_fee_member) {
									$month_van_fee_calc = (($mons['approve_cnt'] ?: 0) - ($mons['cancel_cnt'] ?: 0)) * $member['mb_van_fee'];
								} else {
									$sums = $mons['sugis'] + $mons['dans'] + $mons['sugic'] + $mons['danc'];
								}
						?>
						<tr>
							<td><?php echo str_replace("-", "/", $month_str); ?></td>
							<?php if($is_van_fee_member) { ?>
							<td style="text-align:right;font-weight:600;"><?php echo number_format($mons['approve_cnt']); ?>건</td>
							<td style="text-align:right;color:#e53935;"><?php echo number_format($mons['cancel_cnt']); ?>건</td>
							<td style="text-align:right;font-weight:600;color:#4caf50;"><?php echo number_format($month_van_fee_calc); ?></td>
							<?php } else { ?>
							<td style="text-align:right;font-weight:600;"><?php echo number_format($sums); ?></td>
							<td style="text-align:right"><?php echo number_format($mons['sugis']); ?></td>
							<td style="text-align:right"><?php echo number_format($mons['sugic']); ?></td>
							<td style="text-align:right"><?php echo number_format($mons['dans']); ?></td>
							<td style="text-align:right"><?php echo number_format($mons['danc']); ?></td>
							<?php } ?>
						</tr>
						<?php } ?>
						<?php if($i == 0) { ?>
						<tr><td colspan="<?php echo $is_van_fee_member ? '4' : '6'; ?>" style="text-align:center;color:#999;padding:30px;">데이터가 없습니다</td></tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
