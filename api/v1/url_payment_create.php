<?php
/**
 * URL 결제 생성 API
 *
 * @endpoint POST /api/v1/url_payment_create.php
 * @version 1.0.0
 *
 * @description
 * URL 결제 링크를 생성하는 REST API입니다.
 * 가맹점 관리자나 외부 시스템에서 URL 결제를 프로그래밍 방식으로 생성할 수 있습니다.
 *
 * @request
 * Content-Type: application/json
 *
 * @body
 * {
 *   "api_key": "인증키 (필수)",
 *   "mb_id": "가맹점 ID (필수)",
 *   "mkc_id": "Keyin 설정 ID (필수)",
 *   "goods_name": "상품명 (필수)",
 *   "amount": "결제금액 (필수)",
 *   "buyer_name": "구매자명 (필수)",
 *   "buyer_phone": "구매자 연락처 (필수)",
 *   "seller_name": "판매자명 (선택, 기본값: 가맹점명)",
 *   "seller_phone": "판매자 연락처 (선택)",
 *   "goods_desc": "상품 설명 (선택)",
 *   "memo": "관리용 메모 (선택)",
 *   "expire_date": "만료일 YYYYMMDD (선택, 기본값: 내일)",
 *   "expire_time": "만료시각 HH:MM (선택, 기본값: 23:00)",
 *   "send_sms": "SMS 발송 여부 Y/N (선택, 기본값: N)"
 * }
 *
 * @response
 * {
 *   "success": true/false,
 *   "message": "결과 메시지",
 *   "data": {
 *     "up_id": "URL결제 ID",
 *     "up_code": "URL결제 코드",
 *     "payment_url": "결제 URL",
 *     "amount": "결제금액",
 *     "expire_datetime": "만료일시",
 *     "sms_sent": "SMS 발송 여부"
 *   }
 * }
 */

error_reporting(E_ALL);
ini_set("display_errors", 0);
date_default_timezone_set('Asia/Seoul');

// CORS 헤더
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

// OPTIONS 요청 처리 (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ========================================
// Gnuboard 프레임워크 로드 (로그인 체크 없이 DB만 연결)
// ========================================
include_once(dirname(__FILE__) . '/_common.php');

// ========================================
// 헬퍼 함수
// ========================================

/**
 * JSON 응답 출력
 */
function json_response($success, $message, $data = null, $http_code = 200) {
    http_response_code($http_code);

    $response = [
        'success' => $success,
        'message' => $message
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    // 로그 기록
    write_url_api_log('create', $success ? 'RESPONSE' : 'ERROR', $response);

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * URL 코드 생성 함수 (9자리 영숫자)
 */
function generate_url_code() {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $max_attempts = 100;

    for ($i = 0; $i < $max_attempts; $i++) {
        $code = '';
        for ($j = 0; $j < 9; $j++) {
            $code .= $characters[random_int(0, 61)];
        }

        // 중복 체크
        $check = sql_fetch("SELECT up_id FROM g5_url_payment WHERE up_code = '" . sql_real_escape_string($code) . "'");
        if (!$check['up_id']) {
            return $code;
        }
    }

    // 실패 시 타임스탬프 기반
    return substr(strtoupper(base_convert(microtime(true) * 10000, 10, 36)), 0, 9);
}

/**
 * SMS 발송 함수 (알리고 API)
 */
function send_url_payment_sms($phone, $message) {
    $sms_url = "https://apis.aligo.in/send/";
    $sms = array(
        'user_id' => 'wspay',
        'key' => 'v5smv1ajl0s4xrx1e9db2luomlycrkqz',
        'sender' => '01073651990',
        'receiver' => preg_replace('/[^0-9]/', '', $phone),
        'msg' => $message,
        'msg_type' => (mb_strlen($message, 'UTF-8') > 80) ? 'LMS' : 'SMS',
        'testmode_yn' => 'N'
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sms_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $sms);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $ret = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return array(
            'success' => false,
            'message' => 'cURL Error: ' . $error,
            'msg_id' => '',
            'response' => ''
        );
    }

    curl_close($ch);

    $result = json_decode($ret, true);

    return array(
        'success' => (isset($result['result_code']) && intval($result['result_code']) > 0),
        'message' => $result['message'] ?? 'API 오류',
        'msg_id' => $result['msg_id'] ?? '',
        'response' => $ret
    );
}

/**
 * API 키 검증
 */
function validate_api_key($api_key) {
    if (empty($api_key)) {
        return false;
    }

    // API 키 테이블에서 검증 (g5_api_keys 테이블이 있다면)
    // 현재는 간단한 검증 로직 사용
    $api_config = sql_fetch("SELECT * FROM g5_api_config WHERE ac_key = '" . sql_real_escape_string($api_key) . "' AND ac_status = 'active'");

    if ($api_config['ac_id']) {
        return $api_config;
    }

    // 관리자 mb_id + 고정 시크릿 조합으로도 인증 가능
    // 형식: {mb_id}:{secret}
    if (strpos($api_key, ':') !== false) {
        list($mb_id, $secret) = explode(':', $api_key, 2);
        $admin = sql_fetch("SELECT * FROM g5_member WHERE mb_id = '" . sql_real_escape_string($mb_id) . "' AND mb_level >= 10");
        if ($admin['mb_id'] && $secret === md5($admin['mb_id'] . $admin['mb_datetime'])) {
            return ['type' => 'admin', 'mb_id' => $admin['mb_id']];
        }
    }

    return false;
}

// ========================================
// 요청 처리
// ========================================

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'POST 요청만 허용됩니다.', null, 405);
}

// 요청 데이터 파싱
$input_raw = file_get_contents('php://input');
$input = json_decode($input_raw, true);

// JSON 파싱 실패 시 POST 데이터 사용
if (json_last_error() !== JSON_ERROR_NONE) {
    $input = $_POST;
}

// 로그 기록 (요청)
write_url_api_log('create', 'REQUEST', $input);

// API 키 검증 (헤더 또는 바디에서)
$api_key = $_SERVER['HTTP_X_API_KEY'] ?? $input['api_key'] ?? '';

// API 키 검증을 선택적으로 적용 (현재는 비활성화)
// 실제 운영시에는 아래 주석을 해제하여 활성화
/*
$auth = validate_api_key($api_key);
if (!$auth) {
    json_response(false, '유효하지 않은 API 키입니다.', null, 401);
}
*/

// ========================================
// 필수 파라미터 검증
// ========================================
$mb_id = isset($input['mb_id']) ? trim($input['mb_id']) : '';
$mkc_id = isset($input['mkc_id']) ? intval($input['mkc_id']) : 0;
$goods_name = isset($input['goods_name']) ? trim($input['goods_name']) : '';
$amount = isset($input['amount']) ? intval(str_replace(',', '', $input['amount'])) : 0;
$buyer_name = isset($input['buyer_name']) ? trim($input['buyer_name']) : '';
$buyer_phone = isset($input['buyer_phone']) ? trim($input['buyer_phone']) : '';

// 선택 파라미터
$seller_name = isset($input['seller_name']) ? trim($input['seller_name']) : '';
$seller_phone = isset($input['seller_phone']) ? trim($input['seller_phone']) : '';
$goods_desc = isset($input['goods_desc']) ? trim($input['goods_desc']) : '';
$memo = isset($input['memo']) ? trim($input['memo']) : '';
$expire_date = isset($input['expire_date']) ? preg_replace('/[^0-9]/', '', $input['expire_date']) : '';
$expire_time = isset($input['expire_time']) ? trim($input['expire_time']) : '23:00';
$send_sms = isset($input['send_sms']) && strtoupper($input['send_sms']) === 'Y';

// 필수값 체크 (mkc_id는 선택 - 없으면 자동 선택)
$errors = [];
if (empty($mb_id)) $errors[] = 'mb_id (가맹점 ID)';
if (empty($goods_name)) $errors[] = 'goods_name (상품명)';
if ($amount <= 0) $errors[] = 'amount (결제금액)';
if (empty($buyer_name)) $errors[] = 'buyer_name (구매자명)';
if (empty($buyer_phone)) $errors[] = 'buyer_phone (구매자 연락처)';

if (!empty($errors)) {
    json_response(false, '필수 파라미터가 누락되었습니다: ' . implode(', ', $errors), null, 400);
}

// ========================================
// 가맹점 정보 조회
// ========================================
$merchant = sql_fetch("SELECT * FROM g5_member WHERE mb_id = '" . sql_real_escape_string($mb_id) . "'");
if (!$merchant['mb_id']) {
    json_response(false, '존재하지 않는 가맹점입니다.', ['mb_id' => $mb_id], 404);
}

// 가맹점 레벨 체크 (level 3 = 가맹점)
if ($merchant['mb_level'] != 3) {
    json_response(false, '가맹점(레벨3)만 URL결제를 생성할 수 있습니다.', ['mb_level' => $merchant['mb_level']], 400);
}

// 수기결제 허용 체크
if ($merchant['mb_mailling'] != '1') {
    json_response(false, '수기결제가 허용되지 않은 가맹점입니다.', null, 400);
}

// ========================================
// Keyin 설정 확인
// ========================================
$keyin = null;

if ($mkc_id > 0) {
    // mkc_id가 지정된 경우: 해당 설정 검증
    $keyin = sql_fetch("SELECT k.*, m.mpc_pg_name as master_pg_name,
                               COALESCE(k.mkc_pg_name, m.mpc_pg_name) as pg_name
        FROM g5_member_keyin_config k
        LEFT JOIN g5_manual_payment_config m ON k.mpc_id = m.mpc_id
        WHERE k.mkc_id = '{$mkc_id}'
          AND k.mb_id = '" . sql_real_escape_string($mb_id) . "'
          AND k.mkc_use = 'Y'
          AND k.mkc_status = 'active'");

    if (!$keyin['mkc_id']) {
        json_response(false, '유효하지 않은 Keyin 설정입니다.', ['mkc_id' => $mkc_id], 404);
    }
} else {
    // mkc_id가 없는 경우: 가맹점의 첫 번째 활성 PG 자동 선택
    $keyin = sql_fetch("SELECT k.*, m.mpc_pg_name as master_pg_name,
                               COALESCE(k.mkc_pg_name, m.mpc_pg_name) as pg_name
        FROM g5_member_keyin_config k
        LEFT JOIN g5_manual_payment_config m ON k.mpc_id = m.mpc_id
        WHERE k.mb_id = '" . sql_real_escape_string($mb_id) . "'
          AND k.mkc_use = 'Y'
          AND k.mkc_status = 'active'
        ORDER BY k.mkc_id ASC
        LIMIT 1");

    if (!$keyin['mkc_id']) {
        json_response(false, '가맹점에 등록된 수기결제 PG모듈이 없습니다. 관리자에게 문의하세요.', [
            'mb_id' => $mb_id,
            'help' => '가맹점의 Keyin 설정이 필요합니다. (mkc_use=Y, mkc_status=active)'
        ], 400);
    }

    // 자동 선택된 mkc_id 설정
    $mkc_id = $keyin['mkc_id'];
}

// ========================================
// 기본값 설정
// ========================================

// 판매자명 기본값: 가맹점 닉네임
if (empty($seller_name)) {
    $seller_name = $merchant['mb_nick'];
}

// 판매자 연락처 기본값: 가맹점 연락처
if (empty($seller_phone)) {
    $seller_phone = $merchant['mb_hp'];
}

// 만료일 기본값: 내일
if (empty($expire_date) || strlen($expire_date) != 8) {
    $tomorrow = new DateTime();
    $tomorrow->modify('+1 day');
    $expire_date = $tomorrow->format('Ymd');
}

// 만료시각 형식 검증
if (!preg_match('/^\d{2}:\d{2}$/', $expire_time)) {
    $expire_time = '23:00';
}

// ========================================
// 유효기간 변환
// ========================================
$expire_datetime = substr($expire_date, 0, 4) . '-' . substr($expire_date, 4, 2) . '-' . substr($expire_date, 6, 2) . ' ' . $expire_time . ':59';

// 유효기간 검증 (과거 날짜 불가)
if (strtotime($expire_datetime) < time()) {
    json_response(false, '만료일시는 현재 시각 이후여야 합니다.', ['expire_datetime' => $expire_datetime], 400);
}

// ========================================
// URL 코드 생성
// ========================================
$url_code = generate_url_code();
if (!$url_code) {
    json_response(false, 'URL 코드 생성에 실패했습니다. 다시 시도해주세요.', null, 500);
}

// ========================================
// 데이터베이스 INSERT
// ========================================
$sql = "INSERT INTO g5_url_payment (
            up_code, mb_id, mkc_id, up_amount, up_goods_name, up_goods_desc,
            up_buyer_name, up_buyer_phone, up_seller_name, up_seller_phone,
            up_expire_datetime, up_memo,
            up_mb_1, up_mb_2, up_mb_3, up_mb_4, up_mb_5, up_mb_6, up_mb_6_name,
            up_operator_id, up_created_at
        ) VALUES (
            '" . sql_real_escape_string($url_code) . "',
            '" . sql_real_escape_string($mb_id) . "',
            '{$mkc_id}',
            '{$amount}',
            '" . sql_real_escape_string($goods_name) . "',
            '" . sql_real_escape_string($goods_desc) . "',
            '" . sql_real_escape_string($buyer_name) . "',
            '" . sql_real_escape_string($buyer_phone) . "',
            '" . sql_real_escape_string($seller_name) . "',
            '" . sql_real_escape_string($seller_phone) . "',
            '" . sql_real_escape_string($expire_datetime) . "',
            '" . sql_real_escape_string($memo) . "',
            '" . sql_real_escape_string($merchant['mb_1']) . "',
            '" . sql_real_escape_string($merchant['mb_2']) . "',
            '" . sql_real_escape_string($merchant['mb_3']) . "',
            '" . sql_real_escape_string($merchant['mb_4']) . "',
            '" . sql_real_escape_string($merchant['mb_5']) . "',
            '" . sql_real_escape_string($merchant['mb_6']) . "',
            '" . sql_real_escape_string($merchant['mb_nick']) . "',
            'API',
            NOW()
        )";

$result = sql_query($sql);
if (!$result) {
    json_response(false, '데이터베이스 저장 중 오류가 발생했습니다.', null, 500);
}

$up_id = sql_insert_id();

// ========================================
// SMS 발송 (선택적)
// ========================================
$sms_sent = false;
$sms_message = '';

if ($send_sms && $buyer_phone) {
    // 결제 URL 생성
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'gnushop.xyz';
    $payment_url = "{$protocol}://{$host}/pay/{$url_code}";

    $expire_text = date('Y-m-d H:i', strtotime($expire_datetime));

    // 80자 이내 SMS
    $message = "{$seller_name} " . number_format($amount) . "원\n";
    $message .= "{$payment_url}";

    $sms_result = send_url_payment_sms($buyer_phone, $message);

    if ($sms_result['success']) {
        sql_query("UPDATE g5_url_payment SET up_sms_sent = 'Y', up_sms_sent_datetime = NOW(), up_sms_count = 1 WHERE up_id = '{$up_id}'");
        $sms_sent = true;
        $sms_message = 'SMS 발송 완료';
    } else {
        $sms_message = 'SMS 발송 실패: ' . $sms_result['message'];
    }
}

// ========================================
// 응답 생성
// ========================================
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'gnushop.xyz';
$payment_url = "{$protocol}://{$host}/pay/{$url_code}";

$response_data = [
    'up_id' => $up_id,
    'up_code' => $url_code,
    'payment_url' => $payment_url,
    'amount' => $amount,
    'goods_name' => $goods_name,
    'buyer_name' => $buyer_name,
    'buyer_phone' => $buyer_phone,
    'seller_name' => $seller_name,
    'expire_datetime' => $expire_datetime,
    'sms_sent' => $sms_sent
];

if ($sms_message) {
    $response_data['sms_message'] = $sms_message;
}

json_response(true, 'URL결제가 생성되었습니다.', $response_data);
