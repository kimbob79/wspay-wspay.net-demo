<?php
$title1 = "시스템설정";
$title2 = "결제통보 설정";

include_once('./_common.php');

// 관리자만 접근
if (!$is_admin) {
    alert_close("관리자만 접근 가능합니다.");
}

$wh_id = isset($_GET['wh_id']) ? intval($_GET['wh_id']) : 0;
$preset_mb_id = isset($_GET['mb_id']) ? trim($_GET['mb_id']) : '';
$return_url = isset($_GET['return']) ? trim($_GET['return']) : '';

// mb_id로 기존 설정 조회 (member.php에서 직접 접근 시)
if (!$wh_id && $preset_mb_id) {
    $existing = sql_fetch("SELECT wh_id FROM g5_member_webhook WHERE mb_id = '".sql_escape_string($preset_mb_id)."'");
    if ($existing['wh_id']) {
        $wh_id = $existing['wh_id'];
    }
}

$mode = $wh_id ? 'update' : 'insert';

// 기존 데이터 조회
$wh = [];
if ($wh_id) {
    $wh = sql_fetch("SELECT * FROM g5_member_webhook WHERE wh_id = '{$wh_id}'");
    if (!$wh['wh_id']) {
        alert_close("존재하지 않는 설정입니다.");
    }
}

// 저장 처리
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mb_id = trim($_POST['mb_id']);
    $wh_url = trim($_POST['wh_url']);
    $wh_events = isset($_POST['wh_events']) ? implode(',', $_POST['wh_events']) : 'approval';
    $wh_retry_count = intval($_POST['wh_retry_count']);
    $wh_retry_delay = intval($_POST['wh_retry_delay']);
    $wh_timeout = intval($_POST['wh_timeout']);
    $wh_status = trim($_POST['wh_status']);
    $wh_memo = trim($_POST['wh_memo']);

    // 유효성 검사
    if (!$mb_id) {
        alert("가맹점을 선택해주세요.");
    }
    if (!$wh_url) {
        alert("웹훅 URL을 입력해주세요.");
    }
    if (!filter_var($wh_url, FILTER_VALIDATE_URL)) {
        alert("올바른 URL 형식이 아닙니다.");
    }

    // 중복 체크 (신규 등록 시)
    if ($mode == 'insert') {
        $dup = sql_fetch("SELECT wh_id FROM g5_member_webhook WHERE mb_id = '{$mb_id}'");
        if ($dup['wh_id']) {
            alert("이미 웹훅 설정이 등록된 가맹점입니다.");
        }
    }

    $mb_id = sql_escape_string($mb_id);
    $wh_url = sql_escape_string($wh_url);
    $wh_events = sql_escape_string($wh_events);
    $wh_memo = sql_escape_string($wh_memo);

    // 저장 후 리다이렉트 URL 결정
    $redirect_url = $return_url ? urldecode($return_url) : "?p=webhook_config";

    if ($mode == 'insert') {
        $sql = "INSERT INTO g5_member_webhook SET
            mb_id = '{$mb_id}',
            wh_url = '{$wh_url}',
            wh_events = '{$wh_events}',
            wh_retry_count = '{$wh_retry_count}',
            wh_retry_delay = '{$wh_retry_delay}',
            wh_timeout = '{$wh_timeout}',
            wh_status = '{$wh_status}',
            wh_memo = '{$wh_memo}',
            wh_reg_datetime = NOW()";
        sql_query($sql);
        alert("결제통보 설정이 등록되었습니다.", $redirect_url);
    } else {
        $sql = "UPDATE g5_member_webhook SET
            wh_url = '{$wh_url}',
            wh_events = '{$wh_events}',
            wh_retry_count = '{$wh_retry_count}',
            wh_retry_delay = '{$wh_retry_delay}',
            wh_timeout = '{$wh_timeout}',
            wh_status = '{$wh_status}',
            wh_memo = '{$wh_memo}',
            wh_update_datetime = NOW()
            WHERE wh_id = '{$wh_id}'";
        sql_query($sql);
        alert("결제통보 설정이 수정되었습니다.", $redirect_url);
    }
}

// 가맹점 목록 조회 (레벨 3)
$members_sql = "SELECT mb_id, mb_name, mb_nick FROM g5_member WHERE mb_level = 3 ORDER BY mb_name ASC";
$members_result = sql_query($members_sql);

include_once('./_head.php');
?>

<style>
.webhook-form-header {
    background: linear-gradient(135deg, #7b1fa2 0%, #9c27b0 100%);
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 15px;
}
.webhook-form-header h2 {
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    margin: 0;
}
.webhook-form {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
}
.webhook-form table {
    width: 100%;
    border-collapse: collapse;
}
.webhook-form th {
    width: 150px;
    padding: 12px 10px;
    text-align: left;
    font-weight: 500;
    font-size: 13px;
    vertical-align: top;
    border-bottom: 1px solid #f0f0f0;
}
.webhook-form td {
    padding: 12px 10px;
    font-size: 13px;
    border-bottom: 1px solid #f0f0f0;
}
.webhook-form input[type="text"],
.webhook-form input[type="url"],
.webhook-form input[type="number"],
.webhook-form select,
.webhook-form textarea {
    padding: 8px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
    width: 100%;
    max-width: 400px;
    box-sizing: border-box;
}
.webhook-form textarea {
    height: 80px;
    resize: vertical;
}
.webhook-form .help-text {
    font-size: 11px;
    color: #888;
    margin-top: 4px;
}
.webhook-form .checkbox-group label {
    display: inline-block;
    margin-right: 15px;
    font-weight: normal;
}
.webhook-form .checkbox-group input {
    margin-right: 4px;
}
.webhook-form .radio-group label {
    display: inline-block;
    margin-right: 15px;
    font-weight: normal;
}
.webhook-form .radio-group input {
    margin-right: 4px;
}
.btn-area {
    text-align: center;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #e0e0e0;
}
.btn-save {
    background: #7b1fa2;
    color: #fff;
    border: none;
    padding: 10px 30px;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
}
.btn-save:hover {
    background: #6a1b9a;
}
.btn-cancel {
    background: #757575;
    color: #fff;
    border: none;
    padding: 10px 30px;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
    margin-left: 10px;
    text-decoration: none;
}
.btn-cancel:hover {
    background: #616161;
    color: #fff;
}
.btn-delete {
    background: #c62828;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
    margin-left: 10px;
}
.btn-delete:hover {
    background: #b71c1c;
}
</style>

<div class="webhook-form-header">
    <h2><i class="fa fa-bell"></i> 결제통보 설정 <?php echo $mode == 'insert' ? '등록' : '수정'; ?></h2>
</div>

<form method="post" class="webhook-form">
    <table>
        <tr>
            <th><label for="mb_id">가맹점 <span style="color:red">*</span></label></th>
            <td>
                <?php if ($mode == 'insert') { ?>
                <?php if ($preset_mb_id) {
                    // 가맹점 정보 조회
                    $preset_member = sql_fetch("SELECT mb_id, mb_name, mb_nick FROM g5_member WHERE mb_id = '".sql_escape_string($preset_mb_id)."'");
                ?>
                <input type="hidden" name="mb_id" value="<?php echo htmlspecialchars($preset_mb_id); ?>">
                <strong><?php echo $preset_member['mb_nick'] ?: $preset_member['mb_name']; ?> (<?php echo $preset_mb_id; ?>)</strong>
                <?php } else { ?>
                <select name="mb_id" id="mb_id" required style="max-width:300px;">
                    <option value="">선택하세요</option>
                    <?php while ($m = sql_fetch_array($members_result)) { ?>
                    <option value="<?php echo $m['mb_id']; ?>"><?php echo $m['mb_nick'] ?: $m['mb_name']; ?> (<?php echo $m['mb_id']; ?>)</option>
                    <?php } ?>
                </select>
                <?php } ?>
                <?php } else { ?>
                <input type="hidden" name="mb_id" value="<?php echo $wh['mb_id']; ?>">
                <strong><?php echo $wh['mb_id']; ?></strong>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th><label for="wh_url">웹훅 URL <span style="color:red">*</span></label></th>
            <td>
                <input type="url" name="wh_url" id="wh_url" value="<?php echo htmlspecialchars($wh['wh_url'] ?? ''); ?>" required placeholder="https://example.com/webhook">
                <div class="help-text">결제 알림을 수신할 URL을 입력하세요. HTTPS 권장</div>
            </td>
        </tr>
        <tr>
            <th>수신 이벤트</th>
            <td>
                <?php
                $events = explode(',', $wh['wh_events'] ?? 'approval,cancel');
                $events = array_map('trim', $events);
                ?>
                <div class="checkbox-group">
                    <label><input type="checkbox" name="wh_events[]" value="approval" <?php if(in_array('approval', $events)) echo 'checked'; ?>> 결제 승인</label>
                    <label><input type="checkbox" name="wh_events[]" value="cancel" <?php if(in_array('cancel', $events)) echo 'checked'; ?>> 결제 취소</label>
                </div>
            </td>
        </tr>
        <tr>
            <th><label for="wh_retry_count">재시도 횟수</label></th>
            <td>
                <input type="number" name="wh_retry_count" id="wh_retry_count" value="<?php echo $wh['wh_retry_count'] ?? 3; ?>" min="0" max="10" style="width:80px;"> 회
                <div class="help-text">발송 실패 시 재시도 횟수 (0~10, 권장: 3)</div>
            </td>
        </tr>
        <tr>
            <th><label for="wh_retry_delay">재시도 간격</label></th>
            <td>
                <input type="number" name="wh_retry_delay" id="wh_retry_delay" value="<?php echo $wh['wh_retry_delay'] ?? 5; ?>" min="1" max="60" style="width:80px;"> 초
                <div class="help-text">재시도 사이의 대기 시간 (1~60초, 권장: 5)</div>
            </td>
        </tr>
        <tr>
            <th><label for="wh_timeout">타임아웃</label></th>
            <td>
                <input type="number" name="wh_timeout" id="wh_timeout" value="<?php echo $wh['wh_timeout'] ?? 10; ?>" min="3" max="30" style="width:80px;"> 초
                <div class="help-text">HTTP 요청 타임아웃 (3~30초, 권장: 10)</div>
            </td>
        </tr>
        <tr>
            <th>상태</th>
            <td>
                <div class="radio-group">
                    <label><input type="radio" name="wh_status" value="active" <?php if(($wh['wh_status'] ?? 'active') == 'active') echo 'checked'; ?>> 활성</label>
                    <label><input type="radio" name="wh_status" value="inactive" <?php if(($wh['wh_status'] ?? '') == 'inactive') echo 'checked'; ?>> 비활성</label>
                </div>
            </td>
        </tr>
        <tr>
            <th><label for="wh_memo">메모</label></th>
            <td>
                <textarea name="wh_memo" id="wh_memo"><?php echo htmlspecialchars($wh['wh_memo'] ?? ''); ?></textarea>
            </td>
        </tr>
    </table>

    <div class="btn-area">
        <button type="submit" class="btn-save"><i class="fa fa-check"></i> 저장</button>
        <button type="button" class="btn-cancel" onclick="location.href='<?php echo $return_url ? urldecode($return_url) : '?p=webhook_config'; ?>'">취소</button>
        <?php if ($mode == 'update') { ?>
        <button type="button" class="btn-delete" onclick="deleteWebhook()"><i class="fa fa-trash"></i> 삭제</button>
        <?php } ?>
    </div>
</form>

<?php if ($mode == 'update') { ?>
<script>
function deleteWebhook() {
    if (confirm('정말 삭제하시겠습니까?\n삭제 후 복구할 수 없습니다.')) {
        location.href = '?p=webhook_config_delete&wh_id=<?php echo $wh_id; ?>';
    }
}
</script>
<?php } ?>

<?php include_once('./_tail.php'); ?>
