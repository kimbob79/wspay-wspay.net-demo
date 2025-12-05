<?php

	include_once("./_common.php");

	$title1 = "결제관리";
	$title2 = "결제내역 원본";

	include_once("./_head.php");

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

	$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
	$row = sql_fetch($sql);

	$total_count = $row['cnt']; // 전체개수

	$rows = $config['cf_page_rows'];

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

							<div class="KDC_Tab__root__h2hVQ tab type_other input_date search_br">
								<input type="text" name="fr_date" value="<?php echo $fr_date ?>" id="fr_date" class="KDC_Input__root__3M8Hf KDC_Input__root__3M8Hf_date" style="width:80px;" maxlength="10">
							</div>
							<div class="KDC_Tab__root__h2hVQ tab type_other input_date">
								<input type="text" name="to_date" value="<?php echo $to_date ?>" id="to_date" class="KDC_Input__root__3M8Hf KDC_Input__root__3M8Hf_date" style="width:80px;" maxlength="10">
							</div>
							<div class="KDC_Tab__root__h2hVQ tab type_other input_date" style="width:80px;">
								<select name="sfl" id="sfl" style="border:0;font-size: 14px;background:#fff;width:90%;">
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




							<div class="KDC_Tab__root__h2hVQ tab type_other  search_br"><div class="KDC_Tab__text__VzW9X" onclick="javascript:set_date('오늘');">오늘</div></div>
							<div class="KDC_Tab__root__h2hVQ tab type_other"><div class="KDC_Tab__text__VzW9X" onclick="javascript:set_date('어제');">어제</div></div>
							<div class="KDC_Tab__root__h2hVQ tab type_other"><div class="KDC_Tab__text__VzW9X" onclick="javascript:set_date('이번주');">이번주</div></div>
							<div class="KDC_Tab__root__h2hVQ tab type_other"><div class="KDC_Tab__text__VzW9X" onclick="javascript:set_date('이번달');">이번달</div></div>
							<div class="KDC_Tab__root__h2hVQ tab type_other"><div class="KDC_Tab__text__VzW9X" onclick="javascript:set_date('지난주');">지난주</div></div>
							<div class="KDC_Tab__root__h2hVQ tab type_other"><div class="KDC_Tab__text__VzW9X" onclick="javascript:set_date('지난달');">지난달</div></div>
							<div class="KDC_Tab__root__h2hVQ tab tab_selected type_other"><input type="submit" class="KDC_Tab__text__VzW9X" value="검색" style="background:#444; width:100%; border:0; color:#fff;"></div>
							<?php /*
							<div class="KDC_Tab__root__h2hVQ tab PeriodButtonGroup_custom__EtEdv type_other">
								<div class="KDC_Tab__text__VzW9X"></div>
							</div>
							*/ ?>
						<?php /*
						<div class="sch_last">
							<div style="float:left; margin-right:5px;">
							<select name="sfl" id="sfl">
								<option value="pay_num" <?php echo get_selected($sfl, "pay_num"); ?>>승인번호</option>
								<option value="mb_6_name" <?php echo get_selected($sfl, "mb_6_name"); ?>>가맹점명</option>
								<option value="dv_tid" <?php echo get_selected($sfl, "dv_tid"); ?>>TID</option>
								<option value="pay" <?php echo get_selected($sfl, "pay"); ?>>승인금액</option>
								<option value="pay_card_name" <?php echo get_selected($sfl, "pay_card_name"); ?>>카드사</option>
								<option value="pay_parti" <?php echo get_selected($sfl, "pay_parti"); ?>>할부</option>
							</select>
							<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input" size="7">
							<input type="submit" class="btn_submit" value="검색">
							</div>

							<ul class="anchor" style="float:left">
								<li><button type="submit" onclick="javascript:set_date('오늘');"><span>오늘</span></button></li>
								<li><button type="submit" onclick="javascript:set_date('어제');"><span>어제</span></button></li>
								<li><button type="submit" onclick="javascript:set_date('이번주');"><span>이번주</span></button></li>
								<li><button type="submit" onclick="javascript:set_date('이번달');"><span>이번달</span></button></li>
								<li><button type="submit" onclick="javascript:set_date('지난주');"><span>지난주</span></button></li>
								<li><button type="submit" onclick="javascript:set_date('지난달');"><span>지난달</span></button></li>
								<li><button type="submit" onclick="javascript:set_date('전체');"><span>전체</span></button></li>
							</ul>
						</div>
						*/ ?>
					</form>
				</div>



				<div class="KDC_Row__root__uio5h KDC_Row__responsive__obNwV">
					<div class="KDC_Column__root__NK8XY KDC_Column__flex_1__UcocY">
						<div class="KDC_Section__root__VXHOv">
							<table class="KDC_Table__root__Jim4z">
							<thead>
							<tr>
								<th style="width:50px;">번호</th>
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
								<th>등록</th>
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
								<td>
									<div class="buttons">
										<button  class="KDC_Button__root__N26ep KDC_Button__mini__Sy8ka KDC_Button__color_delete__bAeWl" onclick="alert('준비중입니다.')" type="button">등록</button>
									</div>
								</td>
							</tr>
							<?php } ?>
							<?php /*
							<tr>
								<td>
									<span class="txt_emph">네이티브 앱 키</span>
								</td>
								<td>
									2353db204034cd676a0159c81e193395
									<div class="float-right">
										<button type="button" class="KDC_Button__root__N26ep KDC_Button__mini_small__q6Jwf KDC_Button__color_cancel__TdcOV">복사</button>
									</div>
								</td>
								<td>
									<button type="button" class="KDC_Button__root__N26ep KDC_Button__mini_small__q6Jwf KDC_Button__color_cancel__TdcOV">재발급</button>
								</td>
							</tr>
							<tr>
								<td>
									<span class="txt_emph">REST API 키</span>
								</td>
								<td>
									0174a9739e7945a20ee3d11eab0b624d
									<div class="float-right">
										<button type="button" class="KDC_Button__root__N26ep KDC_Button__mini_small__q6Jwf KDC_Button__color_cancel__TdcOV">복사</button>
									</div>
								</td>
								<td>
									<button type="button" class="KDC_Button__root__N26ep KDC_Button__mini_small__q6Jwf KDC_Button__color_cancel__TdcOV">재발급</button>
								</td>
							</tr>
							<tr>
								<td>
									<span class="txt_emph">JavaScript 키</span>
								</td>
								<td>
									671e6ea2fc913f9fb9d42943a5508a8d
									<div class="float-right">
										<button type="button" class="KDC_Button__root__N26ep KDC_Button__mini_small__q6Jwf KDC_Button__color_cancel__TdcOV">복사</button>
									</div>
								</td>
								<td>
									<button type="button" class="KDC_Button__root__N26ep KDC_Button__mini_small__q6Jwf KDC_Button__color_cancel__TdcOV">재발급</button>
								</td>
							</tr>
							<tr>
								<td>
									<span class="txt_emph">Admin 키</span>
								</td>
								<td>
									1988233fac6f100ad760de67fc3681f4
									<div class="float-right">
										<button type="button" class="KDC_Button__root__N26ep KDC_Button__mini_small__q6Jwf KDC_Button__color_cancel__TdcOV">복사</button>
									</div>
								</td>
								<td>
									<button type="button" class="KDC_Button__root__N26ep KDC_Button__mini_small__q6Jwf KDC_Button__color_cancel__TdcOV">재발급</button>
								</td>
							</tr>
							*/ ?>
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
							//http://cajung.com/new4/?p=payment&fr_date=20220906&to_date=20220906&sfl=mb_id&stx=
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