<?php
	if(!$is_admin) { alert("관리자만 접속 가능합니다."); }

	$w    = isset($_POST['w']) ? trim($_POST['w']) : '';

	if($w == "u") {
		$dv_id    = isset($_POST['dv_id']) ? trim($_POST['dv_id']) : '';
	}

	$dv_tid    = isset($_POST['dv_tid']) ? trim($_POST['dv_tid']) : '';

//	$dv = sql_fetch(" select * from g5_device where dv_id = '{$dv_id}' "); // 기존자료

	$mb_1    = isset($_POST['mb_1']) ? trim($_POST['mb_1']) : '';
	$mb_1_name    = isset($_POST['mb_1_name']) ? trim($_POST['mb_1_name']) : '';
	$mb_1_fee    = isset($_POST['mb_1_fee']) ? trim($_POST['mb_1_fee']) : '';

	$mb_2    = isset($_POST['mb_2']) ? trim($_POST['mb_2']) : '';
	$mb_2_name    = isset($_POST['mb_2_name']) ? trim($_POST['mb_2_name']) : '';
	$mb_2_fee    = isset($_POST['mb_2_fee']) ? trim($_POST['mb_2_fee']) : '';

	$mb_3    = isset($_POST['mb_3']) ? trim($_POST['mb_3']) : '';
	$mb_3_name    = isset($_POST['mb_3_name']) ? trim($_POST['mb_3_name']) : '';
	$mb_3_fee    = isset($_POST['mb_3_fee']) ? trim($_POST['mb_3_fee']) : '';

	$mb_4    = isset($_POST['mb_4']) ? trim($_POST['mb_4']) : '';
	$mb_4_name    = isset($_POST['mb_4_name']) ? trim($_POST['mb_4_name']) : '';
	$mb_4_fee    = isset($_POST['mb_4_fee']) ? trim($_POST['mb_4_fee']) : '';

	$mb_5    = isset($_POST['mb_5']) ? trim($_POST['mb_5']) : '';
	$mb_5_name    = isset($_POST['mb_5_name']) ? trim($_POST['mb_5_name']) : '';
	$mb_5_fee    = isset($_POST['mb_5_fee']) ? trim($_POST['mb_5_fee']) : '';

	$mb_6    = isset($_POST['mb_6']) ? trim($_POST['mb_6']) : '';
	$mb_6_name    = isset($_POST['mb_6_name']) ? trim($_POST['mb_6_name']) : '';
	$mb_6_fee    = isset($_POST['mb_6_fee']) ? trim($_POST['mb_6_fee']) : '';


	$dv_pg    = isset($_POST['dv_pg']) ? trim($_POST['dv_pg']) : '';
	$dv_type    = isset($_POST['dv_type']) ? trim($_POST['dv_type']) : '';
	$dv_certi    = isset($_POST['dv_certi']) ? trim($_POST['dv_certi']) : '';

	$dv_open_date    = isset($_POST['dv_open_date']) ? trim($_POST['dv_open_date']) : '';
	$dv_agent    = isset($_POST['dv_agent']) ? trim($_POST['dv_agent']) : '';
	$dv_number    = isset($_POST['dv_number']) ? trim($_POST['dv_number']) : '';
	$dv_model    = isset($_POST['dv_model']) ? trim($_POST['dv_model']) : '';
	$dv_model_number    = isset($_POST['dv_model_number']) ? trim($_POST['dv_model_number']) : '';
	$dv_sn    = isset($_POST['dv_sn']) ? trim($_POST['dv_sn']) : '';
	$dv_usim    = isset($_POST['dv_usim']) ? trim($_POST['dv_usim']) : '';
	$dv_usim_number    = isset($_POST['dv_usim_number']) ? trim($_POST['dv_usim_number']) : '';


	/*
	$row = sql_fetch(" select count(*) as cnt from g5_device where dv_sn = '{$dv_sn}' and dv_id != '{$dv_id}' "); // PG
	if($row['cnt']) {
		alert("단말기 제품번호가 이미 등록되어 있습니다.");
	}
	*/


	$p_level    = isset($_POST['p_level']) ? trim($_POST['p_level']) : '';
	$p_mb_nick    = isset($_POST['p_mb_nick']) ? trim($_POST['p_mb_nick']) : '';
	$p_dv_tid    = isset($_POST['p_dv_tid']) ? trim($_POST['p_dv_tid']) : '';
	$p_page    = isset($_POST['p_page']) ? trim($_POST['p_page']) : '';



	// TID 중복검사
	if($w != "u") {
		$row = sql_fetch(" select count(*) as cnt from g5_device where dv_tid = '{$dv_tid}' "); // 
		if($row['cnt']) {
			alert("터미널 아이디 이미 등록되어 있습니다.");
		}
	}

	$sql_common = " dv_tid = '{$dv_tid}',

					mb_1 = '{$mb_1}',
					mb_1_name = '{$mb_1_name}',
					mb_1_fee = '{$mb_1_fee}',

					mb_2 = '{$mb_2}',
					mb_2_name = '{$mb_2_name}',
					mb_2_fee = '{$mb_2_fee}',

					mb_3 = '{$mb_3}',
					mb_3_name = '{$mb_3_name}',
					mb_3_fee = '{$mb_3_fee}',

					mb_4 = '{$mb_4}',
					mb_4_name = '{$mb_4_name}',
					mb_4_fee = '{$mb_4_fee}',

					mb_5 = '{$mb_5}',
					mb_5_name = '{$mb_5_name}',
					mb_5_fee = '{$mb_5_fee}',

					mb_6 = '{$mb_6}',
					mb_6_name = '{$mb_6_name}',
					mb_6_fee = '{$mb_6_fee}',

					dv_pg = '{$dv_pg}',
					dv_type = '{$dv_type}',
					dv_certi = '{$dv_certi}',

					dv_open_date = '{$dv_open_date}',
					dv_agent = '{$dv_agent}',
					dv_number = '{$dv_number}',

					dv_model = '{$dv_model}',
					dv_model_number = '{$dv_model_number}',
					dv_sn = '{$dv_sn}',

					dv_usim = '{$dv_usim}',
					dv_usim_number = '{$dv_usim_number}' ";

	if($w == "u") {
		$sql = " update g5_device set {$sql_common} where dv_id = '$dv_id' ";
		sql_query($sql2);
		$alert_msg = "수정";
	} else {
		$sql = " insert into g5_device set {$sql_common} , datetime = '".G5_TIME_YMDHIS."' ";
		$alert_msg = "등록";
	}

//	echo $sql;
//	exit;

	sql_query($sql);

	alert($alert_msg."완료","./?p=member&level=".$p_level."&mb_nick=".$p_mb_nick."&dv_tid=".$p_dv_tid."&page=".$p_page);