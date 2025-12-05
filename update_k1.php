<?php
	include('./_common.php');

	if(!$is_admin) {

		alert("잘못된 접근 입니다.");

	} else {

		$pg_id = $_GET['pg_id'];
		$row = sql_fetch("select * from g5_payment_k1 where pg_id = '{$pg_id}'");

		$mchtId = isset($row['mchtId']) ? trim($row['mchtId']) : ''; // 가맹점 ID
		$trxId = isset($row['trxId']) ? trim($row['trxId']) : ''; // 광원 거래번호
		$tmnId = isset($row['tmnId']) ? trim($row['tmnId']) : ''; // 터미널ID
		$trxDate = isset($row['trxDate']) ? trim($row['trxDate']) : ''; // 승인일시
		$trxType = isset($row['trxType']) ? trim($row['trxType']) : ''; // 거래구분
		$trackId = isset($row['trackId']) ? trim($row['trackId']) : ''; // 주문번호
		$authCd = isset($row['authCd']) ? trim($row['authCd']) : ''; // 승인번호
		$issuer = isset($row['issuer']) ? trim($row['issuer']) : ''; // 발행사
		$acquirer = isset($row['acquirer']) ? trim($row['acquirer']) : ''; // 매입사
		$cardType = isset($row['cardType']) ? trim($row['cardType']) : ''; // 카드종류
		$bin = isset($row['bin']) ? trim($row['bin']) : ''; // 카드번호(bin)
		$last4 = isset($row['last4']) ? trim($row['last4']) : ''; // 카드번호(last4)
		$installment = isset($row['installment']) ? trim($row['installment']) : ''; // 할부기간
		$amount = isset($row['amount']) ? trim($row['amount']) : ''; // 거래금액
		$rootTrxId = isset($row['rootTrxId']) ? trim($row['rootTrxId']) : ''; // 원거래번호


		$pay_type = "Y";
		$pay_cdatetime = "";

		// 취소
		if($trxType == "REFUND") {
			$pay_type = "N";

			// 원거래
			$cancel = sql_fetch("select * from g5_payment_k1 where trxId = '{$rootTrxId}'");

			// 취소일때 데이터 원거래에서 가져오기
			$pay_cdatetime =  date("Y-m-d H:i:s", strtotime($trxDate));
			$issuer = $cancel['issuer'];
			$acquirer = $cancel['acquirer'];
			$cardType = $cancel['cardType'];
			$bin = $cancel['bin'];
			$last4 = $cancel['last4'];
			$authCd = $cancel['authCd'];
			$amount = "-".$cancel['amount'];
			$trackId = $cancel['trackId'];

		}

		sql_query("update g5_payment set pay_cdatetime = '{$pay_cdatetime}' where trxId = '{$rootTrxId}'");

		// tid 쪼개기
		if($tmnId == $config['cf_1']) {
			$tmnId = substr($trackId, 0, -10);
		} else if($tmnId == $config['cf_2']) {
			$tmnId = substr($trackId, 0, -10);
		} else if($tmnId == $config['cf_3']) {
			$tmnId = substr($trackId, 0, -10);
		} else if($tmnId == $config['cf_4']) {
			$tmnId = substr($trackId, 0, -10);
		} else if($tmnId == $config['cf_5']) {
			$tmnId = substr($trackId, 0, -10);
		} else if($tmnId == $config['cf_6']) {
			$tmnId = substr($trackId, 0, -10);
		}

		echo $config['cf_10']."<br><br>";
		echo $config['cfv_10']."<br><br>";

		$row2 = sql_fetch("select * from g5_device where dv_tid = '{$tmnId}'");

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
						dv_tid = '{$tmnId}',
						updatetime = '".G5_TIME_YMDHIS."' ";



		$pay = sql_fetch("select * from g5_payment where trxid = '{$trxId}' and pay_num = '{$authCd}' and pay_type = '{$pay_type}'"); // 기존에 등록되어 있는지 확인

		if($pay['pay_id']) { // 등록되어 있다면 수정
			$sql = " update g5_payment set {$sql_common} where trxid = '$trxId' and pay_num = '{$authCd}' ";
			sql_query($sql);
			alert_close("수정 완료");
		} else { // 등록되지 않았다면 등록
			$sql = " insert into g5_payment set ".$sql_common.", datetime = '".G5_TIME_YMDHIS."'";
			sql_query($sql);
			alert_close("등록 완료");
		}
		/*

		$sql = " update g5_payment set {$sql_common} where pay_id = '{$pay_id}' ";

//		echo $sql;

		sql_query($sql);
		echo $sql;
		exit;
		alert_close_re("등록완료");
		*/
	}
?>