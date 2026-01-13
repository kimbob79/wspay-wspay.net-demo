<?php
	error_reporting(E_ALL);
	ini_set("display_errors", 0);
	include_once('./_common.php');
	
	$pay_cdatetime = "";
	$row =  sql_fetch(" select * from pay_payment_old where pm_id = '{$pm_id}'");
	$pay_datetime = $row['creates'];
	if($row['pay_cdatetime'] > 0) {
		$pay_cdatetime = $row['creates'];
	}
	if(!$row) die('잘못된 경로입니다');
	$mb =  get_member($row['mb_6']);
	/*
	switch($mb['mb_pid2']) {
		case 'the8al':
			$pgc_comopany = '(주)더8에이엘';
			$pgc_name = '김소라';
			$pgc_number = '839-81-02007';
			$pgc_tel = '1522-7049';
			$pgc_addr = '부산광역시 해운대구 해운대해변로 349-25';
			$pgc_homepage = 'https://the8al.co.kr/';
		break;
		case 'paytus':
			$pgc_comopany = '(주)페이투스';
			$pgc_name = '서동균';
			$pgc_number = '810-81-00347';
			$pgc_tel = '02-465-8400';
			$pgc_addr = '서울 금천구 가산디지털1로 168 (우림라이온스밸리) C동 612-1호';
			$pgc_homepage = 'https://paytus.co.kr/';
		break;
	}
	*/
	$pgc_comopany = '㈜패스고';
	$pgc_name = '황태현';
	$pgc_number = '578-81-01933';
	$pgc_tel = '02-561-6999';
	$pgc_addr = '경기도 수원시 영통구 광교중앙로248번길 7-2 B동 302호';
	$pgc_homepage = 'https://www.passgo.kr';

	$pgc_comopany = '원성페이먼츠';
	$pgc_name = '조용기';
	$pgc_number = '596-88-02642';
	$pgc_tel = '1555-0985';
	$pgc_addr = '서울시 강남구 영동대로411, 3층';
	$pgc_homepage = '';

	$pay = $row['price'];
	if($row['card_yn'] == 'Y') {
		$vat = intval(floor(-$row['amount'] * 10 / 110))*-1;
	} else {
		$vat = intval(floor($row['amount'] * 10 / 110));
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

	if($row['card_yn'] == "C") { // 취소일경우
		$pay_datetime = $row['card_sdatetime'];
		$pay_cdatetime = $row['card_cdatetime'];
	}

?>

<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="minimum-scale=1.0, width=device-width, maximum-scale=1, user-scalable=no"/>
<meta name="format-detection" content="telephone=no">
<title>신용카드 카드 영수증</title>
<link rel="stylesheet" href="./css/receipt.css?ver=2106181">
<script src="<?php echo G5_JS_URL; ?>/jquery-1.12.4.min.js?ver=210618"></script>
<style>


@media print {
  .button_area {display:none !important;}
  @page { size: b5; margin: 0; }
  body { margin: 0.5cm; }
}

</style>

</head>

<body>

<div style="margin:10px auto; width:420px;padding:10px; border:1px solid #ddd">
	<div id="header" class="type_end">
		<div class="gnb">
			<h1 class="pageh1">카드 영수증</h1>
		</div>
	</div>
	<div id="u_skip">
		<a href="#container">본문 바로가기</a>
	</div>
	<div id="wrap">
		<div id="container">
			<div id="content">
				<div class="receipt_section">
					<?php if($pay_cdatetime) { ?>
					<div class="notice">
						<p class="message">
							<span class="icon_notice"></span>취소된 결제건입니다.
						</p>
					</div>
					<?php } ?>
					<strong class="detail_title">결제정보</strong>
					<ul class="detail_list">
						<li><strong class="item_title">결제금액</strong><span class="point_color_green"><?php echo number_format($pay); ?></span></li>
						<li><strong class="item_title">승인번호</strong><span class="point_color_green"><?php echo $row['card_num']; ?></span></li>
						<li><strong class="item_title">카드종류</strong><?php echo $row['card_name']; ?></li>
						<?php /*
						<li><strong class="item_title">카드BIN</strong><?php echo $row['bin']; ?></li>
						*/ ?>
						<li><strong class="item_title">할부</strong><?php if($row['pay_installment'] < 1) { echo "일시불"; } else { echo $row['pay_installment']."개월"; } ?></li>
						<li><strong class="item_title">결제일자</strong><?php echo $pay_datetime; ?></li>
						<?php if($pay_cdatetime) { ?>
						<li><strong class="item_title">취소일자</strong><?php echo $pay_cdatetime; ?></li>
						<?php } ?>
						<li><strong class="item_title">주문번호</strong><?php echo $row['card_trxid']; ?></li>
					</ul>
					<?php /*
					<strong class="detail_title">판매자 정보</strong>
					<ul class="detail_list">
						<li><strong class="item_title">판매자상호</strong><?php if($mb['mb_nick']) { echo $mb['mb_nick']; } else { echo "-"; } ?></li>
						<?php if($mb['mb_name']) { ?>
						<li><strong class="item_title">대표자명</strong><?php if(strlen($mb['mb_name']) > 2) { echo $mb['mb_name']; } else { echo "-"; } ?></li>
						<?php } ?>
						<?php if($mb['mb_7'] > 2) { ?>
						<li><strong class="item_title">사업자등록번호</strong><?php if(strlen($mb['mb_7']) > 2) { echo $mb['mb_7']; } else { echo "-"; } ?></li>
						<?php } ?>
						<?php if($$mb_tel > 5) { ?>
						<li><strong class="item_title">전화번호</strong><?php echo $mb_tel; ?></li>
						<?php } ?>
						<?php if($mb['mb_addr1']) { ?>
						<li><strong class="item_title">사업장주소</strong><?php echo $mb['mb_addr1']; ?><?php if(strlen($mb['mb_addr2']) > 2) { echo $mb['mb_addr2']; } else { echo "-"; } ?></li>
						<?php } ?>
					</ul>
					*/ ?>
					<strong class="detail_title">가맹점 정보</strong>
					<ul class="detail_list">
						<li><strong class="item_title">가맹점명</strong><?php echo $pgc_comopany; ?></li>
						<li><strong class="item_title">대표자명</strong><?php echo $pgc_name; ?></li>
						<li><strong class="item_title">사업자등록번호</strong><?php echo $pgc_number; ?></li>
						<li><strong class="item_title">주소</strong><?php echo $pgc_addr; ?></li>
					</ul>
					<strong class="detail_title">금액</strong>
					<ul class="detail_list">
						<li><strong class="item_title">승인금액</strong><?php echo number_format($pay); ?></li>
						<li><strong class="item_title">공급가액</strong><?php echo number_format($payMinusVat); ?></li>
						<li><strong class="item_title">부가세액</strong><?php echo number_format($vat)?></li>
					</ul>
					<div class="total_box">
						<strong class="total">합계</strong>
						<span class="point_color_green price"><?php echo number_format($pay); ?></span>
					</div>
					<div class="button_area">
						<div class="cell">
							<button type="button" id="btnPrint" class="npay_common_button type_green size_large"><span>인쇄하기</span></button>
						</div>
						<div class="cell">
							<button type="button"id="btnClose" class="npay_common_button type_light_green size_large"><span>닫기</span></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


<script type="text/javascript">
	$(document).ready(function() {
		// 출력 버튼 클릭
		$("#btnPrint").click(function() {
			$(".print").hide();
			window.print();
		});
		
		// 닫기 버튼 클릭
		$("#btnClose").click(function() {
			self.close();
		});

		function commaNum(amt) {
			return amt.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		}
	});
</script>


</body>
</html>