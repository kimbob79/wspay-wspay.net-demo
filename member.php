<?php

	$title1 = "가맹점 관리";

	if($level > $member['mb_level']) {
		alert("접속할 수 없습니다.");
	}

	if($level == "8") {
		$title2 = "본사";
	} else if($level == "7") {
		$title2 = "지사";
	} else if($level == "6") {
		$title2 = "총판";
	} else if($level == "5") {
		$title2 = "대리점";
	} else if($level == "4") {
		$title2 = "영업점";
	} else if($level == "3") {
		$title2 = "가맹점";
	} else if($level == "1") {
		$title2 = "삭제회원";
	}
/*

*/
	if($level == '3') {
		if($member['mb_level'] == 10) {
			if(adm_sql_common) {
				$adm_sql = " a.mb_1 IN (".adm_sql_common.")";
			} else {
				$adm_sql = " (1)";
			}
		} else {
			if($member['mb_level'] >= 7) {
				$adm_sql = " a.mb_2 = '{$member['mb_id']}'";
			} else if($member['mb_level'] >= 6) {
				$adm_sql = " a.mb_3 = '{$member['mb_id']}'";
			} else if($member['mb_level'] >= 5) {
				$adm_sql = "a.mb_4 = '{$member['mb_id']}'";
			} else if($member['mb_level'] >= 4) {
				$adm_sql = " a.mb_5 = '{$member['mb_id']}'";
			} else if($member['mb_level'] >= 3) {
				$adm_sql = " a.mb_6 = '{$member['mb_id']}'";
			}
		}
		$sql_common = " from {$g5['member_table']} a left join g5_device b on a.mb_id = b.mb_6 where ".$adm_sql." and mb_level = '{$level}' ";
	} else {
		if($member['mb_level'] == 10) {
			if(adm_sql_common) {
				$adm_sql = " mb_1 IN (".adm_sql_common.")";
			} else {
				$adm_sql = " (1)";
			}
		} else {
			if($member['mb_level'] >= 7) {
				$adm_sql = " mb_2 = '{$member['mb_id']}'";
			} else if($member['mb_level'] >= 6) {
				$adm_sql = " mb_3 = '{$member['mb_id']}'";
			} else if($member['mb_level'] >= 5) {
				$adm_sql = " mb_4 = '{$member['mb_id']}'";
			} else if($member['mb_level'] >= 4) {
				$adm_sql = " mb_5 = '{$member['mb_id']}'";
			} else if($member['mb_level'] >= 3) {
				$adm_sql = " mb_6 = '{$member['mb_id']}'";
			}
		}
		$sql_common = " from {$g5['member_table']} where ".$adm_sql." and mb_level = '{$level}' ";
	}

	// 상호명 검색
	if($mb_nick) {
		$sql_search .= " and mb_nick like '%{$mb_nick}%' ";
	}

	// 상호명 검색
	if($dv_tid) {
		$sql_search .= " and dv_tid like '{$dv_tid}' ";
	}

	// Keyin 설정 필터
	$keyin_filter = isset($_GET['keyin_filter']) ? $_GET['keyin_filter'] : '';
	if($keyin_filter == 'Y') {
		// Keyin 설정이 있는 가맹점만 (수기결제 허용 + Keyin 설정 있음)
		$sql_search .= " and a.mb_mailling = '1' and a.mb_id IN (SELECT DISTINCT mb_id FROM g5_member_keyin_config WHERE mkc_use = 'Y' AND mkc_status = 'active') ";
	} else if($keyin_filter == 'N') {
		// Keyin 설정이 없는 가맹점만 (수기결제 허용했지만 Keyin 설정 없음)
		$sql_search .= " and a.mb_mailling = '1' and a.mb_id NOT IN (SELECT DISTINCT mb_id FROM g5_member_keyin_config WHERE mkc_use = 'Y' AND mkc_status = 'active') ";
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
	$xlsx_sql = "select * {$sql_common} {$sql_search} {$sql_order} ";
	$result = sql_query($sql);

//	echo $sql;
//	echo $total_pay;

if($member['mb_level']==10){
	//echo $sql;
}

?>

<style>

td span { /*font-family: 'FFF-Reaction-Trial';*/ font-size:11px;}
td span .fee_name {font-family: 'NanumGothic';}

.fee_left {float:left;}
.fee_right {float:right;}

.fee { color:#999}
.fee1 {padding:0 3px 1px; background:#2f409f; color:#fff;}
.fee2 {margin-left:5px; padding:0 3px 1px; background:#888; color:#fff;}
.tid {font-weight:300; color:#999}
select { width:100px; }
.table_title {font-size:13px; margin:0 0 10px 5px; font-weight:700; color:#555}

/* 헤더 스타일 */
.member-header {
	background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
	border-radius: 8px;
	padding: 12px 16px;
	margin-bottom: 10px;
	box-shadow: 0 2px 8px rgba(76, 175, 80, 0.2);
}
.member-title {
	color: #fff;
	font-size: 16px;
	font-weight: 600;
	display: flex;
	align-items: center;
	gap: 8px;
}
.member-title i {
	font-size: 14px;
	opacity: 0.8;
}
/* 검색 영역 */
.member-search {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 10px 12px;
	margin-bottom: 10px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.member-search-row {
	display: flex;
	align-items: center;
	flex-wrap: wrap;
	gap: 8px;
}
.search-input-group {
	display: flex;
	align-items: center;
	gap: 4px;
}
.search-input-group input[type="text"] {
	width: 100px;
	height: 32px;
	padding: 0 8px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-size: 13px;
	background: #f8f9fa;
	box-sizing: border-box;
}
.search-input-group input[type="text"]:focus {
	outline: none;
	border-color: #4caf50;
	background: #fff;
}
.btn-search {
	height: 32px;
	padding: 0 12px;
	background: #4caf50;
	color: #fff;
	border: none;
	border-radius: 4px;
	font-size: 12px;
	cursor: pointer;
	transition: background 0.15s;
	box-sizing: border-box;
}
.btn-search:hover {
	background: #66bb6a;
}
.btn-excel {
	height: 32px;
	padding: 0 10px;
	background: #2e7d32;
	color: #fff;
	border: none;
	border-radius: 4px;
	font-size: 12px;
	cursor: pointer;
	transition: background 0.15s;
	box-sizing: border-box;
}
.btn-excel:hover {
	background: #388e3c;
}
/* Keyin 필터 셀렉트 */
.keyin-filter-select {
	height: 32px;
	padding: 0 8px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-size: 13px;
	background: #f8f9fa;
	min-width: 100px;
	box-sizing: border-box;
}
.keyin-filter-select:focus {
	outline: none;
	border-color: #4caf50;
	background: #fff;
}
@media (max-width: 768px) {
	.member-search-row {
		flex-direction: column;
		align-items: flex-start;
	}
}
</style>

<div class="member-header">
	<div class="member-title">
		<i class="fa fa-user-circle"></i>
		<?php echo $title2; ?> 관리
	</div>
</div>

<form id="fsearch" name="fsearch" method="get">
<input type="hidden" name="p" value="<?php echo $p; ?>">
<input type="hidden" name="level" value="<?php echo $level; ?>">
<div class="member-search">
	<div class="member-search-row">
		<!-- 검색 필드 -->
		<div class="search-input-group">
			<input type="text" name="mb_nick" value="<?php echo $mb_nick ?>" id="mb_nick" placeholder="상호명">
			<?php if($level == '3') { ?>
			<input type="text" name="dv_tid" value="<?php echo $dv_tid ?>" id="dv_tid" placeholder="TID">
			<?php if($is_admin) { ?>
			<select name="keyin_filter" class="keyin-filter-select">
				<option value="">Keyin 전체</option>
				<option value="Y" <?php if($keyin_filter == 'Y') echo 'selected'; ?>>수기허용+설정있음</option>
				<option value="N" <?php if($keyin_filter == 'N') echo 'selected'; ?>>수기허용+설정없음</option>
			</select>
			<?php } ?>
			<?php } ?>
			<button type="submit" class="btn-search">검색</button>
			<?php if($is_admin) { ?>
			<button type="button" class="btn-excel" id="xlsx"><i class="fa fa-file-excel-o"></i> 엑셀</button>
			<?php } ?>
		</div>
	</div>
</div>
</form>





<form action="./xlsx/member.php" id="frm_xlsx" method="post">
<input type="hidden" name="xlsx_sql" value="<?php echo $xlsx_sql; ?>">
<input type="hidden" name="p" value="<?php echo $p; ?>">
</form>

<div class="m_board_scroll">
	<div class="m_table_wrap">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
				<tr>
					<th style="width:50px">번호</th>
					<?php if($is_admin) { ?>
					<?php if($level == '3') { ?>
					<th>TID등록</th>
					<th style="text-align:center;">관리</th>
					<th>Keyin설정</th>
					<?php } ?>
					<?php } ?>
					<th>그룹</th>
					<?php if($is_admin && $level == '3') { ?>
					<th>구분</th>
					<?php } ?>
					<?php if($level == '3') { ?>
					<th>수기</th>
					<?php } ?>
					<th>아이디</th>
					<th>상호명</th>
					<th>수수료</th>
					<th>정산주기</th>
					<?php if($level == '3') { ?>
					<th>TID</th>
					<?php } ?>
					<th>사업자등록번호</th>
					<th>대표자명</th>
					<th>전화번호</th>
					<th>휴대전화번호</th>
					<th>이메일</th>
					<th>주소</th>
					<th>은행</th>
					<th>계좌</th>
					<th>예금주</th>
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


					if($row['mb_level'] == "8") {
						$title_s = "본사";
					} else if($row['mb_level'] == "7") {
						$title_s = "지사";
					} else if($row['mb_level'] == "6") {
						$title_s = "총판";
					} else if($row['mb_level'] == "5") {
						$title_s = "대리점";
					} else if($row['mb_level'] == "4") {
						$title_s = "영업점";
					} else if($row['mb_level'] == "3") {
						$title_s = "가맹점";
					} else if($row['mb_level'] == "1") { // 삭제회원

						if($row['mb_sex'] == "8") {
							$title_s = "본사";
						} else if($row['mb_sex'] == "7") {
							$title_s = "지사";
						} else if($row['mb_sex'] == "6") {
							$title_s = "총판";
						} else if($row['mb_sex'] == "5") {
							$title_s = "대리점";
						} else if($row['mb_sex'] == "4") {
							$title_s = "영업점";
						} else if($row['mb_sex'] == "3") {
							$title_s = "가맹점";
						}

					}
					$mb_mailling = "";
					if($row['mb_mailling'] == 1) {
						$mb_mailling = "<div  class='btn_b btn_b06' style='font-size:11px;'>수기</span>";
					}
				?>
				<tr <?php if($p != "memberc") { ?><?php if($dview == "1") { ?>style="background:#f9f9f9;"<?php } ?><?php } ?>>
					<td><?php echo $num; ?></td>
					<?php if($is_admin) { ?>
					<?php if($level == '3') { ?>
					<td><a href="./?p=member_device&mb_id=<?php echo $row['mb_id']; ?>&level=<?php echo $level; ?>&mb_nick=<?php echo $mb_nick; ?>&dv_tid=<?php echo $dv_tid; ?>&page=<?php echo $page; ?>" class="btn_b btn_b02">TID 등록</a></td>
					<?php } ?>
					<td class="is-actions-cell">
						<div class="buttons">
							<?php if($is_admin) { ?>
							<?php /*
							<?php if($member['mb_type'] < 3) { ?>
							<a href="./?p=profile&action=edit&mb_id=<?php echo $row['mb_id']; ?>" class="button is-small is-info">수정</a>
							<?php } ?>
							*/ ?>
							<a href="./?p=member_form&mb_id=<?php echo $row['mb_id']; ?>&mb_level=<?php echo $row['mb_level'];?>&w=u" class="btn_b btn_b02">수정</a>
							<?php } ?>
							<a href="./?p=login_user&mb_id=<?php echo $row['mb_id']; ?>" class="btn_b btn_b03">로그인</a>
							<?php
								//if($level == "3") {
								//$mb_7 = preg_replace('/[^0-9]*/s', '', $row['mb_7']);
								/*
								if(strlen($mb_7) == 10) {
							?>
							<a href="../sftp_mainpay/send.php?mb_id=<?php echo $row['mb_id']; ?>" class="btn_b btn_b02" target="_blank">S9 등록</a>
							<?php } else { ?>
							<a href="#" onclick="alert('사업자등록번호 없음');" class="btn_b btn_b06">S9 등록</a>
							<?php
									}
								}
								*/
							?>
						</div>
					</td>
					<td>
						<?php if($row['mb_mailling'] == 1) { ?>
						<?php
						// Keyin 설정 개수 조회
						$keyin_count_row = sql_fetch("SELECT COUNT(*) as cnt FROM g5_member_keyin_config WHERE mb_id = '{$row['mb_id']}' AND mkc_use = 'Y' AND mkc_status = 'active'");
						$keyin_count = $keyin_count_row['cnt'];
						?>
						<a href="./?p=member_keyin_config&mb_id=<?php echo $row['mb_id']; ?>&level=<?php echo $level; ?>&mb_nick=<?php echo $mb_nick; ?>&dv_tid=<?php echo $dv_tid; ?>&page=<?php echo $page; ?>" class="btn_b <?php echo $keyin_count > 0 ? 'btn_b01' : 'btn_b04'; ?>">
							Keyin<?php if($keyin_count > 0) echo " ({$keyin_count})"; ?>
						</a>
						<?php } else { ?>
						<span style="color:#999; font-size:11px;">-</span>
						<?php } ?>
					</td>
					<?php } ?>
					<td><?php echo $title_s; ?></td>
					<?php if($is_admin && $level == '3') { ?>
					<td style="text-align:center; font-weight:bold; color:<?php echo $row['mb_settle_gbn'] == 'Y' ? '#4caf50' : '#f44336'; ?>">
						<?php echo $row['mb_settle_gbn'] == 'Y' ? 'O' : 'X'; ?>
					</td>
					<?php } ?>
					<?php if($level == '3') { ?>
					<td><?php echo $mb_mailling; ?></td>
					<?php } ?>
					<td><?php echo $row['mb_id']; ?></td>
					<td class="td_name"><?php if($is_admin) { ?><span class="simptip-position-bottom simptip-movable half-arrow simptip-multiline simptip-black" data-tooltip="본　사 : <?php if($row['mb_1_name']) { echo $row['mb_1_name']. " / ".$row['mb_1_fee']; } ?>&#10;지　사 : <?php if($row['mb_2_name']) { echo $row['mb_2_name']. " / ".$row['mb_2_fee']; } ?>&#10;총　판 : <?php if($row['mb_3_name']) { echo $row['mb_3_name']. " / ".$row['mb_3_fee']; } ?>&#10;대리점 : <?php if($row['mb_4_name']) { echo $row['mb_4_name']. " / ".$row['mb_4_fee']; } ?>&#10;영업점 : <?php if($row['mb_5_name']) { echo $row['mb_5_name']. " / ".$row['mb_5_fee']; } ?>"><?php } ?><?php echo $row['mb_nick']; ?><?php if($is_admin) { ?></span><?php } ?></td>
					<td><span class="fee1"><?php echo sprintf('%0.3f', $row['mb_homepage']); ?>%</span></td>
					<td><?php if($row['mb_dupinfo']) { echo "D+".$row['mb_dupinfo']; } ?></td>
					<?php if($level == '3') { ?>
					<td><?php echo $row['dv_tid']; ?></td>
					<?php } ?>
					<td><?php if($row['mb_7'] != "--") { echo $row['mb_7']; } ?></td>
					<td><?php echo $row['mb_name']; ?></td>
					<td><strong><?php if($row['mb_tel'] != "--") { echo $row['mb_tel']; } ?></strong></td>
					<td><?php if($row['mb_hp'] != "010--") { echo $row['mb_hp']; } ?></td>
					<td><?php echo $row['mb_email']; ?></td>
					<td style="text-align:left"><?php echo $row['mb_zip1'].$row['mb_zip2']; ?> <?php echo $row['mb_addr1']; ?> <?php echo $row['mb_addr2']; ?></td>
					<td style="text-align:left"><?php echo $row['mb_8']; ?></td>
					<td style="text-align:left"><?php echo $row['mb_9']; ?></td>
					<td style="text-align:left"><?php echo $row['mb_10']; ?></td>

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

							$download_url = $file[$k]['href'];
							$direct_url = isset($file[$k]['direct_url']) ? $file[$k]['direct_url'] : '';
							$ext = substr(strrchr($file[$k]['file'], '.'), 1);

							if($ext == "gif" || $ext == "jpeg" || $ext == "jpg" || $ext == "png") {
								$files = '<img src="'.$download_url.'&inline=1" style="max-width:50px; max-height:50px; cursor:pointer; border-radius:4px; object-fit:cover;" onclick="openImageModal(\''.$download_url.'&inline=1\')" title="클릭하여 크게 보기">';
							} else if($ext == "pdf") {
								$files = '<a href="'.$download_url.'" target="_blank"><i style="color:blue" class="fa fa-file"></i></a>';
							} else if($ext == "xls" || $ext == "xlsx") {
								$files = '<a href="'.$download_url.'" target="_blank"><i style="color:blue" class="fa fa-file"></i></a>';
							} else if($ext == "doc" || $ext == "docx") {
								$files = '<a href="'.$download_url.'" target="_blank"><i style="color:blue" class="fa fa-file"></i></a>';
							} else if($ext == "ppt" || $ext == "pptx") {
								$files = '<a href="'.$download_url.'" target="_blank"><i style="color:blue" class="fa fa-file"></i></a>';
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
							<?php if($is_admin) { ?>
							<?php /*
							<?php if($member['mb_type'] < 3) { ?>
							<a href="./?p=profile&action=edit&mb_id=<?php echo $row['mb_id']; ?>" class="button is-small is-info">수정</a>
							<?php } ?>
							*/ ?>
							<a href="./?p=member_form&mb_id=<?php echo $row['mb_id']; ?>&mb_level=<?php echo $row['mb_level'];?>&w=u" class="btn_b btn_b02">수정</a>
							<a href="./?p=member_delete&mb_id=<?php echo $row['mb_id']; ?>&level=<?php echo $level; ?>&sfl=<?php echo $sfl; ?>&stx=<?php echo $stx; ?>&page=<?php echo $page; ?>" class="btn_b btn_b06" onclick="return confirm('정말 <?php echo $row['mb_nick']; ?>님을 <?php if($level == "1") { echo "복구"; } else { echo "삭제"; } ?> 하시겠습니까');"><?php if($level == "1") { echo "복구"; } else { echo "삭제"; } ?></a>
							<?php } ?>
							<a href="./?p=login_user&mb_id=<?php echo $row['mb_id']; ?>" class="btn_b btn_b03">로그인</a>
							<?php
								//if($level == "3") {
								//$mb_7 = preg_replace('/[^0-9]*/s', '', $row['mb_7']);
								/*
								if(strlen($mb_7) == 10) {
							?>
							<a href="../sftp_mainpay/send.php?mb_id=<?php echo $row['mb_id']; ?>" class="btn_b btn_b02" target="_blank">S9 등록</a>
							<?php } else { ?>
							<a href="#" onclick="alert('사업자등록번호 없음');" class="btn_b btn_b06">S9 등록</a>
							<?php
									}
								}
								*/
							?>
						</div>
					</td>
				</tr>
				<?php
					}
				?>
			</tbody>
		</table>
	</div>
</div>
<?php
// 등록 버튼 권한 체크:
// - 레벨 7 이상만 등록 가능 (member_form.php에서 레벨 7 미만 차단)
// - 관리자(레벨10)는 모든 레벨 등록 가능
// - 비관리자는 자신의 레벨보다 낮은 레벨만 등록 가능
if($member['mb_level'] >= 7 && ($is_admin || $member['mb_level'] > $level)) {
?>
<div style="padding:10px 0;">
	<a href="./?p=member_form&mb_level=<?php echo $level; ?>&page=<?php echo $page; ?>" class="btn_b btn_b01"><?php echo  $title2; ?> 등록</a>
</div>
<?php } ?>

<?php
	//http://cajung.com/new4/?p=payment&fr_date=20220906&to_date=20220906&sfl=mb_id&stx=
	$qstr = "p=".$p;
	$qstr .= "&level=".$level;
	$qstr .= "&mb_nick=".$mb_nick;
	$qstr .= "&dv_tid=".$dv_tid;
	$qstr .= "&keyin_filter=".$keyin_filter;
	echo get_paging_news(G5_IS_MOBILE ? "5" : "5", $page, $total_page, '?' . $qstr . '&amp;page=');
?>

<script>
// 엑셀 다운로드
$("#xlsx").on("click", function() {
	$("#frm_xlsx").submit();
});
</script>

<!-- 이미지 모달 -->
<div id="imageModal" onclick="closeImageModal()" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.9); cursor:pointer;">
	<span style="position:absolute; top:15px; right:25px; color:#fff; font-size:35px; font-weight:bold; cursor:pointer; z-index:10000;">&times;</span>
	<div style="display:flex; align-items:center; justify-content:center; height:100%; padding:40px;">
		<img id="modalImage" src="" onclick="event.stopPropagation()" style="max-width:80%; max-height:80vh; object-fit:contain; border-radius:8px; box-shadow:0 4px 20px rgba(0,0,0,0.5); cursor:default;">
	</div>
</div>

<script>
function openImageModal(imageSrc) {
	document.getElementById('imageModal').style.display = 'block';
	document.getElementById('modalImage').src = imageSrc;
	document.body.style.overflow = 'hidden';
}

function closeImageModal() {
	document.getElementById('imageModal').style.display = 'none';
	document.getElementById('modalImage').src = '';
	document.body.style.overflow = 'auto';
}

// ESC 키로 모달 닫기
document.addEventListener('keydown', function(event) {
	if (event.key === 'Escape') {
		closeImageModal();
	}
});
</script>