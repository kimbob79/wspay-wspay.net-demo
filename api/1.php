<?php
$ch = curl_init();
$url = 'http://apis.data.go.kr/1741000/StanReginCd/getStanReginCdList'; /*URL*/
$queryParams = '?' . urlencode('serviceKey') . '=q3EHP97R81GyKHc%2B6n0NKa0hF0qy2cvkvwRM147r85x37UvOaHPSfRIp9XJ0oqi6xR50%2FgmSeCTPNxvsj9WF4g%3D%3D'; /*Service Key*/
$queryParams .= '&' . urlencode('pageNo') . '=' . urlencode('1'); /**/
$queryParams .= '&' . urlencode('numOfRows') . '=' . urlencode('3'); /**/
$queryParams .= '&' . urlencode('type') . '=' . urlencode('json'); /**/
$queryParams .= '&' . urlencode('locatadd_nm') . '=' . urlencode('서울특별시'); /**/

curl_setopt($ch, CURLOPT_URL, $url . $queryParams);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, FALSE);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
$response = curl_exec($ch);
curl_close($ch);

var_dump($response);