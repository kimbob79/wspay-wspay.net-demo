<?php
	$today = date("Ymd");

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
	$sql_fild .= ", count(if(a.pay_type = 'Y', a.pay_type, null)) as scnt "; // 승인 건수
	$sql_fild .= ", count(if(a.pay_type != 'Y', a.pay_type, null)) as ccnt "; // 취소 건수
	$sql_fild .= ", sum(if(a.pay_type = 'Y', a.pay, null)) as spay "; // 승인금액
	$sql_fild .= ", sum(if(a.pay_type != 'Y', a.pay, null)) as cpay "; // 취소금액
	$sql_fild .= ", sum(a.pay) as total_pay "; // 합계
	$sql_fild .= ", a.mb_1_name as mb1_name "; // 본사명
	$sql_fild .= ", a.mb_2_name as mb2_name "; // 본사명
	$sql_fild .= ", a.mb_3_name as mb3_name "; // 본사명
	$sql_fild .= ", a.mb_4_name as mb4_name "; // 본사명
	$sql_fild .= ", a.mb_5_name as mb5_name "; // 본사명
	$sql_fild .= ", a.mb_6_name as mb6_name "; // 본사명

	$sql_fild .= ", sum(if(a.pay_cdatetime = '0000-00-00 00:00:00', a.mb_1_pay,0)) as mb_1_pay "; // 본사 수수료
	$sql_fild .= ", sum(if(a.pay_cdatetime = '0000-00-00 00:00:00', a.mb_2_pay,0)) as mb_2_pay "; // 지사 수수료
	$sql_fild .= ", sum(if(a.pay_cdatetime = '0000-00-00 00:00:00', a.mb_3_pay,0)) as mb_3_pay "; // 총판 수수료
	$sql_fild .= ", sum(if(a.pay_cdatetime = '0000-00-00 00:00:00', a.mb_4_pay,0)) as mb_4_pay "; // 대리점 수수료
	$sql_fild .= ", sum(if(a.pay_cdatetime = '0000-00-00 00:00:00', a.mb_5_pay,0)) as mb_5_pay "; // 영업점 수수료
	$sql_fild .= ", sum(if(a.pay_cdatetime = '0000-00-00 00:00:00', a.mb_6_pay,0)) as mb_6_pay "; // 가맹점 정산액

	$sql_common = " from g5_payment a left join g5_member b on a.mb_6 = b.mb_id left join g5_device c on a.dv_tid = c.dv_tid ";


	if($is_admin) {

		if($redpay == "Y") {
			if($member['mb_id'] == "admin") {
				$sql_search = " where a.mb_1 IN (".adm_sql_common.")";
			} else  {
				$sql_search = " where a.mb_1 NOT IN (".adm_sql_common.")";
			}
		} else {
			$sql_search = " where (1)";
		}

		if($membera) {
			$sql_search .= " and a.mb_1 = '$membera' ";
		}
		if($memberb) {
			$sql_search .= " and a.mb_2 = '$memberb' ";
		}
		if($memberc) {
			$sql_search .= " and a.mb_3 = '$memberc' ";
		}
		if($memberd) {
			$sql_search .= " and a.mb_4 = '$memberd' ";
		}
		if($membere) {
			$sql_search .= " and a.mb_5 = '$membere' ";
		}

		if($mb_1_name) {
			$sql_search .= " and a.mb_1_name like '%{$mb_1_name}%' ";
		}
		if($mb_2_name) {
			$sql_search .= " and a.mb_2_name like '%{$mb_2_name}%' ";
		}
		if($mb_3_name) {
			$sql_search .= " and a.mb_3_name like '%{$mb_3_name}%' ";
		}
		if($mb_4_name) {
			$sql_search .= " and a.mb_4_name like '%{$mb_4_name}%' ";
		}
	} else if($member['mb_level'] == 8) {
		$sql_search = " where a.mb_1 = '{$member['mb_id']}'";
	} else if($member['mb_level'] == 7) {
		$sql_search = " where a.mb_2 = '{$member['mb_id']}'";
	} else if($member['mb_level'] == 6) {
		$sql_search = " where a.mb_3 = '{$member['mb_id']}'";
	} else if($member['mb_level'] == 5) {
		$sql_search = " where a.mb_4 = '{$member['mb_id']}'";
	} else if($member['mb_level'] == 4) {
		$sql_search = " where a.mb_5 = '{$member['mb_id']}'";
	} else if($member['mb_level'] == 3) {
		$sql_search = " where a.mb_6 = '{$member['mb_id']}'";
	}
	
	$sql_search .= " and (a.pay_datetime BETWEEN '{$fr_dates} 00:00:00' and '{$to_dates} 23:59:59') ";
	

	if($dv_tid) {
		$sql_search .= " and (a.dv_tid like '%{$dv_tid}%') ";
	}

	if($mb_6_name) {
		$sql_search .= " and (a.mb_6_name like '%{$mb_6_name}%') ";
	}

	if ($sst)
		$sql_order = " order by {$sst} {$sod} ";
	else
		$sql_order = " order by seq desc ";


	$sql = " select {$sql_fild} {$sql_common} {$sql_search} group by a.dv_tid having a.pay <> 0 ORDER BY a.mb_1, a.mb_2, a.mb_3, a.mb_4, a.mb_5, a.mb_6 asc";
	$result = sql_query($sql);
//	echo $member['mb_type']."<br>";
//	echo $sql;
?>

<style>
</style>

<div class="KDC_Content__root__tP6yD KDC_Content__responsive__4kCaB CWLNB">
	<div class="KDC_Tabs__root__JmfCY PeriodButtonGroup_root__UE4Kj">
		<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
			<input type="hidden" name="p" value="<?php echo $p; ?>">

			<div class="KDC_Tab__root__h2hVQ tab type_other input_date search_br">
				<input type="text" name="fr_date" value="<?php echo $fr_date ?>" id="fr_date" class="KDC_Input__root__3M8Hf KDC_Input__root__3M8Hf_date" style="width:80px;" maxlength="10">
			</div>
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date">
				<input type="text" name="to_date" value="<?php echo $to_date ?>" id="to_date" class="KDC_Input__root__3M8Hf KDC_Input__root__3M8Hf_date" style="width:80px;" maxlength="10">
			</div>

			<?php if($is_admin) { ?>
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date" style="width:100px;">
			<select name="membera" id="membera" style="border:0;background:#fff;width:90%;">
				<option value="">본사선택</option>
				<?php
					$sql_g = " select * from g5_member where mb_level = '8' order by mb_nick";
					echo $sql_g;
					$result_g = sql_query($sql_g);
					for ($k=0; $row_g=sql_fetch_array($result_g); $k++) {
					?>
					<option value="<?php echo $row_g['mb_id']; ?>" <?php if($membera == $row_g['mb_id']) { echo "selected"; } ?>><?php echo $row_g['mb_nick']; ?></option>
					<?php
					}
				?>
			</select>
			</div>
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date" style="width:100px;">
			<select name="memberb" id="memberb" style="border:0;background:#fff;width:90%;">
				<option value="">지사선택</option>
				<?php
					$sql_g = " select * from g5_member where mb_level = '7' order by mb_nick";
					$result_g = sql_query($sql_g);
					for ($k=0; $row_g=sql_fetch_array($result_g); $k++) {
					?>
					<option value="<?php echo $row_g['mb_id']; ?>" <?php if($memberb == $row_g['mb_id']) { echo "selected"; } ?>><?php echo $row_g['mb_nick']; ?></option>
					<?php
					}
				?>
			</select>
			</div>
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date" style="width:100px;">
			<select name="memberc" id="memberc" style="border:0;background:#fff;width:90%;">
				<option value="">총판선택</option>
				<?php
					$sql_g = " select * from g5_member where mb_level = '6' order by mb_nick";
					$result_g = sql_query($sql_g);
					for ($k=0; $row_g=sql_fetch_array($result_g); $k++) {
					?>
					<option value="<?php echo $row_g['mb_id']; ?>" <?php if($memberc == $row_g['mb_id']) { echo "selected"; } ?>><?php echo $row_g['mb_nick']; ?></option>
					<?php
					}
				?>
			</select>
			</div>
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date" style="width:100px;">
			<select name="memberd" id="memberd" style="border:0;background:#fff;width:90%;">
				<option value="">대리점선택</option>
				<?php
					$sql_g = " select * from g5_member where mb_level = '5' order by mb_nick";
					$result_g = sql_query($sql_g);
					for ($k=0; $row_g=sql_fetch_array($result_g); $k++) {
					?>
					<option value="<?php echo $row_g['mb_id']; ?>" <?php if($memberd == $row_g['mb_id']) { echo "selected"; } ?>><?php echo $row_g['mb_nick']; ?></option>
					<?php
					}
				?>
			</select>
			</div>
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date" style="width:100px;">
			<select name="membere" id="membere" style="border:0;background:#fff;width:90%;">
				<option value="">영업점선택</option>
				<?php
					$sql_g = " select * from g5_member where mb_level = '4' order by mb_nick";
					$result_g = sql_query($sql_g);
					for ($k=0; $row_g=sql_fetch_array($result_g); $k++) {
					?>
					<option value="<?php echo $row_g['mb_id']; ?>" <?php if($membere == $row_g['mb_id']) { echo "selected"; } ?>><?php echo $row_g['mb_nick']; ?></option>
					<?php
					}
				?>
			</select>
			</div>
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date">
				<input type="text" name="mb_6_name" value="<?php echo $mb_6_name; ?>" id="mb_6_name" class="KDC_Input__root__3M8Hf KDC_Input__root__3M8Hf_date" style="width:100px;" placeholder="가맹점명">
			</div>
			<?php /*
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date" style="width:100px;">
			<select name="memberf" id="memberf" style="border:0;background:#fff;width:90%;">
				<option value="">가맹점선택</option>
				<?php
					$sql_g = " select * from g5_member where mb_level = '3' order by mb_nick";
					$result_g = sql_query($sql_g);
					for ($k=0; $row_g=sql_fetch_array($result_g); $k++) {
					?>
					<option value="<?php echo $row_g['mb_id']; ?>" <?php if($memberf == $row_g['mb_id']) { echo "selected"; } ?>><?php echo $row_g['mb_nick']; ?></option>
					<?php
					}
				?>
			</select>
			</div>
			*/ ?>
			<?php } ?>

			<div class="KDC_Tab__root__h2hVQ tab type_other  search_br"><div class="KDC_Tab__text__VzW9X" onclick="set_date('오늘');">오늘</div></div>
			<div class="KDC_Tab__root__h2hVQ tab type_other"><div class="KDC_Tab__text__VzW9X" onclick="set_date('어제');">어제</div></div>
			<div class="KDC_Tab__root__h2hVQ tab type_other"><div class="KDC_Tab__text__VzW9X" onclick="set_date('이번주');">이번주</div></div>
			<div class="KDC_Tab__root__h2hVQ tab type_other"><div class="KDC_Tab__text__VzW9X" onclick="set_date('이번달');">이번달</div></div>
			<div class="KDC_Tab__root__h2hVQ tab type_other"><div class="KDC_Tab__text__VzW9X" onclick="set_date('지난주');">지난주</div></div>
			<div class="KDC_Tab__root__h2hVQ tab type_other"><div class="KDC_Tab__text__VzW9X" onclick="set_date('지난달');">지난달</div></div>
			<div class="KDC_Tab__root__h2hVQ tab tab_selected type_other"><input type="submit" class="KDC_Tab__text__VzW9X" value="검색" style="background:#444; width:100%; border:0; color:#fff;"></div>
			<div class="KDC_Tab__root__h2hVQ tab tab_selected type_other"><a href="./settlement.xls.php?fr_date=<?php echo $fr_date; ?>&to_date=<?php echo $to_date; ?>&membera=<?php echo $membera; ?>&memberb=<?php echo $memberb; ?>&memberc=<?php echo $memberc; ?>&memberd=<?php echo $memberd; ?>&membere=<?php echo $membere; ?>&memberf=<?php echo $memberf; ?>" class="KDC_Tab__text__VzW9X" style="background:#444; width:100%; border:0; color:#fff;"><span><i class="fa fa-file-excel-o" aria-hidden="true"></i> 엑셀출력</span></a></div>
			
		</form>
	</div>



	<div class="KDC_Row__root__uio5h KDC_Row__responsive__obNwV">
		<div class="KDC_Column__root__NK8XY KDC_Column__flex_1__UcocY">
			<?php /*
			<h1 class="table_title">가맹점</h1>
			*/ ?>
			<div class="KDC_Section__root__VXHOv">
				<table class="KDC_Table__root__Jim4z">
				<thead>
				<tr>
					<th>가맹점명</th>
					<th>승인</th>
					<th>취소</th>
					<th style="width:5%">승인금액</th>
					<th style="width:5%">취소금액</th>
					<th style="width:5%">총금액</th>
					<?php if($is_admin) { ?>
					<th style="width:7%; min-width:130px;">PG</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 8) { ?>
					<th style="width:7%; min-width:140px;">본사</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 7) { ?>
					<th style="width:7%; min-width:140px;">지사</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 6) { ?>
					<th style="width:7%; min-width:140px;">총판</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 5) { ?>
					<th style="width:7%; min-width:140px;">대리점</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 4) { ?>
					<th style="width:7%; min-width:140px;">영업점</th>
					<?php } ?>
					<th style="text-align:right; width:6.5%; min-width:100px;">가맹점</th>
					<?php if($is_admin) { ?>
					<th style="width:10%;">계좌정보</th>
					<?php } ?>
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
					/*
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
					*/

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
					<td class="td_name"><?php echo $row['mb_6_name']; ?><br><span class='tid'><?php echo $row['dv_tid']; ?></span></td>

					<td><?php echo $row['scnt']; ?></td>
					<td><?php echo $row['ccnt']; ?></td>
					<td style="text-align:right"><?php echo number_format($row['spay']); ?></td>
					<td style="text-align:right"><?php echo number_format($row['cpay']); ?></td>
					<td style="text-align:right"><?php echo number_format($total_pay); ?></td>
					<?php if($is_admin) { ?>
					<td style="text-align:right"><?php echo number_format($pg_pay)."<br><div class='fee_left'><span class='fee1'>패스고</span></div><div class='fee_right'><span class='fee3'>3.74%</span></span></div>"; ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 8) { ?>
					<td style="text-align:right"><?php if($row['mb_1_name']) { echo number_format($mb_1_pay)."<br><div class='fee_left'><span class='fee1'>".utf8_strcut($row['mb_1_name'],6)." / ".$row['mb_1_fee']."%</span></div><div class='fee_right'><span class='fee3'>".$mb_1_fee."%</span></div>"; } ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 7) { ?>
					<td style="text-align:right"><?php if($row['mb_2_name']) { echo number_format($mb_2_pay)."<br><div class='fee_left'><span class='fee1'>".utf8_strcut($row['mb_2_name'],6)." / ".$row['mb_2_fee']."%</span></div><div class='fee_right'><span class='fee3'>".$mb_2_fee."%</span></div>"; } ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 6) { ?>
					<td style="text-align:right"><?php if($row['mb_3_name']) { echo number_format($mb_3_pay)."<br><div class='fee_left'><span class='fee1'>".utf8_strcut($row['mb_3_name'],6)." / ".$row['mb_3_fee']."%</span></div><div class='fee_right'><span class='fee3'>".$mb_3_fee."%</span></div>"; } ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 5) { ?>
					<td style="text-align:right"><?php if($row['mb_4_name']) { echo number_format($mb_4_pay)."<br><div class='fee_left'><span class='fee1'>".utf8_strcut($row['mb_4_name'],6)." / ".$row['mb_4_fee']."%</span></div><div class='fee_right'><span class='fee3'>".$mb_4_fee."%</span></div>"; } ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 4) { ?>
					<td style="text-align:right"><?php if($row['mb_5_name']) { echo number_format($mb_5_pay)."<br><div class='fee_left'><span class='fee1'>".utf8_strcut($row['mb_5_name'],6)." / ".$row['mb_5_fee']."%</span></div>"; } ?></td>
					<?php } ?>


					<td style="text-align:right"><?php if($row['mb_6_name']) { echo number_format($mb_6_pay)."<br><div class='fee_left'><span class='fee1'>".$row['mb_6_fee']."%</span></div><div class='fee_right'><span class='fee3'>".$mb_6_fee."%</span></div>"; } ?></td>

					<?php if($is_admin) { ?>
					<td style="text-align:right;white-space: normal;"><?php if($row['mb_8']) { echo $row['mb_8']; ?> <?php echo $row['mb_9']; ?> <?php echo $row['mb_10']; } else { echo "-"; } ?></td>
					<?php } ?>


				</tr>
				<?php } ?>
				</tbody>
				<tfoot>
				<tr style="border-top:1px solid #e5e5e5;">
					<th>가맹점명</th>
					<th>승인</th>
					<th>취소</th>
					<th style="width:5%">승인금액</th>
					<th style="width:5%">취소금액</th>
					<th style="width:5%">총금액</th>
					<?php if($is_admin) { ?>
					<th style="width:6%">PG</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 8) { ?>
					<th style="width:7%">본사</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 7) { ?>
					<th style="width:7%">지사</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 6) { ?>
					<th style="width:7%">총판</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 5) { ?>
					<th style="width:7%">대리점</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 4) { ?>
					<th style="width:7%">영업점</th>
					<?php } ?>
					<th style="text-align:right; width:6.5%">가맹점</th>
					<?php if($is_admin) { ?>
					<th style="width:10%;">계좌정보</th>
					<?php } ?>
				</tr>
				<tr>
					<td>합계</td>
					<td><?php echo number_format($scnt_total); ?></td>
					<td><?php echo number_format($ccnt_total); ?></td>

					<td style="text-align:right"><?php echo number_format($spay_total); ?></td>
					<td style="text-align:right"><?php echo number_format($cpay_total); ?></td>
					<td style="text-align:right"><?php echo number_format($total_pay_total); ?></td>
					<?php if($is_admin) { ?>
					<td style="text-align:right"><?php echo  number_format($pg_total); ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 8) { ?>
					<td style="text-align:right"><?php echo number_format($mb_pay1_total); ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 7) { ?>
					<td style="text-align:right"><?php echo number_format($mb_pay2_total); ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 6) { ?>
					<td style="text-align:right"><?php echo number_format($mb_pay3_total); ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 5) { ?>
					<td style="text-align:right"><?php echo number_format($mb_pay4_total); ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 4) { ?>
					<td style="text-align:right"><?php echo number_format($mb_pay5_total); ?></td>
					<?php } ?>
					<td style="text-align:right"><?php echo number_format($mb_pay6_total); ?></td>
					<?php if($is_admin) { ?>
					<td colspan="3"></td>
					<?php } ?>
				</tr>
				</tfoot>
				</table>
			</div>
			<div style="height:10px;"></div>

			<?php if($is_admin) { ?>

			<?php
				$sql = " select {$sql_fild} {$sql_common} {$sql_search} and a.mb_1 != '' group by a.mb_1 having a.pay <> 0 ORDER BY a.mb_1, a.mb_2, a.mb_3, a.mb_4, a.mb_5, a.mb_6 asc";
				$result = sql_query($sql);
			//	echo $member['mb_type']."<br>";
			//	echo $sql;
			?>
			<h1 class="table_title">본사</h1>
			<div class="KDC_Section__root__VXHOv">
				<table class="KDC_Table__root__Jim4z">
				<thead>
				<tr>
					<th>업체명</th>
					<th style="width:7%">승 / 취</th>
					<th style="width:17%">승인금액</th>
					<th style="width:17%">취소금액</th>
					<th style="width:17%">총금액</th>
					<?php /*
					<th style="width:5%">수수료</th>
					*/ ?>
					<th style="width:17%">정산액</th>
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

					for ($i=0; $row=sql_fetch_array($result); $i++) {


					$scnt_total = $scnt_total + $row['scnt'];
					$ccnt_total = $ccnt_total + $row['ccnt'];
					$spay_total = $spay_total + $row['spay'];
					$cpay_total = $cpay_total + $row['cpay'];

					$total_pay_total = $spay_total - $cpay_total;
					$total_pay = $row['spay'] - $row['cpay'];

				?>
				<tr>
					<td style="border-right: 1px solid #eee; color:#4d4dff; font-weight:700; text-align:left"><?php echo $row['mb1_name']; ?></td>

					<td><?php echo $row['scnt']; ?> / <?php echo $row['ccnt']; ?></td>
					<td style="text-align:right"><?php echo number_format($row['spay']); ?></td>
					<td style="text-align:right"><?php echo number_format($row['cpay']); ?></td>
					<td style="text-align:right"><?php echo number_format($total_pay); ?></td>
					<?php /*
					<td><?php if($row['mb_1_name']) { echo $row['mb_1_fee']; } ?></td>
					*/ ?>
					<td style="text-align:right; font-weight:bold"><?php if($row['mb_1_name']) { echo number_format($row['mb_1_pay']); } ?></td>
				</tr>
				<?php } ?>
				</tbody>
				<tfoot>
				<?php
					if ($i == 0) {
						echo '<tr><td colspan="6" style="height:100px; background:#eee; color:#888; line-height:100px;">본사 내역이 없습니다.</td></tr>';
					}
				?>
				</tfoot>
				</table>
			</div>
			<div style="height:10px;"></div>

			<?php
				$sql = " select {$sql_fild} {$sql_common} {$sql_search} and a.mb_2 != '' group by a.mb_2 having a.pay <> 0 ORDER BY a.mb_1, a.mb_2, a.mb_3, a.mb_4, a.mb_5, a.mb_6 asc";
				$result = sql_query($sql);
			//	echo $member['mb_type']."<br>";
			//	echo $sql;
			?>

			<h1 class="table_title">지사</h1>
			<div class="KDC_Section__root__VXHOv">
				<table class="KDC_Table__root__Jim4z">
				<thead>
				<tr>
					<th>업체명</th>
					<th style="width:7%">승 / 취</th>
					<th style="width:17%">승인금액</th>
					<th style="width:17%">취소금액</th>
					<th style="width:17%">총금액</th>
					<?php /*
					<th style="width:5%">수수료</th>
					*/ ?>
					<th style="width:17%">정산액</th>
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

				?>
				<tr>
					<td style="border-right: 1px solid #eee; color:#4d4dff; font-weight:700; text-align:left"><?php echo $row['mb2_name']; ?></td>

					<td><?php echo $row['scnt']; ?> / <?php echo $row['ccnt']; ?></td>
					<td style="text-align:right"><?php echo number_format($row['spay']); ?></td>
					<td style="text-align:right"><?php echo number_format($row['cpay']); ?></td>
					<td style="text-align:right"><?php echo number_format($total_pay); ?></td>
					<?php /*
					<td><?php if($row['mb_2_name']) { echo $row['mb_2_fee']; } ?></td>
					*/ ?>
					<td style="text-align:right; font-weight:bold"><?php if($row['mb_2_name']) { echo number_format($row['mb_2_pay']); } ?></td>
				</tr>
				<?php } ?>
				</tbody>
				<tfoot>
				<?php
					if ($i == 0) {
						echo '<tr><td colspan="6" style="height:100px; background:#eee; color:#888; line-height:100px;">지사 내역이 없습니다.</td></tr>';
					}
				?>
				</tfoot>
				</table>
			</div>
			<div style="height:10px;"></div>

			<?php
				$sql = " select {$sql_fild} {$sql_common} {$sql_search} and a.mb_3 != '' group by a.mb_3 having a.pay <> 0 ORDER BY a.mb_1, a.mb_2, a.mb_3, a.mb_4, a.mb_5, a.mb_6 asc";
				$result = sql_query($sql);
			//	echo $member['mb_type']."<br>";
			//	echo $sql;
			?>

			<h1 class="table_title">총판</h1>
			<div class="KDC_Section__root__VXHOv">
				<table class="KDC_Table__root__Jim4z">
				<thead>
				<tr>
					<th>업체명</th>
					<th style="width:7%">승 / 취</th>
					<th style="width:17%">승인금액</th>
					<th style="width:17%">취소금액</th>
					<th style="width:17%">총금액</th>
					<?php /*
					<th style="width:5%">수수료</th>
					*/ ?>
					<th style="width:17%">정산액</th>
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

				?>
				<tr>
					<td style="border-right: 1px solid #eee; color:#4d4dff; font-weight:700; text-align:left"><?php echo $row['mb3_name']; ?></td>

					<td><?php echo $row['scnt']; ?> / <?php echo $row['ccnt']; ?></td>
					<td style="text-align:right"><?php echo number_format($row['spay']); ?></td>
					<td style="text-align:right"><?php echo number_format($row['cpay']); ?></td>
					<td style="text-align:right"><?php echo number_format($total_pay); ?></td>
					<?php /*
					<td><?php if($row['mb_3_name']) { echo $row['mb_3_fee']; } ?></td>
					*/ ?>
					<td style="text-align:right; font-weight:bold"><?php if($row['mb_3_name']) { echo number_format($row['mb_3_pay']); } ?></td>
				</tr>
				<?php } ?>
				</tbody>
				<tfoot>
				<?php
					if ($i == 0) {
						echo '<tr><td colspan="6" style="height:100px; background:#eee; color:#888; line-height:100px;">총판 내역이 없습니다.</td></tr>';
					}
				?>
				</tfoot>
				</table>
			</div>
			<div style="height:10px;"></div>

			<?php
				$sql = " select {$sql_fild} {$sql_common} {$sql_search} and a.mb_4 != '' group by a.mb_4 having a.pay <> 0 ORDER BY a.mb_1, a.mb_2, a.mb_3, a.mb_4, a.mb_5, a.mb_6 asc";
				$result = sql_query($sql);
			//	echo $member['mb_type']."<br>";
			//	echo $sql;
			?>

			<h1 class="table_title">대리점</h1>
			<div class="KDC_Section__root__VXHOv">
				<table class="KDC_Table__root__Jim4z">
				<thead>
				<tr>
					<th>업체명</th>
					<th style="width:7%">승 / 취</th>
					<th style="width:17%">승인금액</th>
					<th style="width:17%">취소금액</th>
					<th style="width:17%">총금액</th>
					<?php /*
					<th style="width:5%">수수료</th>
					*/ ?>
					<th style="width:17%">정산액</th>
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

				?>
				<tr>
					<td style="border-right: 1px solid #eee; color:#4d4dff; font-weight:700; text-align:left"><?php echo $row['mb4_name']; ?></td>

					<td><?php echo $row['scnt']; ?> / <?php echo $row['ccnt']; ?></td>
					<td style="text-align:right"><?php echo number_format($row['spay']); ?></td>
					<td style="text-align:right"><?php echo number_format($row['cpay']); ?></td>
					<td style="text-align:right"><?php echo number_format($total_pay); ?></td>
					<?php /*
					<td><?php if($row['mb_4_name']) { echo $row['mb_4_fee']; } ?></td>
					*/ ?>
					<td style="text-align:right; font-weight:bold"><?php if($row['mb_4_name']) { echo number_format($row['mb_4_pay']); } ?></td>
				</tr>
				<?php } ?>
				</tbody>
				<tfoot>
				<?php
					if ($i == 0) {
						echo '<tr><td colspan="6" style="height:100px; background:#eee; color:#888; line-height:100px;">대리점 내역이 없습니다.</td></tr>';
					}
				?>
				</tfoot>
				</table>
			</div>
			<div style="height:10px;"></div>

			<?php
				$sql = " select {$sql_fild} {$sql_common} {$sql_search} and a.mb_5 != '' group by a.mb_5 having a.pay <> 0 ORDER BY a.mb_1, a.mb_2, a.mb_3, a.mb_4, a.mb_5, a.mb_6 asc";
				$result = sql_query($sql);
			//	echo $member['mb_type']."<br>";
			//	echo $sql;
			?>
			<h1 class="table_title">영업점</h1>
			<div class="KDC_Section__root__VXHOv">
				<table class="KDC_Table__root__Jim4z">
				<thead>
				<tr>
					<th>업체명</th>
					<th style="width:7%">승 / 취</th>
					<th style="width:17%">승인금액</th>
					<th style="width:17%">취소금액</th>
					<th style="width:17%">총금액</th>
					<?php /*
					<th style="width:5%">수수료</th>
					*/ ?>
					<th style="width:17%">정산액</th>
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

				?>
				<tr>
					<td style="border-right: 1px solid #eee; color:#4d4dff; font-weight:700; text-align:left"><?php echo $row['mb5_name']; ?></td>
					<td><?php echo $row['scnt']; ?> / <?php echo $row['ccnt']; ?></td>
					<td style="text-align:right"><?php echo number_format($row['spay']); ?></td>
					<td style="text-align:right"><?php echo number_format($row['cpay']); ?></td>
					<td style="text-align:right"><?php echo number_format($total_pay); ?></td>
					<?php /*
					<td><?php if($row['mb_5_name']) { echo $row['mb_5_fee']; } ?></td>
					*/ ?>
					<td style="text-align:right; font-weight:bold"><?php if($row['mb_5_name']) { echo number_format($row['mb_5_pay']); } ?></td>
				</tr>
				<?php } ?>
				</tbody>
				<tfoot>
				<?php
					if ($i == 0) {
						echo '<tr><td colspan="6" style="height:100px; background:#eee; color:#888; line-height:100px;">영업점 내역이 없습니다.</td></tr>';
					}
				?>
				</tfoot>
				</table>
			</div>
			<?php } ?>

		</div>
	</div>
</div>
<div class="page_wrap"><div class="page_nation"></div></div>









<div id="modal-root" class="mobile" style="display:none">
	<div>
		<div class="KDC_Dialog__dimmed__9s78c">
			<div class="KDC_Dialog__root__Ak0Ip">
				<div class="KDC_Dialog__inner__13gmt">
					<div class="KDC_Dialog__container__Ps9E+">
						<div class="KDC_Dialog__header__dgmvO">
							<strong class="tit_layer">기본 정보</strong>
						</div>
						<form>
							<div>
								<ul class="KDC_ListLayout__root__FwMvz FormListLayout_custom__5+ua5 KDC_ListLayout__write__9gvuI">
									<li class="KDC_ListLayout__item__mMzWn">
									<div class="tit_info">
										<label class="lab_normal">앱 아이콘</label>
									</div>
									<div class="KDC_ListLayout__content__OEd-3">
										<div class="KDC_IconInput__root__RBhky">
											<div class="box_thumb" role="presentation" tabindex="0">
												<img class="KDC_Image__root__z8jAm" src="https://k.kakaocdn.net/14/dn/btqvX1CL6kz/sSBw1mbWkyZTkk1Mpt9nw1/o.jpg" width="90" height="90" alt="">
											</div>
											<div class="box_file">
												<input accept="image/*,.jpeg,.jpg,.gif,.png" multiple="" type="file" tabindex="-1" name="icon_image_kage_token" style="display: none;"><button type="button" class="lab_file show_pc" role="presentation" tabindex="0">파일 선택</button>
												<div class="txt_file">
													JPG, GIF, PNG<br>
													권장 사이즈 128px, 최대 250KB
												</div>
											</div>
										</div>
									</div>
									</li>
									<li class="KDC_ListLayout__item__mMzWn">
									<div class="tit_info">
										<label for="name_2" class="lab_normal">앱 이름</label>
									</div>
									<div class="KDC_ListLayout__content__OEd-3">
										<input name="name" type="text" id="name_2" class="KDC_Input__root__3M8Hf" placeholder="내 애플리케이션 이름" value="지도">
									</div>
									</li>
									<li class="KDC_ListLayout__item__mMzWn">
									<div class="tit_info">
										<label for="company_3" class="lab_normal">사업자명</label>
									</div>
									<div class="KDC_ListLayout__content__OEd-3">
										<input name="company" type="text" id="company_3" class="KDC_Input__root__3M8Hf" placeholder="사업자 정보와 동일한 이름" value="지도">
									</div>
									</li>
								</ul>
								<ul class="KDC_Infos__root__ixod2 info_type2 dot">
									<li>입력된 정보는 사용자가 카카오 로그인을 할 때 표시됩니다.</li>
									<li>정보가 정확하지 않은 경우 서비스 이용이 제한될 수 있습니다.</li>
								</ul>
							</div>
							<div class="KDC_Dialog__footer__ZGOFE">
								<div class="KDC_ButtonGroup__root__wZnpQ">
									<button type="button" class="KDC_Button__root__N26ep KDC_Button__normal_narrow__JNy0x KDC_Button__color_cancel__TdcOV">취소</button><button type="submit" disabled="" class="KDC_Button__root__N26ep KDC_Button__normal_narrow__JNy0x">저장</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>






<script>
	$("#membera, #memberb, #memberc, #memberd, #membere, #memberf").click(function() {
		$("#membera, #memberb, #memberc, #memberd, #membere, #memberf").find('option:first').attr('selected', 'selected');
	});

	$("#membera, #memberb, #memberc, #memberd, #membere, #memberf").change(function() {
		$("#membera, #memberb, #memberc, #memberd, #membere, #memberf").val();
		$(this).parents().filter("form").submit();
	});
</script>