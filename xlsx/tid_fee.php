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
$f_name = "수수료 관리";

$xlsx_sql = stripslashes($xlsx_sql);
$result = sql_query($xlsx_sql);

$header = [];
$widths = [];
$halign = [];

// $header 제목명은 모두 달라야 됨
$header["번호"] = 'integer';

$header["본사명"] = 'string';
$header["본사수수료"] = 'string';
$header["지사명"] = 'string';
$header["지사수수료"] = 'string';
$header["총판명"] = 'string';
$header["총판수수료"] = 'string';
$header["대리점명"] = 'string';
$header["대리점수수료"] = 'string';
$header["영업점명"] = 'string';
$header["영업점수수료"] = 'string';
$header["가맹점명"] = 'string';
$header["가맹점수수료"] = 'string';

$header["TID"] = 'string';
$header["차액정산 MBR"] = 'string';
$header["PG"] = 'string';
$header["단말기/수기"] = 'string';
$header["인증/비인증"] = 'string';
$header["등록일시"] = 'string';

//셀너비
$widths[] = 5;
$widths[] = 20; // 본사
$widths[] = 10;
$widths[] = 20; // 지사
$widths[] = 10;
$widths[] = 20; // 총판
$widths[] = 10;
$widths[] = 20; // 대리점
$widths[] = 10;
$widths[] = 20; // 영업점
$widths[] = 10;
$widths[] = 30; // 가맹점
$widths[] = 10;
$widths[] = 15; // TID
$widths[] = 15; // MBR
$widths[] = 10; // PG
$widths[] = 10; // 단말기/수기
$widths[] = 10; // 인증/비인증
$widths[] = 23; // 등록일시

$writer = new XLSXWriter();

$styles1 = array('font'=>'맑은 고딕','font-size'=>10,'font-style'=>'bold','color'=>'#fff', 'fill'=>'#777', 'halign'=>'left', 'valign'=>'center', 'border'=>'left,right,top,bottom', 'border-style'=>'thin', 'widths'=>$widths);
$styles2 = array( 'font'=>'맑은 고딕','font-size'=>11, 'halign'=>'left', 'valign'=>'center', 'border'=>'left,right,top,bottom', 'border-style'=>'thin','wrap_text'=>true);

$writer->writeSheetHeader('Sheet1', $header, $styles1);    //제목줄 서식 포함
$contents = [];
$x = 0;
foreach ($result as $field) {

	if($field['dv_pg'] == 0) {
		$dv_pgs = "코페이";
	} else if($field['dv_pg'] == 1) {
		$dv_pgs = "다날";
	} else if($field['dv_pg'] == 2) {
		$dv_pgs = "광원";
	} else if($field['dv_pg'] == 3) {
		$dv_pgs = "웰컴";
	}

	if($field['dv_type'] == 1) {
		$dv_types = "단말기";
	} else {
		$dv_types = "수기";
	}
	if($field['dv_certi'] == 1) {
		$dv_certis = "인증";
	} else {
		$dv_certis = "비인증";
	}


	// 본사 수수료율
	if($row['mb_2_fee'] > 0) {
		$mb_1_fee = $row['mb_2_fee'] - $row['mb_1_fee']; // 지사 - 본사
	} else if($row['mb_3_fee'] > 0) {
		$mb_1_fee = $row['mb_3_fee'] - $row['mb_1_fee']; // 총판 - 본사
	} else if($row['mb_4_fee'] > 0) {
		$mb_1_fee = $row['mb_4_fee'] - $row['mb_1_fee']; // 대리점 - 본사
	} else if($row['mb_5_fee'] > 0) {
		$mb_1_fee = $row['mb_5_fee'] - $row['mb_1_fee']; // 영업점 - 본사
	} else if($row['mb_6_fee'] > 0) {
		$mb_1_fee = $row['mb_6_fee'] - $row['mb_1_fee']; // 가맹점 - 본사
	}

	// 지사 수수료율
	if($row['mb_3_fee'] > 0) {
		$mb_2_fee = $row['mb_3_fee'] - $row['mb_2_fee']; // 총판 - 지사
	} else if($row['mb_4_fee'] > 0) {
		$mb_2_fee = $row['mb_4_fee'] - $row['mb_2_fee']; // 대리점 - 지사
	} else if($row['mb_5_fee'] > 0) {
		$mb_2_fee = $row['mb_5_fee'] - $row['mb_2_fee']; // 영업점 - 지사
	} else if($row['mb_6_fee'] > 0) {
		$mb_2_fee = $row['mb_6_fee'] - $row['mb_2_fee']; // 가맹점 - 지사
	}

	// 총판 수수료율
	if($row['mb_4_fee'] > 0) {
		$mb_3_fee = $row['mb_4_fee'] - $row['mb_3_fee']; // 대리점 - 총판
	} else if($row['mb_5_fee'] > 0) {
		$mb_3_fee = $row['mb_5_fee'] - $row['mb_3_fee']; // 영업점 - 총판
	} else if($row['mb_6_fee'] > 0) {
		$mb_3_fee = $row['mb_6_fee'] - $row['mb_3_fee']; // 가맹점 - 총판
	}

	// 대리점 수수료율
	if($row['mb_5_fee'] > 0) {
		$mb_4_fee = $row['mb_5_fee'] - $row['mb_4_fee']; // 영업점 - 대리점
	} else if($row['mb_6_fee'] > 0) {
		$mb_4_fee = $row['mb_6_fee'] - $row['mb_4_fee']; // 가맹점 - 대리점
	}

	// 영업점 수수료율
	if($row['mb_6_fee'] > 0) {
		$mb_5_fee = $row['mb_6_fee'] - $row['mb_5_fee']; // 가맹점 - 영업점
	}
	$mb_6_fee = 100 - $row['mb_6_fee'];


	if($row['mb_1_name']) { $mb_1_fee = sprintf('%0.4f', $mb_1_fee); } else { $mb_1_fee = ""; }
	if($row['mb_2_name']) { $mb_2_fee = sprintf('%0.4f', $mb_2_fee); } else { $mb_2_fee = ""; }
	if($row['mb_3_name']) { $mb_3_fee = sprintf('%0.4f', $mb_3_fee); } else { $mb_3_fee = ""; }
	if($row['mb_4_name']) { $mb_4_fee = sprintf('%0.4f', $mb_4_fee); } else { $mb_4_fee = ""; }
	if($row['mb_5_name']) { $mb_5_fee = sprintf('%0.4f', $mb_5_fee); } else { $mb_5_fee = ""; }
	if($row['mb_6_name']) { $mb_6_fee = sprintf('%0.4f', $mb_6_fee); } else { $mb_6_fee = ""; }

	if($row['mb_1_name']) { $mb_1_fee = $mb_1_fee; } else { $mb_1_fee = ""; }
	if($row['mb_2_name']) { $mb_2_fee = $mb_2_fee; } else { $mb_2_fee = ""; }
	if($row['mb_3_name']) { $mb_3_fee = $mb_3_fee; } else { $mb_3_fee = ""; }
	if($row['mb_4_name']) { $mb_4_fee = $mb_4_fee; } else { $mb_4_fee = ""; }
	if($row['mb_5_name']) { $mb_5_fee = $mb_5_fee; } else { $mb_5_fee = ""; }
	if($row['mb_6_name']) { $mb_6_fee = $mb_6_fee; } else { $mb_6_fee = ""; }



	/*


					<td><?php echo $num; ?></td>

					<?php if($member['mb_level'] >= 8) { ?>
					<td class="td_name"><?php if($row['mb_1_name']) { echo $row['mb_1_name']; } ?></td>
					<td><?php if($row['mb_1_name']) { echo "<span class='fee1'>".$row['mb_1_fee']."</span><span class='fee2'>".$mb_1_fee."</span>"; } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 7) { ?>
					<td class="td_name"><?php if($row['mb_2_name']) { echo $row['mb_2_name']; } ?></td>
					<td><?php if($row['mb_2_name']) { echo "<span class='fee1'>".$row['mb_2_fee']."</span><span class='fee2'>".$mb_2_fee."</span>"; } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 6) { ?>
					<td class="td_name"><?php if($row['mb_3_name']) { echo $row['mb_3_name']; } ?></td>
					<td><?php if($row['mb_3_name']) { echo "<span class='fee1'>".$row['mb_3_fee']."</span><span class='fee2'>".$mb_3_fee."</span>"; } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 5) { ?>
					<td class="td_name"><?php if($row['mb_4_name']) { echo $row['mb_4_name']; } ?></td>
					<td><?php if($row['mb_4_name']) { echo "<span class='fee1'>".$row['mb_4_fee']."</span><span class='fee2'>".$mb_4_fee."</span>"; } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 4) { ?>
					<td class="td_name"><?php if($row['mb_5_name']) { echo $row['mb_5_name']; } ?></td>
					<td><?php if($row['mb_5_name']) { echo "<span class='fee1'>".$row['mb_5_fee']."</span><span class='fee2'>".$mb_5_fee."</span>"; } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 3) { ?>
					<td class="td_name"><?php if($row['mb_6_name']) { echo $row['mb_6_name']; } ?></td>
					<td><?php if($row['mb_6_name']) { echo "<span class='fee1'>".$row['mb_6_fee']."</span><span class='fee2'>".$mb_6_fee."</span>"; } ?></td>
					<?php } ?>

					<td><?php echo $row['dv_tid']; ?></td>
					<td><?php echo $dv_pgs; ?></td>
					<td><?php echo $dv_types; ?></td>
					<td><?php echo $dv_certis; ?></td>
					<td><?php echo $row['datetime']; ?></td>

	*/

	$x++;
	$contents[] = $x;
	$contents[] = $field['mb_1_name'];
	$contents[] = $field['mb_1_fee'];
	$contents[] = $field['mb_2_name'];
	$contents[] = $field['mb_2_fee'];
	$contents[] = $field['mb_3_name'];
	$contents[] = $field['mb_3_fee'];
	$contents[] = $field['mb_4_name'];
	$contents[] = $field['mb_4_fee'];
	$contents[] = $field['mb_5_name'];
	$contents[] = $field['mb_5_fee'];
	$contents[] = $field['mb_6_name'];
	$contents[] = $field['mb_6_fee'];


	$contents[] = $field['dv_tid'];
	$contents[] = $field['sftp_mbrno'];
	$contents[] = $dv_pgs;
	$contents[] = $dv_types;
	$contents[] = $dv_certis;
	$contents[] = $field['datetime'];

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