<?php

	$title1 = "NOTI";
	$title2 = "다우";

	$fr_dates = date("Y-m-d", strtotime($fr_date));
	$to_dates = date("Y-m-d", strtotime($to_date));

	$sql_common = " from g5_payment_daou ";

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

	$sql = " select count(*) as cnt, sum(if(cancelYN = 'N', amt, 0)) as total_Y_pay, sum(if(cancelYN = 'Y', amt, 0)) as total_M_pay {$sql_common} {$sql_search} {$sql_order} ";
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
		<li class="sc_current"><a>코페이 NOTI</a></li>
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
							<label><input type="radio" name="sfl" value="appNo" <?php echo get_checked($sfl, "appNo"); ?> checked> 승번</label>
							<label><input type="radio" name="sfl" value="catId" <?php echo get_checked($sfl, "catId"); ?>> TID</label>
							<label><input type="radio" name="sfl" value="amt" <?php echo get_checked($sfl, "amt"); ?>> 금액</label>
							<label><input type="radio" name="sfl" value="appCardCd" <?php echo get_checked($sfl, "appCardCd"); ?>> 카드</label>
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
				<?php /*
				<tr>
					<th>등록</th>
					<th>그룹 ID</th>
					<th>VAN ID</th>
					<th>상점 ID</th>
					<th>결제수단</th>
					<th>카드코드</th>
					<th>취소구분</th>
					<th>거래고유번호</th>
					<th>VAN거래고유번호</th>
					<th>승인일</th>
					<th>취소일</th>
					<th>금액</th>
					<th>잔액</th>
					<th>구매자 ID</th>
					<th>구매자명</th>
					<th>주문번호</th>
					<th>TID</th>
					<th>승인번호</th>
					<th>할부개월</th>
					<th>Noti 통보일</th>
					<th>카드번호</th>
					<th>단말기 CAT_ID</th>
					<th>phone 번호 입력 사항</th>
					<th>단말기/수기결제 구분</th>
					<th>등록일</th>
				</tr>
				*/ ?>
				<tr>
					<th></th>
					<th>gid</th>
					<th>wTid</th>
					<th>cardNm</th>
					<th>cancelYN</th>
					<th>tid</th>
					<th>ediNo</th>
					<th>appDtm</th>
					<th>ccDnt</th>
					<th>amt</th>
					<th>ordNm</th>
					<th>goodsName</th>
					<th>appNo</th>
					<th>quota</th>
					<th>cardNo</th>
					<th>catId</th>
					<th>tmnId</th>

					<th>datetime</th>
				</tr>
			</thead>
			<tbody>
				<?php
					for ($i=0; $row=sql_fetch_array($result); $i++) {
					$num = number_format($total_count - ($page - 1) * $rows - $i);
				?>
				<tr>
					<td>
						<div class="buttons">
							<button  class="btn_b btn_b02" onclick="update_daou('<?php echo $row['pg_id']; ?>')" type="button">등록</button>
						</div>
					</td>
					<td><?php echo $row['gid']; ?></td>
					<td><?php echo $row['wTid']; ?></td>
					<td><?php echo $row['cardNm']; ?></td>
					<td><?php echo $row['cancelYN']; ?></td>
					<td><?php echo $row['tid']; ?></td>
					<td><?php echo $row['ediNo']; ?></td>
					<td><?php echo $row['appDtm']; ?></td>
					<td><?php echo $row['ccDnt']; ?></td>
					<td><?php echo number_format($row['amt']); ?></td>
					<td><?php echo $row['ordNm']; ?></td>
					<td><?php echo $row['goodsName']; ?></td>
					<td><?php echo $row['appNo']; ?></td>
					<td><?php echo $row['quota']; ?></td>
					<td><?php echo $row['cardNo']; ?></td>
					<td><?php echo $row['catId']; ?></td>
					<td><?php echo $row['tmnId']; ?></td>

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

