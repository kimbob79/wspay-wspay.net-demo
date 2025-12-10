<?php
define('G5_IS_ADMIN', true);
require_once './___engin/common.php';

if (isset($token)) {
	$token = @htmlspecialchars(strip_tags($token), ENT_QUOTES);
}


if($member['mb_level'] == '10') {
	$is_admin = 'super';
}

if(!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on")
{
    //Tell the browser to redirect to the HTTPS URL.
    //header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"], true, 301);
    //Prevent the rest of the script from executing.
    //exit;
}
//$redpay = "Y";