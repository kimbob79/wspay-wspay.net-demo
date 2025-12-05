<?php
	$title1 = "결제관리";
	$title2 = "일간 결제내역";

	$fr_dates = date("Y-m-d", strtotime($fr_date));
	$to_dates = date("Y-m-d", strtotime($to_date));
	$ym_date = $year."-".sprintf('%02d', $month);;

	$sql_fild = " * ";
	$sql_fild .= ", SUBSTRING(pay_datetime,1,10) as pay_date "; // 날짜
	$sql_fild .= ", sum(if(pay_type = 'Y', pay, null)) as spay "; // 승인금액
	$sql_fild .= ", sum(if(pay_type != 'Y', pay, null)) as cpay "; // 취소금액
	/*
	$sql_fild .= ", count(if(pay_type = 'Y', pay_type, null)) as scnt "; // 승인 건수
	$sql_fild .= ", count(if(pay_type != 'Y', pay_type, null)) as ccnt "; // 취소 건수
	$sql_fild .= ", sum(pay) as total_pay "; // 합계
	$sql_fild .= ", mb_1_name as mb1_name "; // 본사명
	$sql_fild .= ", mb_2_name as mb2_name "; // 본사명
	$sql_fild .= ", mb_3_name as mb3_name "; // 본사명
	$sql_fild .= ", mb_4_name as mb4_name "; // 본사명
	$sql_fild .= ", mb_5_name as mb5_name "; // 본사명
	$sql_fild .= ", mb_6_name as mb6_name "; // 본사명
	$sql_fild .= ", mb_1_fee as mb_1_fee "; // 본사 수수료
	$sql_fild .= ", mb_2_fee as mb_2_fee "; // 본사 수수료
	$sql_fild .= ", mb_3_fee as mb_3_fee "; // 본사 수수료
	$sql_fild .= ", mb_4_fee as mb_4_fee "; // 본사 수수료
	$sql_fild .= ", mb_5_fee as mb_5_fee "; // 본사 수수료
	$sql_fild .= ", mb_6_fee as mb_6_fee "; // 본사 수수료

	$sql_fild .= ", sum(mb_1_pay) as mb_1_pay "; // 본사 수수료
	$sql_fild .= ", sum(mb_2_pay) as mb_2_pay "; // 지사 수수료
	$sql_fild .= ", sum(mb_3_pay) as mb_3_pay "; // 총판 수수료
	$sql_fild .= ", sum(mb_4_pay) as mb_4_pay "; // 대리점 수수료
	$sql_fild .= ", sum(mb_5_pay) as mb_5_pay "; // 영업점 수수료
	$sql_fild .= ", sum(mb_6_pay) as mb_6_pay "; // 가맹점 정산액
	*/


	if($is_admin) {

		if(adm_sql_common) {
			$adm_sql = " mb_1 IN (".adm_sql_common.")";
		} else {
			$adm_sql = " (1)";
		}

	} else {
		if($member['mb_level'] == "8") { // 본사
			$adm_sql .= " mb_1 = '{$member['mb_id']}'  ";
		} else if($member['mb_level'] == "7") { // 지사
			$adm_sql .= " mb_2 = '{$member['mb_id']}'  ";
		} else if($member['mb_level'] == "6") { // 총판
			$adm_sql .= " mb_3 = '{$member['mb_id']}'  ";
		} else if($member['mb_level'] == "5") { // 대리점
			$adm_sql .= " mb_4 = '{$member['mb_id']}'  ";
		} else if($member['mb_level'] == "4") { //  영업점
			$adm_sql .= " mb_5 = '{$member['mb_id']}'  ";
		} else if($member['mb_level'] == "3") { // 가맹점
			$adm_sql .= " mb_6 = '{$member['mb_id']}'  ";
		}
	}

	$sql_common = " from g5_payment where ".$adm_sql;

	if($member['mb_type'] == 1) {
	} else if($member['mb_type'] == 2) {
		$sql_search = " and mb_pid2 = '{$member['mb_id']}' ";
	} else if($member['mb_type'] == 3) {
		$sql_search = " and mb_pid3 = '{$member['mb_id']}' ";
	} else if($member['mb_type'] == 4) {
		$sql_search = " and mb_pid4 = '{$member['mb_id']}' ";
	} else if($member['mb_type'] == 5) {
		$sql_search = " and mb_pid5 = '{$member['mb_id']}' ";
	} else if($member['mb_type'] == 6) {
		$sql_search = " and mb_pid6 = '{$member['mb_id']}' ";
	} else if($member['mb_type'] == 7) {
		$sql_search = " and mb_pid7 = '{$member['mb_id']}' ";
	} else if($member['mb_type'] == 8) {
		$sql_search = " and mb_pid8 = '{$member['mb_id']}' ";
	} else {
	}

	$sql_search = "and {$sfl} = '{$stx}' ";

	if ($sst)
		$sql_order = " order by {$sst} {$sod} ";
	else
		$sql_order = " order by pay_datetime desc ";
	if($ym_date > 0) {
		$sql = " select count(*) as cnt {$sql_common} {$sql_search}  ";
		$row = sql_fetch($sql);

		$total_count = $row['cnt']; // 전체개수

		$page_count = "200";

		if($page_count) {
			$rows = $page_count;
		} else {
			$rows = $config['cf_page_rows'];
		}

		$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
		if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
		$from_record = ($page - 1) * $rows; // 시작 열을 구함

		$sql = " select {$sql_fild} {$sql_common} {$sql_search} and SUBSTRING(pay_datetime,1,7) between '$ym_date' and '$ym_date' GROUP BY pay_date; ";
		$result = sql_query($sql);
	}
//	echo $sql;
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div class="index_menu">
	<ul class="shortcut">
		<li class="sc_current"><a>일간 결제내역</a></li>
		<li class="sc_visit">
			<aside id="visit">
			</aside>
		</li>
	</ul>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<input type="hidden" name="p" value="<?php echo $p; ?>">
	<div class="searchbox">
		<div class="midd">
			<ul>
				<li>
					<strong>일자</strong>
					<div>

						<?php /*
						<div>
							<input type="text" name="fr_date" value="<?php echo $fr_date ?>" id="fr_date" class="frm_input" size="6" maxlength="10">
						</div>
						<span>~</span>
						<div>
							<input type="text" name="to_date" value="<?php echo $to_date ?>" id="to_date" class="frm_input" size="6" maxlength="10">
						</div>
						*/
						?>


						<select name="year" id="year" required>
							<option value="">년 선택</option>
							<?php
							$currentYear = date("Y");
								for ($i = $currentYear; $i >= $currentYear - 1; $i--) {
									$selectedy = "";
									if($year == $i) { $selectedy = 'selected'; }
									echo "<option value='".$i."' ".$selectedy.">".$i."년</option>";
								}
							?>
						</select>

						<select name="month" id="month" required>
							<option value="">월 선택</option>
							<?php
								for ($i = 1; $i <= 12; $i++) {
									$selectedm = "";
									if($month == $i) { $selectedm = 'selected'; }
									echo "<option value='".$i."' ".$selectedm.">".$i."월</option>";
								}
							?>
						</select>

					</div>
				</li>
				<li>
					<strong>검색</strong>
					<div>
						<div data-skin="radio">
							<label><input type="radio" name="sfl" value="mb_6_name" <?php echo get_checked($sfl, "mb_6_name"); ?> checked> 가맹점명</label>
							<label><input type="radio" name="sfl" value="dv_tid" <?php echo get_checked($sfl, "dv_tid"); ?>> TID</label>
						</div>
					</div>
				</li>
				<li>
					<strong>검색</strong>
					<div>
						<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input" size="7" placeholder="가맹점명/TID" style="width:150px;" required>
						<button type="submit" class="btn_b btn_b02"><span>검색</span></button>
					</div>
				</li>
			</ul>
		</div>
	</div>
</form>





<div class="m_board_scroll">
	<div class="m_table_wrap">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
				<tr>
					<th style="width:85px;">일자</th>
					<th style="width:150px;">가맹점명</th>
					<th style="width:88px;">TID</th>
					<th style="width:110px;">승인</th>
					<th style="width:110px;">취소</th>
					<th style="width:110px;">합계</th>
					<th>차트</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$day_sum = 0;
					for ($i=0; $row=sql_fetch_array($result); $i++) {
					$day_sum = $row['spay'] + $row['cpay'];
					$spay_sum = $spay_sum + $row['spay'];
					$cpay_sum = $cpay_sum + $row['cpay'];
					$total_sum = $total_sum + $day_sum;

				?>
				<tr<?php echo $bgcolor?" style='background: $bgcolor;'":'';?>>
					<td><?php echo $row['pay_date']; ?></td>
					<td class="td_name"><strong><?php echo $row['mb_6_name']; ?></strong></td>
					<td><?php echo $row['dv_tid']; ?></td>
					<td style="text-align:right"><?php echo number_format($row['spay']); ?></td>
					<td style="text-align:right"><?php echo number_format($row['cpay']); ?></td>
					<td style="text-align:right;font-weight:bold"><?php echo number_format($day_sum); ?></td>
					<td><canvas class="chart" width="45" height="1" data-spay="<?php echo $day_sum; ?>"></canvas></td>
				</tr>
				<?php } ?>
				<?php if($i == 0) { ?>
				<tr><td colspan="7" style="height:100px; background:#eee; color:#888; line-height:100px;">날짜와 가맹점명 또는 TID를 입력하세요</td></tr>
				<?php } else { ?>
				<tr<?php echo $bgcolor?" style='background: $bgcolor;'":'';?>>
					<th colspan="3" style="font-weight:bold">합계</th>
					<th style="text-align:right;font-weight:bold"><?php echo number_format($spay_sum); ?></th>
					<th style="text-align:right;font-weight:bold"><?php echo number_format($cpay_sum); ?></th>
					<th style="text-align:right;font-weight:bold"><?php echo number_format($total_sum); ?></th>
					<th></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>

<?php
	$chart_max =  round($total_sum/5);
?>

<script>


/*
	var ctx = document.getElementById('myChart').getContext('2d');
	var myChart = new Chart(ctx, {
		type: 'bar',
		options: {
			legend: {
				display: false,
			},
			scales: {
				xAxes: [{
					gridLines: { //A축 gridLines 지우는 옵션
						display: false,
						drawBorder: false,
					},
					ticks: {
						fontSize: 15,
						fontColor: 'black'
					}
				}],
				yAxes: [{
					gridLines: { //Y축 gridLines 지우는 옵션
						drawBorder: false,
						display: false,
					},
					ticks: {
						beginAtZero: true,
						fontSize: 15,
						fontColor: 'lightgrey',
						maxTicksLimit: 5,
						padding: 25,
					}
				}]
			},
			tooltips: {
				backgroundColor: '#1e90ff'
			}
		},
		data: {
			labels: ['M', 'Tu', 'W', 'Th', 'F', 'Sa', 'Su'],
			datasets: [{
				data: [0, 0, 0, 11, 9, 17, 13],
				tension: 0.0,
				borderColor: 'rgb(255,190,70)',
				backgroundColor: 'rgba(0,0,0,0.0)',
				pointBackgroundColor: ['white', 'white', 'white', 'white', 'white', 'white', 'rgb(255,190,70)'],
				pointRadius: 4,
				borderWidth: 2
			}]
		}
	});
*/



var canvases = document.querySelectorAll('.chart');
canvases.forEach(function(canvas) {
	var ctx = canvas.getContext('2d');
	var spay = parseInt(canvas.getAttribute('data-spay'));

	var myChart = new Chart(ctx, {
		type: 'bar',
		data: {
			labels: [''],
			datasets: [{
				data: [spay],
				backgroundColor: ['#2a57d7'],
			}]
		},
		options: {
			indexAxis: 'y',
			plugins: {
				legend: false,
				datalabels: {
					display: false,
				},
			},
			scales: {
				x: {
					axis: 'x',
					min:0,
					max: <?php echo $chart_max; ?>,
					position: 'top',
					display: false,
					title: {
					  display: false
					},
				},
				y: {
     				grid: {
						display: false,
					},
					pointLabels: {
						display: false,
					}
				},
			}
		}
	});
});
</script>