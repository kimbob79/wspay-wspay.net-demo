<?php
	include("./_common.php");

	if(!$url_code) {
		if($mb_id != $member['mb_id']) {
			alert("로그인한 사람과 결제자가 다릅니다.");
			exit;
		}
	}
	$mb = get_member($mb_id);
	$member_pg = sql_fetch(" select * from pay_member_pg where mb_id = '{$mb_id}' and pg_id = '{$pg_id}' ");
	if($member_pg['pg_use'] == '1') {
		alert_close("사용할 수 없는 결제모듈입니다.");
	}

	include_once("./_head.php");

	$mb_pass = rand(11111,999999);

	if($member['mb_id']) {
		if(!$is_admin) {
			if(!$mb_id) {
				$w = "u";
				$mb_id = $member['mb_id'];
			}
		}
	}

	if($mb_id) {
		$mb = get_member($mb_id);
		$mb_nick = $mb['mb_nick'];
		$mb_name = $mb['mb_name'];
		$mb_hp =explode('-' , $mb['mb_hp']);
		if(!$mb['mb_id']) {
			alert("잘못된 접근입니다.", G5_URL);
		}
		if(!$is_admin) {
			if($member['mb_id'] != $mb['mb_id']) {
				alert("잘못된 접근입니다.", G5_URL);
			}
		}
	}
?>

<script>
	function attachfile_check(value_str, msg) {
		if (value_str == "") return true;
		var rtn = false;
		var re = new RegExp(".(png|jpg|gif|ppt|doc|xls|hwp|pdf|zip|txt|docx|xlsx|pptx)$", "gi");
		rtn = !!value_str.match(re);
		if (!rtn) {
			alert(msg);
		}
		return rtn;
	}

	function insert_bbs() {
		var f = document.insert_bbs_form;

		if ($('#pay_product').val()=='') {
			alert('상품명을 입력하세요');
			$('#pay_product').get(0).focus();
			return;
		}
		if ($('#pay_price').val() == '') {
			alert('결제금액을 입력하세요');
			$('#pay_price').get(0).focus();
			return;
		}
		if ($('#pay_phone').val() == '') {
			alert('휴대전화번호를 입력하세요');
			$('#pay_phone').get(0).focus();
			return;
		}
		if ($('#pay_cardnum').val() == '') {
			alert('카드번호를 입력하세요');
			$('#pay_cardnum').get(0).focus();
			return;
		}
		if ($('#installment').val() == '') {
			alert('할부를 선택해주세요');
			$('#installment').get(0).focus();
			return;
		}
		if ($('#expMM').val() == '') {
			alert('유효기간 월을 선택해주세요');
			$('#expMM').get(0).focus();
			return;
		}
		if ($('#expYY').val() == '') {
			alert('유효기간 년도를 선택해주세요');
			$('#expYY').get(0).focus();
			return;
		}
		if ($('#expYY').val() == '') {
			alert('유효기간을 선택해주세요');
			$('#expYY').get(0).focus();
			return;
		}
		if ($('#pay_password').val() == '') {
			alert('비밀번호 앞 2자리를 입력해주세요');
			$('#pay_password').get(0).focus();
			return;
		}
		if ($('#pay_certify').val() == '') {
			alert('본인확인정보를 입력해주세요');
			$('#pay_certify').get(0).focus();
			return;
		}
		if (!$('#agreement1').prop('checked')) {
			alert('약관에 동의하셔야 가능합니다.');
			return;
		}

		if (f.file_file.value != "")
			if (!attachfile_check(f.file_file.value, "지원하지 않는 파일형식이므로 첨부하실수없습니다.")) return;
		f.target = '_writeFrame';

		f.submit();
	}
	function go_close() {
		window.close();
	}
	
</script>
	<style>
		table.table_pg {
			width:100%;
			border-collapse: collapse;
			text-align: left;
			line-height: 1.5;
			border: 1px solid #ddd;
			border-top: 1px solid #111;

		}
		table.table_pg th {
			width: 20%;
			padding: 10px;
			font-weight: bold;
			color: #369;
			border-bottom: 1px solid #ddd;
			background: #f3f6f7;
		}
		table.table_pg td {
			width: 30%;
			padding: 10px;
			border-bottom: 1px solid #ddd;
		}
	</style>
<section class="container" id="bbs">
	<section class="contents contents-bbs">
		<div class="top">
			<div class="inner">
				<table class="table_pg">
					<tr>
						<th>결제</th>
						<td><?php echo $member_pg['pg_name']; ?></td>
						<th>가맹점</th>
						<td><?php echo $mb['mb_nick']; ?></td>
					</tr>
					<tr>
						<th>업종</th>
						<td><?php echo $mb['mb_1']; ?></td>
						<th>업태</th>
						<td><?php echo $mb['mb_2']; ?></td>
					</tr>
					<tr>
						<th>한도</th>
						<td><?php echo number_format($member_pg['pg_pay']); ?>원</td>
						<th>할부</th>
						<td><?php if($member_pg['pg_hal'] == 0) { echo "일시불"; } else { echo $member_pg['pg_hal']."개월"; } ?></td>
					</tr>
				</table>

			</div>
		</div>
		<div class="bbs-cont">
			<div class="inner">
				<div class="write-form">
					<?php /*
					<ul class="keyword tab-menu">
						<li class="active" rel="tab1"><a href="#">판매자 회원이신 경우</a></li>
						<li rel="tab2"><a href="#">결제고객(구매자)이신 경우</a></li>
					</ul>
					<div class="noti-box">
						<h4>문의 전 안내사항<span class="view-btn"><em class="open-btn">보기</em><em class="close-btn">닫기</em></span></h4>
						<ul class="tab-content" id="tab1">
							<li>
							<span class="quest">가입 후 해야할 일이 있나요?</span>
							<p class="reply">
								가입 후 결제 사용은 바로 가능하나, 계약서 및 구비서류를 발송해주셔야 정산이 가능합니다.<br>
								계약서는 <b class="text-red">페이앱 홈페이지 > 이용안내 > 구비서류/심사</b>페이지에서 출력할 수 있으며, 작성 날인 하여 <b class="text-underline">payapp@udid.co.kr</b> 이메일로 접수해주시기 바랍니다.
							</p>
							</li>
							<li>
							<span class="quest">계약서를 이메일로 발송했는데 언제 처리되나요?</span>
							<p class="reply">
								계약서는 발송해주신 순서대로 서류심사팀에서 확인되어 완료까지는 <b><span class="text-red">최대 일주일</span> 정도 소요</b>될 수 있습니다. 완료 또는 수정 사항이 있을 경우 유선이나 문자, 메일로 연락드리니 조금만 기다려 주시기 바랍니다.
							</p>
							</li>
							<li>
							<span class="quest">정산은 언제 되나요?</span>
							<p class="reply">
								<b>결제일로부터 <span class="text-red">D+5</span></b>(영업일기준) 가 정산일 입니다. 계약서 완료 후 정산이 가능하니 참고하시기 바랍니다.
							</p>
							</li>
							<li>
							<span class="quest">왜 정산 불가인가요?</span>
							<p class="reply">
								계약서가 완료되지 않았거나, 보증보험 증권 등 여러가지 사유가 있을 수 있기 때문에 오른쪽 아래 <b class="text-red">채팅상담</b>으로 문의주시거나, <b class="text-red">1800-3772</b>로 연락하시는 게 가장 빠르고 정확합니다.
							</p>
							</li>
						</ul>
						<!-- //판매자 회원인 경우 -->
						<ul class="tab-content" id="tab2">
							<li>
							<span class="quest">판매자와 연락이 안 됩니다. 취소하고 싶은데 민원을 어떻게 접수하죠?</span>
							<span class="quest">판매자와 결제 취소에 대한 분쟁이 있습니다. 민원을 어떻게 접수하죠?</span>
							<p class="reply reply-lg">
								<b><a class="text-orange" href="/popup_pay_new/popup/pay_view1.html" onclick="return openWindow(this, {name:'payappHistory',width:420,height:630,center:true,scrollbars:true})">결제 내역 조회</a></b> 메뉴에서 내역 조회가 가능하며 결제 내역 조회 결과 화면에 표시되는 <b class="text-red">[민원접수] 기능으로 온라인 민원신청이 가능</b>합니다.<br>
								 온라인 민원 신청 시 페이앱 접수와 판매점에 대한 통보가 자동으로 이뤄지며, 페이앱 담당자가 지속 체크하여 민원 해결을 적극적으로 돕습니다.
							</p>
							</li>
						</ul>
						<!-- //구매자인 경우 -->
					</div>
					<!-- //tab-menu -->
					*/ ?>
					<form name="insert_bbs_form" method=post enctype="multipart/form-data" action="./payment_update.php" onsubmit="return false;">
						<fieldset>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">상품명</label>
								</div>
								<div class="panel-body">
									<input type="text" name="pay_product" id="pay_product" class="form-control" maxlength="20" placeholder="상품명" value="" required>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">결제금액</label>
								</div>
								<div class="panel-body">
									<input type="text" name="pay_price" id="pay_price" class="form-control" maxlength="20" placeholder="결제금액" value="" required>
								</div>
							</div>


							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">휴대전화번호</label>
								</div>
								<div class="panel-body">
									<input type="text" name="pay_phone" id="pay_phone" class="form-control" placeholder="휴대전화번호" value="" required>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">카드번호</label>
								</div>
								<div class="panel-body">
									<input type="text" name="pay_cardnum" id="pay_cardnum" class="form-control" placeholder="카드번호" value="" required>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">할부선택</label>
								</div>
								<div class="panel-body">
									<select name="installment" id="installment" class="form-control">
										<option value="">할부선택</option>
										<option value="0">일시불</option>
										<?php for($h=2; $h<=$member_pg['pg_hal']; $h++) { ?>
										<option value="<?php echo $h; ?>"><?php echo $h; ?>개월</option>
										<?php } ?>
									</select>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">유효기간</label>
								</div>
								<div class="panel-body1">
									<select name="expMM" id="expMM" class="form-control">
										<option value="">월선택</option>
										<option value="01">01월</option>
										<option value="02">02월</option>
										<option value="03">03월</option>
										<option value="04">04월</option>
										<option value="05">05월</option>
										<option value="06">06월</option>
										<option value="07">07월</option>
										<option value="08">08월</option>
										<option value="09">09월</option>
										<option value="10">10월</option>
										<option value="11">11월</option>
										<option value="12">12월</option>
									</select>
								</div>
								<div class="panel-body2">
									<select name="expYY" id="expYY" class="form-control">
										<option value="">년선택</option>
										<option value="24">2024년</option>
										<option value="25">2025년</option>
										<option value="26">2026년</option>
										<option value="27">2027년</option>
										<option value="28">2028년</option>
										<option value="29">2029년</option>
										<option value="30">2030년</option>
										<option value="31">2031년</option>
										<option value="32">2032년</option>
										<option value="33">2033년</option>
										<option value="34">2034년</option>
									</select>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">비밀번호</label>
								</div>
								<div class="panel-body">
									<input type="text" name="pay_password" id="pay_password" class="form-control" maxlength="20" placeholder="비밀번호 앞2자리" value="" required>
								</div>
								<div class="comments">* 카드 비밀번호 앞2자리만 입력</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">본인확인</label>
								</div>
								<div class="panel-body">
									<input type="text" name="pay_certify" id="pay_certify" class="form-control" placeholder="본인확인" value="" required>
								</div>
								<div class="comments">* 개인카드 : 생년월일 6자리<br>* 법인카드 : 사업자번호 10자리</div>
							</div>




							<?php /*
							<div class="panel">
								<div class="panel-heading">
									<label class="tit">약관동의</label>
								</div>
								<div class="panel-body">
									<div class="terms-box">
										<p>
											결제의 모든 책임은 결제자에게 있습니다.
										</p>
										<ol>
											<li>
											1. 수집하는 개인정보 항목<br>
											 필수정보 : 작성자(이름), 비밀번호, 상담내용 </li>
											<li>
											2. 개인정보 수집목적<br>
											 온라인 문의상담 </li>
											<li class="text-bold">
											3. 개인정보 보유 및 이용기간<br>
											<strong>
											개인정보의 수집목적이 달성되면 지체없이 파기 합니다.<br>
											 단, 관계법령에 따라 일정기간 정보의 보관을 규정하는 경우는 아래와 같습니다.<br>
											 이 기간 동안 법령의 규정(소비자의 불만 또는 분쟁처리에 관한 기록 : 3년)에 따라<br>
											 개인정보를 보관하며, 본 정보를 다른 목적으로 절대 이용하지 않습니다. </strong>
											</li>
										</ol>
										 ※개인정보 수집에 동의하지 않으실 수 있으며, 동의하지 않으실 경우 게시글 등록이 제한됩니다.
									</div>
									<div style="margin-top:1rem">
										<label class="input-chk">
										<input type="checkbox" name="agreement1" id="agreement1" value="1">
										<span>약관에 동의 합니다.</span>
										</label>
									</div>
								</div>
							</div>
							*/ ?>
						</fieldset>
					</form>
				</div>
				<div class="btn-center-block">
					<div class="btn-group btn-horizon">
						<div class="btn-table">
							<a class="btn btn-black btn-cell" onclick="insert_bbs()">결제</a>
							<a class="btn btn-black-line btn-cell" onclick="go_close();">취소</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</section>
<?php
	include_once("./_foot.php");
?>