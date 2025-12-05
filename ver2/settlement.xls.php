<?php
	include_once('./_common.php');

	if(!$is_admin) {
//		alert("접근이 불가합니다.");
	}

	header( "Content-type: application/vnd.ms-excel" );
	header( "Content-Disposition: attachment; filename=실시간 정산조회 ".$fr_dates."-".$to_dates." (".date('ymdhis').").xls" );
	header( "Content-Description: PHP Generated Data" );

	if($today == $fr_date and $today == $to_date) {
		$day = 1;
	}

	$fr_dates = date("Y-m-d", strtotime($fr_date));
	$to_dates = date("Y-m-d", strtotime($to_date));
	/*
	총금액
	승인건수
	승인금액
	승인수수료
	취소건수
	취소금액
	*/

	$sql_fild = " * ";
	$sql_fild .= ", count(if(pay_type = 'Y', pay_type, null)) as scnt "; // 승인 건수
	$sql_fild .= ", count(if(pay_type != 'Y', pay_type, null)) as ccnt "; // 취소 건수
	$sql_fild .= ", sum(if(pay_type = 'Y', pay, null)) as spay "; // 승인금액
	$sql_fild .= ", sum(if(pay_type != 'Y', pay, null)) as cpay "; // 취소금액
	$sql_fild .= ", sum(pay) as total_pay "; // 합계

	$sql_fild .= ", sum(if(pay_cdatetime = '0000-00-00 00:00:00', mb_1_pay,0)) as mb_1_pay "; // 본사 수수료
	$sql_fild .= ", sum(if(pay_cdatetime = '0000-00-00 00:00:00', mb_2_pay,0)) as mb_2_pay "; // 지사 수수료
	$sql_fild .= ", sum(if(pay_cdatetime = '0000-00-00 00:00:00', mb_3_pay,0)) as mb_3_pay "; // 총판 수수료
	$sql_fild .= ", sum(if(pay_cdatetime = '0000-00-00 00:00:00', mb_4_pay,0)) as mb_4_pay "; // 대리점 수수료
	$sql_fild .= ", sum(if(pay_cdatetime = '0000-00-00 00:00:00', mb_5_pay,0)) as mb_5_pay "; // 영업점 수수료
	$sql_fild .= ", sum(if(pay_cdatetime = '0000-00-00 00:00:00', mb_6_pay,0)) as mb_6_pay "; // 가맹점 정산액
	

//	$sql_common = " from g5_payment a left join g5_member b on a.mb_6 = b.mb_id left join g5_device c on a.dv_tid = c.dv_tid ";

	$sql_common = " from g5_payment ";


	if($is_admin) {

		if($member['mb_id'] == "admin") {
			$sql_search = " where mb_1 IN ('uusoft','08224922','08223391','1675170777')";
		} else  {
			$sql_search = " where mb_1 NOT IN ('uusoft','08224922','08223391','1675170777')";
		}

		if($membera) {
			$sql_search .= " and mb_1 = '$membera' ";
		}
		if($memberb) {
			$sql_search .= " and mb_2 = '$memberb' ";
		}
		if($memberc) {
			$sql_search .= " and mb_3 = '$memberc' ";
		}
		if($memberd) {
			$sql_search .= " and mb_4 = '$memberd' ";
		}
		if($membere) {
			$sql_search .= " and mb_5 = '$membere' ";
		}
		if($memberf) {
			$sql_search .= " and mb_6 = '$memberf' ";
		}

	} else if($member['mb_level'] == 8) {
		$sql_search = " where mb_1 = '{$member['mb_id']}'";
	} else if($member['mb_level'] == 7) {
		$sql_search = " where mb_2 = '{$member['mb_id']}'";
	} else if($member['mb_level'] == 6) {
		$sql_search = " where mb_3 = '{$member['mb_id']}'";
	} else if($member['mb_level'] == 5) {
		$sql_search = " where mb_4 = '{$member['mb_id']}'";
	} else if($member['mb_level'] == 4) {
		$sql_search = " where mb_5 = '{$member['mb_id']}'";
	} else if($member['mb_level'] == 3) {
		$sql_search = " where mb_6 = '{$member['mb_id']}'";
	}
	
	$sql_search .= " and (pay_datetime BETWEEN '{$fr_dates} 00:00:00' and '{$to_dates} 23:59:59') ";
	

	if($dv_tid) {
		$sql_search .= " and (dv_tid like '%{$dv_tid}%') ";
	}

	if($mb_6_name) {
		$sql_search .= " and (mb_6_name like '%{$mb_6_name}%') ";
	}

	if ($sst)
		$sql_order = " order by {$sst} {$sod} ";
	else
		$sql_order = " order by seq desc ";


	$sql = " select {$sql_fild} {$sql_common} {$sql_search} group by dv_tid having pay <> 0 ORDER BY mb_1, mb_2, mb_3, mb_4, mb_5 asc";
	$result = sql_query($sql);

?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style type="text/css">
th {background:#ddd;}
.txt {mso-number-format:'\@'}
</style>
</head>

<body>

정산일 <?php echo date("Y.m.d", strtotime($fr_date)); ?> ~ <?php echo date("Y.m.d", strtotime($to_date)); ?>

<table border="1">
	<thead>
	<tr style="background:#ddd; font-size:11px;">
		<th rowspan="2">가맹점명</th>
		<th rowspan="2">승인</th>
		<th rowspan="2">취소</th>
		<th rowspan="2">승인금액</th>
		<th rowspan="2">취소금액</th>
		<th rowspan="2">총금액</th>

		<?php if($member['mb_level'] >= 8) { ?>
		<th colspan="4">본사</th>
		<?php } ?>

		<?php if($member['mb_level'] >= 7) { ?>
		<th colspan="4">지사</th>
		<?php } ?>

		<?php if($member['mb_level'] >= 6) { ?>
		<th colspan="4">총판</th>
		<?php } ?>

		<?php if($member['mb_level'] >= 5) { ?>
		<th colspan="4">대리점</th>
		<?php } ?>

		<?php if($member['mb_level'] >= 4) { ?>
		<th colspan="4">영업점</th>
		<?php } ?>

		<th colspan="4">가맹점</th>
		<th rowspan="2">계좌정보</th>
	</tr>
	<tr style="background:#ddd; font-size:11px;">
		<?php if($member['mb_level'] >= 8) { ?>
		<th>업체명</th>
		<th>수수료</th>
		<th>정산수수료</th>
		<th>정산금</th>
		<?php } ?>

		<?php if($member['mb_level'] >= 7) { ?>
		<th>업체명</th>
		<th>수수료</th>
		<th>정산수수료</th>
		<th>정산금</th>
		<?php } ?>

		<?php if($member['mb_level'] >= 6) { ?>
		<th>업체명</th>
		<th>수수료</th>
		<th>정산수수료</th>
		<th>정산금</th>
		<?php } ?>

		<?php if($member['mb_level'] >= 5) { ?>
		<th>업체명</th>
		<th>수수료</th>
		<th>정산수수료</th>
		<th>정산금</th>
		<?php } ?>

		<?php if($member['mb_level'] >= 4) { ?>
		<th>업체명</th>
		<th>수수료</th>
		<th>정산수수료</th>
		<th>정산금</th>
		<?php } ?>

		<th>업체명</th>
		<th>수수료</th>
		<th>정산수수료</th>
		<th>정산금</th>
	</tr>
	</thead>
	<tbody>
	<?php
		$s_pay = 0;
		$scnt_total = 0;
		$ccnt_total = 0;
		$spay_total = 0;
		$cpay_total = 0;
		$total_pay_total = 0;
		$mb_pay2_total = 0;
		$mb_pay3_total = 0;
		$mb_pay4_total = 0;
		$mb_pay5_total = 0;
		$mb_pay6_total = 0;
		$s_pay_total = 0;


		$mb_1_pay = 0;
		$mb_2_pay = 0;
		$mb_3_pay = 0;
		$mb_4_pay = 0;
		$mb_5_pay = 0;
		$mb_6_pay = 0;

		$mb_1_fee = 0;
		$mb_2_fee = 0;
		$mb_3_fee = 0;
		$mb_4_fee = 0;
		$mb_5_fee = 0;
		$mb_6_fee = 0;

		for ($i=0; $row=sql_fetch_array($result); $i++) {

			$total_pay = $row['spay'] - $row['cpay']; // 총 승인금액 (승인-취소)

			// 본사 수수료율
			if($row['mb_2_fee'] > 0) {
				$mb_1_fee = $row['mb_2_fee'] - $row['mb_1_fee']; // 지사 - 본사
			} else if($row['mb_3_fee'] > 0) {
				$mb_1_fee = $row['mb_3_fee'] - $row['mb_1_fee']; // 총판 - 본사
			} else if($row['mb_4_fee'] > 0) {
				$mb_1_fee = $row['mb_4_fee'] - $row['mb_1_fee']; // 대리점 - 본사
			} else if($row['mb_5_fee'] > 0) {
				$mb_1_fee = $row['mb_5_fee'] - $row['mb_1_fee']; // 영업점 - 본사
			} else if($row['mb_6_fee'] > 0) {
				$mb_1_fee = $row['mb_6_fee'] - $row['mb_1_fee']; // 가맹점 - 본사
			}
			// 지사 수수료율
			if($row['mb_3_fee'] > 0) {
				$mb_2_fee = $row['mb_3_fee'] - $row['mb_2_fee']; // 총판 - 지사
			} else if($row['mb_4_fee'] > 0) {
				$mb_2_fee = $row['mb_4_fee'] - $row['mb_2_fee']; // 대리점 - 지사
			} else if($row['mb_5_fee'] > 0) {
				$mb_2_fee = $row['mb_5_fee'] - $row['mb_2_fee']; // 영업점 - 지사
			} else if($row['mb_6_fee'] > 0) {
				$mb_2_fee = $row['mb_6_fee'] - $row['mb_2_fee']; // 가맹점 - 지사
			}
			// 총판 수수료율
			if($row['mb_4_fee'] > 0) {
				$mb_3_fee = $row['mb_4_fee'] - $row['mb_3_fee']; // 대리점 - 총판
			} else if($row['mb_5_fee'] > 0) {
				$mb_3_fee = $row['mb_5_fee'] - $row['mb_3_fee']; // 영업점 - 총판
			} else if($row['mb_6_fee'] > 0) {
				$mb_3_fee = $row['mb_6_fee'] - $row['mb_3_fee']; // 가맹점 - 총판
			}
			// 대리점 수수료율
			if($row['mb_5_fee'] > 0) {
				$mb_4_fee = $row['mb_5_fee'] - $row['mb_4_fee']; // 영업점 - 대리점
			} else if($row['mb_6_fee'] > 0) {
				$mb_4_fee = $row['mb_6_fee'] - $row['mb_4_fee']; // 가맹점 - 대리점
			}
			// 영업점 수수료율
			if($row['mb_6_fee'] > 0) {
				$mb_5_fee = $row['mb_6_fee'] - $row['mb_5_fee']; // 가맹점 - 영업점
			}

			$mb_1_fee = sprintf('%0.2f', $mb_1_fee);
			$mb_2_fee = sprintf('%0.2f', $mb_2_fee);
			$mb_3_fee = sprintf('%0.2f', $mb_3_fee);
			$mb_4_fee = sprintf('%0.2f', $mb_4_fee);
			$mb_5_fee = sprintf('%0.2f', $mb_5_fee);

			if($row['mb_1_name']) { $mb_1_pay = $mb_1_fee * $total_pay / 100; } else { $mb_1_pay = 0; }
			if($row['mb_2_name']) { $mb_2_pay = $mb_2_fee * $total_pay / 100; } else { $mb_2_pay = 0; }
			if($row['mb_3_name']) { $mb_3_pay = $mb_3_fee * $total_pay / 100; } else { $mb_3_pay = 0; }
			if($row['mb_4_name']) { $mb_4_pay = $mb_4_fee * $total_pay / 100; } else { $mb_4_pay = 0; }
			if($row['mb_5_name']) { $mb_5_pay = $mb_5_fee * $total_pay / 100; } else { $mb_5_pay = 0; }
			if($row['mb_6_name']) {
				$mb_6_pay = $row['mb_6_fee'] * $total_pay / 100;
				$mb_6_pay = $total_pay - $mb_6_pay;
			} else {
				$mb_6_pay = 0;
			}

			$pg_pay = round($row['total_pay'] * 0.0374);

			$mb_1_pay = floor($mb_1_pay);
			$mb_2_pay = floor($mb_2_pay);
			$mb_3_pay = floor($mb_3_pay);
			$mb_4_pay = floor($mb_4_pay);
			$mb_5_pay = floor($mb_5_pay);
			$mb_6_pay = floor($mb_6_pay);

			$scnt_total = $scnt_total + $row['scnt'];
			$ccnt_total = $ccnt_total + $row['ccnt'];

			$spay_total = $spay_total + $row['spay'];
			$cpay_total = $cpay_total + $row['cpay'];
			$total_pay_total = $spay_total - $cpay_total;

			$pg_total = $pg_total + $pg_pay;

			$mb_pay1_total = $mb_pay1_total + $mb_1_pay;
			$mb_pay2_total = $mb_pay2_total + $mb_2_pay;
			$mb_pay3_total = $mb_pay3_total + $mb_3_pay;
			$mb_pay4_total = $mb_pay4_total + $mb_4_pay;
			$mb_pay5_total = $mb_pay5_total + $mb_5_pay;
			$mb_pay6_total = $mb_pay6_total + $mb_6_pay;
			$mb_6_fee = 100 - $row['mb_6_fee'];
	?>
	<tr>
		<td style="color:#4d4dff"><?php echo $row['mb_6_name']; ?></td>
		<td><?php echo $row['scnt']; ?></td>
		<td><?php echo $row['ccnt']; ?></td>
		<td><?php echo number_format($row['spay']); ?></td>
		<td><?php echo number_format($row['cpay']); ?></td>
		<td><?php echo number_format($total_pay); ?></td>

		<?php if($member['mb_level'] >= 8) { ?>
		<td style="color:#4d4dff"><?php if($row['mb_1_name']) { echo $row['mb_1_name']; } else { echo ""; } ?></td>
		<td><?php if($row['mb_1_name']) { echo $row['mb_1_fee']."%"; } else { echo ""; } ?></td>
		<td style="color:#4d4dff"><?php if($row['mb_1_name']) { echo $mb_1_fee."%"; } else { echo ""; } ?></td>
		<td style="font-weight:bold"><?php if($row['mb_1_name']) { echo number_format($mb_1_pay); } else { echo ""; } ?></td>
		<?php } ?>

		<?php if($member['mb_level'] >= 7) { ?>
		<td style="color:#4d4dff"><?php if($row['mb_2_name']) { echo $row['mb_2_name']; } else { echo ""; } ?></td>
		<td><?php if($row['mb_2_name']) { echo $row['mb_2_fee']."%"; } else { echo ""; } ?></td>
		<td style="color:#4d4dff"><?php if($row['mb_2_name']) { echo $mb_2_fee."%"; } else { echo ""; } ?></td>
		<td style="font-weight:bold"><?php if($row['mb_2_name']) { echo number_format($mb_2_pay); } else { echo ""; } ?></td>
		<?php } ?>

		<?php if($member['mb_level'] >= 6) { ?>
		<td style="color:#4d4dff"><?php if($row['mb_3_name']) { echo $row['mb_3_name']; } else { echo ""; } ?></td>
		<td><?php if($row['mb_3_name']) { echo $row['mb_3_fee']."%"; } else { echo ""; } ?></td>
		<td style="color:#4d4dff"><?php if($row['mb_3_name']) { echo $mb_3_fee."%"; } else { echo ""; } ?></td>
		<td style="font-weight:bold"><?php if($row['mb_3_name']) { echo number_format($mb_3_pay); } else { echo ""; } ?></td>
		<?php } ?>

		<?php if($member['mb_level'] >= 5) { ?>
		<td style="color:#4d4dff"><?php if($row['mb_4_name']) { echo $row['mb_4_name']; } else { echo ""; } ?></td>
		<td><?php if($row['mb_4_name']) { echo $row['mb_4_fee']."%"; } else { echo ""; } ?></td>
		<td style="color:#4d4dff"><?php if($row['mb_4_name']) { echo $mb_4_fee."%"; } else { echo ""; } ?></td>
		<td style="font-weight:bold"><?php if($row['mb_4_name']) { echo number_format($mb_4_pay); } else { echo ""; } ?></td>
		<?php } ?>

		<?php if($member['mb_level'] >= 4) { ?>
		<td style="color:#4d4dff"><?php if($row['mb_5_name']) { echo $row['mb_5_name']; } else { echo ""; } ?></td>
		<td><?php if($row['mb_5_name']) { echo $row['mb_5_fee']."%"; } else { echo ""; } ?></td>
		<td style="color:#4d4dff"><?php if($row['mb_5_name']) { echo $mb_5_fee."%"; } else { echo ""; } ?></td>
		<td style="font-weight:bold"><?php if($row['mb_5_name']) { echo number_format($mb_5_pay); } else { echo ""; } ?></td>
		<?php } ?>


		<td style="color:#4d4dff"><?php echo $row['mb_6_name']; ?></td>
		<td><?php if($row['mb_6_name']) { echo $row['mb_6_fee']."%"; } else { echo ""; } ?></td>
		<td style="color:#4d4dff"><?php if($row['mb_6_name']) { echo $mb_6_fee."%"; } else { echo ""; } ?></td>
		<td style="font-weight:bold"><?php if($row['mb_6_name']) { echo number_format($mb_6_pay); } else { echo ""; } ?></td>

		<?php if($is_admin) { ?>
		<td><?php if($row['mb_8']) { echo $row['mb_8']; ?> <?php echo $row['mb_9']; ?> <?php echo $row['mb_10']; } else { echo "-"; } ?></td>
		<?php } ?>
	</tr>
	<?php } ?>
	</tbody>
	<tfoot>
	<tr>
		<td style="font-weight:bold">합계</td>
		<td style="font-weight:bold"><?php echo number_format($scnt_total); ?></td>
		<td style="font-weight:bold"><?php echo number_format($ccnt_total); ?></td>

		<td style="font-weight:bold"><?php echo number_format($spay_total); ?></td>
		<td style="font-weight:bold"><?php echo number_format($cpay_total); ?></td>
		<td style="font-weight:bold"><?php echo number_format($total_pay_total); ?></td>
		<?php if($member['mb_level'] >= 8) { ?>
		<td></td>
		<td></td>
		<td></td>
		<td style="font-weight:bold"><?php echo number_format($mb_pay1_total); ?></td>
		<?php } ?>
		<?php if($member['mb_level'] >= 7) { ?>
		<td></td>
		<td></td>
		<td></td>
		<td style="font-weight:bold"><?php echo number_format($mb_pay2_total); ?></td>
		<?php } ?>
		<?php if($member['mb_level'] >= 6) { ?>
		<td></td>
		<td></td>
		<td></td>
		<td style="font-weight:bold"><?php echo number_format($mb_pay3_total); ?></td>
		<?php } ?>
		<?php if($member['mb_level'] >= 5) { ?>
		<td></td>
		<td></td>
		<td></td>
		<td style="font-weight:bold"><?php echo number_format($mb_pay4_total); ?></td>
		<?php } ?>
		<?php if($member['mb_level'] >= 4) { ?>
		<td></td>
		<td></td>
		<td></td>
		<td style="font-weight:bold"><?php echo number_format($mb_pay5_total); ?></td>
		<?php } ?>
		<td></td>
		<td></td>
		<td></td>
		<td style="font-weight:bold"><?php echo number_format($mb_pay6_total); ?></td>
		<?php if($is_admin) { ?>
		<td></td>
		<?php } ?>
	</tr>
	</tfoot>
	<?php
		if ($i == 0) {
			echo '<tr><td colspan="31" style="background:#ddd; color:#888; text-align:center;">가맹점 결제내역이 없습니다.</td></tr>';
		}
	?>
</table>



<?php if($is_admin) { ?>
<br>

정산일 <?php echo date("Y.m.d", strtotime($fr_date)); ?> ~ <?php echo date("Y.m.d", strtotime($to_date)); ?>
<?php
	$sql = " select {$sql_fild} {$sql_common} {$sql_search} and mb_1 != '' group by mb_1 having pay <> 0 ORDER BY mb_1, mb_2, mb_3, mb_4, mb_5 asc";
	$result = sql_query($sql);
?>

<table border="1">
	<tr style="background:#ddd; font-size:11px;">
		<th>본사명</th>
		<th>승</th>
		<th>취</th>
		<th>승인금액</th>
		<th>취소금액</th>
		<th>총금액</th>
		<th>정산액</th>
		<th>부가세제외</th>
		<th>계좌정보</th>
	</tr>
	<?php

		$s_pay = 0;
		$scnt_total = 0;
		$ccnt_total = 0;
		$spay_total = 0;
		$cpay_total = 0;
		$total_pay_total = 0;
		$mb_pay2_total = 0;
		$mb_pay3_total = 0;
		$mb_pay4_total = 0;
		$mb_pay5_total = 0;
		$mb_pay6_total = 0;
		$s_pay_total = 0;

		for ($i=0; $row=sql_fetch_array($result); $i++) {


		$scnt_total = $scnt_total + $row['scnt'];
		$ccnt_total = $ccnt_total + $row['ccnt'];
		$spay_total = $spay_total + $row['spay'];
		$cpay_total = $cpay_total + $row['cpay'];

		$total_pay_total = $spay_total - $cpay_total;
		$total_pay = $row['spay'] - $row['cpay'];
		$mb_1_pay = $row['mb_1_pay'] - $row['mb_1_pay'] * 0.133;
		$mb1 = get_member($row['mb_1']);

		if($mb1['mb_memo_call']) {
			$bank1 = $mb1['mb_memo_call'];
		} else {
			$bank1 = $mb1['mb_8']." " .$mb1['mb_9']." " .$mb1['mb_10'];
		}

	?>
	<tr>
		<td style="color:#4d4dff"><?php echo $row['mb_1_name']; ?></td>
		<td><?php echo $row['scnt']; ?></td>
		<td><?php echo $row['ccnt']; ?></td>
		<td><?php echo number_format($row['spay']); ?></td>
		<td><?php echo number_format($row['cpay']); ?></td>
		<td><?php echo number_format($total_pay); ?></td>
		<td><?php if($row['mb_1_name']) { echo number_format($row['mb_1_pay']); } ?></td>
		<td><?php if($row['mb_1_name']) { echo number_format($mb_1_pay); } ?></td>
		<td><?php echo $bank1; ?></td>
	</tr>
	<?php
		}
		if ($i == 0) {
			echo '<tr><td colspan="9" style="background:#ddd; color:#888; text-align:center;">본사 결제내역이 없습니다.</td></tr>';
		}
		$sql = " select {$sql_fild} {$sql_common} {$sql_search} and mb_2 != '' group by mb_2 having pay <> 0 ORDER BY mb_1, mb_2, mb_3, mb_4, mb_5 asc";
		$result = sql_query($sql);
	?>
	<tr style="background:#ddd; font-size:11px;">
		<th>지사명</th>
		<th>승</th>
		<th>취</th>
		<th>승인금액</th>
		<th>취소금액</th>
		<th>총금액</th>
		<th>정산액</th>
		<th>부가세제외</th>
		<th>계좌정보</th>
	</tr>
	<?php

		$s_pay = 0;
		$scnt_total = 0;
		$ccnt_total = 0;
		$spay_total = 0;
		$cpay_total = 0;
		$total_pay_total = 0;
		$mb_pay2_total = 0;
		$mb_pay3_total = 0;
		$mb_pay4_total = 0;
		$mb_pay5_total = 0;
		$mb_pay6_total = 0;
		$s_pay_total = 0;

		for ($i=0; $row=sql_fetch_array($result); $i++) {

		$s_pay = $row['spay'] + $row['cpay'] - ($row['mb_pay2'] + $row['mb_pay3'] + $row['mb_pay4'] + $row['mb_pay5'] + $row['mb_pay6']);

		$scnt_total = $scnt_total + $row['scnt'];
		$ccnt_total = $ccnt_total + $row['ccnt'];
		$spay_total = $spay_total + $row['spay'];
		$cpay_total = $cpay_total + $row['cpay'];

		$total_pay_total = $spay_total - $cpay_total;
		$mb_pay1_total = $mb_pay1_total + $row['mb_1_pay'];
		$mb_pay2_total = $mb_pay2_total + $row['mb_2_pay'];
		$mb_pay3_total = $mb_pay3_total + $row['mb_3_pay'];
		$mb_pay4_total = $mb_pay4_total + $row['mb_4_pay'];
		$mb_pay5_total = $mb_pay5_total + $row['mb_5_pay'];
		$mb_pay6_total = $mb_pay6_total + $row['mb_6_pay'];
		$s_pay_total = $s_pay_total + $s_pay;
		$total_pay = $row['spay'] - $row['cpay'];

		if($row['mb_2_name'] == "진산") {
			$mb_2_pay = $row['mb_2_pay'] - $row['mb_2_pay'] * 0.1;
		} else if($row['mb_2_name'] == "용훈") {
			$mb_2_pay = $row['mb_2_pay'] - $row['mb_2_pay'] * 0.1;
		} else {
			$mb_2_pay = $row['mb_2_pay'] - $row['mb_2_pay'] * 0.133;
		}

		$mb_2_pay = $row['mb_2_pay'] - $row['mb_2_pay'] * 0.133;

		$mb2 = get_member($row['mb_2']);
		if($mb2['mb_memo_call']) {
			$bank2 = $mb2['mb_memo_call'];
		} else {
			$bank2 = $mb2['mb_8']." " .$mb2['mb_9']." " .$mb2['mb_10'];
		}
	?>
	<tr>
		<td style="color:#4d4dff"><?php echo $row['mb_2_name']; ?></td>
		<td><?php echo $row['scnt']; ?></td>
		<td><?php echo $row['ccnt']; ?></td>
		<td><?php echo number_format($row['spay']); ?></td>
		<td><?php echo number_format($row['cpay']); ?></td>
		<td><?php echo number_format($total_pay); ?></td>
		<td><?php if($row['mb_2_name']) { echo number_format($row['mb_2_pay']); } ?></td>
		<td><?php if($row['mb_2_name']) { echo number_format($mb_2_pay); } ?></td>
		<td><?php echo $bank2; ?></td>
	</tr>
	<?php
		}
		if ($i == 0) {
			echo '<tr><td colspan="9" style="background:#ddd; color:#888; text-align:center;">지사 결제내역이 없습니다.</td></tr>';
		}
		$sql = " select {$sql_fild} {$sql_common} {$sql_search} and mb_3 != '' group by mb_3 having pay <> 0 ORDER BY mb_1, mb_2, mb_3, mb_4, mb_5 asc";
		$result = sql_query($sql);
	?>
	<tr style="background:#ddd; font-size:11px;">
		<th>총판명</th>
		<th>승</th>
		<th>취</th>
		<th>승인금액</th>
		<th>취소금액</th>
		<th>총금액</th>
		<th>정산액</th>
		<th>부가세제외</th>
		<th>계좌정보</th>
	</tr>
	<?php

		$s_pay = 0;
		$scnt_total = 0;
		$ccnt_total = 0;
		$spay_total = 0;
		$cpay_total = 0;
		$total_pay_total = 0;
		$mb_pay2_total = 0;
		$mb_pay3_total = 0;
		$mb_pay4_total = 0;
		$mb_pay5_total = 0;
		$mb_pay6_total = 0;
		$s_pay_total = 0;

		for ($i=0; $row=sql_fetch_array($result); $i++) {

		$s_pay = $row['spay'] + $row['cpay'] - ($row['mb_pay2'] + $row['mb_pay3'] + $row['mb_pay4'] + $row['mb_pay5'] + $row['mb_pay6']);

		$scnt_total = $scnt_total + $row['scnt'];
		$ccnt_total = $ccnt_total + $row['ccnt'];
		$spay_total = $spay_total + $row['spay'];
		$cpay_total = $cpay_total + $row['cpay'];

		$total_pay_total = $spay_total - $cpay_total;
		$mb_pay1_total = $mb_pay1_total + $row['mb_1_pay'];
		$mb_pay2_total = $mb_pay2_total + $row['mb_2_pay'];
		$mb_pay3_total = $mb_pay3_total + $row['mb_3_pay'];
		$mb_pay4_total = $mb_pay4_total + $row['mb_4_pay'];
		$mb_pay5_total = $mb_pay5_total + $row['mb_5_pay'];
		$mb_pay6_total = $mb_pay6_total + $row['mb_6_pay'];
		$s_pay_total = $s_pay_total + $s_pay;
		$total_pay = $row['spay'] - $row['cpay'];

		$mb_3_pay = $row['mb_3_pay'] - $row['mb_3_pay'] * 0.133;

		$mb3 = get_member($row['mb_3']);
		if($mb3['mb_memo_call']) {
			$bank3 = $mb3['mb_memo_call'];
		} else {
			$bank3 = $mb3['mb_8']." " .$mb3['mb_9']." " .$mb3['mb_10'];
		}

	?>
	<tr>
		<td style="color:#4d4dff"><?php echo $row['mb_3_name']; ?></td>
		<td><?php echo $row['scnt']; ?></td>
		<td><?php echo $row['ccnt']; ?></td>
		<td><?php echo number_format($row['spay']); ?></td>
		<td><?php echo number_format($row['cpay']); ?></td>
		<td><?php echo number_format($total_pay); ?></td>
		<td><?php if($row['mb_3_name']) { echo number_format($row['mb_3_pay']); } ?></td>
		<td><?php if($row['mb_3_name']) { echo number_format($mb_3_pay); } ?></td>
		<td><?php echo $bank3; ?></td>
	</tr>
	<?php
		}
		if ($i == 0) {
			echo '<tr><td colspan="9" style="background:#ddd; color:#888; text-align:center;">총판 결제내역이 없습니다.</td></tr>';
		}
		$sql = " select {$sql_fild} {$sql_common} {$sql_search} and mb_4 != '' group by mb_4 having pay <> 0 ORDER BY mb_1, mb_2, mb_3, mb_4, mb_5 asc";
		$result = sql_query($sql);
	?>
	<tr style="background:#ddd; font-size:11px;">
		<th>대리점명</th>
		<th>승</th>
		<th>취</th>
		<th>승인금액</th>
		<th>취소금액</th>
		<th>총금액</th>
		<th>정산액</th>
		<th>부가세제외</th>
		<th>계좌정보</th>
	</tr>
	<?php

		$s_pay = 0;
		$scnt_total = 0;
		$ccnt_total = 0;
		$spay_total = 0;
		$cpay_total = 0;
		$total_pay_total = 0;
		$mb_pay2_total = 0;
		$mb_pay3_total = 0;
		$mb_pay4_total = 0;
		$mb_pay5_total = 0;
		$mb_pay6_total = 0;
		$s_pay_total = 0;

		for ($i=0; $row=sql_fetch_array($result); $i++) {

		$s_pay = $row['spay'] + $row['cpay'] - ($row['mb_pay2'] + $row['mb_pay3'] + $row['mb_pay4'] + $row['mb_pay5'] + $row['mb_pay6']);

		$scnt_total = $scnt_total + $row['scnt'];
		$ccnt_total = $ccnt_total + $row['ccnt'];
		$spay_total = $spay_total + $row['spay'];
		$cpay_total = $cpay_total + $row['cpay'];

		$total_pay_total = $spay_total - $cpay_total;
		$mb_pay1_total = $mb_pay1_total + $row['mb_1_pay'];
		$mb_pay2_total = $mb_pay2_total + $row['mb_2_pay'];
		$mb_pay3_total = $mb_pay3_total + $row['mb_3_pay'];
		$mb_pay4_total = $mb_pay4_total + $row['mb_4_pay'];
		$mb_pay5_total = $mb_pay5_total + $row['mb_5_pay'];
		$mb_pay6_total = $mb_pay6_total + $row['mb_6_pay'];
		$s_pay_total = $s_pay_total + $s_pay;
		$total_pay = $row['spay'] - $row['cpay'];
		$mb_4_pay = $row['mb_4_pay'] - $row['mb_4_pay'] * 0.133;

		$mb4 = get_member($row['mb_4']);
		if($mb4['mb_memo_call']) {
			$bank4 = $mb4['mb_memo_call'];
		} else {
			$bank4 = $mb4['mb_8']." " .$mb4['mb_9']." " .$mb4['mb_10'];
		}

	?>
	<tr>
		<td style="color:#4d4dff"><?php echo $row['mb_4_name']; ?></td>
		<td><?php echo $row['scnt']; ?></td>
		<td><?php echo $row['ccnt']; ?></td>
		<td><?php echo number_format($row['spay']); ?></td>
		<td><?php echo number_format($row['cpay']); ?></td>
		<td><?php echo number_format($total_pay); ?></td>
		<td><?php if($row['mb_4_name']) { echo number_format($row['mb_4_pay']); } ?></td>
		<td><?php if($row['mb_4_name']) { echo number_format($mb_4_pay); } ?></td>
		<td><?php echo $bank4; ?></td>
	</tr>
	<?php
		}
		if ($i == 0) {
			echo '<tr><td colspan="9" style="background:#ddd; color:#888; text-align:center;">대리점 결제내역이 없습니다.</td></tr>';
		}
		$sql = " select {$sql_fild} {$sql_common} {$sql_search} and mb_5 != '' group by mb_5 having pay <> 0 ORDER BY mb_1, mb_2, mb_3, mb_4, mb_5 asc";
		$result = sql_query($sql);
	?>
	<tr style="background:#ddd; font-size:11px;">
		<th>영업점명</th>
		<th>승</th>
		<th>취</th>
		<th>승인금액</th>
		<th>취소금액</th>
		<th>총금액</th>
		<th>정산액</th>
		<th>부가세제외</th>
		<th>계좌정보</th>
	</tr>
	<?php

		$s_pay = 0;
		$scnt_total = 0;
		$ccnt_total = 0;
		$spay_total = 0;
		$cpay_total = 0;
		$total_pay_total = 0;
		$mb_pay2_total = 0;
		$mb_pay3_total = 0;
		$mb_pay4_total = 0;
		$mb_pay5_total = 0;
		$mb_pay6_total = 0;
		$s_pay_total = 0;

		for ($i=0; $row=sql_fetch_array($result); $i++) {

		$s_pay = $row['spay'] + $row['cpay'] - ($row['mb_pay2'] + $row['mb_pay3'] + $row['mb_pay4'] + $row['mb_pay5'] + $row['mb_pay6']);

		$scnt_total = $scnt_total + $row['scnt'];
		$ccnt_total = $ccnt_total + $row['ccnt'];
		$spay_total = $spay_total + $row['spay'];
		$cpay_total = $cpay_total + $row['cpay'];

		$total_pay_total = $spay_total - $cpay_total;
		$mb_pay1_total = $mb_pay1_total + $row['mb_1_pay'];
		$mb_pay2_total = $mb_pay2_total + $row['mb_2_pay'];
		$mb_pay3_total = $mb_pay3_total + $row['mb_3_pay'];
		$mb_pay4_total = $mb_pay4_total + $row['mb_4_pay'];
		$mb_pay5_total = $mb_pay5_total + $row['mb_5_pay'];
		$mb_pay6_total = $mb_pay6_total + $row['mb_6_pay'];
		$s_pay_total = $s_pay_total + $s_pay;
		$total_pay = $row['spay'] - $row['cpay'];
		$mb_5_pay = $row['mb_5_pay'] - $row['mb_5_pay'] * 0.133;

		$mb5 = get_member($row['mb_5']);
		if($mb5['mb_memo_call']) {
			$bank5 = $mb5['mb_memo_call'];
		} else {
			$bank5 = $mb5['mb_8']." " .$mb5['mb_9']." " .$mb5['mb_10'];
		}

	?>
	<tr>
		<td style="color:#4d4dff"><?php echo $row['mb_5_name']; ?></td>
		<td><?php echo $row['scnt']; ?></td>
		<td><?php echo $row['ccnt']; ?></td>
		<td><?php echo number_format($row['spay']); ?></td>
		<td><?php echo number_format($row['cpay']); ?></td>
		<td><?php echo number_format($total_pay); ?></td>
		<td style="color:#4d4dff"><?php if($row['mb_5_name']) { echo number_format($row['mb_5_pay']); } ?></td>
		<td><?php if($row['mb_5_name']) { echo number_format($mb_5_pay); } ?></td>
		<td><?php echo $bank5; ?></td>
	</tr>
	<?php
		}
		if ($i == 0) {
			echo '<tr><td colspan="9" style="background:#ddd; color:#888; text-align:center;">영업점 결제내역이 없습니다.</td></tr>';
		}
	?>
</table>
<?php } ?>

</body>
</html>