<?php
//	error_reporting( E_ALL );
//	ini_set( "display_errors", 1 );

	if($authCd) { $authCd_common = " and authCd = '{$authCd}' "; }
	if($pgid) { $authCd_common = " and pg_id = '{$pgid}' "; }

	if(!$payr) {
		$authCd_common .= " and card_result_code = '0000' ";
	} else if($payr == "2") {
		$authCd_common .= " and card_result_code != '0000' ";
	} else {
		$authCd_common .= " ";
	}

	if($is_admin) {
		$sql_common = " from pay_payment where (datetime BETWEEN '{$fr_date} 00:00:00' and '{$to_date} 23:59:59') $authCd_common";
	} else {
		$sql_common = " from pay_payment where (datetime BETWEEN '{$fr_date} 00:00:00' and '{$to_date} 23:59:59') and mb_id = '{$member['mb_id']}' $authCd_common";
	}


	$sql_search = " ";

	$stx = preg_replace("/[ #\&\+\-%@=\/\\\:;,\.'\"\^`~\_|\!\?\*$#<>()\[\]\{\}]/i", "", $stx);

	if ($stx) {
		$sql_search .= " and ( ";
		if($sfl) {
			$sql_search .= " {$sfl} like '%{$stx}%' ";
		}
		$sql_search .= " ) ";
	}



	// 테이블의 전체 레코드수만 얻음
	$sql = " select COUNT(*) as cnt, SUM(pay_price) as pay_price {$sql_common} {$sql_search} order by pay_id desc";
	$row = sql_fetch($sql);
	$total_count = $row['cnt'];
	$total_sprice = $row['pay_price'];
	/*
	$sql = " select SUM(pay_price) as pay_price {$sql_common} and card_yn = 'Y' ";
	$row = sql_fetch($sql);
	$total_sprice = $row['pay_price'];
	*/

	$sql = " select SUM(pay_price) as pay_price {$sql_common} {$sql_search} and card_yn = 'C' ";
	$row = sql_fetch($sql);
	$total_cprice = $row['pay_price'];

	$total_price = $total_sprice - $total_cprice;

	$rows = $config['cf_page_rows'];
	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) {
		$page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	}
	$from_record = ($page - 1) * $rows; // 시작 열을 구함

	$sql = " select * {$sql_common} {$sql_search} order by pay_id desc limit {$from_record}, {$rows} ";
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
									//$sql2 = " select a.pg_id as pg_id, a.pg_name as pg_name from pay_member_pg as a left join pay_pg as b on a.pg_mid = b.pg_id where a.mb_id = '{$member['mb_id']}' and a.pg_use = '1' group by a.pg_id asc ";
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
					<li class="<?php if(!$payr or $payr == "0") { echo "active"; } ?>"><a href="./?p=list&mb_id=<?php echo $mb_id; ?>&fr_date=<?php echo $fr_date; ?>&to_date=<?php echo $to_date; ?>&payr=0&authCd=<?php echo $authCd; ?>&dates=<?php echo $dates; ?>">승인</a></li>
					<li class="<?php if($payr == "2") { echo "active"; } ?>"><a href="./?p=list&mb_id=<?php echo $mb_id; ?>&fr_date=<?php echo $fr_date; ?>&to_date=<?php echo $to_date; ?>&payr=2&authCd=<?php echo $authCd; ?>&dates=<?php echo $dates; ?>">실패</a></li>
					<li class="<?php if($payr == "3") { echo "active"; } ?>"><a href="./?p=list&mb_id=<?php echo $mb_id; ?>&fr_date=<?php echo $fr_date; ?>&to_date=<?php echo $to_date; ?>&payr=3&authCd=<?php echo $authCd; ?>&dates=<?php echo $dates; ?>">전체</a></li>
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

				<span style="font-size:12px;">Total : <span style="color:#000"><?php echo number_format($total_sprice); ?></span> - <span style="color:red"><?php echo number_format($total_cprice); ?></span> = <span style="color:blue"><?php echo number_format($total_price); ?></span></span>
				<div class="cont-wrap faq-area">
					<div class="scr-x">
						<table class="table table-terms">
						<caption class="hidden">결제내역</caption>
							<thead>
								<tr>
									<th scope="col">상세보기</th>
									<th scope="col">결제타입</th>
									<th scope="col">가맹점명</th>
									<th scope="col">상태</th>
									<th scope="col"><?php if($payr == "2") { echo "실패사유"; } else { echo "승인번호"; } ?></th>
									<th scope="col" style="text-align:right;"><?php echo number_format($total_price); ?></th>
									<th scope="col">결제일시</th>
									<th scope="col">취소일시</th>
									<th scope="col">카드주</th>
									<?php /*
									<th scope="col">카드</th>
									<th scope="col">카드번호</th>
									<th scope="col">휴대전화</th>
									<th scope="col">결제상품</th>
									*/ ?>
									<th scope="col">PG</th>
									<th scope="col">타입</th>
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

										if($row['card_yn'] == "N") {
											$pride = "<del style='color:red'>".number_format($row['pay_price'])."</del>";
											$can_btn = "<a class='btn btn-date-line btn-xm'>완료</a>";
										} else if($row['card_yn'] == "C") {
											$pride = "<del style='color:red'>".number_format($row['pay_price'])."</del>";
											$can_btn = "<a class='btn btn-date-line btn-xm'>완료</a>";
										} else if($row['card_yn'] == "S") {
											$pride = number_format($row['pay_price']);
											$can_btn = "<a class='btn btn-date-line btn-xm'>불가</a>";
										} else {
											$pride = number_format($row['pay_price']);
											if(date("Y-m-d") == date("Y-m-d", strtotime($row['card_sdatetime']))) {

												$row_cancel = sql_fetch(" select count(pay_id) as pm_count from g5_payment where mb_id = '{$row['mb_id']}' and authCd = '{$row['authCd']}' and bin = '{$row['bin']}' and advanceMsg = '정상취소' ");
												// select * from g5_payment where mb_id = 'sss1234' and authCd = '54807295' and bin = '488972' and advanceMsg = '정상취소'

												if($row_cancel['pm_count']) {
													$can_btn = "<a href='javascript:alert(\"이미 취소 완료되었습니다..\");' class='btn btn-white btn-xm'>완료</a>";
												} else {
													$can_btn = "<a href='./cancel.php?pay_id=".$row['pay_id']."' onclick='win_receipt(this.href); return false;' class='btn btn-black btn-xm'>취소</a>";
												}

											} else {
												$can_btn = "<a href='javascript:alert(\"승인 당일에만 취소 가능합니다.\");' class='btn btn-white btn-xm'>불가</a>";
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

										if($row['card_result_code'] == "0000") {
											if($row['card_yn'] == "Y") {
												$advanceMsg = "<span style='color:blue;'>승인</span>";
												$advanceMsgs = "승인";
											} else if($row['card_yn'] == "N") {
												$advanceMsg = "<span style='color:red;'>취소</span>";
												$advanceMsgs = "취소";
											} else if($row['card_yn'] == "C") {
												$advanceMsg = "<span style='color:red;'>승/취</span>";
												$advanceMsgs = "취소";
											}
										} else {
											$advanceMsg = "<span style='color:red;'>실패</span>";
											$advanceMsgs = "실패";
										}

										// PG정보
										$pg = sql_fetch(" select * from pay_pg where pg_code = '".$row['payments']."' ");
										$pg_name = $pg['pg_name'];

										if($row['pg_type'] == '1') {
											$pg_type = '비인증';
										} else if($row['pg_type'] == '2') {
											$pg_type = '인증';
										} else {
											$pg_type = '구인증';
										}

								?>

								<tr <?php if($row['resultYN'] == "1") { echo "style='background:#e5cbc9'"; } ?><?php if($row['resultYN'] == "2") { echo "style='background:#efdcdb'"; } ?>>
									<td>
										<a href="javascript:;" class="btn btn-<?php if($row['card_yn'] == "Y") { echo "blue"; } else { echo "black"; } ?>  btn-xm" onclick="payment_open('<?php echo $row['pay_id']; ?>_tid');" style="width:60px;">
											<div style="float:left">↓</div>
											<div style="float:right"><?php echo $num; ?></div>
										</a>
									</td>
									<?php if($row['url_id'] > 0) { ?>
									<td class="txtblue txtleft">SMS결제</td>
									<?php } else { ?>
									<td class="txtleft">일반결제</td>
									<?php } ?>
									<td class="txtblue txtleft"><?php echo $row['mb_name']; ?></td>
									<td><?php echo $advanceMsgs; ?></td>

									<?php
										if($row['card_num']) {
											echo "<td>".$row['card_num']."</td>";
										} else {
											echo "<td style='color:red;text-align:left;'>".$row['card_result_msg2']."</td>";
										}
									?>

									<td class="txtblue txtright"><?php echo $pride; ?></td>

									<td><?php if($row['card_cdatetime'] != "0000-00-00 00:00:00") { echo date("y-m-d H:i", strtotime($row['card_sdatetime'])); } else { echo date("y-m-d H:i", strtotime($row['datetime'])); } ?></td>
									<td><?php if($row['card_cdatetime'] != "0000-00-00 00:00:00") { echo date("y-m-d H:i", strtotime($row['card_cdatetime'])); } ?></td>
									<td><?php echo $row['pay_pname']; ?></td>
									<?php /*
									<td><?php echo $row['card_name']; ?></td>
									<td><?php echo substr($row['pay_cardnum'],-4); ?></td>
									<td><?php echo $row['pay_phone']; ?></td>
									<td class="txtleft"><?php echo $row['pay_product']; ?></td>
									*/ ?>
									<td scope="row"><?php echo $pg_name; ?></td>
									<td scope="row"><?php echo $pg_type; ?></td>
									<td><?php if($row['pay_installment'] == '0') { echo "일시불"; } else { echo $row['pay_installment']."개월"; } ?></td>
									<?php if($row['card_num']) { ?>
									<td><a href='./receipt.php?pay_id=<?php echo $row['pay_id']; ?>' onclick="win_receipt(this.href, <?php echo $row['card_num']; ?>); return false;" class="btn btn-black btn-xm">영수증</a></td>
									<?php } else { ?>
									<td><a href="javascript:alert('승인건에만 가능합니다.');" class="btn btn-black btn-xm">영수증</a></td>
									<?php } ?>
									<td><?php echo $can_btn; ?></td>
								</tr>
								<tr style="display:none" id="<?php echo $row['pay_id']; ?>_tid">
									<td colspan="4" class="subtable2">
										<?php
											$sql_member_pg = " select * from pay_member_pg where mb_id = '{$row['mb_id']}' ";
											$result_member_pg = sql_query($sql_member_pg);
										?>
										<table>
											<tr>
												<th scope="col">결제타입</th>
												<?php if($row['url_id'] > 0) { ?>
												<td class="txtblue txtleft">URL 결제</td>
												<?php } else { ?>
												<td class="txtleft">일반결제</td>
												<?php } ?>
											</tr>
											<tr>
												<th scope="col">가맹점명</th>
												<td class="txtblue txtleft"><?php echo $row['mb_name']; ?></td>
											</tr>
											<tr>
												<th scope="col">상태</th>
												<td><?php echo $advanceMsgs; ?></td>
											</tr>
											<tr>
												<th scope="col">승인번호</th>
												<td><?php if($row['card_num']) { echo $row['card_num']; } else { echo "<span style='color:red'>".$row['card_result_msg2']."</span>"; } ?></td>
											</tr>
											<tr>
												<th scope="col">결제금액</th>
												<td class="txtblue txtright"><?php echo $pride; ?></td>
											</tr>
											<tr>
												<th scope="col">결제일시</th>
												<td><?php if($row['card_cdatetime'] != "0000-00-00 00:00:00") { echo date("y-m-d H:i", strtotime($row['card_sdatetime'])); } else { echo date("y-m-d H:i", strtotime($row['datetime'])); } ?></td>
											</tr>
											<tr>
												<th scope="col">취소일시</th>
												<td><?php if($row['card_cdatetime'] != "0000-00-00 00:00:00") { echo date("y-m-d H:i", strtotime($row['card_cdatetime'])); } ?></td>
											</tr>
											<tr>
												<th scope="col">결제자명</th>
												<td><?php echo $row['pay_pname']; ?></td>
											</tr>
											<tr>
												<th scope="col">카드</th>
												<td><?php echo $row['card_name']; ?></td>
											</tr>
											<tr>
												<th scope="col">카드번호</th>
												<td><?php echo format_number($row['pay_cardnum']); ?></td>
											</tr>
											<tr>
												<th scope="col">휴대전화</th>
												<td><?php echo $row['pay_phone']; ?></td>
											</tr>
											<tr>
												<th scope="col">결제상품</th>
												<td class="txtleft"><?php echo $row['pay_product']; ?></td>
											</tr>
											<tr>
												<th scope="col">PG</th>
												<td scope="row"><?php echo $pg_name; ?></td>
											</tr>
											<tr>
												<th scope="col">타입</th>
												<td scope="row"><?php echo $pg_type; ?></td>
											</tr>
											<tr>
												<th scope="row">할부</th>
												<td><?php if($row['pay_installment'] == '0') { echo "일시불"; } else { echo $row['pay_installment']."개월"; } ?></td>
											</tr>
											<tr>
												<th scope="col">영수증</th>
												<td><a href='./receipt.php?pay_id=<?php echo $row['pay_id']; ?>' onclick="win_receipt(this.href, <?php echo $row['authCd']; ?>); return false;" class="btn btn-black btn-xm">영수증</a></td>
											</tr>
											<tr>
												<th scope="col">취소</th>
												<td><?php echo $can_btn; ?></td>
											</tr>
											<tr>
												<th scope="col">재결제</th>
												<td><a href="./payment.php?mb_id=<?php echo $row['mb_id'] ?>&pay_id=<?php echo $row['pay_id']; ?>" onclick="return openWindow(this, {name:'payapptest',width:420,height:670,center:true,scrollbars:true})" class="btn btn-blue btn-xm">재결제</a></td>
											</tr>
										</table>
									</td>
									<td colspan="9"></td>
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

					<?php
						$qstr = "p=".$p."&fr_date=".$fr_date."&to_date=".$to_date."&payr=".$payr."&authCd=".$authCd."&dates=".$dates;
						echo get_paging_new(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page=');
					?>
				</div>
			</div>
		</div>
		</div>
	</section>
</section>

<script>
function payment_open(id) {
	$("#"+id).toggle();
}
</script>