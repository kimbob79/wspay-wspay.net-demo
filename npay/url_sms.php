<?php

	$sql_common = " from g5_sms_messages ";
	
	if($is_admin) {
		$sql_search = " where (1) ";
	} else {
		$sql_search = " where  mb_id = '{$member['mb_id']}' ";
	}

	if (!$sst) {
		$sst = "sm_id";
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
					<li class="<?php if($p == "url_list") { echo "active"; } ?>"><a href="./?p=url_list">URL결제</a></li>
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
									<th>수신번호</th>
									<th>발송결과</th>
									<th>발송일시</th>
									<th>발송내용</th>
								</tr>
							</thead>
							<tbody>
								<?php
								for ($i=0; $row=sql_fetch_array($result); $i++) {
									$num = number_format($total_count - ($page - 1) * $config['cf_page_rows'] - $i);
									if($row['sm_status'] == "success") {
										$sm_status = "<span style='color:#4d4dff'>전송완료</span>";
									} else {
										$sm_status = "전송실패";
									}
								?>
								<tr onclick="opens('over_<?php echo $i; ?>');" id="overs" class="overs_tr tr_over_<?php echo $i; ?>">
									<td style="width:50px;"><?php echo $num; ?></td>
									<td><?php echo $row['sm_receiver']; ?></td>
									<td><?php echo $sm_status; ?></td>
									<td><?php echo $row['sm_send_time']; ?></td>
									<td style="text-align:left"><?php echo nl2br($row['sm_message']); ?></td>
								</tr>
								<?php
									}
								?>
							</tbody>
						</table>
					</div>

					<div class="text-right btn-vertical btn-group">
						<a class="btn btn-gray-line" href="./?p=url_form">URL상품등록</a>
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