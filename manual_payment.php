<?php
include_once('./_common.php');

// 수기결제 권한 체크
if(!$is_admin && $member['mb_mailling'] != '1') {
	alert("수기결제 권한이 없습니다.");
}

$title1 = "수기결제";
$title2 = "수기결제";

include_once('./_head.php');
?>

<style>
.manual-payment-container {
	max-width: 480px;
	margin: 0 auto;
}

.manual-payment-header {
	background: linear-gradient(135deg, #FFB300 0%, #FFC107 100%);
	border-radius: 16px;
	padding: 20px 24px;
	margin-bottom: 20px;
	position: relative;
	overflow: hidden;
}

.manual-payment-header:before {
	content: '';
	position: absolute;
	top: 0;
	right: 0;
	width: 200px;
	height: 200px;
	background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
	border-radius: 50%;
	transform: translate(30%, -30%);
}

.manual-payment-header h2 {
	color: white;
	font-size: 20px;
	font-weight: 700;
	margin: 0;
	display: flex;
	align-items: center;
	gap: 12px;
	position: relative;
	z-index: 1;
}

.manual-payment-header h2 i {
	font-size: 24px;
}

.pg-select-area {
	background: #fff;
	border-radius: 16px;
	padding: 20px;
	margin-bottom: 16px;
	border: 1px solid #e8e8e8;
	transition: all 0.3s ease;
}

.pg-select-area:hover {
	border-color: #FFB300;
}

.pg-select-area h3 {
	font-size: 16px;
	font-weight: 700;
	color: #1a1a1a;
	margin-bottom: 12px;
	padding-bottom: 12px;
	border-bottom: 3px solid #FFB300;
	display: flex;
	align-items: center;
	gap: 8px;
}

.pg-select-area h3 i {
	font-size: 18px;
	color: #FFB300;
}

.pg-module-list {
	display: flex;
	flex-direction: column;
	gap: 12px;
	margin-top: 16px;
}

.pg-module-item {
	border: 1px solid #e8e8e8;
	border-radius: 12px;
	padding: 16px 18px;
	cursor: pointer;
	transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
	background: linear-gradient(135deg, #fff 0%, #fafafa 100%);
	display: flex;
	align-items: center;
	justify-content: space-between;
	position: relative;
}


.pg-module-item:hover {
	border-color: #FFB300;
	transform: translateY(-2px) scale(1.01);
	background: linear-gradient(135deg, #fff 0%, #FFF8E1 100%);
}

.pg-module-item.selected {
	border-color: #FFB300;
	background: linear-gradient(135deg, #FFF8E1 0%, #FFECB3 100%);
	transform: scale(1.02);
}

.pg-module-left {
	flex: 1;
}

.pg-module-name {
	font-size: 16px;
	font-weight: 700;
	color: #1a1a1a;
	margin-bottom: 6px;
	letter-spacing: -0.3px;
}

.pg-module-info-row {
	display: flex;
	gap: 16px;
	align-items: center;
}

.pg-module-info {
	font-size: 12px;
	color: #666;
	font-weight: 500;
	display: flex;
	align-items: center;
	gap: 4px;
}

.pg-module-info:before {
	content: '•';
	color: #FFB300;
	font-weight: bold;
	font-size: 14px;
}

.pg-module-badge {
	display: inline-flex;
	align-items: center;
	padding: 8px 16px;
	border-radius: 20px;
	font-size: 12px;
	font-weight: 700;
	flex-shrink: 0;
	letter-spacing: 0.3px;
	transition: all 0.3s ease;
}

.pg-module-item.selected .pg-module-badge {
	transform: scale(1.05);
}

.pg-module-badge.certified-old {
	background: linear-gradient(135deg, #2196F3, #1976d2);
	color: #fff;
}

.pg-module-badge.certified-none {
	background: linear-gradient(135deg, #FF9800, #F57C00);
	color: #fff;
}

.pg-module-badge.certified-new {
	background: linear-gradient(135deg, #4CAF50, #388e3c);
	color: #fff;
}

table.table_pg {
	width:100%;
	border-collapse: collapse;
	text-align: left;
	line-height: 1.5;
	border: 1px solid #ddd;
	border-top: 1px solid #111;
}

table.table_pg th {
	width: 20%;
	padding: 10px;
	font-weight: normal;
	color: #369;
	border-bottom: 1px solid #ddd;
	background: #f3f6f7;
	font-size:11px;
}

table.table_pg td {
	width: 30%;
	padding: 10px;
	border-bottom: 1px solid #ddd;
}

.payment-form-area {
	display: none;
}

.payment-form-area.active {
	display: block;
}

/* Stripe 스타일 결제 폼 - 컴팩트 버전 */
.stripe-payment-form {
	background: #fff;
	border-radius: 16px;
	padding: 20px;
	margin-top: 16px;
	border: 1px solid #e8e8e8;
	transition: all 0.3s ease;
}

.stripe-payment-form:hover {
	border-color: #FFB300;
}

.stripe-form-group {
	margin-bottom: 12px;
}

.stripe-form-label {
	display: block;
	font-size: 13px;
	font-weight: 700;
	color: #1a1a1a;
	margin-bottom: 7px;
	letter-spacing: -0.2px;
}

.stripe-form-input {
	width: 100%;
	padding: 12px 14px;
	font-size: 14px;
	line-height: 1.5;
	color: #1a1a1a;
	background-color: #fff;
	border: 1px solid #e8e8e8;
	border-radius: 10px;
	transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
	font-weight: 500;
}

.stripe-form-input:hover {
	border-color: #d0d0d0;
}

select.stripe-form-input {
	height: 48px;
	padding: 12px 40px 12px 14px;
	appearance: none;
	-webkit-appearance: none;
	-moz-appearance: none;
	background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23FFB300' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
	background-repeat: no-repeat;
	background-position: right 12px center;
	background-size: 18px;
	cursor: pointer;
}

select.stripe-form-input option {
	padding: 10px;
	line-height: 1.5;
	font-weight: 500;
}

.stripe-form-input:focus {
	outline: none;
	border-color: #FFB300;
	background-color: #fff;
}

.stripe-form-input::placeholder {
	color: #aab7c4;
}

.stripe-form-input:disabled {
	background-color: #f6f9fc;
	color: #8898aa;
	cursor: not-allowed;
}

.stripe-form-row {
	display: flex;
	gap: 10px;
	margin-bottom: 12px;
}

.stripe-form-col {
	flex: 1;
}

.stripe-form-col-small {
	flex: 0 0 100px;
}

.stripe-card-element {
	position: relative;
}

.stripe-card-icon {
	position: absolute;
	right: 12px;
	top: 50%;
	transform: translateY(-50%);
	font-size: 18px;
	color: #aab7c4;
	pointer-events: none;
}

.stripe-section-title {
	font-size: 15px;
	font-weight: 700;
	color: #1a1a1a;
	margin-bottom: 14px;
	margin-top: 20px;
	padding: 10px 14px;
	background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
	border-radius: 10px;
	display: flex;
	align-items: center;
	gap: 8px;
}

.stripe-section-title i {
	color: #FFB300;
	font-size: 16px;
}

.stripe-section-title:first-of-type {
	margin-top: 0;
}

.stripe-pay-button {
	width: 100%;
	padding: 16px 24px;
	font-size: 16px;
	font-weight: 700;
	color: #fff;
	background: linear-gradient(135deg, #FFB300 0%, #FFA000 100%);
	border: none;
	border-radius: 12px;
	cursor: pointer;
	transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
	letter-spacing: 0.5px;
	position: relative;
	overflow: hidden;
}

.stripe-pay-button:before {
	content: '';
	position: absolute;
	top: 0;
	left: -100%;
	width: 100%;
	height: 100%;
	background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
	transition: left 0.5s ease;
}

.stripe-pay-button:hover {
	transform: translateY(-2px);
	background: linear-gradient(135deg, #FFC107 0%, #FFB300 100%);
}

.stripe-pay-button:hover:before {
	left: 100%;
}

.stripe-pay-button:active {
	transform: translateY(0);
}

.stripe-pay-button:disabled {
	background: #aab7c4;
	cursor: not-allowed;
	transform: none;
	box-shadow: none;
}

.stripe-cancel-button {
	width: 100%;
	padding: 8px 16px;
	font-size: 13px;
	font-weight: 600;
	color: #6b7c93;
	background: #fff;
	border: 1px solid #e6e6e6;
	border-radius: 5px;
	cursor: pointer;
	transition: all 0.15s ease;
	letter-spacing: 0.02em;
	margin-top: 6px;
}

.stripe-cancel-button:hover {
	background: #f6f9fc;
	border-color: #d1d1d1;
}

.stripe-secure-badge {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 8px;
	margin-top: 12px;
	padding: 10px 16px;
	background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
	border-radius: 10px;
	font-size: 12px;
	color: #2e7d32;
	font-weight: 600;
	border: 1px solid #4caf50;
}

.stripe-secure-badge i {
	color: #4caf50;
	font-size: 16px;
	animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
	0%, 100% { transform: scale(1); }
	50% { transform: scale(1.1); }
}

.stripe-checkbox-wrapper {
	display: flex;
	align-items: center;
	gap: 10px;
	padding: 12px 14px;
	background: linear-gradient(135deg, #f8f9fa 0%, #f0f0f0 100%);
	border-radius: 10px;
	margin-bottom: 8px;
	cursor: pointer;
	transition: all 0.2s ease;
	border: 1px solid transparent;
}

.stripe-checkbox-wrapper:hover {
	background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
	border-color: #FFB300;
	transform: translateX(2px);
}

.stripe-checkbox {
	width: 20px;
	height: 20px;
	border: 1px solid #d0d0d0;
	border-radius: 5px;
	cursor: pointer;
	accent-color: #FFB300;
	flex-shrink: 0;
	transition: all 0.2s ease;
}

.stripe-checkbox:hover {
	border-color: #FFB300;
}

.stripe-checkbox:checked {
	border-color: #FFB300;
	background: #FFB300;
}

.stripe-checkbox-label {
	font-size: 13px;
	color: #1a1a1a;
	cursor: pointer;
	user-select: none;
	line-height: 1.4;
	font-weight: 500;
}

.stripe-link {
	color: #FFB300;
	text-decoration: none;
	font-size: 12px;
	transition: all 0.2s ease;
	margin-left: 4px;
	font-weight: 600;
}

.stripe-link:hover {
	color: #FFA000;
	text-decoration: underline;
	transform: translateX(2px);
}

.stripe-helper-text {
	font-size: 11px;
	color: #6b7c93;
	margin-top: 4px;
	line-height: 1.3;
}

.stripe-error-message {
	font-size: 13px;
	color: #fa755a;
	margin-top: 6px;
	display: none;
}

.stripe-form-input.error {
	border-color: #fa755a;
}

.stripe-form-input.error:focus {
	box-shadow: 0 0 0 3px rgba(250, 117, 90, 0.1), 0 1px 3px 0 rgba(250, 117, 90, 0.3);
}

.stripe-button-group {
	margin-top: 14px;
}

@media (max-width: 768px) {
	.manual-payment-header {
		padding: 16px 18px;
		border-radius: 12px;
		margin-bottom: 16px;
	}

	.manual-payment-header h2 {
		font-size: 18px;
	}

	.manual-payment-header h2 i {
		font-size: 20px;
	}

	.pg-select-area {
		padding: 16px;
		border-radius: 12px;
	}

	.pg-select-area h3 {
		font-size: 15px;
	}

	.pg-module-item {
		padding: 14px 16px;
	}

	.stripe-payment-form {
		padding: 16px;
		border-radius: 12px;
	}

	.stripe-form-row {
		flex-direction: column;
		gap: 0;
	}

	.stripe-form-col-small {
		flex: 1;
	}

	.stripe-section-title {
		font-size: 14px;
		margin-top: 16px;
		margin-bottom: 12px;
		padding: 8px 12px;
	}

	.stripe-form-group {
		margin-bottom: 10px;
	}

	select.stripe-form-input,
	.merchant-select select {
		height: 46px;
		font-size: 14px;
	}

	.stripe-pay-button {
		padding: 14px 20px;
		font-size: 15px;
	}
}

.all-check {
	margin: 2em 0 0.5em;
}

.all-check input[type=checkbox]:checked+label {
	border: 1px solid #FFB300;
}

.all-check input[type=checkbox]:checked+label span i {
	background: url('./npay/img/checkbox_all.png') 0 -16px no-repeat;
	background-size: 100%;
}

.all-check input[type=checkbox] {
	display: none;
}

input:read-only {
	color: #999;
	background-color: #fff;
}

.all-check label {
	display: block;
	width: 100%;
	padding: 0.9em;
	text-align: center;
}

.all-check label, .select-wrapper select {
	position: relative;
	background:#fff;
	border: 1px solid #e5e5e5;
}

.all-check input+label span {
	position: relative;
	display: inline-block;
}

.all-check input[type=checkbox]+label span i {
	display: inline-block;
	margin-right: 10px;
	vertical-align: -3px;
	width: 18px;
	height: 16px;
	background: url('./npay/img/checkbox_all.png') 0 0 no-repeat;
	background-size: 100%;
}

.agreement-wrap ul li {
	padding: 0.3em 0;
	font-weight: 400;
	font-size: 1.3rem;
}

.input-chk {
	position: relative;
	display: inline-block;
	font-weight: 400;
	line-height: 24px;
	color: #666;
}

.input-chk input[type=checkbox] {
	display: none;
}

.input-chk input+span {
	display: inline-block;
	margin-left: 34px;
}

.input-chk input[type=checkbox]:checked+span:before {
	background: url('./npay/img/checkbox.png') 0 -20px no-repeat;
	background-size: 100%;
}

.input-chk input[type=checkbox]+span:before {
	position: absolute;
	left: 0;
	top: 5px;
	content: " ";
	display: inline-block;
	width: 20px;
	height: 20px;
	background: url('./npay/img/checkbox.png') 0 0 no-repeat;
	background-size: 100%;
}

.agreement-wrap ul li a {
	float: right;
	color: #999;
	text-decoration: underline;
	line-height: 24px;
}

.wrap-loading {
	position: fixed;
	left:0;
	right:0;
	top:0;
	bottom:0;
	background: #fff;
	z-index: 9;
}

.wrap-loading div {
	position: fixed;
	top:50%;
	left:50%;
	margin-left: -100px;
	margin-top: -205px;
}

.display-none {
	display:none;
}

.merchant-select {
	margin-bottom: 12px;
}

.merchant-select label {
	display: block;
	font-weight: 700;
	margin-bottom: 10px;
	color: #1a1a1a;
	font-size: 13px;
	letter-spacing: -0.2px;
}

.merchant-select select {
	width: 100%;
	padding: 12px 40px 12px 14px;
	border: 1px solid #e8e8e8;
	border-radius: 10px;
	font-size: 14px;
	height: 48px;
	line-height: 1.5;
	appearance: none;
	-webkit-appearance: none;
	-moz-appearance: none;
	background-color: #fff;
	background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23FFB300' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
	background-repeat: no-repeat;
	background-position: right 12px center;
	background-size: 18px;
	transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
	cursor: pointer;
	font-weight: 500;
	color: #1a1a1a;
}

.merchant-select select:hover {
	border-color: #d0d0d0;
}

.merchant-select select:focus {
	outline: none;
	border-color: #FFB300;
}

.merchant-select select option {
	padding: 8px;
	line-height: 1.5;
}

@media (max-width: 768px) {
	.manual-payment-container {
		max-width: 100%;
		padding: 0 10px;
	}
}
</style>

<div class="wrap-loading display-none">
	<div><img src="./npay/img/loading_payment.gif" width="200" height="411"></div>
</div>

<section class="container" id="bbs">
	<section class="contents contents-bbs">
		<!-- 헤더 -->
		<div class="manual-payment-header">
			<h2>
				<i class="fa fa-credit-card"></i>
				수기결제
			</h2>
		</div>

		<div class="bbs-cont">
			<div class="inner">
				<div class="manual-payment-container">
				<!-- 관리자: 가맹점 선택 -->
				<?php if($is_admin) { ?>
				<div class="pg-select-area merchant-select">
					<h3><i class="fa fa-users"></i> 가맹점 선택</h3>
					<form method="get" id="merchantSelectForm">
						<input type="hidden" name="p" value="manual_payment">
						<label>수기결제를 진행할 가맹점을 선택하세요</label>
						<select name="selected_mb_id" class="form-control">
							<option value="">가맹점을 선택하세요</option>
							<option value="demo1">홍길동 상회 (홍길동)</option>
							<option value="demo2">서울상사 (김철수)</option>
							<option value="demo3">부산무역 (이영희)</option>
						</select>
					</form>
				</div>
				<?php } ?>

				<!-- PG 모듈 선택 -->
				<div class="pg-select-area">
					<h3><i class="fa fa-list"></i> PG 모듈 선택</h3>
					<p style="margin-bottom: 10px; color: #666; font-size: 12px;">
						결제에 사용할 PG 모듈을 선택하세요
					</p>
					<div class="pg-module-list">
						<!-- PG 모듈 1: 페이시스 비인증 -->
						<div class="pg-module-item selected" onclick="selectPgModule(1)">
							<div class="pg-module-left">
								<div class="pg-module-name">페이시스 비인증</div>
								<div class="pg-module-info-row">
									<span class="pg-module-info">한도: 20,000,000원</span>
									<span class="pg-module-info">할부: 12개월</span>
								</div>
							</div>
							<span class="pg-module-badge certified-none">비인증</span>
						</div>

						<!-- PG 모듈 2: 페이시스 구인증 -->
						<div class="pg-module-item" onclick="selectPgModule(2)">
							<div class="pg-module-left">
								<div class="pg-module-name">페이시스 구인증</div>
								<div class="pg-module-info-row">
									<span class="pg-module-info">한도: 20,000,000원</span>
									<span class="pg-module-info">할부: 12개월</span>
								</div>
							</div>
							<span class="pg-module-badge certified-old">구인증</span>
						</div>
					</div>
				</div>

				<!-- 결제 폼 -->
				<div class="payment-form-area active">
					<div class="stripe-payment-form">
						<form name="payment_form" method="post" onsubmit="return false;">
							<input type="hidden" name="pg_id" id="pg_id" value="1">
							<input type="hidden" name="pg_code" id="pg_code" value="paysis">
							<input type="hidden" name="mb_id" id="mb_id" value="<?php echo $member['mb_id']; ?>">

							<!-- 결제 정보 -->
							<div class="stripe-section-title">
								<i class="fa fa-shopping-cart"></i> 결제 정보
							</div>

							<div class="stripe-form-group">
								<label class="stripe-form-label">상품명</label>
								<input type="text" name="pay_product" id="pay_product" class="stripe-form-input" placeholder="상품명" maxlength="50">
							</div>

							<div class="stripe-form-group">
								<label class="stripe-form-label">결제금액</label>
								<input type="text" name="pay_price" id="pay_price" class="stripe-form-input" placeholder="1,000,000" maxlength="15" onkeyup="inputNumberFormat(this);">
							</div>

							<!-- 카드 정보 -->
							<div class="stripe-section-title">
								<i class="fa fa-credit-card"></i> 카드 정보
							</div>

							<div class="stripe-form-group">
								<label class="stripe-form-label">카드번호</label>
								<div class="stripe-card-element">
									<input type="text" name="pay_cardnum" id="pay_cardnum" class="stripe-form-input" placeholder="1234 5678 9012 3456" maxlength="19" oninput="formatCreditCard()">
									<i class="fa fa-credit-card stripe-card-icon"></i>
								</div>
							</div>

							<div class="stripe-form-row">
								<div class="stripe-form-col">
									<label class="stripe-form-label">유효기간 (MM/YY)</label>
									<div style="display: flex; gap: 8px;">
										<select name="pay_MM" id="pay_MM" class="stripe-form-input">
											<option value="">월</option>
											<option value="01">01</option>
											<option value="02">02</option>
											<option value="03">03</option>
											<option value="04">04</option>
											<option value="05">05</option>
											<option value="06">06</option>
											<option value="07">07</option>
											<option value="08">08</option>
											<option value="09">09</option>
											<option value="10">10</option>
											<option value="11">11</option>
											<option value="12">12</option>
										</select>
										<select name="pay_YY" id="pay_YY" class="stripe-form-input">
											<option value="">년</option>
											<option value="24">24</option>
											<option value="25">25</option>
											<option value="26">26</option>
											<option value="27">27</option>
											<option value="28">28</option>
											<option value="29">29</option>
											<option value="30">30</option>
											<option value="31">31</option>
											<option value="32">32</option>
											<option value="33">33</option>
											<option value="34">34</option>
										</select>
									</div>
								</div>
								<div class="stripe-form-col-small">
									<label class="stripe-form-label">할부</label>
									<select name="pay_installment" id="pay_installment" class="stripe-form-input">
										<option value="">선택</option>
										<option value="0">일시불</option>
										<option value="2">2개월</option>
										<option value="3">3개월</option>
										<option value="4">4개월</option>
										<option value="5">5개월</option>
										<option value="6">6개월</option>
										<option value="7">7개월</option>
										<option value="8">8개월</option>
										<option value="9">9개월</option>
										<option value="10">10개월</option>
										<option value="11">11개월</option>
										<option value="12">12개월</option>
									</select>
								</div>
							</div>

							<!-- 구인증 전용 필드 -->
							<div id="auth_panel" style="display: none;">
								<div class="stripe-form-row">
									<div class="stripe-form-col">
										<label class="stripe-form-label">카드 비밀번호 앞 2자리</label>
										<input type="password" name="pay_password" id="pay_password" class="stripe-form-input" placeholder="••" maxlength="2">
									</div>
									<div class="stripe-form-col">
										<label class="stripe-form-label">생년월일 (6자리) 또는 사업자번호 (10자리)</label>
										<input type="text" name="pay_certify" id="pay_certify" class="stripe-form-input" placeholder="YYMMDD 또는 0000000000" maxlength="10">
									</div>
								</div>
							</div>

							<!-- 카드주 정보 -->
							<div class="stripe-section-title">
								<i class="fa fa-user"></i> 카드주 정보
							</div>

							<div class="stripe-form-row">
								<div class="stripe-form-col">
									<label class="stripe-form-label">카드주명</label>
									<input type="text" name="pay_pname" id="pay_pname" class="stripe-form-input" placeholder="홍길동">
								</div>
								<div class="stripe-form-col">
									<label class="stripe-form-label">휴대전화</label>
									<input type="text" name="pay_phone" id="pay_phone" class="stripe-form-input" placeholder="010-1234-5678" maxlength="13">
								</div>
							</div>

							<div class="stripe-form-group">
								<label class="stripe-form-label">이메일 (선택)</label>
								<input type="email" name="pay_email" id="pay_email" class="stripe-form-input" placeholder="example@email.com" maxlength="100">
							</div>

							<!-- 약관 동의 -->
							<div style="margin-top: 12px;">
								<label class="stripe-checkbox-wrapper">
									<input type="checkbox" name="agreeCheckAll" id="agreeCheckAll" class="stripe-checkbox">
									<span class="stripe-checkbox-label">전체 동의</span>
								</label>

								<label class="stripe-checkbox-wrapper">
									<input type="checkbox" name="agreeTerm" id="agreeTerm" class="stripe-checkbox">
									<span class="stripe-checkbox-label">
										이용약관 동의
										<a href="./npay/payment_agree1.php" class="stripe-link" target="_blank" onclick="event.stopPropagation();">(보기)</a>
									</span>
								</label>

								<label class="stripe-checkbox-wrapper">
									<input type="checkbox" name="agreePriv" id="agreePriv" class="stripe-checkbox">
									<span class="stripe-checkbox-label">
										개인정보 처리방침 동의
										<a href="./npay/payment_agree2.php" class="stripe-link" target="_blank" onclick="event.stopPropagation();">(보기)</a>
									</span>
								</label>
							</div>

							<!-- 버튼 -->
							<div class="stripe-button-group">
								<button type="button" class="stripe-pay-button" id="btn1" onclick="processPayment()">
									<i class="fa fa-lock"></i> 결제하기
								</button>
								<button type="button" class="stripe-cancel-button" id="btn2" onclick="resetForm()">
									취소
								</button>
							</div>

							<!-- 보안 배지 -->
							<div class="stripe-secure-badge">
								<i class="fa fa-shield"></i>
								<span>SSL 보안 결제</span>
							</div>
						</form>
					</div>
				</div>
				</div><!-- .manual-payment-container -->
			</div>
		</div>
	</section>
</section>

<script>
function selectPgModule(pgId) {
	// PG 모듈 선택 UI 변경
	$('.pg-module-item').removeClass('selected');
	event.target.closest('.pg-module-item').classList.add('selected');

	// 구인증 PG인 경우 인증정보 패널 표시 (pgId == 2가 구인증)
	if(pgId == 2) {
		$('#auth_panel').show();
	} else {
		$('#auth_panel').hide();
	}

	// 결제 폼 스크롤
	$('html, body').animate({
		scrollTop: $('.payment-form-area').offset().top - 100
	}, 500);
}

function processPayment() {
	$("#btn1, #btn2").hide();

	var pay_product = $("#pay_product").val();
	var pay_price = $("#pay_price").val();
	var pay_pname = $("#pay_pname").val();
	var pay_phone = $("#pay_phone").val();
	var pay_cardnum = $("#pay_cardnum").val();
	var pay_installment = $("#pay_installment").val();
	var pay_MM = $("#pay_MM").val();
	var pay_YY = $("#pay_YY").val();

	// 유효성 검사
	if (!pay_product) {
		alert('상품명을 입력하세요');
		$("#btn1, #btn2").show();
		$('#pay_product').focus();
		return;
	}
	if (!pay_price) {
		alert('결제금액을 입력하세요');
		$("#btn1, #btn2").show();
		$('#pay_price').focus();
		return;
	}
	if (!pay_pname) {
		alert('카드주명을 입력하세요');
		$("#btn1, #btn2").show();
		$('#pay_pname').focus();
		return;
	}
	if (!pay_phone) {
		alert('휴대전화번호를 입력하세요');
		$("#btn1, #btn2").show();
		$('#pay_phone').focus();
		return;
	}
	if(pay_phone) {
		var regex = /^(01[0-9]{1}-?[0-9]{4}-?[0-9]{4}|01[0-9]{8})$/;
		if (!regex.test(pay_phone)) {
			alert("휴대전화번호를 정확히 입력하세요");
			$("#btn1, #btn2").show();
			$('#pay_phone').focus();
			return;
		}
	}
	if (!pay_cardnum) {
		alert('카드번호를 입력하세요');
		$("#btn1, #btn2").show();
		$('#pay_cardnum').focus();
		return;
	}
	if (!pay_installment && pay_installment !== '0') {
		alert('할부를 선택해주세요.');
		$("#btn1, #btn2").show();
		$('#pay_installment').focus();
		return;
	}
	if (!pay_MM) {
		alert('유효기간 월을 선택해주세요');
		$("#btn1, #btn2").show();
		$('#pay_MM').focus();
		return;
	}
	if (!pay_YY) {
		alert('유효기간 년도를 선택해주세요');
		$("#btn1, #btn2").show();
		$('#pay_YY').focus();
		return;
	}

	// 구인증 선택시 인증정보 체크
	if($('#auth_panel').is(':visible')) {
		var pay_password = $("#pay_password").val();
		var pay_certify = $("#pay_certify").val();

		if (!pay_password) {
			alert('비밀번호 앞 2자리를 입력해주세요');
			$("#btn1, #btn2").show();
			$('#pay_password').focus();
			return;
		}
		if (!pay_certify) {
			alert('본인확인정보를 입력해주세요');
			$("#btn1, #btn2").show();
			$('#pay_certify').focus();
			return;
		}
	}

	if (!$('#agreeTerm').prop('checked')) {
		alert('약관에 동의하셔야 가능합니다.');
		$("#btn1, #btn2").show();
		return;
	}
	if (!$('#agreePriv').prop('checked')) {
		alert('개인정보 처리방침 동의하셔야 가능합니다.');
		$("#btn1, #btn2").show();
		return;
	}

	// UI 테스트용 - 실제 결제 처리는 나중에 연동
	$('.wrap-loading').removeClass('display-none');

	setTimeout(function() {
		$('.wrap-loading').addClass('display-none');
		$('html, body').animate({scrollTop:0}, '300');
		alert('결제 UI 테스트 완료!\n\n입력하신 정보:\n상품명: ' + pay_product + '\n금액: ' + pay_price + '원');
		resetForm();
	}, 2000);
}

function resetForm() {
	$('#pay_product').val('');
	$('#pay_price').val('');
	$('#pay_pname').val('');
	$('#pay_phone').val('');
	$('#pay_email').val('');
	$('#pay_cardnum').val('');
	$('#pay_installment').val('');
	$('#pay_MM').val('');
	$('#pay_YY').val('');
	$('#pay_password').val('');
	$('#pay_certify').val('');
	$("#agreeCheckAll, #agreeTerm, #agreePriv").prop("checked", false);
	$("#btn1, #btn2").show();
}

$(function() {
	// 전체 동의 체크박스
	$('input#agreeCheckAll:checkbox').on('click', function () {
		$('input#agreeTerm,input#agreePriv').prop('checked', $(this).prop('checked'));
	});

	$('input#agreeTerm,input#agreePriv').on('change', function () {
		var checked = $('input#agreeTerm').prop('checked') && $('input#agreePriv').prop('checked');
		$('input#agreeCheckAll').prop('checked', checked);
	});

	// 휴대전화 자동 포맷
	$("#pay_phone").keyup(function(){
		$(this).val($(this).val().replace(/[^0-9]/gi, "").replace(/^(\d{2,3})(\d{3,4})(\d{4})$/, `$1-$2-$3`));
	});

	// 입력 필드 포커스 효과
	$('.stripe-form-input').on('focus', function() {
		$(this).parent().addClass('focused');
	}).on('blur', function() {
		$(this).parent().removeClass('focused');
	});
});

// 숫자 콤마 포맷
function comma(str) {
	str = String(str);
	return str.replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');
}

function uncomma(str) {
	str = String(str);
	return str.replace(/[^\d]+/g, '');
}

function inputNumberFormat(obj) {
	obj.value = comma(uncomma(obj.value));
}

// 신용카드 번호 하이픈 추가
function formatCreditCard() {
	var inputElement = document.getElementById('pay_cardnum');
	var inputValue = inputElement.value;
	var formattedValue = cardNoHyphen(inputValue);
	inputElement.value = formattedValue;
}

function cardNoHyphen(str){
	str = str.replace(/[^0-9]/g, '');
	var tmp = '';
	if(str.length < 5){
		return str;
	}else if(str.length < 9){
		tmp += str.substr(0, 4);
		tmp += '-';
		tmp += str.substr(4);
		return tmp;
	}else if(str.length < 13){
		tmp += str.substr(0, 4);
		tmp += '-';
		tmp += str.substr(4, 4);
		tmp += '-';
		tmp += str.substr(8, 4);
		return tmp;
	}else{
		tmp += str.substr(0, 4);
		tmp += '-';
		tmp += str.substr(4, 4);
		tmp += '-';
		tmp += str.substr(8, 4);
		tmp += '-';
		tmp += str.substr(12);
		return tmp;
	}
	return str;
}
</script>

<?php
include_once('./_tail.php');
?>
