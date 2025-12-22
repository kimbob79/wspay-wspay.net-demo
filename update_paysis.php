<?php
	include('./_common.php');

	if(!$is_admin) {

		alert("잘못된 접근 입니다.");

	} else {

		$pg_id = $_GET['pg_id'];
		$row = sql_fetch("select * from g5_payment_paysis where pg_id = '{$pg_id}'");

		$gid = isset($row['gid']) ? trim($row['gid']) : '';							// 그룹 ID
		$vid = isset($row['vid']) ? trim($row['vid']) : '';							// VAN ID
		$mid = isset($row['mid']) ? trim($row['mid']) : '';							// 상점 ID 
		$payMethod = isset($row['payMethod']) ? trim($row['payMethod']) : '';			// 결제수단 
		$appCardCd = isset($row['appCardCd']) ? trim($row['appCardCd']) : '';			// 카드코드/은행코드/ 상품권사코드/ 휴대폰코드 
		$cancelYN  = isset($row['cancelYN']) ? trim($row['cancelYN']) : '';			// 취소구분
		$tid = isset($row['tid']) ? trim($row['tid']) : '';							// 거래고유번호
		$ediNo  = isset($row['ediNo']) ? trim($row['ediNo']) : '';					// VAN거래고유번호
		$appDtm = isset($row['appDtm']) ? trim($row['appDtm']) : '';					// 승인일
		$ccDnt  = isset($row['ccDnt']) ? trim($row['ccDnt']) : '';					// 취소일 
		$amt = isset($row['amt']) ? trim($row['amt']) : '';							// 금액 
		$remainAmt = isset($row['remainAmt']) ? trim($row['remainAmt']) : '';			// 잔액 
		$buyerId = isset($row['buyerId']) ? trim($row['buyerId']) : '';				// 구매자 ID 
		$ordNm = isset($row['ordNm']) ? trim($row['ordNm']) : '';						// 구매자명 
		$ordNo = isset($row['ordNo']) ? trim($row['ordNo']) : '';						// 주문번호 
		$goodsName  = isset($row['goodsName']) ? trim($row['goodsName']) : '';		// 상품명 
		$appNo = isset($row['appNo']) ? trim($row['appNo']) : '';						// 승인번호 
		$quota = isset($row['quota']) ? trim($row['quota']) : '';						// 할부개월 
		$notiDnt  = isset($row['notiDnt']) ? trim($row['notiDnt']) : '';			// Noti 통보일 
		$cardNo = isset($row['cardNo']) ? trim($row['cardNo']) : '';					// 카드번호
		$catId = isset($row['catId']) ? trim($row['catId']) : '';						// 단말기 CAT_ID
		$tPhone = isset($row['tPhone']) ? trim($row['tPhone']) : '';					// phone 번호 입력 사항
		$connCD = isset($row['connCD']) ? trim($row['connCD']) : '';					// 단말기/수기결제 구분


		if ($payMethod) {
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
				$tid = "c".$tid;

				// 원거래
				$cancel = sql_fetch("select * from g5_payment_paysis where tid = '{$tid2}'");

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
				sql_query("update g5_payment set pay_cdatetime = '{$pay_cdatetime}' where trxId = '{$tid2}'");
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



			if($appCardCd == "01") { $appCardCd = "비씨";
			} else if($appCardCd == "02") { $appCardCd = "국민";
			} else if($appCardCd == "03") { $appCardCd = "하나(구외환)";
			} else if($appCardCd == "04") { $appCardCd = "삼성";
			} else if($appCardCd == "06") { $appCardCd = "신한";
			} else if($appCardCd == "07") { $appCardCd = "현대";
			} else if($appCardCd == "08") { $appCardCd = "롯데";
			} else if($appCardCd == "09") { $appCardCd = "한미";
			} else if($appCardCd == "10") { $appCardCd = "신세계한미";
			} else if($appCardCd == "11") { $appCardCd = "씨티";
			} else if($appCardCd == "12") { $appCardCd = "NH농협카드";
			} else if($appCardCd == "13") { $appCardCd = "수협";
			} else if($appCardCd == "14") { $appCardCd = "평화";
			} else if($appCardCd == "15") { $appCardCd = "우리";
			} else if($appCardCd == "16") { $appCardCd = "하나";
			} else if($appCardCd == "17") { $appCardCd = "동남(주택)";
			} else if($appCardCd == "18") { $appCardCd = "주택";
			} else if($appCardCd == "19") { $appCardCd = "조흥(강원)";
			} else if($appCardCd == "20") { $appCardCd = "축협(농협)";
			} else if($appCardCd == "21") { $appCardCd = "광주";
			} else if($appCardCd == "22") { $appCardCd = "전북";
			} else if($appCardCd == "23") { $appCardCd = "제주";
			} else if($appCardCd == "24") { $appCardCd = "산은";
			} else if($appCardCd == "25") { $appCardCd = "해외비자";
			} else if($appCardCd == "26") { $appCardCd = "해외마스터";
			} else if($appCardCd == "27") { $appCardCd = "해외다이너스";
			} else if($appCardCd == "28") { $appCardCd = "해외AMX";
			} else if($appCardCd == "29") { $appCardCd = "해외JCB";
			} else if($appCardCd == "30") { $appCardCd = "해외";
			} else if($appCardCd == "31") { $appCardCd = "SK-OKCashBag";
			} else if($appCardCd == "32") { $appCardCd = "우체국";
			} else if($appCardCd == "33") { $appCardCd = "MG새마을체크";
			} else if($appCardCd == "34") { $appCardCd = "중국은행체크";
			} else if($appCardCd == "38") { $appCardCd = "은련";
			} else if($appCardCd == "46") { $appCardCd = "카카오";
			} else if($appCardCd == "47") { $appCardCd = "강원"; }



			$sql_common = " pay_type = '{$pay_type}',
							pay = '{$amt}',
							pay_num = '{$appNo}',
							trxid = '{$tid}',
							trackId = '{$ordNo}',
							pay_datetime = '{$pay_datetime}',
							pay_cdatetime = '{$pay_cdatetime}',
							pay_parti = '{$quota}',
							pay_card_name = '{$appCardCd}',
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
							pg_name = 'paysis' ";

			$pay = sql_fetch("select * from g5_payment where trxid = '{$tid}' and pay_num = '{$appNo}'");

			if($pay['pay_id']) { // 등록되어 있다면 수정
				$sql = " update g5_payment set {$sql_common} where trxid = '$tid' and pay_num = '{$appNo}' ";
				sql_query($sql);
				// sync_status 업데이트
				sql_query("UPDATE g5_payment_paysis SET sync_status = 'success', sync_message = 'updated' WHERE pg_id = '{$pg_id}'");
				alert_close("수정 완료");
			} else { // 등록되지 않았다면 등록
				$sql = " insert into g5_payment set ".$sql_common.", datetime = '".G5_TIME_YMDHIS."'";
				sql_query($sql);
				// sync_status 업데이트
				sql_query("UPDATE g5_payment_paysis SET sync_status = 'success', sync_message = '' WHERE pg_id = '{$pg_id}'");
				alert_close("등록 완료");
			}
		}
	}