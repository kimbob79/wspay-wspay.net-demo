<?php
include_once('./_common.php');

// 관리자만 접근 가능
if(!$is_admin) {
    alert("관리자만 접근할 수 있습니다.");
}

$title1 = "URL결제";
$title2 = "URL결제 관리";

// 테이블 자동 생성
$check_table = sql_query("SHOW TABLES LIKE 'g5_url_payment'");
if(sql_num_rows($check_table) == 0) {
    $create_sql = "CREATE TABLE IF NOT EXISTS `g5_url_payment` (
      `up_id` int(11) NOT NULL AUTO_INCREMENT,
      `up_code` varchar(9) NOT NULL,
      `mb_id` varchar(50) NOT NULL,
      `mkc_id` int(11) DEFAULT NULL,
      `up_amount` int(11) NOT NULL,
      `up_goods_name` varchar(100) DEFAULT NULL,
      `up_goods_desc` text,
      `up_buyer_name` varchar(50) DEFAULT NULL,
      `up_buyer_phone` varchar(20) DEFAULT NULL,
      `up_seller_name` varchar(50) DEFAULT NULL,
      `up_seller_phone` varchar(20) DEFAULT NULL,
      `up_expire_datetime` datetime NOT NULL,
      `up_max_uses` int(11) DEFAULT 1,
      `up_use_count` int(11) DEFAULT 0,
      `up_status` enum('active','used','expired','cancelled') DEFAULT 'active',
      `up_memo` text,
      `up_sms_sent` char(1) DEFAULT 'N',
      `up_sms_sent_datetime` datetime DEFAULT NULL,
      `up_sms_count` int(11) DEFAULT 0,
      `pk_id` int(11) DEFAULT NULL,
      `up_paid_datetime` datetime DEFAULT NULL,
      `up_mb_1` varchar(50) DEFAULT NULL,
      `up_mb_2` varchar(50) DEFAULT NULL,
      `up_mb_3` varchar(50) DEFAULT NULL,
      `up_mb_4` varchar(50) DEFAULT NULL,
      `up_mb_5` varchar(50) DEFAULT NULL,
      `up_mb_6` varchar(50) DEFAULT NULL,
      `up_mb_6_name` varchar(50) DEFAULT NULL,
      `up_operator_id` varchar(50) DEFAULT NULL,
      `up_created_at` datetime DEFAULT CURRENT_TIMESTAMP,
      `up_updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`up_id`),
      UNIQUE KEY `idx_code` (`up_code`),
      KEY `idx_mb_id` (`mb_id`),
      KEY `idx_status` (`up_status`),
      KEY `idx_expire` (`up_expire_datetime`),
      KEY `idx_created` (`up_created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    sql_query($create_sql);
}

// 만료된 URL 자동 처리
sql_query("UPDATE g5_url_payment SET up_status = 'expired' WHERE up_status = 'active' AND up_expire_datetime < NOW()");

// 검색 조건
$fr_date = isset($_GET['fr_date']) ? preg_replace('/[^0-9]/', '', $_GET['fr_date']) : date("Ymd");
$to_date = isset($_GET['to_date']) ? preg_replace('/[^0-9]/', '', $_GET['to_date']) : date("Ymd");
$fr_dates = substr($fr_date, 0, 4)."-".substr($fr_date, 4, 2)."-".substr($fr_date, 6, 2);
$to_dates = substr($to_date, 0, 4)."-".substr($to_date, 4, 2)."-".substr($to_date, 6, 2);

$sfl = isset($_GET['sfl']) ? $_GET['sfl'] : '';
$stx = isset($_GET['stx']) ? trim($_GET['stx']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// 검색 쿼리 생성
if($fr_date == 'all' && $to_date == 'all') {
    $where = "WHERE 1";
} else {
    $where = "WHERE up_created_at BETWEEN '{$fr_dates} 00:00:00' AND '{$to_dates} 23:59:59'";
}

if($status) {
    $where .= " AND up_status = '".sql_real_escape_string($status)."'";
}

if($sfl && $stx) {
    $stx_esc = sql_real_escape_string($stx);
    if($sfl == 'mb_id') {
        $where .= " AND mb_id LIKE '%{$stx_esc}%'";
    } else if($sfl == 'buyer_name') {
        $where .= " AND up_buyer_name LIKE '%{$stx_esc}%'";
    } else if($sfl == 'buyer_phone') {
        $where .= " AND up_buyer_phone LIKE '%{$stx_esc}%'";
    } else if($sfl == 'goods_name') {
        $where .= " AND up_goods_name LIKE '%{$stx_esc}%'";
    }
}

// 통계 조회
$stats = sql_fetch("SELECT
    COUNT(*) as total_count,
    COUNT(IF(up_status='active', 1, NULL)) as active_count,
    COUNT(IF(up_status='used', 1, NULL)) as used_count,
    COUNT(IF(up_status='expired', 1, NULL)) as expired_count,
    COUNT(IF(up_status='cancelled', 1, NULL)) as cancelled_count,
    SUM(up_amount) as total_amount,
    SUM(IF(up_status='used', up_amount, 0)) as paid_amount
FROM g5_url_payment {$where}");

// 페이지네이션
$rows = 20;
$total_count = $stats['total_count'];
$total_page = ceil($total_count / $rows);
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$from_record = ($page - 1) * $rows;

// 데이터 조회
$sql = "SELECT u.*, m.mb_nick as merchant_name
        FROM g5_url_payment u
        LEFT JOIN g5_member m ON u.mb_id = m.mb_id
        {$where}
        ORDER BY up_id DESC
        LIMIT {$from_record}, {$rows}";
$result = sql_query($sql);

include_once('./_head.php');
?>

<style>
.url-payment-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 15px;
}

.url-payment-header {
    background: linear-gradient(135deg, #009688 0%, #00acc1 100%);
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 10px;
    box-shadow: 0 2px 8px rgba(0, 150, 136, 0.3);
}

.url-payment-title {
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.url-payment-title i {
    font-size: 18px;
}

.stats-card {
    background: #fff;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    border: 1px solid #e0e0e0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 15px;
}

.stat-item {
    text-align: center;
    padding: 10px;
    border-radius: 8px;
    background: #f8f9fa;
}

.stat-item.active { background: #e3f2fd; }
.stat-item.used { background: #e8f5e9; }
.stat-item.expired { background: #fff3e0; }
.stat-item.cancelled { background: #ffebee; }

.stat-value {
    font-size: 24px;
    font-weight: 700;
    color: #333;
}

.stat-label {
    font-size: 12px;
    color: #666;
    margin-top: 4px;
}

.url-payment-search {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 10px 12px;
    margin-bottom: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.search-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}

.search-group {
    display: flex;
    align-items: center;
    gap: 4px;
}

.search-group input[type="text"],
.search-group select {
    padding: 6px 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
    background: #f8f9fa;
}

.search-group input[type="text"]:focus,
.search-group select:focus {
    outline: none;
    border-color: #009688;
    background: #fff;
}

.search-group input.date-input {
    width: 90px;
}

.search-group select {
    min-width: 90px;
}

.search-group span {
    color: #999;
    font-size: 12px;
}

.date-btns {
    display: flex;
    gap: 3px;
}

.date-btns button {
    padding: 5px 8px;
    font-size: 11px;
    border: 1px solid #ddd;
    background: #f8f9fa;
    border-radius: 3px;
    cursor: pointer;
    color: #555;
    transition: all 0.15s;
}

.date-btns button:hover {
    background: #009688;
    border-color: #009688;
    color: #fff;
}

.search-divider {
    width: 1px;
    height: 24px;
    background: #e0e0e0;
    margin: 0 6px;
}

.search-input-group {
    display: flex;
    align-items: center;
    gap: 4px;
}

.search-input-group input[type="text"] {
    width: 120px;
    padding: 6px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
}

.search-input-group input[type="text"]:focus {
    outline: none;
    border-color: #009688;
}

.btn-search {
    padding: 6px 12px;
    background: #009688;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    transition: background 0.15s;
}

.btn-search:hover {
    background: #00796b;
}

.btn-create {
    padding: 6px 12px;
    background: #2196F3;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: background 0.15s;
}

.btn-create:hover {
    background: #1976D2;
    color: #fff;
}

.url-payment-table {
    width: 100%;
    background: #fff;
    border-collapse: collapse;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    font-size: 13px;
}

.url-payment-table th {
    background: #f5f5f5;
    padding: 12px 8px;
    text-align: center;
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #e0e0e0;
}

.url-payment-table td {
    padding: 10px 8px;
    text-align: center;
    border-bottom: 1px solid #eee;
}

.url-payment-table tr:hover {
    background: #f9f9f9;
}

.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.status-badge.active { background: #e3f2fd; color: #1976D2; }
.status-badge.used { background: #e8f5e9; color: #388e3c; }
.status-badge.expired { background: #fff3e0; color: #f57c00; }
.status-badge.cancelled { background: #ffebee; color: #d32f2f; }

.btn-sm {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    cursor: pointer;
    border: none;
    margin: 1px;
}

.btn-sms { background: #4CAF50; color: #fff; }
.btn-sms:hover { background: #388e3c; }
.btn-cancel { background: #f44336; color: #fff; }
.btn-cancel:hover { background: #d32f2f; }
.btn-copy { background: #607d8b; color: #fff; }
.btn-copy:hover { background: #455a64; }

.url-link {
    color: #1976D2;
    text-decoration: none;
    font-family: monospace;
    font-size: 12px;
}

.url-link:hover {
    text-decoration: underline;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 5px;
    margin-top: 20px;
}

.pagination a, .pagination span {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-decoration: none;
    color: #333;
}

.pagination a:hover { background: #f5f5f5; }
.pagination .current { background: #009688; color: #fff; border-color: #009688; }

@media (max-width: 768px) {
    .url-payment-table { font-size: 11px; }
    .url-payment-table th, .url-payment-table td { padding: 8px 4px; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .url-payment-search { padding: 8px; }
    .search-row {
        flex-direction: column;
        align-items: stretch;
        gap: 6px;
    }
    .search-group {
        justify-content: flex-start;
    }
    .search-group.date-group {
        width: 100%;
    }
    .search-group input[type="text"] {
        flex: 1;
        width: auto;
        min-width: 0;
    }
    .search-group select {
        min-width: 0;
        flex: 1;
    }
    .date-btns {
        width: 100%;
        flex-wrap: wrap;
    }
    .date-btns button {
        flex: 1;
        min-width: calc(33% - 4px);
        padding: 6px 4px;
        font-size: 11px;
    }
    .search-divider {
        display: none;
    }
    .search-input-group {
        width: 100%;
    }
    .search-input-group input[type="text"] {
        flex: 1;
        width: auto;
    }
    .btn-search, .btn-create {
        padding: 6px 16px;
    }
}
</style>

<div class="url-payment-container">
    <div class="url-payment-header">
        <div class="url-payment-title">
            <i class="fa fa-link"></i> URL결제 관리
        </div>
    </div>

    <!-- 통계 카드 -->
    <div class="stats-card">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-value"><?php echo number_format($stats['total_count']); ?></div>
                <div class="stat-label">전체</div>
            </div>
            <div class="stat-item active">
                <div class="stat-value"><?php echo number_format($stats['active_count']); ?></div>
                <div class="stat-label">활성</div>
            </div>
            <div class="stat-item used">
                <div class="stat-value"><?php echo number_format($stats['used_count']); ?></div>
                <div class="stat-label">결제완료</div>
            </div>
            <div class="stat-item expired">
                <div class="stat-value"><?php echo number_format($stats['expired_count']); ?></div>
                <div class="stat-label">만료</div>
            </div>
            <div class="stat-item cancelled">
                <div class="stat-value"><?php echo number_format($stats['cancelled_count']); ?></div>
                <div class="stat-label">취소</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo number_format($stats['paid_amount']); ?></div>
                <div class="stat-label">결제금액(원)</div>
            </div>
        </div>
    </div>

    <!-- 검색 영역 -->
    <div class="url-payment-search">
        <form method="get" action="" id="fsearch">
            <input type="hidden" name="p" value="url_payment">
            <div class="search-row">
                <div class="search-group date-group">
                    <input type="text" name="fr_date" class="date-input" id="fr_date" value="<?php echo $fr_date; ?>" placeholder="시작일">
                    <span>~</span>
                    <input type="text" name="to_date" class="date-input" id="to_date" value="<?php echo $to_date; ?>" placeholder="종료일">
                </div>
                <div class="date-btns">
                    <button type="submit" onclick="javascript:set_date('오늘');">오늘</button>
                    <button type="submit" onclick="javascript:set_date('어제');">어제</button>
                    <button type="submit" onclick="javascript:set_date('이번달');">이번달</button>
                    <button type="submit" onclick="javascript:set_date('지난주');">지난주</button>
                    <button type="submit" onclick="javascript:set_date('지난달');">지난달</button>
                    <button type="submit" onclick="javascript:set_date('전체');">전체</button>
                </div>
                <div class="search-divider"></div>
                <div class="search-group">
                    <select name="status">
                        <option value="">상태전체</option>
                        <option value="active" <?php if($status == 'active') echo 'selected'; ?>>활성</option>
                        <option value="used" <?php if($status == 'used') echo 'selected'; ?>>결제완료</option>
                        <option value="expired" <?php if($status == 'expired') echo 'selected'; ?>>만료</option>
                        <option value="cancelled" <?php if($status == 'cancelled') echo 'selected'; ?>>취소</option>
                    </select>
                </div>
                <div class="search-group">
                    <select name="sfl">
                        <option value="">검색항목</option>
                        <option value="mb_id" <?php if($sfl == 'mb_id') echo 'selected'; ?>>가맹점ID</option>
                        <option value="buyer_name" <?php if($sfl == 'buyer_name') echo 'selected'; ?>>구매자명</option>
                        <option value="buyer_phone" <?php if($sfl == 'buyer_phone') echo 'selected'; ?>>구매자연락처</option>
                        <option value="goods_name" <?php if($sfl == 'goods_name') echo 'selected'; ?>>상품명</option>
                    </select>
                </div>
                <div class="search-divider"></div>
                <div class="search-input-group">
                    <input type="text" name="stx" value="<?php echo htmlspecialchars($stx); ?>" placeholder="검색어">
                    <button type="submit" class="btn-search">검색</button>
                    <a href="./?p=url_payment_form" class="btn-create"><i class="fa fa-plus"></i> 생성</a>
                </div>
            </div>
        </form>
    </div>

    <!-- 데이터 테이블 -->
    <table class="url-payment-table">
        <thead>
            <tr>
                <th>No</th>
                <th>URL코드</th>
                <th>가맹점</th>
                <th>상품명</th>
                <th>금액</th>
                <th>구매자</th>
                <th>연락처</th>
                <th>상태</th>
                <th>SMS</th>
                <th>유효기간</th>
                <th>등록일</th>
                <th>관리</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $num = $total_count - $from_record;
            while($row = sql_fetch_array($result)) {
                $status_class = $row['up_status'];
                $status_text = '';
                switch($row['up_status']) {
                    case 'active': $status_text = '활성'; break;
                    case 'used': $status_text = '결제완료'; break;
                    case 'expired': $status_text = '만료'; break;
                    case 'cancelled': $status_text = '취소'; break;
                }

                $url = "https://".$_SERVER['HTTP_HOST']."/pay/".$row['up_code'];
            ?>
            <tr>
                <td><?php echo $num--; ?></td>
                <td>
                    <a href="<?php echo $url; ?>" target="_blank" class="url-link"><?php echo $row['up_code']; ?></a>
                </td>
                <td><?php echo htmlspecialchars($row['merchant_name'] ?: $row['mb_id']); ?></td>
                <td><?php echo htmlspecialchars($row['up_goods_name']); ?></td>
                <td style="text-align:right;"><?php echo number_format($row['up_amount']); ?></td>
                <td><?php echo htmlspecialchars($row['up_buyer_name']); ?></td>
                <td><?php echo htmlspecialchars($row['up_buyer_phone']); ?></td>
                <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                <td>
                    <?php if($row['up_sms_sent'] == 'Y') { ?>
                        <span style="color:#4CAF50;" title="발송완료 (<?php echo $row['up_sms_count']; ?>회)"><i class="fa fa-check-circle"></i></span>
                    <?php } else { ?>
                        <span style="color:#999;"><i class="fa fa-minus-circle"></i></span>
                    <?php } ?>
                </td>
                <td><?php echo date('m-d H:i', strtotime($row['up_expire_datetime'])); ?></td>
                <td><?php echo date('m-d H:i', strtotime($row['up_created_at'])); ?></td>
                <td>
                    <?php if($row['up_status'] == 'active') { ?>
                    <button type="button" class="btn-sm btn-sms" onclick="sendSms(<?php echo $row['up_id']; ?>, '<?php echo $row['up_buyer_phone']; ?>')" title="SMS 발송"><i class="fa fa-envelope"></i></button>
                    <button type="button" class="btn-sm btn-copy" onclick="copyUrl('<?php echo $url; ?>')" title="URL 복사"><i class="fa fa-copy"></i></button>
                    <button type="button" class="btn-sm btn-cancel" onclick="cancelUrl(<?php echo $row['up_id']; ?>)" title="취소"><i class="fa fa-times"></i></button>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
            <?php if($total_count == 0) { ?>
            <tr><td colspan="12" style="padding:30px; color:#999;">데이터가 없습니다.</td></tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- 페이지네이션 -->
    <?php if($total_page > 1) { ?>
    <div class="pagination">
        <?php
        $query_string = "p=url_payment&fr_date={$fr_date}&to_date={$to_date}&status={$status}&sfl={$sfl}&stx=".urlencode($stx);

        if($page > 1) {
            echo '<a href="./?'.$query_string.'&page='.($page-1).'">&laquo;</a>';
        }

        $start_page = max(1, $page - 5);
        $end_page = min($total_page, $page + 5);

        for($i = $start_page; $i <= $end_page; $i++) {
            if($i == $page) {
                echo '<span class="current">'.$i.'</span>';
            } else {
                echo '<a href="./?'.$query_string.'&page='.$i.'">'.$i.'</a>';
            }
        }

        if($page < $total_page) {
            echo '<a href="./?'.$query_string.'&page='.($page+1).'">&raquo;</a>';
        }
        ?>
    </div>
    <?php } ?>
</div>

<script>
$(function() {
    $('#fr_date, #to_date').datepicker({
        dateFormat: 'yymmdd',
        changeMonth: true,
        changeYear: true
    });
});

function set_date(type) {
    var today = new Date();
    var fr_date, to_date;

    switch(type) {
        case '오늘':
            fr_date = to_date = formatDate(today);
            break;
        case '어제':
            today.setDate(today.getDate() - 1);
            fr_date = to_date = formatDate(today);
            break;
        case '이번달':
            fr_date = today.getFullYear() + ('0' + (today.getMonth() + 1)).slice(-2) + '01';
            to_date = formatDate(today);
            break;
        case '지난주':
            var lastWeekEnd = new Date(today);
            lastWeekEnd.setDate(today.getDate() - today.getDay());
            var lastWeekStart = new Date(lastWeekEnd);
            lastWeekStart.setDate(lastWeekEnd.getDate() - 6);
            fr_date = formatDate(lastWeekStart);
            to_date = formatDate(lastWeekEnd);
            break;
        case '지난달':
            var lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            var lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);
            fr_date = formatDate(lastMonth);
            to_date = formatDate(lastMonthEnd);
            break;
        case '전체':
            fr_date = 'all';
            to_date = 'all';
            break;
    }

    $('#fr_date').val(fr_date);
    $('#to_date').val(to_date);
}

function formatDate(date) {
    var year = date.getFullYear();
    var month = ('0' + (date.getMonth() + 1)).slice(-2);
    var day = ('0' + date.getDate()).slice(-2);
    return year + month + day;
}

function copyUrl(url) {
    navigator.clipboard.writeText(url).then(function() {
        alert('URL이 복사되었습니다.\n' + url);
    }).catch(function() {
        prompt('URL을 복사하세요:', url);
    });
}

function sendSms(up_id, phone) {
    if(!confirm('SMS를 발송하시겠습니까?\n수신번호: ' + phone)) return;

    $.ajax({
        url: './?p=url_payment_sms',
        type: 'POST',
        dataType: 'json',
        data: { up_id: up_id },
        success: function(res) {
            if(res.success) {
                alert('SMS가 발송되었습니다.');
                location.reload();
            } else {
                alert('발송 실패: ' + res.message);
            }
        },
        error: function() {
            alert('오류가 발생했습니다.');
        }
    });
}

function cancelUrl(up_id) {
    if(!confirm('이 URL결제를 취소하시겠습니까?')) return;

    $.ajax({
        url: './?p=url_payment_process',
        type: 'POST',
        dataType: 'json',
        data: { action: 'cancel', up_id: up_id },
        success: function(res) {
            if(res.success) {
                alert('취소되었습니다.');
                location.reload();
            } else {
                alert('취소 실패: ' + res.message);
            }
        },
        error: function() {
            alert('오류가 발생했습니다.');
        }
    });
}
</script>

<?php
include_once('./_tail.php');
?>
