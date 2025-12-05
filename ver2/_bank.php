<?php

	include_once("./_common.php");

	$title1 = "결제관리";
	$title2 = "실시간 결제내역";

	include_once("./_head.php");

	if(!$fr_date) { $fr_date = date("Ymd"); }
	if(!$to_date) { $to_date = date("Ymd"); }

	$fr_dates = date("Y-m-d", strtotime($fr_date));
	$to_dates = date("Y-m-d", strtotime($to_date));

	$sql_common = " from g5_payment ";

	if($is_admin) {

		if($redpay == "Y") {
			if($member['mb_id'] == "admin") {
				$adm_sql = " mb_1 IN (".$adm_sql_common.")";
			} else  {
				$adm_sql = " mb_1 NOT IN (".$adm_sql_common.")";
			}
		} else {
			$adm_sql = " where (1)";
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

	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함

	$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
	$result = sql_query($sql);



?>


<div class="KDC_Content__root__tP6yD KDC_Content__responsive__4kCaB CWLNB">
	<div class="KDC_Tabs__root__JmfCY PeriodButtonGroup_root__UE4Kj">
		<div class="KDC_Tab__root__h2hVQ tab tab_selected type_other">총결제</div>
		<div class="KDC_Tab__root__h2hVQ tab type_other  mobilecl"><?php echo number_format($total_pay) ?>원</div>
		<div class="KDC_Tab__root__h2hVQ tab tab_selected type_other search_br">승인</div>
		<div class="KDC_Tab__root__h2hVQ tab type_other  mobilecl"><?php echo number_format($total_Y_pay) ?>원</div>
		<div class="KDC_Tab__root__h2hVQ tab tab_selected type_other search_br">취소</div>
		<div class="KDC_Tab__root__h2hVQ tab type_other  mobilecl"><?php echo number_format($total_M_pay) ?>원</div>
	</div>
	<div class="KDC_Tabs__root__JmfCY PeriodButtonGroup_root__UE4Kj">
		<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
			<input type="hidden" name="p" value="<?php echo $p; ?>">
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date" style="width:80px;">
				<select name="sfl" id="sfl" style="border:0;background:#fff;width:90%;">
					<option value="pay_num" <?php echo get_selected($sfl, "pay_num"); ?>>승인번호</option>
					<option value="mb_6_name" <?php echo get_selected($sfl, "mb_6_name"); ?>>가맹점명</option>
					<option value="dv_tid" <?php echo get_selected($sfl, "dv_tid"); ?>>TID</option>
					<option value="pay" <?php echo get_selected($sfl, "pay"); ?>>승인금액</option>
					<option value="pay_card_name" <?php echo get_selected($sfl, "pay_card_name"); ?>>카드사</option>
					<option value="pay_parti" <?php echo get_selected($sfl, "pay_parti"); ?>>할부</option>
				</select>
			</div>
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date">
				<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="KDC_Input__root__3M8Hf KDC_Input__root__3M8Hf_date" style="width:100px;" placeholder="검색어">
			</div>


			<div class="KDC_Tab__root__h2hVQ tab type_other input_date search_br">
				<input type="text" name="fr_date" value="<?php echo $fr_date ?>" id="fr_date" class="KDC_Input__root__3M8Hf KDC_Input__root__3M8Hf_date" style="width:80px;" maxlength="10">
			</div>
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date">
				<input type="text" name="to_date" value="<?php echo $to_date ?>" id="to_date" class="KDC_Input__root__3M8Hf KDC_Input__root__3M8Hf_date" style="width:80px;" maxlength="10">
			</div>


			<div class="KDC_Tab__root__h2hVQ tab type_other  search_br"><div class="KDC_Tab__text__VzW9X" onclick="set_date('오늘');">오늘</div></div>
			<div class="KDC_Tab__root__h2hVQ tab type_other"><div class="KDC_Tab__text__VzW9X" onclick="set_date('어제');">어제</div></div>
			<div class="KDC_Tab__root__h2hVQ tab type_other"><div class="KDC_Tab__text__VzW9X" onclick="set_date('이번주');">이번주</div></div>
			<div class="KDC_Tab__root__h2hVQ tab type_other"><div class="KDC_Tab__text__VzW9X" onclick="set_date('이번달');">이번달</div></div>
			<div class="KDC_Tab__root__h2hVQ tab type_other"><div class="KDC_Tab__text__VzW9X" onclick="set_date('지난주');">지난주</div></div>
			<div class="KDC_Tab__root__h2hVQ tab type_other"><div class="KDC_Tab__text__VzW9X" onclick="set_date('지난달');">지난달</div></div>
			<div class="KDC_Tab__root__h2hVQ tab tab_selected type_other"><input type="submit" class="KDC_Tab__text__VzW9X" value="검색" style="background:#444; width:100%; border:0; color:#fff;"></div>
		</form>
	</div>



	<div class="KDC_Row__root__uio5h KDC_Row__responsive__obNwV">
		<div class="KDC_Column__root__NK8XY KDC_Column__flex_1__UcocY">
			<div class="KDC_Section__root__VXHOv">
				<table class="KDC_Table__root__Jim4z">
				<thead>
				<tr>
					<th style="width:50px;">NO</th>
					<th>가맹점명</th>
					<th>승인일시</th>
					<th>승인금액</th>
					<th>할부</th>
					<th>카드사</th>
					<th>영수증</th>
					<th>승인번호</th>
					<th>TID</th>
					<th>구분</th>
					<th>거래번호</th>
					<th>주문번호</th>
				</tr>
				</thead>
				<tbody>


				<?php
					for ($i=0; $row=sql_fetch_array($result); $i++) {

					$bgcolor = '';
					$num = number_format($total_count - ($page - 1) * $rows - $i);

					if($row['pay_type'] == "Y" && $row['pay_cdatetime'] > '0000-00-00 00:00:00') {
						$pay_type = "취소";
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

				?>
				<tr<?php echo $bgcolor?" style='background: $bgcolor;'":'';?>>
					<td><?php echo $num; ?></td>
					<td class="leftb"><?php echo $row['mb_6_name']; ?></td>
					<td><?php echo $row['pay_datetime']; ?></td>
					<td><?php if($row['pay_cdatetime'] > 0) { echo "<del>"; } ?><?php echo number_format($row['pay']); ?><?php if($row['pay_cdatetime'] > 0) { echo "</del>"; } ?></td>
					<td><?php echo $pay_parti; ?></td>
					<td><?php echo mb_substr($pay_card_name,0,2); ?></td>
					<td>
						<div class="buttons">
							<button  class="KDC_Button__root__N26ep KDC_Button__mini__Sy8ka KDC_Button__color_cancel__TdcOV" onclick="receiptPopup('<?php echo $row['trxid'];?>', '<?php echo $row['pay_num'];?>')" type="button">영수증</button>
							<?php if($is_admin) { ?>
							<button  class="KDC_Button__root__N26ep KDC_Button__mini__Sy8ka KDC_Button__color_delete__bAeWl" onclick="payment_new('<?php echo $row['trxid'];?>', '<?php echo $row['trackId'];?>', '<?php echo $row['pay_id'];?>')" type="button">재정산</button>
							<?php } ?>
							
						</div>
					</td>
					<td><?php echo $row['pay_num']; ?></td>
					<td><?php echo $row['dv_tid']; ?></td>
					<td><?php echo $pay_type; ?></td>
					<td><?php echo $row['trxid']; ?></td>
					<td><?php echo $row['trackId']; ?></td>
				</tr>
				<?php } ?>
				</tbody>
				</table>
				<?php /*
				<ul class="KDC_Infos__root__ixod2 info_type2 dot">
					<li>네이티브 앱 키: Android, iOS SDK에서 API를 호출할 때 사용합니다.</li>
					<li>JavaScript 키: JavaScript SDK에서 API를 호출할 때 사용합니다.</li>
					<li>REST API 키: REST API를 호출할 때 사용합니다.</li>
					<li>Admin 키: 모든 권한을 갖고 있는 키입니다. 노출이 되지 않도록 주의가 필요합니다.</li>
				</ul>
				*/ ?>
			</div>
			<?php
				$qstr = "p=".$p;
				$qstr .= "&fr_date=".$fr_date;
				$qstr .= "&to_date=".$to_date;
				$qstr .= "&sfl=".$sfl;
				$qstr .= "&stx=".$stx;
				echo get_paging_news(G5_IS_MOBILE ? "5" : "5", $page, $total_page, '?' . $qstr . '&amp;page=');
			?>
		</div>
	</div>
</div>




<?php
	include_once("./_tail.php");
?>