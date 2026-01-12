<?php
/**
 * backup_251226 -> g5_payment 이관 스크립트
 *
 * 기능:
 * 1. backup_251226 데이터를 g5_payment에 삽입
 * 2. 롤백을 위해 삽입된 pay_id를 별도 테이블에 저장
 * 3. 롤백 모드 지원 (?mode=rollback)
 */

include_once('./_common.php');

set_time_limit(0);

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'import';
$backup_table = 'backup_251226';
$rollback_table = 'backup_251226_rollback_ids';

echo "<pre>";
echo "=== backup_251226 -> g5_payment 이관 스크립트 ===\n\n";

// 카드사 코드 -> 이름 매핑
$card_names = array(
    '01' => '비씨',
    '02' => '국민',
    '03' => '하나(외환)',
    '04' => '삼성',
    '05' => '신한',
    '06' => '현대',
    '07' => '롯데',
    '08' => '농협',
    '09' => '씨티',
    '10' => '수협',
    '11' => '신협',
    '12' => '우리',
    '13' => '광주',
    '14' => '전북',
    '15' => 'NH농협',
    '16' => '제주',
    '17' => '케이뱅크',
    '18' => '카카오뱅크',
    '19' => '토스뱅크'
);

// PG사 한글명 -> pg_name 코드 매핑
$pg_name_map = array(
    '광원' => 'k1',
    '코페이' => 'korpay',
    '다날' => 'danal',
    '웰컴' => 'welcom',
    '페이시스' => 'paysis',
    '섹타나인' => 'stn',
    '다우' => 'daou',
    '루트업' => 'routeup',
    '케이에스넷' => 'ksnet',
    '나이스' => 'nice',
    '토스' => 'toss',
    '스마트로' => 'smartro',
    '키움' => 'kiwoom'
);

// ==========================================
// 롤백 모드
// ==========================================
if($mode == 'rollback') {
    echo "[MODE] 롤백 모드\n\n";

    // 롤백 테이블 확인
    $check = sql_fetch("SELECT COUNT(*) as cnt FROM `{$rollback_table}`");
    $total = intval($check['cnt']);

    echo "롤백 테이블 데이터: {$total}건\n";

    if($total == 0) {
        echo "[OK] 삭제할 데이터 없음.\n";
        echo "</pre>";
        exit;
    }

    echo "삭제 진행 중...\n";

    // 롤백 테이블 기반 배치 삭제
    $batch_size = 500;
    $deleted_total = 0;

    while(true) {
        // pay_id 목록 조회
        $ids = array();
        $ids_result = sql_query("SELECT pay_id FROM `{$rollback_table}` LIMIT {$batch_size}");
        while($r = sql_fetch_array($ids_result)) {
            $ids[] = intval($r['pay_id']);
        }

        if(count($ids) == 0) {
            break;
        }

        $ids_str = implode(',', $ids);

        // g5_payment에서 삭제
        sql_query("DELETE FROM g5_payment WHERE pay_id IN ({$ids_str})");
        $affected = sql_affected_rows();
        $deleted_total += $affected;

        // 롤백 테이블에서 삭제
        sql_query("DELETE FROM `{$rollback_table}` WHERE pay_id IN ({$ids_str})");

        echo "  ... {$deleted_total}건 삭제됨\n";
    }

    echo "\n[OK] g5_payment 삭제 완료: {$deleted_total}건\n";
    echo "[OK] 롤백 테이블 초기화 완료\n";

    echo "\n=== 롤백 완료 ===\n";
    echo "</pre>";
    exit;
}

// ==========================================
// 이관 모드
// ==========================================
echo "[MODE] 이관 모드\n\n";

// 1. 롤백 테이블 생성
$create_rollback = "CREATE TABLE IF NOT EXISTS `{$rollback_table}` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `pay_id` int(11) NOT NULL,
    `backup_id` int(11) DEFAULT NULL,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_pay_id` (`pay_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
sql_query($create_rollback);
echo "[OK] 롤백 테이블 준비 완료\n";

// 2. 기존 롤백 데이터 확인
$force = isset($_GET['force']) ? $_GET['force'] : '';
$existing_rollback = sql_fetch("SELECT COUNT(*) as cnt FROM `{$rollback_table}`");
if($existing_rollback['cnt'] > 0) {
    if($force == '1') {
        // 강제 모드: 롤백 테이블 초기화 후 진행
        sql_query("TRUNCATE TABLE `{$rollback_table}`");
        echo "[OK] 롤백 테이블 초기화 (강제 모드)\n";
    } else {
        echo "\n[WARNING] 이전 이관 데이터가 있습니다 ({$existing_rollback['cnt']}건)\n";
        echo "롤백하려면: ?mode=rollback\n";
        echo "강제 진행하려면: ?force=1\n";
        echo "</pre>";
        exit;
    }
}

// 3. backup 테이블 데이터 조회
$backup_count = sql_fetch("SELECT COUNT(*) as cnt FROM `{$backup_table}`");
echo "[OK] backup 테이블 데이터: {$backup_count['cnt']}건\n\n";

if($backup_count['cnt'] == 0) {
    die("ERROR: backup 테이블에 데이터가 없습니다.\n");
}

// 4. 데이터 이관 시작
$result = sql_query("SELECT * FROM `{$backup_table}` ORDER BY id");

$insert_count = 0;
$skip_count = 0;
$error_count = 0;
$no_device_count = 0;
$no_device_list = array(); // 매칭 안되는 TID 목록
$pg_name_stats = array(); // PG명 통계
$unknown_pg_list = array(); // 매핑 안된 PG명

echo "이관 진행 중...\n";

while($row = sql_fetch_array($result)) {
    $tid = $row['tid'];
    $approval_no = $row['approval_no'];
    $approval_datetime = $row['approval_datetime'];
    $approval_amount = intval($row['approval_amount']);
    $merchant_name = $row['merchant_name'];
    $installment = $row['installment'];
    $card_code = $row['card_company'];
    $status = $row['status'];

    // 카드사 이름 변환
    $card_name = isset($card_names[$card_code]) ? $card_names[$card_code] : $card_code;

    // 할부 변환 (일시불 -> 00, X개월 -> XX)
    if($installment == '일시불' || $installment == '00' || $installment == '0') {
        $pay_parti = '00';
    } else {
        $pay_parti = sprintf('%02d', intval($installment));
    }

    // 승인/취소 구분
    $pay_type = ($status == '승인') ? 'Y' : 'N';

    // 중복 체크 (승인번호 + 승인일시 + 금액)
    $dup_check = sql_fetch("SELECT pay_id FROM g5_payment
                            WHERE pay_num = '{$approval_no}'
                            AND pay_datetime = '{$approval_datetime}'
                            AND ABS(pay) = '{$approval_amount}'");
    if($dup_check['pay_id']) {
        $skip_count++;
        continue;
    }

    // TID로 디바이스 조회
    $device = sql_fetch("SELECT * FROM g5_device WHERE dv_tid = '{$tid}'");

    if(!$device['dv_id']) {
        // TID가 없는 경우 - 목록에 저장하고 스킵
        $no_device_count++;
        if(!isset($no_device_list[$tid])) {
            $no_device_list[$tid] = array(
                'tid' => $tid,
                'merchant_name' => $merchant_name,
                'count' => 0
            );
        }
        $no_device_list[$tid]['count']++;
        continue;
    }

    // 수수료 계산 (기존 로직 사용)
    $mb_1_fee = $device['mb_1_fee'];
    $mb_2_fee = $device['mb_2_fee'];
    $mb_3_fee = $device['mb_3_fee'];
    $mb_4_fee = $device['mb_4_fee'];
    $mb_5_fee = $device['mb_5_fee'];
    $mb_6_fee = $device['mb_6_fee'];

    // 수수료 분배 계산
    $calc_mb_5_fee = ($device['mb_5_fee'] > 0.001) ? ($device['mb_6_fee'] - $device['mb_5_fee']) : 0.00;
    $calc_mb_4_fee = ($device['mb_4_fee'] > 0.001) ? ($device['mb_6_fee'] - $calc_mb_5_fee - $device['mb_4_fee']) : 0.00;
    $calc_mb_3_fee = ($device['mb_3_fee'] > 0.001) ? ($device['mb_6_fee'] - $calc_mb_5_fee - $calc_mb_4_fee - $device['mb_3_fee']) : 0.00;
    $calc_mb_2_fee = ($device['mb_2_fee'] > 0.001) ? ($device['mb_6_fee'] - $calc_mb_5_fee - $calc_mb_4_fee - $calc_mb_3_fee - $device['mb_2_fee']) : 0.00;
    $calc_mb_1_fee = $device['mb_6_fee'] - $calc_mb_5_fee - $calc_mb_4_fee - $calc_mb_3_fee - $calc_mb_2_fee - $device['mb_1_fee'];

    $mb_1_pay = $approval_amount * $calc_mb_1_fee / 100;
    $mb_2_pay = $approval_amount * $calc_mb_2_fee / 100;
    $mb_3_pay = $approval_amount * $calc_mb_3_fee / 100;
    $mb_4_pay = $approval_amount * $calc_mb_4_fee / 100;
    $mb_5_pay = $approval_amount * $calc_mb_5_fee / 100;
    $mb_6_pay = $approval_amount - ($approval_amount * $device['mb_6_fee'] / 100);

    // trxId 생성 (backup import 구분용)
    $trxId = 'BK_' . $row['id'] . '_' . $approval_no;

    // mb_1_name(한글)으로 pg_name 코드 찾기
    $pg_name_code = 'unknown';
    $mb_1_name_trim = trim($device['mb_1_name']);
    if(isset($pg_name_map[$mb_1_name_trim])) {
        $pg_name_code = $pg_name_map[$mb_1_name_trim];
    } else {
        // 부분 매칭 시도
        foreach($pg_name_map as $korean => $code) {
            if(strpos($mb_1_name_trim, $korean) !== false) {
                $pg_name_code = $code;
                break;
            }
        }
    }

    // PG명 통계 기록
    if(!isset($pg_name_stats[$pg_name_code])) {
        $pg_name_stats[$pg_name_code] = 0;
    }
    $pg_name_stats[$pg_name_code]++;

    // 매핑 안된 경우 기록
    if($pg_name_code == 'unknown' && !isset($unknown_pg_list[$mb_1_name_trim])) {
        $unknown_pg_list[$mb_1_name_trim] = 0;
    }
    if($pg_name_code == 'unknown') {
        $unknown_pg_list[$mb_1_name_trim]++;
    }

    // INSERT
    $insert_sql = "INSERT INTO g5_payment SET
        pay_type = '{$pay_type}',
        pay = '{$approval_amount}',
        pay_num = '" . sql_escape_string($approval_no) . "',
        trxid = '{$trxId}',
        trackId = '',
        pay_datetime = '{$approval_datetime}',
        pay_cdatetime = '',
        pay_parti = '{$pay_parti}',
        pay_card_name = '" . sql_escape_string($card_name) . "',
        pay_card_num = '',

        mb_1 = '{$device['mb_1']}',
        mb_1_name = '" . sql_escape_string($device['mb_1_name']) . "',
        mb_1_fee = '{$mb_1_fee}',
        mb_1_pay = '{$mb_1_pay}',

        mb_2 = '{$device['mb_2']}',
        mb_2_name = '" . sql_escape_string($device['mb_2_name']) . "',
        mb_2_fee = '{$mb_2_fee}',
        mb_2_pay = '{$mb_2_pay}',

        mb_3 = '{$device['mb_3']}',
        mb_3_name = '" . sql_escape_string($device['mb_3_name']) . "',
        mb_3_fee = '{$mb_3_fee}',
        mb_3_pay = '{$mb_3_pay}',

        mb_4 = '{$device['mb_4']}',
        mb_4_name = '" . sql_escape_string($device['mb_4_name']) . "',
        mb_4_fee = '{$mb_4_fee}',
        mb_4_pay = '{$mb_4_pay}',

        mb_5 = '{$device['mb_5']}',
        mb_5_name = '" . sql_escape_string($device['mb_5_name']) . "',
        mb_5_fee = '{$mb_5_fee}',
        mb_5_pay = '{$mb_5_pay}',

        mb_6 = '{$device['mb_6']}',
        mb_6_name = '" . sql_escape_string($device['mb_6_name']) . "',
        mb_6_fee = '{$mb_6_fee}',
        mb_6_pay = '{$mb_6_pay}',

        dv_type = '{$device['dv_type']}',
        dv_certi = '{$device['dv_certi']}',
        dv_tid = '{$tid}',
        dv_tid_ori = '',
        pg_name = '{$pg_name_code}',

        datetime = '" . G5_TIME_YMDHIS . "'";

    if(sql_query($insert_sql)) {
        $pay_id = sql_insert_id();

        // 롤백 테이블에 기록
        sql_query("INSERT INTO `{$rollback_table}` (pay_id, backup_id) VALUES ('{$pay_id}', '{$row['id']}')");

        $insert_count++;

        // 진행 상황 (50건마다)
        if($insert_count % 50 == 0) {
            echo "  ... {$insert_count}건 처리됨\n";
        }
    } else {
        $error_count++;
        echo "  [ERROR] INSERT 실패 (backup_id: {$row['id']})\n";
    }
}

// 5. 결과 출력
echo "\n=== 이관 완료 ===\n";
echo "총 대상: {$backup_count['cnt']}건\n";
echo "성공: {$insert_count}건\n";
echo "중복 스킵: {$skip_count}건\n";
echo "디바이스 없음: {$no_device_count}건\n";
echo "실패: {$error_count}건\n";

// 확인
$rollback_check = sql_fetch("SELECT COUNT(*) as cnt FROM `{$rollback_table}`");
echo "\n롤백 테이블 기록: {$rollback_check['cnt']}건\n";

// 6. PG명 통계 출력
if(count($pg_name_stats) > 0) {
    echo "\n=== PG명 통계 ===\n";
    arsort($pg_name_stats);
    foreach($pg_name_stats as $pg => $cnt) {
        echo "  {$pg}: {$cnt}건\n";
    }
}

// 7. 매핑 안된 PG명 출력
if(count($unknown_pg_list) > 0) {
    echo "\n=== 매핑 안된 PG명 (mb_1_name) ===\n";
    foreach($unknown_pg_list as $name => $cnt) {
        echo "  '{$name}': {$cnt}건\n";
    }
    echo "\n[WARNING] 위 PG명을 pg_name_map에 추가하세요.\n";
}

// 8. 매칭 안된 TID 목록 출력 (중복 제거)
if(count($no_device_list) > 0) {
    echo "\n=== 매칭 안된 TID 목록 (" . count($no_device_list) . "개) ===\n";
    echo str_pad("TID", 20) . str_pad("가맹점명", 25) . "건수\n";
    echo str_repeat("-", 55) . "\n";
    foreach($no_device_list as $item) {
        echo str_pad($item['tid'], 20) . str_pad($item['merchant_name'], 25) . $item['count'] . "건\n";
    }
}

echo "\n[INFO] 롤백하려면: ?mode=rollback\n";
echo "</pre>";
?>
