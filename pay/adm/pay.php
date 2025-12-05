<?php
$sub_menu = "100101";
require_once './_common.php';

auth_check_menu($auth, $sub_menu, 'r');

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

$g5['title'] = '결제 환경설정';
require_once './admin.head.php';

?>

<form name="fconfigform" id="fconfigform" method="post" onsubmit="return fconfigform_submit(this);">
	<input type="hidden" name="token" value="" id="token">

	<section id="anc_cf_extra">
		<h2 class="h2_frm">기타 수기설정</h2>


		<div class="tbl_frm01">
			<table>
				<thead>
					<tr>
						<th>중복결제</th>
						<th>미정</th>
						<th>미정</th>
					</tr>
				</thead>
				<tbody>
					<?php
						for ($i = 9; $i <= 10; $i++) {
					?>
					<tr>
						<th scope="row"><input type="text" name="cf_<?php echo $i ?>_subj" value="<?php echo get_text($config['cf_' . $i . '_subj']) ?>" id="cf_<?php echo $i ?>_subj" class="frm_input" size="30"></th>
						<td><input type="text" name="cf_<?php echo $i ?>" value="<?php echo get_sanitize_input($config['cf_' . $i]); ?>" id="cf_<?php echo $i ?>" class="frm_input" size="30"></td>
						<td><input type="text" name="cfv_<?php echo $i ?>" value="<?php echo get_sanitize_input($config['cfv_' . $i]); ?>" id="cfv_<?php echo $i ?>" class="frm_input" size="30"></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</section>

	<section id="anc_cf_extra">
		<h2 class="h2_frm">페이시스</h2>
		<div class="tbl_frm01">
			<table>
				<thead>
					<tr>
						<th>API URL
							<div style="font-size:11px; font-weight:normal">
							상용 : https://relay.mainpay.co.kr<br>
							테스트 : https://test-relay.mainpay.co.kr
						</th>
						<th>상점ID</th>
						<th>apiKey</th>
					</tr>
				</thead>
				<tbody>
					<?php
						for ($i = 7; $i <= 7; $i++) {
					?>
					<tr>
						<td>
							<input type="text" name="cf_<?php echo $i ?>_subj" value="<?php echo get_text($config['cf_' . $i . '_subj']) ?>" id="cf_<?php echo $i ?>_subj" class="frm_input" size="30">
						</td>
						<td><input type="text" name="cf_<?php echo $i ?>" value="<?php echo get_sanitize_input($config['cf_' . $i]); ?>" id="cf_<?php echo $i ?>" class="frm_input" size="30"></td>
						<td><input type="text" name="cfv_<?php echo $i ?>" value="<?php echo get_sanitize_input($config['cfv_' . $i]); ?>" id="cfv_<?php echo $i ?>" class="frm_input" size="70"></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</section>

	<section id="anc_cf_extra">
		<h2 class="h2_frm">PG사별 한도 설정</h2>

		<div class="tbl_frm01 tbl_wrap">
			<table>
				<thead>
					<tr>
						<th>PG사</th>
						<th>한도</th>
						<th>할부</th>
						<th>기타</th>
					</tr>
				</thead>
				<tbody>
					<?php
						for ($i = 1; $i <= 6; $i++) {
							if($i == 1) {
								$tits = "광원 인증";
							} else if($i == 2) {
								$tits = "다날";
							} else if($i == 3) {
								$tits = "웰컴";
							} else if($i == 4) {
								$tits = "페이시스";
							} else if($i == 5) {
								$tits = "광원 비인증";
							} else if($i == 6) {
								$tits = "섹타나인";
							} else {
								$tits = "미정";
							}
					?>
					<tr>
						<th scope="row"><?php echo $tits; ?></th>
						<td><input type="text" name="cf_<?php echo $i ?>_subj" value="<?php echo get_text($config['cf_' . $i . '_subj']) ?>" id="cf_<?php echo $i ?>_subj" class="frm_input" size="30"> 원</td>
						<td><input type="text" name="cf_<?php echo $i ?>" value="<?php echo get_sanitize_input($config['cf_' . $i]); ?>" id="cf_<?php echo $i ?>" class="frm_input" size="30"> 개월</td>
						<td><input type="text" name="cfv_<?php echo $i ?>" value="<?php echo get_sanitize_input($config['cfv_' . $i]); ?>" id="cfv_<?php echo $i ?>" class="frm_input" size="30"></td>
					</tr>
					<?php } ?>
					<tr>
						<th scope="row">섹타나인 비인증</th>
						<td>
							<input type="text" name="cf_8_subj" value="<?php echo get_text($config['cf_8_subj']) ?>" id="cf_8_subj" class="frm_input" size="30">
						</td>
						<td><input type="text" name="cf_8" value="<?php echo get_sanitize_input($config['cf_8']); ?>" id="cf_8" class="frm_input" size="30"></td>
						<td><input type="text" name="cfv_8" value="<?php echo get_sanitize_input($config['cfv_8']); ?>" id="cfv_8" class="frm_input" size="30"></td>
					</tr>
				</tbody>
			</table>
		</div>
	</section>


	<section id="anc_cf_key">
		<h2 class="h2_frm">웰컴 API key 설정</h2>

		<div class="tbl_frm01 tbl_wrap">
			<table>
				<colgroup>
					<col style="width:200px">
					<col>
				</colgroup>
				<thead>
					<tr>
						<th>회원그룹</th>
						<th>상점ID</th>
						<th>apiKey </th>
						<th>ivValue </th>
					</tr>
				</thead>
				<tbody>
					<?php for ($i = 11; $i <= 19; $i++) { ?>
					<tr>
						<th scope="row">회원그룹 <?php echo $i-10 ?></th>
						<td><input type="text" name="cf_<?php echo $i ?>_subj" value="<?php echo get_text($config['cf_' . $i . '_subj']) ?>" id="cf_<?php echo $i ?>_subj" class="frm_input" size="20"></td>
						<td><input type="text" name="cf_<?php echo $i ?>" value="<?php echo get_sanitize_input($config['cf_' . $i]); ?>" id="cf_<?php echo $i ?>" class="frm_input" size="40"></td>
						<td><input type="text" name="cfv_<?php echo $i ?>" value="<?php echo get_sanitize_input($config['cfv_' . $i]); ?>" id="cfv_<?php echo $i ?>" class="frm_input" size="40"></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</section>

	<div class="btn_fixed_top btn_confirm">
		<input type="submit" value="확인" class="btn_submit btn" accesskey="s">
	</div>

</form>

<script>
    function fconfigform_submit(f) {

        f.action = "./pay_update.php";
        return true;
    }
</script>


<?php

require_once './admin.tail.php';
