<?php
/**
 * 수기결제 API 처리
 * - action=pay: 결제 처리
 * - action=cancel: 취소 처리
 *
 * 지원 PG:
 * - paysis (페이시스): API KEY, MID, MKEY 사용
 *   - 비인증: cardNo, expireYymm
 *   - 구인증: cardNo, expireYymm, certPw, certNo
 * - rootup (루트업): MID, TID, 결제KEY 사용
 *   - 비인증: card_num, yymm
 *   - 구인증: card_num, yymm, card_pw, auth_num
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

// [DEBUG] API 요청 시작 로그 (가장 먼저 기록) - 민감정보 마스킹
$debug_post = $_POST;
if(isset($debug_post['card_no'])) $debug_post['card_no'] = substr($debug_post['card_no'], 0, 6) . '****' . substr($debug_post['card_no'], -4);
if(isset($debug_post['cert_pw'])) $debug_post['cert_pw'] = '**';
if(isset($debug_post['cert_no'])) $debug_post['cert_no'] = '******';
writeErrorLog('DEBUG_API_START', 'API 요청 시작', [
    'action' => $action,
    'mb_id' => $member['mb_id'] ?? 'unknown',
    'mb_level' => $member['mb_level'] ?? 'unknown',
    'mb_mailling' => $member['mb_mailling'] ?? 'unknown',
    'is_admin' => $is_admin ?? false,
    'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
    'post_data' => $debug_post
]);

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

    // [DEBUG] API 진입 로그 - 상세 파라미터
    writeErrorLog('DEBUG_ENTRY', 'processPayment() 진입', [
        'mb_id' => $member['mb_id'] ?? 'unknown',
        'mb_level' => $member['mb_level'] ?? 'unknown',
        'is_admin' => $is_admin,
        'mkc_id' => $_POST['mkc_id'] ?? 'not_set',
        'amount' => $_POST['amount'] ?? 'not_set',
        'goods_name' => $_POST['goods_name'] ?? 'not_set',
        'buyer_name' => $_POST['buyer_name'] ?? 'not_set',
        'buyer_phone' => $_POST['buyer_phone'] ?? 'not_set',
        'buyer_email' => $_POST['buyer_email'] ?? 'not_set',
        'card_no_masked' => isset($_POST['card_no']) ? substr($_POST['card_no'], 0, 6) . '****' : 'not_set',
        'expire_yymm' => $_POST['expire_yymm'] ?? 'not_set',
        'installment' => $_POST['installment'] ?? 'not_set'
    ]);

    // 필수 파라미터 체크
    $required_fields = ['mkc_id', 'amount', 'goods_name', 'buyer_name', 'card_no', 'expire_yymm', 'installment'];
    foreach($required_fields as $field) {
        if(empty($_POST[$field])) {
            writeErrorLog('DEBUG_MISSING_PARAM', '필수 파라미터 누락', [
                'missing_field' => $field,
                'mb_id' => $member['mb_id'] ?? 'unknown',
                'all_post_keys' => array_keys($_POST)
            ]);
            echo json_encode(['success' => false, 'message' => "필수 항목이 누락되었습니다: {$field}"]);
            exit;
        }
    }

    $mkc_id = intval($_POST['mkc_id']);
    $amount = intval(preg_replace('/[^0-9]/', '', $_POST['amount']));
    $goods_name = trim($_POST['goods_name']);
    $buyer_name = trim($_POST['buyer_name']);
    $buyer_phone = isset($_POST['buyer_phone']) ? preg_replace('/[^0-9]/', '', $_POST['buyer_phone']) : '';
    $buyer_email = isset($_POST['buyer_email']) ? trim($_POST['buyer_email']) : '';  // 윈글로벌용
    $card_no = preg_replace('/[^0-9]/', '', $_POST['card_no']);
    $expire_yymm = preg_replace('/[^0-9]/', '', $_POST['expire_yymm']);
    $installment = str_pad(intval($_POST['installment']), 2, '0', STR_PAD_LEFT);

    // 구인증 추가 필드
    $cert_pw = isset($_POST['cert_pw']) ? trim($_POST['cert_pw']) : '';
    $cert_no = isset($_POST['cert_no']) ? preg_replace('/[^0-9]/', '', $_POST['cert_no']) : '';

    // Keyin 설정 조회
    $keyin_sql = "SELECT k.*, m.mpc_pg_code, m.mpc_pg_name, m.mpc_type, m.mpc_api_key, m.mpc_mid, m.mpc_mkey,
                  m.mpc_rootup_mid, m.mpc_rootup_tid, m.mpc_rootup_key,
                  m.mpc_stn_mbrno, m.mpc_stn_apikey,
                  m.mpc_winglobal_tid, m.mpc_winglobal_apikey
                  FROM g5_member_keyin_config k
                  LEFT JOIN g5_manual_payment_config m ON k.mpc_id = m.mpc_id
                  WHERE k.mkc_id = '{$mkc_id}' AND k.mkc_use = 'Y' AND k.mkc_status = 'active'";
    $keyin = sql_fetch($keyin_sql);

    if(!$keyin) {
        writeErrorLog('DEBUG_KEYIN_NOT_FOUND', 'Keyin 설정을 찾을 수 없음', [
            'mkc_id' => $mkc_id,
            'mb_id' => $member['mb_id'] ?? 'unknown',
            'sql' => $keyin_sql
        ]);
        echo json_encode(['success' => false, 'message' => 'Keyin 설정을 찾을 수 없습니다.']);
        exit;
    }

    // 권한 체크 (관리자가 아닌 경우 자신의 설정만 사용 가능)
    if(!$is_admin && $keyin['mb_id'] !== $member['mb_id']) {
        writeErrorLog('DEBUG_PERMISSION_DENIED', '권한 없음', [
            'mkc_id' => $mkc_id,
            'member_mb_id' => $member['mb_id'] ?? 'unknown',
            'keyin_mb_id' => $keyin['mb_id'] ?? 'unknown',
            'is_admin' => $is_admin
        ]);
        echo json_encode(['success' => false, 'message' => '해당 Keyin 설정에 대한 권한이 없습니다.']);
        exit;
    }

    // API 설정값 결정 (대표가맹점 설정 또는 개별 설정)
    $pg_code = $keyin['mpc_id'] ? $keyin['mpc_pg_code'] : $keyin['mkc_pg_code'];
    $pg_name = $keyin['mpc_id'] ? $keyin['mpc_pg_name'] : $keyin['mkc_pg_name'];
    $auth_type = $keyin['mpc_id'] ? $keyin['mpc_type'] : $keyin['mkc_type'];
    $merchant_oid = $keyin['mkc_oid'] ?: '';

    // PG사별 API 설정값
    if($pg_code === 'rootup') {
        // 루트업: MID, TID, 결제KEY
        $api_key = $keyin['mpc_id'] ? $keyin['mpc_rootup_key'] : $keyin['mkc_api_key'];  // 결제KEY
        $mid = $keyin['mpc_id'] ? $keyin['mpc_rootup_mid'] : $keyin['mkc_mid'];
        $tid = $keyin['mpc_id'] ? $keyin['mpc_rootup_tid'] : $keyin['mkc_mkey'];  // TID
        $mkey = '';  // 루트업은 mkey 사용 안함
        $mbr_no = '';  // 루트업은 mbrNo 사용 안함
    } else if($pg_code === 'stn') {
        // 섹타나인: MBRNO, APIKEY
        $mbr_no = $keyin['mpc_id'] ? $keyin['mpc_stn_mbrno'] : $keyin['mkc_mid'];  // 가맹점 번호
        $api_key = $keyin['mpc_id'] ? $keyin['mpc_stn_apikey'] : $keyin['mkc_api_key'];  // API KEY (signature 생성용)
        $mid = $mbr_no;  // mid 필드에도 mbrNo 저장 (DB 저장용)
        $mkey = '';
        $tid = '';
    } else if($pg_code === 'winglobal') {
        // 윈글로벌: TID, API KEY (Pay Key)
        $tid = $keyin['mpc_id'] ? $keyin['mpc_winglobal_tid'] : $keyin['mkc_mid'];  // TID
        $api_key = $keyin['mpc_id'] ? $keyin['mpc_winglobal_apikey'] : $keyin['mkc_api_key'];  // Pay Key
        $mid = $tid;  // mid 필드에도 TID 저장 (DB 저장용)
        $mkey = '';
        $mbr_no = '';
    } else {
        // 페이시스 등 기타: API KEY, MID, MKEY
        $api_key = $keyin['mpc_id'] ? $keyin['mpc_api_key'] : $keyin['mkc_api_key'];
        $mid = $keyin['mpc_id'] ? $keyin['mpc_mid'] : $keyin['mkc_mid'];
        $mkey = $keyin['mpc_id'] ? $keyin['mpc_mkey'] : $keyin['mkc_mkey'];
        $tid = '';  // 페이시스는 tid 사용 안함
        $mbr_no = '';  // 페이시스는 mbrNo 사용 안함
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

    // 할부 제한 체크
    $max_installment = intval($keyin['mkc_max_installment']);
    $req_installment = intval($installment);
    if($max_installment > 0 && $req_installment > $max_installment) {
        echo json_encode(['success' => false, 'message' => '허용된 최대 할부개월을 초과했습니다. (최대: ' . $max_installment . '개월)']);
        exit;
    }

    // 중복결제 체크 (동일 카드번호+금액으로 5분 이내 승인된 결제가 있는지)
    if($keyin['mkc_duplicate_yn'] !== 'Y') {
        $card_no_masked_check = maskCardNumber($card_no);
        $dup_sql = "SELECT pk_id FROM g5_payment_keyin
                    WHERE mkc_id = '{$mkc_id}'
                    AND pk_card_no_masked = '" . sql_escape_string($card_no_masked_check) . "'
                    AND pk_amount = '{$amount}'
                    AND pk_status = 'approved'
                    AND pk_created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                    LIMIT 1";
        $dup_row = sql_fetch($dup_sql);
        if($dup_row) {
            echo json_encode(['success' => false, 'message' => '동일한 카드와 금액으로 최근 5분 이내 결제된 내역이 있습니다. 중복결제가 차단되었습니다.']);
            exit;
        }
    }

    // 가맹점 계층 정보 조회
    $member_sql = "SELECT mb_id, mb_nick, mb_1, mb_2, mb_3, mb_4, mb_5, mb_6
                   FROM g5_member WHERE mb_id = '{$keyin['mb_id']}'";
    $merchant = sql_fetch($member_sql);

    if(!$merchant) {
        writeErrorLog('DEBUG_MERCHANT_NOT_FOUND', '가맹점 정보를 찾을 수 없음', [
            'keyin_mb_id' => $keyin['mb_id'] ?? 'unknown',
            'mkc_id' => $mkc_id,
            'operator_id' => $member['mb_id'] ?? 'unknown',
            'sql' => $member_sql
        ]);
        echo json_encode(['success' => false, 'message' => '가맹점 정보를 찾을 수 없습니다.']);
        exit;
    } 

    // 주문번호 생성 (페이시스는 30자 필수)
    $order_no = generateOrderNumber($merchant_oid, $pg_code);

    // 카드번호 마스킹 (앞 6자리 + **** + 뒤 4자리)
    $card_no_masked = maskCardNumber($card_no);

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
            $request_data['card_pw'] = $cert_pw;   // 비밀번호 앞 2자리
            $request_data['auth_num'] = $cert_no;  // 생년월일 또는 사업자번호
        }
    } else if($pg_code === 'stn') {
        // 섹타나인 API 요청 데이터
        // timestamp: yyMMddHHmmssSSS (15자리)
        $timestamp = date('ymdHis') . substr(microtime(), 2, 3);
        // signature: sha256(mbrNo|mbrRefNo|amount|apiKey|timestamp)
        $signature = hash('sha256', $mbr_no . '|' . $order_no . '|' . $amount . '|' . $api_key . '|' . $timestamp);

        $request_data = [
            'mbrNo' => $mbr_no,                              // 가맹점 번호 (6자리)
            'mbrRefNo' => $order_no,                         // 가맹점 주문번호 (20자)
            'paymethod' => 'CARD',                           // 지불수단 (고정값)
            'cardNo' => $card_no,                            // 카드번호
            'expd' => $expire_yymm,                          // 유효기간 YYMM
            'amount' => (string)$amount,                     // 결제금액
            'installment' => str_pad($installment, 2, '0', STR_PAD_LEFT),  // 할부개월 (2자리)
            'goodsName' => mb_substr(preg_replace('/[^\p{L}\p{N}\s]/u', '', $goods_name), 0, 30),  // 상품명 (특수문자 제거, 30자)
            'timestamp' => $timestamp,                       // 시스템 시각
            'signature' => $signature,                       // 서명값
            'keyinAuthType' => ($auth_type === 'auth') ? 'O' : 'K',  // K: 비인증, O: 구인증
            'customerName' => $buyer_name,                   // 구매자명
            'customerTelNo' => $buyer_phone                  // 구매자 연락처
        ];

        // 구인증인 경우 인증정보 추가 (필수)
        if($auth_type === 'auth') {
            // authType: 0=생년월일, 1=사업자번호 (자릿수로 판단)
            $auth_type_code = (strlen($cert_no) == 10) ? '1' : '0';
            $request_data['authType'] = $auth_type_code;     // 인증타입
            $request_data['regNo'] = $cert_no;               // 생년월일(YYMMDD) 또는 사업자번호
            $request_data['passwd'] = $cert_pw;              // 카드 비밀번호 앞 2자리
        }
    } else if($pg_code === 'winglobal') {
        // 윈글로벌 API 요청 데이터
        // 이메일 필수 체크
        if(empty($buyer_email)) {
            echo json_encode(['success' => false, 'message' => '윈글로벌 결제는 구매자 이메일이 필수입니다.']);
            exit;
        }
        // 이메일 형식 검증
        if(!filter_var($buyer_email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => '올바른 이메일 형식을 입력하세요.']);
            exit;
        }

        $request_data = [
            'pay' => [
                'trxType' => 'ONTR',                         // 고정값
                'trackId' => $order_no,                      // 가맹점 주문번호
                'amount' => (int)$amount,                    // 결제금액 (long)
                'payerName' => $buyer_name,                  // 구매자명
                'payerEmail' => $buyer_email,                // 구매자 이메일
                'payerTel' => $buyer_phone,                  // 구매자 전화번호
                'card' => [
                    'number' => $card_no,                    // 카드번호
                    'expiry' => $expire_yymm,                // 유효기간 YYMM
                    'cvv' => '',                             // CVV 미사용
                    'installment' => (int)$installment       // 할부개월
                ],
                'products' => [
                    [
                        'prodId' => '',                      // 상품ID (미사용)
                        'name' => $goods_name,               // 상품명
                        'qty' => 1,                          // 수량 고정
                        'price' => (int)$amount,             // 가격
                        'desc' => $goods_name                // 설명 (상품명과 동일)
                    ]
                ],
                'trxId' => '',                               // 응답시 회신
                'udf1' => '',                                // 가맹점 정의 필드
                'udf2' => '',
                'metadata' => []                             // 비인증일 때 빈 객체
            ]
        ];

        // 구인증인 경우 metadata에 인증정보 추가
        if($auth_type === 'auth') {
            $request_data['pay']['metadata'] = [
                'cardAuth' => 'true',                        // 구인증 플래그
                'authPw' => $cert_pw,                        // 카드비밀번호 앞 2자리
                'authDob' => $cert_no                        // 생년월일 YYMMDD
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

    // [DEBUG] INSERT 전 상태 로그 - 모든 주요 변수
    writeErrorLog('DEBUG_PRE_INSERT', 'INSERT 시작 직전', [
        'order_no' => $order_no,
        'merchant_oid' => $merchant_oid,
        'pg_code' => $pg_code,
        'pg_name' => $pg_name,
        'auth_type' => $auth_type,
        'amount' => $amount,
        'installment' => $installment,
        'mkc_id' => $mkc_id,
        'keyin_mb_id' => $keyin['mb_id'] ?? 'unknown',
        'keyin_mpc_id' => $keyin['mpc_id'] ?? 'null',
        'mid' => $mid ?? 'not_set',
        'api_key_set' => !empty($api_key) ? 'yes' : 'no',
        'goods_name' => $goods_name,
        'buyer_name' => $buyer_name,
        'buyer_phone' => $buyer_phone,
        'buyer_email' => $buyer_email,
        'card_no_masked' => $card_no_masked,
        'merchant_mb_1' => $merchant['mb_1'] ?? '',
        'merchant_mb_6' => $merchant['mb_6'] ?? '',
        'merchant_nick' => $merchant['mb_nick'] ?? ''
    ]);

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
        pk_request_data = '" . sql_escape_string(json_encode($request_data, JSON_UNESCAPED_UNICODE)) . "',
        pk_operator_id = '" . sql_escape_string($member['mb_id']) . "',
        pk_created_at = NOW()";

    // INSERT 실행 및 에러 체크
    $insert_result = sql_query($insert_sql, false);
    if(!$insert_result) {
        global $g5;
        $sql_error = mysqli_error($g5['connect_db']);
        $errno = mysqli_errno($g5['connect_db']);
        writeErrorLog('INSERT_ERROR', 'g5_payment_keyin INSERT 실패', [
            'order_no' => $order_no,
            'merchant_oid' => $merchant_oid,
            'mb_id' => $keyin['mb_id'] ?? '',
            'mkc_id' => $mkc_id,
            'pg_code' => $pg_code,
            'pg_name' => $pg_name,
            'auth_type' => $auth_type,
            'amount' => $amount,
            'goods_name' => $goods_name,
            'buyer_name' => $buyer_name,
            'buyer_phone' => $buyer_phone,
            'buyer_email' => $buyer_email,
            'card_no_masked' => $card_no_masked,
            'operator_id' => $member['mb_id'] ?? '',
            'sql_errno' => $errno,
            'sql_error' => $sql_error,
            'sql_length' => strlen($insert_sql)
        ]);
        echo json_encode(['success' => false, 'message' => 'DB 저장 중 오류가 발생했습니다: ' . $sql_error]);
        exit;
    }
    $pk_id = sql_insert_id();

    // [DEBUG] INSERT 성공 로그
    writeErrorLog('DEBUG_POST_INSERT', 'INSERT 성공', [
        'pk_id' => $pk_id,
        'order_no' => $order_no,
        'merchant_oid' => $merchant_oid,
        'mb_id' => $keyin['mb_id'] ?? '',
        'mkc_id' => $mkc_id,
        'pg_code' => $pg_code,
        'pg_name' => $pg_name,
        'auth_type' => $auth_type,
        'amount' => $amount,
        'goods_name' => $goods_name,
        'buyer_name' => $buyer_name,
        'card_no_masked' => $card_no_masked,
        'operator_id' => $member['mb_id'] ?? ''
    ]);

    // PG사별 API 호출
    $response = null;
    switch($pg_code) {
        case 'paysis':
            $response = callPaysisPaymentAPI($api_key, $request_data);
            break;
        case 'rootup':
            $response = callRoutupPaymentAPI($api_key, $request_data);
            break;
        case 'stn':
            $response = callStnPaymentAPI($request_data);
            break;
        case 'winglobal':
            $response = callWinglobalPaymentAPI($api_key, $request_data);
            break;
        default:
            // 지원하지 않는 PG
            writeErrorLog('DEBUG_UNSUPPORTED_PG', '지원하지 않는 PG사', [
                'pk_id' => $pk_id,
                'order_no' => $order_no,
                'pg_code' => $pg_code,
                'pg_name' => $pg_name,
                'amount' => $amount,
                'mb_id' => $member['mb_id'] ?? ''
            ]);
            sql_query("UPDATE g5_payment_keyin SET pk_status = 'failed', pk_res_code = 'UNSUPPORTED', pk_res_msg = '지원하지 않는 PG사입니다.' WHERE pk_id = '{$pk_id}'");
            echo json_encode(['success' => false, 'message' => '지원하지 않는 PG사입니다: ' . $pg_code]);
            exit;
    }

    // [DEBUG] PG API 응답 로그 - 전체 응답
    writeErrorLog('DEBUG_PG_RESPONSE', 'PG API 응답 수신', [
        'pk_id' => $pk_id,
        'order_no' => $order_no,
        'merchant_oid' => $merchant_oid,
        'mb_id' => $member['mb_id'] ?? '',
        'pg_code' => $pg_code,
        'pg_name' => $pg_name,
        'auth_type' => $auth_type,
        'amount' => $amount,
        'response_is_null' => is_null($response),
        'response_is_array' => is_array($response),
        'res_code' => $response['resCode'] ?? 'null',
        'res_msg' => $response['resMsg'] ?? 'null',
        'app_no' => $response['appNo'] ?? 'null',
        'app_date' => $response['appDate'] ?? 'null',
        'tid' => $response['tid'] ?? 'null',
        'full_response' => $response
    ]);

    // response가 null이면 에러 처리
    if($response === null || !is_array($response)) {
        writeErrorLog('DEBUG_PG_NULL_RESPONSE', 'PG API 응답이 null 또는 비정상', [
            'pk_id' => $pk_id,
            'order_no' => $order_no,
            'pg_code' => $pg_code,
            'response_type' => gettype($response),
            'response_value' => $response
        ]);
        sql_query("UPDATE g5_payment_keyin SET pk_status = 'failed', pk_res_code = 'NULL_RESPONSE', pk_res_msg = 'PG API 응답 없음', pk_updated_at = NOW() WHERE pk_id = '{$pk_id}'");
        echo json_encode(['success' => false, 'message' => 'PG API 응답을 받지 못했습니다.']);
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

        // [DEBUG] 최종 성공 응답 직전 로그
        writeErrorLog('DEBUG_FINAL_SUCCESS', '결제 성공 - JSON 응답 직전', [
            'pk_id' => $pk_id,
            'order_no' => $order_no,
            'merchant_oid' => $merchant_oid,
            'mb_id' => $member['mb_id'] ?? '',
            'pg_code' => $pg_code,
            'pg_name' => $pg_name,
            'auth_type' => $auth_type,
            'amount' => $amount,
            'card_no_masked' => substr($card_no, 0, 6) . '****' . substr($card_no, -4),
            'app_no' => $response['appNo'] ?? '',
            'app_date' => $response['appDate'] ?? '',
            'res_code' => $response['resCode'] ?? '',
            'res_msg' => $response['resMsg'] ?? '',
            'tid' => $response['tid'] ?? '',
            'card_issuer' => $response['vanIssCpCd'] ?? '',
            'card_acquirer' => $response['vanCpCd'] ?? ''
        ]);

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
        // [DEBUG] PG 응답 실패 로그
        writeErrorLog('DEBUG_PG_FAILED', 'PG 응답 실패', [
            'pk_id' => $pk_id,
            'order_no' => $order_no,
            'merchant_oid' => $merchant_oid,
            'mb_id' => $member['mb_id'] ?? '',
            'pg_code' => $pg_code,
            'pg_name' => $pg_name,
            'auth_type' => $auth_type,
            'amount' => $amount,
            'card_no_masked' => substr($card_no, 0, 6) . '****' . substr($card_no, -4),
            'res_code' => $response['resCode'] ?? '',
            'res_msg' => $response['resMsg'] ?? '',
            'full_response' => $response
        ]);

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
                           m.mpc_api_key, m.mpc_mid, m.mpc_mkey,
                           m.mpc_rootup_mid, m.mpc_rootup_tid, m.mpc_rootup_key,
                           m.mpc_stn_mbrno, m.mpc_stn_apikey,
                           m.mpc_winglobal_tid, m.mpc_winglobal_apikey
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

    // PG사별 API 설정값 결정
    $pg_code = $payment['pk_pg_code'];
    if($pg_code === 'rootup') {
        // 루트업: 결제KEY, MID, TID
        $api_key = $payment['mpc_rootup_key'] ?: $payment['mkc_api_key'];
        $mid = $payment['mpc_rootup_mid'] ?: $payment['mkc_mid'];
        $tid = $payment['mpc_rootup_tid'] ?: $payment['mkc_mkey'];
        $mbr_no = '';
    } else if($pg_code === 'stn') {
        // 섹타나인: MBRNO, APIKEY
        $mbr_no = $payment['mpc_stn_mbrno'] ?: $payment['mkc_mid'];
        $api_key = $payment['mpc_stn_apikey'] ?: $payment['mkc_api_key'];
        $mid = $mbr_no;
        $tid = '';
    } else if($pg_code === 'winglobal') {
        // 윈글로벌: TID, API KEY (Pay Key)
        $tid = $payment['mpc_winglobal_tid'] ?: $payment['mkc_mid'];
        $api_key = $payment['mpc_winglobal_apikey'] ?: $payment['mkc_api_key'];
        $mid = $tid;
        $mbr_no = '';
    } else {
        // 페이시스 등: API KEY, MID
        $api_key = $payment['mpc_api_key'] ?: $payment['mkc_api_key'];
        $mid = $payment['mpc_mid'] ?: $payment['mkc_mid'];
        $tid = '';
        $mbr_no = '';
    }

    // PG사별 취소 요청 데이터
    if($pg_code === 'rootup') {
        // 루트업 취소 요청 데이터
        // trx_id는 결제 응답에서 받은 거래번호 (pk_tid에 저장됨)
        $cancel_request = [
            'mid' => $mid,
            'tid' => $tid,
            'amount' => (string)$cancel_amount,
            'trx_id' => $payment['pk_tid']  // 결제시 응답받은 거래번호
        ];
    } else if($pg_code === 'stn') {
        // 섹타나인 취소 요청 데이터
        // 결제 응답에서 받은 refNo, tranDate, payType 필요
        $response_data = json_decode($payment['pk_response_data'], true);

        // 정규화된 응답에서 원본 데이터 추출 (_stn_data 또는 _original.data)
        $stn_data = $response_data['_stn_data'] ?? ($response_data['_original']['data'] ?? $response_data);

        // 디버그 로그: 원본 응답 데이터 확인
        writeApiLog('stn', 'cancel_debug', 'ORIGINAL_RESPONSE', [
            'pk_tid' => $payment['pk_tid'],
            'pk_app_date' => $payment['pk_app_date'] ?? '',
            'pk_order_no' => $payment['pk_order_no'],
            'stn_data' => $stn_data,
            'response_data_keys' => array_keys($response_data ?? [])
        ]);

        // orgTranDate는 정확히 6자리(YYMMDD)여야 함
        $org_tran_date = $stn_data['tranDate'] ?? '';
        if(strlen($org_tran_date) == 8) {
            // YYYYMMDD -> YYMMDD (앞 2자리 제거)
            $org_tran_date = substr($org_tran_date, 2, 6);
        } else if(strlen($org_tran_date) != 6 && !empty($payment['pk_app_date'])) {
            // pk_app_date에서 추출 시도 (YYYYMMDDHHMMSS -> YYMMDD)
            $org_tran_date = substr($payment['pk_app_date'], 2, 6);
        }

        // orgRefNo: 거래번호 (12자리) - pk_tid에 저장됨
        $org_ref_no = $stn_data['refNo'] ?? $payment['pk_tid'];

        // payType: 결제타입
        $pay_type = $stn_data['payType'] ?? '';

        $cancel_request = [
            'mbrNo' => $mbr_no,                      // 가맹점 번호
            'mbrRefNo' => $payment['pk_order_no'],   // 가맹점 주문번호
            'orgRefNo' => $org_ref_no,               // 원거래번호
            'orgTranDate' => $org_tran_date,         // 원거래 승인일자 (6자리)
            'payType' => $pay_type,                  // 결제타입
            'paymethod' => 'CARD',                   // 지불수단
            'amount' => (string)$payment['pk_amount'] // 원거래 금액 (전체)
        ];

        // 디버그 로그: 취소 요청 데이터 확인
        writeApiLog('stn', 'cancel_debug', 'CANCEL_REQUEST', $cancel_request);
    } else if($pg_code === 'winglobal') {
        // 윈글로벌 취소 요청 데이터
        // 결제 응답에서 저장된 trxId(pk_tid), 주문번호, 승인일자 필요
        $response_data = json_decode($payment['pk_response_data'], true);

        // 윈글로벌 원본 데이터에서 trxId 추출
        $winglobal_data = $response_data['_winglobal_data'] ?? ($response_data['_original']['pay'] ?? []);
        $root_trx_id = $winglobal_data['trxId'] ?? $payment['pk_tid'];

        // 원거래일 (YYYYMMDD)
        // pk_app_date가 YYYYMMDDHHMMSS 형식일 경우 앞 8자리만 사용
        $root_trx_day = '';
        if(!empty($payment['pk_app_date'])) {
            $root_trx_day = substr(preg_replace('/[^0-9]/', '', $payment['pk_app_date']), 0, 8);
        }

        // 취소 주문번호 생성 (원주문번호 + _C + 타임스탬프)
        $cancel_track_id = $payment['pk_order_no'] . '_C' . date('His');

        $cancel_request = [
            'refund' => [
                'trxType' => 'ONTR',                              // 고정값
                'trackId' => $cancel_track_id,                     // 취소 주문번호
                'amount' => (int)$cancel_amount,                   // 취소금액
                'rootTrxId' => $root_trx_id,                       // 원거래 윈글로벌 거래번호
                'rootTrackId' => $payment['pk_order_no'],          // 원거래 가맹점 주문번호
                'rootTrxDay' => $root_trx_day,                     // 원거래일 (YYYYMMDD)
                'udf1' => '',
                'udf2' => ''
            ]
        ];

        // 디버그 로그
        writeApiLog('winglobal', 'cancel_debug', 'CANCEL_REQUEST', $cancel_request);
    } else {
        // 페이시스 취소 요청 데이터
        $cancel_request = [
            'ordNo' => $payment['pk_order_no'],
            'mid' => $mid,
            'canNm' => $cancel_name,
            'canMsg' => $cancel_reason,
            'canAmt' => (string)$cancel_amount
        ];
    }

    // PG사별 취소 API 호출
    $response = null;
    switch($pg_code) {
        case 'paysis':
            $response = callPaysisCancelAPI($api_key, $cancel_request);
            break;
        case 'rootup':
            $response = callRoutupCancelAPI($api_key, $cancel_request);
            break;
        case 'stn':
            $response = callStnCancelAPI($api_key, $cancel_request);
            break;
        case 'winglobal':
            $response = callWinglobalRefundAPI($api_key, $cancel_request);
            break;
        default:
            echo json_encode(['success' => false, 'message' => '지원하지 않는 PG사입니다: ' . $pg_code]);
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
 * 루트업 결제 API 호출
 * URL: https://api.routeup.kr/api/v2/pay/hand
 * Authorization: ${pay_key}
 */
function callRoutupPaymentAPI($pay_key, $data) {
    $url = 'https://api.routeup.kr/api/v2/pay/hand';
    $pg_code = 'rootup';
    $action = 'pay';

    // 카드번호는 로그에 남기지 않도록 별도 처리
    $log_data = $data;
    if(isset($log_data['card_num'])) {
        $log_data['card_num'] = maskCardNumber($log_data['card_num']);
    }
    if(isset($log_data['card_pw'])) {
        $log_data['card_pw'] = '**';
    }
    if(isset($log_data['auth_num'])) {
        $log_data['auth_num'] = '******';
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

    // 루트업 응답을 공통 포맷으로 변환
    // 루트업 응답: result_cd, result_msg, app_num, app_date 등
    $normalized = normalizeRoutupResponse($result);

    // 응답 로그
    writeApiLog($pg_code, $action, 'RESPONSE', $result);

    return $normalized;
}

/**
 * 루트업 취소 API 호출
 * URL: https://api.routeup.kr/api/v2/pay/cancel
 */
function callRoutupCancelAPI($pay_key, $data) {
    $url = 'https://api.routeup.kr/api/v2/pay/cancel';
    $pg_code = 'rootup';
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

    // 루트업 응답을 공통 포맷으로 변환
    $normalized = normalizeRoutupResponse($result);

    // 응답 로그
    writeApiLog($pg_code, $action, 'RESPONSE', $result);

    return $normalized;
}

/**
 * 루트업 응답을 공통 포맷으로 변환
 * 루트업 응답 필드 → 공통 필드 매핑
 *
 * 결제 응답 필드: result_cd, result_msg, trx_id, appr_num, trx_dttm, issuer, acquirer 등
 * 취소 응답 필드: result_cd, result_msg, trx_id, ori_trx_id, cxl_dttm 등
 */
function normalizeRoutupResponse($response) {
    $result_cd = $response['result_cd'] ?? '';
    $is_success = ($result_cd === '0000');

    // 발급사/매입사: 응답에서 직접 이름 제공됨 (issuer, acquirer)
    // 코드도 제공됨 (issuer_code, acquirer_code)
    $issuer_name = $response['issuer'] ?? '';
    $acquirer_name = $response['acquirer'] ?? '';

    return [
        'resCode' => $is_success ? '0000' : ($result_cd ?: 'UNKNOWN'),
        'resMsg' => $response['result_msg'] ?? '',
        'appNo' => $response['appr_num'] ?? '',                  // 승인번호
        'appDate' => $response['trx_dttm'] ?? '',                // 거래일시
        'tid' => $response['trx_id'] ?? '',                      // 거래번호 (취소시 필요)
        'vanIssCpCd' => $issuer_name,                            // 발급사명
        'vanCpCd' => $acquirer_name,                             // 매입사명
        'cancelDate' => $response['cxl_dttm'] ?? '',             // 취소일시
        'cancelTime' => '',
        '_original' => $response  // 원본 응답 보존
    ];
}

/**
 * 주문번호 생성
 * - 페이시스: 정확히 30자 (OID-YYYYMMDD-HHMMSS-RRRRRRR)
 * - 섹타나인: 정확히 20자 (XXXXYYYYMMDDHHMMSSRR)
 * - 기타: 19자 (OID-YYMM-HHMM-SSRR)
 */
function generateOrderNumber($merchant_oid, $pg_code = 'paysis') {
    // OID가 없으면 랜덤 4자리 생성 (기존 데이터 호환용)
    if(!$merchant_oid) {
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $alphanumeric = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $merchant_oid = $letters[rand(0, 25)] . $alphanumeric[rand(0, 35)] . $alphanumeric[rand(0, 35)] . $alphanumeric[rand(0, 35)];
    }
    $oid = $merchant_oid;

    if($pg_code === 'paysis') {
        // 페이시스: 정확히 30자 (하이픈 없음)
        // 형식: XXXXYYYYMMDDHHMMSSRRRRRRRRRR (4+8+6+12 = 30자)
        $date = date('Ymd');      // 8자리
        $time = date('His');      // 6자리
        $rand = strtoupper(substr(md5(microtime(true) . mt_rand()), 0, 12)); // 12자리
        return "{$oid}{$date}{$time}{$rand}";
    } else if($pg_code === 'stn') {
        // 섹타나인: 정확히 20자 (하이픈 없음)
        // 형식: XXXXYYYYMMDDHHMMSSRR (4+8+6+2 = 20자)
        $date = date('Ymd');      // 8자리
        $time = date('His');      // 6자리
        $rand = strtoupper(substr(md5(microtime(true) . mt_rand()), 0, 2)); // 2자리
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

/**
 * 섹타나인 결제 API 호출
 * URL: https://relay.mainpay.co.kr/v1/api/payments/payment/card-keyin/trans
 * Content-Type: application/x-www-form-urlencoded
 */
function callStnPaymentAPI($data) {
    $url = 'https://relay.mainpay.co.kr/v1/api/payments/payment/card-keyin/trans';
    $pg_code = 'stn';
    $action = 'pay';

    // 카드번호는 로그에 남기지 않도록 별도 처리
    $log_data = $data;
    if(isset($log_data['cardNo'])) {
        $log_data['cardNo'] = maskCardNumber($log_data['cardNo']);
    }
    if(isset($log_data['passwd'])) {
        $log_data['passwd'] = '**';
    }
    if(isset($log_data['regNo'])) {
        $log_data['regNo'] = '******';
    }
    // signature는 민감정보는 아니지만 길어서 일부만 로그
    if(isset($log_data['signature'])) {
        $log_data['signature'] = substr($log_data['signature'], 0, 16) . '...';
    }

    // 요청 로그
    writeApiLog($pg_code, $action, 'REQUEST', $log_data);

    // application/x-www-form-urlencoded 형식으로 변환
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

    // 섹타나인 응답을 공통 포맷으로 변환
    $normalized = normalizeStnResponse($result);

    return $normalized;
}

/**
 * 섹타나인 취소 API 호출
 * HOST: https://relay.mainpay.co.kr
 * POST: /v1/api/payments/payment/cancel
 */
function callStnCancelAPI($api_key, $data) {
    $url = 'https://relay.mainpay.co.kr/v1/api/payments/payment/cancel';
    $pg_code = 'stn';
    $action = 'cancel';

    // timestamp 생성 (yyMMddHHmmssSSS)
    $timestamp = date('ymdHis') . substr(microtime(), 2, 3);

    // signature 생성: sha256(mbrNo|mbrRefNo|amount|apiKey|timestamp)
    $signature = hash('sha256', $data['mbrNo'] . '|' . $data['mbrRefNo'] . '|' . $data['amount'] . '|' . $api_key . '|' . $timestamp);

    // 취소 요청 데이터
    $cancel_data = [
        'mbrNo' => $data['mbrNo'],           // 가맹점 번호
        'mbrRefNo' => $data['mbrRefNo'],     // 가맹점 주문번호
        'orgRefNo' => $data['orgRefNo'],     // 원거래번호
        'orgTranDate' => $data['orgTranDate'], // 원거래 승인일자
        'payType' => $data['payType'],       // 결제타입
        'paymethod' => $data['paymethod'],   // 지불수단 (CARD)
        'amount' => $data['amount'],         // 원거래 금액
        'timestamp' => $timestamp,
        'signature' => $signature
    ];

    // 요청 로그
    $log_data = $cancel_data;
    if(isset($log_data['signature'])) {
        $log_data['signature'] = substr($log_data['signature'], 0, 16) . '...';
    }
    writeApiLog($pg_code, $action, 'REQUEST', $log_data);

    // application/x-www-form-urlencoded 형식으로 변환
    $post_data = http_build_query($cancel_data);

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

    // 섹타나인 응답을 공통 포맷으로 변환
    $normalized = normalizeStnResponse($result, true);

    return $normalized;
}

/**
 * 섹타나인 응답을 공통 포맷으로 변환
 *
 * 섹타나인 응답 구조:
 * - resultCode: '200' 이면 성공
 * - resultMessage: 응답 메시지
 * - data.refNo: 거래번호 (취소시 필요)
 * - data.tranDate: 거래일자 (취소시 필요)
 * - data.payType: 결제타입 (취소시 필요)
 * - data.applNo: 승인번호
 * - data.issueCompanyName: 카드 발급사명
 * - data.acqCompanyName: 카드 매입사명
 */
function normalizeStnResponse($response, $is_cancel = false) {
    $result_code = $response['resultCode'] ?? '';
    $is_success = ($result_code === '200');

    $data = $response['data'] ?? [];

    // 거래일시 조합 (tranDate + tranTime)
    $app_date = '';
    if(!empty($data['tranDate']) && !empty($data['tranTime'])) {
        $app_date = $data['tranDate'] . $data['tranTime'];
    }

    return [
        'resCode' => $is_success ? '0000' : ($result_code ?: 'UNKNOWN'),
        'resMsg' => $response['resultMessage'] ?? '',
        'appNo' => $data['applNo'] ?? '',                           // 승인번호
        'appDate' => $app_date,                                      // 거래일시
        'tid' => $data['refNo'] ?? '',                              // 거래번호 (취소시 필요)
        'vanIssCpCd' => $data['issueCompanyName'] ?? '',            // 발급사명
        'vanCpCd' => $data['acqCompanyName'] ?? '',                 // 매입사명
        'cancelDate' => $is_cancel ? $app_date : '',                // 취소일시
        'cancelTime' => '',
        '_stn_data' => $data,                                        // 섹타나인 원본 data (취소시 필요한 정보 포함)
        '_original' => $response                                     // 원본 응답 보존
    ];
}

/**
 * 윈글로벌 결제 API 호출
 * URL: https://api.winglobalpay.com/api/pay
 * Authorization: Pay Key (api key)
 * Content-Type: application/json
 */
function callWinglobalPaymentAPI($pay_key, $data) {
    $url = 'https://api.winglobalpay.com/api/pay';
    $pg_code = 'winglobal';
    $action = 'pay';

    // 카드번호는 로그에 남기지 않도록 별도 처리
    $log_data = $data;
    if(isset($log_data['pay']['card']['number'])) {
        $log_data['pay']['card']['number'] = maskCardNumber($log_data['pay']['card']['number']);
    }
    if(isset($log_data['pay']['metadata']['authPw'])) {
        $log_data['pay']['metadata']['authPw'] = '**';
    }
    if(isset($log_data['pay']['metadata']['authDob'])) {
        $log_data['pay']['metadata']['authDob'] = '******';
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

    // 윈글로벌 응답을 공통 포맷으로 변환
    $normalized = normalizeWinglobalResponse($result);

    return $normalized;
}

/**
 * 윈글로벌 응답을 공통 포맷으로 변환
 *
 * 윈글로벌 응답 구조:
 * - result.resultCd: '0000' 이면 성공
 * - result.resultMsg: 응답 메시지
 * - result.advanceMsg: 상세 메시지
 * - result.create: 생성일시 (YYYYMMDDHHmmss)
 * - pay.authCd: 승인번호
 * - pay.trxId: 거래번호 (취소시 필요)
 * - pay.card.issuer: 카드 매입사
 * - pay.card.cardType: 신용/체크/기타
 * - pay.card.last4: 카드번호 뒤 4자리
 */
function normalizeWinglobalResponse($response, $is_cancel = false) {
    $result = $response['result'] ?? [];
    $pay = $response['pay'] ?? [];
    $card = $pay['card'] ?? [];

    $result_cd = $result['resultCd'] ?? '';
    $is_success = ($result_cd === '0000');

    // 응답 메시지 조합
    $res_msg = $result['resultMsg'] ?? '';
    if(!empty($result['advanceMsg']) && $result['advanceMsg'] !== $res_msg) {
        $res_msg .= ' - ' . $result['advanceMsg'];
    }

    return [
        'resCode' => $is_success ? '0000' : ($result_cd ?: 'UNKNOWN'),
        'resMsg' => $res_msg,
        'appNo' => $pay['authCd'] ?? '',                             // 승인번호
        'appDate' => $result['create'] ?? '',                        // 거래일시 (YYYYMMDDHHmmss)
        'tid' => $pay['trxId'] ?? '',                                // 거래번호 (취소시 필요)
        'vanIssCpCd' => $card['issuer'] ?? '',                       // 카드 매입사
        'vanCpCd' => $card['cardType'] ?? '',                        // 카드 타입 (신용/체크)
        'cancelDate' => $is_cancel ? ($result['create'] ?? '') : '', // 취소일시
        'cancelTime' => '',
        '_winglobal_data' => $pay,                                   // 윈글로벌 원본 pay 데이터
        '_original' => $response                                     // 원본 응답 보존
    ];
}

/**
 * 윈글로벌 취소(Refund) API 호출
 *
 * API URL: https://api.winglobalpay.com/api/refund
 * 인증: HTTP Header Authorization: Pay Key
 */
function callWinglobalRefundAPI($pay_key, $data) {
    $url = 'https://api.winglobalpay.com/api/refund';
    $pg_code = 'winglobal';
    $action = 'refund';

    // 요청 헤더
    $headers = [
        'Content-Type: application/json',
        'Authorization: ' . $pay_key
    ];

    // 로그용 데이터 (민감 정보 마스킹 없음 - 취소 요청에는 카드정보 없음)
    $log_data = $data;
    writeApiLog($pg_code, $action, 'REQUEST', $log_data);

    // cURL 요청
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // 응답 처리
    if($curl_error) {
        $error_response = [
            'resCode' => 'CURL_ERROR',
            'resMsg' => 'cURL 오류: ' . $curl_error,
            'cancelDate' => '',
            'cancelTime' => ''
        ];
        writeApiLog($pg_code, $action, 'ERROR', ['curl_error' => $curl_error, 'http_code' => $http_code]);
        return $error_response;
    }

    $result = json_decode($response, true);
    if($result === null) {
        $error_response = [
            'resCode' => 'JSON_ERROR',
            'resMsg' => 'JSON 파싱 오류',
            'cancelDate' => '',
            'cancelTime' => ''
        ];
        writeApiLog($pg_code, $action, 'ERROR', ['raw_response' => $response, 'http_code' => $http_code]);
        return $error_response;
    }

    // 응답 로깅
    writeApiLog($pg_code, $action, 'RESPONSE', $result);

    // 윈글로벌 취소 응답을 공통 포맷으로 변환
    $normalized = normalizeWinglobalRefundResponse($result);

    return $normalized;
}

/**
 * 윈글로벌 취소(Refund) 응답을 공통 포맷으로 변환
 *
 * 윈글로벌 취소 응답 구조:
 * - result.resultCd: '0000' 이면 성공
 * - result.resultMsg: 응답 메시지
 * - result.advanceMsg: 상세 메시지
 * - result.create: 취소일시 (YYYYMMDDHHmmss)
 * - refund.authCd: 취소 승인번호
 * - refund.trxId: 취소 거래번호
 */
function normalizeWinglobalRefundResponse($response) {
    $result = $response['result'] ?? [];
    $refund = $response['refund'] ?? [];

    $result_cd = $result['resultCd'] ?? '';
    $is_success = ($result_cd === '0000');

    // 응답 메시지 조합
    $res_msg = $result['resultMsg'] ?? '';
    if(!empty($result['advanceMsg']) && $result['advanceMsg'] !== $res_msg) {
        $res_msg .= ' - ' . $result['advanceMsg'];
    }

    // 취소일시 분리 (YYYYMMDDHHmmss -> YYYYMMDD, HHmmss)
    $create = $result['create'] ?? '';
    $cancel_date = strlen($create) >= 8 ? substr($create, 0, 8) : '';
    $cancel_time = strlen($create) >= 14 ? substr($create, 8, 6) : '';

    return [
        'resCode' => $is_success ? '0000' : ($result_cd ?: 'UNKNOWN'),
        'resMsg' => $res_msg,
        'appNo' => $refund['authCd'] ?? '',                          // 취소 승인번호
        'appDate' => $create,                                        // 취소일시
        'tid' => $refund['trxId'] ?? '',                             // 취소 거래번호
        'cancelDate' => $cancel_date,                                // 취소일 (YYYYMMDD)
        'cancelTime' => $cancel_time,                                // 취소시간 (HHmmss)
        '_winglobal_refund_data' => $refund,                         // 윈글로벌 원본 refund 데이터
        '_original' => $response                                     // 원본 응답 보존
    ];
}

/**
 * 에러 로그 기록
 * 저장 경로: /logs/errorlog/YYYY-MM-DD.log
 */
function writeErrorLog($action, $message, $data = []) {
    $log_dir = __DIR__ . '/logs/errorlog';

    // 디렉토리 생성 (에러 억제 추가)
    if(!is_dir($log_dir)) {
        if(!@mkdir($log_dir, 0755, true)) {
            // mkdir 실패 시 error_log로 대체 기록
            error_log("[ERRORLOG_MKDIR_FAIL] {$action}: {$message} | " . json_encode($data, JSON_UNESCAPED_UNICODE));
            return false;
        }
    }

    $log_file = $log_dir . '/' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s.') . sprintf('%03d', (microtime(true) - floor(microtime(true))) * 1000);

    $log_entry = "[{$timestamp}] [{$action}] {$message}";
    if(!empty($data)) {
        $log_entry .= " | " . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    $log_entry .= "\n";

    // 파일 쓰기 (에러 억제 추가)
    $result = @file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    if($result === false) {
        // file_put_contents 실패 시 error_log로 대체 기록
        error_log("[ERRORLOG_WRITE_FAIL] {$action}: {$message} | " . json_encode($data, JSON_UNESCAPED_UNICODE));
        return false;
    }

    return true;
}
