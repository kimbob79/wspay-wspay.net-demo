
	<?php if($p) { ?>
	<footer id="global-footer">
		<div class="footer-area">
			<section class="footer-wrap">
				<h1><span>WANNA</span> PAY</h1>
				<div class="copyright-wrap">
					<div class="company">
						<p><span>상호명 : 원성페이먼츠</span><span>사업자등록번호 : 596-88-02642</span></p>
						<p><span>고객센터 : 1555-0985</span><span><a href="mailto:wsgpay@naver.com">이메일 : wsgpay@naver.com</a></span></p>
						<?php /*
						<p>
							<span>통신판매업 신고번호 : 제 2012-서울구로-0239호<a href="http://www.ftc.go.kr/www/bizCommList.do?key=232" target="_blank"> [사업자정보확인]</a></span><span>고객센터 : 1800-3772</span><span>팩스 : 02-6008-9760</span><span><a href="mailto:payapp@udid.co.kr">이메일 : payapp@udid.co.kr</a></span>
						</p>
						*/ ?>
						<p>주소 : 경기도 수원시 영통구 창룡대로 256번길 91, B107호</p>
						<p>Copyright © 2022 <strong>REDPAY</strong> Inc. All rights reserved.</p>
					</div>
				</div>
				<?php /*
				<div class="terms">
					<a href="http://udid.co.kr/" target="_blank">회사소개</a>
					<a href="/homepage/udidTerms/payapp_terms.php" target="_blank">이용약관</a>
					<a href="/homepage/udidTerms/payapp_financial.php" target="_blank">전자금융거래이용약관</a>
					<a href="/homepage/udidTerms/payapp_privacy.php" target="_blank">개인정보처리방침</a>
					<a href="/homepage/udidTerms/payapp_billing.php" target="_blank">통신과금서비스이용약관</a>
					<a href="/dev_center/dev_center01.php" target="_blank">개발자센터</a>
					<a href="https://pf.kakao.com/_LAsjT" target="_blank">제휴문의</a>
				</div>
				<div class="banner-wrap">
					<div class="banner-area">
						<div class="banner">
							<strong>PG</strong>
							<span>전자지급결제대행업 등록번호 02-004-00096</span>
						</div>
						<div class="banner">
							<strong>ESCROW</strong>
							<span>결제대금예치업 등록번호 02-006-00035</span>
						</div>
						<div class="banner">
							<strong>통신과금서비스제공업</strong>
							<span>등록번호 058</span>
						</div>
						<!--                        <div role="button" class="banner modal-trigger" data-modal-id="company-mark-isms">-->
						<!--                            <strong>ISMS</strong>-->
						<!--                            <span>-->
						<!--                                ISMS-KISA-2022-033-->
						<!--                                <small>정보보호 관리체계 인증</small>-->
						<!--                            </span>-->
						<!--                        </div>-->
					</div>
					<div class="social">
						<button type="button" class="banner modal-trigger" data-modal-id="company-mark-isms"><img src="https://payapp.kr/homepage/images/common/img_mark_isms.png" alt="ISMS"></button>
						<a href="https://www.instagram.com/payapp_official/" target="_blank" title="페이앱 공식 인스타그램"><i class="ico ico-insta"></i></a>
						<a href="https://www.facebook.com/udidcorporation" target="_blank" title="페이앱 공식 페이스북"><i class="ico ico-facebook"></i></a>
						<a href="https://pf.kakao.com/_LAsjT" target="_blank" title="페이앱 공식 플러스친구"><i class="ico ico-kakaoplus"></i></a>
						<a href="https://blog.naver.com/udidcompany" target="_blank" title="페이앱 공식 네이버 블로그"><i class="ico ico-blog"></i></a>
						<a href="https://post.naver.com/udidpayapp" target="_blank" title="블로그페이 공식 포스트"><i class="ico ico-post"></i></a>
					</div>
				</div>
				*/ ?>
			</section>
		</div>
		<?php /*
		<div class="floating-cont">
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
		</div>
		*/ ?>
		<div class="floating-menu">
			<div role="button" class="f-btn go-top">
				<b>TOP</b>
			</div>
			<?php /*
			<div role="button" class="f-btn go-menu"></div>
			*/ ?>
		</div>
	</footer>
	<?php } ?>
</div>

<div id="contract-sign" class="modal modal-lg">
	<div class="modal-header">
		<h2>계약서 날인 방법</h2>
		<span role="button" class="modal-close">닫기</span>
	</div>
	<div class="modal-content">
		<div class="modal-tab">
			<button class="tab-btn active" id="sign1TabBtn" onclick="openTabcont(event, 'sign1')">이메일 또는 우편접수 시</button>
			<button class="tab-btn" id="sign2TabBtn" onclick="openTabcont(event, 'sign2')">전자계약서 작성 시</button>
		</div>
		<div id="sign1" class="tab-cont panel-wrap">
			<img src="https://payapp.kr/homepage/images/guide/img_contract_sign.png" alt="">
		</div>
		<div id="sign2" class="tab-cont panel-wrap" style="display:none">
			<img src="https://payapp.kr/homepage/images/guide/img_contract_sign2.png" alt="">
		</div>
	</div>
</div>
</body>


<?php if($p) { ?>
<script>

$(function(){
	$("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });
});

$("#fr_date, #to_date, #pay_pg, #mb_id, #payr, #pgid").change(function() {
	$(this).parents().filter("form").submit();
});

function set_date(today) {
	<?php
	$date_term = date('w', G5_SERVER_TIME);
	$week_term = $date_term + 7;
	$last_term = strtotime(date('Y-m-01', G5_SERVER_TIME));
	?>
	if (today == "오늘") {
		document.getElementById("fr_date").value = "<?php echo date('Y-m-d'); ?>";
		document.getElementById("to_date").value = "<?php echo date('Y-m-d'); ?>";
		document.getElementById("day").value = "1";
	} else if (today == "어제") {
		document.getElementById("fr_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME - 86400); ?>";
		document.getElementById("to_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME - 86400); ?>";
		document.getElementById("day").value = "2";
	} else if (today == "이번주") {
		document.getElementById("fr_date").value = "<?php echo date('Y-m-d', strtotime('-'.$date_term.' days', G5_SERVER_TIME)); ?>";
		document.getElementById("to_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME); ?>";
		document.getElementById("day").value = "3";
	} else if (today == "이번달") {
		document.getElementById("fr_date").value = "<?php echo date('Y-m-01', G5_SERVER_TIME); ?>";
		document.getElementById("to_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME); ?>";
		document.getElementById("day").value = "4";
	} else if (today == "지난주") {
		document.getElementById("fr_date").value = "<?php echo date('Y-m-d', strtotime('-'.$week_term.' days', G5_SERVER_TIME)); ?>";
		document.getElementById("to_date").value = "<?php echo date('Y-m-d', strtotime('-'.($week_term - 6).' days', G5_SERVER_TIME)); ?>";
		document.getElementById("day").value = "5";
	} else if (today == "지난달") {
		document.getElementById("fr_date").value = "<?php echo date('Y-m-01', strtotime('-1 Month', $last_term)); ?>";
		document.getElementById("to_date").value = "<?php echo date('Y-m-t', strtotime('-1 Month', $last_term)); ?>";
		document.getElementById("day").value = "6";
	} else if (today == "전체") {
		document.getElementById("fr_date").value = "all";
		document.getElementById("to_date").value = "all";
	}
}

var win_receipt = function(href) {
	window.open(href, "win_receipt", "left=50, top=50, width=500, height=770, scrollbars=1");
}
$(document).ready(function(){
	$("#login_password_lost, #ol_password_lost").click(function(){
		win_popuop(this.href);
		return false;
	});
});
</script>
<?php } ?>

</html>