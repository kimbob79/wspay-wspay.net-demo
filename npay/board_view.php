<?php
	$write_table = $g5['write_prefix'] . $bo_table;
	$sql_common = " from {$write_table} ";
	$row = sql_fetch(" select * from {$write_table} where wr_id = '{$wr_id}' ");
?>
<section class="container" id="bbs">
	<section class="contents contents-bbs">
		<div class="top">
			<div class="inner">
				<div class="heading-tit pull-left">
					<h2>고객센터</h2>
					<ul>
						<li>1800 - 3772</li>
						<li>평일 09:00 ~ 18:00</li>
					</ul>
				</div>
			</div>
		</div>
		<div class="bbs-cont  view-cont">

			<div class="inner">
				<div class="view-item">
					<div class="tit-area">
						<h3>
							<span class="admin-name">[<?php echo $row['ca_name']; ?>]</span> <?php echo $row['wr_subject']; ?></h3>
						<ul>
							<li><?php echo $row['wr_datetime']; ?></li>
							<li><?php echo $row['wr_name']; ?></li>
							<li><?php echo $row['wr_hit']; ?></li>
						</ul>
					</div>
					<div class="cont-area">
						<div class="view-area">
<p><?php echo nl2br($row['wr_content']); ?></p>
						</div>
					</div>
				<?php
					// 코멘트 입출력
					include_once('./board_view_comment.php');
				?>
				</div>
				<div class="btn-center-block">
					<div class="btn-group btn-horizon">
						<div class="btn-table">
							<a href="javascript:go_pw();" class="btn btn-black btn-cell">삭제</a>
							<a href="./?p=<?php echo $p; ?>&bo_table=<?php echo $bo_table; ?>&pm=write&wr_id=<?php echo $wr_id; ?>&w=u&page=<?php echo $page; ?>" class="btn btn-black-line btn-cell">수정</a>
							<a href="./?p=<?php echo $p; ?>&bo_table=<?php echo $bo_table; ?>&page=<?php echo $page; ?>" class="btn btn-gray-line btn-cell">목록</a>
						</div>
					</div>
				</div>
			</div>

		</div>



	</section>
</section>