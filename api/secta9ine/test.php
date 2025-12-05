<?php
exit;

error_reporting( E_ALL );
ini_set( "display_errors", 1 );
	
include('../lib/functions.php');

// 테스트용 결제 데이터
$pay_type = 'card';
$amount = 50000;
$applNo = 'APPL12345678';
$refNo = 'TRX987654321';
$mbrRefNo = 'TRACK123456789';
$pay_datetime = '2025-05-14 15:30:45';
$pay_cdatetime = '2025-05-14 15:31:00';
$installNo = '00';
$issueCompanyNo = 'KB카드';
$cardNo = '1234-56**-****-7890';

// 테스트용 회원 데이터(row2 배열 데이터)
$row2 = [
    'mb_1' => 'partner1',
    'mb_1_name' => '파트너1',
    'mb_2' => 'partner2',
    'mb_2_name' => '파트너2',
    'mb_3' => 'partner3',
    'mb_3_name' => '파트너3',
    'mb_4' => 'partner4',
    'mb_4_name' => '파트너4',
    'mb_5' => 'partner5',
    'mb_5_name' => '파트너5',
    'mb_6' => 'partner6',
    'mb_6_name' => '파트너6',
    'dv_type' => 'normal',
    'dv_certi' => 'Y',
    'sftp_mbrno' => 'SFTP12345'
];

// 파트너 수수료 및 정산금액
$mb_1_fee = 3.5;
$mb_1_pay = 1750;
$mb_2_fee = 2.0;
$mb_2_pay = 1000;
$mb_3_fee = 1.5;
$mb_3_pay = 750;
$mb_4_fee = 1.0;
$mb_4_pay = 500;
$mb_5_fee = 0.8;
$mb_5_pay = 400;
$mb_6_fee = 0.5;
$mb_6_pay = 250;

// 기타 데이터
$mbrNo = 'MBRID12345';
$dv_tid_ori = 'ORIGINAL12345';
define('G5_TIME_YMDHIS', date('Y-m-d H:i:s'));

// 최종 결제 데이터 배열
$payment_data = [
    'pay_type' => $pay_type,
    'pay' => $amount,
    'pay_num' => $applNo,
    'trxid' => $refNo,
    'trackId' => $mbrRefNo,
    'pay_datetime' => $pay_datetime,
    'pay_cdatetime' => $pay_cdatetime,
    'pay_parti' => $installNo,
    'pay_card_name' => $issueCompanyNo,
    'pay_card_num' => $cardNo,
    'mb_1' => $row2['mb_1'],
    'mb_1_name' => $row2['mb_1_name'],
    'mb_1_fee' => $mb_1_fee,
    'mb_1_pay' => $mb_1_pay,
    'mb_2' => $row2['mb_2'],
    'mb_2_name' => $row2['mb_2_name'],
    'mb_2_fee' => $mb_2_fee,
    'mb_2_pay' => $mb_2_pay,
    'mb_3' => $row2['mb_3'],
    'mb_3_name' => $row2['mb_3_name'],
    'mb_3_fee' => $mb_3_fee,
    'mb_3_pay' => $mb_3_pay,
    'mb_4' => $row2['mb_4'],
    'mb_4_name' => $row2['mb_4_name'],
    'mb_4_fee' => $mb_4_fee,
    'mb_4_pay' => $mb_4_pay,
    'mb_5' => $row2['mb_5'],
    'mb_5_name' => $row2['mb_5_name'],
    'mb_5_fee' => $mb_5_fee,
    'mb_5_pay' => $mb_5_pay,
    'mb_6' => $row2['mb_6'],
    'mb_6_name' => $row2['mb_6_name'],
    'mb_6_fee' => $mb_6_fee,
    'mb_6_pay' => $mb_6_pay,
    'dv_type' => $row2['dv_type'],
    'dv_certi' => $row2['dv_certi'],
    'dv_tid' => $mbrNo,
    'dv_tid_ori' => $dv_tid_ori,
    'sftp_mbrno' => $row2['sftp_mbrno'],
    'pg_name' => 'stn',
    'datetime' => G5_TIME_YMDHIS
];


/*
$payment_data = [
    'pay_type' => $pay_type,
    'pay' => $amount,
    'pay_num' => $applNo,
    'trxid' => $refNo,
    'trackId' => $mbrRefNo,
    'pay_datetime' => $pay_datetime,
    'pay_cdatetime' => $pay_cdatetime,
    'pay_parti' => $installNo,
    'pay_card_name' => $issueCompanyNo,
    'pay_card_num' => $cardNo,
    'mb_1' => $row2['mb_1'],
    'mb_1_name' => $row2['mb_1_name'],
    'mb_1_fee' => $mb_1_fee,
    'mb_1_pay' => $mb_1_pay,
    'mb_2' => $row2['mb_2'],
    'mb_2_name' => $row2['mb_2_name'],
    'mb_2_fee' => $mb_2_fee,
    'mb_2_pay' => $mb_2_pay,
    'mb_3' => $row2['mb_3'],
    'mb_3_name' => $row2['mb_3_name'],
    'mb_3_fee' => $mb_3_fee,
    'mb_3_pay' => $mb_3_pay,
    'mb_4' => $row2['mb_4'],
    'mb_4_name' => $row2['mb_4_name'],
    'mb_4_fee' => $mb_4_fee,
    'mb_4_pay' => $mb_4_pay,
    'mb_5' => $row2['mb_5'],
    'mb_5_name' => $row2['mb_5_name'],
    'mb_5_fee' => $mb_5_fee,
    'mb_5_pay' => $mb_5_pay,
    'mb_6' => $row2['mb_6'],
    'mb_6_name' => $row2['mb_6_name'],
    'mb_6_fee' => $mb_6_fee,
    'mb_6_pay' => $mb_6_pay,
    'dv_type' => $row2['dv_type'],
    'dv_certi' => $row2['dv_certi'],
    'dv_tid' => $mbrNo,
    'dv_tid_ori' => $dv_tid_ori,
    'sftp_mbrno' => $row2['sftp_mbrno'],
    'pg_name' => 'stn',
    'datetime' => G5_TIME_YMDHIS
];
*/

// Additional optional fields
if(isset($pay_receipt)) $payment_data['pay_receipt'] = $pay_receipt;
if(isset($cday)) $payment_data['cday'] = $cday;
if(isset($rootTrxId)) $payment_data['rootTrxId'] = $rootTrxId;
if(isset($memo)) $payment_data['memo'] = $memo;
if(isset($deposit)) $payment_data['deposit'] = $deposit;


send_tran_to_wanpas($payment_data);

?>	