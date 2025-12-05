<?php
require_once "./_common.php";

/*
print "<pre>"; print_r($_POST); print "</pre>";
exit;
*/


$mb_id				= trim($_POST['mb_id']);
$url_payments		= trim($_POST['payments']);
$mb_name			= trim($_POST['mb_name']);
$urlcode			= trim($_POST['urlcode']);
$url_pd				= trim($_POST['url_pd']);
$url_price			= trim($_POST['url_price']);
$url_pname			= trim($_POST['url_pname']);
$url_ptel			= trim($_POST['url_ptel1'])."-".trim($_POST['url_ptel2'])."-".trim($_POST['url_ptel3']);
$url_pcontent		= trim($_POST['url_pcontent']);
$url_etc			= trim($_POST['url_etc']);



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
$naver_response = json_decode($naver_response);
$url_shot = $naver_response->result->url;




$sql_common = " mb_id = '{$mb_id}',
				url_payments = '{$url_payments}',
				mb_name = '{$mb_name}',
				urlcode = '{$urlcode}',
				url_shot = '{$url_shot}',
				url_pd = '{$url_pd}',
				url_price = '{$url_price}',
				url_pname = '{$url_pname}',
				url_ptel = '{$url_ptel}',
				url_pcontent = '{$url_pcontent}',
				url_etc = '{$url_etc}', ";


if ($w == '') {

	$sql = " insert into pay_payment_url set ".$sql_common." datetime = '".G5_TIME_YMDHIS."', ip = '".$_SERVER['REMOTE_ADDR']."'  ";

} elseif ($w == 'u') {

	if ($mb_password) {
		$sql_password = " , mb_password = '" . get_encrypt_string($mb_password) . "' ";
		$sql_password .= " , mb_1 = '" .$mb_password. "' ";
	} else {
		$sql_password = "";
	}

	$sql = " update {$g5['member_table']}
				set {$sql_common}
					 {$sql_password}
					 {$sql_certify}
				where mb_id = '{$mb_id}' ";

}
sql_query($sql);
goto_url('./list.php?');
?>