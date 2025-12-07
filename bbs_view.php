<?php
	$sql = " select * from {$write_table} where wr_id = '{$id}'  ";
	$row = sql_fetch($sql);
//	echo $sql;
?>

<style>
.notice-header {
	background: linear-gradient(135deg, #00838f 0%, #00acc1 100%);
	border-radius: 8px;
	padding: 12px 16px;
	margin-bottom: 10px;
	box-shadow: 0 2px 8px rgba(0, 131, 143, 0.2);
}
.notice-header-top {
	display: flex;
	align-items: center;
	justify-content: space-between;
	flex-wrap: wrap;
	gap: 10px;
}
.notice-title {
	display: flex;
	align-items: center;
	gap: 10px;
	color: #fff;
	font-size: 18px;
	font-weight: 600;
}
.notice-title i {
	font-size: 20px;
}
.notice-view-info {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 12px 16px;
	margin-bottom: 10px;
	display: flex;
	gap: 20px;
	flex-wrap: wrap;
	font-size: 13px;
	color: #666;
}
.notice-view-info span {
	display: flex;
	align-items: center;
	gap: 5px;
}
.notice-view-info span strong {
	color: #333;
}
.notice-view-content {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 16px;
	margin-bottom: 10px;
	min-height: 150px;
	line-height: 1.7;
}
.notice-view-subject {
	font-size: 16px;
	font-weight: 600;
	color: #333;
	padding-bottom: 12px;
	margin-bottom: 12px;
	border-bottom: 1px solid #eee;
}
.notice-btn-group {
	display: flex;
	gap: 8px;
	padding: 10px 0;
}
.notice-btn-group a {
	padding: 8px 16px;
	border-radius: 4px;
	font-size: 13px;
	text-decoration: none;
	transition: all 0.2s;
}
.notice-btn-group .btn-list {
	background: #00838f;
	color: #fff;
}
.notice-btn-group .btn-list:hover {
	background: #006064;
}
.notice-btn-group .btn-edit {
	background: #ff9800;
	color: #fff;
}
.notice-btn-group .btn-edit:hover {
	background: #f57c00;
}
.notice-btn-group .btn-delete {
	background: #f44336;
	color: #fff;
}
.notice-btn-group .btn-delete:hover {
	background: #d32f2f;
}
.notice-file-list {
	background: #f8f9fa;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 12px 16px;
	margin-bottom: 10px;
}
.notice-file-list .file-title {
	font-weight: 600;
	color: #333;
	font-size: 14px;
	margin-bottom: 8px;
	padding-bottom: 8px;
	border-bottom: 1px solid #e0e0e0;
}
.notice-file-list ul {
	list-style: none;
	margin: 0;
	padding: 0;
}
.notice-file-list ul li {
	padding: 6px 0;
}
.notice-file-list ul li a {
	color: #00838f;
	text-decoration: none;
	font-size: 13px;
	display: inline-flex;
	align-items: center;
	gap: 6px;
}
.notice-file-list ul li a:hover {
	color: #006064;
	text-decoration: underline;
}
.notice-file-list ul li a .file-size {
	color: #999;
	font-size: 12px;
}
/* 본문 이미지 스타일 */
.notice-view-content .content-image-item {
	margin: 15px 0;
	text-align: center;
}
.notice-view-content .content-image-item img {
	max-width: 100%;
	width: auto;
	height: auto;
	max-height: 500px;
	border-radius: 8px;
	border: 1px solid #e0e0e0;
	cursor: pointer;
	transition: box-shadow 0.2s;
	display: inline-block;
}
.notice-view-content .content-image-item img:hover {
	box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* 이미지 모달 스타일 */
.image-modal {
	display: none;
	position: fixed;
	z-index: 9999;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;
	background-color: rgba(0,0,0,0.9);
	justify-content: center;
	align-items: center;
	flex-direction: column;
}
.image-modal .modal-close {
	position: absolute;
	top: 20px;
	right: 30px;
	color: #fff;
	font-size: 35px;
	font-weight: bold;
	cursor: pointer;
	z-index: 10000;
	transition: color 0.2s;
}
.image-modal .modal-close:hover {
	color: #00acc1;
}
.image-modal .modal-content {
	display: flex;
	flex-direction: column;
	align-items: center;
	max-width: 90%;
	max-height: 90%;
}
.image-modal .modal-content img {
	max-width: 100%;
	max-height: 80vh;
	border-radius: 4px;
	box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}
.image-modal .modal-caption {
	color: #fff;
	text-align: center;
	padding: 10px 20px;
	font-size: 14px;
	margin-top: 10px;
}

@media (max-width: 768px) {
	.notice-view-info {
		flex-direction: column;
		gap: 8px;
	}
	.notice-view-content .content-image-item img {
		max-height: 300px;
	}
	.image-modal .modal-close {
		top: 10px;
		right: 15px;
		font-size: 28px;
	}
	.image-modal .modal-content img {
		max-height: 70vh;
	}
}
</style>

<div class="notice-header">
	<div class="notice-header-top">
		<div class="notice-title">
			<i class="fa fa-bullhorn"></i>
			<?php echo $title2; ?>
		</div>
	</div>
</div>

<div class="notice-view-info">
	<span><i class="fa fa-calendar"></i> <strong>작성일:</strong> <?php echo substr($row['wr_datetime'], 0, 16); ?></span>
</div>

<?php
// 첨부파일 가져오기
$file_result = sql_query(" select * from {$g5['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$id}' order by bf_no ");
$file_count = sql_num_rows($file_result);
$image_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp');
$all_files = array();

while($file = sql_fetch_array($file_result)) {
	$ext = strtolower(pathinfo($file['bf_source'], PATHINFO_EXTENSION));
	$file['is_image'] = in_array($ext, $image_extensions);
	$file['img_url'] = './bbs_image.php?bo_table='.$bo_table.'&wr_id='.$id.'&no='.$file['bf_no'];
	$all_files[] = $file;
}
?>

<div class="notice-view-content">
	<div class="notice-view-subject"><?php echo $row['wr_subject']; ?></div>
	<?php echo nl2br($row['wr_content']); ?>

	<?php foreach($all_files as $file) {
		if($file['is_image']) {
	?>
	<div class="content-image-item">
		<img src="<?php echo $file['img_url']; ?>" alt="<?php echo $file['bf_source']; ?>" onclick="openImageModal('<?php echo $file['img_url']; ?>', '<?php echo addslashes($file['bf_source']); ?>')">
	</div>
	<?php }
	} ?>
</div>

<?php if(count($all_files) > 0) { ?>
<div class="notice-file-list">
	<div class="file-title"><i class="fa fa-paperclip"></i> 첨부파일 (<?php echo count($all_files); ?>)</div>
	<ul>
	<?php foreach($all_files as $file) { ?>
		<li>
			<a href="./bbs_download.php?bo_table=<?php echo $bo_table; ?>&wr_id=<?php echo $id; ?>&no=<?php echo $file['bf_no']; ?>">
				<i class="fa fa-download"></i> <?php echo $file['bf_source']; ?>
				<span class="file-size">(<?php echo number_format($file['bf_filesize']); ?> bytes)</span>
			</a>
		</li>
	<?php } ?>
	</ul>
</div>
<?php } ?>

<!-- 이미지 모달 -->
<div id="imageModal" class="image-modal" onclick="closeImageModal()">
	<span class="modal-close">&times;</span>
	<div class="modal-content">
		<img id="modalImage" src="" alt="">
		<div id="modalCaption" class="modal-caption"></div>
	</div>
</div>

<script>
function openImageModal(src, caption) {
	var modal = document.getElementById('imageModal');
	var modalImg = document.getElementById('modalImage');
	var modalCaption = document.getElementById('modalCaption');

	modal.style.display = 'flex';
	modalImg.src = src;
	modalCaption.textContent = caption;
	document.body.style.overflow = 'hidden';
}

function closeImageModal() {
	var modal = document.getElementById('imageModal');
	modal.style.display = 'none';
	document.body.style.overflow = 'auto';
}

document.addEventListener('keydown', function(e) {
	if(e.key === 'Escape') {
		closeImageModal();
	}
});
</script>

<?php
/* 코멘트 입출력 - 주석처리
include_once('./bbs_view_comment.php');
*/
?>

<div class="notice-btn-group">
	<a href="./?p=bbs&t=<?php echo $t; ?>&page=<?php echo $page; ?>" class="btn-list"><i class="fa fa-list"></i> 목록</a>
	<?php if($is_admin) { ?>
	<a href="./?p=bbs&t=<?php echo $t; ?>&v=write&id=<?php echo $id; ?>&page=<?php echo $page; ?>" class="btn-edit"><i class="fa fa-pencil"></i> 수정</a>
	<a href="./?p=bbs&t=<?php echo $t; ?>&v=delete&id=<?php echo $id; ?>" class="btn-delete" onclick="return confirm('정말 삭제하시겠습니까?');"><i class="fa fa-trash"></i> 삭제</a>
	<?php } ?>
</div>