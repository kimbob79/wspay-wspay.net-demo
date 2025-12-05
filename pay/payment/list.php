<?php
	include_once('./_common.php');

	$bo_table = "list";
	$g5['title'] = "결제내역";
	include_once(G5_THEME_PATH.'/head.php');
	include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
	$yd = date('Y-m-d', strtotime('-1 day'));

	if(!$fr_date) { $fr_date = G5_TIME_YMD; }
	if(!$to_date) { $to_date = G5_TIME_YMD; }
	
	if($authCd) { $authCd_common = " and authCd = '{$authCd}' "; }
	if($mb_id) { $authCd_common = " and mb_id = '{$mb_id}' "; }

	if(!$payr) {
		$authCd_common .= " and resultCd = '0000' ";
	} else if($payr == "2") {
		$authCd_common .= " and resultCd != '0000' ";
	} else {
		$authCd_common .= " ";
	}

	if($is_admin) {
		$sql_common = " from pay_payment_passgo where (datetime BETWEEN '{$fr_date} 00:00:00' and '{$to_date} 23:59:59') $authCd_common";
	} else {
		$sql_common = " from pay_payment_passgo where (datetime BETWEEN '{$fr_date} 00:00:00' and '{$to_date} 23:59:59') and mb_id = '{$member['mb_id']}' $authCd_common";
	}

	// 테이블의 전체 레코드수만 얻음
	$sql = " select COUNT(*) as cnt, SUM(price) as price {$sql_common} ";
	$row = sql_fetch($sql);
	$total_count = $row['cnt'];

	$sql = " select SUM(amount) as price_s {$sql_common} and resultYN != '1' ";
	$row = sql_fetch($sql);
	$total_sprice = $row['price_s'];

	$sql = " select SUM(amount) as price_c {$sql_common} and resultYN = '1' ";
	$row = sql_fetch($sql);
	$total_cprice = $row['price_c'];

	$total_price = $total_sprice - $total_cprice;
	$page = 1;

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
.tbl_head01 tbody td{border:1px solid #d6dce7;padding:10px 5px;text-align:center}
.tbl_head01 tbody tr:nth-child(even){background:#eff3f9}
.tbl_head01 tbody td .frm_input{width:100%;}
.tbl_head01 tbody td select{width:100%}
.tbl_head01 table .tbl_input{height:27px;line-height:25px;border:1px solid #d5d5d5;width:100%}
.tbl_head01 table select {height: 27px;line-height: 25px;width: 100%;}


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
			<p>
				<span style="color:#333;">총<?php echo $total_count; ?>건</span><span style="padding:0 5px; color:#777">|</span>
				<span style="color:blue"><?php echo number_format($total_sprice); ?></span><span style="padding:0 5px; color:#777">-</span>
				<span><?php echo number_format($total_cprice); ?><span style="padding:0 5px; color:#777">=</span>
				<span style="color:#333; font-weight:bold"><?php echo number_format($total_price); ?></span>
			</p>
			<script>
			function select_change()
			{
				document.fnew.submit();
			}
			document.getElementById("gr_id").value = "";
			document.getElementById("view").value = "";
			</script>
		</fieldset>



	</div>
	<div class="tbl_head01 tbl_wrap">
		<table>
			<thead>
			<tr>
				<th>PG</th>
				<th><?php if($is_admin) { ?>업체명<?php } else { ?>카드주<?php } ?></th>
				<th>승인번호</th>
				<th>상태</th>
				<th>승인금액</th>
				<th>승인시간</th>
			</tr>
			</thead>
			<tbody>
			<?php
			for ($i=0; $row=sql_fetch_array($result); $i++) {
				$num = number_format($total_count - ($page - 1) * $config['cf_page_rows'] - $i);
				$bg = 'bg'.($i%2);
				
				$receipt = substr($row['creates'],0,8)."/".$row['authCd'];

				if($is_admin) {
					$can_btn = "<a style='font-weight:bold; color:#ffffFF'>관리자 취소 불가</a>";
				} else {
					if($member['mb_adult'] == 0) {
						if($row['resultYN'] == "1") {
							$pride = "<del style='color:red'>".number_format($row['amount'])."</del>";
							$can_btn = "<a style='font-weight:bold; color:#ffff00'>취소완료</a>";
						} else if($row['resultYN'] == "2") {
							$pride = "<del style='color:red'>".number_format($row['amount'])."</del>";
							$can_btn = "<a style='font-weight:bold; color:#ffff00'>취소완료</a>";
						} else {
							$pride = number_format($row['amount']);
							if(date("Y-m-d") == date("Y-m-d", strtotime($row['creates']))) {

								$row_cancel = sql_fetch(" select count(pm_id) as pm_count from g5_payment where mb_id = '{$row['mb_id']}' and authCd = '{$row['authCd']}' and bin = '{$row['bin']}' and advanceMsg = '정상취소' ");
								// select * from g5_payment where mb_id = 'sss1234' and authCd = '54807295' and bin = '488972' and advanceMsg = '정상취소'

								if($row_cancel['pm_count']) {
									$can_btn = "<a href='javascript:alert(\"이미 취소 완료되었습니다..\");' style='font-weight:bold; color:#ffff00'>취소완료</a>";
								} else {
									$can_btn = "<a href='".G5_URL."/payment/cancel.php?id=".$row['pm_id']."' onclick='win_card_cancel(this.href); return false;' style='font-weight:bold; color:#ffff00'>승인취소하기</a>";
								}

							} else {
								$can_btn = "<a href='javascript:alert(\"승인 당일에만 취소 가능합니다.\");' style='font-weight:bold; color:#ffff00'>취소불가</a>";
							}
						}
					} else {
						if($row['resultYN'] == "1") {
							$pride = "<del style='color:red'>".number_format($row['amount'])."</del>";
							$can_btn = "";
						} else if($row['resultYN'] == "2") {
							$pride = "<del style='color:red'>".number_format($row['amount'])."</del>";
							$can_btn = "";
						} else {
							$pride = number_format($row['amount']);
							$can_btn = "";
						}
					}
				}



				$mb = get_member($row['mb_id']);
				//G5_TIME_Y-m-d
				//
				if($is_admin) {
					$receipt_txt =  $mb['mb_name'];
				} else if($member['mb_id'] == "wpay") {
					$receipt_txt =  $mb['mb_name'];
				} else {
					$receipt_txt =  '영수증';
				}

				if($row['resultCd'] == "0000") {
					if($row['resultYN'] == "0") {
						$advanceMsg = "<span style='color:blue;'>승인</span>";
						$advanceMsgs = "정상승인";
					} else if($row['resultYN'] == "1") {
						$advanceMsg = "<span style='color:red;'>취소</span>";
						$advanceMsgs = "결제취소";
					} else if($row['resultYN'] == "2") {
						$advanceMsg = "<span style='color:red;'>승/취</span>";
						$advanceMsgs = "승인취소";
					}
				} else {
					$advanceMsg = "<span style='color:red;'>실패</span>";
					$advanceMsgs = "결제실패";
				}

				if($row['cardAuth'] == "true") {
					$cardAuth = "<span style='color:blue;'>인</span>";
					$cardAuth_txt = "인증결제";
				} else {
					$cardAuth = "<span style='color:black;'>비</span>";
					$cardAuth_txt = "비인증결제";
				}
				if($row['mb_name'] == "주식회사 케이물류") {
					$row['mb_name'] = "케이물류";
				}

				$urlcode = "";

				if($row['urlcode']) {
					$urlcode = "<span style='background:green;color:#fff;padding:4px 5px 2px;font-size:11px;' margin-left:1px;>U</span>";
				}


				if($row['payments'] == "k1") {
					$pg = "<span style='background:red;color:#fff;padding:4px 5px 2px;font-size:11px;'>광</span>".$urlcode;
					$pgs = "광원";
				} else if($row['payments'] == "k1b") {
					$pg = "<span style='background:#777;color:#fff;padding:4px 5px 2px;font-size:11px;'>광</span>".$urlcode;
					$pgs = "광원 비인증";
				} else if($row['payments'] == "danal") {
					$pg = "<span style='background:blue;color:#fff;padding:4px 5px 2px;font-size:11px;'>다</span>".$urlcode;
					$pgs = "다날";
				} else if($row['payments'] == "welcom") {
					$pg = "<span style='background:#666;color:#fff;padding:4px 5px 2px;font-size:11px;'>웰</span>".$urlcode;
					$pgs = "웰컴";
				} else if($row['payments'] == "paysis") {
					$pg = "<span style='background:#D527B7;color:#fff;padding:4px 5px 2px;font-size:11px;'>페</span>".$urlcode;
					$pgs = "페이시스";
				} else if($row['payments'] == "stn") {
					$pg = "<span style='background:brown;color:#fff;padding:4px 5px 2px;font-size:11px;'>섹</span>".$urlcode;
					$pgs = "페이시스";
				} else if($row['payments'] == "stnb") {
					$pg = "<span style='background:blue;color:#fff;padding:4px 5px 2px;font-size:11px;'>섹</span>".$urlcode;
					$pgs = "페이시스";
				} else {
					$pg = "";
				}


			?>
			<tr <?php if($row['resultCd'] != '0000') { echo "style='background:#ffbebe'"; } else { ""; } ?> onclick="opens('over_<?php echo $i; ?>');" id="overs" class="overs_tr tr_over_<?php echo $i; ?>">
				<td><?php echo $pg; ?></span></td>
				<td style="text-align:left;"><?php if($is_admin) { ?><?php echo substr($mb['mb_nick'],0,12); ?><?php } else { ?><?php echo $row['payerName']; ?><?php } ?></td>
				<td><?php if($row['authCd']) { echo $row['authCd']; } else { echo $row['resultMsg']; } ?></span></td>
				<td><?php echo $advanceMsg; ?></span></td>
				<td style="text-align:right;"><?php echo $pride; ?></td>
				<td style="width:53px;"><?php echo date("H:i", strtotime($row['creates'])); ?></td>
			</tr>
			<tr id="over_<?php echo $i; ?>" <?php if($row['pm_id'] != $pm_id) { ?>style="display:none;"<?php } ?> class="overs">
				<td><i class="fa fa-arrow-right" aria-hidden="true"></i></td>
				<td colspan="<?php if($is_admin) { echo "5"; } else { echo "4"; } ?>" style="background:#444; color:#888; text-align:left;">

					<?php if($is_admin) { ?>
					<div class="over_aad">피지업체 : <span style="color:#fff;"><?php echo $pgs; ?></span></div>
					<div class="over_aad">가맹점명 : <span style="color:#fff;"><?php echo $mb['mb_nick']; ?></span></div>
					<?php } ?>
					<div class="over_aad">결제타입 : <span style="color:#fff;"><?php if($row['urlcode']) { ?>URL 결제<?php } else { ?>일반 결제<?php } ?></span></div>
					<div class="over_aad">결제상태 : <span style="color:#fff;"><?php echo $advanceMsgs; ?></span></div>
					<div class="over_aad">승인번호 : <span style="color:#fff;"><?php if($row['authCd']) { echo $row['authCd']; } else { echo $row['resultMsg']; } ?></span></div>
					<?php /*
					<div class="over_aad">결제방식 : <span style="color:#fff;"><?php echo $cardAuth_txt; ?></span></div>
					*/ ?>
					<div class="over_aad">결제금액 : <span style="color:#fff;"><?php echo $pride; ?></span></div>
					<div class="over_aad">결제일시 : <span style="color:#fff;"><?php echo date("Y-m-d H:i:s", strtotime($row['creates'])); ?></span></div>
					<div class="over_aad">결제자명 : <span style="color:#fff;"><?php echo $row['payerName']; ?></span></div>
					<div class="over_aad">카드정보 : <span style="color:#fff;"><?php echo $row['acquirer']; ?></span></div>
					<div class="over_aad">카드번호 : XXXX-XXXX-XXXX-<span style="color:#fff;"><?php echo $row['last4']; ?></span></div>
					<div class="over_aad">휴대전화 : <span style="color:#fff;"><?php echo $row['payerTel']; ?></span></div>
					<div class="over_aad">결제상품 : <span style="color:#fff;"><?php echo $row['descs']; ?></span></div>
					<div class="over_aad">할부정보 : <span style="color:#fff;"><?php if($row['installment'] < 1) { echo "일시불"; } else { echo $row['installment']."개월"; } ?></span></div>
					<div class="over_aad" style=" height:40px; line-height:40px;">
						<div style="width:49%; text-align:center; float:left;"><?php echo $can_btn; ?></div>

						<div style="width:49%; text-align:center; float:right;"><a href='<?php echo G5_URL; ?>/payment/receipt.php?id=<?php echo $row['trxId']; ?>&receipt=<?php echo $receipt; ?>' onclick="win_receipt(this.href, <?php echo $row['authCd']; ?>); return false;" style="font-weight:bold; color:#ffff00">영수증보기</a></div>

						<?php if($row['payments'] == "stn") { ?>
						<div style="width:49%; text-align:center; float:right;"><a href='https://cp.mainpay.co.kr/salesReceipt/salesReceipt_popup.do?ref_no=<?php echo $row['trxId']; ?>&tran_date=<?php echo substr($row['creates'],2,6); ?>' onclick="win_receipt2(this.href, <?php echo $row['authCd']; ?>); return false;" style="font-weight:bold; color:#ffff00">섹타나인 영수증</a></div>
						<?php } ?>
					</div>
					<?php if($is_admin) { ?>
					<?php if($row['memo']) { ?>
					<div class="over_aad" style="color:#fff"><?php echo $row['memo']; ?></span></div>
					<?php } ?>
					<div style="text-align:center;">
						<form name="form" id="form" action="<?php echo G5_URL; ?>/payment/list_update.php" method="post">
							<div>
								<input type="hidden" name="fr_date" value="<?php echo $fr_date; ?>">
								<input type="hidden" name="to_date" value="<?php echo $to_date; ?>">
								<input type="hidden" name="pm_id" value="<?php echo $row['pm_id']; ?>">
								<input type="text" name="memo" placeholder="추가할 메모를 입력하세요" style="width:100%;height:40px;" class="frm_input">
							</div>
						</form>
					</div>
					<?php } ?>
				</td>
			</tr>
			</tbody>
			<?php
				}
			?>
			<tfoot>
			<tr>
				<th colspan="4">합계</th>
				<th style="text-align:right;"><?php echo number_format($total_price); ?></th>
				<th></th>
			</tr>
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
			<tr><td colspan="<?=$is_admin?'11':'10';?>" style="height:200px">검색하신 날짜에 결제내역이 없습니다.</td></tr>
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