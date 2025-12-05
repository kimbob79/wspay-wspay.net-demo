<?php

	include_once("./_common.php");

	$title1 = "가맹점 관리";
	if($level == "8") { $title2 = "본사 관리";
	} else if($level == "7") { $title2 = "지사 관리";
	} else if($level == "6") { $title2 = "총판 관리";
	} else if($level == "5") { $title2 = "대리점 관리";
	} else if($level == "4") { $title2 = "영업점 관리";
	} else if($level == "3") { $title2 = "가맹점 관리";
	}

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

	$sql_common = " from {$g5['member_table']} where ".$adm_sql." and mb_level = '{$level}' ";

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
//	echo $total_pay;

?>


<div class="KDC_Content__root__tP6yD KDC_Content__responsive__4kCaB CWLNB">

	<div class="KDC_Tabs__root__JmfCY PeriodButtonGroup_root__UE4Kj">
		<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
			<input type="hidden" name="p" value="<?php echo $p; ?>">
			<input type="hidden" name="level" value="<?php echo $level; ?>">

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
					<th style="width:50px">번호</th>
					<th>아이디</th>
					<th>상호명</th>
					<th>수수료</th>
					<th>사업자등록번호</th>
					<th>대표자명</th>
					<th>전화번호</th>
					<th>휴대전화번호</th>
					<th>이메일</th>
					<th>주소</th>
					<th>계좌정보</th>
					<th>주</th>
					<th>사</th>
					<th>통</th>
					<th>계</th>
					<th>F1</th>
					<th>F2</th>
					<th style="text-align:center;">관리</th>
				</tr>
				</thead>
				<tbody>
				<?php
					for ($i=0; $row=sql_fetch_array($result); $i++) {
					$num = number_format($total_count - ($page - 1) * $rows - $i);

					if($row['mb_type'] == "1") {
						$mb_type = "1";
					} else if($row['mb_type'] == "2") {
						$mb_type = "2";
					} else if($row['mb_type'] == "3") {
						$mb_type = "3";
					} else if($row['mb_type'] == "4") {
						$mb_type = "";
					} else if($row['mb_type'] == "5") {
						$mb_type = "대리";
					} else if($row['mb_type'] == "6") {
						$mb_type = "영업";
					} else if($row['mb_type'] == "7") {
						$mb_type = "가맹";
					} else {
						$mb_type = "-";
					}

					if($row['mb_app'] == "1") {
						$mb_app = "승인";
					} else {
						$mb_app = "미승인";
					}

					if($row['mb_sugi'] == "anyil") {
						$mb_sugi = "애이닐";
					} else {
						$mb_sugi = "-";
					}
				?>
				<tr <?php if($p != "memberc") { ?><?php if($dview == "1") { ?>style="background:#f9f9f9;"<?php } ?><?php } ?>>
					<td><?php echo $num; ?></td>
					<td><?php echo $row['mb_id']; ?></td>
					<td class="td_name"><?php echo $row['mb_nick']; ?></td>
					<td><?php echo sprintf('%0.2f', $row['mb_homepage']); ?>%</td>
					<td><?php echo $row['mb_7']; ?></td>
					<td><?php echo $row['mb_name']; ?></td>
					<td><strong><?php echo $row['mb_tel']; ?></strong></td>
					<td><?php echo $row['mb_hp']; ?></td>
					<td><?php echo $row['mb_email']; ?></td>
					<td><?php echo $row['mb_zip1'].$row['mb_zip2']; ?> <?php echo $row['mb_addr1']; ?> <?php echo $row['mb_addr2']; ?></td>
					<td><?php echo $row['mb_8']; ?> <?php echo $row['mb_9']; ?> <?php echo $row['mb_10']; ?></td>

					<?php
						$file = get_member_file($row['mb_id']);
					//echo "<pre>";
					//var_dump($file);
					//echo "</pre>";
						for($k=0; $k<=5; $k++) {

							if($k == 0) {
								$file_title = "주민등록증 사본 ";
							} else if($k == 1) {
								$file_title = "사업자등록증 사본 ";
							} else if($k == 2) {
								$file_title = "통장 사본 ";
							} else if($k == 3) {
								$file_title = "계약서 사본 ";
							} else if($k == 4) {
								$file_title = "기타 첨부파일1 ";
							} else if($k == 5) {
								$file_title = "기타 첨부파일2 ";
							}
			//									echo $file[$k]['file'];

							if($file[$k]['image_type'] == "255") {
								$paths = G5_DATA_URL."/member/_";
							} else {
								$paths = G5_DATA_URL."/member/".$row['mb_id'];
							}

							$file_url = $paths."/".$file[$k]['file'];
							//echo $file[$k]['file']."a<br>";
							$ext = substr(strrchr($file_url, '.'), 1);

							if($ext == "gif" || $ext == "jpeg" || $ext == "jpg" || $ext == "png") {
								$files = '<a href="'.$file_url.'" target="_blank"><i style="color:blue" class="fa fa-file"></i></a>';
							} else if($ext == "pdf") {
								$files = '<a href="http://docs.google.com/gview?url='.$file_url.'" target="_blank"><i style="color:blue" class="fa fa-file"></i></a>';
							} else if($ext == "xls" || $ext == "xlsx") {
								$files = '<a href="http://docs.google.com/gview?url='.$file_url.'" target="_blank"><i style="color:blue" class="fa fa-file"></i></a>';
							} else if($ext == "doc" || $ext == "docx") {
								$files = '<a href="http://docs.google.com/gview?url='.$file_url.'" target="_blank"><i style="color:blue" class="fa fa-file"></i></a>';
							} else if($ext == "ppt" || $ext == "pptx") {
								$files = '<a href="http://docs.google.com/gview?url='.$file_url.'" target="_blank"><i style="color:blue" class="fa fa-file"></i></a>';
							} else {
								$files = '<i style="color:#ddd" class="fa fa-file"></i>';
							}
					?>
					<td title="<?php echo $file_title; ?>"><?php echo $files ?></td>
					<?php
						}
					?>


					<td class="is-actions-cell">
						<div class="buttons">
							<?php /*
							<?php if($member['mb_type'] < 3) { ?>
							<a href="./?p=profile&action=edit&mb_id=<?php echo $row['mb_id']; ?>" class="button is-small is-info">수정</a>
							<?php } ?>
							*/ ?>
							<a href="./?p=member_form&mb_id=<?php echo $row['mb_id']; ?>&mb_level=<?php echo $row['mb_level'];?>&w=u" class="KDC_Button__root__N26ep KDC_Button__mini__Sy8ka KDC_Button__color_cancel__TdcOV">상세/수정</a>
							<a href="./?p=login_user&mb_id=<?php echo $row['mb_id']; ?>" class="KDC_Button__root__N26ep KDC_Button__mini__Sy8ka KDC_Button__color_delete__bAeWl">로그인</a>
							<a href="./?p=member_delete&mb_id=<?php echo $row['mb_id']; ?>&pa=<?php echo $p; ?>" class="KDC_Button__root__N26ep KDC_Button__mini__Sy8ka KDC_Button__color_special__CUcY7">삭제</a>
						</div>
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
				$qstr .= "&level=".$level;
				$qstr .= "&mb_nick=".$mb_nick;
				echo get_paging_news(G5_IS_MOBILE ? "5" : "5", $page, $total_page, '?' . $qstr . '&amp;page=');
			?>
		</div>
	</div>
</div>




<?php
	include_once("./_tail.php");
?>