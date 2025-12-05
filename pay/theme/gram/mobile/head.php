<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

include_once(G5_THEME_PATH.'/head.sub.php');
include_once(G5_LIB_PATH.'/latest.lib.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');
include_once(G5_LIB_PATH.'/poll.lib.php');
include_once(G5_LIB_PATH.'/visit.lib.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/popular.lib.php');
?>

<div id="all_wrap">
<header id="hd">
	<h1 id="hd_h1"><?php echo $g5['title'] ?></h1>

	<div class="to_content"><a href="#container">본문 바로가기</a></div>

	<?php
	if(defined('_INDEX_')) { // index에서만 실행
		include G5_MOBILE_PATH.'/newwin.inc.php'; // 팝업레이어
	} ?>

	<div id="hd_wrapper">


		<?php if($bo_table != "urlpay") { ?>
		<?php if(!$is_guest) { ?>
		<?php if ($is_admin == 'super' || $is_admin) { ?>
		<a href="<?php echo G5_URL ?>/member/list.php" class="hd_admin">회원관리</a>
		<?php } else { ?>
		<a href="<?php echo G5_URL ?>/member/add.php?mb_id=<?php echo $member['mb_id']; ?>&w=u" class="hd_admin">정보수정</a>
		<?php } ?>
		<?php } ?>
		
		<?php echo outlogin('theme/name'); // 로고 ?>
		<?php if(!$is_guest) { ?>
		<div id="ft">
			<ul class="gram_fix_btn">
				<li><a href="<?php echo G5_URL ?>/payment/pay.php" class="gf_btn <?php if($bo_table == "pay") { echo "on"; } ?>">수기결제</a></li>
				<li><a href="<?php echo G5_URL ?>/payment/list.php" class="gf_btn <?php if($bo_table == "list") { echo "on"; } ?>">결제내역</a></li>
				<?php if($member['mb_14'] == "1") { ?>
				<li><a href="<?php echo G5_URL ?>/url/list.php" class="gf_btn last <?php if($bo_table == "url") { echo "on"; } ?>">URL결제</span></a></li>
				<?php } else { ?>
				<li><a href="#" class="gf_btn last" onclick="alert('결제권한이 없습니다.');">URL결제</span></a></li>
				<?php } ?>
			</ul>
		</div>
		<?php } ?>


		<?php if ($is_member) {  ?>
		<a href="<?php echo G5_BBS_URL ?>/logout.php" class="hd_modi">로그아웃</a>
		<?php }  ?>
		<?php } else { ?>
		<div id="logo">
			<a href="http://pay.mpc.icu/url/pay.php?urlcode=<?php echo $urlcode; ?>"><strong>상품결제 페이지</strong></a>
		</div>
		<?php }  ?>



	</div>
</header>

<div id="wrapper">
	<div id="container">
		<?php /*
		<?php if (!defined("_INDEX_")) { ?>
		<h2 id="container_title" class="top" title="<?php echo get_text($g5['title']); ?>">
			<a href="javascript:history.back();"><i class="fa fa-chevron-left" aria-hidden="true"></i><span class="sound_only">뒤로가기</span></a> <?php echo get_head_title($g5['title']); ?>
		</h2>
		<?php } ?>
		*/ ?>