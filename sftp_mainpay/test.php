<?php

function get_dir_list($dirname, $skin_path=SKIN_ROOT_PATH){
    $result_array = array();
    //$dirname  = "../".$skin_path.'/'.$skin.'/'; 
    $handle = opendir($dirname);
    while ($file = readdir($handle)) {
        if($file == '.'||$file == '..') continue;
        if (is_dir($dirname.$file)) $result_array[$file] = $file;
    }
    closedir($handle);
    sort($result_array);
    return $result_array;
}

print_r(get_dir_list("./"));
echo "<br>";

exit;




	if(!$fr_date) { $fr_date = date("Ymd", strtotime('-1 day')); }
	echo $fr_date;

exit;

$pay1 = -12;
$pay2 = 12;
echo abs($pay1);
echo abs($pay2);

exit;




		$row['pay_datetime'] = "2024-05-18 10:33:04";
		$pay_datetime = date("ymd", strtotime($row['pay_datetime']));
		echo $pay_datetime;
		exit;


$cardcode = array("01","02","03","04","05","07","08","12","31");

for($i = 0; $i < count($cardcode); $i++)
{
    echo $cardcode[$i], "\n";
}

exit;
	include_once('./_common.php');
	$mb_id = "1715921151";

	$mb = get_member($mb_id);

	if(!$mb['mb_id']) {
		alert("아이디 오류입니다.");
	}

	$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT']; // 파일 저장경로
	$dateymd = DATE("ymd"); // 오늘날짜
	$openmarket = "1234567890"; // 오픈마켓 사업자등록번호
	$mb_company_number = preg_replace('/[^0-9]*/s', '', $mb['mb_7']); // 사업자등록번호
	$mb_tel = preg_replace('/[^0-9]*/s', '', $mb['mb_tel']); // 전화번호

	$t_count = 500; // 총 전송 건수
	$n_count = 400; // 총 신규 건수
	$c_count = 50; // 총 변경 건수
	$d_count = 50; // 총 해지 건수

	$array = array("01","02","03","04","05","07","08","12","31");

	foreach($array as $cardcode) {
		$content = "HD"; // 레코드 구분 HD 고정
		$content .= $dateymd; // 파일생성일자
		$content .= str_pad($space, 492); // 공백 492

		$content .= "\n\n"; // 줄바꿈

		$content .= "RD"; // 레코드 구분 HD 고정
		$content .= "00"; // 등록구분 신규:00, 해지:01, 변경:02
		$content .= $openmarket; // 오픈마켓 사업자번호
		$content .= $cardcode; //카드사코드
		$content .= $mb_company_number; // 사업자등록번호(하위몰)
		$content .= str_pad($mb_company_style, 20); //업종명(하위몰) 20 한글10자
		$content .= str_pad($mb['mb_nick'], 40); //회사명(하위몰) 40 한글20자
		$content .= str_pad($mb['mb_name'], 30); //대표자명(하위몰) 30 한글15자
		$content .= str_pad($mb_tel, 14); //전화번호(하위몰)
		$content .= str_pad($mb['mb_email'], 30); //이메일(하위몰)
		$content .= str_pad($mb['mb_birth'], 200); //웹사이트URL(하위몰)
		$content .= $dateymd; //정보등록일
		$content .= $mbrno; //가맹점번호(MbrNo)
		$content .= str_pad($space, 28); //공백 28

		$content .= "\n"; // 줄바꿈

		$content .= "TR"; // 레코드 구분 TR 고정
		$content .= sprintf('%010d',$t_count); //총 전송 건수 10자리
		$content .= sprintf('%010d',$n_count); //총 신규 건수 10자리
		$content .= sprintf('%010d',$c_count); //총 변경 건수 10자리
		$content .= sprintf('%010d',$d_count); //총 해지 건수 10자리
		$content .= str_pad($space, 458); //공백 458

		$content .= "\n"; // 줄바꿈
		echo nl2br($content);
	}









	exit;

	$t_count = 50;
	echo sprintf('%010d',$t_count);


	$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];

	//req html 페이지로부터 전달받은 데이터 저장
	$content = "TEST";

	$dateymd = DATE("ymd");

	//내용을 저장할 파일명
	$fileName = "1234569999_REQUEST_INFO.".$dateymd;

	$folder = $DOCUMENT_ROOT."/".$dateymd."/";

	@mkdir($folder, G5_DIR_PERMISSION);
	@chmod($folder, G5_DIR_PERMISSION);
	echo $folder;