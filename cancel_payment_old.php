<?php

	$title1 = "결제관리";
	$title2 = "실시간 결제내역";

	if(!$fr_date) { $fr_date = date("Ymd"); }
	if(!$to_date) { $to_date = date("Ymd"); }

	$fr_dates = date("Y-m-d", strtotime($fr_date));
	$to_dates = date("Y-m-d", strtotime($to_date));

	if($is_admin) {

		if(adm_sql_common) {
			$adm_sql = " mb_1 IN (".adm_sql_common.")";
		} else {
			$adm_sql = " (1)";
		}

		$sql_common = " from g5_payment_cancel where ".$adm_sql;
	} else {
		$sql_common = " from g5_payment_cancel where mb_id = '{$member['mb_id']}'";
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
		$sql_order = " order by datetime desc ";

	$sql = " select count(*) as cnt {$sql_common} {$sql_search} order by ca_id desc ";
	$row = sql_fetch($sql);

	$total_count = $row['cnt']; // 전체개수
	$rows = $config['cf_page_rows'];
	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함

	$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
	$result = sql_query($sql);

//	echo $sql;
?>



<div class="index_menu">
	<ul class="shortcut">
		<li class="sc_current"><a>취소 신청내역</a></li>
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
					<strong>검색</strong>
					<div>
						<div data-skin="radio">
							<label><input type="radio" name="sfl" value="pay_num" <?php echo get_checked($sfl, "pay_num"); ?> checked> 승번</label>
							<label><input type="radio" name="sfl" value="mb_6_name" <?php echo get_checked($sfl, "mb_6_name"); ?>> 가맹</label>
							<label><input type="radio" name="sfl" value="pay" <?php echo get_checked($sfl, "pay"); ?>> 금액</label>
						</div>
					</div>
				</li>
				<li>
					<strong>검색</strong>
					<div>
						<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input" size="7" placeholder="검색어입력" style="width:150px;">
						<button type="submit" class="btn_b btn_b02"><span>검색</span></button>
					</div>
				</li>
			</ul>
		</div>
	</div>
</form>

<form action="./xlsx/xlsx.php" id="frm_xlsx" method="post">
<input type="hidden" name="xlsx_sql" value="<?php echo $xlsx_sql; ?>">
<input type="hidden" name="p" value="<?php echo $p; ?>">
</form>

<div class="m_board_scroll">
	<div class="m_table_wrap">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
				<tr>
					<th style="width:50px;">번호</th>
					<?php if($is_admin) { ?>
					<th>취소완료</th>
					<?php } ?>
					<th>상태</th>
					<th>입금</th>
					<th>신청자</th>
					<th>가맹점명</th>
					<th>승인일시</th>
					<th>승인금액</th>
					<th>승인번호</th>
					<th>메모</th>
					<th>신청일시</th>
					<th>관리</th>
				</tr>
			</thead>
			<tbody>
				<?php
					for ($i=0; $row=sql_fetch_array($result); $i++) {
					$num = number_format($total_count - ($page - 1) * $rows - $i);
					$mb = get_member($row['mb_id']);

					if($mb['mb_level'] == 10) {
						$stitle = "관리자";
					} else if($mb['mb_level'] == 8) {
						$stitle = "본사";
					} else if($mb['mb_level'] == 7) {
						$stitle = "지사";
					} else if($mb['mb_level'] == 6) {
						$stitle = "총판";
					} else if($mb['mb_level'] == 5) {
						$stitle = "대리점";
					} else if($mb['mb_level'] == 4) {
						$stitle = "영업점";
					} else if($mb['mb_level'] == 3) {
						$stitle = "가맹점";
					}

					if($row['ca_success'] == 1) {
						$ca_success = "<span style='color:blue; font-weight:bold; border:1px solid blue; padding:5px 10px'>취소완료</span>";
						$button_class1 = "btn_red";
					} else {
						$ca_success = "<span style='color:red; font-weight:bold; border:1px solid red; padding:5px 10px'>취소대기</span>";
						$button_class1 = "btn_blue";
					}

					if($row['ca_deposit'] == 1) {
						$ca_deposit = "<span style='color:blue; font-weight:bold; border:1px solid blue; padding:5px 10px'>입금완료</span>";
						$button_class2 = "btn_red";
					} else {
						$ca_deposit = "<span style='color:red; font-weight:bold; border:1px solid red; padding:5px 10px'>입금대기</span>";
						$button_class2 = "btn_blue";
					}
				?>
				<tr<?php echo $bgcolor?" style='background: $bgcolor;'":'';?>>
					<td><?php echo $num; ?></td>
					<?php if($is_admin) { ?>
					<td>
						<button  class="<?php echo $button_class1; ?>" onclick="cancel_payment('<?php echo $row['ca_id'];?>', 'success')" type="button">취소완료/대기</button>
					</td>
					<?php } ?>
					<td><?php echo $ca_success; ?></td>
					<td><?php echo $ca_deposit; ?></td>
					<td style="text-align:left; font-weight:bold;"><?php echo $mb['mb_nick']; ?> / <?php echo $stitle; ?></td>
					<td class="td_name"><?php echo $row['mb_6_name']; ?></td>
					<td><?php echo $row['pay_datetime']; ?></td>
					<td class="right"><?php echo number_format($row['pay']); ?></td>
					<td><?php echo $row['pay_num']; ?></td>
					<td><?php echo $row['ca_memo']; ?></td>
					<td><?php echo $row['datetime']; ?></td>
					<td style="min-width:145px;">
						<button  class="btn_reset" onclick="cancel_payment('<?php echo $row['pay_id'];?>', 'memo')" type="button">메모입력</button>
						<button  class="btn_black" onclick="cancel_payment('<?php echo $row['ca_id'];?>', 'delete')" type="button">삭제</button>
						<button  class="<?php echo $button_class2; ?>" onclick="cancel_payment('<?php echo $row['ca_id'];?>', 'deposit')" type="button">입금완료/대기</button>
					</td>
				</tr>
				<?php
					}
					if ($i == 0) {
						echo '<tr><td colspan="12" class="empty_table">자료가 없습니다.</td></tr>';
					}
				?>
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