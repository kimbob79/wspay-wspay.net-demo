<?php
	if($member['mb_level'] <= 3) {
		alert("권한이 없습니다.");
	}

//print "<pre>"; print_r($_POST); print "</pre>";

$w				= trim($_POST['w']);
$pg_id			= trim($_POST['pg_id']);


if($w == "d") { // 삭제

	sql_query(" delete from pay_pg where pg_id = '$pg_id' ");
	sql_query(" delete from pay_member_pg where pg_mid = '$pg_id' ");

} else { // 수정

	$pg_id			= trim($_POST['pg_id']);
	$pg_sort		= trim($_POST['pg_sort']);
	$pg_use			= trim($_POST['pg_use']);
	$pg_code		= trim($_POST['pg_code']);
	$pg_name		= trim($_POST['pg_name']);
	$pg_certified	= trim($_POST['pg_certified']);
	$pg_pay			= trim($_POST['pg_pay']);
	$pg_hal			= trim($_POST['pg_hal']);
	$pg_overlap		= trim($_POST['pg_overlap']);
	$pg_tid			= trim($_POST['pg_tid']);
	$pg_key1		= trim($_POST['pg_key1']);
	$pg_key2		= trim($_POST['pg_key2']);
	$pg_key3		= trim($_POST['pg_key3']);
	$pg_key4		= trim($_POST['pg_key4']);
	$pg_key5		= trim($_POST['pg_key5']);

	$card_nh		= isset($_POST['card_nh']) ? trim($_POST['card_nh']) : '0';
	$card_bc		= isset($_POST['card_bc']) ? trim($_POST['card_bc']) : '0';
	$card_sh		= isset($_POST['card_sh']) ? trim($_POST['card_sh']) : '0';
	$card_kb		= isset($_POST['card_kb']) ? trim($_POST['card_kb']) : '0';
	$card_hana		= isset($_POST['card_hana']) ? trim($_POST['card_hana']) : '0';
	$card_wr		= isset($_POST['card_wr']) ? trim($_POST['card_wr']) : '0';
	$card_ss		= isset($_POST['card_ss']) ? trim($_POST['card_ss']) : '0';
	$card_lo		= isset($_POST['card_lo']) ? trim($_POST['card_lo']) : '0';
	$card_hd		= isset($_POST['card_hd']) ? trim($_POST['card_hd']) : '0';

	$sql_common = " pg_sort = '{$pg_sort}',
					pg_use = '{$pg_use}',
					pg_code = '{$pg_code}',
					pg_name = '{$pg_name}',
					pg_certified = '{$pg_certified}',
					pg_pay = '{$pg_pay}',
					pg_hal = '{$pg_hal}',
					pg_overlap = '{$pg_overlap}',
					pg_tid = '{$pg_tid}',
					pg_key1 = '{$pg_key1}',
					pg_key2 = '{$pg_key2}',
					pg_key3 = '{$pg_key3}',
					pg_key4 = '{$pg_key4}',
					pg_key5 = '{$pg_key5}',
					card_nh = '{$card_nh}',
					card_bc = '{$card_bc}',
					card_sh = '{$card_sh}',
					card_kb = '{$card_kb}',
					card_hana = '{$card_hana}',
					card_wr = '{$card_wr}',
					card_ss = '{$card_ss}',
					card_lo = '{$card_lo}',
					card_hd = '{$card_hd}' ";


	if ($w == 'i') {

		$sql = " insert into pay_pg set {$sql_common} ";

	} elseif ($w == 'u') {

		$sql = " update pay_pg
					set {$sql_common}
					where pg_id = '{$pg_id}' ";

	}
//	echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br>".$sql; exit;
	sql_query($sql);
	

	if($pg_use == '1') { // PG 미사용일경우 회원쪽 PG도 모두 미사용 처리
		$sql = " update pay_member_pg set pg_use = '{$pg_use}' where pg_mid = '{$pg_id}' ";
		sql_query($sql);
	}
	if($pg_overlap == '1') { // PG 중복결제 불가시 회원쪽 PG도 모두 불가 처리
		$sql = " update pay_member_pg set pg_overlap = '{$pg_overlap}' where pg_mid = '{$pg_id}' ";
		sql_query($sql);
	}
	$sql = " update pay_member_pg set pg_code = '{$pg_code}', pg_name = '{$pg_name}' where pg_mid = '{$pg_id}' ";
	sql_query($sql);

}
goto_url('./?p=pg_list');