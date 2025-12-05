<?php
	include('./_common.php');

	if($ca_type == "insert") { // 취소신청
		$pay_id = isset($_POST['pay_id']) ? trim($_POST['pay_id']) : ''; // 결제건아이디
		$mb_id = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : ''; // 신청자명
		$memo = isset($_POST['memo']) ? trim($_POST['memo']) : ''; // 신청자명

		$cancel =  sql_fetch(" select * from g5_payment_cancel where pay_id = '{$pay_id}'");

		if($cancel['pay_id']) {
			alert("이미 취소요청되었습니다.");
		}

		$row =  sql_fetch(" select * from g5_payment where pay_id = '{$pay_id}'");

		$sql_common = " mb_id = '{$mb_id}',
						pay_id = '{$pay_id}',

						mb_1 = '{$row['mb_1']}',
						mb_2 = '{$row['mb_2']}',
						mb_3 = '{$row['mb_3']}',
						mb_4 = '{$row['mb_4']}',
						mb_5 = '{$row['mb_5']}',
						mb_6 = '{$row['mb_6']}',

						mb_6_name = '{$row['mb_6_name']}',
						pay = '{$row['pay']}',
						pay_num = '{$row['pay_num']}',
						pay_datetime = '{$row['pay_datetime']}',
						ca_memo = '{$ca_memo}'";

		$sql = " insert into g5_payment_cancel set ".$sql_common.", datetime = '".G5_TIME_YMDHIS."'";
		sql_query($sql);
		alert_close_re("취소신청이 완료되었습니다.");

	} else if($ca_type == "success") { // 취소완료 / 대기

		if(!$is_admin) { alert("관리자만 접속 가능합니다."); exit; }

		$cancel =  sql_fetch(" select * from g5_payment_cancel where ca_id = '{$ca_id}'");

		if($cancel['ca_success'] == 1) {
			sql_query("update g5_payment_cancel set ca_success = '0' where ca_id = '{$ca_id}'");
			alert_close_re("취소상태를 대기로 변경하였습니다..");
		} else {
			sql_query("update g5_payment_cancel set ca_success = '1' where ca_id = '{$ca_id}'");
			alert_close_re("취소상태를 완료로 변경하였습니다..");
		}

	} else if($ca_type == "deposit") { // 입금완료 / 대기

		$cancel =  sql_fetch(" select * from g5_payment_cancel where ca_id = '{$ca_id}'");

		if($cancel['mb_id'] == $member['mb_id'] || $is_admin) { // 신청인 또는 관리자일경우

			if($cancel['ca_deposit'] == 1) {
				sql_query("update g5_payment_cancel set ca_deposit = '0' where ca_id = '{$ca_id}'");
				alert_close_re("입금대기로 변경하였습니다..");
			} else {
				sql_query("update g5_payment_cancel set ca_deposit = '1' where ca_id = '{$ca_id}'");
				alert_close_re("입금완료로 변경하였습니다..");
			}
		} else {
			alert("신청자만 변경가능합니다.");
		}

	} else if($ca_type == "delete") { // 입금완료 / 대기

		$cancel =  sql_fetch(" select * from g5_payment_cancel where ca_id = '{$ca_id}'");

		if($cancel['mb_id'] == $member['mb_id'] || $is_admin) { // 신청인 또는 관리자일경우

			// 파일테이블 행 삭제
			sql_query(" delete from g5_payment_cancel where ca_id = '{$ca_id}' ");
			alert_close_re("삭제 완료 하였습니다..");

		} else {
			alert("신청자만 변경가능합니다.");
		}
	}