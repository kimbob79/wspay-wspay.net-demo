<?php
	error_reporting(E_ERROR | E_PARSE);
	ini_set('display_errors', 1);

	if(!$is_admin) { alert("관리자만 접속 가능합니다."); }
	include_once(G5_LIB_PATH.'/register.lib.php');

//	print "<pre>"; print_r($_POST); print "</pre>"; exit;

	/*
	if($_POST['mb_app'] == 0 || $_POST['mb_app'] == 1) {
		$sql = " update g5_member set mb_app = '{$_POST['mb_app']}' where mb_id = '{$_POST['mb_id']}' ";
		sql_query($sql);
		goto_url("./?p=profile&mb_id=".$_POST['mb_id']);
		exit;
	}
	*/

	$w    = isset($_POST['w']) ? trim($_POST['w']) : '';

	$mb_level    = isset($_POST['mb_level']) ? trim($_POST['mb_level']) : '';
	if(!$is_admin) {
		if($member['mb_level'] <= $mb_level) { alert("권한이 없습니다."); }
	}

	$mb_id    = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : ''; // 아이디
	$mb_adult    = isset($_POST['mb_adult']) ? trim($_POST['mb_adult']) : ''; // 차액정산
	$mb_dupinfo    = isset($_POST['mb_dupinfo']) ? trim($_POST['mb_dupinfo']) : ''; // 정산주기
	$mb_settle_gbn    = isset($_POST['mb_settle_gbn']) ? trim($_POST['mb_settle_gbn']) : 'N'; // 재정산 여부


	if($mb_level == 8) { // 본사
		$mb_1 = $mb_id;
	} if($mb_level == 7) { // 지사
		$mb_1 = isset($_POST['mb_1']) ? trim($_POST['mb_1']) : '';
		$mb_2 = $mb_id;
	} if($mb_level == 6) { // 총판
		$mb_1 = isset($_POST['mb_1']) ? trim($_POST['mb_1']) : '';
		$mb_2 = isset($_POST['mb_2']) ? trim($_POST['mb_2']) : '';
		$mb_3 = $mb_id;
	} if($mb_level == 5) { // 대리점
		$mb_1 = isset($_POST['mb_1']) ? trim($_POST['mb_1']) : '';
		$mb_2 = isset($_POST['mb_2']) ? trim($_POST['mb_2']) : '';
		$mb_3 = isset($_POST['mb_3']) ? trim($_POST['mb_3']) : '';
		$mb_4 = $mb_id;
	} if($mb_level == 4) { // 영업점
		$mb_1 = isset($_POST['mb_1']) ? trim($_POST['mb_1']) : '';
		$mb_2 = isset($_POST['mb_2']) ? trim($_POST['mb_2']) : '';
		$mb_3 = isset($_POST['mb_3']) ? trim($_POST['mb_3']) : '';
		$mb_4 = isset($_POST['mb_4']) ? trim($_POST['mb_4']) : '';
		$mb_5 = $mb_id;
	} if($mb_level == 3) { // 가맹점
		$mb_1 = isset($_POST['mb_1']) ? trim($_POST['mb_1']) : '';
		$mb_2 = isset($_POST['mb_2']) ? trim($_POST['mb_2']) : '';
		$mb_3 = isset($_POST['mb_3']) ? trim($_POST['mb_3']) : '';
		$mb_4 = isset($_POST['mb_4']) ? trim($_POST['mb_4']) : '';
		$mb_5 = isset($_POST['mb_5']) ? trim($_POST['mb_5']) : '';
		$mb_6 = $mb_id;
	}


	$mb_password    = isset($_POST['mb_password']) ? trim($_POST['mb_password']) : ''; // 비밀번호
	$mb_homepage    = isset($_POST['mb_homepage']) ? trim($_POST['mb_homepage']) : ''; // 비밀번호

	$mb_nick        = isset($_POST['mb_nick']) ? trim($_POST['mb_nick']) : ''; // 상호

	$mb_company_number1        = isset($_POST['mb_company_number1']) ? trim($_POST['mb_company_number1']) : '';
	$mb_company_number2        = isset($_POST['mb_company_number2']) ? trim($_POST['mb_company_number2']) : '';
	$mb_company_number3        = isset($_POST['mb_company_number3']) ? trim($_POST['mb_company_number3']) : '';
	$mb_7 = $mb_company_number1."-".$mb_company_number2."-".$mb_company_number3; // 사업자등록번호

	$mb_name        = isset($_POST['mb_name']) ? trim($_POST['mb_name']) : ''; // 대표자명


	$mb_tel1        = isset($_POST['mb_tel1']) ? trim($_POST['mb_tel1']) : '';
	$mb_tel2        = isset($_POST['mb_tel2']) ? trim($_POST['mb_tel2']) : '';
	$mb_tel3        = isset($_POST['mb_tel3']) ? trim($_POST['mb_tel3']) : '';
	$mb_tel = $mb_tel1."-".$mb_tel2."-".$mb_tel3; // 전화번호

	$mb_hp1        = isset($_POST['mb_hp1']) ? trim($_POST['mb_hp1']) : '';
	$mb_hp2        = isset($_POST['mb_hp2']) ? trim($_POST['mb_hp2']) : '';
	$mb_hp3        = isset($_POST['mb_hp3']) ? trim($_POST['mb_hp3']) : '';
	$mb_hp = $mb_hp1."-".$mb_hp2."-".$mb_hp3; // 휴대전화번호

	$mb_email       = isset($_POST['mb_email']) ? trim($_POST['mb_email']) : ''; // 이메일

	// 주소
	$mb_zip1        = isset($_POST['mb_zip'])           ? substr(trim($_POST['mb_zip']), 0, 3) : "";
	$mb_zip2        = isset($_POST['mb_zip'])           ? substr(trim($_POST['mb_zip']), 3)    : "";
	$mb_addr1       = isset($_POST['mb_addr1'])         ? trim($_POST['mb_addr1'])       : "";
	$mb_addr2       = isset($_POST['mb_addr2'])         ? trim($_POST['mb_addr2'])       : "";
	$mb_addr3       = isset($_POST['mb_addr3'])         ? trim($_POST['mb_addr3'])       : "";
	$mb_addr_jibeon = isset($_POST['mb_addr_jibeon'])   ? trim($_POST['mb_addr_jibeon']) : "";

	$mb_memo_call        = isset($_POST['mb_memo_call']) ? trim($_POST['mb_memo_call']) : ''; // 계좌메모

	$mb_8        = isset($_POST['mb_8']) ? trim($_POST['mb_8']) : ''; // 은행명
	$mb_9        = isset($_POST['mb_9']) ? trim($_POST['mb_9']) : ''; // 계좌번호
	$mb_10        = isset($_POST['mb_10']) ? trim($_POST['mb_10']) : ''; // 예금주명


	$mb_name        = clean_xss_tags($mb_name);
	$mb_email       = get_email_address($mb_email);
	$mb_homepage    = clean_xss_tags($mb_homepage);
	$mb_tel         = clean_xss_tags($mb_tel);
	$mb_hp         = clean_xss_tags($mb_hp);
	$mb_zip1        = preg_replace('/[^0-9]/', '', $mb_zip1);
	$mb_zip2        = preg_replace('/[^0-9]/', '', $mb_zip2);
	$mb_addr1       = clean_xss_tags($mb_addr1);
	$mb_addr2       = clean_xss_tags($mb_addr2);
	$mb_addr3       = clean_xss_tags($mb_addr3);
	/*
	if ($msg = valid_mb_hp($mb_hp)) {
		alert($msg, "", true, true);
	}
	*/

	// 디렉토리가 없다면 생성합니다. (퍼미션도 변경하구요.)
	@mkdir(G5_DATA_PATH.'/member/'.$mb_id, G5_DIR_PERMISSION);
	@chmod(G5_DATA_PATH.'/member/'.$mb_id, G5_DIR_PERMISSION);

	$chars_array = array_merge(range(0,9), range('a','z'), range('A','Z'));

	// 가변 파일 업로드
	$file_upload_msg = '';
	$upload = array();

	if(isset($_FILES['bf_file']['name']) && is_array($_FILES['bf_file']['name'])) {
		for ($i=0; $i<count($_FILES['bf_file']['name']); $i++) {
			$upload[$i]['file']     = '';
			$upload[$i]['source']   = '';
			$upload[$i]['filesize'] = 0;
			$upload[$i]['image']    = array();
			$upload[$i]['image'][0] = 0;
			$upload[$i]['image'][1] = 0;
			$upload[$i]['image'][2] = 0;
			$upload[$i]['fileurl'] = '';
			$upload[$i]['thumburl'] = '';
			$upload[$i]['storage'] = '';

			// 삭제에 체크가 되어있다면 파일을 삭제합니다.
			if (isset($_POST['bf_file_del'][$i]) && $_POST['bf_file_del'][$i]) {
				$upload[$i]['del_check'] = true;

				$row = sql_fetch(" select * from g5_member_file where mb_id = '{$mb_id}' and bf_no = '{$i}' ");

				$delete_file = run_replace('delete_file_path', G5_DATA_PATH.'/member/'.$mb_id.'/'.str_replace('../', '', $row['bf_file']), $row);
				if( file_exists($delete_file) ){
					@unlink($delete_file);
				}
				// 썸네일삭제
				if(preg_match("./\.({$config['cf_image_extension']})$/i", $row['bf_file'])) {
					delete_board_thumbnail($bo_table, $row['bf_file']);
				}
			}
			else
				$upload[$i]['del_check'] = false;

			$tmp_file  = $_FILES['bf_file']['tmp_name'][$i];
			$filesize  = $_FILES['bf_file']['size'][$i];
			$filename  = $_FILES['bf_file']['name'][$i];
			$filename  = get_safe_filename($filename);

			// 서버에 설정된 값보다 큰파일을 업로드 한다면
			if ($filename) {
				if ($_FILES['bf_file']['error'][$i] == 1) {
					$file_upload_msg .= '\"'.$filename.'\" 파일의 용량이 서버에 설정('.$upload_max_filesize.')된 값보다 크므로 업로드 할 수 없습니다.\\n';
					continue;
				}
				else if ($_FILES['bf_file']['error'][$i] != 0) {
					$file_upload_msg .= '\"'.$filename.'\" 파일이 정상적으로 업로드 되지 않았습니다.\\n';
					continue;
				}
			}

			if (is_uploaded_file($tmp_file)) {
				/*
				//=================================================================\
				// 090714
				// 이미지나 플래시 파일에 악성코드를 심어 업로드 하는 경우를 방지
				// 에러메세지는 출력하지 않는다.
				//-----------------------------------------------------------------
				$timg = @getimagesize($tmp_file);
				// image type
				if ( preg_match("./\.({$config['cf_image_extension']})$/i", $filename) ||
					 preg_match("./\.({$config['cf_flash_extension']})$/i", $filename) ) {
					if ($timg['2'] < 1 || $timg['2'] > 18)
						continue;
				}
				//=================================================================
				*/

				$upload[$i]['image'] = $timg;

				// 4.00.11 - 글답변에서 파일 업로드시 원글의 파일이 삭제되는 오류를 수정
				if ($w == 'u') {
					// 존재하는 파일이 있다면 삭제합니다.
					$row = sql_fetch(" select * from g5_member_file where mb_id = '$mb_id' and bf_no = '$i' ");
					
					if(isset($row['bf_file']) && $row['bf_file']){
						$delete_file = run_replace('delete_file_path', G5_DATA_PATH.'/member/'.$mb_id.'/'.str_replace('../', '', $row['bf_file']), $row);
						if( file_exists($delete_file) ){
							@unlink(G5_DATA_PATH.'/member/'.$mb_id.'/'.$row['bf_file']);
						}
						/*
						// 이미지파일이면 썸네일삭제
						if(preg_match("./\.({$config['cf_image_extension']})$/i", $row['bf_file'])) {
							delete_member_thumbnail($mb_id, $row['bf_file']);
						}
						*/
					}
				}

				// 프로그램 원래 파일명
				$upload[$i]['source'] = $filename;
				$upload[$i]['filesize'] = $filesize;
//				echo "0 - ".$upload[$i]['source']."<br>";

				// 아래의 문자열이 들어간 파일은 -x 를 붙여서 웹경로를 알더라도 실행을 하지 못하도록 함
//				$filename = preg_replace("./\.(php|pht|phtm|htm|cgi|pl|exe|jsp|asp|inc|phar)/i", "$0-x", $filename);
//				echo "1 - ".$filename."<br>";

				shuffle($chars_array);
				$shuffle = implode('', $chars_array);

				// 첨부파일 첨부시 첨부파일명에 공백이 포함되어 있으면 일부 PC에서 보이지 않거나 다운로드 되지 않는 현상이 있습니다. (길상여의 님 090925)
				$upload[$i]['file'] = abs(ip2long($_SERVER['REMOTE_ADDR'])).'_'.substr($shuffle,0,8).'_'.replace_filename($filename);
//				echo "2 - ".$upload[$i]['file']."<br>";

				$dest_file = G5_DATA_PATH.'/member/'.$mb_id.'/'.$upload[$i]['file'];
//				echo "3 - ".$dest_file."<br>";

				// 업로드가 안된다면 에러메세지 출력하고 죽어버립니다.
				$error_code = move_uploaded_file($tmp_file, $dest_file) or die($_FILES['bf_file']['error'][$i]);

				// 올라간 파일의 퍼미션을 변경합니다.
				chmod($dest_file, G5_FILE_PERMISSION);
			}
		}   // end for
	}   // end if
//	exit;
	if($upload) {
		// 나중에 테이블에 저장하는 이유는 $wr_id 값을 저장해야 하기 때문입니다.
		for ($i=0; $i<count($upload); $i++)
		{
			$upload[$i]['source'] = sql_real_escape_string($upload[$i]['source']);
			$bf_content[$i] = isset($bf_content[$i]) ? sql_real_escape_string($bf_content[$i]) : '';
			$bf_width = isset($upload[$i]['image'][0]) ? (int) $upload[$i]['image'][0] : 0;
			$bf_height = isset($upload[$i]['image'][1]) ? (int) $upload[$i]['image'][1] : 0;
			$bf_type = isset($upload[$i]['image'][2]) ? (int) $upload[$i]['image'][2] : 0;

	/*
	1 - GIF
	2 - JPEG
	3 - PNG
	4 - SWF
	5 - PSD
	6 - BMP
	7 - TIFF_II
	8 - TIFF_MM
	9 - JPC
	10 - JP2
	11 - JPX
	12 - JB2
	13 - SWC
	14 - IFF
	15 - WBMP
	16 - XBM
	*/

			$row = sql_fetch(" select count(*) as cnt from g5_member_file where mb_id = '{$mb_id}' and bf_no = '{$i}' ");

			if ($row['cnt'])
			{
				// 삭제에 체크가 있거나 파일이 있다면 업데이트를 합니다.
				// 그렇지 않다면 내용만 업데이트 합니다.
				if ($upload[$i]['del_check'] || $upload[$i]['file'])
				{
					$sql = " update g5_member_file
								set bf_source = '{$upload[$i]['source']}',
									bf_file = '{$upload[$i]['file']}',
									bf_filesize = '".(int)$upload[$i]['filesize']."',
									bf_type = '".$bf_type."',
									bf_datetime = '".G5_TIME_YMDHIS."'
							 where mb_id = '{$mb_id}' and bf_no = '{$i}' ";
					sql_query($sql);
				}
			}
			else
			{
				$sql = " insert into g5_member_file
							set mb_id = '{$mb_id}',
								bf_no = '{$i}',
								bf_source = '{$upload[$i]['source']}',
								bf_file = '{$upload[$i]['file']}',
								bf_filesize = '".(int)$upload[$i]['filesize']."',
								bf_type = '".$bf_type."',
								bf_datetime = '".G5_TIME_YMDHIS."' ";
				sql_query($sql);
			}
		}

		// 업로드된 파일 내용에서 가장 큰 번호를 얻어 거꾸로 확인해 가면서
		// 파일 정보가 없다면 테이블의 내용을 삭제합니다.
		$row = sql_fetch(" select max(bf_no) as max_bf_no from g5_member_file where and mb_id = '{$mb_id}' ");
		for ($i=(int)$row['max_bf_no']; $i>=0; $i--)
		{
			$row2 = sql_fetch(" select bf_file from g5_member_file where mb_id = '{$mb_id}' and bf_no = '{$i}' ");

			// 정보가 있다면 빠집니다.
			if (isset($row2['bf_file']) && $row2['bf_file']) break;

			// 그렇지 않다면 정보를 삭제합니다.
			sql_query(" delete from g5_member_file where mb_id = '{$mb_id}' and bf_no = '{$i}' ");
		}
	}

	if($mb_level == '3') {
		$sql = " update g5_device set mb_6_fee = '{$mb_homepage}' where mb_6 = '{$mb_id}' ";
		sql_query($sql);
	} else if($mb_level == '4') {
		$sql = " update g5_device set mb_5_fee = '{$mb_homepage}' where mb_5 = '{$mb_id}' ";
		sql_query($sql);
	} else if($mb_level == '5') {
		$sql = " update g5_device set mb_4_fee = '{$mb_homepage}' where mb_4 = '{$mb_id}' ";
		sql_query($sql);
	} else if($mb_level == '6') {
		$sql = " update g5_device set mb_3_fee = '{$mb_homepage}' where mb_3 = '{$mb_id}' ";
		sql_query($sql);
	} else if($mb_level == '7') {
		$sql = " update g5_device set mb_2_fee = '{$mb_homepage}' where mb_2 = '{$mb_id}' ";
		sql_query($sql);
	} else if($mb_level == '8') {
		$sql = " update g5_device set mb_1_fee = '{$mb_homepage}' where mb_1 = '{$mb_id}' ";
		sql_query($sql);
	}

	$sql_password = "";
	if ($mb_password)
		$sql_password = " , mb_password = '".get_encrypt_string($mb_password)."', mb_birth = '{$mb_password}' ";

		$sql_common = " mb_name = '{$mb_name}',
						mb_nick = '{$mb_nick}',
						mb_email = '{$mb_email}',
						mb_homepage = '{$mb_homepage}',
						mb_tel = '{$mb_tel}',
						mb_hp = '{$mb_hp}',
						mb_certify = '{$mb_certify}',
						mb_adult = '{$mb_adult}',
						mb_dupinfo = '{$mb_dupinfo}',
						mb_settle_gbn = '{$mb_settle_gbn}',
						mb_zip1 = '$mb_zip1',
						mb_zip2 = '$mb_zip2',
						mb_addr1 = '{$mb_addr1}',
						mb_addr2 = '{$mb_addr2}',
						mb_addr3 = '{$mb_addr3}',
						mb_addr_jibeon = '{$mb_addr_jibeon}',
						mb_signature = '{$mb_signature}',
						mb_leave_date = '{$mb_leave_date}',
						mb_intercept_date='{$mb_intercept_date}',
						mb_memo = '{$mb_memo}',
						mb_mailling = '{$mb_mailling}',
						mb_sms = '{$mb_sms}',
						mb_open = '{$mb_open}',
						mb_profile = '{$mb_profile}',
						mb_level = '{$mb_level}',
						mb_memo_call = '{$mb_memo_call}',
						mb_recommend = '{$member['mb_id']}',
						mb_1 = '{$mb_1}',
						mb_2 = '{$mb_2}',
						mb_3 = '{$mb_3}',
						mb_4 = '{$mb_4}',
						mb_5 = '{$mb_5}',
						mb_6 = '{$mb_6}',
						mb_7 = '{$mb_7}',
						mb_8 = '{$mb_8}',
						mb_9 = '{$mb_9}',
						mb_10 = '{$mb_10}'
						{$sql_password}";

	if($w == "u") {
		$sql = " update {$g5['member_table']} set {$sql_common} where mb_id = '$mb_id' ";
		$alert_msg = "수정";
	} else {
		$sql = " insert into {$g5['member_table']} set {$sql_common} , mb_datetime = '".G5_TIME_YMDHIS."', mb_id   = '{$mb_id}' ";
		$alert_msg = "등록";
	}
	sql_query($sql);

//	echo $sql."<br><br>";


	goto_url("./?p=member_form&mb_id=".$mb_id."&mb_level=".$mb_level."&w=u");
