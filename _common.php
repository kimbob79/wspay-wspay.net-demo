<?php
define('G5_IS_ADMIN', true);
require_once './gnu_module/common.php';

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

// 데모 사이트: 오늘 결제 데이터가 없으면 자동 생성
if ($member['mb_id'] && !defined('DEMO_DATA_CHECKED')) {
    define('DEMO_DATA_CHECKED', true);
    @include_once(__DIR__ . '/demo_data_generator.php');
}