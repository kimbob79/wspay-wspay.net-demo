<?php
include_once('./_common.php');
if(!$is_admin) {
	alert("잘못된 접근입니다.");
}

//print "<pre>"; print_r($_POST); print "</pre>";  exit;

$xlsx_sql    = isset($_POST['xlsx_sql']) ? trim($_POST['xlsx_sql']) : '';
$p    = isset($_POST['p']) ? trim($_POST['p']) : '';

set_include_path( get_include_path().PATH_SEPARATOR."..");
include_once("xlsxwriter.class.php");
$f_name = "정산조회_가맹점";

$xlsx_sql = stripslashes($xlsx_sql);
$result = sql_query($xlsx_sql);

$header = [];
$widths = [];
$halign = [];

// $header 제목명은 모두 달라야 됨
$header["TID"] = 'integer';
$header["본 TID"] = 'integer';
$header["가맹점명"] = 'string';
$header["PG"] = 'string';
$header["승인"] = 'string';
$header["취소"] = 'string';
$header["승인금액"] = '#,##0';
$header["취소금액"] = '#,##0';
$header["총금액"] = '#,##0';
$header["정산금액"] = '#,##0';
$header["은행"] = 'string';
$header["은행코드"] = 'string';
$header["계좌번호"] = 'string';
$header["예금주명"] = 'string';

//셀너비
$widths[] = 20; // tid
$widths[] = 20; // tid
$widths[] = 30; // 가맹점명
$widths[] = 15; // PG
$widths[] = 10; // 승인
$widths[] = 10; // 취소
$widths[] = 20; // 승인금액
$widths[] = 20; // 취소금액
$widths[] = 20; // 총금액
$widths[] = 20; // 정산금액
$widths[] = 15; // 은행
$widths[] = 10; // 은행코드
$widths[] = 30; // 계좌번호
$widths[] = 25; // 예금주명

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

	$bankcode ="";
	if($field['mb_8'] == "경남") { $bankcode = "039";
	} else if($field['mb_8'] == "광주") { $bankcode = "034";
	} else if($field['mb_8'] == "국민") { $bankcode = "004";
	} else if($field['mb_8'] == "국민은행") { $bankcode = "004";
	} else if($field['mb_8'] == "기업") { $bankcode = "003";
	} else if($field['mb_8'] == "기업은행") { $bankcode = "003";
	} else if($field['mb_8'] == "농협") { $bankcode = "011";
	} else if($field['mb_8'] == "단위농협") { $bankcode = "012";
	} else if($field['mb_8'] == "대구") { $bankcode = "031";
	} else if($field['mb_8'] == "부산") { $bankcode = "032";
	} else if($field['mb_8'] == "산림조합") { $bankcode = "064";
	} else if($field['mb_8'] == "산업") { $bankcode = "002";
	} else if($field['mb_8'] == "상호저축") { $bankcode = "050";
	} else if($field['mb_8'] == "새마을금고") { $bankcode = "045";
	} else if($field['mb_8'] == "새마을") { $bankcode = "045";
	} else if($field['mb_8'] == "수협") { $bankcode = "007";
	} else if($field['mb_8'] == "신한") { $bankcode = "088";
	} else if($field['mb_8'] == "신협") { $bankcode = "048";
	} else if($field['mb_8'] == "우리") { $bankcode = "020";
	} else if($field['mb_8'] == "우체국") { $bankcode = "071";
	} else if($field['mb_8'] == "전북") { $bankcode = "037";
	} else if($field['mb_8'] == "제주") { $bankcode = "035";
	} else if($field['mb_8'] == "하나") { $bankcode = "081";
	} else if($field['mb_8'] == "한국씨티") { $bankcode = "027";
	} else if($field['mb_8'] == "도이치") { $bankcode = "055";
	} else if($field['mb_8'] == "BOA") { $bankcode = "060";
	} else if($field['mb_8'] == "중국공상") { $bankcode = "062";
	} else if($field['mb_8'] == "SC제일") { $bankcode = "023";
	} else if($field['mb_8'] == "SC제일은행") { $bankcode = "023";
	} else if($field['mb_8'] == "HSBC") { $bankcode = "054";
	} else if($field['mb_8'] == "K뱅크") { $bankcode = "089";
	} else if($field['mb_8'] == "카카오뱅크") { $bankcode = "090";
	} else if($field['mb_8'] == "카카오") { $bankcode = "090";
	} else if($field['mb_8'] == "토스") { $bankcode = "092";
	} else if($field['mb_8'] == "케이뱅크") { $bankcode = "089";
	}

	if(!$field['spay']) $field['spay'] = 0;
	if(!$field['cpay']) $field['cpay'] = 0;
	if($field['cpay']) $field['spay'] = 0;

	$x++;
	$contents[] = $field['dv_tid'];
	$contents[] = $field['dv_tid_ori'];
	$contents[] = $field['mb_6_name'];
	$contents[] = $field['mb_1_name'];
	$contents[] = $field['scnt'];
	$contents[] = $field['ccnt'];
	$contents[] = $field['spay'];
	$contents[] = $field['cpay'];
	$contents[] = $field['total_pay'];
	$contents[] = $field['mb_6_pay'];
	$contents[] = $field['mb_8'];
	$contents[] = $bankcode;
	$contents[] = $field['mb_9'];
	$contents[] = $field['mb_10'];
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