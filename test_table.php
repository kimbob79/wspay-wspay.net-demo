<?php

	$title1 = "결제관리";
	$title2 = "실시간 결제내역";

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
		$sql_search = " where ".$adm_sql." and mb_6_name != '' ";
	} else {
		$sql_search = " where ".$adm_sql." and (pay_datetime BETWEEN '{$fr_dates} 00:00:00' and '{$to_dates} 23:59:59')  and mb_6_name != '' ";
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
//	echo $sql;
//	$sql = " select count(*) as cnt, sum(pay) as total_pay {$sql_common} {$sql_search} {$sql_order} ";
	$row = sql_fetch($sql);

	$total_count = $row['cnt']; // 전체개수
	$total_Y_pay  = $row['total_Y_pay']; // 승인합산
	$total_M_pay  = $row['total_M_pay']; // 취소합산
	$count_Y_pay  = $row['count_Y_pay']; // 승인건수
	$count_M_pay  = $row['count_M_pay']; // 취소건수
	$total_pay = $total_Y_pay + $total_M_pay; // 전체매출합산
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
	$result = sql_query($sql);
?>


<?php /*

			<li><button type="submit" onclick="javascript:set_date('오늘');"><span>오늘</span></button></li>
			<li><button type="submit" onclick="javascript:set_date('어제');"><span>어제</span></button></li>
			<li><button type="submit" onclick="javascript:set_date('이번주');"><span>이번주</span></button></li>
			<li><button type="submit" onclick="javascript:set_date('이번달');"><span>이번달</span></button></li>
			<li><button type="submit" onclick="javascript:set_date('지난주');"><span>지난주</span></button></li>
			<li><button type="submit" onclick="javascript:set_date('지난달');"><span>지난달</span></button></li>
			<li><button type="submit" onclick="javascript:set_date('전체');"><span>전체</span></button></li>
			<li><a href="./payment.xls.php?&fr_date=<?php echo $fr_date; ?>&to_date=<?php echo $to_date; ?>&expansion=<?php echo $expansion; ?>&sfl=<?php echo $sfl; ?>&stx=<?php echo $stx; ?>"><span><i class="fa fa-file-excel-o" aria-hidden="true"></i> 엑셀출력</span></a></li>

*/ ?>

<div class="index_menu">
	<ul class="shortcut">
		<li class="sc_current"><a>통계</a></li>
		<li class="sc_visit">
			<aside id="visit">
				<ul>
					<li>매출<span><?php echo number_format($total_pay); ?></span></li>
					<li>&nbsp;</li>
					<li>승인<span><?php echo number_format($total_Y_pay); ?></span></li>
					<li>취소<span><?php echo number_format($total_M_pay); ?></span></li>
				</ul>
			</aside>
		</li>
	</ul>
</div>

<?php /*
<div class="searchs">
	<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
		<input type="hidden" name="p" value="<?php echo $p; ?>">
		
		<div class="sch_last">
			<div style="float:left; margin:0 5px 5px 0;">
				<input type="text" name="fr_date" value="<?php echo $fr_date ?>" id="fr_date" class="frm_input" size="6" maxlength="10">
				<input type="text" name="to_date" value="<?php echo $to_date ?>" id="to_date" class="frm_input" size="6" maxlength="10">
				<?php if($is_admin) { ?>
				<select name="expansion" id="expansion">
					<option value="" >축소</option>
					<option value="y" <?php if($expansion == "y") { echo "selected"; } ?>>확장</option>
				</select>
				<?php } ?>
			</div>
			<div style="float:left; margin:0 5px 5px 0;">
				<select name="sfl" id="sfl">
					<option value="pay_num" <?php echo get_selected($sfl, "pay_num"); ?>>승인번호</option>
					<option value="mb_6_name" <?php echo get_selected($sfl, "mb_6_name"); ?>>가맹점명</option>
					<option value="dv_tid" <?php echo get_selected($sfl, "dv_tid"); ?>>TID</option>
					<option value="pay" <?php echo get_selected($sfl, "pay"); ?>>승인금액</option>
					<option value="pay_card_name" <?php echo get_selected($sfl, "pay_card_name"); ?>>카드사</option>
					<option value="pay_parti" <?php echo get_selected($sfl, "pay_parti"); ?>>할부</option>
				</select>

				<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input" size="7" placeholder="검색어입력">
				<button type="submit" class="btn_black"><span>검색</span></button>
			</div>

			<ul class="anchor" style="float:left">
				<li><button type="submit" onclick="javascript:set_date('오늘');" class="btn_black_line"><span>오늘</span></button></li>
				<li><button type="submit" onclick="javascript:set_date('어제');" class="btn_black_line"><span>어제</span></button></li>
				<li><button type="submit" onclick="javascript:set_date('이번주');" class="btn_black_line"><span>이번주</span></button></li>
				<li><button type="submit" onclick="javascript:set_date('이번달');" class="btn_black_line"><span>이번달</span></button></li>
				<li><button type="submit" onclick="javascript:set_date('지난주');" class="btn_black_line"><span>지난주</span></button></li>
				<li><button type="submit" onclick="javascript:set_date('지난달');" class="btn_black_line"><span>지난달</span></button></li>
				<li><button type="submit" onclick="javascript:set_date('전체');" class="btn_black_line"><span>전체</span></button></li>
				<li><a href="./payment.xls.php?&fr_date=<?php echo $fr_date; ?>&to_date=<?php echo $to_date; ?>&expansion=<?php echo $expansion; ?>&sfl=<?php echo $sfl; ?>&stx=<?php echo $stx; ?>"  class="btn_black_line"><span>엑셀</span></a></li>
			</ul>
		</div>
</div>
*/ ?>

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
						</div>
						<span>~</span>
						<div>
							<input type="text" name="to_date" value="<?php echo $to_date ?>" id="to_date" class="frm_input" size="6" maxlength="10">
						</div>
					</div>
				</li>
				<li>
					<strong>단축</strong>
					<div>
						<button type="submit" onclick="javascript:set_date('오늘');" class="btn_black_line"><span>오늘</span></button>
						<button type="submit" onclick="javascript:set_date('어제');" class="btn_black_line"><span>어제</span></button>
						<button type="submit" onclick="javascript:set_date('이번주');" class="btn_black_line"><span>이번주</span></button>
						<button type="submit" onclick="javascript:set_date('이번달');" class="btn_black_line"><span>이번달</span></button>
						<button type="submit" onclick="javascript:set_date('지난주');" class="btn_black_line"><span>지난주</span></button>
						<button type="submit" onclick="javascript:set_date('지난달');" class="btn_black_line"><span>지난달</span></button>
					</div>
				</li>
				<?php if($is_admin) { ?>
				<li>
					<strong>옵션</strong>
					<div>
						<div data-skin="checkbox">
							<label><input type="checkbox" name="expansion" id="expansion" value="y" <?php echo get_checked($expansion, "y"); ?>> 정산금 확장</label>
						</div>
					</div>
				</li>
				<?php } ?>
				<li>
					<strong>검색</strong>
					<div>
						<div data-skin="radio">
							<label><input type="radio" name="sfl" value="pay_num" <?php echo get_checked($sfl, "pay_num"); ?> checked> 승번</label>
							<label><input type="radio" name="sfl" value="mb_6_name" <?php echo get_checked($sfl, "mb_6_name"); ?>> 가맹</label>
							<label><input type="radio" name="sfl" value="dv_tid" <?php echo get_checked($sfl, "dv_tid"); ?>> TID</label>
							<label><input type="radio" name="sfl" value="pay" <?php echo get_checked($sfl, "pay"); ?>> 금액</label>
							<label><input type="radio" name="sfl" value="pay_card_name" <?php echo get_checked($sfl, "pay_card_name"); ?>> 카드</label>
						</div>
					</div>
				</li>
				<li>
					<strong>검색</strong>
					<div>
						<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input" size="7" placeholder="검색어입력" style="width:150px;">
						<button type="submit" class="btn_black"><span>검색</span></button>
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
					<th style="width:50px;">번호</th>
					<th>가맹점명</th>
					<th>승인일시</th>
					<th>승인금액</th>
					<?php if($is_admin) { ?>
					<?php if($expansion == "y") { ?>
					<th>본사</th>
					<th>지사</th>
					<th>총판</th>
					<th>대리점</th>
					<th>영업점</th>
					<th>가맹점</th>
					<?php } } ?>

					<th>할부</th>
					<th>카드사</th>
					<th>영수증</th>
					<th>승인번호</th>
					<th>TID</th>
					<th>구분</th>
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
						$bgcolor = '#ffe4e9';
					} else if($row['pay_type'] == "Y") {
						$pay_type = "승인";
					} else if($row['pay_type'] == "N") {
						$pay_type = "취소";
						$bgcolor = 'pink';
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
					} else {
						$pg_name = "웰컴";
					}
				?>
				<tr<?php echo $bgcolor?" style='background: $bgcolor;'":'';?>>
					<td class="center"><?php echo $num; ?></td>
					<td class="td_name"><?php echo $row['mb_6_name']; ?></td>
					<td class="center"><?php echo $row['pay_datetime']; ?></td>
					<td class="right"><?php echo number_format($row['pay']); ?><?php /* if($row['pay_cdatetime'] > 0) { echo "<del>"; }  echo number_format($row['pay']); if($row['pay_cdatetime'] > 0) { echo "</del>"; } */?></td>
					<?php
						if($is_admin) {
							if($expansion == "y") {
					?>
					<td class="left"><?php if($row['mb_1_fee'] > 0) { echo "<div style='float:left;background:#333; color:#fff; font-size:11px; font-weight:100; padding:0 3px;' title='".$row['mb_1_name']."'>".$row['mb_1_fee']."%</div><div style='float:right'>".number_format($row['mb_1_pay'])."</div>"; } ?></td>
					<td class="left"><?php if($row['mb_2_fee'] > 0) { echo "<div style='float:left;background:#333; color:#fff; font-size:11px; font-weight:100; padding:0 3px;' title='".$row['mb_2_name']."'>".$row['mb_2_fee']."%</div><div style='float:right'>".number_format($row['mb_2_pay'])."</div>"; } ?></td>
					<td class="left"><?php if($row['mb_3_fee'] > 0) { echo "<div style='float:left;background:#333; color:#fff; font-size:11px; font-weight:100; padding:0 3px;' title='".$row['mb_3_name']."'>".$row['mb_3_fee']."%</div><div style='float:right'>".number_format($row['mb_3_pay'])."</div>"; } ?></td>
					<td class="left"><?php if($row['mb_4_fee'] > 0) { echo "<div style='float:left;background:#333; color:#fff; font-size:11px; font-weight:100; padding:0 3px;' title='".$row['mb_4_name']."'>".$row['mb_4_fee']."%</div><div style='float:right'>".number_format($row['mb_4_pay'])."</div>"; } ?></td>
					<td class="left"><?php if($row['mb_5_fee'] > 0) { echo "<div style='float:left;background:#333; color:#fff; font-size:11px; font-weight:100; padding:0 3px;' title='".$row['mb_5_name']."'>".$row['mb_5_fee']."%</div><div style='float:right'>".number_format($row['mb_5_pay'])."</div>"; } ?></td>
					<td class="left"><?php if($row['mb_6_fee'] > 0) { echo "<div style='float:left;background:#333; color:#fff; font-size:11px; font-weight:100; padding:0 3px;' title='".$row['mb_6_name']."'>".$row['mb_6_fee']."%</div><div style='float:right'>".number_format($row['mb_6_pay'])."</div>"; } ?></td>
					<?php
							}
						}
					?>

					<td style="text-align:center;"><?php echo $pay_parti; ?></td>
					<td style="text-align:center;"><?php echo mb_substr($pay_card_name,0,2); ?></td>
					<td style="text-align:center; min-width:145px;">
						<div class="buttons">
							<button  class="btn_receipt" onclick="receiptPopup('<?php echo $row['trxid'];?>', '<?php echo $row['pay_num'];?>')" type="button">영수증</button>
							<?php
								if($is_admin) {
									if($row['updatetime'] > 0) {
										$updatetime = "<span style='font-size:11px;'>".substr($row['updatetime'],5,11)."</span>";
									} else {
										$updatetime = "재정산";
									}
							?>
							<button  class="btn_reset" onclick="recalculation('<?php echo $row['pay_id'];?>')" type="button"><?php echo $updatetime; ?></button>
							<?php } ?>
						</div>
					</td>
					<td style="text-align:center;"><?php echo $row['pay_num']; ?></td>
					<td style="text-align:center;"><?php echo $row['dv_tid']; ?></td>
					<td style="text-align:center;"><?php echo $pay_type; ?></td>
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
	$qstr .= "&sfl=".$sfl;
	$qstr .= "&stx=".$stx;
	echo get_paging_news(G5_IS_MOBILE ? "5" : "5", $page, $total_page, '?' . $qstr . '&amp;page=');
?>

