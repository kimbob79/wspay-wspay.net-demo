<?php
// 밴피 설정 여부 확인 (mb_van_fee > 0 이면 제한된 메뉴만 표시)
$is_van_fee_member = ($member['mb_van_fee'] > 0) ? true : false;
?>
<!doctype html>
<html lang="ko" id="html_wrap">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=0,maximum-scale=10,user-scalable=yes">
	<meta name="HandheldFriendly" content="true">
	<meta name="format-detection" content="telephone=no">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<!-- SEO Meta Tags -->
	<meta name="description" content="원성페이먼츠 판매자센터 - 결제 관리, 정산 조회, 가맹점 관리를 위한 통합 솔루션. 실시간 결제내역 확인 및 정산 관리 시스템.">
	<meta name="keywords" content="Sunshine Pay, 판매자센터, 결제관리, 정산조회, 가맹점관리, PG, 결제시스템">
	<meta name="author" content="원성페이먼츠">
	<meta name="robots" content="noindex, nofollow">
	<meta name="googlebot" content="noindex, nofollow">

	<!-- Open Graph Meta Tags -->
	<meta property="og:type" content="website">
	<meta property="og:site_name" content="원성페이먼츠">
	<meta property="og:title" content="원성페이먼츠 판매자센터 - 가맹점 관리 솔루션">
	<meta property="og:description" content="결제 관리, 정산 조회, 가맹점 관리를 위한 통합 솔루션">
	<meta property="og:image" content="./img/og_tag.png">
	<meta property="og:url" content="<?php echo 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; ?>">
	<meta property="og:locale" content="ko_KR">

	<!-- Twitter Card Meta Tags -->
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="원성페이먼츠 판매자센터">
	<meta name="twitter:description" content="결제 관리, 정산 조회, 가맹점 관리를 위한 통합 솔루션">
	<meta name="twitter:image" content="./img/og_tag.png">

	<!-- Favicon -->
	<link rel="icon" type="image/svg+xml" href="/img/favicon.svg?v=<?php echo time(); ?>">
	<link rel="alternate icon" href="/img/favicon.svg?v=<?php echo time(); ?>" type="image/svg+xml">
	<link rel="shortcut icon" href="/img/favicon.svg?v=<?php echo time(); ?>">
	<link rel="apple-touch-icon" href="/img/favicon.svg?v=<?php echo time(); ?>">
	<link rel="manifest" href="/manifest.php?v=<?php echo time(); ?>">
	<meta name="msapplication-TileColor" content="#3B82F6">
	<meta name="msapplication-TileImage" content="/img/favicon.svg?v=<?php echo time(); ?>">
	<meta name="theme-color" content="#3B82F6">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
	<meta name="apple-mobile-web-app-title" content="원성페이먼츠">

	<!-- Canonical URL -->
	<link rel="canonical" href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; ?>">

	<!-- JSON-LD Structured Data -->
	<script type="application/ld+json">
	{
		"@context": "https://schema.org",
		"@type": "WebApplication",
		"name": "원성페이먼츠 판매자센터",
		"applicationCategory": "FinanceApplication",
		"operatingSystem": "Web",
		"description": "결제 관리, 정산 조회, 가맹점 관리를 위한 통합 솔루션",
		"offers": {
			"@type": "Offer",
			"price": "0",
			"priceCurrency": "KRW"
		},
		"provider": {
			"@type": "Organization",
			"name": "원성페이먼츠",
			"url": "<?php echo 'https://'.$_SERVER['HTTP_HOST']; ?>",
			"contactPoint": {
				"@type": "ContactPoint",
				"telephone": "+82-1555-0985",
				"contactType": "Customer Service",
				"availableLanguage": "Korean",
				"hoursAvailable": {
					"@type": "OpeningHoursSpecification",
					"dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
					"opens": "09:00",
					"closes": "18:00"
				}
			}
		}
	}
	</script>

	<title>원성페이먼츠 판매자센터<?php if(isset($title1) && $title1) echo ' - '.$title1; ?><?php if(isset($title2) && $title2) echo ' - '.$title2; ?></title>

	<!-- Performance Optimization -->
	<link rel="dns-prefetch" href="https://fonts.googleapis.com">
	<link rel="dns-prefetch" href="https://ajax.googleapis.com">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="preload" href="/img/favicon.svg?v=<?php echo time(); ?>" as="image" type="image/svg+xml">
	<link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="/css/mobile.css?v=<?php echo time(); ?>">
	<link rel="stylesheet" href="/css/table.css?v=<?php echo time(); ?>">
	<link rel="stylesheet" href="/css/search.css?v=<?php echo time(); ?>">
	<link rel="stylesheet" href="/gnu_module/js/font-awesome/css/font-awesome.min.css?ver=2106181">
	<link rel="stylesheet" href="/css/etc.css?v=<?php echo time(); ?>">
	<link rel="stylesheet" href="/css/board.css?v=<?php echo time(); ?>">
	<link rel="stylesheet" href="/css/popular.css?v=<?php echo time(); ?>">
	<link rel="stylesheet" href="/css/mui.min.css?v=<?php echo time(); ?>">
	<link rel="stylesheet" href="/css/btn.css?v=<?php echo time(); ?>">
	<link rel="stylesheet" href="/css/tooltip.css?v=20260115">
	<link rel="stylesheet" href="/css/header-custom.css?v=<?php echo time(); ?>">
	<link rel="stylesheet" href="/css/top-button.css?v=<?php echo time(); ?>">
	<link type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/base/jquery-ui.css" rel="stylesheet" />
	<script src="/gnu_module/js/jquery-1.12.4.min.js?v=<?php echo time(); ?>"></script>
	<script src="/js/mui.min.js?v=<?php echo time(); ?>"></script>
</head>
<body <? if($p == "main") {?>class="b_main"<?}?>>

<!-- 페이지 로딩바 -->
<div id="page-loader">
	<div class="loader-bar"></div>
</div>
<style>
#page-loader {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 3px;
	z-index: 999999999;
	pointer-events: none;
}
#page-loader .loader-bar {
	height: 100%;
	width: 0;
	background: linear-gradient(90deg, #3B82F6, #60A5FA, #93C5FD, #60A5FA, #3B82F6);
	background-size: 200% 100%;
	animation: loader-progress 1.5s ease-out forwards, loader-shimmer 1s linear infinite;
	box-shadow: 0 0 10px rgba(59, 130, 246, 0.5), 0 0 20px rgba(59, 130, 246, 0.3);
	border-radius: 0 2px 2px 0;
}
@keyframes loader-progress {
	0% { width: 0; }
	20% { width: 25%; }
	50% { width: 60%; }
	80% { width: 85%; }
	100% { width: 92%; }
}
@keyframes loader-shimmer {
	0% { background-position: 200% 0; }
	100% { background-position: -200% 0; }
}
#page-loader.loaded .loader-bar {
	width: 100% !important;
	animation: loader-complete 0.3s ease-out forwards;
}
@keyframes loader-complete {
	0% { width: 92%; opacity: 1; }
	50% { width: 100%; opacity: 1; }
	100% { width: 100%; opacity: 0; }
}
#page-loader.hidden {
	display: none;
}
</style>

<script>
jQuery(function($) {
	var $bodyEl = $('body'),
		$sidedrawerEl = $('#sidedrawer');
	
	function showSidedrawer() {
		// show overlay
		var options = {
  		onclose: function() {
			$sidedrawerEl
      		.removeClass('active')
      		.appendTo(document.body);
		}
		};
    
		var $overlayEl = $(mui.overlay('on', options));
    
    	// show element
    	$sidedrawerEl.appendTo($overlayEl);
		setTimeout(function() {
  			$sidedrawerEl.addClass('active');
		}, 20);
  	}

	function hideSidedrawer() {
		$bodyEl.toggleClass('hide-sidedrawer');
	}

	$('.js-show-sidedrawer').on('click', showSidedrawer);
	$('.js-hide-sidedrawer').on('click', hideSidedrawer);

});
</script>



<!-- 상단 시작 { -->
<header id="header">
	<div id="mobile-indicator"></div>

	<div id="hd_pop">
		<h2>팝업레이어 알림</h2>
		<span class="sound_only">팝업레이어 알림이 없습니다.</span>
	</div>

	<script>
	$(function() {
		$(".hd_pops_reject").click(function() {
			var id = $(this).attr('class').split(' ');
			var ck_name = id[1];
			var exp_time = parseInt(id[2]);
			$("#"+id[1]).css("display", "none");
			set_cookie(ck_name, 1, exp_time, g5_cookie_domain);
		});
		$('.hd_pops_close').click(function() {
			var idb = $(this).attr('class').split(' ');
			$('#'+idb[1]).css('display','none');
		});
	});
	</script>
	<!-- } 팝업레이어 끝 -->
	<div id="hd_wrapper" class="">
		<div class="gnb_side_btn">
			<!-- 데스크톱 메뉴 토글 -->
			<a class="sidedrawer-toggle menu-toggle-desktop js-hide-sidedrawer">
				<i class="fa fa-bars" aria-hidden="true"></i>
				<span class="sound_only">전체메뉴</span>
			</a>
			<!-- 모바일 메뉴 토글 -->
			<a class="sidedrawer-toggle menu-toggle-mobile js-show-sidedrawer">
				<i class="fa fa-bars" aria-hidden="true"></i>
				<span class="sound_only">모바일 전체메뉴</span>
			</a>
			<?php if($is_admin) { ?>
				<a href="/" class="mu_home" style="top: 0px;"><i class="fa fa-home" aria-hidden="true"></i></a>
			<?php } ?>
		</div>
		<div id="logo">
			<a href="/">
				<div class="logo-container">
					<span class="logo-main">Sunshine <span class="logo-pay">Pay</span></span>
					<span class="logo-sub">판매자센터</span>
				</div>
			</a>
		</div>
		<div class="header_ct">

			<div class="hd_sch_wr">
				<button class="hd_sch_bt"><i class="fa fa-search" aria-hidden="true"></i><span class="sound_only">검색창 열기</span></button>
				<fieldset id="hd_sch">
					<h2>사이트 내 전체검색</h2>
					<form name="fsearchbox" action="./" onsubmit="return fsearchbox_submit(this);" method="get">
						<input type="hidden" name="p" value="payment">
						<input type="hidden" name="fr_date" value="all">
						<input type="hidden" name="to_date" value="all">
						<input type="hidden" name="sfl" value="pay_num">
						<input type="text" name="stx" id="sch_stx" placeholder="승인번호 전체검색" required maxlength="20">
						<button type="submit" value="검색" id="sch_submit"><i class="fa fa-search" aria-hidden="true"></i><span class="sound_only">검색</span></button>
					</form>
				</fieldset>
			</div>
			<div id="tnb">
				<button class="profile_btn">
					<span class="profile_img"><i class="fa fa-user" aria-hidden="true"></i></span>
				</button>

				<?php
					if($member['mb_level'] == 10) {
						$stitles = "관리자";
					} else if($member['mb_level'] == 8) {
						$stitles = "본사";
					} else if($member['mb_level'] == 7) {
						$stitles = "지사";
					} else if($member['mb_level'] == 6) {
						$stitles = "총판";
					} else if($member['mb_level'] == 5) {
						$stitles = "대리점";
					} else if($member['mb_level'] == 4) {
						$stitles = "영업점";
					} else if($member['mb_level'] == 3) {
						$stitles = "가맹점";
					}
				?>

				<div class="tnb_member" style="display: none;">
					<ul>
						<li class="tnb_me">
							<strong><?php if($member['mb_nick']) { echo $member['mb_nick']; } else { echo $member['mb_name']; } ?></strong>
						</li>
						<li><a>소속<span><?php echo $stitles; ?></span></a></li>
						<?php /*
						<li><a href="https://theme.sir.kr/gnuboard55/bbs/point.php" target="_blank">포인트<span class="arm_on">1,000</span></a></li>
						<li><a href="https://theme.sir.kr/gnuboard55/bbs/scrap.php" target="_blank">스크랩<span>0</span></a></li>
						*/ ?>
						<li class="tnb_logout"><a href="/logout.php">로그아웃</a></li>
					</ul>
				</div>
				<script>
				// 회원메뉴 열기
				$(document).ready(function(){
					$(document).on("click", ".profile_btn", function() {
						$(".tnb_member").toggle();
					});
				});

				// 탈퇴의 경우 아래 코드를 연동하시면 됩니다.
				function member_leave()
				{
					if (confirm("정말 회원에서 탈퇴 하시겠습니까?"))
						location.href = "https://theme.sir.kr/gnuboard55/bbs/member_confirm.php?url=member_leave.php";
				}
				</script>
			</div>
			<script>
			$(document).ready(function(){
				$(document).on("click", ".hd_sch_bt", function() {
					$("#hd_sch").toggle();
				});
				$(".sch_more_close").on("click", function(){
					$("#hd_sch").hide();
				});
			});
			</script>
		</div>
	</div>
</header>
<!-- } 상단 끝 -->



<aside id="sidedrawer">
	<div id="gnb">
		<!--<div class="gnb_side logo_gnb">
			<div id="logo">
				<a href="/">
					<img src="./img/pg_logo.png" alt="WANNA" class="pc_logo">
					<img src="./img/mpg_logo.png" alt="WANNA" class="mob_logo">
					<?/*WANNA*/?>
				</a>
			</div>
		</div>-->
		<?php if(!$is_van_fee_member && ($is_admin or $member['mb_mailling'] == '1')) { ?>
		<div class="gnb_side no_logo">
			<h2>PAY</h2>
			<ul id="gnb_1dul">
				<li class="gnb_1dli">
					<a href="/pay" target="_self" class="gnb_1da <?php if($p == "manual_payment") { echo "on"; } ?>"><i class="fa fa-credit-card"></i> <span>수기결제</span></a>
				</li>
				<?php if($is_admin) { ?>
				<li class="gnb_1dli">
					<a href="/?p=url_payment" target="_self" class="gnb_1da <?php if($p == "url_payment" || $p == "url_payment_form") { echo "on"; } ?>"><i class="fa fa-link"></i> <span>URL결제</span></a>
				</li>
				<?php } ?>
			</ul>
		</div>
		<?php } ?>
		<div class="gnb_side no_logo">
			<h2>PAYMENT</h2>
			<ul id="gnb_1dul">
				<li class="gnb_1dli">
					<a href="/txn" target="_self" class="gnb_1da <?php if($p == "payment") { echo "on"; } ?>"><span>실시간 결제내역</span></a>
				</li>
				<?php if(!$is_van_fee_member) { ?>
				<?php if($member['mb_level'] >= 4) { ?>
				<li class="gnb_1dli">
					<a href="/txn/merchant" target="_self" class="gnb_1da <?php if($p == "payment_member") { echo "on"; } ?>"><span>가맹점별 결제내역</span></a>
				</li>
				<?php } ?>
				<?php if($is_admin) { ?>
				<li class="gnb_1dli">
					<a href="/txn/missing" target="_self" class="gnb_1da <?php if($p == "payment_loss") { echo "on"; } ?>"><span>누락 결제내역</span></a>
				</li>
				<?php /*?>
				<li class="gnb_1dli">
					<a href="/txn/daily" target="_self" class="gnb_1da <?php if($p == "payment_day") { echo "on"; } ?>"><span>일간 결제내역</span></a>
				</li>
				<?php */ ?>
				<li class="gnb_1dli">
					<a href="/txn/cancel" target="_self" class="gnb_1da <?php if($p == "cancel_payment") { echo "on"; } ?>"><span>취소 내역</span></a>
				</li>
				<li class="gnb_1dli">
					<a href="/txn/memo" target="_self" class="gnb_1da <?php if($p == "payment_memo") { echo "on"; } ?>"><i class="fa fa-sticky-note-o"></i> <span>결제내역 메모</span></a>
				</li>
				<li class="gnb_1dli">
					<a href="/txn/fds" target="_self" class="gnb_1da <?php if($p == "payment_fds") { echo "on"; } ?>"><i class="fa fa-exclamation-triangle"></i> <span>결제이상건(FDS)</span></a>
				</li>
				<?php } ?>
				<?php } ?>
			</ul>
		</div>
		<?php if($is_admin) { ?>
		<div class="gnb_side no_logo drop_box">
			<h2>NOTI <div class="arrow_box"><span class="arrow"></span></div></h2>
			<ul id="gnb_1dul" class="drop_ul">
				<li class="gnb_1dli">
					<a href="/?p=noti_list" target="_self" class="gnb_1da <?php if($p == "noti_list") { echo "on"; } ?>"><span>NOTI외부전송</span></a>
				</li>
				<?php /*
				<li class="gnb_1dli">
					<a href="/?p=payment_k1" target="_self" class="gnb_1da <?php if($p == "payment_k1") { echo "on"; } ?>"><span>광원</span></a>
				</li>
				<li class="gnb_1dli">
					<a href="/?p=payment_korpay" target="_self" class="gnb_1da <?php if($p == "payment_korpay") { echo "on"; } ?>"><span>윈(코페이)</span></a>
				</li>
				<li class="gnb_1dli">
					<a href="/?p=payment_korpay_v2" target="_self" class="gnb_1da <?php if($p == "payment_korpay_v2") { echo "on"; } ?>"><span>코페이</span></a>
				</li>
				<li class="gnb_1dli">
					<a href="/?p=payment_danal" target="_self" class="gnb_1da <?php if($p == "payment_danal") { echo "on"; } ?>"><span>윈(다날)</span></a>
				</li>
				<li class="gnb_1dli">
					<a href="/?p=payment_welcom" target="_self" class="gnb_1da <?php if($p == "payment_welcom") { echo "on"; } ?>"><span>웰컴</span></a>
				</li>
				*/ ?>
				<li class="gnb_1dli">
					<a href="/?p=payment_paysis" target="_self" class="gnb_1da <?php if($p == "payment_paysis") { echo "on"; } ?>"><span>페이시스</span></a>
				</li>
				<li class="gnb_1dli">
					<a href="/?p=payment_stn" target="_self" class="gnb_1da <?php if($p == "payment_stn") { echo "on"; } ?>"><span>섹타나인</span></a>
				</li>
				<li class="gnb_1dli">
					<a href="/?p=payment_routeup" target="_self" class="gnb_1da <?php if($p == "payment_routeup") { echo "on"; } ?>"><span>루트업</span></a>
				</li>
				<li class="gnb_1dli">
					<a href="/?p=payment_daou" target="_self" class="gnb_1da <?php if($p == "payment_daou") { echo "on"; } ?>"><span>윈(다우)</span></a>
				</li>
				<li class="gnb_1dli">
					<a href="/?p=payment_korpay" target="_self" class="gnb_1da <?php if($p == "payment_korpay") { echo "on"; } ?>"><span>윈(코페이)</span></a>
				</li>
				<li class="gnb_1dli">
					<a href="/?p=payment_korpay_v2" target="_self" class="gnb_1da <?php if($p == "payment_korpay_v2") { echo "on"; } ?>"><span>코페이</span></a>
				</li>
				<li class="gnb_1dli">
					<a href="/?p=payment_danal" target="_self" class="gnb_1da <?php if($p == "payment_danal") { echo "on"; } ?>"><span>윈(다날)</span></a>
				</li>
			</ul>
		</div>
		<?php } ?>
		<div class="gnb_side no_logo">
			<h2>SETTLEMENT</h2>
			<ul id="gnb_1dul">
				<li class="gnb_1dli">
					<a href="/calc<?php if($member['mb_level'] == "3") { ?>?MM=<?php echo date("n"); ?>&YYYY=<?php echo date("Y"); ?><?php } ?>" target="_self" class="gnb_1da <?php if($p == "settlement") { echo "on"; } ?>"><span>실시간 정산조회</span></a>
				</li>
				<?php /*if($is_admin) { ?>
				<li class="gnb_1dli">
					<a href="/calc/simple" target="_self" class="gnb_1da <?php if($p == "settlement_master2") { echo "on"; } ?>"><span>정산조회 - 간소화</span></a>
				</li>
				<li class="gnb_1dli">
					<a href="/calc/store" target="_self" class="gnb_1da <?php if($p == "settlement_master3") { echo "on"; } ?>"><span>정산조회 - 가맹점</span></a>
				</li>
				<?php }*/ ?>
			</ul>
		</div>
		<?php
		// 스시이안앤 메뉴 허용 조건: mb_sushian_id가 있는 가맹점 또는 특정 허용 아이디
		$sushian_allowed_ids = array('1766037474', '1765765095', '1757467304');
		$is_sushian_allowed = in_array(strval($member['mb_id']), $sushian_allowed_ids);
		$show_sushian_menu = ($member['mb_level'] == 3 && !empty($member['mb_sushian_id'])) || $is_sushian_allowed;
		if($show_sushian_menu) {
		?>
		<div class="gnb_side no_logo">
			<h2>스시이안앤</h2>
			<ul id="gnb_1dul">
				<li class="gnb_1dli">
					<a href="/?p=metapos_payment_list" target="_self" class="gnb_1da <?php if($p == "metapos_payment_list") { echo "on"; } ?>"><i class="fa fa-th-large"></i> <span>스시이안앤 결제정보</span></a>
				</li>
				<li class="gnb_1dli">
					<a href="/?p=metapos_sales_list" target="_self" class="gnb_1da <?php if($p == "metapos_sales_list") { echo "on"; } ?>"><i class="fa fa-bar-chart"></i> <span>스시이안앤 매출내역</span></a>
				</li>
			</ul>
		</div>
		<?php } ?>
		<?php /*if($is_admin) { ?>
		<div class="gnb_side no_logo">
			<h2>SETTLEMENT SFTP</h2>
			<ul id="gnb_1dul">
				<li class="gnb_1dli">
					<a href="/diff/member" target="_self" class="gnb_1da <?php if($p == "sftp_member") { echo "on"; } ?>"><span>차액정산 회원관리</span></a>
				</li>
				<li class="gnb_1dli">
					<a href="/diff/tid" target="_self" class="gnb_1da <?php if($p == "sftp_tid") { echo "on"; } ?>"><span>차액정산 TID</span></a>
				</li>
				<li class="gnb_1dli">
					<a href="/diff/data" target="_self" class="gnb_1da <?php if($p == "sftp_payment") { echo "on"; } ?>"><span>차액정산 데이터 생성</span></a>
				</li>
				<li class="gnb_1dli">
					<a href="/diff/files" target="_self" class="gnb_1da <?php if($p == "sftp_data") { echo "on"; } ?>"><span>차액정산 파일조회</span></a>
				</li>
			</ul>
		</div>
		<?php }*/ ?>
		<?php if(!$is_van_fee_member && $member['mb_level'] >= 4) { ?>
		<div class="gnb_side no_logo">
			<h2>TID/FEE</h2>
			<ul id="gnb_1dul">
				<li class="gnb_1dli">
					<a href="/fee" target="_self" class="gnb_1da <?php if($p == "tid_fee") { echo "on"; } ?>"><span>수수료 관리</span></a>
				</li>
				<?php /*if($is_admin) { ?>
				<li class="gnb_1dli">
					<a href="/fee/alt" target="_self" class="gnb_1da <?php if($p == "tid_fee2") { echo "on"; } ?>"><span>수수료 관리2</span></a>
				</li>
				<li class="gnb_1dli">
					<a href="/fee/tid" target="_self" class="gnb_1da <?php if($p == "tid_pay") { echo "on"; } ?>"><span>TID별 결제금액</span></a>
				</li>
				<?php }*/ ?>
			</ul>
		</div>
		<div class="gnb_side no_logo">
			<h2>MEMBERSHIP</h2>
			<ul id="gnb_1dul">
				<li class="gnb_1dli">
					<a href="/access" target="_self" class="gnb_1da <?php if($p == "member_info") { echo "on"; } ?>"><span>접속정보</span></a>
				</li>
				<?php /*
				<li class="gnb_1dli">
					<a href="https://theme.sir.kr/gnuboard55/bbs/group.php?gr_id=community" target="_self" class="gnb_1da">회원테이블</a>
				</li>
				*/ ?>
				<?php if($is_admin) { ?>
				<li class="gnb_1dli">
					<a href="/store?level=10" target="_self" class="gnb_1da <?php if($p == "member") { if($level == "10") { echo "on"; } } ?>"><span>관리자 관리</span></a>
				</li>
				<?php } ?>
				<?php if($member['mb_level'] >= 8) { ?>
				<li class="gnb_1dli">
					<a href="/store?level=8" target="_self" class="gnb_1da <?php if($p == "member") { if($level == "8") { echo "on"; } } ?>"><span>본사 관리</span></a>
				</li>
				<?php } ?>
				<?php if($member['mb_level'] >= 7) { ?>
				<li class="gnb_1dli">
					<a href="/store?level=7" target="_self" class="gnb_1da <?php if($p == "member") { if($level == "7") { echo "on"; } } ?>"><span>지사 관리</span></a>
				</li>
				<?php } ?>
				<?php if($member['mb_level'] >= 6) { ?>
				<li class="gnb_1dli">
					<a href="/store?level=6" target="_self" class="gnb_1da <?php if($p == "member") { if($level == "6") { echo "on"; } } ?>"><span>총판 관리</span></a>
				</li>
				<?php } ?>
				<?php if($member['mb_level'] >= 5) { ?>
				<li class="gnb_1dli">
					<a href="/store?level=5" target="_self" class="gnb_1da <?php if($p == "member") { if($level == "5") { echo "on"; } } ?>"><span>대리점 관리</span></a>
				</li>
				<?php } ?>
				<?php if($member['mb_level'] >= 4) { ?>
				<li class="gnb_1dli">
					<a href="/store?level=4" target="_self" class="gnb_1da <?php if($p == "member") { if($level == "4") { echo "on"; } } ?>"><span>영업점 관리</span></a>
				</li>
				<?php } ?>
				<?php if($member['mb_level'] >= 3) { ?>
				<li class="gnb_1dli">
					<a href="/store?level=3" target="_self" class="gnb_1da <?php if($p == "member") { if($level == "3") { echo "on"; } } ?>"><span>가맹점 관리</span></a>
				</li>
				<?php } ?>
				<?php if($member['mb_level'] >= 8) { ?>
				<li class="gnb_1dli">
					<a href="/store?level=1" target="_self" class="gnb_1da <?php if($p == "member") { if($level == "1") { echo "on"; } } ?>"><span>삭제회원 관리</span></a>
				</li>
				<?php } ?>
				<?php if($is_admin) { ?>
				<li class="gnb_1dli">
					<a href="/?p=metapos_store_list" target="_self" class="gnb_1da <?php if($p == "metapos_store_list") { echo "on"; } ?>"><i class="fa fa-th-large"></i> <span>스시이안앤 매장정보</span></a>
				</li>
				<li class="gnb_1dli">
					<a href="/?p=metapos_payment_list" target="_self" class="gnb_1da <?php if($p == "metapos_payment_list") { echo "on"; } ?>"><i class="fa fa-th-large"></i> <span>스시이안앤 결제정보</span></a>
				</li>
				<li class="gnb_1dli">
					<a href="/?p=metapos_sales_list" target="_self" class="gnb_1da <?php if($p == "metapos_sales_list") { echo "on"; } ?>"><i class="fa fa-bar-chart"></i> <span>스시이안앤 매출내역</span></a>
				</li>
				<?php } ?>
			</ul>
		</div>
		<?php } ?>
		<?php /*
		<div class="gnb_side no_logo">
			<h2>MPAY</h2>
			<ul id="gnb_1dul">
				<li class="gnb_1dli">
					<a href="https://theme.sir.kr/gnuboard55/bbs/board.php?bo_table=banner" target="_self" class="gnb_1da">수기결제</a>
				</li>
				<li class="gnb_1dli">
					<a href="https://theme.sir.kr/gnuboard55/bbs/group.php?gr_id=community" target="_self" class="gnb_1da">수기결제 내역</a>
				</li>
			</ul>
		</div>
		*/ ?>
		<?php if(!$is_van_fee_member) { ?>
		<div class="gnb_side no_logo<? if($is_admin) {?> drop_box<?}?>">
			<h2>BOARD <? if($is_admin){?><div class="arrow_box"><span class="arrow"></span></div><?}?></h2>
			<ul id="gnb_1dul" class="drop_ul">
				<li class="gnb_1dli">
					<a href="/board?t=notice" target="_self" class="gnb_1da <?php if($p == "bbs" && $t == "notice") { echo "on"; } ?>"><span>공지사항</span></a>
				</li>
			</ul>
		</div>
		<?php } ?>
		<?php if($is_admin) { ?>
		<div class="gnb_side no_logo">
			<h2>ADMIN</h2>
			<ul id="gnb_1dul">
				<li class="gnb_1dli">
					<a href="/?p=manual_payment_config" target="_self" class="gnb_1da <?php if($p == "manual_payment_config") { echo "on"; } ?>"><span>수기 대표가맹점 설정</span></a>
				</li>
				<li class="gnb_1dli">
					<a href="/?p=webhook_config" target="_self" class="gnb_1da <?php if($p == "webhook_config" || $p == "webhook_config_form") { echo "on"; } ?>"><span>결제통보 설정</span></a>
				</li>
				<li class="gnb_1dli">
					<a href="/?p=webhook_history" target="_self" class="gnb_1da <?php if($p == "webhook_history") { echo "on"; } ?>"><span>결제통보 이력</span></a>
				</li>
				<li class="gnb_1dli">
					<a href="/?p=payment_monthly" target="_self" class="gnb_1da <?php if($p == "payment_monthly") { echo "on"; } ?>"><span>월 결제내역</span></a>
				</li>
			</ul>
		</div>
		<?php } ?>
	</div>
</aside>

<script>
$(function () {

	$(".hd_opener").on("click", function() {
		var $this = $(this);
		var $hd_layer = $this.next(".hd_div");

		if($hd_layer.is(":visible")) {
			$hd_layer.hide();
			$this.find("span").text("열기");
		} else {
			var $hd_layer2 = $(".hd_div:visible");
			$hd_layer2.prev(".hd_opener").find("span").text("열기");
			$hd_layer2.hide();

			$hd_layer.show();
			$this.find("span").text("닫기");
		}
	});

	$("#container").on("click", function() {
		$(".hd_div").hide();

	});

	$(".btn_gnb_op").click(function(){
		$(this).toggleClass("btn_gnb_cl").next(".gnb_2dul").slideToggle(300);
		
	});

	$(".hd_closer").on("click", function() {
		var idx = $(".hd_closer").index($(this));
		$(".hd_div:visible").hide();
		$(".hd_opener:eq("+idx+")").find("span").text("열기");
	});
	//NOTI, BOARD에만 적용
	$('.drop_box h2').each(function(e){
		$(this).click(function(){
			$(this).toggleClass('down');
			if($(this).hasClass('down')){
				$('.drop_ul').eq(e).slideDown();		
			}else{
				$('.drop_ul').eq(e).slideUp();				
			}
		});
	});
});
</script>




<!-- 컨텐츠 시작 { -->
<div id="content-wrapper">
	<div id="wrapper">
		<!-- container 시작 { -->
		<div id="container">
			<div class="conle right <? if($p == "main") {?>main<?}?>">
