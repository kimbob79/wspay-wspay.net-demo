<?php
include_once('./_common.php');

// 관리자 권한 체크
if(!$is_admin) {
    alert("관리자만 접근할 수 있습니다.");
}

$mb_id = isset($_GET['mb_id']) ? $_GET['mb_id'] : '';
$level = isset($_GET['level']) ? $_GET['level'] : '3';
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$mb_nick = isset($_GET['mb_nick']) ? $_GET['mb_nick'] : '';
$dv_tid = isset($_GET['dv_tid']) ? $_GET['dv_tid'] : '';

if(!$mb_id) {
    alert("가맹점 정보가 없습니다.", "./?p=member&level=3");
}

// 가맹점 정보 조회
$member_info = sql_fetch("SELECT * FROM {$g5['member_table']} WHERE mb_id = '{$mb_id}'");
if(!$member_info) {
    alert("존재하지 않는 가맹점입니다.", "./?p=member&level=3");
}

$title1 = "Keyin 설정";
$title2 = $member_info['mb_nick'] . " - Keyin 설정";

// 가맹점별 Keyin 설정 테이블 존재 여부 확인 및 생성
$table_name = "g5_member_keyin_config";
$check_table = sql_query("SHOW TABLES LIKE '{$table_name}'");
if(sql_num_rows($check_table) == 0) {
    $create_sql = "CREATE TABLE `{$table_name}` (
        `mkc_id` int(11) NOT NULL AUTO_INCREMENT,
        `mb_id` varchar(50) NOT NULL COMMENT '가맹점 아이디',
        `mpc_id` int(11) DEFAULT NULL COMMENT '대표가맹점 설정 ID (NULL이면 개별설정)',
        `mkc_pg_code` varchar(20) DEFAULT NULL COMMENT 'PG사 코드',
        `mkc_pg_name` varchar(50) DEFAULT NULL COMMENT 'PG사 이름',
        `mkc_type` varchar(20) DEFAULT NULL COMMENT '인증 타입 (nonauth/auth)',
        `mkc_api_key` varchar(100) DEFAULT NULL COMMENT 'API KEY',
        `mkc_mid` varchar(50) DEFAULT NULL COMMENT '상점 ID',
        `mkc_mkey` varchar(200) DEFAULT NULL COMMENT '암호화 키',
        `mkc_use` char(1) NOT NULL DEFAULT 'Y' COMMENT '사용여부',
        `mkc_memo` text COMMENT '메모',
        `mkc_cancel_yn` char(1) NOT NULL DEFAULT 'Y' COMMENT '취소가능여부',
        `mkc_duplicate_yn` char(1) NOT NULL DEFAULT 'N' COMMENT '중복결제가능여부',
        `mkc_weekend_yn` char(1) NOT NULL DEFAULT 'Y' COMMENT '주말/공휴일결제가능여부',
        `mkc_limit_once` int(11) NOT NULL DEFAULT 0 COMMENT '1회결제한도 (0=무제한)',
        `mkc_limit_daily` int(11) NOT NULL DEFAULT 0 COMMENT '일일결제한도 (0=무제한)',
        `mkc_limit_monthly` int(11) NOT NULL DEFAULT 0 COMMENT '월결제한도 (0=무제한)',
        `mkc_max_installment` int(11) NOT NULL DEFAULT 12 COMMENT '최대할부개월수',
        `mkc_time_start` varchar(5) DEFAULT '00:00' COMMENT '결제가능시작시간',
        `mkc_time_end` varchar(5) DEFAULT '23:59' COMMENT '결제가능종료시간',
        `mkc_oid` varchar(4) DEFAULT NULL COMMENT '가맹점OID (대표가맹점설정 사용시 주문번호 구분용)',
        `mkc_status` enum('active','deleted') DEFAULT 'active' COMMENT '상태 (active/deleted)',
        `mkc_datetime` datetime DEFAULT NULL COMMENT '등록일시',
        `mkc_update` datetime DEFAULT NULL COMMENT '수정일시',
        PRIMARY KEY (`mkc_id`),
        KEY `idx_mb_id` (`mb_id`),
        KEY `idx_mpc_id` (`mpc_id`),
        KEY `idx_status` (`mkc_status`),
        UNIQUE KEY `idx_mkc_oid` (`mkc_oid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='가맹점별 Keyin 설정'";
    sql_query($create_sql);
}

// 기존 테이블에 새 컬럼 추가 (마이그레이션)
$check_column = sql_query("SHOW COLUMNS FROM `{$table_name}` LIKE 'mkc_cancel_yn'");
if(sql_num_rows($check_column) == 0) {
    sql_query("ALTER TABLE `{$table_name}`
        ADD COLUMN `mkc_cancel_yn` char(1) NOT NULL DEFAULT 'Y' COMMENT '취소가능여부' AFTER `mkc_memo`,
        ADD COLUMN `mkc_duplicate_yn` char(1) NOT NULL DEFAULT 'N' COMMENT '중복결제가능여부' AFTER `mkc_cancel_yn`,
        ADD COLUMN `mkc_weekend_yn` char(1) NOT NULL DEFAULT 'Y' COMMENT '주말/공휴일결제가능여부' AFTER `mkc_duplicate_yn`,
        ADD COLUMN `mkc_limit_once` int(11) NOT NULL DEFAULT 0 COMMENT '1회결제한도 (0=무제한)' AFTER `mkc_weekend_yn`,
        ADD COLUMN `mkc_limit_daily` int(11) NOT NULL DEFAULT 0 COMMENT '일일결제한도 (0=무제한)' AFTER `mkc_limit_once`,
        ADD COLUMN `mkc_limit_monthly` int(11) NOT NULL DEFAULT 0 COMMENT '월결제한도 (0=무제한)' AFTER `mkc_limit_daily`,
        ADD COLUMN `mkc_max_installment` int(11) NOT NULL DEFAULT 12 COMMENT '최대할부개월수' AFTER `mkc_limit_monthly`,
        ADD COLUMN `mkc_time_start` varchar(5) DEFAULT '00:00' COMMENT '결제가능시작시간' AFTER `mkc_max_installment`,
        ADD COLUMN `mkc_time_end` varchar(5) DEFAULT '23:59' COMMENT '결제가능종료시간' AFTER `mkc_time_start`
    ");
}

// mkc_oid 컬럼 추가 (마이그레이션)
$check_oid_column = sql_query("SHOW COLUMNS FROM `{$table_name}` LIKE 'mkc_oid'");
if(sql_num_rows($check_oid_column) == 0) {
    sql_query("ALTER TABLE `{$table_name}`
        ADD COLUMN `mkc_oid` varchar(4) DEFAULT NULL COMMENT '가맹점OID (대표가맹점설정 사용시 주문번호 구분용)' AFTER `mkc_time_end`,
        ADD UNIQUE KEY `idx_mkc_oid` (`mkc_oid`)
    ");
} else {
    // 기존 3자리에서 4자리로 변경 (마이그레이션)
    $col_info = sql_fetch("SHOW COLUMNS FROM `{$table_name}` LIKE 'mkc_oid'");
    if($col_info && strpos($col_info['Type'], 'varchar(3)') !== false) {
        sql_query("ALTER TABLE `{$table_name}` MODIFY COLUMN `mkc_oid` varchar(4) DEFAULT NULL COMMENT '가맹점OID (대표가맹점설정 사용시 주문번호 구분용)'");
    }
}

// mkc_status 컬럼 추가 (마이그레이션)
$check_status_column = sql_query("SHOW COLUMNS FROM `{$table_name}` LIKE 'mkc_status'");
if(sql_num_rows($check_status_column) == 0) {
    sql_query("ALTER TABLE `{$table_name}` ADD COLUMN `mkc_status` enum('active','deleted') DEFAULT 'active' COMMENT '상태 (active/deleted)' AFTER `mkc_oid`");
    sql_query("ALTER TABLE `{$table_name}` ADD KEY `idx_status` (`mkc_status`)");
}

// OID가 NULL인 기존 레코드에 OID 자동 부여 (마이그레이션)
$null_oid_records = sql_query("SELECT mkc_id FROM `{$table_name}` WHERE mkc_oid IS NULL OR mkc_oid = ''");
if(sql_num_rows($null_oid_records) > 0) {
    $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $alphanumeric = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    while($row = sql_fetch_array($null_oid_records)) {
        // 고유 OID 생성
        $new_oid = '';
        for($attempt = 0; $attempt < 100; $attempt++) {
            $new_oid = $letters[rand(0, 25)] . $alphanumeric[rand(0, 35)] . $alphanumeric[rand(0, 35)] . $alphanumeric[rand(0, 35)];
            $check = sql_fetch("SELECT mkc_id FROM `{$table_name}` WHERE mkc_oid = '{$new_oid}'");
            if(!$check['mkc_id']) break;
        }
        if($new_oid) {
            sql_query("UPDATE `{$table_name}` SET mkc_oid = '{$new_oid}' WHERE mkc_id = '{$row['mkc_id']}'");
        }
    }
}

// 가맹점 OID 생성 함수 (4자리: 첫자리 A-Z, 나머지 3자리 영숫자)
// 주문번호 형식: XXXX-YYMM-HHMM-SSRR (OID-년월-시분-초+랜덤2자리)
function generate_merchant_oid($table_name) {
    $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $alphanumeric = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $max_attempts = 100; // 무한루프 방지

    for($i = 0; $i < $max_attempts; $i++) {
        // 첫자리: A-Z 중 랜덤
        $oid = $letters[rand(0, 25)];
        // 나머지 3자리: 영숫자 랜덤
        $oid .= $alphanumeric[rand(0, 35)];
        $oid .= $alphanumeric[rand(0, 35)];
        $oid .= $alphanumeric[rand(0, 35)];

        // 중복 체크
        $check = sql_fetch("SELECT mkc_id FROM {$table_name} WHERE mkc_oid = '{$oid}'");
        if(!$check['mkc_id']) {
            return $oid; // 고유값 확인되면 반환
        }
    }

    // 모든 시도 실패시 타임스탬프 기반 생성 (매우 드문 경우)
    return $letters[rand(0, 25)] . strtoupper(substr(md5(microtime()), 0, 3));
}

// 처리
$mode = isset($_POST['mode']) ? $_POST['mode'] : (isset($_GET['mode']) ? $_GET['mode'] : '');

// 삭제 처리 (소프트 삭제 - status를 deleted로 변경)
if($mode == 'delete' && isset($_GET['mkc_id'])) {
    $mkc_id = (int)$_GET['mkc_id'];
    sql_query("UPDATE {$table_name} SET mkc_status = 'deleted' WHERE mkc_id = '{$mkc_id}' AND mb_id = '{$mb_id}'");
    goto_url("./?p=member_keyin_config&mb_id={$mb_id}&level={$level}&page={$page}&mb_nick={$mb_nick}&dv_tid={$dv_tid}");
}

// 저장 처리
if($mode == 'save') {
    $mkc_id = isset($_POST['mkc_id']) ? (int)$_POST['mkc_id'] : 0;
    $config_type = isset($_POST['config_type']) ? $_POST['config_type'] : 'master'; // master: 대표가맹점 설정 사용, custom: 개별설정
    $mpc_id = isset($_POST['mpc_id']) ? (int)$_POST['mpc_id'] : 0;
    $mkc_pg_code = isset($_POST['mkc_pg_code']) ? sql_escape_string($_POST['mkc_pg_code']) : '';
    $mkc_pg_name = isset($_POST['mkc_pg_name']) ? sql_escape_string($_POST['mkc_pg_name']) : '';
    $mkc_type = isset($_POST['mkc_type']) ? sql_escape_string($_POST['mkc_type']) : '';
    $mkc_api_key = isset($_POST['mkc_api_key']) ? sql_escape_string($_POST['mkc_api_key']) : '';
    $mkc_mid = isset($_POST['mkc_mid']) ? sql_escape_string($_POST['mkc_mid']) : '';
    $mkc_mkey = isset($_POST['mkc_mkey']) ? sql_escape_string($_POST['mkc_mkey']) : '';
    $mkc_use = isset($_POST['mkc_use']) ? sql_escape_string($_POST['mkc_use']) : 'Y';
    $mkc_memo = isset($_POST['mkc_memo']) ? sql_escape_string($_POST['mkc_memo']) : '';

    // 새 필드들
    $mkc_cancel_yn = isset($_POST['mkc_cancel_yn']) ? sql_escape_string($_POST['mkc_cancel_yn']) : 'Y';
    $mkc_duplicate_yn = isset($_POST['mkc_duplicate_yn']) ? sql_escape_string($_POST['mkc_duplicate_yn']) : 'N';
    $mkc_weekend_yn = isset($_POST['mkc_weekend_yn']) ? sql_escape_string($_POST['mkc_weekend_yn']) : 'Y';
    $mkc_limit_once = isset($_POST['mkc_limit_once']) ? (int)$_POST['mkc_limit_once'] : 0;
    $mkc_limit_daily = isset($_POST['mkc_limit_daily']) ? (int)$_POST['mkc_limit_daily'] : 0;
    $mkc_limit_monthly = isset($_POST['mkc_limit_monthly']) ? (int)$_POST['mkc_limit_monthly'] : 0;
    $mkc_max_installment = isset($_POST['mkc_max_installment']) ? (int)$_POST['mkc_max_installment'] : 12;
    $mkc_time_start = isset($_POST['mkc_time_start']) ? sql_escape_string($_POST['mkc_time_start']) : '00:00';
    $mkc_time_end = isset($_POST['mkc_time_end']) ? sql_escape_string($_POST['mkc_time_end']) : '23:59';

    if($config_type == 'master') {
        // 대표가맹점 설정 사용
        if(!$mpc_id) {
            alert("대표가맹점 설정을 선택해주세요.");
        }
        // 대표가맹점 설정 정보 가져오기
        $master_config = sql_fetch("SELECT * FROM g5_manual_payment_config WHERE mpc_id = '{$mpc_id}'");
        if(!$master_config) {
            alert("선택한 대표가맹점 설정이 존재하지 않습니다.");
        }
        $mkc_pg_code = $master_config['mpc_pg_code'];
        $mkc_pg_name = $master_config['mpc_pg_name'];
        $mkc_type = $master_config['mpc_type'];
        $mkc_api_key = null;
        $mkc_mid = null;
        $mkc_mkey = null;
    } else {
        // 개별 설정
        $mpc_id = null;
        if(!$mkc_pg_code || !$mkc_type || !$mkc_api_key || !$mkc_mid || !$mkc_mkey) {
            alert("모든 필수 항목을 입력해주세요.");
        }
    }

    $now = date("Y-m-d H:i:s");

    if($mkc_id) {
        // 수정
        // 기존 레코드의 mkc_oid 확인 (OID가 없으면 생성)
        $existing = sql_fetch("SELECT mkc_oid, mpc_id FROM {$table_name} WHERE mkc_id = '{$mkc_id}'");

        // OID가 없으면 새로 생성 (대표설정/개별설정 모두)
        $mkc_oid_sql = "";
        if(!$existing['mkc_oid']) {
            $new_oid = generate_merchant_oid($table_name);
            $mkc_oid_sql = ", mkc_oid = '{$new_oid}'";
        }

        if($config_type == 'master') {
            $sql = "UPDATE {$table_name} SET
                mpc_id = '{$mpc_id}',
                mkc_pg_code = '{$mkc_pg_code}',
                mkc_pg_name = '{$mkc_pg_name}',
                mkc_type = '{$mkc_type}',
                mkc_api_key = NULL,
                mkc_mid = NULL,
                mkc_mkey = NULL,
                mkc_use = '{$mkc_use}',
                mkc_memo = '{$mkc_memo}',
                mkc_cancel_yn = '{$mkc_cancel_yn}',
                mkc_duplicate_yn = '{$mkc_duplicate_yn}',
                mkc_weekend_yn = '{$mkc_weekend_yn}',
                mkc_limit_once = '{$mkc_limit_once}',
                mkc_limit_daily = '{$mkc_limit_daily}',
                mkc_limit_monthly = '{$mkc_limit_monthly}',
                mkc_max_installment = '{$mkc_max_installment}',
                mkc_time_start = '{$mkc_time_start}',
                mkc_time_end = '{$mkc_time_end}',
                mkc_update = '{$now}'
                {$mkc_oid_sql}
                WHERE mkc_id = '{$mkc_id}' AND mb_id = '{$mb_id}'";
        } else {
            // 개별설정
            $sql = "UPDATE {$table_name} SET
                mpc_id = NULL,
                mkc_pg_code = '{$mkc_pg_code}',
                mkc_pg_name = '{$mkc_pg_name}',
                mkc_type = '{$mkc_type}',
                mkc_api_key = '{$mkc_api_key}',
                mkc_mid = '{$mkc_mid}',
                mkc_mkey = '{$mkc_mkey}',
                mkc_use = '{$mkc_use}',
                mkc_memo = '{$mkc_memo}',
                mkc_cancel_yn = '{$mkc_cancel_yn}',
                mkc_duplicate_yn = '{$mkc_duplicate_yn}',
                mkc_weekend_yn = '{$mkc_weekend_yn}',
                mkc_limit_once = '{$mkc_limit_once}',
                mkc_limit_daily = '{$mkc_limit_daily}',
                mkc_limit_monthly = '{$mkc_limit_monthly}',
                mkc_max_installment = '{$mkc_max_installment}',
                mkc_time_start = '{$mkc_time_start}',
                mkc_time_end = '{$mkc_time_end}',
                mkc_update = '{$now}'
                {$mkc_oid_sql}
                WHERE mkc_id = '{$mkc_id}' AND mb_id = '{$mb_id}'";
        }
    } else {
        // 신규 등록 - 대표/개별 모두 OID 생성
        $mkc_oid = generate_merchant_oid($table_name);

        if($config_type == 'master') {
            $sql = "INSERT INTO {$table_name} (mb_id, mpc_id, mkc_pg_code, mkc_pg_name, mkc_type, mkc_api_key, mkc_mid, mkc_mkey, mkc_use, mkc_memo, mkc_cancel_yn, mkc_duplicate_yn, mkc_weekend_yn, mkc_limit_once, mkc_limit_daily, mkc_limit_monthly, mkc_max_installment, mkc_time_start, mkc_time_end, mkc_oid, mkc_datetime)
                VALUES ('{$mb_id}', '{$mpc_id}', '{$mkc_pg_code}', '{$mkc_pg_name}', '{$mkc_type}', NULL, NULL, NULL, '{$mkc_use}', '{$mkc_memo}', '{$mkc_cancel_yn}', '{$mkc_duplicate_yn}', '{$mkc_weekend_yn}', '{$mkc_limit_once}', '{$mkc_limit_daily}', '{$mkc_limit_monthly}', '{$mkc_max_installment}', '{$mkc_time_start}', '{$mkc_time_end}', '{$mkc_oid}', '{$now}')";
        } else {
            $sql = "INSERT INTO {$table_name} (mb_id, mpc_id, mkc_pg_code, mkc_pg_name, mkc_type, mkc_api_key, mkc_mid, mkc_mkey, mkc_use, mkc_memo, mkc_cancel_yn, mkc_duplicate_yn, mkc_weekend_yn, mkc_limit_once, mkc_limit_daily, mkc_limit_monthly, mkc_max_installment, mkc_time_start, mkc_time_end, mkc_oid, mkc_datetime)
                VALUES ('{$mb_id}', NULL, '{$mkc_pg_code}', '{$mkc_pg_name}', '{$mkc_type}', '{$mkc_api_key}', '{$mkc_mid}', '{$mkc_mkey}', '{$mkc_use}', '{$mkc_memo}', '{$mkc_cancel_yn}', '{$mkc_duplicate_yn}', '{$mkc_weekend_yn}', '{$mkc_limit_once}', '{$mkc_limit_daily}', '{$mkc_limit_monthly}', '{$mkc_max_installment}', '{$mkc_time_start}', '{$mkc_time_end}', '{$mkc_oid}', '{$now}')";
        }
    }
    sql_query($sql);
    goto_url("./?p=member_keyin_config&mb_id={$mb_id}&level={$level}&page={$page}&mb_nick={$mb_nick}&dv_tid={$dv_tid}");
}

// 수정 데이터 조회
$edit_data = null;
if(isset($_GET['mkc_id']) && $_GET['mkc_id']) {
    $mkc_id = (int)$_GET['mkc_id'];
    $edit_data = sql_fetch("SELECT * FROM {$table_name} WHERE mkc_id = '{$mkc_id}' AND mb_id = '{$mb_id}'");
}

// 대표가맹점 설정 목록 조회
$master_configs = sql_query("SELECT * FROM g5_manual_payment_config WHERE mpc_use = 'Y' AND mpc_status = 'active' ORDER BY mpc_pg_code, mpc_type");

// 가맹점별 설정 목록 조회 (삭제되지 않은 항목만)
$list = sql_query("SELECT * FROM {$table_name} WHERE mb_id = '{$mb_id}' AND mkc_status = 'active' ORDER BY mkc_id DESC");

include_once('./_head.php');
?>

<style>
.keyin-container {
    padding: 0;
}

.keyin-header {
    background: linear-gradient(135deg, #393E46 0%, #4a5058 100%);
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(57, 62, 70, 0.3);
}

.keyin-title {
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.keyin-title i {
    font-size: 14px;
    opacity: 0.8;
}

.keyin-subtitle {
    color: rgba(255,255,255,0.9);
    font-size: 12px;
    margin-top: 4px;
}

.keyin-section {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.keyin-section h3 {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin: 0 0 16px 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.keyin-section h3 i {
    color: #FFD369;
}

/* 테이블 폼 스타일 */
.form-table {
    width: 100%;
    border-collapse: collapse;
}

.form-table th,
.form-table td {
    padding: 10px 12px;
    border: 1px solid #e0e0e0;
    vertical-align: middle;
}

.form-table th {
    background: #f8f9fa;
    font-size: 13px;
    font-weight: 600;
    color: #333;
    text-align: left;
    width: 90px;
    white-space: nowrap;
}

.form-table th .required {
    color: #e53935;
    margin-left: 2px;
}

.form-table td {
    background: #fff;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 13px;
    transition: border-color 0.15s;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: #FFD369;
}

.form-control-inline {
    display: inline-block;
    width: auto;
    min-width: 150px;
}

select.form-control {
    height: 42px;
    appearance: auto;
}

textarea.form-control {
    resize: vertical;
}

/* 설정 타입 선택 */
.config-type-selector {
    display: flex;
    gap: 16px;
    margin-bottom: 20px;
}

.config-type-option {
    flex: 1;
    padding: 16px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.config-type-option:hover {
    border-color: #FFD369;
    background: #FFF8E1;
}

.config-type-option.selected {
    border-color: #FFD369;
    background: #FFF8E1;
}

.config-type-option input[type="radio"] {
    margin-right: 8px;
    accent-color: #FFD369;
}

.config-type-option .option-title {
    font-weight: 600;
    color: #333;
    display: flex;
    align-items: center;
}

.config-type-option .option-desc {
    font-size: 12px;
    color: #666;
    margin-top: 4px;
    margin-left: 20px;
}

/* 대표가맹점 선택 영역 */
.master-config-panel, .custom-config-panel {
    display: none;
    padding: 16px;
    background: #fafafa;
    border-radius: 8px;
    margin-bottom: 16px;
}

.master-config-panel.active, .custom-config-panel.active {
    display: block;
}

/* 버튼 */
.keyin-btn-group {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.keyin-btn {
    display: inline-block;
    padding: 10px 24px;
    font-size: 14px;
    font-weight: 600;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    text-align: center;
    line-height: 1.4;
}

.keyin-btn-primary {
    background: linear-gradient(135deg, #393E46 0%, #4a5058 100%);
    color: #FFD369 !important;
}

.keyin-btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 211, 105, 0.3);
}

.keyin-btn-secondary {
    background: #f5f5f5;
    color: #666 !important;
    border: 1px solid #ddd;
}

.keyin-btn-secondary:hover {
    background: #eee;
}

.keyin-btn-danger {
    background: #e53935;
    color: #fff !important;
}

.keyin-btn-danger:hover {
    background: #c62828;
}

.keyin-btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

/* 뒤로가기 버튼 */
.keyin-back {
    margin-bottom: 16px;
}

.keyin-back a {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #666;
    text-decoration: none;
    font-size: 13px;
}

.keyin-back a:hover {
    color: #FFD369;
}

/* 카드 리스트 스타일 */
.keyin-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.keyin-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
}

.keyin-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #e0e0e0;
}

.keyin-card-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
}

.keyin-card-no {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    background: #393E46;
    color: #FFD369;
    border-radius: 50%;
    font-size: 12px;
    font-weight: 600;
}

.keyin-card-actions {
    display: flex;
    gap: 8px;
}

.keyin-card-meta {
    color: #666;
    font-size: 12px;
    margin-left: 8px;
    padding-left: 12px;
    border-left: 1px solid #ddd;
}

.keyin-card-meta code {
    background: #FFF8E1;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 11px;
}

.keyin-card-body {
    padding: 12px 16px;
}

.keyin-card-row {
    display: flex;
    gap: 20px;
}

.keyin-card-item {
    flex: 1;
    min-width: 0;
}

.keyin-card-label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: #666;
    margin-bottom: 4px;
    text-transform: uppercase;
}

.keyin-card-value {
    display: block;
    font-size: 13px;
    color: #333;
    word-break: break-all;
}

.keyin-card-value code {
    display: inline-block;
    background: #f5f5f5;
    padding: 4px 8px;
    border-radius: 4px;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 12px;
    color: #333;
    max-width: 100%;
    word-break: break-all;
}

.keyin-empty {
    text-align: center;
    padding: 40px;
    color: #999;
    font-size: 14px;
}

/* 배지 스타일 */
.badge {
    display: inline-block;
    padding: 3px 8px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 4px;
}

.badge-warning {
    background: #FFF8E1;
    color: #393E46;
}

.badge-primary {
    background: #e3f2fd;
    color: #1565c0;
}

.badge-success {
    background: #e8f5e9;
    color: #2e7d32;
}

.badge-danger {
    background: #ffebee;
    color: #c62828;
}

.badge-info {
    background: #e0f7fa;
    color: #00838f;
}

/* 대표설정 배지 */
.badge-master {
    background: #fce4ec;
    color: #c2185b;
}

/* 콤팩트 카드 */
.keyin-card.compact {
    margin-bottom: 8px;
}

.keyin-card-body.compact {
    padding: 8px 12px;
}

.compact-info {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}

.info-tag {
    font-size: 11px;
    color: #666;
    background: #f5f5f5;
    padding: 2px 6px;
    border-radius: 3px;
}

.info-tag b {
    font-weight: 600;
    color: #333;
}

.info-tag b.y {
    color: #2e7d32;
}

.info-tag b.n {
    color: #c62828;
}

.info-tag.limit {
    background: #FFF8E1;
    color: #393E46;
}

.info-tag.limit b {
    color: #FFD369;
}

.info-tag.memo {
    background: #e3f2fd;
    color: #1565c0;
    cursor: help;
}

/* 설정 요약 배지들 */
.setting-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 8px;
}

.setting-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    font-size: 11px;
    border-radius: 4px;
    background: #f5f5f5;
    color: #666;
}

.setting-badge i {
    font-size: 10px;
}

.setting-badge.badge-allow {
    background: #e8f5e9;
    color: #2e7d32;
}

.setting-badge.badge-deny {
    background: #ffebee;
    color: #c62828;
}

.setting-badge.badge-limit {
    background: #FFF8E1;
    color: #393E46;
}

.setting-badge.badge-time {
    background: #e3f2fd;
    color: #1565c0;
}

@media (max-width: 768px) {
    .config-type-selector {
        flex-direction: column;
    }

    .form-table th,
    .form-table td {
        display: block;
        width: 100%;
    }

    .form-table th {
        border-bottom: none;
        padding-bottom: 4px;
    }

    .form-table td {
        border-top: none;
        padding-top: 4px;
    }

    .form-control-inline {
        width: 100%;
    }

    .keyin-card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .keyin-card-row {
        flex-direction: column;
        gap: 12px;
    }
}
</style>

<section class="container" id="bbs">
    <section class="contents contents-bbs">
        <!-- 헤더 -->
        <div class="keyin-header">
            <div class="keyin-title">
                <i class="fa fa-key"></i>
                Keyin 설정 - <?php echo htmlspecialchars($member_info['mb_nick']); ?>
            </div>
            <div class="keyin-subtitle">
                가맹점 ID: <?php echo htmlspecialchars($mb_id); ?>
            </div>
        </div>

        <div class="bbs-cont">
            <div class="inner">
                <div class="keyin-container">
                    <!-- 뒤로가기 -->
                    <div class="keyin-back">
                        <a href="./?p=member&level=<?php echo $level; ?>&page=<?php echo $page; ?>&mb_nick=<?php echo urlencode($mb_nick); ?>&dv_tid=<?php echo urlencode($dv_tid); ?>">
                            <i class="fa fa-arrow-left"></i> 가맹점 목록으로 돌아가기
                        </a>
                    </div>

                    <!-- 등록/수정 폼 -->
                    <div class="keyin-section">
                        <h3>
                            <i class="fa fa-plus-circle"></i>
                            <?php echo $edit_data ? 'Keyin 설정 수정' : '새 Keyin 설정 추가'; ?>
                        </h3>

                        <form method="post" action="./?p=member_keyin_config&mb_id=<?php echo $mb_id; ?>&level=<?php echo $level; ?>&page=<?php echo $page; ?>&mb_nick=<?php echo urlencode($mb_nick); ?>&dv_tid=<?php echo urlencode($dv_tid); ?>">
                            <input type="hidden" name="mode" value="save">
                            <input type="hidden" name="mkc_id" value="<?php echo $edit_data['mkc_id']; ?>">

                            <!-- 설정 타입 선택 -->
                            <div class="config-type-selector">
                                <div class="config-type-option <?php echo (!$edit_data || $edit_data['mpc_id']) ? 'selected' : ''; ?>" onclick="selectConfigType('master')">
                                    <label class="option-title">
                                        <input type="radio" name="config_type" value="master" <?php echo (!$edit_data || $edit_data['mpc_id']) ? 'checked' : ''; ?>>
                                        대표가맹점 설정 사용
                                    </label>
                                    <div class="option-desc">등록된 대표가맹점 설정을 선택하여 사용합니다.</div>
                                </div>
                                <div class="config-type-option <?php echo ($edit_data && !$edit_data['mpc_id']) ? 'selected' : ''; ?>" onclick="selectConfigType('custom')">
                                    <label class="option-title">
                                        <input type="radio" name="config_type" value="custom" <?php echo ($edit_data && !$edit_data['mpc_id']) ? 'checked' : ''; ?>>
                                        개별 설정 입력
                                    </label>
                                    <div class="option-desc">이 가맹점만의 개별 API 인증 정보를 입력합니다.</div>
                                </div>
                            </div>

                            <!-- 대표가맹점 선택 패널 -->
                            <div class="master-config-panel <?php echo (!$edit_data || $edit_data['mpc_id']) ? 'active' : ''; ?>" id="masterPanel">
                                <table class="form-table">
                                    <tr>
                                        <th>대표설정 <span class="required">*</span></th>
                                        <td>
                                            <select name="mpc_id" class="form-control" id="mpc_id">
                                                <option value="">선택하세요</option>
                                                <?php
                                                while($mc = sql_fetch_array($master_configs)) {
                                                    $type_label = $mc['mpc_type'] == 'nonauth' ? '비인증' : '구인증';
                                                    $selected = ($edit_data && $edit_data['mpc_id'] == $mc['mpc_id']) ? 'selected' : '';
                                                    // PG사별 MID 표시
                                                    if($mc['mpc_pg_code'] == 'rootup') {
                                                        $display_mid = $mc['mpc_rootup_mid'];
                                                        $mid_label = 'MID';
                                                    } else if($mc['mpc_pg_code'] == 'stn') {
                                                        $display_mid = $mc['mpc_stn_mbrno'];
                                                        $mid_label = 'MBRNO';
                                                    } else {
                                                        $display_mid = $mc['mpc_mid'];
                                                        $mid_label = 'MID';
                                                    }
                                                ?>
                                                <option value="<?php echo $mc['mpc_id']; ?>" <?php echo $selected; ?>>
                                                    <?php echo $mc['mpc_pg_name']; ?> - <?php echo $type_label; ?> (<?php echo $mid_label; ?>: <?php echo $display_mid; ?>)
                                                </option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!-- 개별 설정 패널 -->
                            <div class="custom-config-panel <?php echo ($edit_data && !$edit_data['mpc_id']) ? 'active' : ''; ?>" id="customPanel">
                                <input type="hidden" name="mkc_pg_name" id="mkc_pg_name" value="<?php echo ($edit_data && !$edit_data['mpc_id']) ? $edit_data['mkc_pg_name'] : ''; ?>">
                                <table class="form-table">
                                    <tr>
                                        <th>PG사 선택 <span class="required">*</span></th>
                                        <td>
                                            <select name="mkc_pg_code" class="form-control form-control-inline" id="mkc_pg_code" onchange="setPgFields(this)">
                                                <option value="">선택하세요</option>
                                                <option value="paysis" data-name="페이시스" <?php if($edit_data && !$edit_data['mpc_id'] && $edit_data['mkc_pg_code'] == 'paysis') echo 'selected'; ?>>페이시스</option>
                                                <option value="rootup" data-name="루트업" <?php if($edit_data && !$edit_data['mpc_id'] && $edit_data['mkc_pg_code'] == 'rootup') echo 'selected'; ?>>루트업</option>
                                            </select>
                                        </td>
                                        <th>인증 타입 <span class="required">*</span></th>
                                        <td>
                                            <select name="mkc_type" class="form-control form-control-inline" id="mkc_type">
                                                <option value="">선택</option>
                                                <option value="nonauth" <?php if($edit_data && !$edit_data['mpc_id'] && $edit_data['mkc_type'] == 'nonauth') echo 'selected'; ?>>비인증</option>
                                                <option value="auth" <?php if($edit_data && !$edit_data['mpc_id'] && $edit_data['mkc_type'] == 'auth') echo 'selected'; ?>>구인증</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <!-- 페이시스 필드 (기본) -->
                                    <tr class="pg-fields paysis-fields">
                                        <th id="label_api_key">API KEY <span class="required">*</span></th>
                                        <td>
                                            <input type="text" name="mkc_api_key" id="mkc_api_key" class="form-control" placeholder="API KEY (32자)" maxlength="100" value="<?php echo ($edit_data && !$edit_data['mpc_id']) ? $edit_data['mkc_api_key'] : ''; ?>">
                                        </td>
                                        <th id="label_mid">상점 ID <span class="required">*</span></th>
                                        <td>
                                            <input type="text" name="mkc_mid" id="mkc_mid" class="form-control" placeholder="MID (10자)" maxlength="50" value="<?php echo ($edit_data && !$edit_data['mpc_id']) ? $edit_data['mkc_mid'] : ''; ?>">
                                        </td>
                                    </tr>
                                    <tr class="pg-fields paysis-fields">
                                        <th id="label_mkey">암호화 키 <span class="required">*</span></th>
                                        <td colspan="3">
                                            <input type="text" name="mkc_mkey" id="mkc_mkey" class="form-control" placeholder="암호화 키 (100자)" maxlength="200" value="<?php echo ($edit_data && !$edit_data['mpc_id']) ? $edit_data['mkc_mkey'] : ''; ?>">
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!-- 공통 설정 테이블 -->
                            <table class="form-table" style="margin-top: 16px;">
                                <tr>
                                    <th>사용여부</th>
                                    <td>
                                        <select name="mkc_use" class="form-control form-control-inline">
                                            <option value="Y" <?php if(!$edit_data || $edit_data['mkc_use'] == 'Y') echo 'selected'; ?>>사용</option>
                                            <option value="N" <?php if($edit_data && $edit_data['mkc_use'] == 'N') echo 'selected'; ?>>미사용</option>
                                        </select>
                                    </td>
                                    <th>취소가능</th>
                                    <td>
                                        <select name="mkc_cancel_yn" class="form-control form-control-inline">
                                            <option value="Y" <?php if(!$edit_data || $edit_data['mkc_cancel_yn'] == 'Y') echo 'selected'; ?>>가능</option>
                                            <option value="N" <?php if($edit_data && $edit_data['mkc_cancel_yn'] == 'N') echo 'selected'; ?>>불가</option>
                                        </select>
                                    </td>
                                    <th>중복결제</th>
                                    <td>
                                        <select name="mkc_duplicate_yn" class="form-control form-control-inline">
                                            <option value="Y" <?php if($edit_data && $edit_data['mkc_duplicate_yn'] == 'Y') echo 'selected'; ?>>허용</option>
                                            <option value="N" <?php if(!$edit_data || $edit_data['mkc_duplicate_yn'] == 'N') echo 'selected'; ?>>차단</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>주말결제</th>
                                    <td>
                                        <select name="mkc_weekend_yn" class="form-control form-control-inline">
                                            <option value="Y" <?php if(!$edit_data || $edit_data['mkc_weekend_yn'] == 'Y') echo 'selected'; ?>>허용</option>
                                            <option value="N" <?php if($edit_data && $edit_data['mkc_weekend_yn'] == 'N') echo 'selected'; ?>>차단</option>
                                        </select>
                                    </td>
                                    <th>최대할부</th>
                                    <td>
                                        <select name="mkc_max_installment" class="form-control form-control-inline">
                                            <?php
                                            $installments = array(0 => '0', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7', 8 => '8', 9 => '9', 10 => '10', 11 => '11', 12 => '12');
                                            $current_inst = $edit_data ? $edit_data['mkc_max_installment'] : 12;
                                            foreach($installments as $val => $label) {
                                                $selected = ($current_inst == $val) ? 'selected' : '';
                                                echo "<option value=\"{$val}\" {$selected}>{$label}개월</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <th>결제시간</th>
                                    <td>
                                        <?php
                                        $current_start = $edit_data ? intval(substr($edit_data['mkc_time_start'], 0, 2)) : 0;
                                        $current_end = $edit_data ? intval(substr($edit_data['mkc_time_end'], 0, 2)) : 23;
                                        ?>
                                        <select name="mkc_time_start" class="form-control form-control-inline" style="width:70px;">
                                            <?php for($h=0; $h<=23; $h++) { ?>
                                            <option value="<?php echo sprintf('%02d:00', $h); ?>" <?php if($current_start == $h) echo 'selected'; ?>><?php echo $h; ?>시</option>
                                            <?php } ?>
                                        </select>
                                        <span style="margin:0 4px;">~</span>
                                        <select name="mkc_time_end" class="form-control form-control-inline" style="width:70px;">
                                            <?php for($h=0; $h<=23; $h++) { ?>
                                            <option value="<?php echo sprintf('%02d:59', $h); ?>" <?php if($current_end == $h) echo 'selected'; ?>><?php echo $h; ?>시</option>
                                            <?php } ?>
                                        </select>
                                        <span style="margin-left:8px; font-size:11px; color:#888;">(예: ~17시 = 17:59까지)</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>1회한도</th>
                                    <td>
                                        <input type="number" name="mkc_limit_once" class="form-control" min="0" step="10000" placeholder="0=무제한" value="<?php echo $edit_data ? $edit_data['mkc_limit_once'] : 0; ?>">
                                    </td>
                                    <th>일한도</th>
                                    <td>
                                        <input type="number" name="mkc_limit_daily" class="form-control" min="0" step="10000" placeholder="0=무제한" value="<?php echo $edit_data ? $edit_data['mkc_limit_daily'] : 0; ?>">
                                    </td>
                                    <th>월한도</th>
                                    <td>
                                        <input type="number" name="mkc_limit_monthly" class="form-control" min="0" step="100000" placeholder="0=무제한" value="<?php echo $edit_data ? $edit_data['mkc_limit_monthly'] : 0; ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th>메모</th>
                                    <td colspan="5">
                                        <input type="text" name="mkc_memo" class="form-control" placeholder="관리용 메모" value="<?php echo htmlspecialchars($edit_data['mkc_memo']); ?>">
                                    </td>
                                </tr>
                            </table>

                            <div class="keyin-btn-group">
                                <button type="submit" class="keyin-btn keyin-btn-primary">
                                    <i class="fa fa-save"></i> <?php echo $edit_data ? '수정' : '등록'; ?>
                                </button>
                                <?php if($edit_data) { ?>
                                <a href="?p=member_keyin_config&mb_id=<?php echo $mb_id; ?>&level=<?php echo $level; ?>&page=<?php echo $page; ?>&mb_nick=<?php echo urlencode($mb_nick); ?>&dv_tid=<?php echo urlencode($dv_tid); ?>" class="keyin-btn keyin-btn-secondary">취소</a>
                                <?php } ?>
                            </div>
                        </form>
                    </div>

                    <!-- 등록된 설정 목록 -->
                    <div class="keyin-section">
                        <h3>
                            <i class="fa fa-list"></i>
                            등록된 Keyin 설정 목록
                        </h3>

                        <div class="keyin-list">
                            <?php
                            $num = 0;
                            while($row = sql_fetch_array($list)) {
                                $num++;
                                $type_badge = $row['mkc_type'] == 'nonauth' ? '<span class="badge badge-warning">비인증</span>' : '<span class="badge badge-primary">구인증</span>';
                                $use_badge = $row['mkc_use'] == 'Y' ? '<span class="badge badge-success">사용</span>' : '<span class="badge badge-danger">미사용</span>';

                                // 대표설정 사용 여부
                                $is_master = !empty($row['mpc_id']);
                                $master_badge = $is_master ? '<span class="badge badge-master">대표설정</span>' : '<span class="badge badge-info">개별설정</span>';

                                // 대표설정 사용시 실제 값 조회
                                if($is_master) {
                                    $master_data = sql_fetch("SELECT * FROM g5_manual_payment_config WHERE mpc_id = '{$row['mpc_id']}'");
                                    // PG사별 필드 매핑
                                    if($master_data['mpc_pg_code'] == 'rootup') {
                                        $display_api_key = $master_data['mpc_rootup_key']; // 결제KEY
                                        $display_mid = $master_data['mpc_rootup_mid'];
                                        $display_mkey = $master_data['mpc_rootup_tid']; // TID
                                    } else if($master_data['mpc_pg_code'] == 'stn') {
                                        $display_api_key = $master_data['mpc_stn_apikey'];
                                        $display_mid = $master_data['mpc_stn_mbrno'];
                                        $display_mkey = '-'; // 섹타나인은 mkey 없음
                                    } else {
                                        $display_api_key = $master_data['mpc_api_key'];
                                        $display_mid = $master_data['mpc_mid'];
                                        $display_mkey = $master_data['mpc_mkey'];
                                    }
                                } else {
                                    $display_api_key = $row['mkc_api_key'];
                                    $display_mid = $row['mkc_mid'];
                                    $display_mkey = $row['mkc_mkey'];
                                }
                            ?>
                            <div class="keyin-card compact">
                                <div class="keyin-card-header">
                                    <div class="keyin-card-title">
                                        <span class="keyin-card-no"><?php echo $num; ?></span>
                                        <strong><?php echo $row['mkc_pg_name']; ?></strong>
                                        <?php echo $type_badge; ?>
                                        <?php echo $master_badge; ?>
                                        <?php echo $use_badge; ?>
                                        <?php if($row['mkc_oid']) { ?>
                                        <span class="keyin-card-meta">OID: <code style="background:#fce4ec; color:#c2185b;"><?php echo $row['mkc_oid']; ?></code></span>
                                        <?php } ?>
                                        <span class="keyin-card-meta">MID: <code><?php echo $display_mid; ?></code></span>
                                    </div>
                                    <div class="keyin-card-actions">
                                        <a href="?p=member_keyin_config&mb_id=<?php echo $mb_id; ?>&mkc_id=<?php echo $row['mkc_id']; ?>&level=<?php echo $level; ?>&page=<?php echo $page; ?>&mb_nick=<?php echo urlencode($mb_nick); ?>&dv_tid=<?php echo urlencode($dv_tid); ?>" class="keyin-btn keyin-btn-sm keyin-btn-secondary">수정</a>
                                        <a href="?p=member_keyin_config&mb_id=<?php echo $mb_id; ?>&mode=delete&mkc_id=<?php echo $row['mkc_id']; ?>&level=<?php echo $level; ?>&page=<?php echo $page; ?>&mb_nick=<?php echo urlencode($mb_nick); ?>&dv_tid=<?php echo urlencode($dv_tid); ?>" class="keyin-btn keyin-btn-sm keyin-btn-danger" onclick="return confirm('정말 삭제하시겠습니까?');">삭제</a>
                                    </div>
                                </div>
                                <div class="keyin-card-body compact">
                                    <!-- 콤팩트 설정 표시 -->
                                    <div class="compact-info">
                                        <?php
                                        // Y/N 표시
                                        $cancel_cls = $row['mkc_cancel_yn'] == 'Y' ? 'y' : 'n';
                                        $dup_cls = $row['mkc_duplicate_yn'] == 'Y' ? 'y' : 'n';
                                        $weekend_cls = $row['mkc_weekend_yn'] == 'Y' ? 'y' : 'n';
                                        $time_start = substr($row['mkc_time_start'], 0, 2);
                                        $time_end = substr($row['mkc_time_end'], 0, 2);
                                        ?>
                                        <span class="info-tag">취소:<b class="<?php echo $cancel_cls; ?>"><?php echo $row['mkc_cancel_yn']; ?></b></span>
                                        <span class="info-tag">중복:<b class="<?php echo $dup_cls; ?>"><?php echo $row['mkc_duplicate_yn']; ?></b></span>
                                        <span class="info-tag">주말:<b class="<?php echo $weekend_cls; ?>"><?php echo $row['mkc_weekend_yn']; ?></b></span>
                                        <span class="info-tag">할부:<b><?php echo $row['mkc_max_installment']; ?>개월</b></span>
                                        <span class="info-tag">시간:<b><?php echo intval($time_start); ?>~<?php echo intval($time_end); ?>시</b></span>
                                        <?php if($row['mkc_limit_once'] > 0) { ?>
                                        <span class="info-tag limit">1회:<b><?php echo number_format($row['mkc_limit_once']/10000); ?>만</b></span>
                                        <?php } ?>
                                        <?php if($row['mkc_limit_daily'] > 0) { ?>
                                        <span class="info-tag limit">일:<b><?php echo number_format($row['mkc_limit_daily']/10000); ?>만</b></span>
                                        <?php } ?>
                                        <?php if($row['mkc_limit_monthly'] > 0) { ?>
                                        <span class="info-tag limit">월:<b><?php echo number_format($row['mkc_limit_monthly']/10000); ?>만</b></span>
                                        <?php } ?>
                                        <?php if($row['mkc_memo']) { ?>
                                        <span class="info-tag memo" title="<?php echo htmlspecialchars($row['mkc_memo']); ?>"><i class="fa fa-comment"></i></span>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <?php if($num == 0) { ?>
                            <div class="keyin-empty">
                                등록된 Keyin 설정이 없습니다.
                            </div>
                            <?php } ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</section>

<script>
function selectConfigType(type) {
    // 라디오 버튼 체크
    document.querySelectorAll('input[name="config_type"]').forEach(function(radio) {
        radio.checked = (radio.value === type);
    });

    // 옵션 박스 스타일
    document.querySelectorAll('.config-type-option').forEach(function(option) {
        option.classList.remove('selected');
    });
    event.currentTarget.classList.add('selected');

    // 패널 표시/숨김
    if(type === 'master') {
        document.getElementById('masterPanel').classList.add('active');
        document.getElementById('customPanel').classList.remove('active');
    } else {
        document.getElementById('masterPanel').classList.remove('active');
        document.getElementById('customPanel').classList.add('active');
    }
}

// PG사별 필드 설정
var pgFieldConfig = {
    'paysis': {
        api_key: { label: 'API KEY', placeholder: 'API KEY (32자)' },
        mid: { label: '상점 ID', placeholder: 'MID (10자)' },
        mkey: { label: '암호화 키', placeholder: '암호화 키 (100자)' }
    },
    'rootup': {
        api_key: { label: '결제 KEY', placeholder: '결제 KEY' },
        mid: { label: 'MID', placeholder: 'MID' },
        mkey: { label: 'TID', placeholder: 'TID' }
    }
};

function setPgFields(select) {
    var pgCode = select.value;
    var selectedOption = select.options[select.selectedIndex];
    var pgName = selectedOption.getAttribute('data-name') || '';

    // PG사 이름 설정
    document.getElementById('mkc_pg_name').value = pgName;

    // PG사별 필드 라벨 및 placeholder 변경
    if(pgCode && pgFieldConfig[pgCode]) {
        var config = pgFieldConfig[pgCode];

        // API KEY / 결제 KEY
        document.getElementById('label_api_key').innerHTML = config.api_key.label + ' <span class="required">*</span>';
        document.getElementById('mkc_api_key').placeholder = config.api_key.placeholder;

        // 상점 ID / MID
        document.getElementById('label_mid').innerHTML = config.mid.label + ' <span class="required">*</span>';
        document.getElementById('mkc_mid').placeholder = config.mid.placeholder;

        // 암호화 키 / TID
        document.getElementById('label_mkey').innerHTML = config.mkey.label + ' <span class="required">*</span>';
        document.getElementById('mkc_mkey').placeholder = config.mkey.placeholder;
    }
}

// 페이지 로드 시 현재 선택된 PG사에 맞게 필드 설정
document.addEventListener('DOMContentLoaded', function() {
    var pgSelect = document.getElementById('mkc_pg_code');
    if(pgSelect && pgSelect.value) {
        setPgFields(pgSelect);
    }
});
</script>

<?php
include_once('./_tail.php');
?>
