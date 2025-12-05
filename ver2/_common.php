<?php
define('G5_IS_ADMIN', true);
require_once '../_engin/common.php';
require_once '../admin.lib.php';

if(!$is_admin) {
//	alert("아직은 접속 불가 합니다.","../");
}

if (isset($token)) {
	$token = @htmlspecialchars(strip_tags($token), ENT_QUOTES);
}

$redpay = "N";
