<?php


	$sql_fild = " * ";
	$sql_fild .= ", count(if(pay_type = 'Y', pay_type, null)) as scnt "; // 승인 건수
	$sql_fild .= ", count(if(pay_type != 'Y', pay_type, null)) as ccnt "; // 취소 건수
	$sql_fild .= ", sum(if(pay_type = 'Y', pay, null)) as spay "; // 승인금액
	$sql_fild .= ", sum(if(pay_type != 'Y', pay, null)) as cpay "; // 취소금액
	$sql_fild .= ", sum(pay) as total_pay "; // 합계

	$sql_fild .= ", sum(if(pay_cdatetime = '0000-00-00 00:00:00', mb_1_pay,0)) as mb_1_pay "; // 본사 수수료
	$sql_fild .= ", sum(if(pay_cdatetime = '0000-00-00 00:00:00', mb_2_pay,0)) as mb_2_pay "; // 지사 수수료
	$sql_fild .= ", sum(if(pay_cdatetime = '0000-00-00 00:00:00', mb_3_pay,0)) as mb_3_pay "; // 총판 수수료
	$sql_fild .= ", sum(if(pay_cdatetime = '0000-00-00 00:00:00', mb_4_pay,0)) as mb_4_pay "; // 대리점 수수료
	$sql_fild .= ", sum(if(pay_cdatetime = '0000-00-00 00:00:00', mb_5_pay,0)) as mb_5_pay "; // 영업점 수수료
	$sql_fild .= ", sum(if(pay_cdatetime = '0000-00-00 00:00:00', mb_6_pay,0)) as mb_6_pay "; // 가맹점 정산액



	$sql = "SELECT {$sql_fild}, DATE(pay_datetime) AS date, sum(pay) as pay, DATE_FORMAT(pay_datetime,'%Y-%m-%d') m FROM g5_payment WHERE mb_6 = '{$member['mb_id']}' GROUP BY m";
	$result = sql_query($sql);

	while ($R = sql_fetch_array($result)) {
		$s_pay = $R['spay'];
		$c_pay = $R['cpay'];
		$schedules[] = array(0 => date("n-j", strtotime($R['date'])), 1 => $R['mb_6_pay'], 2 => $s_pay, 3 => $c_pay, 4 => $R['mb_6_fee'], 5 => $R['scnt'], 6 => $R['ccnt']);
	}


?>


<link rel="stylesheet" type="text/css" href="./css/calendar.css"/>


<?php

	if($YYYY =="") {
		$YYYY = date("Y");
	}
	if($MM =="") {
		$MM = date("m");
	}

	if($MM == 13) {
		$MM = 1;
		$YYYY++;
	}
	if($MM == 0) {
		$MM = 12;
		$YYYY--;
	}

	$before = $MM - 1;
	$after = $MM + 1;

	$firstday_weeknum = date("w", mktime(0, 0, 0, $MM, 1, $YYYY)); 
	$lastday = date("t", mktime(0, 0, 0, $MM, 1, $YYYY));

	if($MM == 2) { 
		if(($YYYY % 4) == 0 && ($YYYY % 100) != 0 || ($YYYY % 400) == 0) { $lastday = 29; }
	}
	/*
	$td1 = "<TD width='100' align='center'><font size='2' align='center'><b>";
	$td2 = "</b></font></TD><TD width='100' height='100' align='center'><font size='2' align='center'><b>";
	$td3 = "</b></font></TD>\n";
	*/
?>







<div class="KDC_Content__root__tP6yD KDC_Content__responsive__4kCaB CWLNB">

	<?php /*
	<div class="KDC_Tabs__root__JmfCY PeriodButtonGroup_root__UE4Kj">
		<div class="KDC_Tab__root__h2hVQ tab tab_selected type_other">총결제</div>
		<div class="KDC_Tab__root__h2hVQ tab type_other  mobilecl"><?php echo number_format($total_pay) ?>원</div>
		<div class="KDC_Tab__root__h2hVQ tab tab_selected type_other search_br">승인</div>
		<div class="KDC_Tab__root__h2hVQ tab type_other  mobilecl"><?php echo number_format($total_Y_pay) ?>원</div>
		<div class="KDC_Tab__root__h2hVQ tab tab_selected type_other search_br">취소</div>
		<div class="KDC_Tab__root__h2hVQ tab type_other  mobilecl"><?php echo number_format($total_M_pay) ?>원</div>
	</div>
	<div class="KDC_Tabs__root__JmfCY PeriodButtonGroup_root__UE4Kj">
		<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
			<input type="hidden" name="p" value="<?php echo $p; ?>">
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date" style="width:80px;">
				<select name="sfl" id="sfl" style="border:0;background:#fff;width:90%;">
					<option value="pay_num" <?php echo get_selected($sfl, "pay_num"); ?>>승인번호</option>
					<option value="mb_6_name" <?php echo get_selected($sfl, "mb_6_name"); ?>>가맹점명</option>
					<option value="dv_tid" <?php echo get_selected($sfl, "dv_tid"); ?>>TID</option>
					<option value="pay" <?php echo get_selected($sfl, "pay"); ?>>승인금액</option>
					<option value="pay_card_name" <?php echo get_selected($sfl, "pay_card_name"); ?>>카드사</option>
					<option value="pay_parti" <?php echo get_selected($sfl, "pay_parti"); ?>>할부</option>
				</select>
			</div>
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date">
				<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="KDC_Input__root__3M8Hf KDC_Input__root__3M8Hf_date" style="width:100px;" placeholder="검색어">
			</div>


			<div class="KDC_Tab__root__h2hVQ tab type_other input_date search_br">
				<input type="text" name="fr_date" value="<?php echo $fr_date ?>" id="fr_date" class="KDC_Input__root__3M8Hf KDC_Input__root__3M8Hf_date" style="width:80px;" maxlength="10">
			</div>
			<div class="KDC_Tab__root__h2hVQ tab type_other input_date">
				<input type="text" name="to_date" value="<?php echo $to_date ?>" id="to_date" class="KDC_Input__root__3M8Hf KDC_Input__root__3M8Hf_date" style="width:80px;" maxlength="10">
			</div>


			<div class="KDC_Tab__root__h2hVQ tab type_other  search_br"><div class="KDC_Tab__text__VzW9X" onclick="set_date('오늘');">오늘</div></div>
			<div class="KDC_Tab__root__h2hVQ tab type_other"><div class="KDC_Tab__text__VzW9X" onclick="set_date('어제');">어제</div></div>
			<div class="KDC_Tab__root__h2hVQ tab type_other"><div class="KDC_Tab__text__VzW9X" onclick="set_date('이번주');">이번주</div></div>
			<div class="KDC_Tab__root__h2hVQ tab type_other"><div class="KDC_Tab__text__VzW9X" onclick="set_date('이번달');">이번달</div></div>
			<div class="KDC_Tab__root__h2hVQ tab type_other"><div class="KDC_Tab__text__VzW9X" onclick="set_date('지난주');">지난주</div></div>
			<div class="KDC_Tab__root__h2hVQ tab type_other"><div class="KDC_Tab__text__VzW9X" onclick="set_date('지난달');">지난달</div></div>
			<div class="KDC_Tab__root__h2hVQ tab tab_selected type_other"><input type="submit" class="KDC_Tab__text__VzW9X" value="검색" style="background:#444; width:100%; border:0; color:#fff;"></div>
		</form>
	</div>
	*/ ?>



	<div class="KDC_Row__root__uio5h KDC_Row__responsive__obNwV">
		<div class="KDC_Column__root__NK8XY KDC_Column__flex_1__UcocY">
			<div class="KDC_Section__root__VXHOv VXHOv">
				<!-- 예정공연안내 //-->
				<div class="calendar">
					<p>
						<a href="<?php echo $PHP_SELF; ?>?p=<?php echo $p; ?>&MM=<?php echo $before; ?>&YYYY=<?php echo $YYYY; ?>&sub=<?php echo $sub; ?>" style="font-size:20px; width:50px;"><i class="fa fa-angle-left" aria-hidden="true"></i></a>
						<span class="Ym"><?php echo $YYYY; ?>. <?php echo $MM; ?></span>
						<a href="<?php echo $PHP_SELF; ?>?p=<?php echo $p; ?>&MM=<?php echo $after; ?>&YYYY=<?php echo $YYYY; ?>&sub=<?php echo $sub; ?>" style="font-size:20px; width:50px;"><i class="fa fa-angle-right" aria-hidden="true"></i></a>
					</p>
					<ul>
						<li class="cal_header">
							<div class="Sun">일</div>
							<div class="Mon">월</div>
							<div class="Tue">화</div>
							<div class="Wed">수</div>
							<div class="Thu">목</div>
							<div class="Fri">금</div>
							<div class="Sat">토</div>
						</li>
						<li>
							<?php
								$week = 0;
								for ($i=0; $i < $firstday_weeknum; $i++) { echo("<div style='background:#eee' class='nones'></div>"); $week++;  }
								for($d=1; $d <= $lastday; $d++) {

									if($week == 6) { $temp = "</li><li>";  };
									if ($week == 7) { $week = 0; } // 밑에 빈공간을 채우기 위한 변수 초기화
									//$day = (date("j") == $d && $MM == date("m"))? "<font color='deepink'><b>".$d."</b></font>":$d;
									// 요일구하기
									$time3 = strtotime($YYYY."-".$MM."-".$d);

									$we = array('Sun', 'Mon', 'Tue', 'Wen', 'Thu', 'Fri', 'Sat');
									$time2 = date("w",$time3);
									$wday = $we[$time2];

									$we2 = array('일', '월', '화', '수', '목', '금', '토');
									$time2 = date("w",$time3);
									$wdays = $we2[$time2];

									if(date("j") == $d && $MM == date("m")) {
										$cls = "class='".$wday." today'";
										$day = $d;
									} else {
										$cls = "class='".$wday." days'";
										$day = $d;
									}
									$check = $YYYY . "-" . $MM . "-" . $d;
									$check2 = $YYYY . "-" . $MM . "-" . $d;


							?>
							<div <?php echo $cls; ?>><time><?php echo $day; ?><span class="W">(<?php echo $wdays; ?>)</span></time>
							<?php
								for ($k = 0; $k < count($schedules); $k++) {
									if ($schedules[$k][0] == "$MM-$day") {
										echo "<div class='in'><table>";
										echo "<tr><th style=''>승인 / 취소</th><td>".$schedules[$k][5]." / ".$schedules[$k][6]."</td>";
										echo "<tr><th style=''>승인금액</th><td style='border:0;'>".number_format($schedules[$k][2])."원</td>";
										echo "<tr><th style=''>취소금액</th><td style='border:0;'>".number_format($schedules[$k][3])."원</td>";
										echo "<tr><th style='border:0;'>정산예정액</th><td style='border:0;font-weight:bold;color:#4d4dff'>".number_format($schedules[$k][1])."원</td>";
										/*
										echo "<tr><th style='border:0;'>수수료</th><td style='border:0;'>".$schedules[$k][3]."%</td>";
										*/
										echo "</table></div>";
									}
								}
							?>
							</div>
							<?php echo $temp ?>
							<?php
								$week++;
								// 초기화
								$temp="";
								}
								for ($i=$week; $i < 7; $i++) { echo("<div style='background:#eee' class='nones'></div>"); }
							?>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>