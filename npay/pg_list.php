<?php
	if($member['mb_level'] <= 3) {
		alert("권한이 없습니다.");
	}

	$sql_common = " from pay_pg ";

	$sql_search = " where (1) ";

	if($use == "1") {
		$sql_search .= " and `pg_use` = '1'";
	} else if($use == "all") {
		$sql_search .= "";
	} else {
		$sql_search .= " and `pg_use` = '0'";
	}


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

	// 테이블의 전체 레코드수만 얻음
	$sql = " select COUNT(*) as cnt {$sql_common}  {$sql_search}";
	$row = sql_fetch($sql);
	$total_count = $row['cnt'];
	$page = 1;

	$sql = " select * {$sql_common} {$sql_search} order by pg_sort asc ";
	$result = sql_query($sql);
?>

<section class="container" id="bbs">
	<section class="contents contents-bbs">
		<div class="top">
			<div class="inner">
				<form name="srch">
				<input type="hidden" name="p" value="<?php echo $p; ?>">
					<div class="top-search pull-right">
						<div class="search-wrap">
							<select class="form-control" name="sfl" id="sfl">
								<option value="pg_name">PG사명</option>
								<option value="pg_pay">한도</option>
								<option value="pg_hal">할부</option>
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
					<li class="<?php if(!$use) { echo "active"; } ?>"><a href="./?p=<?php echo $p; ?>">사용중</a></li>
					<li class="<?php if($use == "1") { echo "active"; } ?>"><a href="./?p=<?php echo $p; ?>&use=1">미사용</a></li>
					<li class="<?php if($use == "all") { echo "active"; } ?>"><a href="./?p=<?php echo $p; ?>&use=all">전체</a></li>
				</ul>
			</div>
		</div>

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
							<tbody>
								<?php
									//	echo $sql;
									for ($i=0; $row=sql_fetch_array($result); $i++) {
										$num = number_format($total_count - ($page - 1) * $config['cf_page_rows'] - $i);

										if($row['pg_use'] == "1") { $pg_use = "미사용"; } else { $pg_use = "사용"; }
										if($row['pg_overlap'] == "1") { $pg_overlap = "불가"; } else { $pg_overlap = "가능"; }
										if($row['pg_certified'] == "1") { $pg_certified = "비인증"; } else if($row['pg_certified'] == "2") { $pg_certified = "인증"; } else { $pg_certified = "구인증"; }
										/*
										if($row['pg_tid'] == "1") { $pg_tid = "사용"; } else { $pg_tid = "미사용"; }
										if($row['pg_key'] == "1") { $pg_key = "사용"; } else { $pg_key = "미사용"; }
										if($row['pg_mbr'] == "1") { $pg_mbr = "사용"; } else { $pg_mbr = "미사용"; }
										*/



								?>


								<tr style="background:<?php if($pg_id == $row['pg_id']) { echo "#eee"; } else { echo "#fff"; } ?>">
									<td><?php echo $row['pg_sort']; ?></td>
									<td><?php echo $pg_use; ?></td>
									<td class="txtblue txtleft"><?php echo $row['pg_code']; ?></td>
									<td class="txtblue txtleft"><?php echo $row['pg_name']; ?></td>
									<td class="txtleft"><?php echo $pg_certified; ?></td>
									<td class="txtblue txtright"><?php echo number_format($row['pg_pay']); ?></td>
									<td><?php echo $row['pg_hal']; ?>개월</td>
									<td><?php echo $pg_overlap; ?></td>
									<td><?php echo $row['pg_tid']; ?></td>
									<td><?php echo $row['pg_key1']; ?></td>
									<td><?php echo $row['pg_key2']; ?></td>
									<td><?php echo $row['pg_key3']; ?></td>
									<td><?php echo $row['pg_key4']; ?></td>
									<td><?php echo $row['pg_key5']; ?></td>
									<td>
										<?php if($row['card_nh'] == "1") { echo "<span class='cards'>농협</span>"; } ?>
										<?php if($row['card_bc'] == "1") { echo "<span class='cards'>비씨</span>"; } ?>
										<?php if($row['card_sh'] == "1") { echo "<span class='cards'>신한</span>"; } ?>
										<?php if($row['card_kb'] == "1") { echo "<span class='cards'>국민</span>"; } ?>
										<?php if($row['card_hana'] == "1") { echo "<span class='cards'>하나</span>"; } ?>
										<?php if($row['card_wr'] == "1") { echo "<span class='cards'>우리</span>"; } ?>
										<?php if($row['card_ss'] == "1") { echo "<span class='cards'>삼성</span>"; } ?>
										<?php if($row['card_lo'] == "1") { echo "<span class='cards'>롯데</span>"; } ?>
										<?php if($row['card_hd'] == "1") { echo "<span class='cards'>현대</span>"; } ?>
									</td>
									<td><a href="./?p=pg_list&pg_id=<?php echo $row['pg_id']; ?>&w=u" class='btn btn-black btn-xm'>수정</a></td>
									<td><a onclick="deletes(<?php echo $row['pg_id']; ?>,'d');" class='btn btn-black btn-xm'>삭제</a></td>
								</tr>
								<?php
									$sums['total_pay'] = 0;
									}
									$sums['total_pay'] = 0;
								?>
							</tbody>
							<thead>
								<tr>
									<th scope="col">순서</th>
									<th scope="col">사용유무</th>
									<th scope="col">PG코드</th>
									<th scope="col">PG사명</th>
									<th scope="col">결제타입</th>
									<th scope="col">최대한도</th>
									<th scope="col">최대할부</th>
									<th scope="col">중복결제</th>
									<th scope="col">TID분리</th>
									<th scope="col">KEY 1</th>
									<th scope="col">KEY 2</th>
									<th scope="col">KEY 3</th>
									<th scope="col">KEY 4</th>
									<th scope="col">KEY 5</th>
									<th scope="col">사용불가 카드</th>
									<th scope="col" colspan="2">관리</th>
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
					*/ ?>

					<div class="text-right btn-vertical btn-group">
						<a class="btn btn-black" href="./?p=pg_list&w=i">신규등록</a>
					</div>

				</div>
			</div>
		</div>


<?php
	if($w) {
		if($pg_id) { $row = sql_fetch(" select * from pay_pg where pg_id = '{$pg_id}' "); }
?>
		<div class="bbs-cont">
			<div class="inner">
				<div class="write-form">
					<form name="insert_bbs_form" method="post" enctype="multipart/form-data" action="./?p=pg_update" onsubmit="return false;">
						<input type="hidden" name="w" value="<?php echo $w; ?>">
						<input type="hidden" name="pg_id" value="<?php echo $pg_id; ?>">
						<fieldset>
							<?php /*
							<legend class="sr-only">문의정보</legend>
							<div class="panel">
								<div class="panel-heading">
									<label for="" class="tit">그룹선택</label>
								</div>
								<div class="panel-body">
									<select name="mb_10" id="mb_10" class="form-control">
										<option value="1" <?php if($mb['mb_10'] == "1") { echo "selected"; } ?>>1번 그룹</option>
										<option value="2" <?php if($mb['mb_10'] == "2") { echo "selected"; } ?>>2번 그룹</option>
										<option value="3" <?php if($mb['mb_10'] == "3") { echo "selected"; } ?>>3번 그룹</option>
										<option value="4" <?php if($mb['mb_10'] == "4") { echo "selected"; } ?>>4번 그룹</option>
										<option value="5" <?php if($mb['mb_10'] == "5") { echo "selected"; } ?>>5번 그룹</option>
										<option value="6" <?php if($mb['mb_10'] == "6") { echo "selected"; } ?>>6번 그룹</option>
										<option value="7" <?php if($mb['mb_10'] == "7") { echo "selected"; } ?>>7번 그룹</option>
										<option value="8" <?php if($mb['mb_10'] == "8") { echo "selected"; } ?>>8번 그룹</option>
										<option value="9" <?php if($mb['mb_10'] == "9") { echo "selected"; } ?>>9번 그룹</option>
									</select>
								</div>
							</div>
							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">순서</label>
								</div>
								<div class="panel-body">
									<div class="panel-body2">
										<label class="input-chk"><input type="checkbox" name="mb_6" value="1" <?php if($mb['mb_6'] == "1") { echo "checked"; } ?>> <span>코페이(최대 <?php echo number_format(substr($config['cf_1'],0,-4)); ?>만원 / <?php echo $config['cf_2']; ?>개월) 결제가능</span></label>
									</div>
									<div class="panel-body2">
										<label class="input-chk"><input type="checkbox" class="form-control" name="mb_7" value="1" <?php if($mb['mb_7'] == "1") { echo "checked"; } ?>> <span>다날(최대 <?php echo number_format(substr($config['cf_3'],0,-4)); ?>만원 / <?php echo $config['cf_4']; ?>개월) 결제가능</span></label>
									</div>
									<div class="panel-body2">
										<label class="input-chk"><input type="checkbox" class="form-control" name="mb_8" value="1" <?php if($mb['mb_8'] == "1") { echo "checked"; } ?>> <span>웰컴(최대 <?php echo number_format(substr($config['cf_5'],0,-4)); ?>만원 / <?php echo $config['cf_6']; ?>개월) 결제가능</span></label>
									</div>
								</div>
							</div>
							*/ ?>
							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">순서</label>
								</div>
								<div class="panel-body">
									<input type="number" class="form-control" name="pg_sort" id="pg_sort" placeholder="숫자만 입력 낮을수록 상위에 뜹니다." value="<?php echo $row['pg_sort']; ?>" required>
								</div>
							</div>
							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">사용유무</label>
								</div>
								<div class="panel-body">
									<select name="pg_use" id="pg_use" class="form-control">
										<option value="0" <?php if($row['pg_use'] == "0") { echo "selected"; } ?>>사용</option>
										<option value="1" <?php if($row['pg_use'] == "1") { echo "selected"; } ?>>미사용</option>
									</select>
								</div>
							</div>
							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">PG코드</label>
								</div>
								<div class="panel-body">
									<input type="text" class="form-control" name="pg_code" id="pg_code" placeholder="PG코드" value="<?php echo $row['pg_code']; ?>" required>
								</div>
							</div>
							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">PG사명</label>
								</div>
								<div class="panel-body">
									<input type="text" class="form-control" name="pg_name" id="pg_name" placeholder="PG사명" value="<?php echo $row['pg_name']; ?>" required>
								</div>
							</div>
							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">결제타입</label>
								</div>
								<div class="panel-body">
									<select name="pg_certified" id="pg_certified" class="form-control">
										<option value="0" <?php if($row['pg_certified'] == "0") { echo "selected"; } ?>>구인증</option>
										<option value="1" <?php if($row['pg_certified'] == "1") { echo "selected"; } ?>>비인증</option>
										<option value="2" <?php if($row['pg_certified'] == "2") { echo "selected"; } ?>>인증[준비중]</option>
									</select>
								</div>
							</div>
							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">최대한도</label>
								</div>
								<div class="panel-body">
									<input type="text" class="form-control" name="pg_pay" id="pg_pay" placeholder="최대한도" value="<?php echo $row['pg_pay']; ?>" required>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">최대할부</label>
								</div>
								<div class="panel-body">
									<select name="pg_hal" id="pg_hal" class="form-control">
										<option value="1" <?php if($row['pg_hal'] == "1") { echo "selected"; } ?>>일시불</option>
										<option value="2" <?php if($row['pg_hal'] == "2") { echo "selected"; } ?>>2개월</option>
										<option value="3" <?php if($row['pg_hal'] == "3") { echo "selected"; } ?>>3개월</option>
										<option value="4" <?php if($row['pg_hal'] == "4") { echo "selected"; } ?>>4개월</option>
										<option value="5" <?php if($row['pg_hal'] == "5") { echo "selected"; } ?>>5개월</option>
										<option value="6" <?php if($row['pg_hal'] == "6") { echo "selected"; } ?>>6개월</option>
										<option value="7" <?php if($row['pg_hal'] == "7") { echo "selected"; } ?>>7개월</option>
										<option value="8" <?php if($row['pg_hal'] == "8") { echo "selected"; } ?>>8개월</option>
										<option value="9" <?php if($row['pg_hal'] == "9") { echo "selected"; } ?>>9개월</option>
										<option value="10" <?php if($row['pg_hal'] == "10") { echo "selected"; } ?>>10개월</option>
										<option value="11" <?php if($row['pg_hal'] == "11") { echo "selected"; } ?>>11개월</option>
										<option value="12" <?php if($row['pg_hal'] == "12") { echo "selected"; } ?>>12개월</option>
									</select>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">중복결제</label>
								</div>
								<div class="panel-body">
									<select name="pg_overlap" id="pg_overlap" class="form-control">
										<option value="0" <?php if($row['pg_overlap'] == "0") { echo "selected"; } ?>>가능</option>
										<option value="1" <?php if($row['pg_overlap'] == "1") { echo "selected"; } ?>>불가</option>
									</select>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">TID 분리</label>
								</div>
								<div class="panel-body">
									<input type="text" class="form-control" name="pg_tid" id="pg_tid" placeholder="TID 분리시 앞3자리" value="<?php echo $row['pg_tid']; ?>" required>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">KEY 1</label>
								</div>
								<div class="panel-body">
									<input type="text" class="form-control" name="pg_key1" id="pg_key1" placeholder="키 이름입력 예) TID, KEY, MBR, ivValue" value="<?php echo $row['pg_key1']; ?>">
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">KEY 2</label>
								</div>
								<div class="panel-body">
									<input type="text" class="form-control" name="pg_key2" id="pg_key2" placeholder="키 이름입력 예) TID, KEY, MBR, ivValue" value="<?php echo $row['pg_key2']; ?>">
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">KEY 3</label>
								</div>
								<div class="panel-body">
									<input type="text" class="form-control" name="pg_key3" id="pg_key3" placeholder="키 이름입력 예) TID, KEY, MBR, ivValue" value="<?php echo $row['pg_key3']; ?>">
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">KEY 4</label>
								</div>
								<div class="panel-body">
									<input type="text" class="form-control" name="pg_key4" id="pg_key4" placeholder="키 이름입력 예) TID, KEY, MBR, ivValue" value="<?php echo $row['pg_key4']; ?>">
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">KEY 5</label>
								</div>
								<div class="panel-body">
									<input type="text" class="form-control" name="pg_key5" id="pg_key5" placeholder="키 이름입력 예) TID, KEY, MBR, ivValue" value="<?php echo $row['pg_key5']; ?>">
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">사용불가 카드</label>
								</div>
								<div class="panel-body">

									<label class="input-chk"><input type="checkbox" class="form-control" name="card_nh" id="card_nh" value="1" <?php if($row['card_nh'] == "1") { echo "checked"; } ?>> <span>농협카드</span></label>
									<label class="input-chk"><input type="checkbox" class="form-control" name="card_bc" id="card_bc" value="1" <?php if($row['card_bc'] == "1") { echo "checked"; } ?>> <span>비씨카드</span></label>
									<label class="input-chk"><input type="checkbox" class="form-control" name="card_sh" id="card_sh" value="1" <?php if($row['card_sh'] == "1") { echo "checked"; } ?>> <span>신한카드</span></label>
									<label class="input-chk"><input type="checkbox" class="form-control" name="card_kb" id="card_kb" value="1" <?php if($row['card_kb'] == "1") { echo "checked"; } ?>> <span>국민카드</span></label>
									<label class="input-chk"><input type="checkbox" class="form-control" name="card_hana" id="card_hana" value="1" <?php if($row['card_hana'] == "1") { echo "checked"; } ?>> <span>하나카드</span></label>
									<label class="input-chk"><input type="checkbox" class="form-control" name="card_wr" id="card_wr" value="1" <?php if($row['card_wr'] == "1") { echo "checked"; } ?>> <span>우리카드</span></label>
									<label class="input-chk"><input type="checkbox" class="form-control" name="card_ss" id="card_ss" value="1" <?php if($row['card_ss'] == "1") { echo "checked"; } ?>> <span>삼성카드</span></label>
									<label class="input-chk"><input type="checkbox" class="form-control" name="card_lo" id="card_lo" value="1" <?php if($row['card_lo'] == "1") { echo "checked"; } ?>> <span>롯데카드</span></label>
									<label class="input-chk"><input type="checkbox" class="form-control" name="card_hd" id="card_hd" value="1" <?php if($row['card_hd'] == "1") { echo "checked"; } ?>> <span>현대카드</span></label>

								</div>
							</div>
						</fieldset>
					</form>
				</div>
				<div class="btn-center-block">
					<div class="btn-group btn-horizon">
						<div class="btn-table">
							<button type="submit" class="btn btn-black btn-cell"  onclick="insert_bbs()"><?php if($w == "u") { echo "수정"; } else { echo "신규등록"; } ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>


		<script>
			function insert_bbs() {
				var f = document.insert_bbs_form;
				/*
				if ($('#bbsType').val()=='') {
					alert('문의 유형을 선택하세요.');
					$('#bbsType').get(0).focus();
					return;
				}
				if ($('#usernm').val() == '') {
					alert('작성자를 입력해 주세요.');
					$('#usernm').get(0).focus();
					return;
				}
				if ($('#title').val() == '') {
					alert('제목을 입력해 주세요.');
					$('#title').get(0).focus();
					return;
				}
				if ($('#contents').val() == '') {
					alert('내용을 입력해 주세요.');
					$('#contents').get(0).focus();
					return;
				}
				if ($('#pass').val() == '') {
					alert('비밀번호를 입력해 주세요.');
					$('#pass').get(0).focus();
					return;
				}
				if ($('#captcha_code').val() == '') {
					alert('보안문자를 입력해 주세요.');
					$('#captcha_code').get(0).focus();
					return;
				}
				if (!$('#agreement1').prop('checked')) {
					alert('약관에 동의하셔야 가능합니다.');
					return;
				}

				if (f.file_file.value != "")
					if (!attachfile_check(f.file_file.value, "지원하지 않는 파일형식이므로 첨부하실수없습니다.")) return;
				f.target = '_writeFrame';
				*/
				f.submit();
			}
		</script>

		<?php } ?>
	</section>
</section>



<form id="dform" method="post">
<input type="hidden" name="pg_id" value="">
<input type="hidden" name="w" value="">
</form>

<script>

function deletes(pg_id,w){
	if(confirm("정말 삭제 하시겠습니까? \n회원들에게 저장된 PG정보도 모두 삭제 됩니다.")){
		document.getElementById("dform").action = "./?p=pg_update";
		document.getElementById("dform").pg_id.value = pg_id;
		document.getElementById("dform").w.value = w;
		document.getElementById("dform").submit();
	}
}
</script>