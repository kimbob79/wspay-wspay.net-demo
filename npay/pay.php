<section class="container" id="intro">
	<section class="section3">
		<div class="inner">
			<div class="main-sub">
				<h2>결제 선택</h2>
			</div>
			<div class="use-area box-group">
				<?php
					$sql_member_pg = " select * from pay_member_pg where mb_id = '{$member['mb_id']}' and pg_use = '0' ";
					$result_member_pg = sql_query($sql_member_pg);
					for ($p=0; $row_member_pg=sql_fetch_array($result_member_pg); $p++) {
						$pg = sql_fetch(" select * from pay_pg where pg_id = '{$row_member_pg['pg_mid']}' ");
						if($pg['pg_certified'] == "1") { $pg_certified = "비인증"; } else if($pg['pg_certified'] == "2") { $pg_certified = "인증"; } else { $pg_certified = "구인증"; }
				?>
				<div class="item">
					<a href="./payment.php?mb_id=<?php echo $member['mb_id']; ?>&pg_id=<?php echo $row_member_pg['pg_id']; ?>" onclick="return openWindow(this, {name:'payapptest',width:420,height:670,center:true,scrollbars:true})">
						<dl class="txt">
							<dt><?php echo $row_member_pg['pg_name']; ?></dt>
							<dd>
								<p>
									타입 : <b><?php echo $pg_certified; ?></b><br>
									한도 : <b><?php if($row_member_pg['pg_pay']) { echo number_format($row_member_pg['pg_pay']); } else { echo "0"; } ?>원</b><br>
									할부 : <b><?php if($row_member_pg['pg_hal'] > 1) { echo $row_member_pg['pg_hal']."개월"; } else if($row_member_pg['pg_hal'] == 1) { echo "일시불"; } else { echo "불가"; } ?></b>
								</p>
								<span class="btn-view">결제하기 <i class="arrow arrow-sm right light"></i></span>
							</dd>
						</dl>
					</a>
				</div>
				<?php
					}
				?>
				<?php /*
				<div class="item">
					<dl class="txt" onclick="location.href='/homepage/guide/guide3.html'">
						<dt>판매점 계약 안내</dt>
						<dd>
							<p>결제가 난 뒤 계약서류가 완료되면<br>정산이 시작됩니다. (이메일 접수)</p>
							<span class="btn-view">자세히 <i class="arrow arrow-sm right light"></i></span>
						</dd>
					</dl>
				</div>
				<div class="item">
					<dl class="txt" onclick="location.href='/homepage/guide/guide4.html'">
						<dt>보증보험 안내</dt>
						<dd>
							<p>월매출 500만원 이하 or 건당 50만원 이하 결제 시 보증보험 면제!<br>(업종에 따라 달라질 수 있습니다.)</p>
							<span class="btn-view">자세히 <i class="arrow arrow-sm right light"></i></span>
						</dd>
					</dl>
				</div>
				<div class="item">
					<dl class="txt" onclick="location.href='/homepage/guide/guide4.html'">
						<dt>보증보험 안내</dt>
						<dd>
							<p>월매출 500만원 이하 or 건당 50만원 이하 결제 시 보증보험 면제!<br>(업종에 따라 달라질 수 있습니다.)</p>
							<span class="btn-view">자세히 <i class="arrow arrow-sm right light"></i></span>
						</dd>
					</dl>
				</div>
				<div class="item">
					<dl class="txt" onclick="location.href='/homepage/guide/guide4.html'">
						<dt>보증보험 안내</dt>
						<dd>
							<p>월매출 500만원 이하 or 건당 50만원 이하 결제 시 보증보험 면제!<br>(업종에 따라 달라질 수 있습니다.)</p>
							<span class="btn-view">자세히 <i class="arrow arrow-sm right light"></i></span>
						</dd>
					</dl>
				</div>
				*/ ?>
			</div>
		</div>
	</section>
</section>


<?php /*
<section class="container" id="bbs">
	<section class="contents contents-bbs">
		<div class="bbs-cont">
			<div class="inner">
				<div class="write-form">
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
							<span class="quest">결제 내역 확인이 필요하세요?</span>
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
					<form name="insert_bbs_form" method=post enctype="multipart/form-data" action="https://www.payapp.kr/homepage/bbs/bbs_ask_write.html" onsubmit="return false;">
						<fieldset>
							<legend class="sr-only">문의정보</legend>
							<div class="panel">
								<div class="panel-heading">
									<label for="" class="tit">문의유형</label>
								</div>
								<div class="panel-body">
									<select name="payments" id="payments" class="form-control">
										<option value="">결제타입 선택</option>
										<?php
											$sql_pg = " select * from pay_pg order by pg_pay desc ";
											$result_pg = sql_query($sql_pg);
											for ($i=0; $row_pg=sql_fetch_array($result_pg); $i++) {
												$pg_id = $row_pg['pg_id'];
										?>
										<option value="<?php echo $row_pg['pg_id']; ?>">최대 <?php echo number_format(substr($row_pg['pg_pay'],0,-4)); ?>만원 / <?php echo $row_pg['pg_hal']; ?>개월 [<?php echo $row_pg['pg_name']; ?>]</option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">제목</label>
								</div>
								<div class="panel-body">
									<input type="text" class="form-control" name="title" id="title" placeholder="제목을 입력해주세요.">
								</div>
							</div>
							<div class="panel">
								<div class="panel-heading">
									<label for="contents" class="tit">내용</label>
								</div>
								<div class="panel-body">
									<textarea name="contents" id="contents" cols="30" rows="10" placeholder="내용을 입력해주세요."></textarea>
								</div>
							</div>
							<div class="panel">
								<div class="panel-heading">
									<label for="" class="tit">파일첨부</label>
								</div>
								<div class="panel-body">
									<input type="file" id="file_file" name="file_file" class="input-file">
									<div class="input-group">
										<input type="text" name="file_file" id="" class="form-control form-upload" placeholder="" disabled>
										<a role="button" href="javascript:;" class="input-group-addon btn-md btn-gray-line upload-field">파일 선택</a>
									</div>
									<div class="guide-text">
										※ 2MB를 초과 할 수 없습니다.
									</div>
								</div>
							</div>
							<div class="panel">
								<div class="panel-heading">
									<label for="usernm" class="tit">작성자</label>
								</div>
								<div class="panel-body">
									<input type="text" class="form-control" name="usernm" id="usernm" placeholder="이름을 입력해주세요." autocomplete="username">
								</div>
							</div>
							<div class="panel">
								<div class="panel-heading">
									<label for="pass" class="tit">비밀번호</label>
								</div>
								<div class="panel-body">
									<input type="password" class="form-control" name="pass" id="pass" placeholder="비밀번호를 입력해주세요." autocomplete="new-password">
								</div>
							</div>
							<div class="panel">
								<div class="panel-heading">
									<label class="tit">약관동의</label>
								</div>
								<div class="panel-body">
									<div class="terms-box">
										<p>
											 (주)유디아이디는 문의 내용 확인 및 답변 처리를 목적으로 아래와 같이 개인정보를 수집하고 있습니다.
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
						<input type=hidden name="chk_html" id="chk_html">
						<input type=hidden name="idx" id="idx" value="">
						<input type=hidden name="mode" id="mode" value="insert_bbs">
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
*/ ?>