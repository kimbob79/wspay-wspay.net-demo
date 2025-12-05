<?php
	function ErrorMsg($msg) {
		echo " <script>window.alert('$msg');history.go(-1);</script>";
		exit;
	}

	function CalendarPrint($mday, $dType1='') {
	//	var_dump($mday);
	//	var_dump($dType1);
		echo "<div>$mday</div>";
		/*
		echo "<font class=lunar>$lunarday</font>2<br/>";
		echo "<font class=gangi>$gaingi</font>3<br/>";
		if(strlen($holidata)>0) echo "<font class=red>$holidata</font>4<br/>";
		if(strlen($memorialdata)>0) echo "<font class=sblue>$memorialdata</font>5<br/>";
		*/
		if(count($dType1)>0) { // 배열 출력
			for ($i = 0; $i < count($dType1); $i++) {
				echo "<div style='font-weight:300; padding-top:50px;'>총승인 : ".number_format($dType1[$i][1])."원</div>";
				echo "<div style='font-weight:900;'>정산금 : ".number_format($dType1[$i][0])."원</div>";
			}
		}
	}

	function SkipOffset($no, $sdate = '', $edate = '') {
		for ($i = 1; $i <= $no; $i++) {
			$ck = $no - $i + 1;
			if ($sdate)
				$num = date('n.j', $sdate - (3600 * 24) * $ck);
			if ($edate)
				$num = date('n.j', $edate + (3600 * 24) * ($i - 1));
			echo "<td valign=top class='tds oday'>".$num."</td>";
		}
	}


	$thisyear = date('Y'); // 4자리 연도
	$thismonth = date('n'); // 0을 포함하지 않는 월
	$today = date('j'); // 0을 포함하지 않는 일

	// $year, $month 값이 없으면 현재 날짜
	$year = isset($_GET['year']) ? $_GET['year'] : $thisyear;
	$month = isset($_GET['month']) ? $_GET['month'] : $thismonth;
	$day = isset($_GET['day']) ? $_GET['day'] : $today;

	//------ 날짜의 범위 체크
	if (($year > 2038) or ($year < 1900))
		ErrorMsg("연도는 1900 ~ 2038년만 가능합니다.");

	$last_day = date('t', mktime(0, 0, 0, $month, 1, $year)); // 해당월의 총일수 구하기

	$prevmonth = $month - 1;
	$nextmonth = $month + 1;
	$prevyear = $nextyear = $year;
	if ($month == 1) {
		$prevmonth = 12;
		$prevyear = $year - 1;
	} elseif ($month == 12) {
		$nextmonth = 1;
		$nextyear = $year + 1;
	}
	$pre_year = $year - 1;
	$next_year = $year + 1;

	/****************** lunar_date ************************/
	$predate = date("Y-m-d", mktime(0, 0, 0, $month - 1, 1, $year));
	$nextdate = date("Y-m-d", mktime(0, 0, 0, $month + 1, 1, $year));

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
		$s_pay = $R['spay'] - $R['cpay'];
		$schedule[] = array(0 => date("n-j", strtotime($R['date'])), 1 =>date("n-j", strtotime($R['date'])) ,2 => $R['mb_6_pay'],3 => $s_pay);
	}
?>
	<style>
		.b-table .table { border-collapse: collapse; }
		.table td.tds { height:131px; text-align:right; }
		.today { background:#99FFFF }
		.oday { background:#f9f9f9; color:#888 }
		.holy {color:red;}
		.holy2 {color:blue;}
		.black {color:#000;}
		.table.is-hoverable tbody tr:not(.is-selected):hover {
		  background-color: #fff;
		}

		.table.is-hoverable.is-striped tbody tr:not(.is-selected):hover {
		  background-color: #fff;
		}

		.table.is-hoverable.is-striped tbody tr:not(.is-selected):hover:nth-child(even) {
		  background-color: #fff;
		}

		.table.is-hoverable td:hover {
		  background-color: #99FFFF;
		}
	</style>


<div class="KDC_Content__root__tP6yD KDC_Content__responsive__4kCaB CWLNB">

	<div class="KDC_Tabs__root__JmfCY PeriodButtonGroup_root__UE4Kj">
		<div class="KDC_Tab__root__h2hVQ tab type_other tab_selected"><a href='<?php echo './?p='.$p.'&year='.$prevyear.'&month='.$prevmonth . '&day=1'; ?>' style="color:#fff;">이전달</a></div>
		<div class="KDC_Tab__root__h2hVQ tab type_other"><?php echo $year . '년 ' . $month . '월 '; ?></div>
		<div class="KDC_Tab__root__h2hVQ tab type_other tab_selected"><a href='<?php echo './?p='.$p.'&year='.$nextyear.'&month='.$nextmonth . '&day=1'; ?>' style="color:#fff;">다음달</a></div>
	</div>



	<div class="KDC_Row__root__uio5h KDC_Row__responsive__obNwV">
		<div class="KDC_Column__root__NK8XY KDC_Column__flex_1__UcocY">
			<div class="KDC_Section__root__VXHOv">
				<table class="KDC_Table__root__Jim4z table is-fullwidth is-striped is-hoverable is-fullwidth memtable">
					<tr class="info">
						<th style="width:14%;text-align:center;">일</td>
						<th style="width:14%;text-align:center;">월</th>
						<th style="width:14%;text-align:center;">화</th>
						<th style="width:14%;text-align:center;">수</th>
						<th style="width:14%;text-align:center;">목</th>
						<th style="width:14%;text-align:center;">금</th>
						<th style="width:14%;text-align:center;">토</th>
					</tr>
					<tr height=<?php echo $cellh;?>>
					<?php
						$date = 1;
						$offset = 0;
						$ck_row = 0;
						//프레임 사이즈 조절을 위한 체크인자
						$R = array();
						while ($date <= $last_day) {

							$mday = $date;

							if ($date == '1') {
								// 시작 요일 구하기 : date("w", strtotime($year."-".$month."-01"));
								$offset = date('w', mktime(0, 0, 0, $month, $date, $year)); // 0: 일요일, 6: 토요일
								SkipOffset($offset, mktime(0, 0, 0, $month, $date, $year));
							}


							if ($offset == 0) {
								$style = "holy"; // 일요일 빨간색으로 표기
							} else if($offset == 6) {
								$style = "holy2"; // 토요일 빨간색 또는 파란색
							} else {
								$style = "black";
							}

							// 사용자 일정 데이터
							$dType1 = array();
							for ($i = 0; $i < count($schedule); $i++) {
								if ($schedule[$i][0] == "$month-$date") {
									$dType1[] = array(0=>$schedule[$i][2],1=>$schedule[$i][3]);
								}
							}

							if ($date == $today && $year == $thisyear && $month == $thismonth) { // 오늘 날짜
								echo "<td valign='top' class='tds today ".$style."' id='".$year."-".$month."-".$mday."'>";
							} else {
								echo "<td valign='top' class='tds ".$style."' id='".$year."-".$month."-".$mday."'>";
							}

							CalendarPrint($mday, $dType1);

							echo "</td>\n";
							// 출력후 값 초기화
							$holidata = "";
							$memorialdata ="";
							$date++; // 날짜 증가
							$offset++;
							if ($offset == 7) {
								echo "</tr>";
								if ($date <= $last_day) {
									echo "<tr>";
									$ck_row++;
								}
								$offset = 0;
							}
						}// end of while
						if ($offset != 0) {
							SkipOffset((7 - $offset), '', mktime(0, 0, 0, $month + 1, 1, $year));
							echo "</tr>\n";
						}
						echo("</td>\n");
						/*
						function Lun2SolDate($date){
							global $dbconn;
							$sql = "SELECT solar_date FROM lunar_data where lunar_date='".$date."'";
							$result = sql_query($sql);
							$R = sql_fetch_array($result);
							return $R[0];
						}
						*/
						function isWeekend($date){
							// 앙력 날짜의 요일을 리턴
							// 일요일 0 토요일 6
							return date("w", strtotime($date));
						}
					?>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>