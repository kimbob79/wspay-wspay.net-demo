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
	padding: 20px;
	background: #e0e0e0;
	border-radius: 16px;
	min-height: calc(100vh - 200px);
}

.manual-payment-header {
	background: linear-gradient(135deg, #e65100 0%, #f57c00 100%);
	border-radius: 8px;
	padding: 12px 16px;
	margin-bottom: 10px;
	box-shadow: 0 2px 8px rgba(230, 81, 0, 0.2);
}

.manual-payment-title {
	color: #fff;
	font-size: 16px;
	font-weight: 600;
	display: flex;
	align-items: center;
	gap: 8px;
}

.manual-payment-title i {
	font-size: 16px;
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

/* 약관 모달 스타일 */
.terms-modal-overlay {
	display: none;
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: rgba(0, 0, 0, 0.6);
	z-index: 9999;
	justify-content: center;
	align-items: center;
	backdrop-filter: blur(4px);
}

.terms-modal-overlay.active {
	display: flex;
}

.terms-modal {
	background: #fff;
	border-radius: 16px;
	width: 90%;
	max-width: 600px;
	max-height: 85vh;
	display: flex;
	flex-direction: column;
	box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
	animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
	from {
		opacity: 0;
		transform: translateY(-30px) scale(0.95);
	}
	to {
		opacity: 1;
		transform: translateY(0) scale(1);
	}
}

.terms-modal-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 20px 24px;
	border-bottom: 1px solid #e8e8e8;
	background: linear-gradient(135deg, #FFB300 0%, #FFA000 100%);
	border-radius: 16px 16px 0 0;
}

.terms-modal-title {
	font-size: 18px;
	font-weight: 700;
	color: #fff;
	display: flex;
	align-items: center;
	gap: 10px;
}

.terms-modal-title i {
	font-size: 20px;
}

.terms-modal-close {
	width: 36px;
	height: 36px;
	border: none;
	background: rgba(255, 255, 255, 0.2);
	border-radius: 50%;
	cursor: pointer;
	display: flex;
	align-items: center;
	justify-content: center;
	transition: all 0.2s ease;
	color: #fff;
	font-size: 18px;
}

.terms-modal-close:hover {
	background: rgba(255, 255, 255, 0.4);
	transform: rotate(90deg);
}

.terms-modal-body {
	flex: 1;
	overflow-y: auto;
	padding: 24px;
	font-size: 13px;
	line-height: 1.8;
	color: #333;
}

.terms-modal-body h2 {
	font-size: 16px;
	font-weight: 700;
	color: #1a1a1a;
	margin: 24px 0 12px 0;
	padding-bottom: 8px;
	border-bottom: 2px solid #FFB300;
}

.terms-modal-body h2:first-child {
	margin-top: 0;
}

.terms-modal-body h3 {
	font-size: 14px;
	font-weight: 600;
	color: #333;
	margin: 16px 0 8px 0;
}

.terms-modal-body p {
	margin: 8px 0;
	text-align: justify;
}

.terms-modal-body ul, .terms-modal-body ol {
	margin: 8px 0 8px 20px;
	padding: 0;
}

.terms-modal-body li {
	margin: 4px 0;
}

.terms-modal-body .term-section {
	margin-bottom: 20px;
	padding: 16px;
	background: #f8f9fa;
	border-radius: 8px;
	border-left: 4px solid #FFB300;
}

.terms-modal-body .term-highlight {
	background: #FFF8E1;
	padding: 12px 16px;
	border-radius: 8px;
	margin: 12px 0;
	border: 1px solid #FFE082;
}

.terms-modal-body table {
	width: 100%;
	border-collapse: collapse;
	margin: 12px 0;
	font-size: 12px;
}

.terms-modal-body table th,
.terms-modal-body table td {
	border: 1px solid #e0e0e0;
	padding: 10px 12px;
	text-align: left;
}

.terms-modal-body table th {
	background: #f8f9fa;
	font-weight: 600;
	color: #333;
}

.terms-modal-footer {
	padding: 16px 24px;
	border-top: 1px solid #e8e8e8;
	display: flex;
	justify-content: center;
	background: #f8f9fa;
	border-radius: 0 0 16px 16px;
}

.terms-modal-confirm {
	padding: 12px 40px;
	background: linear-gradient(135deg, #FFB300 0%, #FFA000 100%);
	color: #fff;
	border: none;
	border-radius: 8px;
	font-size: 14px;
	font-weight: 700;
	cursor: pointer;
	transition: all 0.2s ease;
}

.terms-modal-confirm:hover {
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(255, 179, 0, 0.4);
}

@media (max-width: 768px) {
	.terms-modal {
		width: 95%;
		max-height: 90vh;
		margin: 10px;
		border-radius: 12px;
	}

	.terms-modal-header {
		padding: 16px 20px;
		border-radius: 12px 12px 0 0;
	}

	.terms-modal-title {
		font-size: 16px;
	}

	.terms-modal-body {
		padding: 16px;
	}

	.terms-modal-footer {
		padding: 12px 16px;
		border-radius: 0 0 12px 12px;
	}
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
		padding: 15px 10px;
		border-radius: 0;
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
			<div class="manual-payment-title">
				<i class="fa fa-credit-card"></i>
				수기결제
			</div>
		</div>

		<div class="bbs-cont">
			<div class="inner">
				<div class="manual-payment-container">
				<!-- 관리자: 가맹점 선택 -->
				<?php if($is_admin) {
					// Keyin 설정이 등록된 가맹점 목록 조회
					$selected_mb_id = isset($_GET['selected_mb_id']) ? $_GET['selected_mb_id'] : '';
					$keyin_members_sql = "SELECT DISTINCT m.mb_id, m.mb_nick, m.mb_name
						FROM {$g5['member_table']} m
						INNER JOIN g5_member_keyin_config k ON m.mb_id = k.mb_id
						WHERE m.mb_level = 3 AND k.mkc_use = 'Y' AND k.mkc_status = 'active'
						ORDER BY m.mb_nick";
					$keyin_members = sql_query($keyin_members_sql);
				?>
				<div class="pg-select-area merchant-select">
					<h3><i class="fa fa-users"></i> 가맹점 선택</h3>
					<form method="get" id="merchantSelectForm">
						<input type="hidden" name="p" value="manual_payment">
						<label>수기결제를 진행할 가맹점을 선택하세요 <span style="color:#999; font-size:11px;">(Keyin 설정된 가맹점만 표시)</span></label>
						<select name="selected_mb_id" class="form-control" onchange="this.form.submit()">
							<option value="">가맹점을 선택하세요</option>
							<?php while($km = sql_fetch_array($keyin_members)) { ?>
							<option value="<?php echo $km['mb_id']; ?>" <?php if($selected_mb_id == $km['mb_id']) echo 'selected'; ?>>
								<?php echo htmlspecialchars($km['mb_nick']); ?> (<?php echo htmlspecialchars($km['mb_name']); ?>)
							</option>
							<?php } ?>
						</select>
					</form>
				</div>
				<?php } ?>

				<?php
				// 대상 가맹점 ID 결정 (관리자: 선택된 가맹점, 가맹점: 자기 자신)
				if($is_admin) {
					$target_mb_id = isset($_GET['selected_mb_id']) ? $_GET['selected_mb_id'] : '';
				} else {
					$target_mb_id = $member['mb_id'];
				}

				// 현재 시간/요일 정보
				$current_hour = (int)date('H');
				$current_day = (int)date('w'); // 0=일요일, 6=토요일
				$is_weekend = ($current_day == 0 || $current_day == 6);

				// 해당 가맹점의 Keyin 설정 조회
				$keyin_configs = array();
				$unavailable_configs = array(); // 이용 불가 PG 모듈 (사유 표시용)
				if($target_mb_id) {
					$keyin_sql = "SELECT k.*, m.mpc_pg_name as master_pg_name, m.mpc_type as master_type,
								 m.mpc_api_key as master_api_key, m.mpc_mid as master_mid, m.mpc_mkey as master_mkey
								 FROM g5_member_keyin_config k
								 LEFT JOIN g5_manual_payment_config m ON k.mpc_id = m.mpc_id
								 WHERE k.mb_id = '{$target_mb_id}' AND k.mkc_use = 'Y' AND k.mkc_status = 'active'
								 ORDER BY k.mkc_id ASC";
					$keyin_result = sql_query($keyin_sql);
					while($row = sql_fetch_array($keyin_result)) {
						// 대표가맹점 설정 사용시 master 값 사용, 개별 설정시 자체 값 사용
						if($row['mpc_id']) {
							$row['display_name'] = $row['master_pg_name'];
							$row['certi_type'] = $row['master_type']; // nonauth, auth
							$row['api_key'] = $row['master_api_key'];
							$row['mid'] = $row['master_mid'];
							$row['mkey'] = $row['master_mkey'];
						} else {
							$row['display_name'] = $row['mkc_pg_name'];
							$row['certi_type'] = $row['mkc_type']; // nonauth, auth
							$row['api_key'] = $row['mkc_api_key'];
							$row['mid'] = $row['mkc_mid'];
							$row['mkey'] = $row['mkc_mkey'];
						}

						// 주말 체크 - 주말이고 주말결제 불가일 경우
						$unavail_reason = '';
						if($is_weekend && $row['mkc_weekend_yn'] == 'N') {
							$unavail_reason = '주말 결제 불가';
						}

						// 시간 체크 - 결제 가능 시간대 벗어난 경우
						if(!$unavail_reason) {
							$time_start = isset($row['mkc_time_start']) ? (int)substr($row['mkc_time_start'], 0, 2) : 0;
							$time_end = isset($row['mkc_time_end']) ? (int)substr($row['mkc_time_end'], 0, 2) : 23;

							// 시간대 체크 (종료시간이 23시면 23:59까지 허용)
							if($time_end == 23) {
								$is_time_ok = ($current_hour >= $time_start);
							} else {
								$is_time_ok = ($current_hour >= $time_start && $current_hour < $time_end);
							}

							if(!$is_time_ok) {
								$unavail_reason = '결제가능시간: ' . $time_start . '시~' . $time_end . '시';
							}
						}

						// 최대 할부개월 기본값 설정
						if(!isset($row['mkc_max_installment']) || $row['mkc_max_installment'] === null) {
							$row['mkc_max_installment'] = 12;
						}

						if($unavail_reason) {
							$row['unavail_reason'] = $unavail_reason;
							$unavailable_configs[] = $row;
						} else {
							$keyin_configs[] = $row;
						}
					}
				}
				?>

				<!-- PG 모듈 선택 -->
				<div class="pg-select-area" id="pgSelectArea" <?php echo (count($keyin_configs) == 1) ? 'style="display: none;"' : ''; ?>>
					<h3><i class="fa fa-list"></i> PG 모듈 선택</h3>
					<?php if(empty($keyin_configs) && empty($unavailable_configs)) { ?>
					<p style="margin-bottom: 10px; color: #999; font-size: 13px; text-align: center; padding: 30px 0;">
						<?php if($is_admin && !$target_mb_id) { ?>
						<i class="fa fa-info-circle" style="font-size: 24px; color: #FFB300; display: block; margin-bottom: 10px;"></i>
						먼저 가맹점을 선택해주세요.
						<?php } else { ?>
						<i class="fa fa-exclamation-triangle" style="font-size: 24px; color: #f44336; display: block; margin-bottom: 10px;"></i>
						등록된 Keyin 설정이 없습니다.<br>
						<span style="font-size: 11px; color: #999;">가맹점관리에서 Keyin설정을 먼저 등록해주세요.</span>
						<?php } ?>
					</p>
					<?php } else if(empty($keyin_configs) && !empty($unavailable_configs)) { ?>
					<p style="margin-bottom: 10px; color: #f44336; font-size: 13px; text-align: center; padding: 20px 0;">
						<i class="fa fa-clock-o" style="font-size: 24px; display: block; margin-bottom: 10px;"></i>
						현재 이용 가능한 PG 모듈이 없습니다.
					</p>
					<!-- 이용 불가 PG 모듈 목록 -->
					<div class="pg-module-list">
						<?php foreach($unavailable_configs as $config) { ?>
						<div class="pg-module-item disabled" style="opacity: 0.5; cursor: not-allowed; background: #f5f5f5;">
							<div class="pg-module-left">
								<div class="pg-module-name"><?php echo htmlspecialchars($config['display_name']); ?></div>
								<div style="font-size: 11px; color: #f44336; margin-top: 4px;">
									<i class="fa fa-ban"></i> <?php echo $config['unavail_reason']; ?>
								</div>
							</div>
							<span class="pg-module-badge" style="background: #999;">이용불가</span>
						</div>
						<?php } ?>
					</div>
					<?php } else { ?>
					<p style="margin-bottom: 10px; color: #666; font-size: 12px;">
						결제에 사용할 PG 모듈을 선택하세요
					</p>
					<div class="pg-module-list">
						<?php
						$first = true;
						foreach($keyin_configs as $idx => $config) {
							$badge_class = '';
							$badge_text = '';
							switch($config['certi_type']) {
								case 'nonauth':
									$badge_class = 'certified-none';
									$badge_text = '비인증';
									break;
								case 'auth':
									$badge_class = 'certified-old';
									$badge_text = '구인증';
									break;
								default:
									$badge_class = 'certified-none';
									$badge_text = $config['certi_type'] ? $config['certi_type'] : '미지정';
							}
						?>
						<div class="pg-module-item<?php echo $first ? ' selected' : ''; ?>"
							 onclick="selectPgModule(<?php echo $config['mkc_id']; ?>, '<?php echo $config['certi_type']; ?>', <?php echo (int)$config['mkc_max_installment']; ?>)"
							 data-mkc-id="<?php echo $config['mkc_id']; ?>"
							 data-certi-type="<?php echo $config['certi_type']; ?>"
							 data-api-key="<?php echo htmlspecialchars($config['api_key']); ?>"
							 data-mid="<?php echo htmlspecialchars($config['mid']); ?>"
							 data-mkey="<?php echo htmlspecialchars($config['mkey']); ?>"
							 data-pg-code="<?php echo htmlspecialchars($config['mkc_pg_code']); ?>"
							 data-max-installment="<?php echo (int)$config['mkc_max_installment']; ?>">
							<div class="pg-module-left">
								<div class="pg-module-name"><?php echo htmlspecialchars($config['display_name']); ?></div>
								<?php if($config['mkc_max_installment'] > 0 && $config['mkc_max_installment'] < 12) { ?>
								<div style="font-size: 10px; color: #999; margin-top: 2px;">최대 <?php echo $config['mkc_max_installment']; ?>개월 할부</div>
								<?php } ?>
							</div>
							<span class="pg-module-badge <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span>
						</div>
						<?php
							$first = false;
						}
						?>
					</div>

					<?php if(!empty($unavailable_configs)) { ?>
					<!-- 이용 불가 PG 모듈 안내 -->
					<div style="margin-top: 12px; padding: 10px; background: #fafafa; border-radius: 8px; border: 1px dashed #ddd;">
						<div style="font-size: 11px; color: #999; margin-bottom: 6px;"><i class="fa fa-info-circle"></i> 현재 이용 불가한 모듈</div>
						<?php foreach($unavailable_configs as $config) { ?>
						<div style="font-size: 11px; color: #888; padding: 2px 0;">
							<span style="color: #666;"><?php echo htmlspecialchars($config['display_name']); ?></span>
							<span style="color: #f44336; margin-left: 6px;"><?php echo $config['unavail_reason']; ?></span>
						</div>
						<?php } ?>
					</div>
					<?php } ?>
					<?php } ?>
				</div>

				<!-- 결제 폼 -->
				<div class="payment-form-area<?php echo !empty($keyin_configs) ? ' active' : ''; ?>">
					<div class="stripe-payment-form">
						<form name="payment_form" method="post" onsubmit="return false;">
							<input type="hidden" name="pg_id" id="pg_id" value="">
							<input type="hidden" name="pg_code" id="pg_code" value="">
							<input type="hidden" name="mb_id" id="mb_id" value="<?php echo $target_mb_id; ?>">

							<!-- 비인증 전용 필드 (API Key 등) -->
							<div id="nonauth_panel">
								<div class="stripe-section-title">
									<i class="fa fa-key"></i> API 인증 정보
								</div>

								<div class="stripe-form-group">
									<label class="stripe-form-label">API KEY (dal-api-key)</label>
									<input type="text" name="dal_api_key" id="dal_api_key" class="stripe-form-input" placeholder="API KEY 입력 (32자)" maxlength="32">
								</div>

								<div class="stripe-form-group">
									<label class="stripe-form-label">상점 ID (mid)</label>
									<input type="text" name="mid" id="mid" class="stripe-form-input" placeholder="페이시스 제공 MID (10자)" maxlength="10">
								</div>

								<div class="stripe-form-group">
									<label class="stripe-form-label">암호화 키 (mkey)</label>
									<input type="text" name="mkey" id="mkey" class="stripe-form-input" placeholder="암호화 키 입력 (100자)" maxlength="100">
								</div>
							</div>

							<!-- 결제 정보 -->
							<div class="stripe-section-title">
								<i class="fa fa-shopping-cart"></i> 결제 정보
							</div>

							<div class="stripe-form-group">
								<label class="stripe-form-label">주문번호 (ordNo)</label>
								<input type="text" name="ordNo" id="ordNo" class="stripe-form-input" placeholder="주문번호 (30자)" maxlength="30">
							</div>

							<div class="stripe-form-group">
								<label class="stripe-form-label">상품명 (goodsNm)</label>
								<input type="text" name="pay_product" id="pay_product" class="stripe-form-input" placeholder="상품명 (50자)" maxlength="50">
							</div>

							<div class="stripe-form-group">
								<label class="stripe-form-label">승인금액 (goodAmt)</label>
								<input type="text" name="pay_price" id="pay_price" class="stripe-form-input" placeholder="승인금액 (12자리)" maxlength="15" onkeyup="inputNumberFormat(this);">
							</div>

							<!-- 카드 정보 -->
							<div class="stripe-section-title">
								<i class="fa fa-credit-card"></i> 카드 정보
							</div>

							<div class="stripe-form-group">
								<label class="stripe-form-label">카드번호 (cardNo)</label>
								<div class="stripe-card-element">
									<input type="text" name="pay_cardnum" id="pay_cardnum" class="stripe-form-input" placeholder="카드번호 13자리 (하이픈 없이)" maxlength="19" oninput="formatCreditCard()">
									<i class="fa fa-credit-card stripe-card-icon"></i>
								</div>
							</div>

							<div class="stripe-form-row">
								<div class="stripe-form-col">
									<label class="stripe-form-label">카드번호 유효기간 (expireYymm) - YYMM</label>
									<div style="display: flex; gap: 8px;">
										<select name="pay_YY" id="pay_YY" class="stripe-form-input">
											<option value="">년(YY)</option>
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
										<select name="pay_MM" id="pay_MM" class="stripe-form-input">
											<option value="">월(MM)</option>
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
									</div>
								</div>
								<div class="stripe-form-col-small">
									<label class="stripe-form-label">할부개월 (quotaMon)</label>
									<select name="pay_installment" id="pay_installment" class="stripe-form-input">
										<option value="">선택</option>
										<option value="00">일시불(00)</option>
										<!-- 할부 옵션은 PG 모듈 선택 시 JavaScript로 동적 생성됨 -->
									</select>
								</div>
							</div>

							<!-- 구인증 전용 필드 -->
							<div id="auth_panel" style="display: none;">
								<div class="stripe-section-title">
									<i class="fa fa-key"></i> API 인증 정보 (구인증)
								</div>

								<div class="stripe-form-group">
									<label class="stripe-form-label">API KEY (dal-api-key)</label>
									<input type="text" name="auth_dal_api_key" id="auth_dal_api_key" class="stripe-form-input" placeholder="API KEY 입력 (32자)" maxlength="32">
								</div>

								<div class="stripe-form-group">
									<label class="stripe-form-label">상점 ID (mid)</label>
									<input type="text" name="auth_mid" id="auth_mid" class="stripe-form-input" placeholder="페이시스 제공 MID (10자)" maxlength="10">
								</div>

								<div class="stripe-form-group">
									<label class="stripe-form-label">암호화 키 (mkey)</label>
									<input type="text" name="auth_mkey" id="auth_mkey" class="stripe-form-input" placeholder="암호화 키 입력 (100자)" maxlength="100">
								</div>

								<div class="stripe-section-title" style="margin-top: 16px;">
									<i class="fa fa-shield"></i> 본인인증 정보
								</div>

								<div class="stripe-form-row">
									<div class="stripe-form-col">
										<label class="stripe-form-label">카드 비밀번호 앞 2자리 (certPw)</label>
										<input type="password" name="pay_password" id="pay_password" class="stripe-form-input" placeholder="••" maxlength="2">
									</div>
									<div class="stripe-form-col">
										<label class="stripe-form-label">주민번호 앞 6자리 또는 사업자등록번호 10자리 (certNo)</label>
										<input type="text" name="pay_certify" id="pay_certify" class="stripe-form-input" placeholder="YYMMDD 또는 0000000000" maxlength="10">
									</div>
								</div>

								<!-- 구인증용 hashKey 정보 -->
								<div class="stripe-section-title" style="margin-top: 16px;">
									<i class="fa fa-lock"></i> 해시 정보 (구인증)
								</div>

								<div class="stripe-form-group">
									<label class="stripe-form-label">hashKey (sha256(mid+goodAmt))</label>
									<input type="text" name="auth_hashKey" id="auth_hashKey" class="stripe-form-input" placeholder="sha256 해시값 (100자) - 자동생성됨" maxlength="100" readonly style="background-color: #f6f9fc;">
									<div class="stripe-helper-text">mid와 goodAmt를 입력하면 자동으로 계산됩니다.</div>
								</div>
							</div>

							<!-- 구매자 정보 -->
							<div class="stripe-section-title">
								<i class="fa fa-user"></i> 구매자 정보
							</div>

							<div class="stripe-form-row">
								<div class="stripe-form-col">
									<label class="stripe-form-label">구매자명 (buyerNm)</label>
									<input type="text" name="pay_pname" id="pay_pname" class="stripe-form-input" placeholder="구매자명 (50자)" maxlength="50">
								</div>
								<div class="stripe-form-col">
									<label class="stripe-form-label">구매자 휴대전화 (ordHp) - 선택</label>
									<input type="text" name="pay_phone" id="pay_phone" class="stripe-form-input" placeholder="01012345678 (15자)" maxlength="15">
								</div>
							</div>

							<!-- 비인증용 hashKey 정보 -->
							<div id="nonauth_hash_panel">
								<div class="stripe-section-title">
									<i class="fa fa-lock"></i> 해시 정보 (비인증)
								</div>

								<div class="stripe-form-group">
									<label class="stripe-form-label">hashKey (sha256(mid+goodAmt))</label>
									<input type="text" name="hashKey" id="hashKey" class="stripe-form-input" placeholder="sha256 해시값 (100자) - 자동생성됨" maxlength="100" readonly style="background-color: #f6f9fc;">
									<div class="stripe-helper-text">mid와 goodAmt를 입력하면 자동으로 계산됩니다.</div>
								</div>
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
										<a href="#" class="stripe-link" onclick="openTermsModal('terms', event); return false;">(보기)</a>
									</span>
								</label>

								<label class="stripe-checkbox-wrapper">
									<input type="checkbox" name="agreePriv" id="agreePriv" class="stripe-checkbox">
									<span class="stripe-checkbox-label">
										개인정보 처리방침 동의
										<a href="#" class="stripe-link" onclick="openTermsModal('privacy', event); return false;">(보기)</a>
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

<!-- 이용약관 모달 -->
<div class="terms-modal-overlay" id="termsModalOverlay">
	<div class="terms-modal">
		<div class="terms-modal-header">
			<div class="terms-modal-title">
				<i class="fa fa-file-text-o"></i>
				<span id="termsModalTitle">이용약관</span>
			</div>
			<button type="button" class="terms-modal-close" onclick="closeTermsModal()">
				<i class="fa fa-times"></i>
			</button>
		</div>
		<div class="terms-modal-body" id="termsModalBody">
			<!-- 약관 내용이 동적으로 로드됩니다 -->
		</div>
		<div class="terms-modal-footer">
			<button type="button" class="terms-modal-confirm" onclick="closeTermsModal()">확인</button>
		</div>
	</div>
</div>

<!-- 이용약관 내용 -->
<div id="termsContent" style="display: none;">
<h2>제1조 (목적)</h2>
<p>본 약관은 원성페이먼츠(이하 "회사")가 제공하는 전자결제대행 서비스(이하 "서비스")의 이용과 관련하여 회사와 이용자 간의 권리, 의무 및 책임사항, 기타 필요한 사항을 규정함을 목적으로 합니다.</p>

<h2>제2조 (정의)</h2>
<p>본 약관에서 사용하는 용어의 정의는 다음과 같습니다.</p>
<ol>
	<li><strong>"서비스"</strong>란 회사가 제공하는 신용카드 결제, 수기결제(비인증/인증결제) 등 전자지급결제대행 서비스를 의미합니다.</li>
	<li><strong>"이용자"</strong>란 본 약관에 동의하고 회사가 제공하는 서비스를 이용하는 자를 말합니다.</li>
	<li><strong>"가맹점"</strong>이란 회사와 전자결제대행 서비스 이용계약을 체결하고 재화 또는 용역을 판매하는 자를 말합니다.</li>
	<li><strong>"수기결제"</strong>란 실물 카드 없이 카드번호, 유효기간 등의 정보를 입력하여 진행하는 비대면 결제 방식을 말합니다.</li>
	<li><strong>"거래정보"</strong>란 결제 처리를 위해 필요한 카드정보, 거래금액, 주문정보 등을 의미합니다.</li>
</ol>

<h2>제3조 (약관의 명시, 효력 및 변경)</h2>
<ol>
	<li>회사는 본 약관의 내용을 이용자가 쉽게 알 수 있도록 서비스 초기화면 또는 연결화면에 게시합니다.</li>
	<li>회사는 「전자상거래 등에서의 소비자보호에 관한 법률」, 「전자금융거래법」, 「여신전문금융업법」 등 관련 법령을 위반하지 않는 범위 내에서 본 약관을 개정할 수 있습니다.</li>
	<li>회사가 약관을 개정할 경우에는 적용일자 및 개정사유를 명시하여 최소 7일 전에 공지합니다. 다만, 이용자에게 불리한 약관 개정의 경우에는 30일 전에 공지합니다.</li>
	<li>이용자가 개정약관의 시행일까지 거부의사를 표시하지 않으면 개정약관에 동의한 것으로 간주합니다.</li>
</ol>

<h2>제4조 (서비스의 제공 및 변경)</h2>
<ol>
	<li>회사는 다음과 같은 서비스를 제공합니다.
		<ul>
			<li>신용카드 결제대행 서비스</li>
			<li>수기결제(비인증/구인증) 서비스</li>
			<li>결제 취소 및 환불 처리 서비스</li>
			<li>거래내역 조회 서비스</li>
			<li>기타 회사가 정하는 서비스</li>
		</ul>
	</li>
	<li>회사는 서비스의 품질 향상을 위해 서비스의 내용을 변경할 수 있으며, 변경 시 사전에 공지합니다.</li>
	<li>서비스는 연중무휴, 1일 24시간 제공함을 원칙으로 합니다. 다만, 시스템 점검, 장애 발생 등의 경우에는 서비스가 일시 중단될 수 있습니다.</li>
</ol>

<h2>제5조 (서비스 이용료)</h2>
<ol>
	<li>서비스 이용에 따른 수수료는 가맹점 계약 시 별도로 정한 바에 따릅니다.</li>
	<li>수수료율은 결제 유형, 업종, 거래 규모 등에 따라 차등 적용될 수 있습니다.</li>
	<li>회사는 수수료율 변경 시 최소 30일 전에 가맹점에 통보합니다.</li>
</ol>

<h2>제6조 (이용자의 의무)</h2>
<ol>
	<li>이용자는 다음 행위를 하여서는 안 됩니다.
		<ul>
			<li>허위 또는 타인의 정보를 이용한 결제</li>
			<li>회사의 서비스 운영을 방해하는 행위</li>
			<li>결제 정보의 위조, 변조 또는 부정 사용</li>
			<li>불법적인 목적으로 서비스를 이용하는 행위</li>
			<li>본인 명의가 아닌 카드의 무단 사용</li>
			<li>자금세탁, 불법 현금융통 등 금융범죄 행위</li>
		</ul>
	</li>
	<li>이용자는 본인의 결제 정보가 도용되었거나 부정 사용되었음을 인지한 경우, 즉시 회사 또는 해당 카드사에 통보하여야 합니다.</li>
	<li>이용자는 관련 법령, 본 약관, 이용안내 및 서비스와 관련하여 회사가 공지한 사항을 준수하여야 합니다.</li>
</ol>

<h2>제7조 (결제 처리 및 승인)</h2>
<ol>
	<li>이용자가 결제 정보를 입력하면 회사는 해당 카드사에 승인을 요청합니다.</li>
	<li>카드사의 승인 결과에 따라 결제가 완료 또는 거부됩니다.</li>
	<li>결제 승인 거부 사유에는 한도 초과, 카드 정지, 정보 불일치 등이 포함될 수 있습니다.</li>
	<li>승인된 결제 건에 대한 취소는 가맹점 또는 회사를 통해 요청할 수 있으며, 카드사 정책에 따라 처리됩니다.</li>
</ol>

<h2>제8조 (결제 취소 및 환불)</h2>
<ol>
	<li>이용자는 결제 완료 후 가맹점의 환불 정책 및 관련 법령에 따라 결제 취소를 요청할 수 있습니다.</li>
	<li>결제 취소 처리는 승인 당일 취소(당일 취소)와 승인 익일 이후 취소(매입 취소)로 구분됩니다.</li>
	<li>취소 요청 시 원거래의 승인번호, 결제금액 등의 정보가 일치해야 합니다.</li>
	<li>환불 금액은 카드사의 정책에 따라 3~7영업일 내에 카드 결제 계좌로 환급됩니다.</li>
</ol>

<h2>제9조 (면책조항)</h2>
<ol>
	<li>회사는 천재지변, 전쟁, 테러, 해킹, 시스템 장애 등 불가항력적인 사유로 인한 서비스 중단에 대해 책임지지 않습니다.</li>
	<li>회사는 이용자의 귀책사유로 발생한 손해에 대해 책임지지 않습니다.</li>
	<li>회사는 이용자가 서비스를 통해 얻은 자료로 인한 손해에 대해 책임지지 않습니다.</li>
	<li>회사는 카드사의 승인 거부 또는 거래 제한으로 인한 손해에 대해 책임지지 않습니다.</li>
</ol>

<h2>제10조 (분쟁해결)</h2>
<ol>
	<li>회사와 이용자 간에 발생한 분쟁은 상호 협의하여 해결합니다.</li>
	<li>분쟁이 해결되지 않을 경우, 관련 법령에 따른 분쟁조정기관의 조정을 받을 수 있습니다.</li>
	<li>본 약관과 관련된 소송은 회사 소재지 관할법원을 제1심 관할법원으로 합니다.</li>
</ol>

<h2>제11조 (준거법)</h2>
<p>본 약관의 해석 및 적용에 관하여는 대한민국 법률을 적용합니다.</p>

<div class="term-highlight">
	<strong>부칙</strong><br>
	본 약관은 2025년 12월 10일부터 시행됩니다.<br>
	최종 개정일: 2025년 12월 10일
</div>
</div>

<!-- 개인정보처리방침 내용 -->
<div id="privacyContent" style="display: none;">
<h2>제1조 (개인정보의 수집 및 이용 목적)</h2>
<p>원성페이먼츠(이하 "회사")는 다음의 목적을 위하여 개인정보를 수집하고 이용합니다. 수집한 개인정보는 다음의 목적 이외의 용도로는 이용되지 않으며, 이용 목적이 변경되는 경우에는 별도의 동의를 받는 등 필요한 조치를 이행합니다.</p>

<div class="term-section">
	<h3>1. 전자결제 서비스 제공</h3>
	<ul>
		<li>결제 승인 및 취소 처리</li>
		<li>결제 관련 본인 확인 및 인증</li>
		<li>거래 내역 조회 및 관리</li>
		<li>결제 관련 고객 문의 응대</li>
	</ul>
</div>

<div class="term-section">
	<h3>2. 서비스 개선 및 품질 향상</h3>
	<ul>
		<li>서비스 이용 현황 분석</li>
		<li>시스템 안정성 확보 및 장애 대응</li>
		<li>부정 거래 탐지 및 방지</li>
	</ul>
</div>

<div class="term-section">
	<h3>3. 법적 의무 이행</h3>
	<ul>
		<li>전자금융거래법에 따른 거래기록 보관</li>
		<li>여신전문금융업법에 따른 의무 이행</li>
		<li>세무 및 회계 관련 법적 의무 이행</li>
	</ul>
</div>

<h2>제2조 (수집하는 개인정보 항목)</h2>
<p>회사는 결제 서비스 제공을 위해 다음과 같은 개인정보를 수집합니다.</p>

<table>
	<tr>
		<th>구분</th>
		<th>수집 항목</th>
		<th>수집 목적</th>
	</tr>
	<tr>
		<td>필수정보</td>
		<td>카드번호, 유효기간, 결제금액, 주문번호, 상품정보</td>
		<td>결제 처리 및 승인</td>
	</tr>
	<tr>
		<td>필수정보</td>
		<td>구매자명, 구매자 연락처(선택)</td>
		<td>거래 당사자 확인</td>
	</tr>
	<tr>
		<td>인증결제 시</td>
		<td>카드 비밀번호 앞 2자리, 생년월일 또는 사업자등록번호</td>
		<td>본인 인증</td>
	</tr>
	<tr>
		<td>자동수집</td>
		<td>접속 IP, 접속 일시, 브라우저 정보, 기기 정보</td>
		<td>부정거래 탐지 및 서비스 개선</td>
	</tr>
</table>

<h2>제3조 (개인정보의 보유 및 이용 기간)</h2>
<p>회사는 개인정보 수집 및 이용 목적이 달성된 후에는 해당 정보를 지체 없이 파기합니다. 다만, 관련 법령에 따라 보존이 필요한 경우 아래와 같이 보관합니다.</p>

<table>
	<tr>
		<th>보존 정보</th>
		<th>보존 기간</th>
		<th>관련 법령</th>
	</tr>
	<tr>
		<td>전자금융거래 기록</td>
		<td>5년</td>
		<td>전자금융거래법</td>
	</tr>
	<tr>
		<td>계약 또는 청약철회 등에 관한 기록</td>
		<td>5년</td>
		<td>전자상거래법</td>
	</tr>
	<tr>
		<td>대금결제 및 재화 등의 공급에 관한 기록</td>
		<td>5년</td>
		<td>전자상거래법</td>
	</tr>
	<tr>
		<td>소비자의 불만 또는 분쟁처리에 관한 기록</td>
		<td>3년</td>
		<td>전자상거래법</td>
	</tr>
	<tr>
		<td>웹사이트 방문 기록(로그)</td>
		<td>3개월</td>
		<td>통신비밀보호법</td>
	</tr>
</table>

<h2>제4조 (개인정보의 제3자 제공)</h2>
<p>회사는 이용자의 개인정보를 원칙적으로 외부에 제공하지 않습니다. 다만, 아래의 경우에는 예외로 합니다.</p>
<ol>
	<li>이용자가 사전에 동의한 경우</li>
	<li>결제 처리를 위해 카드사(VAN사 포함)에 제공하는 경우</li>
	<li>법령의 규정에 의거하거나, 수사 목적으로 법령에 정해진 절차와 방법에 따라 수사기관의 요구가 있는 경우</li>
	<li>금융거래 관련 법령에 따라 금융기관에 제공이 필요한 경우</li>
</ol>

<h2>제5조 (개인정보 처리 위탁)</h2>
<p>회사는 원활한 결제 서비스 제공을 위하여 다음과 같이 개인정보 처리업무를 위탁하고 있습니다.</p>

<table>
	<tr>
		<th>수탁업체</th>
		<th>위탁업무 내용</th>
	</tr>
	<tr>
		<td>카드사 및 VAN사</td>
		<td>결제 승인 및 취소 처리</td>
	</tr>
	<tr>
		<td>클라우드 서비스 제공업체</td>
		<td>데이터 저장 및 시스템 운영</td>
	</tr>
</table>

<h2>제6조 (이용자의 권리와 행사방법)</h2>
<p>이용자는 개인정보주체로서 다음과 같은 권리를 행사할 수 있습니다.</p>
<ol>
	<li><strong>개인정보 열람권:</strong> 본인의 개인정보 처리 현황에 대한 열람을 요구할 수 있습니다.</li>
	<li><strong>개인정보 정정권:</strong> 개인정보가 사실과 다른 경우 정정을 요구할 수 있습니다.</li>
	<li><strong>개인정보 삭제권:</strong> 개인정보의 삭제를 요구할 수 있습니다. 다만, 법령에 따라 보존이 필요한 경우 삭제가 제한될 수 있습니다.</li>
	<li><strong>개인정보 처리정지권:</strong> 개인정보 처리의 정지를 요구할 수 있습니다.</li>
</ol>

<h2>제7조 (개인정보의 안전성 확보조치)</h2>
<p>회사는 개인정보의 안전성 확보를 위해 다음과 같은 조치를 취하고 있습니다.</p>
<ol>
	<li><strong>관리적 조치:</strong> 개인정보 보호 교육, 내부관리계획 수립 및 시행, 개인정보 취급 직원 최소화</li>
	<li><strong>기술적 조치:</strong> 개인정보 암호화(AES-256, SHA-256), 접근권한 관리, 침입탐지 시스템 운영, 보안프로그램 설치</li>
	<li><strong>물리적 조치:</strong> 전산실 및 자료보관실 출입 통제, 문서 보안 관리</li>
</ol>

<div class="term-highlight">
	<strong>카드정보 보안</strong><br>
	회사는 PCI-DSS(Payment Card Industry Data Security Standard) 기준을 준수하여 카드정보를 안전하게 처리하며, 카드번호 전체는 저장하지 않습니다.
</div>

<h2>제8조 (개인정보 보호책임자)</h2>
<p>회사는 개인정보 처리에 관한 업무를 총괄해서 책임지고, 개인정보 처리와 관련한 이용자의 불만처리 및 피해구제 등을 위하여 아래와 같이 개인정보 보호책임자를 지정하고 있습니다.</p>

<div class="term-section">
	<strong>개인정보 보호책임자</strong><br>
	- 성명: 개인정보보호팀<br>
	- 연락처: 고객센터를 통해 문의<br>
	- 이메일: privacy@wonsung.co.kr
</div>

<h2>제9조 (개인정보 침해 관련 상담 및 신고)</h2>
<p>개인정보 침해에 대한 신고나 상담이 필요하신 경우 아래 기관에 문의하시기 바랍니다.</p>
<ul>
	<li>개인정보 침해신고센터 (privacy.kisa.or.kr / 국번없이 118)</li>
	<li>대검찰청 사이버수사과 (www.spo.go.kr / 국번없이 1301)</li>
	<li>경찰청 사이버안전국 (cyberbureau.police.go.kr / 국번없이 182)</li>
	<li>개인정보 분쟁조정위원회 (www.kopico.go.kr / 국번없이 1833-6972)</li>
</ul>

<h2>제10조 (고지의 의무)</h2>
<p>현 개인정보처리방침의 내용 추가, 삭제 및 수정이 있을 경우에는 시행일 7일 전부터 회사 홈페이지 또는 서비스 화면을 통해 공지합니다.</p>

<div class="term-highlight">
	<strong>시행일자</strong><br>
	본 개인정보처리방침은 2025년 12월 10일부터 시행됩니다.<br>
	최종 개정일: 2025년 12월 10일
</div>
</div>

<script>
function selectPgModule(mkcId, certiType, maxInstallment) {
	// PG 모듈 선택 UI 변경
	$('.pg-module-item').removeClass('selected');
	var selectedItem = event.target.closest('.pg-module-item');
	selectedItem.classList.add('selected');

	// 선택한 모듈의 data 속성에서 API 정보 가져오기
	var $item = $(selectedItem);
	var apiKey = $item.data('api-key') || '';
	var mid = $item.data('mid') || '';
	var mkey = $item.data('mkey') || '';
	var pgCode = $item.data('pg-code') || '';
	maxInstallment = maxInstallment || $item.data('max-installment') || 12;

	// hidden 필드에 PG 코드 설정
	$('#pg_id').val(mkcId);
	$('#pg_code').val(pgCode);

	// 최대 할부개월에 따라 할부 선택 옵션 업데이트
	updateInstallmentOptions(maxInstallment);

	// 인증 타입에 따라 패널 전환 및 API 정보 자동 입력
	if(certiType == 'auth') {
		// 구인증인 경우
		$('#auth_panel').show();
		$('#nonauth_panel').hide();
		$('#nonauth_hash_panel').hide();

		// 구인증 API 필드에 자동 입력
		$('#auth_dal_api_key').val(apiKey);
		$('#auth_mid').val(mid);
		$('#auth_mkey').val(mkey);

		// 비인증 필드 초기화
		$('#dal_api_key').val('');
		$('#mid').val('');
		$('#mkey').val('');

		// hashKey 재계산
		calculateHashKey('auth');
	} else {
		// 비인증인 경우 (nonauth)
		$('#auth_panel').hide();
		$('#nonauth_panel').show();
		$('#nonauth_hash_panel').show();

		// 비인증 API 필드에 자동 입력
		$('#dal_api_key').val(apiKey);
		$('#mid').val(mid);
		$('#mkey').val(mkey);

		// 구인증 필드 초기화
		$('#auth_dal_api_key').val('');
		$('#auth_mid').val('');
		$('#auth_mkey').val('');

		// hashKey 재계산
		calculateHashKey('nonauth');
	}

	// 결제 폼 스크롤
	$('html, body').animate({
		scrollTop: $('.payment-form-area').offset().top - 100
	}, 500);
}

// 최대 할부개월에 따라 할부 선택 옵션 업데이트
function updateInstallmentOptions(maxInstallment) {
	var $select = $('#pay_installment');
	var currentVal = $select.val();

	// 기존 옵션 제거
	$select.empty();

	// 기본 옵션 추가
	$select.append('<option value="">선택</option>');
	$select.append('<option value="00">일시불(00)</option>');

	// maxInstallment가 0이면 일시불만 허용
	if(maxInstallment == 0) {
		$select.val('00');
		return;
	}

	// 1개월은 일시불과 동일하므로 2개월부터 시작
	for(var i = 2; i <= maxInstallment; i++) {
		var val = i < 10 ? '0' + i : '' + i;
		$select.append('<option value="' + val + '">' + i + '개월(' + val + ')</option>');
	}

	// 이전 선택값이 범위 내에 있으면 유지
	if(currentVal && parseInt(currentVal) <= maxInstallment) {
		$select.val(currentVal);
	}
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

	// 비인증 선택시 API 인증정보 체크
	if($('#nonauth_panel').is(':visible')) {
		var dal_api_key = $("#dal_api_key").val();
		var mid = $("#mid").val();
		var mkey = $("#mkey").val();
		var ordNo = $("#ordNo").val();

		if (!dal_api_key) {
			alert('API KEY를 입력하세요');
			$("#btn1, #btn2").show();
			$('#dal_api_key').focus();
			return;
		}
		if (!mid) {
			alert('상점 ID(mid)를 입력하세요');
			$("#btn1, #btn2").show();
			$('#mid').focus();
			return;
		}
		if (!mkey) {
			alert('암호화 키(mkey)를 입력하세요');
			$("#btn1, #btn2").show();
			$('#mkey').focus();
			return;
		}
		if (!ordNo) {
			alert('주문번호를 입력하세요');
			$("#btn1, #btn2").show();
			$('#ordNo').focus();
			return;
		}
	}

	// 유효성 검사
	if (!pay_product) {
		alert('상품명을 입력하세요');
		$("#btn1, #btn2").show();
		$('#pay_product').focus();
		return;
	}
	if (!pay_price) {
		alert('승인금액을 입력하세요');
		$("#btn1, #btn2").show();
		$('#pay_price').focus();
		return;
	}
	if (!pay_pname) {
		alert('구매자명을 입력하세요');
		$("#btn1, #btn2").show();
		$('#pay_pname').focus();
		return;
	}
	// 휴대전화는 선택사항이므로 필수 체크 제거, 입력시에만 형식 검사
	if(pay_phone) {
		var regex = /^(01[0-9]{1}-?[0-9]{4}-?[0-9]{4}|01[0-9]{8,9})$/;
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
	if (!pay_installment) {
		alert('할부개월을 선택해주세요.');
		$("#btn1, #btn2").show();
		$('#pay_installment').focus();
		return;
	}
	if (!pay_YY) {
		alert('유효기간 년도(YY)를 선택해주세요');
		$("#btn1, #btn2").show();
		$('#pay_YY').focus();
		return;
	}
	if (!pay_MM) {
		alert('유효기간 월(MM)을 선택해주세요');
		$("#btn1, #btn2").show();
		$('#pay_MM').focus();
		return;
	}

	// 구인증 선택시 인증정보 체크
	if($('#auth_panel').is(':visible')) {
		var auth_dal_api_key = $("#auth_dal_api_key").val();
		var auth_mid = $("#auth_mid").val();
		var auth_mkey = $("#auth_mkey").val();
		var pay_password = $("#pay_password").val();
		var pay_certify = $("#pay_certify").val();

		if (!auth_dal_api_key) {
			alert('API KEY를 입력하세요');
			$("#btn1, #btn2").show();
			$('#auth_dal_api_key').focus();
			return;
		}
		if (!auth_mid) {
			alert('상점 ID(mid)를 입력하세요');
			$("#btn1, #btn2").show();
			$('#auth_mid').focus();
			return;
		}
		if (!auth_mkey) {
			alert('암호화 키(mkey)를 입력하세요');
			$("#btn1, #btn2").show();
			$('#auth_mkey').focus();
			return;
		}
		if (!pay_password) {
			alert('카드 비밀번호 앞 2자리(certPw)를 입력해주세요');
			$("#btn1, #btn2").show();
			$('#pay_password').focus();
			return;
		}
		if (!pay_certify) {
			alert('주민번호 앞 6자리 또는 사업자등록번호 10자리(certNo)를 입력해주세요');
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
	// 비인증 API 필드
	$('#dal_api_key').val('');
	$('#mid').val('');
	$('#mkey').val('');
	$('#ordNo').val('');
	$('#hashKey').val('');
	// 구인증 API 필드
	$('#auth_dal_api_key').val('');
	$('#auth_mid').val('');
	$('#auth_mkey').val('');
	$('#auth_hashKey').val('');
	// 결제 정보
	$('#pay_product').val('');
	$('#pay_price').val('');
	$('#pay_pname').val('');
	$('#pay_phone').val('');
	$('#pay_cardnum').val('');
	$('#pay_installment').val('');
	$('#pay_MM').val('');
	$('#pay_YY').val('');
	// 구인증 본인인증 필드
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

	// 휴대전화 자동 포맷 (숫자만 허용, 하이픈 없이)
	$("#pay_phone").keyup(function(){
		$(this).val($(this).val().replace(/[^0-9]/gi, ""));
	});

	// 입력 필드 포커스 효과
	$('.stripe-form-input').on('focus', function() {
		$(this).parent().addClass('focused');
	}).on('blur', function() {
		$(this).parent().removeClass('focused');
	});

	// 비인증 hashKey 자동 계산 (mid + goodAmt 변경 시)
	$('#mid, #pay_price').on('input change', function() {
		calculateHashKey('nonauth');
	});

	// 구인증 hashKey 자동 계산 (auth_mid + goodAmt 변경 시)
	$('#auth_mid').on('input change', function() {
		calculateHashKey('auth');
	});
	$('#pay_price').on('input change', function() {
		// 금액 변경시 구인증 hashKey도 같이 계산
		calculateHashKey('auth');
	});

	// 페이지 로드 시 첫 번째 PG 모듈의 API 정보 자동 입력
	initFirstPgModule();
});

// 첫 번째 PG 모듈 초기화 함수
function initFirstPgModule() {
	var $firstModule = $('.pg-module-item.selected').first();
	if($firstModule.length > 0) {
		var certiType = $firstModule.data('certi-type');
		var apiKey = $firstModule.data('api-key') || '';
		var mid = $firstModule.data('mid') || '';
		var mkey = $firstModule.data('mkey') || '';
		var pgCode = $firstModule.data('pg-code') || '';
		var mkcId = $firstModule.data('mkc-id') || '';
		var maxInstallment = $firstModule.data('max-installment') || 12;

		// hidden 필드에 PG 코드 설정
		$('#pg_id').val(mkcId);
		$('#pg_code').val(pgCode);

		// 최대 할부개월에 따라 할부 선택 옵션 업데이트
		updateInstallmentOptions(maxInstallment);

		// 인증 타입에 따라 패널 전환 및 API 정보 자동 입력
		if(certiType == 'auth') {
			$('#auth_panel').show();
			$('#nonauth_panel').hide();
			$('#nonauth_hash_panel').hide();
			$('#auth_dal_api_key').val(apiKey);
			$('#auth_mid').val(mid);
			$('#auth_mkey').val(mkey);
		} else {
			$('#auth_panel').hide();
			$('#nonauth_panel').show();
			$('#nonauth_hash_panel').show();
			$('#dal_api_key').val(apiKey);
			$('#mid').val(mid);
			$('#mkey').val(mkey);
		}

		// PG 모듈이 1개만 있을 경우 자동으로 입력 필드로 포커스
		var pgModuleCount = $('.pg-module-item').length;
		if(pgModuleCount === 1) {
			setTimeout(function() {
				// 비인증: 주문번호로 포커스, 구인증: 상품명으로 포커스
				if(certiType == 'auth') {
					$('#pay_product').focus();
				} else {
					$('#ordNo').focus();
				}
				// 결제 폼으로 스크롤
				$('html, body').animate({
					scrollTop: $('.payment-form-area').offset().top - 50
				}, 300);
			}, 100);
		}
	}
}

// hashKey 계산 함수 (sha256(mid + goodAmt))
async function calculateHashKey(type) {
	var mid, hashKeyField;

	if(type === 'auth') {
		mid = $('#auth_mid').val();
		hashKeyField = '#auth_hashKey';
	} else {
		mid = $('#mid').val();
		hashKeyField = '#hashKey';
	}

	var goodAmt = uncomma($('#pay_price').val());

	if(mid && goodAmt) {
		var dataToHash = mid + goodAmt;
		var hashHex = await sha256(dataToHash);
		$(hashKeyField).val(hashHex);
	} else {
		$(hashKeyField).val('');
	}
}

// SHA-256 해시 함수 (Web Crypto API 사용)
async function sha256(message) {
	const msgBuffer = new TextEncoder().encode(message);
	const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
	const hashArray = Array.from(new Uint8Array(hashBuffer));
	const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
	return hashHex;
}

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

// 약관 모달 관련 함수
function openTermsModal(type, e) {
	// 이벤트 기본 동작 및 버블링 방지
	if(e) {
		e.preventDefault();
		e.stopPropagation();
	}

	var overlay = document.getElementById('termsModalOverlay');
	var title = document.getElementById('termsModalTitle');
	var modalBody = document.getElementById('termsModalBody');
	var termsContent = document.getElementById('termsContent');
	var privacyContent = document.getElementById('privacyContent');

	if(type === 'terms') {
		title.textContent = '이용약관';
		modalBody.innerHTML = termsContent.innerHTML;
	} else if(type === 'privacy') {
		title.textContent = '개인정보처리방침';
		modalBody.innerHTML = privacyContent.innerHTML;
	}

	// 모달 body 스크롤 맨 위로
	modalBody.scrollTop = 0;

	overlay.classList.add('active');
	document.body.style.overflow = 'hidden';
}

function closeTermsModal() {
	var overlay = document.getElementById('termsModalOverlay');
	overlay.classList.remove('active');
	document.body.style.overflow = '';
}

// 오버레이 클릭 시 모달 닫기
document.addEventListener('DOMContentLoaded', function() {
	var overlay = document.getElementById('termsModalOverlay');
	if(overlay) {
		overlay.addEventListener('click', function(e) {
			if(e.target === overlay) {
				closeTermsModal();
			}
		});
	}

	// ESC 키로 모달 닫기
	document.addEventListener('keydown', function(e) {
		if(e.key === 'Escape') {
			closeTermsModal();
		}
	});
});
</script>

<?php
include_once('./_tail.php');
?>
