<?php
/**
 * Keyin 공개 API 인증 모듈
 * - API 키 + TID 인증
 * - Rate Limiting (분당 10건)
 * - API 키 생성/재발급
 */

/**
 * API 키 생성 (64자 hex)
 */
function keyin_generate_api_key() {
    return 'ssp-' . bin2hex(random_bytes(30));
}

/**
 * API 키 인증
 * X-API-Key 헤더에서 키를 추출 → g5_keyin_api_keys 조회
 *
 * @return array 성공 시 ['kak' => 키 row, 'keyin' => keyin 설정 row], 실패 시 에러 응답 후 exit
 */
function keyin_authenticate() {
    $api_key = $_SERVER['HTTP_X_API_KEY'] ?? '';
    $tid = $_SERVER['HTTP_X_TID'] ?? '';

    if (empty($api_key)) {
        keyin_error_response('AUTH_MISSING_KEY', 'X-API-Key 헤더가 필요합니다.', 401);
    }
    if (empty($tid)) {
        keyin_error_response('AUTH_MISSING_TID', 'X-TID 헤더가 필요합니다.', 401);
    }

    // API 키 조회
    $kak = sql_fetch("SELECT * FROM g5_keyin_api_keys
        WHERE kak_key = '" . sql_escape_string($api_key) . "'
        AND kak_status = 'active'");

    if (!$kak['kak_id']) {
        write_keyin_api_log('auth', 'FAILED', ['reason' => 'invalid_key', 'key_prefix' => substr($api_key, 0, 8) . '...']);
        keyin_error_response('AUTH_INVALID_KEY', '유효하지 않은 API 키입니다.', 401);
    }

    // 연결된 Keyin 설정 조회
    $keyin_sql = "SELECT k.*, m.mpc_pg_code, m.mpc_pg_name, m.mpc_type,
                  m.mpc_api_key, m.mpc_mid, m.mpc_mkey,
                  m.mpc_rootup_mid, m.mpc_rootup_tid, m.mpc_rootup_key,
                  m.mpc_stn_mbrno, m.mpc_stn_apikey,
                  m.mpc_winglobal_tid, m.mpc_winglobal_apikey
                  FROM g5_member_keyin_config k
                  LEFT JOIN g5_manual_payment_config m ON k.mpc_id = m.mpc_id
                  WHERE k.mkc_id = '" . (int)$kak['mkc_id'] . "' AND k.mkc_use = 'Y' AND k.mkc_status = 'active'";
    $keyin = sql_fetch($keyin_sql);

    if (!$keyin['mkc_id']) {
        keyin_error_response('AUTH_CONFIG_INACTIVE', 'Keyin 설정이 비활성화되었거나 존재하지 않습니다.', 401);
    }

    // TID 검증 - PG사별로 다른 TID 필드
    $pg_code = $keyin['mpc_id'] ? $keyin['mpc_pg_code'] : $keyin['mkc_pg_code'];
    $expected_tid = keyin_get_expected_tid($keyin, $pg_code);

    if (!hash_equals((string)$expected_tid, (string)$tid)) {
        write_keyin_api_log('auth', 'FAILED', ['reason' => 'invalid_tid', 'mkc_id' => $kak['mkc_id']]);
        keyin_error_response('AUTH_INVALID_TID', '유효하지 않은 TID입니다.', 401);
    }

    // 마지막 사용 시각 + 사용 횟수 업데이트
    sql_query("UPDATE g5_keyin_api_keys SET kak_last_used_at = NOW(), kak_use_count = kak_use_count + 1 WHERE kak_id = '" . (int)$kak['kak_id'] . "'");

    return ['kak' => $kak, 'keyin' => $keyin, 'pg_code' => $pg_code];
}

/**
 * PG사별 예상 TID 값 추출
 */
function keyin_get_expected_tid($keyin, $pg_code) {
    switch ($pg_code) {
        case 'rootup':
            return $keyin['mpc_id'] ? $keyin['mpc_rootup_tid'] : $keyin['mkc_mkey'];
        case 'winglobal':
            return $keyin['mpc_id'] ? $keyin['mpc_winglobal_tid'] : $keyin['mkc_mid'];
        case 'stn':
            return $keyin['mpc_id'] ? $keyin['mpc_stn_mbrno'] : $keyin['mkc_mid'];
        case 'paysis':
        default:
            return $keyin['mpc_id'] ? $keyin['mpc_mid'] : $keyin['mkc_mid'];
    }
}

/**
 * Rate Limit 체크 (분당 10건)
 *
 * @param int $kak_id API 키 ID
 * @param int $limit 분당 허용 건수
 * @return bool true=통과, false=초과
 */
function keyin_check_rate_limit($kak_id, $limit = 10) {
    $minute_key = date('YmdHi');

    // 원자적 카운터 증가
    sql_query("INSERT INTO g5_keyin_api_rate_limit (kak_id, rl_minute, rl_count, rl_updated_at)
        VALUES ('{$kak_id}', '{$minute_key}', 1, NOW())
        ON DUPLICATE KEY UPDATE rl_count = rl_count + 1, rl_updated_at = NOW()");

    $row = sql_fetch("SELECT rl_count FROM g5_keyin_api_rate_limit
        WHERE kak_id = '{$kak_id}' AND rl_minute = '{$minute_key}'");

    // INSERT가 먼저 count를 증가시키므로, 11번째 요청이면 rl_count=11 > 10 으로 차단
    if ((int)$row['rl_count'] > $limit) {
        write_keyin_api_log('rate_limit', 'EXCEEDED', ['kak_id' => $kak_id, 'count' => $row['rl_count'], 'limit' => $limit]);
        return false;
    }

    // 확률적 정리 (1% 확률로 30분 이전 레코드 삭제)
    if (rand(0, 99) === 0) {
        $old_minute = date('YmdHi', strtotime('-30 minutes'));
        sql_query("DELETE FROM g5_keyin_api_rate_limit WHERE rl_minute < '{$old_minute}'");
    }

    return true;
}
