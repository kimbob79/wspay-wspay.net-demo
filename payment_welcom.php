<?php

	$title1 = "NOTI";
	$title2 = "웰컴";

/*
	$fr_dates = date("Y-m-d", strtotime($fr_date));
	$to_dates = date("Y-m-d", strtotime($to_date));

	$sql_common = " from g5_payment_welcom ";

	if($is_admin) {
		$adm_sql = " (1)";
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
		$sql_search = " where ".$adm_sql." and (datetime BETWEEN '{$fr_dates} 00:00:00' and '{$to_dates} 23:59:59') ";
	}

	if($pay_num) {
		$sql_search .= " and authCd = '{$pay_num}' ";
	}

	if($dv_tid) {
		$sql_search .= " and (dv_tid = '{$dv_tid}') ";
	}

	if($company_name) {
		$sql_search .= " and (mb_name7 = '{$company_name}') ";
	}

	if($gname) { $sql_search .= " and level_company_name like '%{$gname}%' "; }


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

	$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
	$row = sql_fetch($sql);

	$total_count = $row['cnt']; // 전체개수

	$rows = $config['cf_page_rows'];

	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함

	$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
	$result = sql_query($sql);
*/





	$fr_dates = date("Y-m-d", strtotime($fr_date));
	$to_dates = date("Y-m-d", strtotime($to_date));

	$sql_common = " from g5_payment_welcom ";

	if($is_admin) {
		$adm_sql = " (1)";
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
		$sql_search = " where ".$adm_sql." and (datetime BETWEEN '{$fr_dates} 00:00:00' and '{$to_dates} 23:59:59') ";
	}

	if($pay_num) {
		$sql_search .= " and authCd = '{$pay_num}' ";
	}

	if($dv_tid) {
		$sql_search .= " and (dv_tid = '{$dv_tid}') ";
	}

	if($company_name) {
		$sql_search .= " and (mb_name7 = '{$company_name}') ";
	}

	if($gname) { $sql_search .= " and level_company_name like '%{$gname}%' "; }
	/*
	if ($is_admin != 'super')
		$sql_search .= " and (gr_admin = '{$member['mb_id']}') ";
	*/

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

	$sql = " select count(*) as cnt, sum(if(cancel_ymdhms = '', amount, 0)) as total_Y_pay, sum(if(cancel_ymdhms != '', amount, 0)) as total_M_pay {$sql_common} {$sql_search} {$sql_order} ";
	$row = sql_fetch($sql);

	$total_count = $row['cnt']; // 전체개수
	$total_Y_pay  = $row['total_Y_pay']; // 승인합산
	$total_M_pay  = $row['total_M_pay']; // 취소합산
	$total_pay = $total_Y_pay + $total_M_pay; // 전체매출합산

	$rows = $config['cf_page_rows'];

	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함

	$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
	$result = sql_query($sql);







?>



<div class="index_menu">
	<ul class="shortcut">
		<li class="sc_current"><a>웰컴 NOTI</a></li>
		<li class="sc_visit">
			<aside id="visit">
				<ul>
					<li>승인<span><?php echo number_format($total_Y_pay); ?></span></li>
					<li>취소<span><?php echo number_format($total_M_pay); ?></span></li>
					<li>합계<span style="color:#ffff00;"><?php echo number_format($total_pay); ?></span></li>
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
						<button type="submit" onclick="javascript:set_date('오늘');" class="btn_b btn_b09"><span>오늘</span></button>
						<button type="submit" onclick="javascript:set_date('어제');" class="btn_b btn_b09"><span>어제</span></button>
						<button type="submit" onclick="javascript:set_date('이번주');" class="btn_b btn_b09"><span>이번주</span></button>
						<button type="submit" onclick="javascript:set_date('이번달');" class="btn_b btn_b09"><span>이번달</span></button>
						<button type="submit" onclick="javascript:set_date('지난주');" class="btn_b btn_b09"><span>지난주</span></button>
						<button type="submit" onclick="javascript:set_date('지난달');" class="btn_b btn_b09"><span>지난달</span></button>
					</div>
				</li>
				<li>
					<strong>검색</strong>
					<div>
						<div data-skin="radio">
							<label><input type="radio" name="sfl" value="approval_no" <?php echo get_checked($sfl, "approval_no"); ?> checked> 승번</label>
							<label><input type="radio" name="sfl" value="mid" <?php echo get_checked($sfl, "mid"); ?>> MID</label>
							<label><input type="radio" name="sfl" value="product_code" <?php echo get_checked($sfl, "product_code"); ?>> TID</label>
							<label><input type="radio" name="sfl" value="amount" <?php echo get_checked($sfl, "amount"); ?>> 금액</label>
							<label><input type="radio" name="sfl" value="bank_name" <?php echo get_checked($sfl, "bank_name"); ?>> 카드</label>
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


<div class="m_board_scroll">
	<div class="m_table_wrap">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
				<tr>
					<th>번호</th>
					<th>상점ID</th>
					<th>결제구분</th>
					<th>카드/은행 코드</th>
					<th>거래부호</th>
					<th>주문번호</th>
					<th>거래번호</th>
					<th>승인일시</th>
					<th>취소일시</th>
					<th>승인금액</th>
					<th>잔액</th>
					<th>회원ID</th>
					<th>구매자명</th>
					<th>상품코드</th>
					<th>상품명</th>
					<th>승인번호</th>
					<th>할부개월</th>
					<th>계좌번호</th>
					<th>가상계좌 입금일시</th>
					<th>가상계좌 입금금액</th>
					<th>입금자명</th>
					<th>현금영수증 일련번호</th>
					<th>현금영수증 승인번호</th>
					<th>카드/은행 이름</th>
					<th>NOTI 통보 밀리초</th>
					<th>해쉬 값</th>
					<th>상위PG 거래 고유번호</th>
					<th>상위PG 취소거래 고유번호</th>
					<th>에코</th>
					<th>등록일</th>
				</tr>
				<tr>
					<th></th>
					<th>mid</th>
					<th>pay_type</th>
					<th>bank_code</th>
					<th>transaction_flag</th>
					<th>order_no</th>
					<th>transaction_no</th>
					<th>approval_ymdhms</th>
					<th>cancel_ymdhms</th>
					<th>amount</th>
					<th>remain_amount</th>
					<th>user_id</th>
					<th>user_name</th>
					<th>product_code</th>
					<th>product_name</th>
					<th>approval_no</th>
					<th>card_sell_mm</th>
					<th>account_no</th>
					<th>deposit_ymdhms</th>
					<th>deposit_amount</th>
					<th>deposit_name</th>
					<th>cash_seq</th>
					<th>cash_approval_no</th>
					<th>bank_name</th>
					<th>millis</th>
					<th>hash_value</th>
					<th>org_pg_seq_no</th>
					<th>org_pg_cancel_seq_no</th>
					<th>echo</th>
					<th>datetime</th>
				</tr>
			</thead>
			<tbody>
				<?php
					for ($i=0; $row=sql_fetch_array($result); $i++) {

					$bgcolor = '';
					$num = number_format($total_count - ($page - 1) * $rows - $i);

				?>
				<tr<?=$bgcolor?" style='background: $bgcolor;'":'';?>>
					<td>
						<div class="buttons">
							<button  class="btn_b btn_b02" onclick="update_welcom('<?php echo $row['pg_id']; ?>')" type="button">등록</button>
						</div>
					</td>
					<td><?php echo $row['mid']; ?></td>
					<td><?php echo $row['pay_type']; ?></td>
					<td><?php echo $row['bank_code']; ?></td>
					<td><?php echo $row['transaction_flag']; ?></td>
					<td><?php echo $row['order_no']; ?></td>
					<td><?php echo $row['transaction_no']; ?></td>
					<td><?php echo $row['approval_ymdhms']; ?></td>
					<td><?php echo $row['cancel_ymdhms']; ?></td>
					<td><?php echo $row['amount']; ?></td>
					<td><?php echo $row['remain_amount']; ?></td>
					<td><?php echo $row['user_id']; ?></td>
					<td><?php echo $row['user_name']; ?></td>
					<td><?php echo $row['product_code']; ?></td>
					<td><?php echo $row['product_name']; ?></td>
					<td><?php echo $row['approval_no']; ?></td>
					<td><?php echo $row['card_sell_mm']; ?></td>
					<td><?php echo $row['account_no']; ?></td>
					<td><?php echo $row['deposit_ymdhms']; ?></td>
					<td><?php echo $row['deposit_amount']; ?></td>
					<td><?php echo $row['deposit_name']; ?></td>
					<td><?php echo $row['cash_seq']; ?></td>
					<td><?php echo $row['cash_approval_no']; ?></td>
					<td><?php echo $row['bank_name']; ?></td>
					<td><?php echo $row['millis']; ?></td>
					<td><?php echo $row['hash_value']; ?></td>
					<td><?php echo $row['org_pg_seq_no']; ?></td>
					<td><?php echo $row['org_pg_cancel_seq_no']; ?></td>
					<td><?php echo $row['echo']; ?></td>
					<td><?php echo $row['datetime']; ?></td>
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

