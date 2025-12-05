<?php
	include_once("./_head.php");
	$pp = "pay_camera";
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
						<h2>리더기없이<br>스마트폰으로 바로결제</h2>
						<p>스마트폰 카메라로 카드를 스캔하는 순간<br><span class="text-blue">카드번호 입력할 필요없이 즉시 결제가 완료</span>됩니다.</p>
					</div>
					<div class="tag-area">
						<span>#오프라인결제</span>
						<span>#스캔방식</span>
						<span>#정보미저장</span>
					</div>
				</div>
				<div class="video-wrap">
					<article class="video-area">
						<video loop="" autoplay="" muted="" playsinline="">
							<!-- 800*690 -->
							<source src="https://payapp.kr/homepage/images/video/mov_pay_camera.mp4" type="video/mp4">
						</video>
					</article>
				</div>
			</div>
		</section>
		<!-- //main-section -->
		<section class="use-section">
			<div class="inner">
				<div class="heading-sub">
					<h2>카메라결제 사용방법</h2>
				</div>
				<div class="guide-slider">
					<ol class="guide-area">
						<li class="col-3">
							<strong><b>1</b>폰 대면결제 클릭</strong>
							<img src="https://payapp.kr/homepage/images/pay/camera_sec2_guide1.gif" alt="폰 대면결제 화면">
						</li>
						<li class="col-3">
							<strong><b>2</b>카메라결제 클릭</strong>
							<img src="https://payapp.kr/homepage/images/pay/camera_sec2_guide2.gif" alt="카메라결제 화면">
						</li>
						<li class="col-3">
							<strong><b>3</b>카드스캔하기</strong>
							<img src="https://payapp.kr/homepage/images/pay/camera_sec2_guide3.gif" alt="카드스캔하기 화면">
						</li>
						<li class="col-3">
							<strong><b>4</b>결제완료</strong>
							<img src="https://payapp.kr/homepage/images/pay/camera_sec2_guide4.gif" alt="결제완료 화면">
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