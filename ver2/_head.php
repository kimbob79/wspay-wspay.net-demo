<!doctype html>
<html>
<head>
	<meta property="og:url" content="http://mpc.icu/">
	<meta property="og:title" content="PASSGO">
	<meta property="og:type" content="website">
	<meta property="og:image" content="http://k.kakaocdn.net/14/dn/btqC4uKcq7H/4Pmwbc2IgMyWfsgGXm0fAK/o.jpg">
	<meta property="og:description" content="패스고 정산 프로그램">
	<meta property="og:site_name" content="PASSGO">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,shrink-to-fit=no">
	<meta name="format-detection" content="telephone=no">
	<meta name="theme-color" content="#000000">
	<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="Expires" content="0">



	<link rel="apple-touch-icon" sizes="57x57" href="./img/favicon/apple-icon-57x57.png">
	<link rel="apple-touch-icon" sizes="60x60" href="./img/favicon/apple-icon-60x60.png">
	<link rel="apple-touch-icon" sizes="72x72" href="./img/favicon/apple-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="76x76" href="./img/favicon/apple-icon-76x76.png">
	<link rel="apple-touch-icon" sizes="114x114" href="./img/favicon/apple-icon-114x114.png">
	<link rel="apple-touch-icon" sizes="120x120" href="./img/favicon/apple-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="144x144" href="./img/favicon/apple-icon-144x144.png">
	<link rel="apple-touch-icon" sizes="152x152" href="./img/favicon/apple-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="180x180" href="./img/favicon/apple-icon-180x180.png">
	<link rel="icon" type="image/png" sizes="192x192"  href="./img/favicon/android-icon-192x192.png">
	<link rel="icon" type="image/png" sizes="32x32" href="./img/favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="96x96" href="./img/favicon/favicon-96x96.png">
	<link rel="icon" type="image/png" sizes="16x16" href="./img/favicon/favicon-16x16.png">
	<link rel="manifest" href="./img/favicon/manifest.json">
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="msapplication-TileImage" content="./img/favicon/ms-icon-144x144.png">
	<meta name="theme-color" content="#ffffff">




	<title>PASSGO</title>
	<?php /*
	<script defer="defer" src="./js/script.js"></script>
	*/ ?>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
	<link href="./css/style.css?ver=<?php echo time(); ?>" rel="stylesheet">
	<?php /*
	<link href="https://hangeul.pstatic.net/hangeul_static/css/nanum-square-neo.css" rel="stylesheet">
	*/ ?>
	<link href="https://cdn.jsdelivr.net/gh/sunn-us/SUIT/fonts/variable/woff2/SUIT-Variable.css" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo G5_JS_URL; ?>/font-awesome/css/font-awesome.min.css?ver=220620">
	<link type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/base/jquery-ui.css" rel="stylesheet" />
<link type="text/css" href="http://cajung.com/_engin/plugin/jquery-ui/style.css?ver=220620">
<?php
	include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
?>
</head>
<body>
<div id="root" class="mobile">
	<div class="KDC_Layout__root__fakZh">
		<div id="kakaoHead" class="KDC_Header__root__b-dI8" role="banner">
			<h1 class="tit_logo"><a href="./" id="kakaoServiceLogo" class="link_kakaodevelopers"><span class="logo"><span style="color:red">PASS</span>GO</span></a></h1>
			<nav id="kakaoGnb" class="gnb_developers" role="navigation">
				<h2 class="screen_out">메인 메뉴</h2>
				<div class="menu_gnb">
					<div class="area_gnb">
						<ul class="list_gnb">
							<?php /*
							<li><a href="/console/app" class="link_gnb">로그아웃</a></li>
							<li><a href="/product" class="link_gnb">제품</a></li>
							<li><a href="/docs" class="link_gnb">문서</a></li>
							<li><a href="/tool" class="link_gnb">도구</a></li>
							*/ ?>
							<li>
								<div id="logout" class="KDC_Select__root__c163b Android_market_url_select__tmInf">
									<span class="screen_out">선택상자</span>
									<input type="hidden" name="market" value="google_play">
									<button type="button" class="link_selected"><?php echo $member['mb_nick']; ?><span class="screen_out">선택됨</span><span class="kdc_ico_developers"></span></button>
									<span class="screen_out">선택옵션</span>
									<div class="select_opt">
										<div class="select_box">
											<ul class="list_select">
												<li><span class="link_select"><a href="<?php echo G5_ADMIN_URL ?>/member_form.php?w=u&amp;mb_id=<?php echo $member['mb_id'] ?>">관리자정보</a></span></li>
												<li><span class="link_select"><a href="./logout.php">로그아웃</a></span></li>
												<?php /*
												<li><span class="link_select">없음</span></li>
												<li><span class="link_select">구글 플레이</span></li>
												<li><span class="link_select">직접 입력</span></li>
												*/ ?>
											</ul>
										</div>
									</div>
								</div>
							</li>
							<?php /*
							<li class="show_m">
								<div class="menu_lang">
									<strong class="screen_out">언어</strong>
									<div class="area_lang">
										<ul class="list_lang">
											<li><a href="#" class="link_lang selected">KOR</a></li>
											<li><a href="/changeLang?lang=en" class="link_lang">ENG</a></li>
										</ul>
									</div>
								</div>
							</li>
							*/ ?>
						</ul>
					</div>
				</div>
				<?php /*
				<strong class="screen_out">계정 정보</strong>
				<div class="menu_my show_pc">
					<button type="button" class="btn_email" aria-haspopup="true" aria-expanded="false">redpay2022@gmail.com<span class="kdc_ico_developers"></span></button>
				</div>
				<div class="menu_lang show_pc">
					<strong class="screen_out">언어</strong>
					<div class="area_lang">
						<ul class="list_lang">
							<li><a href="#" class="link_lang selected">KOR</a></li>
							<li><a href="/changeLang?lang=en" class="link_lang">ENG</a></li>
						</ul>
					</div>
				</div>
				*/ ?>
			</nav>
		</div>

		<div class="KDC_Breadcrumb__root__oM87S KDC_Breadcrumb__responsive__XpvXZ">
			
			<ul class="list_path">
				<?php if($is_admin) { ?><li class="item_path"><button type="button" class="KDC_Breadcrumb__toggle_lnb_button__dFYys"><span class="KDC_Icon__root__GRlIs KDC_Icon__hamburger_menu__aCOSD mt-5"></span><p class="screen_out">서브 메뉴 열기</p></button></li><?php } ?>
				<?php /*
				<li class="menu-li off">
					<a href="./?p=payment" class="menu-a">결제내역</a>
				</li>
				<li class="menu-li off">
					<a href="./?p=settlement&MM=<?php echo date("n"); ?>&YYYY=<?php echo date("Y"); ?>" class="menu-a">정산내역</a>
				</li>
				<li class="menu-li off">
					<a href="./?p=member_table" class="menu-a">테이블</a>
				</li>
				*/ ?>
				<li class="item_path"><a href="./?p=main"><span class="link_path <?php if($p == "main") { echo "on"; } ?>">대쉬보드</span></a><span class="txt_arr">|</span></li>
				<li class="item_path"><a href="./?p=payment"><span class="link_path <?php if($p == "payment") { echo "on"; } ?>">결제내역</span></a><span class="txt_arr">|</span></li>
				<li class="item_path"><a href="./?p=settlement&MM=<?php echo date("n"); ?>&YYYY=<?php echo date("Y"); ?>"><span class="link_path <?php if($p == "settlement") { echo "on"; } ?>">정산내역</span></a><span class="txt_arr">|</span></li>
				<li class="item_path"><a href="./?p=member_table"><span class="link_path <?php if($p == "member_table") { echo "on"; } ?>">테이블</span></a></li>
			</ul>
		</div>

		<div class="KDC_Body__root__CQLKv">
			<div class="KDC_LNB__root__JFmyq show_pc KDC_LNB__responsive_hidden__JvKxz" style="bottom: 50px;">
				<div>
					<?php include("./_menu.php"); ?>
				</div>
				<div class="KDC_LNBFilter__root__tyiuS">
					<label for="lnb_filter">
						<span class="KDC_Icon__root__GRlIs KDC_Icon__baseline_sort_black__RTErP KDC_LNBFilter__search_icon__XxgEx"></span>
					</label>
					<input name="lnb_filter" autocomplete="off" spellcheck="false" title="승인번호 검색" type="search" id="lnb_filter" class="KDC_Input__root__3M8Hf KDC_LNBFilter__input__SRGMT" placeholder="승인번호 검색" value="">
				</div>
			</div>

			<div class="KDC_LNB__root__JFmyq show_m KDC_Breadcrumb__root__oM87S KDC_Breadcrumb__responsive__XpvXZ">
				<div class="KDC_LNB__inner_side__exzVO">
					<?php include("./_menu.php"); ?>
				</div>
			</div>