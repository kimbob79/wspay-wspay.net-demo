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
	*/

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
		/*
		if ($('#bbsType').val()=='') {
			alert('문의 유형을 선택하세요.');
			$('#bbsType').get(0).focus();
			return;
		}
		if ($('#usernm').val() == '') {
			alert('작성자를 입력해 주세요.');
			$('#usernm').get(0).focus();
			return;
		}
		if ($('#title').val() == '') {
			alert('제목을 입력해 주세요.');
			$('#title').get(0).focus();
			return;
		}
		if ($('#contents').val() == '') {
			alert('내용을 입력해 주세요.');
			$('#contents').get(0).focus();
			return;
		}
		if ($('#pass').val() == '') {
			alert('비밀번호를 입력해 주세요.');
			$('#pass').get(0).focus();
			return;
		}
		if ($('#captcha_code').val() == '') {
			alert('보안문자를 입력해 주세요.');
			$('#captcha_code').get(0).focus();
			return;
		}
		if (!$('#agreement1').prop('checked')) {
			alert('약관에 동의하셔야 가능합니다.');
			return;
		}

		if (f.file_file.value != "")
			if (!attachfile_check(f.file_file.value, "지원하지 않는 파일형식이므로 첨부하실수없습니다.")) return;
		f.target = '_writeFrame';
		*/
		f.submit();
	}
</script>
<section class="container" id="bbs">
	<section class="contents contents-bbs">
		<div class="top">
			<div class="inner">
				<form name="srch">
					<input type="hidden" name="p" value="<?php echo $p; ?>">
					<fieldset>
					<div class="top-search">
						<div style="margin-bottom:.5rem">
								<button type="submit" class="btn btn-<?php if($dates == "t") { echo "black"; } else { echo "date-line"; } ?> btn-sm modal-trigger" onclick="javascript:set_date('오늘');" name="dates" value="t"><span>오늘</span></button>
								<button type="submit" class="btn btn-<?php if($dates == "y") { echo "black"; } else { echo "date-line"; } ?> btn-sm modal-trigger" onclick="javascript:set_date('어제');" name="dates" value="y"><span>어제</span></button>
								<?php /*
								<button type="submit" class="btn btn-black btn-sm modal-trigger" onclick="javascript:set_date('이번주');"><span>이번주</span></button>
								<button type="submit" class="btn btn-black btn-sm modal-trigger" onclick="javascript:set_date('지난주');"><span>지난주</span></button>
								*/ ?>
								<button type="submit" class="btn btn-<?php if($dates == "tm") { echo "black"; } else { echo "date-line"; } ?> btn-sm modal-trigger" onclick="javascript:set_date('이번달');" name="dates" value="tm"><span>이번달</span></button>
								<button type="submit" class="btn btn-<?php if($dates == "ym") { echo "black"; } else { echo "date-line"; } ?> btn-sm modal-trigger" onclick="javascript:set_date('지난달');" name="dates" value="ym"><span>지난달</span></button>
						</div>
					</div>
					<div class="top-search pull-left">
						<div class="search-wrap">
							<select class="form-control select2" name="pay_pg" id="pay_pg">
								<option value="">전체PG</option>
								<?php
									$sql_pg = " select * from pay_pg order by pg_sort asc ";
									$result_pg = sql_query($sql_pg);
									for ($k=0; $row_pg=sql_fetch_array($result_pg); $k++) {
								?>
								<option value="<?php echo $row_pg['pg_id']; ?>" <?php if($pay_pg == $row_pg['pg_id']) { echo "selected"; } ?>><?php echo $row_pg['pg_name']; ?></option>
								<?php
									}
								?>
							</select>
							<input type="text" class="form-control date" placeholder="검색어를 입력하세요." name="fr_date" id="fr_date" value="<?php echo $fr_date; ?>">
							<input type="text" class="form-control date" placeholder="검색어를 입력하세요." name="to_date" id="to_date" value="<?php echo $to_date; ?>">
						</div>
					</div>
					<div class="top-search pull-right">
						<div class="search-wrap">
							<select class="form-control" name="sfl" id="sfl">
								<option value="mb_nick" <?php if($sfl == "mb_nick") { echo "selected"; } ?>>업체명</option>
								<option value="mb_name" <?php if($sfl == "mb_name") { echo "selected"; } ?>>담당자</option>
								<option value="mb_hp" <?php if($sfl == "mb_hp") { echo "selected"; } ?>>휴대전화</option>
							</select>
							<input type="text" class="form-control" placeholder="검색어를 입력하세요." name="stx" id="stx" value="">
							<button type="submit" class="search-btn"></button>
						</div>
					</div>
					</fieldset>
				</form>
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
					<form name="insert_bbs_form" method=post enctype="multipart/form-data" action="./?p=member_form_update" onsubmit="return false;">
						<fieldset>
						<input type="hidden" name="w" value="<?php echo $w; ?>">
						<?php if($is_admin) { ?>
						<input type="hidden" name="mb_level" value="<?php echo $mb['mb_level']; ?>">
						<?php } ?>
							<?php if($is_admin) { ?>
							<div class="panel">
								<div class="panel-heading">
									<label for="" class="tit">운영여부</label>
								</div>
								<div class="panel-body">
									<select name="mb_level" id="mb_level" class="form-control">
										<option value="1" <?php if($mb['mb_level'] == "1") { echo "selected"; } ?>>정지</option>
										<option value="3" <?php if($mb['mb_level'] == "3") { echo "selected"; } ?>>운영</option>
									</select>
								</div>
							</div>
							<?php } else { ?>
							<input type="hidden" name="mb_level" value="3">
							<?php } ?>

							<?php if($mb_id) { ?>
							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">비밀번호1</label>
								</div>
								<div class="panel-body">
									<input type="hidden" name="mb_id" id="mb_id" class="form-control" maxlength="20" placeholder="비밀번호" value="<?php echo $mb['mb_id']; ?>" required>
									<input type="text" name="mb_password" id="mb_password" class="form-control" maxlength="20" placeholder="입력후 저장하시면 비밀번호가 변경됩니다.">
								</div>
							</div>
							<?php } else { ?>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">아이디 <span style='color:red;font-size:1.1rem;font-weight:100'>필수</span></label>
								</div>
								<div class="panel-body">
									<input type="text" name="mb_id" id="mb_id" class="form-control" maxlength="20" placeholder="아이디" value="<?php echo time(); ?>" required>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">비밀번호2 <span style='color:red;font-size:1.1rem;font-weight:100'>필수</span></label>
								</div>
								<div class="panel-body">
									<input type="text" name="mb_password" id="mb_password" class="form-control" maxlength="20" placeholder="비밀번호" value="<?php echo $mb_pass; ?>" required>
								</div>
							</div>
							<?php } ?>


							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">업체명 <span style='color:red;font-size:1.1rem;font-weight:100'>필수</span></label>
								</div>
								<div class="panel-body">
									<input type="text" class="form-control" name="mb_nick" id="mb_nick" placeholder="업체명" value="<?php echo $mb_nick; ?>" required <?php if(!$is_admin) { ?><?php if($w == "u") { echo "readonly"; } ?><?php } ?>>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">업종 <span style='color:red;font-size:1.1rem;font-weight:100'>필수</span></label>
								</div>
								<div class="panel-body">
									<input type="text" class="form-control" name="mb_email_certify2" id="mb_email_certify2" placeholder="업종 : 제조업, 건설업, 도매, 소매등" value="<?php echo $mb['mb_email_certify2']; ?>" required <?php if(!$is_admin) { ?><?php if($w == "u") { echo "readonly"; } ?><?php } ?>>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">종목 <span style='color:red;font-size:1.1rem;font-weight:100'>필수</span></label>
								</div>
								<div class="panel-body">
									<input type="text" class="form-control" name="mb_lost_certify" id="mb_lost_certify" placeholder="종목 : 인테리어, 육가공 등" value="<?php echo $mb['mb_lost_certify']; ?>" required <?php if(!$is_admin) { ?><?php if($w == "u") { echo "readonly"; } ?><?php } ?>>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">담당자 <span style='color:red;font-size:1.1rem;font-weight:100'>필수</span></label>
								</div>
								<div class="panel-body">
									<input type="text" name="mb_name" id="mb_name" class="form-control" maxlength="20" placeholder="담당자명" value="<?php echo $mb_name; ?>" required <?php if(!$is_admin) { ?><?php if($w == "u") { echo "readonly"; } ?><?php } ?>>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">휴대전화번호 <span style='color:red;font-size:1.1rem;font-weight:100'>필수</span></label>
								</div>
								<div class="panel-body">
									<input type="text" name="mb_hp" id="mb_hp" class="form-control" placeholder="휴대폰" value="<?php echo $mb['mb_hp']; ?>" required <?php if(!$is_admin) { ?><?php if($w == "u") { echo "readonly"; } ?><?php } ?>>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">사업자등록 사본 <span style='color:red;font-size:1.1rem;font-weight:100'>필수</span></label>
								</div>
								<div class="panel-body">
									<input type="file" name="mb_icon" id="mb_icon">
									<?php
									$mb_dir = substr($mb['mb_id'], 0, 2);
									$icon_file = G5_DATA_PATH . '/member/' . $mb_dir . '/' . get_mb_icon_name($mb['mb_id']) . '.gif';
									if (file_exists($icon_file)) {
										$icon_url = str_replace(G5_DATA_PATH, G5_DATA_URL, $icon_file);
										$icon_filemtile = (defined('G5_USE_MEMBER_IMAGE_FILETIME') && G5_USE_MEMBER_IMAGE_FILETIME) ? '?' . filemtime($icon_file) : '';
										echo '<img src="' . $icon_url . $icon_filemtile . '" alt="">';
										echo '<br><label for="del_mb_icon" class="input-chk"><input type="checkbox" id="del_mb_icon" name="del_mb_icon" value="1" data-gtm-form-interact-field-id="1"><span><span class="text-red necessary">삭제</span></span></label>';
									}
									?>
								</div>
							</div>
							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">정산계좌 사본 <span style='color:red;font-size:1.1rem;font-weight:100'>필수</span></label>
								</div>
								<div class="panel-body">
									<input type="file" name="mb_img" id="mb_img">
									<?php
									$mb_dir = substr($mb['mb_id'], 0, 2);
									$icon_file = G5_DATA_PATH . '/member_image/' . $mb_dir . '/' . get_mb_icon_name($mb['mb_id']) . '.gif';
									if (file_exists($icon_file)) {
										echo get_member_profile_img($mb['mb_id']);
										echo '<br><label for="del_mb_img" class="input-chk"><input type="checkbox" id="del_mb_img" name="del_mb_img" value="1" data-gtm-form-interact-field-id="1"><span><span class="text-red necessary">삭제</span></span></label>';
									}
									?>
								</div>
							</div>



							<?php
								if($member['mb_level'] > 3) {
							?>
							<style>
								.container{width:100%;margin:0 auto;overflow:hidden;}
								.gallery{margin:5px 0 0 0;box-sizing:border-box;}
								.gallery li{width:31%;float:left;box-sizing:border-box;padding:10px;margin:5px; background:#fff; border:1px solid #aaa;}

								@media (max-width:1200px){
									.container .gallery li{width:33.33333%;}
								}
								@media (max-width:768px){
									.container .gallery li{width:33.33333%;}
								}
								@media (max-width:560px){
									.container .gallery li{width:50%;}
								}
								@media (max-width:480px){
									.container .gallery li{width:100%;}
								}
							</style>

							<div style="margin-top:20px; padding-bottom:20px; border-top:1px solid #ddd;"></div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">PG사 설정</label>
								</div>
								<div class="panel-body">
									<div class="container">
										<ul class="gallery">
										<?php
											$sql_pg = " select * from pay_pg where pg_use = '0' order by pg_sort asc ";
											$result_pg = sql_query($sql_pg);
											for ($i=0; $row_pg=sql_fetch_array($result_pg); $i++) {
												$pg_id = $row_pg['pg_id'];
												$member_pg = sql_fetch(" select * from pay_member_pg where pg_mid = '{$pg_id}' and mb_id = '{$mb['mb_id']}' ");
												$is_used = $member_pg['pg_use'] == "0";
												$display_style = $is_used ? 'block' : 'none';

										?>
											<li>
												<div style="background:#000; color:#fff; padding:10px; margin-bottom:10px;"><?php echo $row_pg['pg_name']; ?></div>
												<div class="guide-text-tit">사용유무</div>
												<div class="panel-body3">
													<select name="pg_use[]" id="pg_use_<?php echo $i; ?>" class="form-control" onchange="toggleFields(<?php echo $i; ?>, this.value)">
														<option value="1" <?php if($member_pg['pg_use'] == "1") { echo "selected"; } ?>>미사용</option>
														<option value="0" <?php if($member_pg['pg_use'] == "0") { echo "selected"; } ?>>사용</option>
													</select>

													<input type="hidden" name="pg_mid[]" id="pg_mid" class="form-control" value="<?php echo $row_pg['pg_id']; ?>">
													<input type="hidden" name="pg_name[]" id="pg_mid" class="form-control" value="<?php echo $row_pg['pg_name']; ?>">
													<input type="hidden" name="pg_code[]" id="pg_mid" class="form-control" value="<?php echo $row_pg['pg_code']; ?>">
													<?php /*
													<label class="input-chk"><input type="checkbox" name="pg_use[]" value="1" <?php if($member_pg['pg_use'] == "1") { echo "checked"; } ?>> <span>(최대 <?php echo number_format(substr($row_pg['pg_pay'],0,-4)); ?>만원 / <?php echo $row_pg['pg_hal']; ?>개월) 결제가능</span></label>
													*/ ?>
												</div>
												<div id="open_<?php echo $i; ?>" style="display: <?php echo $display_style; ?>;">
													<div class="guide-text-tit">최대한도 <?php echo number_format($row_pg['pg_pay']); ?>원</div>
													<div class="panel-body3">
														<input type="text" name="pg_pay[]" id="pg_pay" class="form-control" placeholder="최대<?php echo number_format($row_pg['pg_pay']); ?>원" value="<?php echo $member_pg['pg_pay']; ?>">
														<div class="guide-text"></div>
													</div>

													<div class="guide-text-tit">최대할부 <?php echo $row_pg['pg_hal']; ?>개월</div>
													<div class="panel-body3">
														<select name="pg_hal[]" id="pg_hal" class="form-control">
															<option value="">할부선택 (최대 <?php echo $row_pg['pg_hal']; ?>개월)</option>
															<option value="1" <?php if($row['pg_hal'] == "1") { echo "selected"; } ?>>일시불</option>
															<?php for($h=2; $h<=$row_pg['pg_hal']; $h++) { ?>
															<option value="<?php echo $h; ?>" <?php if($member_pg['pg_hal'] == $h) { echo "selected"; } ?>><?php echo $h; ?>개월</option>
															<?php } ?>
														</select>
														<div class="guide-text"></div>
													</div>
													<div class="guide-text-tit">중복결제</div>
													<div class="panel-body3">
														<select name="pg_overlap[]" id="pg_overlap" class="form-control" <?php if($row_pg['pg_overlap'] == "1") { ?> onFocus="this.initialSelect = this.selectedIndex;" onChange="this.selectedIndex = this.initialSelect;"<?php } ?>>
															<option value="0" <?php if($member_pg['pg_overlap'] == "0") { echo "selected"; } ?>>가능</option>
															<option value="1" <?php if($member_pg['pg_overlap'] == "1") { echo "selected"; } ?>>불가</option>
														</select>
													</div>

													<?php if($row_pg['pg_tid']) { ?>
													<div class="guide-text-tit">분할TID 구분자</div>
													<div class="panel-body3">
														<input type="text" name="pg_tid[]" id="pg_tid" class="form-control" value="<?php echo $row_pg['pg_tid']; ?>" readonly>
													</div>
													<?php } ?>

													<?php if($row_pg['pg_key1']) { ?>
													<div class="guide-text-tit"><?php echo $row_pg['pg_key1']; ?></div>
													<div class="panel-body3">
														<input type="text" name="pg_key1[]" id="pg_key1" class="form-control" placeholder="<?php echo $row_pg['pg_key1']; ?>" value="<?php echo $member_pg['pg_key1']; ?>">
													</div>
													<?php } else { ?>
													<input type="hidden" name="pg_key1[]" id="pg_key1" class="form-control">
													<?php } ?>

													<?php if($row_pg['pg_key2']) { ?>
													<div class="guide-text-tit"><?php echo $row_pg['pg_key2']; ?></div>
													<div class="panel-body3">
														<input type="text" name="pg_key2[]" id="pg_key2" class="form-control" placeholder="<?php echo $row_pg['pg_key2']; ?>" value="<?php echo $member_pg['pg_key2']; ?>">
													</div>
													<?php } else { ?>
													<input type="hidden" name="pg_key2[]" id="pg_key2" class="form-control">
													<?php } ?>

													<?php if($row_pg['pg_key3']) { ?>
													<div class="guide-text-tit"><?php echo $row_pg['pg_key3']; ?></div>
													<div class="panel-body3">
														<input type="text" name="pg_key3[]" id="pg_key3" class="form-control" placeholder="<?php echo $row_pg['pg_key3']; ?>" value="<?php echo $member_pg['pg_key3']; ?>">
													</div>
													<?php } else { ?>
													<input type="hidden" name="pg_key3[]" id="pg_key3" class="form-control">
													<?php } ?>

													<?php if($row_pg['pg_key4']) { ?>
													<div class="guide-text-tit"><?php echo $row_pg['pg_key4']; ?></div>
													<div class="panel-body3">
														<input type="text" name="pg_key4[]" id="pg_key4" class="form-control" placeholder="<?php echo $row_pg['pg_key4']; ?>" value="<?php echo $member_pg['pg_key4']; ?>">
													</div>
													<?php } else { ?>
													<input type="hidden" name="pg_key4[]" id="pg_key4" class="form-control">
													<?php } ?>

													<?php if($row_pg['pg_key5']) { ?>
													<div class="guide-text-tit"><?php echo $row_pg['pg_key5']; ?></div>
													<div class="panel-body3">
														<input type="text" name="pg_key5[]" id="pg_key5" class="form-control" placeholder="<?php echo $row_pg['pg_key5']; ?>" value="<?php echo $member_pg['pg_key5']; ?>">
													</div>
													<?php } else { ?>
													<input type="hidden" name="pg_key5[]" id="pg_key5" class="form-control">
													<?php } ?>
												</div>
											</li>

										<?php } ?>
										</ul>
									</div>
								</div>
							</div>
							<?php } ?>


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
							<a class="btn btn-black btn-cell" onclick="insert_bbs()">등록</a>
							<a class="btn btn-black-line btn-cell" onclick="go_cancel();">취소</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</section>
<script>
function toggleFields(index, value) {
    var displayStyle = (value == "0") ? "block" : "none";
    document.getElementById('open_' + index).style.display = displayStyle;
}
</script>