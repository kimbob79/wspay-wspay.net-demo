<?php
/**
 * 스시이안앤 결제정보 목록 페이지
 * - metapos_payment 테이블 조회
 * - 관리자 또는 스시이안앤 매장ID가 등록된 가맹점 접근 가능
 * - 가맹점별 결제내역 + 총합계
 */

$title1 = "스시이안앤";
$title2 = "결제정보";

// 특정 허용 아이디 목록 (본사처럼 전체 조회 가능)
$sushian_allowed_ids = array('1766037474', '1765765095', '1757467304');
$is_sushian_allowed = in_array(strval($member['mb_id']), $sushian_allowed_ids);

// 허용 아이디는 관리자처럼 취급
if($is_sushian_allowed) {
	$is_admin = true;
}

// 권한 체크: 관리자 또는 mb_sushian_id가 있는 가맹점(level=3)
$is_merchant_view = false; // 가맹점 뷰 모드
$merchant_store_uid = ''; // 가맹점의 매장 UID

if(!$is_admin) {
	// 가맹점(level=3)이고 mb_sushian_id가 있는 경우
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

// 날짜 필터 (기본: 최근 30일)
$fr_date = isset($_GET['fr_date']) ? $_GET['fr_date'] : date('Ymd', strtotime('-30 days'));
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Ymd');

// 날짜 포맷 변환
$fr_dates = substr($fr_date, 0, 4) . '-' . substr($fr_date, 4, 2) . '-' . substr($fr_date, 6, 2);
$to_dates = substr($to_date, 0, 4) . '-' . substr($to_date, 4, 2) . '-' . substr($to_date, 6, 2);

// WHERE 절 구성 (통계용 - prefix 없음)
$sql_where = " WHERE 1 ";

// 날짜 필터 (sal_ymd 컬럼)
$sql_where .= " AND sal_ymd BETWEEN '{$fr_date}' AND '{$to_date}' ";

// 가맹점 뷰일 경우 자기 매장만 조회 (강제 필터)
if($is_merchant_view) {
	$sql_where .= " AND st_uid = '{$merchant_store_uid}'";
	$st_uid_filter = $merchant_store_uid; // 필터값도 고정
}
// 매장 필터 (관리자용)
else if($st_uid_filter) {
	$sql_where .= " AND st_uid = '{$st_uid_filter}'";
}

// 매장명 검색
if($stx) {
	$sql_where .= " AND st_name LIKE '%{$stx}%'";
}

// 결제상태 필터 (매출만)
$bill_status = isset($_GET['bill_status']) ? $_GET['bill_status'] : '';
if($bill_status) {
	$sql_where .= " AND bill_status = '{$bill_status}'";
}

// 목록 쿼리용 WHERE (mp. prefix)
$sql_where_mp = str_replace(
	array('sal_ymd', 'st_uid', 'st_name', 'bill_status'),
	array('mp.sal_ymd', 'mp.st_uid', 'mp.st_name', 'mp.bill_status'),
	$sql_where
);

// 정렬
$sst = isset($_GET['sst']) ? sql_escape_string($_GET['sst']) : '';
$sod = isset($_GET['sod']) ? sql_escape_string($_GET['sod']) : '';

if($sst) {
	$sql_order = " ORDER BY {$sst} {$sod} ";
} else {
	$sql_order = " ORDER BY bill_paid_at DESC, mp_id DESC ";
}

// 매장 목록 조회 (드롭다운용)
$store_list = array();
$sql = "SELECT DISTINCT st_uid, st_name FROM {$store_table} WHERE st_use = 'Y' ORDER BY st_name";
$store_result = sql_query($sql);
while($row = sql_fetch_array($store_result)) {
	$store_list[] = $row;
}

// 전체 통계 조회 (필터 적용) - bill_no 기준으로 카운트
// 취소(C)는 '이전 매출이 취소됨'을 의미하므로 순매출 = 매출(S)만 계산
$sql = "SELECT
	COUNT(DISTINCT bill_no) as total_cnt,
	COUNT(DISTINCT IF(bill_status = 'S', bill_no, NULL)) as sale_cnt,
	COUNT(DISTINCT IF(bill_status = 'C', bill_no, NULL)) as cancel_cnt,
	SUM(IF(bill_status = 'S', pay_amount, 0)) as total_sale_amount,
	SUM(IF(bill_status = 'C', pay_amount, 0)) as total_cancel_amount
	FROM {$table_name} {$sql_where}";
$stat = sql_fetch($sql);

// 부가세는 bill_no 단위로 중복 없이 계산 (영수증당 1번만)
$vat_sql = "SELECT COALESCE(SUM(vat), 0) as total_vat FROM (
	SELECT bill_no, MAX(bill_vat) as vat
	FROM {$table_name} {$sql_where} AND bill_status = 'S'
	GROUP BY bill_no
) vat_sub";
$vat_result = sql_fetch($vat_sql);
$stat['total_sale_vat'] = $vat_result['total_vat'];

$total_count = $stat['total_cnt'];
$page_count = 20;
$rows = $page_count;

$total_page = ceil($total_count / $rows);
if($page < 1) $page = 1;
$from_record = ($page - 1) * $rows;

// 가맹점별 합계 조회 (부가세는 bill_no 단위로 계산)
// 먼저 가맹점별 부가세 조회 (bill_no 단위로 중복 제거)
$store_vat_map = array();
$vat_store_sql = "SELECT st_uid, SUM(vat) as sale_vat FROM (
	SELECT st_uid, bill_no, MAX(bill_vat) as vat
	FROM {$table_name} {$sql_where} AND bill_status = 'S'
	GROUP BY st_uid, bill_no
) vat_sub GROUP BY st_uid";
$vat_store_result = sql_query($vat_store_sql);
while($vat_row = sql_fetch_array($vat_store_result)) {
	$store_vat_map[$vat_row['st_uid']] = $vat_row['sale_vat'];
}

$sql = "SELECT
	st_uid, st_name,
	COUNT(DISTINCT bill_no) as pay_cnt,
	SUM(IF(bill_status = 'S', pay_amount, 0)) as sale_amount,
	SUM(IF(bill_status = 'C', pay_amount, 0)) as cancel_amount,
	COUNT(DISTINCT IF(bill_status = 'S', bill_no, NULL)) as sale_cnt,
	COUNT(DISTINCT IF(bill_status = 'C', bill_no, NULL)) as cancel_cnt
	FROM {$table_name} {$sql_where}
	GROUP BY st_uid, st_name
	ORDER BY sale_amount DESC";
$store_summary = sql_query($sql);

// bill_no 기준 그룹핑하여 목록 조회 (g5_payment 매칭 정보 포함)
$sql = "SELECT mp.bill_no, mp.st_uid, mp.st_name, mp.sal_ymd, mp.bill_table_no, mp.bill_status, mp.bill_amount, mp.bill_vat, mp.bill_discount, mp.bill_ordered_at, mp.bill_paid_at,
	GROUP_CONCAT(DISTINCT mp.pay_method SEPARATOR '||') as pay_methods,
	GROUP_CONCAT(CONCAT_WS(':', IFNULL(mp.pay_method,''), IFNULL(mp.pay_issuer,''), IFNULL(mp.pay_card_no,''), IFNULL(mp.pay_auth_number,''), IFNULL(mp.pay_cash_id,''), mp.pay_amount, IFNULL(mp.g5_pay_id,'')) SEPARATOR '||') as pay_details,
	SUM(mp.pay_amount) as total_pay_amount,
	GROUP_CONCAT(DISTINCT mp.g5_pay_id SEPARATOR ',') as g5_pay_ids
	FROM {$table_name} mp
	{$sql_where_mp}
	GROUP BY mp.bill_no, mp.st_uid, mp.st_name, mp.sal_ymd, mp.bill_table_no, mp.bill_status, mp.bill_amount, mp.bill_vat, mp.bill_discount, mp.bill_ordered_at, mp.bill_paid_at
	ORDER BY mp.bill_paid_at DESC, mp.bill_no DESC
	LIMIT {$from_record}, {$rows}";
$result = sql_query($sql);

include_once('./_head.php');
?>

<style>
/* 메인 헤더 */
.metapos-pay-header {
	background: linear-gradient(135deg, #1565c0 0%, #1976d2 100%);
	border-radius: 8px;
	padding: 12px 16px;
	margin-bottom: 10px;
	box-shadow: 0 2px 8px rgba(21, 101, 192, 0.3);
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
.metapos-pay-stat.total {
	background: rgba(255,255,255,0.25);
	color: #fff;
	font-weight: 600;
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
	border-color: #1976d2;
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
	background: #1565c0;
	color: #fff;
	border: none;
	border-radius: 4px;
	font-size: 12px;
	cursor: pointer;
	transition: background 0.15s;
}
.btn-search:hover {
	background: #1976d2;
}
.btn-sync {
	padding: 6px 12px;
	background: #E65100;
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
	background: #FF6D00;
}
.btn-sync:disabled {
	background: #bdbdbd;
	cursor: not-allowed;
}

/* 요약 카드 */
.summary-section {
	margin-bottom: 15px;
}
.summary-title {
	font-size: 14px;
	font-weight: 600;
	color: #333;
	margin-bottom: 10px;
	display: flex;
	align-items: center;
	gap: 6px;
}
.summary-title i {
	color: #1565c0;
}
.summary-cards {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 10px;
	margin-bottom: 15px;
}
.summary-card {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 12px 14px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.summary-card-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	margin-bottom: 8px;
}
.summary-card-name {
	font-size: 13px;
	font-weight: 600;
	color: #333;
	max-width: 150px;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}
.summary-card-count {
	font-size: 11px;
	color: #666;
	background: #f5f5f5;
	padding: 2px 6px;
	border-radius: 10px;
}
.summary-card-body {
	display: flex;
	justify-content: space-between;
	gap: 10px;
}
.summary-card-item {
	flex: 1;
}
.summary-card-label {
	font-size: 10px;
	color: #888;
	margin-bottom: 2px;
}
.summary-card-value {
	font-size: 14px;
	font-weight: 700;
}
.summary-card-value.sale {
	color: #2e7d32;
}
.summary-card-value.cancel {
	color: #c62828;
}
.summary-card-value.net {
	color: #1565c0;
}

/* 전체 합계 카드 */
.total-summary-card {
	background: linear-gradient(135deg, #1565c0 0%, #1976d2 100%);
	border: none;
	color: #fff;
}
.total-summary-card .summary-card-name {
	color: #fff;
}
.total-summary-card .summary-card-count {
	background: rgba(255,255,255,0.2);
	color: #fff;
}
.total-summary-card .summary-card-label {
	color: rgba(255,255,255,0.7);
}
.total-summary-card .summary-card-value {
	color: #fff;
}
.total-summary-card .summary-card-value.sale {
	color: #a5d6a7;
}
.total-summary-card .summary-card-value.cancel {
	color: #ef9a9a;
}

/* 상태 배지 */
.status-badge {
	display: inline-block;
	padding: 3px 8px;
	border-radius: 12px;
	font-size: 11px;
	font-weight: 600;
}
.status-badge.sale {
	background: #e8f5e9;
	color: #2e7d32;
}
.status-badge.cancel {
	background: #ffebee;
	color: #c62828;
}
.status-badge.return {
	background: #fff3e0;
	color: #e65100;
}

/* 결제수단 배지 */
.pay-method-badge {
	display: inline-block;
	padding: 2px 6px;
	border-radius: 3px;
	font-size: 10px;
	font-weight: 500;
	background: #e3f2fd;
	color: #1565c0;
}
.pay-method-badge.cash {
	background: #e8f5e9;
	color: #2e7d32;
}
.pay-method-badge.cash-receipt {
	background: #fff3e0;
	color: #e65100;
}

/* 금액 셀 */
td.amount {
	font-family: 'Courier New', monospace;
	font-weight: 600;
}
td.amount.positive {
	color: #2e7d32;
}
td.amount.negative {
	color: #c62828;
}

/* 테이블 행 */
tr.row-cancel {
	background: #fafafa !important;
}
tr.row-cancel td {
	color: #9e9e9e;
}

/* 결제 상세 (여러 결제수단) */
.pay-detail-list {
	display: flex;
	flex-direction: column;
	gap: 4px;
}
.pay-detail-item {
	display: flex;
	align-items: center;
	gap: 6px;
	padding: 3px 0;
	border-bottom: 1px dashed #eee;
}
.pay-detail-item:last-child {
	border-bottom: none;
}
.pay-detail-method {
	min-width: 50px;
}
.pay-detail-info {
	font-size: 10px;
	color: #666;
	max-width: 120px;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}
.pay-detail-auth {
	font-size: 10px;
	color: #1565c0;
	font-weight: 500;
}
.pay-detail-amount {
	font-family: 'Courier New', monospace;
	font-size: 11px;
	font-weight: 600;
	margin-left: auto;
}
.pay-detail-amount.positive {
	color: #2e7d32;
}
.pay-detail-amount.negative {
	color: #c62828;
}
.pay-detail-amount.cash-receipt {
	color: #ff9800;
}
/* PG 매칭 배지 */
.pg-match-badge {
	display: inline-flex;
	align-items: center;
	gap: 3px;
	padding: 1px 5px;
	border-radius: 3px;
	font-size: 9px;
	font-weight: 600;
	background: #e3f2fd;
	color: #1565c0;
	margin-left: 4px;
}
.pg-match-badge i {
	font-size: 8px;
}

/* 로딩/모달 */
.loading-overlay {
	position: fixed;
	left: 0;
	right: 0;
	top: 0;
	bottom: 0;
	background: rgba(255,255,255,0.95);
	z-index: 10001;
	display: none;
	flex-direction: column;
	align-items: center;
	justify-content: center;
}
.loading-overlay.show {
	display: flex;
}
.loading-overlay .spinner {
	width: 50px;
	height: 50px;
	border: 4px solid #e0e0e0;
	border-top: 4px solid #1565c0;
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

/* 동기화 결과 모달 */
.sync-modal-overlay {
	position: fixed;
	left: 0;
	right: 0;
	top: 0;
	bottom: 0;
	background: rgba(0,0,0,0.5);
	z-index: 10000;
	display: none;
	justify-content: center;
	align-items: center;
	padding: 15px;
}
.sync-modal-overlay.show {
	display: flex;
}
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
.sync-result-header i {
	font-size: 40px;
	color: #fff;
	margin-bottom: 10px;
}
.sync-result-header h3 {
	color: #fff;
	font-size: 18px;
	font-weight: 600;
	margin: 0;
}
.sync-result-body {
	padding: 20px;
	text-align: center;
}
.sync-result-body .stats {
	display: flex;
	justify-content: center;
	gap: 20px;
	margin-bottom: 15px;
}
.sync-result-body .stat-item {
	text-align: center;
}
.sync-result-body .stat-value {
	font-size: 24px;
	font-weight: 700;
	color: #333;
}
.sync-result-body .stat-label {
	font-size: 12px;
	color: #666;
}
.sync-result-body .stat-item.inserted .stat-value {
	color: #2e7d32;
}
.sync-result-footer {
	padding: 0 20px 20px;
}
.sync-result-footer .btn {
	width: 100%;
	border: none;
	border-radius: 6px;
	font-size: 14px;
	font-weight: 600;
	cursor: pointer;
	background: #1565c0;
	color: #fff;
	transition: background 0.2s;
	white-space: nowrap;
}
.sync-result-footer .btn:hover {
	background: #1976d2;
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
	.metapos-pay-search {
		padding: 8px;
	}
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
	.search-divider {
		display: none;
	}
	.summary-cards {
		grid-template-columns: 1fr;
	}
	.summary-card-body {
		flex-wrap: wrap;
	}
	.summary-card-item {
		min-width: 80px;
	}
	/* 테이블 가로 스크롤 */
	.m_board_scroll {
		overflow-x: auto;
		-webkit-overflow-scrolling: touch;
	}
	.table_list {
		min-width: 600px;
	}
	.td_name {
		white-space: nowrap;
	}
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
			<i class="fa fa-money"></i>
			스시이안앤 결제정보
		</div>
		<?php if($member['mb_level'] >= 10) { ?>
		<div style="display:flex; align-items:center; gap:6px;">
			<button type="button" class="btn-sync" id="btnSync" onclick="syncPayments()">
				<i class="fa fa-refresh"></i> 수동 결제 동기화
			</button>
			<span style="font-size:10px; color:rgba(255,255,255,0.7);">(시작일 기준)</span>
		</div>
		<?php } ?>
	</div>
	<div class="metapos-pay-header-bottom">
		<div class="metapos-pay-stats">
			<div class="metapos-pay-stat">
				전체 <span><?php echo number_format($stat['total_cnt']); ?>건</span>
			</div>
			<div class="metapos-pay-stat sale">
				순매출 <span><?php echo number_format($stat['total_sale_amount']); ?>원</span>
			</div>
			<div class="metapos-pay-stat" style="background: rgba(255,193,7,0.3);">
				부가세 <span><?php echo number_format($stat['total_sale_vat']); ?>원</span>
			</div>
		</div>
	</div>
</div>

<form id="fsearch" name="fsearch" method="get">
<input type="hidden" name="p" value="<?php echo $p; ?>">
<div class="metapos-pay-search">
	<div class="metapos-pay-search-row">
		<div class="metapos-pay-search-group">
			<input type="text" name="fr_date" class="date-input" value="<?php echo $fr_date; ?>" placeholder="시작일" maxlength="8">
			<span>~</span>
			<input type="text" name="to_date" class="date-input" value="<?php echo $to_date; ?>" placeholder="종료일" maxlength="8">
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

<!-- 가맹점별 요약 -->
<?php if(sql_num_rows($store_summary) > 0) { ?>
<div class="summary-section">
	<div class="summary-title">
		<i class="fa fa-bar-chart"></i> <?php echo $is_merchant_view ? '내 매장 매출 현황' : '가맹점별 매출 현황'; ?>
	</div>
	<div class="summary-cards">
		<!-- 전체 합계 카드 -->
		<div class="summary-card total-summary-card">
			<div class="summary-card-header">
				<span class="summary-card-name"><i class="fa fa-calculator"></i> <?php echo $is_merchant_view ? '매출 합계' : '전체 합계'; ?></span>
				<span class="summary-card-count"><?php echo number_format($stat['total_cnt']); ?>건</span>
			</div>
			<div class="summary-card-body">
				<div class="summary-card-item">
					<div class="summary-card-label">순매출</div>
					<div class="summary-card-value"><?php echo number_format($stat['total_sale_amount']); ?></div>
				</div>
				<div class="summary-card-item">
					<div class="summary-card-label">부가세</div>
					<div class="summary-card-value" style="color:#ffeb3b;"><?php echo number_format($stat['total_sale_vat']); ?></div>
				</div>
			</div>
		</div>
		<?php
		// 가맹점별 카드 (관리자만 표시)
		if(!$is_merchant_view) {
			sql_data_seek($store_summary, 0);
			while($sum = sql_fetch_array($store_summary)) {
				$store_vat = isset($store_vat_map[$sum['st_uid']]) ? $store_vat_map[$sum['st_uid']] : 0;
		?>
		<div class="summary-card">
			<div class="summary-card-header">
				<span class="summary-card-name"><?php echo htmlspecialchars($sum['st_name']); ?></span>
				<span class="summary-card-count"><?php echo number_format($sum['pay_cnt']); ?>건</span>
			</div>
			<div class="summary-card-body">
				<div class="summary-card-item">
					<div class="summary-card-label">순매출</div>
					<div class="summary-card-value sale"><?php echo number_format($sum['sale_amount']); ?></div>
				</div>
				<div class="summary-card-item">
					<div class="summary-card-label">부가세</div>
					<div class="summary-card-value" style="color:#ff9800;"><?php echo number_format($store_vat); ?></div>
				</div>
			</div>
		</div>
		<?php
			}
		}
		?>
	</div>
</div>
<?php } ?>

<div class="m_board_scroll">
	<div class="m_table_wrap" style="padding-bottom:30px;">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
				<tr>
					<th style="width:40px;">번호</th>
					<th>매장</th>
					<th>결제일시</th>
					<th>영수증번호</th>
					<th>상태</th>
					<th>결제 상세</th>
					<th class="right">총 결제금액</th>
				</tr>
			</thead>
			<tbody>
				<?php
				if($total_count == 0) {
				?>
				<tr>
					<td colspan="7" class="center" style="padding: 40px 0; color: #999;">
						<i class="fa fa-inbox" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
						조회된 결제내역이 없습니다.
					</td>
				</tr>
				<?php
				} else {
					for ($i=0; $row=sql_fetch_array($result); $i++) {
						$num = number_format($total_count - ($page - 1) * $rows - $i);

						// 상태 표시
						$status_class = 'sale';
						$status_text = '매출';
						if($row['bill_status'] == 'C') {
							$status_class = 'cancel';
							$status_text = '취소';
						} else if($row['bill_status'] == 'R') {
							$status_class = 'return';
							$status_text = '반품';
						}
						$row_class = ($row['bill_status'] != 'S') ? 'row-cancel' : '';

						// 결제 상세 파싱 (pay_method:pay_issuer:pay_card_no:pay_auth_number:pay_cash_id:pay_amount:g5_pay_id)
						$pay_details_arr = array();
						if($row['pay_details']) {
							$details = explode('||', $row['pay_details']);
							foreach($details as $detail) {
								$parts = explode(':', $detail);
								if(count($parts) >= 6) {
									$pay_details_arr[] = array(
										'method' => $parts[0] ?: '-',
										'issuer' => $parts[1],
										'card_no' => $parts[2],
										'auth_no' => $parts[3],
										'cash_id' => $parts[4],
										'amount' => (int)$parts[5],
										'g5_pay_id' => isset($parts[6]) ? (int)$parts[6] : 0
									);
								}
							}
						}
				?>
				<tr class="<?php echo $row_class; ?>">
					<td class="center"><?php echo $num; ?></td>
					<td class="td_name"><?php echo htmlspecialchars($row['st_name']); ?></td>
					<td class="center" style="font-size:11px;"><?php echo $row['bill_paid_at']; ?></td>
					<td class="center" style="font-size:11px;"><?php echo htmlspecialchars($row['bill_no']); ?></td>
					<td class="center">
						<span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
					</td>
					<td style="min-width:250px;">
						<div class="pay-detail-list">
						<?php
						if(count($pay_details_arr) > 0) {
							foreach($pay_details_arr as $pd) {
								$is_cash = (strpos(strtolower($pd['method']), '현금') !== false || strpos(strtolower($pd['method']), 'cash') !== false);
								$has_cash_id = !empty($pd['cash_id']); // 핸드폰번호 있으면 현금영수증
								$is_cash_receipt = ($is_cash && $has_cash_id);

								if($is_cash_receipt) {
									$method_class = 'cash-receipt';
									$method_display = '현금영수증';
								} else if($is_cash) {
									$method_class = 'cash';
									$method_display = $pd['method'];
								} else {
									$method_class = '';
									$method_display = $pd['method'];
								}

								$card_info = $pd['card_no'] ?: $pd['cash_id'];
								if($pd['issuer'] && $card_info) {
									$card_info = $pd['issuer'] . ' ' . $card_info;
								} else if($pd['issuer']) {
									$card_info = $pd['issuer'];
								}
								$amount_class = ($row['bill_status'] == 'S') ? 'positive' : 'negative';
								$has_pg_match = !empty($pd['g5_pay_id']);
						?>
							<div class="pay-detail-item">
								<span class="pay-detail-method">
									<span class="pay-method-badge <?php echo $method_class; ?>"><?php echo htmlspecialchars($method_display); ?></span>
									<?php if($has_pg_match) { ?>
									<span class="pg-match-badge" title="PG결제 매칭됨 (pay_id: <?php echo $pd['g5_pay_id']; ?>)"><i class="fa fa-link"></i>PG</span>
									<?php } ?>
								</span>
								<?php if($card_info) { ?>
								<span class="pay-detail-info" title="<?php echo htmlspecialchars($card_info); ?>"><?php echo htmlspecialchars($card_info); ?></span>
								<?php } ?>
								<?php if($pd['auth_no']) { ?>
								<span class="pay-detail-auth"><?php echo htmlspecialchars($pd['auth_no']); ?></span>
								<?php } ?>
								<span class="pay-detail-amount <?php echo $amount_class; ?>">
									<?php echo ($row['bill_status'] != 'S' ? '-' : '') . number_format($pd['amount']); ?>
								</span>
							</div>
						<?php
							}
						} else {
							echo '<span style="color:#999;">-</span>';
						}
						?>
						</div>
					</td>
					<td class="right amount <?php echo ($row['bill_status'] == 'S') ? 'positive' : 'negative'; ?>" style="font-size:13px;">
						<?php echo ($row['bill_status'] != 'S' ? '-' : '') . number_format($row['total_pay_amount']); ?>
					</td>
				</tr>
				<?php
					}
				}
				?>
			</tbody>
		</table>
	</div>
</div>

<?php
$qstr = "p=".$p;
$qstr .= "&fr_date=".$fr_date;
$qstr .= "&to_date=".$to_date;
if($st_uid_filter) $qstr .= "&st_uid=".$st_uid_filter;
if($bill_status) $qstr .= "&bill_status=".$bill_status;
if($stx) $qstr .= "&stx=".urlencode($stx);
echo get_paging_news(G5_IS_MOBILE ? "5" : "5", $page, $total_page, '?' . $qstr . '&amp;page=');
?>

<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
<script>
$(function() {
	// 날짜 입력 Datepicker
	$(".date-input").datepicker({
		dateFormat: "yymmdd",
		changeMonth: true,
		changeYear: true,
		showButtonPanel: true
	});
});

<?php if($member['mb_level'] >= 10) { ?>
// 결제 데이터 동기화
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

// ESC 키로 모달 닫기
$(document).keydown(function(e) {
	if(e.keyCode == 27) {
		closeSyncResult();
	}
});

// 오버레이 클릭 시 모달 닫기
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
