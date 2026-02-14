<?php

	$title1 = "결제관리";
	$title2 = "실시간 결제내역";

	if(!$fr_date) { $fr_date = date("Ymd"); }
	if(!$to_date) { $to_date = date("Ymd"); }

	$fr_dates = date("Y-m-d", strtotime($fr_date));
	$to_dates = date("Y-m-d", strtotime($to_date));

	$sql_common = " from g5_payment ";

	if($is_admin) {

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

<div class="index_menu">
	<ul class="shortcut">
		<li class="sc_current"><a>실시간 결제내역</a></li>
		<li class="sc_visit">
			<aside id="visit">
				<ul>
					<li>온라인<span><?php echo number_format($row['sugis']); ?></span></li>
					<li>온라인취소<span><?php echo number_format($row['sugic']); ?></span></li>
					<li>오프라인<span><?php echo number_format($row['dans']); ?></span></li>
					<li>오프라인취소<span><?php echo number_format($row['danc']); ?></span></li>
					<li>합계<span style="color:#ffff00;"><?php echo number_format($sums); ?></span></li>
					<?php if($expansion=="y") { ?>
					<li>가맹점합계<span style="color:#ffff00;"><?php echo number_format($row['total_6_pay']); ?></span></li>
					<?php } ?>
				</ul>
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
						<?php /*
						<button type="submit" onclick="javascript:set_date('이번주');" class="btn_b btn_b09"><span>이번주</span></button>
						*/ ?>
						<button type="submit" onclick="javascript:set_date('이번달');" class="btn_b btn_b09"><span>이번달</span></button>
						<button type="submit" onclick="javascript:set_date('지난주');" class="btn_b btn_b09"><span>지난주</span></button>
						<button type="submit" onclick="javascript:set_date('지난달');" class="btn_b btn_b09"><span>지난달</span></button>
					</div>
				</li>
				<li>
					<strong>옵션</strong>
					<div>
						<div data-skin="checkbox">
							<label><input type="checkbox" name="expansion" id="expansion" value="y" <?php echo get_checked($expansion, "y"); ?>> 정산금 확장</label>
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
						
						<?
						/*
						if($member['mb_level']==10){
						?>
						<button type="button" class="btn_b btn_b06" id="xlsx_raw" style="background: deepskyblue;color: black;"><span><i class="fa fa-file-excel-o" aria-hidden="true"></i> 엑셀출력(RAW)</span></button>
						
						<script>
						$("#xlsx_raw").click(function() {
							// Get values from date input fields
							var fr_date = $("#fr_date").val();
							var to_date = $("#to_date").val();
							
							// Create a form dynamically to submit as POST
							var form = $('<form>', {
								'action': 'xlsx_raw_download.php',
								'method': 'post',
								'target': '_blank'
							});
							
							// Add the date parameters to the form
							form.append($('<input>', {
								'type': 'hidden',
								'name': 'fr_date',
								'value': fr_date
							}));
							
							form.append($('<input>', {
								'type': 'hidden',
								'name': 'to_date',
								'value': to_date
							}));
							
							// Append the form to the body, submit it, and then remove it
							form.appendTo('body').submit().remove();
						});
						</script>


						<?
						}
						*/
						?>
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
					<th>본TID</th>
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

					$pp_limit_style = '';
					if($row['pp_limit_3m'] == 'Y' && $row['pp_limit_5m'] == 'Y') {
						$pp_limit_style = 'background: repeating-linear-gradient(135deg, gold, gold 10px, orange 10px, orange 20px);';
					} else if($row['pp_limit_5m'] == 'Y') {
						$pp_limit_style = 'background: orange;';
					} else if($row['pp_limit_3m'] == 'Y') {
						$pp_limit_style = 'background: gold;';
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
				<tr class='<?php echo $bgcolor; ?>' style='<?php echo $pp_limit_style; ?>'>
					<td class="center"><?php echo $num; ?></td>
					<td class="td_name"><?php if($is_admin) { ?><span class="simptip-position-bottom simptip-movable half-arrow simptip-multiline simptip-black" data-tooltip="본　사 : <?php if($row['mb_1_name']) { echo $row['mb_1_name']. " / ".$row['mb_1_fee']; } ?>&#10;지　사 : <?php if($row['mb_2_name']) { echo $row['mb_2_name']. " / ".$row['mb_2_fee']; } ?>&#10;총　판 : <?php if($row['mb_3_name']) { echo $row['mb_3_name']. " / ".$row['mb_3_fee']; } ?>&#10;대리점 : <?php if($row['mb_4_name']) { echo $row['mb_4_name']. " / ".$row['mb_4_fee']; } ?>&#10;영업점 : <?php if($row['mb_5_name']) { echo $row['mb_5_name']. " / ".$row['mb_5_fee']; } ?>"><?php } ?><?php echo $row['mb_6_name']; ?><?php if($is_admin) { ?></span><?php } ?></td>
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
							<button  class="btn_b btn_b05" onclick="recalculation('<?php echo $row['pay_id'];?>')" type="button"><?php echo $updatetime; ?></button>
							<button  class="btn_b btn_b06" onclick="noti('<?php echo $row['pay_id'];?>')" type="button">NOTI</button>
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
	$qstr .= "&expansion=".$expansion;
	$qstr .= "&sfl=".$sfl;
	$qstr .= "&stx=".$stx;
	echo get_paging_news(G5_IS_MOBILE ? "5" : "5", $page, $total_page, '?' . $qstr . '&amp;page=');
?>
