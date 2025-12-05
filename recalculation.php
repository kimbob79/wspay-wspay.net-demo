<?php
	include('./_common.php');

	if(!$is_admin) {

		alert("잘못된 접근 입니다.");

	} else {

		$pay_id = $_GET['pay_id'];
		//echo $pay_id;
		$ori = sql_fetch("select * from g5_payment where pay_id = '{$pay_id}'");
		//echo $ori['dv_tid'];
		//exit;

		$pays = $ori['pay'];
		/*
		if($pays < 1) {
			$ori2 = sql_fetch("select * from g5_payment where trackId = '{$ori['trackId']}' and pay_type = 'Y'");
			$pays = "-".$ori2['pay'];
		}
		*/

		$row2 = sql_fetch("select * from g5_device where dv_tid = '{$ori['dv_tid']}'");

		$mb_1_fee = $row2['mb_1_fee'];
		$mb_2_fee = $row2['mb_2_fee'];
		$mb_3_fee = $row2['mb_3_fee'];
		$mb_4_fee = $row2['mb_4_fee'];
		$mb_5_fee = $row2['mb_5_fee'];
		$mb_6_fee = $row2['mb_6_fee'];

		if($row2['mb_5_fee'] > 0.001) {
			$row2['mb_5_fee'] = $row2['mb_6_fee'] - $row2['mb_5_fee'];
		} else {
			$row2['mb_5_fee'] = 0.00;
		}

		if($row2['mb_4_fee'] > 0.001) {
			$row2['mb_4_fee'] = $row2['mb_6_fee'] - $row2['mb_5_fee'] - $row2['mb_4_fee'];
		} else {
			$row2['mb_4_fee'] = 0.00;
		}

		if($row2['mb_3_fee'] > 0.001) {
			$row2['mb_3_fee'] = $row2['mb_6_fee'] - $row2['mb_5_fee'] - $row2['mb_4_fee'] - $row2['mb_3_fee'];
		} else {
			$row2['mb_3_fee'] = 0.00;
		}

		if($row2['mb_2_fee'] > 0.001) {
			$row2['mb_2_fee'] = $row2['mb_6_fee'] - $row2['mb_5_fee'] - $row2['mb_4_fee'] - $row2['mb_3_fee'] - $row2['mb_2_fee'];
		} else {
			$row2['mb_2_fee'] = 0.00;
		}

		$row2['mb_1_fee'] = $row2['mb_6_fee'] - $row2['mb_5_fee'] - $row2['mb_4_fee'] - $row2['mb_3_fee'] - $row2['mb_2_fee'] - $row2['mb_1_fee'];

		$mb_1_pay = $pays * $row2['mb_1_fee'] /100;
		$mb_2_pay = $pays * $row2['mb_2_fee'] /100;
		$mb_3_pay = $pays * $row2['mb_3_fee'] /100;
		$mb_4_pay = $pays * $row2['mb_4_fee'] /100;
		$mb_5_pay = $pays * $row2['mb_5_fee'] /100;
		$mb_6_pay = $pays * $row2['mb_6_fee'] /100;
		$mb_6_pay = $pays - $mb_6_pay;

		$sql_common = " mb_1 = '{$row2['mb_1']}',
						mb_1_name = '{$row2['mb_1_name']}',
						mb_1_fee = '{$mb_1_fee}',
						mb_1_pay = '{$mb_1_pay}',

						mb_2 = '{$row2['mb_2']}',
						mb_2_name = '{$row2['mb_2_name']}',
						mb_2_fee = '{$mb_2_fee}',
						mb_2_pay = '{$mb_2_pay}',

						mb_3 = '{$row2['mb_3']}',
						mb_3_name = '{$row2['mb_3_name']}',
						mb_3_fee = '{$mb_3_fee}',
						mb_3_pay = '{$mb_3_pay}',

						mb_4 = '{$row2['mb_4']}',
						mb_4_name = '{$row2['mb_4_name']}',
						mb_4_fee = '{$mb_4_fee}',
						mb_4_pay = '{$mb_4_pay}',

						mb_5 = '{$row2['mb_5']}',
						mb_5_name = '{$row2['mb_5_name']}',
						mb_5_fee = '{$mb_5_fee}',
						mb_5_pay = '{$mb_5_pay}',

						mb_6 = '{$row2['mb_6']}',
						mb_6_name = '{$row2['mb_6_name']}',
						mb_6_fee = '{$mb_6_fee}',
						mb_6_pay = '{$mb_6_pay}',
						dv_type = '{$row2['dv_type']}',
						dv_certi = '{$row2['dv_certi']}',
						updatetime = '".G5_TIME_YMDHIS."' ";

		$sql = " update g5_payment set {$sql_common} where pay_id = '{$pay_id}' ";

//		echo $sql;

		sql_query($sql);
		alert_close_re("재정산 완료");
	}
?>
