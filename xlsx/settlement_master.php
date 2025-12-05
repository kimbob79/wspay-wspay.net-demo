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
$f_name = "실시간 정산조회";

$xlsx_sql = stripslashes($xlsx_sql);
$result = sql_query($xlsx_sql);

$header = [];
$widths = [];
$halign = [];

// $header 제목명은 모두 달라야 됨
$header["TID"] = 'integer';
$header["가맹점명"] = 'integer';
$header["승인"] = 'string';
$header["취소"] = 'string';
$header["승인금액"] = '#,##0';
$header["취소금액"] = '#,##0';
$header["총금액"] = '#,##0';
$header["가맹점수수료"] = 'string';
$header["가맹점정산금"] = '#,##0';
$header["본사명"] = '#,##0';
$header["본사수수료"] = 'string';
$header["본사수익금"] = '#,##0';
$header["지사명"] = 'string';
$header["지사수수료"] = 'string';
$header["지사수익금"] = '#,##0';
$header["총판명"] = 'string';
$header["총판수수료"] = 'string';
$header["총판수익금"] = '#,##0';
$header["대리점명"] = 'string';
$header["대리점수수료"] = 'string';
$header["대리점수익금"] = '#,##0';
$header["영업점명"] = 'string';
$header["영업점수수료"] = 'string';
$header["영업점수익금"] = '#,##0';
$header["계좌정보"] = 'string';

//셀너비
$widths[] = 20; // tid
$widths[] = 30; // tid
$widths[] = 20; // tid
$widths[] = 20; // tid
$widths[] = 20; // tid
$widths[] = 20; // tid
$widths[] = 20; // tid
$widths[] = 20; // tid
$widths[] = 20; // tid
$widths[] = 20; // tid
$widths[] = 20; // tid
$widths[] = 20; // tid
$widths[] = 20; // tid
$widths[] = 20; // tid
$widths[] = 20; // tid
$widths[] = 20; // tid
$widths[] = 20; // tid
$widths[] = 20; // tid
$widths[] = 20; // tid
$widths[] = 20; // tid
$widths[] = 20; // tid
$widths[] = 20; // tid
$widths[] = 20; // tid
$widths[] = 20; // tid
$widths[] = 60; // tid

$writer = new XLSXWriter();

$styles1 = array('font'=>'맑은 고딕','font-size'=>10,'font-style'=>'bold','color'=>'#fff', 'fill'=>'#777', 'halign'=>'left', 'valign'=>'center', 'border'=>'left,right,top,bottom', 'border-style'=>'thin', 'widths'=>$widths);
$styles2 = array( 'font'=>'맑은 고딕','font-size'=>11, 'halign'=>'left', 'valign'=>'center', 'border'=>'left,right,top,bottom', 'border-style'=>'thin','wrap_text'=>true);

$writer->writeSheetHeader('Sheet1', $header, $styles1);    //제목줄 서식 포함
$contents = [];
$x = 0;


$s_pay = 0;
$scnt_total = 0;
$ccnt_total = 0;
$spay_total = 0;
$cpay_total = 0;
$total_pay_total = 0;
$mb_pay2_total = 0;
$mb_pay3_total = 0;
$mb_pay4_total = 0;
$mb_pay5_total = 0;
$mb_pay6_total = 0;
$s_pay_total = 0;


$mb_1_pay = 0;
$mb_2_pay = 0;
$mb_3_pay = 0;
$mb_4_pay = 0;
$mb_5_pay = 0;
$mb_6_pay = 0;

$mb_1_fee = 0;
$mb_2_fee = 0;
$mb_3_fee = 0;
$mb_4_fee = 0;
$mb_5_fee = 0;
$mb_6_fee = 0;


foreach ($result as $field) {


					$mb_1_pay = $field['mb_1_pay'];
					$mb_2_pay = $field['mb_2_pay'];
					$mb_3_pay = $field['mb_3_pay'];
					$mb_4_pay = $field['mb_4_pay'];
					$mb_5_pay = $field['mb_5_pay'];
					$mb_6_pay = $field['mb_6_pay'];

					$total_pay = $field['spay'] + $field['cpay']; // 총 승인금액 (승인-취소)

					// 본사 수수료율
					if($field['mb_2_fee'] > 0) {
						$mb_1_fee = $field['mb_2_fee'] - $field['mb_1_fee']; // 지사 - 본사
					} else if($field['mb_3_fee'] > 0) {
						$mb_1_fee = $field['mb_3_fee'] - $field['mb_1_fee']; // 총판 - 본사
					} else if($field['mb_4_fee'] > 0) {
						$mb_1_fee = $field['mb_4_fee'] - $field['mb_1_fee']; // 대리점 - 본사
					} else if($field['mb_5_fee'] > 0) {
						$mb_1_fee = $field['mb_5_fee'] - $field['mb_1_fee']; // 영업점 - 본사
					} else if($field['mb_6_fee'] > 0) {
						$mb_1_fee = $field['mb_6_fee'] - $field['mb_1_fee']; // 가맹점 - 본사
					}
					// 지사 수수료율
					if($field['mb_3_fee'] > 0) {
						$mb_2_fee = $field['mb_3_fee'] - $field['mb_2_fee']; // 총판 - 지사
					} else if($field['mb_4_fee'] > 0) {
						$mb_2_fee = $field['mb_4_fee'] - $field['mb_2_fee']; // 대리점 - 지사
					} else if($field['mb_5_fee'] > 0) {
						$mb_2_fee = $field['mb_5_fee'] - $field['mb_2_fee']; // 영업점 - 지사
					} else if($field['mb_6_fee'] > 0) {
						$mb_2_fee = $field['mb_6_fee'] - $field['mb_2_fee']; // 가맹점 - 지사
					}
					// 총판 수수료율
					if($field['mb_4_fee'] > 0) {
						$mb_3_fee = $field['mb_4_fee'] - $field['mb_3_fee']; // 대리점 - 총판
					} else if($field['mb_5_fee'] > 0) {
						$mb_3_fee = $field['mb_5_fee'] - $field['mb_3_fee']; // 영업점 - 총판
					} else if($field['mb_6_fee'] > 0) {
						$mb_3_fee = $field['mb_6_fee'] - $field['mb_3_fee']; // 가맹점 - 총판
					}
					// 대리점 수수료율
					if($field['mb_5_fee'] > 0) {
						$mb_4_fee = $field['mb_5_fee'] - $field['mb_4_fee']; // 영업점 - 대리점
					} else if($field['mb_6_fee'] > 0) {
						$mb_4_fee = $field['mb_6_fee'] - $field['mb_4_fee']; // 가맹점 - 대리점
					}
					// 영업점 수수료율
					if($field['mb_6_fee'] > 0) {
						$mb_5_fee = $field['mb_6_fee'] - $field['mb_5_fee']; // 가맹점 - 영업점
					}

					$mb_1_fee = sprintf('%0.3f', $mb_1_fee);
					$mb_2_fee = sprintf('%0.3f', $mb_2_fee);
					$mb_3_fee = sprintf('%0.3f', $mb_3_fee);
					$mb_4_fee = sprintf('%0.3f', $mb_4_fee);
					$mb_5_fee = sprintf('%0.3f', $mb_5_fee);
					/*
					if($field['mb_1_name']) { $mb_1_pay = $mb_1_fee * $total_pay / 100; } else { $mb_1_pay = 0; }
					if($field['mb_2_name']) { $mb_2_pay = $mb_2_fee * $total_pay / 100; } else { $mb_2_pay = 0; }
					if($field['mb_3_name']) { $mb_3_pay = $mb_3_fee * $total_pay / 100; } else { $mb_3_pay = 0; }
					if($field['mb_4_name']) { $mb_4_pay = $mb_4_fee * $total_pay / 100; } else { $mb_4_pay = 0; }
					if($field['mb_5_name']) { $mb_5_pay = $mb_5_fee * $total_pay / 100; } else { $mb_5_pay = 0; }
					if($field['mb_6_name']) {
						$mb_6_pay = $field['mb_6_fee'] * $total_pay / 100;
						$mb_6_pay = $total_pay + $mb_6_pay;
					} else {
						$mb_6_pay = 0;
					}
					*/

					$pg_pay = round($field['total_pay'] * 0.0374);

					$mb_1_pay = floor($mb_1_pay);
					$mb_2_pay = floor($mb_2_pay);
					$mb_3_pay = floor($mb_3_pay);
					$mb_4_pay = floor($mb_4_pay);
					$mb_5_pay = floor($mb_5_pay);
					$mb_6_pay = floor($mb_6_pay);

					$scnt_total = $scnt_total + $field['scnt'];
					$ccnt_total = $ccnt_total + $field['ccnt'];

					$spay_total = $spay_total + $field['spay'];
					$cpay_total = $cpay_total + $field['cpay'];
					$total_pay_total = $spay_total + $cpay_total;

					$pg_total = $pg_total + $pg_pay;

					$mb_pay1_total = $mb_pay1_total + $mb_1_pay;
					$mb_pay2_total = $mb_pay2_total + $mb_2_pay;
					$mb_pay3_total = $mb_pay3_total + $mb_3_pay;
					$mb_pay4_total = $mb_pay4_total + $mb_4_pay;
					$mb_pay5_total = $mb_pay5_total + $mb_5_pay;
					$mb_pay6_total = $mb_pay6_total + $mb_6_pay;
					$mb_6_fee = 100 - $field['mb_6_fee'];
					
					$mb_6_fee = sprintf('%0.3f', $mb_6_fee);
					
					$bank = "";
					if($field['mb_8']) {
						$bank = $field['mb_8']." ".$field['mb_9']." ".$field['mb_10'];
					}
	/*
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
	*/

	$x++;
	$contents[] = $field['dv_tid'];
	$contents[] = $field['mb_6_name'];
	$contents[] = $field['scnt'];
	$contents[] = $field['ccnt'];
	$contents[] = $field['spay'];
	$contents[] = $field['cpay'];
	$contents[] = $total_pay;
	$contents[] = $field['mb_6_fee'];
	$contents[] = $mb_6_pay;
	
	$contents[] = $field['mb_1_name'];
	$contents[] = $field['mb_1_fee'];
	$contents[] = $mb_1_pay;
	
	$contents[] = $field['mb_2_name'];
	$contents[] = $field['mb_2_fee'];
	$contents[] = $mb_2_pay;
	
	$contents[] = $field['mb_3_name'];
	$contents[] = $field['mb_3_fee'];
	$contents[] = $mb_3_pay;
	
	$contents[] = $field['mb_4_name'];
	$contents[] = $field['mb_4_fee'];
	$contents[] = $mb_4_pay;
	
	$contents[] = $field['mb_5_name'];
	$contents[] = $field['mb_5_fee'];
	$contents[] = $mb_5_pay;
	
	
	$contents[] = $bank;
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