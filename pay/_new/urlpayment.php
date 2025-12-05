<?php
	$page = "urlpayment";
	$page_title = "URL결제";
	include_once("./_head.php");



	if($is_admin) {
		$sql_common = " from pay_payment_url ";
	} else {
		$sql_common = " from pay_payment_url where  mb_id = '{$member['mb_id']}'";
	}

	// 테이블의 전체 레코드수만 얻음
	$sql = " select COUNT(*) as cnt {$sql_common} ";
	$row = sql_fetch($sql);
	$total_count = $row['cnt'];

	$rows = $config['cf_page_rows'];
	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) {
		$page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	}
	$from_record = ($page - 1) * $rows; // 시작 열을 구함

	$sql = " select * {$sql_common} order by pm_id desc ";
	$result = sql_query($sql);

?>

	<h4 class="mb-4 text-lg font-semibold text-gray-600 dark:text-gray-300">
		<a class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple" href="./?p=urlpayment_form">상품등록</a>
	</h4>
	<div class="w-full mb-8 overflow-hidden rounded-lg shadow-xs">
		<div class="w-full overflow-x-auto">
			<table class="w-full whitespace-no-wrap">
			<thead>
				<tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<th class="px-4 py-3">
						PG
					</th>
					<th class="px-4 py-3">
						상품명
					</th>
					<th class="px-4 py-3">
						상품가격
					</th>
					<th class="px-4 py-3">
						판매자명
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
					$num = number_format($total_count - ($page - 1) * $config['cf_page_rows'] - $i);
					if($row['url_payments'] == "k1") {
						$url_payments = "광원";
					} else if($row['url_payments'] == "danal") {
						$url_payments = "다날";
					} else if($row['url_payments'] == "welcom") {
						$url_payments = "웰컴";
					} else if($row['url_payments'] == "paysis") {
						$url_payments = "페이시스";
					} else {
						$url_payments = "<span style='color:red'>오류</span>";
					}
				?>
				<tr class="text-gray-700 dark:text-gray-400">
					<td class="px-4 py-3 items-center text-sm"><?php echo $url_payments; ?></td>
					<td class="px-4 py-3 items-center text-sm"><?php echo $row['url_pd']; ?></td>
					<td class="px-4 py-3 text-sm"><?php echo number_format($row['url_price']); ?></td>
					<td class="px-4 py-3 text-xs"><?php echo $row['url_pname']; ?></td>
					<td class="px-4 py-3 text-sm"><?php echo date("y-m-d H:i", strtotime($row['datetime'])); ?></td>
					<td class="px-4 py-3">
						<div class="flex items-center space-x-4 text-sm">
							<button class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400 focus:outline-none focus:shadow-outline-gray" aria-label="Edit">
							<svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20">
							<path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
							</svg>
							</button>
							<button class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400 focus:outline-none focus:shadow-outline-gray" aria-label="Delete">
							<svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20">
							<path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
							</svg>
							</button>

							<button class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400 focus:outline-none focus:shadow-outline-gray" aria-label="Delete">
							<svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20">
								<path fill-rule="evenodd" d="M3.75 2A1.75 1.75 0 0 0 2 3.75v3.5C2 8.216 2.784 9 3.75 9h3.5A1.75 1.75 0 0 0 9 7.25v-3.5A1.75 1.75 0 0 0 7.25 2h-3.5ZM3.5 3.75a.25.25 0 0 1 .25-.25h3.5a.25.25 0 0 1 .25.25v3.5a.25.25 0 0 1-.25.25h-3.5a.25.25 0 0 1-.25-.25v-3.5ZM3.75 11A1.75 1.75 0 0 0 2 12.75v3.5c0 .966.784 1.75 1.75 1.75h3.5A1.75 1.75 0 0 0 9 16.25v-3.5A1.75 1.75 0 0 0 7.25 11h-3.5Zm-.25 1.75a.25.25 0 0 1 .25-.25h3.5a.25.25 0 0 1 .25.25v3.5a.25.25 0 0 1-.25.25h-3.5a.25.25 0 0 1-.25-.25v-3.5Zm7.5-9c0-.966.784-1.75 1.75-1.75h3.5c.966 0 1.75.784 1.75 1.75v3.5A1.75 1.75 0 0 1 16.25 9h-3.5A1.75 1.75 0 0 1 11 7.25v-3.5Zm1.75-.25a.25.25 0 0 0-.25.25v3.5c0 .138.112.25.25.25h3.5a.25.25 0 0 0 .25-.25v-3.5a.25.25 0 0 0-.25-.25h-3.5Zm-7.26 1a1 1 0 0 0-1 1v.01a1 1 0 0 0 1 1h.01a1 1 0 0 0 1-1V5.5a1 1 0 0 0-1-1h-.01Zm9 0a1 1 0 0 0-1 1v.01a1 1 0 0 0 1 1h.01a1 1 0 0 0 1-1V5.5a1 1 0 0 0-1-1h-.01Zm-9 9a1 1 0 0 0-1 1v.01a1 1 0 0 0 1 1h.01a1 1 0 0 0 1-1v-.01a1 1 0 0 0-1-1h-.01Zm9 0a1 1 0 0 0-1 1v.01a1 1 0 0 0 1 1h.01a1 1 0 0 0 1-1v-.01a1 1 0 0 0-1-1h-.01Zm-3.5-1.5a1 1 0 0 1 1-1H12a1 1 0 0 1 1 1v.01a1 1 0 0 1-1 1h-.01a1 1 0 0 1-1-1V12Zm6-1a1 1 0 0 0-1 1v.01a1 1 0 0 0 1 1H17a1 1 0 0 0 1-1V12a1 1 0 0 0-1-1h-.01Zm-1 6a1 1 0 0 1 1-1H17a1 1 0 0 1 1 1v.01a1 1 0 0 1-1 1h-.01a1 1 0 0 1-1-1V17Zm-4-1a1 1 0 0 0-1 1v.01a1 1 0 0 0 1 1H12a1 1 0 0 0 1-1V17a1 1 0 0 0-1-1h-.01Z" clip-rule="evenodd"></path>
							</svg>
							</button>
						</div>
					</td>
				</tr>
				<?php
					}
				?>
			</tbody>
			</table>
		</div>
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
	</div>
	<!-- With actions -->








<div x-show="isModalOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-30 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center">
	<div x-show="isModalOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 transform translate-y-1/2" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 transform translate-y-1/2" @click.away="closeModal" @keydown.escape="closeModal" class="w-full px-6 py-4 overflow-hidden bg-white rounded-t-lg dark:bg-gray-800 sm:rounded-lg sm:m-4 sm:max-w-xl" role="dialog" id="modal">
		<header class="flex justify-end">
			<button class="inline-flex items-center justify-center w-6 h-6 text-gray-400 transition-colors duration-150 rounded dark:hover:text-gray-200 hover: hover:text-gray-700" aria-label="close" @click="closeModal">
				<svg class="w-4 h-4" fill="currentColor" viewbox="0 0 20 20" role="img" aria-hidden="true">
					<path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" fill-rule="evenodd"></path>
				</svg>
			</button>
		</header>

		<div>
			<h4 class="mb-4 text-lg font-semibold text-gray-600 dark:text-gray-300">상품정보</h4>
			<div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">

				<label class="mb-4 block text-sm">
					<span class="text-gray-700 dark:text-gray-400">상품명</span>
					<input class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="결제항 상품명을 입력해주세요"/>
				</label>

				<label class="mb-4 block text-sm">
					<span class="text-gray-700 dark:text-gray-400">결제금액</span>
					<input class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="결제항 상품의 금액을 입력해주세요"/>
				</label>

			</div>

			<h4 class="mb-4 text-lg font-semibold text-gray-600 dark:text-gray-300">개인정보</h4>
			<div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">

				<label class="mb-4 block text-sm">
					<span class="text-gray-700 dark:text-gray-400">성명</span>
					<input class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="구매자 성명을 입력해주세요"/>
				</label>

				<label class="mb-4 block text-sm">
					<span class="text-gray-700 dark:text-gray-400">휴대전화번호</span>
					<input class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="구매자 휴대전화번호를 입력해주세요"/>
				</label>

			</div>

			<h4 class="mb-4 text-lg font-semibold text-gray-600 dark:text-gray-300">카드정보</h4>
			<div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">

				<label class="mb-4 block text-sm">
					<span class="text-gray-700 dark:text-gray-400">카드번호</span>
					<input class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="카드번호를 입력해주세요"/>
				</label>

				<label class="mb-4 block text-sm">
					<span class="text-gray-700 dark:text-gray-400">유효기간</span>
					<div class="flex">
						<div class="flex">
							<select name="expiry2" id="expiry2" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">
								<option value="">월</option>
								<?php
									for($i=1; $i<=12; $i++) {
										$ii = sprintf('%02d', $i);
								?>
								<option value="<?php echo $ii; ?>"><?php echo $ii; ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="ml-2 flex">
							<select name="expiry2" id="expiry2" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">
								<option value="">년</option>
								<?php
									for($i=2024; $i<=2050; $i++) {
										$ii = substr($i,-2);
								?>
								<option value="<?php echo $ii; ?>"><?php echo $ii; ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
				</label>

				<label class="mb-4 block text-sm">
					<span class="text-gray-700 dark:text-gray-400">비밀번호</span>
					<input class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="비밀번호 앞 2자리"/>
				</label>

				<label class="mb-4 block text-sm">
					<span class="text-gray-700 dark:text-gray-400">생년월일/사업자등록번호</span>
					<input class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="개인 : 생년월일 / 법인 : 사업자등록번호"/>
				</label>
			</div>
		</div>


		<footer class="flex flex-col items-center justify-end px-6 py-3 -mx-6 -mb-4 space-y-4 sm:space-y-0 sm:space-x-6 sm:flex-row bg-gray-50 dark:bg-gray-800">
			<button @click="closeModal" class="w-full px-5 py-3 text-sm font-medium leading-5 text-white text-gray-700 transition-colors duration-150 border border-gray-300 rounded-lg dark:text-gray-400 sm:px-4 sm:py-2 sm:w-auto active:bg-transparent hover:border-gray-500 focus:border-gray-500 active:text-gray-500 focus:outline-none focus:shadow-outline-gray">Cancel</button>
			<button class="w-full px-5 py-3 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg sm:w-auto sm:px-4 sm:py-2 active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">Accept</button>
		</footer>
	</div>
</div>
<?php
	include_once("./_tail.php");