<?php
	include_once('./_common.php');

	$bo_table = "url";
	$g5['title'] = "URL결제";
	include_once(G5_THEME_PATH.'/head.php');
	include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

	/*
	if($is_admin) {
		$sql_common = " from pay_payment_url where (datetime BETWEEN '{$fr_date} 00:00:00' and '{$to_date} 23:59:59') $authCd_common";
	} else {
		$sql_common = " from pay_payment_url where (datetime BETWEEN '{$fr_date} 00:00:00' and '{$to_date} 23:59:59') and mb_id = '{$member['mb_id']}' $authCd_common";
	}
	*/

	if($is_admin) {
		$sql_common = " from pay_payment_url ";
	} else {
		$sql_common = " from pay_payment_url where  mb_id = '{$member['mb_id']}'";
	}

	// 테이블의 전체 레코드수만 얻음
	$sql = " select COUNT(*) as cnt {$sql_common} ";
	$row = sql_fetch($sql);
	$total_count = $row['cnt'];

	$rows = $config['cf_page_rows'];
	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) {
		$page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	}
	$from_record = ($page - 1) * $rows; // 시작 열을 구함

	$sql = " select * {$sql_common} order by pm_id desc ";
	$result = sql_query($sql);
?>


<style>

/* 최근게시물 스킨 (new) */
#new_sch {background:#fff;text-align:center;margin:0 0 10px 0;}
#new_sch legend {position:absolute;margin:0;padding:0;font-size:0;line-height:0;text-indent:-9999em;overflow:hidden}
#new_sch form {}
#new_sch select {width:100%;border:1px solid #d0d3db;height:45px;padding:0 5px;border-radius:0}
#new_sch .sch_input {float:left;border:1px solid #d0d3db;width:32.5%;height:45px;padding:0 5px;border-radius:0}
#new_sch .sch_input.sch_input2 {margin-right:1%}
/*
#new_sch select {float:left;border:1px solid #d0d3db;width:49.5%;height:45px;padding:0 5px;border-radius:0}
#new_sch select#gr_id {margin-right:1%}
*/
#new_sch .ipt_sch {clear:both;position:relative;padding-bottom:10px}
#new_sch .frm_input {border:1px solid #d0d3db;width:100%;height:45px;border-radius:0}
#new_sch .sch_wr {position:relative;display:inline-block}
#new_sch .btn_submit {position:absolute;right:0;padding:0 10px;height:45px;width:45px;font-size:1.4em;font-weight:bold;color:#434a54;background:transparent}
#new_sch p {padding:12px 0;font-size:0.95em;text-align:center;background:#f7f7f7;color:#ff4a49;border:1px solid #ddd;}

.new_list li {padding:10px;background:#fff;border-bottom:1px solid #e8eaee}
.new_list li:last-child {border-bottom:0}
.new_list .new_tit {line-height:24px;margin-left:8px;font-weight:bold;}
.new_list .profile_img img {border-radius:50%}
.new_list .new_info {color:#646464;font-weight:normal; margin-top:5px;}
.new_list .new_date {margin-left:8px}
.new_list .new_date2 {font-size:0.8em; margin-left:8px}
.new_list .new_board {background:#eeeaf8;color:#ac92ec;padding:3px 4px 2px 4px; font-size:0.8em}
.new_list .new_name { background:#333;color:#fff;padding:3px 4px 2px 4px; font-size:0.8em}


/* 테이블 */
table {clear:both;width:100%;border-collapse:collapse;border-spacing:0;}
table caption {height:0;font-size:0;line-height:0;overflow:hidden}
table td{line-height: 22px;}
tfoot th, tfoot td {border:1px solid #d6dce7;background:#eee;padding:5px;text-align:center;font-weight:bold;}
tfoot th {}

.tbl_wrap {margin:0;padding:0}

/* thead 한 줄 테이블 */
.tbl_head01 {font-size:12px}
.tbl_head01 table {clear:both;width:100%;border-collapse:collapse;border-spacing:0}
.tbl_head01 thead th {background:#6f809a;color:#fff;border:1px solid #60718b;font-weight:bold;text-align:center;padding:8px 5px;}
.tbl_head01 thead th a{color:#fff}
.tbl_head01 thead input {vertical-align:top} /* middle 로 하면 게시판 읽기에서 목록 사용시 체크박스 라인 깨짐 */
.tbl_head01 thead a {color:#383838;text-decoration:underline}
.tbl_head01 tbody th{border:1px solid #d6dce7;padding:5px;text-align:center}
.tbl_head01 tbody td{border:1px solid #d6dce7;padding:10px;text-align:center}
.tbl_head01 tbody td .frm_input{width:100%;}
.tbl_head01 tbody td select{width:100%}
.tbl_head01 table .tbl_input{height:27px;line-height:25px;border:1px solid #d5d5d5;width:100%}
.tbl_head01 table select {height: 27px;line-height: 25px;width: 100%;}

.tbl_head01 table.intable tbody th{border:1px solid #d6dce7;padding:10px 5px;text-align:left}
.tbl_head01 table.intable tbody td{border:1px solid #d6dce7;padding:10px 5px;text-align:left}

.button {
	padding: 5px 10px;
	height: 28px;
	background: #ddd;
	color: #333;
	text-decoration: none;
	vertical-align: middle;
	border:1px solid #aaa;
}


.over_aad {margin-bottom:5px; padding-bottom:5px; border-bottom:1px solid #555;}
.overs_tr {cursor:pointer}
.overs_tr:hover{ background:#ddd;}
.overs_tr:active{ background:#ddd;}
.memodate {font-size:0.8em; color:#9f9f9f;}
</style>

<div id="bo_list">
	<div id="bo_li_op">

		<button id="btn_submit" accesskey="s" class="btn_submit" onclick="location.href='./add.php'" style="margin-bottom:10px">URL 상품등록</button>
		<?php /*
		<fieldset id="new_sch">
			<legend>상세검색</legend>
			<form name="fnew" method="get">


				<?php
					if($is_admin) {
						$sql2 = " select * from {$g5['member_table']} where mb_level = '5' group by mb_name asc ";
						$result2 = sql_query($sql2);
				?>
				<select name="mb_id" id="mb_id" class="mb_id">
					<option value="">전체업체</option>
					<?php for ($k=0; $row2=sql_fetch_array($result2); $k++) { ?>
					<option value="<?php echo $row2['mb_id']; ?>" <?php if($mb_id == $row2['mb_id']) { echo "selected"; } ?>><?php echo $row2['mb_nick']; ?></option>
					<?php } ?>
				</select>
				<?php } ?>

				<?php if($is_admin) { ?>
				<div style=" font-size:0.85em">
					<button type="submit" class="button" onclick="javascript:set_date('오늘');" style="margin:10px 0;"><span>오늘</span></button>
					<button type="submit" class="button" onclick="javascript:set_date('어제');" style="margin:10px 0;"><span>어제</span></button>
					<button type="submit" class="button" onclick="javascript:set_date('이번주');" style="margin:10px 0;"><span>이번주</span></button>
					<button type="submit" class="button" onclick="javascript:set_date('지난주');" style="margin:10px 0;"><span>지난주</span></button>
					<button type="submit" class="button" onclick="javascript:set_date('이번달');" style="margin:10px 0;"><span>이번달</span></button>
					<button type="submit" class="button" onclick="javascript:set_date('지난달');" style="margin:10px 0;"><span>지난달</span></button>
				</div>
				<?php } ?>
				<div class="ipt_sch">
				<input type="text" name="fr_date" required="" id="fr_date" class="sch_input sch_input2" size="10" value="<?php echo $fr_date; ?>" readonly style="text-align:center">
				<input type="text" name="to_date" required="" id="to_date" class="sch_input sch_input2" size="10" value="<?php echo $to_date; ?>" readonly style="text-align:center">

				<select name="payr" id="payr" class="payr" class="sch_input sch_input2" style="width:32.5%;">
					<option value="0" <?php if(!$payr) { echo "selected"; } ?>>승인내역</option>
					<option value="2" <?php if($payr == "2") { echo "selected"; } ?>>실패내역</option>
					<option value="3" <?php if($payr == "3") { echo "selected"; } ?>>전체내역</option>
				</select>
				</div>

				<div class="ipt_sch">
					<label for="mb_id" class="sound_only">검색어<strong class="sound_only">필수</strong></label>
					<input name="authCd" placeholder="승인번호" id="stx" class="frm_input" size="15" maxlength="20" value="<?php echo $authCd; ?>">
					<button type="submit" class="btn_submit"><i class="fa fa-search" aria-hidden="true"></i></button>
				</div>
			</form>
			<script>
			function select_change()
			{
				document.fnew.submit();
			}
			document.getElementById("gr_id").value = "";
			document.getElementById("view").value = "";
			</script>
		</fieldset>
		*/ ?>



	</div>
	<div class="tbl_head01 tbl_wrap">
		<table>
			<thead>
			<tr>
				<th>NO</th>
				<th>PG</th>
				<th>상품명</th>
				<th>상품가격</th>
				<th>판매자명</th>
				<th>등록일</th>
			</tr>
			</thead>


			<tbody>
			<?php
			for ($i=0; $row=sql_fetch_array($result); $i++) {
				$num = number_format($total_count - ($page - 1) * $config['cf_page_rows'] - $i);
				if($row['url_payments'] == "k1") {
					$url_payments = "광원";
				} else if($row['url_payments'] == "danal") {
					$url_payments = "다날";
				} else if($row['url_payments'] == "welcom") {
					$url_payments = "웰컴";
				} else if($row['url_payments'] == "paysis") {
					$url_payments = "페이시스";
				} else if($row['url_payments'] == "stn") {
					$url_payments = "섹타나인";
				} else {
					$url_payments = "<span style='color:red'>오류</span>";
				}


				if(!$row['url_shot']) {
					$row['url_shot'] = "http://pay.mpc.icu/url/pay.php?urlcode=".$row['urlcode'];

				}

			?>
			<tr onclick="opens('over_<?php echo $i; ?>');" id="overs" class="overs_tr tr_over_<?php echo $i; ?>">
				<td style="width:50px;"><?php echo $num; ?></td>
				<td><?php echo $url_payments; ?></td>
				<td><?php echo $row['url_pd']; ?></td>
				<td><?php echo number_format($row['url_price']); ?></td>
				<td><?php echo $row['url_pname']; ?></td>
				<td style="width:100px;"><?php echo date("m-d H:i", strtotime($row['datetime'])); ?></td>
			</tr>
			<tr id="over_<?php echo $i; ?>" <?php if($row['pm_id'] != $pm_id) { ?>style="display:none;"<?php } ?> class="overs">
				<td style="background-color: rgb(221, 221, 221);"><i class="fa fa-arrow-right" aria-hidden="true"></i></td>
				<td colspan="5" style="background:#444; color:#888; text-align:left;">
					<table class="intable">
						<tr>
							<th>등록자</th><td><span style="color:#fff;"><?php echo $row['mb_name']; ?></span></td>
						</tr>
						<tr>
							<th>상품명</th><td><span style="color:#fff;"><?php echo $row['url_pd']; ?></span></td>
						</tr>
						<tr>
							<th>상품가격</th><td><span style="color:#fff;"><?php echo number_format($row['url_price']); ?></span></td>
						</tr>
						<tr>
							<th>판매자명</th><td><span style="color:#fff;"><?php echo $row['url_pname']; ?></span></td>
						</tr>
						<tr>
							<th>판매자 연락처</th><td><span style="color:#fff;"><?php echo $row['url_ptel']; ?></span></td>
						</tr>
						<tr>
							<th>상품설명</th><td><span style="color:#fff;"><?php echo $row['url_pcontent']; ?></span></td>
						</tr>
						<tr>
							<th>비고</th><td><span style="color:#fff;"><?php echo $row['url_etc']; ?></span></td>
						</tr>
						<tr>
							<th>등록일시</th><td><span style="color:#fff;"><?php echo $row['datetime']; ?></span></td>
						</tr>
						<tr>
							<th>URL</th><td><a href="<?php echo $row['url_shot']; ?>" target="_blank"><span style="color:#fff;"><?php echo $row['url_shot']; ?></span></a></td>
						</tr>
						<tr>
							<th>상태</th><td><span style="color:#fff;">사용중</span></td>
						</tr>
						<tr>
							<th>관리</th>
							<td>
								<button style="padding:3px; border:0; background:#ffff00; color:#000;">미사용</button>
								<button style="padding:3px; border:0; background:#ffff00; color:#000;">삭제</button>
							</td>
						<tr>
							<th>QR코드</th><td>
							<div style="text-align:center">
							<?php
								if($row['urlcode']) {
									$current_url = 'http://pay.mpc.icu/url/pay.php?urlcode='.$row['urlcode'];
									echo "<img src='https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($current_url) . "&size=300x300' alt='QR Code' style='width:100%;'>";
								}
							?>
							</div>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<?php
				}
			?>
			</tbody>
			<tfoot>
			<?php
				if($is_admin) {
					$total_price_374 = $total_price*0.0374;
					$total_price_41 = $total_price*0.041;

					$t_price_374 = $total_price - $total_price_374;
					$t_price_41 = $total_price - $total_price_41;

			?>
			<?php /*
			<tr>
				<th colspan="3" style="text-align:right;"><?php echo number_format($total_price); ?> - <?php echo number_format($total_price_374); ?></th>
				<th style="text-align:right;"><?php echo number_format($t_price_374); ?></th>
				<th>3.74%</th>
			</tr>
			<tr>
				<th colspan="3" style="text-align:right;"><?php echo number_format($total_price); ?> - <?php echo number_format($total_price_41); ?></th>
				<th style="text-align:right;"><?php echo number_format($t_price_41); ?></th>
				<th>4.10%</th>
			</tr>
			*/ ?>
			<?php
				}
			?>
			<tfoot>
			<?php
				if($i == 0) {
			?>
			<tr><td colspan="<?=$is_admin?'11':'10';?>" style="height:200px">URL 상품이 없습니다.</td></tr>
			<?php } ?>
		</table>
	</div>
</div>

<script>

$(function(){
	$("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });
});

$("#fr_date, #to_date, #sfl, #mb_id, #payr").change(function() {
	$(this).parents().filter("form").submit();
});



function opens(ids)
{
	$(".overs").hide();
	$("#"+ids).show();
	$(".overs_tr").css("background-color", "#fff");
	$(".tr_"+ids).css("background-color", "#ddd");
}

function fvisit_submit(act)
{
    var f = document.fvisit;
    f.action = act;
    f.submit();
}
<? if($is_admin) { ?>
function reSend(id) {
	$.ajax({
		type: "POST",
		url: "./ajax.resend.php?id="+id,
		dataType: "json",
		success: function (json) {
			if (json) {
				if(json.success==true) {
					alert(json.message);
				} else {
					alert(json.message);
				}
			} else {
				alert("자료가 없습니다.");
			}
		},
	});
};
<? } ?>



function set_date(today)
{
	<?php
	$date_term = date('w', G5_SERVER_TIME);
	$week_term = $date_term + 7;
	$last_term = strtotime(date('Y-m-01', G5_SERVER_TIME));
	?>
	if (today == "오늘") {
		document.getElementById("fr_date").value = "<?php echo date('Y-m-d'); ?>";
		document.getElementById("to_date").value = "<?php echo date('Y-m-d'); ?>";
		document.getElementById("day").value = "1";
	} else if (today == "어제") {
		document.getElementById("fr_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME - 86400); ?>";
		document.getElementById("to_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME - 86400); ?>";
		document.getElementById("day").value = "2";
	} else if (today == "이번주") {
		document.getElementById("fr_date").value = "<?php echo date('Y-m-d', strtotime('-'.$date_term.' days', G5_SERVER_TIME)); ?>";
		document.getElementById("to_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME); ?>";
		document.getElementById("day").value = "3";
	} else if (today == "이번달") {
		document.getElementById("fr_date").value = "<?php echo date('Y-m-01', G5_SERVER_TIME); ?>";
		document.getElementById("to_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME); ?>";
		document.getElementById("day").value = "4";
	} else if (today == "지난주") {
		document.getElementById("fr_date").value = "<?php echo date('Y-m-d', strtotime('-'.$week_term.' days', G5_SERVER_TIME)); ?>";
		document.getElementById("to_date").value = "<?php echo date('Y-m-d', strtotime('-'.($week_term - 6).' days', G5_SERVER_TIME)); ?>";
		document.getElementById("day").value = "5";
	} else if (today == "지난달") {
		document.getElementById("fr_date").value = "<?php echo date('Y-m-01', strtotime('-1 Month', $last_term)); ?>";
		document.getElementById("to_date").value = "<?php echo date('Y-m-t', strtotime('-1 Month', $last_term)); ?>";
		document.getElementById("day").value = "6";
	} else if (today == "전체") {
		document.getElementById("fr_date").value = "all";
		document.getElementById("to_date").value = "all";
	}
}

</script>

<?php
	include_once(G5_THEME_PATH.'/tail.php');
?>