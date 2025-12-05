<?php
include_once('./_common.php');

if(!$is_admin) {
	alert("잘못된 접근");
}

//print "<pre>"; print_r($_POST); print "</pre>";  exit;

$xlsx_sql    = isset($_POST['xlsx_sql']) ? trim($_POST['xlsx_sql']) : '';
$p    = isset($_POST['p']) ? trim($_POST['p']) : '';

set_include_path( get_include_path().PATH_SEPARATOR."..");
include_once("xlsxwriter.class.php");
$f_name = "회원 관리";

$xlsx_sql = stripslashes($xlsx_sql);
$result = sql_query($xlsx_sql);

$header = [];
$widths = [];
$halign = [];

// $header 제목명은 모두 달라야 됨
$header["번호"] = 'integer';

$header["그룹"] = 'string';
$header["아이디"] = 'string';
$header["상호명"] = 'string';
$header["수수료"] = 'string';
$header["TID"] = 'string';
$header["사업자등록번호"] = 'string';
$header["대표자명"] = 'string';
$header["전화번호"] = 'string';
$header["휴대전화번호"] = 'string';
$header["이메일"] = 'string';
$header["주소"] = 'string';
$header["은행"] = 'string';
$header["계좌"] = 'string';
$header["예금주"] = 'string';
$header["등록일시"] = 'string';

//셀너비
$widths[] = 5;
$widths[] = 10; // 그룹
$widths[] = 20; // 아이디
$widths[] = 30; // 상호명
$widths[] = 10; // 수수료
$widths[] = 10; // TID
$widths[] = 20; // 사업자등록번호
$widths[] = 20; // 대표자명
$widths[] = 20; // 전화번호
$widths[] = 15; // 휴대전화번호
$widths[] = 25; // 이메일
$widths[] = 40; // 주소
$widths[] = 50; // 가맹점
$widths[] = 10; // 계좌정보
$widths[] = 20; // 계좌정보
$widths[] = 10; // 계좌정보
$widths[] = 15; // 등록일시

$writer = new XLSXWriter();

$styles1 = array('font'=>'맑은 고딕','font-size'=>10,'font-style'=>'bold','color'=>'#fff', 'fill'=>'#777', 'halign'=>'left', 'valign'=>'center', 'border'=>'left,right,top,bottom', 'border-style'=>'thin', 'widths'=>$widths);
$styles2 = array( 'font'=>'맑은 고딕','font-size'=>11, 'halign'=>'left', 'valign'=>'center', 'border'=>'left,right,top,bottom', 'border-style'=>'thin','wrap_text'=>true);

$writer->writeSheetHeader('Sheet1', $header, $styles1);    //제목줄 서식 포함
$contents = [];
$x = 0;
foreach ($result as $field) {


	if($field['mb_level'] == "8") {
		$title_s = "본사";
	} else if($field['mb_level'] == "7") {
		$title_s = "지사";
	} else if($field['mb_level'] == "6") {
		$title_s = "총판";
	} else if($field['mb_level'] == "5") {
		$title_s = "대리점";
	} else if($field['mb_level'] == "4") {
		$title_s = "영업점";
	} else if($field['mb_level'] == "3") {
		$title_s = "가맹점";
	} else if($field['mb_level'] == "1") { // 삭제회원
		if($field['mb_sex'] == "8") {
			$title_s = "본사";
		} else if($field['mb_sex'] == "7") {
			$title_s = "지사";
		} else if($field['mb_sex'] == "6") {
			$title_s = "총판";
		} else if($field['mb_sex'] == "5") {
			$title_s = "대리점";
		} else if($field['mb_sex'] == "4") {
			$title_s = "영업점";
		} else if($field['mb_sex'] == "3") {
			$title_s = "가맹점";
		}
	}

	$mb_homepage = sprintf('%0.2f', $field['mb_homepage'])."%";
	$addr = $field['mb_zip1'].$field['mb_zip2']." ".$field['mb_addr1']." ".$field['mb_addr2'];
	$bank1 = $field['mb_8'];
	$bank2 = $field['mb_9'];
	$bank3 = $field['mb_10'];

	$x++;
	$contents[] = $x;
	$contents[] = $title_s;
	$contents[] = $field['mb_id'];
	$contents[] = $field['mb_nick'];
	$contents[] = $mb_homepage;
	$contents[] = $field['dv_tid'];
	$contents[] = $field['mb_7'];
	$contents[] = $field['mb_name'];
	$contents[] = $field['mb_tel'];
	$contents[] = $field['mb_hp'];
	$contents[] = $field['mb_email'];
	$contents[] = $addr;
	$contents[] = $bank1;
	$contents[] = $bank2;
	$contents[] = $bank3;
	$contents[] = $field['mb_datetime'];

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