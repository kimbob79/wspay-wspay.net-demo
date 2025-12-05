<?php
	include('./_common.php');

	if(!$is_admin) {

		alert("잘못된 접근 입니다.");

	} else {

		$pg_id = $_GET['pg_id'];
		$row = sql_fetch("select * from g5_payment_danal where pg_id = '{$pg_id}'");

		$QUOTA = isset($row['QUOTA']) ? trim($row['QUOTA']) : '';					// 할부개월수
		$TRANTIME = isset($row['TRANTIME']) ? trim($row['TRANTIME']) : '';			// 매출발생시간 HHmmss
		$CARDNAME = isset($row['CARDNAME']) ? trim($row['CARDNAME']) : '';			// 카드사 명
		$TXTYPE = isset($row['TXTYPE']) ? trim($row['TXTYPE']) : '';				// (승인)"BILL", (취소)"CANCEL"
		$TID = isset($row['TID']) ? trim($row['TID']) : '';							// 다날 거래 키
		$TRANDATE = isset($row['TRANDATE']) ? trim($row['TRANDATE']) : '';			// 매출발생일자 yyyyMMdd
		$ORDERID = isset($row['ORDERID']) ? trim($row['ORDERID']) : '';				// 오프라인 거래 고유번호
		$CAT_ID = isset($row['CAT_ID']) ? trim($row['CAT_ID']) : '';				// CAT 단말 기기 터미널 ID
		$AMOUNT = isset($row['AMOUNT']) ? trim($row['AMOUNT']) : '';				// 승인금액(총 금액)
		$CPID = isset($row['CPID']) ? trim($row['CPID']) : '';						// CPID
		$CARDAUTHNO = isset($row['CARDAUTHNO']) ? trim($row['CARDAUTHNO']) : '';	// 거래 승인 번호
		$O_TID = isset($row['O_TID']) ? trim($row['O_TID']) : '';					// 다날 원거래 키
		$ITEMNAME = isset($row['ITEMNAME']) ? trim($row['ITEMNAME']) : '';			// CAT 단말 기기에서 설정한 상품 코드
		$CATID = isset($row['CATID']) ? trim($row['CATID']) : '';					// CAT 단말 기기 터미널 ID
		$CARDNO = isset($row['CARDNO']) ? trim($row['CARDNO']) : '';				// 카드번호

		$appDtm = $TRANDATE." ".$TRANTIME;

		if($CPID) {

			$pay_type = "Y";
			$pay_cdatetime = "";

			// 취소
			if($TXTYPE == "CANCEL") {
				$pay_type = "N";
				$tid2 = $TID;
				$TID = "c".$TID;
				$pay_cdatetime =  date("Y-m-d H:i:s", strtotime($appDtm)); // 취소날짜 날짜형식으로 변경
				$amt = "-".$amt; // 음수로 변경
				sql_query("update g5_payment set pay_cdatetime = '{$pay_cdatetime}' where trxId = '{$O_TID}'"); // 기존 승인건 취소날짜 입력
			}

			$row2 = sql_fetch("select * from g5_device where dv_tid = '{$CATID}'");

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

			$mb_1_pay = $AMOUNT * $row2['mb_1_fee'] /100;
			$mb_2_pay = $AMOUNT * $row2['mb_2_fee'] /100;
			$mb_3_pay = $AMOUNT * $row2['mb_3_fee'] /100;
			$mb_4_pay = $AMOUNT * $row2['mb_4_fee'] /100;
			$mb_5_pay = $AMOUNT * $row2['mb_5_fee'] /100;
			$mb_6_pay = $AMOUNT * $row2['mb_6_fee'] /100;
			$mb_6_pay = $AMOUNT - $mb_6_pay;

			$pay_datetime =  date("Y-m-d H:i:s", strtotime($appDtm)); // 날짜 입력

			$sql_common = " pay_type = '{$pay_type}',
							pay = '{$AMOUNT}',
							pay_num = '{$CARDAUTHNO}',
							trxid = '{$TID}',
							trackId = '{$ORDERID}',
							pay_datetime = '{$pay_datetime}',
							pay_cdatetime = '{$pay_cdatetime}',
							pay_parti = '{$QUOTA}',
							pay_card_name = '{$CARDNAME}',
							pay_card_num = '{$CARDNO}',

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
							dv_tid = '{$CATID}',
							pg_name = 'danal' ";

			$pay = sql_fetch("select * from g5_payment where trxid = '{$TID}' and pay_num = '{$CARDAUTHNO}' and pay_type = '{$pay_type}'");

			if($pay['pay_id']) { // 등록되어 있다면 수정
				$sql = " update g5_payment set {$sql_common} where trxid = '$TID' and pay_num = '{$CARDAUTHNO}' ";
				sql_query($sql);
				alert_close("수정 완료");
			} else { // 등록되지 않았다면 등록
				$sql = " insert into g5_payment set ".$sql_common.", datetime = '".G5_TIME_YMDHIS."'";
				sql_query($sql);
				alert_close("등록 완료");
			}
			echo $sql;
		}
	}