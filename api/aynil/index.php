<?
// https://api.withpay.co.kr/aynil/?pid=motorpay
extract($_REQUEST);
$noError = false;

$errorSendTel = '01090242565';
//$errorSendTel = '01099895231';
$errorSendTitle = '에이닐정산에러';


// 데이터 임시보관 루틴
$indataFolder = './data/';
$t = microtime(true);
$micro = sprintf("%06d",($t - floor($t)) * 1000000);
$d = new DateTime( date('Y-m-d H:i:s.'.$micro, $t) );
$inTime = $d->format("YmdHisu");
$post = '';
foreach($_POST as $key => $value) {
	if($value) {
		if($post) $post .= '&';
		$post .= $key.'='.$value;
	}
}
$myFile = $indataFolder.$inTime.'.data';
$bfh = fopen($myFile, 'a');
fwrite($bfh, $post);
fclose($bfh);
// 데이터 임시보관 루틴 끝

include('../common/common.php');

/*
$pid = isset($_REQUEST['pid']) ? trim($_REQUEST['pid']) : '';
$mchtId = isset($_REQUEST['mchtId']) ? trim($_REQUEST['mchtId']) : '';
$trxId = isset($_REQUEST['trxId']) ? trim($_REQUEST['trxId']) : '';
$van = isset($_REQUEST['van']) ? trim($_REQUEST['van']) : '';
$tmnId = isset($_REQUEST['tmnId']) ? trim($_REQUEST['tmnId']) : '';
$trxDate = isset($_REQUEST['trxDate']) ? trim($_REQUEST['trxDate']) : '';
$trxType = isset($_REQUEST['trxType']) ? trim($_REQUEST['trxType']) : '';
$trackId = isset($_REQUEST['trackId']) ? trim($_REQUEST['trackId']) : '';
$vanTrxId = isset($_REQUEST['vanTrxId']) ? trim($_REQUEST['vanTrxId']) : '';
$authCd = isset($_REQUEST['authCd']) ? trim($_REQUEST['authCd']) : '';
$issuer = isset($_REQUEST['issuer']) ? trim($_REQUEST['issuer']) : '';
$acquirer = isset($_REQUEST['acquirer']) ? trim($_REQUEST['acquirer']) : '';
$cardType = isset($_REQUEST['cardType']) ? trim($_REQUEST['cardType']) : '';
$bin = isset($_REQUEST['bin']) ? trim($_REQUEST['bin']) : '';
$last4 = isset($_REQUEST['last4']) ? trim($_REQUEST['last4']) : '';
$installment = isset($_REQUEST['installment']) ? trim($_REQUEST['installment']) : '';
$amount = isset($_REQUEST['amount']) ? trim($_REQUEST['amount']) : '';
$rootTrxId = isset($_REQUEST['rootTrxId']) ? trim($_REQUEST['rootTrxId']) : '';
$payerEmail = isset($_REQUEST['payerEmail']) ? trim($_REQUEST['payerEmail']) : '';
$payerTel = isset($_REQUEST['payerTel']) ? trim($_REQUEST['payerTel']) : '';
$payerName = isset($_REQUEST['payerName']) ? trim($_REQUEST['payerName']) : '';
$productname = isset($_REQUEST['name']) ? trim($_REQUEST['name']) : '';
*/

$pay_type = "Y";
$pay_cdatetime = "";

// 취소
if($trxType == "REFUND") {
	$pay_type = "N";

	// 원거래
	$cancel = sql_fetch("select * from g5_pg_anyil where trxId = '{$rootTrxId}' ", $DBC);
	
	// 취소일때 데이터 원거래에서 가져오기
	$pay_cdatetime = $datetime;
	$issuer = $cancel['issuer'];
	$acquirer = $cancel['acquirer'];
	$cardType = $cancel['cardType'];
	$bin = $cancel['bin'];
	$last4 = $cancel['last4'];
}


$sql_common = " pid='{$pid}',
				mchtId='{$mchtId}',
				trxId='{$trxId}',
				van='{$van}',
				tmnId='{$tmnId}',
				trxDate='{$trxDate}',
				trxType='{$trxType}',
				trackId='{$trackId}',
				vanTrxId='{$vanTrxId}',
				authCd='{$authCd}',
				issuer='{$issuer}',
				acquirer='{$acquirer}',
				cardType='{$cardType}',
				bin='{$bin}',
				last4='{$last4}',
				installment='{$installment}',
				amount='{$amount}',
				rootTrxId='{$rootTrxId}',
				payerEmail='{$payerEmail}',
				payerTel='{$payerTel}',
				payerName='{$payerName}',
				productname='{$productname}', ";
// 실제정보 저장
$sql = "insert into g5_pg_anyil set ".$sql_common." datetime = '".$datetime."'";
sql_query($sql, $DBC);
if(!sql_affected_rows($DBC)) {
	sendfcm('4', $errorSendTitle, '마스터 테이블 저장 실패', $errorSendTel);
}
//echo $sql."<br><br>";

// 취소일경우 TAX 원거래
if($pay_type == 'N') {
	$ori = sql_fetch("select * from g5_tax where pay_num = '{$authCd}' and trxid = '{$rootTrxId}' and pay_type = 'Y'", $DBC);
	if(!$ori['pay_id']) {
//		sendfcm('4', $errorSendTitle, '승인정보가 없습니다. - '.$authCd, $errorSendTel);
		echo 'FAIL';
		exit;
	}
	$checkCancelData = sql_fetch("select * from g5_tax where pay_num = '{$authCd}' and trxid = '{$trxId}' and pay_type = 'N'", $DBC);
	if($checkCancelData['pay_id']) {
		sendfcm('4', $errorSendTitle, '취소데이터가 존제합니다. - '.$authCd, $errorSendTel);
		echo 'FAIL';
		exit;
	}
} else {
	$checkPayData = sql_fetch("select * from g5_tax where pay_num = '{$authCd}' and trxid = '{$trxId}' and pay_type = 'Y'", $DBC);
	if($checkPayData['pay_id']) {
		sendfcm('4', $errorSendTitle, '승인데이터가 존제합니다. - '.$authCd, $errorSendTel);
		echo 'FAIL';
		exit;
	}
}

/*
$A = ['a','b','c'];
$$A[0] = 'K';
ECHO $A;
$AAA['BBB']
*/


$pay_datetime =  date("Y-m-d H:i:s", strtotime($trxDate)); // 거래일시 데이터 변환
/*
$calday =  date("Y-m-d", strtotime($trxDate)); // 정산일
$calday = date("Y-m-d",strtotime("-1 day", $calday));
*/
$calday = date("Ymd", strtotime("+1 day", strtotime($trxDate))); // 정산일 무조건 1일뒤

if($pay_type == 'Y') { // 승인일경우
	$row2 = sql_fetch("select * from g5_device where dv_tid = '{$tmnId}'", $DBC);

	if($row2['mb_fee6'] > 0.01) {
		$row2['mb_fee6'] = $row2['mb_fee7'] - $row2['mb_fee6'];
	} else {
		$row2['mb_fee6'] = 0.00;
	}

	if($row2['mb_fee5'] > 0.01) {
		$row2['mb_fee5'] = $row2['mb_fee7'] - $row2['mb_fee6'] - $row2['mb_fee5'];
	} else {
		$row2['mb_fee5'] = 0.00;
	}

	if($row2['mb_fee4'] > 0.01) {
		$row2['mb_fee4'] = $row2['mb_fee7'] - $row2['mb_fee6'] - $row2['mb_fee5'] - $row2['mb_fee4'];
	} else {
		$row2['mb_fee4'] = 0.00;
	}

	if($row2['mb_fee3'] > 0.01) {
		$row2['mb_fee3'] = $row2['mb_fee7'] - $row2['mb_fee6'] - $row2['mb_fee5'] - $row2['mb_fee4'] - $row2['mb_fee3'];
	} else {
		$row2['mb_fee3'] = 0.00;
	}

	$row2['mb_fee2'] = $row2['mb_fee7'] - $row2['mb_fee6'] - $row2['mb_fee5'] - $row2['mb_fee4'] - $row2['mb_fee3'] - $row2['mb_fee2'];


	$mb_money2 = $amount * $row2['mb_fee2'] /100;
	$mb_money3 = $amount * $row2['mb_fee3'] /100;
	$mb_money4 = $amount * $row2['mb_fee4'] /100;
	$mb_money5 = $amount * $row2['mb_fee5'] /100;
	$mb_money6 = $amount * $row2['mb_fee6'] /100;
	$mb_money7 = $amount * $row2['mb_fee7'] /100;
	$mb_money7 = $amount -$mb_money7;


	$mb_fee2 = number_format($row2['mb_fee2'], 2);
	$mb_fee3 = number_format($row2['mb_fee3'], 2);
	$mb_fee4 = number_format($row2['mb_fee4'], 2);
	$mb_fee5 = number_format($row2['mb_fee5'], 2);
	$mb_fee6 = number_format($row2['mb_fee6'], 2);
	$mb_fee7 = number_format($row2['mb_fee7'], 2);


} else { // 취소일경우

	// 취소일경우 원거래에 취소승인일 업데이트
//	$sql = " insert into g5_tax set pay_cdatetime = '{$ori['pay_cdatetime']}' ";
//	sql_query($sql, $DBC);

//	$pay_type = $ori['pay_type'];
	$amount = -$ori['pay'];
	$authCd = $ori['pay_num'];
	$trxid = $ori['trxId'];

	$pay_cdatetime = $ori['pay_datetime'];

	$installment = $ori['pay_parti'];
	$issuer = $ori['pay_card_name'];
	$last4 = $ori['pay_card_num'];
	$calday = $ori['cday'];
			
	$row2['mb_name2'] = $ori['mb_name2'];
	$row2['mb_pid2'] = $ori['mb_pid2'];
	$mb_fee2 = $ori['mb_fee2'];
	$mb_money2 = -$ori['mb_pay2'];
	$row['level1_money_diff'] = -$ori['mb_pay_diff'];
			
			
	$row2['mb_name3'] = $ori['mb_name3'];
	$row2['mb_pid3'] = $ori['mb_pid3'];
	$mb_fee3 = $ori['mb_fee3'];
	$mb_money3 = -$ori['mb_pay3'];
			
			
	$row2['mb_name4'] = $ori['mb_name4'];
	$row2['mb_pid4'] = $ori['mb_pid4'];
	$mb_fee4 = $ori['mb_fee4'];
	$mb_money4 = -$ori['mb_pay4'];
			
			
	$row2['mb_name5'] = $ori['mb_name5'];
	$row2['mb_pid5'] = $ori['mb_pid5'];
	$mb_fee5 = $ori['mb_fee5'];
	$mb_money5 = -$ori['mb_pay5'];
			
			
	$row2['mb_name6'] = $ori['mb_name6'];
	$row2['mb_pid6'] = $ori['mb_pid6'];
	$mb_fee6 = $ori['mb_fee6'];
	$mb_money6 = -$ori['mb_pay6'];
			
			
	$row2['mb_name7'] = $ori['mb_name7'];
	$row2['mb_pid7'] = $ori['mb_pid7'];
	$mb_fee7 = $ori['mb_fee7'];
	$mb_money7 = -$ori['mb_pay7'];
			
	$row2['content'] = $ori['dv_type'];
	$tmnId	=	$ori['dv_tid'];
}

$sql_common = " pay_type = '{$pay_type}',
				pay = '{$amount}',
				pay_num = '{$authCd}',
				trxid = '{$trxId}',
				pay_datetime = '{$pay_datetime}',
				pay_cdatetime = '{$pay_cdatetime}',
				pay_parti = '{$installment}',
				pay_card_name = '{$issuer}',
				pay_card_num = '{$last4}',
				cday = '{$calday}',

				mb_name2 = '{$row2['mb_name2']}',
				mb_pid2 = '{$row2['mb_pid2']}',
				mb_fee2 = '{$mb_fee2}',
				mb_pay2 = '{$mb_money2}',
				mb_pay_diff = '{$row['level1_money_diff']}',


				mb_name3 = '{$row2['mb_name3']}',
				mb_pid3 = '{$row2['mb_pid3']}',
				mb_fee3 = '{$mb_fee3}',
				mb_pay3 = '{$mb_money3}',


				mb_name4 = '{$row2['mb_name4']}',
				mb_pid4 = '{$row2['mb_pid4']}',
				mb_fee4 = '{$mb_fee4}',
				mb_pay4 = '{$mb_money4}',


				mb_name5 = '{$row2['mb_name5']}',
				mb_pid5 = '{$row2['mb_pid5']}',
				mb_fee5 = '{$mb_fee5}',
				mb_pay5 = '{$mb_money5}',


				mb_name6 = '{$row2['mb_name6']}',
				mb_pid6 = '{$row2['mb_pid6']}',
				mb_fee6 = '{$mb_fee6}',
				mb_pay6 = '{$mb_money6}',


				mb_name7 = '{$row2['mb_name7']}',
				mb_pid7 = '{$row2['mb_pid7']}',
				mb_fee7 = '{$mb_fee7}',
				mb_pay7 = '{$mb_money7}',

				dv_type = '{$row2['content']}',
				dv_tid = '{$tmnId}',
				pg_name = 'aynil' ";
// tax 저장
$sql = " insert into g5_tax set {$sql_common} ";
sql_query($sql, $DBC);
$attachCount = sql_affected_rows($DBC);
if($attachCount==0) {
	if($pay_type == 'Y') {
		sendfcm('4', $errorSendTitle, '승인자료 추가에 실패했습니다. - '.$authCd, $errorSendTel);
		echo 'FAIL';
		exit;
	} else {
		sendfcm('4', $errorSendTitle, '취소자료 추가에 실패했습니다. - '.$authCd, $errorSendTel);
		echo 'FAIL';
		exit;
	}
	exit;
}
if($pay_type == 'N') {
	sql_query("update g5_tax set pay_cdatetime = '{$pay_datetime}' where pay_id = '{$ori['pay_id']}'", $DBC);
	$attachCount = sql_affected_rows($DBC);
	if($attachCount==0) {
		sendfcm('4', $errorSendTitle, '취소자료 업데이트에 실패했습니다. - '.$authCd, $errorSendTel);
		echo 'FAIL';
		exit;
	}
}
if($noError == false) {
	echo 'OK';
} else {
	sendfcm('4', $errorSendTitle, '문제가 있습니다', $errorSendTel);
}
?>