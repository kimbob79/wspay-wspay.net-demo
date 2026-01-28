<?php
/**
 * MetaPOS API - 결제 유형별 매출 조회 (테스트용)
 * URL: https://webapi.metapos.co.kr/webapi/sales_search/api/storePayType.asp
 *
 * DB 작업 없이 API 응답값만 출력
 */

// API 설정
$api_url = "https://webapi.metapos.co.kr/webapi/sales_search/api/storePayType.asp";
// 운영키: 5f0efb7dbe66de52458d2ef550f24090, 테스트키: 0675a2779cd8f3cdfa5eafaa3f408121
$api_key = "5f0efb7dbe66de52458d2ef550f24090";

// 요청 파라미터 설정
$params = array(
    "cmd"     => "SPT",           // 조회 구분 (고정값)
    "br_uid"  => "B26010900001",  // 브랜드 고유 id (운영: B26010900001, 테스트: B19052200002)
    "st_uid"  => "I26010900010",  // 매장 고유 id (운영: I26010900010, 테스트: I26011400007)
    "sal_ymd" => date("Ymd")      // 매출일자 (오늘 날짜)
);

// GET 파라미터로 조건 변경 가능
if (isset($_GET['sal_ymd']) && preg_match('/^\d{8}$/', $_GET['sal_ymd'])) {
    $params['sal_ymd'] = $_GET['sal_ymd'];
}
if (isset($_GET['br_uid'])) {
    $params['br_uid'] = $_GET['br_uid'];
}
if (isset($_GET['st_uid'])) {
    $params['st_uid'] = $_GET['st_uid'];
}

// cURL API 호출
$ch = curl_init();

curl_setopt_array($ch, array(
    CURLOPT_URL            => $api_url,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($params, JSON_UNESCAPED_UNICODE),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_HTTPHEADER     => array(
        "Content-Type: application/json",
        "x-api-key: " . $api_key
    )
));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

// 결과 출력
header('Content-Type: application/json; charset=utf-8');

if ($http_code == 200 && !$error) {
    $data = json_decode($response, true);
    echo json_encode(array(
        'status'    => 'success',
        'params'    => $params,
        'http_code' => $http_code,
        'data'      => $data
    ), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} else {
    echo json_encode(array(
        'status'    => 'error',
        'params'    => $params,
        'http_code' => $http_code,
        'error'     => $error,
        'response'  => $response
    ), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
