<?php
	error_reporting(E_ALL);
	ini_set("display_errors", 0);
	include("./_common.php");
	$no_menu = "yes";
	
	
	
	$url_id = $id;
	$url_pay = sql_fetch(" select * from pay_payment_sms where pm_id = '{$url_id}' ");
	$url_code = $url_pay['urlcode'];
	$pay_product = $url_pay['url_pd'];
	$pay_price = $url_pay['url_price'];
	$mb_id = $url_pay['mb_id'];
	$pg_id = $url_pay['pg_id'];
	$row_pg = sql_fetch(" select * from pay_member_pg where mb_id = '{$url_pay['mb_id']}' and pg_id = '$pg_id' ");

	if(!$url_code) {
		alert("잘못된 접근입니다. url_code 없음");
		exit;
	}

	/*
	if($is_admin) {
		$pay_product = "테스트 상품";
		$pay_price = "1004";
		$pay_pname = "테스트";
		$pay_phone = "010-1234-5678";
		$pay_email = "test@naver.com";
		$pay_cardnum = "4579-7362-5143-3035";
		$pay_installment = "0";
		$pay_MM = "02";
		$pay_YY = "28";
		$pay_password = "20";
		$pay_certify = "811211";
	}
	*/
	

	if($pay_id) {
		$pay = sql_fetch(" select * from pay_payment where pay_id = '{$pay_id}' ");
		if(!$is_admin) {
			if($pay['mb_id'] != $member['mb_id']) {
				alert("잘못된 접근 입니다.");
			}
		}
		$pay_product = $pay['pay_product'];
		$pay_pname = $pay['pay_pname'];
		$pay_phone = $pay['pay_phone'];
		$pay_email = $pay['pay_email'];
		$pay_cardnum = $pay['pay_cardnum'];
		$pay_MM = $pay['pay_MM'];
		$pay_YY = $pay['pay_YY'];
		$pay_password = $pay['pay_password'];
		$pay_certify = $pay['pay_certify'];
		$mb_id = $pay['mb_id'];
		$pg_id = $pay['pg_id'];
	}

	// 회원정보
	$mb = get_member($mb_id);
	
	// 회원 PG정보
	$member_pg = sql_fetch(" select * from pay_member_pg where mb_id = '{$mb_id}' and pg_id = '{$pg_id}' ");
	
	// PG정보
	$pg = sql_fetch(" select * from pay_pg where pg_id = '{$member_pg['pg_mid']}' ");

	if($member_pg['pg_use'] == '1') {
		alert_close("사용할 수 없는 결제모듈입니다.");
	}

	include_once("./_head.php");
	/*
	$mb_pass = rand(11111,999999);

	if($member['mb_id']) {
		if(!$is_admin) {
			if(!$mb_id) {
				$w = "u";
				$mb_id = $member['mb_id'];
			}
		}
	}

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
	*/
?>

<script>
	function attachfile_check(value_str, msg) {
		if (value_str == "") return true;
		var rtn = false;
		var re = new RegExp(".(png|jpg|gif|ppt|doc|xls|hwp|pdf|zip|txt|docx|xlsx|pptx)$", "gi");
		rtn = !!value_str.match(re);
		if (!rtn) {
			alert(msg);
		}
		return rtn;
	}

	function insert_bbs() {

		$("#btn1, #btn2").hide(); // 결제버튼 숨기기

		var pg_id= $("#pg_id").val(); // PG 아이디
		var pg_code= $("#pg_code").val(); // PG 코드
		var mb_id= $("#mb_id").val(); // PG 코드
		var url_id= $("#url_id").val(); // PG 코드
		var pg_key1= $("#pg_key1").val(); // PG KEY1
		var pg_key2= $("#pg_key2").val(); // PG KEY2
		var pg_key3= $("#pg_key3").val(); // PG KEY3
		var pg_key4= $("#pg_key4").val(); // PG KEY4
		var pg_key5= $("#pg_key5").val(); // PG KEY5

		var pay_product= $("#pay_product").val(); // 상품명
		var pay_price = $("#pay_price").val(); // 결제금액
		var pay_pname = $("#pay_pname").val(); // 카드주명
		var pay_phone = $("#pay_phone").val(); // 휴대전화번호
		var pay_email = $("#pay_email").val(); // 휴대전화번호
		var pay_cardnum = $("#pay_cardnum").val(); // 카드번호
		var pay_installment = $("#pay_installment").val(); // 할부
		var pay_MM = $("#pay_MM").val(); // 유효기간 월
		var pay_YY = $("#pay_YY").val(); // 유효기간 년
		var pay_password = $("#pay_password").val(); // 비밀번호
		var pay_certify = $("#pay_certify").val(); // 생년월일
		var cardAuth = $("#cardAuth").val(); // 구인증 true 비인증 false

		if ($('#pay_product').val()=='') {
			alert('상품명을 입력하세요');
			$("#btn1, #btn2").show(); // 결제버튼 보이기
			$('#pay_product').get(0).focus();
			return;
		}
		if ($('#pay_price').val() == '') {
			alert('결제금액을 입력하세요');
			$("#btn1, #btn2").show(); // 결제버튼 보이기
			$('#pay_price').get(0).focus();
			return;
		}
		if ($('#pay_pname').val() == '') {
			alert('카드주명을 입력하세요');
			$("#btn1, #btn2").show(); // 결제버튼 보이기
			$('#pay_pname').get(0).focus();
			return;
		}
		if ($('#pay_phone').val() == '') {
			alert('휴대전화번호를 입력하세요');
			$("#btn1, #btn2").show(); // 결제버튼 보이기
			$('#pay_phone').get(0).focus();
			return;
		}
		if(pay_phone) {
			var regex = /^(01[0-9]{1}-?[0-9]{4}-?[0-9]{4}|01[0-9]{8})$/;
			if (!regex.test(pay_phone)) {
				alert("휴대전화번호를 정확히 입력하세요");
				$("#btn1, #btn2").show(); // 결제버튼 보이기
				$('#pay_phone').get(0).focus();
				return;
			}
		}
		if ($('#pay_cardnum').val() == '') {
			alert('카드번호를 입력하세요');
			$("#btn1, #btn2").show(); // 결제버튼 보이기
			$('#pay_cardnum').get(0).focus();
			return;
		}
		if ($('#pay_installment').val() == '') {
			alert('할부를 선택해주세요.');
			$("#btn1, #btn2").show(); // 결제버튼 보이기
			$('#pay_installment').get(0).focus();
			return;
		}
		if ($('#pay_MM').val() == '') {
			alert('유효기간 월을 선택해주세요');
			$("#btn1, #btn2").show(); // 결제버튼 보이기
			$('#pay_MM').get(0).focus();
			return;
		}
		if ($('#pay_YY').val() == '') {
			alert('유효기간 년도를 선택해주세요');
			$("#btn1, #btn2").show(); // 결제버튼 보이기
			$('#expYY').get(0).focus();
			return;
		}
		if ($('#pay_password').val() == '') {
			alert('비밀번호 앞 2자리를 입력해주세요');
			$("#btn1, #btn2").show(); // 결제버튼 보이기
			$('#pay_password').get(0).focus();
			return;
		}
		if ($('#pay_certify').val() == '') {
			alert('본인확인정보를 입력해주세요');
			$("#btn1, #btn2").show(); // 결제버튼 보이기
			$('#pay_certify').get(0).focus();
			return;
		}
		if (!$('#agreeTerm').prop('checked')) {
			alert('약관에 동의하셔야 가능합니다.');
			$("#btn1, #btn2").show(); // 결제버튼 보이기
			return;
		}
		if (!$('#agreePriv').prop('checked')) {
			alert('개인정보 처리방침 동의하셔야 가능합니다.');
			$("#btn1, #btn2").show(); // 결제버튼 보이기
			return;
		}


		$.ajax({
			url: "./payment_update.php",
			type: "POST",
			async:'false',
			data: {
					'pg_id' : pg_id,
					'pg_code' : pg_code,
					'mb_id' : mb_id,
					'url_id' : url_id,
					'pg_key1' : pg_key1,
					'pg_key2' : pg_key2,
					'pg_key3' : pg_key3,
					'pg_key4' : pg_key4,
					'pg_key5' : pg_key5,

					'pay_product' : pay_product,
					'pay_price' : pay_price,
					'pay_pname' : pay_pname,
					'pay_phone' : pay_phone,
					'pay_email' : pay_email,
					'pay_cardnum' : pay_cardnum,
					'pay_installment' : pay_installment,
					'pay_MM' : pay_MM,
					'pay_YY' : pay_YY,
					'pay_password' : pay_password,
					'pay_certify' : pay_certify,
					'cardAuth' : cardAuth
			},
			success:function(data) {
				if(data == "0000") {
					$('html, body').animate({scrollTop:0}, '300');
					$("#pay_price").val("");
					$("#btn1, #btn2").show();
					$("#agreeCheckAll, #agreeTerm, #agreePriv").prop("checked",false);
					alert('결제성공');
				} else {
					alert("결제실패");
					$("#btn1, #btn2").show();
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
				$("#btn1, #btn2").show();
			}
		});
		return false;




		/*
		if (f.file_file.value != "")
			if (!attachfile_check(f.file_file.value, "지원하지 않는 파일형식이므로 첨부하실수없습니다.")) return;
		f.target = '_writeFrame';
		*/

//		f.submit();
	}

	function go_close() {
		window.close();
	}


	$(function() {
		$('input#agreeCheckAll:checkbox').on('click', function () {
			$('input#agreeTerm,input#agreePriv').prop('checked', $(this).prop('checked'));
		});

		$('input#agreeTerm,input#agreePriv').on('change', function () {
			var checked = $('input#agreeTerm').prop('checked') && $('input#agreePriv').prop('checked');
			$('input#agreeCheckAll').prop('checked', checked);
		});

		$("#pay_phone").keyup(function(){
			$(this).val($(this).val().replace(/[^0-9]/gi, "").replace(/^(\d{2,3})(\d{3,4})(\d{4})$/, `$1-$2-$3`));
		});

	});


	// 숫자만, 콤마  onkeyup="inputNumberFormat(this);"
	function comma(str) {
		str = String(str);
		return str.replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');
	}
	function uncomma(str) {
		str = String(str);
		return str.replace(/[^\d]+/g, '');
	}
	function inputNumberFormat(obj) {
		obj.value = comma(uncomma(obj.value));
	}
	function inputOnlyNumberFormat(obj) {
		obj.value = onlynumber(uncomma(obj.value));
	}
	function onlynumber(str) {
		str = String(str);
		return str.replace(/(\d)(?=(?:\d{3})+(?!\d))/g,'$1');
	}
	// 숫자만, 콤마


	//신용카드 번호 하이픈(-) 추가
	function formatCreditCard() {
		var inputElement = document.getElementById('pay_cardnum');
		var inputValue = inputElement.value;
		var formattedValue = cardNoHyphen(inputValue);
		inputElement.value = formattedValue;
	}
	function cardNoHyphen(str){
		str = str.replace(/[^0-9]/g, '');
		var tmp = '';
		if( str.length < 5){
			return str;
		}else if(str.length < 9){
			tmp += str.substr(0, 4);
			tmp += '-';
			tmp += str.substr(4);
			return tmp;
		}else if(str.length < 13){
			tmp += str.substr(0, 4);
			tmp += '-';
			tmp += str.substr(4, 4);
			tmp += '-';
			tmp += str.substr(8, 4);
			return tmp;
		}else{
			tmp += str.substr(0, 4);
			tmp += '-';
			tmp += str.substr(4, 4);
			tmp += '-';
			tmp += str.substr(8, 4);
			tmp += '-';
			tmp += str.substr(12);
			return tmp;
		}
		return str;
	}
	//신용카드 번호 하이픈(-) 추가

</script>
<style>
	table.table_pg {
		width:100%;
		border-collapse: collapse;
		text-align: left;
		line-height: 1.5;
		border: 1px solid #ddd;
		border-top: 1px solid #111;

	}
	table.table_pg th {
		width: 15%;
		padding: 10px;
		font-weight: normal;
		color: #369;
		border-bottom: 1px solid #ddd;
		background: #f3f6f7;
		font-size:11px;
	}
	table.table_pg td {
		width: 35%;
		padding: 10px;
		border-bottom: 1px solid #ddd;
	}
	#bbs .bbs-cont {padding: 1em 0 2em}
	.bbs-cont .inner {padding:0 10px}

	.all-check {
		margin: 2em 0 0.5em;
	}
	.all-check input[type=checkbox]:checked+label {
		border: 1px solid #3651f6;
	}
	.all-check input[type=checkbox]:checked+label span i {
		background: url('../img/checkbox_all.png') 0 -16px no-repeat;
		background-size: 100%;
	}
	.all-check input[type=checkbox] {
		display: none;
	}
	input:read-only {
		color: #999;
		background-color: #fff;
	}
	.all-check label {
		display: block;
		width: 100%;
		padding: 0.9em;
		text-align: center;
	}
	.all-check label, .select-wrapper select {
		position: relative;
		background:#fff;
		border: 1px solid #e5e5e5;
	}
	.all-check input+label span {
		position: relative;
		display: inline-block;
	}
	.all-check input[type=checkbox]+label span i {
		display: inline-block;
		margin-right: 10px;
		vertical-align: -3px;
		width: 18px;
		height: 16px;
		background: url('../img/checkbox_all.png') 0 0 no-repeat;
		background-size: 100%;
	}
	.agreement-wrap ul li {
		padding: 0.3em 0;
		font-weight: 400;
		font-size: 1.3rem;
	}
	.input-chk {
		position: relative;
		display: inline-block;
		font-weight: 400;
		line-height: 24px;
		color: #666;
	}
	.input-chk input[type=checkbox] {
		display: none;
	}
	.input-chk input+span {
		display: inline-block;
		margin-left: 34px;
	}
	.input-chk input[type=checkbox]:checked+span:before {
		background: url(../img/checkbox.png) 0 -20px no-repeat;
		background-size: 100%;
	}
	.input-chk input[type=checkbox]+span:before {
		position: absolute;
		left: 0;
		top: 5px;
		content: " ";
		display: inline-block;
		width: 20px;
		height: 20px;
		background: url('../img/checkbox.png') 0 0 no-repeat;
		background-size: 100%;
	}
	.agreement-wrap ul li a {
		float: right;
		color: #999;
		text-decoration: underline;
		line-height: 24px;
	}
</style>


<style>
.wrap-loading{ /*화면 전체를 어둡게 합니다.*/
	position: fixed;
	left:0;
	right:0;
	top:0;
	bottom:0;
	background: #fff;    /* ie */
	z-index: 9;
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
	<div><img src="./img/loading_payment.gif" width="200" height="411"></div>
</div>

<section class="container" id="bbs">
	<section class="contents contents-bbs">
		<div class="top">
			<div class="inner">
				<table class="table_pg">
					<tr>
						<th>결제사</th>
						<td><?php echo $pm_id; echo $row_pg['pg_name']; ?></td>
						<th>결제금액</th>
						<td><?php echo number_format($url_pay['url_price']); ?></td>
					</tr>
					<tr>
						<th>판매자</th>
						<td><?php echo $url_pay['url_pname']; ?></td>
						<th>연락처</th>
						<td><?php echo $url_pay['url_ptel']; ?></td>
					</tr>
					<tr>
						<th>구매자</th>
						<td><?php echo $url_pay['url_gname']; ?></td>
						<th>연락처</th>
						<td><?php echo $url_pay['url_gtel']; ?></td>
					</tr>
					<tr>
						<th>카드사</th>
						<td colspan="3">
							<?php
								$card_uses = "1";

								if($pg['card_nh'] == "1") { $card_uses = "2"; echo "<span class='cards'>농협</span> "; }
								if($pg['card_bc'] == "1") { $card_uses = "2"; echo "<span class='cards'>비씨</span> "; }
								if($pg['card_sh'] == "1") { $card_uses = "2"; echo "<span class='cards'>신한</span> "; }
								if($pg['card_kb'] == "1") { $card_uses = "2"; echo "<span class='cards'>국민</span> "; }
								if($pg['card_hana'] == "1") { $card_uses = "2"; echo "<span class='cards'>하나</span> "; }
								if($pg['card_wr'] == "1") { $card_uses = "2"; echo "<span class='cards'>우리</span> "; }
								if($pg['card_ss'] == "1") { $card_uses = "2"; echo "<span class='cards'>삼성</span> "; }
								if($pg['card_lo'] == "1") { $card_uses = "2"; echo "<span class='cards'>롯데</span> "; }
								if($pg['card_hd'] == "1") { $card_uses = "2"; echo "<span class='cards'>현대</span> "; }

								if($card_uses == "1") {
									echo "<div style='color:blue; font-size:11px;'>모든카드 결제 가능합니다.</div>";
								} else if($card_uses == "2") {
									echo "<div style='margin-top:3px; color:red; font-size:11px;'>위 카드는 결제 불가합니다.</div>";
								}
							?>
						</td>
					</tr>
				</table>

			</div>
		</div>
		<div class="bbs-cont">
			<div class="inner">
				<div class="write-form">
					<?php /*
					<ul class="keyword tab-menu">
						<li class="active" rel="tab1"><a href="#">판매자 회원이신 경우</a></li>
						<li rel="tab2"><a href="#">결제고객(구매자)이신 경우</a></li>
					</ul>
					<div class="noti-box">
						<h4>문의 전 안내사항<span class="view-btn"><em class="open-btn">보기</em><em class="close-btn">닫기</em></span></h4>
						<ul class="tab-content" id="tab1">
							<li>
							<span class="quest">가입 후 해야할 일이 있나요?</span>
							<p class="reply">
								가입 후 결제 사용은 바로 가능하나, 계약서 및 구비서류를 발송해주셔야 정산이 가능합니다.<br>
								계약서는 <b class="text-red">페이앱 홈페이지 > 이용안내 > 구비서류/심사</b>페이지에서 출력할 수 있으며, 작성 날인 하여 <b class="text-underline">payapp@udid.co.kr</b> 이메일로 접수해주시기 바랍니다.
							</p>
							</li>
							<li>
							<span class="quest">계약서를 이메일로 발송했는데 언제 처리되나요?</span>
							<p class="reply">
								계약서는 발송해주신 순서대로 서류심사팀에서 확인되어 완료까지는 <b><span class="text-red">최대 일주일</span> 정도 소요</b>될 수 있습니다. 완료 또는 수정 사항이 있을 경우 유선이나 문자, 메일로 연락드리니 조금만 기다려 주시기 바랍니다.
							</p>
							</li>
							<li>
							<span class="quest">정산은 언제 되나요?</span>
							<p class="reply">
								<b>결제일로부터 <span class="text-red">D+5</span></b>(영업일기준) 가 정산일 입니다. 계약서 완료 후 정산이 가능하니 참고하시기 바랍니다.
							</p>
							</li>
							<li>
							<span class="quest">왜 정산 불가인가요?</span>
							<p class="reply">
								계약서가 완료되지 않았거나, 보증보험 증권 등 여러가지 사유가 있을 수 있기 때문에 오른쪽 아래 <b class="text-red">채팅상담</b>으로 문의주시거나, <b class="text-red">1800-3772</b>로 연락하시는 게 가장 빠르고 정확합니다.
							</p>
							</li>
						</ul>
						<!-- //판매자 회원인 경우 -->
						<ul class="tab-content" id="tab2">
							<li>
							<span class="quest">판매자와 연락이 안 됩니다. 취소하고 싶은데 민원을 어떻게 접수하죠?</span>
							<span class="quest">판매자와 결제 취소에 대한 분쟁이 있습니다. 민원을 어떻게 접수하죠?</span>
							<p class="reply reply-lg">
								<b><a class="text-orange" href="/popup_pay_new/popup/pay_view1.html" onclick="return openWindow(this, {name:'payappHistory',width:420,height:630,center:true,scrollbars:true})">결제 내역 조회</a></b> 메뉴에서 내역 조회가 가능하며 결제 내역 조회 결과 화면에 표시되는 <b class="text-red">[민원접수] 기능으로 온라인 민원신청이 가능</b>합니다.<br>
								 온라인 민원 신청 시 페이앱 접수와 판매점에 대한 통보가 자동으로 이뤄지며, 페이앱 담당자가 지속 체크하여 민원 해결을 적극적으로 돕습니다.
							</p>
							</li>
						</ul>
						<!-- //구매자인 경우 -->
					</div>
					<!-- //tab-menu -->
					*/ ?>
					<form name="insert_bbs_form" method=post enctype="multipart/form-data" action="./payment_update.php" onsubmit="return false;">
						<input type="hidden" name="pg_id" id="pg_id" value="<?php echo $pg_id; ?>" style='background:#222;color:#fff;'>
						<input type="hidden" name="pg_code" id="pg_code" value="<?php echo $member_pg['pg_code']; ?>" style='background:#222;color:#fff;'>
						<input type="hidden" name="mb_id" id="mb_id" value="<?php echo $mb_id; ?>" style='background:#222;color:#fff;'>
						<input type="hidden" name="url_id" id="url_id" value="<?php echo $url_id; ?>" style='background:#222;color:#fff;'>

						<?php if($row_pg['pg_key1']) { ?>
						<input type="hidden" name="pg_key1" id="pg_key1" value="<?php echo $row_pg['pg_key1']; ?>" style='background:#222;color:#fff;'>
						<?php } ?>

						<?php if($row_pg['pg_key2']) { ?>
						<input type="hidden" name="pg_key2" id="pg_key2" value="<?php echo $row_pg['pg_key2']; ?>" style='background:#222;color:#fff;'>
						<?php } ?>

						<?php if($row_pg['pg_key3']) { ?>
						<input type="hidden" name="pg_key3" id="pg_key3" value="<?php echo $row_pg['pg_key3']; ?>" style='background:#222;color:#fff;'>
						<?php } ?>

						<?php if($row_pg['pg_key4']) { ?>
						<input type="hidden" name="pg_key4" id="pg_key4" value="<?php echo $row_pg['pg_key4']; ?>" style='background:#222;color:#fff;'>
						<?php } ?>

						<?php if($row_pg['pg_key5']) { ?>
						<input type="hidden" name="pg_key5" id="pg_key5" value="<?php echo $row_pg['pg_key5']; ?>" style='background:#222;color:#fff;'>
						<?php } ?>

						<input type="hidden" name="cardAuth" id="cardAuth" value="<?php if($pg['pg_code'] == "k1b") { echo "false"; } else { echo "true"; } ?>" style='background:#222;color:#fff;'>

						<fieldset>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">상품명</label>
								</div>
								<div class="panel-body">
									<input type="text" name="pay_product" id="pay_product" class="form-control" maxlength="20" placeholder="상품명" value="<?php echo $pay_product; ?>" readonly>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">결제금액</label>
								</div>
								<div class="panel-body">
									<input type="text" name="pay_price" id="pay_price" class="form-control" placeholder="결제금액" value="<?php echo number_format($pay_price); ?>" readonly>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">카드주정보</label>
								</div>
								<div class="panel-body1">
									<input type="text" name="pay_pname" id="pay_pname" class="form-control" placeholder="카드주명" value="<?php echo $pay_pname; ?>" required>
								</div>
								<div class="panel-body2">
									<input type="text" name="pay_phone" id="pay_phone" class="form-control" placeholder="휴대전화번호" value="<?php echo $pay_phone; ?>" maxlength="13" required>
								</div>
								<div class="panel-body">
									<input type="text" name="pay_email" id="pay_email" class="form-control" placeholder="[선택입력] 이메일 " value="<?php echo $pay_email; ?>" maxlength="100">
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">카드번호</label>
								</div>
								<div class="panel-body">
									<input type="text" name="pay_cardnum" id="pay_cardnum" class="form-control" placeholder="카드번호" value="<?php echo $pay_cardnum; ?>" required oninput="formatCreditCard()" maxlength="19">
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">할부선택</label>
								</div>
								<div class="panel-body">
									<select name="pay_installment" id="pay_installment" class="form-control">
										<option value="">할부선택</option>
										<option value="0" <?php if($pay_installment == "0") { echo "selected"; } ?>>일시불</option>
										<?php for($h=2; $h<=$row_pg['pg_hal']; $h++) { ?>
										<option value="<?php echo $h; ?>" <?php if($pay_installment == $h) { echo "selected"; } ?>><?php echo $h; ?>개월</option>
										<?php } ?>
									</select>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">유효기간</label>
								</div>
								<div class="panel-body1">
									<select name="pay_MM" id="pay_MM" class="form-control">
										<option value="">월선택</option>
										<option value="01" <?php if($pay_MM == "01") { echo "selected"; } ?>>01월</option>
										<option value="02" <?php if($pay_MM == "02") { echo "selected"; } ?>>02월</option>
										<option value="03" <?php if($pay_MM == "03") { echo "selected"; } ?>>03월</option>
										<option value="04" <?php if($pay_MM == "04") { echo "selected"; } ?>>04월</option>
										<option value="05" <?php if($pay_MM == "05") { echo "selected"; } ?>>05월</option>
										<option value="06" <?php if($pay_MM == "06") { echo "selected"; } ?>>06월</option>
										<option value="07" <?php if($pay_MM == "07") { echo "selected"; } ?>>07월</option>
										<option value="08" <?php if($pay_MM == "08") { echo "selected"; } ?>>08월</option>
										<option value="09" <?php if($pay_MM == "09") { echo "selected"; } ?>>09월</option>
										<option value="10" <?php if($pay_MM == "10") { echo "selected"; } ?>>10월</option>
										<option value="11" <?php if($pay_MM == "11") { echo "selected"; } ?>>11월</option>
										<option value="12" <?php if($pay_MM == "12") { echo "selected"; } ?>>12월</option>
									</select>
								</div>
								<div class="panel-body2">
									<select name="pay_YY" id="pay_YY" class="form-control">
										<option value="">년선택</option>
										<option value="24" <?php if($pay_YY == "24") { echo "selected"; } ?>>2024년</option>
										<option value="25" <?php if($pay_YY == "25") { echo "selected"; } ?>>2025년</option>
										<option value="26" <?php if($pay_YY == "26") { echo "selected"; } ?>>2026년</option>
										<option value="27" <?php if($pay_YY == "27") { echo "selected"; } ?>>2027년</option>
										<option value="28" <?php if($pay_YY == "28") { echo "selected"; } ?>>2028년</option>
										<option value="29" <?php if($pay_YY == "29") { echo "selected"; } ?>>2029년</option>
										<option value="30" <?php if($pay_YY == "30") { echo "selected"; } ?>>2030년</option>
										<option value="31" <?php if($pay_YY == "31") { echo "selected"; } ?>>2031년</option>
										<option value="32" <?php if($pay_YY == "32") { echo "selected"; } ?>>2032년</option>
										<option value="33" <?php if($pay_YY == "33") { echo "selected"; } ?>>2033년</option>
										<option value="34" <?php if($pay_YY == "34") { echo "selected"; } ?>>2034년</option>
									</select>
								</div>
							</div>
							<?php if($pg['pg_certified'] == '0') { ?>
							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">인증정보</label>
								</div>
								<div class="panel-body1">
									<input type="text" name="pay_password" id="pay_password" class="form-control" maxlength="2" placeholder="비밀번호 앞2자리" value="<?php echo $pay_password; ?>" required>
								<div class="comments">* 비밀번호 앞2자리만</div>
								</div>
								<div class="panel-body2">
									<input type="text" name="pay_certify" id="pay_certify" class="form-control" maxlength="10" placeholder="본인확인" value="<?php echo $pay_certify; ?>" required>
								<div class="comments">* 개인 : 생년월일 6자리<br>* 법인 : 사업자번호 10자리</div>
								</div>
							</div>
							<?php } ?>

							<?php /*
							<div class="panel">
								<div class="panel-heading">
									<label class="tit">약관동의</label>
								</div>
								<div class="panel-body">
									<div class="terms-box">
										<p>
											결제의 모든 책임은 결제자에게 있습니다.
										</p>
										<ol>
											<li>
											1. 수집하는 개인정보 항목<br>
											 필수정보 : 작성자(이름), 비밀번호, 상담내용 </li>
											<li>
											2. 개인정보 수집목적<br>
											 온라인 문의상담 </li>
											<li class="text-bold">
											3. 개인정보 보유 및 이용기간<br>
											<strong>
											개인정보의 수집목적이 달성되면 지체없이 파기 합니다.<br>
											 단, 관계법령에 따라 일정기간 정보의 보관을 규정하는 경우는 아래와 같습니다.<br>
											 이 기간 동안 법령의 규정(소비자의 불만 또는 분쟁처리에 관한 기록 : 3년)에 따라<br>
											 개인정보를 보관하며, 본 정보를 다른 목적으로 절대 이용하지 않습니다. </strong>
											</li>
										</ol>
										 ※개인정보 수집에 동의하지 않으실 수 있으며, 동의하지 않으실 경우 게시글 등록이 제한됩니다.
									</div>
									<div style="margin-top:1rem">
										<label class="input-chk">
										<input type="checkbox" name="agreement1" id="agreement1" value="1">
										<span>약관에 동의 합니다.</span>
										</label>
									</div>
								</div>
							</div>
							*/ ?>
						</fieldset>
					</form>
				</div>


				<div class="all-check">
					<input type="checkbox" name="agreeCheckAll" id="agreeCheckAll" data-role="none">
					<label for="agreeCheckAll">
						<span><i></i>전체동의</span>
					</label>
				</div>

				<div class="agreement-wrap">
					<ul>
						<li>
							<label for="agreeTerm" class="input-chk">
								<input type="checkbox" name="agreeTerm" id="agreeTerm" value="1" data-role="none"> <span>심플페이 이용약관 동의</span>
							</label>
							<a href="./payment_agree1.php" data-role="none" data-transition="slide" data-inline="true" data-mini="true" rel="external" data-ajax="false">보기</a>
						</li>
						<li>
							<label for="agreePriv" class="input-chk">
								<input type="checkbox" name="agreePriv" id="agreePriv" value="1" data-role="none"> <span>개인정보 처리방침 동의</span>
							</label>
							<a href="./payment_agree2.php" data-role="none" data-transition="slide" data-inline="true" data-mini="true" rel="external" data-ajax="false">보기</a>
						</li>
					</ul>
				</div>

				<div class="btn-center-block">
					<div class="btn-group btn-horizon">
						<div class="btn-table">
							<a class="btn btn-black btn-cell" id="btn1" onclick="insert_bbs()">결제</a>
							<a class="btn btn-black-line btn-cell" id="btn2" onclick="go_close();">취소</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</section>
<?php
	include_once("./_foot.php");
?>