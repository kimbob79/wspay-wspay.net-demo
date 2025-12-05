<?php
	if(!$p) {
		alert("페이지가 없습니다.");
	}
?>

	<h4 class="mb-4 text-lg font-semibold text-gray-600 dark:text-gray-300">결제방식</h4>
	<div class="px-4 py-3 mb-8 bg-white shadow-md dark:bg-gray-800">
		<label class="block text-sm">
			<select name="payments" id="payments" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">
				<option value="">결제타입 선택</option>
				<option value="k1">광원 - 최대 500만원 / 10개월</option>
				<option value="danal">다날 - 최대 100만원 / 3개월</option>
				<option value="welcom">웰컴 - 최대 400만원 / 12개월</option>
				<option value="paysis">페이시스 - 최대 500만원 / 6개월</option>
			</select>
		</label>
	</div>

	<div id="payments_open" style="display:none">
		<h4 class="mb-4 text-lg font-semibold text-gray-600 dark:text-gray-300">상품정보</h4>
		<div class="px-4 py-3 mb-8 bg-white shadow-md dark:bg-gray-800">

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
		<div class="px-4 py-3 mb-8 bg-white shadow-md dark:bg-gray-800">

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
		<div class="px-4 py-3 mb-8 bg-white shadow-md dark:bg-gray-800">

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
					<div class="flex ml-2">
						<select name="expiry2" id="expiry2" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">
							<option value="">년</option>
							<?php
								$y = date("Y");
								$y2 = $y+6;
								for($i=$y; $i<=$y2; $i++) {
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

<?php
	include_once("./_tail.php");