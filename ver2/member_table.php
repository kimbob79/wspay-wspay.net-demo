<?php

	include_once("./_common.php");

	$title1 = "가맹점 관리";
	$title2 = "회원 테이블";

	include_once("./_head.php");
	$sql_common = " from g5_device where (1) ";


	if($is_admin) {
		$sql_search = " ";
	} else {
		if($member['mb_level'] == "8") { // 본사
			$sql_search = " and mb_1 = '{$member['mb_id']}'  ";
		} else if($member['mb_level'] == "7") { // 지사
			$sql_search = " and mb_2 = '{$member['mb_id']}'  ";
		} else if($member['mb_level'] == "6") { // 총판
			$sql_search = " and mb_3 = '{$member['mb_id']}'  ";
		} else if($member['mb_level'] == "5") { // 대리점
			$sql_search = " and mb_4 = '{$member['mb_id']}'  ";
		} else if($member['mb_level'] == "4") { //  영업점
			$sql_search = " and mb_5 = '{$member['mb_id']}'  ";
		} else if($member['mb_level'] == "3") { // 가맹점
			$sql_search = " and mb_6 = '{$member['mb_id']}'  ";
		}
	}

	if($membera) {	$sql_search .= " and mb_1 = '$membera' ";	}
	if($memberb) {	$sql_search .= " and mb_2 = '$memberb' ";	}
	if($memberc) {	$sql_search .= " and mb_3 = '$memberc' ";	}
	if($memberd) {	$sql_search .= " and mb_4 = '$memberd' ";	}
	if($membere) {	$sql_search .= " and mb_5 = '$membere' ";	}

	/*
	if($l2) { $sql_search .= " and mb_pid2 = '{$l2}' "; }
	if($l3) { $sql_search .= " and mb_pid3 = '{$l3}' "; }
	if($l4) { $sql_search .= " and mb_pid4 = '{$l4}' "; }
	if($l5) { $sql_search .= " and mb_pid5 = '{$l5}' "; }
	if($l6) { $sql_search .= " and mb_pid6 = '{$l6}' "; }
	if($l7) { $sql_search .= " and mb_pid7 = '{$l7}' "; }

	/*
	if ($is_admin != 'super')
		$sql_search .= " and (gr_admin = '{$member['mb_id']}') ";
	*/

	if ($stx) {
		$sql_search .= " and ( ";
		switch ($sfl) {
			case "gr_id" :
			case "gr_admin" :
				$sql_search .= " ({$sfl} = '{$stx}') ";
				break;
			default :
				$sql_search .= " ({$sfl} like '%{$stx}%') ";
				break;
		}
		$sql_search .= " ) ";
	}

	if ($sst)
		$sql_order = " order by {$sst} {$sod} ";
	else
		$sql_order = " order by mb_1, mb_2, mb_3, mb_4, mb_5, mb_6 asc ";


	$sql = " select * {$sql_common} {$sql_search} {$sql_order} ";
	$result = sql_query($sql);

	//	echo $sql."<br>";
	//	echo $member['mb_id']."<br>";
	//	echo $member['mb_type']."<br>";

	//	echo $total_pay;

?>

<style>
.table td, .table th {padding:1em; text-align:left;}
table td div {line-height:30px; border-bottom:1px solid #ddd; padding:0 8px; text-align:left;}
table td .first {color:#fff;font-weight:700;}
table td div.last {border-bottom:0; color:red;font-weight:700;}
table td div.buttons {border-top:1px solid #ddd;border-bottom:0;padding:0.5em}
.tbl_head01 tbody td {padding:0.5em;vertical-align: top; min-width:210px}
select { width:100px; }
</style>

<div class="KDC_Content__root__tP6yD KDC_Content__responsive__4kCaB CWLNB">
	<div class="KDC_Tabs__root__JmfCY PeriodButtonGroup_root__UE4Kj">
		<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
			<input type="hidden" name="p" value="<?php echo $p; ?>">

			<?php if($is_admin) { ?>
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date" style="width:100px;">
			<select name="membera" id="membera" style="border:0;background:#fff;width:90%;">
				<option value="">본사선택</option>
				<?php
					$sql_g = " select * from g5_member where mb_level = '8' order by mb_nick";
					echo $sql_g;
					$result_g = sql_query($sql_g);
					for ($k=0; $row_g=sql_fetch_array($result_g); $k++) {
					?>
					<option value="<?php echo $row_g['mb_id']; ?>" <?php if($membera == $row_g['mb_id']) { echo "selected"; } ?>><?php echo $row_g['mb_nick']; ?></option>
					<?php
					}
				?>
			</select>
			</div>
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date" style="width:100px;">
			<select name="memberb" id="memberb" style="border:0;background:#fff;width:90%;">
				<option value="">지사선택</option>
				<?php
					$sql_g = " select * from g5_member where mb_level = '7' order by mb_nick";
					$result_g = sql_query($sql_g);
					for ($k=0; $row_g=sql_fetch_array($result_g); $k++) {
					?>
					<option value="<?php echo $row_g['mb_id']; ?>" <?php if($memberb == $row_g['mb_id']) { echo "selected"; } ?>><?php echo $row_g['mb_nick']; ?></option>
					<?php
					}
				?>
			</select>
			</div>
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date" style="width:100px;">
			<select name="memberc" id="memberc" style="border:0;background:#fff;width:90%;">
				<option value="">총판선택</option>
				<?php
					$sql_g = " select * from g5_member where mb_level = '6' order by mb_nick";
					$result_g = sql_query($sql_g);
					for ($k=0; $row_g=sql_fetch_array($result_g); $k++) {
					?>
					<option value="<?php echo $row_g['mb_id']; ?>" <?php if($memberc == $row_g['mb_id']) { echo "selected"; } ?>><?php echo $row_g['mb_nick']; ?></option>
					<?php
					}
				?>
			</select>
			</div>
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date" style="width:100px;">
			<select name="memberd" id="memberd" style="border:0;background:#fff;width:90%;">
				<option value="">대리점선택</option>
				<?php
					$sql_g = " select * from g5_member where mb_level = '5' order by mb_nick";
					$result_g = sql_query($sql_g);
					for ($k=0; $row_g=sql_fetch_array($result_g); $k++) {
					?>
					<option value="<?php echo $row_g['mb_id']; ?>" <?php if($memberd == $row_g['mb_id']) { echo "selected"; } ?>><?php echo $row_g['mb_nick']; ?></option>
					<?php
					}
				?>
			</select>
			</div>
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date" style="width:100px;">
			<select name="membere" id="membere" style="border:0;background:#fff;width:90%;">
				<option value="">영업점선택</option>
				<?php
					$sql_g = " select * from g5_member where mb_level = '4' order by mb_nick";
					$result_g = sql_query($sql_g);
					for ($k=0; $row_g=sql_fetch_array($result_g); $k++) {
					?>
					<option value="<?php echo $row_g['mb_id']; ?>" <?php if($membere == $row_g['mb_id']) { echo "selected"; } ?>><?php echo $row_g['mb_nick']; ?></option>
					<?php
					}
				?>
			</select>
			</div>
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date" style="width:100px;">
			<select name="memberf" id="memberf" style="border:0;background:#fff;width:90%;">
				<option value="">가맹점선택</option>
				<?php
					$sql_g = " select * from g5_member where mb_level = '3' order by mb_nick";
					$result_g = sql_query($sql_g);
					for ($k=0; $row_g=sql_fetch_array($result_g); $k++) {
					?>
					<option value="<?php echo $row_g['mb_id']; ?>" <?php if($memberf == $row_g['mb_id']) { echo "selected"; } ?>><?php echo $row_g['mb_nick']; ?></option>
					<?php
					}
				?>
			</select>
			</div>
			<?php } ?>
		</form>
	</div>



	<div class="KDC_Row__root__uio5h KDC_Row__responsive__obNwV">
		<div class="KDC_Column__root__NK8XY KDC_Column__flex_1__UcocY">
			<div class="KDC_Section__root__VXHOv">
				<table class="KDC_Table__root__Jim4z">
				<thead>
	<tr>
		<?php
			if($member['mb_level'] >= 8) {
		?>
		<th style="width:16.6%; text-align:center;">본사</th>
		<?php
			}
			if($member['mb_level'] >= 7) {
		?>
		<th style="width:16.6%; text-align:center;">지사</th>
		<?php
			}
			if($member['mb_level'] >= 6) {
		?>
		<th style="width:16.6%; text-align:center;">총판</th>
		<?php
			}
			if($member['mb_level'] >= 5) {
		?>
		<th style="width:16.6%; text-align:center;">대리점</th>
		<?php
			}
			if($member['mb_level'] >= 4) {
		?>
		<th style="width:16.6%; text-align:center;">영업점</th>
		<?php
			}
			if($member['mb_level'] >= 3) {
		?>
		<th style="width:16.6%; text-align:center;">가맹점</th>
		<?php
			}
		?>
	</tr>
	</thead>
	<tbody>
	<?php
		for ($i=0; $row=sql_fetch_array($result); $i++) {
		/*
//							$num = number_format($total_count - ($page - 1) * $rows - $i);

		if($row['mb_type'] == "1") {
			$mb_type = "관리";
		} else if($row['mb_type'] == "2") {
			$mb_type = "본사";
		} else if($row['mb_type'] == "3") {
			$mb_type = "지사";
		} else if($row['mb_type'] == "4") {
			$mb_type = "총판";
		} else if($row['mb_type'] == "5") {
			$mb_type = "대리점";
		} else if($row['mb_type'] == "6") {
			$mb_type = "영업점";
		} else if($row['mb_type'] == "7") {
			$mb_type = "영업점2";
		} else if($row['mb_type'] == "8") {
			$mb_type = "가맹점";
		} else {
			$mb_type = "-";
		}
		*/
	?>
	<tr>
		<?php
			if($member['mb_level'] >= 8) {
		?>
		<td>

			<?php
				if($row['mb_1']) {
					echo "<div class='first' style='background:#8a00d4;'>· ".$row['mb_1_name']."</div>";
					echo "<div>· ".$row['mb_1']."</div>";
					echo "<div class='last'>· ".$row['mb_1_fee']."%</div>";
					if($is_admin) {
						echo "<div class='buttons'>";
						echo "<a href='./?p=login_user&mb_id=".$row['mb_1']."&url=<?php echo $p; ?>' class='KDC_Button__root__N26ep KDC_Button__mini__Sy8ka KDC_Button__color_cancel__TdcOV'>로그인</a>";
						/*
						echo "<a href='./?p=member_view&mb_id=".$row['mb_6']."' target='_blank' class='button is-small is-white'>상세</a>";
						*/
						echo "</div>";
					}
				}
			?>


		</td>
		<?php
			}
			if($member['mb_level'] >= 7) {
		?>
		<td>

			<?php
				if($row['mb_2']) {
					echo "<div class='first' style='background:#e74645'>· ".$row['mb_2_name']."</div>";
					echo "<div>· ".$row['mb_2']."</div>";
					echo "<div class='last'>· ".$row['mb_2_fee']."%</div>";
					if($is_admin) {
						echo "<div class='buttons'>";
						echo "<a href='./?p=login_user&mb_id=".$row['mb_2']."&url=<?php echo $p; ?>' class='KDC_Button__root__N26ep KDC_Button__mini__Sy8ka KDC_Button__color_cancel__TdcOV'>로그인</a>";
						/*
						echo "<a href='./?p=member_view&mb_id=".$row['mb_6']."' target='_blank' class='button is-small is-white'>상세</a>";
						*/
						echo "</div>";
					}
				}
			?>


		</td>
		<?php
			}
			if($member['mb_level'] >= 6) {
		?>
		<td>

			<?php
				if($row['mb_3']) {
					echo "<div class='first' style='background:#454d66'>· ".$row['mb_3_name']."</div>";
					echo "<div>· ".$row['mb_3']."</div>";
					echo "<div class='last'>· ".$row['mb_3_fee']."%</div>";
					if($is_admin) {
						echo "<div class='buttons'>";
						echo "<a href='./?p=login_user&mb_id=".$row['mb_3']."&url=<?php echo $p; ?>' class='KDC_Button__root__N26ep KDC_Button__mini__Sy8ka KDC_Button__color_cancel__TdcOV'>로그인</a>";
						/*
						echo "<a href='./?p=member_view&mb_id=".$row['mb_6']."' target='_blank' class='button is-small is-white'>상세</a>";
						*/
						echo "</div>";
					}
				}
			?>


		</td>
		<?php
			}
			if($member['mb_level'] >= 5) {
		?>
		<td>

			<?php
				if($row['mb_4']) {
					echo "<div class='first' style='background:#072448'>· ".$row['mb_4_name']."</div>";
					echo "<div>· ".$row['mb_4']."</div>";
					echo "<div class='last'>· ".$row['mb_4_fee']."%</div>";
					if($is_admin) {
						echo "<div class='buttons'>";
						echo "<a href='./?p=login_user&mb_id=".$row['mb_4']."&url=<?php echo $p; ?>' class='KDC_Button__root__N26ep KDC_Button__mini__Sy8ka KDC_Button__color_cancel__TdcOV'>로그인</a>";
						/*
						echo "<a href='./?p=member_view&mb_id=".$row['mb_6']."' target='_blank' class='button is-small is-white'>상세</a>";
						*/
						echo "</div>";
					}
				}
			?>


		</td>
		<?php
			}
			if($member['mb_level'] >= 4) {
		?>
		<td>

			<?php
				if($row['mb_5']) {
					echo "<div class='first' style='background:#122c91'>· ".$row['mb_5_name']."</div>";
					echo "<div>· ".$row['mb_5']."</div>";
					echo "<div class='last'>· ".$row['mb_5_fee']."%</div>";
					if($is_admin) {
						echo "<div class='buttons'>";
						echo "<a href='./?p=login_user&mb_id=".$row['mb_5']."&url=<?php echo $p; ?>' class='KDC_Button__root__N26ep KDC_Button__mini__Sy8ka KDC_Button__color_cancel__TdcOV'>로그인</a>";
						/*
						echo "<a href='./?p=member_view&mb_id=".$row['mb_6']."' target='_blank' class='button is-small is-white'>상세</a>";
						*/
						echo "</div>";
					}
				}
			?>


		</td>
		<?php
			}
			if($member['mb_level'] >= 3) {
		?>
		<td>

			<?php
				if($row['mb_6']) {
					echo "<div class='first' style='background:#000'>· ".$row['mb_6_name']."</div>";
					echo "<div>· ".$row['mb_6']."</div>";
					echo "<div class='last'>· ".$row['mb_6_fee']."%</div>";
					if($is_admin) {
						echo "<div class='buttons'>";
						echo "<a href='./?p=login_user&mb_id=".$row['mb_6']."&url=<?php echo $p; ?>' class='KDC_Button__root__N26ep KDC_Button__mini__Sy8ka KDC_Button__color_cancel__TdcOV'>로그인</a>";
						/*
						echo "<a href='./?p=member_view&mb_id=".$row['mb_6']."' target='_blank' class='button is-small is-white'>상세</a>";
						*/
						echo "</div>";
					}
				}
			?>
		</td>
		<?php
			}
		?>

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
			/*
				$qstr = "p=".$p;
				$qstr .= "&fr_date=".$fr_date;
				$qstr .= "&to_date=".$to_date;
				$qstr .= "&sfl=".$sfl;
				$qstr .= "&stx=".$stx;
				echo get_paging_news(G5_IS_MOBILE ? "5" : "5", $page, $total_page, '?' . $qstr . '&amp;page=');
			*/
			?>
		</div>
	</div>
</div>



<script>
	$("#membera, #memberb, #memberc, #memberd, #membere, #memberf").click(function() {
		$("#membera, #memberb, #memberc, #memberd, #membere, #memberf").find('option:first').attr('selected', 'selected');
	});

	$("#membera, #memberb, #memberc, #memberd, #membere, #memberf").change(function() {
		$("#membera, #memberb, #memberc, #memberd, #membere, #memberf").val();
		$(this).parents().filter("form").submit();
	});
</script>

<?php
	include_once("./_tail.php");
?>