<?php


	$sql_fild = " * ";
	$sql_fild .= ", count(if(pay_type = 'Y', pay_type, null)) as scnt "; // 승인 건수
	$sql_fild .= ", count(if(pay_type != 'Y', pay_type, null)) as ccnt "; // 취소 건수
	$sql_fild .= ", sum(if(pay_type = 'Y', pay, null)) as spay "; // 승인금액
	$sql_fild .= ", sum(if(pay_type != 'Y', pay, null)) as cpay "; // 취소금액
	$sql_fild .= ", sum(pay) as total_pay "; // 합계

	$sql_fild .= ", sum(mb_1_pay) as mb_1_pay "; // 본사 수수료
	$sql_fild .= ", sum(mb_2_pay) as mb_2_pay "; // 지사 수수료
	$sql_fild .= ", sum(mb_3_pay) as mb_3_pay "; // 총판 수수료
	$sql_fild .= ", sum(mb_4_pay) as mb_4_pay "; // 대리점 수수료
	$sql_fild .= ", sum(mb_5_pay) as mb_5_pay "; // 영업점 수수료
	$sql_fild .= ", sum(mb_6_pay) as mb_6_pay "; // 가맹점 정산액


	$schedules = [];

	$sql = "SELECT {$sql_fild}, DATE(pay_datetime) AS date, sum(pay) as pay, DATE_FORMAT(pay_datetime,'%Y-%m-%d') m FROM g5_payment WHERE mb_6 = '{$member['mb_id']}' GROUP BY m";
	$result = sql_query($sql);
	while ($R = sql_fetch_array($result)) {
		$s_pay = $R['spay'];
		$c_pay = $R['cpay'];
		$schedules[] = array(0 => date("Y-n-j", strtotime($R['date'])), 1 => $R['mb_6_pay'], 2 => $s_pay, 3 => $c_pay, 4 => $R['mb_6_fee'], 5 => $R['scnt'], 6 => $R['ccnt']);
	}


?>

<style>
/* 정산 헤더 스타일 */
.settlement-header {
	background: linear-gradient(135deg, #7b1fa2 0%, #8e24aa 100%);
	border-radius: 8px;
	padding: 12px 16px;
	margin-bottom: 10px;
	box-shadow: 0 2px 8px rgba(123, 31, 162, 0.2);
}
.settlement-title {
	color: #fff;
	font-size: 16px;
	font-weight: 600;
	display: flex;
	align-items: center;
	gap: 8px;
}
.settlement-title i {
	font-size: 14px;
	opacity: 0.8;
}
.settlement-notice {
	background: #fff3e0;
	border-left: 3px solid #ff9800;
	padding: 10px 12px;
	margin-bottom: 10px;
	border-radius: 4px;
	font-size: 13px;
	color: #e65100;
}
</style>

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

<div class="settlement-header">
	<div class="settlement-title">
		<i class="fa fa-calendar"></i>
		실시간 정산조회
	</div>
</div>

<div class="settlement-notice">
	<i class="fa fa-info-circle"></i> 실제 정산금은 달라질 수 있습니다.
</div>

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
				<div class='in'>
				<?php
					for ($k = 0; $k < count($schedules); $k++) {
						if ($schedules[$k][0] == "$YYYY-$MM-$day") {
							echo "<table>";
							echo "<tr><th style=''>승인 / 취소</th><td>".$schedules[$k][5]." / ".$schedules[$k][6]."</td>";
							echo "<tr><th style=''>승인금액</th><td style='border:0;'>".number_format($schedules[$k][2])."원</td>";
							echo "<tr><th style=''>취소금액</th><td style='border:0;'>".number_format($schedules[$k][3])."원</td>";
							echo "<tr><th style='border:0;'>정산예정액</th><td style='border:0;font-weight:bold;color:#4d4dff'>".number_format($schedules[$k][1])."원</td>";
							/*
							echo "<tr><th style='border:0;'>수수료</th><td style='border:0;'>".$schedules[$k][3]."%</td>";
							*/
							echo "</table>";
						}
					}
				?>
				</div>
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