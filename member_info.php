<?php

	$g5['title'] = '회원 접속정보';

	if($is_admin) { // 지사

		if(adm_sql_common) {
			$adm_sql = " mb_1 IN (".adm_sql_common.")";
		} else {
			$adm_sql = " (1)";
		}

	} else if($member['mb_level'] == 7) { // 지사
		$sql_search = " mb_2 = '{$member['mb_id']}' ";
	} else if($member['mb_level'] == 6) { // 총판
		$sql_search = " mb_3 = '{$member['mb_id']}' ";
	} else if($member['mb_level'] == 5) { // 대리점
		$sql_search = " mb_4 = '{$member['mb_id']}' ";
	} else if($member['mb_level'] == 4) { // 영업점
		$sql_search = " mb_5 = '{$member['mb_id']}' ";
	}

	
	$sql_common = " from {$g5['member_table']} where ".$adm_sql;


	if($type == "2") {
		$title_s = "본사";
	} else if($type == "3") {
		$title_s = "지사";
	} else if($type == "4") {
		$title_s = "총판";
	} else if($type == "5") {
		$title_s = "대리점";
	} else if($type == "6") {
		$title_s = "영업점";
	} else if($type == "7") {
		$title_s = "가맹점";
	} else {
		$app = '0';
	}


	if($mb_level) {
		$sql_search .= " and mb_level = '{$mb_level}' ";
	}

	// 상호명 검색
	if($mb_nick) {
		$sql_search .= " and mb_nick like '%{$mb_nick}%' ";
	}

	if($dv_tid) {
		$sql_search .= " and dv_tid = '{$dv_tid}' ";
	}


	$sql = " select count(*) as cnt {$sql_common} {$sql_search} order by mb_no desc ";
	$row = sql_fetch($sql);

	$total_count = $row['cnt']; // 전체개수

	if($page_count) {
		$rows = $page_count;
	} else {
		$rows = $config['cf_page_rows'];
	}

	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함

	$sql = " select * {$sql_common} {$sql_search} order by mb_no desc limit {$from_record}, {$rows} ";
	$result = sql_query($sql);

//	echo $sql;
//	echo $total_pay;
?>
<style>
	.etitle {font-size:0.85em; font-weight:100; color:blue}
	.stitle {font-size:0.75em; font-weight:100; color:#999}

/* 헤더 스타일 */
.member-info-header {
	background: linear-gradient(135deg, #ff6f00 0%, #ff8f00 100%);
	border-radius: 8px;
	padding: 12px 16px;
	margin-bottom: 10px;
	box-shadow: 0 2px 8px rgba(255, 111, 0, 0.2);
}
.member-info-title {
	color: #fff;
	font-size: 16px;
	font-weight: 600;
	display: flex;
	align-items: center;
	gap: 8px;
}
.member-info-title i {
	font-size: 14px;
	opacity: 0.8;
}
/* 검색 영역 */
.member-info-search {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 10px 12px;
	margin-bottom: 10px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.member-info-search-row {
	display: flex;
	align-items: center;
	flex-wrap: wrap;
	gap: 8px;
}
.level-btns {
	display: flex;
	gap: 4px;
	flex-wrap: wrap;
}
.level-btns button {
	padding: 6px 12px;
	font-size: 12px;
	border: 1px solid #ddd;
	background: #f8f9fa;
	border-radius: 4px;
	cursor: pointer;
	color: #555;
	transition: all 0.15s;
}
.level-btns button:hover {
	background: #ff6f00;
	border-color: #ff6f00;
	color: #fff;
}
.level-btns button.active {
	background: #ff6f00;
	border-color: #ff6f00;
	color: #fff;
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
	width: 120px;
	padding: 6px 8px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-size: 13px;
	background: #f8f9fa;
}
.search-input-group input[type="text"]:focus {
	outline: none;
	border-color: #ff6f00;
	background: #fff;
}
.btn-search {
	padding: 6px 12px;
	background: #ff6f00;
	color: #fff;
	border: none;
	border-radius: 4px;
	font-size: 12px;
	cursor: pointer;
	transition: background 0.15s;
}
.btn-search:hover {
	background: #ff8f00;
}
@media (max-width: 768px) {
	.member-info-search-row {
		flex-direction: column;
		align-items: flex-start;
	}
	.search-divider {
		display: none;
	}
	.level-btns {
		width: 100%;
	}
}
</style>

<div class="member-info-header">
	<div class="member-info-title">
		<i class="fa fa-users"></i>
		회원 접속정보
	</div>
</div>

<form id="fsearch" name="fsearch" method="get">
<input type="hidden" name="p" value="<?php echo $p; ?>">
<div class="member-info-search">
	<div class="member-info-search-row">
		<!-- 레벨 단축 버튼 -->
		<div class="level-btns">
			<?php if($member['mb_level'] >= 8) { ?>
			<button type="submit" class="<?php if($mb_level == "8") { echo "active"; } ?>" value="8" name="mb_level">본사</button>
			<?php } ?>
			<?php if($member['mb_level'] >= 7) { ?>
			<button type="submit" class="<?php if($mb_level == "7") { echo "active"; } ?>" value="7" name="mb_level">지사</button>
			<?php } ?>
			<?php if($member['mb_level'] >= 6) { ?>
			<button type="submit" class="<?php if($mb_level == "6") { echo "active"; } ?>" value="6" name="mb_level">총판</button>
			<?php } ?>
			<?php if($member['mb_level'] >= 5) { ?>
			<button type="submit" class="<?php if($mb_level == "5") { echo "active"; } ?>" value="5" name="mb_level">대리점</button>
			<?php } ?>
			<?php if($member['mb_level'] >= 4) { ?>
			<button type="submit" class="<?php if($mb_level == "4") { echo "active"; } ?>" value="4" name="mb_level">영업점</button>
			<?php } ?>
			<?php if($member['mb_level'] >= 3) { ?>
			<button type="submit" class="<?php if($mb_level == "3") { echo "active"; } ?>" value="3" name="mb_level">가맹점</button>
			<?php } ?>
		</div>

		<div class="search-divider"></div>

		<!-- 검색 필드 -->
		<div class="search-input-group">
			<input type="text" name="mb_nick" value="<?php echo $mb_nick ?>" id="mb_nick" placeholder="상호명">
			<button type="submit" class="btn-search">검색</button>
		</div>
	</div>
</div>
</form>


<div class="m_board_scroll">
	<div class="m_table_wrap">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<tr>
				<th>번호</th>
				<?php /*
				<th>상위</th>
				*/ ?>
				<th style="width:46%;">상호명</th>
				<th style="width:46%;">복사</th>
			</tr>
			</thead>
			<tbody>
			<?php
				for ($i=0; $row=sql_fetch_array($result); $i++) {
					$num = number_format($total_count - ($page - 1) * $rows - $i);
					if($row['mb_level'] == 8) {
						$stitle = "본사";
					} else if($row['mb_level'] == 7) {
						$stitle = "지사";
					} else if($row['mb_level'] == 6) {
						$stitle = "총판";
					} else if($row['mb_level'] == 5) {
						$stitle = "대리점";
					} else if($row['mb_level'] == 4) {
						$stitle = "영업점";
					} else if($row['mb_level'] == 3) {
						$stitle = "가맹점";
					}
			?>
			<tr>
				<td style="width:50px;"><?php echo $num; ?></td>
				<?php /*
				<td class="td_name">
				<?php
					if($row['mb_1']) {
						$mb1 = get_member($row['mb_1']);
						echo "본　사 : <b>".$mb1['mb_nick']."</b>";
					}
					if($row['mb_2']) {
						$mb2 = get_member($row['mb_2']);
						echo "<br>지　사 : <b>".$mb2['mb_nick']."</b>";
					}
					if($row['mb_3']) {
						$mb3 = get_member($row['mb_3']);
						echo "<br>총　판 : <b>".$mb3['mb_nick']."</b>";
					}
					if($row['mb_4']) {
						$mb4 = get_member($row['mb_4']);
						echo "<br>대리점 : <b>".$mb4['mb_nick']."</b>";
					}
					if($row['mb_5']) {
						$mb5 = get_member($row['mb_5']);
						echo "<br>영업점 : <b>".$mb5['mb_nick']."</b>";
					}
				?>
				</td>
				*/ ?>
				<td class="td_name">
					<div><span class="etitle"><?php echo $stitle; ?></span></div>
					<b><?php echo $row['mb_nick']; ?></b>
					<div><span class="stitle">아이디 : <?php echo $row['mb_id']; ?></span></div>
					<div><span class="stitle">수수료 : <?php echo sprintf('%0.2f', $row['mb_homepage']); ?>%</span></div>
					<div><span class="stitle">대표자 : <?php echo $row['mb_name']; ?></span></div>
				</td>
				<td>
				<textarea style="width:100%; height:120px; border:0" readonly id="copy_<?php echo $i; ?>">
- 선샤인페이 판매자센터 -
상호명 : <?php echo $row['mb_nick']; ?> (<?php echo $stitle; ?>)
접속주소 : https://wspay.net
아이디 : <?php echo $row['mb_id']; ?>

비밀번호 : <?php echo $row['mb_birth']; ?></textarea>

		<script>
		$("#copy_<?php echo $i; ?>").click(function() {
		var content = document.getElementById('copy_<?php echo $i; ?>');
		content.select();
		document.execCommand('copy');
		});
		</script>
				</td>

			</tr>
			<?php
				}
			?>
			</tbody>
		</table>
	</div>
</div>
<?php
	//http://cajung.com/new4/?p=payment&fr_date=20220906&to_date=20220906&sfl=mb_id&stx=
	$qstr = "p=".$p;
	$qstr .= "&fr_date=".$fr_date;
	$qstr .= "&to_date=".$to_date;
	$qstr .= "&sfl=".$sfl;
	$qstr .= "&stx=".$stx;
	echo get_paging_news(G5_IS_MOBILE ? "5" : "5", $page, $total_page, '?' . $qstr . '&amp;page=');
?>



<script>
function company_search(name){
	document.getElementById("company_name").value = name;
	var form = document.pay_success_search;
	form.submit();
}

$(document).ready(function() {
	$('.company-name').click(function(){
		var over_code = $(this).data('over_coe');
		$('#'+over_code).toggle();
	});
});

$("#l1, #l2, #l3, #l4, #l5, #l6, #l7, #dview").change(function() {
	$(this).parents().filter("form").submit();
});
</script>