<?php
	include_once('./_common.php');
	$row =  sql_fetch(" select * from g5_payment where pay_id = '{$pay_id}'");
	if(!$row) die('잘못된 경로입니다');
	$mb =  get_member($row['mb_6']);

	$pgc_comopany = '원성페이먼츠';
	$pgc_name = '조용기';
	$pgc_number = '596-88-02642';
	$pgc_tel = '1555-0985';
	$pgc_addr = '서울시 강남구 영동대로411, 3층';

	$pay = $row['pay'];
	if($row['pay_type'] == 'N') {
		$vat = intval(floor(-$row['pay'] * 10 / 110))*-1;
	} else {
		$vat = intval(floor($row['pay'] * 10 / 110));
	}
	$payMinusVat = $pay - $vat;

	$mb['mb_tel'] = trim($mb['mb_tel']);
	$mb['mb_hp'] = trim($mb['mb_hp']);

	if(strlen($mb['mb_tel']) > 5) {
		$mb_tel = format_tel($mb['mb_tel']);
	} else if(strlen($mb['mb_hp']) > 5) {
		$mb_tel = format_tel($mb['mb_hp']);
	} else {
		$mb_tel = "-";
	}

	$is_cancelled = ($row['pay_type'] == 'N');
?>

<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
<meta name="format-detection" content="telephone=no">
<title>신용카드 매출전표</title>
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
	color: #1a237e;
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
	color: #1a237e;
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
	color: #1a237e;
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
.row .value.highlight { color: #1a237e; font-weight: 700; }
.row .value.store { color: #e65100; font-weight: 600; }
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
.btn-print { background: #1a237e; color: #fff; }
.btn-print:hover { background: #283593; }
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
			<span class="value store"><?php echo $mb['mb_nick'] ? $mb['mb_nick'] : '-'; ?> / <?php echo strlen($mb['mb_name']) > 2 ? $mb['mb_name'] : '-'; ?></span>
		</div>
		<div class="row">
			<span class="label">사업자번호</span>
			<span class="value"><?php echo strlen($mb['mb_7']) > 2 ? $mb['mb_7'] : '-'; ?></span>
		</div>
		<div class="row">
			<span class="label">연락처</span>
			<span class="value"><?php echo $mb_tel; ?></span>
		</div>
		<div class="row">
			<span class="label">주소</span>
			<span class="value"><?php echo $mb['mb_addr1']; ?> <?php echo strlen($mb['mb_addr2']) > 2 ? $mb['mb_addr2'] : ''; ?></span>
		</div>
	</div>

	<div class="section">
		<div class="section-title"><i class="fas fa-credit-card"></i>결제정보<span class="en">Payment</span></div>
		<div class="row">
			<span class="label">승인번호</span>
			<span class="value highlight"><?php echo $row['pay_num']; ?></span>
		</div>
		<div class="row">
			<span class="label">부가세</span>
			<span class="value"><?php echo number_format($vat); ?>원</span>
		</div>
		<div class="row">
			<span class="label">카드</span>
			<span class="value"><?php echo $row['pay_card_name']; ?> / <?php echo $row['pay_card_num']; ?></span>
		</div>
		<div class="row">
			<span class="label">할부</span>
			<span class="value"><?php echo $row['pay_parti'] < 1 ? '일시불' : $row['pay_parti'].'개월'; ?></span>
		</div>
		<div class="row">
			<span class="label">결제일시</span>
			<span class="value"><?php echo $row['pay_type']=='Y' ? $row['pay_datetime'] : $row['pay_cdatetime']; ?></span>
		</div>
		<?php if($is_cancelled && $row['pay_datetime']) { ?>
		<div class="row">
			<span class="label">취소일시</span>
			<span class="value cancel"><?php echo $row['pay_datetime']; ?></span>
		</div>
		<?php } ?>
		<?php if($row['pg_name'] == "stn") {
			$row2 = sql_fetch(" select * from g5_payment_stn where refNo = '{$row['trxid']}'");
		?>
		<div class="row">
			<span class="label">상품명</span>
			<span class="value"><?php echo $row2['goodsName']; ?></span>
		</div>
		<div class="row">
			<span class="label">구매자</span>
			<span class="value"><?php echo $row2['customerName']; ?></span>
		</div>
		<?php } ?>
	</div>

	<div class="section">
		<div class="section-title"><i class="fas fa-building"></i>결제대행<span class="en">PG</span></div>
		<div class="row">
			<span class="label">대행사</span>
			<span class="value"><?php echo $pgc_comopany; ?> / <?php echo $pgc_number; ?></span>
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
		신용카드매출천표를 발급한 경우에는 세금계산서를 발행(교부)하지 않습니다.
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
