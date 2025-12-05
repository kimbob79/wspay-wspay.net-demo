<?php
$ch = curl_init();
$url = 'http://openapi.molit.go.kr:8081/OpenAPI_ToolInstallPackage/service/rest/RTMSOBJSvc/getRTMSDataSvcAptTrade'; /*URL*/
$queryParams = '?' . urlencode('serviceKey') . '=q3EHP97R81GyKHc%2B6n0NKa0hF0qy2cvkvwRM147r85x37UvOaHPSfRIp9XJ0oqi6xR50%2FgmSeCTPNxvsj9WF4g%3D%3D'; /*Service Key*/
$queryParams .= '&' . urlencode('LAWD_CD') . '=' . urlencode('11110'); /**/
$queryParams .= '&' . urlencode('DEAL_YMD') . '=' . urlencode('201512'); /**/

curl_setopt($ch, CURLOPT_URL, $url . $queryParams);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, FALSE);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
$response = curl_exec($ch);
curl_close($ch);

var_dump($response);