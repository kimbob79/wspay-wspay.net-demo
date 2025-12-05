<?php
	include_once('./_common.php');

//	$config['cf_6'] = 5000000;
	/*
	if($member['mb_7'] <> 1) {
		alert_close("권한이 없습니다.");
	}
	*/
	$g5['title'] = "회원관리";
	$bo_table = "member";
	include_once(G5_PATH.'/head.php');
	/*
	if($is_admin) {
		$payerName = "홍길동";
		$payerTel2 = "8350";
		$payerTel3 = "3122";
		$number = 9425207804101664;
		$expiry2 = "04";
		$expiry1 = "24";
		$installment = "A";
		$pd_name = "테스트";
		$pd_price = 1004;
	}
	*/

	$mb_pass = rand(11111,999999);

	if($mb_id) {
		$mb = get_member($mb_id);
		$mb_nick = $mb['mb_nick'];
		$mb_name = $mb['mb_name'];
		$mb_hp =explode('-' , $mb['mb_hp']);
		if(!$mb['mb_id']) {
			alert("잘못된 접근입니다.", G5_URL);
		}
		if(!$is_admin) {
			if($member['mb_id'] != $mb['mb_id']) {
				alert("잘못된 접근입니다.", G5_URL);
			}
		}
	}
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<style>
.title {color:#777; font-size:0.8em}
</style>

<section id="bo_w">
	<div class="tbl_fbasic">
		<form name="fmember" id="fmember" action="./update.php" onsubmit="return fmember_submit(this);" method="post" enctype="multipart/form-data">
		<input type="hidden" name="w" value="<?php echo $w ?>">
		<?php if(!$is_admin) { ?>
		<input type="hidden" name="mb_10" value="<?php echo $mb['mb_10'] ?>">
		<input type="hidden" name="mb_6" value="<?php echo $mb['mb_6'] ?>">
		<input type="hidden" name="mb_7" value="<?php echo $mb['mb_7'] ?>">
		<input type="hidden" name="mb_8" value="<?php echo $mb['mb_8'] ?>">

		<input type="hidden" name="mb_2" value="<?php echo $mb['mb_2'] ?>">
		<input type="hidden" name="mb_3" value="<?php echo $mb['mb_3'] ?>">
		<input type="hidden" name="mb_4" value="<?php echo $mb['mb_4'] ?>">
		<input type="hidden" name="mb_5" value="<?php echo $mb['mb_5'] ?>">
		<?php } ?>
			<table class="tbl_form1" style="width:100%;">
				<tbody>
					<?php if($is_admin) { ?>
					<tr>
						<td class="title">그룹 선택</td>
					</tr>
					<tr>
						<td>
							<select name="mb_10" id="mb_10" class="frm_input">
								<option value="1" <?php if($mb['mb_10'] == "1") { echo "selected"; } ?>>1번 그룹</option>
								<option value="2" <?php if($mb['mb_10'] == "2") { echo "selected"; } ?>>2번 그룹</option>
								<option value="3" <?php if($mb['mb_10'] == "3") { echo "selected"; } ?>>3번 그룹</option>
								<option value="4" <?php if($mb['mb_10'] == "4") { echo "selected"; } ?>>4번 그룹</option>
								<option value="5" <?php if($mb['mb_10'] == "5") { echo "selected"; } ?>>5번 그룹</option>
								<option value="6" <?php if($mb['mb_10'] == "6") { echo "selected"; } ?>>6번 그룹</option>
								<option value="7" <?php if($mb['mb_10'] == "7") { echo "selected"; } ?>>7번 그룹</option>
								<option value="8" <?php if($mb['mb_10'] == "8") { echo "selected"; } ?>>8번 그룹</option>
								<option value="9" <?php if($mb['mb_10'] == "9") { echo "selected"; } ?>>9번 그룹</option>
							</select>
							 ※ 낮은 그룹부터 상위 노출
						</td>
					</tr>
					<tr><td style="height:5px"></td></tr>
					<tr>
						<td class="title">취소 가능유므</td>
					</tr>
					<tr>
						<td>
							<select name="mb_adult" id="mb_adult" class="frm_input">
								<option value="0" <?php if($mb['mb_adult'] == "0") { echo "selected"; } ?>>가능 가능</option>
								<option value="1" <?php if($mb['mb_adult'] == "1") { echo "selected"; } ?>>취소 불가</option>
							</select>
						</td>
					</tr>
					<tr><td style="height:5px"></td></tr>
					<tr>
						<td class="title">결제</td>
					</tr>
					<tr>
						<td>
							<label><input type="checkbox" name="mb_6" value="1" <?php if($mb['mb_6'] == "1") { echo "checked"; } ?>> 광원(최대 <?php echo number_format(substr($config['cf_1_subj'],0,-4)); ?>만원 / <?php echo $config['cf_1']; ?>개월) 결제가능</label><br>
							<label><input type="checkbox" name="mb_15" value="1" <?php if($mb['mb_15'] == "1") { echo "checked"; } ?>> 광원 비인증 (최대 <?php echo number_format(substr($config['cf_5_subj'],0,-4)); ?>만원 / <?php echo $config['cf_5']; ?>개월) 결제가능</label><br>
							<label><input type="checkbox" name="mb_7" value="1" <?php if($mb['mb_7'] == "1") { echo "checked"; } ?>> 다날(최대 <?php echo number_format(substr($config['cf_2_subj'],0,-4)); ?>만원 / <?php echo $config['cf_2']; ?>개월) 결제가능</label><br>
							<label><input type="checkbox" name="mb_8" value="1" <?php if($mb['mb_8'] == "1") { echo "checked"; } ?>> 웰컴(최대 <?php echo number_format(substr($config['cf_3_subj'],0,-4)); ?>만원 / <?php echo $config['cf_3']; ?>개월) 결제가능</label><br>
							<label><input type="checkbox" name="mb_9" value="1" <?php if($mb['mb_9'] == "1") { echo "checked"; } ?>> 페이시스(최대 <?php echo number_format(substr($config['cf_4_subj'],0,-4)); ?>만원 / <?php echo $config['cf_4']; ?>개월) 결제가능</label><br>
							<label><input type="checkbox" name="mb_20" value="1" <?php if($mb['mb_20'] == "1") { echo "checked"; } ?>> 섹타나인(최대 <?php echo number_format(substr($config['cf_6_subj'],0,-4)); ?>만원 / <?php echo $config['cf_6']; ?>개월) 결제가능</label><br>
							<label><input type="checkbox" name="mb_21" value="1" <?php if($mb['mb_21'] == "1") { echo "checked"; } ?>> 섹타나인 비인증(최대 <?php echo number_format(substr($config['cf_8_subj'],0,-4)); ?>만원 / <?php echo $config['cf_8']; ?>개월) 결제가능</label><br>
							<label><input type="checkbox" name="mb_14" value="1" <?php if($mb['mb_14'] == "1") { echo "checked"; } ?>> URL 결제가능</label>
							<?php /*
							<select name="mb_6" id="mb_6" class="frm_input">
								<option value="" <?php if($mb['mb_6'] == "") { echo "selected"; } ?>>인증 비인증 모두 가능</option>
								<option value="b" <?php if($mb['mb_6'] == "b") { echo "selected"; } ?>>비인증 결제만 가능</option>
								<option value="i" <?php if($mb['mb_6'] == "i") { echo "selected"; } ?>>인증 결제만 가능</option>
								<option value="no" <?php if($mb['mb_6'] == "no") { echo "selected"; } ?>>결제불가</option>
							</select>
							*/ ?>
						</td>
					</tr>
					<tr><td style="height:5px"></td></tr>
					<?php } ?>
					<tr>
						<td class="title">업체명</td>
					</tr>
					<tr>
						<td><input type="text" name="mb_nick" id="mb_nick" class="frm_input" maxlength="20" placeholder="업체명" value="<?php echo $mb_nick; ?>" required></td>
					</tr>
					<tr><td style="height:5px"></td></tr>
					<?php if(!$mb_id) { ?>
					<tr>
						<td class="title">아이디</td>
					</tr>
					<tr>
						<td><input type="text" name="mb_id" id="mb_id" class="frm_input" maxlength="20" placeholder="아이디" value="<?php echo time(); ?>" required> ※ 변경가능</td>
					</tr>
					<tr><td style="height:5px"></td></tr>
					<tr>
						<td class="title">비밀번호</td>
					</tr>
					<tr>
						<td><input type="text" name="mb_password" id="mb_password" class="frm_input" maxlength="20" placeholder="비밀번호" value="<?php echo $mb_pass; ?>" required> ※ 변경가능</td>
					</tr>
					<tr><td style="height:5px"></td></tr>
					<?php } else { ?>
					<tr>
						<td class="title">비밀번호</td>
					</tr>
					<tr>
						<td>
							<input type="hidden" name="mb_id" id="mb_id" class="frm_input" maxlength="20" placeholder="아이디" value="<?php echo $mb['mb_id']; ?>" required>
							<input type="text" name="mb_password" id="mb_password" class="frm_input" maxlength="20" placeholder="비밀번호"> ※ 입력시 변경됩니다.
						</td>
					</tr>
					<tr><td style="height:5px"></td></tr>
					<?php } ?>
					<tr>
						<td class="title">담당자</td>
					</tr>
					<tr>
						<td><input type="text" name="mb_name" id="mb_name" class="frm_input" maxlength="20" placeholder="담당자명" value="<?php echo $mb_name; ?>" required></td>
					</tr>
					<tr><td style="height:5px"></td></tr>
					<tr>
						<td class="title">담당자 휴대전화번호</td>
					</tr>
					<tr>
						<td>
							<select name="mb_hp1" id="mb_hp1" class="frm_input" style="width:30%;">
								<option value="010" <?php if($mb_hp[0] == "010") { echo "selected"; } ?>>010</option>
								<option value="011" <?php if($mb_hp[0] == "011") { echo "selected"; } ?>>011</option>
								<option value="017" <?php if($mb_hp[0] == "017") { echo "selected"; } ?>>017</option>
								<option value="018" <?php if($mb_hp[0] == "018") { echo "selected"; } ?>>018</option>
								<option value="019" <?php if($mb_hp[0] == "019") { echo "selected"; } ?>>019</option>
							</select>
							<input type="number" name="mb_hp2" id="mb_hp2" class="frm_input" maxlength="4" size="6" placeholder="휴대폰" oninput="maxLengthCheck(this)" pattern="\d*" placeholder="number" style="width:30%;" value="<?php echo $mb_hp[1]; ?>" required>
							<input type="number" name="mb_hp3" id="mb_hp3" class="frm_input" maxlength="4" size="6" placeholder="번호" oninput="maxLengthCheck(this)" pattern="\d*" placeholder="number" style="width:30%;" value="<?php echo $mb_hp[1]; ?>" required>
						</td>
					</tr>
					<?php if($is_admin) { ?>
					<tr><td style="height:5px"></td></tr>
					<tr>
						<td class="title">광원 인증 TID/KEY</td>
					</tr>
					<tr>
						<td><input type="text" name="mb_2" id="mb_2" class="frm_input" placeholder="광원 인증 TID" value="<?php echo $mb['mb_2']; ?>" style="width:100%;"></td>
					</tr>
					<tr>
						<td><input type="text" name="mb_3" id="mb_3" class="frm_input" placeholder="광원 인증 KEY" value="<?php echo $mb['mb_3']; ?>" style="width:100%;"></td>
					</tr>
					<tr><td style="height:5px"></td></tr>
					<tr>
						<td class="title">광원 비인증 TID/KEY</td>
					</tr>
					<tr>
						<td><input type="text" name="mb_16" id="mb_16" class="frm_input" placeholder="광원 비인증 TID" value="<?php echo $mb['mb_16']; ?>" style="width:100%;"></td>
					</tr>
					<tr>
						<td><input type="text" name="mb_17" id="mb_17" class="frm_input" placeholder="광원 비인증 KEY" value="<?php echo $mb['mb_17']; ?>" style="width:100%;"></td>
					</tr>
					<tr><td style="height:5px"></td></tr>
					<tr>
						<td class="title">다날 TID/KEY</td>
					</tr>
					<tr>
						<td><input type="text" name="mb_4" id="mb_4" class="frm_input" placeholder="다날 TID" value="<?php echo $mb['mb_4']; ?>" style="width:100%;"></td>
					</tr>
					<tr>
						<td><input type="text" name="mb_5" id="mb_5" class="frm_input" placeholder="다날 KEY" value="<?php echo $mb['mb_5']; ?>" style="width:100%;"></td>
					</tr>
					<tr><td style="height:5px"></td></tr>
					<tr>
						<td class="title">페이시스 TID/MBR/KEY</td>
					</tr>
					<tr>
						<td><input type="text" name="mb_11" id="mb_11" class="frm_input" placeholder="페이시스 TID" value="<?php echo $mb['mb_11']; ?>" style="width:100%;"></td>
					</tr>
					<tr>
						<td><input type="text" name="mb_12" id="mb_12" class="frm_input" placeholder="페이시스 MBR" value="<?php echo $mb['mb_12']; ?>" style="width:100%;"></td>
					</tr>
					<tr>
						<td><input type="text" name="mb_13" id="mb_13" class="frm_input" placeholder="페이시스 KEY" value="<?php echo $mb['mb_13']; ?>" style="width:100%;"></td>
					</tr>
					<tr><td style="height:5px"></td></tr>
					<tr>
						<td class="title">섹타나인 MBR/KEY</td>
					</tr>
					<tr>
						<td><input type="text" name="mb_18" id="mb_18" class="frm_input" placeholder="섹타나인 MBR" value="<?php echo $mb['mb_18']; ?>" style="width:100%;"></td>
					</tr>
					<tr>
						<td><input type="text" name="mb_19" id="mb_19" class="frm_input" placeholder="섹타나인 KEY" value="<?php echo $mb['mb_19']; ?>" style="width:100%;"></td>
					</tr>
					<tr>
						<td class="title">섹타나인 비인증 MBR/KEY</td>
					</tr>
					<tr>
						<td><input type="text" name="mb_22" id="mb_22" class="frm_input" placeholder="섹타나인 MBR" value="<?php echo $mb['mb_22']; ?>" style="width:100%;"></td>
					</tr>
					<tr>
						<td><input type="text" name="mb_23" id="mb_23" class="frm_input" placeholder="섹타나인 KEY" value="<?php echo $mb['mb_23']; ?>" style="width:100%;"></td>
					</tr>
					<?php } ?>
					<tr><td style="height:5px"></td></tr>
					<tr>
						<td><input type="submit" value="<?php if($mb_id) { ?>정보수정<?php } else { ?>회원등록<?php } ?>" class="btn_submit" accesskey='s'></td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>

</section>






<script>

function fmember_submit(f) {
	return true;
}



function getPage() {
//	alert("제작중입니다.");

	/*
	$("#btn_submit").hide();
	var mb_id = $("#mb_id").val();
	var payerName = $("#payerName").val();
	var payerEmail = $("#payerEmail").val();
	var payerTel1 = $("#payerTel1").val();
	var payerTel2 = $("#payerTel2").val();
	var payerTel3 = $("#payerTel3").val();
	var number = $("#number").val();
	var expiry2 = $("#expiry2").val();
	var expiry1 = $("#expiry1").val();
	var installment = $("#installment").val();
	var pd_name = $("#pd_name").val();
	var pd_price = $("#pd_price").val();


	if (payerName == "") {
		alert("주문자명 필수입력 입니다.");
		$("#btn_submit").show();
		$("#payerName").focus();
		return;
	}
	if (payerEmail == "") {
		alert("주문자 이메일 필수입력 입니다.");
		$("#btn_submit").show();
		$("#payerEmail").focus();
		return;
	}

	if (payerTel1 == "") {
		alert("주문자 휴대폰번호 필수입력 입니다..");
		$("#btn_submit").show();
		$("#payerTel1").focus();
		return;
	}

	if (payerTel2 == "") {
		alert("주문자 휴대폰번호 필수입력 입니다..");
		$("#btn_submit").show();
		$("#payerTel2").focus();
		return;
	}

	if (payerTel3 == "") {
		alert("주문자 휴대폰번호 필수입력 입니다..");
		$("#btn_submit").show();
		$("#payerTel3").focus();
		return;
	}

	if (number == "") {
		alert("카드번호 필수입력 입니다.");
		$("#btn_submit").show();
		$("#number").focus();
		return;
	}

	if (expiry2 == "") {
		alert("유효기간 년도 필수입력 입니다.");
		$("#btn_submit").show();
		$("#expiry2").focus();
		return;
	}

	if (expiry1 == "") {
		alert("유효기간 월 필수입력 입니다.");
		$("#btn_submit").show();
		$("#expiry1").focus();
		return;
	}

	if (installment == "") {
		alert("할부개월 필수입력 선택 입니다.");
		$("#btn_submit").show();
		$("#installment").focus();
		return;
	}

	if (pd_name == "") {
		alert("상품명 필수입력 입니다.");
		$("#btn_submit").show();
		$("#pd_name").focus();
		return;
	}

	if (pd_price == "") {
		alert("상품금액 필수입력 입니다.");
		$("#btn_submit").show();
		$("#pd_price").focus();
		return;

	} else if(fn(pd_price) > <?php echo $config['cf_6']; ?> ){
		alert("<?php echo number_format($config['cf_6']); ?>원 이하 결제가능합니다.");
		$("#btn_submit").show();
		$("#pd_price").focus();
		return;
	}

	if($('#agree').is(':checked') == false) {
		alert('주의사항에 동의하셔야만 결제 가능합니다.');
		$("#btn_submit").show();
		return;
	}

	$('#mask').show();
	$('.layer').show();

	$.ajax({
		url: "pay_update.php",
		type: "POST",
		async:'false',
		data: {
				'mb_id' : mb_id,
				'payerName' : payerName,
				'payerEmail' : payerEmail,
				'payerTel1' : payerTel1,
				'payerTel2' : payerTel2,
				'payerTel3' : payerTel3,
				'number' : number,
				'expiry2' : expiry2,
				'expiry1' : expiry1,
				'installment' : installment,
				'pd_name' : pd_name,
				'pd_price' : pd_price
		},
		success:function(data) {
			$('#mask').hide();
			$('.layer').hide();
			if(data == "정상승인") {
				alert(data);
				document.location.replace(g5_url + "/passgo/list.php");
			} else {
				alert(data);
				$("#btn_submit").show();
			}
//			window.close();
		},
		error:function() {
			alert('error');
		}
	});
	return false;
	*/
}

</script>
<?php
	include_once(G5_PATH.'/tail.php');