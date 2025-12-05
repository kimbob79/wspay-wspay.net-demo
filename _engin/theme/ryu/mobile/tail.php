<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

		</div>
		</div>
		<!-- } container 끝 -->
	
		

		<footer id="footer">
			<div id="ft_copy">
				<?php /*
				<div id="ft_company">
					<a href="https://theme.sir.kr/gnuboard55/bbs/content.php?co_id=company">회사소개</a>
					<a href="https://theme.sir.kr/gnuboard55/bbs/content.php?co_id=provision">서비스이용약관</a>
				</div>
				*/ ?>
				Copyright &copy; WANNA. 판매자센터<br>
			</div>
		</footer>

	</div>
	<!-- } wrapper 끝 -->
</div>
<!-- } 컨텐츠 끝 -->
<script>

$(".m_board_scroll").on("scroll",function(){
	$(this).find(".txt_ex_scroll").remove();
});
$(".m_board_scroll .txt_ex_scroll").on("click",function(){
	$(this).remove();
});
</script>
<?php
include_once(G5_THEME_PATH."/tail.sub.php");
?>