<?php
	error_reporting(E_ALL);
	ini_set("display_errors", 0);
	include("./_common.php");
	$no_menu = "yes";
	
	$url_id = $id;
	$url_pay = sql_fetch(" select * from pay_payment_sms where pm_id = '{$url_id}' ");
	$url_code = $url_pay['urlcode'];
	$pay_product = $url_pay['url_gname'];
	$pay_price = $url_pay['url_price'];
	$mb_id = $url_pay['mb_id'];
	$pg_id = $url_pay['pg_id'];
	$row_pg = sql_fetch(" select * from pay_member_pg where mb_id = '{$url_pay['mb_id']}' and pg_id = '$pg_id' ");
	
	if($mb_id != $member['mb_id']) {
		alert("잘못된 접근입니다.아이디 불일치");
		exit;
	}

	// 알리고 클래스 로드
	include_once('./sms_class.php');

	
	include_once("./_head.php");
?>

<style>
	table.table_pg {
		width:100%;
		border-collapse: collapse;
		text-align: left;
		line-height: 1.5;
		border: 1px solid #ddd;
		border-top: 1px solid #111;

	}
	table.table_pg th {
		width: 20%;
		padding: 10px;
		font-weight: normal;
		color: #369;
		border-bottom: 1px solid #ddd;
		background: #f3f6f7;
		font-size:11px;
	}
	table.table_pg td {
		width: 80%;
		padding: 10px;
		border-bottom: 1px solid #ddd;
	}
	#bbs .bbs-cont {padding: 1em 0 2em}
	.bbs-cont .inner {padding:0 10px}

	.all-check {
		margin: 2em 0 0.5em;
	}
	.all-check input[type=checkbox]:checked+label {
		border: 1px solid #3651f6;
	}
	.all-check input[type=checkbox]:checked+label span i {
		background: url('../img/checkbox_all.png') 0 -16px no-repeat;
		background-size: 100%;
	}
	.all-check input[type=checkbox] {
		display: none;
	}
	input:read-only {
		color: #999;
		background-color: #fff;
	}
	.all-check label {
		display: block;
		width: 100%;
		padding: 0.9em;
		text-align: center;
	}
	.all-check label, .select-wrapper select {
		position: relative;
		background:#fff;
		border: 1px solid #e5e5e5;
	}
	.all-check input+label span {
		position: relative;
		display: inline-block;
	}
	.all-check input[type=checkbox]+label span i {
		display: inline-block;
		margin-right: 10px;
		vertical-align: -3px;
		width: 18px;
		height: 16px;
		background: url('../img/checkbox_all.png') 0 0 no-repeat;
		background-size: 100%;
	}
	.agreement-wrap ul li {
		padding: 0.3em 0;
		font-weight: 400;
		font-size: 1.3rem;
	}
	.input-chk {
		position: relative;
		display: inline-block;
		font-weight: 400;
		line-height: 24px;
		color: #666;
	}
	.input-chk input[type=checkbox] {
		display: none;
	}
	.input-chk input+span {
		display: inline-block;
		margin-left: 34px;
	}
	.input-chk input[type=checkbox]:checked+span:before {
		background: url(../img/checkbox.png) 0 -20px no-repeat;
		background-size: 100%;
	}
	.input-chk input[type=checkbox]+span:before {
		position: absolute;
		left: 0;
		top: 5px;
		content: " ";
		display: inline-block;
		width: 20px;
		height: 20px;
		background: url('../img/checkbox.png') 0 0 no-repeat;
		background-size: 100%;
	}
	.agreement-wrap ul li a {
		float: right;
		color: #999;
		text-decoration: underline;
		line-height: 24px;
	}
</style>


<style>
.wrap-loading{ /*화면 전체를 어둡게 합니다.*/
	position: fixed;
	left:0;
	right:0;
	top:0;
	bottom:0;
	background: #fff;    /* ie */
	z-index: 9;
}
.wrap-loading div{ /*로딩 이미지*/
	position: fixed;
	top:50%;
	left:50%;
	margin-left: -100px;
	margin-top: -205px;
}
.display-none{ /*감추기*/
	display:none;
}
</style>
<div class="wrap-loading display-none">
	<div><img src="./img/loading_payment.gif" width="200" height="411"></div>
</div>
<section class="container" id="bbs">
	<section class="contents contents-bbs">
		<input type="hidden" id="phone" name="phone" value="<?php echo $url_pay['url_gtel']; ?>">
		<div class="top">
			<div class="inner">
				<table class="table_pg">
					<tr>
						<th>구매자</th>
						<td><?php echo $pay_product; ?></td>
					</tr>
					<tr>
						<th>연락처</th>
						<td><?php echo $url_pay['url_gtel']; ?></td>
					</tr>
					<tr>
						<th>발송내용</th>
						<td>
<textarea id="message" name="message" style="width:100%; height:130px;" readonly>
판매자 : <?php echo $url_pay['url_pname']; ?>

연락처 : <?php echo $url_pay['url_ptel']; ?>

상품명 : <?php echo $pay_product; ?>

결제금액 : <?php echo number_format($pay_price); ?>원
결제링크 : http://simplepay.kr/url_pay.php?id=<?php echo $url_id; ?>
</textarea>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<div class="bbs-cont">
			<div class="inner">
				<button type="submit" id="send-btn" class="btn btn-black btn-cell" style="width:100%;">메시지 전송</button>
			</div>
		</div>
	</section>
</section>


<script>


	function sendAligoSMS(receiverPhone, msgContent, callback) {
		$.ajax({
			url: './sms_send.php',
			type: 'POST',
			dataType: 'json',
			data: {
				phone: receiverPhone,
				message: msgContent
			},
			success: function (res) {
				if (typeof callback === 'function') callback(res);
			},
			error: function (xhr, status, error) {
				console.error('SMS 전송 실패:', error);
				if (typeof callback === 'function') callback({ success: false, error });
			}
		});
	}



	$('#send-btn').on('click', function() {
		const phone = $('#phone').val();
		const message = $('#message').val();

		sendAligoSMS(phone, message, function(result) {
			if (result.success) {
				alert('문자 전송 성공');
				window.close();
			} else {
				alert('실패: ' + result.message);
			}
		});
	});


</script>