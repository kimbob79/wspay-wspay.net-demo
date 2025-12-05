<?php
	include_once("./_head.php");
	$pp = "pay_recurring";
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
						<h2>정해진 주기마다 같은 금액을<br>자동으로 결제 받는 정기결제</h2>
						<p><span class="text-blue">결제 금액과 주기를 미리 설정</span>하면<br>저장된 정보에 따라 <span class="text-blue">자동으로 결제가 진행</span>됩니다.</p>
					</div>
					<div class="tag-area">
						<span>#최초1회설정</span>
						<span>#암호화된인증키</span>
						<span>#자동결제시스템</span>
					</div>
					<div class="word-area">
						<dl>
							<dt><strong>정기결제</strong></dt>
							<dd>정해진 주기마다 같은 금액이 자동으로 결제 </dd>
						</dl>
						<dl>
							<dt><strong>등록결제</strong></dt>
							<dd>등록된 인증 키를 바탕으로 판매자 요청에 의해 자동결제<br>
								수시 결제가 가능하며, 금액이 변동되는 경우에도 자동결제 가능</dd>
						</dl>
					</div>
				</div>
				<div class="video-wrap">
					<article class="video-area">
						<video loop="" autoplay="" muted="" playsinline="">
							<!-- 800*690 -->
							<source src="https://payapp.kr/homepage/images/video/mov_pay_period.mp4" type="video/mp4">
						</video>
					</article>
				</div>
			</div>
		</section>

		<section class="use-section">
			<div class="inner">
				<div class="heading-sub">
					<h2>정기결제 사용방법</h2>
				</div>
				<div class="guide-slider">
					<ol class="guide-area">
						<li class="col-3">
							<strong><b>1</b>정기결제 클릭</strong>
							<img src="https://payapp.kr/homepage/images/pay/period_sec2_guide1.gif" alt="정기결제 화면">
						</li>
						<li class="col-3">
							<strong><b>2</b>정기결제 요청</strong>
							<img src="https://payapp.kr/homepage/images/pay/period_sec2_guide2.gif" alt="정기결제 요청 화면">
						</li>
						<li class="col-3">
							<strong><b>3</b>정기결제 리스트</strong>
							<img src="https://payapp.kr/homepage/images/pay/period_sec2_guide3.gif" alt="정기결제 리스트 화면">
						</li>
						<li class="col-3">
							<strong><b>4</b>상세보기</strong>
							<img src="https://payapp.kr/homepage/images/pay/period_sec2_guide4.gif" alt="상세보기 화면">
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