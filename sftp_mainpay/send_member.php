<?php
	include_once('./_common.php');

	$row = sql_fetch(" select * from g5_sftp_member where sm_mbrno = '{$_GET['mbr']}' ");

	if(!$row['sm_mbrno']) {
		alert_close("존재하지 않는 MBR 입니다.");
	}

	$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT']; // 파일 저장경로
	$dateymd = DATE("ymd"); // 오늘날짜
	$openmarket = $row['sm_openmarket']; // 오픈마켓 사업자등록번호
	$mb_company_number = preg_replace('/[^0-9]*/s', '', $row['sm_bnumber']); // 사업자등록번호
	$sm_tel = preg_replace('/[^0-9]*/s', '', $row['sm_tel']); // 전화번호
	$makedate = date("ymd", strtotime($row['datetime']));

	/*
	$t_count = $ㅁㅁㅁㅁ // 총 전송 건수
	$n_count = $ㅁㅁㅁㅁ // 총 신규 건수
	$c_count = $ㅁㅁㅁㅁ // 총 변경 건수
	$d_count = $ㅁㅁㅁㅁ // 총 해지 건수
	*/

	/*
	01	비씨
	02	신한
	03	삼성
	04	현대
	05	롯데
	07	국민
	08	하나
	12	농협
	31	우리
	*/


	//내용을 저장할 파일명
	$fileName = $mb_company_number."_REQUEST_INFO.".$dateymd;

	$folder = $DOCUMENT_ROOT."/sftp_mainpay/".$dateymd."/";

	@mkdir($folder, G5_DIR_PERMISSION);
	@chmod($folder, G5_DIR_PERMISSION);

	//파일 열기 
	$fp = fopen($folder.$fileName, 'w');

	$t_count = 0; // 총 전송 건수
	$n_count = 0; // 총 신규 건수
	$c_count = 0; // 총 변경 건수
	$d_count = 0; // 총 해지 건수

	$cardcode = array("01","02","03","04","05","07","08","12","31");

	$content = "HD"; // 레코드 구분 HD 고정
	$content .= $dateymd; // 파일생성일자
	$content .= str_pad($space, 492); // 공백 492
	$content .= "\n"; // 줄바꿈

	for($i=0; $i<count($cardcode); $i++) {

		$t_count++;

		if($row['sm_type'] == "00") { $n_count++; }
		if($row['sm_type'] == "01") { $c_count++; }
		if($row['sm_type'] == "02") { $d_count++; }

		$cardcodes = $cardcode[$i];



		$content .= "RD"; // 레코드 구분 HD 고정
		$content .= $row['sm_type']; // 등록구분 신규:00, 해지:01, 변경:02
		$content .= $openmarket; // 오픈마켓 사업자번호
		$content .= $cardcodes; //카드사코드
		$content .= $mb_company_number; // 사업자등록번호(하위몰)
		$content .= str_pad($row['sm_btype'], 20); //업종명(하위몰) 20 한글10자
		$content .= str_pad($row['sm_bname'], 40); //회사명(하위몰) 40 한글20자
		$content .= str_pad($row['sm_ceo'], 30); //대표자명(하위몰) 30 한글15자
		$content .= str_pad($sm_tel, 14); //전화번호(하위몰)
		$content .= str_pad($row['sm_email'], 30); //이메일(하위몰)
		$content .= str_pad($row['sm_website'], 200); //웹사이트URL(하위몰)
		$content .= $makedate; //정보등록일
		$content .= $row['sm_mbrno']; //가맹점번호(MbrNo)
		$content .= str_pad($space, 28); //공백 28

		$content .= "\n"; // 줄바꿈

	}

	$content .= "TR"; // 레코드 구분 TR 고정
	$content .= sprintf('%010d',$t_count); //총 전송 건수 10자리
	$content .= sprintf('%010d',$n_count); //총 신규 건수 10자리
	$content .= sprintf('%010d',$c_count); //총 변경 건수 10자리
	$content .= sprintf('%010d',$d_count); //총 해지 건수 10자리
	$content .= str_pad($space, 458); //공백 458

	$content .= "\n"; // 줄바꿈

	//파일 쓰기
	$fw = fwrite($fp, $content);

	//파일 쓰기 성공 여부 확인
	if($fw == false){
		echo '파일 쓰기에 실패하였습니다.';
	}
	else {
		echo '파일 쓰기 성공!!';
	}

	//파일 종료
	fclose($fp);
?>