<?php
	include_once('./_common.php');

	$mb_id = $_POST['mb_id'];
	$pg_id = $_POST['pg_id'];

	/*
	if(!$url_code) {
		if($mb_id != $member['mb_id']) {
			alert("로그인한 사람과 결제자가 다릅니다.");
			exit;
		}
	}

	if(!$_POST['input_goodname']) {
		alert("상품정보가 없습니다.");
	}
	if(!$_POST['mem_name']) {
		alert("카드주명이 없습니다.");
	}
	if(!$_POST['mem_phone']) {
		alert("카드주 휴대전화번호가 없습니다.");
	}
	if(!$_POST['input_price']) {
		alert("결제금액이 없습니다.");
	}
	*/

	if($is_admin) {
		if(!$mb_id) { $mb_id = "admin"; }
		if(!$pg_id) { $pg_id = "25"; }
	}

	$member_pg = sql_fetch(" select * from pay_member_pg where mb_id = '{$mb_id}' and pg_id = '{$pg_id}' ");

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no"/>
	<title>심플페이</title>
	<script src="./js/jquery-1.9.1.min.js"></script>
	<script src="./js/jquery.mobile-1.3.0.min.js"></script>
	<link rel="stylesheet" type="text/css" href="./css/reset.css?ver=<?php echo time(); ?>">
	<link rel="stylesheet" type="text/css" href="./css/payment.css?ver=<?php echo time(); ?>">
	<link rel="stylesheet" type="text/css" href="./css/loading.css?ver=<?php echo time(); ?>">

	<script type="text/javascript">

		function close_window() {
			if (confirm("Close Window?")) {
				close();
			}
		}

		$(document).bind('pageshow', function () {
			$('#CardNo1,#CardNo2,#CardNo3').on('keyup', function () {
				if ($(this).val().length == 4) {
					if ($(this).attr('id') == 'CardNo1') {
						$('#CardNo2').focus();
					}
					if ($(this).attr('id') == 'CardNo2') {
						$('#CardNo3').focus();
					}
					if ($(this).attr('id') == 'CardNo3') {
						$('#CardNo4').focus();
					}
				}
			});
		});

		var submit_paySubmit = false;

		function paySubmit() {
			if (submit_paySubmit == false) {
				var form = document.payForm;
				if (form.CardNo1.value.length == 0) {
					alert("카드번호를 확인 하세요!");
					form.CardNo1.focus();
					return;
				}
				if (form.CardNo2.value.length == 0) {
					alert("카드번호를 확인 하세요!");
					form.CardNo2.focus();
					return;
				}
				if (form.CardNo3.value.length == 0) {
					alert("카드번호를 확인 하세요!");
					form.CardNo3.focus();
					return;
				}
				if (form.CardNo4.value.length == 0) {
					alert("카드번호를 확인 하세요!");
					form.CardNo4.focus();
					return;
				}
				form.BuyerAuthNum.value = form.BuyerAuthNum.value.replace(/[^0-9]/g, '');
				form.CardPwd.value = form.CardPwd.value.replace(/[^0-9]/g, '');
				if (form.BuyerAuthNum.value.length != 6 && form.BuyerAuthNum.value.length != 10) {
					alert("본인확인 번호를 입력하세요!");
					form.BuyerAuthNum.focus();
					return;
				}
				if (form.CardPwd.value.length != 2) {
					alert("비밀번호 앞 2자리를 입력하세요!");
					form.CardPwd.focus();
					return;
				}
				submit_paySubmit = true;
				form.action = '/WEBPAY_NCKA2/z6Eu8N7';
				form.submit();

				$('#PAYMENT_CARDINFO').hide();
				$('#PAYMENT_CARDING').show();
			} else {
				alert('결제 진행중 입니다.');
			}
		}

		$(function () {
			$('input[type=number]').on('input', function () {
				var tt = $(this),
					maxLength = tt.attr('maxlength'),
					val = tt.val();
				if (val.length > maxLength) {
					tt.val(val.slice(0, maxLength));
				}
			});
			$('.cardevent-popup').on('click',function(){
				$("#cardevent-popup").show();
			});
			$('.closeBtn').on('click',function(){
				$("#cardevent-popup").hide();
			});
		});
	</script>
	</head>

<body class="smsWrap">

	<div id="PAYMENT_CARDINFO" class="bg-white" data-role="page" data-title="심플페이">

		<div data-role="header">
			<div class="logo ico"><span>SIMPLE</span> PAY</div>
			<h1 class="pull-right">비대면 결제서비스 심플페이</h1>
		</div>

		<div data-role="content">
			<?php /*
			<iframe src="/WEBPAY_C5/z6Eu8N7" name="payforming" id="payforming" style="width:0;height:0;display:none;"></iframe>
			*/ ?>
			<div class="content-wrap">
				<form name="payForm" id="payForm" method="post" onsubmit="return false;" data-ajax="false" target="payforming">
					<?php /*
					<div class="panel-goods bg-white">
						<div class="inner">
							<div class="good-price">
								<span>결제상품<i class="ico ico-arrow-r"></i></span>
								<p>
									PayApp TEST
								</p>
							</div>

							<div class="good-price">
								<span>결제금액<i class="ico ico-arrow-r"></i></span>
								<p>
									1,000<em>원</em>
								</p>
							</div>
						</div>
					</div>
					*/ ?>

					<div class="panel-cont">
						<div class="inner">
							<div class="form-group">
								<label for="CardNo1">상품명</label>
								<div id="CardNoDiv">
									<input type="text" name="input_goodname" id="input_goodname" placeholder="결제할 정확한 상품명 입력 / 한우세트, 의류, 조명기기 등" value="<?php echo $_POST['input_goodname']; ?>" <?php if($_POST['input_goodname']) { echo "readonly"; } ?>>
								</div>
							</div>
							<div class="form-group">
								<div class="half-2">
									<label for="CardPwd">카드주 성함</label>
									<input type="text" name="mem_name" id="mem_name" data-role="none" size="2" maxlength="2" pattern="[0-9]*" inputmode="numeric" placeholder="카드주의 실명" value="<?php echo $_POST['mem_name']; ?>" <?php if($_POST['mem_name']) { echo "readonly"; } ?>>
								</div>
								<div class="half-2">
									<label for="CardPwd">카드주 휴대전화번호</label>
									<input type="number" name="mem_phone" id="mem_phone" data-role="none" size="2" maxlength="2" pattern="[0-9]*" inputmode="numeric" placeholder="카드주의 휴대전화번호" value="<?php echo $_POST['mem_phone']; ?>" <?php if($_POST['mem_phone']) { echo "readonly"; } ?>>
								</div>
							</div>
							<div class="form-group">
								<label for="CardNo1">결제금액</label>
								<div id="CardNoDiv">
									<input type="text" name="input_price" id="input_price" data-role="none" data-mini="true" maxlength="4" min="0" max="9999" pattern="[0-9]*" inputmode="numeric" placeholder="결제금액" value="<?php echo $_POST['input_price']; ?>" <?php if($_POST['input_price']) { echo "readonly"; } ?>>
								</div>
								<div class="guide">
									<small class="gray pull-left">* 숫자만 원단위로 입력</small>
								</div>
							</div>
							<div class="form-group">
								<label for="CardNo1">카드번호</label>
								<div >
									<input type="number" name="cardnumber" id="cardnumber" data-role="none" data-mini="true" min="0" max="9999" pattern="[0-9]*" inputmode="numeric" placeholder="정확한 카드번호를 입력해주세요.">
								</div>
							</div>
							<div class="form-group">
								<label for="expMM">유효기간</label>
								<div class="half-2">
									<div class="select-wrapper">
										<select name="expMM" id="expMM" data-role="none" data-mini="true">
											<option value="01">01월</option>
											<option value="02">02월</option>
											<option value="03">03월</option>
											<option value="04">04월</option>
											<option value="05">05월</option>
											<option value="06">06월</option>
											<option value="07">07월</option>
											<option value="08">08월</option>
											<option value="09">09월</option>
											<option value="10">10월</option>
											<option value="11">11월</option>
											<option value="12">12월</option>
										</select>
									</div>
								</div>
								<div class="half-2">
									<div class="select-wrapper">
										<select name="expYY" id="expYY" data-role="none" data-mini="true">
											<option value="24">2024년</option>
											<option value="25">2025년</option>
											<option value="26">2026년</option>
											<option value="27">2027년</option>
											<option value="28">2028년</option>
											<option value="29">2029년</option>
											<option value="30">2030년</option>
											<option value="31">2031년</option>
											<option value="32">2032년</option>
											<option value="33">2033년</option>
											<option value="34">2034년</option>
										</select>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label>할부선택<small class="orange pull-right cardevent-popup" data-popup="#cardevent-popup"><i class="ico ico-mark-qu-orange"></i> 무이자 할부 안내</small></label>
								<div class="select-wrapper">
									<select name="CardQuota" id="CardQuota" data-role="none" data-mini="true">
										<option value="0">일시불</option>
										<?php for($h=2; $h<=$member_pg['pg_hal']; $h++) { ?>
										<option value="<?php echo $h; ?>"><?php echo $h; ?>개월</option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<div class="half-2">
									<label for="CardPwd">비밀번호</label>
									<input type="password" name="CardPwd" id="CardPwd" data-role="none" size="2" maxlength="2" pattern="[0-9]*" inputmode="numeric" placeholder="앞 2자리">
								</div>
								<div class="half-2">
									<label for="BuyerAuthNum">본인확인<small class="gray pull-right">* 5만원 이상 가능</small></label>
									<input type="number" name="BuyerAuthNum" id="BuyerAuthNum" data-role="none" size="10" maxlength="10" pattern="[0-9]*" inputmode="numeric">
									<div class="guide">
										<small class="gray pull-left">* 개인카드 : 생년월일 6자리<br>* 법인카드 : 사업자번호 10자리</small>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="ui-grid-btn">
						<a class="btn btn-gray" href="javascript:window.close();">취소</a>
						<a class="btn btn-blue" href="javascript:paySubmit();">결제</a>
					</div>

					<input type="hidden" name="UserIP" value="14.47.103.147"/>
					<input type="hidden" name="MallIP" value="192.168.20.100"/>
					<input type="hidden" name="EdiDate" value="20240201221144">
				</form>
			</div>
		</div>
	</div>

	<div tabindex="0" id="PAYMENT_CARDING" class="ui-page ui-page-active bg-white" style="min-height: 604px; display:none;" data-role="page">
		<div class="ui-header" data-role="header" role="banner">
			<h3 class="ui-title" role="heading" aria-level="1">신용카드 결제</h3>
		</div>
		<div class="ui-content" role="main" data-role="content">
			<div class="content-wrap">
				<div class="row-table">
					<div class="row-cell">
						<div id="loadingDiv">
							<div id="loadingArea" style="margin: 0 auto;">
								<div id="loadingPoint_1" class="loadingPoint"></div>
								<div id="loadingPoint_2" class="loadingPoint"></div>
								<div id="loadingPoint_3" class="loadingPoint"></div>
								<div id="loadingPoint_4" class="loadingPoint"></div>
								<div id="loadingPoint_5" class="loadingPoint"></div>
								<div id="loadingPoint_6" class="loadingPoint"></div>
								<div id="loadingPoint_7" class="loadingPoint"></div>
								<div id="loadingPoint_8" class="loadingPoint"></div>
							</div>
						</div>
						<div class="road-msg">
							<p>처리중 입니다. 잠시 기다려 주세요.</p>
							<p class="red">결제완료시 영수증이 출력됩니다.</p>
							<p>출력된영수증은 이메일로 보낸후</p>
							<p>출력하실 수 있습니다.</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="cardevent-popup" class="bg-white" style="display:none;position: absolute; top: 0px;">
	<div data-role="content">
		<div class="panel-title">
			<h1>2월 무이자 행사 안내</h1>
		</div>
		<div class="content-wrap">
			<div class="inner">

				<style>
					* {
						-webkit-box-sizing: border-box;
						-moz-box-sizing: border-box;
						box-sizing: border-box
					}
					:after, :before {
						-webkit-box-sizing: border-box;
						-moz-box-sizing: border-box;
						box-sizing: border-box
					}

					.cardevent_wrap{padding:20px;font-size:14px;color:#666;word-break:keep-all}
					.cardevent_wrap .info_top{padding:15px;margin-bottom:10px;color:#333;border:1px solid #E0EBFC;background:#F5F9FF;text-align:center}
					.cardevent_wrap .info_top p:first-child{font-size:16px;margin:0 0 5px}
					/*.cardevent_wrap .info_top p:last-child{color:#333;margin:0}*/
					/*.cardevent_wrap .info_top p:last-child strong{color:#7FAAFF}*/
					/*.cardevent_wrap .info_top span{display:inline-block;padding:2px 5px;margin-right:3px;font-size:11px;border-radius:10px;background:#7FAAFF;color:#fff;vertical-align:top}*/
					.cardevent_wrap .info_txt{padding:15px;background:#F7F7F7}
					.cardevent_wrap .info_txt em{color:#333;font-weight:bold;font-style:normal}
					.cardevent_wrap .info_txt li{position:relative;padding:3px 0 3px 8px;font-size:13px;list-style:none;line-height:1.35}
					.cardevent_wrap .info_txt li::before{position:absolute;top:10px;left:0;content:'';width:3px;height:3px;background:#333}
					.cardevent_wrap .cardevent_tit{display:block;margin:20px 20px 10px 0;color:#333;font-weight:bold}
					.cardevent_wrap table{width:100%;color:#333;text-align:left;border-collapse:collapse;border-spacing:0;letter-spacing:-0.5px;font-size:13px}
					.cardevent_wrap table td{padding:12px 15px;border:1px solid #e0e0e0;line-height:18px}
					.cardevent_wrap table td:nth-of-type(1){color:#666;text-align:center}
					.cardevent_wrap table img{display:inline-block;margin:0 auto;height:30px;vertical-align:middle;padding-right:4px}
					.cardevent_wrap table p{margin:0 0 5px;font-size:13px;color: #f3504e}
					@media screen and (max-width: 375px) {
						.cardevent_wrap table td:nth-of-type(1){font-size:12px}
						.cardevent_wrap table img{display:block;padding:0;margin-bottom:3px}
					}

					.ban-img{
						margin: 20px auto 0px;
					}
					.ban-img a {
						margin-top:5px;
						display: block;
					}
					.ban-img img {
						width: 100%;
						height: auto;
						padding: 0;
					}

				</style>

				<form>
				<div class="cardevent_wrap">
					<div>
						<div class="info_top">
							<p>
								<strong>카드사별 2월 무이자 할부 안내</strong>
							</p>
							<p>
								<span>대상</span><strong>5만원 이상</strong> 결제 고객
							</p>
						</div>
						<ul class="info_txt">
							<li><em>제외 카드</em> : 사업장(법인,개인), 체크, 선불, 기프트카드는 제외됩니다.</li>
							<li><em>제외 가맹점</em> : 직계약 가맹점 및 상점부담무이자 가맹점, 특별 제휴 가맹점 등 일부 가맹점은 제외될 수 있습니다.</li>
							<li style="color:#f3504e"><em style="color:#f3504e">네이버페이/스마일페이</em> : 할부개월 선택 시 무이자할부 행사 안내가 있는 경우에만 할부 혜택이 제공됩니다.</li>
							<li><em>BC 유의사항</em> : 신한, KB, 하나, 우리, IBK, NH, SC, 대구, 부산, 경남, 씨티 BC 카드의 경우 BC카드 무이자 정책이 적용되며, 그 외 발급사 카드(광주, 전북, 제주, 씨티비자 등)의 경우 BC카드 무이자 정책이 아닌 각 발급사(은행사)의 무이자 정책이 적용됩니다.</li>
							<li><em>무이자 표기</em> : 카드 결제창에서 결제방식에 따라 "8개월 무이자" 식으로 "무이자"가 표기된 경우엔 해당 조건을 따르며, "무이자"가 표기되지 않더라도 행사조건을 충족하는 경우 무이자 이벤트가 적용됩니다.</li>
							<li><em>행사조건외 할부개월수 결제 시 유의사항</em> : 무이자/부분무이자조건 외의 개월수로 할부결제 하실 경우 모든 회차에 카드사 할부수수료가 청구됩니다.</li>
						</ul>
					</div>
					<strong class="cardevent_tit">무이자 행사</strong>
					<table>
						<colgroup><col style="width:35%" bgcolor="#f2f2f2"><col></colgroup>
						<tr>
							<td><img src="./img/cardevent/lg_kb.png" alt="국민카드"> 국민카드</td>
							<td rowspan="6" style="text-align:center">2~3개월</td>
							<td rowspan="9" style="text-align:center">5만원 이상</td>
						</tr>
						<tr>
							<td><img src="./img/cardevent/lg_sinhan.png" alt="신한카드"> 신한카드</td>
						</tr>
						<tr>
							<td><img src="./img/cardevent/lg_samsung.png" alt="삼성카드"> 삼성카드</td>
						</tr>
						<tr>
							<td><img src="./img/cardevent/lg_lotte.png" alt="롯데카드"> 롯데카드</td>
						</tr>
						<tr>
							<td><img src="./img/cardevent/lg_hana.png" alt="하나카드"> 하나카드</td>
						</tr>
						<tr>
							<td><img src="./img/cardevent/lg_hyundai.png" alt="현대카드"> 현대카드</td>
						</tr>
						<tr>
							<td><img src="./img/cardevent/lg_nh.png" alt="농협카드"> 농협카드</td>
							<td style="text-align:center" rowspan="3">2~4개월</td>
						</tr>
						<tr>
							<td><img src="./img/cardevent/lg_woori.png" alt="우리카드"> 우리카드</td>
						</tr>
						<tr>
							<td><img src="./img/cardevent/lg_bc.png" alt="BC카드"> 비씨카드</td>
						</tr>
					</table>

					<span class="cardevent_tit">부분 무이자 행사</span>

					<table>
						<colgroup><col style="width:35%" bgcolor="#f2f2f2"><col></colgroup>
						<tr>
							<td><img src="./img/cardevent/lg_kb.png" alt="국민카드"> 국민카드</td>
							<td>6개월: 1~3회차 고객부담<br>10개월: 1~5회차 고객부담</td>
						</tr>
						<tr>
							<td><img src="./img/cardevent/lg_samsung.png" alt="삼성카드"> 삼성카드</td>
							<td>7개월: 1~3회차 고객부담<br>11개월: 1~5회차 고객부담</td>
						</tr>
						<tr>
							<td><img src="./img/cardevent/lg_hana.png" alt="하나카드"> 하나카드</td>
							<td>6개월: 1~3회차 고객부담<br/>10개월: 1~4회차 고객부담<br/>12개월: 1~5회차 고객부담</td>
						</tr>
						<tr>
							<td><img src="./img/cardevent/lg_woori.png" alt="우리카드"> 우리카드</td>
							<td>10개월: 1~3회차 고객부담<br/>12개월: 1~4회차 고객부담</td>
						</tr>
						<tr>
							<td><img src="./img/cardevent/lg_sinhan.png" alt="신한카드"> 신한카드</td>
							<td>10개월: 1~4회차 고객부담<br/>12개월: 1~5회차 고객부담</td>
						</tr>
						<tr>
							<td><img src="./img/cardevent/lg_bc.png" alt="BC카드"> 비씨카드</td>
							<td>10개월: 1~3회차 고객부담<br/>12개월: 1~4회차 고객부담</td>
						</tr>
						<tr>
							<td><img src="./img/cardevent/lg_nh.png" alt="농협카드"> 농협카드</td>
							<td>5~6개월: 1~2회차 고객부담<br/>7~10개월: 1~3회차 고객부담</td>
						</tr>
					</table>
					<?php /*
					<div class="ban-img">
						<a href="https://junggopay.com/main" target="_blank" title="새창열림"><img src="./img/cardevent/img_junggopay.jpg" alt="페이앱이 보증하는 중고거래 카드결제 - 중고페이. 판매자 동의 필요 없이 내맘대로 중고거래 무이자 할부하기!"></a>
						<a href="https://danbipay.com/main" target="_blank" title="새창열림"><img src="./img/cardevent/img_danbipay.jpg" alt="월세도 할부 결제 됩니다. 페이앱이 보증하는 월세카드결제 - 단비페이"></a>
					</div>
					*/ ?>
				</div>
				</form>

			</div>
			<div class="ui-grid-btn">
				<a class="closeBtn btn btn-blue" data-role="button">닫기</a>
			</div>
		</div>
	</div>
</div>

<form name="payresultform" method="post">
	<input type="hidden" name="ResultCode" value="">
	<input type="hidden" name="ResultMsg" value="">
	<input type="hidden" name="ordno" value="2402012211700131">
	<input type="hidden" name="Amount" value="1000">
	<input type="hidden" name="CardNo" value="">
	<input type="hidden" name="retry_url" value="https://www.payapp.kr/z6kHLs0">
</form>

</body>
</html>
