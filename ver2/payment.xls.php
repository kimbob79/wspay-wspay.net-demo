<?php
	include_once('./_common.php');
	if(!$is_admin) {
		alert("접근이 불가합니다.");
	}

	$fr_dates = date("Y-m-d", strtotime($fr_date));
	$to_dates = date("Y-m-d", strtotime($to_date));

	$sql_common = " from g5_payment ";

	if($is_admin) {

		if($member['mb_id'] == "admin") {
			$adm_sql = " mb_1 IN ('uusoft','08224922','08223391','1675170777')";
		} else  {
			$adm_sql = " mb_1 NOT IN ('uusoft','08224922','08223391','1675170777')";
		}

	} else if($member['mb_level'] == 8) {
		$adm_sql = " mb_1 = '{$member['mb_id']}'";
	} else if($member['mb_level'] == 7) {
		$adm_sql = " mb_2 = '{$member['mb_id']}'";
	} else if($member['mb_level'] == 6) {
		$adm_sql = " mb_3 = '{$member['mb_id']}'";
	} else if($member['mb_level'] == 5) {
		$adm_sql = " mb_4 = '{$member['mb_id']}'";
	} else if($member['mb_level'] == 4) {
		$adm_sql = " mb_5 = '{$member['mb_id']}'";
	} else if($member['mb_level'] == 3) {
		$adm_sql = " mb_6 = '{$member['mb_id']}'";
	}

	if ($fr_date == "all" && $to_date == "all") {
		$sql_search = " where ".$adm_sql;
	} else {
		$sql_search = " where ".$adm_sql." and (pay_datetime BETWEEN '{$fr_dates} 00:00:00' and '{$to_dates} 23:59:59') ";
	}

	if($pay_num) {
		$sql_search .= " and pay_num = '{$pay_num}' ";
	}

	if($dv_tid) {
		$sql_search .= " and (dv_tid = '{$dv_tid}') ";
	}

	if($mb_6_name) {
		$sql_search .= " and (mb_6_name = '{$mb_6_name}') ";
	}

	if($gname) { $sql_search .= " and level_company_name like '%{$gname}%' "; }
	/*
	if ($is_admin != 'super')
		$sql_search .= " and (gr_admin = '{$member['mb_id']}') ";
	*/


	if($l2) { $sql_search .= " and mb_pid2 = '{$l2}' "; }
	if($l3) { $sql_search .= " and mb_pid3 = '{$l3}' "; }
	if($l4) { $sql_search .= " and mb_pid4 = '{$l4}' "; }
	if($l5) { $sql_search .= " and mb_pid5 = '{$l5}' "; }
	if($l6) { $sql_search .= " and mb_pid6 = '{$l6}' "; }
	if($l7) { $sql_search .= " and mb_pid7 = '{$l7}' "; }

	if ($stx) {
		$sql_search .= " and ( ";
		switch ($sfl) {
			case "gr_id" :
			case "gr_admin" :
				$sql_search .= " ({$sfl} = '{$stx}') ";
				break;
			default :
				$sql_search .= " ({$sfl} like '%{$stx}%') ";
				break;
		}
		$sql_search .= " ) ";
	}
	
	if ($sst)
		$sql_order = " order by {$sst} {$sod} ";
	else
		$sql_order = " order by pay_datetime desc ";

	$sql = " select count(*) as cnt, sum(pay) as total_pay";
	$sql .= ", sum(if(pay_type = 'Y', pay, 0)) as total_Y_pay, sum(if(pay_type != 'Y', pay, 0)) as total_M_pay, count(if(pay_type = 'Y', 1, null)) as count_Y_pay, count(if(pay_type != 'Y', 1, null)) as count_M_pay {$sql_common} {$sql_search} {$sql_order} ";
	//echo $sql;
//	$sql = " select count(*) as cnt, sum(pay) as total_pay {$sql_common} {$sql_search} {$sql_order} ";
	$row = sql_fetch($sql);

	$total_count = $row['cnt']; // 전체개수
	$total_Y_pay  = $row['total_Y_pay']; // 승인합산
	$total_M_pay  = $row['total_M_pay']; // 취소합산
	$count_Y_pay  = $row['count_Y_pay']; // 승인건수
	$count_M_pay  = $row['count_M_pay']; // 취소건수
	$total_pay = $total_Y_pay - $total_M_pay; // 전체매출합산


	if($page_count) {
		$rows = $page_count;
	} else {
		$rows = $config['cf_page_rows'];
	}
//if($_SERVER['REMOTE_ADDR']=='59.18.140.225') echo $sql;

	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함

	$sql = " select * {$sql_common} {$sql_search} {$sql_order}";
	$result = sql_query($sql);
//	echo $sql;


	header( "Content-type: application/vnd.ms-excel" );
	header( "Content-Disposition: attachment; filename=실시간 결제내역 ".$fr_dates."-".$to_dates." (".date('ymdhis').").xls" );
	header( "Content-Description: PHP Generated Data" );

?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style type="text/css">

.txt {mso-number-format:'\@'}
</style>
</head>

<body>
<table border="1">
	<tr style="background:#ddd; font-size:11px;">
		<th rowspan="2">NO</th>
		<th rowspan="2">가맹점명</th>
		<th rowspan="2">승인일시</th>
		<th rowspan="2">승인금액</th>
		<th colspan="2">본사</th>
		<th colspan="2">지사</th>
		<th colspan="2">총판</th>
		<th colspan="2">대리점</th>
		<th colspan="2">영업점</th>
		<th colspan="2">가맹점</th>
		<th rowspan="2">할부</th>
		<th rowspan="2">카드사</th>
		<th rowspan="2">승인번호</th>
		<th rowspan="2">TID</th>
		<th rowspan="2">구분</th>
		<th rowspan="2">거래번호</th>
		<th rowspan="2">주문번호</th>
	</tr>
	<tr style="background:#ddd; font-size:11px;">
		<th>수수료</th>
		<th>정산금</th>
		<th>수수료</th>
		<th>정산금</th>
		<th>수수료</th>
		<th>정산금</th>
		<th>수수료</th>
		<th>정산금</th>
		<th>수수료</th>
		<th>정산금</th>
		<th>수수료</th>
		<th>정산금</th>
	</tr>
	<?php
		for ($i=0; $row=sql_fetch_array($result); $i++) {

		$bgcolor = '';
		$num = number_format($total_count - ($page - 1) * $rows - $i);

		if($row['pay_type'] == "Y" && $row['pay_cdatetime'] > '0000-00-00 00:00:00') {
			$pay_type = "취소";
			$bgcolor = '#ffe4e9';
		} else if($row['pay_type'] == "Y") {
			$pay_type = "승인";
		} else if($row['pay_type'] == "N") {
			$pay_type = "취소";
			$bgcolor = 'pink';
		} else if($row['pay_type'] == "M") {
			$pay_type = "망취소";
		} else if($row['pay_type'] == "X") {
			$pay_type = "수동취소";
		}
		if($row['pay_parti'] < 1) {
			$pay_parti = "일시불";
		} else {
			$pay_parti = $row['pay_parti']."개월";
		}
		if($row['dv_type'] == "paysharp-a") {
			$dv_type = "더8AL APP";
		} else if($row['dv_type'] == "paysharp-t") {
			$dv_type = "더8AL M100";
		} else if($row['dv_type'] == "pg-korea") {
			$dv_type = "한국결제대행1";
		} else if($row['dv_type'] == "pg-korea2") {
			$dv_type = "한국결제대행2";
		} else if($row['dv_type'] == "thepayone") {
			$dv_type = "더페이원";
		}
		$pay_card_name =  str_replace("카드", "", $row['pay_card_name']);
		$mb_6_fee = 100 - $row['mb_6_fee'];
	?>
	<tr>
		<td><?php echo $num; ?></td>
		<td style="color:#4d4dff"><strong><?php echo $row['mb_6_name']; ?></strong></td>
		<td><?php echo $row['pay_datetime']; ?></td>
		<td><?php if($row['pay_cdatetime'] > 0) { echo "<del>"; } ?><?php echo number_format($row['pay']); ?><?php if($row['pay_cdatetime'] > 0) { echo "</del>"; } ?></td>
		<td><?php if($row['mb_1_fee'] > 0) { echo $row['mb_1_fee']."%"; } ?></td>
		<td><?php if($row['mb_1_fee'] > 0) { echo number_format($row['mb_1_pay']); } ?></td>
		<td><?php if($row['mb_2_fee'] > 0) { echo "".$row['mb_2_fee']."%"; } ?></td>
		<td><?php if($row['mb_1_fee'] > 0) { echo number_format($row['mb_2_pay']); } ?></td>
		<td><?php if($row['mb_3_fee'] > 0) { echo "".$row['mb_3_fee']."%"; } ?></td>
		<td><?php if($row['mb_1_fee'] > 0) { echo number_format($row['mb_3_pay']); } ?></td>
		<td><?php if($row['mb_4_fee'] > 0) { echo "".$row['mb_4_fee']."%"; } ?></td>
		<td><?php if($row['mb_1_fee'] > 0) { echo number_format($row['mb_4_pay']); } ?></td>
		<td><?php if($row['mb_5_fee'] > 0) { echo "".$row['mb_5_fee']."%"; } ?></td>
		<td><?php if($row['mb_1_fee'] > 0) { echo number_format($row['mb_5_pay']); } ?></td>
		<td><?php if($row['mb_6_fee'] > 0) { echo "".$row['mb_6_fee']."%"; } ?></td>
		<td><?php if($row['mb_1_fee'] > 0) { echo number_format($row['mb_6_pay']); } ?></td>

		<td><?php echo $pay_parti; ?></td>
		<td><?php echo mb_substr($pay_card_name,0,2); ?></td>

		<td><?php echo $row['pay_num']; ?></td>
		<td><?php echo $row['dv_tid']; ?></td>
		<td><?php echo $pay_type; ?></td>
		<td><?php echo $row['trxid']; ?></td>
		<td style="mso-number-format:'\@';"><?php echo $row['trackId']; ?></td>
	</tr>
	<?php } ?>
</table>
</body>
</html>