<?php
	include_once("./_head.php");
	$pp = "pay_touch";
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
						<h2>스마트폰에<br>터치만 하면 결제완료</h2>
						<p>대면결제시 번거로운 결제기 필요없이<br><span class="text-blue">간단한 터치만으로 결제가 가능</span>합니다.</p>
					</div>
					<div class="tag-area">
						<span>#터치기능지원</span>
						<span>#NFC결제</span>
						<span>#삼성페이</span>
					</div>
				</div>
				<div class="video-wrap">
					<article class="video-area">
						<video loop="" autoplay="" muted="" playsinline="">
							<!-- 800*690 -->
							<source src="https://payapp.kr/homepage/images/video/mov_pay_touch.mp4" type="video/mp4">
						</video>
					</article>
				</div>
			</div>
		</section>
		<!-- //main-section -->
		<section class="use-section">
			<div class="inner">
				<div class="heading-sub">
					<h2>터치결제 사용방법</h2>
				</div>
				<div class="guide-slider">
					<ol class="guide-area">
						<li class="col-3">
							<strong><b>1</b>폰 대면결제 클릭</strong>
							<img src="https://payapp.kr/homepage/images/pay/touch_sec2_guide1.gif" alt="폰 대면결제 화면">
						</li>
						<li class="col-3">
							<strong><b>2</b>NFC/삼성페이 클릭</strong>
							<img src="https://payapp.kr/homepage/images/pay/touch_sec2_guide2.gif" alt="NFC/삼성페이 화면">
						</li>
						<li class="col-3">
							<strong><b>3</b>휴대폰 뒷면에 접촉</strong>
							<img src="https://payapp.kr/homepage/images/pay/touch_sec2_guide3.gif" alt="휴대폰 뒷면에 접촉 화면">
						</li>
						<li class="col-3">
							<strong><b>4</b>결제완료</strong>
							<img src="https://payapp.kr/homepage/images/pay/touch_sec2_guide4.gif" alt="결제완료 화면">
						</li>
					</ol>
				</div>
				<div class="btn-center-block">
					<a href="javascript:;" class="btn btn-gray-line modal-trigger" data-modal-id="pay-touch-notice">유의사항 안내</a>
				</div>
			</div>
		</section>
	</section>
</section>

<div id="pay-touch-notice" class="modal modal-sm">
	<div class="modal-header">
		<h2>NFC 결제 유의사항</h2>
	</div>
	<div class="modal-content">
		<ul class="info-list">
			<li>(1) 폰 to 폰 결제의 경우 판매자의 핸드폰이 NFC기능을 지원해야 하며, 구매자의 핸드폰에는 삼성페이가 지원되어야 합니다.</li>
			<li>(2) 폰 to 카드 결제의 경우 구매자의 카드가 후불교통카드 기능이 있거나 비자카드의 paywave 또는 마스터카드의 paypass 기능이 지원되어야 합니다.</li>
			<li>(3) 아이폰 및 LG페이는 사용 불가 합니다.</li>
		</ul>
	</div>
	<div class="modal-footer">
		<span role="button" class="btn btn-black btn-block modal-close">확인</span>
	</div>
</div>

<?php
	include_once("./_foot.php");
?>