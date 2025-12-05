<?php
	include_once("./_head.php");
?>


<section class="container" id="intro">


	<?php /*
	<section class="visual-motion">
		<div class="visual-wrap">
			<ul class="main-visual">

				<li class="visual visual1">
					<div class="inner-lg">
						<div class="main-tit">
							<h2>ALL Ready<br>통합결제 솔루션, 페이앱</h2>
							<p>사이트 연동 결제 방식과, 앱을 이용한 온/오프라인 결제, 주문서/쇼핑몰 등 <span class="br-break-lg">다양한 부가 서비스까지 페이앱 무료가입으로. 모두 쉽게. 한번에.</span></p>
							<div class="btn-group visual-btn">
								<button class="btn btn-white btn-lg btn-rounded" type="button" onclick="window.open('https://play.google.com/store/apps/details?id=com.udid.payapp')"><i class="ico ico-l ico-androad"></i>Google Play</button>
								<button class="btn btn-white btn-lg btn-rounded" type="button" onclick="window.open('https://apps.apple.com/kr/app/payapp/id538328034')"><i class="ico ico-l ico-ios"></i>App Store</button>
							</div>
						</div>
						<div class="img-area">
							<img src="https://payapp.kr/homepage/images/main/visual1_img.png" alt="">
						</div>
					</div>
				</li>




				<li class="visual visual2">
					<div class="inner-lg">
						<div class="main-tit">
							<h2>잘 나가는 간편결제들을<br>페이앱 하나로 바로 이용 가능!</h2>
							<p>네이버페이, 카카오페이, 애플페이, 스마일페이 등 유수의 간편결제가 <span class="br-break-lg">기본 탑재돼있어 별도 계약할 필요 없이 바로 결제받기 가능.</span></p>
							<div class="btn-group visual-btn">
								<button class="btn btn-white btn-lg btn-rounded" type="button" onclick="window.open('https://play.google.com/store/apps/details?id=com.udid.payapp')"><i class="ico ico-l ico-androad"></i>Google Play</button>
								<button class="btn btn-white btn-lg btn-rounded" type="button" onclick="window.open('https://apps.apple.com/kr/app/payapp/id538328034')"><i class="ico ico-l ico-ios"></i>App Store</button>
							</div>
						</div>
						<div class="img-area">
							<img src="https://payapp.kr/homepage/images/main/visual2_img.png" alt="">
						</div>
					</div>
				</li>




				<li class="visual visual3">
					<div class="inner-lg">
						<div class="main-tit">
							<h2>No.1 주문서/쇼핑몰 솔루션 <span class="br-break-lg">블로그페이 무료제공!</span></h2>
							<p>페이앱 무료가입으로 이용 가능한 No.1 무료 간편 쇼핑 플랫폼 <span class="br-break-lg">블로그페이로 SNS마켓과 온라인 시장을 내품에!</span></p>
							<div class="btn-group visual-btn">
								<button class="btn btn-blue btn-lg btn-rounded" type="button" onclick="window.open('http://blogpay.co.kr/')">자세히보기</button>
							</div>
						</div>
						<div class="img-area">
							<img src="https://payapp.kr/homepage/images/main/visual3_img.png" alt="">
						</div>
					</div>
				</li>




				<li class="visual visual4">
					<div class="inner-lg">
						<div class="main-tit">
							<h2>사업자 없이도, <span class="br-break-sm">리더기 없이도</span><span class="br-break-lg">즉시 결제받기 가능!</span></h2>
							<p>사업자도, 사업자 없는 개인도, 결제 리더기 없이도 <span class="br-break-lg">무료가입 후 앱으로 PC로 즉시 결제 받기 가능.</span></p>
							<div class="btn-group visual-btn">
								<button class="btn btn-white btn-lg btn-rounded" type="button" onclick="window.open('https://play.google.com/store/apps/details?id=com.udid.payapp')"><i class="ico ico-l ico-androad"></i>Google Play</button>
								<button class="btn btn-white btn-lg btn-rounded" type="button" onclick="window.open('https://apps.apple.com/kr/app/payapp/id538328034')"><i class="ico ico-l ico-ios"></i>App Store</button>
							</div>
						</div>
						<div class="img-area">
							<img src="https://payapp.kr/homepage/images/main/visual4_img.png" alt="">
						</div>
					</div>
				</li>



				<li class="visual visual5">
					<div class="inner-lg">
						<div class="main-tit">
							<h2>API 연동 or <span class="br-break-sm">서비스 영업으로</span><span class="br-break-lg">부가수익 창출 가능!</span></h2>
							<p>API 리셀러, 서비스 리셀러 등 페이앱 리셀러가 되면 <span class="br-break-lg">판매점 모집과 판매점별 마진 책정으로 고수익 창출 가능.</span></p>
							<div class="btn-group visual-btn">
								<button class="btn btn-blue btn-lg btn-rounded" type="button" onclick="location.href='/homepage/reseller/reseller.html'">자세히보기</button>
							</div>
						</div>
						<div class="img-area">
							<img src="https://payapp.kr/homepage/images/main/visual5_img.png" alt="">
						</div>
					</div>
				</li>
			</ul>
		</div>



		<div class="fixed-menu">
			<div class="menu-list">
				<a href="/popup_pay_new/popup/sample_step01.php" onclick="return openWindow(this, {name:'payapptest',width:420,height:610,center:true,scrollbars:true})">결제 테스트</a>
				<a href="https://seller.payapp.kr/a/seller_regist">서비스 신청</a>
				<a href="https://seller.payapp.kr/a/signIn">관리자 로그인</a>
				<a onclick="cardevent()">무이자 할부</a>
				<a href="/popup_pay_new/popup/pay_view1.php" onclick="return openWindow(this, {name:'payappHistory',width:420,height:630,center:true,scrollbars:true})">결제내역 조회</a>
				<a href="/homepage/bbs/bbs_ask.php">1:1 문의</a>
			</div>
		</div>
	</section>
	*/ ?>




	<section class="section1">
		<div class="inner">
			<div class="main-sub">
				<h2><span style="color:red;">레드</span>페이 특장점</h2>
				<?php /*
				<a href="/homepage/about/about.php" class="btn-view pull-right">자세히 <i class="arrow arrow-sm right light"></i></a>
				*/ ?>
			</div>
			<div class="benefit-area box-group">
				<div class="item">
					<div class="txt">
						<i class="ico"></i>
						<p>간편한 사이트<br>결제연동은 기본</p>
					</div>
				</div>
				<div class="item">
					<div class="txt">
						<i class="ico ico-allpay"></i>
						<p>온/오프라인<br>대면/비대면 통합결제</p>
					</div>
				</div>
				<div class="item">
					<div class="txt">
						<i class="ico ico-nkpay"></i>
						<p>네이버페이, 카카오페이,<br>애플페이, 스마일페이</p>
					</div>
				</div>
				<div class="item">
					<div class="txt">
						<i class="ico ico-personal"></i>
						<p>사업자 없는<br>개인도 사용 OK</p>
					</div>
				</div>
				<div class="item">
					<div class="txt">
						<i class="ico ico-free"></i>
						<p>리더기가 필요없는<br>결제 앱 무료 제공</p>
					</div>
				</div>
				<div class="item">
					<div class="txt">
						<i class="ico ico-account"></i>
						<p>주계정 하나로<br>부계정 무제한 지원</p>
					</div>
				</div>
				<div class="item">
					<div class="txt">
						<i class="ico ico-admin"></i>
						<p>PC용 관리자<br>기본 제공</p>
					</div>
				</div>
				<div class="item">
					<div class="txt">
						<i class="ico ico-service"></i>
						<p>주문서, 쇼핑몰 등<br>막강한 무료 부가서비스</p>
					</div>
				</div>
			</div>
		</div>
	</section>


	<section class="section2">
		<div class="inner">
			<div class="main-sub">
				<h2>결제수수료(부가세별도) <span class="br-break-sm">/ 정산안내</span></h2>
				<?php /*
				<a href="/homepage/guide/guide5.php" class="btn-view pull-right">자세히 <i class="arrow arrow-sm right light"></i></a>
				*/ ?>
				<p>결제 수수료 외 어떠한 추가비용도 없습니다.</p>
			</div>
			<table class="table">
				<caption>페이앱 결제수수료와 정산안내 테이블</caption>
				<colgroup>
					<col style="width:26%;">
					<col span="2" style="width:37%;">
				</colgroup>
				<thead>
				<tr>
					<th scope="col">구분</th>
					<th scope="col">단말기 결제 수수료</th>
					<th scope="col">수기 결제 수수료</th>
				</tr>
				</thead>
				<tbody>
				<tr class="sub-line">
					<th scope="row"><span class="tit">사업자<em class="br-break-sm">(법인/개인)</em></span></th>
					<td>
						<?php /*
						<p>
							<span class="label label-round">영중소</span>
							<strong>1.9<em>%</em> ~ 2.85<em>%</em></strong>
						</p>
						*/ ?>
						<p>
							<span class="label label-round">일반</span>
							<strong>3.4<em>%</em>~<em>8.8%</em></strong>
						</p>
					</td>
					<td>
						<span class="label label-round visible-md hidden">영중소</span>
						<strong>3.8<em>%</em>~<em>8.8%</em></strong>
					</td>
				</tr>
				<tr>
					<th scope="row"><span class="tit">비사업자 <em class="br-break-sm">개인</em></span></th>
					<td>
						<span class="label label-round">일반</span>
						<strong>4.0<em>%</em>~<em>9.0%</em></strong>
					</td>
					<td>
						<span class="label label-round visible-md hidden">일반</span>
						<strong>4.0<em>%</em>~<em>9.0%</em></strong>
					</td>
				</tr>
				<tr>
					<th scope="row"><span class="tit">정산안내</span></th>
					<td>
						<b class="sub-tit">표준정산</b>
						<strong>D+5<em>일</em></strong>
					</td>
					<td>
						<b class="sub-tit">빠른정산</b>
						<strong>D+3<em>일 /</em> D+1<em>일</em></strong>
						<span class="label label-round">부가서비스</span>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
	</section>
	<!-- //section2 -->

	<section class="section3">
		<div class="inner">
			<div class="main-sub">
				<h2>이용안내</h2>
			</div>
			<div class="use-area box-group">
				<div class="item">
					<dl class="txt" onclick="location.href='https://seller.payapp.kr/a/seller_regist'">
						<dt>무료가입/즉시결제</dt>
						<dd>
							<p>간편 무료 가입 후<br>즉시 결제받기가 가능합니다.</p>
							<span class="btn-view">자세히 <i class="arrow arrow-sm right light"></i></span>
						</dd>
					</dl>
				</div>
				<div class="item">
					<dl class="txt" onclick="location.href='/homepage/guide/guide3.html'">
						<dt>판매점 계약 안내</dt>
						<dd>
							<p>결제가 난 뒤 계약서류가 완료되면<br>정산이 시작됩니다. (이메일 접수)</p>
							<span class="btn-view">자세히 <i class="arrow arrow-sm right light"></i></span>
						</dd>
					</dl>
				</div>
				<div class="item">
					<dl class="txt" onclick="location.href='/homepage/guide/guide4.html'">
						<dt>보증보험 안내</dt>
						<dd>
							<p>월매출 500만원 이하 or 건당 50만원 이하 결제 시 보증보험 면제!<br>(업종에 따라 달라질 수 있습니다.)</p>
							<span class="btn-view">자세히 <i class="arrow arrow-sm right light"></i></span>
						</dd>
					</dl>
				</div>
				<div class="item">
					<dl class="txt" onclick="location.href='/homepage/guide/guide4.html'">
						<dt>보증보험 안내</dt>
						<dd>
							<p>월매출 500만원 이하 or 건당 50만원 이하 결제 시 보증보험 면제!<br>(업종에 따라 달라질 수 있습니다.)</p>
							<span class="btn-view">자세히 <i class="arrow arrow-sm right light"></i></span>
						</dd>
					</dl>
				</div>
				<div class="item">
					<dl class="txt" onclick="location.href='/homepage/guide/guide4.html'">
						<dt>보증보험 안내</dt>
						<dd>
							<p>월매출 500만원 이하 or 건당 50만원 이하 결제 시 보증보험 면제!<br>(업종에 따라 달라질 수 있습니다.)</p>
							<span class="btn-view">자세히 <i class="arrow arrow-sm right light"></i></span>
						</dd>
					</dl>
				</div>
			</div>
		</div>
	</section>

	<section class="section4">
		<div class="inner">
			<div class="col-6">
				<div class="menu-panel">
					<h3>
						<a href="./?p=notice">
							공지사항 <span class="hidden-xs">더보기</span><i class="ico ico-arrow-r-white"></i>
						</a>
					</h3>
					<ul class="hidden-xs col-6" id="notice-block">
						<li><a href="/homepage/bbs/bbs_notice_view.html?no=459">2024년 1월 카드사 무이자 할부 안내</a></li>
						<li><a href="/homepage/bbs/bbs_notice_view.html?no=458">[공지] 2023년 12월 29일 고객센터 업무 종료 안내</a></li>
						<li><a href="/homepage/bbs/bbs_notice_view.html?no=457">2023년 12월 카드사 무이자 할부 안내</a></li>
					</ul>
				</div>
			</div>
			<div class="col-6">
				<div class="menu-panel">
					<h3>
						<span>대표전화</span> <a href="tel:1800-3772">1800-3772</a>
					</h3>
					<ul>
						<li>평일 09:00 ~ 18:00 (점심 12:00 ~ 13:00)</li>
						<li>fax. 02-6008-9760</li>
						<li>e-mail. payapp@udid.co.kr</li>
					</ul>
				</div>
			</div>
		</div>
	</section>

</section>


<?php /*
<script>
	// 쿠키 생성
	function setCookie(c_name,value,exdays) {
		var exdate=new Date();
		exdate.setDate(exdate.getDate() + exdays);
		var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString()) + "; path=/";
		document.cookie=c_name + "=" + c_value;
	}
	function getCookie(cname) {
		var name = cname + "=";
		var ca = document.cookie.split(';');
		for (var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == ' ') c = c.substring(1);
			if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
		}
		return "";
	}
	// 오늘 날짜 가져오기
	function getToday() {
		var date = new Date();
		var yy = date.getFullYear();
		var mm = date.getMonth() + 1;
		var dd = date.getDate();
		mm = (mm < 10) ? '0' + mm : mm;
		dd = (dd < 10) ? '0' + dd : dd;

		var today = yy + '-' + mm + '-' + dd;
		return today;
	}
	// 팝업창 오늘 하루 닫기
	function todaycloseWin() {
		var today = getToday(); // 오늘 날짜 계산
		setCookie('todayPopup', today, null); // 쿠키값을 오늘 날짜로 셋팅
		$('.pop-show').hide(); // 팝업 숨기기
	}
	// 상단 배너 오늘 하루 닫기
	function todaycloseWinBanner() {
		var today = getToday(); // 오늘 날짜 계산
		setCookie('todayPopupBanner', today, null); // 쿠키값을 오늘 날짜로 셋팅
		$('.banner-section').hide(); // 팝업 숨기기
	}
	function cardevent(){
		window.open("/cardevent/cardevent.php", "cardevent", "width=550,height=500,scrollbars=yes");
	}
</script>

<div class="pop-show">
	<div class="popup-section">
		<ul class="main-popup">
			<li data-text="페이앱 애플페이 OPEN">
				<a href="./pay_applepay.php">
					<img src="https://payapp.kr/homepage/images/popup/img_pop_applepay.jpg" alt="페이앱 애플페이 OPEN! 페이앱으로 애플페이 받으세요">
				</a>
			</li>
			<li data-text="페이앱 가입 혜택">
				<a href="/homepage/event/event01.php" target="_blank" title="새창열림">
					<img src="https://payapp.kr/homepage/images/popup/img_pop_joinBenefit.jpg" alt="페이앱 가입 혜택! 가입비 100% 무료. 최저 수수료 1.9%부터. (최대 1.5% 인하 적용시)">
				</a>
			</li>
		</ul>
		<div class="btn-area">
			<button type="button" onclick="todaycloseWin();">오늘 하루 닫기</button>
			<button type="button" onclick="$('.pop-show').hide();">닫기</button>
		</div>
	</div>
	<div class="pop-overlay"></div>
</div>
*/ ?>



<script type="text/javascript">
	if (document.body.clientWidth > 800) {
		/*
		if (getCookie('Notice')!='done') {
			window.open("/cardevent/cardevent.php", "", "width=550,height=500,scrollbars=yes");
		}
		*/
	}
</script>









<?php
	include_once("./_foot.php");
?>