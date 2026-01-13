<?php
/**
 * 수기결제 내역 CSV 다운로드
 */

include_once('./_common.php');

// 수기결제 권한 체크
if(!$is_admin && $member['mb_mailling'] != '1') {
	die("권한이 없습니다.");
}

$table_name = "g5_payment_keyin";

// 날짜 필터
$fr_date = isset($_GET['fr_date']) ? $_GET['fr_date'] : date("Ymd");
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date("Ymd");
$fr_dates = date("Y-m-d", strtotime($fr_date));
$to_dates = date("Y-m-d", strtotime($to_date));

// 접근 제어 SQL
if($is_admin) {
	if(adm_sql_common) {
		$adm_sql = " p.pk_mb_1 IN (".adm_sql_common.")";
	} else {
		$adm_sql = " (1)";
	}
} else if($member['mb_level'] == 8) {
	$adm_sql = " p.pk_mb_1 = '{$member['mb_id']}'";
} else if($member['mb_level'] == 7) {
	$adm_sql = " p.pk_mb_2 = '{$member['mb_id']}'";
} else if($member['mb_level'] == 6) {
	$adm_sql = " p.pk_mb_3 = '{$member['mb_id']}'";
} else if($member['mb_level'] == 5) {
	$adm_sql = " p.pk_mb_4 = '{$member['mb_id']}'";
} else if($member['mb_level'] == 4) {
	$adm_sql = " p.pk_mb_5 = '{$member['mb_id']}'";
} else if($member['mb_level'] == 3) {
	$adm_sql = " p.mb_id = '{$member['mb_id']}'";
}

// 검색 조건
if ($fr_date == "all" && $to_date == "all") {
	$sql_search = " WHERE ".$adm_sql;
} else {
	$sql_search = " WHERE ".$adm_sql." AND (p.pk_created_at BETWEEN '{$fr_dates} 00:00:00' AND '{$to_dates} 23:59:59')";
}

// 상태 필터
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';
if($status_filter && in_array($status_filter, ['approved', 'failed', 'cancelled', 'pending'])) {
	$sql_search .= " AND p.pk_status = '{$status_filter}'";
}

// PG사 필터
$pg_filter = isset($_GET['pg_filter']) ? sql_escape_string($_GET['pg_filter']) : '';
if($pg_filter) {
	$sql_search .= " AND p.pk_pg_code = '{$pg_filter}'";
}

// 인증타입 필터
$auth_filter = isset($_GET['auth_filter']) ? sql_escape_string($_GET['auth_filter']) : '';
if($auth_filter && in_array($auth_filter, ['nonauth', 'auth'])) {
	$sql_search .= " AND p.pk_auth_type = '{$auth_filter}'";
}

// 검색어
$sfl = isset($_GET['sfl']) ? $_GET['sfl'] : '';
$stx = isset($_GET['stx']) ? $_GET['stx'] : '';
if ($stx) {
	$sql_search .= " AND ( ";
	switch ($sfl) {
		case "pk_app_no" :
		case "pk_order_no" :
			$sql_search .= " (p.{$sfl} = '{$stx}') ";
			break;
		case "pk_card_no" :
			$card_search = preg_replace('/[^0-9]/', '', $stx);
			if(strlen($card_search) == 4) {
				$sql_search .= " (p.pk_card_no_masked LIKE '{$card_search}%' OR p.pk_card_no_masked LIKE '%{$card_search}') ";
			} else {
				$sql_search .= " (p.pk_card_no_masked LIKE '%{$card_search}%') ";
			}
			break;
		default :
			$sql_search .= " (p.{$sfl} LIKE '%{$stx}%') ";
			break;
	}
	$sql_search .= " ) ";
}

// 데이터 조회
$sql = "SELECT p.* FROM {$table_name} p {$sql_search} ORDER BY p.pk_created_at DESC";
$result = sql_query($sql);

// 파일명 생성
$filename = "수기결제내역_" . date("Ymd_His") . ".csv";

// 출력 버퍼 정리
while(ob_get_level()) {
	ob_end_clean();
}

// CSV 헤더 출력
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

// BOM for Excel UTF-8
echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');

// 헤더 행
if($is_admin) {
	fputcsv($output, ['번호', '가맹점명', '주문번호', '상품명', '금액', '할부', '카드사', '카드번호', '구매자명', '구매자연락처', '상태', '승인번호', '요청일시', '취소일시', 'PG', '인증타입', '응답메시지']);
} else {
	fputcsv($output, ['번호', '주문번호', '상품명', '금액', '할부', '카드사', '카드번호', '구매자명', '구매자연락처', '상태', '승인번호', '요청일시', '취소일시', '응답메시지']);
}

// 텍스트로 강제 처리 (선행 0 보존)
function forceText($val) {
	return $val ? "\t" . $val : '';
}

// 데이터 행
$num = 0;
while($row = sql_fetch_array($result)) {
	$num++;

	// 상태 텍스트
	$status_text = '';
	switch($row['pk_status']) {
		case 'approved': $status_text = '승인'; break;
		case 'failed': $status_text = '실패'; break;
		case 'cancelled': $status_text = '취소'; break;
		case 'partial_cancelled': $status_text = '부분취소'; break;
		case 'pending': $status_text = '대기'; break;
	}

	// 할부 텍스트
	if($row['pk_installment'] == '00' || $row['pk_installment'] == '0' || !$row['pk_installment']) {
		$installment_text = '일시불';
	} else {
		$installment_text = intval($row['pk_installment']) . '개월';
	}

	// 인증타입
	$auth_text = $row['pk_auth_type'] == 'auth' ? '구인증' : '비인증';

	// 취소일시
	$cancel_date = '';
	if($row['pk_status'] == 'cancelled' && $row['pk_cancel_date']) {
		$cd = $row['pk_cancel_date'];
		if(strlen($cd) == 14 && is_numeric($cd)) {
			$cancel_date = substr($cd, 0, 4) . '-' . substr($cd, 4, 2) . '-' . substr($cd, 6, 2) . ' ' . substr($cd, 8, 2) . ':' . substr($cd, 10, 2) . ':' . substr($cd, 12, 2);
		} else {
			$cancel_date = $cd;
		}
	}

	// PG명
	$pg_name = $row['pk_pg_name'] ? $row['pk_pg_name'] : $row['pk_pg_code'];

	if($is_admin) {
		fputcsv($output, [
			$num,
			$row['pk_mb_6_name'],
			forceText($row['pk_order_no']),
			$row['pk_goods_name'],
			$row['pk_amount'],
			$installment_text,
			str_replace('카드', '', $row['pk_card_issuer']),
			forceText($row['pk_card_no_masked']),
			$row['pk_buyer_name'],
			forceText($row['pk_buyer_phone']),
			$status_text,
			forceText($row['pk_app_no']),
			$row['pk_created_at'],
			$cancel_date,
			$pg_name,
			$auth_text,
			$row['pk_res_msg']
		]);
	} else {
		fputcsv($output, [
			$num,
			forceText($row['pk_order_no']),
			$row['pk_goods_name'],
			$row['pk_amount'],
			$installment_text,
			str_replace('카드', '', $row['pk_card_issuer']),
			forceText($row['pk_card_no_masked']),
			$row['pk_buyer_name'],
			forceText($row['pk_buyer_phone']),
			$status_text,
			forceText($row['pk_app_no']),
			$row['pk_created_at'],
			$cancel_date,
			$row['pk_res_msg']
		]);
	}
}

fclose($output);
exit;
