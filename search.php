<?php
	error_reporting( E_ALL );
	ini_set( "display_errors", 0 );
	include_once('./_common.php');
?>

<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>원성페이먼츠 거래내역 검색</title>
<link rel="image_src" href="./img/og_tag.png" />
<meta name="description" content="원성페이먼츠" />
<meta property="og:type" content="website" />
<meta property="og:title" content="원성페이먼츠" />
<meta property="og:description" content="가맹점관리 솔루션" />
<meta property="og:image" content="./img/thumbnail.jpg">
<link rel="shortcut icon" href="./img/favicon.ico" type="image/x-icon/">
<style>
:root {
	--point-color: #050E3C;
	--point-light: #0A1A5E;
	--accent-color: #3B82F6;
	--accent-light: #60A5FA;
	--accent-dark: #2563EB;
	--bg-gradient: linear-gradient(135deg, #050E3C 0%, #0A1A5E 50%, #1E3A8A 100%);
	--glass-bg: rgba(255, 255, 255, 0.08);
	--glass-border: rgba(255, 255, 255, 0.15);
	--success-color: #10B981;
	--danger-color: #EF4444;
	--warning-color: #F59E0B;
}

* {
	box-sizing: border-box;
	margin: 0;
	padding: 0;
}

@font-face { font-family: 'Pretendard'; font-weight: 300; font-style: normal; src:local(※), url('/font/Pretendard-Light.woff')}
@font-face { font-family: 'Pretendard'; font-weight: 400; font-style: normal; src:local(※), url('/font/Pretendard-Regular.woff')}
@font-face { font-family: 'Pretendard'; font-weight: 500; font-style: normal; src:local(※), url('/font/Pretendard-Medium.woff')}
@font-face { font-family: 'Pretendard'; font-weight: 600; font-style: normal; src:local(※), url('/font/Pretendard-SemiBold.woff')}
@font-face { font-family: 'Pretendard'; font-weight: 700; font-style: normal; src:local(※), url('/font/Pretendard-Bold.woff')}
@font-face { font-family: 'Pretendard'; font-weight: 800; font-style: normal; src:local(※), url('/font/Pretendard-ExtraBold.woff')}

body {
	font-family: 'Pretendard', -apple-system, BlinkMacSystemFont, sans-serif;
	min-height: 100vh;
	background: var(--bg-gradient);
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 20px;
	position: relative;
}

/* Background Animation */
.bg-animation {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	overflow: hidden;
	z-index: 0;
}

.bg-animation::before {
	content: '';
	position: absolute;
	top: -50%;
	left: -50%;
	width: 200%;
	height: 200%;
	background: radial-gradient(circle at 20% 80%, rgba(59, 130, 246, 0.15) 0%, transparent 50%),
				radial-gradient(circle at 80% 20%, rgba(96, 165, 250, 0.1) 0%, transparent 50%),
				radial-gradient(circle at 40% 40%, rgba(37, 99, 235, 0.08) 0%, transparent 40%);
	animation: bgFloat 20s ease-in-out infinite;
}

@keyframes bgFloat {
	0%, 100% { transform: translate(0, 0) rotate(0deg); }
	33% { transform: translate(30px, -30px) rotate(5deg); }
	66% { transform: translate(-20px, 20px) rotate(-5deg); }
}

/* Floating Particles */
.particles {
	position: absolute;
	width: 100%;
	height: 100%;
}

.particle {
	position: absolute;
	width: 4px;
	height: 4px;
	background: rgba(255, 255, 255, 0.3);
	border-radius: 50%;
	animation: particleFloat 15s infinite;
}

.particle:nth-child(1) { left: 10%; animation-delay: 0s; animation-duration: 20s; }
.particle:nth-child(2) { left: 20%; animation-delay: 2s; animation-duration: 18s; }
.particle:nth-child(3) { left: 30%; animation-delay: 4s; animation-duration: 22s; }
.particle:nth-child(4) { left: 40%; animation-delay: 1s; animation-duration: 16s; }
.particle:nth-child(5) { left: 50%; animation-delay: 3s; animation-duration: 19s; }
.particle:nth-child(6) { left: 60%; animation-delay: 5s; animation-duration: 21s; }
.particle:nth-child(7) { left: 70%; animation-delay: 0.5s; animation-duration: 17s; }
.particle:nth-child(8) { left: 80%; animation-delay: 2.5s; animation-duration: 23s; }
.particle:nth-child(9) { left: 90%; animation-delay: 4.5s; animation-duration: 15s; }

@keyframes particleFloat {
	0% { transform: translateY(100vh) scale(0); opacity: 0; }
	10% { opacity: 1; }
	90% { opacity: 1; }
	100% { transform: translateY(-100vh) scale(1); opacity: 0; }
}

/* Search Container */
.search-container {
	position: relative;
	z-index: 10;
	width: 100%;
	max-width: 420px;
}

.search-card {
	background: var(--glass-bg);
	backdrop-filter: blur(20px);
	-webkit-backdrop-filter: blur(20px);
	border: 1px solid var(--glass-border);
	border-radius: 24px;
	padding: 36px 32px;
	box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4),
				0 0 0 1px rgba(255, 255, 255, 0.05) inset;
	animation: cardSlideUp 0.8s ease-out;
}

@keyframes cardSlideUp {
	from {
		opacity: 0;
		transform: translateY(30px);
	}
	to {
		opacity: 1;
		transform: translateY(0);
	}
}

/* Header Section */
.header-section {
	text-align: center;
	margin-bottom: 28px;
}

.header-icon {
	width: 56px;
	height: 56px;
	background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-dark) 100%);
	border-radius: 14px;
	display: flex;
	align-items: center;
	justify-content: center;
	margin: 0 auto 16px;
	box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
}

.header-icon svg {
	width: 28px;
	height: 28px;
	fill: #fff;
}

.header-title {
	font-size: 20px;
	font-weight: 700;
	color: #fff;
	margin-bottom: 6px;
}

.header-desc {
	font-size: 13px;
	color: rgba(255, 255, 255, 0.5);
	font-weight: 400;
}

/* Form Styles */
.search-form {
	margin-bottom: 24px;
}

.input-group {
	position: relative;
	margin-bottom: 14px;
}

.input-group input {
	width: 100%;
	height: 50px;
	padding: 0 16px 0 46px;
	background: rgba(255, 255, 255, 0.05);
	border: 1px solid rgba(255, 255, 255, 0.1);
	border-radius: 10px;
	font-size: 14px;
	font-family: 'Pretendard', sans-serif;
	font-weight: 500;
	color: #fff;
	outline: none;
	transition: all 0.3s ease;
}

.input-group input::placeholder {
	color: rgba(255, 255, 255, 0.4);
	font-weight: 400;
}

.input-group input:focus {
	background: rgba(255, 255, 255, 0.08);
	border-color: var(--accent-color);
	box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

.input-group input:-webkit-autofill,
.input-group input:-webkit-autofill:hover,
.input-group input:-webkit-autofill:focus {
	-webkit-text-fill-color: #fff !important;
	-webkit-box-shadow: 0 0 0px 1000px rgba(59, 130, 246, 0.1) inset !important;
	transition: background-color 5000s ease-in-out 0s !important;
}

.input-icon {
	position: absolute;
	left: 14px;
	top: 50%;
	transform: translateY(-50%);
	width: 20px;
	height: 20px;
	display: flex;
	align-items: center;
	justify-content: center;
	pointer-events: none;
}

.input-icon svg {
	width: 18px;
	height: 18px;
	fill: rgba(255, 255, 255, 0.4);
	transition: fill 0.3s ease;
}

.input-group input:focus ~ .input-icon svg {
	fill: var(--accent-color);
}

/* Search Button */
.search-btn {
	width: 100%;
	height: 50px;
	margin-top: 20px;
	background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-dark) 100%);
	border: none;
	border-radius: 10px;
	font-size: 15px;
	font-weight: 600;
	font-family: 'Pretendard', sans-serif;
	color: #fff;
	cursor: pointer;
	position: relative;
	overflow: hidden;
	transition: all 0.3s ease;
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 8px;
}

.search-btn svg {
	width: 18px;
	height: 18px;
	fill: #fff;
}

.search-btn::before {
	content: '';
	position: absolute;
	top: 0;
	left: -100%;
	width: 100%;
	height: 100%;
	background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
	transition: left 0.5s ease;
}

.search-btn:hover {
	transform: translateY(-2px);
	box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
}

.search-btn:hover::before {
	left: 100%;
}

/* Results Section */
.results-section {
	margin-top: 24px;
	animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
	from { opacity: 0; transform: translateY(10px); }
	to { opacity: 1; transform: translateY(0); }
}

.result-card {
	background: rgba(255, 255, 255, 0.05);
	border: 1px solid rgba(255, 255, 255, 0.1);
	border-radius: 12px;
	overflow: hidden;
}

.result-header {
	background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-dark) 100%);
	padding: 14px 18px;
	display: flex;
	align-items: center;
	gap: 10px;
}

.result-header svg {
	width: 20px;
	height: 20px;
	fill: #fff;
}

.result-header span {
	font-size: 14px;
	font-weight: 600;
	color: #fff;
}

.result-body {
	padding: 0;
}

.result-row {
	display: flex;
	border-bottom: 1px solid rgba(255, 255, 255, 0.06);
	transition: background 0.2s ease;
}

.result-row:last-child {
	border-bottom: none;
}

.result-row:hover {
	background: rgba(255, 255, 255, 0.03);
}

.result-label {
	width: 100px;
	min-width: 100px;
	padding: 12px 14px;
	font-size: 12px;
	font-weight: 500;
	color: rgba(255, 255, 255, 0.5);
	background: rgba(255, 255, 255, 0.02);
	border-right: 1px solid rgba(255, 255, 255, 0.06);
}

.result-value {
	flex: 1;
	padding: 12px 14px;
	font-size: 13px;
	font-weight: 500;
	color: #fff;
	word-break: break-all;
}

.result-value.highlight {
	color: var(--accent-light);
	font-weight: 600;
}

.result-value.amount {
	font-size: 15px;
	font-weight: 700;
	color: #fff;
}

/* Status Badges */
.status-badge {
	display: inline-flex;
	align-items: center;
	padding: 4px 10px;
	border-radius: 6px;
	font-size: 11px;
	font-weight: 600;
}

.status-badge.approved {
	background: rgba(16, 185, 129, 0.15);
	color: #34D399;
}

.status-badge.canceled {
	background: rgba(239, 68, 68, 0.15);
	color: #F87171;
}

.status-badge.partial {
	background: rgba(245, 158, 11, 0.15);
	color: #FBBF24;
}

/* No Results */
.no-results {
	text-align: center;
	padding: 40px 20px;
}

.no-results svg {
	width: 48px;
	height: 48px;
	fill: rgba(255, 255, 255, 0.2);
	margin-bottom: 16px;
}

.no-results p {
	font-size: 14px;
	color: rgba(255, 255, 255, 0.5);
	font-weight: 400;
}

/* Footer Info */
.footer-section {
	margin-top: 24px;
	text-align: center;
	padding-top: 20px;
	border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.contact-badge {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	background: rgba(59, 130, 246, 0.15);
	padding: 6px 12px;
	border-radius: 16px;
	font-size: 12px;
	font-weight: 600;
	color: var(--accent-light);
	margin-bottom: 6px;
}

.contact-badge svg {
	width: 12px;
	height: 12px;
	fill: var(--accent-light);
}

.operation-hours {
	font-size: 11px;
	color: rgba(255, 255, 255, 0.4);
	font-weight: 400;
}

/* Responsive */
@media (max-width: 480px) {
	body {
		padding: 16px;
	}

	.search-card {
		padding: 28px 24px;
		border-radius: 20px;
	}

	.header-title {
		font-size: 18px;
	}

	.result-label {
		width: 85px;
		min-width: 85px;
		font-size: 11px;
		padding: 10px 12px;
	}

	.result-value {
		font-size: 12px;
		padding: 10px 12px;
	}
}
</style>
</head>
<body>
	<!-- Background Animation -->
	<div class="bg-animation">
		<div class="particles">
			<div class="particle"></div>
			<div class="particle"></div>
			<div class="particle"></div>
			<div class="particle"></div>
			<div class="particle"></div>
			<div class="particle"></div>
			<div class="particle"></div>
			<div class="particle"></div>
			<div class="particle"></div>
		</div>
	</div>

	<!-- Search Container -->
	<div class="search-container">
		<div class="search-card">
			<!-- Header Section -->
			<div class="header-section">
				<div class="header-icon">
					<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
						<path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
					</svg>
				</div>
				<h1 class="header-title">거래내역 검색</h1>
				<p class="header-desc">승인번호와 거래금액을 입력하세요</p>
			</div>

			<!-- Search Form -->
			<form method="get" class="search-form">
				<div class="input-group">
					<input type="text" name="pay_num" id="pay_num" placeholder="승인번호" value="<?php echo htmlspecialchars($pay_num); ?>" required autocomplete="off">
					<div class="input-icon">
						<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
							<path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/>
						</svg>
					</div>
				</div>

				<div class="input-group">
					<input type="text" name="pay" id="pay" placeholder="거래금액" value="<?php echo htmlspecialchars($pay); ?>" required autocomplete="off">
					<div class="input-icon">
						<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
							<path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/>
						</svg>
					</div>
				</div>

				<button type="submit" class="search-btn">
					<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
						<path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
					</svg>
					검색하기
				</button>
			</form>

			<?php
			if($pay_num && $pay) {

				$sql_common = " from g5_payment ";
				$sql_search = " where (1) and mb_6_name != '' ";
				$sql_search .= " and pay_num = '".sql_escape_string($pay_num)."' ";
				$sql_search .= " and pay = '".sql_escape_string($pay)."' ";

				if ($sst)
					$sql_order = " order by {$sst} {$sod} ";
				else
					$sql_order = " order by pay_datetime desc ";

				$sql = " select count(*) as cnt, sum(pay) as total_pay";
				$sql .= " {$sql_common} {$sql_search} {$sql_order} ";
				$row = sql_fetch($sql);

				$page_count = "100";
				if($page_count) {
					$rows = $page_count;
				} else {
					$rows = $config['cf_page_rows'];
				}

				$total_page  = ceil($total_count / $rows);
				if ($page < 1) $page = 1;
				$from_record = ($page - 1) * $rows;

				$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
				$result = sql_query($sql);
				$result_count = sql_num_rows($result);

				if($result_count > 0) {
			?>

			<!-- Results Section -->
			<div class="results-section">
				<?php
				for ($i=0; $row=sql_fetch_array($result); $i++) {
					$bgcolor = '#fff';
					$num = number_format($total_count - ($page - 1) * $rows - $i);

					// 결제 상태
					if($row['pay_type'] == "Y" && $row['pay_cdatetime'] > '0000-00-00 00:00:00') {
						$pay_type = "승인취소";
						$status_class = "canceled";
					} else if($row['pay_type'] == "Y") {
						$pay_type = "승인";
						$status_class = "approved";
					} else if($row['pay_type'] == "N") {
						$pay_type = "취소";
						$status_class = "canceled";
					} else if($row['pay_type'] == "B") {
						$pay_type = "부분취소";
						$status_class = "partial";
					} else if($row['pay_type'] == "M") {
						$pay_type = "망취소";
						$status_class = "canceled";
					} else if($row['pay_type'] == "X") {
						$pay_type = "수동취소";
						$status_class = "canceled";
					}

					// 할부
					if($row['pay_parti'] < 1) {
						$pay_parti = "일시불";
					} else {
						$pay_parti = $row['pay_parti']."개월";
					}

					// 디바이스 타입
					if($row['dv_type'] == "1") {
						$dv_type = "단말기";
					} else if($row['dv_type'] == "2") {
						$dv_type = "수기";
					} else {
						$dv_type = $row['dv_type'];
					}

					// 카드사
					$pay_card_name = str_replace("카드", "", $row['pay_card_name']);

					// PG사
					$pg_names = array(
						'k1' => '광원',
						'welcom' => '웰컴',
						'korpay' => '코페이',
						'danal' => '다날',
						'paysis' => '페이시스',
						'stn' => '섹타나인',
						'daou' => '다우'
					);
					$pg_name = isset($pg_names[$row['pg_name']]) ? $pg_names[$row['pg_name']] : $row['pg_name'];
				?>
				<div class="result-card">
					<div class="result-header">
						<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
							<path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
						</svg>
						<span>거래 상세정보</span>
					</div>
					<div class="result-body">
						<div class="result-row">
							<div class="result-label">가맹점명</div>
							<div class="result-value highlight"><?php echo htmlspecialchars($row['mb_6_name']); ?></div>
						</div>
						<div class="result-row">
							<div class="result-label">승인일시</div>
							<div class="result-value"><?php echo $row['pay_datetime']; ?></div>
						</div>
						<div class="result-row">
							<div class="result-label">승인금액</div>
							<div class="result-value amount"><?php echo number_format($row['pay']); ?>원</div>
						</div>
						<div class="result-row">
							<div class="result-label">할부</div>
							<div class="result-value"><?php echo $pay_parti; ?></div>
						</div>
						<div class="result-row">
							<div class="result-label">카드사</div>
							<div class="result-value"><?php echo mb_substr($pay_card_name, 0, 2); ?></div>
						</div>
						<div class="result-row">
							<div class="result-label">카드번호</div>
							<div class="result-value"><?php echo $row['pay_card_num']; ?></div>
						</div>
						<div class="result-row">
							<div class="result-label">승인번호</div>
							<div class="result-value highlight"><?php echo $row['pay_num']; ?></div>
						</div>
						<div class="result-row">
							<div class="result-label">TID</div>
							<div class="result-value"><?php echo $row['dv_tid']; ?></div>
						</div>
						<div class="result-row">
							<div class="result-label">구분</div>
							<div class="result-value">
								<span class="status-badge <?php echo $status_class; ?>"><?php echo $pay_type; ?></span>
							</div>
						</div>
						<div class="result-row">
							<div class="result-label">결제종류</div>
							<div class="result-value"><?php echo $dv_type; ?></div>
						</div>
						<div class="result-row">
							<div class="result-label">PG</div>
							<div class="result-value"><?php echo $pg_name; ?></div>
						</div>
					</div>
				</div>
				<?php } ?>
			</div>

			<?php
				} else {
			?>
			<!-- No Results -->
			<div class="results-section">
				<div class="no-results">
					<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
						<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
					</svg>
					<p>검색 결과가 없습니다.<br>승인번호와 금액을 다시 확인해주세요.</p>
				</div>
			</div>
			<?php
				}
			}
			?>

			<!-- Footer -->
			<div class="footer-section">
				<span class="contact-badge">
					<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
						<path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
					</svg>
					1555-0985
				</span>
				<p class="operation-hours">운영시간 09:00 ~ 18:00 (주말, 공휴일 제외)</p>
			</div>
		</div>
	</div>
</body>
</html>
