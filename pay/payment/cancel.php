<?php
	include_once('./_common.php');

	include_once(G5_PATH.'/head.sub.php');

	if($member['mb_adult'] == 1) {
		alert_close("취소불가 회원입니다.");
	}

	$row = sql_fetch(" select * from pay_payment_passgo where pm_id = '{$id}' ");

	if(date("Ymd") != substr($row['creates'],0,8)) {
		alert_close("승인 당일에만 취소 가능합니다.");
		exit;
	}
?>

<style>
.mbskin {position:relative;margin:20px auto;text-align:center; max-width:440px; width:100%}
.mbskin .frm_input {width:100%}
.mbskin p {padding-bottom:20px;border-bottom:1px solid #c8c8c8;text-align:left;color:#3c763d}

/* 버튼 */
.mbskin a.btn01 {}
.mbskin a.btn01:focus, .mbskin a.btn01:hover {}
.mbskin a.btn02 {}
.mbskin a.btn02:focus, .mbskin .btn02:hover {}
.mbskin .btn_confirm {} /* 서식단계 진행 */
.mbskin .btn_submit {min-width:100px;height:40px;border-radius:3px;line-height:30px;font-weight:bold;font-size:1.25em;border-radius:3px;margin-bottom: 25px;}
.mbskin .btn_submit:after {display:block;visibility:hidden;clear:both;content:""}

/* 로그인 */
#mb_login {background:#fff;text-align:center;margin:30px auto;max-width:340px;    padding: 20px;}
#mb_login h1 {font-size:1.75em;padding:20px 0;margin-bottom:20px;text-align:center;border-bottom:1px solid #f1f3f6}
#mb_login .mbskin_inner {width:300px;display:inline-block}

#mb_login .login_btn_inner {}
#mb_login #login_fs .frm_input {background:#f8f9fb;border:1px solid #d0d4df;margin-bottom:10px;border-radius:3px}
#mb_login #login_info {margin:15px 0;text-align:right}
#mb_login #login_info:after {display:block;visibility:hidden;clear:both;content:""}
#mb_login #login_info h2 {position:absolute;font-size:0;line-height:0;overflow:hidden}
#mb_login #login_info span {display:inline-block}
#mb_login #login_info a {display:inline-block;float:left;margin-left:5px;padding:8px 10px;border:1px solid #d5d9dd;color:#555}
#mb_login #login_info a:hover {color:#694ecc}


</style>

<?php /*

// 고객정보
$payerName = $_POST['payerName']; // 고객명
$payerEmail = $_POST['payerEmail']; // 고객 이메일
$payerTel = $_POST['payerTel']; // 고객 휴대전화번호

// 카드정보
$number = $_POST['number']; // 카드번호
$expiry = $_POST['expiry']; // YYMM 2103 21년 03월
$installment = $_POST['installment']; // 할구개월수 0 = 일시불, 12 = 12개월

// 상품정보
$pd_name = $_POST['pd_name']; // 상품명
$pd_price = $_POST['pd_price']; // 금액

$mb_id = $member['mb_id']; // 터미널ID]

*/
?>

<style>
	body{ -ms-overflow-style: none; } ::-webkit-scrollbar { display: none; }
</style>

<div id="mb_login" class="mbskin">
	<h1>승인취소</h1>
	<div class="mbskin_inner">
		<fieldset id="login_fs">
			<form name="payment" id="payment" action="./cancel_update.php" method="post" onSubmit="this.submit.disabled=true">
				<input type="hidden" name="mb_id" value="<?php echo $member['mb_id']; ?>">
				<input type="hidden" name="pm_id" value="<?php echo $row['pm_id']; ?>">
				<input type="hidden" name="trxId" value="<?php echo $row['trxId']; ?>">
				<input type="hidden" name="trackId" value="<?php echo $row['trackId']; ?>">
				<input type="hidden" name="keydata" value="<?php echo $row['keydata']; ?>">
				<input type="hidden" name="payments" value="<?php echo $row['payments']; ?>">
				<div class="login_btn_inner">
					<div style="text-align:left; margin-bottom:5px;">주문자명</div>
					<label for="login_id" class="sound_only">주문자명<strong class="sound_only"> 필수</strong></label>
					<input type="text" name="payerName" id="payerName" placeholder="주문자명" required="" class="frm_input required" maxlength="20" value="<?php echo $row['payerName']; ?>" readonly>

					<div style="text-align:left; margin-bottom:5px;">결제금액</div>
					<label for="login_pw" class="sound_only">결제금액<strong class="sound_only"> 필수</strong></label>
					<input type="text" name="price" id="price" placeholder="결제금액" required="" class="frm_input required" maxlength="20" value="<?php echo $row['amount']; ?>" readonly>

					<div style="text-align:left; margin-bottom:5px;">결제일시</div>
					<label for="login_id" class="sound_only">결제일시<strong class="sound_only"> 필수</strong></label>
					<input type="text" name="datetime" id="datetime" placeholder="결제일시" required="" class="frm_input required" maxlength="20" value="<?php echo $row['datetime']; ?>" readonly>

					<div style="text-align:left; margin-bottom:5px;">승인번호</div>
					<label for="login_id" class="sound_only">승인번호<strong class="sound_only"> 필수</strong></label>
					<input type="text" name="authCd" id="authCd" placeholder="승인번호" required="" class="frm_input required" maxlength="20" value="<?php echo $row['authCd']; ?>" readonly>
				</div>
				<button type="submit" value="승인 취소하기" style="padding: 0 15px;border: 0;height: 30px;color: #fff;background: #ff4081;">승인 취소하기</button>
			</form>
		</fieldset>
	</div>
</div>

<script>

$(".btn_submit").click(function() {
	$(this).hide();
});
<?php/*
function payment_submit(act) {
	var f = document.payment;
	f.action = act;
	f.submit();
}
*/ ?>
</script>
<?php
	include_once(G5_PATH.'/tail.sub.php');