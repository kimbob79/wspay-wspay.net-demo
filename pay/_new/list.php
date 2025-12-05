<?php

	$yd = date('Y-m-d', strtotime('-1 day'));
	
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
//	echo $sql;
?>
	<?php /*
	<h4 class="mb-4 text-lg font-semibold text-gray-600 dark:text-gray-300">Table with avatars </h4>
	*/ ?>

	<form name="fnew" method="get">
		<input type="hidden" name="p" value="list">
		<div class="px-4 py-3 mb-4 bg-white rounded-lg shadow-md dark:bg-gray-800">

			<?php /*

			<div class="flex flex-col flex-wrap mb-4 space-y-4 md:flex-row md:items-end md:space-x-4">
				<label>
					<button type="submit" onclick="javascript:set_date('오늘');" class="px-3 py-1 text-xs font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-md active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple"><span>오늘</span></button>
				</label>
				<label>
					<button type="submit" onclick="javascript:set_date('어제');" class="px-3 py-1 text-xs font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-md active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple"><span>어제</span></button>
				</label>
				<label>
					<button type="submit" onclick="javascript:set_date('이번주');" class="px-3 py-1 text-xs font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-md active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple"><span>이번주</span></button>
				</label>
				<label>
					<button type="submit" onclick="javascript:set_date('지난주');" class="px-3 py-1 text-xs font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-md active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple"><span>지난주</span></button>
				</label>
				<label>
					<button type="submit" onclick="javascript:set_date('이번달');" class="px-3 py-1 text-xs font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-md active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple"><span>이번달</span></button>
				</label>
				<label>
					<button type="submit" onclick="javascript:set_date('지난달');" class="px-3 py-1 text-xs font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-md active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple"><span>지난달</span></button>
				</label>
			</div>
			*/ ?>

			<div class="flex flex-col flex-wrap md:flex-row md:items-end md:space-x-4">
				<?php
					if($is_admin) {
						$sql2 = " select * from {$g5['member_table']} where mb_level = '5' group by mb_name asc ";
						$result2 = sql_query($sql2);
				?>
				<label>
					<select class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">
						<option value="">전체업체</option>
						<?php for ($k=0; $row2=sql_fetch_array($result2); $k++) { ?>
						<option value="<?php echo $row2['mb_id']; ?>" <?php if($mb_id == $row2['mb_id']) { echo "selected"; } ?>><?php echo $row2['mb_nick']; ?></option>
						<?php } ?>
					</select>
				</label>
				<?php } ?>
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
						<input name="authCd" placeholder="승인번호" id="stx" value="<?php echo $authCd; ?>" class="block w-full pr-20 mt-1 text-sm text-black dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray form-input" placeholder="Jane Doe"/>
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
				<th class="px-4 py-3">업체명</th>
				<th class="px-4 py-3">PG</th>
				<th class="px-4 py-3">금액</th>
				<th class="px-4 py-3">상태</th>
				<th class="px-4 py-3">결제일시</th>
			</tr>
			</thead>
			<tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">

			<?php
				for ($i=0; $row=sql_fetch_array($result); $i++) {
					$num = number_format($total_count - ($page - 1) * $config['cf_page_rows'] - $i);
					$bg = 'bg'.($i%2);
					
					$receipt = substr($row['creates'],0,8)."/".$row['authCd'];

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
						$pg = "<span style='background:red;color:#fff;padding:4px 5px 2px;font-size:11px;'>광원</span>".$urlcode;
						$pgs = "광원";
					} else if($row['payments'] == "danal") {
						$pg = "<span style='background:blue;color:#fff;padding:4px 5px 2px;font-size:11px;'>다날</span>".$urlcode;
						$pgs = "다날";
					} else if($row['payments'] == "welcom") {
						$pg = "<span style='background:#666;color:#fff;padding:4px 5px 2px;font-size:11px;'>웰컴</span>".$urlcode;
						$pgs = "웰컴";
					} else if($row['payments'] == "paysis") {
						$pg = "<span style='background:#D527B7;color:#fff;padding:4px 5px 2px;font-size:11px;'>페이시스</span>".$urlcode;
						$pgs = "페이시스";
					} else {
						$pg = "";
					}
					// http://pay.mpc.icu/payment/list.php?mb_id=&fr_date=2024-01-07&to_date=2024-01-07&payr=0&authCd=
			?>

			<tr class="text-gray-700 dark:text-gray-400">
				<td class="px-4 py-3 text-sm"><?php if($is_admin) { ?><?php echo substr($mb['mb_nick'],0,12); ?><?php } else { ?><?php echo $row['payerName']; ?><?php } ?></td>
				<td class="px-4 py-3 text-sm"><?php echo $pg; ?></td>
				<td class="px-4 py-3 text-sm"><?php echo $pride; ?></td>
				<td class="px-4 py-3 text-xs"><button @click="openModal" class="px-4 py-2 text-xs font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple"><?php echo $advanceMsgs; ?></button></td>
				<td class="px-4 py-3 text-sm"><?php echo date("Y-m-d H:i:s", strtotime($row['creates'])); ?></td>
			</tr>


			<?php } ?>
			<?php /*
			<tr class="text-gray-700 dark:text-gray-400">
				<td class="px-4 py-3">
					<div class="flex items-center text-sm">
						<!-- Avatar with inset shadow -->
						<div class="relative hidden w-8 h-8 mr-3 rounded-full md:block">
							<img class="object-cover w-full h-full rounded-full" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-0.3.5&q=80&fm=jpg&crop=entropy&cs=tinysrgb&w=200&facepad=3&fit=facearea&s=707b9c33066bf8808c934c8ab394dff6" alt="" loading="lazy"/>
							<div class="absolute inset-0 rounded-full shadow-inner" aria-hidden="true">
							</div>
						</div>
						<div>
							<p class="font-semibold">
								Jolina Angelie
							</p>
							<p class="text-xs text-gray-600 dark:text-gray-400">
								 Unemployed
							</p>
						</div>
					</div>
				</td>
				<td class="px-4 py-3 text-sm">
					 $ 369.95
				</td>
				<td class="px-4 py-3 text-xs">
					<span class="px-2 py-1 font-semibold leading-tight text-orange-700 bg-orange-100 rounded-full dark:text-white dark:bg-orange-600">
					Pending </span>
				</td>
				<td class="px-4 py-3 text-sm">
					 6/10/2020
				</td>
			</tr>
			<tr class="text-gray-700 dark:text-gray-400">
				<td class="px-4 py-3">
					<div class="flex items-center text-sm">
						<!-- Avatar with inset shadow -->
						<div class="relative hidden w-8 h-8 mr-3 rounded-full md:block">
							<img class="object-cover w-full h-full rounded-full" src="https://images.unsplash.com/photo-1551069613-1904dbdcda11?ixlib=rb-1.2.1&q=80&fm=jpg&crop=entropy&cs=tinysrgb&w=200&fit=max&ixid=eyJhcHBfaWQiOjE3Nzg0fQ" alt="" loading="lazy"/>
							<div class="absolute inset-0 rounded-full shadow-inner" aria-hidden="true">
							</div>
						</div>
						<div>
							<p class="font-semibold">
								Sarah Curry
							</p>
							<p class="text-xs text-gray-600 dark:text-gray-400">
								 Designer
							</p>
						</div>
					</div>
				</td>
				<td class="px-4 py-3 text-sm">
					 $ 86.00
				</td>
				<td class="px-4 py-3 text-xs">
					<span class="px-2 py-1 font-semibold leading-tight text-red-700 bg-red-100 rounded-full dark:text-red-100 dark:bg-red-700">
					Denied </span>
				</td>
				<td class="px-4 py-3 text-sm">
					 6/10/2020
				</td>
			</tr>
			<tr class="text-gray-700 dark:text-gray-400">
				<td class="px-4 py-3">
					<div class="flex items-center text-sm">
						<!-- Avatar with inset shadow -->
						<div class="relative hidden w-8 h-8 mr-3 rounded-full md:block">
							<img class="object-cover w-full h-full rounded-full" src="https://images.unsplash.com/photo-1551006917-3b4c078c47c9?ixlib=rb-1.2.1&q=80&fm=jpg&crop=entropy&cs=tinysrgb&w=200&fit=max&ixid=eyJhcHBfaWQiOjE3Nzg0fQ" alt="" loading="lazy"/>
							<div class="absolute inset-0 rounded-full shadow-inner" aria-hidden="true">
							</div>
						</div>
						<div>
							<p class="font-semibold">
								Rulia Joberts
							</p>
							<p class="text-xs text-gray-600 dark:text-gray-400">
								 Actress
							</p>
						</div>
					</div>
				</td>
				<td class="px-4 py-3 text-sm">
					 $ 1276.45
				</td>
				<td class="px-4 py-3 text-xs">
					<span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100">
					Approved </span>
				</td>
				<td class="px-4 py-3 text-sm">
					 6/10/2020
				</td>
			</tr>
			<tr class="text-gray-700 dark:text-gray-400">
				<td class="px-4 py-3">
					<div class="flex items-center text-sm">
						<!-- Avatar with inset shadow -->
						<div class="relative hidden w-8 h-8 mr-3 rounded-full md:block">
							<img class="object-cover w-full h-full rounded-full" src="https://images.unsplash.com/photo-1546456073-6712f79251bb?ixlib=rb-1.2.1&q=80&fm=jpg&crop=entropy&cs=tinysrgb&w=200&fit=max&ixid=eyJhcHBfaWQiOjE3Nzg0fQ" alt="" loading="lazy"/>
							<div class="absolute inset-0 rounded-full shadow-inner" aria-hidden="true">
							</div>
						</div>
						<div>
							<p class="font-semibold">
								Wenzel Dashington
							</p>
							<p class="text-xs text-gray-600 dark:text-gray-400">
								 Actor
							</p>
						</div>
					</div>
				</td>
				<td class="px-4 py-3 text-sm">
					 $ 863.45
				</td>
				<td class="px-4 py-3 text-xs">
					<span class="px-2 py-1 font-semibold leading-tight text-gray-700 bg-gray-100 rounded-full dark:text-gray-100 dark:bg-gray-700">
					Expired </span>
				</td>
				<td class="px-4 py-3 text-sm">
					 6/10/2020
				</td>
			</tr>
			<tr class="text-gray-700 dark:text-gray-400">
				<td class="px-4 py-3">
					<div class="flex items-center text-sm">
						<!-- Avatar with inset shadow -->
						<div class="relative hidden w-8 h-8 mr-3 rounded-full md:block">
							<img class="object-cover w-full h-full rounded-full" src="https://images.unsplash.com/photo-1502720705749-871143f0e671?ixlib=rb-0.3.5&q=80&fm=jpg&crop=entropy&cs=tinysrgb&w=200&fit=max&s=b8377ca9f985d80264279f277f3a67f5" alt="" loading="lazy"/>
							<div class="absolute inset-0 rounded-full shadow-inner" aria-hidden="true">
							</div>
						</div>
						<div>
							<p class="font-semibold">
								Dave Li
							</p>
							<p class="text-xs text-gray-600 dark:text-gray-400">
								 Influencer
							</p>
						</div>
					</div>
				</td>
				<td class="px-4 py-3 text-sm">
					 $ 863.45
				</td>
				<td class="px-4 py-3 text-xs">
					<span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100">
					Approved </span>
				</td>
				<td class="px-4 py-3 text-sm">
					 6/10/2020
				</td>
			</tr>
			<tr class="text-gray-700 dark:text-gray-400">
				<td class="px-4 py-3">
					<div class="flex items-center text-sm">
						<!-- Avatar with inset shadow -->
						<div class="relative hidden w-8 h-8 mr-3 rounded-full md:block">
							<img class="object-cover w-full h-full rounded-full" src="https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?ixlib=rb-1.2.1&q=80&fm=jpg&crop=entropy&cs=tinysrgb&w=200&fit=max&ixid=eyJhcHBfaWQiOjE3Nzg0fQ" alt="" loading="lazy"/>
							<div class="absolute inset-0 rounded-full shadow-inner" aria-hidden="true">
							</div>
						</div>
						<div>
							<p class="font-semibold">
								Maria Ramovic
							</p>
							<p class="text-xs text-gray-600 dark:text-gray-400">
								 Runner
							</p>
						</div>
					</div>
				</td>
				<td class="px-4 py-3 text-sm">
					 $ 863.45
				</td>
				<td class="px-4 py-3 text-xs">
					<span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100">
					Approved </span>
				</td>
				<td class="px-4 py-3 text-sm">
					 6/10/2020
				</td>
			</tr>
			<tr class="text-gray-700 dark:text-gray-400">
				<td class="px-4 py-3">
					<div class="flex items-center text-sm">
						<!-- Avatar with inset shadow -->
						<div class="relative hidden w-8 h-8 mr-3 rounded-full md:block">
							<img class="object-cover w-full h-full rounded-full" src="https://images.unsplash.com/photo-1566411520896-01e7ca4726af?ixlib=rb-1.2.1&q=80&fm=jpg&crop=entropy&cs=tinysrgb&w=200&fit=max&ixid=eyJhcHBfaWQiOjE3Nzg0fQ" alt="" loading="lazy"/>
							<div class="absolute inset-0 rounded-full shadow-inner" aria-hidden="true">
							</div>
						</div>
						<div>
							<p class="font-semibold">
								Hitney Wouston
							</p>
							<p class="text-xs text-gray-600 dark:text-gray-400">
								 Singer
							</p>
						</div>
					</div>
				</td>
				<td class="px-4 py-3 text-sm">
					 $ 863.45
				</td>
				<td class="px-4 py-3 text-xs">
					<span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100">
					Approved </span>
				</td>
				<td class="px-4 py-3 text-sm">
					 6/10/2020
				</td>
			</tr>
			<tr class="text-gray-700 dark:text-gray-400">
				<td class="px-4 py-3">
					<div class="flex items-center text-sm">
						<!-- Avatar with inset shadow -->
						<div class="relative hidden w-8 h-8 mr-3 rounded-full md:block">
							<img class="object-cover w-full h-full rounded-full" src="https://images.unsplash.com/flagged/photo-1570612861542-284f4c12e75f?ixlib=rb-1.2.1&q=80&fm=jpg&crop=entropy&cs=tinysrgb&w=200&fit=max&ixid=eyJhcHBfaWQiOjE3Nzg0fQ" alt="" loading="lazy"/>
							<div class="absolute inset-0 rounded-full shadow-inner" aria-hidden="true">
							</div>
						</div>
						<div>
							<p class="font-semibold">
								Hans Burger
							</p>
							<p class="text-xs text-gray-600 dark:text-gray-400">
								 10x Developer
							</p>
						</div>
					</div>
				</td>
				<td class="px-4 py-3 text-sm">
					 $ 863.45
				</td>
				<td class="px-4 py-3 text-xs">
					<span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100">
					Approved </span>
				</td>
				<td class="px-4 py-3 text-sm">
					 6/10/2020
				</td>
			</tr>
			*/ ?>
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
				<svg aria-hidden="true" class="w-4 h-4 fill-current" viewbox="0 0 20 20">
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

<div x-show="isModalOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-30 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center">
	<div x-show="isModalOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 transform translate-y-1/2" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 transform translate-y-1/2" @click.away="closeModal" @keydown.escape="closeModal" class="w-full px-6 py-4 overflow-hidden bg-white rounded-t-lg dark:bg-gray-800 sm:rounded-lg sm:m-4 sm:max-w-xl" role="dialog" id="modal">
		<header class="flex justify-end">
			<button class="inline-flex items-center justify-center w-6 h-6 text-gray-400 transition-colors duration-150 rounded dark:hover:text-gray-200 hover: hover:text-gray-700" aria-label="close" @click="closeModal">
			<svg class="w-4 h-4" fill="currentColor" viewbox="0 0 20 20" role="img" aria-hidden="true">
				<path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" fill-rule="evenodd"></path>
			</svg>
			</button>
		</header>
		<div class="mt-4 mb-6">
			<p class="mb-2 text-lg font-semibold text-gray-700 dark:text-gray-300">Modal header</p>
			<p class="text-sm text-gray-700 dark:text-gray-400">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Nostrum et eligendi repudiandae voluptatem tempore!</p>
		</div>
		<footer class="flex flex-col items-center justify-end px-6 py-3 -mx-6 -mb-4 space-y-4 sm:space-y-0 sm:space-x-6 sm:flex-row bg-gray-50 dark:bg-gray-800">
			<button @click="closeModal" class="w-full px-5 py-3 text-sm font-medium leading-5 text-white text-gray-700 transition-colors duration-150 border border-gray-300 rounded-lg dark:text-gray-400 sm:px-4 sm:py-2 sm:w-auto active:bg-transparent hover:border-gray-500 focus:border-gray-500 active:text-gray-500 focus:outline-none focus:shadow-outline-gray">Cancel</button>
			<button class="w-full px-5 py-3 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg sm:w-auto sm:px-4 sm:py-2 active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">Accept</button>
		</footer>
	</div>
</div>

