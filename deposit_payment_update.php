<?php
	include('./_common.php');

	sql_query("update g5_payment set deposit = '{$deposit}' where pay_id = '{$pay_id}'");
	if($deposit == '1') {
		alert_close_re("입금취소 완료.");
	} else {
		alert_close_re("입금 완료.");
	}