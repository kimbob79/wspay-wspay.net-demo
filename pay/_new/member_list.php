<?php

	if($proc_mode=='login'){
		if($is_admin) {
			$mb = get_member($login_us_id);
		} else {
			alert('관리자만 가능합니다..');
			exit;
		}
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

	include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

	$yd = date('Y-m-d', strtotime('-1 day'));
	
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
?>
	<?php /*
	<h4 class="mb-4 text-lg font-semibold text-gray-600 dark:text-gray-300">Table with avatars</h4>
	*/ ?>
	<style>
		.member_in {padding:15px;}
		.member_in table th.title { color:#ffff00; padding:20px;}
		.member_in table th { width:120px;border:1px solid #555; border-left:0;font-size: .875rem;}
		.member_in table td { padding:5px; border:1px solid #555; border-right:0;font-size: .875rem;}
	</style>



	<form name="fnew" method="get">
		<div class="px-4 py-3 mb-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
			<div class="flex flex-col flex-wrap md:flex-row md:items-end md:space-x-4">
				<label>
					<input type="text" name="fr_date" required="" id="fr_date" value="<?php echo $fr_date; ?>" readonly class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" size="6">
				</label>
				<label>
					<input type="text" name="to_date" required="" id="to_date" value="<?php echo $to_date; ?>" readonly class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" size="6">
				</label>
				<label>
					<select name="payr" id="payr" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">
						<option value="0" <?php if(!$payr) { echo "selected"; } ?>>승인내역</option>
						<option value="2" <?php if($payr == "2") { echo "selected"; } ?>>실패내역</option>
						<option value="3" <?php if($payr == "3") { echo "selected"; } ?>>전체내역</option>
					</select>
				</label>

				<label>
					<div class="relative text-gray-500 focus-within:text-purple-600">
						<input name="authCd" placeholder="업체명" id="stx" value="<?php echo $authCd; ?>" class="block w-full pr-20 mt-1 text-sm text-black dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray form-input" placeholder="Jane Doe"/>
						<button type="submit" class="absolute inset-y-0 right-0 px-4 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-r-md active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">검색</button>
					</div>
				</label>
			</div>
		</div>
	</form>

	<div class="w-full mb-8 overflow-hidden rounded-lg shadow-xs">
		<div class="w-full overflow-x-auto">
			<table class="w-full whitespace-no-wrap">
				<thead>
					<tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
						<th class="px-4 py-3">
							그룹
						</th>
						<th class="px-4 py-3">
							보기
						</th>
						<th class="px-4 py-3">
							업체명
						</th>
						<th class="px-4 py-3">
							승인금액
						</th>
						<th class="px-4 py-3">
							결제
						</th>
						<th class="px-4 py-3">
							등록일
						</th>
						<th class="px-4 py-3">
							관리
						</th>
					</tr>
				</thead>
				<tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
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
								$mb_6 = "<span style='background:red;color:#fff;padding:4px 5px 2px;font-size:11px; margin:0 0.5px 0 0.5px;'>광원</span>";
							} else {
								$mb_6 = "";
							}

							if($row['mb_7'] == "1") {
								$mb_7 = "<span style='background:blue;color:#fff;padding:4px 5px 2px;font-size:11px; margin:0 0.5px 0 0.5px;'>다날</span>";
							} else {
								$mb_7 = "";
							}

							if($row['mb_8'] == "1") {
								$mb_8 = "<span style='background:#666;color:#fff;padding:4px 5px 2px;font-size:11px; margin:0 0.5px 0 0.5px;'>웰컴</span>";
							} else {
								$mb_8 = "";
							}

							if($row['mb_9'] == "1") {
								$mb_9 = "<span style='background:#D527B7;color:#fff;padding:4px 5px 2px;font-size:11px; margin:0 0.5px 0 0.5px;'>페페시스</span>";
							} else {
								$mb_9 = "";
							}

							if($row['mb_14'] == "1") {
								$mb_14 = "<span style='background:green;color:#fff;padding:4px 5px 2px;font-size:11px; margin:0 0.5px 0 0.5px;'>URL</span>";
							} else {
								$mb_14 = "";
							}

							if(!$row['mb_6']) {
								if(!$row['mb_7']) {
									if(!$row['mb_8']) {
										if(!$row['mb_9']) {
											$mb678 = "<span style='color:red;padding:4px 5px 2px;font-size:11px;'>결제불가</span>";
										}
									}
								}
							}

			//				if($row['mb_10'] == "10") { $row['mb_10'] = ""; }

					?>
					<tr class="text-gray-700 dark:text-gray-400" id="over_<?php echo $i; ?>s">
						<td class="px-4 py-3 text-sm"><?php echo $row['mb_10']; ?></td>
						<td class="px-4 py-3 text-sm">
							<button class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400 focus:outline-none focus:shadow-outline-gray" aria-label="Edit" onclick="opens('over_<?php echo $i; ?>');" id="overs" class="overs_tr tr_over_<?php echo $i; ?>">
								<svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20">
									<path d="M7.5 3.75H6A2.25 2.25 0 0 0 3.75 6v1.5M16.5 3.75H18A2.25 2.25 0 0 1 20.25 6v1.5m0 9V18A2.25 2.25 0 0 1 18 20.25h-1.5m-9 0H6A2.25 2.25 0 0 1 3.75 18v-1.5M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
								</svg>
							</button>
						</td>
						<td class="px-4 py-3 text-sm"><?php echo $row['mb_nick']; ?></td>
						<td class="px-4 py-3 text-sm"><?php echo number_format($sums['total_pay']); ?></td>
						<td class="px-4 py-3 text-xs"><?php echo $mb_6.$mb_7.$mb_8.$mb_9.$mb_14.$mb678; ?></td>
						<td class="px-4 py-3 text-sm"><?php echo $row['mb_datetime']; ?></td>
						<td class="px-4 py-3">
							<div class="flex items-center space-x-4 text-sm">
								<a class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400 focus:outline-none focus:shadow-outline-gray" aria-label="Edit" href="./?p=member_form&mb_id=<?php echo $row['mb_id']; ?>&w=u">
									<svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20">
										<path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
									</svg>
								</a>
								
								<button class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400 focus:outline-none focus:shadow-outline-gray" aria-label="Delete">
									<svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20">
										<path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
									</svg>
								</button>

								<a class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400 focus:outline-none focus:shadow-outline-gray" aria-label="Delete" href="./?p=member_list&proc_mode=login&login_us_id=<?=$row['mb_id']?>">
									<svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20">
										<path fill-rule="evenodd" d="M10 2a.75.75 0 0 1 .75.75v7.5a.75.75 0 0 1-1.5 0v-7.5A.75.75 0 0 1 10 2ZM5.404 4.343a.75.75 0 0 1 0 1.06 6.5 6.5 0 1 0 9.192 0 .75.75 0 1 1 1.06-1.06 8 8 0 1 1-11.313 0 .75.75 0 0 1 1.06 0Z" clip-rule="evenodd"></path>
									</svg>
								</a>
							</div>
						</td>
					</tr>

					<tr id="over_<?php echo $i; ?>" style="display:none;" class="overs">
						<td colspan="7" style="background:#333; color:#ccc; text-align:left;" class="member_in">
							<table width="100%">
								<tbody>
								<tr>
									<th colspan="2">
										<div style="width:100%; line-height:150%; padding:10px;" class="over_aad">
										- 레드페이 수기결제 접속정보 -<br>
										업체명 : <?php echo $row['mb_nick']; ?><br>
										아이디 : <?php echo $row['mb_id']; ?><br>
										비밀번호 : <?php echo $row['mb_1']; ?><br>
										접속주소 : http://pay.redpay.kr
										</div>
									</th>
								</tr>
								<tr>
									<th colspan="2" class="title">광원</th>
								</tr>
								<tr>
									<th class=" text-sm">사용</td>
									<td class=" text-sm"><?php if($row['mb_6'] == "1") { echo "가능"; } else { echo "불가능"; } ?></td>
								</tr>
								<tr>
									<th>TID</th>
									<td>WNK<?php echo $row['mb_id']; ?></td>
								</tr>
								<tr>
									<th>광원 TID</th>
									<td><?php if($row['mb_2']) { echo $row['mb_2']; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<th>광원 KEY</th>
									<td><?php echo $row['mb_3']; ?></td>
								</tr>
								<tr>
									<th colspan="2" class="title">다날</th>
								</tr>
								<tr>
									<th>사용</th>
									<td><?php if($row['mb_7'] == "1") { echo "가능"; } else { echo "불가능"; } ?></td>
								</tr>
								<tr>
									<th>다날 TID</th>
									<td><?php if($row['mb_4']) { echo $row['mb_4']; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<th>다날 KEY</th>
									<td><?php if($row['mb_5']) { echo $row['mb_5']; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<th colspan="2" class="title">웰컴</th>
								</tr>
								<tr>
									<th>사용</th>
									<td><?php if($row['mb_8'] == "1") { echo "가능"; } else { echo "불가능"; } ?></td>
								</tr>
								<tr>
									<th>웰컴 TID</th>
									<td>WNA<?php echo $row['mb_id']; ?></td>
								</tr>
								<tr>
									<th colspan="2" class="title">페이시스</th>
								</tr>
								<tr>
									<th>사용</th>
									<td><?php if($row['mb_9'] == "1") { echo "가능"; } else { echo "불가능"; } ?></td>
								</tr>
								<tr>
									<th>TID</th>
									<td>WNP<?php echo $row['mb_id']; ?></td>
								</tr>
								<tr>
									<th>페이시스 TID</th>
									<td><?php if($row['mb_11']) { echo $row['mb_11']; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<th>페이시스 ID</th>
									<td><?php if($row['mb_12']) { echo $row['mb_12']; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<th>페이시스 KEY</th>
									<td><?php if($row['mb_13']) { echo $row['mb_13']; } else { echo "-"; } ?></td>
								</tr>
								</tbody>
							</table>
						</td>
					</tr>
					<?php } ?>



				</tbody>
			</table>
		</div>
		<?php /*
		<div class="grid px-4 py-3 text-xs font-semibold tracking-wide text-gray-500 uppercase border-t dark:border-gray-700 bg-gray-50 sm:grid-cols-9 dark:text-gray-400 dark:bg-gray-800">
			<span class="flex items-center col-span-3">
			Showing 21-30 of 100 </span>
			<span class="col-span-2"></span>
			<!-- Pagination -->
			<span class="flex col-span-4 mt-2 sm:mt-auto sm:justify-end">
			<nav aria-label="Table navigation">
			<ul class="inline-flex items-center">
				<li>
				<button class="px-3 py-1 rounded-md rounded-l-lg focus:outline-none focus:shadow-outline-purple" aria-label="Previous">
				<svg class="w-4 h-4 fill-current" aria-hidden="true" viewbox="0 0 20 20">
				<path d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" fill-rule="evenodd"></path>
				</svg>
				</button>
				</li>
				<li>
				<button class="px-3 py-1 rounded-md focus:outline-none focus:shadow-outline-purple">
				1 </button>
				</li>
				<li>
				<button class="px-3 py-1 rounded-md focus:outline-none focus:shadow-outline-purple">
				2 </button>
				</li>
				<li>
				<button class="px-3 py-1 text-white transition-colors duration-150 bg-purple-600 border border-r-0 border-purple-600 rounded-md focus:outline-none focus:shadow-outline-purple">
				3 </button>
				</li>
				<li>
				<button class="px-3 py-1 rounded-md focus:outline-none focus:shadow-outline-purple">
				4 </button>
				</li>
				<li>
				<span class="px-3 py-1">...</span>
				</li>
				<li>
				<button class="px-3 py-1 rounded-md focus:outline-none focus:shadow-outline-purple">
				8 </button>
				</li>
				<li>
				<button class="px-3 py-1 rounded-md focus:outline-none focus:shadow-outline-purple">
				9 </button>
				</li>
				<li>
				<button class="px-3 py-1 rounded-md rounded-r-lg focus:outline-none focus:shadow-outline-purple" aria-label="Next">
				<svg class="w-4 h-4 fill-current" aria-hidden="true" viewbox="0 0 20 20">
				<path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" fill-rule="evenodd"></path>
				</svg>
				</button>
				</li>
			</ul>
			</nav>
			</span>
		</div>
		*/ ?>
	</div>
	<script>
		function opens(ids) {
			$("#"+ids).toggle();
		}
	</script>