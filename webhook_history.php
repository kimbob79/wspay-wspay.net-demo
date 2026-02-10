<?php
$title1 = "시스템설정";
$title2 = "결제통보 이력";

include_once('./_common.php');

// 관리자만 접근
if (!$is_admin) {
    alert_close("관리자만 접근 가능합니다.");
}

include_once('./_head.php');

// 검색 조건
$fr_date = isset($_GET['fr_date']) ? trim($_GET['fr_date']) : date('Y-m-d', strtotime('-7 days'));
$to_date = isset($_GET['to_date']) ? trim($_GET['to_date']) : date('Y-m-d');
$mb_id = isset($_GET['mb_id']) ? trim($_GET['mb_id']) : '';
$mb_name = isset($_GET['mb_name']) ? trim($_GET['mb_name']) : '';
$whh_status = isset($_GET['whh_status']) ? trim($_GET['whh_status']) : '';
$whh_event_type = isset($_GET['whh_event_type']) ? trim($_GET['whh_event_type']) : '';
$pay_num = isset($_GET['pay_num']) ? trim($_GET['pay_num']) : '';
$pay_tid = isset($_GET['pay_tid']) ? trim($_GET['pay_tid']) : '';
$pay_amount = isset($_GET['pay_amount']) ? trim($_GET['pay_amount']) : '';

$sql_search = " WHERE h.whh_sent_datetime BETWEEN '{$fr_date} 00:00:00' AND '{$to_date} 23:59:59' ";

if ($mb_id) {
    $mb_id_esc = sql_escape_string($mb_id);
    $sql_search .= " AND h.mb_id = '{$mb_id_esc}' ";
}

if ($mb_name) {
    $mb_name_esc = sql_escape_string($mb_name);
    $sql_search .= " AND (m.mb_name LIKE '%{$mb_name_esc}%' OR m.mb_nick LIKE '%{$mb_name_esc}%') ";
}

if ($whh_status) {
    $sql_search .= " AND h.whh_status = '{$whh_status}' ";
}

if ($whh_event_type) {
    $sql_search .= " AND h.whh_event_type = '{$whh_event_type}' ";
}

if ($pay_num) {
    $pay_num_esc = sql_escape_string($pay_num);
    $sql_search .= " AND p.pay_num LIKE '%{$pay_num_esc}%' ";
}

if ($pay_tid) {
    $pay_tid_esc = sql_escape_string($pay_tid);
    $sql_search .= " AND p.dv_tid LIKE '%{$pay_tid_esc}%' ";
}

if ($pay_amount) {
    $pay_amount_esc = intval($pay_amount);
    $sql_search .= " AND p.pay = '{$pay_amount_esc}' ";
}

// 전체 건수
$sql_count = "SELECT COUNT(*) as cnt
    FROM g5_webhook_history h
    LEFT JOIN g5_member m ON h.mb_id = m.mb_id
    LEFT JOIN g5_payment p ON h.pay_id = p.pay_id
    {$sql_search}";
$total_count = sql_fetch($sql_count);
$total_count = $total_count['cnt'];

// 통계
$stats_sql = "SELECT
    SUM(CASE WHEN h.whh_status = 'success' THEN 1 ELSE 0 END) as success_cnt,
    SUM(CASE WHEN h.whh_status = 'failed' THEN 1 ELSE 0 END) as failed_cnt,
    SUM(CASE WHEN h.whh_status = 'pending' THEN 1 ELSE 0 END) as pending_cnt,
    SUM(CASE WHEN h.whh_status = 'timeout' THEN 1 ELSE 0 END) as timeout_cnt,
    AVG(CASE WHEN h.whh_status = 'success' THEN h.whh_response_time ELSE NULL END) as avg_response_time
    FROM g5_webhook_history h
    LEFT JOIN g5_member m ON h.mb_id = m.mb_id
    LEFT JOIN g5_payment p ON h.pay_id = p.pay_id
    {$sql_search}";
$stats = sql_fetch($stats_sql);

// 페이징
$rows = 30;
$total_page = ceil($total_count / $rows);
if (!$page) $page = 1;
$from_record = ($page - 1) * $rows;

// 목록 조회
$sql = "SELECT h.*, m.mb_name, m.mb_nick, p.pay, p.pay_num as payment_pay_num, p.dv_tid
    FROM g5_webhook_history h
    LEFT JOIN g5_member m ON h.mb_id = m.mb_id
    LEFT JOIN g5_payment p ON h.pay_id = p.pay_id
    {$sql_search}
    ORDER BY h.whh_id DESC
    LIMIT {$from_record}, {$rows}";
$result = sql_query($sql);

?>

<style>
.webhook-history-header {
    background: linear-gradient(135deg, #f57c00 0%, #ff9800 100%);
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 10px;
}
.webhook-history-header h2 {
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    margin: 0;
}
.webhook-history-search {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 10px 12px;
    margin-bottom: 10px;
}
.webhook-history-search .search-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
    margin-bottom: 6px;
}
.webhook-history-search .search-row:last-child {
    margin-bottom: 0;
}
.webhook-history-search input[type="date"],
.webhook-history-search input[type="text"],
.webhook-history-search select {
    padding: 6px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
}
.webhook-history-search button {
    background: #f57c00;
    color: #fff;
    border: none;
    padding: 6px 14px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
}
.webhook-history-search button:hover {
    background: #ef6c00;
}
.stats-box {
    display: flex;
    gap: 15px;
    margin-bottom: 10px;
    flex-wrap: wrap;
}
.stats-item {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 10px 15px;
    min-width: 100px;
}
.stats-item .label {
    font-size: 11px;
    color: #888;
}
.stats-item .value {
    font-size: 18px;
    font-weight: 600;
}
.stats-item.success .value { color: #2e7d32; }
.stats-item.failed .value { color: #c62828; }
.stats-item.pending .value { color: #f57c00; }
.stats-item.timeout .value { color: #7b1fa2; }

.webhook-history-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.webhook-history-table th {
    background: #f5f5f5;
    padding: 10px 6px;
    font-size: 12px;
    font-weight: 600;
    text-align: center;
    border-bottom: 1px solid #e0e0e0;
}
.webhook-history-table td {
    padding: 8px 6px;
    font-size: 12px;
    text-align: center;
    border-bottom: 1px solid #f0f0f0;
}
.webhook-history-table tr:hover {
    background: #fafafa;
}
.status-success { color: #2e7d32; font-weight: 500; }
.status-failed { color: #c62828; font-weight: 500; }
.status-pending { color: #f57c00; font-weight: 500; }
.status-timeout { color: #7b1fa2; font-weight: 500; }

.test-send-box {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 12px 15px;
    margin-bottom: 10px;
}
.test-send-title {
    font-size: 13px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}
.test-send-form {
    display: flex;
    gap: 8px;
    align-items: center;
}
.test-send-form select {
    padding: 6px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
    width: 80px;
}
.test-send-form input[type="text"] {
    padding: 6px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
}
.test-send-form button {
    background: #f57c00;
    color: #fff;
    border: none;
    padding: 6px 14px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    white-space: nowrap;
}
.test-send-form button:hover {
    background: #ef6c00;
}
.test-send-form button:disabled {
    background: #bdbdbd;
    cursor: not-allowed;
}
.test-send-result {
    margin-top: 10px;
    padding: 10px;
    border-radius: 4px;
    font-size: 12px;
}
.test-send-result.success {
    background: #e8f5e9;
    border: 1px solid #c8e6c9;
}
.test-send-result.error {
    background: #ffebee;
    border: 1px solid #ffcdd2;
}
.test-send-result pre {
    background: #f5f5f5;
    padding: 8px;
    margin-top: 8px;
    border-radius: 4px;
    font-size: 11px;
    overflow-x: auto;
    white-space: pre-wrap;
    word-break: break-all;
}

.event-approval { background: #e8f5e9; color: #2e7d32; padding: 2px 6px; border-radius: 3px; font-size: 11px; }
.event-cancel { background: #ffebee; color: #c62828; padding: 2px 6px; border-radius: 3px; font-size: 11px; }

.btn-detail {
    background: #1976d2;
    color: #fff;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    text-decoration: none;
    cursor: pointer;
    border: none;
}
.btn-detail:hover {
    background: #1565c0;
}
.btn-retry {
    background: #f57c00;
    color: #fff;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    text-decoration: none;
    cursor: pointer;
    border: none;
    margin-left: 3px;
}
.btn-retry:hover {
    background: #ef6c00;
}

/* 모달 */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
}
.modal-content {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #fff;
    border-radius: 8px;
    width: 90%;
    max-width: 700px;
    max-height: 80vh;
    overflow-y: auto;
    padding: 20px;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e0e0e0;
    padding-bottom: 10px;
    margin-bottom: 15px;
}
.modal-header h3 {
    margin: 0;
    font-size: 16px;
}
.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
}
.detail-section {
    margin-bottom: 15px;
}
.detail-section h4 {
    font-size: 13px;
    color: #666;
    margin-bottom: 5px;
}
.detail-section pre {
    background: #f5f5f5;
    padding: 10px;
    border-radius: 4px;
    font-size: 12px;
    overflow-x: auto;
    white-space: pre-wrap;
    word-break: break-all;
}
</style>

<div class="webhook-history-header">
    <h2><i class="fa fa-history"></i> 결제통보 발송 이력</h2>
</div>

<div class="test-send-box">
    <div class="test-send-title">테스트 발송</div>
    <div class="test-send-form">
        <select id="test_event_type">
            <option value="approval">승인</option>
            <option value="cancel">취소</option>
        </select>
        <input type="text" id="test_url" placeholder="https://example.com/webhook" style="flex:1;">
        <button type="button" onclick="sendTest()">테스트 전송</button>
    </div>
    <div class="test-send-result" id="testResult" style="display:none;"></div>
</div>

<div class="webhook-history-search">
    <form method="get">
        <input type="hidden" name="p" value="webhook_history">
        <div class="search-row">
            <input type="date" name="fr_date" value="<?php echo $fr_date; ?>">
            ~
            <input type="date" name="to_date" value="<?php echo $to_date; ?>">
            <select name="whh_status">
                <option value="">상태 전체</option>
                <option value="success" <?php if($whh_status == 'success') echo 'selected'; ?>>성공</option>
                <option value="failed" <?php if($whh_status == 'failed') echo 'selected'; ?>>실패</option>
                <option value="pending" <?php if($whh_status == 'pending') echo 'selected'; ?>>대기</option>
                <option value="timeout" <?php if($whh_status == 'timeout') echo 'selected'; ?>>타임아웃</option>
            </select>
            <select name="whh_event_type">
                <option value="">이벤트 전체</option>
                <option value="approval" <?php if($whh_event_type == 'approval') echo 'selected'; ?>>승인</option>
                <option value="cancel" <?php if($whh_event_type == 'cancel') echo 'selected'; ?>>취소</option>
            </select>
        </div>
        <div class="search-row">
            <input type="text" name="mb_id" value="<?php echo htmlspecialchars($mb_id); ?>" placeholder="가맹점ID" style="width:90px;">
            <input type="text" name="mb_name" value="<?php echo htmlspecialchars($mb_name); ?>" placeholder="가맹점명" style="width:90px;">
            <input type="text" name="pay_num" value="<?php echo htmlspecialchars($pay_num); ?>" placeholder="승인번호" style="width:90px;">
            <input type="text" name="pay_tid" value="<?php echo htmlspecialchars($pay_tid); ?>" placeholder="TID" style="width:120px;">
            <input type="text" name="pay_amount" value="<?php echo htmlspecialchars($pay_amount); ?>" placeholder="금액" style="width:80px;">
            <button type="submit"><i class="fa fa-search"></i> 검색</button>
        </div>
    </form>
</div>

<div class="stats-box">
    <div class="stats-item">
        <div class="label">전체</div>
        <div class="value"><?php echo number_format($total_count); ?></div>
    </div>
    <div class="stats-item success">
        <div class="label">성공</div>
        <div class="value"><?php echo number_format($stats['success_cnt'] ?? 0); ?></div>
    </div>
    <div class="stats-item failed">
        <div class="label">실패</div>
        <div class="value"><?php echo number_format($stats['failed_cnt'] ?? 0); ?></div>
    </div>
    <div class="stats-item pending">
        <div class="label">대기</div>
        <div class="value"><?php echo number_format($stats['pending_cnt'] ?? 0); ?></div>
    </div>
    <div class="stats-item timeout">
        <div class="label">타임아웃</div>
        <div class="value"><?php echo number_format($stats['timeout_cnt'] ?? 0); ?></div>
    </div>
    <div class="stats-item">
        <div class="label">평균응답</div>
        <div class="value"><?php echo number_format(($stats['avg_response_time'] ?? 0) / 1000, 2); ?>초</div>
    </div>
</div>

<table class="webhook-history-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>발송시간</th>
            <th>가맹점</th>
            <th>이벤트</th>
            <th>승인번호</th>
            <th>결제금액</th>
            <th>HTTP</th>
            <th>응답</th>
            <th>상태</th>
            <th>관리</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($total_count == 0) {
            echo '<tr><td colspan="10" style="padding:30px; color:#999;">이력이 없습니다.</td></tr>';
        }
        while ($row = sql_fetch_array($result)) {
            $status_class = 'status-' . $row['whh_status'];
            $event_class = 'event-' . $row['whh_event_type'];
        ?>
        <tr>
            <td><?php echo $row['whh_id']; ?></td>
            <td><?php echo substr($row['whh_sent_datetime'], 5, 14); ?></td>
            <td>
                <?php echo $row['mb_nick'] ?: $row['mb_name'] ?: $row['mb_id']; ?>
            </td>
            <td>
                <span class="<?php echo $event_class; ?>">
                    <?php echo $row['whh_event_type'] == 'approval' ? '승인' : '취소'; ?>
                </span>
            </td>
            <td><?php echo $row['payment_pay_num'] ?: '-'; ?></td>
            <td><?php echo $row['pay'] ? number_format($row['pay']) : '-'; ?></td>
            <td><?php echo $row['whh_http_status'] ?: '-'; ?></td>
            <td><?php echo $row['whh_response_time'] ? number_format($row['whh_response_time'] / 1000, 2).'초' : '-'; ?></td>
            <td>
                <span class="<?php echo $status_class; ?>">
                    <?php
                    switch($row['whh_status']) {
                        case 'success': echo '성공'; break;
                        case 'failed': echo '실패'; break;
                        case 'pending': echo '대기'; break;
                        case 'timeout': echo '타임아웃'; break;
                    }
                    ?>
                </span>
            </td>
            <td>
                <button class="btn-detail" onclick="showDetail(<?php echo $row['whh_id']; ?>)">상세</button>
                <button class="btn-retry" onclick="resendWebhook(<?php echo $row['whh_id']; ?>, this)">재전송</button>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<?php
// 페이징
if ($total_page > 1) {
    $qstr = "p=webhook_history&fr_date={$fr_date}&to_date={$to_date}&mb_id={$mb_id}&mb_name={$mb_name}&whh_status={$whh_status}&whh_event_type={$whh_event_type}&pay_num={$pay_num}&pay_tid={$pay_tid}&pay_amount={$pay_amount}";
    echo '<div style="text-align:center; margin-top:15px;">';
    for ($i = max(1, $page - 5); $i <= min($total_page, $page + 5); $i++) {
        if ($i == $page) {
            echo '<span style="display:inline-block; padding:5px 10px; margin:2px; background:#f57c00; color:#fff; border-radius:3px;">'.$i.'</span>';
        } else {
            echo '<a href="?'.$qstr.'&page='.$i.'" style="display:inline-block; padding:5px 10px; margin:2px; background:#f5f5f5; color:#333; border-radius:3px; text-decoration:none;">'.$i.'</a>';
        }
    }
    echo '</div>';
}
?>

<!-- 상세 모달 -->
<div class="modal-overlay" id="detailModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>결제통보 상세 정보</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div id="detailContent">
            Loading...
        </div>
    </div>
</div>

<script>
function showDetail(whh_id) {
    document.getElementById('detailModal').style.display = 'block';
    document.getElementById('detailContent').innerHTML = 'Loading...';

    // AJAX로 상세 정보 조회
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'webhook_history_detail.php?whh_id=' + whh_id, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            document.getElementById('detailContent').innerHTML = xhr.responseText;
        }
    };
    xhr.send();
}

function closeModal() {
    document.getElementById('detailModal').style.display = 'none';
}

// 모달 외부 클릭 시 닫기
document.getElementById('detailModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// 재전송
function resendWebhook(whh_id, btn) {
    if (!confirm('이 결제통보를 재전송하시겠습니까?')) {
        return;
    }

    var originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = '전송중...';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'webhook_resend.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            btn.disabled = false;
            btn.textContent = originalText;

            try {
                var res = JSON.parse(xhr.responseText);
                var msg = res.message;
                if (res.http_code) {
                    msg += ' (HTTP ' + res.http_code + ', ' + (res.response_time / 1000).toFixed(2) + '초)';
                }
                alert(msg);
                if (res.success) {
                    location.reload();
                }
            } catch(e) {
                alert('오류가 발생했습니다.');
            }
        }
    };
    xhr.send('whh_id=' + whh_id);
}

// 테스트 발송
function sendTest() {
    var url = document.getElementById('test_url').value.trim();
    var eventType = document.getElementById('test_event_type').value;
    var resultDiv = document.getElementById('testResult');
    var btn = document.querySelector('.test-send-form button');

    if (!url) {
        alert('URL을 입력해주세요.');
        return;
    }

    btn.disabled = true;
    btn.textContent = '전송중...';
    resultDiv.style.display = 'none';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'webhook_test_send.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            btn.disabled = false;
            btn.textContent = '테스트 전송';

            try {
                var res = JSON.parse(xhr.responseText);
                resultDiv.style.display = 'block';
                resultDiv.className = 'test-send-result ' + (res.success ? 'success' : 'error');

                var html = '<strong>' + res.message + '</strong>';
                html += ' (HTTP ' + res.http_code + ', ' + (res.response_time / 1000).toFixed(2) + '초)';
                if (res.response_body) {
                    html += '<pre>응답: ' + res.response_body + '</pre>';
                }
                resultDiv.innerHTML = html;
            } catch(e) {
                resultDiv.style.display = 'block';
                resultDiv.className = 'test-send-result error';
                resultDiv.innerHTML = '<strong>오류 발생</strong>';
            }
        }
    };
    xhr.send('test_url=' + encodeURIComponent(url) + '&event_type=' + eventType);
}
</script>

<?php include_once('./_tail.php'); ?>
