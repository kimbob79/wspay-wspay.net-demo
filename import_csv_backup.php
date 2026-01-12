<?php
/**
 * CSV to DB Import Script
 * db_1766721050.csv -> backup_251226 테이블
 */

include_once('./_common.php');

// 실행 시간 제한 해제
set_time_limit(0);

// CSV 파일 경로 (이 파일과 같은 디렉토리에 업로드)
$csv_file = __DIR__ . '/db_1766721050.csv';

// 테이블명
$table_name = 'backup_251226';

echo "<pre>";
echo "=== CSV Import Script ===\n\n";

// 1. CSV 파일 존재 확인
if(!file_exists($csv_file)) {
    die("ERROR: CSV 파일이 없습니다. ({$csv_file})\n");
}
echo "[OK] CSV 파일 확인: {$csv_file}\n";

// 2. 테이블 생성 (없으면)
$create_sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `no` int(11) DEFAULT NULL COMMENT '번호',
    `merchant_name` varchar(100) DEFAULT NULL COMMENT '가맹점명',
    `approval_datetime` datetime DEFAULT NULL COMMENT '승인일시',
    `approval_amount` int(11) DEFAULT NULL COMMENT '승인금액',
    `installment` varchar(20) DEFAULT NULL COMMENT '할부',
    `card_company` varchar(20) DEFAULT NULL COMMENT '카드사',
    `approval_no` varchar(50) DEFAULT NULL COMMENT '승인번호',
    `status` varchar(20) DEFAULT NULL COMMENT '구분',
    `tid` varchar(50) DEFAULT NULL COMMENT 'TID',
    PRIMARY KEY (`id`),
    KEY `idx_approval_datetime` (`approval_datetime`),
    KEY `idx_tid` (`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$result = sql_query($create_sql);
if($result) {
    echo "[OK] 테이블 생성/확인 완료: {$table_name}\n";
} else {
    die("ERROR: 테이블 생성 실패\n");
}

// 3. 기존 데이터 삭제 (선택사항 - 중복 방지)
sql_query("TRUNCATE TABLE `{$table_name}`");
echo "[OK] 기존 데이터 초기화 완료\n";

// 4. CSV 파일 읽기
$handle = fopen($csv_file, 'r');
if(!$handle) {
    die("ERROR: CSV 파일을 열 수 없습니다.\n");
}

// BOM 제거
$bom = fread($handle, 3);
if($bom !== "\xef\xbb\xbf") {
    rewind($handle);
}

// 헤더 읽기
$header = fgetcsv($handle);
echo "[OK] CSV 헤더: " . implode(', ', $header) . "\n\n";

// 5. 데이터 삽입
$insert_count = 0;
$error_count = 0;
$line_num = 1;

echo "데이터 삽입 중...\n";

while(($data = fgetcsv($handle)) !== FALSE) {
    $line_num++;

    if(count($data) < 9) {
        echo "  [SKIP] Line {$line_num}: 컬럼 수 부족\n";
        $error_count++;
        continue;
    }

    // 데이터 파싱
    $no = intval($data[0]);
    $merchant_name = sql_escape_string(trim($data[1]));
    $approval_datetime = sql_escape_string(trim($data[2]));

    // 승인금액에서 콤마 제거
    $approval_amount = intval(str_replace(',', '', $data[3]));

    $installment = sql_escape_string(trim($data[4]));
    $card_company = sql_escape_string(trim($data[5]));
    $approval_no = sql_escape_string(trim($data[6]));
    $status = sql_escape_string(trim($data[7]));
    $tid = sql_escape_string(trim($data[8]));

    // INSERT
    $sql = "INSERT INTO `{$table_name}`
            (`no`, `merchant_name`, `approval_datetime`, `approval_amount`, `installment`, `card_company`, `approval_no`, `status`, `tid`)
            VALUES
            ('{$no}', '{$merchant_name}', '{$approval_datetime}', '{$approval_amount}', '{$installment}', '{$card_company}', '{$approval_no}', '{$status}', '{$tid}')";

    if(sql_query($sql)) {
        $insert_count++;
    } else {
        echo "  [ERROR] Line {$line_num}: INSERT 실패\n";
        $error_count++;
    }

    // 진행 상황 출력 (100건마다)
    if($insert_count % 100 == 0) {
        echo "  ... {$insert_count}건 처리됨\n";
    }
}

fclose($handle);

// 6. 결과 출력
echo "\n=== 완료 ===\n";
echo "총 처리: " . ($insert_count + $error_count) . "건\n";
echo "성공: {$insert_count}건\n";
echo "실패: {$error_count}건\n";

// 확인용 조회
$check = sql_fetch("SELECT COUNT(*) as cnt FROM `{$table_name}`");
echo "\n테이블 데이터 수: {$check['cnt']}건\n";

echo "</pre>";
?>
