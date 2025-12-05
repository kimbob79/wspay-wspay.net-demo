<?php
	include_once('./_common.php');

	if($member['mb_6'] == "no") {
		alert("결제 권한이 없습니다.");
	}

	$g5['title'] = "URL 상품등록";
	$bo_table = "url";
	$urlcode = md5($member['mb_id'].time());
	include_once(G5_PATH.'/head.php');
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
	<form action="./update.php" method="post">
	<input type="hidden" name="w" value="<?php echo $w; ?>">
	<input type="hidden" name="mb_id" id="mb_id" value="<?php echo $member['mb_id']; ?>">
	<input type="hidden" name="mb_name" id="mb_name" value="<?php echo $member['mb_name']; ?>">
	<input type="hidden" name="urlcode" value="<?php echo $urlcode; ?>">
	<div class="tbl_fbasic">
		<?php /*
		<div style="text-align:center; line-height:30px; background:red; color:#fff; font-size:1em; padding:20px; 0; margin-bottom:10px;">
			로또 , 주식관련 절대 결제금지!!<br>
			무조건 강제취소 됩니다.
		</div>
		*/ ?>
		<table class="tbl_form1" style="width:100%;border-collapse: collapse; border-spacing: 0 5px;">
			<tbody>
				<tr>
					<td>
						<select name="payments" id="payments" class="frm_input" style="width:100%;" required>
							<option value="">결제타입 선택</option>
							<?php if($member['mb_6'] == "1") { ?>
							<option value="k1">광원 - 최대 <?php echo number_format(substr($config['cf_1_subj'],0,-4)); ?>만원 / <?php echo $config['cf_1']; ?>개월</option>
							<?php } ?>
							<?php if($member['mb_15'] == "1") { ?>
							<option value="k1">광원 비인증 - 최대 <?php echo number_format(substr($config['cf_5_subj'],0,-4)); ?>만원 / <?php echo $config['cf_5']; ?>개월</option>
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
							<option value="stn">섹타나인 비인증 - 최대 <?php echo number_format(substr($config['cf_8_subj'],0,-4)); ?>만원 / <?php echo $config['cf_8']; ?>개월</option>
							<?php } ?>
						</select>
						<input type="hidden" name="cardAuth" id="cardAuth" value="true">
					</td>
				</tr>
				<tr>
					<td id="payment_open">
					<table style="width:100%;border-collapse: collapse; border-spacing: 0 5px;">
						<tr><td style="height:8px"></td></tr>
						<tr><td style="font-size:11px; color:#777;"><span style="color:red">필수)</span> 상품명</td></tr>
						<tr><td style="height:4px"></td></tr>
						<tr>
							<td><input type="text" name="url_pd" value="<?php echo $url_pd; ?>" id="url_pd" required class="full_input frm_input" maxlength="50" placeholder="결제할 상품명을 입력해주세요"></td>
						</tr>
						<tr><td style="height:8px"></td></tr>
						<tr><td style="font-size:11px; color:#777;"><span style="color:red">필수)</span> 상품가격</td></tr>
						<tr><td style="height:4px"></td></tr>
						<tr>
							<td><input type="number" name="url_price" value="<?php echo $url_price; ?>" id="url_price" required class="frm_input pd_price" placeholder="상품 가격을 입력해주세요" maxlength="8" oninput="maxLengthCheck(this)" style="width:150px;"> 원</td>
						</tr>
						<tr><td style="height:8px"></td></tr>
						<tr><td style="font-size:11px; color:#777;"><span style="color:red">필수)</span> 판매자명</td></tr>
						<tr><td style="height:4px"></td></tr>
						<tr>
							<td><input type="text" name="url_pname" value="<?php echo $url_pname; ?>" id="url_pname" required class="frm_input" placeholder="판매자명" maxlength="8" oninput="maxLengthCheck(this)" style="width:200px;"></td>
						</tr>
						<tr><td style="height:8px"></td></tr>
						<tr><td style="font-size:11px; color:#777;"><span style="color:red">필수)</span> 판매자 전화번호</td></tr>
						<tr><td style="height:4px"></td></tr>
						<tr>
							<td>
								<select name="url_ptel1" id="url_ptel1" class="frm_input" style="width:30%;">
									<option value="010">010</option>
									<option value="011">011</option>
									<option value="017">017</option>
									<option value="018">018</option>
									<option value="019">019</option>
								</select>
								<input type="number" name="url_ptel2" id="url_ptel2" required class="frm_input" maxlength="4" size="6" placeholder="휴대폰" oninput="maxLengthCheck(this)" pattern="\d*" placeholder="number" style="width:30%;" value="<?php echo $payerTel2; ?>">
								<input type="number" name="url_ptel3" id="url_ptel3" required class="frm_input" maxlength="4" size="6" placeholder="번호" oninput="maxLengthCheck(this)" pattern="\d*" placeholder="number" style="width:30%;" value="<?php echo $payerTel3; ?>">
							</td>
						</tr>
						<tr><td style="height:8px"></td></tr>
						<tr><td style="font-size:11px; color:#777;">상품설명</td></tr>
						<tr><td style="height:4px"></td></tr>
						<tr>
							<td><input type="text" name="url_pcontent" value="<?php echo $url_pcontent; ?>" id="url_pcontent" class="frm_input" maxlength="18" placeholder="상품설명" oninput="maxLengthCheck(this)" style="width:100%;"></td>
						</tr>
						<tr><td style="height:8px"></td></tr>
						<tr><td style="font-size:11px; color:#777;">비고</td></tr>
						<tr><td style="height:4px"></td></tr>
						<tr>
							<td><input type="text" name="url_etc" value="<?php echo $url_etc; ?>" id="url_etc" class="frm_input" maxlength="18" placeholder="비고" oninput="maxLengthCheck(this)" style="width:100%;"></td>
						</tr>
						<tr><td style="height:8px"></td></tr>
						<tr>
							<td><button id="btn_submit" accesskey="s" class="btn_submit">상품등록하기</button></td>
						</tr>
					</table>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	</form>
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