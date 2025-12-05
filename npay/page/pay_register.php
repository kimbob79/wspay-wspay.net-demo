<?php
	include_once("./_head.php");
	$pp = "pay_register";
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
							<label class="label label-round label-violetblue"><b>PC</b></label>
						</div>
						<h2>결제정보 안심등록 후<br>수시로 변동금액까지<br>바로 결제 받는 등록결제</h2>
						<p>
							<span class="text-blue">고객 결제정보를 암호화된 인증키로 저장</span>한 뒤<br>
							판매자 요청 시 저장된 정보를 불러와 <span class="text-blue">바로 결제하는 서비스</span>입니다.<br>
							등록결제를 잘 활용하시면 유수의 간편결제처럼 <span class="br-break-sm"><em class="text-blue">자사 만의 전용 페이화</em> 할 수 있습니다.</span>
						</p>
					</div>
					<div class="tag-area">
						<span>#최초1회설정</span>
						<span>#암호화된인증키</span>
						<span>#자동결제시스템</span><br>
						<span>#나만의전용페이</span>
						<span>#수시결제가능</span>
						<span>#변동금액도가능</span>
					</div>
					<div class="word-area">
						<dl>
							<dt><strong>정기결제</strong></dt>
							<dd>정해진 주기마다 같은 금액이 자동으로 결제</dd>
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
							<source src="https://payapp.kr/homepage/images/video/mov_pay_register.mp4" type="video/mp4">
						</video>
					</article>
				</div>
			</div>
		</section>
		<section class="use-section">
			<div class="inner">
				<div class="heading-sub">
					<h2>등록결제 사용방법</h2>
				</div>
				<ol class="guide-area guide-vertical">
					<li>
						<strong><b>1</b>정기결제 창에서 등록결제(BILL) 등록 클릭</strong>
						<img src="https://payapp.kr/homepage/images/pay/regist_sec2_guide1.jpg" alt="등록결제 등록 화면">
					</li>
					<li>
						<strong><b>2</b>필수정보 입력으로 등록결제 빌키 등록</strong>
						<img src="https://payapp.kr/homepage/images/pay/regist_sec2_guide2.jpg" alt="등록결제 빌키 등록 화면">
					</li>
					<li>
						<strong><b>3</b>빌키 리스트에서 원하는 등록결제를 골라 [결제요청]</strong>
						<img src="https://payapp.kr/homepage/images/pay/regist_sec2_guide3.jpg" alt="결제요청 화면">
					</li>
					<li>
						<strong><b>4</b>필수정보 입력 후 [확인] 클릭 시 바로 결제완료</strong>
						<img src="https://payapp.kr/homepage/images/pay/regist_sec2_guide4.jpg" alt="결제완료 화면">
					</li>
				</ol>
			</div>
		</section>
	</section>
</section>

<?php
	include_once("./_foot.php");
?>