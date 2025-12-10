<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (!defined('_INDEX_')) define('_INDEX_', true);

include_once(G5_THEME_PATH.'/head.sub.php');
include_once(G5_LIB_PATH.'/latest.lib.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');
include_once(G5_LIB_PATH.'/poll.lib.php');
include_once(G5_LIB_PATH.'/visit.lib.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/popular.lib.php');
?>

<script>
jQuery(function($) {
	var $bodyEl = $('body'),
		$sidedrawerEl = $('#sidedrawer');
	
	function showSidedrawer() {
		// show overlay
		var options = {
  		onclose: function() {
			$sidedrawerEl
      		.removeClass('active')
      		.appendTo(document.body);
		}
		};
    
		var $overlayEl = $(mui.overlay('on', options));
    
    	// show element
    	$sidedrawerEl.appendTo($overlayEl);
		setTimeout(function() {
  			$sidedrawerEl.addClass('active');
		}, 20);
  	}

	function hideSidedrawer() {
		$bodyEl.toggleClass('hide-sidedrawer');
	}

	$('.js-show-sidedrawer').on('click', showSidedrawer);
	$('.js-hide-sidedrawer').on('click', hideSidedrawer);

});
</script>

<!-- 상단 시작 { -->
<header id="header">
    <h1 id="hd_h1"><?php echo $g5['title'] ?></h1>

    <div class="to_content"><a href="#container">본문 바로가기</a></div>
    <div id="mobile-indicator"></div>
    
    <?php
    if(defined('_INDEX_')) { // index에서만 실행
        include G5_MOBILE_PATH.'/newwin.inc.php'; // 팝업레이어
    } ?>

	<div id="hd_wrapper" class="">
		<div class="gnb_side_btn">
			<?/*<a class="sidedrawer-toggle mui--visible-xs-inline-block mui--visible-sm-inline-block js-show-sidedrawer slid_toggle" style="padding-left:10px;"><img src="/img/head/bars.svg" alt="메뉴" width="22px"><span class="sound_only">모바일 전체메뉴</span></a>
			<a class="sidedrawer-toggle mui--hidden-xs mui--hidden-sm js-hide-sidedrawer"><img src="/img/head/bars.svg" alt="메뉴" width="22px"><span class="sound_only">전체메뉴</span></a>20240405 kyj 아래 메뉴로 변경 */?> 
			<a class="sidedrawer-toggle mui--hidden-xs mui--hidden-sm js-hide-sidedrawer slide_svg">
				<svg>
					<use xlink:href="#s_menu" />
					<use xlink:href="#s_menu" />
				</svg>
				<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
					<symbol xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 56" id="s_menu">
						<path d="M48.33,45.6H18a14.17,14.17,0,0,1,0-28.34H78.86a17.37,17.37,0,0,1,0,34.74H42.33l-21-21.26L47.75,4"/>
					</symbol>
				</svg>
			</a><?/* pc버전*/ ?>
			<a class="sidedrawer-toggle mui--visible-xs-inline-block mui--visible-sm-inline-block js-show-sidedrawer slid_toggle slide_svg">		
				<svg>
					<use xlink:href="#s_menu" />
					<use xlink:href="#s_menu" />
				</svg>
				<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
					<symbol xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 56" id="s_menu">
						<path d="M48.33,45.6H18a14.17,14.17,0,0,1,0-28.34H78.86a17.37,17.37,0,0,1,0,34.74H42.33l-21-21.26L47.75,4"/>
					</symbol>
				</svg>
			</a><?/* mob버전*/ ?>
			<?php if($is_admin) { ?>
				<a href="http://mpc.icu/" class="mu_home"><img src="/img/head/home.svg" alt="홈" width="22px"></a>
			<?php } ?>
		</div>
		<div id="logo">
			<a href="http://mpc.icu/">
				<svg version="1.1" id="logo_svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 188 28" style="enable-background:new 0 0 188 28;" xml:space="preserve" width="188" height="28">
				<style type="text/css">
				   .st0{fill:#221613;}
				</style>
				<g>
				   <g>
					  <polygon class="st0 svg-elem-1" points="57.98,2.46 42.64,25.54 47.49,25.54 59.59,6.95 71.48,25.54 76.4,25.54 61.16,2.46       "></polygon>
					  <polygon class="st0 svg-elem-2" points="108.16,19.6 85.18,2.46 81.41,2.46 81.41,25.54 85.21,25.54 85.21,7.58 109.83,25.54 111.96,25.54 
						 111.96,2.46 108.16,2.46       "></polygon>
					  <polygon class="st0 svg-elem-3" points="145.84,19.6 122.87,2.46 119.1,2.46 119.1,25.54 122.9,25.54 122.9,7.58 147.51,25.54 149.64,25.54 
						 149.64,2.46 145.84,2.46       "></polygon>
					  <polygon class="st0 svg-elem-4" points="173.18,2.46 170,2.46 154.66,25.54 159.51,25.54 171.6,6.95 183.5,25.54 188.41,25.54       "></polygon>
					  <polygon class="st0 svg-elem-5" points="43.14,2.46 31.05,21.04 26.4,13.78 24.07,10.15 21.71,13.78 16.98,21.04 5.08,2.46 0.17,2.46 
						 15.41,25.54 18.58,25.54 24.04,17.32 29.47,25.54 32.65,25.54 47.99,2.46       "></polygon>
				   </g>
				</g>
				</svg>
				<!-- <img src="./img/pg_logo.png" alt="WANNA" class="pc_logo">
				<img src="./img/mpg_logo.png" alt="WANNA" class="mob_logo"> -->
			</a><?/*WANNA <span style="font-size:11px; color:#888">판매자센터</span>*/?>
		</div>
      	<?/*<div class="gnb_side_btn">
			<a class="sidedrawer-toggle mui--visible-xs-inline-block mui--visible-sm-inline-block js-show-sidedrawer"><i class="fa fa-bars"></i><span class="sound_only">모바일 전체메뉴</span></a>
			<a class="sidedrawer-toggle mui--hidden-xs mui--hidden-sm js-hide-sidedrawer"><i class="fa fa-bars"></i><span class="sound_only">전체메뉴</span></a>
        </div>

        <div id="logo">
			<a href="./"><img src="/img/pg_logo.png" alt="WANNA"></a>
        </div> 20240430 변경 */ ?>
        <?/*WANNA <span style="font-size:11px; color:#888">판매자센터</span>*/?>

        <div class="header_ct">
			<div class="hd_sch_wr">
	        	<button class="hd_sch_bt"><img src="/img/head/search.svg" alt="검색" width="20px"><span class="sound_only">검색창 열기</span></button>
	            <fieldset id="hd_sch">
		            <h2>사이트 내 전체검색</h2>
		            <form name="fsearchbox" action="<?php echo G5_BBS_URL ?>/search.php" onsubmit="return fsearchbox_submit(this);" method="get">
		            <input type="hidden" name="sfl" value="wr_subject||wr_content">
		            <input type="hidden" name="sop" value="and">
		            <input type="text" name="stx" id="sch_stx" placeholder="검색어를 입력해주세요" required maxlength="20">
		            <button type="submit" value="검색" id="sch_submit"><img src="/img/head/search.svg" alt="검색" width="20px"><span class="sound_only">검색</span></button>
		            </form>
				</fieldset>
	        </div>
			<div id="tnb">
	        	<?php echo outlogin("theme/basic"); ?>
		    </div>
	        <script>
            function fsearchbox_submit(f)
            {
                if (f.stx.value.length < 2) {
                    alert("검색어는 두글자 이상 입력하십시오.");
                    f.stx.select();
                    f.stx.focus();
                    return false;
                }

                // 검색에 많은 부하가 걸리는 경우 이 주석을 제거하세요.
                var cnt = 0;
                for (var i=0; i<f.stx.value.length; i++) {
                    if (f.stx.value.charAt(i) == ' ')
                        cnt++;
                }

                if (cnt > 1) {
                    alert("빠른 검색을 위하여 검색어에 공백은 한개만 입력할 수 있습니다.");
                    f.stx.select();
                    f.stx.focus();
                    return false;
                }

                return true;
            }
            
            $(document).ready(function(){
		        $(document).on("click", ".hd_sch_bt", function() {
			        $("#hd_sch").toggle();
			    });
			    $(".sch_more_close").on("click", function(){
					$("#hd_sch").hide();
				});
			});
            </script>
        </div>
	</div>
</header>
<!-- } 상단 끝 -->


<aside id="sidedrawer">
	<div id="gnb">
		<div class="gnb_side logo_gnb">
			<div id="logo">
				<a href="./"><span class="red">RED</span>PAY</a>
			</div>
		</div>
		<div class="gnb_side no_logo">
			<h2>PAYMENT</h2>
			<ul id="gnb_1dul">
				<li class="gnb_1dli">
					<a href="/?p=payment" target="_self" class="gnb_1da <?php if($p == "payment") { echo "on"; } ?>">실시간 결제내역</a>
				</li>
				<?php if($member['mb_level'] >= 4) { ?>
				<li class="gnb_1dli">
					<a href="/?p=payment_member" target="_self" class="gnb_1da <?php if($p == "payment_member") { echo "on"; } ?>">가맹점별 결제내역</a>
				</li>
				<?php } ?>
				<?php if($is_admin) { ?>
				<li class="gnb_1dli">
					<a href="/?p=cancel_payment" target="_self" class="gnb_1da <?php if($p == "cancel_payment") { echo "on"; } ?>">취소 신청내역</a>
				</li>
				<?php } ?>
			</ul>
		</div>
		<?php if($is_admin) { ?>
		<div class="gnb_side no_logo">
			<h2>NOTI</h2>
			<ul id="gnb_1dul">
				<li class="gnb_1dli">
					<a href="/?p=payment_k1" target="_self" class="gnb_1da <?php if($p == "payment_k1") { echo "on"; } ?>">광원</a>
				</li>
				<li class="gnb_1dli">
					<a href="/?p=payment_korpay" target="_self" class="gnb_1da <?php if($p == "payment_korpay") { echo "on"; } ?>">코페이</a>
				</li>
				<li class="gnb_1dli">
					<a href="/?p=payment_danal" target="_self" class="gnb_1da <?php if($p == "payment_danal") { echo "on"; } ?>">다날</a>
				</li>
				<li class="gnb_1dli">
					<a href="/?p=payment_welcom" target="_self" class="gnb_1da <?php if($p == "payment_welcom") { echo "on"; } ?>">웰컴</a>
				</li>
			</ul>
		</div>
		<?php } ?>
		<div class="gnb_side no_logo">
			<h2>SETTLEMENT</h2>
			<ul id="gnb_1dul">
				<li class="gnb_1dli">
					<a href="/?p=settlement<?php if($member['mb_level'] == "3") { ?>&MM=<?php echo date("n"); ?>&YYYY=<?php echo date("Y"); ?><?php } ?>" target="_self" class="gnb_1da <?php if($p == "settlement") { echo "on"; } ?>">실시간 정산조회</a>
				</li>
			</ul>
		</div>
		<?php if($member['mb_level'] >= 4) { ?>
		<div class="gnb_side no_logo">
			<h2>TID/FEE</h2>
			<ul id="gnb_1dul">
				<li class="gnb_1dli">
					<a href="/?p=tid_fee" target="_self" class="gnb_1da <?php if($p == "tid_fee") { echo "on"; } ?>">수수료 관리</a>
				</li>
			</ul>
		</div>
		<div class="gnb_side no_logo">
			<h2>MEMBERSHIP</h2>
			<ul id="gnb_1dul">
				<li class="gnb_1dli">
					<a href="/?p=member_info" target="_self" class="gnb_1da <?php if($p == "member_info") { echo "on"; } ?>">접속정보</a>
				</li>
				<?php /*
				<li class="gnb_1dli">
					<a href="https://theme.sir.kr/gnuboard55/bbs/group.php?gr_id=community" target="_self" class="gnb_1da">회원테이블</a>
				</li>
				*/ ?>
				<?php if($member['mb_level'] >= 8) { ?>
				<li class="gnb_1dli">
					<a href="/?p=member&level=8" target="_self" class="gnb_1da <?php if($p == "member") { if($level == "8") { echo "on"; } } ?>">본사 관리</a>
				</li>
				<?php } ?>
				<?php if($member['mb_level'] >= 7) { ?>
				<li class="gnb_1dli">
					<a href="/?p=member&level=7" target="_self" class="gnb_1da <?php if($p == "member") { if($level == "7") { echo "on"; } } ?>">지사 관리</a>
				</li>
				<?php } ?>
				<?php if($member['mb_level'] >= 6) { ?>
				<li class="gnb_1dli">
					<a href="/?p=member&level=6" target="_self" class="gnb_1da <?php if($p == "member") { if($level == "6") { echo "on"; } } ?>">총판 관리</a>
				</li>
				<?php } ?>
				<?php if($member['mb_level'] >= 5) { ?>
				<li class="gnb_1dli">
					<a href="/?p=member&level=5" target="_self" class="gnb_1da <?php if($p == "member") { if($level == "5") { echo "on"; } } ?>">대리점 관리</a>
				</li>
				<?php } ?>
				<?php if($member['mb_level'] >= 4) { ?>
				<li class="gnb_1dli">
					<a href="/?p=member&level=4" target="_self" class="gnb_1da <?php if($p == "member") { if($level == "4") { echo "on"; } } ?>">영업점 관리</a>
				</li>
				<?php } ?>
				<?php if($member['mb_level'] >= 3) { ?>
				<li class="gnb_1dli">
					<a href="/?p=member&level=3" target="_self" class="gnb_1da <?php if($p == "member") { if($level == "3") { echo "on"; } } ?>">가맹점 관리</a>
				</li>
				<?php } ?>
				<?php if($member['mb_level'] >= 8) { ?>
				<li class="gnb_1dli">
					<a href="/?p=member&level=1" target="_self" class="gnb_1da <?php if($p == "member") { if($level == "1") { echo "on"; } } ?>">삭제회원 관리</a>
				</li>
				<?php } ?>
			</ul>
		</div>
		<?php } ?>
		<?php /*
		<div class="gnb_side no_logo">
			<h2>MPAY</h2>
			<ul id="gnb_1dul">
				<li class="gnb_1dli">
					<a href="https://theme.sir.kr/gnuboard55/bbs/board.php?bo_table=banner" target="_self" class="gnb_1da">수기결제</a>
				</li>
				<li class="gnb_1dli">
					<a href="https://theme.sir.kr/gnuboard55/bbs/group.php?gr_id=community" target="_self" class="gnb_1da">수기결제 내역</a>
				</li>
			</ul>
		</div>
		*/ ?>
		<div class="gnb_side no_logo">
			<h2>BOARD</h2>
			<ul id="gnb_1dul">
				<li class="gnb_1dli">
					<a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=notice" target="_self" class="gnb_1da <?php if($bo_table == "notice") { echo "on"; } ?>">공지사항</a>
				</li>
				<li class="gnb_1dli">
					<a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=qa" target="_self" class="gnb_1da <?php if($bo_table == "qa") { echo "on"; } ?>">질문답변</a>
				</li>
			</ul>
		</div>
	</div>
</aside>
            
<script>
$(function () {
    //폰트 크기 조정 위치 지정
    var font_resize_class = get_cookie("ck_font_resize_add_class");
    if( font_resize_class == 'ts_up' ){
        $("#text_size button").removeClass("select");
        $("#size_def").addClass("select");
    } else if (font_resize_class == 'ts_up2') {
        $("#text_size button").removeClass("select");
        $("#size_up").addClass("select");
    }

    $(".hd_opener").on("click", function() {
        var $this = $(this);
        var $hd_layer = $this.next(".hd_div");

        if($hd_layer.is(":visible")) {
            $hd_layer.hide();
            $this.find("span").text("열기");
        } else {
            var $hd_layer2 = $(".hd_div:visible");
            $hd_layer2.prev(".hd_opener").find("span").text("열기");
            $hd_layer2.hide();

            $hd_layer.show();
            $this.find("span").text("닫기");
        }
    });

    $("#container").on("click", function() {
        $(".hd_div").hide();

    });

    $(".btn_gnb_op").click(function(){
        $(this).toggleClass("btn_gnb_cl").next(".gnb_2dul").slideToggle(300);
        
    });

    $(".hd_closer").on("click", function() {
        var idx = $(".hd_closer").index($(this));
        $(".hd_div:visible").hide();
        $(".hd_opener:eq("+idx+")").find("span").text("열기");
    });
});
</script>

<!-- 컨텐츠 시작 { -->
<div id="content-wrapper">
	<div id="wrapper">
		<!-- container 시작 { -->
		<div id="container">
			<div class="conle">
			    <?php if (!defined("_INDEX_") && !(defined("_H2_TITLE_") && _H2_TITLE_ === true)) {?>
			    	<h2 id="container_title" class="top" title="<?php echo get_text($g5['title']); ?>"><?php echo get_head_title($g5['title']); ?></h2>
			    <?php } ?>