<?php
	include_once("./_head.php");
	$pp = "pay_accounts";
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
                            <h2>주계정 하나로<br>부계정 무제한 생성</h2>
                            <p>다수의 영업판매직원이 있더라도<br><span class="text-blue">직원별 결제관리가 손쉽게 가능</span>합니다.</p>
                        </div>
                        <div class="tag-area">
                            <span>#권한설정가능</span>
                            <span>#한번에내역관리</span>
                            <span>#간편등록</span>
                        </div>
                    </div>
                    <div class="video-wrap">
                        <article class="video-area">
                            <video loop="" autoplay="" muted="" playsinline="">
                                <!-- 800*690 -->
                                <source src="https://payapp.kr/homepage/images/video/mov_pay_sub.mp4" type="video/mp4">
                            </video>
                        </article>
                    </div>
                </div>
            </section>
            <!-- //main-section -->
            <section class="use-section">
                <div class="inner">
                    <div class="heading-sub">
                        <h2>부계정 사용방법</h2>
                    </div>
                    <div class="guide-slider">
                        <ol class="guide-area">
                            <li class="col-3">
                                <strong><b>1</b>부계정관리 클릭</strong>
                                <img src="https://payapp.kr/homepage/images/pay/sub_sec2_guide1.gif" alt="부계정관리 화면">
                            </li>
                            <li class="col-3">
                                <strong><b>2</b>정보입력</strong>
                                <img src="https://payapp.kr/homepage/images/pay/sub_sec2_guide2.gif" alt="정보입력 화면">
                            </li>
                            <li class="col-3">
                                <strong><b>3</b>리스트확인</strong>
                                <img src="https://payapp.kr/homepage/images/pay/sub_sec2_guide3.gif" alt="리스트확인 화면">
                            </li>
                            <li class="col-3">
                                <strong><b>4</b>매출확인</strong>
                                <img src="https://payapp.kr/homepage/images/pay/sub_sec2_guide4.gif" alt="매출확인 화면">
                            </li>
                        </ol>
                    </div>
                </div>
            </section>
            <!-- //use-section -->

        </section>
        <!-- //contents -->
    </section>
    <!-- //container -->

<?php
	include_once("./_foot.php");
?>