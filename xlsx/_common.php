<?php
define('G5_IS_ADMIN', true);
require_once '../_engin/common.php';

if (isset($token)) {
	$token = @htmlspecialchars(strip_tags($token), ENT_QUOTES);
}
$redpay = "Y";