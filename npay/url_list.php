<?php

	$sql_common = " from pay_payment_sms ";
	
	if($is_admin) {
		$sql_search = " where (1) ";
	} else {
		$sql_search = " where  mb_id = '{$member['mb_id']}' ";
	}

	if($use == "1") {
		$sql_search .= " and `use` = '1'";
	} else if($use == "all") {
		$sql_search .= "";
	} else {
		$sql_search .= " and `use` = '0'";
	}

	if (!$sst) {
		$sst = "pm_id";
		$sod = "desc";
	}

	$sql_order = " order by {$sst} {$sod} ";

	// 테이블의 전체 레코드수만 얻음
	$sql = " select COUNT(*) as cnt {$sql_common} {$sql_search} {$sql_order}";
	$row = sql_fetch($sql);
	$total_count = $row['cnt'];

	$rows = $config['cf_page_rows'];
	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) {
		$page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	}
	$from_record = ($page - 1) * $rows; // 시작 열을 구함

	$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
	$result = sql_query($sql);
//	echo $sql;
?>

<section class="container" id="bbs">
	<section class="contents contents-bbs">
		<div class="top">
			<?php /*
			<div class="inner">
				<form name="srch">
				<div class="top-search pull-left">
					<fieldset>
						<legend class="sr-only">검색하기</legend>
						<div class="search-wrap">
							<select class="form-control select2" name="s_select" id="s_select">
								<option value="s_title">제목</option>
								<option value="s_content">내용</option>
							</select>
							<input type="text" class="form-control date" placeholder="검색어를 입력하세요." name="s_search" id="s_search" value="<?php echo $fr_date; ?>">
							<input type="text" class="form-control date" placeholder="검색어를 입력하세요." name="s_search" id="s_search" value="<?php echo $to_date; ?>">
						</div>
					</fieldset>
				</div>
				<div class="top-search pull-right">
					<fieldset>
						<legend class="sr-only">검색하기</legend>
						<div class="search-wrap">
							<select class="form-control" name="s_select" id="s_select">
								<option value="s_title">제목</option>
								<option value="s_content">내용</option>
							</select>
							<input type="text" class="form-control" placeholder="검색어를 입력하세요." name="s_search" id="s_search" value="">
							<button type="submit" class="search-btn"></button>
						</div>
					</fieldset>
				</div>
				</form>
			</div>
			*/ ?>
			<div class="inner">
				<form name="srch">
					<input type="hidden" name="p" value="<?php echo $p; ?>">
					<fieldset>
					<div class="top-search pull-left">
						<div class="search-wrap">
							<select class="form-control select2" name="pay_pg" id="pay_pg" style="width:100%">
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
						</div>
					</div>
					<div class="top-search pull-right">
						<div class="search-wrap">
							<select class="form-control" name="sfl" id="sfl">
								<option value="mb_name" <?php if($sfl == "mb_name") { echo "selected"; } ?>>등록자</option>
								<option value="mb_name" <?php if($sfl == "mb_name") { echo "selected"; } ?>>가격</option>
								<option value="mb_hp" <?php if($sfl == "mb_hp") { echo "selected"; } ?>>상품명</option>
								<option value="mb_hp" <?php if($sfl == "mb_hp") { echo "selected"; } ?>>휴대전화</option>
							</select>
							<input type="text" class="form-control" placeholder="검색어를 입력하세요." name="stx" id="stx" value="">
							<button type="submit" class="search-btn"></button>
						</div>
					</div>
					</fieldset>
				</form>
			</div>
		</div>

		<div class="sub-tab">
			<div class="inner">
				<ul>
					<li class="<?php if($p == "url_list") { echo "active"; } ?>"><a href="./?p=url_list">SMS결제</a></li>
					<li class="<?php if($p == "url_sms") { echo "active"; } ?>"><a href="./?p=url_sms">문자전송내역</a></li>
				</ul>
			</div>
		</div>


		<div class="bbs-cont">
			<div class="inner">
				<?php /*
				<ul class="keyword tab-menu">
					<li class="active" rel="tab1"><a href="#">전체</a></li>
					<li rel="tab2"><a href="#">가입</a></li>
					<li rel="tab3"><a href="#">결제</a></li>
					<li rel="tab4"><a href="#">수수료</a></li>
					<li rel="tab8"><a href="#">보증보험</a></li>
					<li rel="tab5"><a href="#">정산</a></li>
					<li rel="tab6"><a href="#">취소</a></li>
					<li rel="tab7"><a href="#">기타</a></li>
				</ul>
				<?php echo $sql; ?>
				*/ ?>
				<div class="cont-wrap faq-area">

					<div class="scr-x">
						<table class="table table-terms">
						<caption class="hidden">페이앱 카드 수수료</caption>
							<?php /*
							<colgroup>
								<col style="width:10%;">
								<col span="7" style="width:5%;">
							</colgroup>
							*/ ?>
							<thead>
								<tr>
									<th>NO</th>
									<th>PG</th>
									<th>상품명</th>
									<th>상품가격</th>
									<th>판매자명</th>
									<th>판매자연락처</th>
									<th>구매자명</th>
									<th>구매자연락처</th>
									<th>등록일</th>
									<th>미리보기</th>
									<th>상세보기</th>
									<th>삭제</th>
								</tr>
							</thead>
							<tbody>
								<?php
								for ($i=0; $row=sql_fetch_array($result); $i++) {
									$num = number_format($total_count - ($page - 1) * $config['cf_page_rows'] - $i);
									
									
									if($row['url_payments'] == "k1") {
										$url_payments = "광원";
									} else if($row['url_payments'] == "danal") {
										$url_payments = "다날";
									} else if($row['url_payments'] == "welcom") {
										$url_payments = "웰컴";
									} else if($row['url_payments'] == "paysis") {
										$url_payments = "페이시스";
									} else if($row['url_payments'] == "stn") {
										$url_payments = "섹타나인";
									} else {
										$url_payments = "<span style='color:red'>오류</span>";
									}
									if($is_admin) {
										$row_pg = sql_fetch(" select * from pay_member_pg where pg_id = '{$row['pg_id']}' ");
									} else {
										$row_pg = sql_fetch(" select * from pay_member_pg where mb_id = '{$row['mb_id']}' and pg_id = '{$row['pg_id']}' ");
									}

									if($row['use'] == "1") {
										$use = "미사용";
									} else {
										$use = "사용중";
									}
								?>
								<tr onclick="opens('over_<?php echo $i; ?>');" id="overs" class="overs_tr tr_over_<?php echo $i; ?>">
									<td style="width:50px;"><?php echo $num; ?></td>
									<td class="txtblue"><?php echo $row_pg['pg_name']; ?></td>
									<td><?php echo $row['url_pd']; ?></td>
									<td class="txtblue" style="text-align:right"><?php if($row['url_price']) { echo number_format($row['url_price']); } else { echo 0; } ?></td>
									<td class="txtblue"><?php echo $row['url_pname']; ?></td>
									<td><?php echo $row['url_ptel']; ?></td>
									<td class="txtblue"><?php echo $row['url_gname']; ?></td>
									<td><?php echo $row['url_gtel']; ?></td>
									<td style="width:100px;"><?php echo date("y-m-d H:i", strtotime($row['datetime'])); ?></td>
									<td><a href="./url_pay.php?id=<?php echo $row['pm_id']; ?>" onclick="return openWindow(this, {name:'payapptest',width:420,height:670,center:true,scrollbars:true})" class='btn-date-line btn-xm'>미리보기</a></td>
									<?php
										/*
										if($row['urlcode']) {
											$current_url = 'http://pay.mpc.icu/url/pay.php?urlcode='.$row['urlcode'];
											echo "<img src='https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($current_url) . "&size=100x100' alt='QR Code' style='width:100%;'>";
										}
										*/
									?>
									</td>
									<td><a href="./url_send.php?id=<?php echo $row['pm_id']; ?>" onclick="return openWindow(this, {name:'payapptest',width:420,height:420,center:true,scrollbars:true})" class='btn-blue btn-xm'>문자전송</a></td>
									<td><a href="./?p=url_delete&pm_id=<?php echo $row['pm_id']; ?>&delete=yes" class='btn-black btn-xm'>삭제</a></td>
								</tr>
								<?php
									}
								?>
							</tbody>
						</table>
					</div>

					<div class="text-right btn-vertical btn-group">
						<a class="btn btn-gray-line" href="./?p=url_form">SMS상품등록</a>
					</div>

					<?php
						$qstr = "p=".$p."&pp=".$pp;
						echo get_paging_new(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page=');
					?>

				</div>
			</div>
		</div>
		</div>
	</section>
</section>