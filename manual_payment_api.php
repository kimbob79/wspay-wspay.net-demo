<?php
/**
 * 수기결제 API 처리
 * - action=pay: 결제 처리
 * - action=cancel: 취소 처리
 *
 * 현재 지원 PG: paysis (페이시스)
 * - 비인증: cardNo, expireYymm
 * - 구인증: cardNo, expireYymm, certPw, certNo
 */

include_once('./_common.php');

// AJAX 요청만 허용
header('Content-Type: application/json; charset=utf-8');

// 수기결제 권한 체크
if(!$is_admin && $member['mb_mailling'] != '1') {
    echo json_encode(['success' => false, 'message' => '수기결제 권한이 없습니다.']);
    exit;
}

// POST 요청만 허용
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST 요청만 허용됩니다.']);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

switch($action) {
    case 'pay':
        processPayment();
        break;
    case 'cancel':
        processCancel();
        break;
    default:
        echo json_encode(['success' => false, 'message' => '올바른 action을 지정하세요. (pay, cancel)']);
        exit;
}

/**
 * 결제 처리
 */
function processPayment() {
    global $member, $is_admin;

    // 필수 파라미터 체크
    $required_fields = ['mkc_id', 'amount', 'goods_name', 'buyer_name', 'card_no', 'expire_yymm', 'installment'];
    foreach($required_fields as $field) {
        if(empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "필수 항목이 누락되었습니다: {$field}"]);
            exit;
        }
    }

    $mkc_id = intval($_POST['mkc_id']);
    $amount = intval(preg_replace('/[^0-9]/', '', $_POST['amount']));
    $goods_name = trim($_POST['goods_name']);
    $buyer_name = trim($_POST['buyer_name']);
    $buyer_phone = isset($_POST['buyer_phone']) ? preg_replace('/[^0-9]/', '', $_POST['buyer_phone']) : '';
    $card_no = preg_replace('/[^0-9]/', '', $_POST['card_no']);
    $expire_yymm = preg_replace('/[^0-9]/', '', $_POST['expire_yymm']);
    $installment = str_pad(intval($_POST['installment']), 2, '0', STR_PAD_LEFT);

    // 구인증 추가 필드
    $cert_pw = isset($_POST['cert_pw']) ? trim($_POST['cert_pw']) : '';
    $cert_no = isset($_POST['cert_no']) ? preg_replace('/[^0-9]/', '', $_POST['cert_no']) : '';

    // Keyin 설정 조회
    $keyin_sql = "SELECT k.*, m.mpc_pg_code, m.mpc_pg_name, m.mpc_type, m.mpc_api_key, m.mpc_mid, m.mpc_mkey
                  FROM g5_member_keyin_config k
                  LEFT JOIN g5_manual_payment_config m ON k.mpc_id = m.mpc_id
                  WHERE k.mkc_id = '{$mkc_id}' AND k.mkc_use = 'Y' AND k.mkc_status = 'active'";
    $keyin = sql_fetch($keyin_sql);

    if(!$keyin) {
        echo json_encode(['success' => false, 'message' => 'Keyin 설정을 찾을 수 없습니다.']);
        exit;
    }

    // 권한 체크 (관리자가 아닌 경우 자신의 설정만 사용 가능)
    if(!$is_admin && $keyin['mb_id'] !== $member['mb_id']) {
        echo json_encode(['success' => false, 'message' => '해당 Keyin 설정에 대한 권한이 없습니다.']);
        exit;
    }

    // API 설정값 결정 (대표가맹점 설정 또는 개별 설정)
    $pg_code = $keyin['mpc_id'] ? $keyin['mpc_pg_code'] : $keyin['mkc_pg_code'];
    $pg_name = $keyin['mpc_id'] ? $keyin['mpc_pg_name'] : $keyin['mkc_pg_name'];
    $auth_type = $keyin['mpc_id'] ? $keyin['mpc_type'] : $keyin['mkc_type'];
    $api_key = $keyin['mpc_id'] ? $keyin['mpc_api_key'] : $keyin['mkc_api_key'];
    $mid = $keyin['mpc_id'] ? $keyin['mpc_mid'] : $keyin['mkc_mid'];
    $mkey = $keyin['mpc_id'] ? $keyin['mpc_mkey'] : $keyin['mkc_mkey'];
    $merchant_oid = $keyin['mkc_oid'] ?: '';

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

    // 결제 한도 체크
    if($keyin['mkc_limit_once'] > 0 && $amount > $keyin['mkc_limit_once']) {
        echo json_encode(['success' => false, 'message' => '1회 결제한도를 초과했습니다. (한도: ' . number_format($keyin['mkc_limit_once']) . '원)']);
        exit;
    }

    // 일일 한도 체크
    if($keyin['mkc_limit_daily'] > 0) {
        $today_sql = "SELECT COALESCE(SUM(pk_amount), 0) as total
                      FROM g5_payment_keyin
                      WHERE mkc_id = '{$mkc_id}'
                      AND pk_status = 'approved'
                      AND DATE(pk_created_at) = CURDATE()";
        $today_row = sql_fetch($today_sql);
        if(($today_row['total'] + $amount) > $keyin['mkc_limit_daily']) {
            echo json_encode(['success' => false, 'message' => '일일 결제한도를 초과합니다. (한도: ' . number_format($keyin['mkc_limit_daily']) . '원)']);
            exit;
        }
    }

    // 월 한도 체크
    if($keyin['mkc_limit_monthly'] > 0) {
        $month_sql = "SELECT COALESCE(SUM(pk_amount), 0) as total
                      FROM g5_payment_keyin
                      WHERE mkc_id = '{$mkc_id}'
                      AND pk_status = 'approved'
                      AND DATE_FORMAT(pk_created_at, '%Y%m') = DATE_FORMAT(NOW(), '%Y%m')";
        $month_row = sql_fetch($month_sql);
        if(($month_row['total'] + $amount) > $keyin['mkc_limit_monthly']) {
            echo json_encode(['success' => false, 'message' => '월 결제한도를 초과합니다. (한도: ' . number_format($keyin['mkc_limit_monthly']) . '원)']);
            exit;
        }
    }

    // 결제 가능 시간 체크
    $current_time = date('H:i');
    if($current_time < $keyin['mkc_time_start'] || $current_time > $keyin['mkc_time_end']) {
        echo json_encode(['success' => false, 'message' => '결제 가능 시간이 아닙니다. (' . $keyin['mkc_time_start'] . ' ~ ' . $keyin['mkc_time_end'] . ')']);
        exit;
    }

    // 주말/공휴일 체크
    if($keyin['mkc_weekend_yn'] !== 'Y') {
        $day_of_week = date('N'); // 1(월) ~ 7(일)
        if($day_of_week >= 6) {
            echo json_encode(['success' => false, 'message' => '주말에는 결제가 불가합니다.']);
            exit;
        }
    }

    // 가맹점 계층 정보 조회
    $member_sql = "SELECT mb_id, mb_nick, mb_1, mb_2, mb_3, mb_4, mb_5, mb_6
                   FROM g5_member WHERE mb_id = '{$keyin['mb_id']}'";
    $merchant = sql_fetch($member_sql);

    if(!$merchant) {
        echo json_encode(['success' => false, 'message' => '가맹점 정보를 찾을 수 없습니다.']);
        exit;
    }

    // 주문번호 생성
    $order_no = generateOrderNumber($merchant_oid);

    // 카드번호 마스킹 (앞 6자리 + **** + 뒤 4자리)
    $card_no_masked = maskCardNumber($card_no);

    // 요청 데이터 구성
    $request_data = [
        'ordNo' => $order_no,
        'mkey' => $mkey,
        'mid' => $mid,
        'goodAmt' => (string)$amount,
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

    // DB에 pending 상태로 먼저 저장
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
        pk_card_no_masked = '" . sql_escape_string($card_no_masked) . "',
        pk_status = 'pending',
        pk_mb_1 = '" . sql_escape_string($merchant['mb_1']) . "',
        pk_mb_2 = '" . sql_escape_string($merchant['mb_2']) . "',
        pk_mb_3 = '" . sql_escape_string($merchant['mb_3']) . "',
        pk_mb_4 = '" . sql_escape_string($merchant['mb_4']) . "',
        pk_mb_5 = '" . sql_escape_string($merchant['mb_5']) . "',
        pk_mb_6 = '" . sql_escape_string($merchant['mb_6']) . "',
        pk_mb_6_name = '" . sql_escape_string($merchant['mb_nick']) . "',
        pk_request_data = '" . sql_escape_string(json_encode($request_data, JSON_UNESCAPED_UNICODE)) . "',
        pk_operator_id = '" . sql_escape_string($member['mb_id']) . "',
        pk_created_at = NOW()";
    sql_query($insert_sql);
    $pk_id = sql_insert_id();

    // PG사별 API 호출
    $response = null;
    switch($pg_code) {
        case 'paysis':
            $response = callPaysisPaymentAPI($api_key, $request_data);
            break;
        default:
            // 지원하지 않는 PG
            sql_query("UPDATE g5_payment_keyin SET pk_status = 'failed', pk_res_code = 'UNSUPPORTED', pk_res_msg = '지원하지 않는 PG사입니다.' WHERE pk_id = '{$pk_id}'");
            echo json_encode(['success' => false, 'message' => '지원하지 않는 PG사입니다: ' . $pg_code]);
            exit;
    }

    // 응답 저장 및 상태 업데이트
    $update_sql = "UPDATE g5_payment_keyin SET
        pk_response_data = '" . sql_escape_string(json_encode($response, JSON_UNESCAPED_UNICODE)) . "',
        pk_res_code = '" . sql_escape_string($response['resCode'] ?? '') . "',
        pk_res_msg = '" . sql_escape_string($response['resMsg'] ?? '') . "',
        pk_updated_at = NOW()";

    if(isset($response['resCode']) && $response['resCode'] === '0000') {
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

        echo json_encode([
            'success' => true,
            'message' => '결제가 완료되었습니다.',
            'data' => [
                'pk_id' => $pk_id,
                'order_no' => $order_no,
                'app_no' => $response['appNo'] ?? '',
                'app_date' => $response['appDate'] ?? '',
                'amount' => $amount,
                'card_issuer' => $response['vanIssCpCd'] ?? ''
            ]
        ]);
    } else {
        // 실패
        $update_sql .= ", pk_status = 'failed'";
        $update_sql .= " WHERE pk_id = '{$pk_id}'";
        sql_query($update_sql);

        echo json_encode([
            'success' => false,
            'message' => '결제에 실패했습니다: ' . ($response['resMsg'] ?? '알 수 없는 오류'),
            'error_code' => $response['resCode'] ?? ''
        ]);
    }
}

/**
 * 취소 처리
 */
function processCancel() {
    global $member, $is_admin;

    // 필수 파라미터 체크
    $required_fields = ['pk_id', 'cancel_name', 'cancel_reason'];
    foreach($required_fields as $field) {
        if(empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "필수 항목이 누락되었습니다: {$field}"]);
            exit;
        }
    }

    $pk_id = intval($_POST['pk_id']);
    $cancel_name = trim($_POST['cancel_name']);
    $cancel_reason = trim($_POST['cancel_reason']);
    $cancel_amount = isset($_POST['cancel_amount']) ? intval(preg_replace('/[^0-9]/', '', $_POST['cancel_amount'])) : 0;

    // 원거래 조회
    $payment_sql = "SELECT p.*, k.mkc_cancel_yn, k.mkc_api_key, k.mkc_mid, k.mkc_mkey,
                           m.mpc_api_key, m.mpc_mid, m.mpc_mkey
                    FROM g5_payment_keyin p
                    LEFT JOIN g5_member_keyin_config k ON p.mkc_id = k.mkc_id
                    LEFT JOIN g5_manual_payment_config m ON k.mpc_id = m.mpc_id
                    WHERE p.pk_id = '{$pk_id}'";
    $payment = sql_fetch($payment_sql);

    if(!$payment) {
        echo json_encode(['success' => false, 'message' => '거래 정보를 찾을 수 없습니다.']);
        exit;
    }

    // 권한 체크
    if(!$is_admin) {
        // 가맹점인 경우 자신의 거래만 취소 가능
        if($payment['mb_id'] !== $member['mb_id']) {
            echo json_encode(['success' => false, 'message' => '해당 거래에 대한 취소 권한이 없습니다.']);
            exit;
        }
        // 취소 가능 여부 체크
        if($payment['mkc_cancel_yn'] !== 'Y') {
            echo json_encode(['success' => false, 'message' => '해당 설정은 취소가 허용되지 않습니다.']);
            exit;
        }
    }

    // 상태 체크
    if($payment['pk_status'] !== 'approved') {
        echo json_encode(['success' => false, 'message' => '승인된 거래만 취소할 수 있습니다. 현재 상태: ' . $payment['pk_status']]);
        exit;
    }

    // 취소 금액 설정 (기본값: 전액 취소)
    if($cancel_amount <= 0) {
        $cancel_amount = $payment['pk_amount'] - $payment['pk_cancel_amount'];
    }

    // 취소 가능 금액 체크
    $remaining_amount = $payment['pk_amount'] - $payment['pk_cancel_amount'];
    if($cancel_amount > $remaining_amount) {
        echo json_encode(['success' => false, 'message' => '취소 가능 금액을 초과했습니다. (취소 가능: ' . number_format($remaining_amount) . '원)']);
        exit;
    }

    // API 설정값 결정
    $api_key = $payment['mpc_api_key'] ?: $payment['mkc_api_key'];
    $mid = $payment['mpc_mid'] ?: $payment['mkc_mid'];

    // 취소 요청 데이터
    $cancel_request = [
        'ordNo' => $payment['pk_order_no'],
        'mid' => $mid,
        'canNm' => $cancel_name,
        'canMsg' => $cancel_reason,
        'canAmt' => (string)$cancel_amount
    ];

    // PG사별 취소 API 호출
    $response = null;
    switch($payment['pk_pg_code']) {
        case 'paysis':
            $response = callPaysisCancelAPI($api_key, $cancel_request);
            break;
        default:
            echo json_encode(['success' => false, 'message' => '지원하지 않는 PG사입니다: ' . $payment['pk_pg_code']]);
            exit;
    }

    // 응답 처리
    if(isset($response['resCode']) && $response['resCode'] === '0000') {
        // 취소 성공
        $new_cancel_amount = $payment['pk_cancel_amount'] + $cancel_amount;
        $new_status = ($new_cancel_amount >= $payment['pk_amount']) ? 'cancelled' : 'partial_cancelled';
        $cancel_date = ($response['cancelDate'] ?? '') . ($response['cancelTime'] ?? '');

        $update_sql = "UPDATE g5_payment_keyin SET
            pk_status = '{$new_status}',
            pk_cancel_amount = '{$new_cancel_amount}',
            pk_cancel_name = '" . sql_escape_string($cancel_name) . "',
            pk_cancel_reason = '" . sql_escape_string($cancel_reason) . "',
            pk_cancel_date = '" . sql_escape_string($cancel_date) . "',
            pk_updated_at = NOW()
            WHERE pk_id = '{$pk_id}'";
        sql_query($update_sql);

        echo json_encode([
            'success' => true,
            'message' => '취소가 완료되었습니다.',
            'data' => [
                'pk_id' => $pk_id,
                'cancel_amount' => $cancel_amount,
                'cancel_date' => $cancel_date,
                'status' => $new_status
            ]
        ]);
    } else {
        // 취소 실패
        echo json_encode([
            'success' => false,
            'message' => '취소에 실패했습니다: ' . ($response['resMsg'] ?? '알 수 없는 오류'),
            'error_code' => $response['resCode'] ?? ''
        ]);
    }
}

/**
 * 페이시스 결제 API 호출
 */
function callPaysisPaymentAPI($api_key, $data) {
    $url = 'https://apis.paysis.co.kr:9443/dalgate/api/v1/manual/pay';
    $pg_code = 'paysis';
    $action = 'pay';

    // 카드번호는 로그에 남기지 않도록 별도 처리
    $log_data = $data;
    if(isset($log_data['cardNo'])) {
        $log_data['cardNo'] = maskCardNumber($log_data['cardNo']);
    }
    if(isset($log_data['certPw'])) {
        $log_data['certPw'] = '**';
    }
    if(isset($log_data['certNo'])) {
        $log_data['certNo'] = '******';
    }

    // 요청 로그
    writeApiLog($pg_code, $action, 'REQUEST', $log_data);

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
        $result = [
            'resCode' => 'CURL_ERROR',
            'resMsg' => 'API 통신 오류: ' . $curl_error
        ];
        // 에러 로그
        writeApiLog($pg_code, $action, 'ERROR', ['http_code' => $http_code, 'curl_error' => $curl_error]);
        return $result;
    }

    $result = json_decode($response, true);
    if(!$result) {
        $result = [
            'resCode' => 'PARSE_ERROR',
            'resMsg' => 'API 응답 파싱 오류 (HTTP: ' . $http_code . ')'
        ];
        // 에러 로그
        writeApiLog($pg_code, $action, 'ERROR', ['http_code' => $http_code, 'raw_response' => $response]);
        return $result;
    }

    // 응답 로그
    writeApiLog($pg_code, $action, 'RESPONSE', $result);

    return $result;
}

/**
 * 페이시스 취소 API 호출
 */
function callPaysisCancelAPI($api_key, $data) {
    $url = 'https://apis.paysis.co.kr:9443/dalgate/api/v1/manual/cancel';
    $pg_code = 'paysis';
    $action = 'cancel';

    // 요청 로그
    writeApiLog($pg_code, $action, 'REQUEST', $data);

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
        $result = [
            'resCode' => 'CURL_ERROR',
            'resMsg' => 'API 통신 오류: ' . $curl_error
        ];
        // 에러 로그
        writeApiLog($pg_code, $action, 'ERROR', ['http_code' => $http_code, 'curl_error' => $curl_error]);
        return $result;
    }

    $result = json_decode($response, true);
    if(!$result) {
        $result = [
            'resCode' => 'PARSE_ERROR',
            'resMsg' => 'API 응답 파싱 오류 (HTTP: ' . $http_code . ')'
        ];
        // 에러 로그
        writeApiLog($pg_code, $action, 'ERROR', ['http_code' => $http_code, 'raw_response' => $response]);
        return $result;
    }

    // 응답 로그
    writeApiLog($pg_code, $action, 'RESPONSE', $result);

    return $result;
}

/**
 * 주문번호 생성 (OID-YYMM-HHMM-SSRR)
 */
function generateOrderNumber($merchant_oid) {
    $oid = $merchant_oid ?: 'XXXX';
    $yymm = date('ym');
    $hhmm = date('Hi');
    $ss = date('s');
    $rand = strtoupper(substr(md5(microtime(true) . mt_rand()), 0, 2));
    return "{$oid}-{$yymm}-{$hhmm}-{$ss}{$rand}";
}

/**
 * 카드번호 마스킹 (앞6자리 + **** + 뒤4자리)
 */
function maskCardNumber($card_no) {
    $len = strlen($card_no);
    if($len < 10) return str_repeat('*', $len);

    $first = substr($card_no, 0, 6);
    $last = substr($card_no, -4);
    $middle_len = $len - 10;
    $middle = str_repeat('*', max(4, $middle_len));

    return $first . $middle . $last;
}

/**
 * API 로그 기록
 * 로그 경로: /logs/api/{PG코드}/{날짜}.log
 *
 * @param string $pg_code PG사 코드 (paysis, danal, korpay 등)
 * @param string $action 액션 (pay, cancel)
 * @param string $type 로그 타입 (REQUEST, RESPONSE, ERROR)
 * @param array $data 로그 데이터
 */
function writeApiLog($pg_code, $action, $type, $data) {
    // 로그 기본 경로
    $base_path = dirname(__FILE__) . '/logs/api';

    // PG사별 폴더
    $pg_path = $base_path . '/' . $pg_code;

    // 폴더 생성 (없으면)
    if(!is_dir($base_path)) {
        @mkdir($base_path, 0755, true);
    }
    if(!is_dir($pg_path)) {
        @mkdir($pg_path, 0755, true);
    }

    // 날짜별 로그 파일
    $log_file = $pg_path . '/' . date('Y-m-d') . '.log';

    // 로그 포맷
    $timestamp = date('Y-m-d H:i:s.') . substr(microtime(), 2, 3);
    $log_entry = sprintf(
        "[%s] [%s] [%s] %s\n",
        $timestamp,
        strtoupper($action),
        $type,
        json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );

    // 파일에 기록
    @file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}
