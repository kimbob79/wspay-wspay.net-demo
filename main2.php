<?php
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
?>


<?php /*
<div class="index_menu">
	<ul class="shortcut">
		<li><a href="https://theme.sir.kr/gnuboard55/bbs/faq.php"><i class="fa fa-question-circle"></i> Q&amp;A</a></li>
		<li><a href="https://theme.sir.kr/gnuboard55/bbs/qalist.php"><i class="fa fa-comments"></i> FAQ</a></li>
		<li><a href="https://theme.sir.kr/gnuboard55/bbs/new.php"><i class="fa fa-history"></i> 새글</a></li>
		<li class="sc_current"><a href="https://theme.sir.kr/gnuboard55/bbs/current_connect.php"><i class="fa fa-users"></i> 접속자</a></li>
		<li class="sc_visit">
			<aside id="visit">
				<h2>접속자집계 <i class="fa fa-angle-right"></i></h2>
				<ul>
					<li>오늘<span>14</span></li>
					<li>어제<span>21</span></li>
					<li>최대<span>142</span></li>
					<li>전체<span>11,352</span></li>
				</ul>
			</aside>
		</li>
	</ul>
</div>
*/ ?>

<div class="conle_idx_top pc">
	<div class="lt" style="float:left;width:32%">
		<div class="bx-wrapper" style="max-width: 100%;">
			<div class="bx-viewport" style="width: 100%; overflow: hidden; position:">

				<div class="lt_slider" style="width: 100%%; position: relative; transition-duration: 0s;">
					<div class="lt_slider_li">
						<strong><a><?php echo date("n");?>월 결제현황</a></strong>

						<div class="desc_toolcont">
							<?php
								$month = date("Y-m");
								$today = date("Y-m-d");
								$sql = " select
											date(pay_datetime) as date,
											sum(if(dv_type = '2' and pay_type = 'Y', pay, null)) as sugis,
											sum(if(dv_type = '2' and pay_type = 'N', pay, null)) as sugic,
											sum(if(dv_type = '1' and pay_type = 'Y', pay, null)) as dans,
											sum(if(dv_type = '1' and pay_type = 'N', pay, null)) as danc
										from
											g5_payment
										WHERE
										".$adm_sql.$groups."
											and SUBSTRING(pay_datetime,1,7) = '$month'
										GROUP BY date";
								$result = sql_query($sql);
							//	echo $sql;
							?>
		
							<table class="table_list td_pd">
								<thead>
									<tr>
										<th scope="col" style="width:5%;">날짜</th>
										<th scope="col">총승인</th>
										<th scope="col" style="width:18%;">수기</th>
										<th scope="col" style="width:18%;">수기취소</th>
										<th scope="col" style="width:18%;">단말기</th>
										<th scope="col" style="width:18%;">단말기취소</th>
									</tr>
								</thead>
								<tbody>
									<?php
										$sums = "";
										$todays = "";
										for ($i=0; $row=sql_fetch_array($result); $i++) {
											$sums = $row['sugis'] + $row['dans'] + $row['sugic'] + $row['danc'];
											if($today == $row['date']) {
												$todays = "1";
											}
									?>
									<tr>
										<td style="text-align:center;<?php if($todays == "1") { echo "background:#4d0585;color:#fff"; } ?>"><?php echo substr($row['date'],8,2) ?>/<?php echo($yoil[date('w', strtotime($row['date']))]); ?></td>
										<td style="text-align:right;font-weight:bold;<?php if($todays == "1") { echo "background:#4d0585;color:#fff"; } ?>"><?php echo number_format($sums); ?></td>
										<td style="text-align:right;<?php if($todays == "1") { echo "background:#4d0585;color:#fff"; } ?>"><?php echo number_format($row['sugis']); ?></td>
										<td style="text-align:right;<?php if($todays == "1") { echo "background:#4d0585;color:#fff"; } ?>"><?php echo number_format($row['sugic']); ?></td>
										<td style="text-align:right;<?php if($todays == "1") { echo "background:#4d0585;color:#fff"; } ?>"><?php echo number_format($row['dans']); ?></td>
										<td style="text-align:right;<?php if($todays == "1") { echo "background:#4d0585;color:#fff"; } ?>"><?php echo number_format($row['danc']); ?></td>
									</tr>
									<?php
									}
									?>
								</tbody>
							</table>
						</div>


						<?php
							if($is_admin) {
								$yoil = array("일","월","화","수","목","금","토");

								for($k=0; $k<2; $k++) {
									if($k==0) {			$days = date('y.m.d');
									} else if($k==1) {	$days = date('y.m.d', strtotime('-1 day'));
									} else if($k==2) {	$days = date('y.m.d', strtotime('-2 day'));
									} else if($k==3) {	$days = date('y.m.d', strtotime('-3 day'));
									} else if($k==4) {	$days = date('y.m.d', strtotime('-4 day'));
									} else if($k==5) {	$days = date('y.m.d', strtotime('-5 day'));
									} else if($k==6) {	$days = date('y.m.d', strtotime('-6 day'));
									}

									if(adm_sql_common) {
										$adm_sql = " mb_1 IN (".adm_sql_common.")";
									} else {
										$adm_sql = " (1)";
									}

									$sql_fild = " * ";
									$sql_fild .= ", count(if(pay_type = 'Y', pay_type, null)) as scnt "; // 승인 건수
									$sql_fild .= ", count(if(pay_type != 'Y', pay_type, null)) as ccnt "; // 취소 건수
									$sql_fild .= ", sum(if(pay_type = 'Y', pay, 0)) as spay "; // 승인금액
									$sql_fild .= ", sum(if(pay_type != 'Y', pay, 0)) as cpay "; // 취소금액
									$sql_fild .= ", (SUM(IF(pay_type = 'Y', pay, 0)) + SUM(IF(pay_type != 'Y', pay, 0))) as total_pay "; // 합계
								//	$sql_fild .= ", sum(a.pay) as total_pay "; // 합계

									$sql_common = " from g5_payment ";

									$sql_search = " where ".$adm_sql." and mb_6 != '' and (pay_datetime BETWEEN '{$days} 00:00:00' and '{$days} 23:59:59') ";

									$sql = " select {$sql_fild} {$sql_common} {$sql_search} group by mb_6 having pay <> 0 ORDER BY total_pay desc limit 0,20";
									$result = sql_query($sql);
								//	echo $member['mb_type']."<br>";
								//	echo $sql;
						?>
						<BR>
						<strong><a><?php echo $days;?> (<?php echo($yoil[date('w', strtotime($days))]);?>)</span> 가맹점 TOP 20</a></strong>

						<div class="desc_toolcont" style="padding-bottom:115px;">
							<table class="table_list td_pd">
								<thead>
									<tr>
										<th style="min-width:160px">가맹점명</th>
										<th style="width:60px">승/취소</th>
										<th style="width:18%">총승인</th>
										<th style="width:18%">승인</th>
										<th style="width:18%">취소</th>
									</tr>
								</thead>
								<tbody>
									<?php
										for ($i=0; $row=sql_fetch_array($result); $i++) {
										$total_pay = $row['spay'] + $row['cpay'];

									?>
									<tr>
										<td class="td_name"><span class="simptip-position-bottom simptip-movable half-arrow simptip-multiline simptip-black" data-tooltip="본　사 : <?php if($row['mb_1_name']) { echo $row['mb_1_name']. " / ".$row['mb_1_fee']; } ?>&#10;지　사 : <?php if($row['mb_2_name']) { echo $row['mb_2_name']. " / ".$row['mb_2_fee']; } ?>&#10;총　판 : <?php if($row['mb_3_name']) { echo $row['mb_3_name']. " / ".$row['mb_3_fee']; } ?>&#10;대리점 : <?php if($row['mb_4_name']) { echo $row['mb_4_name']. " / ".$row['mb_4_fee']; } ?>&#10;영업점 : <?php if($row['mb_5_name']) { echo $row['mb_5_name']. " / ".$row['mb_5_fee']; } ?>"><?php echo $row['mb_6_name']; ?></span></td>
										<td><?php echo $row['scnt']; ?> / <?php echo $row['ccnt']; ?></td>
										<td style="text-align:right;font-weight: bold;"><?php echo number_format($total_pay); ?></td>
										<td style="text-align:right"><?php echo number_format($row['spay']); ?></td>
										<td style="text-align:right"><?php echo number_format($row['cpay']); ?></td>
									</tr>
									<?php
										}
										if($i == 0) {
											echo '<tr><td colspan="5" style="height:100px; background:#eee; color:#888; line-height:100px;">결제 내역이 없습니다.</td></tr>';
										}
									?>
								</tbody>
							</table>
						</div>

						<?php
								}
							}
						?>



					</div>
				</div>

			</div>
		</div>
	</div>
	<?php
		$d = mktime(0,0,0, date("m"), 1, date("Y")); //이번달 1일
		$prev_month = strtotime("-1 month", $d); //한달전
		//echo date("Y-m-01", $prev_month ); //지난달 1일
		$pn = date("n", $prev_month ); //지난달 말일
	?>
	<div class="lt lt_even" style="float:left;width:32%">
		<div class="bx-wrapper" style="max-width: 100%;">
			<div class="bx-viewport" style="width: 100%; overflow: hidden; position: relative;">

				<div class="lt_slider" style="width: 100%%; position: relative; transition-duration: 0s;">
					<div class="lt_slider_li">
						<strong><a><?php echo $pn; ?>월 결제현황</a></strong>
						
						<div class="desc_toolcont">
							<?php
								$month = date("Y-m", mktime(0, 0, 0, intval(date('m'))-1, intval(date('d')), intval(date('Y'))  ));
								$sql = " select
											date(pay_datetime) as date,
											sum(if(dv_type = '2' and pay_type = 'Y', pay, null)) as sugis,
											sum(if(dv_type = '2' and pay_type = 'N', pay, null)) as sugic,
											sum(if(dv_type = '1' and pay_type = 'Y', pay, null)) as dans,
											sum(if(dv_type = '1' and pay_type = 'N', pay, null)) as danc
										from
											g5_payment
										WHERE
										".$adm_sql.$groups."
											and SUBSTRING(pay_datetime,1,7) = '$month'
										GROUP BY date";
								$result = sql_query($sql);
							?>
		
							<table class="table_list td_pd">
								<thead>
									<tr>
										<th scope="col" style="width:5%;">날짜</th>
										<th scope="col">총승인</th>
										<th scope="col" style="width:18%;">수기</th>
										<th scope="col" style="width:18%;">수기취소</th>
										<th scope="col" style="width:18%;">단말기</th>
										<th scope="col" style="width:18%;">단말기취소</th>
									</tr>
								</thead>
								<tbody>
									<?php
										$sums = "";
										for ($i=0; $row=sql_fetch_array($result); $i++) {
											$sums = $row['sugis'] + $row['dans'] + $row['sugic'] + $row['danc'];
									?>
									<tr>
										<td style="text-align:center;"><?php echo substr($row['date'],8,2) ?>/<?php echo($yoil[date('w', strtotime($row['date']))]); ?></td>
										<td style="text-align:right;font-weight:bold;"><?php echo number_format($sums); ?></td>
										<td style="text-align:right"><?php echo number_format($row['sugis']); ?></td>
										<td style="text-align:right"><?php echo number_format($row['sugic']); ?></td>
										<td style="text-align:right"><?php echo number_format($row['dans']); ?></td>
										<td style="text-align:right"><?php echo number_format($row['danc']); ?></td>
									</tr>
									<?php
									}
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="lt lt_even" style="float:left;width:32%;">
		<div class="bx-wrapper" style="max-width: 100%;">
			<div class="bx-viewport" style="width: 100%; overflow: hidden;">

				<div class="lt_slider" style="width: 100%%; position: relative; transition-duration: 0s;">
					<div class="lt_slider_li">
						<strong><a>월별 결제현황</a></strong>
						
						<div class="desc_toolcont">

							<table class="table_list td_pd">
								<thead>
									<tr>
										<th scope="col" style="width:5%;">월</th>
										<th scope="col">총승인</th>
										<th scope="col" style="width:18%;">수기</th>
										<th scope="col" style="width:18%;">수기취소</th>
										<th scope="col" style="width:18%;">단말기</th>
										<th scope="col" style="width:18%;">단말기취소</th>
									</tr>
								</thead>
								<tbody>
									<?php
										if($member['mb_level'] == 10) {

											if(adm_sql_common) {
												$adm_sql = " mb_1 IN (".adm_sql_common.")";
											} else {
												$adm_sql = " (1)";
											}

										} else if($member['mb_level'] == 8) {
											$adm_sql = " mb_1 = '{$member['mb_id']}'";
										} else if($member['mb_level'] == 7) {
											$adm_sql = " mb_2 = '{$member['mb_id']}'";
										} else if($member['mb_level'] == 6) {
											$adm_sql = " mb_3 = '{$member['mb_id']}'";
										} else if($member['mb_level'] == 5) {
											$adm_sql = " mb_4 = '{$member['mb_id']}'";
										} else if($member['mb_level'] == 4) {
											$adm_sql = " mb_5 = '{$member['mb_id']}'";
										} else if($member['mb_level'] == 3) {
											$adm_sql = " mb_6 = '{$member['mb_id']}'";
										}


										$sql = " SELECT *, MONTH(`datetime`) AS `date`,
														sum(if(dv_type = '2' and pay_type = 'Y', pay, null)) as orderprices,
														sum(if(dv_type = '1' and pay_type = 'Y', pay, null)) as orderpriced,
														sum(if(pay_type = 'N', pay, null)) as cancelprice
													from g5_payment
													where ".$adm_sql."  GROUP BY date order by datetime desc";


										$sql = "SELECT DATE_FORMAT(`datetime`,'%Y-%m') m, 
												sum(if(dv_type = '2' and pay_type = 'Y', pay, null)) as sugis,
												sum(if(dv_type = '2' and pay_type = 'N', pay, null)) as sugic,
												sum(if(dv_type = '1' and pay_type = 'Y', pay, null)) as dans,
												sum(if(dv_type = '1' and pay_type = 'N', pay, null)) as danc
												FROM g5_payment
												where ".$adm_sql."   GROUP BY m order by m desc";

										$result = sql_query($sql);
										//echo $sql."<br><br>";
										for ($i = 0; $mons = sql_fetch_array($result); $i++) {
										$sums = $mons['sugis'] + $mons['dans'] + $mons['sugic'] + $mons['danc'];
										$mons['orders'] = (int)$mons['orderprices'];
										$mons['orderd'] = (int)$mons['orderpriced'];
										$mons['cancel'] = (int)$mons['cancelprice'];
										$month = substr($mons['m'],2,5);
									?>
									<tr>
										<td style="text-align:center;"><?php echo str_replace("-", "/", $month); ?></td>
										<td style="text-align:right;font-weight:bold;"><?php echo number_format($sums); ?></div>
										<td style="text-align:right"><?php echo number_format($mons['sugis']); ?></td>
										<td style="text-align:right"><?php echo number_format($mons['sugic']); ?></td>
										<td style="text-align:right"><?php echo number_format($mons['dans']); ?></td>
										<td style="text-align:right"><?php echo number_format($mons['danc']); ?></td>
									</tr>

									<?php
									}
									?>

								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="conle_idx_top mobile">
	<div class="lt" style="float:left;width:49.5%;">
		<div class="bx-wrapper" style="max-width: 100%;">
			<div class="bx-viewport" style="width: 100%; overflow: hidden;">

				<div class="lt_slider" style="width: 100%%; position: relative; transition-duration: 0s;">
					<div class="lt_slider_li">
						<strong><a><?php echo date("n");?>월 결제현황</a></strong>

						<div class="desc_toolcont">
							<?php
								$month = date("Y-m");
								$today = date("Y-m-d");
								$sql = " select
											date(pay_datetime) as date,
											sum(if(dv_type = '2' and pay_type = 'Y', pay, null)) as sugis,
											sum(if(dv_type = '2' and pay_type = 'N', pay, null)) as sugic,
											sum(if(dv_type = '1' and pay_type = 'Y', pay, null)) as dans,
											sum(if(dv_type = '1' and pay_type = 'N', pay, null)) as danc
										from
											g5_payment
										WHERE
										".$adm_sql.$groups."
											and SUBSTRING(pay_datetime,1,7) = '$month'
										GROUP BY date order by datetime desc";
								$result = sql_query($sql);
							?>
		
							<table class="table_list td_pd">
								<thead>
									<tr>
										<th scope="col" style="width:5%;">날짜</th>
										<th scope="col">총승인</th>
										<th scope="col" style="width:18%;">수기</th>
										<th scope="col" style="width:18%;">수기취소</th>
										<th scope="col" style="width:18%;">단말기</th>
										<th scope="col" style="width:18%;">단말기취소</th>
									</tr>
								</thead>
								<tbody>
									<?php
										$sums = "";
										$todays = "";
										for ($i=0; $row=sql_fetch_array($result); $i++) {
											$sums = $row['sugis'] + $row['dans'] + $row['sugic'] + $row['danc'];
											if($today == $row['date']) {
												$todays = "1";
											} else {
												$todays = "0";
											}
									?>
									<tr>
										<td style="text-align:center;<?php if($todays == "1") { echo "background:#4d0585;color:#fff"; } ?>"><?php echo substr($row['date'],8,2) ?>/<?php echo($yoil[date('w', strtotime($row['date']))]); ?></td>
										<td style="text-align:right;font-weight:bold;<?php if($todays == "1") { echo "background:#4d0585;color:#fff"; } ?>"><?php echo number_format($sums); ?></td>
										<td style="text-align:right;<?php if($todays == "1") { echo "background:#4d0585;color:#fff"; } ?>"><?php echo number_format($row['sugis']); ?></td>
										<td style="text-align:right;<?php if($todays == "1") { echo "background:#4d0585;color:#fff"; } ?>"><?php echo number_format($row['sugic']); ?></td>
										<td style="text-align:right;<?php if($todays == "1") { echo "background:#4d0585;color:#fff"; } ?>"><?php echo number_format($row['dans']); ?></td>
										<td style="text-align:right;<?php if($todays == "1") { echo "background:#4d0585;color:#fff"; } ?>"><?php echo number_format($row['danc']); ?></td>
									</tr>
									<?php
									}
									?>
								</tbody>
							</table>
						</div>


						<?php
							if($is_admin) {
								$yoil = array("일","월","화","수","목","금","토");

								for($k=0; $k<2; $k++) {
									if($k==0) {			$days = date('y.m.d');
									} else if($k==1) {	$days = date('y.m.d', strtotime('-1 day'));
									} else if($k==2) {	$days = date('y.m.d', strtotime('-2 day'));
									} else if($k==3) {	$days = date('y.m.d', strtotime('-3 day'));
									} else if($k==4) {	$days = date('y.m.d', strtotime('-4 day'));
									} else if($k==5) {	$days = date('y.m.d', strtotime('-5 day'));
									} else if($k==6) {	$days = date('y.m.d', strtotime('-6 day'));
									}

									if(adm_sql_common) {
										$adm_sql = " mb_1 IN (".adm_sql_common.")";
									} else {
										$adm_sql = " (1)";
									}

									$sql_fild = " * ";
									$sql_fild .= ", count(if(pay_type = 'Y', pay_type, null)) as scnt "; // 승인 건수
									$sql_fild .= ", count(if(pay_type != 'Y', pay_type, null)) as ccnt "; // 취소 건수
									$sql_fild .= ", sum(if(pay_type = 'Y', pay, 0)) as spay "; // 승인금액
									$sql_fild .= ", sum(if(pay_type != 'Y', pay, 0)) as cpay "; // 취소금액
									$sql_fild .= ", (SUM(IF(pay_type = 'Y', pay, 0)) + SUM(IF(pay_type != 'Y', pay, 0))) as total_pay "; // 합계
								//	$sql_fild .= ", sum(a.pay) as total_pay "; // 합계

									$sql_common = " from g5_payment ";

									$sql_search = " where ".$adm_sql." and mb_6 != '' and (pay_datetime BETWEEN '{$days} 00:00:00' and '{$days} 23:59:59') ";

									$sql = " select {$sql_fild} {$sql_common} {$sql_search} group by mb_6 having pay <> 0 ORDER BY total_pay desc limit 0,20";
									$result = sql_query($sql);
								//	echo $member['mb_type']."<br>";
								//	echo $sql;
						?>
						<BR>
						<strong><a><?php echo $days;?> (<?php echo($yoil[date('w', strtotime($days))]);?>)</span> 가맹점 TOP 20</a></strong>

						<div class="desc_toolcont" style="padding-bottom:115px;">
							<table class="table_list td_pd">
								<thead>
									<tr>
										<th style="min-width:160px">가맹점명</th>
										<th style="width:60px">승/취소</th>
										<th style="width:100px">총승인</th>
										<th style="width:100px">승인</th>
										<th style="width:100px">취소</th>
									</tr>
								</thead>
								<tbody>
									<?php
										for ($i=0; $row=sql_fetch_array($result); $i++) {
										$total_pay = $row['spay'] + $row['cpay'];

									?>
									<tr>
										<td class="td_name"><span class="simptip-position-bottom simptip-movable half-arrow simptip-multiline simptip-black" data-tooltip="본　사 : <?php if($row['mb_1_name']) { echo $row['mb_1_name']. " / ".$row['mb_1_fee']; } ?>&#10;지　사 : <?php if($row['mb_2_name']) { echo $row['mb_2_name']. " / ".$row['mb_2_fee']; } ?>&#10;총　판 : <?php if($row['mb_3_name']) { echo $row['mb_3_name']. " / ".$row['mb_3_fee']; } ?>&#10;대리점 : <?php if($row['mb_4_name']) { echo $row['mb_4_name']. " / ".$row['mb_4_fee']; } ?>&#10;영업점 : <?php if($row['mb_5_name']) { echo $row['mb_5_name']. " / ".$row['mb_5_fee']; } ?>"><?php echo $row['mb_6_name']; ?></span></td>
										<td><?php echo $row['scnt']; ?> / <?php echo $row['ccnt']; ?></td>
										<td style="text-align:right;font-weight:bold;"><?php echo number_format($total_pay); ?></td>
										<td style="text-align:right"><?php echo number_format($row['spay']); ?></td>
										<td style="text-align:right"><?php echo number_format($row['cpay']); ?></td>
									</tr>
									<?php
										}
										if($i == 0) {
											echo '<tr><td colspan="5" style="height:100px; background:#eee; color:#888; line-height:100px;">결제 내역이 없습니다.</td></tr>';
										}
									?>
								</tbody>
							</table>
						</div>

						<?php
								}
							}
						?>

					</div>
				</div>

			</div>
		</div>
	</div>

	<div class="lt lt_even" style="float:left;width:32%;">
		<div class="bx-wrapper" style="max-width: 100%;">
			<div class="bx-viewport" style="width: 100%; overflow: hidden;">

				<div class="lt_slider" style="width: 100%%; position: relative; transition-duration: 0s;">
					<div class="lt_slider_li">
						<strong><a><?php echo $pn; ?>월 결제현황</a></strong>
						
						<div class="desc_toolcont">
							<?php
								$month = date("Y-m", mktime(0, 0, 0, intval(date('m'))-1, intval(date('d')), intval(date('Y'))  ));
								$sql = " select
											date(pay_datetime) as date,
											sum(if(dv_type = '2' and pay_type = 'Y', pay, null)) as sugis,
											sum(if(dv_type = '2' and pay_type = 'N', pay, null)) as sugic,
											sum(if(dv_type = '1' and pay_type = 'Y', pay, null)) as dans,
											sum(if(dv_type = '1' and pay_type = 'N', pay, null)) as danc
										from
											g5_payment
										WHERE
										".$adm_sql.$groups."
											and SUBSTRING(pay_datetime,1,7) = '$month'
										GROUP BY date order by datetime desc";
								$result = sql_query($sql);
							?>
		
							<table class="table_list td_pd">
								<thead>
									<tr>
										<th scope="col" style="width:5%;">날짜</th>
										<th scope="col">총승인</th>
										<th scope="col" style="width:18%;">수기</th>
										<th scope="col" style="width:18%;">수기취소</th>
										<th scope="col" style="width:18%;">단말기</th>
										<th scope="col" style="width:18%;">단말기취소</th>
									</tr>
								</thead>
								<tbody>
									<?php
										$sums = "";
										for ($i=0; $row=sql_fetch_array($result); $i++) {
											$sums = $row['sugis'] + $row['dans'] + $row['sugic'] + $row['danc'];
									?>
									<tr>
										<td style="text-align:center;"><?php echo substr($row['date'],8,2) ?>/<?php echo($yoil[date('w', strtotime($row['date']))]); ?></td>
										<td style="text-align:right;font-weight:bold;"><?php echo number_format($sums); ?></td>
										<td style="text-align:right"><?php echo number_format($row['sugis']); ?></td>
										<td style="text-align:right"><?php echo number_format($row['sugic']); ?></td>
										<td style="text-align:right"><?php echo number_format($row['dans']); ?></td>
										<td style="text-align:right"><?php echo number_format($row['danc']); ?></td>
									</tr>
									<?php
									}
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="lt lt_even" style="float:left;width:32%;">
		<div class="bx-wrapper" style="max-width: 100%;">
			<div class="bx-viewport" style="width: 100%; overflow: hidden;">

				<div class="lt_slider" style="width: 100%%; position: relative; transition-duration: 0s;">
					<div class="lt_slider_li">
						<strong><a>월별 결제현황</a></strong>
						
						<div class="desc_toolcont">

							<table class="table_list td_pd">
								<thead>
									<tr>
										<th scope="col" style="width:5%;">월</th>
										<th scope="col">총승인</th>
										<th scope="col" style="width:18%;">수기</th>
										<th scope="col" style="width:18%;">수기취소</th>
										<th scope="col" style="width:18%;">단말기</th>
										<th scope="col" style="width:18%;">단말기취소</th>
									</tr>
								</thead>
								<tbody>
									<?php
										if($member['mb_level'] == 10) {

											if(adm_sql_common) {
												$adm_sql = " mb_1 IN (".adm_sql_common.")";
											} else {
												$adm_sql = " (1)";
											}

										} else if($member['mb_level'] == 8) {
											$adm_sql = " mb_1 = '{$member['mb_id']}'";
										} else if($member['mb_level'] == 7) {
											$adm_sql = " mb_2 = '{$member['mb_id']}'";
										} else if($member['mb_level'] == 6) {
											$adm_sql = " mb_3 = '{$member['mb_id']}'";
										} else if($member['mb_level'] == 5) {
											$adm_sql = " mb_4 = '{$member['mb_id']}'";
										} else if($member['mb_level'] == 4) {
											$adm_sql = " mb_5 = '{$member['mb_id']}'";
										} else if($member['mb_level'] == 3) {
											$adm_sql = " mb_6 = '{$member['mb_id']}'";
										}


										$sql = " SELECT *, MONTH(`datetime`) AS `date`,
														sum(if(dv_type = '2' and pay_type = 'Y', pay, null)) as orderprices,
														sum(if(dv_type = '1' and pay_type = 'Y', pay, null)) as orderpriced,
														sum(if(pay_type = 'N', pay, null)) as cancelprice
													from g5_payment
													where ".$adm_sql."  GROUP BY date order by datetime desc";


										$sql = "SELECT DATE_FORMAT(`datetime`,'%Y-%m') m, 
												sum(if(dv_type = '2' and pay_type = 'Y', pay, null)) as sugis,
												sum(if(dv_type = '2' and pay_type = 'N', pay, null)) as sugic,
												sum(if(dv_type = '1' and pay_type = 'Y', pay, null)) as dans,
												sum(if(dv_type = '1' and pay_type = 'N', pay, null)) as danc
												FROM g5_payment
												where ".$adm_sql."   GROUP BY m order by m desc";

										$result = sql_query($sql);
										//echo $sql."<br><br>";
										for ($i = 0; $mons = sql_fetch_array($result); $i++) {
										$sums = $mons['sugis'] + $mons['dans'] + $mons['sugic'] + $mons['danc'];
										$mons['orders'] = (int)$mons['orderprices'];
										$mons['orderd'] = (int)$mons['orderpriced'];
										$mons['cancel'] = (int)$mons['cancelprice'];
										$month = substr($mons['m'],2,5);
									?>
									<tr>
										<td style="text-align:center;"><?php echo str_replace("-", "/", $month); ?></td>
										<td style="text-align:right;font-weight:bold;"><?php echo number_format($sums); ?></div>
										<td style="text-align:right"><?php echo number_format($mons['sugis']); ?></td>
										<td style="text-align:right"><?php echo number_format($mons['sugic']); ?></td>
										<td style="text-align:right"><?php echo number_format($mons['dans']); ?></td>
										<td style="text-align:right"><?php echo number_format($mons['danc']); ?></td>
									</tr>

									<?php
									}
									?>

								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>