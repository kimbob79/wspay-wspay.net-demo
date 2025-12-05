<?php
if(!$is_admin) {
	alert("접근이 불가한 페이지 입니다.");
}


// 테이블 이름
$tableName = $pg_table;

// 쿼리 작성 및 실행 (테이블의 스키마 조회)
$sql = "SHOW FULL COLUMNS FROM  $tableName";
$result = sql_query($sql);


// 쿼리 작성 및 실행 (테이블의 스키마 조회)
$sql2 = "SHOW FULL COLUMNS FROM  $tableName";
$result2 = sql_query($sql2);


$sql_common = " from {$tableName} ";


$sql_search = " where (datetime BETWEEN '{$fr_date} 00:00:00' and '{$to_date} 23:59:59') ";
if ($stx) {
	$sql_search .= " and ( ";
	switch ($sfl) {
		case 'mb_point':
			$sql_search .= " ({$sfl} >= '{$stx}') ";
			break;
		case 'mb_level':
			$sql_search .= " ({$sfl} = '{$stx}') ";
			break;
		case 'mb_tel':
		case 'mb_hp':
			$sql_search .= " ({$sfl} like '%{$stx}') ";
			break;
		default:
			$sql_search .= " ({$sfl} like '{$stx}%') ";
			break;
	}
	$sql_search .= " ) ";
}

if ($is_admin != 'super') {
	$sql_search .= " and mb_level <= '{$member['mb_level']}' ";
}

if (!$sst) {
	$sst = "datetime";
	$sod = "desc";
}

$sql_order = " order by {$sst} {$sod} ";


$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) {
    $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
}
$from_record = ($page - 1) * $rows; // 시작 열을 구함


// 데이터 출력
$sql_data = "SELECT * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result_data = sql_query($sql_data);

?>
<section class="container" id="bbs">
	<section class="contents contents-bbs">
		<div class="top">
			<div class="inner">
				<form name="srch">
					<input type="hidden" name="p" value="<?php echo $p; ?>">
					<input type="hidden" name="pg_table" value="<?php echo $pg_table; ?>">
					<fieldset>
					<div class="top-search">
						<div style="margin-bottom:.5rem">
								<button type="submit" class="btn btn-<?php if($dates == "t") { echo "black"; } else { echo "date-line"; } ?> btn-sm" onclick="javascript:set_date('오늘');" name="dates" value="t"><span>오늘</span></button>
								<button type="submit" class="btn btn-<?php if($dates == "y") { echo "black"; } else { echo "date-line"; } ?> btn-sm" onclick="javascript:set_date('어제');" name="dates" value="y"><span>어제</span></button>
								<?php /*
								<button type="submit" class="btn btn-black btn-sm" onclick="javascript:set_date('이번주');"><span>이번주</span></button>
								<button type="submit" class="btn btn-black btn-sm" onclick="javascript:set_date('지난주');"><span>지난주</span></button>
								*/ ?>
								<button type="submit" class="btn btn-<?php if($dates == "tm") { echo "black"; } else { echo "date-line"; } ?> btn-sm" onclick="javascript:set_date('이번달');" name="dates" value="tm"><span>이번달</span></button>
								<button type="submit" class="btn btn-<?php if($dates == "ym") { echo "black"; } else { echo "date-line"; } ?> btn-sm" onclick="javascript:set_date('지난달');" name="dates" value="ym"><span>지난달</span></button>
						</div>
					</div>
					<div class="top-search pull-left">
						<div class="search-wrap">
							<select class="form-control select2" name="pay_pg" id="pay_pg">
								<option value="">전체PG</option>
								<?php
									$sql_pg = " select * from pay_pg order by pg_sort asc ";
									$result_pg = sql_query($sql_pg);
									for ($k=0; $row_pg=sql_fetch_array($result_pg); $k++) {
								?>
								<option value="<?php echo $row_pg['pg_id']; ?>" <?php if($pay_pg == $row_pg['pg_id']) { echo "selected"; } ?>><?php echo $row_pg['pg_name']; ?></option>
								<?php
									}
								?>
							</select>
							<input type="text" class="form-control date" placeholder="검색어를 입력하세요." name="fr_date" id="fr_date" value="<?php echo $fr_date; ?>">
							<input type="text" class="form-control date" placeholder="검색어를 입력하세요." name="to_date" id="to_date" value="<?php echo $to_date; ?>">
						</div>
					</div>
					<div class="top-search pull-right">
						<div class="search-wrap">
							<select class="form-control" name="sfl" id="sfl">
								<option value="mb_nick" <?php if($sfl == "mb_nick") { echo "selected"; } ?>>업체명</option>
								<option value="mb_name" <?php if($sfl == "mb_name") { echo "selected"; } ?>>담당자</option>
								<option value="mb_hp" <?php if($sfl == "mb_hp") { echo "selected"; } ?>>휴대전화</option>
							</select>
							<input type="text" class="form-control" placeholder="검색어를 입력하세요." name="stx" id="stx" value="">
							<button type="submit" class="search-btn"></button>
						</div>
					</div>
					</fieldset>
				</form>
			</div>
			<?php /*
			<div class="inner">
				<div class="heading-tit pull-left">
					<h2>고객센터</h2>
					<ul>
						<li>1800 - 3772</li>
						<li>평일 09:00 ~ 18:00</li>
					</ul>
				</div>
				<div class="top-search pull-right">
					<form name="srch">
						<fieldset>
							<legend class="sr-only">검색하기</legend>
							<div class="search-wrap">
								<input type="text" class="form-control date" placeholder="검색어를 입력하세요." name="s_search" id="s_search" value="<?php echo $fr_date; ?>">
								<input type="text" class="form-control date" placeholder="검색어를 입력하세요." name="s_search" id="s_search" value="<?php echo $to_date; ?>">
								<select class="form-control" name="s_select" id="s_select">
									<option value="s_title">제목</option>
									<option value="s_content">내용</option>
								</select>
								<input type="text" class="form-control" placeholder="검색어를 입력하세요." name="s_search" id="s_search" value="">
								<button type="submit" class="search-btn"></button>
							</div>
						</fieldset>
					</form>
				</div>
			</div>
			*/ ?>
		</div>
		<?php /*
		<div class="sub-tab">
			<div class="inner">
				<ul>
					<li><a href="/homepage/bbs/bbs_faq.html">자주묻는 질문</a></li>
					<li><a href="/homepage/bbs/bbs_notice.html">공지사항</a></li>
					<li class="active"><a href="/homepage/bbs/bbs_ask.html">문의사항</a></li>
					<li><a href="/homepage/bbs/bbs_use.html">이용가이드</a></li>
				</ul>
			</div>
		</div>
		*/ ?>
		<div class="bbs-cont">
			<div class="inner">
				<?php /*
				<ul class="keyword tab-menu">
					<li class="active" rel="tab1"><a href="#">전체</a></li>
					<li rel="tab2"><a href="#">코페이</a></li>
					<li rel="tab3"><a href="#">다날</a></li>
					<li rel="tab4"><a href="#">웰컴</a></li>
					<li rel="tab8"><a href="#">광원</a></li>
					<li rel="tab5"><a href="#">결제불가</a></li>
				</ul>
				*/ ?>
				<div class="cont-wrap faq-area">
					<div class="scr-x">
						<table class="table table-terms">
						<?php
							// 컬럼명 출력
							echo "<tr>";
							while($row = $result->fetch_assoc()) {
								echo "<th style='text-align:center'>".$row["Field"]."</th>";
							}
							echo "</tr>";

							echo "<tr>";
							while($row2 = $result2->fetch_assoc()) {
								echo "<th style='text-align:center'>".$row2["Comment"]."</th>";
							}
							echo "</tr>";

							if ($result_data->num_rows > 0) {
								while($row_data = $result_data->fetch_assoc()) {
									echo "<tr>";
									foreach ($row_data as $key => $value) {
										echo "<td>".$value."</td>";
									}
									echo "</tr>";
								}
							} else {
								echo "<tr><td colspan='" . $result->num_rows . "'>0 results</td></tr>";
							}
						?>
						</table>
					</div>

					<?php
						$qstr = "p=".$p."&fr_date=".$fr_date."&to_date=".$to_date."&pg_table=".$pg_table;
						echo get_paging_new(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page=');
					?>
				</div>
			</div>
		</div>
	</div>
</div>