<?php
/**
 * 수기결제 내역 페이지
 * - g5_payment_keyin 테이블 조회
 * - 관리자: 전체 조회
 * - 가맹점: 자신의 내역만 조회
 */

$title1 = "수기결제";
$title2 = "수기결제 내역";

include_once('./_common.php');

// 수기결제 권한 체크
if(!$is_admin && $member['mb_mailling'] != '1') {
	alert("수기결제 권한이 없습니다.");
}

// 테이블 존재 여부 확인 및 생성
$table_name = "g5_payment_keyin";
$check_table = sql_query("SHOW TABLES LIKE '{$table_name}'");
if(sql_num_rows($check_table) == 0) {
	$create_sql = "CREATE TABLE `{$table_name}` (
		`pk_id` int(11) NOT NULL AUTO_INCREMENT,
		`pk_order_no` varchar(50) NOT NULL COMMENT '주문번호 (OID-YYMM-HHMM-SSRR)',
		`pk_merchant_oid` varchar(4) DEFAULT NULL COMMENT '가맹점 OID',
		`mb_id` varchar(50) NOT NULL COMMENT '가맹점 아이디',
		`mkc_id` int(11) DEFAULT NULL COMMENT 'Keyin 설정 ID',
		`pk_pg_code` varchar(20) NOT NULL COMMENT 'PG사 코드 (paysis 등)',
		`pk_pg_name` varchar(50) DEFAULT NULL COMMENT 'PG사 이름',
		`pk_mid` varchar(50) DEFAULT NULL COMMENT '상점 ID',
		`pk_auth_type` varchar(20) DEFAULT NULL COMMENT '인증 타입 (nonauth/auth)',
		`pk_amount` int(11) NOT NULL COMMENT '결제 금액',
		`pk_installment` varchar(2) DEFAULT '00' COMMENT '할부개월 (00=일시불)',
		`pk_goods_name` varchar(100) DEFAULT NULL COMMENT '상품명',
		`pk_buyer_name` varchar(50) DEFAULT NULL COMMENT '구매자명',
		`pk_buyer_phone` varchar(20) DEFAULT NULL COMMENT '구매자 연락처',
		`pk_card_issuer` varchar(50) DEFAULT NULL COMMENT '발급사명',
		`pk_card_acquirer` varchar(50) DEFAULT NULL COMMENT '매입사명',
		`pk_card_no_masked` varchar(20) DEFAULT NULL COMMENT '카드번호 마스킹',
		`pk_status` enum('pending','approved','failed','cancelled','partial_cancelled') DEFAULT 'pending' COMMENT '결제 상태',
		`pk_res_code` varchar(10) DEFAULT NULL COMMENT 'PG 응답코드',
		`pk_res_msg` varchar(200) DEFAULT NULL COMMENT 'PG 응답메시지',
		`pk_app_no` varchar(50) DEFAULT NULL COMMENT '승인번호',
		`pk_app_date` varchar(20) DEFAULT NULL COMMENT '승인일시 (PG 응답)',
		`pk_tid` varchar(100) DEFAULT NULL COMMENT 'PG 거래 ID',
		`pk_cancel_amount` int(11) DEFAULT 0 COMMENT '취소 금액',
		`pk_cancel_name` varchar(50) DEFAULT NULL COMMENT '취소자명',
		`pk_cancel_reason` varchar(200) DEFAULT NULL COMMENT '취소 사유',
		`pk_cancel_date` varchar(20) DEFAULT NULL COMMENT '취소 일시 (PG 응답)',
		`pk_mb_1` varchar(50) DEFAULT NULL,
		`pk_mb_2` varchar(50) DEFAULT NULL,
		`pk_mb_3` varchar(50) DEFAULT NULL,
		`pk_mb_4` varchar(50) DEFAULT NULL,
		`pk_mb_5` varchar(50) DEFAULT NULL,
		`pk_mb_6` varchar(50) DEFAULT NULL,
		`pk_mb_6_name` varchar(50) DEFAULT NULL COMMENT '가맹점명',
		`pk_request_data` longtext COMMENT '요청 데이터 JSON',
		`pk_response_data` longtext COMMENT '응답 데이터 JSON',
		`pk_operator_id` varchar(50) DEFAULT NULL COMMENT '결제 진행자 ID',
		`pk_memo` text COMMENT '관리 메모',
		`pk_created_at` datetime DEFAULT CURRENT_TIMESTAMP,
		`pk_updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (`pk_id`),
		KEY `idx_order_no` (`pk_order_no`),
		KEY `idx_mb_id` (`mb_id`),
		KEY `idx_status` (`pk_status`),
		KEY `idx_created_at` (`pk_created_at`),
		KEY `idx_app_no` (`pk_app_no`),
		KEY `idx_pg_code` (`pk_pg_code`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='수기결제 거래 내역'";
	sql_query($create_sql);
}

// 날짜 필터
if(!$fr_date) { $fr_date = date("Ymd"); }
if(!$to_date) { $to_date = date("Ymd"); }

$fr_dates = date("Y-m-d", strtotime($fr_date));
$to_dates = date("Y-m-d", strtotime($to_date));

// 접근 제어 SQL
if($is_admin) {
	if(adm_sql_common) {
		$adm_sql = " pk_mb_1 IN (".adm_sql_common.")";
	} else {
		$adm_sql = " (1)";
	}
} else if($member['mb_level'] == 8) {
	$adm_sql = " pk_mb_1 = '{$member['mb_id']}'";
} else if($member['mb_level'] == 7) {
	$adm_sql = " pk_mb_2 = '{$member['mb_id']}'";
} else if($member['mb_level'] == 6) {
	$adm_sql = " pk_mb_3 = '{$member['mb_id']}'";
} else if($member['mb_level'] == 5) {
	$adm_sql = " pk_mb_4 = '{$member['mb_id']}'";
} else if($member['mb_level'] == 4) {
	$adm_sql = " pk_mb_5 = '{$member['mb_id']}'";
} else if($member['mb_level'] == 3) {
	$adm_sql = " mb_id = '{$member['mb_id']}'";
}

// 검색 조건
if ($fr_date == "all" && $to_date == "all") {
	$sql_search = " WHERE ".$adm_sql;
} else {
	$sql_search = " WHERE ".$adm_sql." AND (pk_created_at BETWEEN '{$fr_dates} 00:00:00' AND '{$to_dates} 23:59:59')";
}

// 상태 필터
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';
if($status_filter && in_array($status_filter, ['approved', 'failed', 'cancelled', 'pending'])) {
	$sql_search .= " AND pk_status = '{$status_filter}'";
}

// PG사 필터
$pg_filter = isset($_GET['pg_filter']) ? sql_escape_string($_GET['pg_filter']) : '';
if($pg_filter) {
	$sql_search .= " AND pk_pg_code = '{$pg_filter}'";
}

// 인증타입 필터
$auth_filter = isset($_GET['auth_filter']) ? sql_escape_string($_GET['auth_filter']) : '';
if($auth_filter && in_array($auth_filter, ['nonauth', 'auth'])) {
	$sql_search .= " AND pk_auth_type = '{$auth_filter}'";
}

// 검색어
if ($stx) {
	$sql_search .= " AND ( ";
	switch ($sfl) {
		case "pk_app_no" :
		case "pk_order_no" :
			$sql_search .= " ({$sfl} = '{$stx}') ";
			break;
		case "pk_mb_6_name" :
		case "pk_goods_name" :
		case "pk_buyer_name" :
		default :
			$sql_search .= " ({$sfl} LIKE '%{$stx}%') ";
			break;
	}
	$sql_search .= " ) ";
}

// 정렬
if ($sst)
	$sql_order = " ORDER BY {$sst} {$sod} ";
else
	$sql_order = " ORDER BY pk_created_at DESC ";

// 통계 조회
$sql = "SELECT
	COUNT(*) as cnt,
	SUM(pk_amount) as total_amount,
	SUM(IF(pk_status = 'approved', pk_amount, 0)) as approved_amount,
	COUNT(IF(pk_status = 'approved', 1, NULL)) as approved_count,
	SUM(IF(pk_status = 'failed', pk_amount, 0)) as failed_amount,
	COUNT(IF(pk_status = 'failed', 1, NULL)) as failed_count,
	SUM(IF(pk_status IN ('cancelled', 'partial_cancelled'), pk_cancel_amount, 0)) as cancelled_amount,
	COUNT(IF(pk_status IN ('cancelled', 'partial_cancelled'), 1, NULL)) as cancelled_count,
	COUNT(IF(pk_status = 'pending', 1, NULL)) as pending_count
	FROM {$table_name} {$sql_search}";
$stat = sql_fetch($sql);

$total_count = $stat['cnt'];
$page_count = "30";
$rows = $page_count ? $page_count : $config['cf_page_rows'];

$total_page = ceil($total_count / $rows);
if ($page < 1) $page = 1;
$from_record = ($page - 1) * $rows;

// 목록 조회
$sql = "SELECT * FROM {$table_name} {$sql_search} {$sql_order} LIMIT {$from_record}, {$rows}";
$result = sql_query($sql);

include_once('./_head.php');
?>

<style>
.manual-list-header {
	background: linear-gradient(135deg, #e65100 0%, #f57c00 100%);
	border-radius: 8px;
	padding: 12px 16px;
	margin-bottom: 10px;
	box-shadow: 0 2px 8px rgba(230, 81, 0, 0.2);
}
.manual-list-header-top {
	display: flex;
	align-items: center;
	justify-content: space-between;
	flex-wrap: wrap;
	gap: 10px;
}
.manual-list-title {
	color: #fff;
	font-size: 16px;
	font-weight: 600;
	display: flex;
	align-items: center;
	gap: 8px;
}
.manual-list-title i {
	font-size: 14px;
	opacity: 0.8;
}
.manual-list-stats {
	display: flex;
	flex-wrap: wrap;
	gap: 6px;
}
.manual-list-stat {
	display: inline-flex;
	align-items: center;
	background: rgba(255,255,255,0.12);
	border-radius: 4px;
	padding: 4px 10px;
	font-size: 12px;
	color: rgba(255,255,255,0.85);
	gap: 6px;
}
.manual-list-stat.approved {
	background: rgba(76,175,80,0.3);
}
.manual-list-stat.failed {
	background: rgba(244,67,54,0.3);
}
.manual-list-stat.cancelled {
	background: rgba(158,158,158,0.3);
}
.manual-list-stat.total {
	background: rgba(255,193,7,0.3);
	color: #fff;
	font-weight: 600;
}
.manual-list-stat span {
	color: #fff;
	font-weight: 600;
}
.manual-list-search {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 10px 12px;
	margin-bottom: 10px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.manual-list-search-row {
	display: flex;
	align-items: center;
	flex-wrap: wrap;
	gap: 8px;
}
.manual-list-search-group {
	display: flex;
	align-items: center;
	gap: 4px;
}
.manual-list-search-group input[type="text"],
.manual-list-search-group select {
	padding: 6px 8px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-size: 13px;
	background: #f8f9fa;
}
.manual-list-search-group input[type="text"] {
	width: 90px;
}
.manual-list-search-group select {
	min-width: 80px;
}
.manual-list-search-group input[type="text"]:focus,
.manual-list-search-group select:focus {
	outline: none;
	border-color: #e65100;
	background: #fff;
}
.manual-list-search-group span {
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
	background: #e65100;
	border-color: #e65100;
	color: #fff;
}
.search-divider {
	width: 1px;
	height: 24px;
	background: #e0e0e0;
	margin: 0 6px;
}
.radio-group {
	display: flex;
	align-items: center;
	gap: 8px;
}
.radio-group label {
	display: flex;
	align-items: center;
	gap: 3px;
	font-size: 12px;
	color: #555;
	cursor: pointer;
}
.radio-group input[type="radio"] {
	margin: 0;
	accent-color: #e65100;
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
	border-color: #e65100;
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
	background: #f57c00;
}
.btn-excel {
	padding: 6px 10px;
	background: #2e7d32;
	color: #fff;
	border: none;
	border-radius: 4px;
	font-size: 12px;
	cursor: pointer;
	transition: background 0.15s;
}
.btn-excel:hover {
	background: #388e3c;
}
/* 상태 배지 */
.status-badge {
	display: inline-block;
	padding: 3px 8px;
	border-radius: 12px;
	font-size: 11px;
	font-weight: 600;
}
.status-badge.approved {
	background: #e8f5e9;
	color: #2e7d32;
}
.status-badge.failed {
	background: #ffebee;
	color: #c62828;
}
.status-badge.cancelled {
	background: #fafafa;
	color: #757575;
	text-decoration: line-through;
}
.status-badge.pending {
	background: #fff3e0;
	color: #e65100;
}
/* 인증타입 배지 */
.auth-badge {
	display: inline-block;
	padding: 2px 6px;
	border-radius: 3px;
	font-size: 10px;
	font-weight: 600;
}
.auth-badge.nonauth {
	background: #fff3e0;
	color: #e65100;
}
.auth-badge.auth {
	background: #e3f2fd;
	color: #1565c0;
}
/* 신규결제 버튼 */
.btn-manual-module {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	padding: 10px 20px;
	background: linear-gradient(135deg, #1565c0 0%, #1976d2 100%);
	color: #fff;
	border: none;
	border-radius: 8px;
	font-size: 14px;
	font-weight: 600;
	cursor: pointer;
	transition: all 0.2s ease;
	text-decoration: none;
	box-shadow: 0 2px 8px rgba(21, 101, 192, 0.3);
}
.btn-manual-module:hover {
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(21, 101, 192, 0.4);
	background: linear-gradient(135deg, #1976d2 0%, #2196f3 100%);
	color: #fff;
}
.btn-manual-module i {
	font-size: 16px;
}
.module-btn-wrapper {
	display: flex;
	justify-content: flex-start;
	margin: 15px 0;
}
.module-btn-wrapper.top {
	margin-top: 0;
	margin-bottom: 15px;
}
/* 취소 버튼 */
.btn-cancel {
	padding: 4px 8px;
	background: #f44336;
	color: #fff;
	border: none;
	border-radius: 3px;
	font-size: 11px;
	cursor: pointer;
	transition: background 0.15s;
}
.btn-cancel:hover {
	background: #d32f2f;
}
.btn-cancel:disabled {
	background: #bdbdbd;
	cursor: not-allowed;
}
/* 테이블 행 색상 */
tr.row-cancelled {
	background: #fafafa !important;
}
tr.row-cancelled td {
	color: #9e9e9e;
}
tr.row-failed {
	background: #fff8f8 !important;
}
@media (max-width: 768px) {
	.manual-list-header-top {
		flex-direction: column;
		align-items: flex-start;
	}
	.manual-list-stats {
		width: 100%;
	}
	.manual-list-search-row {
		flex-direction: column;
		align-items: flex-start;
	}
	.search-divider {
		display: none;
	}
	.radio-group {
		flex-wrap: wrap;
	}
	.btn-manual-module {
		width: 100%;
		justify-content: center;
		padding: 12px 20px;
	}
}
</style>

<?php if($is_admin) { ?>
<!-- ===== 관리자용 화면 (기존) ===== -->
<div class="module-btn-wrapper top">
	<a href="/?p=manual_payment_module" class="btn-manual-module">
		<i class="fa fa-credit-card"></i>
		신규결제
	</a>
</div>

<div class="manual-list-header">
	<div class="manual-list-header-top">
		<div class="manual-list-title">
			<i class="fa fa-list-alt"></i>
			수기결제 내역
		</div>
		<div class="manual-list-stats">
			<div class="manual-list-stat approved">
				승인 <span><?php echo number_format($stat['approved_count']); ?>건</span> / <span><?php echo number_format($stat['approved_amount']); ?>원</span>
			</div>
			<div class="manual-list-stat failed">
				실패 <span><?php echo number_format($stat['failed_count']); ?>건</span>
			</div>
			<div class="manual-list-stat cancelled">
				취소 <span><?php echo number_format($stat['cancelled_count']); ?>건</span> / <span><?php echo number_format($stat['cancelled_amount']); ?>원</span>
			</div>
			<?php if($stat['pending_count'] > 0) { ?>
			<div class="manual-list-stat">
				대기 <span><?php echo number_format($stat['pending_count']); ?>건</span>
			</div>
			<?php } ?>
			<div class="manual-list-stat total">
				전체 <span><?php echo number_format($total_count); ?>건</span>
			</div>
		</div>
	</div>
</div>

<form id="fsearch" name="fsearch" method="get">
<input type="hidden" name="p" value="<?php echo $p; ?>">
<div class="manual-list-search">
	<div class="manual-list-search-row">
		<div class="manual-list-search-group">
			<input type="text" name="fr_date" value="<?php echo $fr_date ?>" id="fr_date" placeholder="시작일">
			<span>~</span>
			<input type="text" name="to_date" value="<?php echo $to_date ?>" id="to_date" placeholder="종료일">
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
		<div class="manual-list-search-group">
			<select name="status_filter">
				<option value="">상태전체</option>
				<option value="approved" <?php if($status_filter == 'approved') echo 'selected'; ?>>승인</option>
				<option value="failed" <?php if($status_filter == 'failed') echo 'selected'; ?>>실패</option>
				<option value="cancelled" <?php if($status_filter == 'cancelled') echo 'selected'; ?>>취소</option>
				<option value="pending" <?php if($status_filter == 'pending') echo 'selected'; ?>>대기</option>
			</select>
		</div>
		<div class="manual-list-search-group">
			<select name="pg_filter">
				<option value="">PG전체</option>
				<option value="paysis" <?php if($pg_filter == 'paysis') echo 'selected'; ?>>페이시스</option>
			</select>
		</div>
		<div class="manual-list-search-group">
			<select name="auth_filter">
				<option value="">인증전체</option>
				<option value="nonauth" <?php if($auth_filter == 'nonauth') echo 'selected'; ?>>비인증</option>
				<option value="auth" <?php if($auth_filter == 'auth') echo 'selected'; ?>>구인증</option>
			</select>
		</div>
		<div class="search-divider"></div>
		<div class="radio-group">
			<label><input type="radio" name="sfl" value="pk_app_no" <?php echo get_checked($sfl, "pk_app_no"); ?> checked>승번</label>
			<label><input type="radio" name="sfl" value="pk_mb_6_name" <?php echo get_checked($sfl, "pk_mb_6_name"); ?>>가맹</label>
			<label><input type="radio" name="sfl" value="pk_order_no" <?php echo get_checked($sfl, "pk_order_no"); ?>>주문번호</label>
			<label><input type="radio" name="sfl" value="pk_goods_name" <?php echo get_checked($sfl, "pk_goods_name"); ?>>상품명</label>
			<label><input type="radio" name="sfl" value="pk_buyer_name" <?php echo get_checked($sfl, "pk_buyer_name"); ?>>구매자</label>
		</div>
		<div class="search-divider"></div>
		<div class="search-input-group">
			<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" placeholder="검색어">
			<button type="submit" class="btn-search">검색</button>
		</div>
	</div>
</div>
</form>

<div class="m_board_scroll">
	<div class="m_table_wrap" style="padding-bottom:115px;">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
				<tr>
					<th style="width:50px;">번호</th>
					<th>가맹점명</th>
					<th>상품명</th>
					<th>금액</th>
					<th>할부</th>
					<th>카드사</th>
					<th>상태</th>
					<th>승인번호</th>
					<th>요청일시</th>
					<th>PG</th>
					<th>인증</th>
					<th>응답메시지</th>
					<th>관리</th>
				</tr>
			</thead>
			<tbody>
				<?php
				if($total_count == 0) {
				?>
				<tr>
					<td colspan="13" class="center" style="padding: 40px 0; color: #999;">
						<i class="fa fa-inbox" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
						조회된 내역이 없습니다.
					</td>
				</tr>
				<?php
				} else {
					for ($i=0; $row=sql_fetch_array($result); $i++) {
						$num = number_format($total_count - ($page - 1) * $rows - $i);

						// 상태 표시
						$status_class = '';
						$status_text = '';
						$row_class = '';
						switch($row['pk_status']) {
							case 'approved':
								$status_class = 'approved';
								$status_text = '승인';
								break;
							case 'failed':
								$status_class = 'failed';
								$status_text = '실패';
								$row_class = 'row-failed';
								break;
							case 'cancelled':
								$status_class = 'cancelled';
								$status_text = '취소';
								$row_class = 'row-cancelled';
								break;
							case 'partial_cancelled':
								$status_class = 'cancelled';
								$status_text = '부분취소';
								$row_class = 'row-cancelled';
								break;
							case 'pending':
								$status_class = 'pending';
								$status_text = '대기';
								break;
						}

						// 인증타입 표시
						$auth_class = $row['pk_auth_type'] == 'auth' ? 'auth' : 'nonauth';
						$auth_text = $row['pk_auth_type'] == 'auth' ? '구인증' : '비인증';

						// 할부 표시
						if($row['pk_installment'] == '00' || $row['pk_installment'] == '0' || !$row['pk_installment']) {
							$installment_text = '일시불';
						} else {
							$installment_text = intval($row['pk_installment']) . '개월';
						}

						// PG사 이름
						$pg_name = $row['pk_pg_name'] ? $row['pk_pg_name'] : $row['pk_pg_code'];
				?>
				<tr class="<?php echo $row_class; ?>">
					<td class="center"><?php echo $num; ?></td>
					<td class="td_name"><?php echo htmlspecialchars($row['pk_mb_6_name']); ?></td>
					<td><?php echo htmlspecialchars($row['pk_goods_name']); ?></td>
					<td class="right"><?php echo number_format($row['pk_amount']); ?></td>
					<td class="center"><?php echo $installment_text; ?></td>
					<td class="center">
						<?php if($row['pk_card_issuer']) { echo htmlspecialchars(str_replace('카드', '', $row['pk_card_issuer'])); } ?>
						<?php if($row['pk_card_no_masked']) { ?><br><small style="color:#999;"><?php echo $row['pk_card_no_masked']; ?></small><?php } ?>
					</td>
					<td class="center"><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
					<td class="center"><?php echo $row['pk_app_no'] ? $row['pk_app_no'] : '-'; ?></td>
					<td class="center"><?php echo $row['pk_created_at']; ?></td>
					<td class="center"><?php echo $pg_name; ?></td>
					<td class="center"><span class="auth-badge <?php echo $auth_class; ?>"><?php echo $auth_text; ?></span></td>
					<td style="max-width:150px; font-size:11px; color:#666;">
						<?php
						if($row['pk_res_code']) {
							echo '[' . $row['pk_res_code'] . '] ';
						}
						echo htmlspecialchars($row['pk_res_msg']);
						?>
					</td>
					<td class="center">
						<?php if($row['pk_status'] == 'approved') { ?>
						<button type="button" class="btn-cancel" onclick="openCancelPopup(<?php echo $row['pk_id']; ?>)">취소</button>
						<?php } else { ?>
						-
						<?php } ?>
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
$qstr .= "&status_filter=".$status_filter;
$qstr .= "&pg_filter=".$pg_filter;
$qstr .= "&auth_filter=".$auth_filter;
$qstr .= "&sfl=".$sfl;
$qstr .= "&stx=".$stx;
echo get_paging_news(G5_IS_MOBILE ? "5" : "5", $page, $total_page, '?' . $qstr . '&amp;page=');
?>

<script>
function openCancelPopup(pk_id) {
	if(!confirm('이 거래를 취소하시겠습니까?')) return;

	var width = 500;
	var height = 600;
	var left = (screen.width - width) / 2;
	var top = (screen.height - height) / 2;

	window.open('/?p=manual_payment_cancel&pk_id=' + pk_id, 'cancel_popup',
		'width=' + width + ',height=' + height + ',left=' + left + ',top=' + top + ',scrollbars=yes');
}
</script>

<?php } else { ?>
<!-- ===== 가맹점용 화면 (관리자와 동일, 일부 컬럼 숨김) ===== -->
<div class="module-btn-wrapper top">
	<a href="/?p=manual_payment_module" class="btn-manual-module">
		<i class="fa fa-credit-card"></i>
		신규결제
	</a>
</div>

<div class="manual-list-header">
	<div class="manual-list-header-top">
		<div class="manual-list-title">
			<i class="fa fa-list-alt"></i>
			수기결제 내역
		</div>
		<div class="manual-list-stats">
			<div class="manual-list-stat approved">
				승인 <span><?php echo number_format($stat['approved_count']); ?>건</span> / <span><?php echo number_format($stat['approved_amount']); ?>원</span>
			</div>
			<div class="manual-list-stat failed">
				실패 <span><?php echo number_format($stat['failed_count']); ?>건</span>
			</div>
			<div class="manual-list-stat cancelled">
				취소 <span><?php echo number_format($stat['cancelled_count']); ?>건</span> / <span><?php echo number_format($stat['cancelled_amount']); ?>원</span>
			</div>
			<div class="manual-list-stat total">
				전체 <span><?php echo number_format($total_count); ?>건</span>
			</div>
		</div>
	</div>
</div>

<form id="fsearch" name="fsearch" method="get">
<input type="hidden" name="p" value="<?php echo $p; ?>">
<div class="manual-list-search">
	<div class="manual-list-search-row">
		<div class="manual-list-search-group">
			<input type="text" name="fr_date" value="<?php echo $fr_date ?>" id="fr_date" placeholder="시작일">
			<span>~</span>
			<input type="text" name="to_date" value="<?php echo $to_date ?>" id="to_date" placeholder="종료일">
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
		<div class="manual-list-search-group">
			<select name="status_filter">
				<option value="">상태전체</option>
				<option value="approved" <?php if($status_filter == 'approved') echo 'selected'; ?>>승인</option>
				<option value="failed" <?php if($status_filter == 'failed') echo 'selected'; ?>>실패</option>
				<option value="cancelled" <?php if($status_filter == 'cancelled') echo 'selected'; ?>>취소</option>
			</select>
		</div>
		<div class="search-divider"></div>
		<div class="radio-group">
			<label><input type="radio" name="sfl" value="pk_app_no" <?php echo get_checked($sfl, "pk_app_no"); ?> checked>승인번호</label>
			<label><input type="radio" name="sfl" value="pk_goods_name" <?php echo get_checked($sfl, "pk_goods_name"); ?>>상품명</label>
			<label><input type="radio" name="sfl" value="pk_buyer_name" <?php echo get_checked($sfl, "pk_buyer_name"); ?>>구매자</label>
		</div>
		<div class="search-divider"></div>
		<div class="search-input-group">
			<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" placeholder="검색어">
			<button type="submit" class="btn-search">검색</button>
		</div>
	</div>
</div>
</form>

<div class="m_board_scroll">
	<div class="m_table_wrap" style="padding-bottom:115px;">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
				<tr>
					<th style="width:50px;">번호</th>
					<th>상품명</th>
					<th>금액</th>
					<th>할부</th>
					<th>카드사</th>
					<th>상태</th>
					<th>승인번호</th>
					<th>요청일시</th>
					<th>관리</th>
				</tr>
			</thead>
			<tbody>
				<?php
				if($total_count == 0) {
				?>
				<tr>
					<td colspan="9" class="center" style="padding: 40px 0; color: #999;">
						<i class="fa fa-inbox" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
						조회된 내역이 없습니다.
					</td>
				</tr>
				<?php
				} else {
					// 결과를 다시 조회 (가맹점용)
					$result = sql_query($sql);
					for ($i=0; $row=sql_fetch_array($result); $i++) {
						$num = number_format($total_count - ($page - 1) * $rows - $i);

						// 상태 표시
						$status_class = '';
						$status_text = '';
						$row_class = '';
						switch($row['pk_status']) {
							case 'approved':
								$status_class = 'approved';
								$status_text = '승인';
								break;
							case 'failed':
								$status_class = 'failed';
								$status_text = '실패';
								$row_class = 'row-failed';
								break;
							case 'cancelled':
								$status_class = 'cancelled';
								$status_text = '취소';
								$row_class = 'row-cancelled';
								break;
							case 'partial_cancelled':
								$status_class = 'cancelled';
								$status_text = '부분취소';
								$row_class = 'row-cancelled';
								break;
							case 'pending':
								$status_class = 'pending';
								$status_text = '대기';
								break;
						}

						// 할부 표시
						if($row['pk_installment'] == '00' || $row['pk_installment'] == '0' || !$row['pk_installment']) {
							$installment_text = '일시불';
						} else {
							$installment_text = intval($row['pk_installment']) . '개월';
						}
				?>
				<tr class="<?php echo $row_class; ?>">
					<td class="center"><?php echo $num; ?></td>
					<td><?php echo htmlspecialchars($row['pk_goods_name']); ?></td>
					<td class="right"><?php echo number_format($row['pk_amount']); ?></td>
					<td class="center"><?php echo $installment_text; ?></td>
					<td class="center">
						<?php if($row['pk_card_issuer']) { echo htmlspecialchars(str_replace('카드', '', $row['pk_card_issuer'])); } ?>
						<?php if($row['pk_card_no_masked']) { ?><br><small style="color:#999;"><?php echo $row['pk_card_no_masked']; ?></small><?php } ?>
					</td>
					<td class="center"><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
					<td class="center"><?php echo $row['pk_app_no'] ? $row['pk_app_no'] : '-'; ?></td>
					<td class="center"><?php echo $row['pk_created_at']; ?></td>
					<td class="center">
						<?php if($row['pk_status'] == 'approved') { ?>
						<button type="button" class="btn-cancel" onclick="openCancelPopup(<?php echo $row['pk_id']; ?>)">취소</button>
						<?php } else { ?>
						-
						<?php } ?>
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
$qstr .= "&status_filter=".$status_filter;
$qstr .= "&sfl=".$sfl;
$qstr .= "&stx=".$stx;
echo get_paging_news(G5_IS_MOBILE ? "5" : "5", $page, $total_page, '?' . $qstr . '&amp;page=');
?>

<script>
function openCancelPopup(pk_id) {
	if(!confirm('이 거래를 취소하시겠습니까?')) return;

	var width = 500;
	var height = 600;
	var left = (screen.width - width) / 2;
	var top = (screen.height - height) / 2;

	window.open('/?p=manual_payment_cancel&pk_id=' + pk_id, 'cancel_popup',
		'width=' + width + ',height=' + height + ',left=' + left + ',top=' + top + ',scrollbars=yes');
}
</script>
<?php } ?>

<?php
include_once('./_tail.php');
?>
