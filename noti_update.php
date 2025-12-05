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

	$nt_id    = isset($_POST['nt_id']) ? trim($_POST['nt_id']) : '';

	$nt_category    = isset($_POST['nt_category']) ? trim($_POST['nt_category']) : '';
	$nt_mbrno    = isset($_POST['nt_mbrno']) ? trim($_POST['nt_mbrno']) : '';
	$nt_url    = isset($_POST['nt_url']) ? trim($_POST['nt_url']) : '';
	$nt_memo    = isset($_POST['nt_memo']) ? trim($_POST['nt_memo']) : '';


	$sql = "SELECT nt_id FROM g5_noti WHERE nt_mbrno = '{$nt_mbrno}'";
	$url_row = sql_fetch($sql);

	// 2. forward URL 없으면 종료
	if ($url_row['nt_id']) {
		alert("이미등록된 코드입니다.");
		return; // 전송하지 않음
	}
	

	$sql_common = " nt_category = '{$nt_category}',
					nt_mbrno = '{$nt_mbrno}',
					nt_url = '{$nt_url}',
					nt_memo = '{$nt_memo}', ";
	if($nt_id) {
		$sql = " update g5_noti set {$sql_common} updatetime = '".G5_TIME_YMDHIS."' where nt_id = '{$nt_id}' ";
	} else {
		$sql = " insert into g5_noti set {$sql_common} datetime = '".G5_TIME_YMDHIS."'";
	}

	sql_query($sql);
//	echo $sql;	exit;

	goto_url("./?p=noti_list&page=".$page."&results=ok");