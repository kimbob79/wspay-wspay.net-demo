<?php


	$sql_common = " from {$write_table} ";
	$sql_search = " where (1) ";

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
	
	// 레벨별 열람 필터 (관리자는 전체 조회)
	if(!$is_admin && $member['mb_level'] >= 3 && $member['mb_level'] <= 8) {
		$wr_field = 'wr_' . (10 - $member['mb_level']);
		$sql_search .= " AND ({$wr_field} = 'Y' OR (wr_2 = '' AND wr_3 = '' AND wr_4 = '' AND wr_5 = '' AND wr_6 = '' AND wr_7 = ''))";
	}

	if(!$sst)
		$sst  = "wr_num, wr_reply";

	if ($sst) {
		$sql_order = " order by {$sst} {$sod} ";
	}

	$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
	$row = sql_fetch($sql);
	$total_count = $row['cnt'];


	$rows = $config['cf_page_rows'];
	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함

	$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
	$result = sql_query($sql);

//	echo $sql;
?>

<style>
.notice-header {
	background: linear-gradient(135deg, #00838f 0%, #00acc1 100%);
	border-radius: 8px;
	padding: 12px 16px;
	margin-bottom: 10px;
	box-shadow: 0 2px 8px rgba(0, 131, 143, 0.2);
}
.notice-header-top {
	display: flex;
	align-items: center;
	justify-content: space-between;
	flex-wrap: wrap;
	gap: 10px;
}
.notice-title {
	display: flex;
	align-items: center;
	gap: 10px;
	color: #fff;
	font-size: 18px;
	font-weight: 600;
}
.notice-title i {
	font-size: 20px;
}
.notice-search {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 10px 12px;
	margin-bottom: 10px;
}
.notice-search .search-row {
	display: flex;
	align-items: center;
	gap: 10px;
	flex-wrap: wrap;
}
.notice-search .search-row label {
	font-weight: 600;
	color: #333;
	font-size: 13px;
}
.notice-search .search-row input[type="text"] {
	width: 200px;
	padding: 6px 10px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-size: 13px;
	background: #f8f9fa;
}
.notice-search .search-row input[type="text"]:focus {
	outline: none;
	border-color: #00838f;
	background: #fff;
}
.notice-search .btn-search {
	padding: 6px 16px;
	background: #00838f;
	color: #fff;
	border: none;
	border-radius: 4px;
	font-size: 13px;
	cursor: pointer;
	transition: background 0.2s;
}
.notice-search .btn-search:hover {
	background: #006064;
}
@media (max-width: 768px) {
	.notice-header-top {
		flex-direction: column;
		align-items: flex-start;
	}
	.notice-search .search-row {
		flex-direction: column;
		align-items: flex-start;
	}
	.notice-search .search-row input[type="text"] {
		width: 100%;
	}
}
</style>

<div class="notice-header">
	<div class="notice-header-top">
		<div class="notice-title">
			<i class="fa fa-bullhorn"></i>
			<?php echo $title2; ?>
		</div>
	</div>
</div>

<form id="fsearch" name="fsearch" method="get">
<input type="hidden" name="p" value="<?php echo $p; ?>">
<input type="hidden" name="t" value="<?php echo $t; ?>">
<input type="hidden" name="sfl" value="wr_subject">
<div class="notice-search">
	<div class="search-row">
		<label>검색</label>
		<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" placeholder="제목검색">
		<button type="submit" class="btn-search"><i class="fa fa-search"></i> 검색</button>
	</div>
</div>
</form>



<div class="m_board_scroll">
	<div class="m_table_wrap">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
				<tr>
					<th style="width:50px;">번호</th>
					<th>제목</th>
					<th style="width:130px;">작성일</th>
				</tr>
			</thead>
			<tbody>
				<?php
					for ($i=0; $row=sql_fetch_array($result); $i++) {
					$num = number_format($total_count - ($page - 1) * $rows - $i);
				?>
				<tr>
					<td class="center"><?php echo $num; ?></td>
					<td class="td_name"><a href="./?p=<?php echo $p; ?>&t=<?php echo $t; ?>&v=view&id=<?php echo $row['wr_id']; ?>&page=<?php echo $page; ?>"><?php echo $row['wr_subject']; ?></a></td>
					<td style="text-align:center;"><?php echo substr($row['wr_datetime'], 0, 16); ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>
<?php if($is_admin || $member['mb_level'] >= $board['bo_write_level']) { ?>
<div style="padding:10px 0;">
	<a href="./?p=bbs&t=<?php echo $t; ?>&v=write&page=<?php echo $page; ?>" class="btn_cancel">글쓰기</a>
</div>
<?php } ?>
<?php
	//http://cajung.com/new4/?p=payment&fr_date=20220906&to_date=20220906&sfl=mb_id&stx=
	$qstr = "p=".$p;
	$qstr .= "&t=".$t;
	$qstr .= "&sfl=".$sfl;
	$qstr .= "&stx=".$stx;
	echo get_paging_news(G5_IS_MOBILE ? "5" : "5", $page, $total_page, '?' . $qstr . '&amp;page=');
?>
