<?php
	require_once "./_common.php";
	//print "<pre>"; print_r($_POST); print "</pre>"; exit;



	$pm_id          = trim($_POST['pm_id']);
	$memo          = trim($_POST['memo']);

	$fr_date          = trim($_POST['fr_date']);
	$to_date          = trim($_POST['to_date']);

	$row = sql_fetch(" select * from pay_payment_passgo where pm_id = '$pm_id' ");


	if($row['memo']) {
		$rmemo = $row['memo']."<br>";
	}

	$memo_full = addslashes($rmemo.$memo."<br><span class='memodate'>(작성일시 : ".date("Y.m.d H:i:s").")</span>");

	$sql = " update pay_payment_passgo set memo = '{$memo_full}' where pm_id = '{$pm_id}' ";
	sql_query($sql);
	goto_url('./list.php?pm_id='.$pm_id.'&fr_date='.$fr_date.'&to_date='.$to_date);
?>
