<?php
	$title1 = "NOTI";
	$title2 = "NOTI 관리";

	$sql_common = " from g5_noti where (1) ";
	if($nt_mbrno) { $sql_search .= " and nt_mbrno = '{$nt_mbrno}' "; }

	if ($sst)
		$sql_order = " order by {$sst} {$sod} ";
	else
		$sql_order = " order by datetime desc ";

	$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
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

	$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
	$xlsx_sql = "select * {$sql_common} {$sql_search} {$sql_order} ";
	$result = sql_query($sql);

//	echo $sql."<br><br>";
//	echo $dv_pg;

?>
<style>

td span { /*font-family: 'FFF-Reaction-Trial';*/ font-size:11px;}
td span .fee_name {font-family: 'NanumGothic';}

.fee_left {float:left;}
.fee_right {float:right;}

.tid {font-weight:300; color:#999}
select { width:100px; }
.table_title {font-size:13px; margin:0 0 10px 5px; font-weight:700; color:#555}
</style>


<div class="index_menu">
	<ul class="shortcut">
		<li class="sc_current"><a>섹타나인 NOTI 외부전송</a></li>
		<li class="sc_visit">
			<aside id="visit">
			</aside>
		</li>
	</ul>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<input type="hidden" name="p" value="<?php echo $p; ?>">
	<div class="searchbox">
		<div class="midd">
			<ul>
				<li>
					<strong>검색</strong>
					<div>
						<input type="text" name="nt_mbrno" value="<?php echo $nt_mbrno ?>" id="nt_mbrno" class="frm_input" size="7" placeholder="MBR/TID" style="width:100px;">
						<button type="submit" class="btn_b btn_b02"><span>검색</span></button>
					</div>
				</li>
			</ul>
		</div>
	</div>
</form>


<form name="fmember" id="fmember" action="./?p=noti_update" method="post" enctype="multipart/form-data">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="nt_id" value="<?php echo $nt_id ?>">


<div class="m_board_scroll">
	<div class="m_table_wrap">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
				<tr>
					<th>NO</th>
					<th>구분</th>
					<th>MBR/TID</th>
					<th>URL</th>
					<th>MEMO</th>
					<th>마지막전송일시</th>
					<th>등록일</th>
					<th>수정일</th>
					<?php if($member['mb_level'] >= 8) { ?>
					<th>관리</th>
					<?php } ?>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td style="height:59px;"></td>
					<td>
						<?php if(!$nt_id) { ?>
						<select name="nt_category" required class="required" style="width:70px">
							<option value="">선택</option>
							<option value="MBR">MBR</option>
							<option value="TID">TID</option>
						</select>
						<?php } ?>
					</td>
					<td><?php if(!$nt_id) { ?><input type="text" autocomplete="off" name="nt_mbrno" value="<?php echo $row['nt_mbrno']; ?>" required class="frm_input" size="6" placeholder="MBR"><?php } ?></td>
					<td><?php if(!$nt_id) { ?><input type="text" autocomplete="off" name="nt_url" value="<?php echo $row['nt_url']; ?>" required class="frm_input"  placeholder="URL" style="width:100%"><?php } ?></td>
					<td><?php if(!$nt_id) { ?><input type="text" autocomplete="off" name="nt_memo" value="<?php echo $row['nt_memo']; ?>" maxlength="10" class="frm_input" style="width:100%" placeholder="메모"><?php } ?></td>
					<td></td>
					<td></td>
					<td></td>
					<td>
						<?php if(!$nt_id) { ?><button type="submit" class="btn_admin">신규등록</button><?php } ?>
					</td>
				</tr>
				<?php
					for ($i=0; $row=sql_fetch_array($result); $i++) {
					$num = number_format($total_count - ($page - 1) * $rows - $i);

					if($row['sm_type'] == 00) {
						$sm_type = "신규";
					} else if($row['sm_type'] == 01) {
						$sm_type = "해지";
					} else if($row['dv_pg'] == 02) {
						$sm_type = "변경";
					}

					if($nt_id == $row['nt_id']) {
				?>
				<tr>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php echo $num; ?></td>
					<td>
						<select name="nt_category" required class="required" style="width:70px">
							<option value="MBR" <?php if($row['nt_category'] == "MBR") { echo "selected"; } ?>>MBR</option>
							<option value="TID" <?php if($row['nt_category'] == "TID") { echo "selected"; } ?>>TID</option>
						</select>
					</td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><input type="text" autocomplete="off" name="nt_mbrno" value="<?php echo $row['nt_mbrno']; ?>" required class="frm_input" size="10"></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><input type="text" autocomplete="off" name="nt_url" value="<?php echo $row['nt_url']; ?>" required class="frm_input" style="width:100%"></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><input type="text" autocomplete="off" name="nt_memo" value="<?php echo $row['nt_memo']; ?>" class="frm_input" style="width:100%"></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;">
						<button type="submit" class="btn_admin">저장</button>
					</td>
				</tr>
				<?php } else { ?>
				<tr>
					<td><?php echo $num; ?></td>
					<td><?php echo $row['nt_category']; ?></td>
					<td><?php echo $row['nt_mbrno']; ?></td>
					<td style="text-align:left"><?php echo $row['nt_url']; ?></td>
					<td style="text-align:left"><?php echo $row['nt_memo']; ?></td>
					<td><?php if($row['lastupdate'] != "0000-00-00 00:00:00") { echo $row['lastupdate']; } else { echo "-"; } ?></td>
					<td><?php echo $row['datetime']; ?></td>
					<td><?php if($row['updatetime'] != "0000-00-00 00:00:00") { echo $row['updatetime']; } else { echo "-"; } ?></td>
					<?php if($member['mb_level'] >= 8) { ?>
					<td class="is-actions-cell">
						<div class="buttons">
							<a href="./?p=noti_list&nt_id=<?php echo $row['nt_id']; ?>&page=<?php echo $page; ?>" class="btn_b btn_b02">수정</a>
							<a href="./?p=noti_delete&nt_id=<?php echo $row['nt_id']; ?>&page=<?php echo $page; ?>" class="btn_b btn_b06" onclick="return confirm('정말 <?php echo $row['sm_bname']; ?>가맹점을 삭제 하시겠습니까');">삭제</a>
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
		$qstr .= "&fr_date=".$fr_date;
		$qstr .= "&to_date=".$to_date;
		$qstr .= "&sfl=".$sfl;
		$qstr .= "&stx=".$stx;

		$qstr .= "&membera=".$membera;
		$qstr .= "&memberb=".$memberb;
		$qstr .= "&memberc=".$memberc;
		$qstr .= "&memberd=".$memberd;
		$qstr .= "&membere=".$membere;
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
</script>