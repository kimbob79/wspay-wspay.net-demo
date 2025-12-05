<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$outlogin_skin_url.'/style.css">', 0);
?>

<aside id="login_before" class="login_mn">
    <h2>회원메뉴</h2>
	<ul class="no_log">
        <li>
            <span>0</span><br>쪽지
        </li>
        <li>
            <span>0</span><br>포인트
        </li>
        <li>
            <span>0</span><br>스크랩
        </li>
    </ul>
</aside>
<!-- 로그인 전 외부로그인 끝 -->