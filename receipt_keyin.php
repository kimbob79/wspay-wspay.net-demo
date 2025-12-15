<?php
/**
 * 수기결제 영수증 페이지
 * - g5_payment_keyin 테이블 데이터를 사용하여 영수증 출력
 */
include_once('./_common.php');

$pk_id = isset($_GET['pk_id']) ? intval($_GET['pk_id']) : 0;
if(!$pk_id) die('잘못된 경로입니다');

$row = sql_fetch("SELECT * FROM g5_payment_keyin WHERE pk_id = '{$pk_id}'");
if(!$row) die('잘못된 경로입니다');

// 접근 권한 체크 (관리자 또는 해당 가맹점만)
if(!$is_admin) {
	if($member['mb_level'] == 3 && $row['mb_id'] != $member['mb_id']) {
		die('접근 권한이 없습니다');
	} else if($member['mb_level'] > 3 && $member['mb_level'] < 10) {
		// 상위 계층은 하위 데이터 조회 가능
		$level_field = 'pk_mb_' . (9 - $member['mb_level']);
		if($row[$level_field] != $member['mb_id']) {
			die('접근 권한이 없습니다');
		}
	}
}

// 가맹점 정보 조회
$mb = sql_fetch("SELECT * FROM g5_member WHERE mb_id = '{$row['mb_id']}'");

// PG사 정보
$pgc_company = '원성페이먼츠';
$pgc_name = '조용기';
$pgc_number = '596-88-02642';
$pgc_tel = '1555-0985';
$pgc_addr = '경기도 수원시 영통구 창룡대로 256번길 91, B107호';

// 결제 금액 및 부가세 계산
$pay = $row['pk_amount'];
$is_cancelled = ($row['pk_status'] == 'cancelled' || $row['pk_status'] == 'partial_cancelled');

if($is_cancelled) {
	$vat = intval(floor(-$row['pk_amount'] * 10 / 110)) * -1;
} else {
	$vat = intval(floor($row['pk_amount'] * 10 / 110));
}
$payMinusVat = $pay - $vat;

// 할부 표시
if($row['pk_installment'] == '00' || $row['pk_installment'] == '0' || !$row['pk_installment']) {
	$installment_text = '일시불';
} else {
	$installment_text = intval($row['pk_installment']) . '개월';
}

// 승인일시 포맷팅
$app_date = $row['pk_app_date'];
if(strlen($app_date) == 14 && is_numeric($app_date)) {
	$app_date_formatted = substr($app_date, 0, 4) . '-' . substr($app_date, 4, 2) . '-' . substr($app_date, 6, 2) . ' ' . substr($app_date, 8, 2) . ':' . substr($app_date, 10, 2) . ':' . substr($app_date, 12, 2);
} else {
	$app_date_formatted = $app_date ? $app_date : $row['pk_created_at'];
}

// 취소일시 포맷팅
$cancel_date = $row['pk_cancel_date'];
if($cancel_date && strlen($cancel_date) == 14 && is_numeric($cancel_date)) {
	$cancel_date_formatted = substr($cancel_date, 0, 4) . '-' . substr($cancel_date, 4, 2) . '-' . substr($cancel_date, 6, 2) . ' ' . substr($cancel_date, 8, 2) . ':' . substr($cancel_date, 10, 2) . ':' . substr($cancel_date, 12, 2);
} else {
	$cancel_date_formatted = $cancel_date;
}

// 가맹점 연락처 포맷팅
$mb['mb_tel'] = isset($mb['mb_tel']) ? trim($mb['mb_tel']) : '';
$mb['mb_hp'] = isset($mb['mb_hp']) ? trim($mb['mb_hp']) : '';

if(strlen($mb['mb_tel']) > 5) {
	$mb_tel = format_tel($mb['mb_tel']);
} else if(strlen($mb['mb_hp']) > 5) {
	$mb_tel = format_tel($mb['mb_hp']);
} else {
	$mb_tel = "-";
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
<meta name="format-detection" content="telephone=no">
<title>신용카드 매출전표 - 수기결제</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script src="/gnu_module/js/jquery-1.12.4.min.js?ver=210618"></script>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
	font-family: 'Pretendard', -apple-system, BlinkMacSystemFont, sans-serif;
	background: #f0f0f0;
	display: flex;
	justify-content: center;
	padding: 15px;
	font-size: 12px;
}
.receipt {
	width: 320px;
	background: #fff;
	border: 1px solid #ddd;
	padding: 20px;
}
.receipt-header {
	text-align: center;
	padding-bottom: 12px;
	border-bottom: 1px dashed #ccc;
	margin-bottom: 12px;
}
.receipt-header h1 {
	font-size: 15px;
	font-weight: 700;
	color: #546e7a;
	margin-bottom: 2px;
}
.receipt-header .en {
	font-size: 9px;
	color: #999;
	letter-spacing: 1px;
}
.status {
	display: inline-block;
	margin-top: 8px;
	padding: 3px 10px;
	border-radius: 3px;
	font-size: 11px;
	font-weight: 600;
}
.status.approved { background: #e8f5e9; color: #2e7d32; }
.status.cancelled { background: #ffebee; color: #c62828; }

.total-box {
	text-align: center;
	padding: 12px 0;
	border-bottom: 1px dashed #ccc;
	margin-bottom: 12px;
}
.total-label { font-size: 10px; color: #888; margin-bottom: 4px; }
.total-amount {
	font-size: 26px;
	font-weight: 800;
	color: #37474f;
}
.total-amount.cancelled { color: #c62828; text-decoration: line-through; }
.total-amount span { font-size: 14px; }

.section {
	margin-bottom: 10px;
	padding-bottom: 10px;
	border-bottom: 1px dotted #e0e0e0;
}
.section:last-of-type { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
.section-title {
	font-size: 11px;
	font-weight: 700;
	color: #546e7a;
	margin-bottom: 6px;
	display: flex;
	align-items: center;
	gap: 5px;
}
.section-title i { font-size: 10px; opacity: 0.7; }
.section-title .en { font-size: 9px; color: #999; font-weight: 400; margin-left: 4px; }

.row {
	display: flex;
	justify-content: space-between;
	padding: 3px 0;
	font-size: 11px;
}
.row .label { color: #666; }
.row .value { color: #333; font-weight: 500; text-align: right; max-width: 180px; word-break: break-all; }
.row .value.highlight { color: #37474f; font-weight: 700; }
.row .value.store { color: #546e7a; font-weight: 600; }
.row .value.cancel { color: #c62828; }

.footer {
	margin-top: 12px;
	padding-top: 10px;
	border-top: 1px dashed #ccc;
	text-align: center;
}
.legal {
	font-size: 9px;
	color: #999;
	line-height: 1.5;
	margin-bottom: 8px;
}
.contact {
	font-size: 10px;
	color: #666;
}
.contact span { margin: 0 6px; }

.btn-area {
	display: flex;
	gap: 8px;
	margin-top: 15px;
}
.btn {
	flex: 1;
	padding: 10px;
	border: none;
	border-radius: 4px;
	font-size: 12px;
	font-weight: 600;
	cursor: pointer;
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 5px;
}
.btn-print { background: #607d8b; color: #fff; }
.btn-print:hover { background: #78909c; }
.btn-close { background: #eee; color: #666; }
.btn-close:hover { background: #ddd; }

@media print {
	body { background: #fff; padding: 0; }
	.receipt { border: none; width: 100%; }
	.btn-area { display: none !important; }
	@page { size: 80mm auto; margin: 5mm; }
}
</style>
</head>
<body>
<div class="receipt">
	<div class="receipt-header">
		<h1>신용카드 매출전표</h1>
		<div class="en">CREDIT CARD SALES SLIP</div>
		<div class="status <?php echo $is_cancelled ? 'cancelled' : 'approved'; ?>">
			<?php echo $is_cancelled ? '취소' : '승인'; ?>
		</div>
	</div>

	<div class="total-box">
		<div class="total-label">결제금액</div>
		<div class="total-amount <?php echo $is_cancelled ? 'cancelled' : ''; ?>">
			<?php echo number_format($pay); ?><span>원</span>
		</div>
	</div>

	<div class="section">
		<div class="section-title"><i class="fas fa-store"></i>가맹정보<span class="en">Store</span></div>
		<div class="row">
			<span class="label">상호/대표</span>
			<span class="value store"><?php echo $row['pk_mb_6_name'] ? $row['pk_mb_6_name'] : '-'; ?> / <?php echo isset($mb['mb_name']) && strlen($mb['mb_name']) > 2 ? $mb['mb_name'] : '-'; ?></span>
		</div>
		<div class="row">
			<span class="label">사업자번호</span>
			<span class="value"><?php echo isset($mb['mb_7']) && strlen($mb['mb_7']) > 2 ? $mb['mb_7'] : '-'; ?></span>
		</div>
		<div class="row">
			<span class="label">연락처</span>
			<span class="value"><?php echo $mb_tel; ?></span>
		</div>
		<div class="row">
			<span class="label">주소</span>
			<span class="value"><?php echo isset($mb['mb_addr1']) ? $mb['mb_addr1'] : ''; ?> <?php echo isset($mb['mb_addr2']) && strlen($mb['mb_addr2']) > 2 ? $mb['mb_addr2'] : ''; ?></span>
		</div>
	</div>

	<div class="section">
		<div class="section-title"><i class="fas fa-credit-card"></i>결제정보<span class="en">Payment</span></div>
		<div class="row">
			<span class="label">주문번호</span>
			<span class="value" style="font-size:10px;"><?php echo $row['pk_order_no'] ? $row['pk_order_no'] : '-'; ?></span>
		</div>
		<div class="row">
			<span class="label">승인번호</span>
			<span class="value highlight"><?php echo $row['pk_app_no'] ? $row['pk_app_no'] : '-'; ?></span>
		</div>
		<div class="row">
			<span class="label">부가세</span>
			<span class="value"><?php echo number_format($vat); ?>원</span>
		</div>
		<div class="row">
			<span class="label">카드</span>
			<span class="value"><?php echo $row['pk_card_issuer'] ? $row['pk_card_issuer'] : '-'; ?> / <?php echo $row['pk_card_no_masked'] ? $row['pk_card_no_masked'] : '-'; ?></span>
		</div>
		<div class="row">
			<span class="label">할부</span>
			<span class="value"><?php echo $installment_text; ?></span>
		</div>
		<div class="row">
			<span class="label">결제일시</span>
			<span class="value"><?php echo $is_cancelled ? $cancel_date_formatted : $app_date_formatted; ?></span>
		</div>
		<?php if($is_cancelled && $app_date_formatted) { ?>
		<div class="row">
			<span class="label">취소일시</span>
			<span class="value cancel"><?php echo $cancel_date_formatted; ?></span>
		</div>
		<?php } ?>
		<div class="row">
			<span class="label">상품명</span>
			<span class="value"><?php echo htmlspecialchars($row['pk_goods_name']); ?></span>
		</div>
		<div class="row">
			<span class="label">구매자</span>
			<span class="value"><?php echo htmlspecialchars($row['pk_buyer_name']); ?></span>
		</div>
		<?php if($row['pk_buyer_phone']) { ?>
		<div class="row">
			<span class="label">연락처</span>
			<span class="value"><?php echo htmlspecialchars($row['pk_buyer_phone']); ?></span>
		</div>
		<?php } ?>
	</div>

	<div class="section">
		<div class="section-title"><i class="fas fa-building"></i>결제대행<span class="en">PG</span></div>
		<div class="row">
			<span class="label">대행사</span>
			<span class="value"><?php echo $pgc_company; ?> / <?php echo $pgc_number; ?></span>
		</div>
		<div class="row">
			<span class="label">대표자</span>
			<span class="value"><?php echo $pgc_name; ?></span>
		</div>
		<div class="row">
			<span class="label">주소</span>
			<span class="value"><?php echo $pgc_addr; ?></span>
		</div>
	</div>

	<div class="footer">
		<div class="legal">
		부가가치세법 제33조, 제36조 및 제46조에 따라<br>
		신용카드매출전표를 발급한 경우에는 세금계산서를 발행(교부)하지 않습니다.
		</div>
		<div class="contact">
			전자금융업 등록번호 02-004-00210<br>
			대표번호 : <?php echo $pgc_tel; ?>
		</div>
	</div>

	<div class="btn-area">
		<button type="button" id="btnPrint" class="btn btn-print"><i class="fas fa-print"></i>인쇄</button>
		<button type="button" id="btnClose" class="btn btn-close"><i class="fas fa-times"></i>닫기</button>
	</div>
</div>

<script>
$(function() {
	// 인쇄 버튼 - 모든 브라우저 지원
	$("#btnPrint").click(function(e) {
		e.preventDefault();
		try {
			window.print();
		} catch(err) {
			alert('인쇄 기능을 사용할 수 없습니다.');
		}
		return false;
	});

	// 닫기 버튼 - 모든 브라우저 지원
	$("#btnClose").click(function(e) {
		e.preventDefault();

		// 방법 1: 팝업 창인 경우 닫기 시도
		if (window.opener) {
			try {
				window.close();
				return false;
			} catch(err) {
				// 닫기 실패시 다음 방법으로
			}
		}

		// 방법 2: 히스토리가 있으면 뒤로가기
		if (window.history.length > 1) {
			window.history.back();
		} else {
			// 방법 3: 메인 페이지로 이동
			window.location.href = './';
		}

		return false;
	});

	// 키보드 단축키 지원
	$(document).keydown(function(e) {
		// Ctrl+P 또는 Cmd+P - 인쇄
		if ((e.ctrlKey || e.metaKey) && e.keyCode == 80) {
			e.preventDefault();
			$("#btnPrint").click();
			return false;
		}
		// ESC - 닫기
		if (e.keyCode == 27) {
			$("#btnClose").click();
			return false;
		}
	});
});
</script>
</body>
</html>
