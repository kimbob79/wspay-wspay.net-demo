<?php
	include_once('./_common.php');
	$row =  sql_fetch(" select * from g5_payment where pay_id = '{$pay_id}'");
	if(!$row) die('잘못된 경로입니다');
	$mb =  get_member($row['mb_6']);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
<meta name="format-detection" content="telephone=no">
<title>신용카드 취소요청</title>
<link rel="stylesheet" href="./css/table.css?ver=2106181">
<link rel="stylesheet" href="./css/btn.css?ver=2106181">
<script src="<?php echo G5_JS_URL; ?>/jquery-1.12.4.min.js?ver=210618"></script>
<style>


@media print {
  .button_area {display:none !important;}
  @page { size: b5; margin: 0; }
  body { margin: 0.5cm; }
}

</style>

</head>
<body>


<form name="fmember" id="fmember" action="./?p=cancel_payment_update" onsubmit="return fregisterform_submit(this);" method="post" enctype="multipart/form-data">
	<input type="hidden" name="ca_type" value="insert">
	<input type="hidden" name="mb_id" value="<?php echo $member['mb_id'] ?>">
	<input type="hidden" name="pay_id" value="<?php echo $row['pay_id']; ?>">

	<table class="table_view">
		<tbody>
			<tr>
				<th style="width:100px"><label for="mb_id">가맹점명</label></th>
				<td><?php echo $row['mb_6_name']; ?></td>
			</tr>
			<tr>
				<th style="width:100px"><label for="mb_id">결제금액</label></th>
				<td><?php echo number_format($row['pay']); ?></td>
			</tr>
			<tr>
				<th style="width:100px"><label for="mb_id">결제일시</label></th>
				<td><?php echo $row['pay_datetime']; ?></td>
			</tr>
			<tr>
				<th style="width:100px"><label for="mb_id">환불액</label></th>
				<td><?php echo number_format($row['mb_6_pay']); ?> / <?php echo $row['mb_6_fee']; ?>%</td>
			</tr>
			<tr>
				<th style="width:100px"><label for="mb_id">환불계좌</label></th>
				<td>기업은행 원성페이먼츠<br>489-056645-04-014</td>
			</tr>
			<tr>
				<th style="width:100px"><label for="mb_id">사유/메모</label></th>
				<td><textarea name="ca_memo" style="width:100%; height:50px;"></textarea></td>
			</tr>
		</tbody>
	</table>

	<div style="padding:10px 0; text-align:center;">
		<input type="submit" value="취소요청" class="btn_b btn_b02" accesskey='s'>
		<button type="button" onclick="window.close();" class="btn_b btn_b06">창닫기</button>
	</div>
</form>


</body>
</html>