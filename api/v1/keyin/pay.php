<?php
/**
 * Keyin 공개 API - 결제 엔드포인트
 *
 * POST /api/v1/keyin/pay.php
 *
 * 요청 헤더:
 *   X-API-Key: {발급받은 API 키}
 *   X-TID: {터미널 ID}
 *   Content-Type: application/json
 *
 * 요청 바디:
 * {
 *   "amount": 50000,
 *   "goods_name": "상품명",
 *   "buyer_name": "홍길동",
 *   "buyer_phone": "01012345678",
 *   "card_no": "1234567890123456",
 *   "expire_yymm": "2612",
 *   "installment": "00",
 *   "buyer_email": "",
 *   "cert_pw": "",
 *   "cert_no": ""
 * }
 */

include_once('./_common.php');
include_once('./_auth.php');
include_once('./_pg.php');

// 기존 웹훅 시스템 사용 (lib/webhook.lib.php)
$webhook_lib = dirname(__FILE__) . '/../../../lib/webhook.lib.php';
if (file_exists($webhook_lib)) {
    @include_once($webhook_lib);
}

// POST만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    keyin_error_response('METHOD_NOT_ALLOWED', 'POST 요청만 허용됩니다.', 405);
}

// ============================================
// 1. 인증
// ============================================
$auth = keyin_authenticate();
$kak = $auth['kak'];
$keyin = $auth['keyin'];
$pg_code = $auth['pg_code'];

write_keyin_api_log('pay', 'REQUEST_START', [
    'kak_id' => $kak['kak_id'],
    'mkc_id' => $kak['mkc_id'],
    'mb_id' => $kak['mb_id'],
    'pg_code' => $pg_code,
    'remote_ip' => $_SERVER['REMOTE_ADDR'] ?? ''
]);

// ============================================
// 2. Rate Limit 체크
// ============================================
if (!keyin_check_rate_limit($kak['kak_id'])) {
    keyin_error_response('RATE_LIMIT_EXCEEDED', '요청 빈도 제한을 초과했습니다. (분당 10건)', 429);
}

// ============================================
// 3. 입력값 파싱
// ============================================
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    keyin_error_response('INVALID_JSON', '요청 데이터가 올바른 JSON 형식이 아닙니다.');
}

// 필수 파라미터 체크
$required_fields = ['amount', 'goods_name', 'buyer_name', 'card_no', 'expire_yymm', 'installment'];
foreach ($required_fields as $field) {
    if (empty($input[$field]) && $input[$field] !== '0' && $input[$field] !== 0) {
        keyin_error_response('MISSING_PARAM', "필수 항목이 누락되었습니다: {$field}");
    }
}

$amount = intval(preg_replace('/[^0-9]/', '', $input['amount']));
$goods_name = trim($input['goods_name']);
$buyer_name = trim($input['buyer_name']);
$buyer_phone = isset($input['buyer_phone']) ? preg_replace('/[^0-9]/', '', $input['buyer_phone']) : '';
$buyer_email = isset($input['buyer_email']) ? trim($input['buyer_email']) : '';
$card_no = preg_replace('/[^0-9]/', '', $input['card_no']);
$expire_yymm = preg_replace('/[^0-9]/', '', $input['expire_yymm']);
$installment = str_pad(intval($input['installment']), 2, '0', STR_PAD_LEFT);
$cert_pw = isset($input['cert_pw']) ? trim($input['cert_pw']) : '';
$cert_no = isset($input['cert_no']) ? preg_replace('/[^0-9]/', '', $input['cert_no']) : '';

// 금액 검증
if ($amount < 100) {
    keyin_error_response('INVALID_AMOUNT', '결제금액은 100원 이상이어야 합니다.');
}

// 카드번호 검증
if (strlen($card_no) < 15 || strlen($card_no) > 16) {
    keyin_error_response('INVALID_CARD', '카드번호가 올바르지 않습니다.');
}

// 유효기간 검증
if (strlen($expire_yymm) !== 4) {
    keyin_error_response('INVALID_EXPIRE', '유효기간(YYMM)이 올바르지 않습니다.');
}

// ============================================
// 4. PG 인증정보 추출
// ============================================
$cred = keyin_resolve_credentials($keyin, $pg_code);
$api_key = $cred['api_key'];
$mid = $cred['mid'];
$mkey = $cred['mkey'];
$tid = $cred['tid'];
$mbr_no = $cred['mbr_no'];
$pg_name = $cred['pg_name'];
$auth_type = $cred['auth_type'];
$merchant_oid = $cred['merchant_oid'];

// 구인증 필드 검증
if ($auth_type === 'auth') {
    if (empty($cert_pw) || strlen($cert_pw) < 2) {
        keyin_error_response('VALIDATION_ERROR', '카드 비밀번호 앞 2자리를 입력하세요.');
    }
    if (empty($cert_no) || (strlen($cert_no) != 6 && strlen($cert_no) != 10)) {
        keyin_error_response('VALIDATION_ERROR', '주민번호 앞 6자리 또는 사업자번호 10자리를 입력하세요.');
    }
}

// ============================================
// 5. 한도/시간/주말/중복 체크 (5중 검증)
// ============================================
$mkc_id = $keyin['mkc_id'];

// 1회 한도
if ($keyin['mkc_limit_once'] > 0 && $amount > $keyin['mkc_limit_once']) {
    keyin_error_response('LIMIT_EXCEEDED', '1회 결제한도를 초과했습니다. (한도: ' . number_format($keyin['mkc_limit_once']) . '원)');
}

// 일일 한도
if ($keyin['mkc_limit_daily'] > 0) {
    $today_row = sql_fetch("SELECT COALESCE(SUM(pk_amount), 0) as total FROM g5_payment_keyin WHERE mkc_id = '{$mkc_id}' AND pk_status = 'approved' AND DATE(pk_created_at) = CURDATE()");
    if (($today_row['total'] + $amount) > $keyin['mkc_limit_daily']) {
        keyin_error_response('LIMIT_EXCEEDED', '일일 결제한도를 초과합니다. (한도: ' . number_format($keyin['mkc_limit_daily']) . '원)');
    }
}

// 월 한도
if ($keyin['mkc_limit_monthly'] > 0) {
    $month_row = sql_fetch("SELECT COALESCE(SUM(pk_amount), 0) as total FROM g5_payment_keyin WHERE mkc_id = '{$mkc_id}' AND pk_status = 'approved' AND DATE_FORMAT(pk_created_at, '%Y%m') = DATE_FORMAT(NOW(), '%Y%m')");
    if (($month_row['total'] + $amount) > $keyin['mkc_limit_monthly']) {
        keyin_error_response('LIMIT_EXCEEDED', '월 결제한도를 초과합니다. (한도: ' . number_format($keyin['mkc_limit_monthly']) . '원)');
    }
}

// 결제 가능 시간
$current_time = date('H:i');
$time_start = $keyin['mkc_time_start'] ?: '00:00';
$time_end = $keyin['mkc_time_end'] ?: '23:59';
if ($current_time < $time_start || $current_time > $time_end) {
    keyin_error_response('TIME_RESTRICTED', '결제 가능 시간이 아닙니다. (' . $time_start . ' ~ ' . $time_end . ')');
}

// 주말 체크
if ($keyin['mkc_weekend_yn'] !== 'Y') {
    $day_of_week = date('N');
    if ($day_of_week >= 6) {
        keyin_error_response('WEEKEND_RESTRICTED', '주말에는 결제가 불가합니다.');
    }
}

// 할부 제한
$max_installment = intval($keyin['mkc_max_installment']);
$req_installment = intval($installment);
if ($max_installment > 0 && $req_installment > $max_installment) {
    keyin_error_response('LIMIT_EXCEEDED', '허용된 최대 할부개월을 초과했습니다. (최대: ' . $max_installment . '개월)');
}

// 중복결제 체크
$card_no_masked = maskCardNumber($card_no);
if ($keyin['mkc_duplicate_yn'] !== 'Y') {
    $dup_limit = intval($keyin['mkc_duplicate_limit']);
    if ($dup_limit > 0) {
        $dup_row = sql_fetch("SELECT COUNT(*) as cnt FROM g5_payment_keyin WHERE mkc_id = '{$mkc_id}' AND pk_card_no_masked = '" . sql_escape_string($card_no_masked) . "' AND pk_amount = '{$amount}' AND pk_status = 'approved' AND DATE(pk_created_at) = CURDATE()");
        if (intval($dup_row['cnt']) >= $dup_limit) {
            keyin_error_response('DUPLICATE_PAYMENT', '동일한 카드와 금액으로 오늘 ' . $dup_limit . '회까지만 결제 가능합니다.');
        }
    } else {
        $dup_row = sql_fetch("SELECT pk_id FROM g5_payment_keyin WHERE mkc_id = '{$mkc_id}' AND pk_card_no_masked = '" . sql_escape_string($card_no_masked) . "' AND pk_amount = '{$amount}' AND pk_status = 'approved' AND pk_created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) LIMIT 1");
        if ($dup_row['pk_id']) {
            keyin_error_response('DUPLICATE_PAYMENT', '동일한 카드와 금액으로 최근 5분 이내 결제된 내역이 있습니다.');
        }
    }
}

// ============================================
// 6. 가맹점 계층 정보 조회
// ============================================
$merchant = sql_fetch("SELECT mb_id, mb_nick, mb_1, mb_2, mb_3, mb_4, mb_5, mb_6 FROM g5_member WHERE mb_id = '{$keyin['mb_id']}'");
if (!$merchant) {
    keyin_error_response('SYSTEM_ERROR', '가맹점 정보를 찾을 수 없습니다.', 500);
}

// ============================================
// 7. 주문번호 생성 + 요청 데이터 구성
// ============================================
$order_no = generateOrderNumber($merchant_oid, $pg_code);

// PG사별 요청 데이터 구성
if ($pg_code === 'rootup') {
    $request_data = [
        'mid' => $mid, 'tid' => $tid, 'amount' => (string)$amount,
        'ord_num' => $order_no, 'item_name' => $goods_name,
        'buyer_name' => $buyer_name, 'buyer_phone' => $buyer_phone,
        'card_num' => $card_no, 'yymm' => $expire_yymm, 'installment' => $installment
    ];
    if ($auth_type === 'auth') {
        $request_data['card_pw'] = $cert_pw;
        $request_data['auth_num'] = $cert_no;
    }
} else if ($pg_code === 'stn') {
    $timestamp = date('ymdHis') . substr(microtime(), 2, 3);
    $signature = hash('sha256', $mbr_no . '|' . $order_no . '|' . $amount . '|' . $api_key . '|' . $timestamp);
    $request_data = [
        'mbrNo' => $mbr_no, 'mbrRefNo' => $order_no, 'paymethod' => 'CARD',
        'cardNo' => $card_no, 'expd' => $expire_yymm, 'amount' => (string)$amount,
        'installment' => str_pad($installment, 2, '0', STR_PAD_LEFT),
        'goodsName' => mb_substr(preg_replace('/[^\p{L}\p{N}\s]/u', '', $goods_name), 0, 30),
        'timestamp' => $timestamp, 'signature' => $signature,
        'keyinAuthType' => ($auth_type === 'auth') ? 'O' : 'K',
        'customerName' => $buyer_name, 'customerTelNo' => $buyer_phone
    ];
    if ($auth_type === 'auth') {
        $auth_type_code = (strlen($cert_no) == 10) ? '1' : '0';
        $request_data['authType'] = $auth_type_code;
        $request_data['regNo'] = $cert_no;
        $request_data['passwd'] = $cert_pw;
    }
} else if ($pg_code === 'winglobal') {
    if (empty($buyer_email) || !filter_var($buyer_email, FILTER_VALIDATE_EMAIL)) {
        keyin_error_response('VALIDATION_ERROR', '윈글로벌 결제는 올바른 구매자 이메일이 필수입니다.');
    }
    $request_data = [
        'pay' => [
            'trxType' => 'ONTR', 'trackId' => $order_no, 'amount' => (int)$amount,
            'payerName' => $buyer_name, 'payerEmail' => $buyer_email, 'payerTel' => $buyer_phone,
            'card' => ['number' => $card_no, 'expiry' => $expire_yymm, 'cvv' => '', 'installment' => (int)$installment],
            'products' => [['prodId' => '', 'name' => $goods_name, 'qty' => 1, 'price' => (int)$amount, 'desc' => $goods_name]],
            'trxId' => '', 'udf1' => '', 'udf2' => '', 'metadata' => []
        ]
    ];
    if ($auth_type === 'auth') {
        $request_data['pay']['metadata'] = ['cardAuth' => 'true', 'authPw' => $cert_pw, 'authDob' => $cert_no];
    }
} else {
    // paysis
    $request_data = [
        'ordNo' => $order_no, 'mkey' => $mkey, 'mid' => $mid,
        'goodsAmt' => (string)$amount, 'cardNo' => $card_no, 'expireYymm' => $expire_yymm,
        'quotaMon' => $installment, 'buyerNm' => $buyer_name, 'goodsNm' => $goods_name,
        'ordHp' => $buyer_phone, 'hashKey' => hash('sha256', $mid . $amount)
    ];
    if ($auth_type === 'auth') {
        $request_data['certPw'] = $cert_pw;
        $request_data['certNo'] = $cert_no;
    }
}

// ============================================
// 8. DB pending 저장
// ============================================
$operator_id = 'api_' . $keyin['mb_id'];
$insert_sql = "INSERT INTO g5_payment_keyin SET
    pk_order_no = '" . sql_escape_string($order_no) . "',
    pk_merchant_oid = '" . sql_escape_string($merchant_oid) . "',
    mb_id = '" . sql_escape_string($keyin['mb_id']) . "',
    mkc_id = '{$mkc_id}',
    pk_pg_code = '" . sql_escape_string($pg_code) . "',
    pk_pg_name = '" . sql_escape_string($pg_name) . "',
    pk_mid = '" . sql_escape_string($mid) . "',
    pk_auth_type = '" . sql_escape_string($auth_type) . "',
    pk_amount = '{$amount}',
    pk_installment = '" . sql_escape_string($installment) . "',
    pk_goods_name = '" . sql_escape_string($goods_name) . "',
    pk_buyer_name = '" . sql_escape_string($buyer_name) . "',
    pk_buyer_phone = '" . sql_escape_string($buyer_phone) . "',
    pk_buyer_email = '" . sql_escape_string($buyer_email) . "',
    pk_card_no_masked = '" . sql_escape_string($card_no_masked) . "',
    pk_status = 'pending',
    pk_mb_1 = '" . sql_escape_string($merchant['mb_1']) . "',
    pk_mb_2 = '" . sql_escape_string($merchant['mb_2']) . "',
    pk_mb_3 = '" . sql_escape_string($merchant['mb_3']) . "',
    pk_mb_4 = '" . sql_escape_string($merchant['mb_4']) . "',
    pk_mb_5 = '" . sql_escape_string($merchant['mb_5']) . "',
    pk_mb_6 = '" . sql_escape_string($merchant['mb_6']) . "',
    pk_mb_6_name = '" . sql_escape_string($merchant['mb_nick']) . "',
    pk_request_data = '" . sql_escape_string(json_encode(keyin_mask_request_data($request_data, $pg_code), JSON_UNESCAPED_UNICODE)) . "',
    pk_operator_id = '" . sql_escape_string($operator_id) . "',
    pk_created_at = NOW()";

$insert_result = sql_query($insert_sql, false);
if (!$insert_result) {
    keyin_error_response('SYSTEM_ERROR', 'DB 저장 중 오류가 발생했습니다.', 500);
}
$pk_id = sql_insert_id();

// ============================================
// 9. PG API 호출
// ============================================
$response = null;
switch ($pg_code) {
    case 'paysis':
        $response = callPaysisPaymentAPI_v2($api_key, $request_data);
        break;
    case 'rootup':
        $response = callRoutupPaymentAPI_v2($api_key, $request_data);
        break;
    case 'stn':
        $response = callStnPaymentAPI_v2($request_data);
        break;
    case 'winglobal':
        $response = callWinglobalPaymentAPI_v2($api_key, $request_data);
        break;
    default:
        sql_query("UPDATE g5_payment_keyin SET pk_status = 'failed', pk_res_code = 'UNSUPPORTED', pk_res_msg = '지원하지 않는 PG사' WHERE pk_id = '{$pk_id}'");
        keyin_error_response('SYSTEM_ERROR', '지원하지 않는 PG사입니다: ' . $pg_code, 500);
}

// null 응답 처리
if ($response === null || !is_array($response)) {
    sql_query("UPDATE g5_payment_keyin SET pk_status = 'failed', pk_res_code = 'NULL_RESPONSE', pk_res_msg = 'PG API 응답 없음', pk_updated_at = NOW() WHERE pk_id = '{$pk_id}'");
    keyin_error_response('PG_ERROR', 'PG API 응답을 받지 못했습니다.');
}

// ============================================
// 10. 결과 처리
// ============================================
// PG 원본 응답에서 불필요한 데이터 제거 후 저장
$response_for_db = $response;
unset($response_for_db['_original'], $response_for_db['_stn_data'], $response_for_db['_winglobal_data']);

$update_sql = "UPDATE g5_payment_keyin SET
    pk_response_data = '" . sql_escape_string(json_encode($response_for_db, JSON_UNESCAPED_UNICODE)) . "',
    pk_res_code = '" . sql_escape_string($response['resCode'] ?? '') . "',
    pk_res_msg = '" . sql_escape_string($response['resMsg'] ?? '') . "',
    pk_updated_at = NOW()";

if (isset($response['resCode']) && $response['resCode'] === '0000') {
    // 성공
    $update_sql .= ",
        pk_status = 'approved',
        pk_app_no = '" . sql_escape_string($response['appNo'] ?? '') . "',
        pk_app_date = '" . sql_escape_string($response['appDate'] ?? '') . "',
        pk_tid = '" . sql_escape_string($response['tid'] ?? '') . "',
        pk_card_issuer = '" . sql_escape_string($response['vanIssCpCd'] ?? '') . "',
        pk_card_acquirer = '" . sql_escape_string($response['vanCpCd'] ?? '') . "'";
    $update_sql .= " WHERE pk_id = '{$pk_id}'";
    sql_query($update_sql);

    $receipt_url = 'https://wspay.net/receipt_keyin.php?pk_id=' . $pk_id;

    // 웹훅 전송 - 기존 g5_member_webhook 시스템 사용
    if (function_exists('webhook_send_notification')) {
        $wh_pg_name = $pg_code . '_keyin';  // paysis_keyin, stn_k 등
        if ($pg_code === 'stn') $wh_pg_name = 'stn_k';

        $wh_pg_data = $response;
        $wh_pg_data['amt'] = $amount;
        $wh_pg_data['cardNo'] = $card_no_masked;
        $wh_pg_data['ordNo'] = $order_no;
        $wh_pg_data['quota'] = $installment;

        $wh_device_data = [
            'mb_6' => $merchant['mb_6'] ?? '',
            'mb_6_name' => $merchant['mb_nick'] ?? '',
            'dv_tid' => '',
            'dv_type' => '2'  // keyin
        ];

        $wh_payment_data = [
            'pay_id' => null,
            'pay_type' => 'Y'  // 승인
        ];

        webhook_send_notification($keyin['mb_id'], $wh_pg_name, $wh_pg_data, $wh_device_data, $wh_payment_data);
    }

    write_keyin_api_log('pay', 'SUCCESS', [
        'pk_id' => $pk_id, 'order_no' => $order_no, 'amount' => $amount,
        'pg_code' => $pg_code, 'mb_id' => $keyin['mb_id']
    ]);

    keyin_json_response([
        'success' => true,
        'message' => '결제가 완료되었습니다.',
        'data' => [
            'order_no' => $order_no,
            'approval_number' => $response['appNo'] ?? '',
            'approved_at' => $response['appDate'] ?? '',
            'amount' => $amount,
            'goods_name' => $goods_name,
            'buyer_name' => $buyer_name,
            'card_no_masked' => $card_no_masked,
            'installment' => $installment,
            'card_issuer' => $response['vanIssCpCd'] ?? '',
            'pg' => $pg_code,
            'pg_name' => $pg_name,
            'receipt_url' => $receipt_url
        ]
    ]);
} else {
    // 실패
    $update_sql .= ", pk_status = 'failed'";
    $update_sql .= " WHERE pk_id = '{$pk_id}'";
    sql_query($update_sql);

    write_keyin_api_log('pay', 'FAILED', [
        'pk_id' => $pk_id, 'order_no' => $order_no, 'amount' => $amount,
        'pg_code' => $pg_code, 'res_code' => $response['resCode'] ?? '', 'res_msg' => $response['resMsg'] ?? ''
    ]);

    keyin_json_response([
        'success' => false,
        'error_code' => 'PG_DECLINED',
        'message' => '결제에 실패했습니다: ' . ($response['resMsg'] ?? '알 수 없는 오류'),
        'pg_error_code' => $response['resCode'] ?? ''
    ]);
}
