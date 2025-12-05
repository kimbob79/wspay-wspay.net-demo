<?php
	error_reporting( E_ALL );
	ini_set( "display_errors", 1 );

$data = array('gid' => $gid, 'vid' => $vid, 'mid' => $mid, 'payMethod' => $payMethod, 'appCardCd' => $appCardCd, 'cancelYN' => $cancelYN, 'tid' => $tid, 'ediNo' => $ediNo, 'appDtm' => $appDtm, 'ccDnt' => $ccDnt, 'amt' => $amt, 'remainAmt' => $remainAmt, 'buyerId' => $buyerId, 'ordNm' => $ordNm, 'ordNo' => $ordNo, 'goodsName' => $goodsName, 'appNo' => $appNo, 'quota' => $quota, 'notiDnt' => $notiDnt, 'cardNo' => $cardNo, 'catId' => $catId, 'tPhone' => $tPhone, 'canAmt' => $canAmt, 'partCanFlg' => $partCanFlg, 'connCD' => $connCD, 'usePointAmt' => $usePointAmt, 'vacntNo' => $vacntNo, 'socHpNo' => $socHpNo);
$url = 'http://cajung.com/api/korpay/index.php';

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