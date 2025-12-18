<?php
	if(!$is_admin) { alert("관리자만 접속 가능합니다."); }

	if(!$mb_id && !$dv_id) {
		alert("가맹점 관리에서 접근해주세요");
	}

	$mb = get_member($mb_id);

	if($dv_id) {
		$row = sql_fetch(" select * from g5_device where dv_id = '{$dv_id}' ");
		$mb = get_member($row['mb_6']);
		$mb['mb_nick'] = $row['mb_6_name'];

		if($row['mb_1']) {
			$mb['mb_1'] = $row['mb_1'];
			$mb1['mb_id'] = $row['mb_1'];
			$mb1['mb_nick'] = $row['mb_1_name'];
			$mb1['mb_1_fee'] = $row['mb_1_fee'];
		}
		if($row['mb_2']) {
			$mb['mb_2'] = $row['mb_2'];
			$mb2['mb_id'] = $row['mb_2'];
			$mb2['mb_nick'] = $row['mb_2_name'];
			$mb2['mb_2_fee'] = $row['mb_2_fee'];
		}
		if($row['mb_3']) {
			$mb['mb_3'] = $row['mb_3'];
			$mb3['mb_id'] = $row['mb_3'];
			$mb3['mb_nick'] = $row['mb_3_name'];
			$mb3['mb_3_fee'] = $row['mb_3_fee'];
		}
		if($row['mb_4']) {
			$mb['mb_4'] = $row['mb_4'];
			$mb4['mb_id'] = $row['mb_4'];
			$mb4['mb_nick'] = $row['mb_4_name'];
			$mb4['mb_4_fee'] = $row['mb_4_fee'];
		}
		if($row['mb_5']) {
			$mb['mb_5'] = $row['mb_5'];
			$mb5['mb_id'] = $row['mb_5'];
			$mb5['mb_nick'] = $row['mb_5_name'];
			$mb5['mb_5_fee'] = $row['mb_5_fee'];
		}
		if($row['mb_6']) {
			$mb['mb_6'] = $row['mb_6'];
			$mb6['mb_id'] = $row['mb_6'];
			$mb6['mb_nick'] = $row['mb_6_name'];
			$mb6['mb_6_fee'] = $row['mb_6_fee'];
		}
	}


?>
<form name="fmember" id="fmember" action="./?p=member_device_update" onsubmit="return device_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="dv_id" value="<?php echo $row['dv_id']; ?>">
<input type="hidden" name="dv_type" value="1">
<input type="hidden" name="dv_certi" value="1">
<input type="hidden" name="p_level" value="<?php echo $level; ?>">
<input type="hidden" name="p_mb_nick" value="<?php echo $mb_nick; ?>">
<input type="hidden" name="p_dv_tid" value="<?php echo $dv_tid; ?>">
<input type="hidden" name="p_page" value="<?php echo $page; ?>">

<table class="table_view">
	<caption><?php echo $g5['title']; ?></caption>
	<colgroup>
		<col class="grid_4">
		<col>
	</colgroup>
	<tbody>
		<tr>
			<th scope="row"><label for="mb_password">가맹점<?php echo $sound_only ?></label></th>
			<td>
				<input type="text" value="<?php echo $mb['mb_nick']; ?>" readonly required class="required frm_input" required style="width:200px; margin-bottom:5px;">
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mb_homepage">터미널ID</label></th>
			<td>
				<input type="text" autocomplete="on" name="dv_tid" id="dv_tid" <?php if($dv_id) { echo "readonly"; } ?> value="<?php echo $row['dv_tid']; ?>" required class="required frm_input" required placeholder="TID" style="width:200px; margin-bottom:5px;">
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mb_homepage">PG사</label></th>
			<td>
				<select name="dv_pg" required class="required frm_input">
					<option value="">PG사</option>
					<?php /*
					<option value="0" <?php if($row['dv_pg'] == "0") { echo "selected"; } ?>>코페이</option>
					<option value="1" <?php if($row['dv_pg'] == "1") { echo "selected"; } ?>>다날</option>
					<option value="2" <?php if($row['dv_pg'] == "2") { echo "selected"; } ?>>광원</option>
					<option value="3" <?php if($row['dv_pg'] == "3") { echo "selected"; } ?>>웰컴</option>
					*/ ?>
					<option value="4" <?php if($row['dv_pg'] == "4") { echo "selected"; } ?>>페이시스</option>
					<option value="5" <?php if($row['dv_pg'] == "5") { echo "selected"; } ?>>섹타나인</option>
					<?php /*
					<option value="6" <?php if($row['dv_pg'] == "6") { echo "selected"; } ?>>다우</option>
					*/ ?>
					<option value="7" <?php if($row['dv_pg'] == "7") { echo "selected"; } ?>>루트업</option>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mb_homepage">결제종류</label></th>
			<td>
				<select name="dv_type" required class="required frm_input">
					<option value="">결제종류 선택</option>
					<option value="1" <?php if($row['dv_type'] == "1") { echo "selected"; } ?>>오프라인</option>
					<option value="2" <?php if($row['dv_type'] == "2") { echo "selected"; } ?>>온라인</option>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mb_homepage">인증/비인증</label></th>
			<td>
				<select name="dv_certi" required class="required frm_input">
					<option value="">인증/비인증 선택</option>
					<option value="1" <?php if($row['dv_certi'] == "1") { echo "selected"; } ?>>인증</option>
					<option value="2" <?php if($row['dv_certi'] == "2") { echo "selected"; } ?>>비인증</option>
					<option value="3" <?php if($row['dv_certi'] == "3") { echo "selected"; } ?>>구인증</option>
				</select>
			</td>
		</tr>
		<?php /*
		<tr>
			<th scope="row"><label for="mb_nick">단말기 정보</label></th>
			<td>
				<div style="margin-bottom:10px;">
					<input type="text" autocomplete="on" name="dv_open_date" value="<?php echo $row['dv_open_date']; ?>" class="frm_input" placeholder="개통일자" style="width:300px">
				</div>
				<div style="margin-bottom:10px;">
					<input type="text" autocomplete="on" name="dv_agent" value="<?php echo $row['dv_agent']; ?>" class="frm_input" placeholder="개통명의" style="width:300px">
				</div>
				<div style="margin-bottom:10px;">
					<input type="text" autocomplete="on" name="dv_number" value="<?php echo $row['dv_number']; ?>" class="frm_input" placeholder="개통번호" style="width:300px">
				</div>
				<div style="margin-bottom:10px;">
					<input type="text" autocomplete="on" name="dv_model" value="<?php echo $row['dv_model']; ?>" class="frm_input" placeholder="단말기모델" style="width:300px">
				</div>
				<div style="margin-bottom:10px;">
					<input type="text" autocomplete="on" name="dv_model_number" value="<?php echo $row['dv_model_number']; ?>" class="frm_input" placeholder="단말기 인련번호" style="width:300px">
				</div>
				<div style="margin-bottom:10px;">
					<input type="text" autocomplete="on" name="dv_sn" value="<?php echo $row['dv_sn']; ?>" class="frm_input" placeholder="단말기 시리얼번호" style="width:300px">
				</div>
				<div style="margin-bottom:10px;">
					<input type="text" autocomplete="on" name="dv_usim" value="<?php echo $row['dv_usim']; ?>" class="frm_input" placeholder="유심모델명" style="width:300px">
				</div>
				<div>
					<input type="text" autocomplete="on" name="dv_usim_number" value="<?php echo $row['dv_usim_number']; ?>" class="frm_input" placeholder="유심일련번호" style="width:300px">
				</div>
			</td>
		</tr>
		*/ ?>
		<?php
			if($mb['mb_1']) {
				if(!$row['mb_1']) {
					$mb1 = get_member($mb['mb_1']);
				}
		?>
		<tr>
			<th scope="row"><label for="mb_homepage">본사 수수료</label></th>
			<td>
				<input type="hidden" autocomplete="on" name="mb_1" id="mb_1" value="<?php echo $mb1['mb_id']; ?>" readonly>
				<input type="hidden" autocomplete="on" name="mb_1_name" id="mb_1_name" value="<?php echo $mb1['mb_nick']; ?>" readonly>
				<input type="text" autocomplete="on" name="mb_1_fee" id="mb_1_fee" value="<?php if($dv_id) { echo $row['mb_1_fee']; } else { echo sprintf('%0.3f', $mb1['mb_homepage']); } ?>" class="frm_input" required style="width:100px" maxlength="10" oninput="this.value = this.value.replace(/[^0-9.]/g, '');">
				<span class="icon is-small is-left"><i class="mdi mdi-percent"></i></span>
				<span id="mb_name2" style="line-height:40px; margin-left:10px;"></span>
				<?php
					echo "<strong>".$mb1['mb_nick']."</strong>";
				?>
			</td>
		</tr>
		<?php } ?>

		<?php
			if($mb['mb_2']) {
				if(!$row['mb_2']) {
					$mb2 = get_member($mb['mb_2']);
				}
		?>
		<tr>
			<th scope="row"><label for="mb_homepage">지사 수수료</label></th>
			<td>

				<input type="hidden" autocomplete="on" name="mb_2" id="mb_2" value="<?php echo $mb2['mb_id']; ?>" readonly>
				<input type="hidden" autocomplete="on" name="mb_2_name" id="mb_2_name" value="<?php echo $mb2['mb_nick']; ?>" readonly>
				<input type="text" autocomplete="on" name="mb_2_fee" id="mb_2_fee" value="<?php if($dv_id) { echo $row['mb_2_fee']; } else { echo sprintf('%0.3f', $mb2['mb_homepage']); } ?>" class="frm_input" required style="width:100px" maxlength="10" oninput="this.value = this.value.replace(/[^0-9.]/g, '');">
				<span class="icon is-small is-left"><i class="mdi mdi-percent"></i></span>
				<span id="mb_name3" style="line-height:40px; margin-left:10px;"></span>
				<?php
					echo "<strong>".$mb2['mb_nick']."</strong>";
				?>
			</td>
		</tr>

		<?php } ?>

		<?php
			if($mb['mb_3']) {
				if(!$row['mb_3']) {
					$mb3 = get_member($mb['mb_3']);
				}
		?>
		<tr>
			<th scope="row"><label for="mb_homepage">총판 수수료</label></th>
			<td>

				<input type="hidden" autocomplete="on" name="mb_3" id="mb_3" value="<?php echo $mb3['mb_id']; ?>" readonly>
				<input type="hidden" autocomplete="on" name="mb_3_name" id="mb_3_name" value="<?php echo $mb3['mb_nick']; ?>" readonly>
				<input type="text" autocomplete="on" name="mb_3_fee" id="mb_3_fee" value="<?php if($dv_id) { echo $row['mb_3_fee']; } else { echo sprintf('%0.3f', $mb3['mb_homepage']); } ?>" class="frm_input" required style="width:100px" maxlength="10" oninput="this.value = this.value.replace(/[^0-9.]/g, '');">
				<span class="icon is-small is-left"><i class="mdi mdi-percent"></i></span>
				<span id="mb_name4" style="line-height:40px; margin-left:10px;"></span>
				<?php
					echo "<strong>".$mb3['mb_nick']."</strong>";
				?>
			</td>
		</tr>
		<?php } ?>

		<?php
			if($mb['mb_4']) {
				if(!$row['mb_4']) {
					$mb4 = get_member($mb['mb_4']);
				}
		?>
		<tr>
			<th scope="row"><label for="mb_homepage">대리점 수수료</label></th>
			<td>
				<input type="hidden" autocomplete="on" name="mb_4" id="mb_4" value="<?php echo $mb4['mb_id']; ?>" readonly>
				<input type="hidden" autocomplete="on" name="mb_4_name" id="mb_4_name" value="<?php echo $mb4['mb_nick']; ?>" readonly>
				<input type="text" autocomplete="on" name="mb_4_fee" id="mb_4_fee" value="<?php if($dv_id) { echo $row['mb_4_fee']; } else { echo sprintf('%0.3f', $mb4['mb_homepage']); } ?>" class="frm_input" required style="width:100px" maxlength="10" oninput="this.value = this.value.replace(/[^0-9.]/g, '');">
				<span class="icon is-small is-left"><i class="mdi mdi-percent"></i></span>
				<span id="mb_name5" style="line-height:40px; margin-left:10px;"></span>
				<?php
					echo "<strong>".$mb4['mb_nick']."</strong>";
				?>
			</td>
		</tr>
		<?php } ?>

		<?php
			if($mb['mb_5']) {
				if(!$row['mb_5']) {
					$mb5 = get_member($mb['mb_5']);
				}
		?>
		<tr>
			<th scope="row"><label for="mb_homepage">영업점 수수료</label></th>
			<td>
				<input type="hidden" autocomplete="on" name="mb_5" id="mb_5" value="<?php echo $mb5['mb_id']; ?>" readonly>
				<input type="hidden" autocomplete="on" name="mb_5_name" id="mb_5_name" value="<?php echo $mb5['mb_nick']; ?>" readonly>
				<input type="text" autocomplete="on" name="mb_5_fee" id="mb_5_fee" value="<?php if($dv_id) { echo $row['mb_5_fee']; } else { echo sprintf('%0.3f', $mb5['mb_homepage']); } ?>" class="frm_input" required style="width:100px" maxlength="10" oninput="this.value = this.value.replace(/[^0-9.]/g, '');">
				<span class="icon is-small is-left"><i class="mdi mdi-percent"></i></span>
				<span id="mb_name6" style="line-height:40px; margin-left:10px;"></span>
				<?php
					echo "<strong>".$mb5['mb_nick']."</strong>";
				?>
			</td>
		</tr>
		<?php } ?>

		<?php
			if($mb['mb_6']) {
				if(!$row['mb_6']) {
					$mb6 = get_member($mb['mb_6']);
				}
		?>
		<tr>
			<th scope="row"><label for="mb_homepage">가맹점 수수료</label></th>
			<td>
				<input type="hidden" autocomplete="on" name="mb_6" id="mb_6" value="<?php echo $mb6['mb_id']; ?>" readonly>
				<input type="hidden" autocomplete="on" name="mb_6_name" id="mb_6_name" value="<?php echo $mb6['mb_nick']; ?>" readonly>
				<input type="text" autocomplete="on" name="mb_6_fee" id="mb_6_fee" value="<?php if($dv_id) { echo $row['mb_6_fee']; } else { echo sprintf('%0.3f', $mb6['mb_homepage']); } ?>" class="frm_input" required style="width:100px" maxlength="10" oninput="this.value = this.value.replace(/[^0-9.]/g, '');">
				<span class="icon is-small is-left"><i class="mdi mdi-percent"></i></span>
				<span id="mb_name7" style="line-height:40px; margin-left:10px;"></span>
				<?php
					echo "<strong>".$mb6['mb_nick']."</strong>";
				?>
			</td>
		</tr>
		<?php } ?>
	</tbody>
</table>

<div style="padding:10px 0;">
	<input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>

</form>


<script>
	// submit 최종 폼체크
	function device_submit(f)
	{
		// 회원아이디 검사
		if (f.mb_name.value == "") {
			alert("가맹점을 선택해주세요");
			f.mb_name.select();
			return false;
		}
	}
</script>