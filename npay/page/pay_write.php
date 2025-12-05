<?php
	include_once("./_head.php");
	$pp = "pay_write";
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
						<h2>별도 특약 필요 없는<br>간편 수기결제 (키인결제)</h2>
						<p>별도 수기특약 없이 카드번호, 유효기간 등<br><span class="text-blue">카드정보 직접입력을 통해 간편 수기결제가 가능</span>합니다.</p>
					</div>
					<div class="tag-area">
						<span>#비대면으로_고객이_직접_카드정보_입력가능</span><br>
						<span>#상담사결제_전화결제_시에도_유용</span>
					</div>
				</div>
				<div class="video-wrap">
					<article class="video-area">
						<video loop="" autoplay="" muted="" playsinline="">
							<!-- 800*690 -->
							<source src="https://payapp.kr/homepage/images/video/mov_pay_write.mp4" type="video/mp4">
						</video>
					</article>
				</div>
			</div>
		</section>
		<!-- //main-section -->
		<section class="use-section">
			<div class="inner">
				<div class="heading-sub">
					<h2>수기결제 사용방법</h2>
				</div>
				<div class="guide-slider">
					<ol class="guide-area">
						<li class="col-6">
							<strong><b>1</b>카드정보 입력</strong>
							<img src="https://payapp.kr/homepage/images/pay/write_sec2_guide1.gif" alt="수기결제 카드정보 입력 화면">
						</li>
						<li class="col-6">
							<strong><b>2</b>결제완료</strong>
							<img src="https://payapp.kr/homepage/images/pay/write_sec2_guide2.gif" alt="수기결제 결제완료 화면">
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