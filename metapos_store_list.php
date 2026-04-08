<?php
/**
 * POS매장 매장정보 목록 페이지
 * - metapos_store 테이블 조회
 * - 관리자 전용
 */

$title1 = "POS매장";
$title2 = "매장정보";

include_once('./_common.php');

// 관리자 권한 체크
if(!$is_admin) {
	alert("관리자만 접근 가능합니다.");
}

// 테이블명
$table_name = "metapos_store";
$history_table = "metapos_store_history";

// 검색 조건
$stx = isset($_GET['stx']) ? sql_escape_string($_GET['stx']) : '';

// 상태 필터
$status_filter = isset($_GET['st_use']) ? $_GET['st_use'] : '';

// WHERE 절 구성
$sql_where = " WHERE 1 ";

// 상태 필터
if($status_filter && in_array($status_filter, ['Y', 'N'])) {
	$sql_where .= " AND st_use = '{$status_filter}'";
}

// 검색어 (상호명)
if($stx) {
	$sql_where .= " AND st_name LIKE '%{$stx}%'";
}

// 정렬
$sst = isset($_GET['sst']) ? sql_escape_string($_GET['sst']) : '';
$sod = isset($_GET['sod']) ? sql_escape_string($_GET['sod']) : '';

if($sst) {
	$sql_order = " ORDER BY {$sst} {$sod} ";
} else {
	$sql_order = " ORDER BY updated_at DESC, ms_id DESC ";
}

// 통계 조회
$sql = "SELECT
	COUNT(*) as cnt,
	COUNT(IF(st_use = 'Y', 1, NULL)) as open_count,
	COUNT(IF(st_use = 'N', 1, NULL)) as close_count
	FROM {$table_name} {$sql_where}";
$stat = sql_fetch($sql);

$total_count = $stat['cnt'];
$page_count = 20;
$rows = $page_count;

$total_page = ceil($total_count / $rows);
if($page < 1) $page = 1;
$from_record = ($page - 1) * $rows;

// 목록 조회
$sql = "SELECT * FROM {$table_name} {$sql_where} {$sql_order} LIMIT {$from_record}, {$rows}";
$result = sql_query($sql);

include_once('./_head.php');
?>

<style>
.metapos-header {
	background: linear-gradient(135deg, #E65100 0%, #FF6D00 100%);
	border-radius: 8px;
	padding: 12px 16px;
	margin-bottom: 10px;
	box-shadow: 0 2px 8px rgba(230, 81, 0, 0.3);
}
.metapos-header-top {
	display: flex;
	align-items: center;
	justify-content: space-between;
	flex-wrap: wrap;
	gap: 10px;
	margin-bottom: 10px;
}
.metapos-header-bottom {
	display: flex;
	align-items: center;
	justify-content: flex-start;
}
.metapos-title {
	color: #fff;
	font-size: 16px;
	font-weight: 600;
	display: flex;
	align-items: center;
	gap: 8px;
}
.metapos-title i {
	font-size: 14px;
	opacity: 0.8;
}
.metapos-stats {
	display: flex;
	flex-wrap: wrap;
	gap: 6px;
}
.metapos-stat {
	display: inline-flex;
	align-items: center;
	background: rgba(255,255,255,0.12);
	border-radius: 4px;
	padding: 4px 10px;
	font-size: 12px;
	color: rgba(255,255,255,0.85);
	gap: 6px;
}
.metapos-stat.open {
	background: rgba(76,175,80,0.3);
}
.metapos-stat.close {
	background: rgba(158,158,158,0.3);
}
.metapos-stat.total {
	background: rgba(255,255,255,0.25);
	color: #fff;
	font-weight: 600;
}
.metapos-stat span {
	color: #fff;
	font-weight: 600;
}
.metapos-search {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 10px 12px;
	margin-bottom: 10px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.metapos-search-row {
	display: flex;
	align-items: center;
	flex-wrap: wrap;
	gap: 8px;
}
.metapos-search-group {
	display: flex;
	align-items: center;
	gap: 4px;
}
.metapos-search-group input[type="text"],
.metapos-search-group select {
	padding: 6px 8px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-size: 13px;
	background: #f8f9fa;
}
.metapos-search-group input[type="text"] {
	width: 180px;
}
.metapos-search-group select {
	min-width: 100px;
}
.metapos-search-group input[type="text"]:focus,
.metapos-search-group select:focus {
	outline: none;
	border-color: #FF6D00;
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
	background: #E65100;
	color: #fff;
	border: none;
	border-radius: 4px;
	font-size: 12px;
	cursor: pointer;
	transition: background 0.15s;
}
.btn-search:hover {
	background: #FF6D00;
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
/* 상태 배지 */
.status-badge {
	display: inline-block;
	padding: 3px 8px;
	border-radius: 12px;
	font-size: 11px;
	font-weight: 600;
}
.status-badge.open {
	background: #e8f5e9;
	color: #2e7d32;
}
.status-badge.close {
	background: #fafafa;
	color: #757575;
}
/* 테이블 행 */
tr.row-close {
	background: #fafafa !important;
}
tr.row-close td {
	color: #9e9e9e;
}
/* 매출 버튼 */
.btn-sales {
	display: inline-block;
	padding: 4px 8px;
	background: #1565c0;
	color: #fff;
	border: none;
	border-radius: 3px;
	font-size: 11px;
	cursor: pointer;
	transition: background 0.15s;
	text-decoration: none;
	margin-right: 4px;
}
.btn-sales:hover {
	background: #1976d2;
	color: #fff;
}
/* 히스토리 버튼 */
.btn-history {
	padding: 4px 8px;
	background: #7b1fa2;
	color: #fff;
	border: none;
	border-radius: 3px;
	font-size: 11px;
	cursor: pointer;
	transition: background 0.15s;
}
.btn-history:hover {
	background: #9c27b0;
}
/* 히스토리 모달 */
.history-modal-overlay {
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
.history-modal-overlay.show {
	display: flex;
}
.history-modal {
	width: 600px;
	max-width: 100%;
	max-height: 80vh;
	background: #fff;
	border-radius: 12px;
	overflow: hidden;
	box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}
.history-modal-header {
	background: linear-gradient(135deg, #7b1fa2 0%, #9c27b0 100%);
	padding: 16px 20px;
	display: flex;
	align-items: center;
	justify-content: space-between;
}
.history-modal-header h3 {
	color: #fff;
	font-size: 16px;
	font-weight: 600;
	margin: 0;
	display: flex;
	align-items: center;
	gap: 8px;
}
.history-modal-close {
	background: none;
	border: none;
	color: #fff;
	font-size: 20px;
	cursor: pointer;
	opacity: 0.8;
}
.history-modal-close:hover {
	opacity: 1;
}
.history-modal-body {
	padding: 20px;
	max-height: 60vh;
	overflow-y: auto;
}
.history-item {
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 12px;
	margin-bottom: 10px;
}
.history-item:last-child {
	margin-bottom: 0;
}
.history-item-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 8px;
}
.history-item-type {
	display: inline-block;
	padding: 2px 8px;
	border-radius: 4px;
	font-size: 11px;
	font-weight: 600;
}
.history-item-type.insert {
	background: #e3f2fd;
	color: #1565c0;
}
.history-item-type.update {
	background: #fff3e0;
	color: #e65100;
}
.history-item-date {
	font-size: 12px;
	color: #666;
}
.history-item-changes {
	font-size: 12px;
	color: #333;
}
.history-item-changes .field-change {
	padding: 4px 0;
	border-bottom: 1px solid #f0f0f0;
}
.history-item-changes .field-change:last-child {
	border-bottom: none;
}
.history-item-changes .field-name {
	font-weight: 600;
	color: #7b1fa2;
}
.history-item-changes .old-value {
	color: #c62828;
	text-decoration: line-through;
}
.history-item-changes .new-value {
	color: #2e7d32;
}
/* 동기화 결과 모달 */
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
	background: linear-gradient(135deg, #1565c0 0%, #1976d2 100%);
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
.sync-result-body .stat-item.updated .stat-value {
	color: #e65100;
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
	background: #E65100;
	color: #fff;
	transition: background 0.2s;
	white-space: nowrap;
}
.sync-result-footer .btn:hover {
	background: #FF6D00;
}
/* 로딩 스피너 */
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
	border-top: 4px solid #E65100;
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
@media (max-width: 768px) {
	.metapos-header-top {
		flex-direction: row;
		align-items: center;
		justify-content: space-between;
	}
	.metapos-header-bottom {
		flex-direction: column;
		align-items: flex-start;
	}
	.metapos-stats {
		width: 100%;
		flex-wrap: wrap;
		gap: 4px;
	}
	.metapos-stat {
		font-size: 10px;
		padding: 3px 6px;
	}
	.metapos-search {
		padding: 8px;
	}
	.metapos-search-row {
		flex-direction: column;
		align-items: stretch;
		gap: 6px;
	}
	.metapos-search-group {
		width: 100%;
	}
	.metapos-search-group input[type="text"] {
		flex: 1;
		width: auto;
	}
	.metapos-search-group select {
		flex: 1;
	}
	.search-divider {
		display: none;
	}
	.history-modal {
		width: 100%;
	}
}
</style>

<!-- 로딩 스피너 -->
<div class="loading-overlay" id="loadingOverlay">
	<div class="spinner"></div>
	<div class="loading-text">동기화 중...</div>
</div>

<!-- 동기화 결과 모달 -->
<div class="history-modal-overlay" id="syncResultOverlay">
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
				<div class="stat-item updated">
					<div class="stat-value" id="syncUpdated">0</div>
					<div class="stat-label">수정</div>
				</div>
				<div class="stat-item">
					<div class="stat-value" id="syncSkipped">0</div>
					<div class="stat-label">변경없음</div>
				</div>
			</div>
			<div id="syncMessage" style="font-size:13px; color:#666;"></div>
		</div>
		<div class="sync-result-footer"><button type="button" class="btn" onclick="closeSyncResult()">확인</button></div>
	</div>
</div>

<!-- 히스토리 모달 -->
<div class="history-modal-overlay" id="historyModalOverlay">
	<div class="history-modal">
		<div class="history-modal-header">
			<h3><i class="fa fa-history"></i> 변경 히스토리</h3>
			<button type="button" class="history-modal-close" onclick="closeHistoryModal()">&times;</button>
		</div>
		<div class="history-modal-body" id="historyModalBody">
			<!-- 히스토리 내용 로드 -->
		</div>
	</div>
</div>

<div class="metapos-header">
	<div class="metapos-header-top">
		<div class="metapos-title">
			<i class="fa fa-building"></i>
			POS매장 매장정보
		</div>
		<button type="button" class="btn-sync" id="btnSync" onclick="syncMetapos()">
			<i class="fa fa-refresh"></i> API 동기화
		</button>
	</div>
	<div class="metapos-header-bottom">
		<div class="metapos-stats">
			<div class="metapos-stat open">
				영업 <span><?php echo number_format($stat['open_count']); ?>개</span>
			</div>
			<div class="metapos-stat close">
				폐점 <span><?php echo number_format($stat['close_count']); ?>개</span>
			</div>
			<div class="metapos-stat total">
				전체 <span><?php echo number_format($total_count); ?>개</span>
			</div>
		</div>
	</div>
</div>

<form id="fsearch" name="fsearch" method="get">
<input type="hidden" name="p" value="<?php echo $p; ?>">
<div class="metapos-search">
	<div class="metapos-search-row">
		<div class="metapos-search-group">
			<select name="st_use">
				<option value="">상태 전체</option>
				<option value="Y" <?php if($status_filter == 'Y') echo 'selected'; ?>>영업중</option>
				<option value="N" <?php if($status_filter == 'N') echo 'selected'; ?>>폐점</option>
			</select>
		</div>
		<div class="search-divider"></div>
		<div class="metapos-search-group">
			<input type="text" name="stx" value="<?php echo htmlspecialchars($stx); ?>" placeholder="매장명 검색">
			<button type="submit" class="btn-search"><i class="fa fa-search"></i> 검색</button>
		</div>
	</div>
</div>
</form>

<div class="m_board_scroll">
	<div class="m_table_wrap" style="padding-bottom:30px;">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
				<tr>
					<th style="width:50px;">번호</th>
					<th>매장 ID</th>
					<th>브랜드</th>
					<th>매장명</th>
					<th>사업자번호</th>
					<th>대표자</th>
					<th>전화번호</th>
					<th>휴대폰</th>
					<th>주소</th>
					<th>상태</th>
					<th>최종수정</th>
					<th>관리</th>
				</tr>
			</thead>
			<tbody>
				<?php
				if($total_count == 0) {
				?>
				<tr>
					<td colspan="12" class="center" style="padding: 40px 0; color: #999;">
						<i class="fa fa-inbox" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
						조회된 매장이 없습니다.
					</td>
				</tr>
				<?php
				} else {
					for ($i=0; $row=sql_fetch_array($result); $i++) {
						$num = number_format($total_count - ($page - 1) * $rows - $i);

						// 상태 표시
						$status_class = ($row['st_use'] == 'Y') ? 'open' : 'close';
						$status_text = ($row['st_use'] == 'Y') ? '영업' : '폐점';
						$row_class = ($row['st_use'] == 'N') ? 'row-close' : '';
				?>
				<tr class="<?php echo $row_class; ?>">
					<td class="center"><?php echo $num; ?></td>
					<td class="center" style="font-size:11px;"><?php echo htmlspecialchars($row['st_uid']); ?></td>
					<td class="center"><?php echo htmlspecialchars($row['br_name']); ?></td>
					<td class="td_name"><?php echo htmlspecialchars($row['st_name']); ?></td>
					<td class="center"><?php echo $row['st_biz_no'] ? htmlspecialchars($row['st_biz_no']) : '-'; ?></td>
					<td class="center"><?php echo $row['st_ceo_nm'] ? htmlspecialchars($row['st_ceo_nm']) : '-'; ?></td>
					<td class="center"><?php echo $row['st_tel'] ? htmlspecialchars($row['st_tel']) : '-'; ?></td>
					<td class="center"><?php echo $row['st_hp'] ? htmlspecialchars($row['st_hp']) : '-'; ?></td>
					<td style="max-width:200px; font-size:11px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?php echo $row['st_addr'] ? htmlspecialchars($row['st_addr']) : ''; ?>"><?php echo $row['st_addr'] ? htmlspecialchars($row['st_addr']) : '-'; ?></td>
					<td class="center">
						<span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
					</td>
					<td class="center" style="font-size:11px;"><?php echo $row['updated_at']; ?></td>
					<td class="center" style="white-space:nowrap;">
						<a href="/?p=metapos_payment_list&st_uid=<?php echo $row['st_uid']; ?>" class="btn-sales" 
    style="height: 25px;">
							<i class="fa fa-won"></i> 매출
						</a>
						<button type="button" class="btn-history" onclick="openHistoryModal('<?php echo $row['st_uid']; ?>', '<?php echo addslashes($row['st_name']); ?>')" 
    style="height: 25px;">
							<i class="fa fa-history"></i> 히스토리
						</button>
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
if($status_filter) $qstr .= "&st_use=".$status_filter;
if($stx) $qstr .= "&stx=".urlencode($stx);
echo get_paging_news(G5_IS_MOBILE ? "5" : "5", $page, $total_page, '?' . $qstr . '&amp;page=');
?>

<script>
// API 동기화
function syncMetapos() {
	if(!confirm('MetaPOS API에서 매장 정보를 동기화하시겠습니까?')) return;

	$('#loadingOverlay').addClass('show');
	$('#btnSync').prop('disabled', true);

	$.ajax({
		url: './worker/metapos_store.php',
		type: 'GET',
		dataType: 'json',
		success: function(response) {
			$('#loadingOverlay').removeClass('show');
			$('#btnSync').prop('disabled', false);

			if(response.status == 'success') {
				$('#syncInserted').text(response.sync_result.inserted);
				$('#syncUpdated').text(response.sync_result.updated);
				$('#syncSkipped').text(response.sync_result.skipped);
				$('#syncMessage').text('총 ' + response.sync_result.total + '개 매장 처리 완료');
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

// 히스토리 모달
function openHistoryModal(st_uid, st_name) {
	$('#historyModalBody').html('<div style="text-align:center; padding:30px; color:#666;"><i class="fa fa-spinner fa-spin" style="font-size:24px;"></i><br><br>로딩 중...</div>');
	$('#historyModalOverlay').addClass('show');

	$.ajax({
		url: './metapos_store_history.php',
		type: 'GET',
		data: { st_uid: st_uid },
		dataType: 'json',
		success: function(response) {
			if(response.success && response.data.length > 0) {
				var html = '<div style="margin-bottom:15px; font-weight:600; color:#333;">매장: ' + st_name + '</div>';

				response.data.forEach(function(item) {
					var typeClass = item.change_type.toLowerCase();
					var typeText = item.change_type == 'INSERT' ? '신규등록' : '수정';

					html += '<div class="history-item">';
					html += '<div class="history-item-header">';
					html += '<span class="history-item-type ' + typeClass + '">' + typeText + '</span>';
					html += '<span class="history-item-date">' + item.created_at + '</span>';
					html += '</div>';

					if(item.change_type == 'UPDATE' && item.changed_fields) {
						html += '<div class="history-item-changes">';
						var changes = JSON.parse(item.changed_fields);
						for(var field in changes) {
							html += '<div class="field-change">';
							html += '<span class="field-name">' + getFieldLabel(field) + ':</span> ';
							html += '<span class="old-value">' + (changes[field].old || '(없음)') + '</span>';
							html += ' → ';
							html += '<span class="new-value">' + (changes[field].new || '(없음)') + '</span>';
							html += '</div>';
						}
						html += '</div>';
					} else if(item.change_type == 'INSERT') {
						html += '<div class="history-item-changes" style="color:#666;">매장 정보가 처음 등록되었습니다.</div>';
					}

					html += '</div>';
				});

				$('#historyModalBody').html(html);
			} else {
				$('#historyModalBody').html('<div style="text-align:center; padding:30px; color:#999;">변경 히스토리가 없습니다.</div>');
			}
		},
		error: function() {
			$('#historyModalBody').html('<div style="text-align:center; padding:30px; color:#c62828;">히스토리를 불러오는데 실패했습니다.</div>');
		}
	});
}

function closeHistoryModal() {
	$('#historyModalOverlay').removeClass('show');
}

function getFieldLabel(field) {
	var labels = {
		'br_name': '브랜드명',
		'st_name': '매장명',
		'st_biz_no': '사업자번호',
		'st_use': '상태',
		'st_ceo_nm': '대표자',
		'st_tel': '전화번호',
		'st_hp': '휴대폰',
		'st_addr': '주소'
	};
	return labels[field] || field;
}

// ESC 키로 모달 닫기
$(document).keydown(function(e) {
	if(e.keyCode == 27) {
		closeHistoryModal();
		closeSyncResult();
	}
});

// 오버레이 클릭 시 모달 닫기
$('#historyModalOverlay').click(function(e) {
	if(e.target === this) {
		closeHistoryModal();
	}
});
$('#syncResultOverlay').click(function(e) {
	if(e.target === this) {
		closeSyncResult();
	}
});
</script>

<?php
include_once('./_tail.php');
?>
