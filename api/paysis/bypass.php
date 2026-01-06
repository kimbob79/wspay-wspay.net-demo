<?php
/**
 * 페이시스(Paysis) API Bypass Proxy
 *
 * 제3의 서버에서 IP/방화벽 제한으로 페이시스 API를 직접 호출할 수 없을 때,
 * 이 서버를 통해 우회 호출하기 위한 프록시 엔드포인트
 *
 * 사용법:
 * POST /api/paysis/bypass.php
 *
 * 요청 헤더:
 * - Content-Type: application/json
 * - X-Bypass-Key: (설정된 bypass 인증키)
 * - X-Dal-Api-Key: (페이시스 dal-api-key)
 *
 * 요청 바디:
 * {
 *     "action": "pay" | "cancel",
 *     "data": { ... 페이시스 API 요청 데이터 ... }
 * }
 *
 * 응답: 페이시스 API 응답 그대로 반환
 */

// 에러 출력 방지 (JSON 응답 유지)
error_reporting(0);
ini_set('display_errors', 0);
date_default_timezone_set('Asia/Seoul');

// CORS 허용 (필요시)
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Bypass-Key, X-Dal-Api-Key');

// OPTIONS 요청 처리 (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include('./_common.php');

// ============================================
// 설정
// ============================================

// Bypass 인증키 (제3 서버에서 이 키를 알아야 호출 가능)
// 실제 운영시 이 값을 변경하거나 별도 설정파일에서 로드
define('BYPASS_AUTH_KEY', 'wsp_paysis_bypass_2025');

// 페이시스 API 엔드포인트
define('PAYSIS_API_PAY', 'https://apis.paysis.co.kr:9443/dalgate/api/v1/manual/pay');
define('PAYSIS_API_CANCEL', 'https://apis.paysis.co.kr:9443/dalgate/api/v1/manual/cancel');

// 허용된 IP 목록 (비어있으면 모두 허용, 설정하면 해당 IP만 허용)
$ALLOWED_IPS = [
    '117.52.20.162',
    '61.111.38.139',
    '14.55.246.103'
];

// 로그 디렉토리
define('LOG_DIR', __DIR__ . '/../../logs/api/paysis');

// ============================================
// 함수
// ============================================

/**
 * 실제 클라이언트 IP 가져오기 (프록시/로드밸런서 대응)
 */
function getRealClientIP() {
    // X-Forwarded-For 체크 (콤마로 여러 IP가 있을 수 있음, 첫번째가 원본)
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $real_ip = trim($ips[0]);
        if (filter_var($real_ip, FILTER_VALIDATE_IP)) {
            return $real_ip;
        }
    }

    // X-Real-IP 체크
    if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        $real_ip = trim($_SERVER['HTTP_X_REAL_IP']);
        if (filter_var($real_ip, FILTER_VALIDATE_IP)) {
            return $real_ip;
        }
    }

    // CF-Connecting-IP 체크 (Cloudflare)
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $real_ip = trim($_SERVER['HTTP_CF_CONNECTING_IP']);
        if (filter_var($real_ip, FILTER_VALIDATE_IP)) {
            return $real_ip;
        }
    }
 
    // 기본값
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * Bypass 로그 기록
 */
function writeBypassLog($action, $type, $data = []) {
    $log_dir = LOG_DIR;

    // 디렉토리 생성
    if (!is_dir($log_dir)) {
        @mkdir($log_dir, 0755, true);
    }

    $log_file = $log_dir . '/bypass_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s.') . sprintf('%03d', (microtime(true) - floor(microtime(true))) * 1000);

    // 민감정보 마스킹
    if (isset($data['cardNo'])) {
        $data['cardNo'] = substr($data['cardNo'], 0, 6) . '****' . substr($data['cardNo'], -4);
    }
    if (isset($data['certPw'])) {
        $data['certPw'] = '**';
    }
    if (isset($data['certNo'])) {
        $data['certNo'] = '******';
    }

    $remote_ip = getRealClientIP();
    $log_entry = "[{$timestamp}] [{$remote_ip}] [{$action}] [{$type}]";
    if (!empty($data)) {
        $log_entry .= " | " . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    $log_entry .= "\n";

    @file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

/**
 * JSON 응답 반환
 */
function jsonResponse($data, $http_code = 200) {
    http_response_code($http_code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 에러 응답
 */
function errorResponse($code, $message, $http_code = 400) {
    writeBypassLog('SYSTEM', 'ERROR', ['code' => $code, 'message' => $message]);
    jsonResponse([
        'resCode' => $code,
        'resMsg' => $message
    ], $http_code);
}

/**
 * 페이시스 API 호출
 */
function callPaysisAPI($url, $api_key, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'dal-api-key: ' . $api_key
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    return [
        'response' => $response,
        'http_code' => $http_code,
        'curl_error' => $curl_error
    ];
}

// ============================================
// 메인 로직
// ============================================

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('METHOD_NOT_ALLOWED', 'POST 요청만 허용됩니다.', 405);
}

// IP 체크 (허용 목록이 설정된 경우) - 프록시 대응
$remote_ip = getRealClientIP();
if (!empty($ALLOWED_IPS) && !in_array($remote_ip, $ALLOWED_IPS)) {
    writeBypassLog('SYSTEM', 'BLOCKED_IP', ['ip' => $remote_ip]);
    errorResponse('IP_NOT_ALLOWED', '허용되지 않은 IP입니다.', 403);
}

// Bypass 인증키 체크
$bypass_key = $_SERVER['HTTP_X_BYPASS_KEY'] ?? '';
if ($bypass_key !== BYPASS_AUTH_KEY) {
    writeBypassLog('SYSTEM', 'AUTH_FAILED', ['provided_key' => substr($bypass_key, 0, 5) . '***']);
    errorResponse('AUTH_FAILED', 'Bypass 인증에 실패했습니다.', 401);
}

// dal-api-key 체크
$dal_api_key = $_SERVER['HTTP_X_DAL_API_KEY'] ?? '';
if (empty($dal_api_key)) {
    errorResponse('MISSING_API_KEY', 'X-Dal-Api-Key 헤더가 필요합니다.', 400);
}

// 요청 바디 파싱
$input = file_get_contents('php://input');
$request = json_decode($input, true);

if (!$request) {
    errorResponse('INVALID_JSON', '요청 데이터가 올바른 JSON 형식이 아닙니다.', 400);
}

// 필수 필드 체크
if (empty($request['action'])) {
    errorResponse('MISSING_ACTION', 'action 필드가 필요합니다. (pay 또는 cancel)', 400);
}
if (empty($request['data']) || !is_array($request['data'])) {
    errorResponse('MISSING_DATA', 'data 필드가 필요합니다.', 400);
}

$action = $request['action'];
$data = $request['data'];

// 유효한 action 체크
if (!in_array($action, ['pay', 'cancel'])) {
    errorResponse('INVALID_ACTION', 'action은 pay 또는 cancel만 가능합니다.', 400);
}

// 요청 로그
writeBypassLog($action, 'REQUEST', $data);

// API 엔드포인트 결정
$api_url = ($action === 'pay') ? PAYSIS_API_PAY : PAYSIS_API_CANCEL;

// 페이시스 API 호출
$api_result = callPaysisAPI($api_url, $dal_api_key, $data);

// curl 에러 체크
if ($api_result['curl_error']) {
    writeBypassLog($action, 'CURL_ERROR', [
        'http_code' => $api_result['http_code'],
        'curl_error' => $api_result['curl_error']
    ]);
    jsonResponse([
        'resCode' => 'CURL_ERROR',
        'resMsg' => 'API 통신 오류: ' . $api_result['curl_error']
    ], 502);
}

// 응답 파싱
$response = json_decode($api_result['response'], true);

if (!$response) {
    writeBypassLog($action, 'PARSE_ERROR', [
        'http_code' => $api_result['http_code'],
        'raw_response' => substr($api_result['response'], 0, 500)
    ]);
    jsonResponse([
        'resCode' => 'PARSE_ERROR',
        'resMsg' => 'API 응답 파싱 오류 (HTTP: ' . $api_result['http_code'] . ')'
    ], 502);
}

// 응답 로그
writeBypassLog($action, 'RESPONSE', $response);

// ============================================
// 결제 성공 시 DB 저장 (수기결제 내역 - g5_payment_keyin)
// ============================================
if (isset($response['resCode']) && $response['resCode'] === '0000') {
    // 요청 데이터에서 필요한 값 추출
    $mid = $data['mid'] ?? '';
    $ordNo = $data['ordNo'] ?? '';
    $amt = $data['goodsAmt'] ?? '0';
    $cardNo = $data['cardNo'] ?? '';
    $quota = $data['quotaMon'] ?? '00';
    $buyerNm = $data['buyerNm'] ?? '';
    $goodsNm = $data['goodsNm'] ?? '';

    // 응답 데이터에서 필요한 값 추출
    $tid = $response['tid'] ?? '';
    $appNo = $response['appNo'] ?? '';
    $appDate = $response['appDate'] ?? '';
    $vanIssCpCd = $response['vanIssCpCd'] ?? '';  // 발급사코드
    $vanCpCd = $response['vanCpCd'] ?? '';  // 매입사코드
    $resCode = $response['resCode'] ?? '';
    $resMsg = $response['resMsg'] ?? '';

    // 카드번호 마스킹
    $cardNo_masked = strlen($cardNo) >= 10 ? substr($cardNo, 0, 6) . '****' . substr($cardNo, -4) : $cardNo;

    // MID로 Keyin 설정 조회 (개별설정 또는 대표가맹점설정)
    $keyin_sql = "SELECT k.*, m.mpc_mid, m.mpc_pg_code, m.mpc_pg_name, m.mpc_type
        FROM g5_member_keyin_config k
        LEFT JOIN g5_manual_payment_config m ON k.mpc_id = m.mpc_id
        WHERE k.mkc_use = 'Y' AND k.mkc_status = 'active'
        AND (
            (k.mpc_id IS NULL AND k.mkc_mid = '{$mid}')
            OR (k.mpc_id IS NOT NULL AND m.mpc_mid = '{$mid}')
        )";
    $keyin_result = sql_query($keyin_sql);
    $keyin_count = sql_num_rows($keyin_result);

    $keyin_config = null;
    $target_mb_id = '';
    $mkc_id = 0;

    if ($keyin_count == 1) {
        $keyin_config = sql_fetch_array($keyin_result);
        $target_mb_id = $keyin_config['mb_id'];
        $mkc_id = $keyin_config['mkc_id'];
    } else if ($keyin_count > 1) {
        // 대표가맹점 설정을 여러 가맹점이 공유하는 경우 → mkc_oid로 구분
        $mkc_oid = substr($ordNo, 0, 4);
        while ($row = sql_fetch_array($keyin_result)) {
            if ($row['mkc_oid'] == $mkc_oid) {
                $keyin_config = $row;
                $target_mb_id = $row['mb_id'];
                $mkc_id = $row['mkc_id'];
                break;
            }
        }
    }

    // 가맹점 정보 조회
    $merchant = array();
    if ($target_mb_id) {
        $merchant = sql_fetch("SELECT mb_id, mb_nick, mb_1, mb_2, mb_3, mb_4, mb_5, mb_6
            FROM g5_member WHERE mb_id = '{$target_mb_id}'");
    }

    // PG 정보
    $pg_code = $keyin_config['mpc_pg_code'] ?? $keyin_config['mkc_pg_code'] ?? 'paysis';
    $pg_name = $keyin_config['mpc_pg_name'] ?? $keyin_config['mkc_pg_name'] ?? '페이시스';
    $auth_type = $keyin_config['mpc_type'] ?? $keyin_config['mkc_type'] ?? 'noauth';
    $merchant_oid = $keyin_config['mkc_oid'] ?? '';

    if ($action === 'pay') {
        // ==================== 결제 ====================
        // 중복 체크
        $existing = sql_fetch("SELECT pk_id FROM g5_payment_keyin WHERE pk_order_no = '{$ordNo}'");

        if (!$existing['pk_id']) {
            // g5_payment_keyin에 저장
            $insert_sql = "INSERT INTO g5_payment_keyin SET
                pk_order_no = '" . sql_escape_string($ordNo) . "',
                pk_merchant_oid = '" . sql_escape_string($merchant_oid) . "',
                mb_id = '" . sql_escape_string($target_mb_id) . "',
                mkc_id = '{$mkc_id}',
                pk_pg_code = '" . sql_escape_string($pg_code) . "',
                pk_pg_name = '" . sql_escape_string($pg_name) . "',
                pk_mid = '" . sql_escape_string($mid) . "',
                pk_auth_type = '" . sql_escape_string($auth_type) . "',
                pk_amount = '" . sql_escape_string($amt) . "',
                pk_installment = '" . sql_escape_string($quota) . "',
                pk_goods_name = '" . sql_escape_string($goodsNm) . "',
                pk_buyer_name = '" . sql_escape_string($buyerNm) . "',
                pk_buyer_phone = '',
                pk_buyer_email = '',
                pk_card_no_masked = '" . sql_escape_string($cardNo_masked) . "',
                pk_status = 'approved',
                pk_app_no = '" . sql_escape_string($appNo) . "',
                pk_app_date = '" . sql_escape_string($appDate) . "',
                pk_tid = '" . sql_escape_string($tid) . "',
                pk_res_code = '" . sql_escape_string($resCode) . "',
                pk_res_msg = '" . sql_escape_string($resMsg) . "',
                pk_card_issuer = '" . sql_escape_string($vanIssCpCd) . "',
                pk_card_acquirer = '" . sql_escape_string($vanCpCd) . "',
                pk_mb_1 = '" . sql_escape_string($merchant['mb_1'] ?? '') . "',
                pk_mb_2 = '" . sql_escape_string($merchant['mb_2'] ?? '') . "',
                pk_mb_3 = '" . sql_escape_string($merchant['mb_3'] ?? '') . "',
                pk_mb_4 = '" . sql_escape_string($merchant['mb_4'] ?? '') . "',
                pk_mb_5 = '" . sql_escape_string($merchant['mb_5'] ?? '') . "',
                pk_mb_6 = '" . sql_escape_string($merchant['mb_6'] ?? '') . "',
                pk_mb_6_name = '" . sql_escape_string($merchant['mb_nick'] ?? '') . "',
                pk_request_data = '" . sql_escape_string(json_encode($data, JSON_UNESCAPED_UNICODE)) . "',
                pk_response_data = '" . sql_escape_string(json_encode($response, JSON_UNESCAPED_UNICODE)) . "',
                pk_operator_id = 'bypass',
                pk_created_at = NOW(),
                pk_updated_at = NOW()";

            $insert_result = sql_query($insert_sql);
            $pk_id = sql_insert_id();

            if ($insert_result && $pk_id) {
                // 영수증 URL 추가
                $response['receiptUrl'] = 'https://wspay.net/receipt_keyin.php?pk_id=' . $pk_id;

                writeBypassLog($action, 'DB_INSERT_SUCCESS', [
                    'pk_id' => $pk_id,
                    'mid' => $mid,
                    'ordNo' => $ordNo,
                    'mb_id' => $target_mb_id,
                    'receiptUrl' => $response['receiptUrl']
                ]);
            } else {
                writeBypassLog($action, 'DB_INSERT_FAILED', [
                    'mid' => $mid,
                    'ordNo' => $ordNo,
                    'reason' => $target_mb_id ? 'insert failed' : 'keyin config not found'
                ]);
            }
        } else {
            writeBypassLog($action, 'DB_DUPLICATE', [
                'pk_id' => $existing['pk_id'],
                'ordNo' => $ordNo
            ]);
        }
    } else if ($action === 'cancel') {
        // ==================== 취소 ====================
        $orgTid = $data['orgTid'] ?? '';
        $canAmt = $data['canAmt'] ?? $amt;

        // 원거래 조회
        $original = sql_fetch("SELECT * FROM g5_payment_keyin WHERE pk_tid = '{$orgTid}'");

        if ($original['pk_id']) {
            // 원거래 상태 업데이트
            $cancel_sql = "UPDATE g5_payment_keyin SET
                pk_status = 'cancelled',
                pk_cancel_amount = pk_cancel_amount + " . intval($canAmt) . ",
                pk_cancel_date = NOW(),
                pk_updated_at = NOW()
                WHERE pk_id = '{$original['pk_id']}'";
            sql_query($cancel_sql);

            writeBypassLog($action, 'DB_CANCEL_SUCCESS', [
                'pk_id' => $original['pk_id'],
                'orgTid' => $orgTid,
                'canAmt' => $canAmt
            ]);
        } else {
            writeBypassLog($action, 'DB_CANCEL_FAILED', [
                'orgTid' => $orgTid,
                'reason' => 'original transaction not found'
            ]);
        }
    }
}

// 페이시스 응답 그대로 반환
jsonResponse($response);
