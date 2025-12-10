<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
$thumb_width = 115;
$thumb_height = 80;
?>

<div class="pic_tab">
	<h2 class="lat_title"><a href="<?php echo get_pretty_url($bo_table); ?>"><?php echo $bo_subject ?></a></h2>
	
	<div class="pic_tab_wrap">
		<div class="pic_tab_ul">
		    <ul>
		    <?php
		    for ($i=0; $i<count($list); $i++) {
		    $thumb = get_list_thumbnail($bo_table, $list[$i]['wr_id'], $thumb_width, $thumb_height, false, true);
		
		    if($thumb['src']) {
		        $img = $thumb['src'];
		    } else {
		        $img = G5_THEME_IMG_URL.'/no_img.png';
		        $thumb['alt'] = '이미지가 없습니다.';
		    }
		    $img_content = '<img src="'.$img.'" alt="'.$thumb['alt'].'" >';
		    ?>
		        <li>
		            <a href="<?php echo $list[$i]['href'] ?>" class="lt_img"><?php echo $img_content; ?></a>
			        <div class="lt_cnt">
			        	<?php
			            echo "<a href=\"".$list[$i]['href']."\" class=\"lt_tit\"> ";
						if ($list[$i]['icon_secret']) echo "<i class=\"fa fa-lock\" aria-hidden=\"true\"></i><span class=\"sound_only\">비밀글</span> ";
			            if ($list[$i]['is_notice'])
			                echo "<strong>".$list[$i]['subject']."</strong>";
			            else
			                echo $list[$i]['subject'];
			            
			            if ($list[$i]['icon_new']) echo "<span class=\"new_icon\">N<span class=\"sound_only\">새글</span></span>";
			            if ($list[$i]['icon_hot']) echo "<span class=\"hot_icon\">H<span class=\"sound_only\">인기글</span></span>";
			
			            echo "</a>";
						$content_length = 100;
			            ?>
			            <div class="lt_subject_pre">
	                    	<?php echo get_text(cut_str(strip_tags($list[$i]['wr_content']), $content_length), 50); ?>
	                    </div>
			            
			            <div class="lt_info">
				  			<span class="sound_only">작성자</span><span class="lt_nick"><?php echo $list[$i]['name'] ?></span>
				  			<span class="lt_date"><?php echo $list[$i]['datetime2'] ?></span>
						</div>
					</div>
		        </li>
		    <?php }  ?>
		    <?php if (count($list) == 0) { //게시물이 없을 때  ?>
		    <li class="empty_li">게시물이 없습니다.</li>
		    <?php }  ?>
		    </ul>
		</div>
	</div>
</div>

<script>
// 각 pic_tabs 스킨에 bxSlider를 적용한다.
$('.pic_tab_ul').each(function(idx) {
    $('.pic_tab_heading').eq(idx).attr('id', 'pic_tab_heading_' + idx);

    if($(this).find('ul').length > 1) {
        $(this).bxSlider({
            hideControlOnEnd: true,
            pagerCustom: '#pic_tab_heading_' + idx
        });
    }
});
</script>
