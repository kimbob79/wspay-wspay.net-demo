<?php

/*
// 현재 페이지의 주소를 가져옵니다.
$current_url = 'http://pay.mpc.icu/url/pay.php?urlcode=8f3cbee2250afb717fc5ff7296367eae';


// QR 코드를 출력합니다.
echo "<img src='https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($current_url) . "&size=500x500' alt='QR Code' style='width:100%;'><br><br>";
echo $current_url."<br><br>";

$current_url = 'http://pay.mpc.icu/url/pay.php?urlcode=8f3cbee2250afb717fc5ff7296367eae11111111';

// QR 코드를 출력합니다.
echo "<img src='https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($current_url) . "&size=500x500' alt='QR Code'><br><br>";
echo $current_url;
*/






$urlcode = "d3f9ae350938eee7c2b842762d3ab538";

// 네이버 단축URL
$long_url = "http://pay.mpc.icu/url/pay.php?urlcode=".$urlcode;
$encText = urlencode($long_url);


$url = "https://openapi.naver.com/v1/util/shorturl?url=".$encText;

$is_post = false;
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, $is_post);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$headers = array();

$headers[] = "X-Naver-Client-Id: "."j_5eTXIVXJw1TQvcT_5W"; //클라이언트 아이디 예시
$headers[] = "X-Naver-Client-Secret: "."dM3BWle0v0"; //클라이언트 시크릿 키 예시

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$naver_response = curl_exec ($ch);

$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close ($ch);

//문자열 값을 php 배열(오브젝트)로 변환
$naver_response = json_decode($naver_response);
//네이버 단축url
$url_shot = $naver_response->result->url;


echo $url_shot."a";




/*
$urlcode = "d3f9ae350938eee7c2b842762d3ab538";

// 네이버 단축URL
$long_url = "http://pay.mpc.icu/url/pay.php?urlcode=".$urlcode;


function Naver_Shortener( $client_id, $secret, $short_url )
{
	$headers = array(
		'X-Naver-Client-Id:' . $client_id,
		'X-Naver-Client-Secret: ' . $secret
	);


	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 
		"https://openapi.naver.com/v1/util/shorturl?url=" . urlencode($short_url)
	);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$data = curl_exec($ch);
	curl_close($ch);

	$data = json_decode($data, true);
	if($data['code'] === "200")
	{
		return array(
			// 원본 URL 
			// ex. https://developers.naver.com/docs/utils/shortenurl
			'original_url' => $data['result']['orgUrl'],
			// 짧은 URL 
			// ex. http://me2.do/GqtgOZX9
			'shout_url' => $data['result']['url']
		);
	}
	return $data['code'];
}

$url = Naver_Shortener('j_5eTXIVXJw1TQvcT_5W', 'dM3BWle0v0', $long_url);
print_r($url);
*/

?>