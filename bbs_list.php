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

<div class="index_menu">
	<ul class="shortcut">
		<li class="sc_current"><a><?php echo $title2; ?></a></li>
		<li class="sc_visit">
			<aside id="visit">
			</aside>
		</li>
	</ul>
</div>


<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<input type="hidden" name="p" value="<?php echo $p; ?>">
<input type="hidden" name="t" value="<?php echo $t; ?>">
<input type="hidden" name="sfl" value="wr_subject">
	<div class="searchbox">
		<div class="midd">
			<ul>
				<li>
					<strong>검색</strong>
					<div>
						<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input" size="7" placeholder="제목검색" style="width:150px;">
						<button type="submit" class="btn_black"><span>검색</span></button>
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
					<th style="width:50px;">번호</th>
					<th>제목</th>
					<th style="width:100px;">작성자</th>
					<th style="width:150px;">작성일</th>
					<th style="width:50px;">읽음</th>
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
					<td class="center"><?php echo $row['wr_name']; ?></td>
					<td style="text-align:center;"><?php echo $row['wr_datetime']; ?></td>
					<td style="text-align:center;"><?php echo $row['wr_hit']; ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>
<div style="padding:10px 0;">
	<a href="./?p=bbs&t=<?php echo $t; ?>&v=write&page=<?php echo $page; ?>" class="btn_cancel">글쓰기</a>
</div>
<?php
	//http://cajung.com/new4/?p=payment&fr_date=20220906&to_date=20220906&sfl=mb_id&stx=
	$qstr = "p=".$p;
	$qstr = "t=".$t;
	$qstr .= "&sfl=".$sfl;
	$qstr .= "&stx=".$stx;
	echo get_paging_news(G5_IS_MOBILE ? "5" : "5", $page, $total_page, '?' . $qstr . '&amp;page=');
?>
