<?php
	require('_paysis_utils.php');                // 유틸리티 포함

	$logPath = "c://app.log";            //(Windows)로그경로 (하위디렉토리를 반드시 만들어야 합니다.)
	//$logPath = "/home/app.log";        //(Linux)로그경로 (하위디렉토리를 반드시 만들어야 합니다.)

	/*=================================================================================================
		****** 아래의 정보는 TEST용 가맹점 정보 입니다. 
			REAL 서비스 전환 시 반드시 실제 가맹점 정보로 변경해야 합니다. *******
    =================================================================================================*/
	//$API_BASE = "https://relay.mainpay.co.kr";						//REAL API URL
	//$API_BASE = "https://test-relay.mainpay.co.kr";						//TEST API URL
	//$mbrNo = "100011";													//테스트 가맹점번호
	//$apiKey = "U1FVQVJFLTEwMDAxMTIwMTgwNDA2MDkyNTMyMTA1MjM0";			//테스트 apiKey


	$API_BASE = $config['cf_7_subj'];						//TEST API URL
	$mbrNo = $config['cf_7'];													//테스트 가맹점번호
	$apiKey = $config['cfv_7'];			//테스트 apiKey

?>