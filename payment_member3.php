<?php
	include_once('./_common.php');

	$fr_dates = date("Y-m-d", strtotime($fr_date));
	$to_dates = date("Y-m-d", strtotime($to_date));


	if($is_admin) {

		if(adm_sql_common) {
			$adm_sql = " mb_1 IN (".adm_sql_common.")";
		} else {
			$adm_sql = " (1)";
		}

	} else {
		if($member['mb_level'] == "8") { // 본사
			$adm_sql .= " mb_1 = '{$member['mb_id']}'  ";
		} else if($member['mb_level'] == "7") { // 지사
			$adm_sql .= " mb_2 = '{$member['mb_id']}'  ";
		} else if($member['mb_level'] == "6") { // 총판
			$adm_sql .= " mb_3 = '{$member['mb_id']}'  ";
		} else if($member['mb_level'] == "5") { // 대리점
			$adm_sql .= " mb_4 = '{$member['mb_id']}'  ";
		} else if($member['mb_level'] == "4") { //  영업점
			$adm_sql .= " mb_5 = '{$member['mb_id']}'  ";
		} else if($member['mb_level'] == "3") { // 가맹점
			$adm_sql .= " mb_6 = '{$member['mb_id']}'  ";
		}
	}

	$sql_common = " from g5_device where ".$adm_sql;

	if($member['mb_type'] == 1) {
	} else if($member['mb_type'] == 2) {
		$sql_search = " and mb_pid2 = '{$member['mb_id']}' ";
	} else if($member['mb_type'] == 3) {
		$sql_search = " and mb_pid3 = '{$member['mb_id']}' ";
	} else if($member['mb_type'] == 4) {
		$sql_search = " and mb_pid4 = '{$member['mb_id']}' ";
	} else if($member['mb_type'] == 5) {
		$sql_search = " and mb_pid5 = '{$member['mb_id']}' ";
	} else if($member['mb_type'] == 6) {
		$sql_search = " and mb_pid6 = '{$member['mb_id']}' ";
	} else if($member['mb_type'] == 7) {
		$sql_search = " and mb_pid7 = '{$member['mb_id']}' ";
	} else if($member['mb_type'] == 8) {
		$sql_search = " and mb_pid8 = '{$member['mb_id']}' ";
	} else {
	}

	if($mb_6_name) { $sql_search .= " and mb_6_name like '%{$mb_6_name}%' "; }

	if ($sst)
		$sql_order = " order by {$sst} {$sod} ";
	else
		$sql_order = " order by datetime desc ";

	$sql = " select count(*) as cnt {$sql_common} {$sql_search}  ";
	$row = sql_fetch($sql);

	$total_count = $row['cnt']; // 전체개수

	$page_count = "200";

	if($page_count) {
		$rows = $page_count;
	} else {
		$rows = $config['cf_page_rows'];
	}

	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함

	$sql = " select * {$sql_common} {$sql_search} {$sql_order} ";
	$result = sql_query($sql);
?>







<div class="m_board_scroll">
	<div class="m_table_wrap">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
				<tr>
					<th style="width:50px;">번호</th>
					<th>가맹점</th>
					<th>대표자명</th>
					<th>대표자생년월일</th>
					<th>연락처</th>
					<th>은행명</th>
					<th>계좌번호</th>
					<th>예금주</th>
					<th>사업체구분</th>
					<th>사업자등록번호</th>
					<th>법인등록번호</th>
					<th>사업장주소</th>
					<th>업태</th>
					<th>종목</th>
					<th>정산방법</th>
					<th>정산유형</th>
					<th>정산주기</th>
					<th>송금수수료</th>
					<th>단말기사용료</th>
					<th>단말기사용료 차감구분</th>
					
					<th>영업점 차감시 AID</th>
					<th>지사 AID</th>
					<th>지사명</th>
					<th>지사 수수료율</th>
					<th>총판AID</th>
					<th>총판명</th>
					<th>총판 수수료율</th>
					<th>대리점 AID</th>
					<th>대리점명</th>
					<th>대리점 수수료율</th>
					<th>영업점A AID</th>
					<th>영업점A 수수료율</th>
					<th>영업점B AID</th>
					<th>영업점B 명</th>
					<th>영업점B 수수료율</th>
					<th>결제정보</th>
					<th>PG사</th>
					<th>PG CATID</th>
					<th>PG MID</th>
					<th>PG SID</th>
					<th>PG KEY</th>
					<th>가맹점 수수료</th>
					<th>원가 수수료</th>
					<th>단말기번호</th>
					<th>취소기능</th>
					<th>구간</th>
					<th>계산서 발행여부</th>
					<th>전자세금계산서 이메일</th>
					<th>할부제한</th>
					<th>지급대행사</th>
					<th>삼성페이 사용여부</th>
					<th>코페이MID</th>
					<th>월한도</th>
					<th>일한도</th>
					<th>1회한도</th>
					<th>기간 결제금액</th>
					<th>현재 TID</th>
					<th>단말기/수기</th>
					<th>등록일</th>
				</tr>
			</thead>
			<tbody>
				<?php
					for ($i=0; $row=sql_fetch_array($result); $i++) {

					$num = number_format($total_count - ($page - 1) * $rows - $i);

					if($row['dv_type'] == 1) {
						$dv_types = "단말기";
					} else {
						$dv_types = "수기";
					}
					if($row['dv_certi'] == 1) {
						$dv_certis = "인증";
					} else {
						$dv_certis = "비인증";
					}

					$sql_sum = " select sum(pay) as sum_pay from g5_payment where (1) and (pay_datetime BETWEEN '{$fr_dates} 00:00:00' and '{$to_dates} 23:59:59') and dv_tid = '{$row['dv_tid']}'  ";
					$sum = sql_fetch($sql_sum);
					
					$mb = get_member($row['mb_6']);
					


// 은행코드

if($mb['mb_8'] == "한국은행") { $mb_8 = "001";
} else if($mb['mb_8'] == "산업은행") { $mb_8 = "002";
} else if($mb['mb_8'] == "기업은행") { $mb_8 = "003";
} else if($mb['mb_8'] == "기업") { $mb_8 = "003";
} else if($mb['mb_8'] == "국민은행") { $mb_8 = "004";
} else if($mb['mb_8'] == "국민") { $mb_8 = "004";
} else if($mb['mb_8'] == "외환은행") { $mb_8 = "005";
} else if($mb['mb_8'] == "수협중앙회") { $mb_8 = "007";
} else if($mb['mb_8'] == "수협") { $mb_8 = "007";
} else if($mb['mb_8'] == "수협은행") { $mb_8 = "007";
} else if($mb['mb_8'] == "수출입은행") { $mb_8 = "008";
} else if($mb['mb_8'] == "NH농협은행") { $mb_8 = "011";
} else if($mb['mb_8'] == "농협") { $mb_8 = "011";
} else if($mb['mb_8'] == "농협은") { $mb_8 = "011";
} else if($mb['mb_8'] == "농협은행") { $mb_8 = "011";
} else if($mb['mb_8'] == "지역농.축협") { $mb_8 = "012";
} else if($mb['mb_8'] == "농축협") { $mb_8 = "012";
} else if($mb['mb_8'] == "우리은행") { $mb_8 = "020";
} else if($mb['mb_8'] == "우리") { $mb_8 = "020";
} else if($mb['mb_8'] == "SC제일은행") { $mb_8 = "023";
} else if($mb['mb_8'] == "한국씨티은행") { $mb_8 = "027";
} else if($mb['mb_8'] == "iM뱅크") { $mb_8 = "031";
} else if($mb['mb_8'] == "대구은행") { $mb_8 = "031";
} else if($mb['mb_8'] == "대구") { $mb_8 = "031";
} else if($mb['mb_8'] == "부산은행") { $mb_8 = "032";
} else if($mb['mb_8'] == "부산") { $mb_8 = "032";
} else if($mb['mb_8'] == "광주은행") { $mb_8 = "034";
} else if($mb['mb_8'] == "제주은행") { $mb_8 = "035";
} else if($mb['mb_8'] == "전북은행") { $mb_8 = "037";
} else if($mb['mb_8'] == "전북") { $mb_8 = "037";
} else if($mb['mb_8'] == "경남은행") { $mb_8 = "039";
} else if($mb['mb_8'] == "경남") { $mb_8 = "039";
} else if($mb['mb_8'] == "새마을금고") { $mb_8 = "045";
} else if($mb['mb_8'] == "새마을") { $mb_8 = "045";
} else if($mb['mb_8'] == "새마을금고연합회") { $mb_8 = "045";
} else if($mb['mb_8'] == "신협중앙회") { $mb_8 = "048";
} else if($mb['mb_8'] == "신협") { $mb_8 = "048";
} else if($mb['mb_8'] == "신용협동조합") { $mb_8 = "048";
} else if($mb['mb_8'] == "상호저축은행") { $mb_8 = "050";
} else if($mb['mb_8'] == "저축은행") { $mb_8 = "050";
} else if($mb['mb_8'] == "모건스탠리은행") { $mb_8 = "052";
} else if($mb['mb_8'] == "HSBC은행") { $mb_8 = "054";
} else if($mb['mb_8'] == "도이치은행") { $mb_8 = "055";
} else if($mb['mb_8'] == "에이비엔암로은행") { $mb_8 = "056";
} else if($mb['mb_8'] == "제이피모간체이스은행") { $mb_8 = "057";
} else if($mb['mb_8'] == "미즈호코퍼레이트은행") { $mb_8 = "058";
} else if($mb['mb_8'] == "미쓰비시도쿄UFJ은행") { $mb_8 = "059";
} else if($mb['mb_8'] == "BOA") { $mb_8 = "060";
} else if($mb['mb_8'] == "우체국") { $mb_8 = "071";
} else if($mb['mb_8'] == "신용보증기금") { $mb_8 = "076";
} else if($mb['mb_8'] == "기술신용보증기금") { $mb_8 = "077";
} else if($mb['mb_8'] == "하나은행") { $mb_8 = "081";
} else if($mb['mb_8'] == "하나") { $mb_8 = "081";
} else if($mb['mb_8'] == "신한은행") { $mb_8 = "088";
} else if($mb['mb_8'] == "신한") { $mb_8 = "088";
} else if($mb['mb_8'] == "케이뱅크") { $mb_8 = "089";
} else if($mb['mb_8'] == "카카오뱅크") { $mb_8 = "090";
} else if($mb['mb_8'] == "카카오") { $mb_8 = "090";
} else if($mb['mb_8'] == "토스뱅크") { $mb_8 = "092";
} else if($mb['mb_8'] == "토스") { $mb_8 = "092";
} else if($mb['mb_8'] == "한국주택금융공사") { $mb_8 = "093";
} else if($mb['mb_8'] == "서울보증보험") { $mb_8 = "094";
} else if($mb['mb_8'] == "경찰청") { $mb_8 = "095";
} else if($mb['mb_8'] == "금융결제원") { $mb_8 = "099";
} else if($mb['mb_8'] == "동양종합금융증권") { $mb_8 = "209";
} else if($mb['mb_8'] == "현대증권") { $mb_8 = "218";
} else if($mb['mb_8'] == "미래에셋증권") { $mb_8 = "230";
} else if($mb['mb_8'] == "대우증권") { $mb_8 = "238";
} else if($mb['mb_8'] == "삼성증권") { $mb_8 = "240";
} else if($mb['mb_8'] == "한국투자증권") { $mb_8 = "243";
} else if($mb['mb_8'] == "우리투자증권") { $mb_8 = "247";
} else if($mb['mb_8'] == "교보증권") { $mb_8 = "261";
} else if($mb['mb_8'] == "하이투자증권") { $mb_8 = "262";
} else if($mb['mb_8'] == "에이치엠씨투자증권") { $mb_8 = "263";
} else if($mb['mb_8'] == "키움증권") { $mb_8 = "264";
} else if($mb['mb_8'] == "이트레이드증권") { $mb_8 = "265";
} else if($mb['mb_8'] == "에스케이증권") { $mb_8 = "266";
} else if($mb['mb_8'] == "대신증권") { $mb_8 = "267";
} else if($mb['mb_8'] == "솔로몬투자증권") { $mb_8 = "268";
} else if($mb['mb_8'] == "한화증권") { $mb_8 = "269";
} else if($mb['mb_8'] == "하나대투증권") { $mb_8 = "270";
} else if($mb['mb_8'] == "굿모닝신한증권") { $mb_8 = "278";
} else if($mb['mb_8'] == "동부증권") { $mb_8 = "279";
} else if($mb['mb_8'] == "유진투자증권") { $mb_8 = "280";
} else if($mb['mb_8'] == "메리츠증권") { $mb_8 = "287";
} else if($mb['mb_8'] == "엔에이치투자증권") { $mb_8 = "289";
} else if($mb['mb_8'] == "부국증권") { $mb_8 = "290";
} else if($mb['mb_8'] == "신영증권") { $mb_8 = "291";
} else if($mb['mb_8'] == "엘아이지투자증권") { $mb_8 = "292";
} else {  $mb_8 = "<span style='color:blue;'>".$mb['mb_8']."<span>"; }




				?>
				<tr<?php echo $bgcolor?" style='background: $bgcolor;'":'';?>>
					<td><?php echo $num; ?></td>
					<td><strong><?php echo $row['mb_6_name']; ?></strong></td>
					<td><?php echo $mb['mb_name']; ?></td>
					<td></td>
					<td>
						<?php if($mb['mb_hp'] != "010--") { ?>
						<?php echo $mb['mb_hp']; } else { ?>
						<?php if($mb['mb_tel'] != "--") { echo $mb['mb_tel']; } ?>
						<?php } ?>
					</td>
					<td style="text-align:left"><?php echo $mb_8; ?></td>
					<td style="text-align:left"><?php echo $mb['mb_9']; ?></td>
					<td style="text-align:left"><?php echo $mb['mb_10']; ?></td>
					<td style="text-align:left"><?php if($mb['mb_7'] == "--") { echo "비사업자"; } else if(substr($mb['mb_7'],4,2) == "08") { echo "법인"; } else { echo "개인"; } ?></td>
					<td style="text-align:left"><?php if($mb['mb_7'] == "--") { echo ""; } else { echo $mb['mb_7']; } ?></td>
					<td></td>
					<td style="text-align:left"><?php echo $mb['mb_zip1'].$mb['mb_zip2']; ?> <?php echo $mb['mb_addr1']; ?> <?php echo $mb['mb_addr2']; ?></td>
					<td></td>
					<td></td>
					<td>지갑정산<?php /* 정산방법 */ ?></td>
					<td>익일<?php /* 정산유형 */ ?></td>
					<td>일일<?php /* 정산주기 */ ?></td>
					<td><?php /* 송금수수료 */ ?></td>
					<td><?php /* 단말기 사용료 */ ?></td>
					<td><?php /* 단말기사용료 차감구분 */ ?></td>
					<td></td>
					<?php /*
					<td>주식회사레드페이</td>
					<td>2.3</td>
					*/ ?>
					<td><?php if($row['mb_1_name']) { echo $row['mb_1_name']; } else { echo "&nbsp;"; } ?></td><?php /* 총판 */ ?>
					<td><?php if($row['mb_1_name']) { echo $row['mb_1_fee']; } else { echo "&nbsp;"; } ?></td>
					<td>2.3</td>
					<td></td>
					<td><?php if($row['mb_2_name']) { echo $row['mb_2_name']; } else { echo "&nbsp;"; } ?></td><?php /* 총판 */ ?>
					<td><?php if($row['mb_2_name']) { echo $row['mb_2_fee']; } else { echo "&nbsp;"; } ?></td>
					<td></td>
					<td><?php if($row['mb_3_name']) { echo $row['mb_3_name']; } else { echo "&nbsp;"; } ?></td><?php /* 대리점 */ ?>
					<td><?php if($row['mb_3_name']) { echo $row['mb_3_fee']; } else { echo "&nbsp;"; } ?></td>
					<td></td>
					<td><?php if($row['mb_4_name']) { echo $row['mb_4_name']; } else { echo "&nbsp;"; } ?></td><?php /* 영업점 A */ ?>
					<td><?php if($row['mb_4_name']) { echo $row['mb_4_fee']; } else { echo "&nbsp;"; } ?></td>
					<td></td>
					<td><?php if($row['mb_5_name']) { echo $row['mb_5_name']; } else { echo "&nbsp;"; } ?></td><?php /* 영업점 B */ ?>
					<td><?php if($row['mb_5_name']) { echo $row['mb_5_fee']; } else { echo "&nbsp;"; } ?></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td><strong><?php echo $row['mb_6_fee']; ?></strong></td> <?php /* 가맹점 수수료 */ ?>
					<td></td><?php /* 원가 수수료 */ ?>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td style="text-align:right"><?php echo number_format($sum['sum_pay']); ?></td>
					<td><?php echo $row['dv_tid']; ?></td>
					<td><?php echo $dv_types." ".$dv_certis; ?></td>
					<td><?php echo $row['datetime']; ?></td>
					
					
					
					
					
					
				</tr>
				<?php } ?>
				<?php /*
				<tr>
					<td>
						<span class="txt_emph">네이티브 앱 키</span>
					</td>
					<td>
						2353db204034cd676a0159c81e193395
						<div class="float-right">
							<button type="button" class="KDC_Button__root__N26ep KDC_Button__mini_small__q6Jwf KDC_Button__color_cancel__TdcOV">복사</button>
						</div>
					</td>
					<td>
						<button type="button" class="KDC_Button__root__N26ep KDC_Button__mini_small__q6Jwf KDC_Button__color_cancel__TdcOV">재발급</button>
					</td>
				</tr>
				<tr>
					<td>
						<span class="txt_emph">REST API 키</span>
					</td>
					<td>
						0174a9739e7945a20ee3d11eab0b624d
						<div class="float-right">
							<button type="button" class="KDC_Button__root__N26ep KDC_Button__mini_small__q6Jwf KDC_Button__color_cancel__TdcOV">복사</button>
						</div>
					</td>
					<td>
						<button type="button" class="KDC_Button__root__N26ep KDC_Button__mini_small__q6Jwf KDC_Button__color_cancel__TdcOV">재발급</button>
					</td>
				</tr>
				<tr>
					<td>
						<span class="txt_emph">JavaScript 키</span>
					</td>
					<td>
						671e6ea2fc913f9fb9d42943a5508a8d
						<div class="float-right">
							<button type="button" class="KDC_Button__root__N26ep KDC_Button__mini_small__q6Jwf KDC_Button__color_cancel__TdcOV">복사</button>
						</div>
					</td>
					<td>
						<button type="button" class="KDC_Button__root__N26ep KDC_Button__mini_small__q6Jwf KDC_Button__color_cancel__TdcOV">재발급</button>
					</td>
				</tr>
				<tr>
					<td>
						<span class="txt_emph">Admin 키</span>
					</td>
					<td>
						1988233fac6f100ad760de67fc3681f4
						<div class="float-right">
							<button type="button" class="KDC_Button__root__N26ep KDC_Button__mini_small__q6Jwf KDC_Button__color_cancel__TdcOV">복사</button>
						</div>
					</td>
					<td>
						<button type="button" class="KDC_Button__root__N26ep KDC_Button__mini_small__q6Jwf KDC_Button__color_cancel__TdcOV">재발급</button>
					</td>
				</tr>
				*/ ?>
			</tbody>
		</table>
	</div>
</div>
<?php
	/*
	//http://cajung.com/new4/?p=payment&fr_date=20220906&to_date=20220906&sfl=mb_id&stx=
	$qstr = "p=".$p;
	$qstr .= "&fr_date=".$fr_date;
	$qstr .= "&to_date=".$to_date;
	$qstr .= "&sfl=".$sfl;
	$qstr .= "&stx=".$stx;
	echo get_paging_news(G5_IS_MOBILE ? "5" : "5", $page, $total_page, '?' . $qstr . '&amp;page=');
	*/
?>

