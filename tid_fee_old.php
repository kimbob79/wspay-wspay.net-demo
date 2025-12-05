<?php
	$title1 = "수수료 관리";
	$title2 = "수수료 관리";



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

	$sql_common = " from g5_device_old where ".$adm_sql;
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




	if($dv_pg != null) { $sql_search .= " and dv_pg = '{$dv_pg}' "; }
	if($dv_type) { $sql_search .= " and dv_type = '{$dv_type}' "; }
	if($dv_certi) { $sql_search .= " and dv_certi = '{$dv_certi}' "; }


	if($membera) {	$sql_search .= " and mb_1 = '$membera' ";	}
	if($memberb) {	$sql_search .= " and mb_2 = '$memberb' ";	}
	if($memberc) {	$sql_search .= " and mb_3 = '$memberc' ";	}
	if($memberd) {	$sql_search .= " and mb_4 = '$memberd' ";	}
	if($membere) {	$sql_search .= " and mb_5 = '$membere' ";	}
	if($memberf) {	$sql_search .= " and mb_6 = '$memberf' ";	}
	if($mb_nick) {	$sql_search .= " and mb_6_name like '%$mb_nick%' ";	}

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
		$sql_order = " order by datetime desc ";

	$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
	$row = sql_fetch($sql);

	$total_count = $row['cnt']; // 전체개수

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

//	echo $sql."<br><br>";
//	echo $dv_pg;

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
	<div class="searchbox">
		<div class="midd">
			<ul>
				<?php  if($is_admin) { ?>
				<li>
					<strong>결제</strong>
					<div>
						<div data-skin="select">
							<select name="dv_pg" id="dv_pg" style="width:70px;">
								<option value="">PG사</option>
								<option value="0" <?php if($dv_pg == "0") { echo "selected"; } ?>>코페이</option>
								<option value="1" <?php if($dv_pg == "1") { echo "selected"; } ?>>다날</option>
								<option value="2" <?php if($dv_pg == "2") { echo "selected"; } ?>>광원</option>
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
						<input type="text" name="mb_6_name" value="<?php echo $mb_6_name ?>" id="mb_6_name" class="frm_input" size="7" placeholder="가맹점명" style="width:100px;">
						<input type="text" name="dv_tid" value="<?php echo $dv_tid ?>" id="dv_tid" class="frm_input" size="7" placeholder="TID" style="width:100px;">
						<button type="submit" class="btn_b btn_b02"><span>검색</span></button>
					</div>
				</li>
				<li>
					<strong>수수료</strong>
					<div>
						<span class="fee1" style="letter-spacing: -1px;">설정 수수료</span>
						<span class="fee2" style="letter-spacing: -1px;">수익 수수료</span>
					</div>
				</li>
			</ul>
		</div>
	</div>
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
					<th style="width:50px;" rowspan="2">번호</th>

					<?php if($member['mb_level'] >= 8) { ?>
					<th colspan="2">본사</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 7) { ?>
					<th colspan="2">지사</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 6) { ?>
					<th colspan="2">총판</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 5) { ?>
					<th colspan="2">대리점</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 4) { ?>
					<th colspan="2">영업점</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 3) { ?>
					<th colspan="2">가맹점</th>
					<?php } ?>

					<th rowspan="2">TID</th>
					<th rowspan="2">PG</th>
					<th rowspan="2">단말기/수기</th>
					<th rowspan="2">인증/비인증</th>
					<th rowspan="2">등록일시</th>
					<?php if($member['mb_level'] >= 8) { ?>
					<th rowspan="2">관리</th>
					<?php } ?>
				</tr>
				<tr>

					<?php if($member['mb_level'] >= 8) { ?>
					<th>사명</th>
					<th>수수료</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 7) { ?>
					<th>지사</th>
					<th>수수료</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 6) { ?>
					<th>총판</th>
					<th>수수료</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 5) { ?>
					<th>대리점</th>
					<th>수수료</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 4) { ?>
					<th>영업점</th>
					<th>수수료</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 3) { ?>
					<th>가맹점</th>
					<th>수수료</th>
					<?php } ?>
				</tr>
				</thead>
				<tbody>
				<?php
					for ($i=0; $row=sql_fetch_array($result); $i++) {
					$num = number_format($total_count - ($page - 1) * $rows - $i);



					if($row['dv_pg'] == 0) {
						$dv_pgs = "코페이";
					} else if($row['dv_pg'] == 1) {
						$dv_pgs = "다날";
					} else if($row['dv_pg'] == 2) {
						$dv_pgs = "광원";
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
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php if($row['mb_1_name']) { ?><input type="text" autocomplete="off" name="mb_1_fee" value="<?php echo $row['mb_1_fee']; ?>" required class="frm_input" style="text-align:right" size="4" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '');"> %<?php } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 7) { ?>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;" class="td_name"><?php if($row['mb_2_name']) { ?><?php echo $row['mb_2_name']; ?><?php } ?></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php if($row['mb_2_name']) { ?><input type="text" autocomplete="off" name="mb_2_fee" value="<?php echo $row['mb_2_fee']; ?>" required class="frm_input" style="text-align:right" size="4" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '');"> %<?php } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 6) { ?>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;" class="td_name"><?php if($row['mb_3_name']) { ?><?php echo $row['mb_3_name']; ?><?php } ?></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php if($row['mb_3_name']) { ?><input type="text" autocomplete="off" name="mb_3_fee" value="<?php echo $row['mb_3_fee']; ?>" required class="frm_input" style="text-align:right" size="4" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '');"> %<?php } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 5) { ?>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;" class="td_name"><?php if($row['mb_4_name']) { ?><?php echo $row['mb_4_name']; ?><?php } ?></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php if($row['mb_4_name']) { ?><input type="text" autocomplete="off" name="mb_4_fee" value="<?php echo $row['mb_4_fee']; ?>" required class="frm_input" style="text-align:right" size="4" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '');"> %<?php } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 4) { ?>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;" class="td_name"><?php if($row['mb_5_name']) { ?><?php echo $row['mb_5_name']; ?><?php } ?></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php if($row['mb_5_name']) { ?><input type="text" autocomplete="off" name="mb_5_fee" value="<?php echo $row['mb_5_fee']; ?>" required class="frm_input" style="text-align:right" size="4" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '');"> %<?php } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 3) { ?>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;" class="td_name"><?php if($row['mb_6_name']) { ?><?php echo $row['mb_6_name']; ?><?php } ?></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php if($row['mb_6_name']) { ?><input type="text" autocomplete="off" name="mb_6_fee" value="<?php echo $row['mb_6_fee']; ?>" required class="frm_input" style="text-align:right" size="4" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '');"> %<?php } ?></td>
					<?php } ?>

					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php echo $row['dv_tid']; ?></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;">
						<select name="dv_pg" required class="required" style="width:70px">
							<option value="">PG사</option>
							<option value="0" <?php if($row['dv_pg'] == "0") { echo "selected"; } ?>>코페이</option>
							<option value="1" <?php if($row['dv_pg'] == "1") { echo "selected"; } ?>>다날</option>
							<option value="2" <?php if($row['dv_pg'] == "2") { echo "selected"; } ?>>광원</option>
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
					<td><?php if($row['mb_1_name']) { echo "<span class='fee1'>".$row['mb_1_fee']."</span><span class='fee2'>".$mb_1_fee."</span>"; } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 7) { ?>
					<td class="td_name"><?php if($row['mb_2_name']) { echo $row['mb_2_name']; } ?></td>
					<td><?php if($row['mb_2_name']) { echo "<span class='fee1'>".$row['mb_2_fee']."</span><span class='fee2'>".$mb_2_fee."</span>"; } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 6) { ?>
					<td class="td_name"><?php if($row['mb_3_name']) { echo $row['mb_3_name']; } ?></td>
					<td><?php if($row['mb_3_name']) { echo "<span class='fee1'>".$row['mb_3_fee']."</span><span class='fee2'>".$mb_3_fee."</span>"; } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 5) { ?>
					<td class="td_name"><?php if($row['mb_4_name']) { echo $row['mb_4_name']; } ?></td>
					<td><?php if($row['mb_4_name']) { echo "<span class='fee1'>".$row['mb_4_fee']."</span><span class='fee2'>".$mb_4_fee."</span>"; } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 4) { ?>
					<td class="td_name"><?php if($row['mb_5_name']) { echo $row['mb_5_name']; } ?></td>
					<td><?php if($row['mb_5_name']) { echo "<span class='fee1'>".$row['mb_5_fee']."</span><span class='fee2'>".$mb_5_fee."</span>"; } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 3) { ?>
					<td class="td_name"><?php if($row['mb_6_name']) { echo $row['mb_6_name']; } ?></td>
					<td><?php if($row['mb_6_name']) { echo "<span class='fee1'>".$row['mb_6_fee']."</span><span class='fee2'>".$mb_6_fee."</span>"; } ?></td>
					<?php } ?>

					<td><?php echo $row['dv_tid']; ?></td>
					<td><?php echo $dv_pgs; ?></td>
					<td><?php echo $dv_types; ?></td>
					<td><?php echo $dv_certis; ?></td>
					<td><?php echo $row['datetime']; ?></td>
					<?php if($member['mb_level'] >= 8) { ?>
					<td class="is-actions-cell">
						<div class="buttons">

							<a href="./?p=tid_fee&dv_pg=<?php echo $dv_pg; ?>&dv_type=<?php echo $dv_type; ?>&dv_certi=<?php echo $dv_certi; ?>&membera=<?php echo $membera; ?>&memberb=<?php echo $memberb; ?>&memberc=<?php echo $memberc; ?>&memberd=<?php echo $memberd; ?>&membere=<?php echo $membere; ?>&memberf=<?php echo $memberf; ?>&mb_nick=<?php echo $mb_nick; ?>&dv_tid=<?php echo $dv_tid; ?>&dv_id=<?php echo $row['dv_id']; ?>&mb_6_name=<?php echo $mb_6_name; ?>&page_count=<?php echo $page_count; ?>&page=<?php echo $page; ?>" class="btn_b btn_b02">수정</a>

							<a href="./?p=tid_fee_delete&dv_id=<?php echo $row['dv_id']; ?>&page_count=<?php echo $page_count; ?>&page=<?php echo $page; ?>" class="btn_b btn_b06" onclick="return confirm('정말 <?php echo $row['mb_6_name']; ?>가맹점을 삭제 하시겠습니까');">삭제</a>
						</div>
					</td>
					<?php } ?>
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