<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
$thumb_width = 115;
$thumb_height = 80;
?>

<div class="lt_slider_li">
	<strong><a href="<?php echo get_pretty_url($bo_table); ?>" ><?php echo $bo_subject ?></a></strong>
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
		        if ($list[$i]['icon_secret']) echo "<i class=\"fa fa-lock\" aria-hidden=\"true\"></i> ";
		        //echo $list[$i]['icon_reply']." ";
		        echo "<a href=\"".$list[$i]['href']."\" class=\"lt_tit\">";
		        if ($list[$i]['is_notice'])
		            echo "<strong>".$list[$i]['subject']."</strong>";
		        else
		            echo $list[$i]['subject'];
	
		            // if ($list[$i]['link']['count']) { echo "[{$list[$i]['link']['count']}]"; }
		            // if ($list[$i]['file']['count']) { echo "<{$list[$i]['file']['count']}>"; }
	
		        if ($list[$i]['icon_new']) echo " <span class=\"new_icon\">N</span>";
		        if ($list[$i]['icon_file']) echo " <i class=\"fa fa-download\" aria-hidden=\"true\"></i>" ;
		        if ($list[$i]['icon_link']) echo " <i class=\"fa fa-link\" aria-hidden=\"true\"></i>" ;
		        if ($list[$i]['icon_hot']) echo " <i class=\"fa fa-heart\" aria-hidden=\"true\"></i>";
	
		        echo "</a>";
				$content_length = 100;
		        ?>
		        <div class="lt_subject_pre">
	            	<?php echo get_text(cut_str(strip_tags($list[$i]['wr_content']), $content_length), 50); ?>
	            </div>
            
		        <div class="lt_info">
		            <?php echo $list[$i]['name'] ?>
		            <span class="lt_date"><?php echo $list[$i]['datetime2'] ?></span>
		        </div>
	        </div>
	    </li>
	<?php } ?>
	<?php if (count($list) == 0) { //게시물이 없을 때 ?>
	<li class="empty_li">게시물이 없습니다.</li>
	<?php } ?>
	</ul>
</div>
