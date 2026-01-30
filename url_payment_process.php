<?php
include_once('./_common.php');

$action = isset($_POST['action']) ? $_POST['action'] : '';

// AJAX 요청인 경우 JSON 헤더 설정 및 출력 버퍼 정리
if($action && in_array($action, ['get_keyin', 'cancel', 'create_ajax'])) {
    // 출력 버퍼 정리 (이전에 출력된 내용 제거)
    while(ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
}

// 관리자만 접근 가능
if(!$is_admin) {
    if($action) {
        echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    } else {
        alert("관리자만 접근할 수 있습니다.");
    }
    exit;
}

/**
 * URL 코드 생성 함수 (9자리 영숫자)
 */
function generate_url_code() {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $max_attempts = 100;

    for($i = 0; $i < $max_attempts; $i++) {
        $code = '';
        for($j = 0; $j < 9; $j++) {
            $code .= $characters[random_int(0, 61)];
        }

        // 중복 체크
        $check = sql_fetch("SELECT up_id FROM g5_url_payment WHERE up_code = '".sql_real_escape_string($code)."'");
        if(!$check['up_id']) {
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
    // SMS API 설정
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
    $ret = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($ret, true);

    return array(
        'success' => (isset($result['result_code']) && intval($result['result_code']) > 0),
        'message' => $result['message'] ?? 'API 오류',
        'msg_id' => $result['msg_id'] ?? '',
        'response' => $ret
    );
}

// ========== AJAX 요청 처리 ==========

// Keyin 설정 조회
if($action == 'get_keyin') {
    $mb_id = sql_real_escape_string($_POST['mb_id']);

    $sql = "SELECT mkc.mkc_id, mkc.mkc_pg_code, mkc.mkc_pg_name,
                   COALESCE(mkc.mkc_type, mpc.mpc_type) as type,
                   COALESCE(mkc.mkc_pg_name, mpc.mpc_pg_name) as pg_name
            FROM g5_member_keyin_config mkc
            LEFT JOIN g5_manual_payment_config mpc ON mkc.mpc_id = mpc.mpc_id
            WHERE mkc.mb_id = '{$mb_id}'
              AND mkc.mkc_status = 'active'
              AND mkc.mkc_use = 'Y'
            ORDER BY mkc.mkc_id";

    $result = sql_query($sql);
    $data = array();

    while($row = sql_fetch_array($result)) {
        $data[] = array(
            'mkc_id' => $row['mkc_id'],
            'pg_name' => $row['pg_name'],
            'type' => $row['type']
        );
    }

    echo json_encode(array('success' => true, 'data' => $data));
    exit;
}

// URL결제 취소
if($action == 'cancel') {
    $up_id = intval($_POST['up_id']);

    $url_pay = sql_fetch("SELECT * FROM g5_url_payment WHERE up_id = '{$up_id}'");
    if(!$url_pay) {
        echo json_encode(array('success' => false, 'message' => '존재하지 않는 URL결제입니다.'));
        exit;
    }

    if($url_pay['up_status'] != 'active') {
        echo json_encode(array('success' => false, 'message' => '활성 상태의 URL만 취소할 수 있습니다.'));
        exit;
    }

    sql_query("UPDATE g5_url_payment SET up_status = 'cancelled', up_updated_at = NOW() WHERE up_id = '{$up_id}'");

    echo json_encode(array('success' => true, 'message' => '취소되었습니다.'));
    exit;
}

// ========== URL결제 생성 (POST 폼) ==========
if($action == 'create') {
    $mb_id = sql_real_escape_string($_POST['mb_id']);
    $mkc_id = intval($_POST['mkc_id']);
    $goods_name = sql_real_escape_string($_POST['goods_name']);
    $goods_desc = sql_real_escape_string($_POST['goods_desc']);
    $amount = intval(str_replace(',', '', $_POST['amount']));
    $seller_name = sql_real_escape_string($_POST['seller_name']);
    $seller_phone = sql_real_escape_string($_POST['seller_phone']);
    $buyer_name = sql_real_escape_string($_POST['buyer_name']);
    $buyer_phone = sql_real_escape_string($_POST['buyer_phone']);
    $expire_date = preg_replace('/[^0-9]/', '', $_POST['expire_date']);
    $expire_time = $_POST['expire_time'];
    $memo = sql_real_escape_string($_POST['memo']);
    $send_sms = isset($_POST['send_sms']) && $_POST['send_sms'] == 'Y';

    // 유효성 검사
    if(!$mb_id || !$mkc_id || !$goods_name || $amount <= 0) {
        alert("필수 항목을 모두 입력해주세요.");
        exit;
    }

    // 가맹점 정보 조회
    $merchant = sql_fetch("SELECT * FROM g5_member WHERE mb_id = '{$mb_id}'");
    if(!$merchant) {
        alert("존재하지 않는 가맹점입니다.");
        exit;
    }

    // Keyin 설정 확인
    $keyin = sql_fetch("SELECT * FROM g5_member_keyin_config WHERE mkc_id = '{$mkc_id}' AND mb_id = '{$mb_id}'");
    if(!$keyin) {
        alert("유효하지 않은 수기결제 설정입니다.");
        exit;
    }

    // 유효기간 변환
    $expire_datetime = substr($expire_date, 0, 4).'-'.substr($expire_date, 4, 2).'-'.substr($expire_date, 6, 2).' '.$expire_time.':59';

    // URL 코드 생성
    $url_code = generate_url_code();
    if(!$url_code) {
        alert("URL 코드 생성에 실패했습니다. 다시 시도해주세요.");
        exit;
    }

    // INSERT
    $sql = "INSERT INTO g5_url_payment (
                up_code, mb_id, mkc_id, up_amount, up_goods_name, up_goods_desc,
                up_buyer_name, up_buyer_phone, up_seller_name, up_seller_phone,
                up_expire_datetime, up_memo,
                up_mb_1, up_mb_2, up_mb_3, up_mb_4, up_mb_5, up_mb_6, up_mb_6_name,
                up_operator_id, up_created_at
            ) VALUES (
                '{$url_code}', '{$mb_id}', '{$mkc_id}', '{$amount}', '{$goods_name}', '{$goods_desc}',
                '{$buyer_name}', '{$buyer_phone}', '{$seller_name}', '{$seller_phone}',
                '{$expire_datetime}', '{$memo}',
                '{$merchant['mb_1']}', '{$merchant['mb_2']}', '{$merchant['mb_3']}',
                '{$merchant['mb_4']}', '{$merchant['mb_5']}', '{$merchant['mb_6']}', '{$merchant['mb_nick']}',
                '{$member['mb_id']}', NOW()
            )";

    sql_query($sql);
    $up_id = sql_insert_id();

    // SMS 발송
    $sms_msg = '';
    if($send_sms && $buyer_phone) {
        $url = "https://".$_SERVER['HTTP_HOST']."/pay/".$url_code;
        $expire_text = date('Y-m-d H:i', strtotime($expire_datetime));

        // 80자 이내 SMS
        $message = "{$seller_name} ".number_format($amount)."원\n";
        $message .= "{$url}";

        $sms_result = send_url_payment_sms($buyer_phone, $message);

        if($sms_result['success']) {
            sql_query("UPDATE g5_url_payment SET up_sms_sent = 'Y', up_sms_sent_datetime = NOW(), up_sms_count = 1 WHERE up_id = '{$up_id}'");
            $sms_msg = " SMS가 발송되었습니다.";
        } else {
            $sms_msg = " (SMS 발송 실패: ".$sms_result['message'].")";
        }
    }

    alert("URL결제가 생성되었습니다.".$sms_msg, "./?p=url_payment");
    exit;
}

// 잘못된 접근
alert("잘못된 접근입니다.");
exit;
?>
