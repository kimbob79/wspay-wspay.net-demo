<?php
	include_once("./_head.php");
	$p = "pay";
	$pp = "pay_simple";
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
						<h2>전자결제<br>(웹사이트 결제)</h2>
						<p>
							전자결제 서비스는<br>
							<span class="linebreak">쇼핑몰이나 웹사이트에서 이용하는 <span class="br-break-sm">온라인 결제 서비스 입니다.</span></span>
							<span class="linebreak">페이앱에서 제공하는 다양한 <span class="br-break-sm">결제 수단을 전자결제 서비스 하나로</span></span>
							고객님의 사이트/쇼핑몰에서 이용할 수 있습니다.
						</p>
					</div>
					<div class="tag-area">
						<span>#PG결제수단</span>
						<span>#전자결제</span>
						<span>#온라인결제서비스</span>
					</div>
				</div>
				<div class="video-wrap">
					<img src="https://payapp.kr/homepage/images/pay/paysimple_visual.png">
				</div>
			</div>
		</section>
		<!-- //main-section -->
		<section class="ico-section">
			<div class="inner">
				<ul class="box-group">
					<li class="item">
						<i class="ico ico-simple ico-simple1"></i>
						<p>
							페이앱과의 PG계약 만으로
							<span class="br-break-sm">국내 모든 카드사의 <span class="br-break-lg">신용카드 <em class="br-break-sm">결제를 이용할 수 있습니다.</em></span></span>
							(ISP 인증결제, 수기결제 등)
						</p>
					</li>
					<li class="item">
						<i class="ico ico-simple ico-simple2"></i>
						<p>
							신용카드, 휴대폰결제 외에도 네이버페이,
							<span class="linebreak">
								<span class="br-break-sm">카카오페이, 애플페이,</span>
								<span class="br-break-sm">삼성페이, 페이코 등</span>
							</span>
							간편결제가 기본 탑재돼 있습니다.
						</p>
					</li>
					<li class="item">
						<i class="ico ico-simple ico-simple3"></i>
						<p>
							현금영수증 발행 등<br>
							<span class="br-break-sm">다양한 부가서비스를</span> 제공합니다.
						</p>
					</li>
					<li class="item">
						<i class="ico ico-simple ico-simple4"></i>
						<p>
							PC와 모바일 등 <span class="br-break-sm">다양한 환경에 최적화된</span>
							<span class="linebreak">반응형 결제 UI를 제공합니다.</span>
						</p>
					</li>
					<li class="item">
						<i class="ico ico-simple ico-simple5"></i>
						<p>
							결제관리 및 매출 관리를
							<span class="linebreak"><span class="br-break-sm">쉽고 편리하게 할 수 있는 </span>관리자 페이지를 제공합니다.</span>
						</p>
					</li>
					<li class="item">
						<i class="ico ico-simple ico-simple6"></i>
						<p>
							국내 카드사의 2개월~6개월
							<span class="linebreak">상시 무이자 할부를 <span class="br-break-sm">지원합니다.</span></span>
						</p>
					</li>
				</ul>
			</div>
		</section>
		<!-- //ico-section -->
		<section class="screen-section">
			<div class="inner">
				<div class="heading-sub text-center">
					<h2>전자결제<span class="br-break-sm">(웹사이트 결제) 화면</span></h2>
				</div>
				<div class="screen-wrap text-center">
					<div class="screen">
						<strong class="label label-round label-blue-line">카드결제</strong>
						<div class="screen-slider">
							<ol class="screen-area">
								<li><img src="https://payapp.kr/homepage/images/pay/simple_sec2_screen1.png" alt="카드결제 화면1"></li>
								<li><img src="https://payapp.kr/homepage/images/pay/simple_sec2_screen2.png" alt="카드결제 화면2"></li>
								<li><img src="https://payapp.kr/homepage/images/pay/simple_sec2_screen3.png" alt="카드결제 화면3"></li>
							</ol>
						</div>
					</div>
					<!-- //카드결제 -->
					<div class="screen">
						<strong class="label label-round label-blue-line">무통장결제</strong>
						<div class="screen-slider">
							<ol class="screen-area">
								<li><img src="https://payapp.kr/homepage/images/pay/simple_sec2_screen1.png" alt="무통장결제 화면1"></li>
								<li><img src="https://payapp.kr/homepage/images/pay/simple_sec2_screen4.png" alt="무통장결제 화면2"></li>
								<li><img src="https://payapp.kr/homepage/images/pay/simple_sec2_screen5.png" alt="무통장결제 화면3"></li>
							</ol>
						</div>
					</div>
					<!-- //무통장결제 -->
				</div>
			</div>
		</section>
		<!-- //screen-section -->
		<section class="info-section">
			<div class="inner">
				<strong class="col-4">서비스 이용안내</strong>
				<ul class="col-8">
					<li><b>1</b>페이앱은 기본적으로 <a href="https://seller.payapp.kr/a/seller_regist" target="_blank">무료 간편가입</a> 후 즉시 결제받기가 가능합니다.</li>
					<li><b>2</b>웹사이트 PG 연동이 필요하신 경우 <a href="https://www.payapp.kr/dev_center/dev_center01.php" target="_blank">결제 API 연동 매뉴얼</a>을 참고해 사이트에 바로 적용하세요.</li>
					<li><b>3</b>시스템 적용 후 테스트가 완료되면 즉시 전자결제 서비스 이용이 가능합니다.</li>
				</ul>
			</div>
		</section>
	</section>
</section>



<?php
	include_once("./_foot.php");
?>