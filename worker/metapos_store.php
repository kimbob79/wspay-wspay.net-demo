<?php
/**
 * MetaPOS API - 매장 조회 및 DB 동기화
 * URL: https://webapi.metapos.co.kr/webapi/sales_search/api/storeInfo.asp
 *
 * 기능:
 * - MetaPOS API에서 매장 정보 조회
 * - 신규 매장은 INSERT
 * - 기존 매장 정보 변경 시 UPDATE + 히스토리 기록
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

/**
 * SQL escape 함수
 */
function sql_escape_string($str) {
    global $mysqli;
    return $mysqli->real_escape_string($str);
}

/**
 * SQL 쿼리 실행
 */
function sql_query($sql) {
    global $mysqli;
    return $mysqli->query($sql);
}

/**
 * SQL fetch (단일 row)
 */
function sql_fetch($sql) {
    global $mysqli;
    $result = $mysqli->query($sql);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

/**
 * SQL insert id
 */
function sql_insert_id() {
    global $mysqli;
    return $mysqli->insert_id;
}

// API 설정
$api_url = "https://webapi.metapos.co.kr/webapi/sales_search/api/storeInfo.asp";
$api_key = "5f0efb7dbe66de52458d2ef550f24090";  # 운영키: 5f0efb7dbe66de52458d2ef550f24090, 테스트키: 0675a2779cd8f3cdfa5eafaa3f408121
// $api_key = "0675a2779cd8f3cdfa5eafaa3f408121";

// 요청 파라미터 설정
$params = array(
    "cmd"    => "SL",            // 조회 구분 (고정값)
    "br_uid" => "B26010900001",  // 브랜드 고유 id > 운영모드: B26010900001, 테스트모드: B19052200002
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

/**
 * 매장 정보 동기화 함수
 * @param array $store_data API에서 받은 매장 데이터
 * @param string $br_uid 브랜드 고유 ID
 * @return array 처리 결과
 */
function syncStoreData($store_data, $br_uid) {

    $result = array(
        'inserted' => 0,
        'updated'  => 0,
        'skipped'  => 0,
        'errors'   => array(),
        'details'  => array()
    );

    // 비교할 필드 목록
    $compare_fields = array('br_name', 'st_name', 'st_biz_no', 'st_use', 'st_ceo_nm', 'st_tel', 'st_hp', 'st_addr');

    foreach ($store_data as $store) {
        $st_uid = $store['st_uid'];

        if (empty($st_uid)) {
            $result['errors'][] = "st_uid가 비어있는 데이터 발견";
            continue;
        }

        // 기존 데이터 조회
        $sql = "SELECT * FROM metapos_store WHERE st_uid = '" . sql_escape_string($st_uid) . "'";
        $existing = sql_fetch($sql);

        // API 데이터 정규화 (null -> 빈문자열)
        $new_data = array(
            'st_uid'    => $st_uid,
            'br_uid'    => $br_uid,
            'br_name'   => $store['br_name'] ?? '',
            'st_name'   => $store['st_name'] ?? '',
            'st_biz_no' => $store['st_biz_no'] ?? '',
            'st_use'    => $store['st_use'] ?? 'Y',
            'st_ceo_nm' => $store['st_ceo_nm'] ?? '',
            'st_tel'    => $store['st_tel'] ?? '',
            'st_hp'     => $store['st_hp'] ?? '',
            'st_addr'   => $store['st_addr'] ?? '',
            'st_data'   => json_encode($store, JSON_UNESCAPED_UNICODE)
        );

        if (!$existing) {
            // 신규 INSERT
            $insert_sql = "INSERT INTO metapos_store
                (st_uid, br_uid, br_name, st_name, st_biz_no, st_use, st_ceo_nm, st_tel, st_hp, st_addr, st_data, created_at, updated_at)
                VALUES (
                    '" . sql_escape_string($new_data['st_uid']) . "',
                    '" . sql_escape_string($new_data['br_uid']) . "',
                    '" . sql_escape_string($new_data['br_name']) . "',
                    '" . sql_escape_string($new_data['st_name']) . "',
                    '" . sql_escape_string($new_data['st_biz_no']) . "',
                    '" . sql_escape_string($new_data['st_use']) . "',
                    '" . sql_escape_string($new_data['st_ceo_nm']) . "',
                    '" . sql_escape_string($new_data['st_tel']) . "',
                    '" . sql_escape_string($new_data['st_hp']) . "',
                    '" . sql_escape_string($new_data['st_addr']) . "',
                    '" . sql_escape_string($new_data['st_data']) . "',
                    NOW(),
                    NOW()
                )";

            sql_query($insert_sql);
            $ms_id = sql_insert_id();

            // 히스토리 기록 (INSERT)
            $history_sql = "INSERT INTO metapos_store_history
                (ms_id, st_uid, change_type, changed_fields, old_data, new_data, created_at)
                VALUES (
                    '{$ms_id}',
                    '" . sql_escape_string($st_uid) . "',
                    'INSERT',
                    NULL,
                    NULL,
                    '" . sql_escape_string(json_encode($new_data, JSON_UNESCAPED_UNICODE)) . "',
                    NOW()
                )";
            sql_query($history_sql);

            $result['inserted']++;
            $result['details'][] = array(
                'st_uid' => $st_uid,
                'st_name' => $new_data['st_name'],
                'action' => 'INSERT'
            );

        } else {
            // 기존 데이터와 비교
            $changed_fields = array();
            $old_data = array();

            foreach ($compare_fields as $field) {
                $old_value = $existing[$field] ?? '';
                $new_value = $new_data[$field] ?? '';

                if ($old_value !== $new_value) {
                    $changed_fields[$field] = array(
                        'old' => $old_value,
                        'new' => $new_value
                    );
                    $old_data[$field] = $old_value;
                }
            }

            if (!empty($changed_fields)) {
                // 변경사항이 있으면 UPDATE
                $update_sql = "UPDATE metapos_store SET
                    br_uid = '" . sql_escape_string($new_data['br_uid']) . "',
                    br_name = '" . sql_escape_string($new_data['br_name']) . "',
                    st_name = '" . sql_escape_string($new_data['st_name']) . "',
                    st_biz_no = '" . sql_escape_string($new_data['st_biz_no']) . "',
                    st_use = '" . sql_escape_string($new_data['st_use']) . "',
                    st_ceo_nm = '" . sql_escape_string($new_data['st_ceo_nm']) . "',
                    st_tel = '" . sql_escape_string($new_data['st_tel']) . "',
                    st_hp = '" . sql_escape_string($new_data['st_hp']) . "',
                    st_addr = '" . sql_escape_string($new_data['st_addr']) . "',
                    st_data = '" . sql_escape_string($new_data['st_data']) . "',
                    updated_at = NOW()
                    WHERE st_uid = '" . sql_escape_string($st_uid) . "'";

                sql_query($update_sql);

                // 히스토리 기록 (UPDATE)
                $history_sql = "INSERT INTO metapos_store_history
                    (ms_id, st_uid, change_type, changed_fields, old_data, new_data, created_at)
                    VALUES (
                        '{$existing['ms_id']}',
                        '" . sql_escape_string($st_uid) . "',
                        'UPDATE',
                        '" . sql_escape_string(json_encode($changed_fields, JSON_UNESCAPED_UNICODE)) . "',
                        '" . sql_escape_string(json_encode($old_data, JSON_UNESCAPED_UNICODE)) . "',
                        '" . sql_escape_string(json_encode($new_data, JSON_UNESCAPED_UNICODE)) . "',
                        NOW()
                    )";
                sql_query($history_sql);

                $result['updated']++;
                $result['details'][] = array(
                    'st_uid' => $st_uid,
                    'st_name' => $new_data['st_name'],
                    'action' => 'UPDATE',
                    'changed_fields' => array_keys($changed_fields)
                );

            } else {
                // 변경사항 없음
                $result['skipped']++;
            }
        }
    }

    return $result;
}

// API 호출 실행
$result = callMetaposApi($api_url, $api_key, $params);

// 결과 출력
header('Content-Type: application/json; charset=utf-8');

if ($result['success'] && isset($result['data']['RtnResult']) && $result['data']['RtnResult'] === '000') {
    // API 호출 성공 - DB 동기화 실행
    $store_list = $result['data']['DataInfo'] ?? array();
    $sync_result = syncStoreData($store_list, $params['br_uid']);

    echo json_encode(array(
        'status'      => 'success',
        'api_params'  => $params,
        'api_result'  => array(
            'RtnResult' => $result['data']['RtnResult'],
            'DataSize'  => $result['data']['DataSize']
        ),
        'sync_result' => array(
            'total'    => count($store_list),
            'inserted' => $sync_result['inserted'],
            'updated'  => $sync_result['updated'],
            'skipped'  => $sync_result['skipped'],
            'errors'   => $sync_result['errors']
        ),
        'details'     => $sync_result['details']
    ), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} else {
    // API 호출 실패
    echo json_encode(array(
        'status'    => 'error',
        'http_code' => $result['http_code'],
        'error'     => $result['error'],
        'params'    => $params,
        'response'  => $result['response']
    ), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
