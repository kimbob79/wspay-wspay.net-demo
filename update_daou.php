<?php
	include('./_common.php');

	if(!$is_admin) {

		alert("잘못된 접근 입니다.");

	} else {

		$pg_id = $_GET['pg_id'];
		$row = sql_fetch("select * from g5_payment_daou where pg_id = '{$pg_id}'");

		$gid = isset($row['gid']) ? trim($row['gid']) : '';						//
		$wTid = isset($row['wTid']) ? trim($row['wTid']) : '';					//
		$cardNm = isset($row['cardNm']) ? trim($row['cardNm']) : '';				//
		$cancelYN = isset($row['cancelYN']) ? trim($row['cancelYN']) : '';		//
		$tid = isset($row['tid']) ? trim($row['tid']) : '';						//
		$ediNo = isset($row['ediNo']) ? trim($row['ediNo']) : '';					//
		$appDtm = isset($row['appDtm']) ? trim($row['appDtm']) : '';				//
		$ccDnt = isset($row['ccDnt']) ? trim($row['ccDnt']) : '';					//
		$amt = isset($row['amt']) ? trim($row['amt']) : '';						//
		$ordNm = isset($row['ordNm']) ? trim($row['ordNm']) : '';					//
		$goodsName = isset($row['goodsName']) ? trim($row['goodsName']) : '';		//
		$appNo = isset($row['appNo']) ? trim($row['appNo']) : '';					//
		$quota = isset($row['quota']) ? trim($row['quota']) : '';					//
		$cardNo = isset($row['cardNo']) ? trim($row['cardNo']) : '';				//
		$catId = isset($row['catId']) ? trim($row['catId']) : '';					//
		$tmnId = isset($row['tmnId']) ? trim($row['tmnId']) : '';					//


		if ($catId) {
			/*
			if($mid == "wel000695m") {
				$product_code = substr($order_no, 0, -10); // tid
			}
			*/

			$pay_type = "Y";
			$pay_cdatetime = "";

			// 취소
			if($cancelYN == "Y") {
				$pay_type = "N";
				$tid2 = substr($tid,1);
				$tid = $tid;

				// 원거래
				$cancel = sql_fetch("select * from g5_payment_daou where tid = '{$tid2}'");

				// 취소일때 데이터 원거래에서 가져오기
				$pay_cdatetime =  date("Y-m-d H:i:s", strtotime($ccDnt));
				$appDtm = $ccDnt;
				/*
				$issuer = $cancel['issuer'];
				$acquirer = $cancel['acquirer'];
				$cardType = $cancel['cardType'];
				$bin = $cancel['bin'];
				$last4 = $cancel['last4'];
				$authCd = $cancel['authCd'];
				*/
				//$amt = "-".$cancel['amt']; // 음수로 변경
				sql_query("update g5_payment set pay_cdatetime = '{$pay_cdatetime}' where trackId = '{$ediNo}' and pay_num = '{$appNo}'");
			}

			$row2 = sql_fetch("select * from g5_device where dv_tid = '{$catId}'");

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



			$mb_1_pay = $amt * $row2['mb_1_fee'] /100;
			$mb_2_pay = $amt * $row2['mb_2_fee'] /100;
			$mb_3_pay = $amt * $row2['mb_3_fee'] /100;
			$mb_4_pay = $amt * $row2['mb_4_fee'] /100;
			$mb_5_pay = $amt * $row2['mb_5_fee'] /100;
			$mb_6_pay = $amt * $row2['mb_6_fee'] /100;
			$mb_6_pay = $amt - $mb_6_pay;




			$pay_datetime =  date("Y-m-d H:i:s", strtotime($appDtm));
		//	$calday =  date("Ymd", strtotime($trxDate));



			$sql_common = " pay_type = '{$pay_type}',
							pay = '{$amt}',
							pay_num = '{$appNo}',
							trxid = '{$tid}',
							trackId = '{$ediNo}',
							pay_datetime = '{$pay_datetime}',
							pay_cdatetime = '{$pay_cdatetime}',
							pay_parti = '{$quota}',
							pay_card_name = '{$cardNm}',
							pay_card_num = '{$cardNo}',

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
							dv_tid = '{$catId}',
							pg_name = 'daou' ";

			$pay = sql_fetch("select * from g5_payment where trxid = '{$tid}' and pay_num = '{$appNo}'");


			if($pay['pay_id']) { // 등록되어 있다면 수정
				$sql = " update g5_payment set {$sql_common} where trxid = '$tid' and pay_num = '{$appNo}' ";
				sql_query($sql);
				alert_close("수정 완료");
			} else { // 등록되지 않았다면 등록
				$sql = " insert into g5_payment set ".$sql_common.", datetime = '".G5_TIME_YMDHIS."'";
				sql_query($sql);
				alert_close("등록 완료");
			}
		}
	}