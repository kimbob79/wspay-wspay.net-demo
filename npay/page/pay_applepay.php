<?php
	include_once("./_head.php");
	$pp = "pay_applepay";
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
						<h2>추가비용 없이<br>온오프 애플페이<br>결제수납 즉시 가능</h2>
						<p>페이앱 하나면 NFC 단말기가 없어도<br>온라인, 오프라인 <span class="text-blue">애플페이 결제받기가 바로 가능</span>합니다.</p>
					</div>
					<div class="tag-area">
						<span>#온오프</span>
						<span>#어디서나</span>
						<span>#애플페이</span>
						<span>#결제받기OK</span>
					</div>
				</div>
				<div class="video-wrap">
					<img src="https://payapp.kr/homepage/images/pay/applepay_visual.png">
				</div>
			</div>
		</section>
		<!-- //main-section -->

		<section class="contents" id="guide" style="margin:0">
			<section class="inner">
				<div class="panel" style="margin-top:3em">
					<div class="panel-heading">
						<h3>결제 특장점</h3>
					</div>
					<div class="panel-body">
						<div class="num-list">
							<ul>
								<li>2023년 4월에 페이앱, 블로그페이, 프로셀 등 페이앱 전체 15만 가맹점에 애플페이 결제 서비스 오픈</li>
								<li>온라인에서는 애플페이 결제설정 On 만으로 결제받기 가능</li>
								<li>오프라인 대면결제 시 페이앱 앱 만 설치하면 판매자 스마트폰 만으로 애플페이 결제받기 가능<br>(폰 대면결제 > 애플페이 > QR생성 > 구매자결제로 완료)</li>
								<li>대면결제 시 별도의 POS 기기를 구매하지 않아도, 스마트폰 앱만으로 애플페이 수납이 가능하기 때문에 별도 비용 불필요</li>
								<li>애플페이 결제를 받을 경우 기존 신용카드 결제수수료와 동일하며, 영중소 우대수수료도 동일하게 적용<br>(가입비, 사용료등 추가비용이 전혀 없음)</li>
								<li>애플페이 외에도 네이버페이, 카카오페이, 페이코, 스마일페이등 다양한 간편결제를 지원하고 있으며, 부계정 기능으로 종업원이나 아르바이트생도 사용이 가능</li>
							</ul>
						</div>
					</div>
				</div>
			</section>
		</section>

		<section class="use-section">
			<div class="inner">
				<div class="heading-sub">
					<h2>애플페이 결제 사용방법</h2>
				</div>
				<div class="guide-slider">
					<ol class="guide-area applepay-guide">
						<li class="col-6">
							<strong><b>1</b>온라인 : 결제창에 애플페이 추가</strong>
							<img src="https://payapp.kr/homepage/images/pay/applepay_sec2_guide1.png" alt="애플페이 온라인 결제창에 애플페이 추가">
						</li>
						<li class="col-6">
							<strong><b>2</b>오프라인 : QR결제</strong>
							<img src="https://payapp.kr/homepage/images/pay/applepay_sec2_guide2.png" alt="애플페이 오프라인 QR결제 화면">
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