<?php
	if($member['mb_level'] <= 3) {
		alert("권한이 없습니다.");
	}
	/*
	if($authCd) { $authCd_common = " and authCd = '{$authCd}' "; }
	if($mb_name) { $authCd_common = " and mb_name like '%{$mb_name}%' "; }

	if($is_admin) {
		$sql_common = " from {$g5['member_table']} ";
		$sql_search = " where (1) ";
		$sql_common = " from {$g5['member_table']} where resultCd = '0000' and (datetime BETWEEN '{$fr_date} 00:00:00' and '{$to_date} 23:59:59') $authCd_common";
	} else {
		$sql_common = " from {$g5['member_table']} ";
		$sql_search = "  where resultCd = '0000' and (datetime BETWEEN '{$fr_date} 00:00:00' and '{$to_date} 23:59:59') and mb_id = '{$member['mb_id']}' $authCd_common ";
		$sql_common = " from g5_payment_passgo";
	}

	$sql_common = " from  {$g5['member_table']} ";
	$sql_search = " where mb_level >= '3' ";

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

	if($pay_pg) {
		$sql_search .= " and mb_id in ( select mb_id from pay_member_pg where pg_mid = '{$pay_pg}' and pg_use = '1' ) ";
	}

	// 테이블의 전체 레코드수만 얻음
	$sql = " select COUNT(*) as cnt {$sql_common} {$sql_search} ";
	$row = sql_fetch($sql);
	$total_count = $row['cnt'];

	$sql = " select * {$sql_common} {$sql_search} order by mb_datetime asc ";
	$result = sql_query($sql);
	
	*/
	
	
	$sql_common = " from  {$g5['member_table']} ";
	$sql_search = " where mb_level = '3' and mb_mailling = '1' ";

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

	if (!$sst) {
		$sst = "mb_no";
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
	
?>

<section class="container" id="bbs">
	<section class="contents contents-bbs">
		<div class="top">
			<div class="inner">
				<form name="srch">
					<input type="hidden" name="p" value="<?php echo $p; ?>">
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
		<?php // echo $sql; ?>
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
						<caption class="hidden">TABLE</caption>
							<colgroup>
								<col style="width:50px;">
								<col>
								<col style="width:10%">
								<col style="width:10%">
								<col style="width:15%;">
								<col style="width:10%">
								<col span="3" style="width:10%;">
								<col span="2" style="width:10%;">
							</colgroup>
							<tbody>
								<?php
									//echo $sql;
									for ($i=0; $row=sql_fetch_array($result); $i++) {
										$total_pay = 0;
									//	$member_total = 0;

										$sums_sql = " select  SUM(IF(card_yn = 'Y', pay_price, 0)) AS Y_price, SUM(IF(card_yn = 'C', pay_price, 0)) AS N_price from pay_payment where (datetime BETWEEN '{$fr_date} 00:00:00' and '{$to_date} 23:59:59') and mb_id = '{$row['mb_id']}' ";
										$sums = sql_fetch($sums_sql);
										//echo $sums_sql."<br>";

										$Y_price = $sums['Y_price'];
										$N_price = -abs($sums['N_price']);

										$total_Y_pay = $total_Y_pay + $Y_price;
										$total_N_pay = $total_N_pay + $N_price;
										$total_pay = $Y_price + $N_price;
										$member_total = $member_total + $total_pay;
										/*
										if($row['mb_6'] == "b") {
											$mb_6 = "<span style='background:gray;color:#fff;padding:4px 5px 2px;font-size:11px;'>비</span>";
										} else if($row['mb_6'] == "i") {
											$mb_6 = "<span style='background:blue;color:#fff;padding:4px 5px 2px;font-size:11px;'>인</span>";
										} else if($row['mb_6'] == "no") {
											$mb_6 = "<span style='background:red;color:#fff;padding:4px 5px 2px;font-size:11px;'>N</span>";
										} else {
											$mb_6 = "<span style='background:green;color:#fff;padding:4px 5px 2px;font-size:11px;'>모</span>";
										}
										*/
										echo $mb678 = "";

										if($row['mb_6'] == "1") {
											$mb_6 = "<span style='background:red;color:#fff;padding:4px 5px 2px;font-size:11px;'>코페이</span>";
										} else {
											$mb_6 = "";
										}

										if($row['mb_7'] == "1") {
											$mb_7 = "<span style='background:blue;color:#fff;padding:4px 5px 2px;font-size:11px; margin-left:1px'>다날</span>";
										} else {
											$mb_7 = "";
										}

										if($row['mb_8'] == "1") {
											$mb_8 = "<span style='background:#666;color:#fff;padding:4px 5px 2px;font-size:11px; margin-left:1px'>웰컴</span>";
										} else {
											$mb_8 = "";
										}

										if(!$row['mb_6']) {
											if(!$row['mb_7']) {
												if(!$row['mb_8']) {
													$mb678 = "<span style='color:red;padding:4px 5px 2px;font-size:11px;'>결제불가</span>";
												}
											}
										}

						//				if($row['mb_10'] == "10") { $row['mb_10'] = ""; }
										if($row['mb_level'] == '1') {
										}
										$mblevel = "<span style='color:red'>정지</span>";
										if($row['mb_level'] >= 3) {
											$mblevel = "<span style='color:blue'>운영</span>";
										}
								?>


								<tr>
									<td scope="row" style="height:75px"><?php echo $mblevel; ?></td>
									<td style="text-align:left;" class="txtblue"><?php echo $row['mb_nick']; ?></td>
									<?php /*
									<td>
									<?php
										$sql_member_pg = " select * from pay_member_pg where mb_id = '{$row['mb_id']}' ";
										$result_member_pg = sql_query($sql_member_pg);
										for ($p=0; $row_member_pg=sql_fetch_array($result_member_pg); $p++) {
											if(!$row_member_pg['pg_pay']) { $pg_pay = 0; } else { $pg_pay = $row_member_pg['pg_pay']; }
											if($row_member_pg['pg_use'] == '1') {
												echo "<span class='btn-blue btn-xm' style='margin:1px;' title='PG사명 : ".$row_member_pg['pg_name']." / 최대한도 ㅣ ".number_format($pg_pay)." / 최대할부 : ".$row_member_pg['pg_hal']."'>";
											} else {
												echo "<span class='btn-light-gray btn-xm' style='margin:1px;color:#aaa'>";
											}
											echo $row_member_pg['pg_name'];
											echo "</span>";
										}
									?>
									</td>
									*/ ?>
									<td style="text-align:right;"><?php echo number_format($Y_price); ?></td>
									<td style="text-align:right"><?php echo number_format($N_price); ?></td>
									<td style="text-align:left;"><?php echo $row['mb_name']; ?></td>
									<td><?php echo format_phone($row['mb_hp']); ?></td>
									<td>
									
									<?php
									$mb_dir = substr($row['mb_id'], 0, 2);
									$icon_file = G5_DATA_PATH . '/member/' . $mb_dir . '/' . get_mb_icon_name($row['mb_id']) . '.gif';
									if (file_exists($icon_file)) {
										$icon_url = str_replace(G5_DATA_PATH, G5_DATA_URL, $icon_file);
										$icon_filemtile = (defined('G5_USE_MEMBER_IMAGE_FILETIME') && G5_USE_MEMBER_IMAGE_FILETIME) ? '?' . filemtime($icon_file) : '';
										echo '<a href="' . $icon_url . $icon_filemtile . '" target="_blank"><img src="' . $icon_url . $icon_filemtile . '" alt="" style="height:50px;"></a> ';
									}

									$icon_file = G5_DATA_PATH . '/member_image/' . $mb_dir . '/' . get_mb_icon_name($row['mb_id']) . '.gif';
									if (file_exists($icon_file)) {
										$icon_url = str_replace(G5_DATA_PATH, G5_DATA_URL, $icon_file);
										$icon_filemtile = (defined('G5_USE_MEMBER_IMAGE_FILETIME') && G5_USE_MEMBER_IMAGE_FILETIME) ? '?' . filemtime($icon_file) : '';
										echo '<a href="' . $icon_url . $icon_filemtile . '" target="_blank"><img src="' . $icon_url . $icon_filemtile . '" alt="" style="height:50px;"></a> ';
									}
									?>

									</td>
									<?php /*
									<td><?php echo substr($row['mb_datetime'],0,-9); ?></td>
									*/ ?>
									
									<td><a href="./?admin_login=login&login_us_id=<?=$row['mb_id']?>" class='btn-black-line btn-xm'>로그인</a></td>
									<td><a href="javascript:;" class="btn btn-blue btn-xm" onclick="member_open('<?php echo $row['mb_id']; ?>_tid');">PG정보</a></td>
									<td><a href="javascript:;" class="btn btn-light-gray btn-xm" onclick="member_open('<?php echo $row['mb_id']; ?>_login');">접속정보</a></td>
									<td><a href="./?p=member_form&mb_id=<?php echo $row['mb_id']; ?>&w=u" class='btn-black btn-xm'>정보수정</a></td>
								</tr>
								<tr style="display:none" id="<?php echo $row['mb_id']; ?>_tid">
									<td></td>
									<td colspan="9" class="subtable" style="vertical-align: top;">
										<?php
											$sql_member_pg = " select * from pay_member_pg where mb_id = '{$row['mb_id']}' ";
											$result_member_pg = sql_query($sql_member_pg);
										?>
										<table>
											<thead>
											<tr>
												<th>PG사명</th>
												<th>타입</th>
												<th>사용불가카드</th>
												<th style="width:15%">사용유무</th>
												<th style="width:15%">한도</th>
												<th style="width:10%">할부</th>
												<th style="width:10%">중복결제</th>
												<th style="width:15%">TID or MBR</th>
												<th style="width:15%">분할TID</th>
											</tr>
											</thead>
											<tbody>
											<?php
												for ($p=0; $row_member_pg=sql_fetch_array($result_member_pg); $p++) {
													if($row_member_pg['pg_use'] == 0) { $pg_use = "<div class='btn-blue btn-xm'>사용중</span>"; } else { $pg_use = "<div class='btn-light-gray btn-xm' style='color:#aaa'>미사용</div>"; }
													$pg = sql_fetch(" select * from pay_pg where pg_id = '{$row_member_pg['pg_mid']}' ");
													if($pg['pg_certified'] == "1") { $pg_certified = "비인증"; } else if($pg['pg_certified'] == "2") { $pg_certified = "인증"; } else { $pg_certified = "구인증"; }
											?>
											<tr>
												<td class="pname"><?php echo $row_member_pg['pg_name']; ?></td>
												<td><?php echo $pg_certified; ?></td>
												<td>
													<?php
														if($pg['card_nh'] == "1") { echo "<span class='cards'>농협</span> "; }
														if($pg['card_bc'] == "1") { echo "<span class='cards'>비씨</span> "; }
														if($pg['card_sh'] == "1") { echo "<span class='cards'>신한</span> "; }
														if($pg['card_kb'] == "1") { echo "<span class='cards'>국민</span> "; }
														if($pg['card_hana'] == "1") { echo "<span class='cards'>하나</span> "; }
														if($pg['card_wr'] == "1") { echo "<span class='cards'>우리</span> "; }
														if($pg['card_ss'] == "1") { echo "<span class='cards'>삼성</span> "; }
														if($pg['card_lo'] == "1") { echo "<span class='cards'>롯데</span> "; }
														if($pg['card_hd'] == "1") { echo "<span class='cards'>현대</span> "; }
													?>
												</td>
												<td><?php echo $pg_use; ?></td>
												<td class="pay"><?php if($row_member_pg['pg_pay']) { echo number_format($row_member_pg['pg_pay']); } else { echo "0"; } ?></td>
												<td><?php if($row_member_pg['pg_hal'] >= 1) { echo $row_member_pg['pg_hal']."개월"; } else { echo "일시불"; } ?></td>
												<td><?php if($row_member_pg['pg_overlap'] == 1) { echo "불가"; } else { echo "가능"; } ?></td>
												<td><?php echo $row_member_pg['pg_key1']; ?></td>
												<td><?php echo $row_member_pg['pg_tid']; ?><?php echo $row['mb_id']; ?></td>
											</tr>
											<?php
												}
											?>
											</tbody>
										</table>
									</td>
									<td colspan="2" style="text-align:left; vertical-align: top;" class="subtable">
										<table>
											<tbody>
											<tr>
												<th style="width:50px;">업체명</th>
												<td class="pname"><?php echo $row['mb_nick']; ?></td>
											</tr>
											<tr>
												<th>담당자</th>
												<td class="pname"><?php echo $row['mb_name']; ?></td>
											</tr>
											<tr>
												<th>후대전화</th>
												<td class="pname"><?php echo format_phone($row['mb_hp']); ?></td>
											</tr>
											<tr>
												<th>업종</th>
												<td class="pname"><?php echo $row['mb_email_certify2']; ?></td>
											</tr>
											<tr>
												<th>종목</th>
												<td class="pname"><?php echo $row['mb_lost_certify']; ?></td>
											</tr>
											</tbody>
										</table>
									</td>
								</tr>
								<tr style="display:none;" id="<?php echo $row['mb_id']; ?>_login">
									<td></td>
									<td colspan="9" class="subtable">
<textarea style="border:0; height:150px;" readonly id="copy_<?php echo $row['mb_id']; ?>">
- 원성페이먼츠 접속정보 -
업체명 : <?php echo $row['mb_nick']; ?>&#10;접속주소 : http://www.<?php echo $_SERVER['SERVER_NAME']; ?>&#10;아이디 : <?php echo $row['mb_id']; ?>&#10;비밀번호 : <?php echo $row['mb_birth']; ?>
</textarea>

<script>
$("#copy_<?php echo $row['mb_id']; ?>").click(function() {
var content = document.getElementById('copy_<?php echo $row['mb_id']; ?>');
content.select();
document.execCommand('copy');
});
</script>

클릭시 자동복사 됩니다.
									</td>
								</tr>
								<?php
									$sums['total_pay'] = 0;
									}
									$sums['total_pay'] = 0;
								?>
							</tbody>
							<thead>
								<tr>
									<th scope="row">운영</th>
									<th scope="col">업체명</th>
									<?php /*
									<th scope="col">결제업체</th>
									*/ ?>
									<th scope="col" style="text-align:right;"><?php echo number_format($total_Y_pay); ?></th>
									<th scope="col" style="text-align:right;"><?php echo number_format($total_N_pay); ?></th>
									<th scope="col">담당자</th>
									<th scope="col">휴대전화</th>
									<th scope="col">서류</th>
									<?php /*
									<th scope="col">등록일</th>
									*/ ?>
									<th scope="col">로그인</th>
									<th scope="col">PG정보</th>
									<th scope="col">접속정보</th>
									<th scope="col">관리</th>
								</tr>
							</thead>
						</table>
					</div>
					<?php /*
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

					<div class="text-right btn-vertical btn-group">
						<a class="btn btn-black" href="./?p=member_form">회원등록</a>
					</div>
					*/ ?>

					<?php
						$qstr = "p=".$_GET['p'];
						echo get_paging_new(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page=');
					?>
				</div>
			</div>
		</div>
		</div>
	</section>
</section>

<script>
function member_open(id) {
	$("#"+id).toggle();
}
</script>

<?php
	include_once("./_foot.php");
?>