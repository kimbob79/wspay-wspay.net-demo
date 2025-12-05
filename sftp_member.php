<?php
	$title1 = "차액정산";
	$title2 = "회원 관리";

	$sql_common = " from g5_sftp_member where (1) ";
	if($sm_bname) { $sql_search .= " and sm_bname LIKE '%{$sm_bname}%' "; }
	if($sm_mbrno) { $sql_search .= " and sm_mbrno = '{$sm_mbrno}' "; }

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
		<li class="sc_current"><a>회원 관리</a></li>
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
						<input type="text" name="sm_bname" value="<?php echo $sm_bname ?>" id="sm_bname" class="frm_input" size="7" placeholder="업체명" style="width:100px;">
						<input type="text" name="sm_mbrno" value="<?php echo $sm_mbrno ?>" id="sm_mbrno" class="frm_input" size="7" placeholder="MBR" style="width:100px;">
						<button type="submit" class="btn_b btn_b02"><span>검색</span></button>
					</div>
				</li>
			</ul>
		</div>
	</div>
</form>


<form name="fmember" id="fmember" action="./?p=sftp_member_update" method="post" enctype="multipart/form-data">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="sm_id" value="<?php echo $sm_id ?>">


<div class="m_board_scroll">
	<div class="m_table_wrap">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
				<tr>
					<th>NO</th>
					<th>MBR</th>
					<th>구분</th>
					<th>오픈마켓사업자</th>
					<th>사업자등록번호</th>
					<th>업종명</th>
					<th>업체명</th>
					<th>주소</th>
					<th>대표자명</th>
					<th>전화번호</th>
					<th>이메일</th>
					<th>홈페이지</th>
					<th>수정일시</th>
					<th>등록일시</th>
					<th>반송코드</th>
					<?php if($member['mb_level'] >= 8) { ?>
					<th>관리</th>
					<?php } ?>
				</tr>
				</thead>
				<tbody>
				<?php if(!$sm_id) { ?>
				<tr>
					<td></td>
					<td><input type="text" autocomplete="off" name="sm_mbrno" value="<?php echo $row['sm_mbrno']; ?>" required class="frm_input" size="6" placeholder="MBR / 6"></td>
					<td>
						<select name="sm_type" required class="required" style="width:70px">
							<option value="00" <?php if($row['sm_type'] == "00") { echo "selected"; } ?>>신규</option>
						</select>
					</td>
					<td><input type="text" autocomplete="off" name="sm_openmarket" value="<?php echo $row['sm_openmarket']; ?>" required maxlength="10" class="frm_input" size="10" placeholder="오픈마켓 / 10"></td>
					<td><input type="text" autocomplete="off" name="sm_bnumber" value="<?php echo $row['sm_bnumber']; ?>" required maxlength="10" class="frm_input" size="10" placeholder="사업자 / 10"></td>
					<td><input type="text" autocomplete="off" name="sm_btype" value="<?php echo $row['sm_btype']; ?>" maxlength="20" class="frm_input" size="10" placeholder="업종명 / 20"></td>
					<td><input type="text" autocomplete="off" name="sm_bname" value="<?php echo $row['sm_bname']; ?>" required maxlength="40" class="frm_input" size="10" placeholder="업체명 / 40"></td>
					<td><input type="text" autocomplete="off" name="sm_addr" value="<?php echo $row['sm_addr']; ?>" maxlength="100" class="frm_input" placeholder="주소 / 100"></td>
					<td><input type="text" autocomplete="off" name="sm_ceo" value="<?php echo $row['sm_ceo']; ?>" required maxlength="30" class="frm_input" size="5" placeholder="대표자 / 30"></td>
					<td><input type="text" autocomplete="off" name="sm_tel" value="<?php echo $row['sm_tel']; ?>" required maxlength="14" class="frm_input" size="10" placeholder="전화번호 / 14"></td>
					<td><input type="text" autocomplete="off" name="sm_email" value="<?php echo $row['sm_email']; ?>" maxlength="30" class="frm_input" size="15" placeholder="이메일 30"></td>
					<td><input type="text" autocomplete="off" name="sm_website" value="<?php echo $row['sm_website']; ?>" required maxlength="200" class="frm_input" size="20" placeholder="홈페이지 200"></td>
					<td colspan="4">
						<button type="submit" class="btn_admin">신규등록</button>
					</td>
				</tr>
				<?php
					}
					for ($i=0; $row=sql_fetch_array($result); $i++) {
					$num = number_format($total_count - ($page - 1) * $rows - $i);

					if($row['sm_type'] == 00) {
						$sm_type = "신규";
					} else if($row['sm_type'] == 01) {
						$sm_type = "해지";
					} else if($row['dv_pg'] == 02) {
						$sm_type = "변경";
					}

					if($sm_id == $row['sm_id']) {
				?>
				<tr>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><?php echo $num; ?></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><input type="text" autocomplete="off" name="sm_mbrno" value="<?php echo $row['sm_mbrno']; ?>" required class="frm_input" size="6"></td>

					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;">
						<select name="sm_type" required class="required" style="width:70px">
							<option value="">구분</option>
							<option value="00" <?php if($row['sm_type'] == "00") { echo "selected"; } ?>>신규</option>
							<option value="01" <?php if($row['sm_type'] == "01") { echo "selected"; } ?>>해지</option>
							<option value="02" <?php if($row['sm_type'] == "02") { echo "selected"; } ?>>변경</option>
							<option value="09" <?php if($row['sm_type'] == "02") { echo "selected"; } ?>>완료</option>
						</select>
					</td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><input type="text" autocomplete="off" name="sm_openmarket" value="<?php echo $row['sm_openmarket']; ?>" required class="frm_input" size="10"></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><input type="text" autocomplete="off" name="sm_bnumber" value="<?php echo $row['sm_bnumber']; ?>" required class="frm_input" size="10"></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><input type="text" autocomplete="off" name="sm_btype" value="<?php echo $row['sm_btype']; ?>" required class="frm_input" size="10"></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><input type="text" autocomplete="off" name="sm_bname" value="<?php echo $row['sm_bname']; ?>" required class="frm_input" size="10"></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><input type="text" autocomplete="off" name="sm_addr" value="<?php echo $row['sm_addr']; ?>" required class="frm_input"></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><input type="text" autocomplete="off" name="sm_ceo" value="<?php echo $row['sm_ceo']; ?>" required class="frm_input" size="5"></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><input type="text" autocomplete="off" name="sm_tel" value="<?php echo $row['sm_tel']; ?>" required class="frm_input" size="10"></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><input type="text" autocomplete="off" name="sm_email" value="<?php echo $row['sm_email']; ?>" required class="frm_input" size="15"></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"><input type="text" autocomplete="off" name="sm_website" value="<?php echo $row['sm_website']; ?>" required class="frm_input" size="20"></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;"></td>
					<td style="<?php if($results != "ok") { echo "background:#ffff99"; } ?>;">
						<button type="submit" class="btn_admin">저장</button>
					</td>
				</tr>
				<?php } else { ?>
				<tr>
					<td><button type="button" onclick="send_member('<?php echo $row['sm_mbrno']; ?>');" class="btn_b btn_b02"><span>차액정산 생성</span></button></td>
					<td><?php echo $row['sm_mbrno']; ?></td>
					<td><?php echo $sm_type; ?></td>
					<td><?php echo $row['sm_openmarket']; ?></td>
					<td><?php echo $row['sm_bnumber']; ?></td>
					<td><?php echo $row['sm_btype']; ?></td>
					<td><?php echo $row['sm_bname']; ?></td>
					<td><?php echo $row['sm_addr']; ?></td>
					<td><?php echo $row['sm_ceo']; ?></td>
					<td><?php echo $row['sm_tel']; ?></td>
					<td><?php echo $row['sm_email']; ?></td>
					<td><?php echo $row['sm_website']; ?></td>
					<td><?php echo $row['updatetime']; ?></td>
					<td><?php echo $row['datetime']; ?></td>
					<td><?php echo $row['sm_error']; ?></td>
					<?php if($member['mb_level'] >= 8) { ?>
					<td class="is-actions-cell">
						<div class="buttons">
							<a href="./?p=sftp_member&sm_id=<?php echo $row['sm_id']; ?>&page=<?php echo $page; ?>" class="btn_b btn_b02">수정</a>
							<a href="./?p=sftp_delete&sm_id=<?php echo $row['sm_id']; ?>&page=<?php echo $page; ?>" class="btn_b btn_b06" onclick="return confirm('정말 <?php echo $row['sm_bname']; ?>가맹점을 삭제 하시겠습니까');">삭제</a>
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