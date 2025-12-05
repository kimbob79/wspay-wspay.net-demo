<?php
	include_once('./_common.php');

	$date = $_GET['date'];
	$fr_dates = date("Y-m-d", strtotime($date));

	if(!$date) {
		alert_close('날짜가 넘어오지 않았습니다.');
	}

	$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT']; // 파일 저장경로
	$dateymd = DATE("ymd"); // 오늘날짜
//	$company_number = "1234567890"; // 가맹점 사업자번호

	$folder = $DOCUMENT_ROOT."/sftp_mainpay/".$dateymd."/";

	@mkdir($folder, G5_DIR_PERMISSION);
	@chmod($folder, G5_DIR_PERMISSION);

	$sql .= " select * from g5_sftp_member order by datetime desc ";
	$result = sql_query($sql);

	for ($i=0; $row=sql_fetch_array($result); $i++) {

		// 하위사업자번호
		$sm_bnumber = preg_replace('/[^0-9]*/s', '', $row['sm_bnumber']); // 사업자등록번호

		//내용을 저장할 파일명
		$fileName = $sm_bnumber."_REQUEST.".$dateymd;

		$folder = $DOCUMENT_ROOT."/sftp_mainpay/".$dateymd."/";

		//파일 열기 
		$fp = fopen($folder.$fileName, 'w');

		$content = "HD"; // 레코드 구분 HD 고정
		$content .= $dateymd; // 파일생성일자
		$content .= str_pad($space, 192); // 공백 192

		$sql_data = " select * from g5_payment where sftp_mbrno = '".$row['sm_mbrno']."' and (pay_datetime BETWEEN '".$fr_dates." 00:00:00' and '".$fr_dates." 23:59:59') order by datetime desc ";
		$result_data = sql_query($sql_data);

		$total_pay = 0;

		for ($k=0; $row_data=sql_fetch_array($result_data); $k++) {

			// 승인취소
			if($row_data['pay_type'] == "Y") {
				$pay_type = 0;
				$pay_type2 = "CA";
			} else if($row_data['pay_type'] == "N") {
				$pay_type = 1;
				$pay_type2 = "CC";
			}

			// 승일일자
			$pay_datetime = date("ymd", strtotime($row_data['pay_datetime']));

			$mbrno = $row_data['sftp_mbrno'];

			// 결제금액
			$pay = abs($row_data['pay']);

			// 금액 합계
			$total_pay = $total_pay + $row_data['pay'];

			$content .= "\n"; // 줄바꿈

			$content .= "DT"; // 레코드 구분 HD 고정
			$content .= $pay_type; // 매입취소구분 승인:0, 취소:1
			$content .= $pay_datetime; // 거래일자
			$content .= $company_number; //중간하위사업자번호
			$content .= $sm_bnumber; // 최종하위사업자번호
			$content .= $mbrno; //가맹점번호(MbrNo)
			$content .= str_pad($row_data['trxid'], 20); // PG거래번호
			$content .= $pay_type2; // 전문구분
			$content .= str_pad($row_data['trackId'], 40); // 가맹점 주문번호
			$content .= str_pad($pay, 15); // 하위사업자 매출액
			$content .= str_pad($pay, 15); // 원거래 매입금액
			$content .= str_pad($space, 30); // 가맹점 검증값
			$content .= str_pad($space, 43); //공백 28
		}
		$content .= "\n"; // 줄바꿈

		$content .= "TR"; // 레코드 구분 TR 고정
		$content .= sprintf('%07d',$k); //총 건수 합계 7자리
		$content .= sprintf('%08d',$total_pay); //총 매출액 합계 18자리
		$content .= str_pad($space, 150); //공백 458


		//파일 쓰기
		$fw = fwrite($fp, $content);
	}

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