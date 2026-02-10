<?php
	include('./_common.php');

	if(!$is_admin) {

		alert("잘못된 접근 입니다.");

	} else {

		$pg_id = $_GET['pg_id'];
		$row = sql_fetch("select * from g5_payment_stn where pg_id = '{$pg_id}'");

		$cmd = isset($row['cmd']) ? trim($row['cmd']) : '';
		$paymethod = isset($row['paymethod']) ? trim($row['paymethod']) : '';
		$payType = isset($row['payType']) ? trim($row['payType']) : '';
		$requestFlag = isset($row['requestFlag']) ? trim($row['requestFlag']) : '';
		$mbrRefNo = isset($row['mbrRefNo']) ? trim($row['mbrRefNo']) : '';
		$mbrNo = isset($row['mbrNo']) ? trim($row['mbrNo']) : '';
		$refNo = isset($row['refNo']) ? trim($row['refNo']) : '';
		$tranDate = isset($row['tranDate']) ? trim($row['tranDate']) : '';
		$tranTime = isset($row['tranTime']) ? trim($row['tranTime']) : '';
		$orgRefNo = isset($row['orgRefNo']) ? trim($row['orgRefNo']) : '';
		$orgTranDate = isset($row['orgTranDate']) ? trim($row['orgTranDate']) : '';
		$vanCatId = isset($row['vanCatId']) ? trim($row['vanCatId']) : '';
		$cardMerchNo = isset($row['cardMerchNo']) ? trim($row['cardMerchNo']) : '';
		$applNo = isset($row['applNo']) ? trim($row['applNo']) : '';
		$issueCompanyNo = isset($row['issueCompanyNo']) ? trim($row['issueCompanyNo']) : '';
		$acqCompanyNo = isset($row['acqCompanyNo']) ? trim($row['acqCompanyNo']) : '';
		$cardNo = isset($row['cardNo']) ? trim($row['cardNo']) : '';
		$installNo = isset($row['installNo']) ? trim($row['installNo']) : '';
		$goodsName = isset($row['goodsName']) ? trim($row['goodsName']) : '';
		$amount = isset($row['amount']) ? trim($row['amount']) : '';
		$customerName = isset($row['customerName']) ? trim($row['customerName']) : '';
		$customerTelNo = isset($row['customerTelNo']) ? trim($row['customerTelNo']) : '';
		$customerEmail = isset($row['customerEmail']) ? trim($row['customerEmail']) : '';
		$sid = isset($row['sid']) ? trim($row['sid']) : '';
		$retailerCode = isset($row['retailerCode']) ? trim($row['retailerCode']) : '';

		$tranDatetranTime = "20".$tranDate.$tranTime;


		if($paymethod) {

			$pay_type = "Y";
			$pay_cdatetime = "";
			// 취소
			if($cmd != 0) {
				if($cmd == "2") {
					$pay_type = "B"; // 부분취소
				} else {
					$pay_type = "N";
				}

				// 취소일때 데이터 원거래에서 가져오기
				$pay_cdatetime = date("Y-m-d H:i:s", strtotime($tranDatetranTime));
				$amount = "-" . $amount; // 음수로 변경
				sql_query("update g5_payment set pay_cdatetime = '{$pay_cdatetime}' where trxId = '{$orgRefNo}'");
			}

			// 동기화 상태 변수 초기화
			$sync_status = 'pending';
			$sync_message = '';

			// ========================================
			// 수기결제 (requestFlag = 'K')
			// ========================================
			if($requestFlag == 'K') {
				// mbrRefNo(주문번호) 앞 4자리(mkc_oid)로 Keyin 설정 조회
				$mkc_oid = substr($mbrRefNo, 0, 4);
				$keyin_config = sql_fetch("SELECT * FROM g5_member_keyin_config
					WHERE mkc_use = 'Y' AND mkc_status = 'active' AND mkc_pg_code = 'stn'
					AND mkc_oid = '{$mkc_oid}'");

				$target_mb_id = $keyin_config['mb_id'] ?? '';

				// 해당 가맹점의 디바이스 정보 조회
				$row2 = array();
				if($target_mb_id) {
					$row2 = sql_fetch("SELECT d.*, d.mb_6 as merchant_mb_id
						FROM g5_device d
						WHERE d.mb_6 = '{$target_mb_id}'
						LIMIT 1");
				}

				// 디바이스 조회 실패 체크
				if(!$row2['dv_id']) {
					$sync_status = 'failed';
					$sync_message = $target_mb_id ? "device not found for mb_id '{$target_mb_id}'" : "keyin config not found for mkc_oid '{$mkc_oid}'";
				}

				$dv_tid_ori = $mbrNo;
				$dv_tid_value = $row2['dv_tid']; // 수기결제는 디바이스 TID 사용
				$pg_name_value = 'stn_k'; // 수기결제 구분
				$dv_type_value = '2'; // 수기결제 타입
			}
			// ========================================
			// 오프라인 단말기 결제 (일반)
			// ========================================
			else {
				$arraydata = explode(PHP_EOL, trim($config['cf_3']));
				$arraydata = array_map('trim', $arraydata);

				$arraydata2 = explode(PHP_EOL, trim($config['cf_4']));
				$arraydata2 = array_map('trim', $arraydata2);

				if($mbrNo == "114004") {
					$mbrNo = substr($mbrRefNo, 0, 13);
					$dv_tid_ori = "114004";
				} else if(in_array($mbrNo, $arraydata)) {
					$mbrNo = substr($mbrRefNo, 0, -10);
					$dv_tid_ori = $mbrNo;
				} else if(in_array($mbrNo, $arraydata2)) {
					$mbrNo = $mbrNo;
					$dv_tid_ori = $mbrNo;
				} else {
					$dv_tid_ori = $mbrNo;
					$mbrNo = $vanCatId;
				}

				$row2 = sql_fetch("select * from g5_device where dv_tid = '{$mbrNo}'");

				// 디바이스 조회 실패 체크
				if(!$row2['dv_id']) {
					$sync_status = 'failed';
					$sync_message = "device '{$mbrNo}' not found";
				}

				$dv_tid_value = $mbrNo; // 일반결제는 mbrNo 사용
				$pg_name_value = 'stn'; // 일반결제
				$dv_type_value = $row2['dv_type']; // 디바이스 타입 사용
			}

			// 수수료 계산 (row2는 위에서 이미 조회됨)
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



			$mb_1_pay = $amount * $row2['mb_1_fee'] /100;
			$mb_2_pay = $amount * $row2['mb_2_fee'] /100;
			$mb_3_pay = $amount * $row2['mb_3_fee'] /100;
			$mb_4_pay = $amount * $row2['mb_4_fee'] /100;
			$mb_5_pay = $amount * $row2['mb_5_fee'] /100;
			$mb_6_pay = $amount * $row2['mb_6_fee'] /100;
			$mb_6_pay = $amount - $mb_6_pay;




			$pay_datetime =  date("Y-m-d H:i:s", strtotime($tranDatetranTime));
		//	$calday =  date("Ymd", strtotime($trxDate));


			if($issueCompanyNo == "01") { $issueCompanyNo = "비씨카드";
			} else if($issueCompanyNo == "02") { $issueCompanyNo = "신한카드";
			} else if($issueCompanyNo == "03") { $issueCompanyNo = "삼성카드";
			} else if($issueCompanyNo == "04") { $issueCompanyNo = "현대카드";
			} else if($issueCompanyNo == "05") { $issueCompanyNo = "롯데카드";
			} else if($issueCompanyNo == "06") { $issueCompanyNo = "해외JCB카드";
			} else if($issueCompanyNo == "07") { $issueCompanyNo = "국민카드";
			} else if($issueCompanyNo == "08") { $issueCompanyNo = "하나카드(구외환)";
			} else if($issueCompanyNo == "09") { $issueCompanyNo = "해외카드";
			} else if($issueCompanyNo == "11") { $issueCompanyNo = "수협카드";
			} else if($issueCompanyNo == "12") { $issueCompanyNo = "농협카드";
			} else if($issueCompanyNo == "13") { $issueCompanyNo = "한미카드";
			} else if($issueCompanyNo == "15") { $issueCompanyNo = "씨티카드";
			} else if($issueCompanyNo == "21") { $issueCompanyNo = "신한카드";
			} else if($issueCompanyNo == "22") { $issueCompanyNo = "제주카드";
			} else if($issueCompanyNo == "23") { $issueCompanyNo = "광주카드";
			} else if($issueCompanyNo == "24") { $issueCompanyNo = "전북카드";
			} else if($issueCompanyNo == "26") { $issueCompanyNo = "신협카드";
			} else if($issueCompanyNo == "27") { $issueCompanyNo = "하나카드";
			} else if($issueCompanyNo == "30") { $issueCompanyNo = "신세계카드";
			} else if($issueCompanyNo == "31") { $issueCompanyNo = "우리카드";
			} else if($issueCompanyNo == "32") { $issueCompanyNo = "푸르미카드";
			} else if($issueCompanyNo == "33") { $issueCompanyNo = "꿈자람카드";
			} else if($issueCompanyNo == "34") { $issueCompanyNo = "온누리상품권";
			} else if($issueCompanyNo == "35") { $issueCompanyNo = "코나머니(해피기프트카드)";
			} else if($issueCompanyNo == "36") { $issueCompanyNo = "지드림카드"; }




			$sql_common = " pay_type = '{$pay_type}',
							pay = '{$amount}',
							pay_num = '{$applNo}',
							trxid = '{$refNo}',
							trackId = '{$mbrRefNo}',
							pay_datetime = '{$pay_datetime}',
							pay_cdatetime = '{$pay_cdatetime}',
							pay_parti = '{$installNo}',
							pay_card_name = '{$issueCompanyNo}',
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

							dv_type = '{$dv_type_value}',
							dv_certi = '{$row2['dv_certi']}',
							dv_tid = '{$dv_tid_value}',
							dv_tid_ori = '{$dv_tid_ori}',
							sftp_mbrno = '{$row2['sftp_mbrno']}',
							pg_name = '{$pg_name_value}' ";

			$pay = sql_fetch("select * from g5_payment where trxid = '{$refNo}' and pay_num = '{$applNo}'");

			if($sync_status == 'failed') {
				// 디바이스 조회 실패 시 동기화 상태 업데이트
				$sync_message_escaped = sql_escape_string($sync_message);
				sql_query("UPDATE g5_payment_stn SET sync_status = '{$sync_status}', sync_message = '{$sync_message_escaped}' WHERE pg_id = '{$pg_id}'");
				alert_close("등록 실패: " . $sync_message);
			} else if($pay['pay_id']) {
				// 등록되어 있다면 수정
				$sql = " update g5_payment set {$sql_common} where trxid = '{$refNo}' and pay_num = '{$applNo}' ";
				sql_query($sql);
				sql_query("UPDATE g5_payment_stn SET sync_status = 'success', sync_message = 'updated' WHERE pg_id = '{$pg_id}'");
				alert_close("수정 완료");
			} else {
				// 등록되지 않았다면 등록
				$sql = " insert into g5_payment set ".$sql_common.", datetime = '".G5_TIME_YMDHIS."'";
				sql_query($sql);
				sql_query("UPDATE g5_payment_stn SET sync_status = 'success', sync_message = '' WHERE pg_id = '{$pg_id}'");
				alert_close("등록 완료");
			}
		}
	}