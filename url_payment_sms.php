<?php
include_once('./_common.php');

// 관리자만 접근 가능
if(!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$up_id = intval($_POST['up_id']);

if(!$up_id) {
    echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
    exit;
}

// URL결제 정보 조회
$url_pay = sql_fetch("SELECT * FROM g5_url_payment WHERE up_id = '{$up_id}'");

if(!$url_pay) {
    echo json_encode(['success' => false, 'message' => '존재하지 않는 URL결제입니다.']);
    exit;
}

if($url_pay['up_status'] != 'active') {
    echo json_encode(['success' => false, 'message' => '활성 상태의 URL만 SMS 발송이 가능합니다.']);
    exit;
}

if(!$url_pay['up_buyer_phone']) {
    echo json_encode(['success' => false, 'message' => '구매자 연락처가 없습니다.']);
    exit;
}

// SMS 메시지 생성
$url = "https://".$_SERVER['HTTP_HOST']."/pay/".$url_pay['up_code'];
$expire_text = date('Y-m-d H:i', strtotime($url_pay['up_expire_datetime']));

// 80자 이내 SMS
$message = "{$url_pay['up_seller_name']} ".number_format($url_pay['up_amount'])."원\n";
$message .= "{$url}";

// SMS API 설정
$sms_url = "https://apis.aligo.in/send/";
$sms = array(
    'user_id' => 'wspay',
    'key' => 'v5smv1ajl0s4xrx1e9db2luomlycrkqz',
    'sender' => '01073651990',
    'receiver' => preg_replace('/[^0-9]/', '', $url_pay['up_buyer_phone']),
    'msg' => $message,
    'msg_type' => (mb_strlen($message, 'UTF-8') > 80) ? 'LMS' : 'SMS',
    'testmode_yn' => 'N'
);

// SMS 발송
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $sms_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $sms);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
$ret = curl_exec($ch);
curl_close($ch);

$result = json_decode($ret, true);

if(isset($result['result_code']) && $result['result_code'] == '1') {
    // 발송 성공 - DB 업데이트
    $sms_count = $url_pay['up_sms_count'] + 1;
    sql_query("UPDATE g5_url_payment SET
                up_sms_sent = 'Y',
                up_sms_sent_datetime = NOW(),
                up_sms_count = '{$sms_count}'
               WHERE up_id = '{$up_id}'");

    echo json_encode([
        'success' => true,
        'message' => 'SMS가 발송되었습니다.',
        'data' => [
            'msg_id' => $result['msg_id'] ?? '',
            'sms_count' => $sms_count
        ]
    ]);
} else {
    // 발송 실패
    echo json_encode([
        'success' => false,
        'message' => $result['message'] ?? 'SMS 발송에 실패했습니다.'
    ]);
}
exit;
?>
