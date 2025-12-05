<?php

if($_GET['mbr'] == "114685" || $_GET['mbr'] == "114725" || $_GET['mbr'] == "114687") {
	echo $_GET['mbr'];
} else {
	echo $_GET['mbr']."aaaaaaa";
}


exit;

	// MBR 113737 / 114274
	if($_GET['mbrNo'] == "113737" || $_GET['mbrNo'] == "114274") {
		echo "전송";
	}





$order_no = "WNA1711360063_DK24169877070273";
$product_code = substr($order_no, 0, 13);
echo $product_code;
exit;

					$datel = "240529";
					$dater = date("Y-m-d", strtotime($datel));
					echo $dater;
					exit;

	$tid = "cpsis26260m01032312202215059007";
	echo substr($tid, 1);
	exit;

	$to_month = date("Y-m");
	$ye_nonth = date("Y-m", mktime(0, 0, 0, intval(date('m'))-1, intval(date('d')), intval(date('Y'))  ));


	if($mb_level == 10) {

		if($redpay == "Y") {
			if($member['mb_id'] == "admin") {
				$adm_sql = " and mb_1 IN (".adm_sql_common.")";
			} else  {
				$adm_sql = " and mb_1 NOT IN (".adm_sql_common.")";
			}
		} else {
			$adm_sql = "";
		}

	} else if($mb_level == 8) {
		$adm_sql = " and mb_1 = '{$mb_id}'";
	} else if($mb_level == 7) {
		$adm_sql = " and mb_2 = '{$mb_id}'";
	} else if($mb_level == 6) {
		$adm_sql = " and mb_3 = '{$mb_id}'";
	} else if($mb_level == 5) {
		$adm_sql = " and mb_4 = '{$mb_id}'";
	} else if($mb_level == 4) {
		$adm_sql = " and mb_5 = '{$mb_id}'";
	} else if($mb_level == 3) {
		$adm_sql = " and mb_6 = '{$mb_id}'";
	}

	$sql = " select
				DATE(pay_datetime) as date,
				sum(pay) as pay
			from
				g5_payment
			GROUP
			BY date";

/*
--이번달 말일
SELECT LAST_DAY(NOW()) FROM DUAL;
#결과창: 2022-12-31

--지난달 말일
SELECT LAST_DAY(NOW() - interval 1 month) FROM DUAL;
#결과창: 2022-11-30

--이번달 첫일
SELECT LAST_DAY(NOW() - interval 1 month) + interval 1 DAY FROM DUAL;
#결과창: 2022-12-01

--지난달 첫일
SELECT LAST_DAY(NOW() - interval 2 month) + interval 1 DAY FROM DUAL;
#결과창: 2022-11-01
*/

?>






	<a href="#" class="btn btn-primary btn-icon-split">
		<span class="icon text-white-50">
			<i class="fas fa-flag"></i>
		</span>
		<span class="text">Split Button Primary</span>
	</a>
	<br><br>
	<a href="#" class="btn btn-success btn-icon-split">
		<span class="icon text-white-50">
			<i class="fas fa-check"></i>
		</span>
		<span class="text">Split Button Success</span>
	</a>
	<br><br>
	<a href="#" class="btn btn-info btn-icon-split">
		<span class="icon text-white-50">
			<i class="fas fa-info-circle"></i>
		</span>
		<span class="text">Split Button Info</span>
	</a>
	<br><br>
	<a href="#" class="btn btn-warning btn-icon-split">
		<span class="icon text-white-50">
			<i class="fas fa-exclamation-triangle"></i>
		</span>
		<span class="text">Split Button Warning</span>
	</a>
	<br><br>
	<a href="#" class="btn btn-danger btn-icon-split">
		<span class="icon text-white-50">
			<i class="fas fa-trash"></i>
		</span>
		<span class="text">Split Button Danger</span>
	</a>
	<br><br>
	<a href="#" class="btn btn-secondary btn-icon-split">
		<span class="icon text-white-50">
			<i class="fas fa-arrow-right"></i>
		</span>
		<span class="text">Split Button Secondary</span>
	</a>
	<br><br>
	<a href="#" class="btn btn-light btn-icon-split">
		<span class="icon text-gray-600">
			<i class="fas fa-arrow-right"></i>
		</span>
		<span class="text">Split Button Light</span>
	</a>
	<br><br>
	<a href="#" class="btn btn-primary btn-icon-split btn-sm">
		<span class="icon text-white-50">
			<i class="fas fa-flag"></i>
		</span>
		<span class="text">Split Button Small</span>
	</a>
	<br><br>
	<a href="#" class="btn btn-primary btn-icon-split btn-lg">
		<span class="icon text-white-50">
			<i class="fas fa-flag"></i>
		</span>
		<span class="text">Split Button Large</span>
	</a>
	<br><br>
	<br><br>






<section id="bo_w">
	<div class="desc_toolcont">
		<?php
		$sql = " select
					date(pay_datetime) as date,
					sum(if(dv_type = '2' and pay_type = 'Y', pay, null)) as orderprices,
					sum(if(dv_type = '1' and pay_type = 'Y', pay, null)) as orderpriced,
					sum(if(pay_type = 'N', pay, null)) as cancelprice
				from
					g5_payment
				WHERE
					SUBSTRING(pay_datetime,1,7) = '$to_month'
				GROUP BY date";
		$result = sql_query($sql);
		echo $sql;
		?>
		<table class="main_table">
			<tr>
				<th>dates</th>
				<th>orderprices</th>
				<th>orderpriced</th>
				<th>cancelprice</th>
			<tr>
			<?php
				for ($i=0; $row=sql_fetch_array($result); $i++) {
			?>
			<tr>
				<td><?php echo $row['date']; ?></td>
				<td><?php echo $row['orderprices']; ?></td>
				<td><?php echo $row['orderpriced']; ?></td>
				<td><?php echo $row['cancelprice']; ?></td>
			</tr>
			<?php
				}
			?>
		</table>
		<?php
		$sql = " select
					date(pay_datetime) as date,
					sum(if(dv_type = '2' and pay_type = 'Y', pay, null)) as orderprices,
					sum(if(dv_type = '1' and pay_type = 'Y', pay, null)) as orderpriced,
					sum(if(pay_type = 'N', pay, null)) as cancelprice
				from
					g5_payment
				WHERE
					SUBSTRING(pay_datetime,1,7) = '$ye_nonth'
				GROUP BY date";
		$result = sql_query($sql);
		echo $sql;
		?>
		<table class="main_table">
			<tr>
				<th>dates</th>
				<th>orderprices</th>
				<th>orderpriced</th>
				<th>cancelprice</th>
			<tr>
			<?php
				for ($i=0; $row=sql_fetch_array($result); $i++) {
			?>
			<tr>
				<td><?php echo $row['date']; ?></td>
				<td><?php echo $row['orderprices']; ?></td>
				<td><?php echo $row['orderpriced']; ?></td>
				<td><?php echo $row['cancelprice']; ?></td>
			</tr>
			<?php
				}
			?>
		</table>
	</div>
</section>