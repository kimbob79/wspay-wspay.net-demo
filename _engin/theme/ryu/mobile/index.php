<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

define('_INDEX_', true);

include_once(G5_THEME_MOBILE_PATH.'/head.php');
include_once(G5_LIB_PATH.'/popular.lib.php');

add_javascript('<script src="'.G5_JS_URL.'/jquery.bxslider.js"></script>', 10);
?>

<!-- 메인 최신글 시작 -->
<div class="conle_idx_top">
	
	<div class="lt" style="float:left;width:49.5%">
    	<div class="lt_slider">
    		<!-- 베이직 슬라이더1 { -->
			<?php
		    // 이 함수가 바로 최신글을 추출하는 역할을 합니다.
		    // 사용방법 : latest(스킨, 게시판아이디, 출력라인, 글자수);
		    // 테마의 스킨을 사용하려면 theme/basic 과 같이 지정
		    echo latest('theme/basic_slider', 'free', 4, 20);
		    ?>
		    <!-- } 베이직 슬라이더1 끝 -->
			<!-- 베이직 슬라이더2 { -->
			<?php
		    // 이 함수가 바로 최신글을 추출하는 역할을 합니다.
		    // 사용방법 : latest(스킨, 게시판아이디, 출력라인, 글자수);
		    // 테마의 스킨을 사용하려면 theme/basic 과 같이 지정
		    echo latest('theme/basic_slider', 'gallery', 4, 20);
		    ?>
			<?php
		    // 이 함수가 바로 최신글을 추출하는 역할을 합니다.
		    // 사용방법 : latest(스킨, 게시판아이디, 출력라인, 글자수);
		    // 테마의 스킨을 사용하려면 theme/basic 과 같이 지정
		    echo latest('theme/basic_slider', 'qa', 4, 20);
		    ?>
		    <!-- } 베이직 슬라이더2 끝 -->
		</div>
	</div>
	<div class="lt lt_even" style="float:left;width:49.5%">
    	<div class="lt_slider">
    		<!-- 베이직 슬라이더1 { -->
			<?php
		    // 이 함수가 바로 최신글을 추출하는 역할을 합니다.
		    // 사용방법 : latest(스킨, 게시판아이디, 출력라인, 글자수);
		    // 테마의 스킨을 사용하려면 theme/basic 과 같이 지정
		    echo latest('theme/basic_slider', 'free', 4, 20);
		    ?>
		    <!-- } 베이직 슬라이더1 끝 -->
			<!-- 베이직 슬라이더2 { -->
			<?php
		    // 이 함수가 바로 최신글을 추출하는 역할을 합니다.
		    // 사용방법 : latest(스킨, 게시판아이디, 출력라인, 글자수);
		    // 테마의 스킨을 사용하려면 theme/basic 과 같이 지정
		    echo latest('theme/basic_slider', 'gallery', 4, 20);
		    ?>
			<?php
		    // 이 함수가 바로 최신글을 추출하는 역할을 합니다.
		    // 사용방법 : latest(스킨, 게시판아이디, 출력라인, 글자수);
		    // 테마의 스킨을 사용하려면 theme/basic 과 같이 지정
		    echo latest('theme/basic_slider', 'qa', 4, 20);
		    ?>
		    <!-- } 베이직 슬라이더2 끝 -->
		</div>
	</div>

    <div class="gal_tab tabs" style="float:left;width:100%;">
		<div id="tab_top">
			<div class="pic_tab_heading" role="tablist" aria-label="Entertainment">
				<button role="tab" aria-selected="true" aria-controls="nils-tab" id="nils">게시판1</button>
				<button role="tab" aria-selected="false" aria-controls="agnes-tab" id="agnes" tabindex="-1">게시판2</button>
				<!-- <a href="" class="lt_more">더보기</a> -->
			</div>
		</div>
		<div id="tab_bottom">
			<div tabindex="0" role="tabpanel" id="nils-tab" aria-labelledby="nils" class="tab_list">
				<!-- 탭 갤러리 최신글 1 { -->
			    <?php
			    // 이 함수가 바로 최신글을 추출하는 역할을 합니다.
			    // 사용방법 : latest(스킨, 게시판아이디, 출력라인, 글자수);
			    // 테마의 스킨을 사용하려면 theme/basic 과 같이 지정
			    echo latest('theme/pic_basic', 'gallery', 6, 23);
			    ?>
			    <!-- } 탭 갤러리 최신글1 끝 -->
			</div>
	
			<div tabindex="0" role="tabpanel" id="agnes-tab" aria-labelledby="agnes" hidden="" class="tab_list">
				<!-- 탭 갤러리 최신글2 { -->
			    <?php
			    // 이 함수가 바로 최신글을 추출하는 역할을 합니다.
			    // 사용방법 : latest(스킨, 게시판아이디, 출력라인, 글자수);
			    // 테마의 스킨을 사용하려면 theme/basic 과 같이 지정
			    echo latest('theme/pic_basic', 'free', 6, 23);
			    ?>
			    <!-- } 탭 갤러리 최신글2 끝 -->
		    </div>
	    </div>
    </div>
</div>
<!-- 메인 최신글 끝 -->

<div class="index_menu">
	<ul class="shortcut">
        <li><a href="<?php echo G5_BBS_URL ?>/faq.php"><i class="fa fa-question-circle"></i> FAQ</a></li>
        <li><a href="<?php echo G5_BBS_URL ?>/qalist.php"><i class="fa fa-comments"></i> Q&A</a></li>
        <li><a href="<?php echo G5_BBS_URL ?>/new.php"><i class="fa fa-history"></i> 새글</a></li>
        <li class="sc_current">
        	<a href="<?php echo G5_BBS_URL ?>/current_connect.php"><i class="fa fa-users"></i> 접속자</a>
        </li>
        <li class="sc_visit"><?php echo visit("theme/basic"); // 방문자수 ?></li>
    </ul>
</div>

<!-- 최신글 탭 스타일 -->
<script src="<?php echo G5_THEME_JS_URL ?>/latest_tab.js"></script>
<!-- 게시판 슬라이더 -->
<script>
$('.lt_slider').each(function(){
	$(this).bxSlider({
		pager:true,
		hideControlOnEnd: true,
		nextText: '<i class="fa fa-angle-right" aria-hidden="true"></i>',
		prevText: '<i class="fa fa-angle-left" aria-hidden="true"></i>'
	});
});
</script>

<?php
include_once(G5_THEME_MOBILE_PATH.'/tail.php');
?>
