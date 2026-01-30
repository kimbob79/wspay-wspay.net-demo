<?php
/**
 * URL 결제 API - 결제 처리
 *
 * 지원 PG:
 * - paysis (페이시스): API KEY, MID, MKEY 사용
 * - rootup (루트업): MID, TID, 결제KEY 사용
 * - stn (섹타나인): MBRNO, APIKEY 사용
 * - winglobal (윈글로벌): TID, API KEY (Pay Key) 사용
 */
error_reporting(E_ALL);
ini_set("display_errors", 0);

// Gnuboard 프레임워크 로드
if(!defined('_GNUBOARD_')) define('_GNUBOARD_', true);

// gnu_module 또는 _engin 디렉토리 탐색
$_g5_path = file_exists(__DIR__.'/gnu_module/common.php')
    ? __DIR__.'/gnu_module/common.php'
    : __DIR__.'/_engin/common.php';
include_once($_g5_path);
unset($_g5_path);

header('Content-Type: application/json; charset=utf-8');

// POST 데이터
$up_id = intval($_POST['up_id']);
$up_code = trim($_POST['up_code']);
$card_no = preg_replace('/[^0-9]/', '', $_POST['card_no']);
$expire_mm = $_POST['expire_mm'];
$expire_yy = $_POST['expire_yy'];
$installment = isset($_POST['installment']) ? str_pad(intval($_POST['installment']), 2, '0', STR_PAD_LEFT) : '00';
$cert_pw = isset($_POST['cert_pw']) ? trim($_POST['cert_pw']) : '';
$cert_no = isset($_POST['cert_no']) ? preg_replace('/[^0-9]/', '', $_POST['cert_no']) : '';
$buyer_name = trim($_POST['buyer_name']);
$buyer_phone = isset($_POST['buyer_phone']) ? preg_replace('/[^0-9]/', '', $_POST['buyer_phone']) : '';
$buyer_email = isset($_POST['buyer_email']) ? trim($_POST['buyer_email']) : '';

// 기본 검증
if(!$up_id || !$up_code || !$card_no || !$expire_mm || !$expire_yy) {
    echo json_encode(['success' => false, 'message' => '필수 정보가 누락되었습니다.']);
    exit;
}

// URL 결제 정보 조회 (PG 설정 포함)
$url_pay = sql_fetch("SELECT u.*, m.mb_nick as merchant_name,
                             mkc.mkc_pg_code, mkc.mkc_pg_name, mkc.mkc_type, mkc.mpc_id, mkc.mkc_oid,
                             mkc.mkc_api_key, mkc.mkc_mid, mkc.mkc_mkey,
                             mpc.mpc_pg_code, mpc.mpc_pg_name, mpc.mpc_type,
                             mpc.mpc_api_key, mpc.mpc_mid, mpc.mpc_mkey,
                             mpc.mpc_rootup_mid, mpc.mpc_rootup_tid, mpc.mpc_rootup_key,
                             mpc.mpc_stn_mbrno, mpc.mpc_stn_apikey,
                             mpc.mpc_winglobal_tid, mpc.mpc_winglobal_apikey
                      FROM g5_url_payment u
                      LEFT JOIN g5_member m ON u.mb_id = m.mb_id
                      LEFT JOIN g5_member_keyin_config mkc ON u.mkc_id = mkc.mkc_id
                      LEFT JOIN g5_manual_payment_config mpc ON mkc.mpc_id = mpc.mpc_id
                      WHERE u.up_id = '{$up_id}' AND u.up_code = '".sql_real_escape_string($up_code)."'");

if(!$url_pay) {
    echo json_encode(['success' => false, 'message' => '유효하지 않은 결제 요청입니다.']);
    exit;
}

// 상태 검증
if($url_pay['up_status'] != 'active') {
    $status_msg = '';
    switch($url_pay['up_status']) {
        case 'used': $status_msg = '이미 결제가 완료된 링크입니다.'; break;
        case 'expired': $status_msg = '만료된 결제 링크입니다.'; break;
        case 'cancelled': $status_msg = '취소된 결제 링크입니다.'; break;
        default: $status_msg = '결제할 수 없는 상태입니다.';
    }
    echo json_encode(['success' => false, 'message' => $status_msg]);
    exit;
}

// 유효기간 검증
if(strtotime($url_pay['up_expire_datetime']) < time()) {
    sql_query("UPDATE g5_url_payment SET up_status = 'expired' WHERE up_id = '{$up_id}'");
    echo json_encode(['success' => false, 'message' => '유효기간이 만료되었습니다.']);
    exit;
}

// PG 정보 결정 (대표가맹점 설정 또는 개별 설정)
$pg_code = $url_pay['mpc_id'] ? $url_pay['mpc_pg_code'] : $url_pay['mkc_pg_code'];
$pg_name = $url_pay['mpc_id'] ? $url_pay['mpc_pg_name'] : $url_pay['mkc_pg_name'];
$auth_type = $url_pay['mpc_id'] ? $url_pay['mpc_type'] : $url_pay['mkc_type'];
$merchant_oid = $url_pay['mkc_oid'] ?: '';

// PG사별 API 설정값 결정
if($pg_code === 'rootup') {
    // 루트업: MID, TID, 결제KEY
    $api_key = $url_pay['mpc_id'] ? $url_pay['mpc_rootup_key'] : $url_pay['mkc_api_key'];
    $mid = $url_pay['mpc_id'] ? $url_pay['mpc_rootup_mid'] : $url_pay['mkc_mid'];
    $tid = $url_pay['mpc_id'] ? $url_pay['mpc_rootup_tid'] : $url_pay['mkc_mkey'];
    $mkey = '';
    $mbr_no = '';
} else if($pg_code === 'stn') {
    // 섹타나인: MBRNO, APIKEY
    $mbr_no = $url_pay['mpc_id'] ? $url_pay['mpc_stn_mbrno'] : $url_pay['mkc_mid'];
    $api_key = $url_pay['mpc_id'] ? $url_pay['mpc_stn_apikey'] : $url_pay['mkc_api_key'];
    $mid = $mbr_no;
    $mkey = '';
    $tid = '';
} else if($pg_code === 'winglobal') {
    // 윈글로벌: TID, API KEY (Pay Key)
    $tid = $url_pay['mpc_id'] ? $url_pay['mpc_winglobal_tid'] : $url_pay['mkc_mid'];
    $api_key = $url_pay['mpc_id'] ? $url_pay['mpc_winglobal_apikey'] : $url_pay['mkc_api_key'];
    $mid = $tid;
    $mkey = '';
    $mbr_no = '';
} else {
    // 페이시스 등 기타: API KEY, MID, MKEY
    $api_key = $url_pay['mpc_id'] ? $url_pay['mpc_api_key'] : $url_pay['mkc_api_key'];
    $mid = $url_pay['mpc_id'] ? $url_pay['mpc_mid'] : $url_pay['mkc_mid'];
    $mkey = $url_pay['mpc_id'] ? $url_pay['mpc_mkey'] : $url_pay['mkc_mkey'];
    $tid = '';
    $mbr_no = '';
}

// 구인증인 경우 인증정보 필수 체크
if($auth_type === 'auth') {
    if(empty($cert_pw) || strlen($cert_pw) < 2) {
        echo json_encode(['success' => false, 'message' => '카드 비밀번호 앞 2자리를 입력하세요.']);
        exit;
    }
    if(empty($cert_no) || (strlen($cert_no) != 6 && strlen($cert_no) != 10)) {
        echo json_encode(['success' => false, 'message' => '주민번호 앞 6자리 또는 사업자번호 10자리를 입력하세요.']);
        exit;
    }
}

// 윈글로벌: 이메일 필수 체크
if($pg_code === 'winglobal') {
    if(empty($buyer_email)) {
        echo json_encode(['success' => false, 'message' => '윈글로벌 결제는 구매자 이메일이 필수입니다.']);
        exit;
    }
    if(!filter_var($buyer_email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => '올바른 이메일 형식을 입력하세요.']);
        exit;
    }
}

// 결제 금액
$amount = intval($url_pay['up_amount']);
$goods_name = $url_pay['up_goods_name'];

// 유효기간 (YYMM)
$expire_yymm = $expire_yy . $expire_mm;

// 주문번호 생성 (PG사별 규격)
$order_no = generateUrlPaymentOrderNumber($merchant_oid, $pg_code);

// 카드번호 마스킹
$card_no_masked = maskUrlPaymentCardNumber($card_no);

// PG사별 요청 데이터 구성
if($pg_code === 'rootup') {
    // 루트업 API 요청 데이터
    $request_data = [
        'mid' => $mid,
        'tid' => $tid,
        'amount' => (string)$amount,
        'ord_num' => $order_no,
        'item_name' => $goods_name,
        'buyer_name' => $buyer_name,
        'buyer_phone' => $buyer_phone,
        'card_num' => $card_no,
        'yymm' => $expire_yymm,
        'installment' => $installment
    ];
    // 구인증인 경우 인증정보 추가
    if($auth_type === 'auth') {
        $request_data['card_pw'] = $cert_pw;
        $request_data['auth_num'] = $cert_no;
    }
} else if($pg_code === 'stn') {
    // 섹타나인 API 요청 데이터
    $timestamp = date('ymdHis') . substr(microtime(), 2, 3);
    $signature = hash('sha256', $mbr_no . '|' . $order_no . '|' . $amount . '|' . $api_key . '|' . $timestamp);

    $request_data = [
        'mbrNo' => $mbr_no,
        'mbrRefNo' => $order_no,
        'paymethod' => 'CARD',
        'cardNo' => $card_no,
        'expd' => $expire_yymm,
        'amount' => (string)$amount,
        'installment' => $installment,
        'goodsName' => mb_substr(preg_replace('/[^\p{L}\p{N}\s]/u', '', $goods_name), 0, 30),
        'timestamp' => $timestamp,
        'signature' => $signature,
        'keyinAuthType' => ($auth_type === 'auth') ? 'O' : 'K',
        'customerName' => $buyer_name,
        'customerTelNo' => $buyer_phone
    ];
    // 구인증인 경우 인증정보 추가
    if($auth_type === 'auth') {
        $auth_type_code = (strlen($cert_no) == 10) ? '1' : '0';
        $request_data['authType'] = $auth_type_code;
        $request_data['regNo'] = $cert_no;
        $request_data['passwd'] = $cert_pw;
    }
} else if($pg_code === 'winglobal') {
    // 윈글로벌 API 요청 데이터
    $request_data = [
        'pay' => [
            'trxType' => 'ONTR',
            'trackId' => $order_no,
            'amount' => (int)$amount,
            'payerName' => $buyer_name,
            'payerEmail' => $buyer_email,
            'payerTel' => $buyer_phone,
            'card' => [
                'number' => $card_no,
                'expiry' => $expire_yymm,
                'cvv' => '',
                'installment' => (int)$installment
            ],
            'products' => [
                [
                    'prodId' => '',
                    'name' => $goods_name,
                    'qty' => 1,
                    'price' => (int)$amount,
                    'desc' => $goods_name
                ]
            ],
            'trxId' => '',
            'udf1' => '',
            'udf2' => '',
            'metadata' => []
        ]
    ];
    // 구인증인 경우 metadata에 인증정보 추가
    if($auth_type === 'auth') {
        $request_data['pay']['metadata'] = [
            'cardAuth' => 'true',
            'authPw' => $cert_pw,
            'authDob' => $cert_no
        ];
    }
} else {
    // 페이시스 API 요청 데이터
    $request_data = [
        'ordNo' => $order_no,
        'mkey' => $mkey,
        'mid' => $mid,
        'goodsAmt' => (string)$amount,
        'cardNo' => $card_no,
        'expireYymm' => $expire_yymm,
        'quotaMon' => $installment,
        'buyerNm' => $buyer_name,
        'goodsNm' => $goods_name,
        'ordHp' => $buyer_phone,
        'hashKey' => hash('sha256', $mid . $amount)
    ];
    // 구인증인 경우 인증정보 추가
    if($auth_type === 'auth') {
        $request_data['certPw'] = $cert_pw;
        $request_data['certNo'] = $cert_no;
    }
}

// PG사별 API 호출
$response = null;
switch($pg_code) {
    case 'paysis':
        $response = callUrlPaysisPaymentAPI($api_key, $request_data);
        break;
    case 'rootup':
        $response = callUrlRoutupPaymentAPI($api_key, $request_data);
        break;
    case 'stn':
        $response = callUrlStnPaymentAPI($request_data);
        break;
    case 'winglobal':
        $response = callUrlWinglobalPaymentAPI($api_key, $request_data);
        break;
    default:
        // 지원하지 않는 PG
        echo json_encode(['success' => false, 'message' => '지원하지 않는 PG사입니다: ' . $pg_code]);
        exit;
}

// response가 null이면 에러 처리
if($response === null || !is_array($response)) {
    echo json_encode(['success' => false, 'message' => 'PG API 응답을 받지 못했습니다.']);
    exit;
}

// 결과 처리
$res_code = $response['resCode'] ?? '';
$res_msg = $response['resMsg'] ?? '';
$app_no = $response['appNo'] ?? '';
$app_date = $response['appDate'] ?? '';
$tid_response = $response['tid'] ?? '';
$card_issuer = $response['vanIssCpCd'] ?? '';
$card_acquirer = $response['vanCpCd'] ?? '';

$is_success = ($res_code === '0000');
$pk_status = $is_success ? 'approved' : 'failed';

// g5_payment_keyin에 결제 내역 저장
$sql_keyin = "INSERT INTO g5_payment_keyin (
    pk_order_no, pk_merchant_oid, mb_id, mkc_id, pk_pg_code, pk_pg_name,
    pk_mid, pk_auth_type, pk_amount, pk_installment, pk_goods_name,
    pk_buyer_name, pk_buyer_phone, pk_buyer_email, pk_card_issuer, pk_card_acquirer, pk_card_no_masked,
    pk_status, pk_res_code, pk_res_msg, pk_app_no, pk_app_date, pk_tid,
    pk_mb_1, pk_mb_2, pk_mb_3, pk_mb_4, pk_mb_5, pk_mb_6, pk_mb_6_name,
    pk_request_data, pk_response_data, pk_memo, pk_created_at
) VALUES (
    '".sql_real_escape_string($order_no)."',
    '".sql_real_escape_string($merchant_oid)."',
    '".sql_real_escape_string($url_pay['mb_id'])."',
    '".sql_real_escape_string($url_pay['mkc_id'])."',
    '".sql_real_escape_string($pg_code)."',
    '".sql_real_escape_string($pg_name)."',
    '".sql_real_escape_string($mid)."',
    '".sql_real_escape_string($auth_type)."',
    '{$amount}',
    '".sql_real_escape_string($installment)."',
    '".sql_real_escape_string($goods_name)."',
    '".sql_real_escape_string($buyer_name)."',
    '".sql_real_escape_string($buyer_phone)."',
    '".sql_real_escape_string($buyer_email)."',
    '".sql_real_escape_string($card_issuer)."',
    '".sql_real_escape_string($card_acquirer)."',
    '".sql_real_escape_string($card_no_masked)."',
    '{$pk_status}',
    '".sql_real_escape_string($res_code)."',
    '".sql_real_escape_string($res_msg)."',
    '".sql_real_escape_string($app_no)."',
    '".sql_real_escape_string($app_date)."',
    '".sql_real_escape_string($tid_response)."',
    '".sql_real_escape_string($url_pay['up_mb_1'])."',
    '".sql_real_escape_string($url_pay['up_mb_2'])."',
    '".sql_real_escape_string($url_pay['up_mb_3'])."',
    '".sql_real_escape_string($url_pay['up_mb_4'])."',
    '".sql_real_escape_string($url_pay['up_mb_5'])."',
    '".sql_real_escape_string($url_pay['up_mb_6'])."',
    '".sql_real_escape_string($url_pay['up_mb_6_name'])."',
    '".sql_real_escape_string(json_encode($request_data, JSON_UNESCAPED_UNICODE))."',
    '".sql_real_escape_string(json_encode($response, JSON_UNESCAPED_UNICODE))."',
    'URL결제 (up_id: {$up_id})',
    NOW()
)";

sql_query($sql_keyin);
$pk_id = sql_insert_id();

// 결제 성공 시 URL 결제 상태 업데이트
if($is_success) {
    sql_query("UPDATE g5_url_payment SET
                up_status = 'used',
                up_use_count = up_use_count + 1,
                pk_id = '{$pk_id}',
                up_paid_datetime = NOW()
               WHERE up_id = '{$up_id}'");

    echo json_encode([
        'success' => true,
        'message' => '결제가 완료되었습니다.',
        'data' => [
            'order_no' => $order_no,
            'app_no' => $app_no,
            'amount' => $amount,
            'pk_id' => $pk_id
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => $res_msg ?: '결제에 실패했습니다.',
        'data' => [
            'res_code' => $res_code,
            'res_msg' => $res_msg
        ]
    ]);
}
exit;

// ============================================================
// PG사별 API 호출 함수들
// ============================================================

/**
 * 주문번호 생성 (PG사별 규격)
 */
function generateUrlPaymentOrderNumber($merchant_oid, $pg_code = 'paysis') {
    if(!$merchant_oid) {
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $alphanumeric = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $merchant_oid = $letters[rand(0, 25)] . $alphanumeric[rand(0, 35)] . $alphanumeric[rand(0, 35)] . $alphanumeric[rand(0, 35)];
    }
    $oid = $merchant_oid;

    if($pg_code === 'paysis') {
        // 페이시스: 정확히 30자 (하이픈 없음)
        $date = date('Ymd');
        $time = date('His');
        $rand = strtoupper(substr(md5(microtime(true) . mt_rand()), 0, 12));
        return "{$oid}{$date}{$time}{$rand}";
    } else if($pg_code === 'stn') {
        // 섹타나인: 정확히 20자 (하이픈 없음)
        $date = date('Ymd');
        $time = date('His');
        $rand = strtoupper(substr(md5(microtime(true) . mt_rand()), 0, 2));
        return "{$oid}{$date}{$time}{$rand}";
    } else {
        // 기타 PG: 기존 19자 형식
        $yymm = date('ym');
        $hhmm = date('Hi');
        $ss = date('s');
        $rand = strtoupper(substr(md5(microtime(true) . mt_rand()), 0, 2));
        return "{$oid}-{$yymm}-{$hhmm}-{$ss}{$rand}";
    }
}

/**
 * 카드번호 마스킹
 */
function maskUrlPaymentCardNumber($card_no) {
    $len = strlen($card_no);
    if($len < 10) return str_repeat('*', $len);
    return substr($card_no, 0, 6) . '****' . substr($card_no, -4);
}

/**
 * 페이시스 결제 API 호출
 */
function callUrlPaysisPaymentAPI($api_key, $data) {
    $url = 'https://apis.paysis.co.kr:9443/dalgate/api/v1/manual/pay';

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

    if($curl_error) {
        return [
            'resCode' => 'CURL_ERROR',
            'resMsg' => 'API 통신 오류: ' . $curl_error
        ];
    }

    $result = json_decode($response, true);
    if(!$result) {
        return [
            'resCode' => 'PARSE_ERROR',
            'resMsg' => 'API 응답 파싱 오류 (HTTP: ' . $http_code . ')'
        ];
    }

    return $result;
}

/**
 * 루트업 결제 API 호출
 */
function callUrlRoutupPaymentAPI($pay_key, $data) {
    $url = 'https://api.routeup.kr/api/v2/pay/hand';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: ' . $pay_key
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if($curl_error) {
        return [
            'resCode' => 'CURL_ERROR',
            'resMsg' => 'API 통신 오류: ' . $curl_error
        ];
    }

    $result = json_decode($response, true);
    if(!$result) {
        return [
            'resCode' => 'PARSE_ERROR',
            'resMsg' => 'API 응답 파싱 오류 (HTTP: ' . $http_code . ')'
        ];
    }

    // 루트업 응답을 공통 포맷으로 변환
    return normalizeUrlRoutupResponse($result);
}

/**
 * 루트업 응답 정규화
 */
function normalizeUrlRoutupResponse($response) {
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

/**
 * 섹타나인 결제 API 호출
 */
function callUrlStnPaymentAPI($data) {
    $url = 'https://relay.mainpay.co.kr/v1/api/payments/payment/card-keyin/trans';

    $post_data = http_build_query($data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded; charset=utf-8'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if($curl_error) {
        return [
            'resCode' => 'CURL_ERROR',
            'resMsg' => 'API 통신 오류: ' . $curl_error
        ];
    }

    $result = json_decode($response, true);
    if(!$result) {
        return [
            'resCode' => 'PARSE_ERROR',
            'resMsg' => 'API 응답 파싱 오류 (HTTP: ' . $http_code . ')'
        ];
    }

    // 섹타나인 응답을 공통 포맷으로 변환
    return normalizeUrlStnResponse($result);
}

/**
 * 섹타나인 응답 정규화
 */
function normalizeUrlStnResponse($response) {
    $result_code = $response['resultCode'] ?? '';
    $is_success = ($result_code === '200');
    $data = $response['data'] ?? [];

    $app_date = '';
    if(!empty($data['tranDate']) && !empty($data['tranTime'])) {
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
        '_original' => $response
    ];
}

/**
 * 윈글로벌 결제 API 호출
 */
function callUrlWinglobalPaymentAPI($pay_key, $data) {
    $url = 'https://api.winglobalpay.com/api/pay';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: ' . $pay_key
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if($curl_error) {
        return [
            'resCode' => 'CURL_ERROR',
            'resMsg' => 'API 통신 오류: ' . $curl_error
        ];
    }

    $result = json_decode($response, true);
    if(!$result) {
        return [
            'resCode' => 'PARSE_ERROR',
            'resMsg' => 'API 응답 파싱 오류 (HTTP: ' . $http_code . ')'
        ];
    }

    // 윈글로벌 응답을 공통 포맷으로 변환
    return normalizeUrlWinglobalResponse($result);
}

/**
 * 윈글로벌 응답 정규화
 */
function normalizeUrlWinglobalResponse($response) {
    $result = $response['result'] ?? [];
    $pay = $response['pay'] ?? [];
    $card = $pay['card'] ?? [];

    $result_cd = $result['resultCd'] ?? '';
    $is_success = ($result_cd === '0000');

    $res_msg = $result['resultMsg'] ?? '';
    if(!empty($result['advanceMsg']) && $result['advanceMsg'] !== $res_msg) {
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
        '_original' => $response
    ];
}
?>
