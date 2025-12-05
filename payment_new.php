<?php
	include_once('./_common.php');
	if(!$is_admin) { alert("관리자만 접속 가능합니다."); }
	$row = sql_fetch(" select * from g5_payment_k1 where trxId = '".$trxid."' and trackId = '".$trackId."' ");
?>
<style>
.tbl_frm01 table {
    min-width: 100%;
}
</style>
<div class="tbl_frm01 tbl_wrap">
<form name="payment_new" action="./payment_new_update.php">
<input type="hidden" name="pay_id" value="<?php echo $pay_id; ?>" readonly class="frm_input">
<input type="hidden" name="mchtId" value="<?php echo $row['mchtId']; ?>" readonly class="frm_input">
<input type="hidden" name="trxId" value="<?php echo $row['trxId']; ?>" readonly class="frm_input">
<input type="hidden" name="tmnId" value="<?php echo $row['tmnId']; ?>" readonly class="frm_input">
<input type="hidden" name="trxDate" value="<?php echo $row['trxDate']; ?>" readonly class="frm_input">
<input type="hidden" name="trxType" value="<?php echo $row['trxType']; ?>" readonly class="frm_input">
<input type="hidden" name="trackId" value="<?php echo $row['trackId']; ?>" readonly class="frm_input">
<input type="hidden" name="authCd" value="<?php echo $row['authCd']; ?>" readonly class="frm_input">
<input type="hidden" name="issuer" value="<?php echo $row['issuer']; ?>" readonly class="frm_input">
<input type="hidden" name="acquirer" value="<?php echo $row['acquirer']; ?>" readonly class="frm_input">
<input type="hidden" name="cardType" value="<?php echo $row['cardType']; ?>" readonly class="frm_input">
<input type="hidden" name="bin" value="<?php echo $row['bin']; ?>" readonly class="frm_input">
<input type="hidden" name="last4" value="<?php echo $row['last4']; ?>" readonly class="frm_input">
<input type="hidden" name="installment" value="<?php echo $row['installment']; ?>" readonly class="frm_input">
<input type="hidden" name="amount" value="<?php echo $row['amount']; ?>" readonly class="frm_input">
<?php if($row['rootTrxId']) { ?>
<input type="hidden" name="rootTrxId" value="<?php echo $row['rootTrxId']; ?>" readonly class="frm_input">
<?php } ?>
<input type="hidden" name="datetime" value="<?php echo $row['datetime']; ?>" readonly class="frm_input">
</form>

<div style="width:100%; padding:150px 0; text-align:center;">등 록 중</div>

<script>
	document.payment_new.submit();
</script>
</div>