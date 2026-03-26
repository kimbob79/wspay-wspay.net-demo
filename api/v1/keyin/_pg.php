<?php
/**
 * Keyin 공개 API - PG사별 결제 호출 함수
 * manual_payment_api.php에서 결제 관련 함수만 복사
 *
 * 지원 PG: paysis, rootup, stn, winglobal
 */

// ============================================
// 유틸리티 함수
// ============================================

/**
 * 카드번호 마스킹 (앞6자리 + **** + 뒤4자리)
 */
if (!function_exists('maskCardNumber')) {
    function maskCardNumber($card_no) {
        $len = strlen($card_no);
        if ($len < 10) return str_repeat('*', $len);
        $first = substr($card_no, 0, 6);
        $last = substr($card_no, -4);
        $middle_len = $len - 10;
        $middle = str_repeat('*', max(4, $middle_len));
        return $first . $middle . $last;
    }
}

/**
 * 주문번호 생성
 * - 페이시스: 정확히 30자
 * - 섹타나인: 정확히 20자
 * - 기타: 19자
 */
if (!function_exists('generateOrderNumber')) {
    function generateOrderNumber($merchant_oid, $pg_code = 'paysis') {
        if (!$merchant_oid) {
            $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $alphanumeric = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $merchant_oid = $letters[rand(0, 25)] . $alphanumeric[rand(0, 35)] . $alphanumeric[rand(0, 35)] . $alphanumeric[rand(0, 35)];
        }
        $oid = $merchant_oid;

        if ($pg_code === 'paysis') {
            $date = date('Ymd');
            $time = date('His');
            $rand = strtoupper(substr(md5(microtime(true) . mt_rand()), 0, 12));
            return "{$oid}{$date}{$time}{$rand}";
        } else if ($pg_code === 'stn') {
            $date = date('Ymd');
            $time = date('His');
            $rand = strtoupper(substr(md5(microtime(true) . mt_rand()), 0, 2));
            return "{$oid}{$date}{$time}{$rand}";
        } else {
            $yymm = date('ym');
            $hhmm = date('Hi');
            $ss = date('s');
            $rand = strtoupper(substr(md5(microtime(true) . mt_rand()), 0, 2));
            return "{$oid}-{$yymm}-{$hhmm}-{$ss}{$rand}";
        }
    }
}

/**
 * PG API 로그 기록
 * 경로: /logs/api/{PG코드}/{날짜}.log
 */
if (!function_exists('writeApiLog')) {
    function writeApiLog($pg_code, $action, $type, $data) {
        $base_path = dirname(__FILE__) . '/../../../logs/api';
        $pg_path = $base_path . '/' . $pg_code;

        if (!is_dir($base_path)) @mkdir($base_path, 0755, true);
        if (!is_dir($pg_path)) @mkdir($pg_path, 0755, true);

        $log_file = $pg_path . '/' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s.') . substr(microtime(), 2, 3);
        $log_entry = sprintf("[%s] [%s] [%s] %s\n", $timestamp, strtoupper($action), $type, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        @file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
}

/**
 * 요청 데이터에서 민감정보 마스킹 (DB 저장용)
 */
function keyin_mask_request_data($data, $pg_code) {
    $masked = $data;
    if ($pg_code === 'rootup') {
        if (isset($masked['card_num'])) $masked['card_num'] = maskCardNumber($masked['card_num']);
        if (isset($masked['card_pw'])) $masked['card_pw'] = '**';
        if (isset($masked['auth_num'])) $masked['auth_num'] = '******';
    } else if ($pg_code === 'stn') {
        if (isset($masked['cardNo'])) $masked['cardNo'] = maskCardNumber($masked['cardNo']);
        if (isset($masked['passwd'])) $masked['passwd'] = '**';
        if (isset($masked['regNo'])) $masked['regNo'] = '******';
    } else if ($pg_code === 'winglobal') {
        if (isset($masked['pay']['card']['number'])) $masked['pay']['card']['number'] = maskCardNumber($masked['pay']['card']['number']);
        if (isset($masked['pay']['metadata']['authPw'])) $masked['pay']['metadata']['authPw'] = '**';
        if (isset($masked['pay']['metadata']['authDob'])) $masked['pay']['metadata']['authDob'] = '******';
    } else {
        // paysis
        if (isset($masked['cardNo'])) $masked['cardNo'] = maskCardNumber($masked['cardNo']);
        if (isset($masked['certPw'])) $masked['certPw'] = '**';
        if (isset($masked['certNo'])) $masked['certNo'] = '******';
    }
    return $masked;
}

// ============================================
// PG사별 API 설정값 추출
// ============================================

/**
 * Keyin 설정에서 PG사별 인증 정보 추출
 *
 * @param array $keyin g5_member_keyin_config + g5_manual_payment_config JOIN 결과
 * @param string $pg_code PG사 코드
 * @return array ['api_key', 'mid', 'mkey', 'tid', 'mbr_no', 'pg_name', 'auth_type', 'merchant_oid']
 */
function keyin_resolve_credentials($keyin, $pg_code) {
    $pg_name = $keyin['mpc_id'] ? $keyin['mpc_pg_name'] : $keyin['mkc_pg_name'];
    $auth_type = $keyin['mpc_id'] ? $keyin['mpc_type'] : $keyin['mkc_type'];
    $merchant_oid = $keyin['mkc_oid'] ?: '';

    $api_key = '';
    $mid = '';
    $mkey = '';
    $tid = '';
    $mbr_no = '';

    if ($pg_code === 'rootup') {
        $api_key = $keyin['mpc_id'] ? $keyin['mpc_rootup_key'] : $keyin['mkc_api_key'];
        $mid = $keyin['mpc_id'] ? $keyin['mpc_rootup_mid'] : $keyin['mkc_mid'];
        $tid = $keyin['mpc_id'] ? $keyin['mpc_rootup_tid'] : $keyin['mkc_mkey'];
    } else if ($pg_code === 'stn') {
        $mbr_no = $keyin['mpc_id'] ? $keyin['mpc_stn_mbrno'] : $keyin['mkc_mid'];
        $api_key = $keyin['mpc_id'] ? $keyin['mpc_stn_apikey'] : $keyin['mkc_api_key'];
        $mid = $mbr_no;
    } else if ($pg_code === 'winglobal') {
        $tid = $keyin['mpc_id'] ? $keyin['mpc_winglobal_tid'] : $keyin['mkc_mid'];
        $api_key = $keyin['mpc_id'] ? $keyin['mpc_winglobal_apikey'] : $keyin['mkc_api_key'];
        $mid = $tid;
    } else {
        // paysis
        $api_key = $keyin['mpc_id'] ? $keyin['mpc_api_key'] : $keyin['mkc_api_key'];
        $mid = $keyin['mpc_id'] ? $keyin['mpc_mid'] : $keyin['mkc_mid'];
        $mkey = $keyin['mpc_id'] ? $keyin['mpc_mkey'] : $keyin['mkc_mkey'];
    }

    return compact('api_key', 'mid', 'mkey', 'tid', 'mbr_no', 'pg_name', 'auth_type', 'merchant_oid');
}

// ============================================
// 페이시스 (Paysis)
// ============================================

function callPaysisPaymentAPI_v2($api_key, $data) {
    $url = 'https://apis.paysis.co.kr:9443/dalgate/api/v1/manual/pay';
    $pg_code = 'paysis';
    $action = 'pay';

    $log_data = $data;
    if (isset($log_data['cardNo'])) $log_data['cardNo'] = maskCardNumber($log_data['cardNo']);
    if (isset($log_data['certPw'])) $log_data['certPw'] = '**';
    if (isset($log_data['certNo'])) $log_data['certNo'] = '******';
    writeApiLog($pg_code, $action, 'REQUEST', $log_data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'dal-api-key: ' . $api_key]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        writeApiLog($pg_code, $action, 'ERROR', ['http_code' => $http_code, 'curl_error' => $curl_error]);
        return ['resCode' => 'CURL_ERROR', 'resMsg' => 'API 통신 오류: ' . $curl_error];
    }

    $result = json_decode($response, true);
    if (!$result) {
        writeApiLog($pg_code, $action, 'ERROR', ['http_code' => $http_code, 'raw_response' => substr($response, 0, 500)]);
        return ['resCode' => 'PARSE_ERROR', 'resMsg' => 'API 응답 파싱 오류 (HTTP: ' . $http_code . ')'];
    }

    writeApiLog($pg_code, $action, 'RESPONSE', $result);
    return $result;
}

// ============================================
// 루트업 (Routeup)
// ============================================

function callRoutupPaymentAPI_v2($pay_key, $data) {
    $url = 'https://api.routeup.kr/api/v2/pay/hand';
    $pg_code = 'rootup';
    $action = 'pay';

    $log_data = $data;
    if (isset($log_data['card_num'])) $log_data['card_num'] = maskCardNumber($log_data['card_num']);
    if (isset($log_data['card_pw'])) $log_data['card_pw'] = '**';
    if (isset($log_data['auth_num'])) $log_data['auth_num'] = '******';
    writeApiLog($pg_code, $action, 'REQUEST', $log_data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: ' . $pay_key]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        writeApiLog($pg_code, $action, 'ERROR', ['http_code' => $http_code, 'curl_error' => $curl_error]);
        return ['resCode' => 'CURL_ERROR', 'resMsg' => 'API 통신 오류: ' . $curl_error];
    }

    $result = json_decode($response, true);
    if (!$result) {
        writeApiLog($pg_code, $action, 'ERROR', ['http_code' => $http_code, 'raw_response' => substr($response, 0, 500)]);
        return ['resCode' => 'PARSE_ERROR', 'resMsg' => 'API 응답 파싱 오류 (HTTP: ' . $http_code . ')'];
    }

    writeApiLog($pg_code, $action, 'RESPONSE', $result);
    return normalizeRoutupResponse_v2($result);
}

function normalizeRoutupResponse_v2($response) {
    $result_cd = $response['result_cd'] ?? '';
    $is_success = ($result_cd === '0000');
    return [
        'resCode' => $is_success ? '0000' : ($result_cd ?: 'UNKNOWN'),
        'resMsg' => $response['result_msg'] ?? '',
        'appNo' => $response['appr_num'] ?? '',
        'appDate' => $response['trx_dttm'] ?? '',
        'tid' => $response['trx_id'] ?? '',
        'vanIssCpCd' => $response['issuer'] ?? '',
        'vanCpCd' => $response['acquirer'] ?? '',
        '_original' => $response
    ];
}

// ============================================
// 섹타나인 (STN)
// ============================================

function callStnPaymentAPI_v2($data) {
    $url = 'https://relay.mainpay.co.kr/v1/api/payments/payment/card-keyin/trans';
    $pg_code = 'stn';
    $action = 'pay';

    $log_data = $data;
    if (isset($log_data['cardNo'])) $log_data['cardNo'] = maskCardNumber($log_data['cardNo']);
    if (isset($log_data['passwd'])) $log_data['passwd'] = '**';
    if (isset($log_data['regNo'])) $log_data['regNo'] = '******';
    if (isset($log_data['signature'])) $log_data['signature'] = substr($log_data['signature'], 0, 16) . '...';
    writeApiLog($pg_code, $action, 'REQUEST', $log_data);

    $post_data = http_build_query($data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded; charset=utf-8']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        writeApiLog($pg_code, $action, 'ERROR', ['http_code' => $http_code, 'curl_error' => $curl_error]);
        return ['resCode' => 'CURL_ERROR', 'resMsg' => 'API 통신 오류: ' . $curl_error];
    }

    $result = json_decode($response, true);
    if (!$result) {
        writeApiLog($pg_code, $action, 'ERROR', ['http_code' => $http_code, 'raw_response' => substr($response, 0, 500)]);
        return ['resCode' => 'PARSE_ERROR', 'resMsg' => 'API 응답 파싱 오류 (HTTP: ' . $http_code . ')'];
    }

    writeApiLog($pg_code, $action, 'RESPONSE', $result);
    return normalizeStnResponse_v2($result);
}

function normalizeStnResponse_v2($response) {
    $result_code = $response['resultCode'] ?? '';
    $is_success = ($result_code === '200');
    $data = $response['data'] ?? [];

    $app_date = '';
    if (!empty($data['tranDate']) && !empty($data['tranTime'])) {
        $app_date = $data['tranDate'] . $data['tranTime'];
    }

    return [
        'resCode' => $is_success ? '0000' : ($result_code ?: 'UNKNOWN'),
        'resMsg' => $response['resultMessage'] ?? '',
        'appNo' => $data['applNo'] ?? '',
        'appDate' => $app_date,
        'tid' => $data['refNo'] ?? '',
        'vanIssCpCd' => $data['issueCompanyName'] ?? '',
        'vanCpCd' => $data['acqCompanyName'] ?? '',
        '_stn_data' => $data,
        '_original' => $response
    ];
}

// ============================================
// 윈글로벌 (Winglobal)
// ============================================

function callWinglobalPaymentAPI_v2($pay_key, $data) {
    $url = 'https://api.winglobalpay.com/api/pay';
    $pg_code = 'winglobal';
    $action = 'pay';

    $log_data = $data;
    if (isset($log_data['pay']['card']['number'])) $log_data['pay']['card']['number'] = maskCardNumber($log_data['pay']['card']['number']);
    if (isset($log_data['pay']['metadata']['authPw'])) $log_data['pay']['metadata']['authPw'] = '**';
    if (isset($log_data['pay']['metadata']['authDob'])) $log_data['pay']['metadata']['authDob'] = '******';
    writeApiLog($pg_code, $action, 'REQUEST', $log_data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: ' . $pay_key]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        writeApiLog($pg_code, $action, 'ERROR', ['http_code' => $http_code, 'curl_error' => $curl_error]);
        return ['resCode' => 'CURL_ERROR', 'resMsg' => 'API 통신 오류: ' . $curl_error];
    }

    $result = json_decode($response, true);
    if (!$result) {
        writeApiLog($pg_code, $action, 'ERROR', ['http_code' => $http_code, 'raw_response' => substr($response, 0, 500)]);
        return ['resCode' => 'PARSE_ERROR', 'resMsg' => 'API 응답 파싱 오류 (HTTP: ' . $http_code . ')'];
    }

    writeApiLog($pg_code, $action, 'RESPONSE', $result);
    return normalizeWinglobalResponse_v2($result);
}

function normalizeWinglobalResponse_v2($response) {
    $result = $response['result'] ?? [];
    $pay = $response['pay'] ?? [];
    $card = $pay['card'] ?? [];

    $result_cd = $result['resultCd'] ?? '';
    $is_success = ($result_cd === '0000');

    $res_msg = $result['resultMsg'] ?? '';
    if (!empty($result['advanceMsg']) && $result['advanceMsg'] !== $res_msg) {
        $res_msg .= ' - ' . $result['advanceMsg'];
    }

    return [
        'resCode' => $is_success ? '0000' : ($result_cd ?: 'UNKNOWN'),
        'resMsg' => $res_msg,
        'appNo' => $pay['authCd'] ?? '',
        'appDate' => $result['create'] ?? '',
        'tid' => $pay['trxId'] ?? '',
        'vanIssCpCd' => $card['issuer'] ?? '',
        'vanCpCd' => $card['cardType'] ?? '',
        '_winglobal_data' => $pay,
        '_original' => $response
    ];
}
