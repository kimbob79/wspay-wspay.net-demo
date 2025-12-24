<?php
	$title1 = "결제관리";
	$title2 = "가맹점별 결제내역";

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

	} else {
		if($member['mb_level'] == "8") { // 본사
			$adm_sql .= " mb_1 = '{$member['mb_id']}'  ";
		} else if($member['mb_level'] == "7") { // 지사
			$adm_sql .= " mb_2 = '{$member['mb_id']}'  ";
		} else if($member['mb_level'] == "6") { // 총판
			$adm_sql .= " mb_3 = '{$member['mb_id']}'  ";
		} else if($member['mb_level'] == "5") { // 대리점
			$adm_sql .= " mb_4 = '{$member['mb_id']}'  ";
		} else if($member['mb_level'] == "4") { //  영업점
			$adm_sql .= " mb_5 = '{$member['mb_id']}'  ";
		} else if($member['mb_level'] == "3") { // 가맹점
			$adm_sql .= " mb_6 = '{$member['mb_id']}'  ";
		}
	}

	$sql_common = " from g5_device where ".$adm_sql;

	if($member['mb_type'] == 1) {
	} else if($member['mb_type'] == 2) {
		$sql_search = " and mb_pid2 = '{$member['mb_id']}' ";
	} else if($member['mb_type'] == 3) {
		$sql_search = " and mb_pid3 = '{$member['mb_id']}' ";
	} else if($member['mb_type'] == 4) {
		$sql_search = " and mb_pid4 = '{$member['mb_id']}' ";
	} else if($member['mb_type'] == 5) {
		$sql_search = " and mb_pid5 = '{$member['mb_id']}' ";
	} else if($member['mb_type'] == 6) {
		$sql_search = " and mb_pid6 = '{$member['mb_id']}' ";
	} else if($member['mb_type'] == 7) {
		$sql_search = " and mb_pid7 = '{$member['mb_id']}' ";
	} else if($member['mb_type'] == 8) {
		$sql_search = " and mb_pid8 = '{$member['mb_id']}' ";
	} else {
	}

	if($mb_6_name) { $sql_search .= " and mb_6_name like '%{$mb_6_name}%' "; }

	if ($sst)
		$sql_order = " order by {$sst} {$sod} ";
	else
		$sql_order = " order by datetime desc ";

	$sql = " select count(*) as cnt {$sql_common} {$sql_search}  ";
	$row = sql_fetch($sql);

	$total_count = $row['cnt']; // 전체개수

	$page_count = "20";

	if($page_count) {
		$rows = $page_count;
	} else {
		$rows = $config['cf_page_rows'];
	}

	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함

	$sql = " select * {$sql_common} {$sql_search} {$sql_order} ";
	$result = sql_query($sql);
?>

<style>
.pm-header {
	background: linear-gradient(135deg, #00695c 0%, #00897b 100%);
	border-radius: 8px;
	padding: 12px 16px;
	margin-bottom: 10px;
	box-shadow: 0 2px 8px rgba(0, 105, 92, 0.2);
}
.pm-header-top {
	display: flex;
	align-items: center;
	justify-content: space-between;
	flex-wrap: wrap;
	gap: 10px;
}
.pm-title {
	color: #fff;
	font-size: 16px;
	font-weight: 600;
	display: flex;
	align-items: center;
	gap: 8px;
}
.pm-title i {
	font-size: 14px;
	opacity: 0.8;
}
.pm-stats {
	display: flex;
	flex-wrap: wrap;
	gap: 6px;
}
.pm-stat {
	display: inline-flex;
	align-items: center;
	background: rgba(255,255,255,0.12);
	border-radius: 4px;
	padding: 4px 10px;
	font-size: 12px;
	color: rgba(255,255,255,0.85);
	gap: 6px;
}
.pm-stat.total {
	background: rgba(255,193,7,0.3);
	color: #fff;
	font-weight: 600;
}
.pm-stat span {
	color: #fff;
	font-weight: 600;
}
.pm-search {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 10px 12px;
	margin-bottom: 10px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.pm-search-row {
	display: flex;
	align-items: center;
	flex-wrap: wrap;
	gap: 8px;
}
.pm-search-group {
	display: flex;
	align-items: center;
	gap: 4px;
}
.pm-search-group input[type="text"] {
	width: 90px;
	padding: 6px 8px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-size: 13px;
	background: #f8f9fa;
}
.pm-search-group input[type="text"]:focus {
	outline: none;
	border-color: #00695c;
	background: #fff;
}
.pm-search-group span {
	color: #999;
	font-size: 12px;
}
.pm-date-btns {
	display: flex;
	gap: 3px;
}
.pm-date-btns button {
	padding: 5px 8px;
	font-size: 11px;
	border: 1px solid #ddd;
	background: #f8f9fa;
	border-radius: 3px;
	cursor: pointer;
	color: #555;
	transition: all 0.15s;
}
.pm-date-btns button:hover {
	background: #00695c;
	border-color: #00695c;
	color: #fff;
}
.pm-search-divider {
	width: 1px;
	height: 24px;
	background: #e0e0e0;
	margin: 0 6px;
}
.pm-search-input {
	display: flex;
	align-items: center;
	gap: 4px;
}
.pm-search-input input[type="text"] {
	width: 150px;
	padding: 6px 10px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-size: 13px;
}
.pm-search-input input[type="text"]:focus {
	outline: none;
	border-color: #00695c;
}
.pm-btn-search {
	padding: 6px 12px;
	background: #00695c;
	color: #fff;
	border: none;
	border-radius: 4px;
	font-size: 12px;
	cursor: pointer;
	transition: background 0.15s;
}
.pm-btn-search:hover {
	background: #00897b;
}
@media (max-width: 768px) {
	.pm-header-top {
		flex-direction: column;
		align-items: flex-start;
	}
	.pm-stats {
		width: 100%;
	}
	.pm-search-row {
		flex-direction: column;
		align-items: flex-start;
	}
	.pm-search-divider {
		display: none;
	}
}
</style>

<div class="pm-header">
	<div class="pm-header-top">
		<div class="pm-title">
			<i class="fa fa-users"></i>
			가맹점별 결제내역
		</div>
		<div class="pm-stats">
			<div class="pm-stat total">총 가맹점 <span><?php echo number_format($total_count); ?>개</span></div>
		</div>
	</div>
</div>

<form id="fsearch" name="fsearch" method="get">
<input type="hidden" name="p" value="<?php echo $p; ?>">
<div class="pm-search">
	<div class="pm-search-row">
		<div class="pm-search-group">
			<input type="text" name="fr_date" value="<?php echo $fr_date ?>" id="fr_date" placeholder="시작일">
			<span>~</span>
			<input type="text" name="to_date" value="<?php echo $to_date ?>" id="to_date" placeholder="종료일">
		</div>
		<div class="pm-date-btns">
			<button type="submit" onclick="javascript:set_date('오늘');">오늘</button>
			<button type="submit" onclick="javascript:set_date('어제');">어제</button>
			<button type="submit" onclick="javascript:set_date('이번달');">이번달</button>
			<button type="submit" onclick="javascript:set_date('지난주');">지난주</button>
			<button type="submit" onclick="javascript:set_date('지난달');">지난달</button>
		</div>
		<div class="pm-search-divider"></div>
		<div class="pm-search-input">
			<input type="text" name="mb_6_name" value="<?php echo $mb_6_name ?>" placeholder="가맹점명 검색">
			<button type="submit" class="pm-btn-search">검색</button>
		</div>
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
					<?php if($member['mb_level'] >= 3) { ?>
					<th style="width:12%">가맹점</th>
					<?php } ?>
					<th>결제금액</th>
					<th>터미널ID</th>
					<th>결제타입</th>
					<th>등록일</th>
					<?php if($member['mb_level'] >= 8) { ?>
					<th style="width:12%">본사</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 7) { ?>
					<th style="width:12%">지사</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 6) { ?>
					<th style="width:12%">총판</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 5) { ?>
					<th style="width:12%">대리점</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 4) { ?>
					<th style="width:12%">영업점</th>
					<?php } ?>
				</tr>
			</thead>
			<tbody>
				<?php
					for ($i=0; $row=sql_fetch_array($result); $i++) {

					$num = number_format($total_count - ($page - 1) * $rows - $i);

					if($row['dv_type'] == 1) {
						$dv_types = "단말기";
					} else {
						$dv_types = "수기";
					}
					if($row['dv_certi'] == 1) {
						$dv_certis = "인증";
					} else {
						$dv_certis = "비인증";
					}

					$sql_sum = " select sum(pay) as sum_pay from g5_payment where (1) and (pay_datetime BETWEEN '{$fr_dates} 00:00:00' and '{$to_dates} 23:59:59') and dv_tid = '{$row['dv_tid']}'  ";
					$sum = sql_fetch($sql_sum);

				?>
				<tr<?php echo $bgcolor?" style='background: $bgcolor;'":'';?>>
					<td><?php echo $num; ?></td>
					<?php if($member['mb_level'] >= 3) { ?>
					<td class="td_name"><strong><?php echo $row['mb_6_name']; ?></strong></td>
					<?php } ?>
					<td style="text-align:right"><?php echo number_format($sum['sum_pay']); ?></td>
					<td><?php echo $row['dv_tid']; ?></td>
					<td><?php echo $dv_types." ".$dv_certis; ?></td>
					<td><?php echo $row['datetime']; ?></td>

					
					<?php if($member['mb_level'] >= 8) { ?>
					<td class="td_name"><?php if($row['mb_1_name']) { echo $row['mb_1_name']; } else { echo "-"; } ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 7) { ?>
					<td class="td_name"><?php if($row['mb_2_name']) { echo $row['mb_2_name']; } else { echo "-"; } ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 6) { ?>
					<td class="td_name"><?php if($row['mb_3_name']) { echo $row['mb_3_name']; } else { echo "-"; } ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 5) { ?>
					<td class="td_name"><?php if($row['mb_4_name']) { echo $row['mb_4_name']; } else { echo "-"; } ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 4) { ?>
					<td class="td_name"><?php if($row['mb_5_name']) { echo $row['mb_5_name']; } else { echo "-"; } ?></td>
					<?php } ?>
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
	</div>
</div>
<?php
	/*
	//http://cajung.com/new4/?p=payment&fr_date=20220906&to_date=20220906&sfl=mb_id&stx=
	$qstr = "p=".$p;
	$qstr .= "&fr_date=".$fr_date;
	$qstr .= "&to_date=".$to_date;
	$qstr .= "&sfl=".$sfl;
	$qstr .= "&stx=".$stx;
	echo get_paging_news(G5_IS_MOBILE ? "5" : "5", $page, $total_page, '?' . $qstr . '&amp;page=');
	*/
?>

