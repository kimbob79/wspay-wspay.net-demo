<?php
require_once "./_common.php";
//print "<pre>"; print_r($_POST); print "</pre>"; exit;
if(!$is_admin) {
	if($member['mb_id'] != $_POST['mb_id']) {
		alert("잘못된 접근입니다.");
		exit;
	}
}


$w          = trim($_POST['w']);
$mb_nick          = trim($_POST['mb_nick']);
$mb_id          = trim($_POST['mb_id']);
$mb_password    = trim($_POST['mb_password']);
$mb_name          = trim($_POST['mb_name']);
$mb_hp          = $_POST['mb_hp1']."-".$_POST['mb_hp2']."-".$_POST['mb_hp3'];
$mb_adult          = trim($_POST['mb_adult']);
$mb_2          = trim($_POST['mb_2']);
$mb_3          = trim($_POST['mb_3']);
$mb_4          = trim($_POST['mb_4']);
$mb_5          = trim($_POST['mb_5']);
$mb_6          = trim($_POST['mb_6']);
$mb_7          = trim($_POST['mb_7']);
$mb_8          = trim($_POST['mb_8']);
$mb_9          = trim($_POST['mb_9']);
$mb_10          = trim($_POST['mb_10']);
$mb_11          = trim($_POST['mb_11']);
$mb_12          = trim($_POST['mb_12']);
$mb_13          = trim($_POST['mb_13']);
$mb_14          = trim($_POST['mb_14']);
$mb_15          = trim($_POST['mb_15']);
$mb_16          = trim($_POST['mb_16']);
$mb_17          = trim($_POST['mb_17']);
$mb_18          = trim($_POST['mb_18']);
$mb_19          = trim($_POST['mb_19']);
$mb_20          = trim($_POST['mb_20']);
$mb_21          = trim($_POST['mb_21']);
$mb_22          = trim($_POST['mb_22']);
$mb_23          = trim($_POST['mb_23']);
$mb_email = $mb_id."@test.com";

if($is_admin) {
	$sql_common = " mb_nick = '{$_POST['mb_nick']}',
					mb_name = '{$_POST['mb_name']}',
					mb_email = '{$mb_email}',
					mb_hp = '{$mb_hp}',
					mb_adult = '{$mb_adult}',
					mb_2 = '{$mb_2}',
					mb_3 = '{$mb_3}',
					mb_4 = '{$mb_4}',
					mb_5 = '{$mb_5}',
					mb_6 = '{$mb_6}',
					mb_7 = '{$mb_7}',
					mb_8 = '{$mb_8}',
					mb_9 = '{$mb_9}',
					mb_10 = '{$mb_10}',
					mb_11 = '{$mb_11}',
					mb_12 = '{$mb_12}',
					mb_13 = '{$mb_13}',
					mb_14 = '{$mb_14}',
					mb_15 = '{$mb_15}',
					mb_16 = '{$mb_16}',
					mb_17 = '{$mb_17}',
					mb_18 = '{$mb_18}',
					mb_19 = '{$mb_19}',
					mb_20 = '{$mb_20}',
					mb_21 = '{$mb_21}',
					mb_22 = '{$mb_22}',
					mb_23 = '{$mb_23}' ";
} else {
	if($w == "u") {

	$sql_common = " mb_nick = '{$_POST['mb_nick']}',
					mb_name = '{$_POST['mb_name']}',
					mb_email = '{$mb_email}',
					mb_hp = '{$mb_hp}' ";
	} else {
	$sql_common = " mb_nick = '{$_POST['mb_nick']}',
					mb_name = '{$_POST['mb_name']}',
					mb_email = '{$mb_email}',
					mb_hp = '{$mb_hp}',
					mb_adult = '{$mb_adult}',
					mb_2 = '{$mb_2}',
					mb_3 = '{$mb_3}',
					mb_4 = '{$mb_4}',
					mb_5 = '{$mb_5}',
					mb_6 = '{$mb_6}',
					mb_7 = '{$mb_7}',
					mb_8 = '{$mb_8}',
					mb_9 = '{$mb_9}',
					mb_10 = '{$mb_10}',
					mb_11 = '{$mb_11}',
					mb_12 = '{$mb_12}',
					mb_13 = '{$mb_13}',
					mb_14 = '{$mb_14}',
					mb_15 = '{$mb_15}',
					mb_16 = '{$mb_16}',
					mb_17 = '{$mb_17}',
					mb_18 = '{$mb_18}',
					mb_19 = '{$mb_19}',
					mb_20 = '{$mb_20}',
					mb_21 = '{$mb_21}',
					mb_22 = '{$mb_22}',
					mb_23 = '{$mb_23}' ";
	}
}

if ($w == '') {

	$sql = " insert into {$g5['member_table']} set mb_id = '{$mb_id}', mb_password = '" . get_encrypt_string($mb_password) . "', mb_datetime = '" . G5_TIME_YMDHIS . "', mb_ip = '{$_SERVER['REMOTE_ADDR']}', mb_email_certify = '" . G5_TIME_YMDHIS . "', mb_1 = '{$mb_password}', mb_level = '5', {$sql_common} ";

} elseif ($w == 'u') {

	if ($mb_password) {
		$sql_password = " , mb_password = '" . get_encrypt_string($mb_password) . "' ";
		$sql_password .= " , mb_1 = '" .$mb_password. "' ";
	} else {
		$sql_password = "";
	}

	$sql = " update {$g5['member_table']}
				set {$sql_common}
					 {$sql_password}
					 {$sql_certify}
				where mb_id = '{$mb_id}' ";

}
sql_query($sql);
goto_url('./add.php?mb_id='.$mb_id);
?>