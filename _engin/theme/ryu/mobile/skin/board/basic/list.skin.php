<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 선택옵션으로 인해 셀합치기가 가변적으로 변함
$colspan = 2;

if ($is_checkbox) $colspan++;

foreach($list as $i => $v) {
    $list[$i]['wr_reply_style'] = "padding-left:". ($list[$i]['reply'] ? (strlen($list[$i]['wr_reply'])*10) : '0'). "px";
}

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>


		<form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
		<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
		<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
		<input type="hidden" name="stx" value="<?php echo $stx ?>">
		<input type="hidden" name="spt" value="<?php echo $spt ?>">
		<input type="hidden" name="sst" value="<?php echo $sst ?>">
		<input type="hidden" name="sod" value="<?php echo $sod ?>">
		<input type="hidden" name="page" value="<?php echo $page ?>">
		<input type="hidden" name="sw" value="">
		
		<?php if ($is_category) { ?>
	    <nav id="bo_cate">
	        <h2><?php echo ($board['bo_mobile_subject'] ? $board['bo_mobile_subject'] : $board['bo_subject']) ?> 카테고리</h2>
	        <ul id="bo_cate_ul">
	            <?php echo $category_option ?>
	        </ul>
	    </nav>
	    <?php } ?>

	    <div id="bo_li_top_op">
		    <div class="bo_list_total">
			    <span>전체 <?php echo number_format($total_count) ?>건</span>
			    <?php echo $page ?> 페이지
			</div>		
			<?php if ($rss_href || $write_href) { ?>
			<ul class="<?php echo isset($view) ? 'view_is_list btn_top' : 'btn_top2';?>">
				<?php if ($rss_href) { ?><li><a href="<?php echo $rss_href ?>" class="btn_b02">RSS</a></li><?php } ?>
			    <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b btn_b02">글쓰기</a></li><?php } ?>
				<?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" class="btn_b btn_b10">관리자</a></li><?php } ?>
				
			    <?php if ($is_admin == 'super' || $is_auth) {  ?>
				<li>
					<button type="button" class="btn_more btn_b btn_b01"><span class="sound_only">글쓰기 옵션 더보기</span><i class="fa fa-ellipsis-v" aria-hidden="true"></i></button>
					<?php if ($list_href || $is_checkbox || $write_href) { ?>
			        <ul class="btn_bo_adm">
			        	<?php if ($list_href) { ?>
				        <li><a href="<?php echo $list_href ?>" class="btn_b01 btn">목록</a></li>
				        <?php } ?>
			            <li><button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value">선택삭제</button></li>
			            <li><button type="submit" name="btn_submit" value="선택복사" onclick="document.pressed=this.value">선택복사</button></li>
			            <li><button type="submit" name="btn_submit" value="선택이동" onclick="document.pressed=this.value">선택이동</button></li>
			        </ul>
			        <?php } ?>
				    <script>
						$(document).ready(function(){
							$(".btn_more").click(function(){
								$(".btn_bo_adm").toggle();
							});
						});
					</script>
				</li>
			    <?php } ?>
			</ul>
			<?php } ?>
		</div>





<div class="m_board_scroll">
	<div class="m_table_wrap">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
				<tr>
					<?php if ($is_checkbox) { ?>
					<th style="width:5%;">
					<div class="list_chk all_chk">
						<input type="checkbox" id="chkall" onclick="if (this.checked) all_checked(true); else all_checked(false);">
						<label for="chkall"></label>
					</div>
					</th>
					<?php } ?>
					<th style="width:5%">번호</th>
					<?php if ($is_category && $list[$i]['ca_name']) { ?>
					<th style="width:10%">분류</th>
					<?php } ?>
					<th>제목</th>
					<th style="width:10%;">글쓴이</th>
					<th style="width:5%;"><?php echo subject_sort_link('wr_hit', $qstr2, 1) ?>조회</a></th>
					<th style="width:10%;"><?php echo subject_sort_link('wr_datetime', $qstr2, 1) ?>날짜</a></th>
				</tr>
			</thead>
			<tbody>
				<?php for ($i=0; $i<count($list); $i++) { ?>
				<tr>
					<?php if ($is_checkbox) { // 게시글별 체크박스?>
					<td>
					<span class="sel bo_chk li_chk">
						<label for="chk_wr_id_<?php echo $i ?>"><span class="sound_only"><?php echo $list[$i]['subject'] ?></span></label>
						<input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>"> 	
					</span>
					</td>
					<?php } ?>

					<td>
					<?php
					if ($list[$i]['is_notice']) // 공지사항
						echo '<strong class="notice_icon">공지</strong>';
					else if ($wr_id == $list[$i]['wr_id'])
						echo "<span class=\"bo_current\">열람중</span>";
					else
						echo $list[$i]['num'];
					?>
					</td>

					<?php if ($is_category && $list[$i]['ca_name']) { ?>
					<td>
					<a href="<?php echo $list[$i]['ca_name_href'] ?>" class="bo_cate_link"><?php echo $list[$i]['ca_name'] ?></a>
					</td>
					<?php } ?>

					<td class="td_name">
				
					<a href="<?php echo $list[$i]['href'] ?>" style="<?php echo $list[$i]['wr_reply_style']; ?>">
						<?php echo $list[$i]['icon_reply']; ?>
						<?php echo $list[$i]['subject'] ?>

						<?php
						if (isset($list[$i]['icon_hot'])) echo $list[$i]['icon_hot'];
						if (isset($list[$i]['icon_file'])) echo $list[$i]['icon_file'];
						if (isset($list[$i]['icon_link'])) echo $list[$i]['icon_link'];
						if (isset($list[$i]['icon_secret'])) echo $list[$i]['icon_secret'];
						?>
						<?php if ($list[$i]['icon_new']) { ?>
						<span class="new_icon">N<span class="sound_only">새글</span></span>
						<?php } ?>
						<?php if ($list[$i]['comment_cnt']) { ?>
						<span class="bo_cmt"><span class="sound_only">댓글</span><i class="far fa-comment-dots"></i> <?php echo $list[$i]['comment_cnt']; ?><span class="sound_only">개</span></span>
						<?php } ?>
					</a>
					</td>

					<td><?php echo $list[$i]['name'] ?></td>
					<td><?php echo $list[$i]['wr_hit'] ?></td>
					<td><?php echo $list[$i]['datetime2'] ?></td>
				</tr>
				<?php } ?>
				<?php if (count($list) == 0) { echo '<tr><td colspan="8" class="empty_table">게시물이 없습니다.</td></tr>'; } ?>
			</tbody>
		</table>



		</form>
	</div>
</div>
	<div id="bo_li_op">
		<!-- 게시판 검색 시작 { -->
		<fieldset id="bo_sch">
		    <legend>게시물 검색</legend>
		    <form name="fsearch" method="get">
		    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
		    <input type="hidden" name="sca" value="<?php echo $sca ?>">
		    <input type="hidden" name="sop" value="and">
		    <label for="sfl" class="sound_only">검색대상</label>
		    <select name="sfl" id="sfl">
		        <?php echo get_board_sfl_select_options($sfl); ?>
		    </select>
		    <input name="stx" value="<?php echo stripslashes($stx) ?>" placeholder="검색어를 입력하세요" required id="stx" class="sch_input" size="15" maxlength="20">
		    <button type="submit" value="검색" class="sch_btn"><i class="fa fa-search" aria-hidden="true"></i> <span class="sound_only">검색</span></button>
		    </form>
		</fieldset>
		
		<!-- } 게시판 검색 끝 -->
		<?php if ($rss_href || $write_href) { ?>		
		<ul class="<?php echo isset($view) ? 'view_is_list btn_top' : 'btn_top2';?>">
			<?php if ($rss_href) { ?><li><a href="<?php echo $rss_href ?>" class="btn_b02">RSS</a></li><?php } ?>
			<?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b btn_b02">글쓰기</a></li><?php } ?>
			<?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" class="btn_b btn_b10">관리자</a></li><?php } ?>
			
		    <?php if ($is_admin == 'super' || $is_auth) {  ?>
			<li>
				<button type="button" class="btn_more btn_b btn_b01"><span class="sound_only">글쓰기 옵션 더보기</span><i class="fa fa-ellipsis-v" aria-hidden="true"></i></button>
				<?php if ($list_href || $is_checkbox || $write_href) { ?>
		        <ul class="btn_bo_adm2">
		        	<?php if ($list_href) { ?>
			        <li><a href="<?php echo $list_href ?>" class="btn_b01 btn">목록</a></li>
			        <?php } ?>
		            <li><button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value">선택삭제</button></li>
		            <li><button type="submit" name="btn_submit" value="선택복사" onclick="document.pressed=this.value">선택복사</button></li>
		            <li><button type="submit" name="btn_submit" value="선택이동" onclick="document.pressed=this.value">선택이동</button></li>
		        </ul>
		        <?php } ?>
			    <script>
					$(document).ready(function(){
						$(".btn_more2").click(function(){
							$(".btn_bo_adm2").toggle();
						});
					});
				</script>
			</li>
		    <?php } ?>
		</ul>
		<?php } ?>		
	</div>

<?php if($is_checkbox) { ?>
<noscript>
<p>자바스크립트를 사용하지 않는 경우<br>별도의 확인 절차 없이 바로 선택삭제 처리하므로 주의하시기 바랍니다.</p>
</noscript>
<?php } ?>

<!-- 페이지 -->
<?php echo $write_pages; ?>

<?php if ($is_checkbox) { ?>
<script>
function all_checked(sw) {
    var f = document.fboardlist;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]")
            f.elements[i].checked = sw;
    }
}

function fboardlist_submit(f) {
    var chk_count = 0;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked)
            chk_count++;
    }

    if (!chk_count) {
        alert(document.pressed + "할 게시물을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택복사") {
        select_copy("copy");
        return;
    }

    if(document.pressed == "선택이동") {
        select_copy("move");
        return;
    }

    if(document.pressed == "선택삭제") {
        if (!confirm("선택한 게시물을 정말 삭제하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다\n\n답변글이 있는 게시글을 선택하신 경우\n답변글도 선택하셔야 게시글이 삭제됩니다."))
            return false;

        f.removeAttribute("target");
        f.action = g5_bbs_url+"/board_list_update.php";
    }

    return true;
}

// 선택한 게시물 복사 및 이동
function select_copy(sw) {
    var f = document.fboardlist;

    if (sw == 'copy')
        str = "복사";
    else
        str = "이동";

    var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");

    f.sw.value = sw;
    f.target = "move";
    f.action = g5_bbs_url+"/move.php";
    f.submit();
}

</script>
<?php } ?>
<!-- 게시판 목록 끝 -->
