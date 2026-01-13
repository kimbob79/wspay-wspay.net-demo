<?php
	include_once('./_common.php');

	if(!$is_admin) { alert("관리자만 접속 가능합니다."); }

//	print "<pre>"; print_r($_POST); print "</pre>";  exit;

	if($member['mb_type'] > 3) {
		alert("권한이 없습니다.");
	}

	$page    = $_POST['page'];

	$dv_id    = isset($_POST['dv_id']) ? trim($_POST['dv_id']) : '';

	$dv_pg    = isset($_POST['dv_pg']) ? trim($_POST['dv_pg']) : '';
	$dv_type    = isset($_POST['dv_type']) ? trim($_POST['dv_type']) : '';
	$dv_certi    = isset($_POST['dv_certi']) ? trim($_POST['dv_certi']) : '';
	$sftp_mbrno    = isset($_POST['sftp_mbrno']) ? trim($_POST['sftp_mbrno']) : '';

	$membera    = isset($_POST['membera']) ? trim($_POST['membera']) : '';
	$memberb    = isset($_POST['memberb']) ? trim($_POST['memberb']) : '';
	$memberc    = isset($_POST['memberc']) ? trim($_POST['memberc']) : '';
	$memberd    = isset($_POST['memberd']) ? trim($_POST['memberd']) : '';
	$membere    = isset($_POST['membere']) ? trim($_POST['membere']) : '';

	// 검색 필터용 (수정된 값이 아닌 기존 검색 조건 유지)
	$search_dv_pg    = isset($_POST['search_dv_pg']) ? trim($_POST['search_dv_pg']) : '';
	$search_dv_type    = isset($_POST['search_dv_type']) ? trim($_POST['search_dv_type']) : '';
	$search_dv_certi    = isset($_POST['search_dv_certi']) ? trim($_POST['search_dv_certi']) : '';
	$mb_1_name    = isset($_POST['mb_1_name']) ? trim($_POST['mb_1_name']) : '';
	$mb_2_name    = isset($_POST['mb_2_name']) ? trim($_POST['mb_2_name']) : '';
	$mb_3_name    = isset($_POST['mb_3_name']) ? trim($_POST['mb_3_name']) : '';
	$mb_4_name    = isset($_POST['mb_4_name']) ? trim($_POST['mb_4_name']) : '';
	$mb_5_name    = isset($_POST['mb_5_name']) ? trim($_POST['mb_5_name']) : '';
	$mb_6_name    = isset($_POST['mb_6_name']) ? trim($_POST['mb_6_name']) : '';
	$dv_tid    = isset($_POST['dv_tid']) ? trim($_POST['dv_tid']) : '';

//	$dv = sql_fetch(" select * from g5_device where dv_id = '{$dv_id}' "); // 기존자료

	$mb_1_fee    = isset($_POST['mb_1_fee']) ? trim($_POST['mb_1_fee']) : '';
	$mb_2_fee    = isset($_POST['mb_2_fee']) ? trim($_POST['mb_2_fee']) : '';
	$mb_3_fee    = isset($_POST['mb_3_fee']) ? trim($_POST['mb_3_fee']) : '';
	$mb_4_fee    = isset($_POST['mb_4_fee']) ? trim($_POST['mb_4_fee']) : '';
	$mb_5_fee    = isset($_POST['mb_5_fee']) ? trim($_POST['mb_5_fee']) : '';
	$mb_6_fee    = isset($_POST['mb_6_fee']) ? trim($_POST['mb_6_fee']) : '';
	

	$sql_common = " mb_1_fee = '{$mb_1_fee}',
					mb_2_fee = '{$mb_2_fee}',
					mb_3_fee = '{$mb_3_fee}',
					mb_4_fee = '{$mb_4_fee}',
					mb_5_fee = '{$mb_5_fee}',
					mb_6_fee = '{$mb_6_fee}',
					dv_pg = '{$dv_pg}',
					dv_type = '{$dv_type}',
					dv_certi = '{$dv_certi}',
					sftp_mbrno = '{$sftp_mbrno}' ";
	if($dv_id) {

		$sql = " update g5_device set {$sql_common} where dv_id = '{$dv_id}' ";
	} else {
		$sql = " insert into g5_device set {$sql_common} , dv_datetime = '".G5_TIME_YMDHIS."'";
	}

	sql_query($sql);
//	echo $sql;

	goto_url("./?p=tid_fee&dv_pg=".$search_dv_pg."&dv_type=".$search_dv_type."&dv_certi=".$search_dv_certi."&memberb=".$memberb."&memberc=".$memberc."&memberd=".$memberd."&membere=".$membere."&mb_1_name=".$mb_1_name."&mb_2_name=".$mb_2_name."&mb_3_name=".$mb_3_name."&mb_4_name=".$mb_4_name."&mb_5_name=".$mb_5_name."&mb_6_name=".$mb_6_name."&dv_tid=".$dv_tid."&page=".$page."&results=ok#row-".$dv_id);