


		<div class="KDC_Body__menu_open_dimmed__e3MuD" style="display:none;"></div>
		</div>
		<?php /*
		<div id="kakaoFoot" class="KDC_Footer__root__jItG5 KDC_Body__hidden_footer__jwSoF" role="contentinfo">
			<div class="inner_foot">
				<h2 class="screen_out">서비스 이용정보</h2>
				<div class="area_familysite">
					<div class="opt_relation">
						<div class="screen_out">
							Family Site 선택상자
						</div>
						<span class="screen_out">선택내용 : </span><strong class="tit_opt"><button class="link_tit" aria-haspopup="true" aria-expanded="false">Family Site<span class="kdc_ico_developers">열기</span></button></strong>
					</div>
					<div class="box_site">
						<a href="https://kakaobusiness-policy.kakao.com/SERVICE/" class="link_site" target="_blank">카카오비즈니스 이용약관</a><span class="txt_bar">|</span><a href="/terms/latest/site-terms" class="link_site">서비스 약관</a><span class="txt_bar">|</span><a href="/terms/latest/site-policies" class="link_site">운영 정책</a><span class="txt_bar">|</span><a href="/terms/latest/site-policies#quota" class="link_site">쿼터</a><span class="txt_bar">|</span><a href="https://business.kakao.com/policy/privacy/" class="link_site link_privacy" target="_blank"><strong>개인정보 처리방침</strong></a>
					</div>
				</div>
				<div class="area_copyright">
					<small class="txt_copy">© <a href="https://www.kakaocorp.com" class="link_kako">Kakao Corp.</a></small>
				</div>
			</div>
		</div>
		*/ ?>

		<div id="kakaoFoot" class="KDC_Footer__root__jItG5" role="contentinfo">
			<div class="inner_foot">
				<?php /*
				<h2 class="screen_out">서비스 이용정보</h2>
				<div class="area_familysite">
					<div class="opt_relation">
						<div class="screen_out">
							Family Site 선택상자
						</div>
						<span class="screen_out">선택내용 : </span><strong class="tit_opt"><button class="link_tit" aria-haspopup="true" aria-expanded="false">Family Site<span class="kdc_ico_developers">열기</span></button></strong>
					</div>
					<div class="box_site">
						<a href="https://kakaobusiness-policy.kakao.com/SERVICE/" class="link_site" target="_blank">카카오비즈니스 이용약관</a><span class="txt_bar">|</span><a href="/terms/latest/site-terms" class="link_site">서비스 약관</a><span class="txt_bar">|</span><a href="/terms/latest/site-policies" class="link_site">운영 정책</a><span class="txt_bar">|</span><a href="/terms/latest/site-policies#quota" class="link_site">쿼터</a><span class="txt_bar">|</span><a href="https://business.kakao.com/policy/privacy/" class="link_site link_privacy" target="_blank"><strong>개인정보 처리방침</strong></a>
					</div>
				</div>
				*/ ?>
				<div class="area_copyright">
					<small class="txt_copy">© <a href="https://www.kakaocorp.com" class="link_kako">PASSGO Corp.</a></small>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="modal-root" class="mobile">
	<div>
		<button type="button" class="KDC_GoToTop__root__8tiu+ show_pc" style="bottom: 102px;"><span class="kdc_ico_developers">상단으로 이동</span></button>
	</div>
</div>


<script>

	$(".KDC_Breadcrumb__toggle_lnb_button__dFYys").click(function(){
		$('.KDC_Header__root__b-dI8').toggleClass("KDC_Header__lnb_expanded__gF+b0");
		$('.KDC_Body__root__CQLKv').toggleClass("lnb_expanded");
		$('.KDC_LNB__root__JFmyq').toggleClass("KDC_LNB__responsive_hidden__JvKxz");
		$('.KDC_LNB__root__JFmyq').toggleClass("KDC_LNB__expanded__BMK18");
		$('.KDC_Body__menu_open_dimmed__e3MuD').toggle();
	});

/*

KDC_Header__root__b-dI8
추가 KDC_Header__lnb_expanded__gF+b0

KDC_Body__root__CQLKv
추가 lnb_expanded

KDC_LNB__root__JFmyq show_pc
삭제 KDC_LNB__responsive_hidden__JvKxz

KDC_LNB__root__JFmyq show_m
추가 KDC_LNB__expanded__BMK18

KDC_Body__root__CQLKv lnb_expanded


		if($("h1").hasClass("active")){
			$("h1").removeClass("active");
		} else{
			$("h1").addClass("active");
		}
		*/

	$(".btn_gnb").click(function(){
		$('.KDC_Header__root__b-dI8').toggleClass("KDC_Header__lnb_expanded__gF+b0");
		$('.KDC_Body__root__CQLKv').toggleClass("lnb_expanded");
		$('.KDC_LNB__root__JFmyq').toggleClass("KDC_LNB__responsive_hidden__JvKxz");
		$('.KDC_LNB__root__JFmyq').toggleClass("KDC_LNB__expanded__BMK18");
		$('.KDC_Body__menu_open_dimmed__e3MuD').toggle();
	});


	$("#logout").click(function(){
		$(this).toggleClass("opt_open");
	});


	$(function(){
		$("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yymmdd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });
	});


	$("#page_count, #dv_type, #dv_certi, #expansion").change(function() {
		$(this).parents().filter("form").submit();
	});

	function set_date(today) {
		<?php
			$date_term = date('w', G5_SERVER_TIME);
			$week_term = $date_term + 7;
			$last_term = strtotime(date('Y-m-01', G5_SERVER_TIME));
		?>
		if (today == "오늘") {
			document.getElementById("fr_date").value = "<?php echo date('Ymd'); ?>";
			document.getElementById("to_date").value = "<?php echo date('Ymd'); ?>";
			//document.getElementById("day").value = "1";
		} else if (today == "어제") {
			document.getElementById("fr_date").value = "<?php echo date('Ymd', G5_SERVER_TIME - 86400); ?>";
			document.getElementById("to_date").value = "<?php echo date('Ymd', G5_SERVER_TIME - 86400); ?>";
			//document.getElementById("day").value = "2";
		} else if (today == "이번주") {
			document.getElementById("fr_date").value = "<?php echo date('Ymd', strtotime('-'.$date_term.' days', G5_SERVER_TIME)); ?>";
			document.getElementById("to_date").value = "<?php echo date('Ymd', G5_SERVER_TIME); ?>";
			//document.getElementById("day").value = "3";
		} else if (today == "이번달") {
			document.getElementById("fr_date").value = "<?php echo date('Ym01', G5_SERVER_TIME); ?>";
			document.getElementById("to_date").value = "<?php echo date('Ymd', G5_SERVER_TIME); ?>";
			//document.getElementById("day").value = "4";
		} else if (today == "지난주") {
			document.getElementById("fr_date").value = "<?php echo date('Ymd', strtotime('-'.$week_term.' days', G5_SERVER_TIME)); ?>";
			document.getElementById("to_date").value = "<?php echo date('Ymd', strtotime('-'.($week_term - 6).' days', G5_SERVER_TIME)); ?>";
			//document.getElementById("day").value = "5";
		} else if (today == "지난달") {
			document.getElementById("fr_date").value = "<?php echo date('Ym01', strtotime('-1 Month', $last_term)); ?>";
			document.getElementById("to_date").value = "<?php echo date('Ymt', strtotime('-1 Month', $last_term)); ?>";
			//document.getElementById("day").value = "6";
		} else if (today == "전체") {
			document.getElementById("fr_date").value = "all";
			document.getElementById("to_date").value = "all";
		}
		$('#fsearch').submit();
	}



function PopupCenter(url, title, w, h, opts) {
	var _innerOpts = '';
	if(opts !== null && typeof opts === 'object' ){
		for (var p in opts ) {
			if (opts.hasOwnProperty(p)) {
				_innerOpts += p + '=' + opts[p] + ',';
			}
		}
	}
	var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
	var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;
	var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
	var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
	var left = ((width / 2) - (w / 2)) + dualScreenLeft;
	var top = ((height / 2) - (h / 2)) + dualScreenTop;
	var newWindow = window.open(url, title, _innerOpts + ' width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
	if (window.focus) {
		newWindow.focus();
	}
}

</script>

</body>
<whale-quicksearch translate="no"></whale-quicksearch></html>