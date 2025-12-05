<?php
	include('./_common.php');

	if(!$is_admin) {

		alert("잘못된 접근 입니다.");

	} else {

		$pg_id = $_GET['pg_id'];
		$row = sql_fetch("select * from g5_payment_welcom where pg_id = '{$pg_id}'");

		$mid = isset($row['mid']) ? trim($row['mid']) : '';
		$pay_type = isset($row['pay_type']) ? trim($row['pay_type']) : '';
		$bank_code = isset($row['bank_code']) ? trim($row['bank_code']) : '';
		$transaction_flag = isset($row['transaction_flag']) ? trim($row['transaction_flag']) : '';
		$order_no = isset($row['order_no']) ? trim($row['order_no']) : '';
		$transaction_no = isset($row['transaction_no']) ? trim($row['transaction_no']) : '';
		$approval_ymdhms = isset($row['approval_ymdhms']) ? trim($row['approval_ymdhms']) : '';
		$cancel_ymdhms = isset($row['cancel_ymdhms']) ? trim($row['cancel_ymdhms']) : '';
		$amount = isset($row['amount']) ? trim($row['amount']) : '';
		$remain_amount = isset($row['remain_amount']) ? trim($row['remain_amount']) : '';
		$user_id = isset($row['user_id']) ? trim($row['user_id']) : '';
		$user_name = isset($row['user_name']) ? trim($row['user_name']) : '';
		$product_code = isset($row['product_code']) ? trim($row['product_code']) : '';
		$product_name = isset($row['product_name']) ? trim($row['product_name']) : '';
		$approval_no = isset($row['approval_no']) ? trim($row['approval_no']) : '';
		$card_sell_mm = isset($row['card_sell_mm']) ? trim($row['card_sell_mm']) : '';
		$account_no = isset($row['account_no']) ? trim($row['account_no']) : '';
		$deposit_ymdhms = isset($row['deposit_ymdhms']) ? trim($row['deposit_ymdhms']) : '';
		$deposit_amount = isset($row['deposit_amount']) ? trim($row['deposit_amount']) : '';
		$deposit_name = isset($row['deposit_name']) ? trim($row['deposit_name']) : '';
		$cash_seq = isset($row['cash_seq']) ? trim($row['cash_seq']) : '';
		$cash_approval_no = isset($row['cash_approval_no']) ? trim($row['cash_approval_no']) : '';
		$bank_name = isset($row['bank_name']) ? trim($row['bank_name']) : '';

		if ($mid) {

			if($mid == "wel000695m") {
				$product_code = substr($order_no, 0, -10); // tid
			}

			$pay_types = "Y";



			$pay_datetime =  date("Y-m-d H:i:s", strtotime($approval_ymdhms));
		//	$calday =  date("Ymd", strtotime($trxDate));

			// 취소
			if($cancel_ymdhms > 0) {
				$pay_types = "N";
				$amount = "-".$amount; // 음수로 변경

				// 원거래
				$cancel = sql_fetch("select * from g5_payment_welcom where trxid = '{$transaction_no}'");

				// 취소일때 데이터 원거래에서 가져오기
				$pay_datetime =  date("Y-m-d H:i:s", strtotime($cancel_ymdhms));
				$pay_cdatetime =  date("Y-m-d H:i:s", strtotime($cancel_ymdhms));

				sql_query("update g5_payment set pay_cdatetime = '{$pay_cdatetime}' where trxId = '{$transaction_no}'");
				$transaction_no = "C".$transaction_no;

			}

			$row2 = sql_fetch("select * from g5_device where dv_tid = '{$product_code}'");

			$mb_1_fee = number_format($row2['mb_1_fee'], 2);
			$mb_2_fee = number_format($row2['mb_2_fee'], 2);
			$mb_3_fee = number_format($row2['mb_3_fee'], 2);
			$mb_4_fee = number_format($row2['mb_4_fee'], 2);
			$mb_5_fee = number_format($row2['mb_5_fee'], 2);
			$mb_6_fee = number_format($row2['mb_6_fee'], 2);


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

			$mb_1_pay = $amount * $row2['mb_1_fee'] /100;
			$mb_2_pay = $amount * $row2['mb_2_fee'] /100;
			$mb_3_pay = $amount * $row2['mb_3_fee'] /100;
			$mb_4_pay = $amount * $row2['mb_4_fee'] /100;
			$mb_5_pay = $amount * $row2['mb_5_fee'] /100;
			$mb_6_pay = $amount * $row2['mb_6_fee'] /100;
			$mb_6_pay = $amount - $mb_6_pay;


			$sql_common = " pay_type = '{$pay_types}',
							pay = '{$amount}',
							pay_num = '{$approval_no}',
							trxid = '{$transaction_no}',
							trackId = '{$order_no}',
							pay_datetime = '{$pay_datetime}',
							pay_cdatetime = '{$pay_cdatetime}',
							pay_parti = '{$card_sell_mm}',
							pay_card_name = '{$bank_name}',
							pay_card_num = '',

							mb_1 = '{$row2['mb_1']}',
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
							dv_tid = '{$product_code}',
							pg_name = 'welcom' ";


			$pay = sql_fetch("select * from g5_payment where trxid = '{$transaction_no}' and pay_num = '{$approval_no}'");

			if($pay['pay_id']) { // 등록되어 있다면 수정
				$sql = " update g5_payment set {$sql_common} where trxid = '$transaction_no' and pay_num = '{$approval_no}' ";
				sql_query($sql);
				alert_close("수정 완료");
			} else { // 등록되지 않았다면 등록
				$sql = " insert into g5_payment set ".$sql_common.", datetime = '".G5_TIME_YMDHIS."'";
				sql_query($sql);
				alert_close("등록 완료");
			}

		}
	}