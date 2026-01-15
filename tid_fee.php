<?php
	$title1 = "수수료 관리";
	$title2 = "수수료 관리";



	if($is_admin) { // 관리자

		if(adm_sql_common) {
			$adm_sql = " a.mb_1 IN (".adm_sql_common.")";
		} else {
			$adm_sql = " (1)";
		}

	} else if($member['mb_level'] == 7) { // 지사
		$sql_search = " a.mb_2 = '{$member['mb_id']}' ";
	} else if($member['mb_level'] == 6) { // 총판
		$sql_search = " a.mb_3 = '{$member['mb_id']}' ";
	} else if($member['mb_level'] == 5) { // 대리점
		$sql_search = " a.mb_4 = '{$member['mb_id']}' ";
	} else if($member['mb_level'] == 4) { // 영업점
		$sql_search = " a.mb_5 = '{$member['mb_id']}' ";
	}

	$sql_common = " from g5_device a left join g5_member b on a.mb_6 = b.mb_id where ".$adm_sql;
	/*
	if ($is_admin != 'super')
		$sql_search .= " and (gr_admin = '{$member['mb_id']}') ";
	*/

	if($l2) { $sql_search .= " and a.mb_pid2 = '{$l2}' "; }
	if($l3) { $sql_search .= " and a.mb_pid3 = '{$l3}' "; }
	if($l4) { $sql_search .= " and a.mb_pid4 = '{$l4}' "; }
	if($l5) { $sql_search .= " and a.mb_pid5 = '{$l5}' "; }
	if($l6) { $sql_search .= " and a.mb_pid6 = '{$l6}' "; }
	if($l7) { $sql_search .= " and a.mb_pid7 = '{$l7}' "; }
	if($device_type) { $sql_search .= " and a.dv_type = '{$device_type}' "; }




	if($dv_pg != null) {
		if($dv_pg == 'winglobal') {
			// 윈글로벌: 다우(6), 코페이(0), 다날(1) 검색
			$sql_search .= " and a.dv_pg IN ('0', '1', '6') ";
		} else {
			$sql_search .= " and a.dv_pg = '{$dv_pg}' ";
		}
	}
	if($dv_type) { $sql_search .= " and a.dv_type = '{$dv_type}' "; }
	if($dv_certi) { $sql_search .= " and a.dv_certi = '{$dv_certi}' "; }


	if($membera) {	$sql_search .= " and a.mb_1 = '$membera' ";	}
	if($memberb) {	$sql_search .= " and a.mb_2 = '$memberb' ";	}
	if($memberc) {	$sql_search .= " and a.mb_3 = '$memberc' ";	}
	if($memberd) {	$sql_search .= " and a.mb_4 = '$memberd' ";	}
	if($membere) {	$sql_search .= " and a.mb_5 = '$membere' ";	}
	if($memberf) {	$sql_search .= " and a.mb_6 = '$memberf' ";	}
	if($mb_nick) {	$sql_search .= " and a.mb_6_name like '%$mb_nick%' ";	}

	if($mb_1_name) { $sql_search .= " and a.mb_1_name like '%{$mb_1_name}%' "; }
	if($mb_2_name) { $sql_search .= " and a.mb_2_name like '%{$mb_2_name}%' "; }
	if($mb_3_name) { $sql_search .= " and a.mb_3_name like '%{$mb_3_name}%' "; }
	if($mb_4_name) { $sql_search .= " and a.mb_4_name like '%{$mb_4_name}%' "; }
	if($mb_5_name) { $sql_search .= " and a.mb_5_name like '%{$mb_5_name}%' "; }
	if($mb_6_name) { $sql_search .= " and a.mb_6_name like '%{$mb_6_name}%' "; }

	if($dv_tid) { $sql_search .= " and a.dv_tid like '%{$dv_tid}%' "; }
	if($mb_6_name) { $sql_search .= " and a.mb_6_name like '%{$mb_6_name}%' "; }

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
		$sql_order = " order by a.dv_id desc ";

	$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
	$row = sql_fetch($sql);

	$total_count = $row['cnt']; // 전체개수

	if($page_count) {
		$rows = $page_count;
	} else {
		$rows = 200;
	}

	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함

	$sql = " select a.*, b.mb_settle_gbn {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
	$xlsx_sql = "select a.*, b.mb_settle_gbn {$sql_common} {$sql_search} {$sql_order} ";
	$result = sql_query($sql);

//	echo $sql."<br><br>";
//	echo $dv_pg;

?>
<style>
td span { font-size:11px;}
td span .fee_name {font-family: 'NanumGothic';}
.fee_left {float:left;}
.fee_right {float:right;}
.tid {font-weight:300; color:#999}
.table_title {font-size:13px; margin:0 0 10px 5px; font-weight:700; color:#555}

.payment-header {
	background: linear-gradient(135deg, #00695c 0%, #00897b 100%);
	border-radius: 8px;
	padding: 12px 16px;
	margin-bottom: 10px;
	box-shadow: 0 2px 8px rgba(0, 105, 92, 0.2);
}
.payment-header-top {
	display: flex;
	align-items: center;
	justify-content: space-between;
	flex-wrap: wrap;
	gap: 10px;
}
.payment-title {
	color: #fff;
	font-size: 16px;
	font-weight: 600;
	display: flex;
	align-items: center;
	gap: 8px;
}
.payment-title i {
	font-size: 14px;
	opacity: 0.8;
}
.payment-stats {
	display: flex;
	flex-wrap: wrap;
	gap: 6px;
}
.payment-stat {
	display: inline-flex;
	align-items: center;
	background: rgba(255,255,255,0.12);
	border-radius: 4px;
	padding: 4px 10px;
	font-size: 12px;
	color: rgba(255,255,255,0.85);
	gap: 6px;
}
.payment-stat span {
	color: #fff;
	font-weight: 600;
}
.payment-search {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 10px 12px;
	margin-bottom: 10px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.payment-search-row {
	display: flex;
	align-items: center;
	flex-wrap: wrap;
	gap: 8px;
}
.payment-search-group {
	display: flex;
	align-items: center;
	gap: 4px;
}
.payment-search-group select {
	padding: 6px 8px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-size: 12px;
	background: #f8f9fa;
	min-width: 70px;
}
.payment-search-group select:focus {
	outline: none;
	border-color: #00695c;
}
.payment-search-group input[type="text"] {
	padding: 6px 8px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-size: 12px;
	background: #f8f9fa;
	width: 80px;
}
.payment-search-group input[type="text"]:focus {
	outline: none;
	border-color: #00695c;
	background: #fff;
}
.search-divider {
	width: 1px;
	height: 24px;
	background: #e0e0e0;
	margin: 0 6px;
}
.search-input-group {
	display: flex;
	align-items: center;
	gap: 4px;
}
.search-input-group input[type="text"] {
	padding: 6px 10px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-size: 12px;
	width: 100px;
}
.search-input-group input[type="text"]:focus {
	outline: none;
	border-color: #00695c;
}
.btn-search {
	padding: 6px 12px;
	background: #00695c;
	color: #fff;
	border: none;
	border-radius: 4px;
	font-size: 12px;
	cursor: pointer;
	transition: background 0.15s;
}
.btn-search:hover {
	background: #00897b;
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
.fee-legend {
	display: flex;
	align-items: center;
	gap: 8px;
	font-size: 11px;
}
@media (max-width: 768px) {
	.payment-header-top {
		flex-direction: column;
		align-items: flex-start;
	}
	.payment-search-row {
		flex-direction: column;
		align-items: flex-start;
	}
	.search-divider {
		display: none;
	}
}
</style>

<div class="payment-header">
	<div class="payment-header-top">
		<div class="payment-title">
			<i class="fa fa-percent"></i>
			수수료 관리
		</div>
		<div class="payment-stats">
			<div class="payment-stat">총 TID <span><?php echo number_format($total_count); ?></span></div>
		</div>
	</div>
</div>

<form id="fsearch" name="fsearch" method="get">
<input type="hidden" name="p" value="<?php echo $p; ?>">
<div class="payment-search">
	<div class="payment-search-row">
		<?php if($is_admin) { ?>
		<div class="payment-search-group">
			<select name="dv_pg" id="dv_pg">
				<option value="">PG사</option>
				<option value="4" <?php if($dv_pg == "4") { echo "selected"; } ?>>페이시스</option>
				<option value="5" <?php if($dv_pg == "5") { echo "selected"; } ?>>섹타나인</option>
				<option value="7" <?php if($dv_pg == "7") { echo "selected"; } ?>>루트업</option>
				<option value="winglobal" <?php if($dv_pg == "winglobal") { echo "selected"; } ?>>윈글로벌</option>
			</select>
			<select name="dv_type" id="dv_type">
				<option value="">결제종류</option>
				<option value="1" <?php if($dv_type == "1") { echo "selected"; } ?>>오프라인</option>
				<option value="2" <?php if($dv_type == "2") { echo "selected"; } ?>>온라인</option>
			</select>
			<select name="dv_certi" id="dv_certi">
				<option value="">인증/비인증</option>
				<option value="1" <?php if($dv_certi == "1") { echo "selected"; } ?>>인증</option>
				<option value="2" <?php if($dv_certi == "2") { echo "selected"; } ?>>비인증</option>
				<option value="3" <?php if($dv_certi == "3") { echo "selected"; } ?>>구인증</option>
			</select>
		</div>
		<div class="search-divider"></div>
		<div class="payment-search-group">
			<select name="memberb" id="memberb">
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
			<select name="memberc" id="memberc">
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
			<select name="memberd" id="memberd">
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
			<select name="membere" id="membere">
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
		<div class="search-divider"></div>
		<?php } ?>
		<div class="payment-search-group">
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
		</div>
		<div class="search-divider"></div>
		<div class="search-input-group">
			<input type="text" name="mb_6_name" value="<?php echo $mb_6_name ?>" id="mb_6_name" placeholder="가맹점명">
			<input type="text" name="dv_tid" value="<?php echo $dv_tid ?>" id="dv_tid" placeholder="TID">
			<button type="submit" class="btn-search">검색</button>
			<?php if($is_admin) { ?>
			<button type="button" class="btn-excel" id="xlsx"><i class="fa fa-file-excel-o"></i> 엑셀</button>
			<?php } ?>
		</div>
		<div class="search-divider"></div>
		<div class="fee-legend">
			<span class="fee1" style="letter-spacing: -1px;">설정 수수료</span>
			<span class="fee2" style="letter-spacing: -1px;">수익 수수료</span>
		</div>
	</div>
</div>
</form>







<form action="./xlsx/tid_fee.php" id="frm_xlsx" method="post">
<input type="hidden" name="xlsx_sql" value="<?php echo $xlsx_sql; ?>">
<input type="hidden" name="p" value="<?php echo $p; ?>">
</form>







<form name="fmember" id="fmember" action="./?p=tid_fee_update" method="post" enctype="multipart/form-data">
<input type="hidden" name="mb_1_name" value="<?php echo $mb_1_name ?>">
<input type="hidden" name="mb_2_name" value="<?php echo $mb_2_name ?>">
<input type="hidden" name="mb_3_name" value="<?php echo $mb_3_name ?>">
<input type="hidden" name="mb_4_name" value="<?php echo $mb_4_name ?>">
<input type="hidden" name="mb_5_name" value="<?php echo $mb_5_name ?>">
<input type="hidden" name="mb_6_name" value="<?php echo $mb_6_name ?>">


<input type="hidden" name="memberb" value="<?php echo $memberb ?>">
<input type="hidden" name="memberc" value="<?php echo $memberc ?>">
<input type="hidden" name="memberd" value="<?php echo $memberd ?>">
<input type="hidden" name="membere" value="<?php echo $membere ?>">

<input type="hidden" name="dv_tid" value="<?php echo $dv_tid ?>">
<input type="hidden" name="search_dv_pg" value="<?php echo $dv_pg ?>">
<input type="hidden" name="search_dv_type" value="<?php echo $dv_type ?>">
<input type="hidden" name="search_dv_certi" value="<?php echo $dv_certi ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="dv_id" value="<?php echo $dv_id ?>">


<div class="m_board_scroll">
	<div class="m_table_wrap">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
				<tr>
					<th style="width:50px;" rowspan="2">번호</th>
					<?php if($is_admin) { ?>
					<th style="width:50px;" rowspan="2">구분</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 8) { ?>
					<th colspan="2">본사</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 7) { ?>
					<th colspan="2">지사</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 6) { ?>
					<th colspan="2">총판</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 5) { ?>
					<th colspan="2">대리점</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 4) { ?>
					<th colspan="2">영업점</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 3) { ?>
					<th colspan="2">가맹점</th>
					<?php } ?>

					<th rowspan="2">TID</th>
					<th rowspan="2">PG</th>
					<th rowspan="2">오프라인/온라인</th>
					<th rowspan="2">인증/비인증</th>
					<th rowspan="2">차액정산 MBR</th>
					<th rowspan="2">등록일시</th>
					<?php if($member['mb_level'] >= 8) { ?>
					<th rowspan="2">관리</th>
					<?php } ?>
				</tr>
				<tr>

					<?php if($member['mb_level'] >= 8) { ?>
					<th>사명</th>
					<th>수수료</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 7) { ?>
					<th>지사</th>
					<th>수수료</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 6) { ?>
					<th>총판</th>
					<th>수수료</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 5) { ?>
					<th>대리점</th>
					<th>수수료</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 4) { ?>
					<th>영업점</th>
					<th>수수료</th>
					<?php } ?>

					<?php if($member['mb_level'] >= 3) { ?>
					<th>가맹점</th>
					<th>수수료</th>
					<?php } ?>
				</tr>
				</thead>
				<tbody>
				<?php
					for ($i=0; $row=sql_fetch_array($result); $i++) {
					$num = number_format($total_count - ($page - 1) * $rows - $i);



					if($row['dv_pg'] == 0) {
						$dv_pgs = "코페이";
					} else if($row['dv_pg'] == 1) {
						$dv_pgs = "다날";
					} else if($row['dv_pg'] == 2) {
						$dv_pgs = "광원";
					} else if($row['dv_pg'] == 3) {
						$dv_pgs = "웰컴";
					} else if($row['dv_pg'] == 4) {
						$dv_pgs = "페이시스";
					} else if($row['dv_pg'] == 5) {
						$dv_pgs = "섹타나인";
					} else if($row['dv_pg'] == 6) {
						$dv_pgs = "다우";
					} else if($row['dv_pg'] == 7) {
						$dv_pgs = "루트업";
					}

					if($row['dv_type'] == 1) {
						$dv_types = "오프라인";
					} else {
						$dv_types = "온라인";
					}
					if($row['dv_certi'] == 1) {
						$dv_certis = "인증";
					} else if($row['dv_certi'] == 2) {
						$dv_certis = "비인증";
					} else if($row['dv_certi'] == 3) {
						$dv_certis = "구인증";
					}


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
					$mb_6_fee = 100 - $row['mb_6_fee'];


					if($row['mb_1_name']) { $mb_1_fee = sprintf('%0.4f', $mb_1_fee); } else { $mb_1_fee = ""; }
					if($row['mb_2_name']) { $mb_2_fee = sprintf('%0.4f', $mb_2_fee); } else { $mb_2_fee = ""; }
					if($row['mb_3_name']) { $mb_3_fee = sprintf('%0.4f', $mb_3_fee); } else { $mb_3_fee = ""; }
					if($row['mb_4_name']) { $mb_4_fee = sprintf('%0.4f', $mb_4_fee); } else { $mb_4_fee = ""; }
					if($row['mb_5_name']) { $mb_5_fee = sprintf('%0.4f', $mb_5_fee); } else { $mb_5_fee = ""; }
					if($row['mb_6_name']) { $mb_6_fee = sprintf('%0.4f', $mb_6_fee); } else { $mb_6_fee = ""; }

					if($row['mb_1_name']) { $mb_1_fee = $mb_1_fee; } else { $mb_1_fee = ""; }
					if($row['mb_2_name']) { $mb_2_fee = $mb_2_fee; } else { $mb_2_fee = ""; }
					if($row['mb_3_name']) { $mb_3_fee = $mb_3_fee; } else { $mb_3_fee = ""; }
					if($row['mb_4_name']) { $mb_4_fee = $mb_4_fee; } else { $mb_4_fee = ""; }
					if($row['mb_5_name']) { $mb_5_fee = $mb_5_fee; } else { $mb_5_fee = ""; }
					if($row['mb_6_name']) { $mb_6_fee = $mb_6_fee; } else { $mb_6_fee = ""; }

					if($dv_id == $row['dv_id']) {
				?>
				<tr id="row-<?php echo $row['dv_id']; ?>">
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php echo $num; ?></td>
					<?php if($is_admin) { ?>
					<td style="text-align:center; font-weight:bold; color:<?php echo $row['mb_settle_gbn'] == 'Y' ? '#4caf50' : '#f44336'; ?>; <?php if($results != "ok") { echo "background:#ffff99"; } ?>">
						<?php echo $row['mb_settle_gbn'] == 'Y' ? 'O' : 'X'; ?>
					</td>
					<?php } ?>

					<?php if($member['mb_level'] >= 8) { ?>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;" class="td_name"><?php if($row['mb_1_name']) { ?><?php echo $row['mb_1_name']; ?><?php } ?></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php if($row['mb_1_name']) { ?><input type="text" autocomplete="off" name="mb_1_fee" value="<?php echo $row['mb_1_fee']; ?>" required class="frm_input" style="text-align:right" size="4" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '');"> %<?php } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 7) { ?>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;" class="td_name"><?php if($row['mb_2_name']) { ?><?php echo $row['mb_2_name']; ?><?php } ?></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php if($row['mb_2_name']) { ?><input type="text" autocomplete="off" name="mb_2_fee" value="<?php echo $row['mb_2_fee']; ?>" required class="frm_input" style="text-align:right" size="4" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '');"> %<?php } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 6) { ?>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;" class="td_name"><?php if($row['mb_3_name']) { ?><?php echo $row['mb_3_name']; ?><?php } ?></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php if($row['mb_3_name']) { ?><input type="text" autocomplete="off" name="mb_3_fee" value="<?php echo $row['mb_3_fee']; ?>" required class="frm_input" style="text-align:right" size="4" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '');"> %<?php } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 5) { ?>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;" class="td_name"><?php if($row['mb_4_name']) { ?><?php echo $row['mb_4_name']; ?><?php } ?></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php if($row['mb_4_name']) { ?><input type="text" autocomplete="off" name="mb_4_fee" value="<?php echo $row['mb_4_fee']; ?>" required class="frm_input" style="text-align:right" size="4" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '');"> %<?php } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 4) { ?>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;" class="td_name"><?php if($row['mb_5_name']) { ?><?php echo $row['mb_5_name']; ?><?php } ?></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php if($row['mb_5_name']) { ?><input type="text" autocomplete="off" name="mb_5_fee" value="<?php echo $row['mb_5_fee']; ?>" required class="frm_input" style="text-align:right" size="4" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '');"> %<?php } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 3) { ?>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;" class="td_name"><?php if($row['mb_6_name']) { ?><?php echo $row['mb_6_name']; ?><?php } ?></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php if($row['mb_6_name']) { ?><input type="text" autocomplete="off" name="mb_6_fee" value="<?php echo $row['mb_6_fee']; ?>" required class="frm_input" style="text-align:right" size="4" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '');"> %<?php } ?></td>
					<?php } ?>

					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php echo $row['dv_tid']; ?></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;">
						<select name="dv_pg" required class="required" style="width:70px">
							<option value="">PG사</option>
							<option value="4" <?php if($row['dv_pg'] == "4") { echo "selected"; } ?>>페이시스</option>
							<option value="5" <?php if($row['dv_pg'] == "5") { echo "selected"; } ?>>섹타나인</option>
							<option value="7" <?php if($row['dv_pg'] == "7") { echo "selected"; } ?>>루트업</option>
						</select>
					</td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;">
						<select name="dv_type" required class="required" style="width:70px">
							<option value="">결제종류</option>
							<option value="1" <?php if($row['dv_type'] == "1") { echo "selected"; } ?>>오프라인</option>
							<option value="2" <?php if($row['dv_type'] == "2") { echo "selected"; } ?>>온라인</option>
						</select>
					</td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;">
						<select name="dv_certi" required class="required" style="width:70px">
							<option value="">인증/비인증</option>
							<option value="1" <?php if($row['dv_certi'] == "1") { echo "selected"; } ?>>인증</option>
							<option value="2" <?php if($row['dv_certi'] == "2") { echo "selected"; } ?>>비인증</option>
							<option value="3" <?php if($row['dv_certi'] == "3") { echo "selected"; } ?>>구인증</option>
						</select>
					</td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><input type="text" autocomplete="off" name="sftp_mbrno" value="<?php echo $row['sftp_mbrno']; ?>" class="frm_input" size="4"></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;">
						<button type="submit" class="btn_admin">저장</button>
					</td>
				</tr>
				<?php } else { ?>
				<tr id="row-<?php echo $row['dv_id']; ?>">
					<td><?php echo $num; ?></td>
					<?php if($is_admin) { ?>
					<td style="text-align:center; font-weight:bold; color:<?php echo $row['mb_settle_gbn'] == 'Y' ? '#4caf50' : '#f44336'; ?>">
						<?php echo $row['mb_settle_gbn'] == 'Y' ? 'O' : 'X'; ?>
					</td>
					<?php } ?>

					<?php if($member['mb_level'] >= 8) { ?>
					<td class="td_name"><?php if($row['mb_1_name']) { echo $row['mb_1_name']; } ?></td>
					<td><?php if($row['mb_1_name']) { echo "<span class='fee1'>".$row['mb_1_fee']."</span><span class='fee2'>".$mb_1_fee."</span>"; } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 7) { ?>
					<td class="td_name"><?php if($row['mb_2_name']) { echo $row['mb_2_name']; } ?></td>
					<td><?php if($row['mb_2_name']) { echo "<span class='fee1'>".$row['mb_2_fee']."</span><span class='fee2'>".$mb_2_fee."</span>"; } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 6) { ?>
					<td class="td_name"><?php if($row['mb_3_name']) { echo $row['mb_3_name']; } ?></td>
					<td><?php if($row['mb_3_name']) { echo "<span class='fee1'>".$row['mb_3_fee']."</span><span class='fee2'>".$mb_3_fee."</span>"; } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 5) { ?>
					<td class="td_name"><?php if($row['mb_4_name']) { echo $row['mb_4_name']; } ?></td>
					<td><?php if($row['mb_4_name']) { echo "<span class='fee1'>".$row['mb_4_fee']."</span><span class='fee2'>".$mb_4_fee."</span>"; } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 4) { ?>
					<td class="td_name"><?php if($row['mb_5_name']) { echo $row['mb_5_name']; } ?></td>
					<td><?php if($row['mb_5_name']) { echo "<span class='fee1'>".$row['mb_5_fee']."</span><span class='fee2'>".$mb_5_fee."</span>"; } ?></td>
					<?php } ?>

					<?php if($member['mb_level'] >= 3) { ?>
					<td class="td_name"><?php if($row['mb_6_name']) { echo $row['mb_6_name']; } ?></td>
					<td><?php if($row['mb_6_name']) { echo "<span class='fee1'>".$row['mb_6_fee']."</span><span class='fee2'>".$mb_6_fee."</span>"; } ?></td>
					<?php } ?>

					<td><?php echo $row['dv_tid']; ?></td>
					<td><?php echo $dv_pgs; ?></td>
					<td><?php echo $dv_types; ?></td>
					<td><?php echo $dv_certis; ?></td>
					<td><?php echo $row['sftp_mbrno']; ?></td>
					<td><?php echo $row['datetime']; ?></td>
					<?php if($member['mb_level'] >= 8) { ?>
					<td class="is-actions-cell">
						<div class="buttons">
							<a href="./?p=tid_fee&dv_pg=<?php echo $dv_pg; ?>&dv_type=<?php echo $dv_type; ?>&dv_certi=<?php echo $dv_certi; ?>&membera=<?php echo $membera; ?>&memberb=<?php echo $memberb; ?>&memberc=<?php echo $memberc; ?>&memberd=<?php echo $memberd; ?>&membere=<?php echo $membere; ?>&memberf=<?php echo $memberf; ?>&mb_nick=<?php echo $mb_nick; ?>&dv_tid=<?php echo $dv_tid; ?>&dv_id=<?php echo $row['dv_id']; ?>&mb_6_name=<?php echo $mb_6_name; ?>&page_count=<?php echo $page_count; ?>&page=<?php echo $page; ?>#row-<?php echo $row['dv_id']; ?>" class="btn_b btn_b02">수정</a>

							<a href="./?p=tid_fee_delete&dv_id=<?php echo $row['dv_id']; ?>&page_count=<?php echo $page_count; ?>&page=<?php echo $page; ?>" class="btn_b btn_b06" onclick="return confirm('정말 <?php echo $row['mb_6_name']; ?>가맹점을 삭제 하시겠습니까');">삭제</a>
						</div>
					</td>
					<?php } ?>
				</tr>
				<?php } } ?>
			</tbody>
		</table>
	</div>
</div>
</form>
	<?php
		//http://cajung.com/new4/?p=payment&fr_date=20220906&to_date=20220906&sfl=mb_id&stx=
		$qstr = "p=".$p;

		$qstr .= "&membera=".$membera;
		$qstr .= "&memberb=".$memberb;
		$qstr .= "&memberc=".$memberc;
		$qstr .= "&memberd=".$memberd;
		$qstr .= "&membere=".$membere;
		$qstr .= "&memberf=".$memberf;


		$qstr .= "&mb_1_name=".$mb_1_name;
		$qstr .= "&mb_2_name=".$mb_2_name;
		$qstr .= "&mb_3_name=".$mb_3_name;
		$qstr .= "&mb_4_name=".$mb_4_name;
		$qstr .= "&mb_5_name=".$mb_5_name;
		$qstr .= "&memberf=".$memberf;


		$qstr .= "&dv_pg=".$dv_pg;
		$qstr .= "&dv_type=".$dv_type;
		$qstr .= "&dv_certi=".$dv_certi;
		$qstr .= "&page_count=".$page_count;
		echo get_paging_news(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page=');
	?>


<script>

	$("#membera, #memberb, #memberc, #memberd, #membere, #memberf, #dv_pg, #dv_type, #dv_certi").change(function() {
		$("#membera, #memberb, #memberc, #memberd, #membere, #memberf, #dv_pg, #dv_type, #dv_certi").val();
		$(this).parents().filter("form").submit();
	});

	// 스크롤 위치 복원
	$(document).ready(function() {
		if(window.location.hash) {
			var hash = window.location.hash;
			var targetRow = $(hash);

			if(targetRow.length) {
				setTimeout(function() {
					$('html, body').animate({
						scrollTop: targetRow.offset().top - 100
					}, 400);

					// 해당 행 하이라이트 효과
					targetRow.addClass('highlight-flash');
					setTimeout(function() {
						targetRow.removeClass('highlight-flash');
					}, 2000);
				}, 100);
			}
		}
	});
</script>

<style>
@keyframes flashHighlight {
	0%, 100% { background-color: transparent; }
	50% { background-color: #fff3cd; }
}
.highlight-flash td {
	animation: flashHighlight 1.5s ease-in-out;
}
</style>