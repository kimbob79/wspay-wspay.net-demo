<?php
	include_once("./_head.php");
	$pp = "pay_wechatpay";
?>

<section class="container" id="pay">
	<section class="contents">
		<section class="inner">
			<!--<div class="heading-tit">
				<h2>결제방식</h2>
				<p>페이앱에서 이용가능한 다양한 결제방법</p>
			</div>-->
			<script>
				// tab group
				function tabFixed() {
					var st = $(window).scrollTop(),
						payH = $('#pay .main-section').offset().top -76; // header Height

					if( st > payH ) {
						$(".tab-group").addClass("fixed");
					} else {
						$(".tab-group").removeClass("fixed");
					}
				}
				$(document).ready(function(){ tabFixed(); });
				$(window).scroll(function(){ tabFixed(); });
				$('.tab-group .tab-menu .active').ready(function(){
					//$('.tab-group').scrollLeft($('.tab-group .tab-menu .active').position().left);
					$('.tab-group').stop().animate({scrollLeft:$('.tab-group .tab-menu .active').position().left},1000);
				});
			</script>
			<?php
				include_once("./_menu_pay.php");
			?>
		</section>

		<section class="main-section">
			<div class="inner-lg">
				<div class="title-wrap">
					<div class="heading-main">
						<div class="label-wrap">
							<label class="label label-round label-aquablue"><b>APP</b></label>
						</div>
						<h2>위챗페이<br>결제수납 즉시 가능</h2>
						<p class="pay-txt-box">
							기존 페이앱 고객은 위챗페이 신청만 하시면
							즉시 서비스 사용이 가능합니다.
							Phone to Phone 서비스로 별도의 장비 구입이 필요 없으며,
							편리하게 원화(KRW)로 결제하면
							소비자 <span class="text-blue">위안화(CNY)로 자동으로</span> 결제되며 원화로 정산해 드립니다.
						</p>
					</div>
					<div class="tag-area">
						<span>#간편가입 바로 OPEN</span>
						<span>#장비비용 부담 Zero</span>
						<span>#환율 걱정 無</span>
					</div>
				</div>
				<div class="video-wrap">
					<img src="https://payapp.kr/homepage/images/pay/wechatpay_visual.png">
				</div>
			</div>
		</section>
		<!-- //main-section -->
		<section class="use-section">
			<div class="inner">
				<div class="heading-sub text-center">
					<h2>위챗페이 신청방법</h2>
				</div>
				<div class="guide-slider">
					<ol class="guide-area wechatpay-guide">
						<li class="col-4">
							<strong><b>1</b>환경설정 클릭</strong>
							<img src="https://payapp.kr/homepage/images/pay/wechatpay_sec2_guide1.png" alt="위챗페이 신청화면1">
						</li>
						<li class="col-4">
							<strong><b>2</b>결제설정 클릭</strong>
							<img src="https://payapp.kr/homepage/images/pay/wechatpay_sec2_guide2.png" alt="위챗페이 신청화면2">
						</li>
						<li class="col-4">
							<strong><b>3</b>위챗페이 신청</strong>
							<img src="https://payapp.kr/homepage/images/pay/wechatpay_sec2_guide3.png" alt="위챗페이 신청화면3">
						</li>
						<li class="col-4">
							<strong><b>4</b>사업자정보 확인</strong>
							<img src="https://payapp.kr/homepage/images/pay/wechatpay_sec2_guide4.png" alt="위챗페이 신청화면4">
						</li>
						<li class="col-4">
							<strong><b>5</b>판매물품 업종 선택</strong>
							<img src="https://payapp.kr/homepage/images/pay/wechatpay_sec2_guide5.png" alt="위챗페이 신청화면5">
						</li>
						<li class="col-4">
							<strong><b>6</b>등록완료</strong>
							<img src="https://payapp.kr/homepage/images/pay/wechatpay_sec2_guide6.png" alt="위챗페이 신청화면6">
						</li>
					</ol>
				</div>
				<div class="heading-sub text-center">
					<h2>위챗페이 결제방법</h2>
				</div>
				<div class="guide-slider">
					<ol class="guide-area wechatpay-guide">
						<li class="col-4">
							<strong><b>1</b>결제금액(KRW) 입력</strong>
							<img src="https://payapp.kr/homepage/images/pay/wechatpay_sec3_guide1.png" alt="위챗페이 결제화면1">
						</li>
						<li class="col-4">
							<strong><b>2</b>위챗페이 전용 QR</strong>
							<img src="https://payapp.kr/homepage/images/pay/wechatpay_sec3_guide2.png" alt="위챗페이 결제화면2">
						</li>
						<li class="col-4">
							<strong><b>3</b>결제완료</strong>
							<img src="https://payapp.kr/homepage/images/pay/wechatpay_sec3_guide3.png" alt="위챗페이 결제화면3">
						</li>
					</ol>
				</div>
			</div>
		</section>
	</section>
</section>

<?php
	include_once("./_foot.php");
?>