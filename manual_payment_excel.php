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

// NOTI 상태 필터 (관리자 전용)
$noti_filter = isset($_GET['noti_filter']) ? $_GET['noti_filter'] : '';
if($is_admin && $noti_filter && in_array($noti_filter, ['noti_missing', 'payment_missing', 'normal'])) {
	// 승인 건만 대상
	$sql_search .= " AND p.pk_status = 'approved' AND p.pk_app_no IS NOT NULL AND p.pk_app_no != ''";

	if($noti_filter == 'noti_missing') {
		// PG NOTI 테이블에 레코드 없음
		$sql_search .= " AND (
			(p.pk_pg_code = 'paysis' AND NOT EXISTS (
				SELECT 1 FROM g5_payment_paysis noti WHERE noti.connCd='0005' AND noti.appNo = p.pk_app_no AND CAST(noti.amt AS SIGNED) = p.pk_amount
			))
			OR (p.pk_pg_code = 'stn' AND NOT EXISTS (
				SELECT 1 FROM g5_payment_stn noti WHERE noti.requestFlag='K' AND noti.applNo = p.pk_app_no AND CAST(noti.amount AS SIGNED) = p.pk_amount
			))
			OR (p.pk_pg_code = 'rootup' AND NOT EXISTS (
				SELECT 1 FROM g5_payment_routeup noti WHERE noti.module_type='1' AND noti.appr_num = p.pk_app_no AND CAST(noti.amount AS SIGNED) = p.pk_amount
			))
			OR (p.pk_pg_code = 'winglobal' AND NOT EXISTS (
				SELECT 1 FROM g5_payment_daou noti WHERE noti.appNo = p.pk_app_no AND CAST(noti.amt AS SIGNED) = p.pk_amount
			) AND NOT EXISTS (
				SELECT 1 FROM g5_payment_korpay noti WHERE noti.appNo = p.pk_app_no AND CAST(noti.amt AS SIGNED) = p.pk_amount
			) AND NOT EXISTS (
				SELECT 1 FROM g5_payment_danal noti WHERE noti.CARDAUTHNO = p.pk_app_no AND CAST(noti.AMOUNT AS SIGNED) = p.pk_amount
			))
		)";
	} else if($noti_filter == 'payment_missing') {
		// PG NOTI 있지만 g5_payment에 없음
		$sql_search .= " AND (
			(p.pk_pg_code IN ('paysis','stn','rootup') AND (
				(p.pk_pg_code = 'paysis' AND EXISTS (
					SELECT 1 FROM g5_payment_paysis noti WHERE noti.connCd='0005' AND noti.appNo = p.pk_app_no AND CAST(noti.amt AS SIGNED) = p.pk_amount
				))
				OR (p.pk_pg_code = 'stn' AND EXISTS (
					SELECT 1 FROM g5_payment_stn noti WHERE noti.requestFlag='K' AND noti.applNo = p.pk_app_no AND CAST(noti.amount AS SIGNED) = p.pk_amount
				))
				OR (p.pk_pg_code = 'rootup' AND EXISTS (
					SELECT 1 FROM g5_payment_routeup noti WHERE noti.module_type='1' AND noti.appr_num = p.pk_app_no AND CAST(noti.amount AS SIGNED) = p.pk_amount
				))
			) AND NOT EXISTS (
				SELECT 1 FROM g5_payment gp
				WHERE gp.pg_name IN ('paysis_keyin','stn_k','routeup_k')
				AND gp.pay_num = p.pk_app_no AND gp.pay = p.pk_amount
			))
			OR (p.pk_pg_code = 'winglobal' AND (
				EXISTS (SELECT 1 FROM g5_payment_daou noti WHERE noti.appNo = p.pk_app_no AND CAST(noti.amt AS SIGNED) = p.pk_amount)
				OR EXISTS (SELECT 1 FROM g5_payment_korpay noti WHERE noti.appNo = p.pk_app_no AND CAST(noti.amt AS SIGNED) = p.pk_amount)
				OR EXISTS (SELECT 1 FROM g5_payment_danal noti WHERE noti.CARDAUTHNO = p.pk_app_no AND CAST(noti.AMOUNT AS SIGNED) = p.pk_amount)
			) AND NOT EXISTS (
				SELECT 1 FROM g5_payment gp
				WHERE gp.pg_name IN ('daou','korpay','danal')
				AND gp.pay_num = p.pk_app_no AND gp.pay = p.pk_amount
			))
		)";
	} else if($noti_filter == 'normal') {
		// 정상: PG NOTI + g5_payment 모두 존재
		$sql_search .= " AND (
			(p.pk_pg_code IN ('paysis','stn','rootup') AND (
				(p.pk_pg_code = 'paysis' AND EXISTS (
					SELECT 1 FROM g5_payment_paysis noti WHERE noti.connCd='0005' AND noti.appNo = p.pk_app_no AND CAST(noti.amt AS SIGNED) = p.pk_amount
				))
				OR (p.pk_pg_code = 'stn' AND EXISTS (
					SELECT 1 FROM g5_payment_stn noti WHERE noti.requestFlag='K' AND noti.applNo = p.pk_app_no AND CAST(noti.amount AS SIGNED) = p.pk_amount
				))
				OR (p.pk_pg_code = 'rootup' AND EXISTS (
					SELECT 1 FROM g5_payment_routeup noti WHERE noti.module_type='1' AND noti.appr_num = p.pk_app_no AND CAST(noti.amount AS SIGNED) = p.pk_amount
				))
			) AND EXISTS (
				SELECT 1 FROM g5_payment gp
				WHERE gp.pg_name IN ('paysis_keyin','stn_k','routeup_k')
				AND gp.pay_num = p.pk_app_no AND gp.pay = p.pk_amount
			))
			OR (p.pk_pg_code = 'winglobal' AND (
				EXISTS (SELECT 1 FROM g5_payment_daou noti WHERE noti.appNo = p.pk_app_no AND CAST(noti.amt AS SIGNED) = p.pk_amount)
				OR EXISTS (SELECT 1 FROM g5_payment_korpay noti WHERE noti.appNo = p.pk_app_no AND CAST(noti.amt AS SIGNED) = p.pk_amount)
				OR EXISTS (SELECT 1 FROM g5_payment_danal noti WHERE noti.CARDAUTHNO = p.pk_app_no AND CAST(noti.AMOUNT AS SIGNED) = p.pk_amount)
			) AND EXISTS (
				SELECT 1 FROM g5_payment gp
				WHERE gp.pg_name IN ('daou','korpay','danal')
				AND gp.pay_num = p.pk_app_no AND gp.pay = p.pk_amount
			))
		)";
	}
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

// 정렬
$sst = isset($_GET['sst']) ? $_GET['sst'] : '';
$sod = isset($_GET['sod']) ? $_GET['sod'] : '';
// 정렬 필드 화이트리스트 (SQL 인젝션 방지)
$allowed_sst = ['pk_created_at', 'pk_amount', 'pk_status', 'pk_pg_code', 'pk_mb_6_name', 'pk_buyer_name', 'pk_goods_name', 'pk_app_no', 'pk_order_no', 'pk_card_issuer', 'pk_auth_type'];
if ($sst && in_array($sst, $allowed_sst)) {
	$sod = ($sod == 'asc') ? 'asc' : 'desc';
	$sql_order = " ORDER BY p.{$sst} {$sod} ";
} else {
	$sql_order = " ORDER BY p.pk_created_at DESC ";
}

// 데이터 조회
$sql = "SELECT p.* FROM {$table_name} p {$sql_search}{$sql_order}";
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

// 연락처 하이픈 포맷 (선행 0 보존)
function formatPhone($phone) {
	if (!$phone) return '';
	$num = preg_replace('/[^0-9]/', '', $phone);
	if (strlen($num) == 11) {
		return substr($num, 0, 3) . '-' . substr($num, 3, 4) . '-' . substr($num, 7, 4);
	} else if (strlen($num) == 10) {
		return substr($num, 0, 3) . '-' . substr($num, 3, 3) . '-' . substr($num, 6, 4);
	}
	return $phone;
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
			$row['pk_order_no'],
			$row['pk_goods_name'],
			$row['pk_amount'],
			$installment_text,
			str_replace('카드', '', $row['pk_card_issuer']),
			$row['pk_card_no_masked'],
			$row['pk_buyer_name'],
			formatPhone($row['pk_buyer_phone']),
			$status_text,
			$row['pk_app_no'],
			$row['pk_created_at'],
			$cancel_date,
			$pg_name,
			$auth_text,
			$row['pk_res_msg']
		]);
	} else {
		fputcsv($output, [
			$num,
			$row['pk_order_no'],
			$row['pk_goods_name'],
			$row['pk_amount'],
			$installment_text,
			str_replace('카드', '', $row['pk_card_issuer']),
			$row['pk_card_no_masked'],
			$row['pk_buyer_name'],
			formatPhone($row['pk_buyer_phone']),
			$status_text,
			$row['pk_app_no'],
			$row['pk_created_at'],
			$cancel_date,
			$row['pk_res_msg']
		]);
	}
}

fclose($output);
exit;
