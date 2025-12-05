<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$outlogin_skin_url.'/style.css">', 0);
?>

<aside id="pro_before" class="pro">
    <h2>회원로그인</h2>
    <span class="profile_img">
        <?php echo get_member_profile_img($member['mb_id']); ?>
    </span>
</aside>

<!-- 로그인 전 외부로그인 끝 -->
