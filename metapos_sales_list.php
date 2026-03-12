<?php
/**
 * 스시이안앤 매출내역 페이지
 * - 일자별 매출 요약 (영수건수, 영수단가, 총매출)
 * - 관리자 또는 스시이안앤 매장ID가 등록된 가맹점 접근 가능
 */

$title1 = "스시이안앤";
$title2 = "매출내역";

// 특정 허용 아이디 목록 (본사처럼 전체 조회 가능)
$sushian_allowed_ids = array('1766037474', '1765765095', '1757467304');
$is_sushian_allowed = in_array(strval($member['mb_id']), $sushian_allowed_ids);

// 허용 아이디는 관리자처럼 취급
if($is_sushian_allowed) {
	$is_admin = true;
}

// 권한 체크: 관리자 또는 mb_sushian_id가 있는 가맹점(level=3)
$is_merchant_view = false;
$merchant_store_uid = '';

if(!$is_admin) {
	if($member['mb_level'] == 3 && !empty($member['mb_sushian_id'])) {
		$is_merchant_view = true;
		$merchant_store_uid = $member['mb_sushian_id'];
	}
	else {
		alert("접근 권한이 없습니다.");
	}
}

// 테이블명
$table_name = "metapos_payment";
$store_table = "metapos_store";

// 검색 조건
$stx = isset($_GET['stx']) ? sql_escape_string($_GET['stx']) : '';
$st_uid_filter = isset($_GET['st_uid']) ? sql_escape_string($_GET['st_uid']) : '';

// 검색 타입 (daily: 일별, monthly: 월별)
$date_type = isset($_GET['date_type']) ? $_GET['date_type'] : 'daily';

// 날짜 필터 (기본: 오늘)
if($date_type == 'monthly') {
	$search_year = isset($_GET['search_year']) ? intval($_GET['search_year']) : date('Y');
	$search_month = isset($_GET['search_month']) ? intval($_GET['search_month']) : date('m');
	$fr_date = sprintf('%04d%02d01', $search_year, $search_month);
	$to_date = date('Ymt', strtotime($fr_date));
} else {
	$fr_date = isset($_GET['fr_date']) ? $_GET['fr_date'] : date('Ym01');
	$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Ymd');
	$search_year = date('Y');
	$search_month = date('m');
}

// 날짜 포맷 변환
$fr_dates = substr($fr_date, 0, 4) . '-' . substr($fr_date, 4, 2) . '-' . substr($fr_date, 6, 2);
$to_dates = substr($to_date, 0, 4) . '-' . substr($to_date, 4, 2) . '-' . substr($to_date, 6, 2);

// WHERE 절 구성
$sql_where = " WHERE 1 ";
$sql_where .= " AND sal_ymd BETWEEN '{$fr_date}' AND '{$to_date}' ";

if($is_merchant_view) {
	$sql_where .= " AND st_uid = '{$merchant_store_uid}'";
	$st_uid_filter = $merchant_store_uid;
} else if($st_uid_filter) {
	$sql_where .= " AND st_uid = '{$st_uid_filter}'";
}

if($stx) {
	$sql_where .= " AND st_name LIKE '%{$stx}%'";
}

// 결제상태 필터
$bill_status = isset($_GET['bill_status']) ? $_GET['bill_status'] : '';
if($bill_status) {
	$sql_where .= " AND bill_status = '{$bill_status}'";
}

// 매장 목록 조회 (드롭다운용)
$store_list = array();
$sql = "SELECT DISTINCT st_uid, st_name FROM {$store_table} WHERE st_use = 'Y' ORDER BY st_name";
$store_result = sql_query($sql);
while($row = sql_fetch_array($store_result)) {
	$store_list[] = $row;
}

// 전체 통계 조회 (헤더용)
$sql = "SELECT
	COUNT(DISTINCT bill_no) as total_cnt,
	COUNT(DISTINCT IF(bill_status = 'S', bill_no, NULL)) as sale_cnt,
	COUNT(DISTINCT IF(bill_status = 'C', bill_no, NULL)) as cancel_cnt,
	SUM(IF(bill_status = 'S', pay_amount, 0)) as total_sale_amount,
	SUM(IF(bill_status = 'C', pay_amount, 0)) as total_cancel_amount,
	SUM(IF(bill_status = 'S' AND pay_method LIKE '%카드%', pay_amount, 0)) as card_sale_amount,
	SUM(IF(bill_status = 'S' AND pay_method LIKE '%카드%' AND g5_pay_id IS NOT NULL AND g5_pay_id != '', pay_amount, 0)) as card_pg_amount,
	SUM(IF(bill_status = 'S' AND pay_method LIKE '%카드%' AND (g5_pay_id IS NULL OR g5_pay_id = ''), pay_amount, 0)) as card_van_amount,
	SUM(IF(bill_status = 'S' AND (pay_method LIKE '%현금%' OR pay_method LIKE '%cash%'), pay_amount, 0)) as cash_sale_amount
	FROM {$table_name} {$sql_where}";
$stat = sql_fetch($sql);

// 부가세 (bill_no 단위 중복 제거)
$vat_sql = "SELECT COALESCE(SUM(vat), 0) as total_vat FROM (
	SELECT bill_no, MAX(bill_vat) as vat
	FROM {$table_name} {$sql_where} AND bill_status = 'S'
	GROUP BY bill_no
) vat_sub";
$vat_result = sql_fetch($vat_sql);
$stat['total_sale_vat'] = $vat_result['total_vat'];

// 일자별 매출 요약 쿼리 (매출 S 기준)
$sql_where_sales = $sql_where;
if(!$bill_status) {
	$sql_where_sales .= " AND bill_status = 'S'";
}

$sql = "SELECT
	sal_ymd,
	COUNT(DISTINCT bill_no) as receipt_cnt,
	SUM(pay_amount) as total_sales
	FROM {$table_name} {$sql_where_sales}
	GROUP BY sal_ymd
	ORDER BY sal_ymd ASC";
$daily_result = sql_query($sql);

// 일자별 데이터 수집 및 합계 계산
$daily_data = array();
$grand_receipt_cnt = 0;
$grand_total_sales = 0;
while($row = sql_fetch_array($daily_result)) {
	$daily_data[] = $row;
	$grand_receipt_cnt += $row['receipt_cnt'];
	$grand_total_sales += $row['total_sales'];
}
$grand_avg_price = ($grand_receipt_cnt > 0) ? round($grand_total_sales / $grand_receipt_cnt) : 0;

// 요일 이름
$day_names = array('일', '월', '화', '수', '목', '금', '토');

include_once('./_head.php');
?>

<style>
/* 메인 헤더 */
.metapos-pay-header {
	background: linear-gradient(135deg, #e65100 0%, #ff6d00 100%);
	border-radius: 8px;
	padding: 12px 16px;
	margin-bottom: 10px;
	box-shadow: 0 2px 8px rgba(230, 81, 0, 0.3);
}
.metapos-pay-header-top {
	display: flex;
	align-items: center;
	justify-content: space-between;
	flex-wrap: wrap;
	gap: 10px;
	margin-bottom: 10px;
}
.metapos-pay-header-bottom {
	display: flex;
	align-items: center;
	justify-content: flex-start;
}
.metapos-pay-title {
	color: #fff;
	font-size: 16px;
	font-weight: 600;
	display: flex;
	align-items: center;
	gap: 8px;
}
.metapos-pay-title i {
	font-size: 14px;
	opacity: 0.8;
}
.metapos-pay-stats {
	display: flex;
	flex-wrap: wrap;
	gap: 6px;
}
.metapos-pay-stat {
	display: inline-flex;
	align-items: center;
	background: rgba(255,255,255,0.12);
	border-radius: 4px;
	padding: 4px 10px;
	font-size: 12px;
	color: rgba(255,255,255,0.85);
	gap: 6px;
}
.metapos-pay-stat.sale {
	background: rgba(76,175,80,0.3);
}
.metapos-pay-stat.cancel {
	background: rgba(244,67,54,0.3);
}
.metapos-pay-stat span {
	color: #fff;
	font-weight: 600;
}

/* 검색 영역 */
.metapos-pay-search {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 10px 12px;
	margin-bottom: 10px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.metapos-pay-search-row {
	display: flex;
	align-items: center;
	flex-wrap: wrap;
	gap: 8px;
}
.metapos-pay-search-group {
	display: flex;
	align-items: center;
	gap: 4px;
}
.metapos-pay-search-group input[type="text"],
.metapos-pay-search-group select {
	padding: 6px 8px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-size: 13px;
	background: #f8f9fa;
}
.metapos-pay-search-group input[type="text"].date-input {
	width: 90px;
	text-align: center;
}
.metapos-pay-search-group input[type="text"]:focus,
.metapos-pay-search-group select:focus {
	outline: none;
	border-color: #e65100;
	background: #fff;
}
.search-divider {
	width: 1px;
	height: 24px;
	background: #e0e0e0;
	margin: 0 6px;
}
.btn-search {
	padding: 6px 12px;
	background: #e65100;
	color: #fff;
	border: none;
	border-radius: 4px;
	font-size: 12px;
	cursor: pointer;
	transition: background 0.15s;
}
.btn-search:hover {
	background: #ff6d00;
}

/* 검색 타입 탭 */
.search-type-tabs {
	display: flex;
	gap: 0;
	margin-bottom: 10px;
	border-bottom: 2px solid #e0e0e0;
}
.search-type-tab {
	padding: 8px 16px;
	font-size: 13px;
	font-weight: 500;
	color: #666;
	background: transparent;
	border: none;
	cursor: pointer;
	position: relative;
	transition: color 0.2s;
}
.search-type-tab:hover {
	color: #e65100;
}
.search-type-tab.active {
	color: #e65100;
	font-weight: 600;
}
.search-type-tab.active::after {
	content: '';
	position: absolute;
	left: 0;
	right: 0;
	bottom: -2px;
	height: 2px;
	background: #e65100;
}

/* 퀵버튼 */
.quick-btns {
	display: flex;
	gap: 4px;
	margin-left: 8px;
}
.quick-btn {
	padding: 5px 10px;
	font-size: 11px;
	background: #f5f5f5;
	border: 1px solid #ddd;
	border-radius: 4px;
	cursor: pointer;
	transition: all 0.15s;
	white-space: nowrap;
}
.quick-btn:hover {
	background: #fff3e0;
	border-color: #ff6d00;
	color: #e65100;
}
.quick-btn.active {
	background: #e65100;
	border-color: #e65100;
	color: #fff;
}

/* 월별 검색 */
.monthly-search {
	display: none;
}
.monthly-search.active {
	display: flex;
}
.daily-search {
	display: flex;
}
.daily-search.hidden {
	display: none;
}
.btn-sync {
	padding: 6px 12px;
	background: #1565c0;
	color: #fff;
	border: none;
	border-radius: 4px;
	font-size: 12px;
	cursor: pointer;
	transition: background 0.15s;
	display: inline-flex;
	align-items: center;
	gap: 4px;
}
.btn-sync:hover {
	background: #1976d2;
}
.btn-sync:disabled {
	background: #bdbdbd;
	cursor: not-allowed;
}

/* 매출 테이블 */
.sales-table-wrap {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	overflow: hidden;
	box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.sales-table-wrap table {
	width: 100%;
	border-collapse: collapse;
}
.sales-table-wrap thead th {
	background: #f8f9fa;
	padding: 10px 12px;
	font-size: 12px;
	font-weight: 600;
	color: #555;
	border-bottom: 2px solid #e0e0e0;
	white-space: nowrap;
	text-align: center;
}
.sales-table-wrap tbody td {
	padding: 10px 12px;
	font-size: 13px;
	border-bottom: 1px solid #f0f0f0;
	color: #333;
	text-align: center;
}
.sales-table-wrap tbody tr:hover {
	background: #fff8f0;
}
.sales-table-wrap tbody tr:last-child td {
	border-bottom: none;
}
.sales-table-wrap tfoot td {
	padding: 12px;
	font-size: 13px;
	font-weight: 700;
	background: linear-gradient(135deg, #e65100 0%, #ff6d00 100%);
	color: #fff;
	border-top: 2px solid #e65100;
	text-align: center;
}
td.amount {
	font-family: 'Courier New', monospace;
	font-weight: 600;
}
.day-sun { color: #c62828; font-weight: 600; }
.day-sat { color: #1565c0; font-weight: 600; }

/* 로딩/모달 */
.loading-overlay {
	position: fixed;
	left: 0; right: 0; top: 0; bottom: 0;
	background: rgba(255,255,255,0.95);
	z-index: 10001;
	display: none;
	flex-direction: column;
	align-items: center;
	justify-content: center;
}
.loading-overlay.show { display: flex; }
.loading-overlay .spinner {
	width: 50px; height: 50px;
	border: 4px solid #e0e0e0;
	border-top: 4px solid #e65100;
	border-radius: 50%;
	animation: spin 1s linear infinite;
}
@keyframes spin {
	0% { transform: rotate(0deg); }
	100% { transform: rotate(360deg); }
}
.loading-overlay .loading-text {
	margin-top: 20px;
	font-size: 14px;
	color: #666;
	font-weight: 500;
}
.sync-modal-overlay {
	position: fixed;
	left: 0; right: 0; top: 0; bottom: 0;
	background: rgba(0,0,0,0.5);
	z-index: 10000;
	display: none;
	justify-content: center;
	align-items: center;
	padding: 15px;
}
.sync-modal-overlay.show { display: flex; }
.sync-result-modal {
	width: 400px;
	background: #fff;
	border-radius: 12px;
	box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}
.sync-result-header {
	padding: 20px;
	text-align: center;
	border-radius: 12px 12px 0 0;
	background: linear-gradient(135deg, #E65100 0%, #FF6D00 100%);
}
.sync-result-header i { font-size: 40px; color: #fff; margin-bottom: 10px; }
.sync-result-header h3 { color: #fff; font-size: 18px; font-weight: 600; margin: 0; }
.sync-result-body { padding: 20px; text-align: center; }
.sync-result-body .stats { display: flex; justify-content: center; gap: 20px; margin-bottom: 15px; }
.sync-result-body .stat-item { text-align: center; }
.sync-result-body .stat-value { font-size: 24px; font-weight: 700; color: #333; }
.sync-result-body .stat-label { font-size: 12px; color: #666; }
.sync-result-body .stat-item.inserted .stat-value { color: #2e7d32; }
.sync-result-footer { padding: 0 20px 20px; }
.sync-result-footer .btn {
	width: 100%; border: none; border-radius: 6px; font-size: 14px; font-weight: 600;
	cursor: pointer; background: #e65100; color: #fff; transition: background 0.2s; white-space: nowrap;
}
.sync-result-footer .btn:hover { background: #ff6d00; }

/* 데이터 없음 */
.no-data {
	text-align: center;
	padding: 40px 0;
	color: #999;
}
.no-data i {
	font-size: 32px;
	display: block;
	margin-bottom: 10px;
}

/* 반응형 */
@media (max-width: 768px) {
	.metapos-pay-header-top {
		flex-direction: row;
		align-items: center;
		justify-content: space-between;
	}
	.metapos-pay-header-bottom {
		flex-direction: column;
		align-items: flex-start;
	}
	.metapos-pay-stats {
		width: 100%;
		flex-wrap: wrap;
		gap: 4px;
	}
	.metapos-pay-stat {
		font-size: 10px;
		padding: 3px 6px;
	}
	.metapos-pay-search { padding: 8px; }
	.search-type-tabs { margin-bottom: 8px; }
	.search-type-tab { padding: 6px 12px; font-size: 12px; }
	.metapos-pay-search-row {
		flex-direction: column;
		align-items: stretch;
		gap: 6px;
	}
	.metapos-pay-search-group {
		width: 100%;
		flex-wrap: wrap;
	}
	.metapos-pay-search-group select {
		flex: 1;
		min-width: 0;
	}
	.search-divider { display: none; }
	.quick-btns {
		width: 100%;
		margin-left: 0;
		margin-top: 6px;
	}
	.quick-btn { flex: 1; text-align: center; }
	.daily-search, .monthly-search.active { flex-wrap: wrap; }
	.sales-table-wrap { border-radius: 0; }
}
</style>

<?php if($member['mb_level'] >= 10) { ?>
<!-- 로딩 스피너 -->
<div class="loading-overlay" id="loadingOverlay">
	<div class="spinner"></div>
	<div class="loading-text">결제 데이터 동기화 중...</div>
</div>

<!-- 동기화 결과 모달 -->
<div class="sync-modal-overlay" id="syncResultOverlay">
	<div class="sync-result-modal">
		<div class="sync-result-header">
			<i class="fa fa-refresh"></i>
			<h3>동기화 완료</h3>
		</div>
		<div class="sync-result-body">
			<div class="stats">
				<div class="stat-item inserted">
					<div class="stat-value" id="syncInserted">0</div>
					<div class="stat-label">신규</div>
				</div>
				<div class="stat-item">
					<div class="stat-value" id="syncSkipped">0</div>
					<div class="stat-label">기존</div>
				</div>
				<div class="stat-item" style="color:#1565c0;">
					<div class="stat-value" id="syncMatched">0</div>
					<div class="stat-label">PG매칭</div>
				</div>
				<div class="stat-item" style="color:#e65100;">
					<div class="stat-value" id="syncStatusChanged">0</div>
					<div class="stat-label">취소변경</div>
				</div>
			</div>
			<div id="syncMessage" style="font-size:13px; color:#666;"></div>
		</div>
		<div class="sync-result-footer"><button type="button" class="btn" onclick="closeSyncResult()">확인</button></div>
	</div>
</div>
<?php } ?>

<div class="metapos-pay-header">
	<div class="metapos-pay-header-top">
		<div class="metapos-pay-title">
			<i class="fa fa-bar-chart"></i>
			스시이안앤 매출내역
		</div>
	</div>
	<div class="metapos-pay-header-bottom">
		<div class="metapos-pay-stats">
			<div class="metapos-pay-stat">
				전체 <span><?php echo number_format($stat['total_cnt']); ?>건</span>
			</div>
			<div class="metapos-pay-stat sale">
				순매출 <span><?php echo number_format($stat['total_sale_amount']); ?>원</span>
			</div>
			<div class="metapos-pay-stat" style="background: rgba(100,181,246,0.3);">
				카드(일반) <span><?php echo number_format($stat['card_pg_amount']); ?>원</span>
			</div>
			<div class="metapos-pay-stat" style="background: rgba(206,147,255,0.3);">
				카드(기타) <span><?php echo number_format($stat['card_van_amount']); ?>원</span>
			</div>
			<div class="metapos-pay-stat" style="background: rgba(129,199,132,0.3);">
				현금 <span><?php echo number_format($stat['cash_sale_amount']); ?>원</span>
			</div>
			<div class="metapos-pay-stat" style="background: rgba(255,193,7,0.3);">
				부가세 <span><?php echo number_format($stat['total_sale_vat']); ?>원</span>
			</div>
		</div>
	</div>
</div>

<form id="fsearch" name="fsearch" method="get">
<input type="hidden" name="p" value="<?php echo $p; ?>">
<input type="hidden" name="date_type" id="date_type" value="<?php echo $date_type; ?>">
<div class="metapos-pay-search">
	<!-- 검색 타입 탭 -->
	<div class="search-type-tabs">
		<button type="button" class="search-type-tab <?php echo $date_type == 'daily' ? 'active' : ''; ?>" onclick="setDateType('daily')">일별</button>
		<button type="button" class="search-type-tab <?php echo $date_type == 'monthly' ? 'active' : ''; ?>" onclick="setDateType('monthly')">월별</button>
	</div>

	<div class="metapos-pay-search-row">
		<!-- 일별 검색 -->
		<div class="metapos-pay-search-group daily-search <?php echo $date_type == 'monthly' ? 'hidden' : ''; ?>">
			<input type="text" name="fr_date" id="fr_date" class="date-input" value="<?php echo $fr_date; ?>" placeholder="시작일" maxlength="8">
			<span>~</span>
			<input type="text" name="to_date" id="to_date" class="date-input" value="<?php echo $to_date; ?>" placeholder="종료일" maxlength="8">
			<div class="quick-btns">
				<button type="button" class="quick-btn" onclick="setQuickDate('today')">오늘</button>
				<button type="button" class="quick-btn" onclick="setQuickDate('yesterday')">어제</button>
				<button type="button" class="quick-btn" onclick="setQuickDate('thisMonth')">이번달</button>
				<button type="button" class="quick-btn" onclick="setQuickDate('lastMonth')">저번달</button>
			</div>
		</div>

		<!-- 월별 검색 -->
		<div class="metapos-pay-search-group monthly-search <?php echo $date_type == 'monthly' ? 'active' : ''; ?>">
			<select name="search_year" id="search_year" style="width:90px;">
				<?php for($y = date('Y'); $y >= date('Y') - 3; $y--) { ?>
				<option value="<?php echo $y; ?>" <?php if($search_year == $y) echo 'selected'; ?>><?php echo $y; ?>년</option>
				<?php } ?>
			</select>
			<select name="search_month" id="search_month" style="width:70px;">
				<?php for($m = 1; $m <= 12; $m++) { ?>
				<option value="<?php echo $m; ?>" <?php if($search_month == $m) echo 'selected'; ?>><?php echo $m; ?>월</option>
				<?php } ?>
			</select>
			<div class="quick-btns">
				<button type="button" class="quick-btn" onclick="setQuickMonth('thisMonth')">이번달</button>
				<button type="button" class="quick-btn" onclick="setQuickMonth('lastMonth')">저번달</button>
			</div>
		</div>

		<?php if(!$is_merchant_view) { ?>
		<div class="search-divider"></div>
		<div class="metapos-pay-search-group">
			<select name="st_uid">
				<option value="">매장 전체</option>
				<?php foreach($store_list as $store) { ?>
				<option value="<?php echo $store['st_uid']; ?>" <?php if($st_uid_filter == $store['st_uid']) echo 'selected'; ?>><?php echo htmlspecialchars($store['st_name']); ?></option>
				<?php } ?>
			</select>
		</div>
		<?php } else { ?>
		<input type="hidden" name="st_uid" value="<?php echo htmlspecialchars($merchant_store_uid); ?>">
		<?php } ?>
		<div class="metapos-pay-search-group">
			<select name="bill_status">
				<option value="">상태 전체</option>
				<option value="S" <?php if($bill_status == 'S') echo 'selected'; ?>>매출</option>
				<option value="C" <?php if($bill_status == 'C') echo 'selected'; ?>>취소</option>
			</select>
		</div>
		<div class="search-divider"></div>
		<div class="metapos-pay-search-group">
			<input type="text" name="stx" value="<?php echo htmlspecialchars($stx); ?>" placeholder="매장명 검색" style="width:140px;">
			<button type="submit" class="btn-search"><i class="fa fa-search"></i> 검색</button>
		</div>
	</div>
</div>
</form>

<!-- 일자별 매출 테이블 -->
<div class="sales-table-wrap">
	<table>
		<thead>
			<tr>
				<th class="center" style="width:130px;">매출일자</th>
				<th class="center" style="width:60px;">요일</th>
				<th class="center" style="width:120px;">영수건수</th>
				<th class="center" style="width:140px;">영수단가</th>
				<th class="center" style="width:160px;">총매출</th>
			</tr>
		</thead>
		<tbody>
			<?php
			if(count($daily_data) == 0) {
			?>
			<tr>
				<td colspan="5" class="no-data">
					<i class="fa fa-inbox"></i>
					조회된 매출내역이 없습니다.
				</td>
			</tr>
			<?php
			} else {
				foreach($daily_data as $row) {
					$ymd = $row['sal_ymd'];
					$formatted_date = substr($ymd, 0, 4) . '-' . substr($ymd, 4, 2) . '-' . substr($ymd, 6, 2);
					$day_idx = date('w', strtotime($formatted_date));
					$day_name = $day_names[$day_idx];
					$day_class = '';
					if($day_idx == 0) $day_class = 'day-sun';
					else if($day_idx == 6) $day_class = 'day-sat';

					$receipt_cnt = (int)$row['receipt_cnt'];
					$total_sales = (int)$row['total_sales'];
					$avg_price = ($receipt_cnt > 0) ? round($total_sales / $receipt_cnt) : 0;
			?>
			<tr>
				<td class="center"><?php echo $formatted_date; ?></td>
				<td class="center <?php echo $day_class; ?>"><?php echo $day_name; ?></td>
				<td class="center amount"><?php echo number_format($receipt_cnt); ?></td>
				<td class="center amount"><?php echo number_format($avg_price); ?></td>
				<td class="center amount"><?php echo number_format($total_sales); ?></td>
			</tr>
			<?php
				}
			}
			?>
		</tbody>
		<?php if(count($daily_data) > 0) { ?>
		<tfoot>
			<tr>
				<td class="center" colspan="2"><i class="fa fa-calculator"></i> 합계</td>
				<td class="center"><?php echo number_format($grand_receipt_cnt); ?></td>
				<td class="center"><?php echo number_format($grand_avg_price); ?></td>
				<td class="center"><?php echo number_format($grand_total_sales); ?></td>
			</tr>
		</tfoot>
		<?php } ?>
	</table>
</div>

<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
<script>
$(function() {
	$(".date-input").datepicker({
		dateFormat: "yymmdd",
		changeMonth: true,
		changeYear: true,
		showButtonPanel: true
	});
});

function setDateType(type) {
	$('#date_type').val(type);
	$('.search-type-tab').removeClass('active');
	$('.search-type-tab').each(function() {
		if($(this).text().trim() == (type == 'daily' ? '일별' : '월별')) {
			$(this).addClass('active');
		}
	});

	if(type == 'daily') {
		$('.daily-search').removeClass('hidden');
		$('.monthly-search').removeClass('active');
	} else {
		$('.daily-search').addClass('hidden');
		$('.monthly-search').addClass('active');
	}
}

function setQuickDate(type) {
	var today = new Date();
	var fr, to;

	switch(type) {
		case 'today':
			fr = to = formatDate(today);
			break;
		case 'yesterday':
			var yesterday = new Date(today);
			yesterday.setDate(yesterday.getDate() - 1);
			fr = to = formatDate(yesterday);
			break;
		case 'thisMonth':
			fr = today.getFullYear() + String(today.getMonth() + 1).padStart(2, '0') + '01';
			to = formatDate(today);
			break;
		case 'lastMonth':
			var lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
			var lastDay = new Date(today.getFullYear(), today.getMonth(), 0);
			fr = formatDate(lastMonth);
			to = formatDate(lastDay);
			break;
	}

	$('#fr_date').val(fr);
	$('#to_date').val(to);
	$('#fsearch').submit();
}

function setQuickMonth(type) {
	var today = new Date();
	var year, month;

	if(type == 'thisMonth') {
		year = today.getFullYear();
		month = today.getMonth() + 1;
	} else {
		var lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
		year = lastMonth.getFullYear();
		month = lastMonth.getMonth() + 1;
	}

	$('#search_year').val(year);
	$('#search_month').val(month);
	$('#fsearch').submit();
}

function formatDate(date) {
	var y = date.getFullYear();
	var m = String(date.getMonth() + 1).padStart(2, '0');
	var d = String(date.getDate()).padStart(2, '0');
	return y + m + d;
}

<?php if($member['mb_level'] >= 10) { ?>
function syncPayments() {
	var syncDate = '<?php echo substr($fr_date, 0, 4) . "-" . substr($fr_date, 4, 2) . "-" . substr($fr_date, 6, 2); ?>';
	if(!confirm('[' + syncDate + '] 날짜의 결제 데이터를 동기화하시겠습니까?\n\n※ 검색 시작일 기준 하루치만 동기화됩니다.')) return;

	$('#loadingOverlay').addClass('show');
	$('#btnSync').prop('disabled', true);

	$.ajax({
		url: './worker/metapos_paytype.php?sal_ymd=<?php echo $fr_date; ?>',
		type: 'GET',
		dataType: 'json',
		success: function(response) {
			$('#loadingOverlay').removeClass('show');
			$('#btnSync').prop('disabled', false);

			if(response.status == 'success') {
				$('#syncInserted').text(response.sync_total.inserted);
				$('#syncSkipped').text(response.sync_total.skipped);
				$('#syncMatched').text(response.sync_total.matched);
				$('#syncStatusChanged').text(response.sync_total.status_changed || 0);
				$('#syncMessage').text('총 ' + response.store_count + '개 매장 처리 완료');
				$('#syncResultOverlay').addClass('show');
			} else {
				alert('동기화 실패: ' + (response.error || response.message || '알 수 없는 오류'));
			}
		},
		error: function(xhr, status, error) {
			$('#loadingOverlay').removeClass('show');
			$('#btnSync').prop('disabled', false);
			alert('서버 오류가 발생했습니다: ' + error);
		}
	});
}

function closeSyncResult() {
	$('#syncResultOverlay').removeClass('show');
	location.reload();
}

$(document).keydown(function(e) {
	if(e.keyCode == 27) {
		closeSyncResult();
	}
});

$('#syncResultOverlay').click(function(e) {
	if(e.target === this) {
		closeSyncResult();
	}
});
<?php } ?>
</script>

<?php
include_once('./_tail.php');
?>
