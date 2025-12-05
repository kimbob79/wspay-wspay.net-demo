<?php

	include_once("./_common.php");

	$title1 = "가맹점 관리";
	$title2 = "접속정보";

	include_once("./_head.php");


	if($redpay == "Y") {
		if($member['mb_id'] == "admin") {
			$adm_sql = " mb_1 IN (".adm_sql_common.")";
		} else  {
			$adm_sql = " mb_1 NOT IN (".adm_sql_common.")";
		}
	} else {
		$adm_sql = "(1)";
	}

	if($mb_level) {
		$sql_common = " from {$g5['member_table']} where mb_level = '{$mb_level}' ";
	} else {
		$sql_common = " from {$g5['member_table']} where ".$adm_sql;
	}

	if($type == "2") {
		$title_s = "본사";
	} else if($type == "3") {
		$title_s = "지사";
	} else if($type == "4") {
		$title_s = "총판";
	} else if($type == "5") {
		$title_s = "대리점";
	} else if($type == "6") {
		$title_s = "영업점";
	} else if($type == "7") {
		$title_s = "가맹점";
	} else {
		$app = '0';
	}



	if(!$is_admin) {
	
		if($member['mb_pid2']) {
			$sql_search .= "and mb_pid2 = '{$member['mb_pid2']}' ";
		}
		if($member['mb_pid3']) {
			$sql_search .= " and mb_pid3 = '{$member['mb_pid3']}' ";
		}
		if($member['mb_pid4']) {
			$sql_search .= "  and mb_pid4 = '{$member['mb_pid4']}' ";
		}
		if($member['mb_pid5']) {
			$sql_search .= " and mb_pid5 = '{$member['mb_pid5']}' ";
		}
		if($member['mb_pid6']) {
			$sql_search .= " and mb_pid6 = '{$member['mb_pid6']}' ";
		}
		if($mb_company) {
			$sql_search .= " and mb_company like '%{$mb_company}%' ";
		}
	}


	// 상호명 검색
	if($mb_nick) {
		$sql_search .= " and mb_nick like '%{$mb_nick}%' ";
	}

	if($dv_tid) {
		$sql_search .= " and dv_tid = '{$dv_tid}' ";
	}


	$sql = " select count(*) as cnt {$sql_common} {$sql_search} order by mb_no desc ";
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

	$sql = " select * {$sql_common} {$sql_search} order by mb_no desc limit {$from_record}, {$rows} ";
	$result = sql_query($sql);
//	echo $sql;

?>


<div class="KDC_Content__root__tP6yD KDC_Content__responsive__4kCaB CWLNB">
	<div class="KDC_Tabs__root__JmfCY PeriodButtonGroup_root__UE4Kj">
		<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
			<input type="hidden" name="p" value="<?php echo $p; ?>">


			<div class="KDC_Tab__root__h2hVQ tab type_other  search_br <?php if(!$mb_level && $mb_level < "2") { echo "tab_selected"; } ?>"><button type="submit" <?php if(!$mb_level && $mb_level < "2") { echo "style='color:#fff;'"; } ?> value="0" name="mb_level"><span>전체</span></button></div>
			<div class="KDC_Tab__root__h2hVQ tab type_other <?php if($mb_level == "8") { echo "tab_selected"; } ?>"><button type="submit" <?php if($mb_level == "8") { echo "style='color:#fff;'"; } ?> value="8" name="mb_level"><span>본사</span></button></div>
			<div class="KDC_Tab__root__h2hVQ tab type_other <?php if($mb_level == "7") { echo "tab_selected"; } ?>"><button type="submit" <?php if($mb_level == "7") { echo "style='color:#fff;'"; } ?> value="7" name="mb_level"><span>지사</span></button></div>
			<div class="KDC_Tab__root__h2hVQ tab type_other <?php if($mb_level == "6") { echo "tab_selected"; } ?>"><button type="submit" <?php if($mb_level == "6") { echo "style='color:#fff;'"; } ?> value="6" name="mb_level"><span>총판</span></button></div>
			<div class="KDC_Tab__root__h2hVQ tab type_other <?php if($mb_level == "5") { echo "tab_selected"; } ?>"><button type="submit" <?php if($mb_level == "5") { echo "style='color:#fff;'"; } ?> value="5" name="mb_level"><span>대리점</span></button></div>
			<div class="KDC_Tab__root__h2hVQ tab type_other <?php if($mb_level == "4") { echo "tab_selected"; } ?>"><button type="submit" <?php if($mb_level == "4") { echo "style='color:#fff;'"; } ?> value="4" name="mb_level"><span>영업점</span></button></div>
			<div class="KDC_Tab__root__h2hVQ tab type_other <?php if($mb_level == "3") { echo "tab_selected"; } ?>"><button type="submit" <?php if($mb_level == "3") { echo "style='color:#fff;'"; } ?> value="3" name="mb_level"><span>가맹점</span></button></div>
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date">
				<input type="text" name="mb_nick" value="<?php echo $mb_nick ?>" id="stx" class="KDC_Input__root__3M8Hf KDC_Input__root__3M8Hf_date" style="width:100px;" placeholder="상호명">
			</div>
			<div class="KDC_Tab__root__h2hVQ tab tab_selected type_other"><input type="submit" class="KDC_Tab__text__VzW9X" value="검색" style="background:#444; width:100%; border:0; color:#fff;"></div>
		</form>
	</div>



	<div class="KDC_Row__root__uio5h KDC_Row__responsive__obNwV">
		<div class="KDC_Column__root__NK8XY KDC_Column__flex_1__UcocY">
			<div class="KDC_Section__root__VXHOv">
				<table class="KDC_Table__root__Jim4z">
				<thead>
				<tr>
					<th style="width:50px;">번호</th>
					<?php /*
					<th>상위</th>
					*/ ?>
					<th>상호명</th>
					<th style="text-align:center;">복사</th>
				</tr>
				</thead>
				<tbody>


				<?php
					for ($i=0; $row=sql_fetch_array($result); $i++) {
						$num = number_format($total_count - ($page - 1) * $rows - $i);
						if($row['mb_level'] == 8) {
							$stitle = "본사";
						} else if($row['mb_level'] == 7) {
							$stitle = "지사";
						} else if($row['mb_level'] == 6) {
							$stitle = "총판";
						} else if($row['mb_level'] == 5) {
							$stitle = "대리점";
						} else if($row['mb_level'] == 4) {
							$stitle = "영업점";
						} else if($row['mb_level'] == 3) {
							$stitle = "가맹점";
						}

				?>
				<tr<?php echo $bgcolor?" style='background: $bgcolor;'":'';?>>
					<td><?php echo $num; ?></td>
					<?php /*
					<td class="td_name">
					<?php
						if($row['mb_1']) {
							$mb1 = get_member($row['mb_1']);
							echo "본　사 : <b>".$mb1['mb_nick']."</b>";
						}
						if($row['mb_2']) {
							$mb2 = get_member($row['mb_2']);
							echo "<br>지　사 : <b>".$mb2['mb_nick']."</b>";
						}
						if($row['mb_3']) {
							$mb3 = get_member($row['mb_3']);
							echo "<br>총　판 : <b>".$mb3['mb_nick']."</b>";
						}
						if($row['mb_4']) {
							$mb4 = get_member($row['mb_4']);
							echo "<br>대리점 : <b>".$mb4['mb_nick']."</b>";
						}
						if($row['mb_5']) {
							$mb5 = get_member($row['mb_5']);
							echo "<br>영업점 : <b>".$mb5['mb_nick']."</b>";
						}
					?>
					</td>
					*/ ?>
					<td style="text-align:left">
						<div><span class="etitle"><?php echo $stitle; ?></span></div>
						<div style="color:#4d4dff; font-weight:700; text-align:left"><?php echo $row['mb_nick']; ?></div>
						<div><span class="stitle">아이디 : <?php echo $row['mb_id']; ?></span></div>
						<div><span class="stitle">수수료 : <?php echo sprintf('%0.2f', $row['mb_homepage']); ?>%</span></div>
						<div><span class="stitle">대표자 : <?php echo $row['mb_name']; ?></span></div>
					</td>
					<td>
<textarea style="width:100%; height:120px; border:0; line-height:200%;" readonly id="copy_<?php echo $i; ?>">
- 레드페이 접속정보 -
상호명 : [<?php echo $stitle; ?>] <?php echo $row['mb_nick']; ?>

접속주소 : http://redpay.co.kr/ver2
아이디 : <?php echo $row['mb_id']; ?>

비밀번호 : <?php echo $row['mb_id']; ?></textarea>

			<script>
			$("#copy_<?php echo $i; ?>").click(function() {
			var content = document.getElementById('copy_<?php echo $i; ?>');
			content.select();
			document.execCommand('copy');
			});
			</script>

					</td>

				</tr>
				<?php
					}
				?>
				</tbody>
				</table>
				<?php /*
				<ul class="KDC_Infos__root__ixod2 info_type2 dot">
					<li>네이티브 앱 키: Android, iOS SDK에서 API를 호출할 때 사용합니다.</li>
					<li>JavaScript 키: JavaScript SDK에서 API를 호출할 때 사용합니다.</li>
					<li>REST API 키: REST API를 호출할 때 사용합니다.</li>
					<li>Admin 키: 모든 권한을 갖고 있는 키입니다. 노출이 되지 않도록 주의가 필요합니다.</li>
				</ul>
				*/ ?>
			</div>
			<?php
				$qstr = "p=".$p;
				$qstr .= "&fr_date=".$fr_date;
				$qstr .= "&to_date=".$to_date;
				$qstr .= "&sfl=".$sfl;
				$qstr .= "&stx=".$stx;
				echo get_paging_news(G5_IS_MOBILE ? "5" : "5", $page, $total_page, '?' . $qstr . '&amp;page=');
			?>
		</div>
	</div>
</div>




<?php
	include_once("./_tail.php");
?>