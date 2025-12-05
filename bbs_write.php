<form name="fwrite" id="fwrite" action="./?p=bbs&v=update" onsubmit="return fwrite_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
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


<?php
	if($id) {
		$sql = " select * from {$write_table} where wr_id = '{$id}'  ";
		$row = sql_fetch($sql);
	//	echo $sql;
	}
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
			<th scope="row">제목</th>
			<td><input type="text" name="wr_subject" value="<?php echo $row['wr_subject']; ?>" id="wr_subject" required class="frm_input full_input required" placeholder="제목"></td>
		</tr>
		<tr>
			<td colspan="2"><textarea id="wr_content" name="wr_content" class="" maxlength="65536" style="width:100%;height:300px"><?php echo $row['wr_content']; ?></textarea></td>
		</tr>
		<tr>
			<th scope="row">파일</th>
			<td><input type="file" name="bf_file[]" id="bf_file_1" title="파일첨부 1 : 용량 1,048,576 바이트 이하만 업로드 가능" class="frm_file "></td>
		</tr>
	</tbody>
</table>

<div style="padding:10px 0;">
	<?php if($id) {?>
	<a href="./?p=bbs&t=<?php echo $t; ?>&v=view&id=<?php echo $id; ?>&page=<?php echo $page; ?>" class="btn_cancel">취소</a>
	<?php } else { ?>
	<a href="./?p=bbs&t=<?php echo $t; ?>&page=<?php echo $page; ?>" class="btn_cancel">취소</a>
	<?php } ?>
	<button type="submit" id="btn_submit" class="btn_submit" accesskey="s">작성완료</button>
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