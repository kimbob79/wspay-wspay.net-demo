<?php	
	if($authCd) { $authCd_common = " and authCd = '{$authCd}' "; }
	if($mb_id) { $authCd_common = " and mb_id = '{$mb_id}' "; }

	if(!$payr) {
		$authCd_common .= " and resultCd = '0000' ";
	} else if($payr == "2") {
		$authCd_common .= " and resultCd != '0000' ";
	} else {
		$authCd_common .= " ";
	}

	if($is_admin) {
		$sql_common = " from pay_payment_old where (datetime BETWEEN '{$fr_date} 00:00:00' and '{$to_date} 23:59:59') $authCd_common";
	} else {
		$sql_common = " from pay_payment_old where (datetime BETWEEN '{$fr_date} 00:00:00' and '{$to_date} 23:59:59') and mb_id = '{$member['mb_id']}' $authCd_common";
	}

	// 테이블의 전체 레코드수만 얻음
	$sql = " select COUNT(*) as cnt, SUM(price) as price {$sql_common} ";
	$row = sql_fetch($sql);
	$total_count = $row['cnt'];

	$sql = " select SUM(amount) as price_s {$sql_common} and resultYN != '1' ";
	$row = sql_fetch($sql);
	$total_sprice = $row['price_s'];

	$sql = " select SUM(amount) as price_c {$sql_common} and resultYN = '1' ";
	$row = sql_fetch($sql);
	$total_cprice = $row['price_c'];

	$total_price = $total_sprice - $total_cprice;
	$page = 1;

	$sql = " select * {$sql_common} order by pm_id desc ";
	$result = sql_query($sql);
?>



<section class="container" id="bbs">
	<section class="contents contents-bbs">
		<div class="top">
			<div class="inner">
				<form name="srch">
					<fieldset>
						<legend class="sr-only">검색하기</legend>
						<input type="hidden" name="p" value="<?php echo $p; ?>">
						<input type="hidden" name="payr" value="<?php echo $payr; ?>">
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
								<?php
									$sql2 = " select * from pay_member_pg where mb_id = '{$member['mb_id']}' and pg_use = '0' group by pg_id asc ";
									$result2 = sql_query($sql2);
								?>
								<select name="pgid" id="pgid" class="form-control select2">
									<option value="">전체PG</option>
									<?php
										for ($k=0; $row2=sql_fetch_array($result2); $k++) {
											$pg_certified = "구인증";
											if($row2['pg_certified'] == 1) {
												$pg_certified = "비인증";
											}
									?>
									<option value="<?php echo $row2['pg_id']; ?>" <?php if($pgid == $row2['pg_id']) { echo "selected"; } ?>><?php echo $row2['pg_name']; ?> <?php echo $pg_certified; ?></option>
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
									<option value="card_num" <?php if($sfl == "card_num") { echo "selected"; } ?>>승인번호</option>
									<option value="pay_price" <?php if($sfl == "pay_price") { echo "selected"; } ?>>승인금액</option>
									<option value="pay_pname" <?php if($sfl == "pay_pname") { echo "selected"; } ?>>결제자명</option>
									<option value="pay_cardnum" <?php if($sfl == "pay_cardnum") { echo "selected"; } ?>>카드번호</option>
									<option value="pay_phone" <?php if($sfl == "pay_phone") { echo "selected"; } ?>>휴대전화</option>
									<option value="pay_product" <?php if($sfl == "pay_product") { echo "selected"; } ?>>결제상품</option>
								</select>
								<input type="text" class="form-control" placeholder="검색어를 입력하세요." name="stx" id="stx" value="<?php echo $stx; ?>">
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
		<div class="sub-tab">
			<div class="inner">
				<ul>
					<li class="<?php if(!$payr or $payr == "0") { echo "active"; } ?>"><a href="./?p=list&mb_id=<?php echo $mb_id; ?>&fr_date=<?php echo $fr_date; ?>&to_date=<?php echo $to_date; ?>&payr=0&authCd=<?php echo $authCd; ?>&dates=<?php echo $dates; ?>">정상승인</a></li>
					<li class="<?php if($payr == "2") { echo "active"; } ?>"><a href="./?p=list&mb_id=<?php echo $mb_id; ?>&fr_date=<?php echo $fr_date; ?>&to_date=<?php echo $to_date; ?>&payr=2&authCd=<?php echo $authCd; ?>&dates=<?php echo $dates; ?>">승인실패</a></li>
					<li class="<?php if($payr == "3") { echo "active"; } ?>"><a href="./?p=list&mb_id=<?php echo $mb_id; ?>&fr_date=<?php echo $fr_date; ?>&to_date=<?php echo $to_date; ?>&payr=3&authCd=<?php echo $authCd; ?>&dates=<?php echo $dates; ?>">전체내역</a></li>
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
				*/ ?>
				<div class="cont-wrap faq-area">

					<div class="scr-x">
						<table class="table table-terms">
						<caption class="hidden">결제내역</caption>
							<thead>
								<tr>
									<th scope="row">NO</th>
									<th scope="col">가맹점명</th>
									<th scope="col">상태</th>
									<th scope="col">승인번호</th>
									<th scope="col" style="text-align:right;"><?php echo number_format($total_price); ?></th>
									<th scope="col">결제일시</th>
									<th scope="col">결제자명</th>
									<th scope="col">카드</th>
									<th scope="col">카드번호</th>
									<th scope="col">휴대전화</th>
									<th scope="col">결제상품</th>
									<th scope="col">PG</th>
									<th scope="row">할부</th>
									<th scope="col">영수증</th>
									<th scope="col">취소</th>
								</tr>
							</thead>
							<tbody>
								<?php
									for ($i=0; $row=sql_fetch_array($result); $i++) {
										$num = number_format($total_count - ($page - 1) * $config['cf_page_rows'] - $i);
										$bg = 'bg'.($i%2);
										
										$receipt = substr($row['creates'],0,8)."/".$row['authCd'];

										if($row['resultYN'] == "1") {
											$pride = "<del style='color:red'>".number_format($row['amount'])."</del>";
											$can_btn = "<a class='btn btn-date-line btn-xm modal-trigger'>완료</a>";
										} else if($row['resultYN'] == "2") {
											$pride = "<del style='color:red'>".number_format($row['amount'])."</del>";
											$can_btn = "<a class='btn btn-date-line btn-xm modal-trigger'>완료</a>";
										} else {
											$pride = number_format($row['amount']);
											if(date("Y-m-d") == date("Y-m-d", strtotime($row['creates']))) {

												$row_cancel = sql_fetch(" select count(pm_id) as pm_count from g5_payment where mb_id = '{$row['mb_id']}' and authCd = '{$row['authCd']}' and bin = '{$row['bin']}' and advanceMsg = '정상취소' ");
												// select * from g5_payment where mb_id = 'sss1234' and authCd = '54807295' and bin = '488972' and advanceMsg = '정상취소'

												if($row_cancel['pm_count']) {
													$can_btn = "<a href='javascript:alert(\"이미 취소 완료되었습니다..\");' class='btn btn-white btn-xm modal-trigger'>완료</a>";
												} else {
													$can_btn = "<a href='".G5_URL."/payment/cancel.php?id=".$row['pm_id']."' onclick='win_card_cancel(this.href); return false;' class='btn btn-black btn-xm modal-trigger'>취소</a>";
												}

											} else {
												$can_btn = "<a href='javascript:alert(\"승인 당일에만 취소 가능합니다.\");' class='btn btn-white btn-xm modal-trigger'>불가</a>";
											}
										}

										$mb = get_member($row['mb_id']);
										//G5_TIME_Y-m-d
										//
										if($is_admin) {
											$receipt_txt =  $mb['mb_name'];
										} else if($member['mb_id'] == "wpay") {
											$receipt_txt =  $mb['mb_name'];
										} else {
											$receipt_txt =  '영수증';
										}

										if($row['resultCd'] == "0000") {
											if($row['resultYN'] == "0") {
												$advanceMsg = "<span style='color:blue;'>승인</span>";
												$advanceMsgs = "승인";
											} else if($row['resultYN'] == "1") {
												$advanceMsg = "<span style='color:red;'>취소</span>";
												$advanceMsgs = "취소";
											} else if($row['resultYN'] == "2") {
												$advanceMsg = "<span style='color:red;'>승/취</span>";
												$advanceMsgs = "승취";
											}
										} else {
											$advanceMsg = "<span style='color:red;'>실패</span>";
											$advanceMsgs = "실패";
										}

										if($row['cardAuth'] == "true") {
											$cardAuth = "<span style='color:blue;'>인</span>";
											$cardAuth_txt = "인증결제";
										} else {
											$cardAuth = "<span style='color:black;'>비</span>";
											$cardAuth_txt = "비인증결제";
										}
										if($row['mb_name'] == "주식회사 케이물류") {
											$row['mb_name'] = "케이물류";
										}


										if($row['payments'] == "korpay") {
											$pg = "<span style='background:red;color:#fff;padding:4px 5px 2px;font-size:11px;'>코</span>";
											$pgs = "코페이";
										} else if($row['payments'] == "danal") {
											$pg = "<span style='background:blue;color:#fff;padding:4px 5px 2px;font-size:11px;'>다</span>";
											$pgs = "다날";
										} else if($row['payments'] == "welcom") {
											$pg = "<span style='background:#666;color:#fff;padding:4px 5px 2px;font-size:11px;'>웰</span>";
											$pgs = "웰컴";
										} else {
											$pg = "";
										}

										if($row['acquirer'] == "하나(구외환)") { $row['acquirer'] = "하나"; }
								?>

								<tr <?php if($row['resultYN'] == "1") { echo "style='background:#e5cbc9'"; } ?><?php if($row['resultYN'] == "2") { echo "style='background:#efdcdb'"; } ?>>
									<td scope="row"><?php echo $num; ?></td>
									<td class="txtblue txtleft"><?php echo $row['mb_name']; ?></td>
									<td><?php echo $advanceMsgs; ?></td>
									<td><?php echo $row['authCd']; ?></td>
									<td class="txtblue txtright"><?php echo $pride; ?></td>
									<td><?php echo date("y-m-d H:i", strtotime($row['creates'])); ?></td>
									<td><?php echo $row['payerName']; ?></td>
									<td><?php echo $row['acquirer']; ?></td>
									<td><?php echo $row['last4']; ?></td>
									<td><?php echo $row['payerTel']; ?></td>
									<td class="txtleft"><?php echo $row['descs']; ?></td>
									<td scope="row"><?php echo $pgs; ?></td>
									<td><?php if($row['installment'] < 1) { echo "일시불"; } else { echo $row['installment']."개월"; } ?></td>
									<td><a href='./receipt2.php?pm_id=<?php echo $row['pm_id']; ?>' onclick="win_receipt(this.href, <?php echo $row['authCd']; ?>); return false;" class="btn btn-black btn-xm">영수증</a></td>
									<td><?php echo $can_btn; ?></td>
								</tr>
								<?php
									}
									if($i == 0) {
								?>
								<tr>
									<td colspan="15" style="height:200px;">결제내역이 없습니다.</td>
								</tr>
								<?php
									}
								?>
							</tbody>
						</table>
					</div>
					<?php /*
					<div class="text-right btn-vertical btn-group">
						<a class="btn btn-gray-line" href="./form.php">문의하기</a>
					</div>

					<div class="paging">
						<a href="/homepage/bbs/bbs_notice.html?PageNo=1&amp;s_select=&amp;s_search=" class="btn-paging"><i class="ico ico-first"></i></a>
						<a href="/homepage/bbs/bbs_notice.html?PageNo=5&amp;s_select=&amp;s_search=" class="btn-paging"><i class="ico ico-prev"></i></a>
						<ol>
							<li class="active"><a href="/homepage/bbs/bbs_notice.html?PageNo=1&amp;s_select=&amp;s_search=">1</a></li>
							<li><a href="/homepage/bbs/bbs_notice.html?PageNo=2&amp;s_select=&amp;s_search=">2</a></li>
							<li><a href="/homepage/bbs/bbs_notice.html?PageNo=3&amp;s_select=&amp;s_search=">3</a></li>
							<li><a href="/homepage/bbs/bbs_notice.html?PageNo=4&amp;s_select=&amp;s_search=">4</a></li>
							<li><a href="/homepage/bbs/bbs_notice.html?PageNo=5&amp;s_select=&amp;s_search=">5</a></li>
						</ol>
						<a href="/homepage/bbs/bbs_notice.html?PageNo=6&amp;s_select=&amp;s_search=" class="btn-paging"><i class="ico ico-next"></i></a>
						<a href="/homepage/bbs/bbs_notice.html?PageNo=21&amp;s_select=&amp;s_search=" class="btn-paging"><i class="ico ico-last"></i></a>
					</div>
					*/ ?>
				</div>
			</div>
		</div>
		</div>
	</section>
</section>