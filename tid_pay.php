<?php
	$title1 = "수수료 관리";
	$title2 = "수수료 관리";

	if(!$fr_date) { $fr_date = date("Ymd"); }
	if(!$to_date) { $to_date = date("Ymd"); }

	$fr_dates = date("Y-m-d", strtotime($fr_date));
	$to_dates = date("Y-m-d", strtotime($to_date));

	if($is_admin) { // 관리자

		if(adm_sql_common) {
			$adm_sql = " mb_1 IN (".adm_sql_common.")";
		} else {
			$adm_sql = " (1)";
		}

	} else if($member['mb_level'] == 7) { // 지사
		$sql_search = " mb_2 = '{$member['mb_id']}' ";
	} else if($member['mb_level'] == 6) { // 총판
		$sql_search = " mb_3 = '{$member['mb_id']}' ";
	} else if($member['mb_level'] == 5) { // 대리점
		$sql_search = " mb_4 = '{$member['mb_id']}' ";
	} else if($member['mb_level'] == 4) { // 영업점
		$sql_search = " mb_5 = '{$member['mb_id']}' ";
	}

	$sql_common = " from g5_device where ".$adm_sql;
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
	if($device_type) { $sql_search .= " and dv_type = '{$device_type}' "; }

	if($dv_pg) { $sql_searchs .= " and c.dv_pg = '{$dv_pg}' "; }
	if($dv_type) { $sql_search .= " and dv_type = '{$dv_type}' "; }
	if($dv_certi) { $sql_search .= " and dv_certi = '{$dv_certi}' "; }

	if($membera) {	$sql_searchs .= " and c.mb_1 = '$membera' ";	}
	if($memberb) {	$sql_searchs .= " and c.mb_2 = '$memberb' ";	}
	if($memberc) {	$sql_searchs .= " and c.mb_3 = '$memberc' ";	}
	if($memberd) {	$sql_searchs .= " and c.mb_4 = '$memberd' ";	}
	if($membere) {	$sql_searchs .= " and c.mb_5 = '$membere' ";	}
	if($memberf) {	$sql_searchs .= " and c.mb_6 = '$memberf' ";	}
	if($mb_nick) {	$sql_searchs .= " and c.mb_6_name like '%$mb_nick%' ";	}

	if($mb_1_name) { $sql_search .= " and mb_1_name like '%{$mb_1_name}%' "; }
	if($mb_2_name) { $sql_search .= " and mb_2_name like '%{$mb_2_name}%' "; }
	if($mb_3_name) { $sql_search .= " and mb_3_name like '%{$mb_3_name}%' "; }
	if($mb_4_name) { $sql_search .= " and mb_4_name like '%{$mb_4_name}%' "; }
	if($mb_5_name) { $sql_search .= " and mb_5_name like '%{$mb_5_name}%' "; }
	if($mb_6_name) { $sql_search .= " and mb_6_name like '%{$mb_6_name}%' "; }

	if($dv_tid) { $sql_search .= " and dv_tid like '%{$dv_tid}%' "; }
	if($mb_6_name) { $sql_search .= " and mb_6_name like '%{$mb_6_name}%' "; }

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
		$sql_order = " order by dv_id desc ";

	$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
	$row = sql_fetch($sql);

	$total_count = $row['cnt']; // 전체개수
	$page_count = 500;
	if($page_count) {
		$rows = $page_count;
	} else {
		$rows = $config['cf_page_rows'];
	}

	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함

	$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";

	$sql =" SELECT c.*, COALESCE(SUM(a.pay), 0) AS total_amount FROM g5_device c LEFT JOIN g5_payment a ON c.dv_tid = a.dv_tid and a.pay_datetime BETWEEN '{$fr_dates} 00:00:00' AND '{$to_dates} 23:59:59' where (1) {$sql_searchs} GROUP BY c.dv_tid {$sql_order} limit {$from_record}, {$rows}";

	$xlsx_sql = "select * {$sql_common} {$sql_search} {$sql_order} ";
	$result = sql_query($sql);


	//	echo $sql."<br><br>";
	//	echo $dv_pg;  and (pay_datetime BETWEEN '{$fr_dates} 00:00:00' and '{$to_dates} 23:59:59')
?>
<style>

td span { /*font-family: 'FFF-Reaction-Trial';*/ font-size:11px;}
td span .fee_name {font-family: 'NanumGothic';}

.fee_left {float:left;}
.fee_right {float:right;}

.tid {font-weight:300; color:#999}
select { width:100px; }
.table_title {font-size:13px; margin:0 0 10px 5px; font-weight:700; color:#555}
</style>


<div class="index_menu">
	<ul class="shortcut">
		<li class="sc_current"><a>수수료 관리</a></li>
		<li class="sc_visit">
			<aside id="visit">
			</aside>
		</li>
	</ul>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<input type="hidden" name="p" value="<?php echo $p; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">
	<div class="searchbox">
		<div class="midd">
			<ul>
				<?php  if($is_admin) { ?>
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
						<button type="button" onclick="javascript:set_date('전체');" class="btn_b btn_b09"><span>전체</span></button>
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
					<strong>결제</strong>
					<div>
						<div data-skin="select">

							<select name="dv_pg" id="dv_pg" style="width:70px;">
								<option value="">PG사</option>
								<?php
									$pg_sql = "select * from g5_pg where (1) order by pg_id desc";
									$pg_result = sql_query($pg_sql);
									while ($pg = sql_fetch_array($pg_result)) {
								?>
								<option value="<?php echo $pg['pg_code']; ?>" <?php if($dv_pg == $pg['pg_code']) { echo "selected"; } ?>><?php echo $pg['pg_name']; ?></option>
								<?php } ?>
							</select>
							<select name="dv_type" id="dv_type" style="width:70px;">
								<option value="">결제종류</option>
								<option value="1" <?php if($dv_type == "1") { echo "selected"; } ?>>단말기</option>
								<option value="2" <?php if($dv_type == "2") { echo "selected"; } ?>>수기</option>
							</select>
							<select name="dv_certi" id="dv_certi" style="width:70px;">
								<option value="">인증/비인증</option>
								<option value="1" <?php if($dv_certi == "1") { echo "selected"; } ?>>인증</option>
								<option value="2" <?php if($dv_certi == "2") { echo "selected"; } ?>>비인증</option>
							</select>
						</div>
					</div>
				</li>
				<li>
					<strong>선택</strong>
					<div>
						<div data-skin="select">
							<select name="memberb" id="memberb" style="width:70px;">
								<option value="">지사</option>
								<?php
									$sql_g = " select * from g5_member where mb_level = '7' order by mb_nick";
									$result_g = sql_query($sql_g);
									for ($k=0; $row_g=sql_fetch_array($result_g); $k++) {
									?>
									<option value="<?php echo $row_g['mb_id']; ?>" <?php if($memberb == $row_g['mb_id']) { echo "selected"; } ?>><?php echo $row_g['mb_nick']; ?></option>
									<?php
									}
								?>
							</select>
							<select name="memberc" id="memberc" style="width:70px;">
								<option value="">총판</option>
								<?php
									$sql_g = " select * from g5_member where mb_level = '6' order by mb_nick";
									$result_g = sql_query($sql_g);
									for ($k=0; $row_g=sql_fetch_array($result_g); $k++) {
									?>
									<option value="<?php echo $row_g['mb_id']; ?>" <?php if($memberc == $row_g['mb_id']) { echo "selected"; } ?>><?php echo $row_g['mb_nick']; ?></option>
									<?php
									}
								?>
							</select>
							<select name="memberd" id="memberd" style="width:70px;">
								<option value="">대리점</option>
								<?php
									$sql_g = " select * from g5_member where mb_level = '5' order by mb_nick";
									$result_g = sql_query($sql_g);
									for ($k=0; $row_g=sql_fetch_array($result_g); $k++) {
									?>
									<option value="<?php echo $row_g['mb_id']; ?>" <?php if($memberd == $row_g['mb_id']) { echo "selected"; } ?>><?php echo $row_g['mb_nick']; ?></option>
									<?php
									}
								?>
							</select>
							<select name="membere" id="membere" style="width:70px;">
								<option value="">영업점</option>
								<?php
									$sql_g = " select * from g5_member where mb_level = '4' order by mb_nick";
									$result_g = sql_query($sql_g);
									for ($k=0; $row_g=sql_fetch_array($result_g); $k++) {
									?>
									<option value="<?php echo $row_g['mb_id']; ?>" <?php if($membere == $row_g['mb_id']) { echo "selected"; } ?>><?php echo $row_g['mb_nick']; ?></option>
									<?php
									}
								?>
							</select>
						</div>
					</div>
				</li>
				<?php } ?>
				<li>
					<strong>검색</strong>
					<div>
						<button type="submit" class="btn_b btn_b02"><span>검색</span></button>
						<?php /*
						<input type="text" name="mb_6_name" value="<?php echo $mb_6_name ?>" id="mb_6_name" class="frm_input" size="7" placeholder="가맹점명" style="width:100px;">
						<input type="text" name="dv_tid" value="<?php echo $dv_tid ?>" id="dv_tid" class="frm_input" size="7" placeholder="TID" style="width:100px;">
						<?php if($is_admin) { ?>
						<button type="button" class="btn_b btn_b06" id="xlsx"><span><i class="fa fa-file-excel-o" aria-hidden="true"></i> 엑셀출력</span></button>
						<?php } ?>
						*/ ?>
					</div>
				</li>
			</ul>
		</div>
	</div>
</form>





<form action="./xlsx/tid_fee.php" id="frm_xlsx" method="post">
<input type="hidden" name="xlsx_sql" value="<?php echo $xlsx_sql; ?>">
<input type="hidden" name="p" value="<?php echo $p; ?>">
</form>





<form name="fmember" id="fmember" action="./?p=tid_fee_update" method="post" enctype="multipart/form-data">
<input type="hidden" name="mb_1_name" value="<?php echo $mb_1_name ?>">
<input type="hidden" name="mb_2_name" value="<?php echo $mb_2_name ?>">
<input type="hidden" name="mb_3_name" value="<?php echo $mb_3_name ?>">
<input type="hidden" name="mb_4_name" value="<?php echo $mb_4_name ?>">
<input type="hidden" name="mb_5_name" value="<?php echo $mb_5_name ?>">
<input type="hidden" name="mb_6_name" value="<?php echo $mb_6_name ?>">
<input type="hidden" name="dv_tid" value="<?php echo $dv_tid ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="dv_id" value="<?php echo $dv_id ?>">


<div class="m_board_scroll">
	<div class="m_table_wrap">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
				<tr>
					<th style="width:50px;"></th>

					<?php if($member['mb_level'] >= 8) { ?>
					<th></th>
					<th colspan="2">수수료</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 7) { ?>
					<th></th>
					<th colspan="2">수수료</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 6) { ?>
					<th></th>
					<th colspan="2">수수료</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 5) { ?>
					<th></th>
					<th colspan="2">수수료</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 4) { ?>
					<th></th>
					<th colspan="2">수수료</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 3) { ?>
					<th></th>
					<th colspan="2">수수료</th>
					<?php } ?>

					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
				</tr>
				<tr>
					<th style="width:50px;">번호</th>

					<?php if($member['mb_level'] >= 8) { ?>
					<th>본사</th>
					<th>적용</th>
					<th>적립</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 7) { ?>
					<th>지사</th>
					<th>적용</th>
					<th>적립</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 6) { ?>
					<th>총판</th>
					<th>적용</th>
					<th>적립</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 5) { ?>
					<th>대리점</th>
					<th>적용</th>
					<th>적립</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 4) { ?>
					<th>영업점</th>
					<th>적용</th>
					<th>적립</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 3) { ?>
					<th>가맹점</th>
					<th>적용</th>
					<th>적립</th>
					<?php } ?>

					<th>TID</th>
					<th>PG</th>
					<th>단말기/수기</th>
					<th>인증/비인증</th>
					<th>등록일시</th>
					<th>결제금액</th>
				</tr>
				</thead>
				<tbody>
				<?php
					for ($i=0; $row=sql_fetch_array($result); $i++) {
						$num = number_format($total_count - ($page - 1) * $rows - $i);
						$row_pg = sql_fetch(" select * from g5_pg where pg_code = '{$row['dv_pg']}' ");
						$dv_pgs = $row_pg['pg_name'];


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
					if($row['dv_bank'] == 1) {
						$dv_bank = "지갑정산";
					} else {
						$dv_bank = "계좌이체";
					}
					if($row['dv_set'] == 1) {
						$dv_set = "D+1";
					} else {
						$dv_set = "즉결";
					}

/*
					if($row['dv_pg'] == 'ton') {
						$dv_pgs = "페이트리";
					} else  {
						$dv_pgs = "워너";
					}

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
*/

					// 본사 수수료율
					if($row['mb_2_fee'] > 0) {
						$mb_1_fee = $row['mb_2_fee'] - $row['mb_1_fee']; // 지사 - 본사
					} else if($row['mb_3_fee'] > 0) {
						$mb_1_fee = $row['mb_3_fee'] - $row['mb_1_fee']; // 총판 - 본사
					} else if($row['mb_4_fee'] > 0) {
						$mb_1_fee = $row['mb_4_fee'] - $row['mb_1_fee']; // 대리점 - 본사
					} else if($row['mb_5_fee'] > 0) {
						$mb_1_fee = $row['mb_5_fee'] - $row['mb_1_fee']; // 영업점 - 본사
					} else if($row['mb_6_fee'] > 0) {
						$mb_1_fee = $row['mb_6_fee'] - $row['mb_1_fee']; // 가맹점 - 본사
					}

					// 지사 수수료율
					if($row['mb_3_fee'] > 0) {
						$mb_2_fee = $row['mb_3_fee'] - $row['mb_2_fee']; // 총판 - 지사
					} else if($row['mb_4_fee'] > 0) {
						$mb_2_fee = $row['mb_4_fee'] - $row['mb_2_fee']; // 대리점 - 지사
					} else if($row['mb_5_fee'] > 0) {
						$mb_2_fee = $row['mb_5_fee'] - $row['mb_2_fee']; // 영업점 - 지사
					} else if($row['mb_6_fee'] > 0) {
						$mb_2_fee = $row['mb_6_fee'] - $row['mb_2_fee']; // 가맹점 - 지사
					}

					// 총판 수수료율
					if($row['mb_4_fee'] > 0) {
						$mb_3_fee = $row['mb_4_fee'] - $row['mb_3_fee']; // 대리점 - 총판
					} else if($row['mb_5_fee'] > 0) {
						$mb_3_fee = $row['mb_5_fee'] - $row['mb_3_fee']; // 영업점 - 총판
					} else if($row['mb_6_fee'] > 0) {
						$mb_3_fee = $row['mb_6_fee'] - $row['mb_3_fee']; // 가맹점 - 총판
					}

					// 대리점 수수료율
					if($row['mb_5_fee'] > 0) {
						$mb_4_fee = $row['mb_5_fee'] - $row['mb_4_fee']; // 영업점 - 대리점
					} else if($row['mb_6_fee'] > 0) {
						$mb_4_fee = $row['mb_6_fee'] - $row['mb_4_fee']; // 가맹점 - 대리점
					}

					// 영업점 수수료율
					if($row['mb_6_fee'] > 0) {
						$mb_5_fee = $row['mb_6_fee'] - $row['mb_5_fee']; // 가맹점 - 영업점
					}
					$mb_6_fee = 100 - $row['mb_6_fee'];


					if($row['mb_1_name']) { $mb_1_fee = sprintf('%0.4f', $mb_1_fee); } else { $mb_1_fee = ""; }
					if($row['mb_2_name']) { $mb_2_fee = sprintf('%0.4f', $mb_2_fee); } else { $mb_2_fee = ""; }
					if($row['mb_3_name']) { $mb_3_fee = sprintf('%0.4f', $mb_3_fee); } else { $mb_3_fee = ""; }
					if($row['mb_4_name']) { $mb_4_fee = sprintf('%0.4f', $mb_4_fee); } else { $mb_4_fee = ""; }
					if($row['mb_5_name']) { $mb_5_fee = sprintf('%0.4f', $mb_5_fee); } else { $mb_5_fee = ""; }
					if($row['mb_6_name']) { $mb_6_fee = sprintf('%0.4f', $mb_6_fee); } else { $mb_6_fee = ""; }

					if($row['mb_1_name']) { $mb_1_fee = $mb_1_fee; } else { $mb_1_fee = ""; }
					if($row['mb_2_name']) { $mb_2_fee = $mb_2_fee; } else { $mb_2_fee = ""; }
					if($row['mb_3_name']) { $mb_3_fee = $mb_3_fee; } else { $mb_3_fee = ""; }
					if($row['mb_4_name']) { $mb_4_fee = $mb_4_fee; } else { $mb_4_fee = ""; }
					if($row['mb_5_name']) { $mb_5_fee = $mb_5_fee; } else { $mb_5_fee = ""; }
					if($row['mb_6_name']) { $mb_6_fee = $mb_6_fee; } else { $mb_6_fee = ""; }

					if($dv_id == $row['dv_id']) {
				?>
				<tr>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php echo $num; ?></td>

					<?php if($member['mb_level'] >= 8) { ?>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;" class="td_name"><?php if($row['mb_1_name']) { ?><?php echo $row['mb_1_name']; ?><?php } ?></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php if($row['mb_1_name']) { ?><input type="text" autocomplete="off" name="mb_1_fee" value="<?php echo $row['mb_1_fee']; ?>" required class="frm_input" style="text-align:right" size="4" maxlength="7" oninput="this.value = this.value.replace(/[^0-9.]/g, '');"> %<?php } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 7) { ?>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;" class="td_name"><?php if($row['mb_2_name']) { ?><?php echo $row['mb_2_name']; ?><?php } ?></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php if($row['mb_2_name']) { ?><input type="text" autocomplete="off" name="mb_2_fee" value="<?php echo $row['mb_2_fee']; ?>" required class="frm_input" style="text-align:right" size="4" maxlength="7" oninput="this.value = this.value.replace(/[^0-9.]/g, '');"> %<?php } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 6) { ?>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;" class="td_name"><?php if($row['mb_3_name']) { ?><?php echo $row['mb_3_name']; ?><?php } ?></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php if($row['mb_3_name']) { ?><input type="text" autocomplete="off" name="mb_3_fee" value="<?php echo $row['mb_3_fee']; ?>" required class="frm_input" style="text-align:right" size="4" maxlength="7" oninput="this.value = this.value.replace(/[^0-9.]/g, '');"> %<?php } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 5) { ?>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;" class="td_name"><?php if($row['mb_4_name']) { ?><?php echo $row['mb_4_name']; ?><?php } ?></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php if($row['mb_4_name']) { ?><input type="text" autocomplete="off" name="mb_4_fee" value="<?php echo $row['mb_4_fee']; ?>" required class="frm_input" style="text-align:right" size="4" maxlength="7" oninput="this.value = this.value.replace(/[^0-9.]/g, '');"> %<?php } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 4) { ?>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;" class="td_name"><?php if($row['mb_5_name']) { ?><?php echo $row['mb_5_name']; ?><?php } ?></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php if($row['mb_5_name']) { ?><input type="text" autocomplete="off" name="mb_5_fee" value="<?php echo $row['mb_5_fee']; ?>" required class="frm_input" style="text-align:right" size="4" maxlength="7" oninput="this.value = this.value.replace(/[^0-9.]/g, '');"> %<?php } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 3) { ?>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;" class="td_name"><?php if($row['mb_6_name']) { ?><?php echo $row['mb_6_name']; ?><?php } ?></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php if($row['mb_6_name']) { ?><input type="text" autocomplete="off" name="mb_6_fee" value="<?php echo $row['mb_6_fee']; ?>" required class="frm_input" style="text-align:right" size="4" maxlength="7" oninput="this.value = this.value.replace(/[^0-9.]/g, '');"> %<?php } ?></td>
					<?php } ?>

					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php echo $row['dv_tid']; ?></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><input type="text" autocomplete="off" name="pay_alram" value="<?php echo $row['pay_alram']; ?>" class="frm_input" style="text-align:right" placeholder="금액알림"></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;">
						<select name="dv_pg" required class="required" style="width:70px">
							<option value="">PG사</option>
							<option value="0" <?php if($row['dv_pg'] == "0") { echo "selected"; } ?>>코페이</option>
							<option value="1" <?php if($row['dv_pg'] == "1") { echo "selected"; } ?>>다날</option>
							<option value="2" <?php if($row['dv_pg'] == "2") { echo "selected"; } ?>>광원</option>
							<option value="3" <?php if($row['dv_pg'] == "3") { echo "selected"; } ?>>웰컴</option>
							<option value="4" <?php if($row['dv_pg'] == "4") { echo "selected"; } ?>>페이시스</option>
							<option value="5" <?php if($row['dv_pg'] == "5") { echo "selected"; } ?>>섹타나인</option>
						</select>
					</td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;">
						<select name="dv_type" required class="required" style="width:70px">
							<option value="">결제종류</option>
							<option value="1" <?php if($row['dv_type'] == "1") { echo "selected"; } ?>>단말기</option>
							<option value="2" <?php if($row['dv_type'] == "2") { echo "selected"; } ?>>수기</option>
						</select>
					</td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;">
						<select name="dv_certi" required class="required" style="width:70px">
							<option value="">인증/비인증</option>
							<option value="1" <?php if($row['dv_certi'] == "1") { echo "selected"; } ?>>인증</option>
							<option value="2" <?php if($row['dv_certi'] == "2") { echo "selected"; } ?>>비인증</option>
						</select>
					</td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;">
						<button type="submit" class="btn_admin">저장</button>
					</td>
				</tr>
				<?php } else { ?>
				<tr>
					<td><?php echo $num; ?></td>

					<?php if($member['mb_level'] >= 8) { ?>
					<td class="td_name"><?php if($row['mb_1_name']) { echo $row['mb_1_name']; } ?></td>
					<td><?php echo $row['mb_1_fee']; ?></td>
					<td><?php echo $mb_1_fee; ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 7) { ?>
					<td class="td_name"><?php if($row['mb_2_name']) { echo $row['mb_2_name']; } ?></td>
					<td><?php echo $row['mb_2_fee']; ?></td>
					<td><?php echo $mb_2_fee; ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 6) { ?>
					<td class="td_name"><?php if($row['mb_3_name']) { echo $row['mb_3_name']; } ?></td>
					<td><?php echo $row['mb_3_fee']; ?></td>
					<td><?php echo $mb_3_fee; ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 5) { ?>
					<td class="td_name"><?php if($row['mb_4_name']) { echo $row['mb_4_name']; } ?></td>
					<td><?php echo $row['mb_4_fee']; ?></td>
					<td><?php echo $mb_4_fee; ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 4) { ?>
					<td class="td_name"><?php if($row['mb_5_name']) { echo $row['mb_5_name']; } ?></td>
					<td><?php echo $row['mb_5_fee']; ?></td>
					<td><?php echo $mb_5_fee; ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 3) { ?>
					<td class="td_name"><?php if($row['mb_6_name']) { echo $row['mb_6_name']; } ?></td>
					<td><?php echo $row['mb_6_fee']; ?></td>
					<td><?php echo $mb_6_fee; ?></td>
					<?php } ?>

					<td><?php echo $row['dv_tid']; ?></td>
					<td><?php echo $dv_pgs; ?></td>
					<td><?php echo $dv_types; ?></td>
					<td><?php echo $dv_certis; ?></td>
					<td><?php echo $row['datetime']; ?></td>
					<td style="text-align:left"><?php echo number_format($row['total_amount']); ?></td>
				</tr>
				<?php } } ?>
			</tbody>
		</table>
	</div>
</div>
</form>
	<?php
		//http://cajung.com/new4/?p=payment&fr_date=20220906&to_date=20220906&sfl=mb_id&stx=
		$qstr = "p=".$p;
		$qstr .= "&fr_date=".$fr_date;
		$qstr .= "&to_date=".$to_date;
		$qstr .= "&sfl=".$sfl;
		$qstr .= "&stx=".$stx;

		$qstr .= "&membera=".$membera;
		$qstr .= "&memberb=".$memberb;
		$qstr .= "&memberc=".$memberc;
		$qstr .= "&memberd=".$memberd;
		$qstr .= "&membere=".$membere;
		$qstr .= "&memberf=".$memberf;
		$qstr .= "&dv_pg=".$dv_pg;
		$qstr .= "&dv_type=".$dv_type;
		$qstr .= "&dv_certi=".$dv_certi;
		$qstr .= "&page_count=".$page_count;
		echo get_paging_news(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page=');
	?>


<script>

	$("#membera, #memberb, #memberc, #memberd, #membere, #memberf, #dv_pg, #dv_type, #dv_certi").change(function() {
		$("#membera, #memberb, #memberc, #memberd, #membere, #memberf, #dv_pg, #dv_type, #dv_certi").val();
		$(this).parents().filter("form").submit();
	});
</script>