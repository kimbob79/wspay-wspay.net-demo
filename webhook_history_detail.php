<?php
include_once('./_common.php');

// 관리자만 접근
if (!$is_admin) {
    die('권한이 없습니다.');
}

$whh_id = isset($_GET['whh_id']) ? intval($_GET['whh_id']) : 0;

if (!$whh_id) {
    die('잘못된 요청입니다.');
}

$row = sql_fetch("SELECT h.*, m.mb_name, m.mb_nick, p.pay, p.pay_num as payment_pay_num, p.pay_card_name
    FROM g5_webhook_history h
    LEFT JOIN g5_member m ON h.mb_id = m.mb_id
    LEFT JOIN g5_payment p ON h.pay_id = p.pay_id
    WHERE h.whh_id = '{$whh_id}'");

if (!$row['whh_id']) {
    die('존재하지 않는 이력입니다.');
}

// JSON 포맷팅
$payload_formatted = '';
if ($row['whh_payload']) {
    $payload_decoded = json_decode($row['whh_payload'], true);
    if ($payload_decoded) {
        $payload_formatted = json_encode($payload_decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        $payload_formatted = $row['whh_payload'];
    }
}
?>

<style>
.detail-info-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 15px;
}
.detail-info-table th {
    width: 100px;
    padding: 8px 10px;
    text-align: left;
    font-weight: 500;
    font-size: 12px;
    background: #f9f9f9;
    border: 1px solid #e0e0e0;
}
.detail-info-table td {
    padding: 8px 10px;
    font-size: 12px;
    border: 1px solid #e0e0e0;
}
.detail-section {
    margin-bottom: 15px;
}
.detail-section h4 {
    font-size: 13px;
    color: #666;
    margin-bottom: 5px;
    padding-bottom: 5px;
    border-bottom: 1px solid #e0e0e0;
}
.detail-section pre {
    background: #f5f5f5;
    padding: 10px;
    border-radius: 4px;
    font-size: 11px;
    overflow-x: auto;
    white-space: pre-wrap;
    word-break: break-all;
    max-height: 200px;
    margin: 0;
}
.status-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 500;
}
.status-badge.success { background: #e8f5e9; color: #2e7d32; }
.status-badge.failed { background: #ffebee; color: #c62828; }
.status-badge.pending { background: #fff3e0; color: #f57c00; }
.status-badge.timeout { background: #f3e5f5; color: #7b1fa2; }
</style>

<table class="detail-info-table">
    <tr>
        <th>이력 ID</th>
        <td><?php echo $row['whh_id']; ?></td>
        <th>이벤트 ID</th>
        <td><?php echo $row['whh_event_id']; ?></td>
    </tr>
    <tr>
        <th>가맹점</th>
        <td><?php echo $row['mb_nick'] ?: $row['mb_name'] ?: $row['mb_id']; ?> (<?php echo $row['mb_id']; ?>)</td>
        <th>이벤트</th>
        <td><?php echo $row['whh_event_type'] == 'approval' ? '결제 승인' : '결제 취소'; ?></td>
    </tr>
    <tr>
        <th>결제금액</th>
        <td><?php echo $row['pay'] ? number_format($row['pay']).'원' : '-'; ?></td>
        <th>승인번호</th>
        <td><?php echo $row['payment_pay_num'] ?: '-'; ?></td>
    </tr>
    <tr>
        <th>발송시간</th>
        <td><?php echo $row['whh_sent_datetime']; ?></td>
        <th>완료시간</th>
        <td><?php echo $row['whh_completed_datetime'] ?: '-'; ?></td>
    </tr>
    <tr>
        <th>상태</th>
        <td>
            <span class="status-badge <?php echo $row['whh_status']; ?>">
                <?php
                switch($row['whh_status']) {
                    case 'success': echo '성공'; break;
                    case 'failed': echo '실패'; break;
                    case 'pending': echo '대기중'; break;
                    case 'timeout': echo '타임아웃'; break;
                }
                ?>
            </span>
        </td>
        <th>재시도</th>
        <td><?php echo $row['whh_retry_count']; ?> / <?php echo $row['whh_max_retry_count']; ?> 회</td>
    </tr>
    <tr>
        <th>HTTP 코드</th>
        <td><?php echo $row['whh_http_status'] ?: '-'; ?></td>
        <th>응답시간</th>
        <td><?php echo $row['whh_response_time'] ? number_format($row['whh_response_time'] / 1000, 2).'초' : '-'; ?></td>
    </tr>
</table>

<div class="detail-section">
    <h4>결제통보 URL</h4>
    <pre><?php echo htmlspecialchars($row['whh_url']); ?></pre>
</div>

<div class="detail-section">
    <h4>요청 페이로드 (JSON)</h4>
    <pre><?php echo htmlspecialchars($payload_formatted); ?></pre>
</div>

<?php if ($row['whh_response_body']) { ?>
<div class="detail-section">
    <h4>응답 본문</h4>
    <pre><?php echo htmlspecialchars($row['whh_response_body']); ?></pre>
</div>
<?php } ?>

<?php if ($row['whh_error_message']) { ?>
<div class="detail-section">
    <h4>에러 메시지</h4>
    <pre style="background:#ffebee; color:#c62828;"><?php echo htmlspecialchars($row['whh_error_message']); ?></pre>
</div>
<?php } ?>
