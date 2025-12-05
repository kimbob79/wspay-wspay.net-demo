<?php



$sql_common = " from g5_write_notice ";

$sql_search = " where (1) ";
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
    $sst = "wr_id";
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


$listall = '<a href="' . $_SERVER['SCRIPT_NAME'] . '" class="ov_listall">전체목록</a>';


$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

echo $sql;



?>
<section class="container" id="bbs">
	<section class="contents contents-bbs">
		<div class="top">
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
								<select class="form-control" name="s_select" id="s_select">
									<option value="s_keyword">제목+내용</option>
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
		</div>
		<div class="bbs-cont">
			<div class="inner">
				<ul class="cont-wrap list-area">


					<?php
						for ($i = 0; $row = sql_fetch_array($result); $i++) {
							$num = number_format($total_count - ($page - 1) * $config['cf_page_rows'] - $i);
					?>


					<li class="item-info">
						<a class="view-link" data-idx="58848" data-desecret="1">
							<div class="list-tit">
								<div class="left-box">
									<?php
										if ($row['is_notice']) {
											echo '<span class="label-black">공지</span>';
										} else if ($wr_id == $row['wr_id']) {
											echo "열람중";
										} else {
											echo $num;
										}
									?>
								</div>
								<div class="right-box ask-box">
									<p class="tit"><span class="admin-name">[<?php echo $row['ca_name']; ?>]</span>신한 슈퍼SOL 패키지명 추가 안내</p>
									<div class="txt">
										<div class="pull-right">
											<?php /*
											<span class="q-name text-blue">답변완료</span>
											<span class="user">고객센터</span>
											*/ ?>
											<span class="num date"><?php echo $row['wr_datetime']; ?></span>
										</div>
									</div>
									
								</div>
							</div>
						</a>
					</li>


					<?php
					}
					if ($i == 0) {
						echo "<tr><td colspan=\"" . $colspan . "\" class=\"empty_table\">자료가 없습니다.</td></tr>";
					}
					?>

				</ul>
				<?php /*
				<div class="text-right btn-vertical btn-group">
					<a class="btn btn-gray-line" href="https://www.payapp.kr/homepage/bbs/bbs_ask_write.html">문의하기</a>
				</div>
				*/ ?>
				
				<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>
				<?php /*
				<div class="paging">
					<ol>
						<li class="active"><a href="/homepage/bbs/bbs_ask.html?PageNo=1&amp;s_select=&amp;s_search=">1</a></li>
						<li><a href="/homepage/bbs/bbs_ask.html?PageNo=2&amp;s_select=&amp;s_search=">2</a></li>
						<li><a href="/homepage/bbs/bbs_ask.html?PageNo=3&amp;s_select=&amp;s_search=">3</a></li>
						<li><a href="/homepage/bbs/bbs_ask.html?PageNo=4&amp;s_select=&amp;s_search=">4</a></li>
						<li><a href="/homepage/bbs/bbs_ask.html?PageNo=5&amp;s_select=&amp;s_search=">5</a></li>
					</ol>
					<a href="/homepage/bbs/bbs_ask.html?PageNo=6&amp;s_select=&amp;s_search=" class="btn-paging"><i class="ico ico-next"></i></a>
					<a href="/homepage/bbs/bbs_ask.html?PageNo=404&amp;s_select=&amp;s_search=" class="btn-paging"><i class="ico ico-last"></i></a>
				</div>
				*/ ?>

			</div>
		</div>
	</section>
</section>