<?php
//	if(!$is_admin) { alert("관리자만 접속가능합니다."); }
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
	$sql_fild .= ", (sum(IF(pay_type = 'Y', pay, 0)) + sum(IF(pay_type != 'Y', pay, 0))) as total_pay "; // 합계
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
	$sql_fild .= ", b.mb_settle_gbn as mb_settle_gbn "; // 재정산 구분

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
	if($mb_5_name) {
		$sql_search .= " and a.mb_5_name like '%{$mb_5_name}%' ";
	}

	if($dv_type) {
		$sql_search .= " and a.dv_type = '{$dv_type}' ";
	}

	$sql_search .= " and (a.pay_datetime BETWEEN '{$fr_dates} 00:00:00' and '{$to_dates} 23:59:59') ";
	

	if($dv_tid) {
		$sql_search .= " and (a.dv_tid like '{$dv_tid}') ";
	}

	if($mb_6_name) {
		$sql_search .= " and (a.mb_6_name like '%{$mb_6_name}%') ";
	}

	if ($sst)
		$sql_order = " order by {$sst} {$sod} ";
	else
		$sql_order = " order by seq desc ";

	if($sorta == "names") {
		$sql = " select {$sql_fild} {$sql_common} {$sql_search} group by a.dv_tid having a.pay <> 0 ORDER BY c.mb_6_name asc";
	} else if($sorta == "tid") {
		$sql = " select {$sql_fild} {$sql_common} {$sql_search} group by a.dv_tid having a.pay <> 0 ORDER BY a.dv_tid asc";
	} else {
		$sql = " select {$sql_fild} {$sql_common} {$sql_search} group by a.dv_tid having a.pay <> 0 ORDER BY total_pay desc";
	}
	$xlsx_sql = "select {$sql_fild} {$sql_common} {$sql_search} group by a.dv_tid having a.pay <> 0 ORDER BY total_pay desc";
	$result = sql_query($sql);
//	echo $member['mb_type']."<br>";
//	echo $sql;
//	echo "<br><br>";
//	echo $xlsx_sql;
?>

<style>
td span { /*font-family: 'FFF-Reaction-Trial';*/ font-size:11px;}
td span .fee_name {font-family: 'NanumGothic';}

.fee_left {float:left;}
.fee_right {float:right;}

.fee { color:#999}
.tid {font-weight:300; color:#999}
.table_title {font-size:13px; margin:0 0 10px 5px; font-weight:700; color:#555}

/* 정산 헤더 스타일 */
.settlement-header {
	background: linear-gradient(135deg, #7b1fa2 0%, #8e24aa 100%);
	border-radius: 8px;
	padding: 12px 16px;
	margin-bottom: 10px;
	box-shadow: 0 2px 8px rgba(123, 31, 162, 0.2);
}
.settlement-title {
	color: #fff;
	font-size: 16px;
	font-weight: 600;
	display: flex;
	align-items: center;
	gap: 8px;
}
.settlement-title i {
	font-size: 14px;
	opacity: 0.8;
}
/* 검색 영역 */
.settlement-search {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 10px 12px;
	margin-bottom: 10px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.settlement-search-row {
	display: flex;
	align-items: center;
	flex-wrap: wrap;
	gap: 8px;
}
.settlement-search-group {
	display: flex;
	align-items: center;
	gap: 4px;
}
.settlement-search-group input[type="text"] {
	width: 90px;
	padding: 6px 8px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-size: 13px;
	background: #f8f9fa;
}
.settlement-search-group input[type="text"]:focus {
	outline: none;
	border-color: #7b1fa2;
	background: #fff;
}
.settlement-search-group span {
	color: #999;
	font-size: 12px;
}
.date-btns {
	display: flex;
	gap: 3px;
}
.date-btns button {
	padding: 5px 8px;
	font-size: 11px;
	border: 1px solid #ddd;
	background: #f8f9fa;
	border-radius: 3px;
	cursor: pointer;
	color: #555;
	transition: all 0.15s;
}
.date-btns button:hover {
	background: #7b1fa2;
	border-color: #7b1fa2;
	color: #fff;
}
.search-divider {
	width: 1px;
	height: 24px;
	background: #e0e0e0;
	margin: 0 6px;
}
.select-group {
	display: flex;
	gap: 4px;
}
.select-group select {
	width: 70px !important;
	padding: 6px 8px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-size: 12px;
	background: #f8f9fa;
	cursor: pointer;
}
.select-group select:focus {
	outline: none;
	border-color: #7b1fa2;
	background: #fff;
}
.search-input-group {
	display: flex;
	align-items: center;
	gap: 4px;
}
.search-input-group input[type="text"] {
	width: 80px;
	padding: 6px 8px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-size: 13px;
	background: #f8f9fa;
}
.search-input-group input[type="text"]:focus {
	outline: none;
	border-color: #7b1fa2;
	background: #fff;
}
.radio-group {
	display: flex;
	align-items: center;
	gap: 8px;
}
.radio-group label {
	display: flex;
	align-items: center;
	gap: 3px;
	font-size: 12px;
	color: #555;
	cursor: pointer;
}
.radio-group input[type="radio"] {
	margin: 0;
	accent-color: #7b1fa2;
}
.btn-search {
	padding: 6px 12px;
	background: #7b1fa2;
	color: #fff;
	border: none;
	border-radius: 4px;
	font-size: 12px;
	cursor: pointer;
	transition: background 0.15s;
}
.btn-search:hover {
	background: #8e24aa;
}
.btn-excel {
	padding: 6px 10px;
	background: #2e7d32;
	color: #fff;
	border: none;
	border-radius: 4px;
	font-size: 12px;
	cursor: pointer;
	transition: background 0.15s;
}
.btn-excel:hover {
	background: #388e3c;
}
@media (max-width: 768px) {
	.settlement-search-row {
		flex-direction: column;
		align-items: flex-start;
	}
	.search-divider {
		display: none;
	}
	.select-group {
		flex-wrap: wrap;
	}
	.radio-group {
		flex-wrap: wrap;
	}
}
</style>

<div class="settlement-header">
	<div class="settlement-title">
		<i class="fa fa-calculator"></i>
		실시간 정산조회
	</div>
</div>

<form id="fsearch" name="fsearch" method="get">
<input type="hidden" name="p" value="<?php echo $p; ?>">
<div class="settlement-search">
	<div class="settlement-search-row">
		<!-- 날짜 선택 -->
		<div class="settlement-search-group">
			<input type="text" name="fr_date" value="<?php echo $fr_date ?>" id="fr_date" placeholder="시작일">
			<span>~</span>
			<input type="text" name="to_date" value="<?php echo $to_date ?>" id="to_date" placeholder="종료일">
		</div>
		<!-- 단축 버튼 -->
		<div class="date-btns">
			<button type="submit" onclick="javascript:set_date('오늘');">오늘</button>
			<button type="submit" onclick="javascript:set_date('어제');">어제</button>
			<button type="submit" onclick="javascript:set_date('이번주');">이번주</button>
			<button type="submit" onclick="javascript:set_date('이번달');">이번달</button>
			<button type="submit" onclick="javascript:set_date('지난주');">지난주</button>
			<button type="submit" onclick="javascript:set_date('지난달');">지난달</button>
		</div>

		<?php if($is_admin) { ?>
		<div class="search-divider"></div>
		<!-- 회원 선택 -->
		<div class="select-group">
			<select name="membera" id="membera">
				<option value="">본사</option>
				<?php
					$sql_g = " select * from g5_member where mb_level = '8' order by mb_nick";
					$result_g = sql_query($sql_g);
					for ($k=0; $row_g=sql_fetch_array($result_g); $k++) {
				?>
					<option value="<?php echo $row_g['mb_id']; ?>" <?php if($membera == $row_g['mb_id']) { echo "selected"; } ?>><?php echo $row_g['mb_nick']; ?></option>
				<?php } ?>
			</select>
			<select name="memberb" id="memberb">
				<option value="">지사</option>
				<?php
					$sql_g = " select * from g5_member where mb_level = '7' order by mb_nick";
					$result_g = sql_query($sql_g);
					for ($k=0; $row_g=sql_fetch_array($result_g); $k++) {
				?>
					<option value="<?php echo $row_g['mb_id']; ?>" <?php if($memberb == $row_g['mb_id']) { echo "selected"; } ?>><?php echo $row_g['mb_nick']; ?></option>
				<?php } ?>
			</select>
			<select name="memberc" id="memberc">
				<option value="">총판</option>
				<?php
					$sql_g = " select * from g5_member where mb_level = '6' order by mb_nick";
					$result_g = sql_query($sql_g);
					for ($k=0; $row_g=sql_fetch_array($result_g); $k++) {
				?>
					<option value="<?php echo $row_g['mb_id']; ?>" <?php if($memberc == $row_g['mb_id']) { echo "selected"; } ?>><?php echo $row_g['mb_nick']; ?></option>
				<?php } ?>
			</select>
			<select name="memberd" id="memberd">
				<option value="">대리점</option>
				<?php
					$sql_g = " select * from g5_member where mb_level = '5' order by mb_nick";
					$result_g = sql_query($sql_g);
					for ($k=0; $row_g=sql_fetch_array($result_g); $k++) {
				?>
					<option value="<?php echo $row_g['mb_id']; ?>" <?php if($memberd == $row_g['mb_id']) { echo "selected"; } ?>><?php echo $row_g['mb_nick']; ?></option>
				<?php } ?>
			</select>
			<select name="membere" id="membere">
				<option value="">영업점</option>
				<?php
					$sql_g = " select * from g5_member where mb_level = '4' order by mb_nick";
					$result_g = sql_query($sql_g);
					for ($k=0; $row_g=sql_fetch_array($result_g); $k++) {
				?>
					<option value="<?php echo $row_g['mb_id']; ?>" <?php if($membere == $row_g['mb_id']) { echo "selected"; } ?>><?php echo $row_g['mb_nick']; ?></option>
				<?php } ?>
			</select>
		</div>
		<?php } ?>

		<div class="search-divider"></div>
		<!-- 회원명 검색 -->
		<div class="search-input-group">
			<?php if($member['mb_level'] > 8) { ?>
			<input type="text" name="mb_1_name" value="<?php echo $mb_1_name ?>" id="mb_1_name" placeholder="본사명">
			<?php } ?>
			<?php if($member['mb_level'] > 7) { ?>
			<input type="text" name="mb_2_name" value="<?php echo $mb_2_name ?>" id="mb_2_name" placeholder="지사명">
			<?php } ?>
			<?php if($member['mb_level'] > 6) { ?>
			<input type="text" name="mb_3_name" value="<?php echo $mb_3_name ?>" id="mb_3_name" placeholder="총판명">
			<?php } ?>
			<?php if($member['mb_level'] > 5) { ?>
			<input type="text" name="mb_4_name" value="<?php echo $mb_4_name ?>" id="mb_4_name" placeholder="대리점명">
			<?php } ?>
			<?php if($member['mb_level'] > 4) { ?>
			<input type="text" name="mb_5_name" value="<?php echo $mb_5_name ?>" id="mb_5_name" placeholder="영업점명">
			<?php } ?>
			<input type="text" name="mb_6_name" value="<?php echo $mb_6_name ?>" id="mb_6_name" placeholder="가맹점명" style="width:100px;">
			<input type="text" name="dv_tid" value="<?php echo $dv_tid ?>" id="dv_tid" placeholder="TID" style="width:100px;">
		</div>

		<div class="search-divider"></div>
		<!-- 타입 선택 -->
		<div class="radio-group">
			<label><input type="radio" name="dv_type" value="" <?php if(!$dv_type) { echo "checked"; } ?>>전체</label>
			<label><input type="radio" name="dv_type" value="2" <?php if($dv_type == "2") { echo "checked"; } ?>>온라인</label>
			<label><input type="radio" name="dv_type" value="1" <?php if($dv_type == "1") { echo "checked"; } ?>>오프라인</label>
		</div>

		<div class="search-divider"></div>
		<!-- 정렬 및 버튼 -->
		<div class="search-input-group">
			<select name="sorta" style="width:80px; padding:6px 8px; border:1px solid #ddd; border-radius:4px; font-size:12px; background:#f8f9fa;">
				<option value="">금액순</option>
				<option value="tid" <?php if($sorta == "tid") { echo "selected"; } ?>>TID</option>
				<option value="names" <?php if($sorta == "names") { echo "selected"; } ?>>가맹점명</option>
			</select>
			<button type="submit" class="btn-search">검색</button>
			<?php if($is_admin) { ?>
			<button type="button" class="btn-excel" id="xlsx"><i class="fa fa-file-excel-o"></i> 엑셀</button>
			<?php } ?>
		</div>
	</div>
</div>
</form>

<form action="./xlsx/settlement_master.php" id="frm_xlsx" method="post">
<input type="hidden" name="xlsx_sql" value="<?php echo $xlsx_sql; ?>">
<input type="hidden" name="p" value="<?php echo $p; ?>">
</form>

<div class="m_board_scroll">
	<div class="m_table_wrap">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
				<tr>
					<?php if($is_admin) { ?>
					<th rowspan="2">구분</th>
					<?php } ?>
					<th rowspan="2">가맹점명</th>
					<th rowspan="2">TID</th>
					<th rowspan="2">승인</th>
					<th rowspan="2">취소</th>
					<th rowspan="2">승인금액</th>
					<th rowspan="2">취소금액</th>
					<th rowspan="2">총금액</th>
					<?php if($member['mb_level'] >= 8) { ?>
					<th colspan="3">본사</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 7) { ?>
					<th colspan="3">지사</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 6) { ?>
					<th colspan="3">총판</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 5) { ?>
					<th colspan="3">대리점</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 4) { ?>
					<th colspan="3">영업점</th>
					<?php } ?>
					<th colspan="2">가맹점</th>
					<?php if($is_admin) { ?>
					<th rowspan="2">계좌정보</th>
					<?php } ?>
				</tr>
				<tr>
					<?php if($member['mb_level'] >= 8) { ?>
					<th>사명</th>
					<th>수수료</th>
					<th>수익금</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 7) { ?>
					<th>사명</th>
					<th>수수료</th>
					<th>수익금</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 6) { ?>
					<th>사명</th>
					<th>수수료</th>
					<th>수익금</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 5) { ?>
					<th>사명</th>
					<th>수수료</th>
					<th>수익금</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 4) { ?>
					<th>사명</th>
					<th>수수료</th>
					<th>수익금<br><span style="font-size:10px;color:#4caf50">(밴피)</span></th>
					<?php } ?>

					<th>수수료</th>
					<th>수익금</th>
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

					$mb_1_fee = sprintf('%0.3f', $mb_1_fee);
					$mb_2_fee = sprintf('%0.3f', $mb_2_fee);
					$mb_3_fee = sprintf('%0.3f', $mb_3_fee);
					$mb_4_fee = sprintf('%0.3f', $mb_4_fee);
					$mb_5_fee = sprintf('%0.3f', $mb_5_fee);
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
					
					$mb_6_fee = sprintf('%0.3f', $mb_6_fee);

				?>
				<tr>
					<?php if($is_admin) { ?>
					<td style="text-align:center; font-weight:bold; color:<?php echo $row['mb_settle_gbn'] == 'Y' ? '#4caf50' : '#f44336'; ?>">
						<?php echo $row['mb_settle_gbn'] == 'Y' ? 'O' : 'X'; ?>
					</td>
					<?php } ?>
					<td class="td_name"><?php echo $row['mb_6_name']; ?></td>
					<td><span class='tid'><?php echo $row['dv_tid']; ?></span></td>

					<td><?php echo $row['scnt']; ?></td>
					<td><?php echo $row['ccnt']; ?></td>
					<td style="text-align:right"><?php echo number_format($row['spay']); ?></td>
					<td style="text-align:right"><?php echo number_format($row['cpay']); ?></td>
					<td style="text-align:right;font-weight:bold"><?php echo number_format($total_pay); ?></td>

					<?php if($member['mb_level'] >= 8) { ?>
					<td class="td_name"><?php if($row['mb_1_name']) { echo utf8_strcut($row['mb_1_name'],6); } ?></td>
					<td><?php if($row['mb_1_name']) { echo "<span class='fee1'>".$row['mb_1_fee']."</span><span class='fee2'>".$mb_1_fee."</span>"; } ?></td>
					<td style="text-align:right;font-weight:bold"><?php if($row['mb_1_name']) { echo number_format($mb_1_pay); } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 7) { ?>
					<td class="td_name"><?php if($row['mb_2_name']) { echo utf8_strcut($row['mb_2_name'],6); } ?></td>
					<td><?php if($row['mb_2_name']) { echo "<span class='fee1'>".$row['mb_2_fee']."</span><span class='fee2'>".$mb_2_fee."</span>"; } ?></td>
					<td style="text-align:right;font-weight:bold"><?php if($row['mb_2_name']) { echo number_format($mb_2_pay); } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 6) { ?>
					<td class="td_name"><?php if($row['mb_3_name']) { echo utf8_strcut($row['mb_3_name'],6); } ?></td>
					<td><?php if($row['mb_3_name']) { echo "<span class='fee1'>".$row['mb_3_fee']."</span><span class='fee2'>".$mb_3_fee."</span>"; } ?></td>
					<td style="text-align:right;font-weight:bold"><?php if($row['mb_3_name']) { echo number_format($mb_3_pay); } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 5) { ?>
					<td class="td_name"><?php if($row['mb_4_name']) { echo utf8_strcut($row['mb_4_name'],6); } ?></td>
					<td><?php if($row['mb_4_name']) { echo "<span class='fee1'>".$row['mb_4_fee']."</span><span class='fee2'>".$mb_4_fee."</span>"; } ?></td>
					<td style="text-align:right;font-weight:bold"><?php if($row['mb_4_name']) { echo number_format($mb_4_pay); } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 4) { ?>
					<?php
					// 영업점 밴피 계산
					$mb5_van_fee = 0;
					$mb5_van_fee_amount = 0;
					if($row['mb_5']) {
						$mb5_info = get_member($row['mb_5']);
						if($mb5_info['mb_van_fee'] > 0) {
							$mb5_van_fee = $mb5_info['mb_van_fee'];
							$mb5_van_fee_amount = ($row['scnt'] - $row['ccnt']) * $mb5_van_fee;
						}
					}
					?>
					<td class="td_name"><?php if($row['mb_5_name']) { echo utf8_strcut($row['mb_5_name'],6); } ?></td>
					<td><?php if($row['mb_5_name']) { echo "<span class='fee1'>".$row['mb_5_fee']."</span><span class='fee2'>".$mb_5_fee."</span>"; } ?></td>
					<td style="text-align:right;font-weight:bold"><?php if($row['mb_5_name']) { echo number_format($mb_5_pay); if($mb5_van_fee > 0) { echo "<br><span style='color:#4caf50;font-size:11px'>(".number_format($mb5_van_fee_amount).")</span>"; } } ?></td>
					<?php } ?>




					<td><span class='fee1'><?php echo $row['mb_6_fee']; ?></span><span class='fee2'><?php echo $mb_6_fee; ?></span></td>
					<td style="text-align:right;font-weight:bold"><?php echo number_format($mb_6_pay); ?></td>


					<?php if($is_admin) { ?>
					<td style="text-align:right;"><?php if($row['mb_8']) { echo $row['mb_8']; ?> <?php echo $row['mb_9']; ?> <?php echo $row['mb_10']; } else { echo "-"; } ?></td>
					<?php } ?>


				</tr>
				<?php } ?>
			</tbody>
			<tfoot>
				<tr style="border-top:1px solid #e5e5e5;">
					<?php if($is_admin) { ?>
					<th>구분</th>
					<?php } ?>
					<th>가맹점명</th>
					<th>TID</th>
					<th>승인</th>
					<th>취소</th>
					<th>승인금액</th>
					<th>취소금액</th>
					<th>총금액</th>
					<?php if($member['mb_level'] >= 8) { ?>
					<th colspan="3">본사</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 7) { ?>
					<th colspan="3">지사</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 6) { ?>
					<th colspan="3">총판</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 5) { ?>
					<th colspan="3">대리점</th>
					<?php } ?>
					<?php if($member['mb_level'] >= 4) { ?>
					<th colspan="3">영업점</th>
					<?php } ?>
					<th colspan="2">가맹점</th>
					<?php if($is_admin) { ?>
					<th rowspan="2">계좌정보</th>
					<?php } ?>
				</tr>
				<tr>
					<td colspan="<?php echo $is_admin ? '3' : '2'; ?>">합계</td>
					<td><?php echo number_format($scnt_total); ?></td>
					<td><?php echo number_format($ccnt_total); ?></td>

					<td style="text-align:right"><?php echo number_format($spay_total); ?></td>
					<td style="text-align:right"><?php echo number_format($cpay_total); ?></td>
					<td style="text-align:right"><?php echo number_format($total_pay_total); ?></td>
					<?php if($member['mb_level'] >= 8) { ?>
					<td style="text-align:right" colspan="3"><?php echo number_format($mb_pay1_total); ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 7) { ?>
					<td style="text-align:right" colspan="3"><?php echo number_format($mb_pay2_total); ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 6) { ?>
					<td style="text-align:right" colspan="3"><?php echo number_format($mb_pay3_total); ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 5) { ?>
					<td style="text-align:right" colspan="3"><?php echo number_format($mb_pay4_total); ?></td>
					<?php } ?>
					<?php if($member['mb_level'] >= 4) { ?>
					<td style="text-align:right" colspan="3"><?php echo number_format($mb_pay5_total); ?></td>
					<?php } ?>
					<td style="text-align:right" colspan="2"><?php echo number_format($mb_pay6_total); ?></td>
				</tr>
			</tfoot>
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
				<th style="width:7%">승 / 취</th>
				<th style="width:10%">승인금액</th>
				<th style="width:10%">취소금액</th>
				<th style="width:10%">총금액</th>
				<?php /*
				<th style="width:5%">수수료</th>
				*/ ?>
				<th style="width:10%">정산액</th>
				<th style="width:10%">부가세제외</th>
				<th style="width:20%">계좌정보</th>
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

				$mb_1_pay = floor($row['mb_1_pay'] - $row['mb_1_pay'] * 0.133);
				$mb1 = get_member($row['mb_1']);
				if($mb1['mb_memo_call']) {
					$bank1 = $mb1['mb_memo_call'];
				} else {
					$bank1 = $mb1['mb_8']." " .$mb1['mb_9']." " .$mb1['mb_10'];
				}

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
			</tr>
			<?php } ?>
			</tbody>
			<tfoot>
			<?php
				if ($i == 0) {
					echo '<tr><td colspan="8" style="height:100px; background:#eee; color:#888; line-height:100px;">본사 내역이 없습니다.</td></tr>';
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
				<th style="width:7%">승 / 취</th>
				<th style="width:10%">승인금액</th>
				<th style="width:10%">취소금액</th>
				<th style="width:10%">총금액</th>
				<?php /*
				<th style="width:5%">수수료</th>
				*/ ?>
				<th style="width:10%">정산액</th>
				<th style="width:10%">부가세제외</th>
				<th style="width:20%">계좌정보</th>
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
					$mb_2_pay = floor($row['mb_2_pay'] - $row['mb_2_pay'] * 0.1);
				} else if($row['mb_2_name'] == "용훈") {
					$mb_2_pay = floor($row['mb_2_pay'] - $row['mb_2_pay'] * 0.1);
				} else {
					$mb_2_pay = floor($row['mb_2_pay'] - $row['mb_2_pay'] * 0.133);
				}

				$mb2 = get_member($row['mb_2']);
				if($mb2['mb_memo_call']) {
					$bank2 = $mb2['mb_memo_call'];
				} else {
					$bank2 = $mb2['mb_8']." " .$mb2['mb_9']." " .$mb2['mb_10'];
				}

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
				<td style="text-align:left; font-weight:bold"><?php echo $bank2; ?></td>
			</tr>
			<?php } ?>
			</tbody>
			<tfoot>
			<?php
				if ($i == 0) {
					echo '<tr><td colspan="8" style="height:100px; background:#eee; color:#888; line-height:100px;">지사 내역이 없습니다.</td></tr>';
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
				<th style="width:7%">승 / 취</th>
				<th style="width:10%">승인금액</th>
				<th style="width:10%">취소금액</th>
				<th style="width:10%">총금액</th>
				<?php /*
				<th style="width:5%">수수료</th>
				*/ ?>
				<th style="width:10%">정산액</th>
				<th style="width:10%">부가세제외</th>
				<th style="width:20%">계좌정보</th>
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

				$mb_3_pay = floor($row['mb_3_pay'] - $row['mb_3_pay'] * 0.133);

				$mb3 = get_member($row['mb_3']);
				if($mb3['mb_memo_call']) {
					$bank3 = $mb3['mb_memo_call'];
				} else {
					$bank3 = $mb3['mb_8']." " .$mb3['mb_9']." " .$mb3['mb_10'];
				}

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
				<td style="text-align:left; font-weight:bold"><?php echo $bank3; ?></td>
			</tr>
			<?php } ?>
			</tbody>
			<tfoot>
			<?php
				if ($i == 0) {
					echo '<tr><td colspan="8" style="height:100px; background:#eee; color:#888; line-height:100px;">총판 내역이 없습니다.</td></tr>';
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
				<th style="width:7%">승 / 취</th>
				<th style="width:10%">승인금액</th>
				<th style="width:10%">취소금액</th>
				<th style="width:10%">총금액</th>
				<?php /*
				<th style="width:5%">수수료</th>
				*/ ?>
				<th style="width:10%">정산액</th>
				<th style="width:10%">부가세제외</th>
				<th style="width:20%">계좌정보</th>
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

				$mb_4_pay = floor($row['mb_4_pay'] - $row['mb_4_pay'] * 0.133);

				$mb4 = get_member($row['mb_4']);
				if($mb4['mb_memo_call']) {
					$bank4 = $mb4['mb_memo_call'];
				} else {
					$bank4 = $mb4['mb_8']." " .$mb4['mb_9']." " .$mb4['mb_10'];
				}

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
				<td style="text-align:left; font-weight:bold"><?php echo $bank4; ?></td>
			</tr>
			<?php } ?>
			</tbody>
			<tfoot>
			<?php
				if ($i == 0) {
					echo '<tr><td colspan="8" style="height:100px; background:#eee; color:#888; line-height:100px;">대리점 내역이 없습니다.</td></tr>';
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
				<th style="width:7%">승 / 취</th>
				<th style="width:10%">승인금액</th>
				<th style="width:10%">취소금액</th>
				<th style="width:10%">총금액</th>
				<?php /*
				<th style="width:5%">수수료</th>
				*/ ?>
				<th style="width:10%">정산액</th>
				<th style="width:10%">부가세제외</th>
				<th style="width:8%">밴피합계</th>
				<th style="width:17%">계좌정보</th>
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
				$van_fee_total = 0;

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

				$mb_5_pay = floor($row['mb_5_pay'] - $row['mb_5_pay'] * 0.133);

				$mb5 = get_member($row['mb_5']);
				if($mb5['mb_memo_call']) {
					$bank5 = $mb5['mb_memo_call'];
				} else {
					$bank5 = $mb5['mb_8']." " .$mb5['mb_9']." " .$mb5['mb_10'];
				}

				// 밴피 계산: (승인건수 - 취소건수) * 밴피
				$van_fee_amount = 0;
				if($mb5['mb_van_fee'] > 0) {
					$van_fee_amount = ($row['scnt'] - $row['ccnt']) * $mb5['mb_van_fee'];
					$van_fee_total = $van_fee_total + $van_fee_amount;
				}

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
				<td style="text-align:right; font-weight:bold; color:#4caf50"><?php echo ($mb5['mb_van_fee'] > 0) ? number_format($van_fee_amount) : '-'; ?></td>
				<td style="text-align:left; font-weight:bold"><?php echo $bank5; ?></td>
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
	// 날짜 선택기
	$(function() {
		$("#fr_date, #to_date").datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: "yymmdd",
			showButtonPanel: true,
			yearRange: "c-99:c+99"
		});
	});

	// 엑셀 다운로드
	$("#xlsx").on("click", function() {
		$("#frm_xlsx").submit();
	});

	// 회원 선택 드롭다운
	$("#membera, #memberb, #memberc, #memberd, #membere, #memberf, #dv_type").click(function() {
		$("#membera, #memberb, #memberc, #memberd, #membere, #memberf").find('option:first').attr('selected', 'selected');
	});

	$("#membera, #memberb, #memberc, #memberd, #membere, #memberf").change(function() {
		$("#membera, #memberb, #memberc, #memberd, #membere, #memberf").val();
		$(this).parents().filter("form").submit();
	});
</script>