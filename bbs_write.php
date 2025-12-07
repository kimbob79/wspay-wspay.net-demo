<?php
	if($id) {
		$sql = " select * from {$write_table} where wr_id = '{$id}'  ";
		$row = sql_fetch($sql);
	//	echo $sql;
	}
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
.notice-write-form {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 16px;
	margin-bottom: 10px;
}
.notice-write-form .form-group {
	margin-bottom: 12px;
}
.notice-write-form .form-group:last-child {
	margin-bottom: 0;
}
.notice-write-form .form-group label {
	display: block;
	font-weight: 600;
	color: #333;
	font-size: 13px;
	margin-bottom: 6px;
}
.notice-write-form .form-group input[type="text"] {
	width: 100%;
	padding: 10px 12px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-size: 14px;
	background: #f8f9fa;
	box-sizing: border-box;
}
.notice-write-form .form-group input[type="text"]:focus {
	outline: none;
	border-color: #00838f;
	background: #fff;
}
.notice-write-form .form-group textarea {
	width: 100%;
	padding: 12px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-size: 14px;
	background: #f8f9fa;
	resize: vertical;
	box-sizing: border-box;
	line-height: 1.6;
}
.notice-write-form .form-group textarea:focus {
	outline: none;
	border-color: #00838f;
	background: #fff;
}
.notice-write-form .form-group input[type="file"] {
	padding: 8px 0;
	font-size: 13px;
}
.notice-btn-group {
	display: flex;
	gap: 8px;
	padding: 10px 0;
}
.notice-btn-group a,
.notice-btn-group button {
	padding: 8px 16px;
	border-radius: 4px;
	font-size: 13px;
	text-decoration: none;
	transition: all 0.2s;
	border: none;
	cursor: pointer;
}
.notice-btn-group .btn-cancel {
	background: #9e9e9e;
	color: #fff;
}
.notice-btn-group .btn-cancel:hover {
	background: #757575;
}
.notice-btn-group .btn-submit {
	background: #00838f;
	color: #fff;
}
.notice-btn-group .btn-submit:hover {
	background: #006064;
}
@media (max-width: 768px) {
	.notice-write-form {
		padding: 12px;
	}
}
</style>

<div class="notice-header">
	<div class="notice-header-top">
		<div class="notice-title">
			<i class="fa fa-bullhorn"></i>
			<?php echo $title2; ?> - <?php echo $id ? '수정' : '작성'; ?>
		</div>
	</div>
</div>

<form name="fwrite" id="fwrite" action="./?p=bbs&t=<?php echo $t; ?>&v=update" onsubmit="return fwrite_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="bo_table" value="<?php echo $t; ?>">
<input type="hidden" name="wr_id" value="<?php echo $id; ?>">
<input type="hidden" name="sca" value="<?php echo $sca ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="spt" value="<?php echo $spt ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="mb_id" value="<?php echo $member['mb_id']; ?>">

<div class="notice-write-form">
	<div class="form-group">
		<label for="wr_subject"><i class="fa fa-pencil"></i> 제목</label>
		<input type="text" name="wr_subject" value="<?php echo $row['wr_subject']; ?>" id="wr_subject" required placeholder="제목을 입력하세요">
	</div>
	<div class="form-group">
		<label for="wr_content"><i class="fa fa-align-left"></i> 내용</label>
		<textarea id="wr_content" name="wr_content" maxlength="65536" style="height:300px" placeholder="내용을 입력하세요"><?php echo $row['wr_content']; ?></textarea>
	</div>
	<div class="form-group">
		<label><i class="fa fa-paperclip"></i> 파일첨부</label>
		<input type="file" name="bf_file[]" id="bf_file_1" title="파일첨부 : 용량 1MB 이하만 업로드 가능">
	</div>
</div>

<div class="notice-btn-group">
	<?php if($id) {?>
	<a href="./?p=bbs&t=<?php echo $t; ?>&v=view&id=<?php echo $id; ?>&page=<?php echo $page; ?>" class="btn-cancel"><i class="fa fa-times"></i> 취소</a>
	<?php } else { ?>
	<a href="./?p=bbs&t=<?php echo $t; ?>&page=<?php echo $page; ?>" class="btn-cancel"><i class="fa fa-times"></i> 취소</a>
	<?php } ?>
	<button type="submit" id="btn_submit" class="btn-submit" accesskey="s"><i class="fa fa-check"></i> 작성완료</button>
</div>
</form>

<script>
function html_auto_br(obj)
{
	if (obj.checked) {
		result = confirm("자동 줄바꿈을 하시겠습니까?\n\n자동 줄바꿈은 게시물 내용중 줄바뀐 곳을<br>태그로 변환하는 기능입니다.");
		if (result) {
			obj.value = "html2";
		} else {
			obj.value = "html1";
		}

		$("label[for='html']").addClass('click_on');
	} else {
		obj.value = "";
		$("label[for='html']").removeClass('click_on');
	}
}

function fwrite_submit(f)
{
	var wr_content_editor = document.getElementById('wr_content');
if (!wr_content_editor.value) { alert("내용을 입력해 주십시오."); wr_content_editor.focus(); return false; }

	var subject = "";
	var content = "";
	$.ajax({
		url: g5_bbs_url+"/ajax.filter.php",
		type: "POST",
		data: {
			"subject": f.wr_subject.value,
			"content": f.wr_content.value
		},
		dataType: "json",
		async: false,
		cache: false,
		success: function(data, textStatus) {
			subject = data.subject;
			content = data.content;
		}
	});

	if (subject) {
		alert("제목에 금지단어('"+subject+"')가 포함되어있습니다");
		f.wr_subject.focus();
		return false;
	}

	if (content) {
		alert("내용에 금지단어('"+content+"')가 포함되어있습니다");
		if (typeof(ed_wr_content) != "undefined")
			ed_wr_content.returnFalse();
		else
			f.wr_content.focus();
		return false;
	}

	if (document.getElementById("char_count")) {
		if (char_min > 0 || char_max > 0) {
			var cnt = parseInt(check_byte("wr_content", "char_count"));
			if (char_min > 0 && char_min > cnt) {
				alert("내용은 "+char_min+"글자 이상 쓰셔야 합니다.");
				return false;
			}
			else if (char_max > 0 && char_max < cnt) {
				alert("내용은 "+char_max+"글자 이하로 쓰셔야 합니다.");
				return false;
			}
		}
	}

	if (!chk_captcha()) return false;

	document.getElementById("btn_submit").disabled = "disabled";

	return true;
}
</script>