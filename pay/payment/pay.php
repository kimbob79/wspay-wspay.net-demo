<?php
	include_once('./_common.php');

	if($member['mb_6'] == "no") {
		alert("결제 권한이 없습니다.");
	}

	$g5['title'] = "수기결제";
	$bo_table = "pay";
	include_once(G5_PATH.'/head.php');
	/*
	if($is_admin) {
		$payerName = "테스트";
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

	$k1_hal = '<option value="">할부선택</option><option value="0">일시불</option>';
	for($i=2; $i<=$config['cf_1']; $i++)
	{
		$k1_hal .= '<option value="'.$i.'">'.$i.'개월</option>';
	}

	$k1b_hal = '<option value="">할부선택</option><option value="0">일시불</option>';
	for($i=2; $i<=$config['cf_5']; $i++)
	{
		$k1b_hal .= '<option value="'.$i.'">'.$i.'개월</option>';
	}

	$danal_hal = '<option value="">할부선택</option><option value="0">일시불</option>';
	for($i=2; $i<=$config['cf_2']; $i++)
	{
		$danal_hal .= '<option value="'.$i.'">'.$i.'개월</option>';
	}

	$welcom_hal = '<option value="">할부선택</option><option value="0">일시불</option>';
	for($i=2; $i<=$config['cf_3']; $i++)
	{
		$welcom_hal .= '<option value="'.$i.'">'.$i.'개월</option>';
	}

	$paysis_hal = '<option value="">할부선택</option><option value="0">일시불</option>';
	for($i=2; $i<=$config['cf_4']; $i++)
	{
		$paysis_hal .= '<option value="'.$i.'">'.$i.'개월</option>';
	}

	$stn_hal = '<option value="">할부선택</option><option value="0">일시불</option>';
	for($i=2; $i<=$config['cf_6']; $i++)
	{
		$stn_hal .= '<option value="'.$i.'">'.$i.'개월</option>';
	}

	$stnb_hal = '<option value="">할부선택</option><option value="0">일시불</option>';
	for($i=2; $i<=$config['cf_6']; $i++)
	{
		$stnb_hal .= '<option value="'.$i.'">'.$i.'개월</option>';
	}
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<style>
.wrap-loading{ /*화면 전체를 어둡게 합니다.*/
	position: fixed;
	left:0;
	right:0;
	top:0;
	bottom:0;
	background: #fff;    /* ie */
}
.wrap-loading div{ /*로딩 이미지*/
	position: fixed;
	top:50%;
	left:50%;
	margin-left: -100px;
	margin-top: -205px;
}
.display-none{ /*감추기*/
	display:none;
}
</style>
<div class="wrap-loading display-none">
	<div><img src="./img/loading2.gif" width="200" height="411"></div>
</div>   
<section id="bo_w">
	<input type="hidden" name="mb_id" id="mb_id" value="<?php echo $member['mb_id']; ?>">
	<input type="hidden" name="payerEmail" value="<?php echo $member['mb_email']; ?>" id="payerEmail" class="frm_input"  placeholder="카드주 이메일">
	<div class="tbl_fbasic">

		<div style="text-align:center; line-height:30px; background:red; color:#fff; font-size:1em; padding:20px; 0; margin-bottom:10px;">
			로또 , 주식관련 절대 결제금지!!<br>
			무조건 강제취소 됩니다.
		</div>
		<table class="tbl_form1" style="width:100%;border-collapse: collapse; border-spacing: 0 5px;">
			<tbody>
				<tr><td style="height:8px"></td></tr>
				<tr><td style="font-size:11px; color:#777;">결제타입</td></tr>
				<tr><td style="height:4px"></td></tr>
				<tr>
					<td>
						<select name="payments" id="payments" class="frm_input" style="width:100%;">
							<option value="">결제타입 선택</option>
							<?php if($member['mb_6'] == "1") { ?>
							<option value="k1">광원 - 최대 <?php echo number_format(substr($config['cf_1_subj'],0,-4)); ?>만원 / <?php echo $config['cf_1']; ?>개월</option>
							<?php } ?>
							<?php if($member['mb_15'] == "1") { ?>
							<option value="k1b">광원비인증 - 최대 <?php echo number_format(substr($config['cf_5_subj'],0,-4)); ?>만원 / <?php echo $config['cf_5']; ?>개월</option>
							<?php } ?>
							<?php if($member['mb_7'] == "1") { ?>
							<option value="danal">다날 - 최대 <?php echo number_format(substr($config['cf_2_subj'],0,-4)); ?>만원 / <?php echo $config['cf_2']; ?>개월</option>
							<?php } ?>
							<?php if($member['mb_8'] == "1") { ?>
							<option value="welcom">웰컴 - 최대 <?php echo number_format(substr($config['cf_3_subj'],0,-4)); ?>만원 / <?php echo $config['cf_3']; ?>개월</option>
							<?php } ?>
							<?php if($member['mb_9'] == "1") { ?>
							<option value="paysis">페이시스 - 최대 <?php echo number_format(substr($config['cf_4_subj'],0,-4)); ?>만원 / <?php echo $config['cf_4']; ?>개월</option>
							<?php } ?>
							<?php if($member['mb_20'] == "1") { ?>
							<option value="stn">섹타나인 - 최대 <?php echo number_format(substr($config['cf_6_subj'],0,-4)); ?>만원 / <?php echo $config['cf_6']; ?>개월</option>
							<?php } ?>
							<?php if($member['mb_21'] == "1") { ?>
							<option value="stnb">섹타나인 비인증 - 최대 <?php echo number_format(substr($config['cf_8_subj'],0,-4)); ?>만원 / <?php echo $config['cf_8']; ?>개월</option>
							<?php } ?>
						</select>
						<input type="hidden" name="cardAuth" id="cardAuth" value="true">
					</td>
				</tr>
				<tr>
					<td id="payment_open" style="display:none">
					<table style="width:100%;border-collapse: collapse; border-spacing: 0 5px;">
						<tr><td style="height:8px"></td></tr>
						<tr><td style="font-size:11px; color:#777;">상품명</td></tr>
						<tr><td style="height:4px"></td></tr>
						<tr>
							<td>
							<?php /*
								<?php
									if($member['mb_id'] == "zzzz") { // 라임플러스
										$pd_name_title0 = "라임플러스";
										$pd_name_title1 = "도매 및 소매업";
										$pd_name_title2 = "잡화";
									} else if($member['mb_id'] == "bbbb") { // 시스템에어컨
										$pd_name_title0 = "우리 씨스템 에어콘";
										$pd_name_title1 = "도소매 서비스업";
										$pd_name_title2 = "에어컨 판매 및 설치";
									} else if($member['mb_id'] == "kkkk") { // 선환비앤에서
										$pd_name_title0 = "성환비앤에스";
										$pd_name_title1 = "농업";
										$pd_name_title2 = "약초 및 인삼재배";
									} else if($member['mb_id'] == "cccc") { // 원형전기
										$pd_name_title0 = "우리 씨스템 에어콘";
										$pd_name_title1 = "도소매 서비스업";
										$pd_name_title2 = "에어컨 판매 및 설치";
									} else if($member['mb_id'] == "abcd") { // 에스제이케이미디어
										$pd_name_title0 = "우리 씨스템 에어콘";
										$pd_name_title1 = "도소매 서비스업";
										$pd_name_title2 = "에어컨 판매 및 설치";
									} else if($member['mb_id'] == "1660970661") { // 주식회사 케이물류
										$pd_name_title0 = "주식회사 케이물류";
										$pd_name_title1 = "도매 및 소매업";
										$pd_name_title2 = "식자재, 농수산물, 유통업, 잡화";
									} else if($member['mb_id'] == "1660970661") { // 일오삼
										$pd_name_title0 = "일오삼";
										$pd_name_title1 = "소매업";
										$pd_name_title2 = "전자상거래";
									} else if($member['mb_id'] == "1660704878") { // 가곡금속연마
										$pd_name_title0 = "가곡금속연마";
										$pd_name_title1 = "제조업, 건설업";
										$pd_name_title2 = "금속제품, 인테리어공사";
									} else if($member['mb_id'] == "1676353968") { // 가곡금속연마
										$pd_name_title0 = "비트패스";
										$pd_name_title1 = "정보통신업";
										$pd_name_title2 = "응용 소프트웨어 개발 및 공급업";
									} else if($member['mb_id'] == "1677215301") { // 아이디아이엔이
										$pd_name_title0 = "아이디아이엔이";
										$pd_name_title1 = "건설업";
										$pd_name_title2 = "인테리어, 전기공사";
									}
								?>
								<div style="padding:10px; line-height:30px;">
									회사 : <?php echo $pd_name_title0; ?><br>
									업태 : <?php echo $pd_name_title1; ?><br>
									종목 : <?php echo $pd_name_title2; ?><br>
									<strong>업태 및 종목에 맞는 상품명을 입력해주시기 바랍니다.</strong>
								</div>
							*/ ?>
								<input type="text" name="pd_name" value="<?php echo $pd_name; ?>" id="pd_name" class="full_input frm_input" maxlength="50" placeholder="상품명 사업자특성에 맞게 정확히 입력해주세요">
							</td>
						</tr>
						<tr><td style="height:8px"></td></tr>
						<tr><td style="font-size:11px; color:#777;">결제금액</td></tr>
						<tr><td style="height:4px"></td></tr>
						<tr>
							<td><input type="number" name="pd_price" value="<?php echo $pd_price; ?>" id="pd_price" class="frm_input pd_price" placeholder="결제금액" maxlength="8" oninput="maxLengthCheck(this)" style="width:150px;"> 원
							</td>
						</tr>
						<tr><td style="height:8px"></td></tr>
						<tr><td style="font-size:11px; color:#777;">할부선택</td></tr>
						<tr><td style="height:4px"></td></tr>
						<tr>
							<td>
								<select name="installment" id="installment" class="frm_input" style="width:100px;">
									<option value="">할부선택</option>
									<option value="0" <?php if($installment == 'A') { echo "selected"; } ?>>일시불</option>
									<option value="2" <?php if($installment == 'B') { echo "selected"; } ?>>2개월</option>
									<option value="3" <?php if($installment == 'C') { echo "selected"; } ?>>3개월</option>
									<option value="4" <?php if($installment == 'D') { echo "selected"; } ?>>4개월</option>
									<option value="5" <?php if($installment == 'E') { echo "selected"; } ?>>5개월</option>
									<option value="6" <?php if($installment == 'F') { echo "selected"; } ?>>6개월</option>
								</select>
							</td>
						</tr>
						<tr><td style="height:8px"></td></tr>
						<tr><td style="font-size:11px; color:#777;">카드주 성명</td></tr>
						<tr><td style="height:4px"></td></tr>
						<tr>
							<td><input type="text" name="payerName" id="payerName" class="frm_input" maxlength="100" size="10" placeholder="카드주 성명" value="<?php echo $payerName; ?>"></td>
						</tr>
						<tr><td style="height:8px"></td></tr>
						<tr><td style="font-size:11px; color:#777;">카드주 전화번호</td></tr>
						<tr><td style="height:4px"></td></tr>
						<tr>
							<td>
								<select name="payerTel1" id="payerTel1" class="frm_input" style="width:30%;">
									<option value="010">010</option>
									<option value="011">011</option>
									<option value="017">017</option>
									<option value="018">018</option>
									<option value="019">019</option>
								</select>
								<input type="number" name="payerTel2" id="payerTel2" class="frm_input" maxlength="4" size="6" placeholder="휴대폰" oninput="maxLengthCheck(this)" pattern="\d*" placeholder="number" style="width:30%;" value="<?php echo $payerTel2; ?>">
								<input type="number" name="payerTel3" id="payerTel3" class="frm_input" maxlength="4" size="6" placeholder="번호" oninput="maxLengthCheck(this)" pattern="\d*" placeholder="number" style="width:30%;" value="<?php echo $payerTel3; ?>">
							</td>
						</tr>
						<tr><td style="height:8px"></td></tr>
						<tr><td style="font-size:11px; color:#777;">카드번호</td></tr>
						<tr><td style="height:4px"></td></tr>
						<tr>
							<td><input type="number" name="number" value="<?php echo $number; ?>" id="number" class="frm_input" maxlength="18" placeholder="카드번호" oninput="maxLengthCheck(this)" style="width:100%;"></td>
						</tr>
						<tr><td style="height:8px"></td></tr>
						<tr><td style="font-size:11px; color:#777;">유효기간</td></tr>
						<tr><td style="height:4px"></td></tr>
						<tr>
							<td>
								<label for="expiry" class="sound_only">유효기간<strong>필수</strong></label>
								<select name="expiry2" id="expiry2" class="frm_input" style="width:70px;">
									<option value="">월</option>
									<option value="01" <?php if($expiry2 == '01') { echo "selected"; } ?>>01</option>
									<option value="02" <?php if($expiry2 == '02') { echo "selected"; } ?>>02</option>
									<option value="03" <?php if($expiry2 == '03') { echo "selected"; } ?>>03</option>
									<option value="04" <?php if($expiry2 == '04') { echo "selected"; } ?>>04</option>
									<option value="05" <?php if($expiry2 == '05') { echo "selected"; } ?>>05</option>
									<option value="06" <?php if($expiry2 == '06') { echo "selected"; } ?>>06</option>
									<option value="07" <?php if($expiry2 == '07') { echo "selected"; } ?>>07</option>
									<option value="08" <?php if($expiry2 == '08') { echo "selected"; } ?>>08</option>
									<option value="09" <?php if($expiry2 == '09') { echo "selected"; } ?>>09</option>
									<option value="10" <?php if($expiry2 == '10') { echo "selected"; } ?>>10</option>
									<option value="11" <?php if($expiry2 == '11') { echo "selected"; } ?>>11</option>
									<option value="12" <?php if($expiry2 == '12') { echo "selected"; } ?>>12</option>
								</select>
								<input type="number" name="expiry1" value="<?php echo $expiry1; ?>" id="expiry1" class="frm_input" maxlength="2" size="6" placeholder="년" oninput="maxLengthCheck(this)" style="width:80px;">
							</td>
						</tr>

						<tr><td id="k1b_open1" style="height:8px"></td></tr>
						<tr><td id="k1b_open2" style="font-size:11px; color:#777;">비밀번호</td></tr>
						<tr><td id="k1b_open3" style="height:4px"></td></tr>
						<tr>
							<td id="k1b_open4">
								<input type="number" name="authPw" value="<?php echo $authPw; ?>" id="authPw" class="frm_input" maxlength="2" size="6" placeholder="비밀번호" oninput="maxLengthCheck(this)" style="width:80px;"> 
								<div style="margin-top:10px;">※ 카드비밀번호 앞2자리</div>
							</td>
						</tr>
						<tr><td id="k1b_open5" style="height:8px"></td></tr>
						<tr><td id="k1b_open6" style="font-size:11px; color:#777;">생년워일</td></tr>
						<tr><td id="k1b_open7" style="height:4px"></td></tr>
						<tr>
							<td id="k1b_open8">
								<input type="number" name="authDob" value="<?php echo $authDob; ?>" id="authDob" class="frm_input" maxlength="10" size="10" placeholder="생년월일/사업자" oninput="maxLengthCheck(this)" style="width:130px;">
								<div style="margin-top:10px;">※ <strong>개인</strong> : 생년월일 6자리 | <strong>법인</strong> : 사업자 10자리</div>
							</td>
						</tr>

						<?php /*
						<tr><td style="height:5px"></td></tr>
						<tr><td><span style="color:blue;font-size:0.9em"><?php echo number_format($config['cf_1']); ?>원 이하 결제가능합니다.</span></td></tr>
						*/ ?>
						<tr><td style="height:20px"></td></tr>
						<?php /*
						<tr><td style="line-height:26px;">※ 중요내용<br>- "정상승인" 메세지가 나올때까지 기다리셔야 합니다.<br>- 카드승인까지 약 1~5초 정도 소요됩니다.</td></tr>
						<tr><td style="height:20px"></td></tr>
						*/ ?>
						<tr>
							<td><button id="btn_submit" accesskey="s" class="btn_submit" onclick="getPage();">결제하기</button></td>
						</tr>
					</table>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</section>






<script>




$(function() {
	$('#pd_price').click(function(){
		$(this).val('');
	});
	$("#pd_price").each((i,ele)=>{
		let clone=$(ele).clone(false)
		clone.attr("type","text")
		let ele1=$(ele)
		clone.val(Number(ele1.val()).toLocaleString("en"))
		$(ele).after(clone)
		$(ele).hide()
		$(clone).hide()
		$(ele1).show()
		clone.mouseenter(()=>{
			ele1.show()
			clone.hide()
		})
		setInterval(()=>{
			let newv=Number(ele1.val()).toLocaleString("en")
			if(clone.val()!=newv){
				clone.val(newv)
			}
		},10)

		$(ele).mouseleave(()=>{
			if(clone.val() != 0){
				$(clone).show()
				$(ele1).hide()
			}
		})
	});


	$('#agree').click(function(){
		var checked = $('#agree').is(':checked');
		
		if(checked) {
//			alert("주의사항에 동의 하였습니다.");
			$(".tbl_form1").show();
			$(".danger").hide();
		} else {
			$(".tbl_form1").hide();
			$(".danger").show();
		}
	});


	$('#payments').change(function(){
		var payments = $(this).val();

		// 광원 비인증이 아닐경우 비번 생년월일 보여지기
		$("#cardAuth").val("true");
		$("#k1b_open1").show();
		$("#k1b_open2").show();
		$("#k1b_open3").show();
		$("#k1b_open4").show();
		$("#k1b_open5").show();
		$("#k1b_open6").show();
		$("#k1b_open7").show();
		$("#k1b_open8").show();


		if(payments == 'k1') { // 광원 선택
			$("#payment_open").show();
			$("#installment option").remove();
			$('#installment').append('<?php echo $k1_hal; ?>');
		} else if(payments == 'k1b') { // 광원 비인증 선택
			$("#cardAuth").val("false");
			$("#k1b_open1").hide();
			$("#k1b_open2").hide();
			$("#k1b_open3").hide();
			$("#k1b_open4").hide();
			$("#k1b_open5").hide();
			$("#k1b_open6").hide();
			$("#k1b_open7").hide();
			$("#k1b_open8").hide();
			$("#payment_open").show();
			$("#installment option").remove();
			$('#installment').append('<?php echo $k1b_hal; ?>');
		} else if(payments == 'danal') { // 다날 선택
			$("#payment_open").show();
			$("#installment option").remove();
			$('#installment').append('<?php echo $danal_hal; ?>');
		} else if(payments == 'welcom') { // 웰컴 선택
			$("#payment_open").show();
			$("#installment option").remove();
			$('#installment').append('<?php echo $welcom_hal; ?>');
		} else if(payments == 'paysis') { // 페이시스 선택
			$("#payment_open").show();
			$("#installment option").remove();
			$('#installment').append('<?php echo $paysis_hal; ?>');
		} else if(payments == 'stn') { // 섹타나인 선택
			$("#payment_open").show();
			$("#installment option").remove();
			$('#installment').append('<?php echo $stn_hal; ?>');
		} else if(payments == 'stnb') { // 광원 비인증 선택
			$("#cardAuth").val("false");
			$("#k1b_open1").hide();
			$("#k1b_open2").hide();
			$("#k1b_open3").hide();
			$("#k1b_open4").hide();
			$("#k1b_open5").hide();
			$("#k1b_open6").hide();
			$("#k1b_open7").hide();
			$("#k1b_open8").hide();
			$("#payment_open").show();
			$("#installment option").remove();
			$('#installment').append('<?php echo $stnb_hal; ?>');
		} else {
			$("#payment_open").hide();
		}
	});


});


function maxLengthCheck(object){
	if (object.value.length > object.maxLength){
		object.value = object.value.slice(0, object.maxLength);
	}
	obj.value = comma(uncomma(obj.value));
}

function inputMoveNumber(num) {
	if(isFinite(num.value) == false) {
		alert("카드번호는 숫자만 입력할 수 있습니다.");
		num.value = "";
		return false;
	}
	max = num.getAttribute("maxlength");
	if(num.value.length >= max) {
		num.nextElementSibling.focus();
	}
}

function fn(str){
	var res;
	res = str.replace(/[^0-9]/g,"");
	return res;
}





function getPage() {

	$("#btn_submit").hide();

	var payments = $("#payments").val();
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

	var cardAuth = $("#cardAuth").val();
	var authPw = $("#authPw").val();
	var authDob = $("#authDob").val();
	
	if (payerName == "") {
		alert("카드주명 필수입력 입니다.");
		$("#btn_submit").show();
		$("#payerName").focus();
		return;
	}
	if (payerEmail == "") {
		alert("카드주명 이메일 필수입력 입니다.");
		$("#btn_submit").show();
		$("#payerEmail").focus();
		return;
	}

	if (payerTel1 == "") {
		alert("카드주명 휴대폰번호 필수입력 입니다..");
		$("#btn_submit").show();
		$("#payerTel1").focus();
		return;
	}

	if (payerTel2 == "") {
		alert("카드주명 휴대폰번호 필수입력 입니다..");
		$("#btn_submit").show();
		$("#payerTel2").focus();
		return;
	}

	if (payerTel3 == "") {
		alert("카드주명 휴대폰번호 필수입력 입니다..");
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

	if(cardAuth == "true") {
		if (authPw == "") {
			alert("인증결제는 비밀번호 앞 2자리 필수 입니다.");
			$("#btn_submit").show();
			$("#authPw").focus();
			return;
		}
		if (authDob == "") {
			alert("인증결제는 생년월일 필수 입니다.");
			$("#btn_submit").show();
			$("#authDob").focus();
			return;
		}
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

	if(payments == "k1") {
		if (pd_price == "") {
			alert("상품금액 필수입력 입니다.");
			$("#btn_submit").show();
			$("#pd_price").focus();
			return;

		} else if(fn(pd_price) > <?php echo $config['cf_1_subj']; ?> ){
			alert("해당결제는 <?php echo number_format($config['cf_1_subj']); ?>원 이하 결제가능합니다.");
			$("#btn_submit").show();
			$("#pd_price").focus();
			return;
		}
	} else if(payments == "danal") {
		if (pd_price == "") {
			alert("상품금액 필수입력 입니다.");
			$("#btn_submit").show();
			$("#pd_price").focus();
			return;

		} else if(fn(pd_price) > <?php echo $config['cf_2_subj']; ?> ){
			alert("해당결제는 <?php echo number_format($config['cf_2_subj']); ?>원 이하 결제가능합니다.");
			$("#btn_submit").show();
			$("#pd_price").focus();
			return;
		}
	}
	/*
	if($('#agree').is(':checked') == false) {
		alert('주의사항에 동의하셔야만 결제 가능합니다.');
		$("#btn_submit").show();
		return;
	}
	*/
	
	$.ajax({
		url: "pay_update.php",
		type: "POST",
		async:'false',
		data: {
				'payments' : payments,
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
				'pd_price' : pd_price,
				'cardAuth' : cardAuth,
				'authPw' : authPw,
				'authDob' : authDob
		},
		success:function(data) {
			if(data == "0000") {
				alert('결제성공');
				document.location.replace(g5_url + "/payment/list.php");
			} else {
				alert(data);
				$("#btn_submit").show();
			}
		},
		beforeSend:function() { // 이미지 보여주기 처리
			$('.wrap-loading').removeClass('display-none');
		},
		complete:function() { // 이미지 감추기 처리
			$('.wrap-loading').addClass('display-none');
		},
		error: function (request, status, error) {
			console.log("code: " + request.status)
			console.log("message: " + request.responseText)
			console.log("error: " + error);
			$("#btn_submit").show();
		}
	});
	return false;
	
}


/*
$(document).ready(function(){
	$('#btn_submit').click(function(){
		$("#btn_submit").hide();
	});
});
*/


function comma(str) {
	str = String(str);
	return str.replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');
}

function uncomma(str) {
	str = String(str);
	return str.replace(/[^\d]+/g, '');
}

</script>
<?php
	include_once(G5_PATH.'/tail.php');