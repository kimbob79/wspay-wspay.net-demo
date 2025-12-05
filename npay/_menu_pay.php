<div class="tab-group">
	<div class="tab-menu">
		<a href="./pay_simple.php" <?php if($pp == "pay_simple") { echo 'class="active"'; } ?>>전자결제</a>
		<a href="./pay_sms.php" <?php if($pp == "pay_sms") { echo 'class="active"'; } ?>>원격결제</a>
		<a href="./pay_write.php" <?php if($pp == "pay_write") { echo 'class="active"'; } ?>>수기결제</a>
		<a href="./pay_camera.php" <?php if($pp == "pay_camera") { echo 'class="active"'; } ?>>스캔결제</a>
		<a href="./pay_touch.php" <?php if($pp == "pay_touch") { echo 'class="active"'; } ?>>터치결제</a>
		<a href="./pay_applepay.php" <?php if($pp == "pay_applepay") { echo 'class="active"'; } ?>>애플페이</a>
		<a href="./pay_wechatpay.php" <?php if($pp == "pay_wechatpay") { echo 'class="active"'; } ?>>위챗페이</a>
		<a href="./pay_qr.php" <?php if($pp == "php") { echo 'class="active"'; } ?>>QR결제</a>
		<a href="./pay_recurring.php" <?php if($pp == "pay_recurring") { echo 'class="active"'; } ?>>정기결제</a>
		<a href="./pay_register.php" <?php if($pp == "pay_register") { echo 'class="active"'; } ?>>등록결제</a>
		<a href="./pay_global.php" <?php if($pp == "pay_global") { echo 'class="active"'; } ?>>해외결제</a>
		<a href="./pay_accounts.php" <?php if($pp == "pay_accounts") { echo 'class="active"'; } ?>>부계정</a>
	</div>
</div>