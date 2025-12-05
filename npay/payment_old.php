<?php
	include_once('./_common.php');
	if(!$url_code) {
		if($mb_id != $member['mb_id']) {
			alert("로그인한 사람과 결제자가 다릅니다.");
			exit;
		}
	}
	$mb = get_member($mb_id);
	$member_pg = sql_fetch(" select * from pay_member_pg where mb_id = '{$mb_id}' and pg_id = '{$pg_id}' ");
	if($member_pg['pg_use'] == '1') {
		alert_close("사용할 수 없는 결제모듈입니다.");
	}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
	<meta name="format-detection" content="telephone=no" />
	<meta property="og:type" content="website">
	<meta property="og:title" content="심플페이">
	<meta property="og:image" content="https://payapp.kr/new_home/img/meta_payapp_logo.png">
	<meta property="og:image:width" content="800">
	<meta property="og:image:height" content="400">
	<meta property="og:description" content="유유소프트">
	<meta property="og:site_name" content="페이앱 공식 홈페이지">
	<title>심플페이</title>
	<script src="./js/jquery-1.9.1.min.js"></script>
	<script src="./js/jquery.mobile-1.3.0.min.js"></script>
	<link rel="stylesheet" type="text/css" href="./css/reset.css?ver=<?php echo time(); ?>">
	<link rel="stylesheet" type="text/css" href="./css/payment.css?ver=<?php echo time(); ?>">
	<script type="text/javascript">
		$(document).bind( "pagebeforechange", function( e, data ) {
		});
		$(document).bind('pageinit',function(){
		});
		$(document).bind("mobileinit", function(){
			$.mobile.ajaxEnabled = false;
		});
		$(function() {
			$('a#payment_call').css('cursor', 'pointer').on('click', function () {
				var f = $(document.frmNext);
				if ($('#input_goodname', f).val() == '') {
					alert('결제하실 상품명을 입력하세요.');
					$("#input_goodname").focus();
					return;
				}
				if ($('#input_price', f).val() == '') {
					alert('결제하실 금액을 입력하세요.');
					$("#input_price").focus();
					return;
				}
				if ($('#mem_name', f).val() == '') {
					alert('카드주 실명을 입력하세요.');
					$("#mem_name").focus();
					return;
				}
				if ($('#mem_phone', f).val() == '' || $('#mem_phone', f).val() == '0101234XXXX') {
					alert('카드주 휴대전화 번호를 입력하세요.');
					$("#mem_phone").focus();
					return;
				}
				if (!$('input#agreeTerm', f).prop('checked')) {
					alert('이용약관에 동의하세요.');
					$("input#agreeTerm").focus();
					return;
				}
				if (!$('input#agreePriv', f).prop('checked')) {
					alert('개인정보 처리방침에 동의하세요.');
					$("input#agreePriv").focus();
					return;
				}
				//$(f).submit();
				f.attr('action', './payment_request.php').submit();
			});
			$('#input_price').on('keyup input', function () {
				var f = $(document.frmNext);
				var num = $('#input_price', f).val().replace(/[^0-9]/g, ''), n = '', idx = 0;
				for (var i = 0; i < num.length; i += 3) {
					idx = num.length - (3 + i);
					n = num.substr((idx < 0 ? 0 : idx), 3 + (idx < 0 ? idx : 0)) + (n == '' ? n : ',' + n);
				}
				$('#input_price', f).val(n);
			});
			$('#price', $(document.forms[0])).on('change',function () {
				if ($(this).val() == 'input_price') {
					$('#input_price').parent().parent().show();
				} else {
					$('#input_price').parent().parent().hide();
				}
			});
			$('input#agreeCheckAll:checkbox').on('click', function () {
				$('input#agreeTerm,input#agreePriv').prop('checked', $(this).prop('checked'));
			});
			$('input#agreeTerm,input#agreePriv').on('change', function () {
				var checked = $('input#agreeTerm').prop('checked') && $('input#agreePriv').prop('checked');
				$('input#agreeCheckAll').prop('checked', checked);
			});
		});
		function popwinclose(){
			window.close();
		}
	</script>
</head>
<body class="smsWrap">
	<div id="page1" class="bg-white" data-role="page" data-title="심플페이">

		<div data-role="header">
			<div class="logo ico"><span>SIMPLE</span> PAY</div>
			<h1 class="pull-right">비대면 결제서비스 심플페이</h1>
		</div>

		<div data-role="content">
			<div class="content-wrap">

				<form action="./payment_request.php" method="POST" name="frmNext" id="frmNext" onsubmit="return false;">
				<input type="hidden" name="mb_id" value="<?php echo $mb_id; ?>">
				<input type="hidden" name="pg_id" value="<?php echo $pg_id; ?>">

					<div class="sell-name">
						<div class="inner">
							<dl class="clearfix">
								<dt>가맹점</dt>
								<dd>-&nbsp;&nbsp;&nbsp;<?php echo $mb['mb_nick']; ?></dd>
							</dl>
							<dl class="clearfix">
								<dt>업종</dt>
								<dd>-&nbsp;&nbsp;&nbsp;<?php echo $mb['mb_1']; ?></dd>
							</dl>
							<dl class="clearfix">
								<dt>업태</dt>
								<dd>-&nbsp;&nbsp;&nbsp;<?php echo $mb['mb_2']; ?></dd>
							</dl>
							<dl class="clearfix">
								<dt>PG사</dt>
								<dd>-&nbsp;&nbsp;&nbsp;<?php echo $member_pg['pg_name']; ?></dd>
							</dl>
							<dl class="clearfix">
								<dt>최대한도</dt>
								<dd>-&nbsp;&nbsp;&nbsp;<?php echo number_format($member_pg['pg_pay']); ?>원</dd>
							</dl>
							<dl class="clearfix">
								<dt>최대할부</dt>
								<dd>-&nbsp;&nbsp;&nbsp;<?php if($member_pg['pg_hal'] == 0) { echo "일시불"; } else { echo $member_pg['pg_hal']."개월"; } ?></dd>
							</dl>
						</div>
					</div>

					<div class="panel-goods">

						<div class="inner">
							<h3>상품명</h3>
							<input type="text" name="input_goodname" id="input_goodname" value="" placeholder="결제하실 상품명을 입력하세요." data-role="text" data-mini="true" class="form-control">
							<h3>결제금액</h3>
							<input type="text" name="input_price" id="input_price" placeholder="결제하실 금액을 입력하세요." value="" data-role="none" data-mini="true" class="form-control">
							<h3>카드주 실명</h3>
							<input type="text" pattern="[0-9]*" name="mem_name" placeholder="카드주 실명을 입력하세요." id="mem_name" maxlength="11" data-role="text" data-mini="true" class="form-control">
							<h3>카드주 휴대전화</h3>
							<input type="text" pattern="[0-9]*" name="mem_phone" placeholder="카드주 휴대전화 번호를 입력하세요." id="mem_phone" maxlength="11" data-role="text" data-mini="true" class="form-control" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
							<?php /*
							<div class="description">
								<p><i class="ico ico-mark-ex"></i>수기결제는 "결제요청 SMS발송" 버튼을 눌러주세요.</p>
							</div>
							*/ ?>

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

						</div>
					</div>
					<div class="ui-grid-btn">
						<a id="payment_call" class="btn btn-blue">바로결제</a>
					</div>
				</form>
			</div>
		</div>
	</div>

</body>
</html>
