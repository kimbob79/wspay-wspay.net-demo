<?php

	$title1 = "NOTI";
	$title2 = "루트업";

	$fr_dates = date("Y-m-d", strtotime($fr_date));
	$to_dates = date("Y-m-d", strtotime($to_date));

	$sql_common = " from g5_payment_routeup ";

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
		$sql_search .= " and appr_num = '{$pay_num}' ";
	}

	if($dv_tid) {
		$sql_search .= " and (tid = '{$dv_tid}') ";
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

	$sql = " select count(*) as cnt, sum(if(is_cancel = '0', amount, 0)) as total_Y_pay, sum(if(is_cancel = '1', amount, 0)) as total_M_pay {$sql_common} {$sql_search} {$sql_order} ";
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

<style>
.noti-header {
	background: linear-gradient(135deg, #1565c0 0%, #1976d2 100%);
	border-radius: 8px;
	padding: 12px 16px;
	margin-bottom: 10px;
	display: flex;
	justify-content: space-between;
	align-items: center;
	flex-wrap: wrap;
	gap: 10px;
}
.noti-header-title {
	color: #fff;
	font-size: 16px;
	font-weight: 600;
	display: flex;
	align-items: center;
	gap: 8px;
}
.noti-header-title i {
	font-size: 18px;
}
.noti-header-stats {
	display: flex;
	gap: 15px;
	flex-wrap: wrap;
}
.noti-header-stats .stat-item {
	background: rgba(255,255,255,0.15);
	border-radius: 6px;
	padding: 6px 12px;
	color: #fff;
	font-size: 13px;
}
.noti-header-stats .stat-item .stat-label {
	opacity: 0.9;
	margin-right: 6px;
}
.noti-header-stats .stat-item .stat-value {
	font-weight: 600;
}
.noti-header-stats .stat-item.total .stat-value {
	color: #ffeb3b;
}
@media (max-width: 768px) {
	.noti-header {
		flex-direction: column;
		align-items: flex-start;
	}
	.noti-header-stats {
		width: 100%;
		justify-content: flex-start;
	}
}
</style>
<div class="noti-header">
	<div class="noti-header-title">
		<i class="fa fa-credit-card"></i>
		<span>루트업 NOTI</span>
	</div>
	<div class="noti-header-stats">
		<div class="stat-item">
			<span class="stat-label">승인</span>
			<span class="stat-value"><?php echo number_format($total_Y_pay); ?></span>
		</div>
		<div class="stat-item">
			<span class="stat-label">취소</span>
			<span class="stat-value"><?php echo number_format($total_M_pay); ?></span>
		</div>
		<div class="stat-item total">
			<span class="stat-label">합계</span>
			<span class="stat-value"><?php echo number_format($total_pay); ?></span>
		</div>
	</div>
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
							<label><input type="radio" name="sfl" value="appr_num" <?php echo get_checked($sfl, "appr_num"); ?> checked> 승번</label>
							<label><input type="radio" name="sfl" value="mid" <?php echo get_checked($sfl, "mid"); ?>> 가맹점ID</label>
							<label><input type="radio" name="sfl" value="tid" <?php echo get_checked($sfl, "tid"); ?>> TID</label>
							<label><input type="radio" name="sfl" value="amount" <?php echo get_checked($sfl, "amount"); ?>> 금액</label>
							<label><input type="radio" name="sfl" value="issuer" <?php echo get_checked($sfl, "issuer"); ?>> 카드</label>
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
					<th>동기화</th>
					<th>등록</th>
					<th>가맹점ID</th>
					<th>단말기ID</th>
					<th>거래번호</th>
					<th>거래금액</th>
					<th>주문번호</th>
					<th>승인번호</th>
					<th>상품명</th>
					<th>구매자명</th>
					<th>구매자번호</th>
					<th>발급사명</th>
					<th>매입사명</th>
					<th>카드번호</th>
					<th>할부기간</th>
					<th>거래시간</th>
					<th>취소시간</th>
					<th>취소여부</th>
					<th>취소회차</th>
					<th>원거래번호</th>
					<th>모듈타입</th>
					<th>등록일</th>
				</tr>
				<tr>
					<th>sync</th>
					<th></th>
					<th>mid</th>
					<th>tid</th>
					<th>trx_id</th>
					<th>amount</th>
					<th>ord_num</th>
					<th>appr_num</th>
					<th>item_name</th>
					<th>buyer_name</th>
					<th>buyer_phone</th>
					<th>issuer</th>
					<th>acquirer</th>
					<th>card_num</th>
					<th>installment</th>
					<th>trx_dttm</th>
					<th>cxl_dttm</th>
					<th>is_cancel</th>
					<th>cxl_seq</th>
					<th>ori_trx_id</th>
					<th>module_type</th>
					<th>datetime</th>
				</tr>
			</thead>
			<tbody>
				<?php
					for ($i=0; $row=sql_fetch_array($result); $i++) {
					$num = number_format($total_count - ($page - 1) * $rows - $i);
					$sync_style = '';
					if($row['sync_status'] == 'failed') {
						$sync_style = 'background-color: #ffebee;';
					}

					// 모듈타입 표시
					$module_type_text = '';
					switch($row['module_type']) {
						case '0': $module_type_text = '장비'; break;
						case '1': $module_type_text = '수기'; break;
						case '2': $module_type_text = '인증'; break;
						case '3': $module_type_text = '간편'; break;
						case '4': $module_type_text = '빌링'; break;
						default: $module_type_text = $row['module_type']; break;
					}
				?>
				<tr style="<?php echo $sync_style; ?>">
					<td>
						<?php if($row['sync_status'] == 'failed') { ?>
							<span style="color: #d32f2f; font-weight: bold;" title="<?php echo htmlspecialchars($row['sync_message']); ?>">실패</span>
						<?php } else if($row['sync_status'] == 'success') { ?>
							<span style="color: #388e3c;">성공</span>
						<?php } else { ?>
							<span style="color: #757575;">대기</span>
						<?php } ?>
					</td>
					<td>
						<div class="buttons">
							<button class="btn_b btn_b02" onclick="update_routeup('<?php echo $row['pg_id']; ?>')" type="button">등록</button>
						</div>
					</td>
					<td><?php echo $row['mid']; ?></td>
					<td><?php echo $row['tid']; ?></td>
					<td><?php echo $row['trx_id']; ?></td>
					<td class="td_name" style="text-align:right"><?php echo number_format($row['amount']); ?></td>
					<td><?php echo $row['ord_num']; ?></td>
					<td><?php echo $row['appr_num']; ?></td>
					<td><?php echo $row['item_name']; ?></td>
					<td><?php echo $row['buyer_name']; ?></td>
					<td><?php echo $row['buyer_phone']; ?></td>
					<td><?php echo $row['issuer']; ?></td>
					<td><?php echo $row['acquirer']; ?></td>
					<td><?php echo $row['card_num']; ?></td>
					<td><?php echo $row['installment']; ?></td>
					<td><?php echo $row['trx_dttm']; ?></td>
					<td><?php echo $row['cxl_dttm']; ?></td>
					<td><?php echo $row['is_cancel'] == '1' ? '취소' : '승인'; ?></td>
					<td><?php echo $row['cxl_seq']; ?></td>
					<td><?php echo $row['ori_trx_id']; ?></td>
					<td><?php echo $module_type_text; ?></td>
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

