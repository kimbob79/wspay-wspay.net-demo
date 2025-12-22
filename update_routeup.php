<?php
	include('./_common.php');

	if(!$is_admin) {

		alert("잘못된 접근 입니다.");

	} else {

		$pg_id = $_GET['pg_id'];
		$row = sql_fetch("select * from g5_payment_routeup where pg_id = '{$pg_id}'");

		$mid = isset($row['mid']) ? trim($row['mid']) : '';
		$tid = isset($row['tid']) ? trim($row['tid']) : '';
		$trx_id = isset($row['trx_id']) ? trim($row['trx_id']) : '';
		$amount = isset($row['amount']) ? trim($row['amount']) : '';
		$ord_num = isset($row['ord_num']) ? trim($row['ord_num']) : '';
		$appr_num = isset($row['appr_num']) ? trim($row['appr_num']) : '';
		$item_name = isset($row['item_name']) ? trim($row['item_name']) : '';
		$buyer_name = isset($row['buyer_name']) ? trim($row['buyer_name']) : '';
		$buyer_phone = isset($row['buyer_phone']) ? trim($row['buyer_phone']) : '';
		$issuer = isset($row['issuer']) ? trim($row['issuer']) : '';
		$acquirer = isset($row['acquirer']) ? trim($row['acquirer']) : '';
		$issuer_code = isset($row['issuer_code']) ? trim($row['issuer_code']) : '';
		$acquirer_code = isset($row['acquirer_code']) ? trim($row['acquirer_code']) : '';
		$card_num = isset($row['card_num']) ? trim($row['card_num']) : '';
		$installment = isset($row['installment']) ? trim($row['installment']) : '';
		$trx_dttm = isset($row['trx_dttm']) ? trim($row['trx_dttm']) : '';
		$cxl_dttm = isset($row['cxl_dttm']) ? trim($row['cxl_dttm']) : '';
		$is_cancel = isset($row['is_cancel']) ? trim($row['is_cancel']) : '';
		$cxl_seq = isset($row['cxl_seq']) ? trim($row['cxl_seq']) : '';
		$ori_trx_id = isset($row['ori_trx_id']) ? trim($row['ori_trx_id']) : '';
		$module_type = isset($row['module_type']) ? trim($row['module_type']) : '';

		if($trx_id) {

			$pay_type = "Y";
			$pay_cdatetime = "";
			$trx_id_for_payment = $trx_id;

			// 취소 처리
			if($is_cancel == "1") {
				$pay_type = "N";
				$trx_id_for_payment = "c" . $trx_id;

				// 취소시간 설정
				$pay_cdatetime = date("Y-m-d H:i:s", strtotime($cxl_dttm));
				$amount = "-" . $amount; // 음수로 변경

				// 원거래 취소일시 업데이트
				sql_query("UPDATE g5_payment SET pay_cdatetime = '{$pay_cdatetime}' WHERE trxId = '{$ori_trx_id}'");
			}

			// 수기결제 (module_type = 1)
			if($module_type == '1') {
				// 1. MID + TID로 Keyin 설정 조회 (개별설정 또는 대표가맹점설정)
				// 루트업: 개별설정은 mkc_mid + mkc_mkey, 대표설정은 mpc_rootup_mid
				$keyin_sql = "SELECT k.*, m.mpc_rootup_mid
					FROM g5_member_keyin_config k
					LEFT JOIN g5_manual_payment_config m ON k.mpc_id = m.mpc_id
					WHERE k.mkc_use = 'Y' AND k.mkc_status = 'active'
					AND (
						(k.mpc_id IS NULL AND k.mkc_mid = '{$mid}' AND k.mkc_mkey = '{$tid}')
						OR (k.mpc_id IS NOT NULL AND m.mpc_rootup_mid = '{$mid}')
					)";
				$keyin_result = sql_query($keyin_sql);
				$keyin_count = sql_num_rows($keyin_result);

				$keyin_config = null;
				$target_mb_id = '';

				if($keyin_count == 1) {
					// 개별설정 가맹점이거나 대표설정을 단독 사용하는 경우
					$keyin_config = sql_fetch_array($keyin_result);
					$target_mb_id = $keyin_config['mb_id'];
				} else if($keyin_count > 1) {
					// 대표가맹점 설정을 여러 가맹점이 공유하는 경우 → mkc_oid로 구분
					$mkc_oid = substr($ord_num, 0, 4);
					while($row_k = sql_fetch_array($keyin_result)) {
						if($row_k['mkc_oid'] == $mkc_oid) {
							$keyin_config = $row_k;
							$target_mb_id = $row_k['mb_id'];
							break;
						}
					}
				}

				// 2. 해당 가맹점의 디바이스 정보 조회
				$row2 = array();
				if($target_mb_id) {
					$row2 = sql_fetch("SELECT d.*, d.mb_6 as merchant_mb_id
						FROM g5_device d
						WHERE d.mb_6 = '{$target_mb_id}'
						LIMIT 1");
				}

				$catId = $row2['dv_tid'];
				$dv_tid_ori = '';
				$pg_name = 'routeup_k';
				$dv_type_val = '2';  // 수기결제는 온라인
			}
			// 오프라인 단말기 결제 (module_type = 0 또는 기타)
			else {
				$catId = $tid;
				$dv_tid_ori = '';

				$row2 = sql_fetch("SELECT * FROM g5_device WHERE dv_tid = '{$catId}'");
				$pg_name = 'routeup';
				$dv_type_val = $row2['dv_type'];  // 디바이스 설정값 사용
			}

			$mb_1_fee = $row2['mb_1_fee'];
			$mb_2_fee = $row2['mb_2_fee'];
			$mb_3_fee = $row2['mb_3_fee'];
			$mb_4_fee = $row2['mb_4_fee'];
			$mb_5_fee = $row2['mb_5_fee'];
			$mb_6_fee = $row2['mb_6_fee'];

			// 수수료 계산
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

			$mb_1_pay = $amount * $row2['mb_1_fee'] / 100;
			$mb_2_pay = $amount * $row2['mb_2_fee'] / 100;
			$mb_3_pay = $amount * $row2['mb_3_fee'] / 100;
			$mb_4_pay = $amount * $row2['mb_4_fee'] / 100;
			$mb_5_pay = $amount * $row2['mb_5_fee'] / 100;
			$mb_6_pay = $amount * $row2['mb_6_fee'] / 100;
			$mb_6_pay = $amount - $mb_6_pay;

			$pay_datetime = date("Y-m-d H:i:s", strtotime($trx_dttm));

			// 발급사명을 카드사명으로 사용
			$pay_card_name = $issuer;

			$sql_common = " pay_type = '{$pay_type}',
							pay = '{$amount}',
							pay_num = '{$appr_num}',
							trxid = '{$trx_id_for_payment}',
							trackId = '{$ord_num}',
							pay_datetime = '{$pay_datetime}',
							pay_cdatetime = '{$pay_cdatetime}',
							pay_parti = '{$installment}',
							pay_card_name = '{$pay_card_name}',
							pay_card_num = '{$card_num}',

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

							dv_type = '{$dv_type_val}',
							dv_certi = '{$row2['dv_certi']}',
							dv_tid = '{$catId}',
							dv_tid_ori = '{$dv_tid_ori}',
							pg_name = '{$pg_name}' ";

			$pay = sql_fetch("select * from g5_payment where trxid = '{$trx_id_for_payment}' and pay_num = '{$appr_num}'");
			echo $catId."<br>";
			if($pay['pay_id']) { // 등록되어 있다면 수정
				$sql = " update g5_payment set {$sql_common} where trxid = '{$trx_id_for_payment}' and pay_num = '{$appr_num}' ";
				sql_query($sql);
				echo $sql;

				// sync_status 업데이트
				sql_query("UPDATE g5_payment_routeup SET sync_status = 'success', sync_message = 'updated' WHERE pg_id = '{$pg_id}'");

				alert_close("수정 완료");
			} else { // 등록되지 않았다면 등록
				$sql = " insert into g5_payment set ".$sql_common.", datetime = '".G5_TIME_YMDHIS."'";
				sql_query($sql);
				echo $sql;

				// sync_status 업데이트
				sql_query("UPDATE g5_payment_routeup SET sync_status = 'success', sync_message = '' WHERE pg_id = '{$pg_id}'");

				alert_close("등록 완료");
			}
		}
	}
