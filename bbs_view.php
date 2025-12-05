<?php
	$sql = " select * from {$write_table} where wr_id = '{$id}'  ";
	$row = sql_fetch($sql);
//	echo $sql;
?>
<div class="index_menu">
	<ul class="shortcut">
		<li class="sc_current"><a><?php echo $title2; ?></a></li>
		<li class="sc_visit">
			<aside id="visit">
			</aside>
		</li>
	</ul>
</div>

<table class="table_view">
	<tbody>
		<tr>
			<th scope="row">작성자</th>
			<td><?php echo $row['wr_name']; ?></td>
		</tr>
		<tr>
			<th scope="row">작성일</th>
			<td><?php echo $row['wr_datetime']; ?></td>
		</tr>
		<tr>
			<th scope="row">읽음</th>
			<td><?php echo $row['wr_hit']; ?></td>
		</tr>
		<tr>
			<th scope="row">제목</th>
			<td><strong class="color_black"><?php echo $row['wr_subject']; ?></strong></td>
		</tr>
		<tr>
			<td colspan="2" style="min-height:100px"><?php echo nl2br($row['wr_content']); ?></td>
		</tr>
		<?php /*
		<tr>
			<th scope="row">파일</th>
			<td>15시간</td>
		</tr>
		*/ ?>
	</tbody>
</table>

<?php
// 코멘트 입출력
include_once('./bbs_view_comment.php');
?>

<div style="padding:10px 0;">
	<a href="./?p=bbs&t=<?php echo $t; ?>&page=1" class="btn_cancel">목록</a>
	<a href="./?p=bbs&t=<?php echo $t; ?>&v=write&id=<?php echo $id; ?>&page=<?php echo $page; ?>" class="btn_cancel">수정</a>
	<a href="./?p=bbs&t=<?php echo $t; ?>&v=delete&id=<?php echo $id; ?>" class="btn_cancel">삭제</a>
</div>