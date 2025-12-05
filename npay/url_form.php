<?php
//	$config['cf_6'] = 5000000;
	/*
	if($member['mb_7'] <> 1) {
		alert_close("권한이 없습니다.");
	}
	*/
	/*
	if($is_admin) {
		$payerName = "홍길동";
		$payerTel2 = "8350";
		$payerTel3 = "3122";
		$number = 9425207804101664;
		$expiry2 = "04";
		$expiry1 = "24";
		$installment = "A";
		$pd_name = "테스트";
		$pd_price = 1004;
	}

	$mb_pass = rand(11111,999999);

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
	*/
	$urlcode = md5($member['mb_id'].time());
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
		if ($('#url_pd').val()=='') {
			alert('상품명을 입력해 주세요');
			$('#url_pd').get(0).focus();
			return;
		}
		if ($('#url_price').val() == '') {
			alert('상품가격을 입력해 주세요.');
			$('#url_price').get(0).focus();
			return;
		}
		if ($('#url_pname').val() == '') {
			alert('판매자명 또는 업체명을 입력해 주세요.');
			$('#url_pname').get(0).focus();
			return;
		}
		if ($('#url_ptel').val() == '') {
			alert('판매자 연락처를 입력해 주세요.');
			$('#url_ptel').get(0).focus();
			return;
		}
		if ($('#url_gname').val() == '') {
			alert('구매자명을 입력해 주세요.');
			$('#url_gname').get(0).focus();
			return;
		}
		if ($('#url_gtel').val() == '') {
			alert('구매자 연락처를 입력해 주세요.');
			$('#url_gtel').get(0).focus();
			return;
		}
		if ($('#pg_id').val()=='') {
			alert('결제할 PG사를 선택해 주세요');
			$('#pg_id').get(0).focus();
			return;
		}
		if (!$('#agreement1').prop('checked')) {
			alert('약관에 동의하셔야 가능합니다.');
			return;
		}
		/*
		if (f.file_file.value != "")
			if (!attachfile_check(f.file_file.value, "지원하지 않는 파일형식이므로 첨부하실수없습니다.")) return;
		f.target = '_writeFrame';
		*/
		f.submit();
	}
</script>

<section class="container" id="bbs">
	<section class="contents contents-bbs">
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
					<form name="insert_bbs_form" method="post" action="./?p=url_form_update" onsubmit="return false;">
						<fieldset>
							<input type="hidden" name="mb_id" value="<?php echo $member['mb_id']; ?>">
							<input type="hidden" name="urlcode" value="<?php echo $urlcode; ?>">

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">상품명 <span style='color:red;font-size:1.1rem;font-weight:100'>필수</span></label>
								</div>
								<div class="panel-body">
									<input type="text" class="form-control" name="url_pd" id="url_pd" placeholder="상품명" value="<?php echo $row['url_pd']; ?>" required>
								</div>
							</div>
							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">상품가격 <span style='color:red;font-size:1.1rem;font-weight:100'>필수</span></label>
								</div>
								<div class="panel-body">
									<input type="text" class="form-control" name="url_price" id="url_price" placeholder="상품가격" value="<?php echo $row['url_price']; ?>" required>
								</div>
							</div>
							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">판매자명(업체명) <span style='color:red;font-size:1.1rem;font-weight:100'>필수</span></label>
								</div>
								<div class="panel-body">
									<input type="text" class="form-control" name="url_pname" id="url_pname" placeholder="판매자명" value="<?php echo $row['url_pname']; ?>" required>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">판매자 연락처 <span style='color:red;font-size:1.1rem;font-weight:100'>필수</span></label>
								</div>
								<div class="panel-body">
									<input type="text" name="url_ptel" id="url_ptel" class="form-control" maxlength="20" placeholder="판매자 연락처" value="<?php echo $row['url_ptel']; ?>" required>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">구매자명 <span style='color:red;font-size:1.1rem;font-weight:100'>필수</span></label>
								</div>
								<div class="panel-body">
									<input type="text" name="url_gname" id="url_gname" class="form-control" placeholder="구매자명" value="<?php echo $row['url_gname']; ?>" required>
								</div>
							</div>


							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">구매자 연락처 <span style='color:red;font-size:1.1rem;font-weight:100'>필수</span></label>
								</div>
								<div class="panel-body">
									<input type="text" name="url_gtel" id="url_gtel" class="form-control" placeholder="구매자 연락처" value="<?php echo $row['url_gtel']; ?>" required>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">결제PG <span style='color:red;font-size:1.1rem;font-weight:100'>필수</span></label>
								</div>
								<div class="panel-body">
									<select name="pg_id" id="pg_id" class="form-control">
										<option value="">선택하세요</option>
										<?php
											$sql_pg = " select * from pay_member_pg where mb_id = '{$member['mb_id']}' and pg_use = '0'";
											$result_pg = sql_query($sql_pg);
											for ($i=0; $row_pg=sql_fetch_array($result_pg); $i++) {
												$pg_id = $row_pg['pg_id'];
												$pg_name = $row_pg['pg_name'];
												$pg_pay = $row_pg['pg_pay'];
												$pg_hal = $row_pg['pg_hal'];

										?>
										<option value="<?php echo $pg_id; ?>"><?php echo $pg_name; ?> / <?php echo number_format($pg_pay); ?> / <?php echo $pg_hal; ?>개월</option>
										<?php } ?>
									</select>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label class="tit">약관동의 <span style='color:red;font-size:1.1rem;font-weight:100'>필수</span></label>
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
							
						</fieldset>
					</form>
				</div>
				<div class="btn-center-block">
					<div class="btn-group btn-horizon">
						<div class="btn-table">
							<a class="btn btn-black btn-cell" onclick="insert_bbs()">등록</a>
							<a class="btn btn-black-line btn-cell" onclick="go_cancel();">취소</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</section>