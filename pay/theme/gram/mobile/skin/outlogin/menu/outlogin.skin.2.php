<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$outlogin_skin_url.'/style.css">', 0);
?>

<!-- 로그인 후 외부로그인 시작 -->
<aside id="login_after" class="login_mn">
    <h2>나의 회원정보</h2>
    <ul class="log">
        <li>
            <a href="<?php echo G5_BBS_URL ?>/memo.php" target="_blank">
            	<span><?php echo $memo_not_read ?></span>
                <br><span class="sound_only">안 읽은</span>쪽지
            </a>
        </li>
        <li>
            <a href="<?php echo G5_BBS_URL ?>/point.php" target="_blank">
				<span><?php echo $point ?></span>
                <br>포인트
            </a>
        </li>
        <li>
            <a href="<?php echo G5_BBS_URL ?>/scrap.php" target="_blank">
            	<span><?php echo $mb_scrap_cnt; ?></span>
				<br>스크랩
            </a>
        </li>
    </ul>

</aside>

<script>
// 탈퇴의 경우 아래 코드를 연동하시면 됩니다.
function member_leave()
{
    if (confirm("정말 회원에서 탈퇴 하시겠습니까?"))
        location.href = "<?php echo G5_BBS_URL ?>/member_confirm.php?url=member_leave.php";
}
</script>
<!-- 로그인 후 외부로그인 끝 -->
