<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

global $is_admin;

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$visit_skin_url.'/style.css">', 0);
?>

<aside id="visit">
    <h2>접속자집계 <i class="fa fa-angle-right"></i></h2>
    <ul>
        <li>오늘<span><?php echo number_format($visit[1]) ?></span></li>
        <li>어제<span><?php echo number_format($visit[2]) ?></span></li>
        <li>최대<span><?php echo number_format($visit[3]) ?></span></li>
        <li>전체<span><?php echo number_format($visit[4]) ?></span></li>
        <?php if ($is_admin == "super") { ?><li class="visit_admin"><a href="<?php echo G5_ADMIN_URL ?>/visit_list.php" class="btn_b01"><i class="fa fa-cog" aria-hidden="true"></i><span class="sound_only">상세보기</span></a></li><?php } ?>
    </ul>
</aside>
