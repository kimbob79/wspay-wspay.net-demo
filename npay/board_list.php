
<?php
	$write_table = $g5['write_prefix'] . $bo_table;
	$sql_common = " from {$write_table} ";

	// 문의 게시판은 1:1 게시판으로
	if($bo_table == "qa") {
		if($is_admin) {
			$sql_search = " where wr_is_comment = 0 ";
		} else {
			$sql_search = " where wr_is_comment = 0 and mb_id = '{$member['mb_id']}' ";
		}
	} else {
		$sql_search = " where wr_is_comment = 0 ";
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

	if (!$sst) {
		$sst = "wr_datetime";
		$sod = "desc";
	}

	$sql_order = " order by {$sst} {$sod} ";

	$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
	$row = sql_fetch($sql);
	$total_count = $row['cnt'];

	$config['cf_page_rows'] = 5;
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
				<div class="heading-tit pull-left">
					<h2><?php echo $row_board['bo_subject']; ?></h2>
					<ul>
						<li>SIMPLEPAY</li>
						<li>편리한 결제 환경</li>
					</ul>
				</div>
				<div class="top-search pull-right">
					<form name="srch">
						<input type="hidden" name="p" value="<?php echo $p; ?>">
						<fieldset>
							<legend class="sr-only">검색하기</legend>
							<div class="search-wrap">
								<select class="form-control" name="sfl" id="sfl">
									<option value="s_keyword">제목+내용</option>
									<option value="s_title">제목</option>
									<option value="s_content">내용</option>
								</select>
								<input type="text" class="form-control" placeholder="검색어를 입력하세요." name="stx" id="stx" value="<?php echo $stx; ?>">
								<button type="submit" class="search-btn"></button>
							</div>
						</fieldset>
					</form>
				</div>
			</div>
		</div>
		<div class="bbs-cont">
			<div class="inner">
				<ul class="cont-wrap list-area">

					<?php
						for ($i = 0; $row = sql_fetch_array($result); $i++) {
							$num = number_format($total_count - ($page - 1) * $config['cf_page_rows'] - $i);
					?>
					<li class="item-info">
						<a class="view-link" href="./?p=<?php echo $p; ?>&bo_table=<?php echo $bo_table; ?>&pm=view&page=<?php echo $page; ?>&wr_id=<?php echo $row['wr_id']; ?>">
							<div class="list-tit">
								<div class="left-box"><?php echo $num; ?></div>
								<div class="right-box ask-box">
									<p class="tit">
										<?php echo $row['wr_subject']; ?>
										<i class="ico ico-lock"></i>
									</p>
									<div class="txt">
										<div class="pull-right">
											<span class="q-name text-blue"><?php echo $row['ca_name']; ?></span>
											<span class="user"><?php echo $row['wr_name']; ?></span>
											<span class="num date"><?php echo $row['wr_datetime']; ?></span>
										</div>
									</div>
								</div>
							</div>
						</a>
					</li>
					<?php
						}
					?>
					<?php /*
					<li class="item-info">
						<a class="view-link" href="./?p=<?php echo $p; ?>&pp=<?php echo $pp; ?>&pm=view">
							<div class="list-tit">
								<div class="left-box">
									<span class="label-black">공지</span>
								</div>
								<div class="right-box ask-box">
									<p class="tit"><span class="admin-name">[연동문의]</span>신한 슈퍼SOL 패키지명 추가 안내</p>
									<div class="txt">
										<div class="pull-right">
											<span class="user">고객센터</span>
											<span class="num date">2023-12-18 17:39:58</span>
										</div>
									</div>
								</div>
							</div>
						</a>
					</li>
					<li class="item-info">
						<a class="view-link" href="./?p=<?php echo $p; ?>&pp=<?php echo $pp; ?>&pm=view">
							<div class="list-tit">
								<div class="left-box">
									<span class="label-black">공지</span>
								</div>
								<div class="right-box ask-box">
									<p class="tit"><span class="admin-name">[연동문의]</span>페이앱을 로컬에서 테스트 할 수 있는 프로그램입니다.</p>
									<div class="txt">
										<div class="pull-right">
											<span class="user">남**</span>
											<span class="num date">2017-02-06 15:19:51</span>
										</div>
									</div>
								</div>
							</div>
						</a>
					</li>
					<li class="item-info">
						<a class="view-link" href="./?p=<?php echo $p; ?>&pp=<?php echo $pp; ?>&pm=view">
							<div class="list-tit">
								<div class="left-box">
									<span class="label-black">공지</span>
								</div>
								<div class="right-box ask-box">
									<p class="tit"><span class="admin-name">[연동문의]</span>Node.JS용 REST API 예제를 만들었습니다. 필요하신 분들은 보시길 바랍니다.</p>
									<div class="txt">
										<div class="pull-right">
											<span class="user">해*</span>
											<span class="num date">2016-10-10 16:10:12</span>
										</div>
									</div>
								</div>
							</div>
						</a>
					</li>
					<li class="item-info">
						<a class="view-link" href="./?p=<?php echo $p; ?>&pp=<?php echo $pp; ?>&pm=view">
							<div class="list-tit">
								<div class="left-box">
									<span class="label-black">공지</span>
								</div>
								<div class="right-box ask-box">
									<p class="tit"><span class="admin-name">[연동문의]</span>PayApp 서비스 연동 매뉴얼<i class="ico ico-file"></i></p>
									<div class="txt">
										<div class="pull-right">
											<span class="user">고객센터</span>
											<span class="num date">2012-11-19 15:36:27</span>
										</div>
									</div>
								</div>
							</div>
						</a>
					</li>
					<li class="item-info">
						<a class="view-link" href="./?p=<?php echo $p; ?>&pp=<?php echo $pp; ?>&pm=view">
							<div class="list-tit">
								<div class="left-box">4035</div>
								<div class="right-box ask-box">
									<p class="tit"><span class="admin-name">[일반문의]</span>비공개글<i class="ico ico-lock"></i></p>
									<div class="txt">
										<div class="pull-right">
											<span class="q-name text-blue">답변완료</span>
											<span class="user">우**</span>
											<span class="num date">2024-01-24 17:08:58</span>
										</div>
									</div>
								</div>
							</div>
						</a>
					</li>
					<li class="item-info">
						<a class="view-link" href="./?p=<?php echo $p; ?>&pp=<?php echo $pp; ?>&pm=view">
							<div class="list-tit">
								<div class="left-box">4034</div>
									<div class="right-box ask-box">
										<p class="tit"><span class="admin-name">[연동문의]</span>비공개글<i class="ico ico-lock"></i></p>
										<div class="txt">
											<div class="pull-right"><span class="q-name">신규문의</span>
											<span class="user">김**</span>
											<span class="num date">2024-01-24 16:50:34</span>
										</div>
									</div>
								</div>
							</div>
						</a>
					</li>
					<li class="item-info">
						<a class="view-link" href="./?p=<?php echo $p; ?>&pp=<?php echo $pp; ?>&pm=view">
							<div class="list-tit">
								<div class="left-box">4035</div>
								<div class="right-box ask-box">
									<p class="tit"><span class="admin-name">[일반문의]</span>비공개글<i class="ico ico-lock"></i></p>
									<div class="txt">
										<div class="pull-right">
											<span class="q-name text-blue">답변완료</span>
											<span class="user">우**</span>
											<span class="num date">2024-01-24 17:08:58</span>
										</div>
									</div>
								</div>
							</div>
						</a>
					</li>
					<li class="item-info">
						<a class="view-link" href="./?p=<?php echo $p; ?>&pp=<?php echo $pp; ?>&pm=view">
							<div class="list-tit">
								<div class="left-box">4034</div>
									<div class="right-box ask-box">
										<p class="tit"><span class="admin-name">[연동문의]</span>비공개글<i class="ico ico-lock"></i></p>
										<div class="txt">
											<div class="pull-right"><span class="q-name">신규문의</span>
											<span class="user">김**</span>
											<span class="num date">2024-01-24 16:50:34</span>
										</div>
									</div>
								</div>
							</div>
						</a>
					</li>
					<li class="item-info">
						<a class="view-link" href="./?p=<?php echo $p; ?>&pp=<?php echo $pp; ?>&pm=view">
							<div class="list-tit">
								<div class="left-box">4035</div>
								<div class="right-box ask-box">
									<p class="tit"><span class="admin-name">[일반문의]</span>비공개글<i class="ico ico-lock"></i></p>
									<div class="txt">
										<div class="pull-right">
											<span class="q-name text-blue">답변완료</span>
											<span class="user">우**</span>
											<span class="num date">2024-01-24 17:08:58</span>
										</div>
									</div>
								</div>
							</div>
						</a>
					</li>
					<li class="item-info">
						<a class="view-link" href="./?p=<?php echo $p; ?>&pp=<?php echo $pp; ?>&pm=view">
							<div class="list-tit">
								<div class="left-box">4034</div>
									<div class="right-box ask-box">
										<p class="tit"><span class="admin-name">[연동문의]</span>비공개글<i class="ico ico-lock"></i></p>
										<div class="txt">
											<div class="pull-right"><span class="q-name">신규문의</span>
											<span class="user">김**</span>
											<span class="num date">2024-01-24 16:50:34</span>
										</div>
									</div>
								</div>
							</div>
						</a>
					</li>
					<li class="item-info">
						<a class="view-link" href="./?p=<?php echo $p; ?>&pp=<?php echo $pp; ?>&pm=view">
							<div class="list-tit">
								<div class="left-box">4035</div>
								<div class="right-box ask-box">
									<p class="tit"><span class="admin-name">[일반문의]</span>비공개글<i class="ico ico-lock"></i></p>
									<div class="txt">
										<div class="pull-right">
											<span class="q-name text-blue">답변완료</span>
											<span class="user">우**</span>
											<span class="num date">2024-01-24 17:08:58</span>
										</div>
									</div>
								</div>
							</div>
						</a>
					</li>
					<li class="item-info">
						<a class="view-link" href="./?p=<?php echo $p; ?>&pp=<?php echo $pp; ?>&pm=view">
							<div class="list-tit">
								<div class="left-box">4034</div>
									<div class="right-box ask-box">
										<p class="tit"><span class="admin-name">[연동문의]</span>비공개글<i class="ico ico-lock"></i></p>
										<div class="txt">
											<div class="pull-right"><span class="q-name">신규문의</span>
											<span class="user">김**</span>
											<span class="num date">2024-01-24 16:50:34</span>
										</div>
									</div>
								</div>
							</div>
						</a>
					</li>
					*/ ?>
				</ul>
				<div class="text-right btn-vertical btn-group">
					<a class="btn btn-gray-line" href="./?p=<?php echo $p; ?>&bo_table=<?php echo $bo_table; ?>&pm=write">문의하기</a>
				</div>
				<?php
					$qstr = "p=".$p."&bo_table=".$bo_table;
					echo get_paging_new(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page=');
				?>
			</div>
		</div>
	</section>
</section>