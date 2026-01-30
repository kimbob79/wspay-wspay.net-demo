<?php
/**
 * MetaPOS API - 결제 유형별 매출 조회
 * URL: https://webapi.metapos.co.kr/webapi/sales_search/api/storePayType.asp
 *
 * "pay_auth_number": "023224458",
 * "pay_cash_id": "010****0877"
 * 현금영수증 승인받은것은 소득공제
 * 이거 2개값이 필수로 존재한다고 합니다!
 */

// dbconfig.php 로드를 위한 상수 정의
define('_GNUBOARD_', true);
include_once(__DIR__ . '/../gnu_module/data/dbconfig.php');

// DB 연결
$mysqli = new mysqli(
    preg_replace('/:\d+$/', '', G5_MYSQL_HOST),
    G5_MYSQL_USER,
    G5_MYSQL_PASSWORD,
    G5_MYSQL_DB,
    (int)preg_replace('/^.*:/', '', G5_MYSQL_HOST . ':13306')
);

if ($mysqli->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'DB 연결 실패: ' . $mysqli->connect_error]));
}
$mysqli->set_charset('utf8mb4');

// API 설정
$api_url = "https://webapi.metapos.co.kr/webapi/sales_search/api/storePayType.asp";
// 운영키: 5f0efb7dbe66de52458d2ef550f24090, 테스트키: 0675a2779cd8f3cdfa5eafaa3f408121
$api_key = "5f0efb7dbe66de52458d2ef550f24090";  
// $api_key = "0675a2779cd8f3cdfa5eafaa3f408121";

// 오늘 날짜
$today = date('Ymd');
// $today = "20260126";    // 임시 테스트

// GET 파라미터로 날짜 변경 가능
if (isset($_GET['sal_ymd']) && preg_match('/^\d{8}$/', $_GET['sal_ymd'])) {
    $today = $_GET['sal_ymd'];
}

// metapos_store 테이블에서 매장 조회
// [테스트] br_uid='B19052200002', st_uid='I26011400007' 고정
// [운영] br_uid='B26010900001' 고정
$sql = "SELECT br_uid, st_uid, st_name
        FROM metapos_store
        WHERE br_uid = 'B26010900001'
        AND st_uid = 'I26010900010'
        AND st_use = 'Y'";
$store_result = $mysqli->query($sql);

if (!$store_result || $store_result->num_rows == 0) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'error',
        'message' => '조회할 매장이 없습니다.',
        'sql' => $sql
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
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

/**
 * g5_payment 테이블에서 매칭되는 결제 찾기
 * 매칭 조건: 승인일자 + 승인번호 + 승인금액
 * @param mysqli $mysqli DB 연결
 * @param string $sal_ymd 매출일자 (YYYYMMDD)
 * @param string $pay_auth_number 승인번호
 * @param int $pay_amount 승인금액
 * @return int|null g5_payment.pay_id 또는 null
 */
function findG5PaymentMatch($mysqli, $sal_ymd, $pay_auth_number, $pay_amount) {
    // 승인번호가 없으면 매칭 불가 (현금결제 등)
    if (empty($pay_auth_number)) {
        return null;
    }

    // 매출일자를 DATE 형식으로 변환 (YYYYMMDD -> YYYY-MM-DD)
    $sal_date = substr($sal_ymd, 0, 4) . '-' . substr($sal_ymd, 4, 2) . '-' . substr($sal_ymd, 6, 2);

    // g5_payment에서 매칭 조회
    // 조건: 승인일자(DATE) + 승인번호 + 승인금액
    $match_sql = "SELECT pay_id FROM g5_payment
                  WHERE DATE(pay_datetime) = '{$sal_date}'
                  AND pay_num = '" . $mysqli->real_escape_string($pay_auth_number) . "'
                  AND pay = " . (int)$pay_amount . "
                  LIMIT 1";

    $match_result = $mysqli->query($match_sql);

    if ($match_result && $match_result->num_rows > 0) {
        $row = $match_result->fetch_assoc();
        return (int)$row['pay_id'];
    }

    return null;
}

/**
 * 결제 데이터 저장 함수
 * @param mysqli $mysqli DB 연결
 * @param array $api_data API 응답 데이터
 * @param array $store 매장 정보
 * @return array 처리 결과
 */
function savePaymentData($mysqli, $api_data, $store) {
    $result = array(
        'inserted' => 0,
        'skipped'  => 0,
        'matched'  => 0,
        'updated'  => 0,  // 기존 데이터 업데이트 카운트
        'status_changed' => 0,  // bill_status 변경 카운트 (S→C 취소 등)
        'errors'   => array()
    );

    // API 응답에서 데이터 추출
    if (!isset($api_data['DataInfo']) || !is_array($api_data['DataInfo'])) {
        return $result;
    }

    $br_uid = $api_data['BrUid'] ?? $store['br_uid'];
    $br_name = $api_data['BrName'] ?? '';
    $st_uid = $api_data['StUid'] ?? $store['st_uid'];
    $st_name = $api_data['StName'] ?? $store['st_name'];
    $sal_ymd = $api_data['SalYmd'] ?? '';

    foreach ($api_data['DataInfo'] as $payment) {
        $sal_seq = $payment['sal_seq'] ?? null;
        $bill_sal_seq = $payment['bill_sal_seq'] ?? '01';  // 결제 순서 (없으면 01)
        $bill_no = $payment['bill_no'] ?? '';

        if (empty($bill_no)) {
            $result['errors'][] = "bill_no가 비어있는 데이터 발견";
            continue;
        }

        // g5_payment 매칭 (카드결제인 경우만) - 먼저 매칭 시도
        $pay_auth_number = $payment['pay_auth_number'] ?? '';
        $pay_amount = (int)($payment['pay_amount'] ?? 0);
        $g5_pay_id = findG5PaymentMatch($mysqli, $sal_ymd, $pay_auth_number, $pay_amount);

        // 중복 체크 (unique key: st_uid, sal_ymd, sal_seq, bill_sal_seq)
        $check_sql = "SELECT mp_id, g5_pay_id, bill_status FROM metapos_payment
                      WHERE st_uid = '" . $mysqli->real_escape_string($st_uid) . "'
                      AND sal_ymd = '" . $mysqli->real_escape_string($sal_ymd) . "'
                      AND sal_seq = " . (int)$sal_seq . "
                      AND bill_sal_seq = '" . $mysqli->real_escape_string($bill_sal_seq) . "'";

        $check_result = $mysqli->query($check_sql);

        if ($check_result && $check_result->num_rows > 0) {
            // 이미 존재하는 데이터
            $existing = $check_result->fetch_assoc();
            $need_update = false;
            $update_fields = array();

            // 1. bill_status 변경 체크 (S→C 취소 처리 등)
            $new_bill_status = $payment['bill_status'] ?? 'S';
            if ($existing['bill_status'] != $new_bill_status) {
                $update_fields[] = "bill_status = '" . $mysqli->real_escape_string($new_bill_status) . "'";
                $need_update = true;
                $result['status_changed']++;
            }

            // 2. 기존에 미매칭이었는데 이번에 매칭된 경우
            if (empty($existing['g5_pay_id']) && $g5_pay_id) {
                $update_fields[] = "g5_pay_id = {$g5_pay_id}";
                $need_update = true;
                $result['matched']++;
            }

            // 업데이트 실행
            if ($need_update && count($update_fields) > 0) {
                $update_sql = "UPDATE metapos_payment SET " . implode(', ', $update_fields) . " WHERE mp_id = " . (int)$existing['mp_id'];
                if ($mysqli->query($update_sql)) {
                    $result['updated']++;
                }
            }

            $result['skipped']++;
            continue;
        }

        // 신규 INSERT 시 매칭 카운트
        if ($g5_pay_id) {
            $result['matched']++;
        }

        // INSERT
        $insert_sql = "INSERT INTO metapos_payment (
            br_uid, br_name, st_uid, st_name, sal_ymd, sal_seq, bill_sal_seq,
            pos_uid, pos_name, bill_no, bill_table_no, bill_status,
            bill_amount, bill_vat, bill_discount, bill_ordered_at, bill_paid_at,
            pay_price, pay_vat, pay_amount, pay_method, pay_issuer,
            pay_card_no, pay_auth_number, pay_card_month, pay_approved_at, pay_cash_id,
            g5_pay_id, raw_data, created_at
        ) VALUES (
            '" . $mysqli->real_escape_string($br_uid) . "',
            '" . $mysqli->real_escape_string($br_name) . "',
            '" . $mysqli->real_escape_string($st_uid) . "',
            '" . $mysqli->real_escape_string($st_name) . "',
            '" . $mysqli->real_escape_string($sal_ymd) . "',
            " . (int)$sal_seq . ",
            '" . $mysqli->real_escape_string($bill_sal_seq) . "',
            '" . $mysqli->real_escape_string($payment['pos_uid'] ?? '') . "',
            '" . $mysqli->real_escape_string($payment['pos_name'] ?? '') . "',
            '" . $mysqli->real_escape_string($bill_no) . "',
            '" . $mysqli->real_escape_string($payment['bill_table_no'] ?? '') . "',
            '" . $mysqli->real_escape_string($payment['bill_status'] ?? 'S') . "',
            " . (int)($payment['bill_amount'] ?? 0) . ",
            " . (int)($payment['bill_vat'] ?? 0) . ",
            " . (int)($payment['bill_discount'] ?? 0) . ",
            " . (empty($payment['bill_ordered_at']) ? "NULL" : "'" . $mysqli->real_escape_string($payment['bill_ordered_at']) . "'") . ",
            " . (empty($payment['bill_paid_at']) ? "NULL" : "'" . $mysqli->real_escape_string($payment['bill_paid_at']) . "'") . ",
            " . (int)($payment['pay_price'] ?? 0) . ",
            " . (int)($payment['pay_vat'] ?? 0) . ",
            " . (int)($payment['pay_amount'] ?? 0) . ",
            '" . $mysqli->real_escape_string($payment['pay_method'] ?? '') . "',
            '" . $mysqli->real_escape_string($payment['pay_issuer'] ?? '') . "',
            '" . $mysqli->real_escape_string($payment['pay_card_no'] ?? '') . "',
            '" . $mysqli->real_escape_string($pay_auth_number) . "',
            " . (int)($payment['pay_card_month'] ?? 0) . ",
            " . (empty($payment['pay_approved_at']) ? "NULL" : "'" . $mysqli->real_escape_string($payment['pay_approved_at']) . "'") . ",
            '" . $mysqli->real_escape_string($payment['pay_cash_id'] ?? '') . "',
            " . ($g5_pay_id ? $g5_pay_id : "NULL") . ",
            '" . $mysqli->real_escape_string(json_encode($payment, JSON_UNESCAPED_UNICODE)) . "',
            NOW()
        )";

        if ($mysqli->query($insert_sql)) {
            $result['inserted']++;
        } else {
            $result['errors'][] = "INSERT 실패 (bill_no: {$bill_no}): " . $mysqli->error;
        }
    }

    return $result;
}

// 결과 배열
$results = [];
$total_sync = array('inserted' => 0, 'skipped' => 0, 'matched' => 0, 'updated' => 0, 'status_changed' => 0, 'errors' => array());

// 각 매장별로 API 호출
while ($store = $store_result->fetch_assoc()) {
    $params = array(
        "cmd"     => "SPT",
        "br_uid"  => $store['br_uid'],
        "st_uid"  => $store['st_uid'],
        "sal_ymd" => $today
    );

    $api_result = callMetaposApi($api_url, $api_key, $params);

    // API 호출 성공 시 DB 저장
    $sync_result = array('inserted' => 0, 'skipped' => 0, 'matched' => 0, 'updated' => 0, 'status_changed' => 0, 'errors' => array());
    if ($api_result['success'] && isset($api_result['data']['RtnResult']) && $api_result['data']['RtnResult'] === '000') {
        $sync_result = savePaymentData($mysqli, $api_result['data'], $store);
        $total_sync['inserted'] += $sync_result['inserted'];
        $total_sync['skipped'] += $sync_result['skipped'];
        $total_sync['matched'] += $sync_result['matched'];
        $total_sync['updated'] += $sync_result['updated'];
        $total_sync['status_changed'] += $sync_result['status_changed'];
        $total_sync['errors'] = array_merge($total_sync['errors'], $sync_result['errors']);
    }

    $results[] = array(
        'store' => array(
            'br_uid'  => $store['br_uid'],
            'st_uid'  => $store['st_uid'],
            'st_name' => $store['st_name']
        ),
        'params'  => $params,
        'success' => $api_result['success'],
        'data_size' => $api_result['data']['DataSize'] ?? 0,
        'sync_result' => $sync_result,
        'error'   => $api_result['error']
    );
}

$mysqli->close();

// 결과 출력
header('Content-Type: application/json; charset=utf-8');

echo json_encode(array(
    'status'      => 'success',
    'sal_ymd'     => $today,
    'store_count' => count($results),
    'sync_total'  => array(
        'inserted' => $total_sync['inserted'],
        'skipped'  => $total_sync['skipped'],
        'matched'  => $total_sync['matched'],
        'updated'  => $total_sync['updated'],
        'status_changed' => $total_sync['status_changed'],
        'errors'   => $total_sync['errors']
    ),
    'results'     => $results
), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
