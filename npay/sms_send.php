<?php
include_once('./_common.php');

if (!defined('_GNUBOARD_')) exit;

// 로그인 체크
if (!$is_member) {
    ajax_error('로그인이 필요합니다.');
}

// 입력 데이터
$mb_id = $member['mb_id']; // 현재 로그인 회원
$receiver = trim($_POST['phone']);
$message = trim($_POST['message']);
$subject = ''; // LMS일 경우 제목, 필요 시 확장 가능
$type = (mb_strlen($message, 'UTF-8') > 80) ? 'LMS' : 'SMS'; // 메시지 타입 결정

// 유효성 검사
if (!$receiver || !$message) {
    echo json_encode(['success' => false, 'message' => '수신번호 또는 메시지가 비어있습니다.']);
    exit;
}

$sql_insert = "
    INSERT INTO g5_sms_messages 
    (mb_id, sm_receiver, sm_message, sm_type, sm_subject, sm_status, sm_reg_dt)
    VALUES 
    ('$mb_id', '$receiver', '$message', '$type', '$subject', 'pending', NOW())
";
sql_query($sql_insert);
$sm_id = sql_insert_id(); // 방금 삽입된 메시지의 ID

// 2️알리고 API 설정
$api_url = 'https://apis.aligo.in/send/';
$api_key = '65v1dy9tfe5etneyp4iym3bexvwuhya3';
$user_id = 'ryujaemin';
$sender = '010-9989-5231'; // 사전 등록된 번호

$params = [
    'key' => $api_key,
    'user_id' => $user_id,
    'sender' => $sender,
    'receiver' => $receiver,
    'msg' => $message,
    'msg_type' => $type,
    'title' => $subject,
    'testmode_yn' => 'N'
];

// 3️CURL 전송
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
$response = curl_exec($ch);
curl_close($ch);

$response_data = json_decode($response, true);

$status = 'failed';
$msg_id = '';
$result_msg = '';

if (isset($response_data['result_code']) && $response_data['result_code'] == '1') {
    $status = 'success';
    $msg_id = $response_data['msg_id'] ?? '';
    $result_msg = '전송 성공';
} else {
    $result_msg = $response_data['message'] ?? 'API 오류';
}

// 4️DB 업데이트
$sql_update = "
    UPDATE g5_sms_messages 
    SET 
        sm_status = '$status',
        sm_msg_id = '$msg_id',
        sm_response = '" . sql_real_escape_string($response) . "',
        sm_send_time = NOW()
    WHERE sm_id = '$sm_id'
";
sql_query($sql_update);

// 5️응답
echo json_encode([
    'success' => ($status === 'success'),
    'message' => $result_msg,
    'data' => [
        'msg_id' => $msg_id,
        'sm_id' => $sm_id
    ]
]);
?>