<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$outlogin_skin_url.'/style.css">', 0);
?>

<!-- 로그인 후 외부로그인 시작 -->
<div id="logo">
	<?php if($is_admin) { ?><a href="<?php echo G5_ADMIN_URL ?>" target="_blank"><?php } else { ?><a href="<?php echo G5_URL ?>"><?php } ?><strong><?php echo $nick ?>님</strong></a>
</div>
<!-- 로그인 후 외부로그인 끝 -->
