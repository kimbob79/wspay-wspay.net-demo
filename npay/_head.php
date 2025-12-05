<?php
	include_once("./_head.sub.php");
?>

<div id="wrap">
	<header id="global-header">
		<div class="sticky-bar"></div>
		<div class="header-wrapper">
			<h1>
				<a href="./"><span>WANNA</span></a>
			</h1>
			<?php if($no_menu != "yes") { ?>
			<nav class="gnb">
				<div class="menu-btn">메뉴클릭</div>
				<div class="mobile-lnb">
					<ul>
						<?php
							include("./_menu.php");
						?>
					</ul>
					<div class="mobile-info">
						<?php if($is_guest) { ?>
						<a href="./?p=login">로그인</a>
						<?php } else { ?>
						<a href="./?p=logout">로그아웃</a>
						<?php } ?>
						<a href="./?p=member_form&mb_id=<?php echo $member['mb_id']; ?>&w=u"><?php echo $member['mb_nick']; ?></a>
						<?php /*
						<a href="tel:1800-3772">고객센터 전화문의</a>
						<a href="https://seller.payapp.kr/a/signIn">로그인</a>
						<a title="페이앱 카드 무이자 행사 내용 보기" onclick="cardevent();">무이자 할부안내</a>
						<button type="button" onclick="window.open('https://seller.payapp.kr/a/seller_regist')" class="btn btn-blue btn-round btn-block">판매자 회원가입</button>
						<div class="mobile-ban">
							<a href="http://blogpay.co.kr/" target="_blank" title="새창으로 이동">
								<img src="https://payapp.kr/homepage/images/banner/img_aside_blogpay_m.png" alt="블로그페이 바로가기">
							</a>
						</div>
						*/ ?>
					</div>
				</div>

				<div class="web">
					<ul>
						<?php
							include("./_menu.php");
						?>
					</ul>
				</div>
			</nav>
			<?php } ?>
			<?php
				if($p) {
					include_once("./_login.php");
				}
			?>

		</div>
	</header>