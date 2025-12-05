<?php
	include_once('./_common.php');
	$row = sql_fetch(" select * from g5_payment where pay_id = ".$pay_id);
?>
<!doctype html>
<html lang="ko" id="html_wrap" style="min-width:100px;">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=0,maximum-scale=10,user-scalable=yes">
	<meta name="HandheldFriendly" content="true">
	<meta name="format-detection" content="telephone=no">
	<title>레드페이 관리자센터</title>
	<link rel="stylesheet" href="./css/mobile.css?ver=<?php echo time(); ?>">
	<link rel="stylesheet" href="./css/btn.css?ver=<?php echo time(); ?>">
	<script src="/_engin/js/jquery-1.12.4.min.js?ver=2106185"></script>
</head>
<body  style="min-width:100px;">

<div style=" width:100%; padding:10px;">
<textarea style="width:100%; height:120px; border:0; font-size:1.4em" readonly id="copy_content">
가맹점명 : <?php echo $row['mb_6_name']; ?>

결제코드 : <?php echo $row['dv_tid']; ?>

결제금액 : <?php echo number_format($row['pay']); ?>원
결제일시 : <?php echo $row['pay_datetime']; ?>

승인번호 : <?php echo $row['pay_num']; ?>
</textarea>
</div>

<div id="copy_ok" style="display:none; text-align:center;">복사가 완료되었습니다.</div>

<div style="width:100%; text-align:center;padding:10px;">
	<button id="copy_button"  class="btn_b btn_b02">복사하기 / 창닫기</button>
</div>

<script>
$("#copy_button").click(function() {
var content = document.getElementById('copy_content');
content.select();
document.execCommand('copy');
$("#copy_ok").show();
window.close();
});
</script>

</body>
</html>