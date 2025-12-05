<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$outlogin_skin_url.'/style.css">', 0);
?>

<!-- 로그인 후 외부로그인 시작 -->
<aside id="pro_after" class="pro">
    <h2>나의 회원정보</h2>
    <span class="profile_img">
        <?php echo get_member_profile_img($member['mb_id']); ?>
    </span>
</aside>
<!-- 로그인 후 외부로그인 끝 -->
