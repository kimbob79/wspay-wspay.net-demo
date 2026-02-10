<?php
$title1 = "시스템설정";
$title2 = "결제통보 설정";

include_once('./_common.php');

// 관리자만 접근
if (!$is_admin) {
    alert_close("관리자만 접근 가능합니다.");
}

include_once('./_head.php');

// 검색 조건
$sfl = isset($_GET['sfl']) ? trim($_GET['sfl']) : '';
$stx = isset($_GET['stx']) ? trim($_GET['stx']) : '';
$wh_status = isset($_GET['wh_status']) ? trim($_GET['wh_status']) : '';

$sql_search = " WHERE 1 ";

if ($stx) {
    if ($sfl == 'mb_id') {
        $sql_search .= " AND w.mb_id LIKE '%{$stx}%' ";
    } else if ($sfl == 'mb_name') {
        $sql_search .= " AND m.mb_name LIKE '%{$stx}%' ";
    }
}

if ($wh_status) {
    $sql_search .= " AND w.wh_status = '{$wh_status}' ";
}

// 전체 건수
$sql_count = "SELECT COUNT(*) as cnt
    FROM g5_member_webhook w
    LEFT JOIN g5_member m ON w.mb_id = m.mb_id
    {$sql_search}";
$total_count = sql_fetch($sql_count);
$total_count = $total_count['cnt'];

// 페이징
$rows = 20;
$total_page = ceil($total_count / $rows);
if (!$page) $page = 1;
$from_record = ($page - 1) * $rows;

// 목록 조회
$sql = "SELECT w.*, m.mb_name, m.mb_nick
    FROM g5_member_webhook w
    LEFT JOIN g5_member m ON w.mb_id = m.mb_id
    {$sql_search}
    ORDER BY w.wh_id DESC
    LIMIT {$from_record}, {$rows}";
$result = sql_query($sql);
?>

<style>
.webhook-header {
    background: linear-gradient(135deg, #7b1fa2 0%, #9c27b0 100%);
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.webhook-header h2 {
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    margin: 0;
}
.webhook-header .btn-add {
    background: #fff;
    color: #7b1fa2;
    padding: 6px 14px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
}
.webhook-header .btn-add:hover {
    background: #f3e5f5;
}
.webhook-search {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 10px 12px;
    margin-bottom: 10px;
}
.webhook-search select,
.webhook-search input[type="text"] {
    padding: 6px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
}
.webhook-search button {
    background: #7b1fa2;
    color: #fff;
    border: none;
    padding: 6px 14px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
}
.webhook-search button:hover {
    background: #6a1b9a;
}
.webhook-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.webhook-table th {
    background: #f5f5f5;
    padding: 10px 8px;
    font-size: 13px;
    font-weight: 600;
    text-align: center;
    border-bottom: 1px solid #e0e0e0;
}
.webhook-table td {
    padding: 10px 8px;
    font-size: 13px;
    text-align: center;
    border-bottom: 1px solid #f0f0f0;
}
.webhook-table tr:hover {
    background: #fafafa;
}
.status-active {
    color: #2e7d32;
    font-weight: 500;
}
.status-inactive {
    color: #757575;
}
.btn-edit {
    background: #1976d2;
    color: #fff;
    padding: 4px 10px;
    border-radius: 3px;
    font-size: 12px;
    text-decoration: none;
}
.btn-edit:hover {
    background: #1565c0;
    color: #fff;
}
.btn-history {
    background: #f57c00;
    color: #fff;
    padding: 4px 10px;
    border-radius: 3px;
    font-size: 12px;
    text-decoration: none;
    margin-left: 4px;
}
.btn-history:hover {
    background: #ef6c00;
    color: #fff;
}
.btn-delete {
    background: #c62828;
    color: #fff;
    padding: 4px 10px;
    border-radius: 3px;
    font-size: 12px;
    text-decoration: none;
    margin-left: 4px;
}
.btn-delete:hover {
    background: #b71c1c;
    color: #fff;
}
.stats-success {
    color: #2e7d32;
}
.stats-fail {
    color: #c62828;
}
.url-cell {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    text-align: left;
}
</style>

<div class="webhook-header">
    <h2><i class="fa fa-bell"></i> 결제통보 설정 관리</h2>
    <a href="?p=webhook_config_form" class="btn-add"><i class="fa fa-plus"></i> 신규 등록</a>
</div>

<div class="webhook-search">
    <form method="get">
        <input type="hidden" name="p" value="webhook_config">
        <select name="sfl">
            <option value="mb_id" <?php if($sfl == 'mb_id') echo 'selected'; ?>>가맹점ID</option>
            <option value="mb_name" <?php if($sfl == 'mb_name') echo 'selected'; ?>>가맹점명</option>
        </select>
        <input type="text" name="stx" value="<?php echo $stx; ?>" placeholder="검색어">
        <select name="wh_status">
            <option value="">상태 전체</option>
            <option value="active" <?php if($wh_status == 'active') echo 'selected'; ?>>활성</option>
            <option value="inactive" <?php if($wh_status == 'inactive') echo 'selected'; ?>>비활성</option>
        </select>
        <button type="submit"><i class="fa fa-search"></i> 검색</button>
    </form>
</div>

<div style="margin-bottom: 8px; font-size: 13px; color: #666;">
    총 <strong><?php echo number_format($total_count); ?></strong>건
</div>

<table class="webhook-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>가맹점</th>
            <th>웹훅 URL</th>
            <th>이벤트</th>
            <th>재시도</th>
            <th>상태</th>
            <th>성공/실패</th>
            <th>등록일</th>
            <th>관리</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($total_count == 0) {
            echo '<tr><td colspan="9" style="padding:30px; color:#999;">등록된 결제통보 설정이 없습니다.</td></tr>';
        }
        while ($row = sql_fetch_array($result)) {
        ?>
        <tr>
            <td><?php echo $row['wh_id']; ?></td>
            <td>
                <?php echo $row['mb_nick'] ?: $row['mb_name']; ?><br>
                <span style="font-size:11px; color:#888;"><?php echo $row['mb_id']; ?></span>
            </td>
            <td class="url-cell" title="<?php echo htmlspecialchars($row['wh_url']); ?>">
                <?php echo htmlspecialchars($row['wh_url']); ?>
            </td>
            <td><?php echo $row['wh_events']; ?></td>
            <td><?php echo $row['wh_retry_count']; ?>회</td>
            <td>
                <?php if ($row['wh_status'] == 'active') { ?>
                    <span class="status-active">● 활성</span>
                <?php } else { ?>
                    <span class="status-inactive">○ 비활성</span>
                <?php } ?>
            </td>
            <td>
                <span class="stats-success"><?php echo number_format($row['wh_success_count']); ?></span> /
                <span class="stats-fail"><?php echo number_format($row['wh_fail_count']); ?></span>
            </td>
            <td><?php echo substr($row['wh_reg_datetime'], 0, 10); ?></td>
            <td>
                <a href="?p=webhook_config_form&wh_id=<?php echo $row['wh_id']; ?>" class="btn-edit">수정</a>
                <a href="?p=webhook_history&mb_id=<?php echo $row['mb_id']; ?>" class="btn-history">이력</a>
                <a href="javascript:;" onclick="deleteWebhook(<?php echo $row['wh_id']; ?>)" class="btn-delete">삭제</a>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<?php
// 페이징
if ($total_page > 1) {
    $qstr = "p=webhook_config&sfl={$sfl}&stx={$stx}&wh_status={$wh_status}";
    echo '<div style="text-align:center; margin-top:15px;">';
    for ($i = 1; $i <= $total_page; $i++) {
        if ($i == $page) {
            echo '<span style="display:inline-block; padding:5px 10px; margin:2px; background:#7b1fa2; color:#fff; border-radius:3px;">'.$i.'</span>';
        } else {
            echo '<a href="?'.$qstr.'&page='.$i.'" style="display:inline-block; padding:5px 10px; margin:2px; background:#f5f5f5; color:#333; border-radius:3px; text-decoration:none;">'.$i.'</a>';
        }
    }
    echo '</div>';
}
?>

<script>
function deleteWebhook(wh_id) {
    if (confirm('정말 삭제하시겠습니까?\n삭제 후 복구할 수 없습니다.')) {
        location.href = '?p=webhook_config_delete&wh_id=' + wh_id;
    }
}
</script>

<?php include_once('./_tail.php'); ?>
