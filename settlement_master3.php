<?php
	if(!$is_admin) { alert("관리자만 접속가능합니다."); }
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
	$xlsx_sql = " select {$sql_fild} {$sql_common} {$sql_search} group by a.dv_tid having a.pay <> 0 ORDER BY a.mb_1, a.mb_2, a.mb_3, a.mb_4, a.mb_5, a.mb_6 asc ";
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
						<button type="button" class="btn_b btn_b06" id="xlsx"><span><i class="fa fa-file-excel-o" aria-hidden="true"></i> 엑셀출력</span></button>
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

<form action="./xlsx/settlement_master3.php" id="frm_xlsx" method="post">
<input type="hidden" name="xlsx_sql" value="<?php echo $xlsx_sql; ?>">
<input type="hidden" name="p" value="<?php echo $p; ?>">
</form>



<div class="m_board_scroll">
	<div class="m_table_wrap">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
				<tr>
					<th>TID</th>
					<th>본 TID</th>
					<th>가맹점명</th>
					<th>PG</th>
					<th>승인</th>
					<th>취소</th>
					<th>승인금액</th>
					<th>취소금액</th>
					<th>총금액</th>

					<th>정산금액</th>

					<th>은행</th>
					<th>은행코드</th>
					<th>계좌번호</th>
					<th>예금주명</th>
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

					$bankcode ="";
					if($row['mb_8'] == "경남") { $bankcode = "039";
					} else if($row['mb_8'] == "광주") { $bankcode = "034";
					} else if($row['mb_8'] == "국민") { $bankcode = "004";
					} else if($row['mb_8'] == "국민은행") { $bankcode = "004";
					} else if($row['mb_8'] == "기업") { $bankcode = "003";
					} else if($row['mb_8'] == "기업은행") { $bankcode = "003";
					} else if($row['mb_8'] == "농협") { $bankcode = "011";
					} else if($row['mb_8'] == "단위농협") { $bankcode = "012";
					} else if($row['mb_8'] == "대구") { $bankcode = "031";
					} else if($row['mb_8'] == "부산") { $bankcode = "032";
					} else if($row['mb_8'] == "산림조합") { $bankcode = "064";
					} else if($row['mb_8'] == "산업") { $bankcode = "002";
					} else if($row['mb_8'] == "상호저축") { $bankcode = "050";
					} else if($row['mb_8'] == "새마을금고") { $bankcode = "045";
					} else if($row['mb_8'] == "새마을") { $bankcode = "045";
					} else if($row['mb_8'] == "수협") { $bankcode = "007";
					} else if($row['mb_8'] == "신한") { $bankcode = "088";
					} else if($row['mb_8'] == "신협") { $bankcode = "048";
					} else if($row['mb_8'] == "우리") { $bankcode = "020";
					} else if($row['mb_8'] == "우리은행") { $bankcode = "020";
					} else if($row['mb_8'] == "우체국") { $bankcode = "071";
					} else if($row['mb_8'] == "전북") { $bankcode = "037";
					} else if($row['mb_8'] == "제주") { $bankcode = "035";
					} else if($row['mb_8'] == "하나") { $bankcode = "081";
					} else if($row['mb_8'] == "한국씨티") { $bankcode = "027";
					} else if($row['mb_8'] == "도이치") { $bankcode = "055";
					} else if($row['mb_8'] == "BOA") { $bankcode = "060";
					} else if($row['mb_8'] == "중국공상") { $bankcode = "062";
					} else if($row['mb_8'] == "SC제일") { $bankcode = "023";
					} else if($row['mb_8'] == "SC제일은행") { $bankcode = "023";
					} else if($row['mb_8'] == "HSBC") { $bankcode = "054";
					} else if($row['mb_8'] == "K뱅크") { $bankcode = "089";
					} else if($row['mb_8'] == "카카오뱅크") { $bankcode = "090";
					} else if($row['mb_8'] == "카카오") { $bankcode = "090";
					} else if($row['mb_8'] == "토스") { $bankcode = "092";
					} else if($row['mb_8'] == "케이뱅크") { $bankcode = "089";
					}



				?>
				<tr>
					<td class="td_name"><span class='tid'><?php echo $row['dv_tid']; ?></span></td>
					<td class="td_name"><span class='tid'><?php echo $row['dv_tid_ori']; ?></span></td>
					<td class="td_name"><?php echo $row['mb_6_name']; ?></td>
					<td><?php echo $row['mb_1_name']; ?></td>
					<td><?php echo $row['scnt']; ?></td>
					<td><?php echo $row['ccnt']; ?></td>

					<td style="text-align:right"><?php echo number_format($row['spay']); ?></td>
					<td style="text-align:right"><?php echo number_format($row['cpay']); ?></td>
					<td style="text-align:right;"><?php echo number_format($total_pay); ?></td>
					<td class="td_name" style="text-align:right;font-weight:bold"><?php echo number_format($mb_6_pay); ?></td>


					<td><?php echo $row['mb_8']; ?></td>
					<td style="<?php if(!$bankcode) { echo "background:#777;"; } ?>"><?php echo $bankcode; ?></td>
					<td><?php echo $row['mb_9']; ?></td>
					<td><?php echo $row['mb_10']; ?></td>


				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>





<script>
	$("#membera, #memberb, #memberc, #memberd, #membere, #memberf, #dv_type").click(function() {
		$("#membera, #memberb, #memberc, #memberd, #membere, #memberf").find('option:first').attr('selected', 'selected');
	});

	$("#membera, #memberb, #memberc, #memberd, #membere, #memberf").change(function() {
		$("#membera, #memberb, #memberc, #memberd, #membere, #memberf").val();
		$(this).parents().filter("form").submit();
	});
</script>