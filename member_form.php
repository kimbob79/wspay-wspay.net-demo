<?php
	if($member['mb_level'] < 7) { alert("권한이 없습니다."); }
	$title1 = "가맹점 관리";
	if($level == "8") { $title2 = "본사 관리";
	} else if($level == "7") { $title2 = "지사 관리";
	} else if($level == "6") { $title2 = "총판 관리";
	} else if($level == "5") { $title2 = "대리점 관리";
	} else if($level == "4") { $title2 = "영업점 관리";
	} else if($level == "3") { $title2 = "가맹점 관리";
	}

	if($w == "u") {
		$g5['title'] = $titles.' 수정';
	} else {
		$g5['title'] = $titles.' 신규등록';
	}
	if(!$is_admin) {
		if($member['mb_level'] <= $mb_level) { alert("권한이 없습니다."); }
	}

	if($w == "u") {
		$mb = get_member($mb_id);

		if($member['mb_level'] == 7) {
			if($mb['mb_2'] != $member['mb_id']) {
				alert("잘못된 접근입니다.");
			}
		}

		if(!$mb['mb_id']) {
			alert("회원이 없거나 잘못된 접근입니다.");
		}

		// 사업자등록번호
		$mb_company_number =explode('-' , $mb['mb_7']);
		$mb_company_number1 = $mb_company_number[0];
		$mb_company_number2 = $mb_company_number[1];
		$mb_company_number3 = $mb_company_number[2];

		$mb_tel =explode('-' , $mb['mb_tel']);
		$mb_tel1 = $mb_tel[0];
		$mb_tel2 = $mb_tel[1];
		$mb_tel3 = $mb_tel[2];

		$mb_hp =explode('-' , $mb['mb_hp']);
		$mb_hp1 = $mb_hp[0];
		$mb_hp2 = $mb_hp[1];
		$mb_hp3 = $mb_hp[2];

		if(!$mb_level) { $mb_level = $mb['mb_level']; }
	}


	if(adm_sql_common) {
		$u_sql = " and mb_1 IN (".adm_sql_common.")";
	} else {
		$u_sql = " ";
		if($member['mb_level'] == 7) {
			$u_sql1 = " and mb_id = '{$member['mb_1']}' ";
			$u_sql2 = " and mb_2 = '{$member['mb_id']}' ";
		}
	}


	include_once("./_head.php");


//	echo $sql;
//	echo $total_pay;

?>
<script src="https://spi.maps.daum.net/imap/map_js_init/postcode.v2.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="index_menu">
	<ul class="shortcut">
		<li class="sc_current"><a>
		<?php
			if($mb_level == "8") {
				$type_name = "본사";
			} else if($mb_level == "7") {
				$type_name = "지사";
			} else if($mb_level == "6") {
				$type_name = "총판";
			} else if($mb_level == "5") {
				$type_name = "대리점";
			} else if($mb_level == "4") {
				$type_name = "영업점";
			} else if($mb_level == "3") {
				$type_name = "가맹점";
			}
			if($mb_id) {
				echo " 수정";
			} else {
				echo $type_name." 신규등록";
			}
		?>
		</a></li>
	</ul>
</div>

<form name="fmember" id="fmember" action="./?p=member_form_update" onsubmit="return fregisterform_submit(this);" method="post" enctype="multipart/form-data">
	<input type="hidden" name="w" value="<?php echo $w ?>">
	<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
	<input type="hidden" name="stx" value="<?php echo $stx ?>">
	<input type="hidden" name="sst" value="<?php echo $sst ?>">
	<input type="hidden" name="sod" value="<?php echo $sod ?>">
	<input type="hidden" name="page" value="<?php echo $page ?>">
	<input type="hidden" name="token" value="">
	<input type="hidden" name="mb_level" value="<?php echo $mb_level ?>">


	<table class="table_view">
		<tbody>
			<tr>
				<th style="width:100px"><label for="mb_id">구분</label></th>
				<td>
					<?php
						if($mb_level == "8") {
							$type_name = "본사";
						} else if($mb_level == "7") {
							$type_name = "지사";
						} else if($mb_level == "6") {
							$type_name = "총판";
						} else if($mb_level == "5") {
							$type_name = "대리점";
						} else if($mb_level == "4") {
							$type_name = "영업점";
						} else if($mb_level == "3") {
							$type_name = "가맹점";
						}
						if($mb_id) {
							echo " 수정";
						} else {
							echo $type_name." 신규등록";
						}
					?>
				</td>
			</tr>
			<?php /*
			<tr>
				<th><label for="mb_adult">차액정산</label></th>
				<td><label><input type="checkbox" name="mb_adult" value="1" id="mb_adult" class="frm_input" <?php if($mb['mb_adult'] == 1) { echo "checked"; } ?>> 차액정산 사용시 체크해주세요</label></td>
			</tr>
			<tr>
				<th><label for="mb_dupinfo">차액정산 MBR</label></th>
				<td><input type="text" name="mb_dupinfo" value="<?php echo $mb['mb_dupinfo']; ?>" id="mb_dupinfo" class="frm_input"></td>
			</tr>
			*/ ?>

			<tr>
				<th style="color:red;"><label for="mb_id">소속</label></th>
				<td>
					※ 자신보다 하위업체는 선택하지 않으셔도 됩니다.<br>

					<?php
						if($mb_level <= 7) {
							$sql2 = " select * from g5_member where mb_level = '8'".$u_sql.$u_sql1." order by mb_nick asc ";
							$result2 = sql_query($sql2);
							//echo $sql2;
						?>
					<div class="select" style="margin-bottom:5px; margin-right:5px; float:left">
						<select name="mb_1" id="mb_1" style="width:200px; height:100px" <?php if($member['mb_level'] <= 7) { echo "required"; } ?> size="10">
							<option value=' '>= 본사 없음 =</option>
							<?php
								for ($i=0; $row2=sql_fetch_array($result2); $i++) {
							?>
							<option value="<?php echo $row2['mb_id'] ?>" <?php if($row2['mb_id'] == $mb['mb_1']) { echo "selected"; } ?>><?php echo $row2['mb_nick']; ?></option>
							<?php
								}
							?>
						</select>
					</div>
					<?php } ?>

					<?php
						if($mb_level <= 6) {
							$sql2 = " select * from g5_member where mb_level = '7'".$u_sql.$u_sql2." order by mb_nick asc ";
							$result2 = sql_query($sql2);
							//echo $sql2;
					?>
					<div class="select" style="margin-bottom:5px; margin-right:5px; float:left">
						<select name="mb_2" id="mb_2" style="width:200px; height:100px" <?php if($member['mb_level'] <= 7) { echo "required"; } ?> size="10">
							<option value=" ">= 지사 없음 =</option>
							<?php
								for ($i=0; $row2=sql_fetch_array($result2); $i++) {
							?>
							<option value="<?php echo $row2['mb_id'] ?>" <?php if($row2['mb_id'] == $mb['mb_2']) { echo "selected"; } ?>><?php echo $row2['mb_nick']; ?><?php if($member['mb_level'] >= 8) { ?> / <?php echo $row2['mb_homepage']; ?>%<?php } ?></option>
							<?php
								}
							?>
						</select>
					</div>
					<?php } ?>

					<?php
						if($mb_level <= 5) {
							$sql2 = " select * from g5_member where mb_level = '6'".$u_sql.$u_sql2." order by mb_nick asc ";
							$result2 = sql_query($sql2);
							//echo $sql2;
					?>
					<div class="select" style="margin-bottom:5px; margin-right:5px; float:left">
						<select name="mb_3" id="mb_3" style="width:200px; height:100px" size="10">
							<option value=" ">= 총판 없음 =</option>
							<?php
								for ($i=0; $row2=sql_fetch_array($result2); $i++) {
							?>
							<option value="<?php echo $row2['mb_id'] ?>" <?php if($row2['mb_id'] == $mb['mb_3']) { echo "selected"; } ?>><?php echo $row2['mb_nick']; ?><?php if($member['mb_level'] >= 7) { ?> / <?php echo $row2['mb_homepage']; ?>%<?php } ?></option>
							<?php
								}
							?>
						</select>
					</div>
					<?php } ?>

					<?php
						if($mb_level <= 4) {
							$sql2 = " select * from g5_member where mb_level = '5'".$u_sql.$u_sql2." order by mb_nick asc ";
							$result2 = sql_query($sql2);
							//echo $sql2;
					?>
					<div class="select" style="margin-bottom:5px; margin-right:5px; float:left">
						<select name="mb_4" id="mb_4" style="width:200px; height:100px" size="10">
							<option value=" ">= 대리점 없음 =</option>
							<?php
								for ($i=0; $row2=sql_fetch_array($result2); $i++) {
							?>
							<option value="<?php echo $row2['mb_id'] ?>" <?php if($row2['mb_id'] == $mb['mb_4']) { echo "selected"; } ?>><?php echo $row2['mb_nick']; ?><?php if($member['mb_level'] >= 6) { ?> / <?php echo $row2['mb_homepage']; ?>%<?php } ?></option>
							<?php
								}
							?>
						</select>
					</div>
					<?php } ?>

					<?php
						if($mb_level <= 3) {
							$sql2 = " select * from g5_member where mb_level = '4'".$u_sql.$u_sql2." order by mb_nick asc ";
							$result2 = sql_query($sql2);
							//echo $sql2;
					?>
					<div class="select" style="margin-bottom:5px; margin-right:5px; float:left">
						<select name="mb_5" id="mb_5" style="width:200px; height:100px" size="10">
							<option value=" ">= 영업점 없음 =</option>
							<?php
								for ($i=0; $row2=sql_fetch_array($result2); $i++) {
							?>
							<option value="<?php echo $row2['mb_id'] ?>" <?php if($row2['mb_id'] == $mb['mb_5']) { echo "selected"; } ?>><?php echo $row2['mb_nick']; ?><?php if($member['mb_level'] >= 5) { ?> / <?php echo $row2['mb_homepage']; ?>%<?php } ?></option>
							<?php
								}
							?>
						</select>
					</div>
					<?php } ?>
				</td>
			</tr>

			<tr>
				<th style="color:red;"><label for="mb_settle_gbn">재정산 여부</label></th>
				<td>
					<label><input type="radio" name="mb_settle_gbn" value="Y" id="mb_settle_gbn_y" class="frm_input" <?php if($mb['mb_settle_gbn'] == 'Y') { echo "checked"; } ?>> 재정산</label>
					<label style="margin-left:10px;"><input type="radio" name="mb_settle_gbn" value="N" id="mb_settle_gbn_n" class="frm_input" <?php if($mb['mb_settle_gbn'] != 'Y') { echo "checked"; } ?>> 미재정산</label>
				</td>
			</tr>

			<?php /*
			<input name="apiId./v2/user/me.query.property_keys" type="text" class="frm_input" placeholder="Property 키 목록, JSON Array를[&quot;properties.nickname&quot;]과 같은 형식 사용" value="">
			*/ ?>

			<tr>
				<th><label for="mb_id">수기결제</label></th>
				<td>
					<label><input type="checkbox" name="mb_mailling" value="1" id="mb_mailling" class="frm_input" <?php if($mb['mb_mailling'] == 1) { echo "checked"; } ?>> 수기결제를 허용할경우 체크해주세요</label>
				</td>
			</tr>
			
			<tr>
				<th style="color:red;"><label for="mb_id">아이디<?php echo $sound_only ?></label></th>
				<td>
					<?php
						if($w == "u") {
					?>
					<input type="hidden" readonly name="mb_id" value="<?php echo $mb['mb_id']; ?>" required class="frm_input" style="max-width:200px"><?php echo $mb['mb_id']; ?>
					<?php
						} else {

						$uid = time(); // 회원 아이디 형식 설정
						$uidpass = date("md").rand(1000, 9999); // 회원 아이디 형식 설정
						if(!$mb['mb_id']) { $mb['mb_id'] = $uid; }
						if($mb_id) {
							echo "<div style='padding-top: 0.375em;'>".$mb['mb_id']."</div>";
						} else {
					?>
						<input type="text" name="mb_id" value="<?php echo $mb['mb_id']; ?>" required class="frm_input" style="max-width:200px"> ※ 랜덤 생성됨
					<?php } } ?>
				</td>
			</tr>
			<tr>
				<th style="color:red;"><label for="mb_password">비밀번호<?php echo $sound_only ?></label></th>
				<td>
					<input type="text"  autocomplete="on" name="mb_password" class="frm_input" value="<?php if($w != "u") { echo $uidpass; } ?>" style="max-width:200px"><?php if($w != "u") { ?> ※ 랜덤 샌성됨<?php } ?>
				</td>
			</tr>
			<?php if($mb_level != '10') { ?>
			<tr>
				<th style="color:red;"><label for="mb_homepage">수수료</label></th>
				<td><input type="text" name="mb_homepage" value="<?php echo $mb['mb_homepage'] ?>" id="mb_homepage" required class="frm_input" style="max-width:100px"> %</td>
			</tr>

			<?php if($mb_level == '4') { ?>
			<tr>
				<th style="color:#4caf50;"><label for="mb_van_fee">밴피</label></th>
				<td><input type="text" name="mb_van_fee" value="<?php echo $mb['mb_van_fee'] ?>" id="mb_van_fee" class="frm_input" style="max-width:100px" maxlength="4" oninput="this.value = this.value.replace(/[^0-9]/g, '');"> 원</td>
			</tr>
			<?php } ?>

			<tr>
				<th><label for="mb_dupinfo">정산주기</label></th>
				<td>
					<select name="mb_dupinfo">
						<option value="">정산주기 선택</option>
						<option value="0" <?php if($mb['mb_dupinfo'] == "0") { echo "selected"; } ?>>D+0</option>
						<option value="1" <?php if($mb['mb_dupinfo'] == "1") { echo "selected"; } ?>>D+1</option>
						<option value="3" <?php if($mb['mb_dupinfo'] == "3") { echo "selected"; } ?>>D+3</option>
						<option value="7" <?php if($mb['mb_dupinfo'] == "7") { echo "selected"; } ?>>D+7</option>
					</select>
				</td>
			</tr>

			<tr>
				<th style="color:red;"><label for="mb_nick">상호명</label></th>
				<td><input type="text" name="mb_nick" value="<?php echo $mb['mb_nick'] ?>" id="mb_nick" required class="frm_input" style="max-width:200px"></td>
			</tr>
			<tr>
				<th><label for="mb_level">사업자등록번호</label></th>
				<td>
					<input type="text" autocomplete="on" name="mb_company_number1" value="<?php echo $mb_company_number1; ?>" class="frm_input" style="max-width:80px" maxlength="3" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
					<input type="text" autocomplete="on" name="mb_company_number2" value="<?php echo $mb_company_number2; ?>" class="frm_input" style="max-width:50px" maxlength="2" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
					<input type="text" autocomplete="on" name="mb_company_number3" value="<?php echo $mb_company_number3; ?>" class="frm_input" style="max-width:100px" maxlength="5" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
				</td>
			</tr>
			<tr>
				<th><label for="mb_name">대표자명</label></th>
				<td><input type="text" name="mb_name" value="<?php echo $mb['mb_name'] ?>" id="mb_name" class="frm_input" style="max-width:100px"></td>
			</tr>
			<tr>
				<th><label for="mb_tel">전화번호</label></th>
				<td>
					<select name="mb_tel1" style="max-width:70px; height:28px;">
						<option value="">선택</option>
						<option value="010" <?php if($mb_tel1 == "010") { echo "selected"; } ?>>010</option>
						<option value="011" <?php if($mb_tel1 == "011") { echo "selected"; } ?>>011</option>
						<option value="017" <?php if($mb_tel1 == "017") { echo "selected"; } ?>>017</option>
						<option value="018" <?php if($mb_tel1 == "018") { echo "selected"; } ?>>018</option>
						<option value="019" <?php if($mb_tel1 == "019") { echo "selected"; } ?>>019</option>
						<option value="02" <? if($mb_mb_tel1 == "02") echo "selected"; ?>>02</option>
						<option value="031" <? if($mb_tel1 == "031") echo "selected"; ?>>031</option>
						<option value="032" <? if($mb_tel1 == "032") echo "selected"; ?>>032</option>
						<option value="033" <? if($mb_tel1 == "033") echo "selected"; ?>>033</option>
						<option value="041" <? if($mb_tel1 == "041") echo "selected"; ?>>041</option>
						<option value="042" <? if($mb_tel1 == "042") echo "selected"; ?>>042</option>
						<option value="043" <? if($mb_tel1 == "043") echo "selected"; ?>>043</option>
						<option value="051" <? if($mb_tel1 == "051") echo "selected"; ?>>051</option>
						<option value="052" <? if($mb_tel1 == "052") echo "selected"; ?>>052</option>
						<option value="053" <? if($mb_tel1 == "053") echo "selected"; ?>>053</option>
						<option value="054" <? if($mb_tel1 == "054") echo "selected"; ?>>054</option>
						<option value="055" <? if($mb_tel1 == "055") echo "selected"; ?>>055</option>
						<option value="061" <? if($mb_tel1 == "061") echo "selected"; ?>>061</option>
						<option value="062" <? if($mb_tel1 == "062") echo "selected"; ?>>062</option>
						<option value="063" <? if($mb_tel1 == "063") echo "selected"; ?>>063</option>
						<option value="064" <? if($mb_tel1 == "064") echo "selected"; ?>>064</option>
						<option value="070" title="인터넷전화 (070)" <? if($mb_tel1 == "070") echo "selected"; ?>>070</option>
						<option value="050" title="평생전화 (050)" <? if($mb_tel1 == "050") echo "selected"; ?>>050</option>
						<option value="0507" <? if($mb_tel1 == "0507") echo "selected"; ?>>0507</option>
					</select>
					<input type="text" autocomplete="on" name="mb_tel2" value="<?php echo $mb_tel2; ?>" <?php if($basic['mb_tel']) { ?>required<?php } ?> class="frm_input" size="4" maxlength="4" oninput="this.value = this.value.replace(/[^0-9]/g, '');" style="max-width:60px">
					<input type="text" autocomplete="on" name="mb_tel3" value="<?php echo $mb_tel3; ?>" <?php if($basic['mb_tel']) { ?>required<?php } ?> class="frm_input" size="4" maxlength="4" oninput="this.value = this.value.replace(/[^0-9]/g, '');" style="max-width:60px">
				</td>
			</tr>
			<tr>
				<th>휴대전화</th>
				<td>
					<select name="mb_hp1" style="max-width:70px; height:28px;">
						<option value="010" <?php if($mb_hp1 == "010") { echo "selected"; } ?>>010</option>
						<option value="011" <?php if($mb_hp1 == "011") { echo "selected"; } ?>>011</option>
						<option value="017" <?php if($mb_hp1 == "017") { echo "selected"; } ?>>017</option>
						<option value="018" <?php if($mb_hp1 == "018") { echo "selected"; } ?>>018</option>
						<option value="019" <?php if($mb_hp1 == "019") { echo "selected"; } ?>>019</option>
						<option value="0507" <?php if($mb_hp1 == "0507") { echo "selected"; } ?>>0507</option>
					</select>
					<input type="text" autocomplete="on" name="mb_hp2" value="<?php echo $mb_hp2; ?>" class="frm_input" size="4" maxlength="4" oninput="this.value = this.value.replace(/[^0-9]/g, '');" style="max-width:60px">
					<input type="text" autocomplete="on" name="mb_hp3" value="<?php echo $mb_hp3; ?>" class="frm_input" size="4" maxlength="4" oninput="this.value = this.value.replace(/[^0-9]/g, '');" style="max-width:60px">
				</td>
			</tr>
			<tr>
				<th><label for="mb_email">이메일</label></th>
				<td><input type="text" name="mb_email" value="<?php echo $mb['mb_email'] ?>" id="mb_email" maxlength="100" class="frm_input email" style="max-width:200px"></td>
			</tr>
			<tr>
				<th>주소</th>
				<td>
					<button type="button" class="btn_b btn_b02" onclick="win_zip('fmember', 'mb_zip', 'mb_addr1', 'mb_addr2', 'mb_addr3', 'mb_addr_jibeon');" style="margin-bottom:2px;">주소 검색</button>
					<input type="text" name="mb_zip" value="<?php echo $mb['mb_zip1'] . $mb['mb_zip2']; ?>" id="mb_zip" class="frm_input" placeholder="우편번호" size="5" maxlength="6" style="max-width:200px;margin-bottom:2px;"><br>
					<input type="text" name="mb_addr1" value="<?php echo $mb['mb_addr1'] ?>" id="mb_addr1" class="frm_input full_input" placeholder="기본주소" style="max-width:300px;margin-bottom:2px;"><br>
					<input type="text" name="mb_addr2" value="<?php echo $mb['mb_addr2'] ?>" id="mb_addr2" class="frm_input full_input" placeholder="상세주소" style="max-width:300px;margin-bottom:2px;"><br>
					<input type="text" name="mb_addr3" value="<?php echo $mb['mb_addr3'] ?>" id="mb_addr3" class="frm_input full_input" placeholder="참고항목" style="max-width:300px">
					<input type="hidden" name="mb_addr_jibeon" value="<?php echo $mb['mb_addr_jibeon']; ?>"><br>
				</td>
			</tr>
			<tr>
				<th><label for="mb_memo_call">계좌메모</label></th>
				<td>
					<input type="text" autocomplete="on" name="mb_memo_call" value="<?php echo $mb['mb_memo_call']; ?>" class="frm_input full_input" style="max-width:500px" placeholder="여기 메모가 있으면 정산조회에 계좌보다 메모가 보여집니다.">
				</td>
			</tr>
			<tr>
				<th><label for="mb_8">계좌정보</label></th>
				<td>
					<input type="text" autocomplete="on" name="mb_8" value="<?php echo $mb['mb_8']; ?>" class="frm_input" style="max-width:100px" placeholder="은행명">
					<input type="text" autocomplete="on" name="mb_9" value="<?php echo $mb['mb_9']; ?>" class="frm_input" style="max-width:200px" placeholder="계좌번호(숫자만)" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
					<input type="text" autocomplete="on" name="mb_10" value="<?php echo $mb['mb_10']; ?>" class="frm_input" style="max-width:100px" placeholder="예금주명">
				</td>
			</tr>
			<tr>
				<th><label for="mb_sushian_id">스시이안앤 매장ID</label></th>
				<td>
					<input type="text" autocomplete="on" name="mb_sushian_id" value="<?php echo $mb['mb_sushian_id']; ?>" id="mb_sushian_id" class="frm_input" style="max-width:300px" maxlength="30" placeholder="스시이안앤 매장ID (선택사항)">
				</td>
			</tr>

			<?php
				
				$file = get_member_file($mb['mb_id']);
				for($i=0; $i<=5; $i++) {
					if($i == 0) {
						$file_title = "주민등록증<br>사본 ";
					} else if($i == 1) {
						$file_title = "사업자등록증<br>사본 ";
					} else if($i == 2) {
						$file_title = "통장 사본 ";
					} else if($i == 3) {
						$file_title = "계약서 사본 ";
					} else if($i == 4) {
						$file_title = "기타<br>첨부파일1 ";
					} else if($i == 5) {
						$file_title = "기타<br>첨부파일2 ";
					}

			?>
			<tr>
				<th><label for="mb_hp"><?php echo $file_title; ?></label></th>
				<td>
					<input type="file" name="bf_file[]" id="bf_file_<?php echo $i ?>" class="multi with-preview"  accept=".gif, .jpeg, .jpg, .png, .pdf, .doc, .docx, .xls, .xlsx, .ppt, .pptx">
					<?php
						if($mb_id && $file[$i]['file']) {

						if($file[$i]['image_type'] == "255") {
							$paths = G5_DATA_URL."/member/_";
						} else {
							$paths = G5_DATA_URL."/member/".$mb_id;
						}
						$download_url = $file[$i]['href'];
						$direct_url = isset($file[$i]['direct_url']) ? $file[$i]['direct_url'] : '';
						$ext = substr(strrchr($file[$i]['file'], '.'), 1);

						$filename = $file[$i]['source'];
						$filesize = $file[$i]['size'];

						// 파일 아이콘 결정
						if($ext == "gif" || $ext == "jpeg" || $ext == "jpg" || $ext == "png") {
							$icon = '<i class="fa fa-file-image-o" style="color:#4caf50"></i>';
							$file_type = '이미지';
						} else if($ext == "pdf") {
							$icon = '<i class="fa fa-file-pdf-o" style="color:#f44336"></i>';
							$file_type = 'PDF';
						} else if($ext == "xls" || $ext == "xlsx") {
							$icon = '<i class="fa fa-file-excel-o" style="color:#4caf50"></i>';
							$file_type = '엑셀';
						} else if($ext == "doc" || $ext == "docx") {
							$icon = '<i class="fa fa-file-word-o" style="color:#2196f3"></i>';
							$file_type = '워드';
						} else if($ext == "ppt" || $ext == "pptx") {
							$icon = '<i class="fa fa-file-powerpoint-o" style="color:#ff9800"></i>';
							$file_type = 'PPT';
						} else {
							$icon = '<i class="fa fa-file-o" style="color:#999"></i>';
							$file_type = '파일';
						}
					?>
					<div style="padding:10px; background:#f8f9fa; border-radius:4px; margin-bottom:5px;">
						<div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
							<span style="font-size:24px;"><?php echo $icon; ?></span>
							<div style="flex:1;">
								<div style="font-weight:600; color:#333; font-size:14px;"><?php echo htmlspecialchars($filename); ?></div>
								<div style="color:#999; font-size:12px;"><?php echo $file_type; ?> · <?php echo $filesize; ?> · 다운로드 <?php echo $file[$i]['download']; ?>회</div>
							</div>
						</div>
						<?php if($ext == "gif" || $ext == "jpeg" || $ext == "jpg" || $ext == "png") { ?>
						<div style="margin-bottom:8px;">
							<img src="<?php echo $download_url; ?>&inline=1"
								 style="max-width:200px; max-height:150px; height:auto; border-radius:4px; border:1px solid #ddd; cursor:pointer; object-fit:cover;"
								 onclick="openImageModal('<?php echo $download_url; ?>&inline=1')"
								 title="클릭하여 크게 보기">
						</div>
						<?php } ?>
						<a href="<?php echo $download_url; ?>" class="btn_b btn_b02" style="display:inline-block;">
							<i class="fa fa-download"></i> 다운로드
						</a>
					</div>
					<div class="file_del">
						<input type="checkbox" id="bf_file_del<?php echo $i ?>" name="bf_file_del[<?php echo $i;  ?>]" value="1"> <label for="bf_file_del<?php echo $i ?>">파일 삭제</label>
					</div>
					<?php } ?>
					<div class="frm_info"><strong>이미지, PDF, 워드, 엑셀, 파워포인트, 한글 파일만 업로드 가능</strong>합니다.</div>
				</td>
			</tr>
			<?php } ?>
			<?php } else { ?>
			<tr>
				<th><label for="mb_name">관리자명</label></th>
				<td><input type="text" name="mb_name" value="<?php echo $mb['mb_name'] ?>" id="mb_name" class="frm_input" style="max-width:100px"></td>
			</tr>
			<tr>
				<th>휴대전화</th>
				<td>
					<select name="mb_hp1" style="max-width:70px; height:28px;">
						<option value="010" <?php if($mb_hp1 == "010") { echo "selected"; } ?>>010</option>
						<option value="011" <?php if($mb_hp1 == "011") { echo "selected"; } ?>>011</option>
						<option value="017" <?php if($mb_hp1 == "017") { echo "selected"; } ?>>017</option>
						<option value="018" <?php if($mb_hp1 == "018") { echo "selected"; } ?>>018</option>
						<option value="019" <?php if($mb_hp1 == "019") { echo "selected"; } ?>>019</option>
					</select>
					<input type="text" autocomplete="on" name="mb_hp2" value="<?php echo $mb_hp2; ?>" class="frm_input" size="4" maxlength="4" oninput="this.value = this.value.replace(/[^0-9]/g, '');" style="max-width:60px">
					<input type="text" autocomplete="on" name="mb_hp3" value="<?php echo $mb_hp3; ?>" class="frm_input" size="4" maxlength="4" oninput="this.value = this.value.replace(/[^0-9]/g, '');" style="max-width:60px">
				</td>
			</tr>
			<tr>
				<th><label for="mb_email">이메일</label></th>
				<td><input type="text" name="mb_email" value="<?php echo $mb['mb_email'] ?>" id="mb_email" maxlength="100" class="frm_input email" style="max-width:200px"></td>
			</tr>
			<tr>
				<th>주소</th>
				<td>
					<button type="button" class="btn_b btn_b02" onclick="win_zip('fmember', 'mb_zip', 'mb_addr1', 'mb_addr2', 'mb_addr3', 'mb_addr_jibeon');" style="margin-bottom:2px;">주소 검색</button>
					<input type="text" name="mb_zip" value="<?php echo $mb['mb_zip1'] . $mb['mb_zip2']; ?>" id="mb_zip" class="frm_input" placeholder="우편번호" size="5" maxlength="6" style="max-width:200px;margin-bottom:2px;"><br>
					<input type="text" name="mb_addr1" value="<?php echo $mb['mb_addr1'] ?>" id="mb_addr1" class="frm_input full_input" placeholder="기본주소" style="max-width:300px;margin-bottom:2px;"><br>
					<input type="text" name="mb_addr2" value="<?php echo $mb['mb_addr2'] ?>" id="mb_addr2" class="frm_input full_input" placeholder="상세주소" style="max-width:300px;margin-bottom:2px;"><br>
					<input type="text" name="mb_addr3" value="<?php echo $mb['mb_addr3'] ?>" id="mb_addr3" class="frm_input full_input" placeholder="참고항목" style="max-width:300px">
					<input type="hidden" name="mb_addr_jibeon" value="<?php echo $mb['mb_addr_jibeon']; ?>"><br>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>

	<?php
	/*
		for($i=0; $i<=5; $i++) {
			if($i == 0) {
				$file_title = "주민등록증 사본 ";
			} else if($i == 1) {
				$file_title = "사업자등록증 사본 ";
			} else if($i == 2) {
				$file_title = "통장 사본 ";
			} else if($i == 3) {
				$file_title = "계약서 사본 ";
			} else if($i == 4) {
				$file_title = "기타 첨부파일1 ";
			} else if($i == 5) {
				$file_title = "기타 첨부파일2 ";
			}

			if($file[$i]['image_type'] == "255") {
				$paths = G5_DATA_URL."/member/_";
			} else {
				$paths = G5_DATA_URL."/member/".$mb_id;
			}

			$file_url = $paths."/".$file[$i]['file'];
			$ext = substr(strrchr($file_url, '.'), 1);

			if($ext == "gif" || $ext == "jpeg" || $ext == "jpg" || $ext == "png") {
				$files = '<a href="'.$file_url.'" target="_blank"><img src="'.$file_url.'"></a>';
			} else if($ext == "pdf") {
				$files = '<a href="http://docs.google.com/gview?url='.$file_url.'" target="_blank">PDF파일 보기</a>';
			} else if($ext == "xls" || $ext == "xlsx") {
				$files = '<a href="http://docs.google.com/gview?url='.$file_url.'" target="_blank">엑셀파일 보기</a>';
			} else if($ext == "doc" || $ext == "docx") {
				$files = '<a href="http://docs.google.com/gview?url='.$file_url.'" target="_blank">워드파일 보기</a>';
			} else if($ext == "ppt" || $ext == "pptx") {
				$files = '<a href="http://docs.google.com/gview?url='.$file_url.'" target="_blank">파워포인트파일 보기</a>';
			} else {
				$files = '<span style="color: #f14668;">파일없음</span>';
			}
	?>
	<div class="tile is-parent">
		<div class="card-content">
			<?php echo $file_title; ?>
			<div class="level is-mobile">
				<?php echo $files; ?>
			</div>
		</div>
	</div>
	<?php } ?>
	*/ ?>

	<div style="padding:10px 0;">
		<a href="javascript:history.back();" class="btn btn_02">목록</a>
		<input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
	</div>
</form>




<script>
$(document).ready(function() {
	$('#mb_1').select2({
		placeholder: '= 본사 선택 ='
	});
	$('#mb_2').select2({
		placeholder: '= 지사 선택 ='
	});
	$('#mb_3').select2({
		placeholder: '= 총판 선택 ='
	});
	$('#mb_4').select2({
		placeholder: '= 대리점 선택 ='
	});
	$('#mb_5').select2({
		placeholder: '= 영업점 선택 ='
	});
});

</script>



<script>
	/*
	// 본사 선택
	$("#mb_1").on("change",function(){
		var mb_1 = $(this).val();
		$.ajax({
			type: 'POST',
			url: './ajax/ajax.level8.php',
			data: { mb_1 : mb_1, mb_level : "7" },
			success: function(data) {
				$("#mb_2").html(data);
			}
		});
	});

	// 지사 선택
	$("#mb_2").on("change",function(){
		var mb_2 = $(this).val();
		$.ajax({
			type: 'POST',
			url: './ajax/ajax.level7.php',
			data: { mb_2 : mb_2, mb_level : "6" },
			success: function(data) {
				$("#mb_3").html(data);
			}
		});
	});

	// 총판 선택
	$("#mb_3").on("change",function(){
		var mb_3 = $(this).val();
		$.ajax({
			type: 'POST',
			url: './ajax/ajax.level6.php',
			data: { mb_3 : mb_3, mb_level : "5" },
			success: function(data) {
				$("#mb_4").html(data);
			}
		});
	});

	// 대리점 선택
	$("#mb_4").on("change",function(){
		var mb_4 = $(this).val();
		$.ajax({
			type: 'POST',
			url: './ajax/ajax.level5.php',
			data: { mb_4 : mb_4, mb_level : "4" },
			success: function(data) {
				$("#mb_5").html(data);
			}
		});
	});

	// 영업점 선택
	$("#mb_5").on("change",function(){
		var mb_5 = $(this).val();
		$.ajax({
			type: 'POST',
			url: './ajax/ajax.level6.php',
			data: { mb_5 : mb_5, mb_level : "3" },
			success: function(data) {
				$("#mb_6").html(data);
			}
		});
	});
	*/

	/*
	$("#l7").on("change",function(){
		var l7 = $(this).val();
		$.ajax({
			type: 'POST',
			url: './ajax/ajax.level7.php',
			data: { l7 : l7, mb_type : "7" },
			success: function(data) {
				if(data) {
					$("#model, #type").html('<li>← 선택해주세요</li>').val('');
				}
				$("#l7").html(data);
			}
		});
	});
	*/

	// 수정시에 업체선택 비활성
	$(document).ready(function() {
		<?php if($member['mb_type'] >= 2) { ?>
		$("#l2").prop('disabled',true);
		<?php } ?>
		<?php if($member['mb_type'] >= 3) { ?>
		$("#l3").prop('disabled',true);
		<?php } ?>
		<?php if($member['mb_type'] >= 4) { ?>
		$("#l4").prop('disabled',true);
		<?php } ?>
		<?php if($member['mb_type'] >= 5) { ?>
		$("#l5").prop('disabled',true);
		<?php } ?>
		<?php if($member['mb_type'] >= 6) { ?>
		$("#l6").prop('disabled',true);
		<?php } ?>
	});




	// 수정버튼 클릭시 다시 활성
	$(".submitok").on("click",function(){
		$("#l2, #l3, #l4, #l5, #l6").prop('disabled',false);
	});


	// submit 최종 폼체크
	function fregisterform_submit(f)
	{
		// 회원아이디 검사
		if (f.w.value == "") {
			var msg = reg_mb_id_check();
			if (msg) {
				alert(msg);
				f.mb_id.select();
				return false;
			}
		}

		if (f.w.value == "") {
			if (f.mb_password.value.length < 3) {
				alert("비밀번호를 3글자 이상 입력하십시오.");
				f.mb_password.focus();
				return false;
			}
		}

		if (f.mb_password.value != f.mb_password_re.value) {
			alert("비밀번호가 같지 않습니다.");
			f.mb_password_re.focus();
			return false;
		}

		if (f.mb_password.value.length > 0) {
			if (f.mb_password_re.value.length < 3) {
				alert("비밀번호를 3글자 이상 입력하십시오.");
				f.mb_password_re.focus();
				return false;
			}
		}

		// 이름 검사
		if (f.w.value=="") {
			if (f.mb_name.value.length < 1) {
				alert("이름을 입력하십시오.");
				f.mb_name.focus();
				return false;
			}

			/*
			var pattern = /([^가-힣\x20])/i;
			if (pattern.test(f.mb_name.value)) {
				alert("이름은 한글로 입력하십시오.");
				f.mb_name.select();
				return false;
			}
			*/
		}

		<?php if($w == '' && $config['cf_cert_use'] && $config['cf_cert_req']) { ?>
		// 본인확인 체크
		if(f.cert_no.value=="") {
			alert("회원가입을 위해서는 본인확인을 해주셔야 합니다.");
			return false;
		}
		<?php } ?>

		// 닉네임 검사
		if ((f.w.value == "") || (f.w.value == "u" && f.mb_nick.defaultValue != f.mb_nick.value)) {
			var msg = reg_mb_nick_check();
			if (msg) {
				alert(msg);
				f.reg_mb_nick.select();
				return false;
			}
		}

		// E-mail 검사
		if ((f.w.value == "") || (f.w.value == "u" && f.mb_email.defaultValue != f.mb_email.value)) {
			var msg = reg_mb_email_check();
			if (msg) {
				alert(msg);
				f.reg_mb_email.select();
				return false;
			}
		}

		<?php if (($config['cf_use_hp'] || $config['cf_cert_hp']) && $config['cf_req_hp']) {  ?>
		// 휴대폰번호 체크
		var msg = reg_mb_hp_check();
		if (msg) {
			alert(msg);
			f.reg_mb_hp.select();
			return false;
		}
		<?php } ?>

		if (typeof f.mb_icon != "undefined") {
			if (f.mb_icon.value) {
				if (!f.mb_icon.value.toLowerCase().match(/.(gif|jpe?g|png)$/i)) {
					alert("회원아이콘이 이미지 파일이 아닙니다.");
					f.mb_icon.focus();
					return false;
				}
			}
		}

		if (typeof f.mb_img != "undefined") {
			if (f.mb_img.value) {
				if (!f.mb_img.value.toLowerCase().match(/.(gif|jpe?g|png)$/i)) {
					alert("회원이미지가 이미지 파일이 아닙니다.");
					f.mb_img.focus();
					return false;
				}
			}
		}

		if (typeof(f.mb_recommend) != "undefined" && f.mb_recommend.value) {
			if (f.mb_id.value == f.mb_recommend.value) {
				alert("본인을 추천할 수 없습니다.");
				f.mb_recommend.focus();
				return false;
			}

			var msg = reg_mb_recommend_check();
			if (msg) {
				alert(msg);
				f.mb_recommend.select();
				return false;
			}
		}

		document.getElementById("btn_submit").disabled = "disabled";

		return true;
	}
</script>

<!-- 이미지 모달 -->
<div id="imageModal" onclick="closeImageModal()" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.9); cursor:pointer;">
	<span style="position:absolute; top:15px; right:25px; color:#fff; font-size:35px; font-weight:bold; cursor:pointer; z-index:10000;">&times;</span>
	<div style="display:flex; align-items:center; justify-content:center; height:100%; padding:40px;">
		<img id="modalImage" src="" onclick="event.stopPropagation()" style="max-width:80%; max-height:80vh; object-fit:contain; border-radius:8px; box-shadow:0 4px 20px rgba(0,0,0,0.5); cursor:default;">
	</div>
</div>

<script>
function openImageModal(imageSrc) {
	document.getElementById('imageModal').style.display = 'block';
	document.getElementById('modalImage').src = imageSrc;
	document.body.style.overflow = 'hidden';
}

function closeImageModal() {
	document.getElementById('imageModal').style.display = 'none';
	document.getElementById('modalImage').src = '';
	document.body.style.overflow = 'auto';
}

// ESC 키로 모달 닫기
document.addEventListener('keydown', function(event) {
	if (event.key === 'Escape') {
		closeImageModal();
	}
});
</script>

<?php
	include_once("./_tail.php");
?>