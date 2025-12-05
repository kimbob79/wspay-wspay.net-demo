<?php
include_once('./_common.php');
/*
if(!$is_admin) {
	alert("잘못된 접근입니다.");
}
*/

//print "<pre>"; print_r($_POST); print "</pre>";  exit;

$xlsx_sql    = isset($_POST['xlsx_sql']) ? trim($_POST['xlsx_sql']) : '';
$p    = isset($_POST['p']) ? trim($_POST['p']) : '';

set_include_path( get_include_path().PATH_SEPARATOR."..");
include_once("xlsxwriter.class.php");
$f_name = "실시간_결제내역";

$xlsx_sql = stripslashes($xlsx_sql);
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