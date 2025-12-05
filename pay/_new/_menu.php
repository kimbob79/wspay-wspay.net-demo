			<div class="logo dark:logo">
				<a class="ml-6 text-lg font-bold text-gray-800 dark:text-gray-200" href="./">
					<div style="float:left;margin-right:5px;margin-left:1.2rem;"><img src="./assets/img/logo.png" style='width:30px;'></div>
					<div style="float:left">Simple PAY</div>
				</a>
			</div>
			<ul class="menu dark:menu">
				<li class="relative px-6 py-3">
					<?php if($p == "main") { ?><span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span><?php } ?>
					<a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 <?php if($p == "main") { echo " text-gray-800 dark:text-gray-100"; } ?>" href="./">
						<svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor" style="margin:0.5rem 0 0;">
							<path clip-rule="evenodd" fill-rule="evenodd" d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z"></path>
						</svg>
						<span>HOME</span>
					</a>
				</li>
				<li class="relative px-6 py-3">
					<?php if($p == "payment") { ?><span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span><?php } ?>
					<a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 <?php if($p == "payment") { echo " text-gray-800 dark:text-gray-100"; } ?>" href="./?p=payment">
						<svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor" style="margin:0.5rem 0 0;">
							<path clip-rule="evenodd" fill-rule="evenodd" d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z"></path>
						</svg>
						<span>수기결제</span>
					</a>
				</li>
				<li class="relative px-6 py-3">
					<?php if($p == "urlpayment") { ?><span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span><?php } ?>
					<a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 <?php if($p == "urlpayment") { echo " text-gray-800 dark:text-gray-100"; } ?>" href="./?p=urlpayment">
						<svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor" style="margin:0.5rem 0 0;">
							<path clip-rule="evenodd" fill-rule="evenodd" d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z"></path>
						</svg>
						<span>URL결제</span>
					</a>
				</li>
				<li class="relative px-6 py-3">
					<?php if($p == "list") { ?><span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span><?php } ?>
					<a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 <?php if($p == "list") { echo " text-gray-800 dark:text-gray-100"; } ?>" href="./?p=list">
						<svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor" style="margin:0.5rem 0 0;">
							<path clip-rule="evenodd" fill-rule="evenodd" d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z"></path>
						</svg>
						<span>결제내역</span>
					</a>
				</li>
				<li class="relative px-6 py-3">
					<?php if($p == "member_list") { ?><span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span><?php } ?>
					<a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 <?php if($p == "member_list") { echo " text-gray-800 dark:text-gray-100"; } ?>" href="./?p=member_list">
						<svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor" style="margin:0.5rem 0 0;">
							<path clip-rule="evenodd" fill-rule="evenodd" d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z"></path>
						</svg>
						<span>회원관리</span>
					</a>
				</li>
				<li class="relative px-6 py-3">
					<button class="inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" @click="toggleadminMenu" aria-haspopup="true">
						<span class="inline-flex items-center">
							<svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor" style="margin:0.5rem 0 0;">
								<path clip-rule="evenodd" fill-rule="evenodd" d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z"></path>
							</svg>
							<span>제작</span>
						</span>
						<svg class="w-4 h-4" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20">
							<path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
						</svg>
					</button>
					<template x-if="isadminMenuOpen">
						<ul x-transition:enter="transition-all ease-in-out duration-300" x-transition:enter-start="opacity-25 max-h-0" x-transition:enter-end="opacity-100 max-h-xl" x-transition:leave="transition-all ease-in-out duration-300" x-transition:leave-start="opacity-100 max-h-xl" x-transition:leave-end="opacity-0 max-h-0" class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-gray-500 rounded-md shadow-inner bg-gray-50 dark:text-gray-400 dark:bg-gray-900" aria-label="submenu">
							<li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200">
								<a class="w-full" href="https://windmillui.com" target="_blank">제작사홈페이지</a>
							</li>
							<li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200">
								<a class="w-full" href="https://github.com/estevanmaito/windmill-dashboard" target="_blank">깃허브</a>
							</li>
							<li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200">
								<a class="w-full" href="https://heroicons.dev" target="_blank">아이콘</a>
							</li>
							<li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200">
								<a class="w-full" href="https://www.chartjs.org" target="_blank">차트</a>
							</li>
							<li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200">
								<a class="w-full" href="https://uifaces.co/" target="_blank">얼굴이미지</a>
							</li>
						</ul>
					</template>
				</li>
			</ul>
			<?php if($is_admin) { ?>
			<ul class="menu dark:menu">
				<li class="relative px-6 py-3">
					<a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 <?php if($p == "forms") { echo " text-gray-800 dark:text-gray-100"; } ?>" href="./forms.php">
						<svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
							<path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
						</svg>
						<span class="ml-4">Forms</span>
					</a>
				</li>
				<li class="relative px-6 py-3">
					<a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 <?php if($p == "cards") { echo " text-gray-800 dark:text-gray-100"; } ?>" href="./cards.php">
						<svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" stroke="currentColor">
							<path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
						</svg>
						<span class="ml-4">Cards</span>
					</a>
				</li>
				<li class="relative px-6 py-3">
					<a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 <?php if($p == "charts") { echo " text-gray-800 dark:text-gray-100"; } ?>" href="./charts.php">
						<svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" stroke="currentColor">
							<path d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
							<path d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
						</svg>
						<span class="ml-4">Charts</span>
					</a>
				</li>
				<li class="relative px-6 py-3">
					<a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 <?php if($p == "buttons") { echo " text-gray-800 dark:text-gray-100"; } ?>" href="./buttons.php">
						<svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" stroke="currentColor">
							<path d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path>
						</svg>
						<span class="ml-4">Buttons</span>
					</a>
				</li>
				<li class="relative px-6 py-3">
					<a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 <?php if($p == "modals") { echo " text-gray-800 dark:text-gray-100"; } ?>" href="./modals.php">
						<svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" stroke="currentColor">
							<path d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
						</svg>
						<span class="ml-4">Modals</span>
					</a>
				</li>
				<li class="relative px-6 py-3">
					<a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 <?php if($p == "tables") { echo " text-gray-800 dark:text-gray-100"; } ?>" href="./tables.php">
						<svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" stroke="currentColor">
							<path d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
						</svg>
						<span class="ml-4">Tables</span>
					</a>
				</li>
				<li class="relative px-6 py-3">
					<button class="inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" @click="togglePagesMenu" aria-haspopup="true">
						<span class="inline-flex items-center">
							<svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" stroke="currentColor">
								<path d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
							</svg>
							<span class="ml-4">Pages</span>
						</span>
						<svg class="w-4 h-4" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20">
							<path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
						</svg>
					</button>
					<template x-if="isPagesMenuOpen">
						<ul x-transition:enter="transition-all ease-in-out duration-300" x-transition:enter-start="opacity-25 max-h-0" x-transition:enter-end="opacity-100 max-h-xl" x-transition:leave="transition-all ease-in-out duration-300" x-transition:leave-start="opacity-100 max-h-xl" x-transition:leave-end="opacity-0 max-h-0" class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-gray-500 rounded-md shadow-inner bg-gray-50 dark:text-gray-400 dark:bg-gray-900" aria-label="submenu">
							<li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200">
								<a class="w-full" href="pages/login.html">Login</a>
							</li>
							<li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200">
								<a class="w-full" href="pages/create-account.html">Create account </a>
							</li>
							<li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200">
								<a class="w-full" href="pages/forgot-password.html">Forgot password </a>
							</li>
							<li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200">
								<a class="w-full" href="pages/404.html">404</a>
							</li>
							<li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200">
								<a class="w-full" href="pages/blank.html">Blank</a>
							</li>
						</ul>
					</template>
				</li>
			</ul>
			<?php } ?>
			<div class="px-6 my-6">
				<button class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">Create account <span class="ml-2" aria-hidden="true">+</span></button>
			</div>
			<div class="px-6 my-6 text-xs">
				Copyright © REDPAY.
			</div>


