<?php
include_once('./_common.php');

// 관리자 권한 체크
if(!$is_admin) {
    alert("관리자만 접근할 수 있습니다.");
}

$title1 = "수기 대표가맹점 설정";
$title2 = "수기 대표가맹점 설정";

// 테이블 존재 여부 확인 및 생성
$table_name = "g5_manual_payment_config";
$check_table = sql_query("SHOW TABLES LIKE '{$table_name}'");
if(sql_num_rows($check_table) == 0) {
    // 테이블 생성
    $create_sql = "CREATE TABLE `{$table_name}` (
        `mpc_id` int(11) NOT NULL AUTO_INCREMENT,
        `mpc_pg_code` varchar(20) NOT NULL COMMENT 'PG사 코드',
        `mpc_pg_name` varchar(50) NOT NULL COMMENT 'PG사 이름',
        `mpc_type` varchar(20) NOT NULL COMMENT '인증타입 (nonauth/auth)',
        `mpc_api_key` varchar(100) NOT NULL COMMENT 'API KEY',
        `mpc_mid` varchar(20) NOT NULL COMMENT '상점 ID',
        `mpc_mkey` varchar(200) NOT NULL COMMENT '암호화 키',
        `mpc_use` enum('Y','N') DEFAULT 'Y' COMMENT '사용여부',
        `mpc_memo` text COMMENT '메모',
        `mpc_status` enum('active','deleted') DEFAULT 'active' COMMENT '상태 (active/deleted)',
        `mpc_datetime` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '등록일시',
        `mpc_update` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일시',
        PRIMARY KEY (`mpc_id`),
        KEY `idx_pg_code` (`mpc_pg_code`),
        KEY `idx_type` (`mpc_type`),
        KEY `idx_status` (`mpc_status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='수기결제 PG 설정'";
    sql_query($create_sql);
}

// mpc_status 컬럼 추가 (마이그레이션)
$check_status_column = sql_query("SHOW COLUMNS FROM `{$table_name}` LIKE 'mpc_status'");
if(sql_num_rows($check_status_column) == 0) {
    sql_query("ALTER TABLE `{$table_name}` ADD COLUMN `mpc_status` enum('active','deleted') DEFAULT 'active' COMMENT '상태 (active/deleted)' AFTER `mpc_memo`");
    sql_query("ALTER TABLE `{$table_name}` ADD KEY `idx_status` (`mpc_status`)");
}

// 루트업 전용 컬럼 추가 (마이그레이션)
$check_rootup_column = sql_query("SHOW COLUMNS FROM `{$table_name}` LIKE 'mpc_rootup_mid'");
if(sql_num_rows($check_rootup_column) == 0) {
    sql_query("ALTER TABLE `{$table_name}` ADD COLUMN `mpc_rootup_mid` varchar(20) DEFAULT NULL COMMENT '루트업 MID' AFTER `mpc_mkey`");
    sql_query("ALTER TABLE `{$table_name}` ADD COLUMN `mpc_rootup_tid` varchar(20) DEFAULT NULL COMMENT '루트업 TID' AFTER `mpc_rootup_mid`");
    sql_query("ALTER TABLE `{$table_name}` ADD COLUMN `mpc_rootup_key` varchar(100) DEFAULT NULL COMMENT '루트업 결제KEY' AFTER `mpc_rootup_tid`");
}

// 섹타나인 전용 컬럼 추가 (마이그레이션)
$check_stn_column = sql_query("SHOW COLUMNS FROM `{$table_name}` LIKE 'mpc_stn_mbrno'");
if(sql_num_rows($check_stn_column) == 0) {
    sql_query("ALTER TABLE `{$table_name}` ADD COLUMN `mpc_stn_mbrno` varchar(20) DEFAULT NULL COMMENT '섹타나인 회원번호' AFTER `mpc_rootup_key`");
    sql_query("ALTER TABLE `{$table_name}` ADD COLUMN `mpc_stn_apikey` varchar(100) DEFAULT NULL COMMENT '섹타나인 API키' AFTER `mpc_stn_mbrno`");
}

// 윈글로벌 전용 컬럼 추가 (마이그레이션)
$check_winglobal_column = sql_query("SHOW COLUMNS FROM `{$table_name}` LIKE 'mpc_winglobal_tid'");
if(sql_num_rows($check_winglobal_column) == 0) {
    sql_query("ALTER TABLE `{$table_name}` ADD COLUMN `mpc_winglobal_tid` varchar(20) DEFAULT NULL COMMENT '윈글로벌 TID' AFTER `mpc_stn_apikey`");
    sql_query("ALTER TABLE `{$table_name}` ADD COLUMN `mpc_winglobal_apikey` varchar(100) DEFAULT NULL COMMENT '윈글로벌 API키' AFTER `mpc_winglobal_tid`");
}

// 저장 처리
if($_POST['mode'] == 'save') {
    $mpc_id = (int)$_POST['mpc_id'];
    $mpc_pg_code = sql_escape_string($_POST['mpc_pg_code']);
    $mpc_pg_name = sql_escape_string($_POST['mpc_pg_name']);
    $mpc_type = sql_escape_string($_POST['mpc_type']);
    $mpc_use = sql_escape_string($_POST['mpc_use']);
    $mpc_memo = sql_escape_string($_POST['mpc_memo']);

    // 페이시스 전용 필드
    $mpc_api_key = sql_escape_string($_POST['mpc_api_key']);
    $mpc_mid = sql_escape_string($_POST['mpc_mid']);
    $mpc_mkey = sql_escape_string($_POST['mpc_mkey']);

    // 루트업 전용 필드
    $mpc_rootup_mid = sql_escape_string($_POST['mpc_rootup_mid']);
    $mpc_rootup_tid = sql_escape_string($_POST['mpc_rootup_tid']);
    $mpc_rootup_key = sql_escape_string($_POST['mpc_rootup_key']);

    // 섹타나인 전용 필드
    $mpc_stn_mbrno = sql_escape_string($_POST['mpc_stn_mbrno']);
    $mpc_stn_apikey = sql_escape_string($_POST['mpc_stn_apikey']);

    // 윈글로벌 전용 필드
    $mpc_winglobal_tid = sql_escape_string($_POST['mpc_winglobal_tid']);
    $mpc_winglobal_apikey = sql_escape_string($_POST['mpc_winglobal_apikey']);

    if($mpc_id > 0) {
        // 수정
        $sql = "UPDATE {$table_name} SET
            mpc_pg_code = '{$mpc_pg_code}',
            mpc_pg_name = '{$mpc_pg_name}',
            mpc_type = '{$mpc_type}',
            mpc_api_key = '{$mpc_api_key}',
            mpc_mid = '{$mpc_mid}',
            mpc_mkey = '{$mpc_mkey}',
            mpc_rootup_mid = '{$mpc_rootup_mid}',
            mpc_rootup_tid = '{$mpc_rootup_tid}',
            mpc_rootup_key = '{$mpc_rootup_key}',
            mpc_stn_mbrno = '{$mpc_stn_mbrno}',
            mpc_stn_apikey = '{$mpc_stn_apikey}',
            mpc_winglobal_tid = '{$mpc_winglobal_tid}',
            mpc_winglobal_apikey = '{$mpc_winglobal_apikey}',
            mpc_use = '{$mpc_use}',
            mpc_memo = '{$mpc_memo}'
            WHERE mpc_id = {$mpc_id}";
        sql_query($sql);
        $msg = "수정되었습니다.";
    } else {
        // 신규 등록
        $sql = "INSERT INTO {$table_name}
            (mpc_pg_code, mpc_pg_name, mpc_type, mpc_api_key, mpc_mid, mpc_mkey, mpc_rootup_mid, mpc_rootup_tid, mpc_rootup_key, mpc_stn_mbrno, mpc_stn_apikey, mpc_winglobal_tid, mpc_winglobal_apikey, mpc_use, mpc_memo)
            VALUES
            ('{$mpc_pg_code}', '{$mpc_pg_name}', '{$mpc_type}', '{$mpc_api_key}', '{$mpc_mid}', '{$mpc_mkey}', '{$mpc_rootup_mid}', '{$mpc_rootup_tid}', '{$mpc_rootup_key}', '{$mpc_stn_mbrno}', '{$mpc_stn_apikey}', '{$mpc_winglobal_tid}', '{$mpc_winglobal_apikey}', '{$mpc_use}', '{$mpc_memo}')";
        sql_query($sql);
        $msg = "등록되었습니다.";
    }

    echo "<script>alert('{$msg}'); location.href='?p=manual_payment_config';</script>";
    exit;
}

// 삭제 처리 (소프트 삭제 - status를 deleted로 변경)
if($_GET['mode'] == 'delete') {
    $mpc_id = (int)$_GET['mpc_id'];
    if($mpc_id > 0) {
        sql_query("UPDATE {$table_name} SET mpc_status = 'deleted' WHERE mpc_id = {$mpc_id}");
        echo "<script>alert('삭제되었습니다.'); location.href='?p=manual_payment_config';</script>";
        exit;
    }
}

// 수정할 데이터 조회
$edit_data = null;
if($_GET['mpc_id']) {
    $mpc_id = (int)$_GET['mpc_id'];
    $edit_data = sql_fetch("SELECT * FROM {$table_name} WHERE mpc_id = {$mpc_id}");
}

// 목록 조회 (삭제되지 않은 항목만)
$list = sql_query("SELECT * FROM {$table_name} WHERE mpc_status = 'active' ORDER BY mpc_pg_code, mpc_type");

include_once('./_head.php');
?>

<style>
.config-container {
    padding: 0;
}

.config-header {
    background: linear-gradient(135deg, #393E46 0%, #4a5058 100%);
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(57, 62, 70, 0.3);
}

.config-title {
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.config-title i {
    font-size: 16px;
}

.config-section {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #e0e0e0;
}

.config-section h3 {
    font-size: 15px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 16px;
    padding-bottom: 10px;
    border-bottom: 2px solid #FFD369;
    display: flex;
    align-items: center;
    gap: 8px;
}

.config-section h3 i {
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
    font-size: 14px;
    border: 1px solid #ddd;
    border-radius: 6px;
    transition: border-color 0.2s;
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

.config-btn-group {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.config-btn {
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

.config-btn-primary {
    background: linear-gradient(135deg, #393E46 0%, #4a5058 100%);
    color: #FFD369 !important;
}

.config-btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 211, 105, 0.3);
}

.config-btn-secondary {
    background: #f5f5f5;
    color: #666 !important;
    border: 1px solid #ddd;
}

.config-btn-secondary:hover {
    background: #eee;
}

.config-btn-danger {
    background: #e53935;
    color: #fff !important;
}

.config-btn-danger:hover {
    background: #c62828;
}

.config-btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

/* 카드 리스트 스타일 */
.config-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.config-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
}

.config-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #e0e0e0;
}

.config-card-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
}

.config-card-no {
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

.config-card-actions {
    display: flex;
    gap: 8px;
}

.config-card-meta {
    color: #666;
    font-size: 12px;
    margin-left: 8px;
    padding-left: 12px;
    border-left: 1px solid #ddd;
}

.config-card-meta code {
    background: #FFF8E1;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 11px;
}

.config-card-body {
    padding: 16px;
}

.config-card-body.compact {
    padding: 12px 16px;
}

.config-card-row {
    display: flex;
    gap: 20px;
    margin-bottom: 12px;
}

.config-card-row:last-child {
    margin-bottom: 0;
}

.config-card-item {
    flex: 1;
    min-width: 0;
}

.config-card-item.full {
    flex: 1 1 100%;
}

.config-card-label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: #666;
    margin-bottom: 4px;
    text-transform: uppercase;
}

.config-card-value {
    display: block;
    font-size: 13px;
    color: #333;
    word-break: break-all;
}

.config-card-value code {
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

.config-card-value.memo {
    background: #fffde7;
    padding: 8px;
    border-radius: 4px;
    font-size: 12px;
    color: #666;
}

.config-empty {
    text-align: center;
    padding: 40px;
    color: #999;
    font-size: 14px;
}

@media (max-width: 768px) {
    .config-card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .config-card-row {
        flex-direction: column;
        gap: 12px;
    }
}

/* 테이블 스타일 (기존 유지) */
.config-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.config-table th {
    background: #f8f9fa;
    padding: 12px 10px;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #FFD369;
}

.config-table td {
    padding: 12px 10px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.config-table tr:hover {
    background: #f8f9ff;
}

.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.badge-primary {
    background: #e3f2fd;
    color: #1976d2;
}

.badge-warning {
    background: #fff3e0;
    color: #f57c00;
}

.badge-success {
    background: #e8f5e9;
    color: #388e3c;
}

.badge-danger {
    background: #ffebee;
    color: #c62828;
}

.action-btns {
    display: flex;
    gap: 6px;
}

.text-muted {
    color: #999;
    font-size: 12px;
}

.api-key-mask {
    font-family: monospace;
    color: #666;
}

@media (max-width: 768px) {
    .config-container {
        padding: 10px;
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

    .config-table {
        font-size: 12px;
    }

    .config-table th,
    .config-table td {
        padding: 8px 6px;
    }
}
</style>

<section class="container" id="bbs">
    <section class="contents contents-bbs">
        <!-- 헤더 -->
        <div class="config-header">
            <div class="config-title">
                <i class="fa fa-cog"></i>
                수기 대표가맹점 설정
            </div>
        </div>

        <div class="bbs-cont">
            <div class="inner">
                <div class="config-container">
                    <!-- 등록/수정 폼 -->
                    <div class="config-section">
                        <h3>
                            <i class="fa fa-plus-circle"></i>
                            <?php echo $edit_data ? 'PG 설정 수정' : 'PG 설정 등록'; ?>
                        </h3>

                        <form method="post" action="?p=manual_payment_config">
                            <input type="hidden" name="mode" value="save">
                            <input type="hidden" name="mpc_id" value="<?php echo $edit_data['mpc_id']; ?>">
                            <input type="hidden" name="mpc_pg_name" id="mpc_pg_name" value="<?php echo $edit_data['mpc_pg_name']; ?>">

                            <table class="form-table">
                                <tr>
                                    <th>PG사 선택 <span class="required">*</span></th>
                                    <td>
                                        <select name="mpc_pg_code" id="mpc_pg_code" class="form-control form-control-inline" required onchange="setPgName(this); togglePgFields(this.value);">
                                            <option value="">선택하세요</option>
                                            <option value="paysis" data-name="페이시스" <?php if($edit_data['mpc_pg_code'] == 'paysis') echo 'selected'; ?>>페이시스</option>
                                            <option value="rootup" data-name="루트업" <?php if($edit_data['mpc_pg_code'] == 'rootup') echo 'selected'; ?>>루트업</option>
                                            <option value="stn" data-name="섹타나인" <?php if($edit_data['mpc_pg_code'] == 'stn') echo 'selected'; ?>>섹타나인</option>
                                            <option value="winglobal" data-name="윈글로벌" <?php if($edit_data['mpc_pg_code'] == 'winglobal') echo 'selected'; ?>>윈글로벌</option>
                                        </select>
                                    </td>
                                    <th>인증 타입 <span class="required">*</span></th>
                                    <td>
                                        <select name="mpc_type" class="form-control form-control-inline" required>
                                            <option value="">선택</option>
                                            <option value="nonauth" <?php if($edit_data['mpc_type'] == 'nonauth') echo 'selected'; ?>>비인증</option>
                                            <option value="auth" <?php if($edit_data['mpc_type'] == 'auth') echo 'selected'; ?>>구인증</option>
                                        </select>
                                    </td>
                                    <th>사용여부</th>
                                    <td>
                                        <select name="mpc_use" class="form-control form-control-inline">
                                            <option value="Y" <?php if($edit_data['mpc_use'] != 'N') echo 'selected'; ?>>사용</option>
                                            <option value="N" <?php if($edit_data['mpc_use'] == 'N') echo 'selected'; ?>>미사용</option>
                                        </select>
                                    </td>
                                </tr>
                                <!-- 페이시스 전용 필드 -->
                                <tr class="pg-fields paysis-fields" style="<?php echo ($edit_data && $edit_data['mpc_pg_code'] != 'paysis') ? 'display:none;' : ''; ?>">
                                    <th>API KEY <span class="required">*</span></th>
                                    <td>
                                        <input type="text" name="mpc_api_key" id="mpc_api_key" class="form-control" value="<?php echo $edit_data['mpc_api_key']; ?>" placeholder="API KEY (32자)" maxlength="100">
                                    </td>
                                    <th>상점 ID <span class="required">*</span></th>
                                    <td>
                                        <input type="text" name="mpc_mid" id="mpc_mid" class="form-control" value="<?php echo $edit_data['mpc_mid']; ?>" placeholder="MID (10자)" maxlength="20">
                                    </td>
                                    <th>암호화 키 <span class="required">*</span></th>
                                    <td>
                                        <input type="text" name="mpc_mkey" id="mpc_mkey" class="form-control" value="<?php echo $edit_data['mpc_mkey']; ?>" placeholder="MKEY (100자)" maxlength="200">
                                    </td>
                                </tr>
                                <!-- 루트업 전용 필드 -->
                                <tr class="pg-fields rootup-fields" style="<?php echo (!$edit_data || $edit_data['mpc_pg_code'] != 'rootup') ? 'display:none;' : ''; ?>">
                                    <th>MID <span class="required">*</span></th>
                                    <td>
                                        <input type="text" name="mpc_rootup_mid" id="mpc_rootup_mid" class="form-control" value="<?php echo $edit_data['mpc_rootup_mid']; ?>" placeholder="MID" maxlength="20">
                                    </td>
                                    <th>TID <span class="required">*</span></th>
                                    <td>
                                        <input type="text" name="mpc_rootup_tid" id="mpc_rootup_tid" class="form-control" value="<?php echo $edit_data['mpc_rootup_tid']; ?>" placeholder="TID" maxlength="20">
                                    </td>
                                    <th>결제KEY <span class="required">*</span></th>
                                    <td>
                                        <input type="text" name="mpc_rootup_key" id="mpc_rootup_key" class="form-control" value="<?php echo $edit_data['mpc_rootup_key']; ?>" placeholder="결제KEY" maxlength="100">
                                    </td>
                                </tr>
                                <!-- 섹타나인 전용 필드 -->
                                <tr class="pg-fields stn-fields" style="<?php echo (!$edit_data || $edit_data['mpc_pg_code'] != 'stn') ? 'display:none;' : ''; ?>">
                                    <th>회원번호 <span class="required">*</span></th>
                                    <td>
                                        <input type="text" name="mpc_stn_mbrno" id="mpc_stn_mbrno" class="form-control" value="<?php echo $edit_data['mpc_stn_mbrno']; ?>" placeholder="mbrNo" maxlength="20">
                                    </td>
                                    <th>API KEY <span class="required">*</span></th>
                                    <td colspan="3">
                                        <input type="text" name="mpc_stn_apikey" id="mpc_stn_apikey" class="form-control" value="<?php echo $edit_data['mpc_stn_apikey']; ?>" placeholder="apiKey" maxlength="100">
                                    </td>
                                </tr>
                                <!-- 윈글로벌 전용 필드 -->
                                <tr class="pg-fields winglobal-fields" style="<?php echo (!$edit_data || $edit_data['mpc_pg_code'] != 'winglobal') ? 'display:none;' : ''; ?>">
                                    <th>TID <span class="required">*</span></th>
                                    <td>
                                        <input type="text" name="mpc_winglobal_tid" id="mpc_winglobal_tid" class="form-control" value="<?php echo $edit_data['mpc_winglobal_tid']; ?>" placeholder="TID" maxlength="20">
                                    </td>
                                    <th>API KEY <span class="required">*</span></th>
                                    <td colspan="3">
                                        <input type="text" name="mpc_winglobal_apikey" id="mpc_winglobal_apikey" class="form-control" value="<?php echo $edit_data['mpc_winglobal_apikey']; ?>" placeholder="API KEY" maxlength="100">
                                    </td>
                                </tr>
                                <tr>
                                    <th>메모</th>
                                    <td colspan="5">
                                        <textarea name="mpc_memo" class="form-control" rows="2" placeholder="관리용 메모 (선택사항)"><?php echo $edit_data['mpc_memo']; ?></textarea>
                                    </td>
                                </tr>
                            </table>

                            <div class="config-btn-group">
                                <button type="submit" class="config-btn config-btn-primary">
                                    <i class="fa fa-save"></i> <?php echo $edit_data ? '수정' : '등록'; ?>
                                </button>
                                <?php if($edit_data) { ?>
                                <a href="?p=manual_payment_config" class="config-btn config-btn-secondary">취소</a>
                                <?php } ?>
                            </div>
                        </form>
                    </div>

                    <!-- 목록 -->
                    <div class="config-section">
                        <h3>
                            <i class="fa fa-list"></i>
                            등록된 PG 설정 목록
                        </h3>

                        <div class="config-list">
                            <?php
                            $num = 0;
                            while($row = sql_fetch_array($list)) {
                                $num++;
                                $type_badge = $row['mpc_type'] == 'nonauth' ? '<span class="badge badge-warning">비인증</span>' : '<span class="badge badge-primary">구인증</span>';
                                $use_badge = $row['mpc_use'] == 'Y' ? '<span class="badge badge-success">사용</span>' : '<span class="badge badge-danger">미사용</span>';

                                // PG사별 MID 표시
                                if($row['mpc_pg_code'] == 'rootup') {
                                    $display_mid = $row['mpc_rootup_mid'];
                                } else if($row['mpc_pg_code'] == 'stn') {
                                    $display_mid = $row['mpc_stn_mbrno'];
                                } else if($row['mpc_pg_code'] == 'winglobal') {
                                    $display_mid = $row['mpc_winglobal_tid'];
                                } else {
                                    $display_mid = $row['mpc_mid'];
                                }
                            ?>
                            <div class="config-card">
                                <div class="config-card-header">
                                    <div class="config-card-title">
                                        <span class="config-card-no"><?php echo $num; ?></span>
                                        <strong><?php echo $row['mpc_pg_name']; ?></strong>
                                        <?php echo $type_badge; ?>
                                        <?php echo $use_badge; ?>
                                        <span class="config-card-meta">MID: <code><?php echo $display_mid; ?></code></span>
                                    </div>
                                    <div class="config-card-actions">
                                        <a href="?p=manual_payment_config&mpc_id=<?php echo $row['mpc_id']; ?>" class="config-btn config-btn-sm config-btn-secondary">수정</a>
                                        <a href="?p=manual_payment_config&mode=delete&mpc_id=<?php echo $row['mpc_id']; ?>" class="config-btn config-btn-sm config-btn-danger" onclick="return confirm('정말 삭제하시겠습니까?');">삭제</a>
                                    </div>
                                </div>
                                <div class="config-card-body compact">
                                    <?php if($row['mpc_pg_code'] == 'rootup') { ?>
                                    <!-- 루트업 필드 표시 -->
                                    <div class="config-card-row">
                                        <div class="config-card-item">
                                            <span class="config-card-label">MID</span>
                                            <span class="config-card-value"><code><?php echo $row['mpc_rootup_mid']; ?></code></span>
                                        </div>
                                        <div class="config-card-item">
                                            <span class="config-card-label">TID</span>
                                            <span class="config-card-value"><code><?php echo $row['mpc_rootup_tid']; ?></code></span>
                                        </div>
                                        <div class="config-card-item">
                                            <span class="config-card-label">결제KEY</span>
                                            <span class="config-card-value"><code><?php echo $row['mpc_rootup_key']; ?></code></span>
                                        </div>
                                    </div>
                                    <?php } else if($row['mpc_pg_code'] == 'stn') { ?>
                                    <!-- 섹타나인 필드 표시 -->
                                    <div class="config-card-row">
                                        <div class="config-card-item">
                                            <span class="config-card-label">회원번호 (mbrNo)</span>
                                            <span class="config-card-value"><code><?php echo $row['mpc_stn_mbrno']; ?></code></span>
                                        </div>
                                        <div class="config-card-item">
                                            <span class="config-card-label">API KEY</span>
                                            <span class="config-card-value"><code><?php echo $row['mpc_stn_apikey']; ?></code></span>
                                        </div>
                                    </div>
                                    <?php } else if($row['mpc_pg_code'] == 'winglobal') { ?>
                                    <!-- 윈글로벌 필드 표시 -->
                                    <div class="config-card-row">
                                        <div class="config-card-item">
                                            <span class="config-card-label">TID</span>
                                            <span class="config-card-value"><code><?php echo $row['mpc_winglobal_tid']; ?></code></span>
                                        </div>
                                        <div class="config-card-item">
                                            <span class="config-card-label">API KEY</span>
                                            <span class="config-card-value"><code><?php echo $row['mpc_winglobal_apikey']; ?></code></span>
                                        </div>
                                    </div>
                                    <?php } else { ?>
                                    <!-- 페이시스 필드 표시 -->
                                    <div class="config-card-row">
                                        <div class="config-card-item">
                                            <span class="config-card-label">API KEY</span>
                                            <span class="config-card-value"><code><?php echo $row['mpc_api_key']; ?></code></span>
                                        </div>
                                        <div class="config-card-item">
                                            <span class="config-card-label">MKEY</span>
                                            <span class="config-card-value"><code><?php echo $row['mpc_mkey']; ?></code></span>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php } ?>
                            <?php if($num == 0) { ?>
                            <div class="config-empty">
                                등록된 PG 설정이 없습니다.
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
function setPgName(select) {
    var selectedOption = select.options[select.selectedIndex];
    var pgName = selectedOption.getAttribute('data-name') || '';
    document.getElementById('mpc_pg_name').value = pgName;
}

function togglePgFields(pgCode) {
    // 모든 PG 필드 숨기기
    document.querySelectorAll('.pg-fields').forEach(function(el) {
        el.style.display = 'none';
    });

    // 모든 필드 required 해제
    document.getElementById('mpc_api_key').required = false;
    document.getElementById('mpc_mid').required = false;
    document.getElementById('mpc_mkey').required = false;
    document.getElementById('mpc_rootup_mid').required = false;
    document.getElementById('mpc_rootup_tid').required = false;
    document.getElementById('mpc_rootup_key').required = false;
    document.getElementById('mpc_stn_mbrno').required = false;
    document.getElementById('mpc_stn_apikey').required = false;
    document.getElementById('mpc_winglobal_tid').required = false;
    document.getElementById('mpc_winglobal_apikey').required = false;

    // 선택한 PG 필드만 표시
    if(pgCode === 'paysis') {
        document.querySelectorAll('.paysis-fields').forEach(function(el) {
            el.style.display = '';
        });
        // 페이시스 필드 required 설정
        document.getElementById('mpc_api_key').required = true;
        document.getElementById('mpc_mid').required = true;
        document.getElementById('mpc_mkey').required = true;
    } else if(pgCode === 'rootup') {
        document.querySelectorAll('.rootup-fields').forEach(function(el) {
            el.style.display = '';
        });
        // 루트업 필드 required 설정
        document.getElementById('mpc_rootup_mid').required = true;
        document.getElementById('mpc_rootup_tid').required = true;
        document.getElementById('mpc_rootup_key').required = true;
    } else if(pgCode === 'stn') {
        document.querySelectorAll('.stn-fields').forEach(function(el) {
            el.style.display = '';
        });
        // 섹타나인 필드 required 설정
        document.getElementById('mpc_stn_mbrno').required = true;
        document.getElementById('mpc_stn_apikey').required = true;
    } else if(pgCode === 'winglobal') {
        document.querySelectorAll('.winglobal-fields').forEach(function(el) {
            el.style.display = '';
        });
        // 윈글로벌 필드 required 설정
        document.getElementById('mpc_winglobal_tid').required = true;
        document.getElementById('mpc_winglobal_apikey').required = true;
    }
}

// 페이지 로드 시 현재 선택된 PG에 맞게 필드 표시
document.addEventListener('DOMContentLoaded', function() {
    var pgSelect = document.getElementById('mpc_pg_code');
    if(pgSelect && pgSelect.value) {
        togglePgFields(pgSelect.value);
    }
});
</script>

<?php
include_once('./_tail.php');
?>
