<?php
	include_once('./_common.php');

	$mb_id = $_POST['mb_id'];
	$pay_id = $_POST['pay_id'];
	$memo = $_POST['memo'];

	$mb = get_member($mb_id);
	

	$sql = " insert into g5_payment_memo
				set pay_id = '{$pay_id}',
					mb_id = '{$mb_id}',
					mb_name = '{$mb['mb_name']}',
					me_memo = '{$memo}',
					ip = '{$_SERVER['REMOTE_ADDR']}',
					datetime = '".G5_TIME_YMDHIS."' ";
	sql_query($sql);

	
	$sql = " update g5_payment set memo = memo + 1 where pay_id = '$pay_id' ";
	sql_query($sql);
	alert_close_re("등록 완료");

?>