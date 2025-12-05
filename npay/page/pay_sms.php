<?php
	include_once("./_head.php");
	$pp = "pay_sms";
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
							<label class="label label-round label-violetblue"><b>PC</b></label>
						</div>
						<h2>비대면 판매가 필요한 순간<br>SMS, 카톡으로 결제요청</h2>
						<p>구매자 휴대폰으로 통합결제창을 열 수 있는 링크를<br><span class="text-blue">SMS 또는 카톡으로 발송해 결제</span> 받을 수 있습니다.</p>
					</div>
					<div class="tag-area">
						<span>#한국_최초의_원격결제솔루션_페이앱</span>
						<span>#링크전송</span><br>
						<span>#언제어디서나</span>
						<span>#언택트_결제_No.1</span>
					</div>
				</div>
				<div class="video-wrap">
					<article class="video-area">
						<video loop="" autoplay="" muted="" playsinline="">
							<!-- 800*690 -->
							<source src="https://payapp.kr/homepage/images/video/mov_pay_sms.mp4" type="video/mp4">
						</video>
					</article>
				</div>
			</div>
		</section>
		<!-- //main-section -->
		<section class="use-section">
			<div class="inner">
				<div class="heading-sub">
					<h2>SMS/카톡결제 사용방법</h2>
				</div>
				<div class="guide-slider">
					<ol class="guide-area">
						<li class="col-4">
							<strong><b>1</b>원격결제 클릭</strong>
							<img src="https://payapp.kr/homepage/images/pay/sms_sec2_guide1.gif" alt="원격결제 화면">
						</li>
						<li class="col-4">
							<strong><b>2</b>SMS결제 요청 클릭</strong>
							<img src="https://payapp.kr/homepage/images/pay/sms_sec2_guide2.gif" alt="SMS결제 요청 화면">
						</li>
						<li class="col-4">
							<strong><b>3</b>SMS결제 링크 전송</strong>
							<img src="https://payapp.kr/homepage/images/pay/sms_sec2_guide3.gif" alt="SMS결제 링크 전송 화면">
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