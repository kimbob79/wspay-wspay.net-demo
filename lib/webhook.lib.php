<?php
/**
 * 웹훅 시스템 라이브러리
 *
 * 결제(승인/취소) 발생 시 가맹점에 노티 발송
 * - PG별 데이터를 통합 JSON 형식으로 정규화
 * - 재시도 로직 포함
 * - 이력 저장
 */

if (!defined('_GNUBOARD_')) exit;

/**
 * 웹훅 발송 메인 함수 (하이브리드 방식)
 *
 * - 즉시 1회만 시도 (타임아웃 3초)
 * - 실패 시 pending 상태로 이력 저장
 * - 재시도는 크론(cron/webhook_retry.php)이 처리
 *
 * @param string $mb_id 가맹점 아이디
 * @param string $pg_name PG사 이름 (paysis, korpay, stn 등)
 * @param array $pg_data PG에서 받은 원본 데이터
 * @param array $device_data 디바이스 정보 (g5_device)
 * @param array $payment_data 결제 정보 (pay_id, pay_type 등)
 * @return bool
 */
function webhook_send_notification($mb_id, $pg_name, $pg_data, $device_data, $payment_data) {
    try {
        // 1. 웹훅 설정 조회
        $config = webhook_get_config($mb_id);
        if (!$config || $config['wh_status'] != 'active') {
            return true; // 설정 없거나 비활성 - 정상 리턴
        }

        // 2. 이벤트 타입 결정
        $event_type = webhook_get_event_type($payment_data['pay_type']);

        // 3. 이벤트 필터링 체크
        $events = explode(',', $config['wh_events']);
        $events = array_map('trim', $events);
        if (!in_array($event_type, $events)) {
            return true; // 구독하지 않은 이벤트 - 스킵
        }

        // 4. 데이터 정규화 (통합 JSON 형식)
        $payload = webhook_normalize_data($pg_name, $pg_data, $device_data, $payment_data);
        $payload_json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // 5. 이벤트 ID 생성
        $event_id = webhook_generate_event_id();

        // 6. HTTP 요청 - 즉시 1회만 시도 (타임아웃 3초)
        $result = webhook_http_post_once($config['wh_url'], $payload_json, [
            'timeout' => 3, // 즉시 발송은 3초 타임아웃
            'event_type' => $event_type
        ]);

        // 7. 이력 저장
        // 성공: success, 실패: pending (크론이 재시도)
        $status = $result['success'] ? 'success' : 'pending';

        webhook_save_history([
            'wh_id' => $config['wh_id'],
            'mb_id' => $mb_id,
            'pay_id' => $payment_data['pay_id'] ?? null,
            'event_type' => $event_type,
            'event_id' => $event_id,
            'url' => $config['wh_url'],
            'payload' => $payload_json,
            'http_status' => $result['http_status'],
            'response_body' => $result['response_body'],
            'response_time' => $result['response_time'],
            'retry_count' => 0,
            'max_retry_count' => $config['wh_retry_count'],
            'status' => $status,
            'error_message' => $result['error'] ?? null
        ]);

        // 8. 설정 통계 업데이트 (즉시 발송 성공 시만)
        if ($result['success']) {
            webhook_update_stats($config['wh_id'], true);
        }

        return $result['success'];

    } catch (Exception $e) {
        webhook_log_error("Webhook error for mb_id={$mb_id}: " . $e->getMessage());
        return false;
    }
}

/**
 * 웹훅 설정 조회
 */
function webhook_get_config($mb_id) {
    $mb_id = sql_escape_string($mb_id);
    $sql = "SELECT * FROM g5_member_webhook WHERE mb_id = '{$mb_id}' AND wh_status = 'active'";
    return sql_fetch($sql);
}

/**
 * 이벤트 타입 결정
 */
function webhook_get_event_type($pay_type) {
    switch ($pay_type) {
        case 'N':
            return 'cancel';
        case 'B':
            return 'partial_cancel';
        case 'Y':
        default:
            return 'approval';
    }
}

/**
 * 이벤트 ID 생성
 */
function webhook_generate_event_id($prefix = 'evt') {
    return $prefix . '_' . date('YmdHis') . '_' . substr(md5(uniqid(mt_rand(), true)), 0, 8);
}

/**
 * 데이터 정규화 - PG 구분 없는 통합 JSON 형식
 */
function webhook_normalize_data($pg_name, $pg_data, $device_data, $payment_data) {
    $event_type = webhook_get_event_type($payment_data['pay_type']);

    // 기본 구조
    $payload = [
        'event' => $event_type == 'approval' ? 'payment.approved' : 'payment.cancelled',
        'version' => '1.0',
        'timestamp' => date('Y-m-d H:i:s'),
        'merchant' => [
            'mb_id' => $device_data['mb_6'] ?? '',
            'mb_name' => $device_data['mb_6_name'] ?? ''
        ],
        'transaction' => [],
        'card' => []
    ];

    // PG별 정규화 함수 호출
    $normalize_func = "webhook_normalize_{$pg_name}";
    if (function_exists($normalize_func)) {
        $payload = $normalize_func($payload, $pg_data, $device_data, $payment_data);
    } else {
        // 기본 정규화
        $payload = webhook_normalize_default($payload, $pg_data, $device_data, $payment_data);
    }

    return $payload;
}

/**
 * 기본 정규화 함수
 */
function webhook_normalize_default($payload, $pg_data, $device_data, $payment_data) {
    $amt = abs(intval($pg_data['amt'] ?? $pg_data['amount'] ?? 0));

    $payload['transaction'] = [
        'trx_id' => $pg_data['tid'] ?? '',
        'tid' => $device_data['dv_tid'] ?? '',
        'order_number' => $pg_data['ordNo'] ?? $pg_data['mbrRefNo'] ?? '',
        'amount' => $amt,
        'approval_number' => $pg_data['appNo'] ?? $pg_data['applNo'] ?? '',
        'approval_datetime' => webhook_format_datetime($pg_data['appDtm'] ?? $pg_data['tranDate'] ?? '') ?: '',
        'cancel_datetime' => isset($pg_data['ccDnt']) && !empty($pg_data['ccDnt']) ? webhook_format_datetime($pg_data['ccDnt']) : '',
        'installment' => $pg_data['quota'] ?? $pg_data['installNo'] ?? '00',
        'device_type' => ($device_data['dv_type'] ?? '') == '2' ? 'keyin' : 'terminal'
    ];

    $payload['card'] = [
        'card_name' => webhook_get_card_name($pg_data['appCardCd'] ?? $pg_data['issueCompanyNo'] ?? '', $pg_data['fnNm'] ?? ''),
        'card_number' => webhook_mask_card_number($pg_data['cardNo'] ?? '')
    ];

    return $payload;
}

/**
 * 페이시스 정규화
 */
function webhook_normalize_paysis($payload, $pg_data, $device_data, $payment_data) {
    return webhook_normalize_default($payload, $pg_data, $device_data, $payment_data);
}

/**
 * 페이시스 수기결제 정규화
 */
function webhook_normalize_paysis_keyin($payload, $pg_data, $device_data, $payment_data) {
    $payload = webhook_normalize_default($payload, $pg_data, $device_data, $payment_data);
    $payload['transaction']['device_type'] = 'keyin';
    return $payload;
}

/**
 * 코페이 정규화
 */
function webhook_normalize_korpay($payload, $pg_data, $device_data, $payment_data) {
    return webhook_normalize_default($payload, $pg_data, $device_data, $payment_data);
}

/**
 * 섹타나인 정규화
 */
function webhook_normalize_stn($payload, $pg_data, $device_data, $payment_data) {
    $amt = abs(intval($pg_data['amount'] ?? 0));

    $payload['transaction'] = [
        'trx_id' => $pg_data['refNo'] ?? '',
        'tid' => $device_data['dv_tid'] ?? '',
        'order_number' => $pg_data['mbrRefNo'] ?? '',
        'amount' => $amt,
        'approval_number' => $pg_data['applNo'] ?? '',
        'approval_datetime' => webhook_format_stn_datetime($pg_data['tranDate'] ?? '', $pg_data['tranTime'] ?? '') ?: '',
        'cancel_datetime' => '',
        'installment' => $pg_data['installNo'] ?? '00',
        'device_type' => ($device_data['dv_type'] ?? '') == '2' ? 'keyin' : 'terminal'
    ];

    $payload['card'] = [
        'card_name' => webhook_get_card_name_stn($pg_data['issueCompanyNo'] ?? ''),
        'card_number' => webhook_mask_card_number($pg_data['cardNo'] ?? '')
    ];

    return $payload;
}

/**
 * 섹타나인 K타입 정규화
 */
function webhook_normalize_stn_k($payload, $pg_data, $device_data, $payment_data) {
    $payload = webhook_normalize_stn($payload, $pg_data, $device_data, $payment_data);
    $payload['transaction']['device_type'] = 'keyin';
    return $payload;
}

/**
 * 루트업 정규화
 */
function webhook_normalize_routeup($payload, $pg_data, $device_data, $payment_data) {
    return webhook_normalize_default($payload, $pg_data, $device_data, $payment_data);
}

/**
 * 다우 정규화
 */
function webhook_normalize_daou($payload, $pg_data, $device_data, $payment_data) {
    return webhook_normalize_default($payload, $pg_data, $device_data, $payment_data);
}

/**
 * 다날 정규화
 */
function webhook_normalize_danal($payload, $pg_data, $device_data, $payment_data) {
    return webhook_normalize_default($payload, $pg_data, $device_data, $payment_data);
}

/**
 * HTTP POST 요청 - 1회만 시도 (즉시 발송용)
 */
function webhook_http_post_once($url, $payload_json, $options = []) {
    $timeout = isset($options['timeout']) ? intval($options['timeout']) : 3;
    $event_type = $options['event_type'] ?? 'approval';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json; charset=utf-8',
        'X-Webhook-Event: ' . $event_type,
        'User-Agent: WsPay-Webhook/1.0'
    ]);

    $start_time = microtime(true);
    $response = curl_exec($ch);
    $response_time = round((microtime(true) - $start_time) * 1000); // ms

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    $curl_errno = curl_errno($ch);
    curl_close($ch);

    $is_timeout = ($curl_errno == CURLE_OPERATION_TIMEDOUT || $curl_errno == CURLE_OPERATION_TIMEOUTED);
    $success = ($response !== false && $http_code >= 200 && $http_code < 300);

    return [
        'success' => $success,
        'http_status' => $http_code,
        'response_body' => substr($response ?: '', 0, 5000),
        'response_time' => $response_time,
        'timeout' => $is_timeout,
        'error' => $success ? null : ($curl_error ?: "HTTP {$http_code}")
    ];
}

/**
 * HTTP POST 요청 - 크론 재시도용 (타임아웃 길게)
 */
function webhook_http_post_retry($url, $payload_json, $options = []) {
    $timeout = isset($options['timeout']) ? intval($options['timeout']) : 10;
    $event_type = $options['event_type'] ?? 'approval';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json; charset=utf-8',
        'X-Webhook-Event: ' . $event_type,
        'User-Agent: WsPay-Webhook/1.0'
    ]);

    $start_time = microtime(true);
    $response = curl_exec($ch);
    $response_time = round((microtime(true) - $start_time) * 1000);

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    $curl_errno = curl_errno($ch);
    curl_close($ch);

    $is_timeout = ($curl_errno == CURLE_OPERATION_TIMEDOUT || $curl_errno == CURLE_OPERATION_TIMEOUTED);
    $success = ($response !== false && $http_code >= 200 && $http_code < 300);

    return [
        'success' => $success,
        'http_status' => $http_code,
        'response_body' => substr($response ?: '', 0, 5000),
        'response_time' => $response_time,
        'timeout' => $is_timeout,
        'error' => $success ? null : ($curl_error ?: "HTTP {$http_code}")
    ];
}

/**
 * 재시도 대상 웹훅 조회 (크론용)
 */
function webhook_get_pending_list($limit = 100) {
    $limit = intval($limit);
    $sql = "SELECT h.*, w.wh_timeout, w.wh_retry_delay
        FROM g5_webhook_history h
        LEFT JOIN g5_member_webhook w ON h.wh_id = w.wh_id
        WHERE h.whh_status = 'pending'
        AND h.whh_retry_count < h.whh_max_retry_count
        AND w.wh_status = 'active'
        ORDER BY h.whh_sent_datetime ASC
        LIMIT {$limit}";

    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) {
        $list[] = $row;
    }
    return $list;
}

/**
 * 웹훅 재시도 처리 (크론에서 호출)
 */
function webhook_process_retry($history) {
    $whh_id = intval($history['whh_id']);
    $wh_id = intval($history['wh_id']);
    $timeout = intval($history['wh_timeout']) ?: 10;

    // HTTP 요청
    $result = webhook_http_post_retry($history['whh_url'], $history['whh_payload'], [
        'timeout' => $timeout,
        'event_type' => $history['whh_event_type']
    ]);

    $new_retry_count = intval($history['whh_retry_count']) + 1;
    $max_retry = intval($history['whh_max_retry_count']);

    // 상태 결정
    if ($result['success']) {
        $new_status = 'success';
    } else if ($new_retry_count >= $max_retry) {
        $new_status = $result['timeout'] ? 'timeout' : 'failed';
    } else {
        $new_status = 'pending'; // 계속 재시도
    }

    // 이력 업데이트
    $http_status = $result['http_status'] ? intval($result['http_status']) : 'NULL';
    $response_body = sql_escape_string($result['response_body'] ?? '');
    $response_time = $result['response_time'] ? intval($result['response_time']) : 'NULL';
    $error_message = $result['error'] ? sql_escape_string($result['error']) : '';

    $sql = "UPDATE g5_webhook_history SET
        whh_retry_count = {$new_retry_count},
        whh_status = '{$new_status}',
        whh_http_status = {$http_status},
        whh_response_body = '{$response_body}',
        whh_response_time = {$response_time},
        whh_error_message = '{$error_message}',
        whh_completed_datetime = NOW()
        WHERE whh_id = {$whh_id}";

    sql_query($sql);

    // 최종 성공/실패 시 통계 업데이트
    if ($new_status == 'success' || $new_status == 'failed' || $new_status == 'timeout') {
        webhook_update_stats($wh_id, $new_status == 'success');
    }

    return $result['success'];
}

/**
 * 웹훅 이력 저장
 */
function webhook_save_history($data) {
    $wh_id = intval($data['wh_id']);
    $mb_id = sql_escape_string($data['mb_id']);
    $pay_id = $data['pay_id'] ? intval($data['pay_id']) : 'NULL';
    $event_type = sql_escape_string($data['event_type']);
    $event_id = sql_escape_string($data['event_id']);
    $url = sql_escape_string($data['url']);
    $payload = sql_escape_string($data['payload']);
    $http_status = $data['http_status'] ? intval($data['http_status']) : 'NULL';
    $response_body = sql_escape_string($data['response_body'] ?? '');
    $response_time = $data['response_time'] ? intval($data['response_time']) : 'NULL';
    $retry_count = intval($data['retry_count']);
    $max_retry_count = intval($data['max_retry_count'] ?? 3);
    $status = sql_escape_string($data['status']);
    $error_message = $data['error_message'] ? sql_escape_string($data['error_message']) : '';

    // 성공 시 completed_datetime 설정, pending은 NULL
    $completed = ($status == 'success') ? "NOW()" : "NULL";

    $sql = "INSERT INTO g5_webhook_history SET
        wh_id = {$wh_id},
        mb_id = '{$mb_id}',
        pay_id = {$pay_id},
        whh_event_type = '{$event_type}',
        whh_event_id = '{$event_id}',
        whh_url = '{$url}',
        whh_payload = '{$payload}',
        whh_http_status = {$http_status},
        whh_response_body = '{$response_body}',
        whh_response_time = {$response_time},
        whh_retry_count = {$retry_count},
        whh_max_retry_count = {$max_retry_count},
        whh_status = '{$status}',
        whh_error_message = '{$error_message}',
        whh_sent_datetime = NOW(),
        whh_completed_datetime = {$completed}";

    return sql_query($sql);
}

/**
 * 웹훅 설정 통계 업데이트
 */
function webhook_update_stats($wh_id, $success) {
    $wh_id = intval($wh_id);

    if ($success) {
        $sql = "UPDATE g5_member_webhook SET
            wh_success_count = wh_success_count + 1,
            wh_last_success = NOW()
            WHERE wh_id = {$wh_id}";
    } else {
        $sql = "UPDATE g5_member_webhook SET
            wh_fail_count = wh_fail_count + 1,
            wh_last_fail = NOW()
            WHERE wh_id = {$wh_id}";
    }

    sql_query($sql);
}

/**
 * 날짜 형식 변환
 */
function webhook_format_datetime($datetime_str) {
    if (empty($datetime_str)) return '';

    // 다양한 형식 지원
    $timestamp = strtotime($datetime_str);
    if ($timestamp === false) {
        // YYYYMMDDHHmmss 형식 시도
        if (strlen($datetime_str) == 14) {
            $timestamp = strtotime(
                substr($datetime_str, 0, 4) . '-' .
                substr($datetime_str, 4, 2) . '-' .
                substr($datetime_str, 6, 2) . ' ' .
                substr($datetime_str, 8, 2) . ':' .
                substr($datetime_str, 10, 2) . ':' .
                substr($datetime_str, 12, 2)
            );
        }
    }

    return $timestamp ? date('Y-m-d H:i:s', $timestamp) : '';
}

/**
 * 섹타나인 날짜 형식 변환
 */
function webhook_format_stn_datetime($date_str, $time_str) {
    if (empty($date_str)) return '';

    // YYYYMMDD + HHMMSS
    $datetime = $date_str;
    if (!empty($time_str)) {
        $datetime .= $time_str;
    }

    return webhook_format_datetime($datetime);
}

/**
 * 카드번호 마스킹
 */
function webhook_mask_card_number($card_no) {
    if (strlen($card_no) < 8) return $card_no;
    return substr($card_no, 0, 4) . '****' . substr($card_no, -4);
}

/**
 * 카드코드 → 카드사명 변환
 */
function webhook_get_card_name($card_code, $fn_name = '') {
    // 수기결제의 경우 fnNm 사용
    if (!empty($fn_name)) {
        return $fn_name;
    }

    $card_map = [
        '01' => '비씨', '02' => '국민', '03' => '하나(외환)',
        '04' => '삼성', '06' => '신한', '07' => '현대',
        '08' => '롯데', '11' => '씨티', '12' => 'NH농협',
        '13' => '수협', '15' => '우리', '16' => '하나',
        '21' => '광주', '22' => '전북', '23' => '제주',
        '25' => '해외비자', '26' => '해외마스터', '32' => '우체국',
        '33' => 'MG새마을', '38' => '은련', '46' => '카카오'
    ];

    return $card_map[$card_code] ?? $card_code;
}

/**
 * 섹타나인 카드코드 → 카드사명 변환
 */
function webhook_get_card_name_stn($issue_code) {
    $card_map = [
        '11' => 'KB국민', '12' => '비씨', '14' => '삼성',
        '15' => '신한', '16' => '현대', '17' => '롯데',
        '18' => '하나', '19' => '씨티', '21' => 'NH농협',
        '22' => '수협', '23' => '우리', '31' => '광주',
        '32' => '전북', '33' => '제주', '34' => 'MG새마을',
        '35' => '우체국', '41' => '신협', '51' => '은련'
    ];

    return $card_map[$issue_code] ?? $issue_code;
}

/**
 * 에러 로깅
 */
function webhook_log_error($message) {
    $log_dir = G5_DATA_PATH . '/logs/webhook';
    if (!is_dir($log_dir)) {
        @mkdir($log_dir, 0755, true);
    }

    $log_file = $log_dir . '/' . date('Y-m-d') . '_error.log';
    $log_entry = "[" . date('Y-m-d H:i:s') . "] {$message}\n";
    @file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}
