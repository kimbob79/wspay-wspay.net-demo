<?php
include_once('./_common.php');

// 로그인 체크
if(!$member['mb_id']) {
	alert("로그인이 필요합니다.");
}

// 파라미터 수신
$fr_date = isset($_POST['fr_date']) ? preg_replace('/[^0-9]/', '', $_POST['fr_date']) : date("Ymd");
$to_date = isset($_POST['to_date']) ? preg_replace('/[^0-9]/', '', $_POST['to_date']) : date("Ymd");
$sfl = isset($_POST['sfl']) ? sql_escape_string($_POST['sfl']) : '';
$stx = isset($_POST['stx']) ? sql_escape_string($_POST['stx']) : '';
$pay_num = isset($_POST['pay_num']) ? sql_escape_string($_POST['pay_num']) : '';
$dv_tid = isset($_POST['dv_tid']) ? sql_escape_string($_POST['dv_tid']) : '';
$mb_6_name = isset($_POST['mb_6_name']) ? sql_escape_string($_POST['mb_6_name']) : '';
$gname = isset($_POST['gname']) ? sql_escape_string($_POST['gname']) : '';
$l2 = isset($_POST['l2']) ? sql_escape_string($_POST['l2']) : '';
$l3 = isset($_POST['l3']) ? sql_escape_string($_POST['l3']) : '';
$l4 = isset($_POST['l4']) ? sql_escape_string($_POST['l4']) : '';
$l5 = isset($_POST['l5']) ? sql_escape_string($_POST['l5']) : '';
$l6 = isset($_POST['l6']) ? sql_escape_string($_POST['l6']) : '';
$l7 = isset($_POST['l7']) ? sql_escape_string($_POST['l7']) : '';

$fr_dates = date("Y-m-d", strtotime($fr_date));
$to_dates = date("Y-m-d", strtotime($to_date));

// SQL 생성 (payment.php와 동일한 로직)
$sql_common = " from g5_payment LEFT JOIN g5_member ON g5_payment.mb_6 = g5_member.mb_id ";

if($is_admin) {
	if(adm_sql_common) {
		$adm_sql = " mb_1 IN (".adm_sql_common.")";
	} else {
		$adm_sql = " (1)";
	}
} else if($member['mb_level'] == 8) {
	$adm_sql = " g5_payment.mb_1 = '{$member['mb_id']}'";
} else if($member['mb_level'] == 7) {
	$adm_sql = " g5_payment.mb_2 = '{$member['mb_id']}'";
} else if($member['mb_level'] == 6) {
	$adm_sql = " g5_payment.mb_3 = '{$member['mb_id']}'";
} else if($member['mb_level'] == 5) {
	$adm_sql = " g5_payment.mb_4 = '{$member['mb_id']}'";
} else if($member['mb_level'] == 4) {
	$adm_sql = " g5_payment.mb_5 = '{$member['mb_id']}'";
} else if($member['mb_level'] == 3) {
	$adm_sql = " g5_payment.mb_6 = '{$member['mb_id']}'";
} else {
	alert("권한이 없습니다.");
}

if ($fr_date == "all" && $to_date == "all") {
	$sql_search = " where ".$adm_sql." and mb_6_name != '' ";
} else {
	$sql_search = " where ".$adm_sql." and (pay_datetime BETWEEN '{$fr_dates} 00:00:00' and '{$to_dates} 23:59:59') and mb_6_name != '' ";
}

if($pay_num) {
	$sql_search .= " and pay_num = '{$pay_num}' ";
}
if($dv_tid) {
	$sql_search .= " and (dv_tid = '{$dv_tid}') ";
}
if($mb_6_name) {
	$sql_search .= " and (mb_6_name = '{$mb_6_name}') ";
}
if($gname) {
	$sql_search .= " and level_company_name like '%{$gname}%' ";
}
if($l2) { $sql_search .= " and mb_pid2 = '{$l2}' "; }
if($l3) { $sql_search .= " and mb_pid3 = '{$l3}' "; }
if($l4) { $sql_search .= " and mb_pid4 = '{$l4}' "; }
if($l5) { $sql_search .= " and mb_pid5 = '{$l5}' "; }
if($l6) { $sql_search .= " and mb_pid6 = '{$l6}' "; }
if($l7) { $sql_search .= " and mb_pid7 = '{$l7}' "; }

// 허용된 검색 필드만 사용
$allowed_sfl = array('pay_num', 'mb_6_name', 'dv_tid', 'dv_tid_ori', 'pay', 'pay_card_name', 'pay_card_num');
if ($stx && $sfl && in_array($sfl, $allowed_sfl)) {
	if($sfl == "gr_id" || $sfl == "gr_admin") {
		$sql_search .= " and ({$sfl} = '{$stx}') ";
	} else {
		$sql_search .= " and ({$sfl} like '%{$stx}%') ";
	}
}

$sql_order = " order by pay_datetime desc ";
$xlsx_sql = "select * {$sql_common} {$sql_search} {$sql_order}";

set_include_path( get_include_path().PATH_SEPARATOR."..");
include_once("xlsxwriter.class.php");
$f_name = "실시간_결제내역";

$result = sql_query($xlsx_sql);

$header = [];
$widths = [];
$halign = [];

// $header 제목명은 모두 달라야 됨
$header["번호"] = 'integer';
$header["가맹점명"] = 'string';
$header["승인일시"] = 'string';
$header["수수료"] = 'string';
$header["승인금액"] = '#,##0';
$header["할부"] = 'string';
$header["카드사"] = 'string';
$header["카드번호"] = 'string';
$header["승인번호"] = 'string';
$header["구분"] = 'string';
$header["TID"] = 'string';
$header["본TID"] = 'string';
$header["PG"] = 'string';
$header["주문번호"] = 'string';

//셀너비
$widths[] = 8;
$widths[] = 50;
$widths[] = 25;
$widths[] = 15;
$widths[] = 15;
$widths[] = 10;
$widths[] = 20;
$widths[] = 20;
$widths[] = 20;
$widths[] = 20;
$widths[] = 20;
$widths[] = 20;
$widths[] = 20;
$widths[] = 50;

$writer = new XLSXWriter();

$styles1 = array('font'=>'맑은 고딕','font-size'=>10,'font-style'=>'bold','color'=>'#fff', 'fill'=>'#777', 'halign'=>'left', 'valign'=>'center', 'border'=>'left,right,top,bottom', 'border-style'=>'thin', 'widths'=>$widths);
$styles2 = array( 'font'=>'맑은 고딕','font-size'=>11, 'halign'=>'left', 'valign'=>'center', 'border'=>'left,right,top,bottom', 'border-style'=>'thin','wrap_text'=>true);

$writer->writeSheetHeader('Sheet1', $header, $styles1);    //제목줄 서식 포함
$contents = [];
$x = 0;
foreach ($result as $field) {

	if($field['pay_type'] == "Y" && $field['pay_cdatetime'] > '0000-00-00 00:00:00') {
		$pay_type = "승인취소";
	} else if($field['pay_type'] == "Y") {
		$pay_type = "승인";
	} else if($field['pay_type'] == "N") {
		$pay_type = "취소";
		$bgcolor = 'pink';
	} else if($field['pay_type'] == "M") {
		$pay_type = "망취소";
	} else if($field['pay_type'] == "X") {
		$pay_type = "수동취소";
	}

	if($field['pay_parti'] < 1) {
		$pay_parti = "일시불";
	} else {
		$pay_parti = $field['pay_parti']."개월";
	}

	if($field['pg_name'] == "k1") {
		$pg_name = "광원";
	} else if($field['pg_name'] == "welcom") {
		$pg_name = "웰컴";
	} else if($field['pg_name'] == "korpay") {
		$pg_name = "코페이";
	} else {
		$pg_name = "웰컴";
	}

	$x++;
	$contents[] = $x;
	$contents[] = $field['mb_6_name'];
	$contents[] = $field['pay_datetime'];
	$contents[] = $field['mb_6_fee'];
	$contents[] = $field['pay'];
	$contents[] = $pay_parti;
	$contents[] = $field['pay_card_name'];
	$contents[] = $field['pay_card_num'];
	$contents[] = $field['pay_num'];
	$contents[] = $pay_type;
	$contents[] = $field['dv_tid'];
	$contents[] = $field['dv_tid_ori'];
	$contents[] = $pg_name;
	$contents[] = $field['trxid'];
	$writer->writeSheetRow('Sheet1', $contents, $styles2);
	$contents = [];
}
$filename = $f_name."_".time().".xlsx";
$writer->writeToFile(G5_DATA_PATH.'/tmp/'.$filename);
$filepath = G5_DATA_PATH.'/tmp/'.$filename;
$filepath = addslashes($filepath);
$original = urlencode($filename);
if(preg_match("/msie/i", $_SERVER["HTTP_USER_AGENT"]) && preg_match("/5\.5/", $_SERVER["HTTP_USER_AGENT"])) {
	header("content-type: doesn/matter");
	header("content-length: ".filesize("$filepath"));
	header("content-disposition: attachment; filename=\"$original\"");
	header("content-transfer-encoding: binary");
} else if (preg_match("/Firefox/i", $_SERVER["HTTP_USER_AGENT"])){
	header("content-type: file/unknown");
	header("content-length: ".filesize("$filepath"));
	header("content-disposition: attachment; filename=\"".basename($filename)."\"");
	header("content-description: php generated data");
} else {
	header("content-type: file/unknown");
	header("content-length: ".filesize("$filepath"));
	header("content-disposition: attachment; filename=\"$original\"");
	header("content-description: php generated data");
}
header("pragma: no-cache");
header("expires: 0");
flush();
$fp = fopen($filepath, "rb");
if (!fpassthru($fp)) {
	fclose($fp);
}
//파일 삭제
$delete_file = G5_DATA_PATH.'/tmp/'.$filename;
if(file_exists($delete_file) ){
	@unlink($delete_file);
}