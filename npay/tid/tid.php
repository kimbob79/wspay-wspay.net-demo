<?php
	error_reporting(E_ALL);
	ini_set("display_errors", 0);

	include_once('./_common.php');


	$sql_common = " from g5_device_mpc_2 left join g5_device_mpc on g5_device_mpc_2.a = g5_device_mpc.dv_tid where a != '' ";

	if ($sst)
		$sql_order = " order by {$sst} {$sod} ";
	else
		$sql_order = " order by datetime  asc ";

	$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
	$row = sql_fetch($sql);

	$total_count = $row['cnt']; // 전체개수

	$rows = 1000;

	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함

	$sql = " select * {$sql_common} order by d_id  asc limit {$from_record}, {$rows} ";
	$result = sql_query($sql);

//	echo $sql;


?>


<table border="1">
	<thead>
		<?php /*
		<tr>
			<th>no</th>
			<th>a</th>
			<th>b</th>
			<th>dv_id</th>
			<th>dv_tid</th>

			<th>mb_1</th>
			<th>mb_1_name</th>
			<th>mb_1_fee</th>

			<th>mb_2</th>
			<th>mb_2_name</th>
			<th>mb_2_fee</th>

			<th>mb_3</th>
			<th>mb_3_name</th>
			<th>mb_3_fee</th>

			<th>mb_4</th>
			<th>mb_4_name</th>
			<th>mb_4_fee</th>

			<th>mb_5</th>
			<th>mb_5_name</th>
			<th>mb_5_fee</th>

			<th>mb_6</th>
			<th>mb_6_name</th>
			<th>mb_6_fee</th>


			<th>dv_pg</th>
			<th>dv_type</th>
			<th>dv_certi</th>
			<th>dv_jungsan</th>
			<th>dv_open_date</th>
			<th>dv_agent</th>
			<th>dv_number</th>
			<th>dv_model</th>
			<th>dv_model_number</th>
			<th>dv_sn</th>
			<th>dv_usim</th>
			<th>dv_usim_number</th>
			<th>dv_history</th>
			<th>sftp_mbrno</th>
			<th>updatetime</th>
			<th>datetime</th>
		</tr>
		*/ ?>
		</thead>
		<tbody>
		<tr>
			<td colspan="27">INSERT INTO `g5_device` (`dv_id`, `dv_tid`, `mb_1`, `mb_1_name`, `mb_1_fee`, `mb_2`, `mb_2_name`, `mb_2_fee`, `mb_3`, `mb_3_name`, `mb_3_fee`, `mb_4`, `mb_4_name`, `mb_4_fee`, `mb_5`, `mb_5_name`, `mb_5_fee`, `mb_6`, `mb_6_name`, `mb_6_fee`, `dv_pg`, `dv_type`, `dv_certi`, `dv_open_date`, `dv_agent`, `dv_number`, `dv_model`, `dv_model_number`, `dv_sn`, `dv_usim`, `dv_usim_number`, `dv_history`, `datetime`) VALUES </td>
		</tr>
		<?php
			for ($i=0; $row=sql_fetch_array($result); $i++) {
				$num = number_format($total_count - ($page - 1) * $rows - $i);

				if($row['mb_1'] == "06021382") {
					$mb_1 = "1675170777";
					$mb_1_name = "섹타나인(X)";
				} else {
					$mb_1 = "1707887466";
					$mb_1_name = "섹타나인(O)";
				}

		?>
		<?php /*
		<tr>
			<th rowspan="2"><?php echo $num; ?></th>
			<th><?php echo $row['a']; ?></th>
			<th><?php echo $row['b']; ?></th>
			<th><?php echo $row['dv_id']; ?></th>
			<th><?php echo $row['dv_tid']; ?></th>

			<th><?php echo $row['mb_1']; ?></th>
			<th><?php echo $row['mb_1_name']; ?></th>
			<th><?php echo $row['mb_1_fee']; ?></th>

			<th><?php echo $row['mb_2']; ?></th>
			<th><?php echo $row['mb_2_name']; ?></th>
			<th><?php echo $row['mb_2_fee']; ?></th>

			<th><?php echo $row['mb_3']; ?></th>
			<th><?php echo $row['mb_3_name']; ?></th>
			<th><?php echo $row['mb_3_fee']; ?></th>

			<th><?php echo $row['mb_4']; ?></th>
			<th><?php echo $row['mb_4_name']; ?></th>
			<th><?php echo $row['mb_4_fee']; ?></th>

			<th><?php echo $row['mb_5']; ?></th>
			<th><?php echo $row['mb_5_name']; ?></th>
			<th><?php echo $row['mb_5_fee']; ?></th>

			<th><?php echo $row['mb_6']; ?></th>
			<th><?php echo $row['mb_6_name']; ?></th>
			<th><?php echo $row['mb_6_fee']; ?></th>


			<th><?php echo $row['dv_pg']; ?></th>
			<th><?php echo $row['dv_type']; ?></th>
			<th><?php echo $row['dv_certi']; ?></th>
			<th><?php echo $row['dv_jungsan']; ?></th>
			<th><?php echo $row['dv_open_date']; ?></th>
			<th><?php echo $row['dv_agent']; ?></th>
			<th><?php echo $row['dv_number']; ?></th>
			<th><?php echo $row['dv_model']; ?></th>
			<th><?php echo $row['dv_model_number']; ?></th>
			<th><?php echo $row['dv_sn']; ?></th>
			<th><?php echo $row['dv_usim']; ?></th>
			<th><?php echo $row['dv_usim_number']; ?></th>
			<th><?php echo $row['dv_history']; ?></th>
			<th><?php echo $row['sftp_mbrno']; ?></th>
			<th><?php echo $row['updatetime']; ?></th>
			<th><?php echo $row['datetime']; ?></th>
		</tr>
		*/ ?>
		<tr>
			<td colspan="25">
			(
			'',
			'<?php echo $row['b']; ?>',
			'<?php echo $mb_1; ?>',
			'<?php echo $mb_1_name; ?>',
			'<?php echo $row['mb_1_fee']; ?>',
			'<?php echo $row['mb_2']; ?>',
			'<?php echo $row['mb_2_name']; ?>',
			'<?php echo $row['mb_2_fee']; ?>',
			'<?php echo $row['mb_3']; ?>',
			'<?php echo $row['mb_3_name']; ?>',
			'<?php echo $row['mb_3_fee']; ?>',
			'<?php echo $row['mb_4']; ?>',
			'<?php echo $row['mb_4_name']; ?>',
			'<?php echo $row['mb_4_fee']; ?>',
			'<?php echo $row['mb_5']; ?>',
			'<?php echo $row['mb_5_name']; ?>',
			'<?php echo $row['mb_5_fee']; ?>',
			'<?php echo $row['mb_6']; ?>',
			'<?php echo $row['mb_6_name']; ?>',
			'<?php echo $row['mb_6_fee']; ?>',
			'<?php echo $row['dv_pg']; ?>',
			'<?php echo $row['dv_type']; ?>',
			'<?php echo $row['dv_certi']; ?>',
			'<?php echo $row['dv_open_date']; ?>',
			'1032',
			'<?php echo $row['dv_number']; ?>',
			'<?php echo $row['dv_model']; ?>',
			'<?php echo $row['dv_model_number']; ?>',
			'<?php echo $row['dv_sn']; ?>',
			'<?php echo $row['dv_usim']; ?>',
			'<?php echo $row['dv_usim_number']; ?>',
			'<?php echo $row['dv_history']; ?>',
			''
			),</td>
		</tr>
		<?php } ?>
	</tbody>
</table>

<?php
	echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page=');
?>
