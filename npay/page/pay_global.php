<?php
	include_once("./_head.php");
	$pp = "pay_global";
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
						<h2>전 세계 220개국<br>어디든 실시간 결제가능</h2>
						<p>해외 구매자는 <span class="text-blue">VISA, MASTER, JCB카드</span>를<br>이용하여 <span class="text-blue">SMS, 이메일로 결제가 가능</span>합니다.</p>
					</div>
					<div class="tag-area">
						<span>#해외결제서비스</span>
						<span>#APP설치없이</span>
						<span>#회원가입없이</span>
					</div>
				</div>
				<div class="video-wrap">
					<article class="video-area">
						<video loop="" autoplay="" muted="" playsinline="">
							<!-- 800*690 -->
							<source src="https://payapp.kr/homepage/images/video/mov_pay_foreign.mp4" type="video/mp4">
						</video>
					</article>
				</div>
			</div>
		</section>

		<section class="use-section">
			<div class="inner">
				<div class="heading-sub">
					<h2>해외결제 사용방법</h2>
				</div>
				<div class="guide-slider">
					<ol class="guide-area">
						<li class="col-3">
							<strong><b>1</b>원격결제 클릭</strong>
							<img src="https://payapp.kr/homepage/images/pay/foreign_sec2_guide1.gif" alt="원격결제 화면">
						</li>
						<li class="col-3">
							<strong><b>2</b>해외결제요청 클릭</strong>
							<img src="https://payapp.kr/homepage/images/pay/foreign_sec2_guide2.gif" alt="해외결제요청 화면">
						</li>
						<li class="col-3">
							<strong><b>3</b>결제현황 리스트</strong>
							<img src="https://payapp.kr/homepage/images/pay/foreign_sec2_guide3.gif" alt="결제현황 리스트 화면">
						</li>
						<li class="col-3">
							<strong><b>4</b>상세보기</strong>
							<img src="https://payapp.kr/homepage/images/pay/foreign_sec2_guide4.gif" alt="상세보기 화면">
						</li>
					</ol>
				</div>
				<div class="btn-center-block">
					<a href="javascript:;" class="btn btn-gray-line modal-trigger" data-modal-id="pay-foreign-way">사용방법 안내</a>
				</div>
			</div>
		</section>
	</section>
</section>

    <div id="pay-foreign-way" class="modal modal-sm">
        <div class="modal-header">
            <h2>해외결제 이용방법</h2>
        </div>
        <div class="modal-content">
            <ul class="info-list">
                <li>(1) 페이앱 판매자 계정에 로그인 후 구매자 국가 선택</li>
                <li>(2) 구매자 국가 선택 시 해당 국가의 국가번호 자동 지정</li>
                <li>(3) 결제요청 내용 입력</li>
                <li>(4) 구매자는 문자/이메일에서 결제링크 클릭 후 결제내용 확인</li>
                <li>(5) 결제 동의 버튼 클릭 후 결제정보 입력</li>
                <li>(6) 결제 완료 시 영수증 구매자 핸드폰에 출력</li>
                <li>(7) 구매자는 영수증 이메일 수신 가능</li>
            </ul>
        </div>
        <div class="modal-footer">
            <span role="button" class="btn btn-black btn-block modal-close">확인</span>
        </div>
    </div>
    <!-- //해외결제 이용방법 모달 -->

<?php
	include_once("./_foot.php");
?>