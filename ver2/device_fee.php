<?php
	include_once("./_common.php");

	$title1 = "수수료 관리";
	$title2 = "수수료 관리";

	include_once("./_head.php");


	if($redpay == "Y") {
		if($member['mb_id'] == "admin") {
			$adm_sql = " mb_1 IN (".adm_sql_common.")";
		} else  {
			$adm_sql = " mb_1 NOT IN (".adm_sql_common.")";
		}
	} else {
		$adm_sql = "(1)";
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

?>

<style>
</style>

<div class="KDC_Content__root__tP6yD KDC_Content__responsive__4kCaB CWLNB">
	<div class="KDC_Tabs__root__JmfCY PeriodButtonGroup_root__UE4Kj">
		<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
			<input type="hidden" name="p" value="<?php echo $p; ?>">

			<?php if($is_admin) { ?>
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date" style="width:100px;">
			<select name="membera" id="membera" style="border:0;background:#fff;width:90%;">
				<option value="">본사선택</option>
				<?php
					$sql_g = " select * from g5_member where mb_level = '8' order by mb_nick";
					echo $sql_g;
					$result_g = sql_query($sql_g);
					for ($k=0; $row_g=sql_fetch_array($result_g); $k++) {
					?>
					<option value="<?php echo $row_g['mb_id']; ?>" <?php if($membera == $row_g['mb_id']) { echo "selected"; } ?>><?php echo $row_g['mb_nick']; ?></option>
					<?php
					}
				?>
			</select>
			</div>
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date" style="width:100px;">
			<select name="memberb" id="memberb" style="border:0;background:#fff;width:90%;">
				<option value="">지사선택</option>
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
			</div>
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date" style="width:100px;">
			<select name="memberc" id="memberc" style="border:0;background:#fff;width:90%;">
				<option value="">총판선택</option>
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
			</div>
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date" style="width:100px;">
			<select name="memberd" id="memberd" style="border:0;background:#fff;width:90%;">
				<option value="">대리점선택</option>
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
			</div>
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date" style="width:100px;">
			<select name="membere" id="membere" style="border:0;background:#fff;width:90%;">
				<option value="">영업점선택</option>
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
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date">
				<input type="text" name="mb_6_name" value="<?php echo $mb_6_name; ?>" id="mb_6_name" class="KDC_Input__root__3M8Hf KDC_Input__root__3M8Hf_date" style="width:100px;" placeholder="가맹점명">
			</div>
			<?php /*
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date" style="width:100px;">
			<select name="memberf" id="memberf" style="border:0;background:#fff;width:90%;">
				<option value="">가맹점선택</option>
				<?php
					$sql_g = " select * from g5_member where mb_level = '3' order by mb_nick";
					$result_g = sql_query($sql_g);
					for ($k=0; $row_g=sql_fetch_array($result_g); $k++) {
					?>
					<option value="<?php echo $row_g['mb_id']; ?>" <?php if($memberf == $row_g['mb_id']) { echo "selected"; } ?>><?php echo $row_g['mb_nick']; ?></option>
					<?php
					}
				?>
			</select>
			</div>
			*/ ?>
			<?php } ?>
			<div class="KDC_Tab__root__h2hVQ tab tab_selected type_other"><input type="submit" class="KDC_Tab__text__VzW9X" value="검색" style="background:#444; width:100%; border:0; color:#fff;"></div>
		</form>
	</div>



	<div class="KDC_Row__root__uio5h KDC_Row__responsive__obNwV">
		<div class="KDC_Column__root__NK8XY KDC_Column__flex_1__UcocY">
			<div class="KDC_Section__root__VXHOv">


				<form name="fmember" id="fmember" action="./?p=device_fee_update" method="post" enctype="multipart/form-data">
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

				<table class="KDC_Table__root__Jim4z">
				<thead>
				<tr>
					<th style="width:50px;">번호</th>
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
					<?php if($member['mb_level'] >= 3) { ?>
					<th style="width:12%">가맹점</th>
					<?php } ?>
					<th>터미널ID</th>
					<th>결제타입</th>
					<?php if($member['mb_level'] >= 8) { ?>
					<th>관리</th>
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


					if($row['mb_1_name']) { $mb_1_fee = sprintf('%0.2f', $mb_1_fee); } else { $mb_1_fee = ""; }
					if($row['mb_2_name']) { $mb_2_fee = sprintf('%0.2f', $mb_2_fee); } else { $mb_2_fee = ""; }
					if($row['mb_3_name']) { $mb_3_fee = sprintf('%0.2f', $mb_3_fee); } else { $mb_3_fee = ""; }
					if($row['mb_4_name']) { $mb_4_fee = sprintf('%0.2f', $mb_4_fee); } else { $mb_4_fee = ""; }
					if($row['mb_5_name']) { $mb_5_fee = sprintf('%0.2f', $mb_5_fee); } else { $mb_5_fee = ""; }
					if($row['mb_6_name']) { $mb_6_fee = sprintf('%0.2f', $mb_6_fee); } else { $mb_6_fee = ""; }

					if($dv_id == $row['dv_id']) {
				?>
				<tr style="background:<?php if($results == "ok") { echo "#c2c2ff"; } else { echo "#ffff99"; } ?>;">
					<td><?php echo $num; ?></td>
					<?php if($member['mb_level'] >= 8) { ?>
					<td class="td_name">
						<?php if($row['mb_1_name']) { ?>
							<?php echo $row['mb_1_name']; ?><br>
							기존 : <span class="has-text-link-dark has-text-weight-semibold"><?php echo $row['mb_1_fee']; ?></span><br>
							변경 : <input type="text" autocomplete="off" name="mb_1_fee" value="<?php echo $row['mb_1_fee']; ?>" required style="width:50px; padding:0 3px; border:1px solid #ccc;" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '');">
						<?php } else { ?>
							-
						<?php } ?>
					</td>
					<?php } ?>
					<?php if($member['mb_level'] >= 7) { ?>
					<td class="td_name">
						<?php if($row['mb_2_name']) { ?>
							<?php echo $row['mb_2_name']; ?><br>
							기존 : <span class="has-text-link-dark has-text-weight-semibold"><?php echo $row['mb_2_fee']; ?></span><br>
							변경 : <input type="text" autocomplete="off" name="mb_2_fee" value="<?php echo $row['mb_2_fee']; ?>" required style="width:50px; padding:0 3px; border:1px solid #ccc;" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '');">
						<?php } else { ?>
							-
						<?php } ?>
					</td>
					<?php } ?>
					<?php if($member['mb_level'] >= 6) { ?>
					<td class="td_name">
						<?php if($row['mb_3_name']) { ?>
							<?php echo $row['mb_3_name']; ?><br>
							기존 : <span class="has-text-link-dark has-text-weight-semibold"><?php echo $row['mb_3_fee']; ?></span><br>
							변경 : <input type="text" autocomplete="off" name="mb_3_fee" value="<?php echo $row['mb_3_fee']; ?>" required style="width:50px; padding:0 3px; border:1px solid #ccc;" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '');">
						<?php } else { ?>
							-
						<?php } ?>
					</td>
					<?php } ?>
					<?php if($member['mb_level'] >= 5) { ?>
					<td class="td_name">
						<?php if($row['mb_4_name']) { ?>
							<?php echo $row['mb_4_name']; ?><br>
							기존 : <span class="has-text-link-dark has-text-weight-semibold"><?php echo $row['mb_4_fee']; ?></span><br>
							변경 : <input type="text" autocomplete="off" name="mb_4_fee" value="<?php echo $row['mb_4_fee']; ?>" required style="width:50px; padding:0 3px; border:1px solid #ccc;" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '');">
						<?php } else { ?>
							-
						<?php } ?>
					</td>
					<?php } ?>
					<?php if($member['mb_level'] >= 4) { ?>
					<td class="td_name">
						<?php if($row['mb_5_name']) { ?>
							<?php echo $row['mb_5_name']; ?><br>
							기존 : <span class="has-text-link-dark has-text-weight-semibold"><?php echo $row['mb_5_fee']; ?></span><br>
							변경 : <input type="text" autocomplete="off" name="mb_5_fee" value="<?php echo $row['mb_5_fee']; ?>" required style="width:50px; padding:0 3px; border:1px solid #ccc;" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '');">
						<?php } else { ?>
							-
						<?php } ?>
					</td>
					<?php } ?>
					<?php if($member['mb_level'] >= 3) { ?>
					<td class="td_name">
						<?php if($row['mb_6_name']) { ?>
							<?php echo $row['mb_6_name']; ?><br>
							기존 : <span class="has-text-link-dark has-text-weight-semibold"><?php echo $row['mb_6_fee']; ?></span><br>
							변경 : <input type="text" autocomplete="off" name="mb_6_fee" value="<?php echo $row['mb_6_fee']; ?>" required style="width:50px; padding:0 3px; border:1px solid #ccc;" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '');">
						<?php } else { ?>
							-
						<?php } ?>
					</td>
					<?php } ?>
					<td><?php echo $row['dv_tid']; ?></td>
					<td>
						<select name="dv_type" required class="required">
							<option value="">결제종류</option>
							<option value="1" <?php if($row['dv_type'] == "1") { echo "selected"; } ?>>단말기</option>
							<option value="2" <?php if($row['dv_type'] == "2") { echo "selected"; } ?>>수기</option>
						</select>
						<br>
						<select name="dv_certi" required class="required">
							<option value="">인증/비인증</option>
							<option value="1" <?php if($row['dv_certi'] == "1") { echo "selected"; } ?>>인증</option>
							<option value="2" <?php if($row['dv_certi'] == "2") { echo "selected"; } ?>>비인증</option>
						</select>
					</td>
					<td>
						<button type="submit" class="btn btn_03">저장</button>
					</td>
				</tr>
				<?php } else { ?>
				<tr>
					<td><?php echo $num; ?></td>
					<?php if($member['mb_level'] >= 8) { ?>
					<td class="td_name"><?php if($row['mb_1_name']) { echo $row['mb_1_name']." <div><span class='fee'>".$row['mb_1_fee']."%</span> <span class='fee2'>".$mb_1_fee."%</span></div>"; } else { echo "-"; } ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 7) { ?>
					<td class="td_name"><?php if($row['mb_2_name']) { echo $row['mb_2_name']." <div><span class='fee'>".$row['mb_2_fee']."%</span> <span class='fee2'>".$mb_2_fee."%</span></div>"; } else { echo "-"; } ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 6) { ?>
					<td class="td_name"><?php if($row['mb_3_name']) { echo $row['mb_3_name']." <div><span class='fee'>".$row['mb_3_fee']."%</span> <span class='fee2'>".$mb_3_fee."%</span></div>"; } else { echo "-"; } ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 5) { ?>
					<td class="td_name"><?php if($row['mb_4_name']) { echo $row['mb_4_name']." <div><span class='fee'>".$row['mb_4_fee']."%</span> <span class='fee2'>".$mb_4_fee."%</span></div>"; } else { echo "-"; } ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 4) { ?>
					<td class="td_name"><?php if($row['mb_5_name']) { echo $row['mb_5_name']." <div><span class='fee'>".$row['mb_5_fee']."%</span> <span class='fee2'>".$mb_5_fee."%</span></div>"; } else { echo "-"; } ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 3) { ?>
					<td class="td_name"><?php if($row['mb_6_name']) { echo $row['mb_6_name']." <div><span class='fee'>".$row['mb_6_fee']."%</span> <span class='fee2'>".$mb_6_fee."%</span></div>"; } else { echo "-"; } ?></td>
					<?php } ?>
					<td><?php echo $row['dv_tid']; ?></td>
					<td><?php echo $dv_types." ".$dv_certis; ?></td>
					<?php if($member['mb_level'] >= 3) { ?>
					<td class="is-actions-cell">
						<div class="buttons">
							<a href="./?p=device_fee&membera=<?php echo $membera; ?>&memberb=<?php echo $memberb; ?>&memberc=<?php echo $memberc; ?>&memberd=<?php echo $memberd; ?>&membere=<?php echo $membere; ?>&memberf=<?php echo $memberf; ?>&mb_nick=<?php echo $mb_nick; ?>&dv_tid=<?php echo $dv_tid; ?>&dv_id=<?php echo $row['dv_id']; ?>&page_count=<?php echo $page_count; ?>&page=<?php echo $page; ?>" class="KDC_Button__root__N26ep KDC_Button__mini__Sy8ka KDC_Button__color_cancel__TdcOV">수수료수정</a>

							<a href="./?p=member_device&mb_id=<?php echo $row['mb_6']; ?>&dv_id=<?php echo $row['dv_id']; ?>" class="KDC_Button__root__N26ep KDC_Button__mini__Sy8ka KDC_Button__color_delete__bAeWl">단말기수정</a>

							<a href="./?p=device_fee_delete&dv_id=<?php echo $row['dv_id']; ?>&page_count=<?php echo $page_count; ?>&page=<?php echo $page; ?>" class="KDC_Button__root__N26ep KDC_Button__mini__Sy8ka KDC_Button__color_special__CUcY7">삭제</a>
						</div>
					</td>
					<?php } ?>
				</tr>
				<?php } } ?>
				</tbody>
				</table>
				</form>
			</div>
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
				$qstr .= "&dv_type=".$dv_type;
				$qstr .= "&dv_certi=".$dv_certi;
				$qstr .= "&page_count=".$page_count;
				echo get_paging_news(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page=');
			?>
		</div>
	</div>
</div>


<script>
	$("#membera, #memberb, #memberc, #memberd, #membere, #memberf").click(function() {
		$("#membera, #memberb, #memberc, #memberd, #membere, #memberf").find('option:first').attr('selected', 'selected');
	});

	$("#membera, #memberb, #memberc, #memberd, #membere, #memberf").change(function() {
		$("#membera, #memberb, #memberc, #memberd, #membere, #memberf").val();
		$(this).parents().filter("form").submit();
	});
</script>


<?php
	include_once("./_tail.php");
?>