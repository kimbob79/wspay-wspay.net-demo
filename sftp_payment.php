<?php
	$title1 = "결제관리";
	$title2 = "실시간 결제내역";

	if(!$fr_datee) { $fr_datee = date("Ymd", strtotime('-1 day')); }
	$fr_dates = date("Y-m-d", strtotime($fr_datee));

	$sql .= " select * from g5_sftp_member order by datetime desc ";
	$result = sql_query($sql);

//	echo $sql."<br><br>";
//	echo $total_count;
?>

<div class="index_menu">
	<ul class="shortcut">
		<li class="sc_current"><a>차액정산 데이터 생성</a></li>
		<li class="sc_visit">
		</li>
	</ul>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<input type="hidden" name="p" value="<?php echo $p; ?>">
	<div class="searchbox">
		<div class="midd">
			<ul>
				<li>
					<strong>일자</strong>
					<div>
						<div>
							<input type="text" name="fr_datee" value="<?php echo $fr_datee ?>" id="fr_datee" class="frm_input" size="6" maxlength="10">
							<button type="submit" class="btn_b btn_b02"><span>검색</span></button>
						</div>
					</div>
				</li>
				<li>
					<strong>생성</strong>
					<div>
						<button type="button" onclick="sftp_data('<?php echo $fr_datee; ?>');" class="btn_b btn_b02"><span>차액정산 데이터파일 생성하기</span></button>
					</div>
				</li>
			</ul>
		</div>
	</div>
</form>

<div class="m_board_scroll">
	<div class="m_table_wrap" style="padding-bottom:115px;">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd">
			<thead>
				<tr>
					<th>구분</th>
					<th>오픈마켓사업자</th>
					<th>사업자등록번호</th>
					<th>업종명</th>

					<th>회사명</th>
					<th>주소</th>
					<th>대표자명</th>
					<th>전화번호</th>
					<th>이메일</th>
					<th>홈페이지</th>
					<th>MBR</th>

					<th>수정일시</th>
					<th>등록일시</th>
					<th>반송코드</th>
				</tr>
			</thead>
			<tbody>
				<?php
					for ($i=0; $row=sql_fetch_array($result); $i++) {
						if($row['sm_type'] == 00) {
							$sm_type = "신규";
						} else if($row['sm_type'] == 01) {
							$sm_type = "해지";
						} else if($row['dv_pg'] == 02) {
							$sm_type = "변경";
						}
				?>
				<tr class='<?php echo $bgcolor; ?>'>
					<td><?php echo $sm_type; ?></td>
					<td><?php echo $row['sm_openmarket']; ?></td>
					<td><?php echo $row['sm_bnumber']; ?></td>
					<td><?php echo $row['sm_btype']; ?></td>
					<td><?php echo $row['sm_bname']; ?></td>
					<td><?php echo $row['sm_addr']; ?></td>
					<td><?php echo $row['sm_ceo']; ?></td>
					<td><?php echo $row['sm_tel']; ?></td>
					<td><?php echo $row['sm_email']; ?></td>
					<td><?php echo $row['sm_website']; ?></td>
					<td><?php echo $row['sm_mbrno']; ?></td>
					<td><?php echo $row['updatetime']; ?></td>
					<td><?php echo $row['datetime']; ?></td>
					<td><?php echo $row['sm_error']; ?></td>
				</tr>
				<tr>
					<td style="background:#eee;">파일명</td>
					<td colspan="13" style="text-align:left;background:#eee;"><?php echo $row['sm_bnumber']; ?>_REQUEST.<?php echo date("ymd"); ?></td>
				</tr>
				<tr>
					<td style="background:#DDD;">매입자료</td>
					<td style="background:#DDD;" colspan="13">
					<?php
						$sql_data = " select * from g5_payment where sftp_mbrno = '".$row['sm_mbrno']."' and (pay_datetime BETWEEN '".$fr_dates." 00:00:00' and '".$fr_dates." 23:59:59') order by datetime desc ";
					//	echo $sql_data;
						$result_data = sql_query($sql_data);
					?>
					<table class="table_list td_pd">
						<tr>
							<th colspan="3">Header Record (200byte)</th>
						</tr>
						<tr>
							<th>레코드구분</th>
							<th>파일생성일자</th>
							<th>공백</th>
						</tr>
						<tr>
							<td>HD</td>
							<td><?php echo date("ymd"); ?></td>
							<td></td>
						</tr>

						<tr>
							<th colspan="13">Data Record (200byte)</th>
						</tr>
						<tr>
							<th>레코드구분</th>
							<th>매입취소구분</th>
							<th>거래일자</th>
							<th>중간하위사업자번호</th>
							<th>최종하위사업자번호</th>
							<th>가맹점번호</th>
							<th>거래번호</th>
							<th>전문구분</th>
							<th>주문번호</th>
							<th>매출액</th>
							<th>매입금액</th>
							<th>검증값</th>
							<th>공백</th>
						</tr>
						<?php
							for ($k=0; $row_data=sql_fetch_array($result_data); $k++) {

								$mb = get_member($row_data['mb_6']);

								// 승인취소
								if($row_data['pay_type'] == "Y") {
									$pay_type = 0;
									$pay_type2 = "CA";
								} else if($row_data['pay_type'] == "N") {
									$pay_type = 1;
									$pay_type2 = "CC";
								}

								// 승일일자
								$pay_datetime = date("ymd", strtotime($row_data['pay_datetime']));

								// 하위사업자번호
								$sm_bnumber = preg_replace('/[^0-9]*/s', '', $row['sm_bnumber']); // 사업자등록번호

								$mbrno = $row_data['sftp_mbrno'];

								// 결제금액
								$pay = abs($row_data['pay']);

								// 금액 합계
								$total_pay = $total_pay + $row_data['pay'];


						?>
						<tr>
							<td>DT</td>
							<td><?php echo $pay_type; ?></td>
							<td><?php echo $pay_datetime; ?></td>
							<td><?php echo $company_number; ?></td>
							<td><?php echo $sm_bnumber; ?></td>
							<td><?php echo $mbrno; ?></td>
							<td><?php echo str_pad($row_data['trxid'], 20); ?></td>
							<td><?php echo $pay_type2; ?></td>
							<td><?php echo str_pad($row_data['trackId'], 40); ?></td>
							<td style="text-align:right"><?php echo str_pad($pay, 15); ?></td>
							<td style="text-align:right"><?php echo str_pad($pay, 15); ?></td>
							<td><?php echo str_pad($space, 30); ?></td>
							<td><?php echo str_pad($space, 43); ?></td>
						</tr>
						<?php
							}
						?>
						<tr>
							<th colspan="3">Total Record (200byte)</th>
						</tr>
						<tr>
							<th>레코드구분</th>
							<th>총 건수 합계</th>
							<th>총 매출액 합계</th>
							<th>공백</th>
						</tr>
						<tr>
							<td>TR</td>
							<td><?php echo sprintf('%07d',$k); ?></td>
							<td><?php echo sprintf('%08d',$total_pay); ?></td>
							<td><?php echo str_pad($space, 150); ?></td>
						</tr>
					</table>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>
<?php
	//http://cajung.com/new4/?p=payment&fr_date=20220906&to_date=20220906&sfl=mb_id&stx=
	$qstr = "p=".$p;
	$qstr .= "&fr_date=".$fr_date;
	$qstr .= "&to_date=".$to_date;
	$qstr .= "&expansion=".$expansion;
	$qstr .= "&sfl=".$sfl;
	$qstr .= "&stx=".$stx;
	echo get_paging_news(G5_IS_MOBILE ? "5" : "5", $page, $total_page, '?' . $qstr . '&amp;page=');
?>

<script>


$(function(){
	$("#fr_datee").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yymmdd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "-1d" });
});
</script>