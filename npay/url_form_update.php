<?php
// print "<pre>"; print_r($_POST); print "</pre>"; exit;
 
 /*
 (
    [mb_id] => admin
    [url_pd] => 1
    [url_price] => 2
    [url_pname] => 3
    [url_ptel] => 4
    [url_gname] => 5
    [url_gtel] => 6
    [pg_id] => 134
    [agreement1] => 1
)
 */


	$mb_id				= trim($_POST['mb_id']);
	$urlcode			= trim($_POST['urlcode']);
	$url_pd				= trim($_POST['url_pd']);
	$url_price			= trim($_POST['url_price']);
	$url_pname			= trim($_POST['url_pname']);
	$url_ptel			= trim($_POST['url_ptel']);
	$url_gname			= trim($_POST['url_gname']);
	$url_gtel			= trim($_POST['url_gtel']);
	$pg_id				= trim($_POST['pg_id']);
//	$agreement1			= trim($_POST['agreement1']);

	// 네이버 단축URL
	$long_url = "http://simplepay.kr/url_pay.php?urlcode=".$urlcode;
	$encText = urlencode($long_url);
	$url = "https://openapi.naver.com/v1/util/shorturl?url=".$encText;
	$is_post = false;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, $is_post);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$headers = array();
	$headers[] = "X-Naver-Client-Id: "."PomTeIgMbmiRRhDwn0aJ"; //클라이언트 아이디 예시
	$headers[] = "X-Naver-Client-Secret: "."ohcFEUzDDQ"; //클라이언트 시크릿 키 예시
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$naver_response = curl_exec ($ch);
	$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close ($ch);
	$naver_response = json_decode($naver_response);
	$url_shot = $naver_response->result->url;


	$sql_common = " mb_id = '{$mb_id}',
					urlcode = '{$urlcode}',
					url_shot = '{$url_shot}',
					url_pd = '{$url_pd}',
					url_price = '{$url_price}',
					url_pname = '{$url_pname}',
					url_ptel = '{$url_ptel}',
					url_gname = '{$url_gname}',
					url_gtel = '{$url_gtel}',
					pg_id = '{$pg_id}' ";

	$sql = " insert into pay_payment_sms set {$sql_common}, datetime = '".G5_TIME_YMDHIS."' ";
	sql_query($sql);
//	echo $sql;
//	exit;

goto_url('./?p=url_list');