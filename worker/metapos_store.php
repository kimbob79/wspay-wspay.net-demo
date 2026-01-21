<?php
/**
 * MetaPOS API - 매장 조회
 * URL: https://webapi.metapos.co.kr/webapi/sales_search/api/storeInfo.asp
 */

// API 설정
$api_url = "https://webapi.metapos.co.kr/webapi/sales_search/api/storeInfo.asp";
$api_key = "0675a2779cd8f3cdfa5eafaa3f408121";

// 요청 파라미터 설정
$params = array(
    "cmd"    => "SL",            // 조회 구분 (고정값)
    "br_uid" => "B19052200002",  // 브랜드 고유 id
    "st_use" => "all"            // 매장 조회 조건 (all:전체, open:개점, close:폐점)
);

// GET 파라미터로 조건 변경 가능
if (isset($_GET['br_uid'])) {
    $params['br_uid'] = $_GET['br_uid'];
}
if (isset($_GET['st_use']) && in_array($_GET['st_use'], array('all', 'open', 'close'))) {
    $params['st_use'] = $_GET['st_use'];
}

/**
 * API 호출 함수
 */
function callMetaposApi($url, $api_key, $params) {
    $ch = curl_init();

    // JSON 데이터 생성
    $json_data = json_encode($params, JSON_UNESCAPED_UNICODE);

    // cURL 옵션 설정
    curl_setopt_array($ch, array(
        CURLOPT_URL            => $url,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $json_data,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER     => array(
            "Content-Type: application/json",
            "x-api-key: " . $api_key
        )
    ));

    // API 호출
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    return array(
        'success'   => ($http_code == 200 && !$error),
        'http_code' => $http_code,
        'error'     => $error,
        'response'  => $response,
        'data'      => json_decode($response, true)
    );
}

// API 호출 실행
$result = callMetaposApi($api_url, $api_key, $params);

// 결과 출력
header('Content-Type: application/json; charset=utf-8');

if ($result['success']) {
    echo json_encode(array(
        'status'  => 'success',
        'params'  => $params,
        'data'    => $result['data']
    ), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} else {
    echo json_encode(array(
        'status'    => 'error',
        'http_code' => $result['http_code'],
        'error'     => $result['error'],
        'params'    => $params,
        'response'  => $result['response']
    ), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
