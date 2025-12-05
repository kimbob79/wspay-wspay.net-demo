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
	$sql_fild .= ", a.mb_1_fee as mb_1_fee "; // 본사 수수료
	$sql_fild .= ", a.mb_2_fee as mb_2_fee "; // 본사 수수료
	$sql_fild .= ", a.mb_3_fee as mb_3_fee "; // 본사 수수료
	$sql_fild .= ", a.mb_4_fee as mb_4_fee "; // 본사 수수료
	$sql_fild .= ", a.mb_5_fee as mb_5_fee "; // 본사 수수료
	$sql_fild .= ", a.mb_6_fee as mb_6_fee "; // 본사 수수료

	$sql_fild .= ", sum(a.mb_1_pay) as mb_1_pay "; // 본사 수수료
	$sql_fild .= ", sum(a.mb_2_pay) as mb_2_pay "; // 지사 수수료
	$sql_fild .= ", sum(a.mb_3_pay) as mb_3_pay "; // 총판 수수료
	$sql_fild .= ", sum(a.mb_4_pay) as mb_4_pay "; // 대리점 수수료
	$sql_fild .= ", sum(a.mb_5_pay) as mb_5_pay "; // 영업점 수수료
	$sql_fild .= ", sum(a.mb_6_pay) as mb_6_pay "; // 가맹점 정산액


	$sql_common = " from g5_payment a left join g5_member b on a.mb_6 = b.mb_id left join g5_device c on a.dv_tid = c.dv_tid ";


	if($is_admin) {

		if(adm_sql_common) {
			$sql_search = " where a.mb_1 IN (".adm_sql_common.")";
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

	if($dv_type) {
		$sql_search .= " and a.dv_type = '{$dv_type}' ";
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

td span { /*font-family: 'FFF-Reaction-Trial';*/ font-size:11px;}
td span .fee_name {font-family: 'NanumGothic';}

.fee_left {float:left;}
.fee_right {float:right;}

.fee { color:#999}
.tid {font-weight:300; color:#999}
select { width:100px; }
.table_title {font-size:13px; margin:0 0 10px 5px; font-weight:700; color:#555}
</style>


<div class="index_menu">
	<ul class="shortcut">
		<li class="sc_current"><a>실시간 정산조회</a></li>
	</ul>
</div>


<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<input type="hidden" name="p" value="<?php echo $p; ?>">
	<div class="searchbox">
		<div class="midd">
			<ul>
				<li>
					<strong>일자</strong>
					<div>
						<div>
							<input type="text" name="fr_date" value="<?php echo $fr_date ?>" id="fr_date" class="frm_input" size="6" maxlength="10">
						</div>
						<span>~</span>
						<div>
							<input type="text" name="to_date" value="<?php echo $to_date ?>" id="to_date" class="frm_input" size="6" maxlength="10">
						</div>
					</div>
				</li>
				<li>
					<strong>단축</strong>
					<div>
						<button type="submit" onclick="javascript:set_date('오늘');" class="btn_b btn_b09"><span>오늘</span></button>
						<button type="submit" onclick="javascript:set_date('어제');" class="btn_b btn_b09"><span>어제</span></button>
						<button type="submit" onclick="javascript:set_date('이번주');" class="btn_b btn_b09"><span>이번주</span></button>
						<button type="submit" onclick="javascript:set_date('이번달');" class="btn_b btn_b09"><span>이번달</span></button>
						<button type="submit" onclick="javascript:set_date('지난주');" class="btn_b btn_b09"><span>지난주</span></button>
						<button type="submit" onclick="javascript:set_date('지난달');" class="btn_b btn_b09"><span>지난달</span></button>
					</div>
				</li>
				<?php if($is_admin) { ?>
				<li>
					<strong>선택</strong>
					<div>
						<div data-skin="select">
							<select name="memberb" id="memberb" style="width:70px;">
								<option value="">지사</option>
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
							<select name="memberc" id="memberc" style="width:70px;">
								<option value="">총판</option>
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
							<select name="memberd" id="memberd" style="width:70px;">
								<option value="">대리점</option>
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
							<select name="membere" id="membere" style="width:70px;">
								<option value="">영업점</option>
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
					</div>
				</li>
				<?php } ?>
				<li>
					<strong>검색</strong>
					<div>
						<input type="text" name="mb_6_name" value="<?php echo $mb_6_name ?>" id="mb_6_name" class="frm_input" size="7" placeholder="가맹점명" style="width:150px;">
						<button type="submit" class="btn_b btn_b02"><span>검색</span></button>
					</div>
				</li>
				<li>
					<strong>타입</strong>
					<div data-skin="select">
						<label style="margin-right:10px"><input type="radio" name="dv_type" value="" id="dv_type" <?php if(!$dv_type) { echo "checked"; } ?>> 전체</label> 
						<label style="margin-right:10px"><input type="radio" name="dv_type" value="2" id="dv_type" <?php if($dv_type == "2") { echo "checked"; } ?>> 수기</label> 
						<label><input type="radio" name="dv_type" value="1" id="dv_type" <?php if($dv_type == "1") { echo "checked"; } ?>> 단말기</label>
					</div>
				</li>
				<li>
					<strong>수수료</strong>
					<div>
						<span class="fee1" style="letter-spacing: -1px;">설정 수수료</span>
						<span class="fee2" style="letter-spacing: -1px;">수익 수수료</span>
					</div>
				</li>
			</ul>
		</div>
	</div>
</form>




<div class="m_board_scroll">
	<div class="m_table_wrap">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
				<tr>
					<th>TID</th>
					<th>가맹점명</th>
					<th>승인금액</th>
					<th>취소금액</th>
					<th>총금액</th>
					<?php if($member['mb_level'] >= 8) { ?>
					<th>본사</th>
					<th>수익금</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 7) { ?>
					<th>지사</th>
					<th>수익금</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 6) { ?>
					<th>총판</th>
					<th>수익금</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 5) { ?>
					<th>대리점</th>
					<th>수익금</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 4) { ?>
					<th>영업점</th>
					<th>수익금</th>
					<?php } ?>

					<th>정산금액</th>

					<?php if($is_admin) { ?>
					<th>수수료 합계</th>
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

					$mb_1_pay = $row['mb_1_pay'];
					$mb_2_pay = $row['mb_2_pay'];
					$mb_3_pay = $row['mb_3_pay'];
					$mb_4_pay = $row['mb_4_pay'];
					$mb_5_pay = $row['mb_5_pay'];
					$mb_6_pay = $row['mb_6_pay'];

					$total_pay = $row['spay'] + $row['cpay']; // 총 승인금액 (승인-취소)

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

					$mb_1_fee = sprintf('%0.4f', $mb_1_fee);
					$mb_2_fee = sprintf('%0.4f', $mb_2_fee);
					$mb_3_fee = sprintf('%0.4f', $mb_3_fee);
					$mb_4_fee = sprintf('%0.4f', $mb_4_fee);
					$mb_5_fee = sprintf('%0.4f', $mb_5_fee);
					/*
					if($row['mb_1_name']) { $mb_1_pay = $mb_1_fee * $total_pay / 100; } else { $mb_1_pay = 0; }
					if($row['mb_2_name']) { $mb_2_pay = $mb_2_fee * $total_pay / 100; } else { $mb_2_pay = 0; }
					if($row['mb_3_name']) { $mb_3_pay = $mb_3_fee * $total_pay / 100; } else { $mb_3_pay = 0; }
					if($row['mb_4_name']) { $mb_4_pay = $mb_4_fee * $total_pay / 100; } else { $mb_4_pay = 0; }
					if($row['mb_5_name']) { $mb_5_pay = $mb_5_fee * $total_pay / 100; } else { $mb_5_pay = 0; }
					if($row['mb_6_name']) {
						$mb_6_pay = $row['mb_6_fee'] * $total_pay / 100;
						$mb_6_pay = $total_pay + $mb_6_pay;
					} else {
						$mb_6_pay = 0;
					}
					*/

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
					$total_pay_total = $spay_total + $cpay_total;

					$pg_total = $pg_total + $pg_pay;

					$mb_pay1_total = $mb_pay1_total + $mb_1_pay;
					$mb_pay2_total = $mb_pay2_total + $mb_2_pay;
					$mb_pay3_total = $mb_pay3_total + $mb_3_pay;
					$mb_pay4_total = $mb_pay4_total + $mb_4_pay;
					$mb_pay5_total = $mb_pay5_total + $mb_5_pay;
					$mb_pay6_total = $mb_pay6_total + $mb_6_pay;
					$mb_6_fee = 100 - $row['mb_6_fee'];
					
					$mb_6_fee = sprintf('%0.4f', $mb_6_fee);

					$mb_pay_to = $mb_1_pay + $mb_2_pay + $mb_3_pay + $mb_4_pay + $mb_5_pay;

				?>
				<tr>
					<td class="td_name"><span class='tid'><?php echo $row['dv_tid']; ?></span></td>
					<td class="td_name"><?php echo $row['mb_6_name']; ?></td>

					<td style="text-align:right"><?php echo number_format($row['spay']); ?></td>
					<td style="text-align:right"><?php echo number_format($row['cpay']); ?></td>
					<td style="text-align:right;font-weight:bold"><?php echo number_format($total_pay); ?></td>

					<?php if($member['mb_level'] >= 8) { ?>
					<td class="td_name"><?php if($row['mb_1_name']) { echo utf8_strcut($row['mb_1_name'],6); } ?></td>
					<td style="text-align:right;"><?php if($row['mb_1_name']) { echo number_format($mb_1_pay); } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 7) { ?>
					<td class="td_name"><?php if($row['mb_2_name']) { echo utf8_strcut($row['mb_2_name'],6); } ?></td>
					<td style="text-align:right;"><?php if($row['mb_2_name']) { echo number_format($mb_2_pay); } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 6) { ?>
					<td class="td_name"><?php if($row['mb_3_name']) { echo utf8_strcut($row['mb_3_name'],6); } ?></td>
					<td style="text-align:right;"><?php if($row['mb_3_name']) { echo number_format($mb_3_pay); } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 5) { ?>
					<td class="td_name"><?php if($row['mb_4_name']) { echo utf8_strcut($row['mb_4_name'],6); } ?></td>
					<td style="text-align:right;"><?php if($row['mb_4_name']) { echo number_format($mb_4_pay); } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 4) { ?>
					<td class="td_name"><?php if($row['mb_5_name']) { echo utf8_strcut($row['mb_5_name'],6); } ?></td>
					<td style="text-align:right;"><?php if($row['mb_5_name']) { echo number_format($mb_5_pay); } ?></td>
					<?php } ?>




					<td style="text-align:right;"><?php echo number_format($mb_6_pay); ?></td>


					<?php if($is_admin) { ?>
					<td style="text-align:right;font-weight:bold"><?php echo number_format($mb_pay_to); ?></td>
					<?php } ?>


				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>
<div style="height:50px;"></div>

<?php if($is_admin) { ?>

<?php
	$sql = " select {$sql_fild} {$sql_common} {$sql_search} and a.mb_1 != '' group by a.mb_1 having a.pay <> 0 ORDER BY a.mb_1, a.mb_2, a.mb_3, a.mb_4, a.mb_5, a.mb_6 asc";
	$result = sql_query($sql);
//	echo $member['mb_type']."<br>";
//	echo $sql;
?>
<h1 class="table_title">본사</h1>
<div class="m_board_scroll">
	<div class="m_table_wrap">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
			<tr>
				<th>업체명</th>
				<th style="width:5%">승 / 취</th>
				<th style="width:10%">승인금액</th>
				<th style="width:10%">취소금액</th>
				<th style="width:10%">총금액</th>
				<?php /*
				<th style="width:5%">수수료</th>
				*/ ?>
				<th style="width:10%">정산액</th>
				<th style="width:10%">부가세제외</th>
				<th style="width:15%">계좌메모</th>
				<th style="width:15%">계좌정보</th>
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

				$total_pay_total = $spay_total + $cpay_total;
				$total_pay = $row['spay'] + $row['cpay'];

				$mb_1_pay = $row['mb_1_pay'] - $row['mb_1_pay'] * 0.133;

				$mb1 = get_member($row['mb_1']);
				$bank1 = $mb1['mb_memo_call'];
				$bank2 = $mb1['mb_8']." " .$mb1['mb_9']." " .$mb1['mb_10'];

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
				<td style="text-align:right; font-weight:bold"><?php if($row['mb_1_name']) { echo number_format($mb_1_pay); } ?></td>
				<td style="text-align:left; font-weight:bold"><?php echo $bank1; ?></td>
				<td style="text-align:left; font-weight:bold"><?php echo $bank2; ?></td>
			</tr>
			<?php } ?>
			</tbody>
			<tfoot>
			<?php
				if ($i == 0) {
					echo '<tr><td colspan="9" style="height:100px; background:#eee; color:#888; line-height:100px;">본사 내역이 없습니다.</td></tr>';
				}
			?>
			</tfoot>
		</table>
	</div>
</div>
<div style="height:50px;"></div>

<?php
	$sql = " select {$sql_fild} {$sql_common} {$sql_search} and a.mb_2 != '' group by a.mb_2 having a.pay <> 0 ORDER BY a.mb_1, a.mb_2, a.mb_3, a.mb_4, a.mb_5, a.mb_6 asc";
	$result = sql_query($sql);
//	echo $member['mb_type']."<br>";
//	echo $sql;
?>

<h1 class="table_title">지사</h1>
<div class="m_board_scroll">
	<div class="m_table_wrap">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
			<tr>
				<th>업체명</th>
				<th style="width:5%">승 / 취</th>
				<th style="width:10%">승인금액</th>
				<th style="width:10%">취소금액</th>
				<th style="width:10%">총금액</th>
				<?php /*
				<th style="width:5%">수수료</th>
				*/ ?>
				<th style="width:10%">정산액</th>
				<th style="width:10%">부가세제외</th>
				<th style="width:15%">계좌메모</th>
				<th style="width:15%">계좌정보</th>
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

				$s_pay = $row['spay'] + $row['cpay'] + ($row['mb_pay2'] + $row['mb_pay3'] + $row['mb_pay4'] + $row['mb_pay5'] + $row['mb_pay6']);

				$scnt_total = $scnt_total + $row['scnt'];
				$ccnt_total = $ccnt_total + $row['ccnt'];
				$spay_total = $spay_total + $row['spay'];
				$cpay_total = $cpay_total + $row['cpay'];

				$total_pay_total = $spay_total + $cpay_total;
				$mb_pay1_total = $mb_pay1_total + $row['mb_1_pay'];
				$mb_pay2_total = $mb_pay2_total + $row['mb_2_pay'];
				$mb_pay3_total = $mb_pay3_total + $row['mb_3_pay'];
				$mb_pay4_total = $mb_pay4_total + $row['mb_4_pay'];
				$mb_pay5_total = $mb_pay5_total + $row['mb_5_pay'];
				$mb_pay6_total = $mb_pay6_total + $row['mb_6_pay'];
				$s_pay_total = $s_pay_total + $s_pay;
				$total_pay = $row['spay'] + $row['cpay'];

				if($row['mb_2_name'] == "진산") {
					$mb_2_pay = $row['mb_2_pay'] - $row['mb_2_pay'] * 0.1;
				} else if($row['mb_2_name'] == "용훈") {
					$mb_2_pay = $row['mb_2_pay'] - $row['mb_2_pay'] * 0.1;
				} else {
					$mb_2_pay = $row['mb_2_pay'] - $row['mb_2_pay'] * 0.133;
				}

				$mb2 = get_member($row['mb_2']);
				$bank1 = $mb2['mb_memo_call'];
				$bank2 = $mb2['mb_8']." " .$mb2['mb_9']." " .$mb2['mb_10'];

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
				<td style="text-align:right; font-weight:bold"><?php if($row['mb_2_name']) { echo number_format($mb_2_pay); } ?></td>
				<td style="text-align:left; font-weight:bold"><?php echo $bank1; ?></td>
				<td style="text-align:left; font-weight:bold"><?php echo $bank2; ?></td>
			</tr>
			<?php } ?>
			</tbody>
			<tfoot>
			<?php
				if ($i == 0) {
					echo '<tr><td colspan="9" style="height:100px; background:#eee; color:#888; line-height:100px;">지사 내역이 없습니다.</td></tr>';
				}
			?>
			</tfoot>
		</table>
	</div>
</div>
<div style="height:50px;"></div>

<?php
	$sql = " select {$sql_fild} {$sql_common} {$sql_search} and a.mb_3 != '' group by a.mb_3 having a.pay <> 0 ORDER BY a.mb_1, a.mb_2, a.mb_3, a.mb_4, a.mb_5, a.mb_6 asc";
	$result = sql_query($sql);
//	echo $member['mb_type']."<br>";
//	echo $sql;
?>

<h1 class="table_title">총판</h1>
<div class="m_board_scroll">
	<div class="m_table_wrap">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
			<tr>
				<th>업체명</th>
				<th style="width:5%">승 / 취</th>
				<th style="width:10%">승인금액</th>
				<th style="width:10%">취소금액</th>
				<th style="width:10%">총금액</th>
				<?php /*
				<th style="width:5%">수수료</th>
				*/ ?>
				<th style="width:10%">정산액</th>
				<th style="width:10%">부가세제외</th>
				<th style="width:15%">계좌메모</th>
				<th style="width:15%">계좌정보</th>
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

				$s_pay = $row['spay'] + $row['cpay'] + ($row['mb_pay2'] + $row['mb_pay3'] + $row['mb_pay4'] + $row['mb_pay5'] + $row['mb_pay6']);

				$scnt_total = $scnt_total + $row['scnt'];
				$ccnt_total = $ccnt_total + $row['ccnt'];
				$spay_total = $spay_total + $row['spay'];
				$cpay_total = $cpay_total + $row['cpay'];

				$total_pay_total = $spay_total + $cpay_total;
				$mb_pay1_total = $mb_pay1_total + $row['mb_1_pay'];
				$mb_pay2_total = $mb_pay2_total + $row['mb_2_pay'];
				$mb_pay3_total = $mb_pay3_total + $row['mb_3_pay'];
				$mb_pay4_total = $mb_pay4_total + $row['mb_4_pay'];
				$mb_pay5_total = $mb_pay5_total + $row['mb_5_pay'];
				$mb_pay6_total = $mb_pay6_total + $row['mb_6_pay'];
				$s_pay_total = $s_pay_total + $s_pay;
				$total_pay = $row['spay'] + $row['cpay'];

				$mb_3_pay = $row['mb_3_pay'] - $row['mb_3_pay'] * 0.133;

				$mb3 = get_member($row['mb_3']);
				$bank1 = $mb3['mb_memo_call'];
				$bank2 = $mb3['mb_8']." " .$mb3['mb_9']." " .$mb3['mb_10'];

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
				<td style="text-align:right; font-weight:bold"><?php if($row['mb_3_name']) { echo number_format($mb_3_pay); } ?></td>
				<td style="text-align:left; font-weight:bold"><?php echo $bank1; ?></td>
				<td style="text-align:left; font-weight:bold"><?php echo $bank2; ?></td>
			</tr>
			<?php } ?>
			</tbody>
			<tfoot>
			<?php
				if ($i == 0) {
					echo '<tr><td colspan="9" style="height:100px; background:#eee; color:#888; line-height:100px;">총판 내역이 없습니다.</td></tr>';
				}
			?>
			</tfoot>
		</table>
	</div>
</div>
<div style="height:50px;"></div>

<?php
	$sql = " select {$sql_fild} {$sql_common} {$sql_search} and a.mb_4 != '' group by a.mb_4 having a.pay <> 0 ORDER BY a.mb_1, a.mb_2, a.mb_3, a.mb_4, a.mb_5, a.mb_6 asc";
	$result = sql_query($sql);
//	echo $member['mb_type']."<br>";
//	echo $sql;
?>

<h1 class="table_title">대리점</h1>
<div class="m_board_scroll">
	<div class="m_table_wrap">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
			<tr>
				<th>업체명</th>
				<th style="width:5%">승 / 취</th>
				<th style="width:10%">승인금액</th>
				<th style="width:10%">취소금액</th>
				<th style="width:10%">총금액</th>
				<?php /*
				<th style="width:5%">수수료</th>
				*/ ?>
				<th style="width:10%">정산액</th>
				<th style="width:10%">부가세제외</th>
				<th style="width:15%">계좌메모</th>
				<th style="width:15%">계좌정보</th>
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

				$s_pay = $row['spay'] + $row['cpay'] + ($row['mb_pay2'] + $row['mb_pay3'] + $row['mb_pay4'] + $row['mb_pay5'] + $row['mb_pay6']);

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
				$total_pay = $row['spay'] + $row['cpay'];

				$mb_4_pay = $row['mb_4_pay'] - $row['mb_4_pay'] * 0.133;

				$mb4 = get_member($row['mb_4']);
				$bank1 = $mb4['mb_memo_call'];
				$bank2 = $mb4['mb_8']." " .$mb4['mb_9']." " .$mb4['mb_10'];

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
				<td style="text-align:right; font-weight:bold"><?php if($row['mb_4_name']) { echo number_format($mb_4_pay); } ?></td>
				<td style="text-align:left; font-weight:bold"><?php echo $bank1; ?></td>
				<td style="text-align:left; font-weight:bold"><?php echo $bank2; ?></td>
			</tr>
			<?php } ?>
			</tbody>
			<tfoot>
			<?php
				if ($i == 0) {
					echo '<tr><td colspan="9" style="height:100px; background:#eee; color:#888; line-height:100px;">대리점 내역이 없습니다.</td></tr>';
				}
			?>
			</tfoot>
		</table>
	</div>
</div>
<div style="height:50px;"></div>

<?php
	$sql = " select {$sql_fild} {$sql_common} {$sql_search} and a.mb_5 != '' group by a.mb_5 having a.pay <> 0 ORDER BY a.mb_1, a.mb_2, a.mb_3, a.mb_4, a.mb_5, a.mb_6 asc";
	$result = sql_query($sql);
//	echo $member['mb_type']."<br>";
//	echo $sql;
?>
<h1 class="table_title">영업점</h1>
<div class="m_board_scroll">
	<div class="m_table_wrap">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
			<tr>
				<th>업체명</th>
				<th style="width:5%">승 / 취</th>
				<th style="width:10%">승인금액</th>
				<th style="width:10%">취소금액</th>
				<th style="width:10%">총금액</th>
				<?php /*
				<th style="width:5%">수수료</th>
				*/ ?>
				<th style="width:10%">정산액</th>
				<th style="width:10%">부가세제외</th>
				<th style="width:15%">계좌메모</th>
				<th style="width:15%">계좌정보</th>
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

				$s_pay = $row['spay'] + $row['cpay'] + ($row['mb_pay2'] + $row['mb_pay3'] + $row['mb_pay4'] + $row['mb_pay5'] + $row['mb_pay6']);

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
				$total_pay = $row['spay'] + $row['cpay'];

				$mb_5_pay = $row['mb_5_pay'] - $row['mb_5_pay'] * 0.133;

				$mb5 = get_member($row['mb_5']);
				$bank1 = $mb5['mb_memo_call'];
				$bank2 = $mb5['mb_8']." " .$mb5['mb_9']." " .$mb5['mb_10'];

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
				<td style="text-align:right; font-weight:bold"><?php if($row['mb_5_name']) { echo number_format($mb_5_pay); } ?></td>
				<td style="text-align:left; font-weight:bold"><?php echo $bank1; ?></td>
				<td style="text-align:left; font-weight:bold"><?php echo $bank2; ?></td>
			</tr>
			<?php } ?>
			</tbody>
			<tfoot>
			<?php
				if ($i == 0) {
					echo '<tr><td colspan="9" style="height:100px; background:#eee; color:#888; line-height:100px;">영업점 내역이 없습니다.</td></tr>';
				}
			?>
			</tfoot>
		</table>
	</div>
</div>
<?php } ?>






<script>
	$("#membera, #memberb, #memberc, #memberd, #membere, #memberf, #dv_type").click(function() {
		$("#membera, #memberb, #memberc, #memberd, #membere, #memberf").find('option:first').attr('selected', 'selected');
	});

	$("#membera, #memberb, #memberc, #memberd, #membere, #memberf").change(function() {
		$("#membera, #memberb, #memberc, #memberd, #membere, #memberf").val();
		$(this).parents().filter("form").submit();
	});
</script>