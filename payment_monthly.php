<?php
$title1 = "결제관리";
$title2 = "월 결제내역";

// 관리자 전용
if(!$is_admin) {
    alert("관리자만 접근할 수 있습니다.");
}

// 기간 모드: recent12 (디폴트, 최근12개월) / year (년도별)
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'recent12';
$year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));
if($year < 2020 || $year > intval(date('Y')) + 1) {
    $year = intval(date('Y'));
}

// 기간 설정
if($mode === 'year') {
    $date_from = "{$year}-01-01 00:00:00";
    $date_to = "{$year}-12-31 23:59:59";
    $period_label = "{$year}년";
} else {
    // 최근 12개월 (이번 달 포함)
    $date_from = date('Y-m-01 00:00:00', strtotime('-11 months'));
    $date_to = date('Y-m-t 23:59:59');
    $period_label = substr($date_from, 0, 7) . " ~ " . substr($date_to, 0, 7);
}

// 월별 집계 쿼리
$sql = "SELECT
    DATE_FORMAT(pay_datetime, '%Y-%m') as pay_month,
    COUNT(DISTINCT CASE WHEN dv_type = '1' THEN mb_6 END) as offline_merchants,
    COUNT(DISTINCT CASE WHEN dv_type = '2' THEN mb_6 END) as online_merchants,
    COUNT(DISTINCT mb_6) as total_merchants,
    COUNT(IF(pay_type = 'Y', 1, NULL)) as approve_cnt,
    SUM(IF(pay_type = 'Y', pay, 0)) as approve_amt,
    COUNT(IF(pay_type != 'Y', 1, NULL)) as cancel_cnt,
    SUM(IF(pay_type != 'Y', ABS(pay), 0)) as cancel_amt,
    COUNT(IF(pay_type = 'Y', 1, NULL)) - COUNT(IF(pay_type != 'Y', 1, NULL)) as net_cnt,
    SUM(IF(pay_type = 'Y', pay, 0)) - SUM(IF(pay_type != 'Y', ABS(pay), 0)) as net_amt
FROM g5_payment
WHERE pay_datetime BETWEEN '{$date_from}' AND '{$date_to}'
GROUP BY pay_month
ORDER BY pay_month ASC";

$result = sql_query($sql);

// 합계 계산 + 차트 데이터 구성
$sum_approve_cnt = 0;
$sum_approve_amt = 0;
$sum_cancel_cnt = 0;
$sum_cancel_amt = 0;
$sum_net_cnt = 0;
$sum_net_amt = 0;
$rows_data = [];
$chart_labels = [];
$chart_approve = [];
$chart_cancel = [];
$chart_net = [];
$chart_merchants = [];

while($row = sql_fetch_array($result)) {
    $rows_data[] = $row;
    $sum_approve_cnt += $row['approve_cnt'];
    $sum_approve_amt += $row['approve_amt'];
    $sum_cancel_cnt += $row['cancel_cnt'];
    $sum_cancel_amt += $row['cancel_amt'];
    $sum_net_cnt += $row['net_cnt'];
    $sum_net_amt += $row['net_amt'];

    // 차트 데이터
    $m = intval(substr($row['pay_month'], 5, 2));
    $chart_labels[] = $m . '월';
    $chart_approve[] = intval($row['approve_amt']);
    $chart_cancel[] = intval($row['cancel_amt']);
    $chart_net[] = intval($row['net_amt']);
    $chart_merchants[] = intval($row['total_merchants']);
}

// 전체 기간 고유 가맹점 수
$period_merchants = sql_fetch("SELECT
    COUNT(DISTINCT CASE WHEN dv_type = '1' THEN mb_6 END) as offline_merchants,
    COUNT(DISTINCT CASE WHEN dv_type = '2' THEN mb_6 END) as online_merchants,
    COUNT(DISTINCT mb_6) as total_merchants
FROM g5_payment
WHERE pay_datetime BETWEEN '{$date_from}' AND '{$date_to}'");
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

<style>
.monthly-header {
    background: linear-gradient(135deg, #00897b 0%, #26a69a 100%);
    border-radius: 8px;
    padding: 14px 18px;
    margin-bottom: 12px;
    box-shadow: 0 2px 12px rgba(0, 137, 123, 0.25);
}

.monthly-header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.monthly-header-title {
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.monthly-header-title i { font-size: 18px; }

.monthly-header-period {
    color: rgba(255,255,255,0.8);
    font-size: 12px;
    background: rgba(255,255,255,0.12);
    padding: 3px 10px;
    border-radius: 4px;
}

.monthly-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-top: 12px;
}

.monthly-stat {
    background: rgba(255,255,255,0.12);
    border-radius: 8px;
    padding: 12px 14px;
    color: #fff;
    backdrop-filter: blur(4px);
}

.monthly-stat .stat-label {
    font-size: 11px;
    opacity: 0.8;
    margin-bottom: 4px;
    letter-spacing: 0.3px;
}

.monthly-stat .stat-value {
    font-size: 20px;
    font-weight: 700;
    line-height: 1.2;
}

.monthly-stat .stat-sub {
    font-size: 11px;
    opacity: 0.7;
    margin-top: 2px;
}

.monthly-stat.approve .stat-value { color: #a5d6a7; }
.monthly-stat.cancel .stat-value { color: #ef9a9a; }
.monthly-stat.net .stat-value { color: #fff176; }

/* 검색 */
.monthly-search {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 10px 16px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.monthly-search label {
    font-size: 13px;
    font-weight: 600;
    color: #333;
}

.monthly-search select {
    padding: 6px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
    background: #f8f9fa;
}

.monthly-search select:focus { outline: none; border-color: #00897b; }

.monthly-search .btn-search {
    padding: 6px 16px;
    background: #00897b;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s;
}

.monthly-search .btn-search:hover { background: #00796b; }

.monthly-search .btn-mode {
    padding: 6px 14px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
    cursor: pointer;
    background: #fff;
    color: #666;
    transition: all 0.15s;
}

.monthly-search .btn-mode.active {
    background: #00897b;
    color: #fff;
    border-color: #00897b;
}

/* 차트 영역 */
.chart-container {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 12px;
}

.chart-tabs {
    display: flex;
    gap: 4px;
    margin-bottom: 16px;
}

.chart-tab {
    padding: 6px 14px;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    background: #fff;
    color: #666;
    transition: all 0.15s;
}

.chart-tab:hover { background: #f5f5f5; }
.chart-tab.active { background: #00897b; color: #fff; border-color: #00897b; }

.chart-wrap {
    position: relative;
    height: 280px;
}

/* 테이블 */
.table_list tr.sum-row td {
    background: #e0f2f1 !important;
    font-weight: 700;
    border-top: 2px solid #00897b;
}

.table_list .month-cell { font-weight: 600; color: #00897b; }
.table_list .amt-cell { text-align: right; font-variant-numeric: tabular-nums; }
.table_list .cancel-cell { color: #e53935; }
.table_list .net-cell { color: #1565c0; font-weight: 600; }

@media (max-width: 768px) {
    .monthly-stats { grid-template-columns: repeat(2, 1fr); }
    .monthly-search { flex-direction: column; align-items: flex-start; }
    .chart-wrap { height: 220px; }
}
</style>

<!-- 헤더 -->
<div class="monthly-header">
    <div class="monthly-header-top">
        <div class="monthly-header-title">
            <i class="fa fa-calendar"></i>
            <span>월 결제내역</span>
        </div>
        <div class="monthly-header-period"><?php echo $period_label; ?></div>
    </div>
    <div class="monthly-stats">
        <div class="monthly-stat">
            <div class="stat-label">가맹점</div>
            <div class="stat-value"><?php echo number_format($period_merchants['total_merchants']); ?></div>
            <div class="stat-sub">오프 <?php echo number_format($period_merchants['offline_merchants']); ?> / 온 <?php echo number_format($period_merchants['online_merchants']); ?></div>
        </div>
        <div class="monthly-stat approve">
            <div class="stat-label">총 승인</div>
            <div class="stat-value"><?php echo number_format($sum_approve_amt); ?></div>
            <div class="stat-sub"><?php echo number_format($sum_approve_cnt); ?>건</div>
        </div>
        <div class="monthly-stat cancel">
            <div class="stat-label">총 취소</div>
            <div class="stat-value"><?php echo number_format($sum_cancel_amt); ?></div>
            <div class="stat-sub"><?php echo number_format($sum_cancel_cnt); ?>건</div>
        </div>
        <div class="monthly-stat net">
            <div class="stat-label">순매출</div>
            <div class="stat-value"><?php echo number_format($sum_net_amt); ?></div>
            <div class="stat-sub"><?php echo number_format($sum_net_cnt); ?>건</div>
        </div>
    </div>
</div>

<!-- 검색 -->
<form method="get" class="monthly-search" id="searchForm">
    <input type="hidden" name="p" value="payment_monthly">
    <input type="hidden" name="mode" id="searchMode" value="<?php echo $mode; ?>">

    <button type="button" class="btn-mode <?php echo $mode === 'recent12' ? 'active' : ''; ?>" onclick="setMode('recent12')">최근 12개월</button>

    <button type="button" class="btn-mode <?php echo $mode === 'year' ? 'active' : ''; ?>" onclick="setMode('year')">년도별</button>

    <select name="year" id="yearSelect" <?php echo $mode === 'recent12' ? 'style="display:none;"' : ''; ?>>
        <?php for($y = intval(date('Y')); $y >= 2024; $y--) { ?>
        <option value="<?php echo $y; ?>" <?php if($year == $y) echo 'selected'; ?>><?php echo $y; ?>년</option>
        <?php } ?>
    </select>

    <button type="submit" class="btn-search"><i class="fa fa-search"></i> 조회</button>
</form>

<!-- 차트 -->
<?php if(!empty($rows_data)) { ?>
<div class="chart-container">
    <div class="chart-tabs">
        <button class="chart-tab active" onclick="switchChart('amount', this)">금액</button>
        <button class="chart-tab" onclick="switchChart('count', this)">건수</button>
        <button class="chart-tab" onclick="switchChart('merchants', this)">가맹점</button>
    </div>
    <div class="chart-wrap">
        <canvas id="monthlyChart"></canvas>
    </div>
</div>
<?php } ?>

<!-- 테이블 -->
<div class="m_board_scroll">
    <div class="m_table_wrap">
        <table class="table_list td_pd">
            <thead>
                <tr>
                    <th rowspan="2" style="width:70px;">월</th>
                    <th colspan="3" style="background:#e0f2f1;">가맹점 수</th>
                    <th colspan="2" style="background:#e8f5e9;">승인</th>
                    <th colspan="2" style="background:#ffebee;">취소</th>
                    <th colspan="2" style="background:#e3f2fd;">순매출</th>
                </tr>
                <tr>
                    <th style="background:#e0f2f1;">오프라인</th>
                    <th style="background:#e0f2f1;">온라인</th>
                    <th style="background:#e0f2f1;">합계</th>
                    <th style="background:#e8f5e9;">건수</th>
                    <th style="background:#e8f5e9;">금액</th>
                    <th style="background:#ffebee;">건수</th>
                    <th style="background:#ffebee;">금액</th>
                    <th style="background:#e3f2fd;">건수</th>
                    <th style="background:#e3f2fd;">금액</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($rows_data)) { ?>
                <tr>
                    <td colspan="10" style="text-align:center; padding:40px; color:#999;">데이터가 없습니다.</td>
                </tr>
                <?php } else { ?>
                <?php foreach($rows_data as $row) {
                    $ym = explode('-', $row['pay_month']);
                    $month_label = intval($ym[0]) . '.' . intval($ym[1]);
                ?>
                <tr>
                    <td class="month-cell" style="text-align:center;"><?php echo $month_label; ?></td>
                    <td class="amt-cell"><?php echo number_format($row['offline_merchants']); ?></td>
                    <td class="amt-cell"><?php echo number_format($row['online_merchants']); ?></td>
                    <td class="amt-cell"><strong><?php echo number_format($row['total_merchants']); ?></strong></td>
                    <td class="amt-cell"><?php echo number_format($row['approve_cnt']); ?></td>
                    <td class="amt-cell"><?php echo number_format($row['approve_amt']); ?></td>
                    <td class="amt-cell cancel-cell"><?php echo number_format($row['cancel_cnt']); ?></td>
                    <td class="amt-cell cancel-cell"><?php echo number_format($row['cancel_amt']); ?></td>
                    <td class="amt-cell net-cell"><?php echo number_format($row['net_cnt']); ?></td>
                    <td class="amt-cell net-cell"><?php echo number_format($row['net_amt']); ?></td>
                </tr>
                <?php } ?>
                <tr class="sum-row">
                    <td style="text-align:center;"><strong>합계</strong></td>
                    <td class="amt-cell"><?php echo number_format($period_merchants['offline_merchants']); ?></td>
                    <td class="amt-cell"><?php echo number_format($period_merchants['online_merchants']); ?></td>
                    <td class="amt-cell"><strong><?php echo number_format($period_merchants['total_merchants']); ?></strong></td>
                    <td class="amt-cell"><?php echo number_format($sum_approve_cnt); ?></td>
                    <td class="amt-cell"><?php echo number_format($sum_approve_amt); ?></td>
                    <td class="amt-cell cancel-cell"><?php echo number_format($sum_cancel_cnt); ?></td>
                    <td class="amt-cell cancel-cell"><?php echo number_format($sum_cancel_amt); ?></td>
                    <td class="amt-cell net-cell"><?php echo number_format($sum_net_cnt); ?></td>
                    <td class="amt-cell net-cell"><?php echo number_format($sum_net_amt); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// 검색 모드 전환
function setMode(m) {
    document.getElementById('searchMode').value = m;
    document.querySelectorAll('.btn-mode').forEach(function(b) { b.classList.remove('active'); });
    event.target.classList.add('active');
    document.getElementById('yearSelect').style.display = (m === 'year') ? '' : 'none';
}

<?php if(!empty($rows_data)) { ?>
// 차트 데이터
var chartLabels = <?php echo json_encode($chart_labels); ?>;
var chartData = {
    amount: {
        datasets: [
            { label: '승인금액', data: <?php echo json_encode($chart_approve); ?>, backgroundColor: 'rgba(76, 175, 80, 0.15)', borderColor: '#4caf50', borderWidth: 2.5, fill: true, tension: 0.35, pointRadius: 4, pointBackgroundColor: '#4caf50', pointHoverRadius: 6 },
            { label: '취소금액', data: <?php echo json_encode($chart_cancel); ?>, backgroundColor: 'rgba(229, 57, 53, 0.1)', borderColor: '#e53935', borderWidth: 2, fill: true, tension: 0.35, pointRadius: 3, pointBackgroundColor: '#e53935', pointHoverRadius: 5, borderDash: [4, 3] },
            { label: '순매출', data: <?php echo json_encode($chart_net); ?>, backgroundColor: 'rgba(21, 101, 192, 0.08)', borderColor: '#1565c0', borderWidth: 2.5, fill: false, tension: 0.35, pointRadius: 4, pointBackgroundColor: '#1565c0', pointHoverRadius: 6 }
        ]
    },
    count: {
        datasets: [
            { label: '승인건수', data: <?php echo json_encode(array_column($rows_data, 'approve_cnt')); ?>, backgroundColor: '#4caf50', borderColor: '#4caf50', borderWidth: 1, borderRadius: 4, barPercentage: 0.6 },
            { label: '취소건수', data: <?php echo json_encode(array_column($rows_data, 'cancel_cnt')); ?>, backgroundColor: '#e53935', borderColor: '#e53935', borderWidth: 1, borderRadius: 4, barPercentage: 0.6 }
        ]
    },
    merchants: {
        datasets: [
            { label: '오프라인', data: <?php echo json_encode(array_column($rows_data, 'offline_merchants')); ?>, backgroundColor: '#00897b', borderColor: '#00897b', borderWidth: 1, borderRadius: 4, barPercentage: 0.5 },
            { label: '온라인', data: <?php echo json_encode(array_column($rows_data, 'online_merchants')); ?>, backgroundColor: '#42a5f5', borderColor: '#42a5f5', borderWidth: 1, borderRadius: 4, barPercentage: 0.5 }
        ]
    }
};

var chartTypes = { amount: 'line', count: 'bar', merchants: 'bar' };
var currentChart = null;

function renderChart(mode) {
    if(currentChart) currentChart.destroy();

    var isBar = chartTypes[mode] === 'bar';
    var cfg = {
        type: chartTypes[mode],
        data: { labels: chartLabels, datasets: chartData[mode].datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { usePointStyle: true, padding: 16, font: { size: 12 } } },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleFont: { size: 13 },
                    bodyFont: { size: 12 },
                    padding: 12,
                    cornerRadius: 6,
                    callbacks: {
                        label: function(ctx) {
                            var v = ctx.parsed.y;
                            if(mode === 'amount') return ctx.dataset.label + ': ' + v.toLocaleString() + '원';
                            if(mode === 'count') return ctx.dataset.label + ': ' + v.toLocaleString() + '건';
                            return ctx.dataset.label + ': ' + v.toLocaleString() + '개';
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 12 } } },
                y: {
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: {
                        font: { size: 11 },
                        callback: function(v) {
                            if(mode === 'amount') {
                                if(v >= 100000000) return (v/100000000).toFixed(1) + '억';
                                if(v >= 10000) return (v/10000).toFixed(0) + '만';
                                return v;
                            }
                            return v.toLocaleString();
                        }
                    },
                    beginAtZero: true
                }
            }
        }
    };

    if(isBar) cfg.options.scales.x.stacked = false;

    currentChart = new Chart(document.getElementById('monthlyChart'), cfg);
}

function switchChart(mode, btn) {
    document.querySelectorAll('.chart-tab').forEach(function(t) { t.classList.remove('active'); });
    btn.classList.add('active');
    renderChart(mode);
}

// 초기 렌더
renderChart('amount');
<?php } ?>
</script>
