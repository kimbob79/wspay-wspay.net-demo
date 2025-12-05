

	if($mbrNo == "114004") {

		$data = array('cmd' => $cmd, 'paymethod' => $paymethod, 'payType' => $payType, 'requestFlag' => $requestFlag, 'mbrRefNo' => $mbrRefNo, 'mbrNo' => $mbrNo, 'refNo' => $refNo, 'tranDate' => $tranDate, 'tranTime' => $tranTime, 'orgRefNo' => $orgRefNo, 'orgTranDate' => $orgTranDate, 'vanCatId' => $vanCatId, 'cardMerchNo' => $cardMerchNo, 'applNo' => $applNo, 'issueCompanyNo' => $issueCompanyNo, 'acqCompanyNo' => $acqCompanyNo, 'cardNo' => $cardNo, 'installNo' => $installNo, 'goodsName' => $goodsName, 'amount' => $amount, 'customerName' => $customerName, 'customerTelNo' => $customerTelNo, 'customerEmail' => $customerEmail, 'sid' => $sid, 'retailerCode' => $retailerCode);

		$url = 'http://www.notibstnpay.com/HTN_NOTI/sector_noti';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 요청 결과를 문자열로 받음
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // curl이 첫 응답 시간에 대한 timeout
		curl_setopt($ch, CURLOPT_TIMEOUT, 60); // curl 전체 실행 시간에 대한 timeout
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 원격 서버의 인증서가 유효한지 검사하지 않음
		$result = curl_exec($ch); // 요청 결과
		curl_close($ch);

	}

	if($mbrNo == "113737" || $mbrNo == "114274") {

		$data = array('cmd' => $cmd, 'paymethod' => $paymethod, 'payType' => $payType, 'requestFlag' => $requestFlag, 'mbrRefNo' => $mbrRefNo, 'mbrNo' => $mbrNo, 'refNo' => $refNo, 'tranDate' => $tranDate, 'tranTime' => $tranTime, 'orgRefNo' => $orgRefNo, 'orgTranDate' => $orgTranDate, 'vanCatId' => $vanCatId, 'cardMerchNo' => $cardMerchNo, 'applNo' => $applNo, 'issueCompanyNo' => $issueCompanyNo, 'acqCompanyNo' => $acqCompanyNo, 'cardNo' => $cardNo, 'installNo' => $installNo, 'goodsName' => $goodsName, 'amount' => $amount, 'customerName' => $customerName, 'customerTelNo' => $customerTelNo, 'customerEmail' => $customerEmail, 'sid' => $sid, 'retailerCode' => $retailerCode);

/*
기존 : https://repay.devfm.co.kr/repaydata/ 
변경 : https://repayapi.devfm.co.kr/repaydata/receive

노티 발송 URL 변경건 입니다 이사님!
확인 부탁 드립니다.!
*/

		$url = 'https://repayapi.devfm.co.kr/repaydata/receive';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 요청 결과를 문자열로 받음
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // curl이 첫 응답 시간에 대한 timeout
		curl_setopt($ch, CURLOPT_TIMEOUT, 60); // curl 전체 실행 시간에 대한 timeout
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 원격 서버의 인증서가 유효한지 검사하지 않음
		$result = curl_exec($ch); // 요청 결과
		curl_close($ch);

	}

	if($mbrNo == "117267" || $mbrNo == "117185" || $mbrNo == "117186" || $mbrNo == "117362") {

		$data = array('cmd' => $cmd, 'paymethod' => $paymethod, 'payType' => $payType, 'requestFlag' => $requestFlag, 'mbrRefNo' => $mbrRefNo, 'mbrNo' => $mbrNo, 'refNo' => $refNo, 'tranDate' => $tranDate, 'tranTime' => $tranTime, 'orgRefNo' => $orgRefNo, 'orgTranDate' => $orgTranDate, 'vanCatId' => $vanCatId, 'cardMerchNo' => $cardMerchNo, 'applNo' => $applNo, 'issueCompanyNo' => $issueCompanyNo, 'acqCompanyNo' => $acqCompanyNo, 'cardNo' => $cardNo, 'installNo' => $installNo, 'goodsName' => $goodsName, 'amount' => $amount, 'customerName' => $customerName, 'customerTelNo' => $customerTelNo, 'customerEmail' => $customerEmail, 'sid' => $sid, 'retailerCode' => $retailerCode);

		$url = 'https://ss-pay.co.kr/api/stn/';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 요청 결과를 문자열로 받음
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // curl이 첫 응답 시간에 대한 timeout
		curl_setopt($ch, CURLOPT_TIMEOUT, 60); // curl 전체 실행 시간에 대한 timeout
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 원격 서버의 인증서가 유효한지 검사하지 않음
		$result = curl_exec($ch); // 요청 결과
		curl_close($ch);
	}
	
	if($mbrNo == "114685" || $mbrNo == "114725" || $mbrNo == "114687") {

		$data = array('cmd' => $cmd, 'paymethod' => $paymethod, 'payType' => $payType, 'requestFlag' => $requestFlag, 'mbrRefNo' => $mbrRefNo, 'mbrNo' => $mbrNo, 'refNo' => $refNo, 'tranDate' => $tranDate, 'tranTime' => $tranTime, 'orgRefNo' => $orgRefNo, 'orgTranDate' => $orgTranDate, 'vanCatId' => $vanCatId, 'cardMerchNo' => $cardMerchNo, 'applNo' => $applNo, 'issueCompanyNo' => $issueCompanyNo, 'acqCompanyNo' => $acqCompanyNo, 'cardNo' => $cardNo, 'installNo' => $installNo, 'goodsName' => $goodsName, 'amount' => $amount, 'customerName' => $customerName, 'customerTelNo' => $customerTelNo, 'customerEmail' => $customerEmail, 'sid' => $sid, 'retailerCode' => $retailerCode);

		$url = 'https://api.mipay.im/api/PGNoti/Mainpay';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 요청 결과를 문자열로 받음
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // curl이 첫 응답 시간에 대한 timeout
		curl_setopt($ch, CURLOPT_TIMEOUT, 60); // curl 전체 실행 시간에 대한 timeout
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 원격 서버의 인증서가 유효한지 검사하지 않음
		$result = curl_exec($ch); // 요청 결과
		curl_close($ch);
	}
	
	if($mbrNo == "117268" || $mbrNo == "117434" || $mbrNo == "117433" || $mbrNo == "117432") {

		$data = array('cmd' => $cmd, 'paymethod' => $paymethod, 'payType' => $payType, 'requestFlag' => $requestFlag, 'mbrRefNo' => $mbrRefNo, 'mbrNo' => $mbrNo, 'refNo' => $refNo, 'tranDate' => $tranDate, 'tranTime' => $tranTime, 'orgRefNo' => $orgRefNo, 'orgTranDate' => $orgTranDate, 'vanCatId' => $vanCatId, 'cardMerchNo' => $cardMerchNo, 'applNo' => $applNo, 'issueCompanyNo' => $issueCompanyNo, 'acqCompanyNo' => $acqCompanyNo, 'cardNo' => $cardNo, 'installNo' => $installNo, 'goodsName' => $goodsName, 'amount' => $amount, 'customerName' => $customerName, 'customerTelNo' => $customerTelNo, 'customerEmail' => $customerEmail, 'sid' => $sid, 'retailerCode' => $retailerCode);

		$url = 'https://www.salesbilling.co.kr:3636/api/wanna/noti/tran';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 요청 결과를 문자열로 받음
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // curl이 첫 응답 시간에 대한 timeout
		curl_setopt($ch, CURLOPT_TIMEOUT, 60); // curl 전체 실행 시간에 대한 timeout
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 원격 서버의 인증서가 유효한지 검사하지 않음
		$result = curl_exec($ch); // 요청 결과
		curl_close($ch);
	}



/*
	if(in_array($mbrNo, $arraydata)) {
		$tid2 = substr($mbrRefNo, 0, -10);
		
		if($tid2 == "WNS1758156813" || $tid2 == "WNS1757565555") {

			$data = array('cmd' => $cmd, 'paymethod' => $paymethod, 'payType' => $payType, 'requestFlag' => $requestFlag, 'mbrRefNo' => $mbrRefNo, 'mbrNo' => $mbrNo, 'refNo' => $refNo, 'tranDate' => $tranDate, 'tranTime' => $tranTime, 'orgRefNo' => $orgRefNo, 'orgTranDate' => $orgTranDate, 'vanCatId' => $vanCatId, 'cardMerchNo' => $cardMerchNo, 'applNo' => $applNo, 'issueCompanyNo' => $issueCompanyNo, 'acqCompanyNo' => $acqCompanyNo, 'cardNo' => $cardNo, 'installNo' => $installNo, 'goodsName' => $goodsName, 'amount' => $amount, 'customerName' => $customerName, 'customerTelNo' => $customerTelNo, 'customerEmail' => $customerEmail, 'sid' => $sid, 'retailerCode' => $retailerCode);

			$url = 'https://www.salesbilling.co.kr:3636/api/wanna/noti/tran';

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);

			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 요청 결과를 문자열로 받음
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // curl이 첫 응답 시간에 대한 timeout
			curl_setopt($ch, CURLOPT_TIMEOUT, 60); // curl 전체 실행 시간에 대한 timeout
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 원격 서버의 인증서가 유효한지 검사하지 않음
			$result = curl_exec($ch); // 요청 결과
			curl_close($ch);
		}
	}
*/
	/******** 외부로 전송 ************/