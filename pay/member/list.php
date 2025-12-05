<?php
	include_once('./_common.php');

	if(!$is_admin) {
		alert("잘못된 접근입니다.");
	}

	if($proc_mode=='login'){
		$mb = get_member($login_us_id);
		if($mb){
			//관리자 정보 저장하기
			set_session('ss_admin_mb_id', get_session('ss_mb_id'));
			set_session('ss_admin_mb_key', get_session('ss_mb_key'));
			set_session('ss_admin_redir', $PHP_SELF);

			// 회원아이디 세션 생성
			set_session('ss_mb_id', $mb['mb_id']);
			// FLASH XSS 공격에 대응하기 위하여 회원의 고유키를 생성해 놓는다. 관리자에서 검사함 - 110106
			set_session('ss_mb_key', md5($mb['mb_datetime'] . get_real_client_ip() . $_SERVER['HTTP_USER_AGENT']));
			if(function_exists('update_auth_session_token')) update_auth_session_token($mb['mb_datetime']);

			alert($mb['mb_name']." 아이디로 로그인합니다.", G5_URL);
		}
		else {
			alert('존재하지 않는 회원입니다.');
		}
		exit;
	}



	$bo_table = "member";
	$g5['title'] = "회원관리";
	include_once(G5_THEME_PATH.'/head.php');
	include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
	$yd = date('Y-m-d', strtotime('-1 day'));
	if(!$fr_date) { $fr_date = G5_TIME_YMD; }
	if(!$to_date) { $to_date = G5_TIME_YMD; }
	
	if($authCd) { $authCd_common = " and authCd = '{$authCd}' "; }
	if($mb_name) { $authCd_common = " and mb_name like '%{$mb_name}%' "; }

	if($is_admin) {
		$sql_common = " from {$g5['member_table']} where resultCd = '0000' and (datetime BETWEEN '{$fr_date} 00:00:00' and '{$to_date} 23:59:59') $authCd_common";
	} else {
		$sql_common = " from g5_payment_passgo where resultCd = '0000' and (datetime BETWEEN '{$fr_date} 00:00:00' and '{$to_date} 23:59:59') and mb_id = '{$member['mb_id']}' $authCd_common";
	}

	
	$sql_common = " from {$g5['member_table']} where (1) $authCd_common";

	// 테이블의 전체 레코드수만 얻음
	$sql = " select COUNT(*) as cnt {$sql_common} ";
	$row = sql_fetch($sql);
	$total_count = $row['cnt'];

	$sql = " select SUM(price) as price_s {$sql_common} and advanceMsg = '정상승인' ";
	$row = sql_fetch($sql);
	$total_sprice = $row['price_s'];

	$sql = " select SUM(price) as price_c {$sql_common} and advanceMsg = '정상취소' ";
	$row = sql_fetch($sql);
	$total_cprice = $row['price_c'];

	$total_price = $total_sprice - $total_cprice;
	$page = 1;

	$sql = " select * {$sql_common} order by mb_10 asc ";
	$result = sql_query($sql);

//	echo $sql;
?>


<style>

/* 최근게시물 스킨 (new) */
#new_sch {background:#fff;text-align:center;margin:0 0 10px 0;}
#new_sch legend {position:absolute;margin:0;padding:0;font-size:0;line-height:0;text-indent:-9999em;overflow:hidden}
#new_sch form {padding:0 0 10px 0}
#new_sch select {width:100%;border:1px solid #d0d3db;height:45px;padding:0 5px;border-radius:0}
#new_sch .sch_input {float:left;border:1px solid #d0d3db;width:49.5%;height:45px;padding:0 5px;border-radius:0}
#new_sch .sch_input.sch_input2 {margin-right:1%}
/*
#new_sch select {float:left;border:1px solid #d0d3db;width:49.5%;height:45px;padding:0 5px;border-radius:0}
#new_sch select#gr_id {margin-right:1%}
*/
#new_sch .ipt_sch {clear:both;position:relative;padding-top:10px}
#new_sch .frm_input {border:1px solid #d0d3db;width:100%;height:45px;border-radius:0}
#new_sch .sch_wr {position:relative;display:inline-block}
#new_sch .btn_submit {position:absolute;top:10px;right:0;padding:0 10px;height:45px;width:45px;font-size:1.4em;font-weight:bold;color:#434a54;background:transparent}
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
.tbl_head01 {}
.tbl_head01 table {clear:both;width:100%;border-collapse:collapse;border-spacing:0}
.tbl_head01 thead th {background:#6f809a;color:#fff;border:1px solid #60718b;font-weight:bold;text-align:center;padding:8px 5px;font-size:0.8em}
.tbl_head01 thead th a{color:#fff}
.tbl_head01 thead input {vertical-align:top} /* middle 로 하면 게시판 읽기에서 목록 사용시 체크박스 라인 깨짐 */
.tbl_head01 thead a {color:#383838;text-decoration:underline}
.tbl_head01 tbody th{border:1px solid #d6dce7;padding:5px;text-align:center}
.tbl_head01 tbody td{border:1px solid #d6dce7;padding:5px;text-align:center}
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
</style>

<div id="bo_list">
	<div id="bo_li_op">



		<fieldset id="new_sch">
			<legend>상세검색</legend>
			<form name="fnew" method="get">

				<?php if($is_admin) { ?>
				<div style=" font-size:0.85em">
					<button type="submit" class="button" onclick="javascript:set_date('오늘');"><span>오늘</span></button>
					<button type="submit" class="button" onclick="javascript:set_date('어제');"><span>어제</span></button>
					<button type="submit" class="button" onclick="javascript:set_date('이번주');"><span>이번주</span></button>
					<button type="submit" class="button" onclick="javascript:set_date('지난주');"><span>지난주</span></button>
					<button type="submit" class="button" onclick="javascript:set_date('이번달');"><span>이번달</span></button>
					<button type="submit" class="button" onclick="javascript:set_date('지난달');"><span>지난달</span></button>
				</div>
				<?php } ?>
				<div class="ipt_sch">
					<input type="text" name="fr_date" required="" id="fr_date" class="sch_input sch_input2" size="10" value="<?php echo $fr_date; ?>" readonly style="text-align:center">
					<input type="text" name="to_date" required="" id="to_date" class="sch_input" size="10" value="<?php echo $to_date; ?>" readonly style="text-align:center">
				</div>
				<div class="ipt_sch">
					<label for="mb_id" class="sound_only">검색어<strong class="sound_only">필수</strong></label>
					<input name="mb_name" placeholder="업체명" id="stx" class="frm_input" size="15" maxlength="20" value="<?php echo $mb_name; ?>">
					<button type="submit" class="btn_submit"><i class="fa fa-search" aria-hidden="true"></i></button>
				</div>
			</form>
			<p>
				총<?php echo $total_count; ?>개의 업체가 등록되어 있습니다.
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
	<button id="btn_submit" accesskey="s" class="btn_submit" onclick="location.href='<?php echo G5_URL; ?>/member/add.php'" style="margin-bottom:10px;">회원등록</button>
	<div class="tbl_head01 tbl_wrap">
		<table>
			<thead>
			<tr>
				<th>그룹</th>
				<th>업체명</th>
				<th>결제</th>
				<th>취</th>
				<?php /*
				<th>TID</th>
				*/ ?>
				<th style="width:110px;">승인금액</th>
			</tr>
			</thead>
			<tbody>
			<?php
			for ($i=0; $row=sql_fetch_array($result); $i++) {
				$total_pay = 0;
				$sums = sql_fetch(" select sum(amount) as total_pay from pay_payment_passgo where (datetime BETWEEN '{$fr_date} 00:00:00' and '{$to_date} 23:59:59') and mb_id = '{$row['mb_id']}' and resultCd = '0000' and resultYN != '1' ");
				$total_pay = $sums['total_pay'];
				$member_total = $member_total + $total_pay;
				/*
				if($row['mb_6'] == "b") {
					$mb_6 = "<span style='background:gray;color:#fff;padding:4px 5px 2px;font-size:11px;'>비</span>";
				} else if($row['mb_6'] == "i") {
					$mb_6 = "<span style='background:blue;color:#fff;padding:4px 5px 2px;font-size:11px;'>인</span>";
				} else if($row['mb_6'] == "no") {
					$mb_6 = "<span style='background:red;color:#fff;padding:4px 5px 2px;font-size:11px;'>N</span>";
				} else {
					$mb_6 = "<span style='background:green;color:#fff;padding:4px 5px 2px;font-size:11px;'>모</span>";
				}
				*/
				echo $mb678 = "";

				if($row['mb_6'] == "1") {
					$mb_6 = "<span style='background:red;color:#fff;padding:4px 5px 2px;font-size:11px; margin:0 0.5px 0 0.5px;'>광</span>";
				} else {
					$mb_6 = "";
				}

				if($row['mb_15'] == "1") {
					$mb_15 = "<span style='background:#777;color:#fff;padding:4px 5px 2px;font-size:11px; margin:0 0.5px 0 0.5px;'>광</span>";
				} else {
					$mb_15 = "";
				}

				if($row['mb_7'] == "1") {
					$mb_7 = "<span style='background:blue;color:#fff;padding:4px 5px 2px;font-size:11px; margin:0 0.5px 0 0.5px;'>다</span>";
				} else {
					$mb_7 = "";
				}

				if($row['mb_8'] == "1") {
					$mb_8 = "<span style='background:#666;color:#fff;padding:4px 5px 2px;font-size:11px; margin:0 0.5px 0 0.5px;'>웰</span>";
				} else {
					$mb_8 = "";
				}

				if($row['mb_9'] == "1") {
					$mb_9 = "<span style='background:#D527B7;color:#fff;padding:4px 5px 2px;font-size:11px; margin:0 0.5px 0 0.5px;'>페</span>";
				} else {
					$mb_9 = "";
				}

				if($row['mb_20'] == "1") {
					$mb_20 = "<span style='background:brown;color:#fff;padding:4px 5px 2px;font-size:11px; margin:0 0.5px 0 0.5px;'>섹</span>";
				} else {
					$mb_20 = "";
				}

				if($row['mb_21'] == "1") {
					$mb_21 = "<span style='background:blue;color:#fff;padding:4px 5px 2px;font-size:11px; margin:0 0.5px 0 0.5px;'>섹</span>";
				} else {
					$mb_21 = "";
				}

				if($row['mb_14'] == "1") {
					$mb_14 = "<span style='background:green;color:#fff;padding:4px 5px 2px;font-size:11px; margin:0 0.5px 0 0.5px;'>U</span>";
				} else {
					$mb_14 = "";
				}

				if(!$row['mb_6']) {
					if(!$row['mb_7']) {
						if(!$row['mb_8']) {
							if(!$row['mb_9']) {
								if(!$row['mb_15']) {
									if(!$row['mb_20']) {
										if(!$row['mb_21']) {
											$mb678 = "<span style='color:red;padding:4px 5px 2px;font-size:11px;'>결제불가</span>";
										}
									}
								}
							}
						}
					}
				}

				if($row['mb_adult'] == "1") { $mb_adult = "<div style='color:#fff; background:red;'>X</div>"; } else { $mb_adult = "O"; }

//				if($row['mb_10'] == "10") { $row['mb_10'] = ""; }

			?>
			<tr onclick="opens('over_<?php echo $i; ?>');" id="overs" class="overs_tr tr_over_<?php echo $i; ?>">
				<td><?php echo $row['mb_10']; ?></td>
				<td style="width:33.3%; text-align:left"><?php echo $row['mb_nick']; ?></td>
				<td><?php echo $mb_6; ?><?php echo $mb_15; ?><?php echo $mb_7; ?><?php echo $mb_8; ?><?php echo $mb_9; ?><?php echo $mb_20; ?><?php echo $mb_21; ?><?php echo $mb_14; ?><?php echo $mb678; ?></td>
				<?php /*
				<td><?php if($row['mb_2']) { echo "<span style='background:blue;color:#fff;padding:2px 5px;font-size:11px;'>Y</span>"; } else { echo "<span style='background:red;color:#fff;padding:2px 5px;font-size:11px;'>N</span>"; } ?></td>
				*/ ?>
				<td style="width:5%"><?php echo $mb_adult; ?></td>
				<td style="text-align:right;width:30%"><?php echo number_format($sums['total_pay']); //date("Y-m-d", strtotime($row['mb_datetime'])); ?></td>
			</tr>
			<tr id="over_<?php echo $i; ?>" style="display:none;" class="overs">
				<td><i class="fa fa-arrow-right" aria-hidden="true"></i></td>
				<td colspan="<?php if($is_admin) { echo "5"; } else { echo "4"; } ?>" style="background:#444; color:#888; text-align:left;">
					<div style="width:100%; line-height:150%; padding:10px;" class="over_aad">
					- 워너페이 수기결제 접속정보 -<br>
					업체명 : <?php echo $row['mb_nick']; ?><br>
					아이디 : <?php echo $row['mb_id']; ?><br>
					비밀번호 : <?php echo $row['mb_1']; ?><br>
					접속주소 : http://pay.mpc.icu
					</div>

					<div class="over_aad" style="color:#ffff00">광원 인증 결제 : <?php if($row['mb_6'] == "1") { echo "가능"; } else { echo "불가능"; } ?></div>
					<?php if($row['mb_6'] == "1") { ?>
					<div class="over_aad">광원 인증 회원 TID : WNK<?php echo $row['mb_id']; ?></div>
					<div class="over_aad">광원 인증 TID : <?php echo $row['mb_2']; ?></div>
					<div class="over_aad">광원 인증 KEY : <span style="font-size:0.8em"><?php echo $row['mb_3']; ?></span></div>
					<?php } ?>

					<div class="over_aad" style="color:#ffff00">광원 비인증 결제 : <?php if($row['mb_15'] == "1") { echo "가능"; } else { echo "불가능"; } ?></div>
					<?php if($row['mb_15'] == "1") { ?>
					<div class="over_aad">광원 비인증 회원 TID : WNB<?php echo $row['mb_id']; ?></div>
					<div class="over_aad">광원 비인증 TID : <?php echo $row['mb_16']; ?></div>
					<div class="over_aad">광원 비인증 KEY : <span style="font-size:0.8em"><?php echo $row['mb_17']; ?></span></div>
					<?php } ?>

					<div class="over_aad" style="color:#ffff00">다날 결제 : <?php if($row['mb_7'] == "1") { echo "가능"; } else { echo "불가능"; } ?></div>
					<?php if($row['mb_7'] == "1") { ?>
					<div class="over_aad">다날 TID : <?php echo $row['mb_4']; ?></div>
					<div class="over_aad">다날 KEY : <span style="font-size:0.8em"><?php echo $row['mb_5']; ?></span></div>
					<?php } ?>

					<div class="over_aad" style="color:#ffff00">웰컴 결제 : <?php if($row['mb_8'] == "1") { echo "가능"; } else { echo "불가능"; } ?></div>
					<?php if($row['mb_8'] == "1") { ?>
					<div class="over_aad">웰컴 TID : WNA<?php echo $row['mb_id']; ?></div>
					<?php } ?>

					<div class="over_aad" style="color:#ffff00">페이시스 결제 : <?php if($row['mb_9'] == "1") { echo "가능"; } else { echo "불가능"; } ?></div>
					<?php if($row['mb_9'] == "1") { ?>
					<div class="over_aad">페이시스 TID : WNP<?php echo $row['mb_id']; ?></div>
					<div class="over_aad">페이시스 TID : <?php echo $row['mb_11']; ?></div>
					<div class="over_aad">페이시스 ID : <?php echo $row['mb_12']; ?></div>
					<div class="over_aad">페이시스 KEY : <span style="font-size:0.8em"><?php echo $row['mb_13']; ?></span></div>
					<?php } ?>

					<div class="over_aad" style="color:#ffff00">섹타나인 결제 : <?php if($row['mb_20'] == "1") { echo "가능"; } else { echo "불가능"; } ?></div>
					<?php if($row['mb_20'] == "1") { ?>
					<div class="over_aad">섹타나인 TID : WNS<?php echo $row['mb_id']; ?></div>
					<div class="over_aad">페이시스 ID : <?php echo $row['mb_18']; ?></div>
					<div class="over_aad">페이시스 KEY : <span style="font-size:0.8em"><?php echo $row['mb_19']; ?></span></div>
					<?php } ?>

					<div class="over_aad" style="color:#ffff00">섹타나인 비인증 결제 : <?php if($row['mb_21'] == "1") { echo "가능"; } else { echo "불가능"; } ?></div>
					<?php if($row['mb_21'] == "1") { ?>
					<div class="over_aad">섹타나인 TID : WNO<?php echo $row['mb_id']; ?></div>
					<div class="over_aad">페이시스 ID : <?php echo $row['mb_22']; ?></div>
					<div class="over_aad">페이시스 KEY : <span style="font-size:0.8em"><?php echo $row['mb_23']; ?></span></div>
					<?php } ?>

					<div class="over_aad"><a href="<?php echo G5_URL; ?>/member/add.php?mb_id=<?php echo $row['mb_id']; ?>&w=u" style="font-weight:bold; color:#ffff00">★ 정보수정</a></div>
					<div><a href="<?php echo $PHP_SELF; ?>?proc_mode=login&login_us_id=<?=$row['mb_id']?>" style="font-weight:bold; color:#ffff00">★ 로그인</a></div>
				</td>
			</tr>
			<tbody>
			<?php
				$sums['total_pay'] = 0;
				}
				$sums['total_pay'] = 0;
			?>
			<tfoot>
			<tr>
				<th colspan="4">합계</th>
				<th style="text-align:right"><?php echo number_format($member_total); ?></th>
			</tr>
			<tfoot>
		</table>
	</div>
	<?php /*
	<div id="fnewlist" class="new_list">
		<ul>
			<?php
			for ($i=0; $row=sql_fetch_array($result); $i++) {
				$s_vie = '<a href="./mail_preview.php?ma_id='.$row['ma_id'].'" target="_blank" class="btn btn_03">미리보기</a>';
				$num = number_format($total_count - ($page - 1) * $config['cf_page_rows'] - $i);
				$bg = 'bg'.($i%2);
				
				$receipt = substr($row['creates'],0,8)."/".$row['authCd'];

				if($row['resultYN'] == "1") {
					$pride = "<del>".number_format($row['amount'])."원</del>";
					$can_btn = "<span style='background:#AAA; font-size:0.8em; padding:3px 4px 2px 4px'> 취소완료</span>";
				} else {
					$pride = number_format($row['amount'])."원";
					if(date("Y-m-d") == date("Y-m-d", strtotime($row['creates']))) {

						$row_cancel = sql_fetch(" select count(pm_id) as pm_count from g5_payment where mb_id = '{$row['mb_id']}' and authCd = '{$row['authCd']}' and bin = '{$row['bin']}' and advanceMsg = '정상취소' ");
						// select * from g5_payment where mb_id = 'sss1234' and authCd = '54807295' and bin = '488972' and advanceMsg = '정상취소'

						if($row_cancel['pm_count']) {
							$can_btn = "<a href='javascript:alert(\"이미 취소 완료되었습니다..\");' class=' btn_b03' style='background:#dedede;padding: 0 12px;  height: 23px; line-height: 24px;'>취소완료</a>";
						} else {
							$can_btn = "<a href='./cancel.php?id=".$row['trxId']."' onclick='win_card_cancel(this.href); return false;' style='background:red; color:#fff; font-size:0.8em; padding:3px 4px 2px 4px'>승인취소</a>";
						}

					} else {
						$can_btn = "<a href='javascript:alert(\"승인 당일에만 취소 가능합니다.\");' style='background:#EEE; font-size:0.8em; padding:3px 4px 2px 4px'>취소불가</a>";
					}
				}

				$mb = get_member($row['mb_id']);
				//G5_TIME_YMD
				//
				if($is_admin) {
					$receipt_txt =  $mb['mb_name'];
				} else if($member['mb_id'] == "wpay") {
					$receipt_txt =  $mb['mb_name'];
				} else {
					$receipt_txt =  영수증;
				}

				if($row['advanceMsg'] == "정상승인") {
					$advanceMsg = "<span style='color:blue;'>승인</span>";
				} else if($row['advanceMsg'] == "정상취소") {
					$advanceMsg = "<span style='color:red;'>취소</span>";
				}

			?>
			<li>
				<span class="sv_wrap">
					<a href="#a" class="sv_member" target="_blank" rel="nofollow" onclick="return false;">
						<span class="new_name"><?php echo $row['mb_name']; ?></span>
						<span class="new_board"><?php echo $row['authCd']; ?></span>
					</a>
					<span class="sv">
						<a>승인번호 : <?php echo $row['authCd']; ?></a>
						<a>결제상태 : <?php echo $row['advanceMsg']; ?></a>
						<a>결제자명 : <?php echo $row['payerName']; ?></a>
						<a>휴대전화 : <?php echo $row['payerTel']; ?></a>
						<a>결제상품 : <?php echo $row['descs']; ?></a>
						<a>현재상태 : <?php echo $advanceMsg; ?></a>
						<a>할부정보 : <?php if($row['installment'] < 1) { echo "일시불"; } else { echo $row['installment']."개월"; } ?></a>
						<a href='<?php echo G5_URL; ?>/passgo/receipt.php?id=<?php echo $row['trxId']; ?>&receipt=<?php echo $receipt; ?>' onclick="win_receipt(this.href, <?php echo $row['authCd']; ?>); return false;">영수증</a>
					</span>
				</span>
				<span class="new_tit"><?php echo $advanceMsg; ?></span>
				<span class="new_date2"><?php echo date("Y-m-d H:i:s", strtotime($row['creates'])); ?></span>
				<span class="new_date"><?php echo $can_btn; ?></span>
				<span class="new_tit"><?php echo $pride; ?></span>
			</li>
			<?php
				}
				if($i == 0) {
			?>
				<td colspan="<?=$is_admin?'11':'10';?>" style="height:200px">검색하신 날짜에 결제내역이 없습니다.</td>
			<?php } ?>
		</ul>
	</div>
	*/ ?>



</div>

<script>

$(function(){
	$("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });
});

$("#fr_date, #to_date, #sfl, #mb_id").change(function() {
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