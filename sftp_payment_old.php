<?php

	$title1 = "결제관리";
	$title2 = "실시간 결제내역";

	if(!$fr_datee) { $fr_datee = date("Ymd", strtotime('-1 day')); }
	if(!$to_datee) { $to_dates = date("Ymd", strtotime('-1 day')); }

	$fr_date = date("Y-m-d", strtotime($fr_date));
	$to_date = date("Y-m-d", strtotime($fr_date));

	$sql_common = " from g5_payment ";

	if($is_admin) {

		if(adm_sql_common) {
			$adm_sql = " mb_1 IN (".adm_sql_common.")";
		} else {
			$adm_sql = " sftp_mbrno != '' ";
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
		$sql_search = " where ".$adm_sql." and (pay_datetime BETWEEN '{$fr_date} 00:00:00' and '{$fr_date} 23:59:59') ";
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

	$sql = " select count(*) as cnt";
	$sql .= " {$sql_common} {$sql_search} {$sql_order} ";
//	echo $sql."<br><Br>";
//	$sql = " select count(*) as cnt, sum(pay) as total_pay {$sql_common} {$sql_search} {$sql_order} ";
	$row = sql_fetch($sql);


	$total_count = $row['cnt']; // 전체개수
	$page_count = "100";
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

	echo $sql."<br><br>";
//	echo $total_count;
?>

<div class="index_menu">
	<ul class="shortcut">
		<li class="sc_current"><a>실시간 결제내역</a></li>
		<li class="sc_visit">
			<aside id="visit">
				<ul>
					<li>수기승<span><?php echo number_format($row['sugis']); ?></span></li>
					<li>수기취<span><?php echo number_format($row['sugic']); ?></span></li>
					<li>단말기승<span><?php echo number_format($row['dans']); ?></span></li>
					<li>단말기취<span><?php echo number_format($row['danc']); ?></span></li>
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
							<button type="submit" class="btn_b btn_b02"><span>검색</span></button>
						</div>
					</div>
				</li>
				<li>
					<strong>생성</strong>
					<div>
						<button type="button" onclick="sftp_data('<?php echo $fr_date; ?>');" class="btn_b btn_b02"><span>차액정산 데이터파일 생성하기</span></button>
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
					<th>차액정산 MBR</th>
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
					<td><?php echo $row['sftp_mbrno']; ?></td>
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

