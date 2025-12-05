<?php
	include_once('./_common.php');

	if(!$is_admin) { alert("관리자만 접속 가능합니다."); }



/*
	print "<pre>"; print_r($_POST); print "</pre>";  exit;
Array
(
    [mb_1_name] => 
    [mb_2_name] => 
    [mb_3_name] => 
    [mb_4_name] => 
    [mb_5_name] => 
    [mb_6_name] => 
    [dv_tid] => 
    [page] => 1
    [token] => 
    [sm_id] => 
    [sm_type] => 00
    [sm_openmarket] => 1234567890
    [sm_bnumber] => 1234567890
    [sm_btype] => 음식점
    [sm_bname] => 본수원갈비
    [sm_addr] => 경기 수원시 팔달구 중부대로223번길 41
    [sm_ceo] => 홍길
    [sm_tel] => 031-211-8434
    [sm_email] => help@bonsuwon.com
    [sm_website] => http://www.bonsuwon.com/
    [sm_mbrno] => 111111
)

*/


	if($member['mb_type'] > 3) {
		alert("권한이 없습니다.");
	}

	$page    = $_POST['page'];

	$sm_id    = isset($_POST['sm_id']) ? trim($_POST['sm_id']) : '';

	$sm_type    = isset($_POST['sm_type']) ? trim($_POST['sm_type']) : '';
	$sm_openmarket    = isset($_POST['sm_openmarket']) ? trim($_POST['sm_openmarket']) : '';
	$sm_bnumber    = isset($_POST['sm_bnumber']) ? trim($_POST['sm_bnumber']) : '';
	$sm_btype    = isset($_POST['sm_btype']) ? trim($_POST['sm_btype']) : '';
	$sm_bname    = isset($_POST['sm_bname']) ? trim($_POST['sm_bname']) : '';
	$sm_addr    = isset($_POST['sm_addr']) ? trim($_POST['sm_addr']) : '';
	$sm_ceo    = isset($_POST['sm_ceo']) ? trim($_POST['sm_ceo']) : '';
	$sm_tel    = isset($_POST['sm_tel']) ? trim($_POST['sm_tel']) : '';
	$sm_email    = isset($_POST['sm_email']) ? trim($_POST['sm_email']) : '';
	$sm_website    = isset($_POST['sm_website']) ? trim($_POST['sm_website']) : '';
	$sm_mbrno    = isset($_POST['sm_mbrno']) ? trim($_POST['sm_mbrno']) : '';
	

	$sql_common = " sm_type = '{$sm_type}',
					sm_openmarket = '{$sm_openmarket}',
					sm_bnumber = '{$sm_bnumber}',
					sm_btype = '{$sm_btype}',
					sm_bname = '{$sm_bname}',
					sm_addr = '{$sm_addr}',
					sm_ceo = '{$sm_ceo}',
					sm_tel = '{$sm_tel}',
					sm_email = '{$sm_email}',
					sm_website = '{$sm_website}',
					sm_mbrno = '{$sm_mbrno}',
					updatetime = '".G5_TIME_YMDHIS."' ";
	if($sm_id) {
		$sql = " update g5_sftp_member set {$sql_common} where sm_id = '{$sm_id}' ";
	} else {
		$sql = " insert into g5_sftp_member set {$sql_common} , datetime = '".G5_TIME_YMDHIS."'";
	}

	sql_query($sql);
//	echo $sql;

	goto_url("./?p=sftp_member&page=".$page."&results=ok");