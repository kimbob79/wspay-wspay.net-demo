<?php
	include_once('./_common.php');
	$row =  sql_fetch(" select * from g5_payment where trxid = '{$id}' and pay_num = '$num'");
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
?>

<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="euc-kr">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name='description' content='KCP'/>
<meta name='keywords' content='KCP, 한국사이버결제, 전자결제, 간편결제, 페이코, VAN, PG'/>
<meta name="autocomplete" content="off"./>
<meta name="ROBOTS" content="NOINDEX"./>
<meta name="naver-site-verification" content="f17caf27ee17adcc9000608a5397c482f62ebea4" /> <meta http-equiv="Expires" content="-1">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Cache-Control" content="No-Cache">
<meta name="viewport" id="meta_viewport" content="width=device-width,initial-scale=1.0,minimum-scale=0,maximum-scale=10">
<title>영수증</title>
<script src="<?php echo G5_JS_URL; ?>/jquery-1.12.4.min.js?ver=210618"></script>
<style>
* {font-family:'nmalgun gothic';line-height:120%;box-sizing:border-box;}
html,body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,form,fieldset,p,button{margin:0;padding:0;border:0;}
table { width:100%; font-size:12px; }
table td:nth-child(2n) { font-weight:bolder;}
.button_print {background-color: #3298dc; border-color: transparent; color: #fff; border-width: 1px; cursor: pointer; padding: 0.5em 1em; text-align: center; white-space: nowrap;}
.button_close {background-color: white; border-color: #dbdbdb; color: #363636; border-width: 1px; cursor: pointer; padding: 0.5em 1em; text-align: center; white-space: nowrap;}
._cancel {position:absolute;top:300px;margin:0 auto;width:182px;}
#popup {position:relative;}
.cancel {position:absolute;width:100%;height:90%;left:0;top:0;;background:url(./img/ico_cancel.png) no-repeat center;}
</style>
</head>
<body>
<?php
	$pay = $row['pay'];
	if($row['pay_type'] == 'N') {
		$vat = intval(floor(-$row['pay'] * 10 / 110))*-1;
	} else {
		$vat = intval(floor($row['pay'] * 10 / 110));
	}
	$payMinusVat = $pay - $vat;
?>

<div id="popup" style="width:340px; border:1px solid #999; padding:10px 10px 10px 10px; margin:10px auto;">
	<table style="width:100%; border-bottom: 1px solid #555; padding-bottom:10px; margin-bottom:10px;">
		<tr>
			<td>구매자용</td>
		</tr>
		<tr>
			<td colspan="2" style="text-align:center; font-size:20px; font-weight:bold;">신용카드 매출전표</td>
		</tr>
	</table>


	<table style="width:100%; width:100%; border-bottom: 1px dashed #999; padding-bottom:7px; margin-bottom:7px;">
		<tr>
			<td style="width:120px;letter-spacing: 1px;">판매자상호</td>
			<td style="text-align:right"><?php echo $mb['mb_nick']; ?></td>
		</tr>
		<tr>
			<td style="letter-spacing: 1px;">사업자등록번호</td>
			<td style="text-align:right"><?php echo $mb['mb_7']; ?></td>
		</tr>
		<tr>
			<td>사업장주소</td>
			<td style="text-align:right"><?php echo $mb['mb_addr1']; ?> <?php echo $mb['mb_addr2']; ?></td>
		</tr>
		<tr>
			<td style="letter-spacing: 1px;">[대 표 자]</td>
			<td style="text-align:right"><?php echo $mb['mb_name']; ?></td>
		</tr>
		<tr>
			<td style="letter-spacing: 1px;">[연 락 처]</td>
			<td style="text-align:right"><?php if($mb['mb_tel']) { echo format_tel($mb['mb_tel']); } else { echo format_tel($mb['mb_hp']); } ?></td>
		</tr>
	</table>


	<table style="width:100%; width:100%; border-bottom: 1px dashed #999; padding-bottom:7px; margin-bottom:7px;">
		<tr>
			<td colspan="2" style="font-weight:bold">결제정보</td>
		</tr>
		<tr>
			<td>[공급가액]</td>
			<td align="right"><?php echo number_format($payMinusVat); ?> 원</td>
		</tr>
		<tr>
			<td style="letter-spacing: 1px;">[부 가 세]</td>
			<td align="right"><?php echo number_format($vat)?> 원</td>
		</tr>
		<tr>
			<td style="letter-spacing: 1px;">[봉 사 료]</td>
			<td align="right">0 원</td>
		</tr>
		<tr>
			<td style="letter-spacing: 1px;">[총 금 액]</td>
			<td align="right"><span style="font-size:18px;color:#db0010;font-weight:bolder;font-family:Arial,Helvetica,sans-serif;"><?php echo number_format($pay); ?></span> 원</td>
		</tr>
	</table>


	<table style="width:100%; width:100%; border-bottom: 1px dashed #999; padding-bottom:7px; margin-bottom:7px;">
		<tr>
			<td colspan="2" style="font-weight:bold">승인정보</td>
		</tr>
		<tr>
			<td style="width:70px">[카드종류]</td>
			<td><?php echo $row['pay_card_name']; ?></td>
		</tr>
		<tr>
			<td>[할부개월]</td>
			<td><?php if($row['pay_parti'] < 1) { echo "일시불"; } else { echo $row['pay_parti']."개월"; } ?></td>
		</tr>
		<tr>
			<td>[카드번호]</td>
			<td><?php echo $row['pay_card_num']; ?></td>
		</tr>
		<tr>
			<td>[승인금액]</td>
			<td><?php echo number_format($pay); ?>원</td>
		</tr>
		<tr>
			<td>[승인번호]</td>
			<td><?php echo $row['pay_num']; ?></td>
		</tr>
		<tr>
			<td>[승인일시]</td>
			<td><?=$row['pay_type']=='Y'?$row['pay_datetime']:$row['pay_cdatetime']; ?></td>
		</tr>
<? if($row['pay_cdatetime']>0) { ?>
		<tr>
			<td>[취소일시]</td>
			<td><?=$row['pay_type']=='N'?$row['pay_datetime']:'';?></td>
		</tr>
<? } ?>
	</table>

	<table style="width:100%;">
		<tr>
			<td colspan="2" style="font-weight:bold">서비스사</td>
		</tr>
		<tr>
			<td style="width:70px;letter-spacing: 1px;">[회 사 명]</td>
			<td><?=$pgc_comopany;?></td>
		</tr>
		<tr>
			<td style="letter-spacing: 1px;">[대 표 자]</td>
			<td><?=$pgc_name;?></td>
		</tr>
		<tr>
			<td style="letter-spacing: 1px;">[사 업 자]</td>
			<td><?=$pgc_number;?></td>
		</tr>
		<tr>
			<td>[주　　소]</td>
			<td><?=$pgc_addr;?></td>
		</tr>
		<tr>
			<td style="letter-spacing: 1px;">[연 락 처]</td>
			<td><?=$pgc_tel;?></td>
		</tr>
		<tr>
			<td>[홈페이지]</td>
			<td><?=$pgc_homepage;?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
	</table>
	<div class="print" style="width:100%;text-align:center; border-top: 1px dashed #999; padding:20px 0;">
		<input type="button" value="출력" id="btnPrint" class="button_print"  style="cursor:hand">
		<input type="button" value="닫기" id="btnClose" class="button_close" style="cursor:hand">
	</div>
<? if($row['pay_cdatetime'] > 0) { ?>
	<div class="cancel"></div>
<? } ?>
</div>
<script type="text/javascript">
$(document).ready(function() {
	resizeTo($('#popup').width()+60,$('#popup').height()+126);
//	alert(window.innerHeight);
//	alert(window.outerHeight);
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
