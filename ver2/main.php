<?php
	include_once("./_common.php");

	$title1 = "대쉬보드";
	$title2 = "";

	include_once("./_head.php");


	if(!$fr_date) { $fr_date = date("Ymd"); }
	if(!$to_date) { $to_date = date("Ymd"); }


	$fr_dates = date("Y-m-d", strtotime($fr_date));
	$to_dates = date("Y-m-d", strtotime($to_date));

	$sql_common = " from g5_payment ";

	if($is_admin) {

		if($redpay == "Y") {
			if($member['mb_id'] == "admin") {
				$adm_sql = " mb_1 IN (".adm_sql_common.")";
			} else  {
				$adm_sql = " mb_1 NOT IN (".adm_sql_common.")";
			}
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

	if ($fr_date == "all" && $to_date == "all") {
		$sql_search = " where ".$adm_sql;
	} else {
		$sql_search = " where ".$adm_sql." and (pay_datetime BETWEEN '{$fr_dates} 00:00:00' and '{$to_dates} 23:59:59') ";
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
	$sql .= ", sum(if(pay_type = 'Y', pay, 0)) as total_Y_pay, sum(if(pay_type != 'Y', pay, 0)) as total_M_pay, count(if(pay_type = 'Y', 1, null)) as count_Y_pay, count(if(pay_type != 'Y', 1, null)) as count_M_pay {$sql_common} {$sql_search} {$sql_order} ";
	//echo $sql;
//	$sql = " select count(*) as cnt, sum(pay) as total_pay {$sql_common} {$sql_search} {$sql_order} ";
	$row = sql_fetch($sql);

	$total_count = $row['cnt']; // 전체개수
	$total_Y_pay  = $row['total_Y_pay']; // 승인합산
	$total_M_pay  = $row['total_M_pay']; // 취소합산
	$count_Y_pay  = $row['count_Y_pay']; // 승인건수
	$count_M_pay  = $row['count_M_pay']; // 취소건수
	$total_pay = $total_Y_pay - $total_M_pay; // 전체매출합산


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
	$result = sql_query($sql);

?>


			<div class="KDC_Content__root__tP6yD KDC_Content__responsive__4kCaB CWLNB">

				<?php /*
				<div class="KDC_AppInfo__root__ogv51 area_appinfo KDC_Content__header__kKRmJ no_active type_top">
					<div class="box_thumb">
						<img class="KDC_Image__root__z8jAm" src="https://k.kakaocdn.net/14/dn/btqvX1CL6kz/sSBw1mbWkyZTkk1Mpt9nw1/o.jpg" width="64" height="64" alt="app_icon">
					</div>
					<div class="box_typeinfo">
						<strong class="tit_typeinfo">지도<a href="/console/app"><button type="button" class="KDC_IconButton__root__sc-Qh btn_dropdown"><span class="KDC_Icon__root__GRlIs KDC_Icon__list__ov-TF mb-1"></span><span class="screen_out">애플리케이션 선택</span></button></a></strong>
						<div class="inbox_typeinfo">
							<span class="KDC_Badge__root__ZytDg item_info">ID 843580</span><span class="KDC_Badge__root__ZytDg item_info">OWNER</span><span class="KDC_Badge__root__ZytDg item_info item_type2">Web</span>
						</div>
					</div>
				</div>
				*/ ?>

				<div class="KDC_Row__root__uio5h KDC_Row__responsive__obNwV">
					<div class="KDC_Column__root__NK8XY KDC_Column__flex_1__UcocY">

						<style>

						.box_tool {
						}
						.list_tool {
							overflow:hidden
						}
						.list_tool li {
							float:left;
							width:572px;
							margin:0 8px 8px 0;
						}
						.list_tool .box_toolcont {
							position:relative;
							padding:10px 15px 10px;
							background-color:#fff;
							box-sizing:border-box;
							min-height:409px;
							border:1px solid hsla(0,0%,90%,.9);
						}
						.list_tool .tit_toolcont {
							display:block;
							font-weight:700;
							font-size:13px;
							line-height:32px;
							color:#111;
						}
						.list_tool .tit_toolcont .link_g {
							display:inline-block;
							vertical-align:top
						}
						.list_tool .desc_toolcont {
							padding-top:10px;
							font-size:13px;
							line-height:24px;
							color:#444;
							letter-spacing:-.2px
						}


						@media only screen and (max-width:828px) {
							.list_tool li {
								float:none;
								width:100%;
							}
							.list_tool .box_toolcont {
								padding:10px 0 0 0;
								border-bottom:0;
								min-height:100px;
							}
							.list_tool .tit_toolcont {
								font-size:13px;
								line-height:30px;
								padding-left:10px;
							}
							.list_tool .desc_toolcont {
								padding-top:10px;
								font-size:13px;
								line-height:23px
							}
							.list_tool .link_submain {
								margin-top:35px
							}
						}
						table th {font-size:11px;}
						</style>





						<div class="box_tool">
							<ul class="list_tool">
								<li>
									<div class="box_toolcont">
										<strong class="tit_toolcont">일별 결제현황</strong>
										<div class="desc_toolcont">

											<?php
												// 일자별 주문 합계 금액
												function get_order_date_sum($date, $mb_id, $mb_level)
												{

													if($mb_level == 10) {


														if($redpay == "Y") {
															if($mb_id == "admin") {
																$adm_sql = " and mb_1 IN (".adm_sql_common.")";
															} else  {
																$adm_sql = " and mb_1 NOT IN (".adm_sql_common.")";
															}
														}

													} else if($mb_level == 8) {
														$adm_sql = " and mb_1 = '{$mb_id}'";
													} else if($mb_level == 7) {
														$adm_sql = " and mb_2 = '{$mb_id}'";
													} else if($mb_level == 6) {
														$adm_sql = " and mb_3 = '{$mb_id}'";
													} else if($mb_level == 5) {
														$adm_sql = " and mb_4 = '{$mb_id}'";
													} else if($mb_level == 4) {
														$adm_sql = " and mb_5 = '{$mb_id}'";
													} else if($mb_level == 3) {
														$adm_sql = " and mb_6 = '{$mb_id}'";
													}

													global $g5;
													$sql = " select sum(if(dv_type = '2' and pay_type = 'Y', pay, null)) as orderprices,
																	sum(if(dv_type = '1' and pay_type = 'Y', pay, null)) as orderpriced,
																	sum(if(pay_type != 'Y', pay, null)) as cancelprice
																from g5_payment
																where SUBSTRING(pay_datetime, 1, 10) = '$date' ".$adm_sql;
													$row = sql_fetch($sql);
										
													if($member['mb_id'] == "admin") {
														echo $sql."<br>";
														echo $member['mb_1']."<br>";
													}

													$info = array();
													$info['orders'] = (int)$row['orderprices'];
													$info['orderd'] = (int)$row['orderpriced'];
													$info['cancel'] = (int)$row['cancelprice'];

													return $info;
												}

												$arr_order = array();
												$x_val = array();

												for($i=0; $i<7; $i++) {
													$date = date('Y-m-d', strtotime('-'.$i.' days', G5_SERVER_TIME));
													$x_val[] = $date;
													$arr_order[] = get_order_date_sum($date, $member['mb_id'], $member['mb_level']);
												}
											?>

											<table class="main_table">
												<thead>
													<tr>
														<th scope="col" style="width:14%;">날짜</th>
														<th scope="col" style="width:23%;">총승인</th>
														<th scope="col" style="width:23%;">수기</th>
														<th scope="col" style="width:20%;">단말기</th>
														<th scope="col" style="width:20%;">취소</th>
													</tr>
												</thead>
												<tbody>
													<?php
													for($i=0; $i<count($x_val); $i++) {
														$day = $x_val[$i];
														$yoil = array("일","월","화","수","목","금","토");
													?>
													<tr>
														<td style="text-align:center;line-height:14px"><?php echo date("m.d", strtotime($x_val[$i]))."<span style='font-size:11px;'>(".$yoil[date('w', strtotime($day))]; ?>)</span></td>
														<td style="text-align:right;font-weight:bold;"><?php echo number_format($arr_order[$i]['orders']+$arr_order[$i]['orderd']-$arr_order[$i]['cancel']); ?></div>
														<td style="text-align:right"><?php echo number_format($arr_order[$i]['orders']); ?></td>
														<td style="text-align:right"><?php echo number_format($arr_order[$i]['orderd']); ?></td>
														<td style="text-align:right"><?php echo number_format($arr_order[$i]['cancel']); ?></td>
													</tr>
													<?php
													}
													?>
												</tbody>
											</table>

										</div>
									</div>
								</li>

								<li>
									<div class="box_toolcont">
										<strong class="tit_toolcont">월별 결제현황</strong>
										<div class="desc_toolcont">
											<table class="main_table">
												<thead>
													<tr>
														<th scope="col" style="width:10%;">월</th>
														<th scope="col" style="width:25%;">총승인</th>
														<th scope="col" style="width:25%;">수기</th>
														<th scope="col" style="width:20%;">단말기</th>
														<th scope="col" style="width:20%;">취소</th>
													</tr>
												</thead>
												<tbody>
													<?php
														if($member['mb_level'] == 10) {

															if($redpay == "Y") {
																if($member['mb_id'] == "admin") {
																	$adm_sql = " mb_1 IN (".adm_sql_common.")";
																} else  {
																	$adm_sql = " mb_1 NOT IN (".adm_sql_common.")";
																}
															} else {
																$adm_sql = "(1)";
															}

														} else if($member['mb_level'] == 8) {
															$adm_sql = " and mb_1 = '{$member['mb_id']}'";
														} else if($member['mb_level'] == 7) {
															$adm_sql = " and mb_2 = '{$member['mb_id']}'";
														} else if($member['mb_level'] == 6) {
															$adm_sql = " and mb_3 = '{$member['mb_id']}'";
														} else if($member['mb_level'] == 5) {
															$adm_sql = " and mb_4 = '{$member['mb_id']}'";
														} else if($member['mb_level'] == 4) {
															$adm_sql = " and mb_5 = '{$member['mb_id']}'";
														} else if($member['mb_level'] == 3) {
															$adm_sql = " and mb_6 = '{$member['mb_id']}'";
														}

														$sql = " SELECT *, MONTH(`datetime`) AS `date`,
																		sum(if(dv_type = '2' and pay_type = 'Y', pay, null)) as orderprices,
																		sum(if(dv_type = '1' and pay_type = 'Y', pay, null)) as orderpriced,
																		sum(if(pay_type != 'Y', pay, null)) as cancelprice
																	from g5_payment
																	where ".$adm_sql."  GROUP BY date order by datetime desc";

														$result = sql_query($sql);
														for ($i = 0; $mons = sql_fetch_array($result); $i++) {
														$mons['orders'] = (int)$mons['orderprices'];
														$mons['orderd'] = (int)$mons['orderpriced'];
														$mons['cancel'] = (int)$mons['cancelprice'];
													?>
													<tr>
														<td style="text-align:center;"><?php echo substr($mons['datetime'],5,2); ?>월</td>
														<td style="text-align:right;font-weight:bold;"><?php echo number_format($mons['orders']+$mons['orderd']-$mons['cancel']); ?></div>
														<td style="text-align:right"><?php echo number_format($mons['orders']); ?></td>
														<td style="text-align:right"><?php echo number_format($mons['orderd']); ?></td>
														<td style="text-align:right"><?php echo number_format($mons['cancel']); ?></td>
													</tr>

													<?php
													}
													?>

												</tbody>
											</table>
										</div>
									</div>
								</li>
								<?php /*
								<li>
									<div class="box_toolcont">
										<strong class="tit_toolcont">최근 7개월 결제차트</strong>
										<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
										 <canvas id="myChart"></canvas>
									</div>
								</li>
								*/ ?>
							</ul>
						</div>



						<div class="box_tool">
							<ul class="list_tool">

								<?php
									if($is_admin) {
										$yoil = array("일","월","화","수","목","금","토");

										for($k=0; $k<6; $k++) {
											if($k==0) {
												$days = date('Y-m-d');
											} else if($k==1) {
												$days = date('Y-m-d', strtotime('-1 day'));
											} else if($k==2) {
												$days = date('Y-m-d', strtotime('-2 day'));
											} else if($k==3) {
												$days = date('Y-m-d', strtotime('-3 day'));
											} else if($k==4) {
												$days = date('Y-m-d', strtotime('-4 day'));
											} else if($k==5) {
												$days = date('Y-m-d', strtotime('-5 day'));
											} else if($k==6) {
												$days = date('Y-m-d', strtotime('-6 day'));
											}

											$sql_fild = " * ";
											$sql_fild .= ", count(if(pay_type = 'Y', pay_type, null)) as scnt "; // 승인 건수
											$sql_fild .= ", count(if(pay_type != 'Y', pay_type, null)) as ccnt "; // 취소 건수
											$sql_fild .= ", sum(if(pay_type = 'Y', pay, null)) as spay "; // 승인금액
											$sql_fild .= ", sum(if(pay_type != 'Y', pay, null)) as cpay "; // 취소금액
										//	$sql_fild .= ", sum(a.pay) as total_pay "; // 합계

											$sql_common = " from g5_payment ";

											$sql_search = " where ".$adm_sql." and mb_3 != '' and (pay_datetime BETWEEN '{$days} 00:00:00' and '{$days} 23:59:59') ";

											$sql = " select {$sql_fild} {$sql_common} {$sql_search} group by mb_3 having pay <> 0 ORDER BY spay desc limit 0, 7";
											$result = sql_query($sql);
										//	echo $member['mb_type']."<br>";
										//	echo $sql;
								?>

								<li>
									<div class="box_toolcont">
										<strong class="tit_toolcont"><?php echo $days;?> (<?php echo($yoil[date('w', strtotime($days))]);?>)</span> 총판별 실적</strong>
										<div class="desc_toolcont">


											<table class="main_table">
												<thead>
												<tr>
													<th style="width:10%;">순위</th>
													<th style="width:19%">지사명</th>
													<th style="width:11%">승/취</th>
													<th style="width:20%">승인금액</th>
													<th style="width:20%">취소금액</th>
													<th style="width:20%">총금액</th>
												</tr>
												</thead>
												<tbody>
												<?php
													for ($i=0; $row=sql_fetch_array($result); $i++) {
													$total_pay = $row['spay'] - $row['cpay'];

												?>
												<tr>
													<td style="text-align:center;"><?php echo $i+1; ?></td>
													<td style=" color:#4d4dff; font-weight:700"><?php echo $row['mb_3_name']; ?></td>
													<td style="text-align:center;"><?php echo $row['scnt']; ?> / <?php echo $row['ccnt']; ?></td>
													<td style="text-align:right;"><?php echo number_format($row['spay']); ?></td>
													<td style="text-align:right;"><?php echo number_format($row['cpay']); ?></td>
													<td style="text-align:right;"><?php echo number_format($total_pay); ?></td>
												</tr>
												<?php } ?>
												</tbody>
											</table>

										</div>
									</div>
								</li>


								<?php
										}
									}
								?>











							</ul>
						</div>


					</div>
				</div>
			</div>

<?php /*
<script>
  const labels = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
  ];

  const data = {
    labels: labels,
    datasets: [{
      label: 'My First dataset',
      backgroundColor: 'rgb(255, 99, 132)',
      borderColor: 'rgb(255, 99, 132)',
      data: [0, 10, 5, 2, 20, 30, 45],
    }]
  };

  const config = {
    type: 'line',
    data: data,
    options: {}
  };
</script>
<script>
  const myChart = new Chart(
    document.getElementById('myChart'),
    config
  );
</script>
*/ ?>
<?php
	include_once("./_tail.php");
?>