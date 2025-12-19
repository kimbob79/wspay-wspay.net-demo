<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>원성페이먼츠 로그인 - 판매자센터</title>

<!-- SEO Meta Tags -->
<meta name="description" content="원성페이먼츠 판매자센터 로그인 - 결제 관리, 정산 조회, 가맹점 관리를 위한 통합 솔루션" />
<meta name="keywords" content="Sunshine Pay, 판매자센터, 로그인, 결제관리, 정산조회, 가맹점관리" />
<meta name="author" content="원성페이먼츠" />
<meta name="robots" content="noindex, nofollow" />
<meta name="googlebot" content="noindex, nofollow" />

<!-- Open Graph Meta Tags -->
<meta property="og:type" content="website" />
<meta property="og:site_name" content="원성페이먼츠" />
<meta property="og:title" content="원성페이먼츠 판매자센터 - 로그인" />
<meta property="og:description" content="가맹점 관리 솔루션 - 결제 관리, 정산 조회, 가맹점 관리" />
<meta property="og:image" content="./img/og_tag.png" />
<meta property="og:url" content="<?php echo 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; ?>" />
<meta property="og:locale" content="ko_KR" />

<!-- Twitter Card Meta Tags -->
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="원성페이먼츠 판매자센터" />
<meta name="twitter:description" content="가맹점 관리 솔루션" />
<meta name="twitter:image" content="./img/og_tag.png" />

<!-- Favicon -->
<link rel="icon" type="image/svg+xml" href="/img/favicon.svg?v=<?php echo time(); ?>">
<link rel="alternate icon" href="/img/favicon.svg?v=<?php echo time(); ?>" type="image/svg+xml">
<link rel="shortcut icon" href="/img/favicon.svg?v=<?php echo time(); ?>">
<link rel="apple-touch-icon" href="/img/favicon.svg?v=<?php echo time(); ?>">
<link rel="manifest" href="/manifest.php?v=<?php echo time(); ?>">
<meta name="msapplication-TileColor" content="#3B82F6">
<meta name="msapplication-TileImage" content="/img/favicon.svg?v=<?php echo time(); ?>">
<meta name="theme-color" content="#3B82F6">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="원성페이먼츠">

<!-- Canonical URL -->
<link rel="canonical" href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; ?>" />

<!-- JSON-LD Structured Data -->
<script type="application/ld+json">
{
	"@context": "https://schema.org",
	"@type": "WebApplication",
	"name": "원성페이먼츠 판매자센터",
	"applicationCategory": "FinanceApplication",
	"operatingSystem": "Web",
	"description": "결제 관리, 정산 조회, 가맹점 관리를 위한 통합 솔루션",
	"offers": {
		"@type": "Offer",
		"price": "0",
		"priceCurrency": "KRW"
	},
	"provider": {
		"@type": "Organization",
		"name": "원성페이먼츠",
		"url": "<?php echo 'https://'.$_SERVER['HTTP_HOST']; ?>",
		"contactPoint": {
			"@type": "ContactPoint",
			"telephone": "+82-1555-0985",
			"contactType": "Customer Service",
			"availableLanguage": "Korean",
			"hoursAvailable": {
				"@type": "OpeningHoursSpecification",
				"dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
				"opens": "09:00",
				"closes": "18:00"
			}
		}
	}
}
</script>

<!-- Performance Optimization -->
<link rel="dns-prefetch" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" href="/img/favicon.svg?v=<?php echo time(); ?>" as="image" type="image/svg+xml">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
	font-family: 'Pretendard', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
	min-height: 100vh;
	background: var(--bg-gradient);
	display: flex;
	align-items: center;
	justify-content: center;
	overflow: hidden;
	position: relative;
}

/* Animated Background */
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
	animation: bgFloat 8s ease-in-out infinite;
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
	animation: particleFloat 6s infinite;
}

.particle:nth-child(1) { left: 10%; animation-delay: 0s; animation-duration: 7s; }
.particle:nth-child(2) { left: 20%; animation-delay: 1s; animation-duration: 6s; }
.particle:nth-child(3) { left: 30%; animation-delay: 2s; animation-duration: 8s; }
.particle:nth-child(4) { left: 40%; animation-delay: 0.5s; animation-duration: 5s; }
.particle:nth-child(5) { left: 50%; animation-delay: 1.5s; animation-duration: 7s; }
.particle:nth-child(6) { left: 60%; animation-delay: 2.5s; animation-duration: 6.5s; }
.particle:nth-child(7) { left: 70%; animation-delay: 0.3s; animation-duration: 5.5s; }
.particle:nth-child(8) { left: 80%; animation-delay: 1.2s; animation-duration: 8s; }
.particle:nth-child(9) { left: 90%; animation-delay: 2.2s; animation-duration: 6s; }

@keyframes particleFloat {
	0% { transform: translateY(100vh) scale(0); opacity: 0; }
	10% { opacity: 1; }
	90% { opacity: 1; }
	100% { transform: translateY(-100vh) scale(1); opacity: 0; }
}

/* Geometric Shapes - Fintech Style */
.geometric-container {
	position: absolute;
	width: 100%;
	height: 100%;
	overflow: hidden;
}

.hexagon {
	position: absolute;
	width: 100px;
	height: 100px;
	opacity: 0.1;
}

.hexagon::before {
	content: '';
	position: absolute;
	width: 100%;
	height: 100%;
	background: transparent;
	border: 2px solid rgba(59, 130, 246, 0.4);
	clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
	animation: hexagonRotate 8s linear infinite;
}

.hexagon:nth-child(1) {
	top: 10%;
	left: 15%;
	animation: float1 6s ease-in-out infinite;
}

.hexagon:nth-child(2) {
	top: 60%;
	left: 75%;
	width: 80px;
	height: 80px;
	animation: float2 7s ease-in-out infinite;
}

.hexagon:nth-child(3) {
	top: 30%;
	left: 80%;
	width: 120px;
	height: 120px;
	animation: float3 8s ease-in-out infinite;
}

.triangle {
	position: absolute;
	width: 0;
	height: 0;
	border-left: 40px solid transparent;
	border-right: 40px solid transparent;
	border-bottom: 70px solid rgba(96, 165, 250, 0.15);
	animation: triangleRotate 10s linear infinite;
}

.triangle:nth-child(4) {
	top: 20%;
	left: 5%;
	animation: float1 7s ease-in-out infinite, triangleRotate 10s linear infinite;
}

.triangle:nth-child(5) {
	top: 70%;
	left: 85%;
	transform: rotate(180deg);
	animation: float2 6s ease-in-out infinite, triangleRotate 12s linear infinite reverse;
}

.square {
	position: absolute;
	width: 60px;
	height: 60px;
	border: 2px solid rgba(37, 99, 235, 0.2);
	background: transparent;
	animation: squareRotate 6s linear infinite;
}

.square:nth-child(6) {
	top: 80%;
	left: 10%;
	animation: float3 7s ease-in-out infinite, squareRotate 6s linear infinite;
}

.square:nth-child(7) {
	top: 15%;
	left: 90%;
	width: 50px;
	height: 50px;
	animation: float1 8s ease-in-out infinite, squareRotate 8s linear infinite reverse;
}

/* Circuit Lines */
.circuit-line {
	position: absolute;
	background: linear-gradient(90deg,
		transparent 0%,
		rgba(59, 130, 246, 0.3) 50%,
		transparent 100%);
	height: 1px;
	animation: circuitFlow 8s linear infinite;
}

.circuit-line:nth-child(8) {
	top: 25%;
	width: 200px;
	left: -200px;
	animation: circuitFlow1 4s linear infinite;
}

.circuit-line:nth-child(9) {
	top: 55%;
	width: 250px;
	left: -250px;
	animation: circuitFlow2 5s linear infinite;
}

.circuit-line:nth-child(10) {
	top: 75%;
	width: 180px;
	left: -180px;
	animation: circuitFlow3 3.5s linear infinite;
}

/* Grid Pattern */
.grid-pattern {
	position: absolute;
	width: 100%;
	height: 100%;
	background-image:
		linear-gradient(rgba(59, 130, 246, 0.03) 1px, transparent 1px),
		linear-gradient(90deg, rgba(59, 130, 246, 0.03) 1px, transparent 1px);
	background-size: 50px 50px;
	animation: gridMove 8s linear infinite;
}

/* Animations */
@keyframes hexagonRotate {
	0% { transform: rotate(0deg); }
	100% { transform: rotate(360deg); }
}

@keyframes triangleRotate {
	0% { transform: rotate(0deg); }
	100% { transform: rotate(360deg); }
}

@keyframes squareRotate {
	0% { transform: rotate(0deg); }
	100% { transform: rotate(45deg); }
}

@keyframes float1 {
	0%, 100% { transform: translate(0, 0); }
	33% { transform: translate(30px, -20px); }
	66% { transform: translate(-20px, 30px); }
}

@keyframes float2 {
	0%, 100% { transform: translate(0, 0); }
	33% { transform: translate(-30px, 20px); }
	66% { transform: translate(20px, -30px); }
}

@keyframes float3 {
	0%, 100% { transform: translate(0, 0); }
	50% { transform: translate(15px, 15px); }
}

@keyframes circuitFlow1 {
	0% { left: -200px; opacity: 0; }
	10% { opacity: 1; }
	90% { opacity: 1; }
	100% { left: 100%; opacity: 0; }
}

@keyframes circuitFlow2 {
	0% { left: -250px; opacity: 0; }
	10% { opacity: 1; }
	90% { opacity: 1; }
	100% { left: 100%; opacity: 0; }
}

@keyframes circuitFlow3 {
	0% { left: -180px; opacity: 0; }
	10% { opacity: 1; }
	90% { opacity: 1; }
	100% { left: 100%; opacity: 0; }
}

@keyframes gridMove {
	0% { transform: translate(0, 0); }
	100% { transform: translate(50px, 50px); }
}

/* Login Container */
.login-container {
	position: relative;
	z-index: 10;
	width: 100%;
	max-width: 440px;
	padding: 20px;
}

.login-card {
	background: var(--glass-bg);
	backdrop-filter: blur(20px);
	-webkit-backdrop-filter: blur(20px);
	border: 1px solid var(--glass-border);
	border-radius: 24px;
	padding: 48px 40px;
	box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4),
				0 0 0 1px rgba(255, 255, 255, 0.05) inset,
				0 0 60px rgba(59, 130, 246, 0.15);
	animation: cardSlideUp 0.8s ease-out;
	position: relative;
	overflow: hidden;
}

.login-card::before {
	content: '';
	position: absolute;
	top: -2px;
	left: -2px;
	right: -2px;
	bottom: -2px;
	background: linear-gradient(45deg,
		transparent,
		rgba(59, 130, 246, 0.1),
		transparent,
		rgba(96, 165, 250, 0.1),
		transparent);
	border-radius: 24px;
	z-index: -1;
	animation: borderGlow 2s linear infinite;
	background-size: 400% 400%;
}

@keyframes borderGlow {
	0% { background-position: 0% 50%; }
	50% { background-position: 100% 50%; }
	100% { background-position: 0% 50%; }
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

/* Logo Section */
.logo-section {
	text-align: center;
	margin-bottom: 40px;
	position: relative;
}

.logo-section::before,
.logo-section::after {
	content: '';
	position: absolute;
	width: 60px;
	height: 2px;
	background: linear-gradient(90deg,
		transparent,
		rgba(59, 130, 246, 0.5),
		transparent);
	top: 50%;
	animation: dataFlow 2s ease-in-out infinite;
}

.logo-section::before {
	left: 0;
}

.logo-section::after {
	right: 0;
	animation-delay: 1s;
}

@keyframes dataFlow {
	0%, 100% { opacity: 0.3; transform: scaleX(1); }
	50% { opacity: 1; transform: scaleX(1.2); }
}

.logo-title {
	display: flex;
	align-items: baseline;
	justify-content: center;
	gap: 8px;
	margin-bottom: 8px;
}

.logo-main {
	font-size: 28px;
	font-weight: 800;
	color: #FFC107;
	letter-spacing: 1px;
}

.logo-pay {
	color: #fff;
}

.logo-sub {
	font-size: 18px;
	font-weight: 500;
	color: #fff;
	font-style: italic;
}

.logo-desc {
	font-size: 14px;
	color: rgba(255, 255, 255, 0.6);
	font-weight: 400;
}

/* Form Styles */
.login-form {
	margin-top: 32px;
}

.input-group {
	position: relative;
	margin-bottom: 20px;
}

.input-group input {
	width: 100%;
	height: 56px;
	padding: 0 20px 0 52px;
	background: rgba(255, 255, 255, 0.05);
	border: 1px solid rgba(255, 255, 255, 0.1);
	border-radius: 12px;
	font-size: 15px;
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
	box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15),
				0 0 20px rgba(59, 130, 246, 0.1);
	animation: inputGlow 1.5s ease-in-out infinite;
}

@keyframes inputGlow {
	0%, 100% { box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15), 0 0 20px rgba(59, 130, 246, 0.1); }
	50% { box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25), 0 0 30px rgba(59, 130, 246, 0.2); }
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
	left: 18px;
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
	width: 20px;
	height: 20px;
	fill: rgba(255, 255, 255, 0.4);
	transition: fill 0.3s ease;
}

.input-group input:focus ~ .input-icon svg {
	fill: var(--accent-color);
}

.password-toggle {
	position: absolute;
	right: 16px;
	top: 50%;
	transform: translateY(-50%);
	width: 24px;
	height: 24px;
	cursor: pointer;
	display: flex;
	align-items: center;
	justify-content: center;
	opacity: 0.5;
	transition: opacity 0.3s ease;
}

.password-toggle:hover {
	opacity: 1;
}

.password-toggle svg {
	width: 20px;
	height: 20px;
	fill: rgba(255, 255, 255, 0.6);
}

/* Login Button */
.login-btn {
	width: 100%;
	height: 56px;
	margin-top: 28px;
	background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-dark) 100%);
	border: none;
	border-radius: 12px;
	font-size: 16px;
	font-weight: 600;
	font-family: 'Pretendard', sans-serif;
	color: #fff;
	cursor: pointer;
	position: relative;
	overflow: hidden;
	transition: all 0.3s ease;
}

.login-btn::before {
	content: '';
	position: absolute;
	top: 0;
	left: -100%;
	width: 100%;
	height: 100%;
	background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
	transition: left 0.5s ease;
}

.login-btn::after {
	content: '';
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: linear-gradient(0deg,
		transparent 0%,
		rgba(255, 255, 255, 0.05) 50%,
		transparent 100%);
	background-size: 100% 200%;
	animation: scanLine 1.5s linear infinite;
}

@keyframes scanLine {
	0% { background-position: 0% 0%; }
	100% { background-position: 0% 200%; }
}

.login-btn:hover {
	transform: translateY(-2px);
	box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
}

.login-btn:hover::before {
	left: 100%;
}

.login-btn:active {
	transform: translateY(0);
}

/* Secondary Button */
.search-btn {
	width: 100%;
	height: 48px;
	margin-top: 12px;
	background: transparent;
	border: 1px solid rgba(255, 255, 255, 0.2);
	border-radius: 12px;
	font-size: 14px;
	font-weight: 500;
	font-family: 'Pretendard', sans-serif;
	color: rgba(255, 255, 255, 0.7);
	cursor: pointer;
	transition: all 0.3s ease;
}

.search-btn:hover {
	background: rgba(255, 255, 255, 0.05);
	border-color: rgba(255, 255, 255, 0.3);
	color: #fff;
}

/* Footer Info */
.login-footer {
	margin-top: 36px;
	text-align: center;
	padding-top: 24px;
	border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.contact-info {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 8px;
	margin-bottom: 8px;
}

.contact-badge {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	background: rgba(59, 130, 246, 0.15);
	padding: 6px 14px;
	border-radius: 20px;
	font-size: 13px;
	font-weight: 600;
	color: var(--accent-light);
}

.contact-badge svg {
	width: 14px;
	height: 14px;
	fill: var(--accent-light);
}

.operation-hours {
	font-size: 12px;
	color: rgba(255, 255, 255, 0.4);
	font-weight: 400;
}

/* Security Badge */
.security-badge {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 6px;
	margin-top: 16px;
	font-size: 11px;
	color: rgba(255, 255, 255, 0.3);
}

.security-badge svg {
	width: 12px;
	height: 12px;
	fill: rgba(255, 255, 255, 0.3);
}

/* Responsive */
@media (max-width: 480px) {
	.login-card {
		padding: 36px 28px;
		border-radius: 20px;
	}

	.logo-main {
		font-size: 24px;
	}

	.logo-sub {
		font-size: 16px;
	}

	.input-group input {
		height: 52px;
		font-size: 14px;
	}

	.login-btn {
		height: 52px;
		font-size: 15px;
	}

	.search-btn {
		height: 44px;
		font-size: 13px;
	}
}

@media (max-height: 700px) {
	.login-card {
		padding: 32px 36px;
	}

	.logo-section {
		margin-bottom: 28px;
	}

	.login-footer {
		margin-top: 24px;
		padding-top: 20px;
	}
}
</style>
</head>
<body>
	<!-- Background Animation -->
	<div class="bg-animation">
		<!-- Grid Pattern -->
		<div class="grid-pattern"></div>

		<!-- Geometric Shapes -->
		<div class="geometric-container">
			<div class="hexagon"></div>
			<div class="hexagon"></div>
			<div class="hexagon"></div>
			<div class="triangle"></div>
			<div class="triangle"></div>
			<div class="square"></div>
			<div class="square"></div>
			<div class="circuit-line"></div>
			<div class="circuit-line"></div>
			<div class="circuit-line"></div>
		</div>

		<!-- Floating Particles -->
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

	<!-- Login Container -->
	<div class="login-container">
		<div class="login-card">
			<!-- Logo Section -->
			<div class="logo-section">
				<div class="logo-title">
					<span class="logo-main">Sunshine <span class="logo-pay">Pay</span></span>
				</div>
				<p class="logo-desc">판매자센터</p>
			</div>

			<!-- Login Form -->
			<form action="./login_check.php" method="post" class="login-form" autocomplete="on">
				<input type="hidden" name="url" value="<?php echo $p; ?>">

				<div class="input-group">
					<input type="text" name="mb_id" placeholder="아이디를 입력하세요" required autocomplete="username">
					<div class="input-icon">
						<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
							<path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
						</svg>
					</div>
				</div>

				<div class="input-group">
					<input type="password" name="mb_password" id="pw_box" placeholder="패스워드를 입력하세요" required autocomplete="current-password">
					<div class="input-icon">
						<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
							<path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
						</svg>
					</div>
					<div class="password-toggle" id="togglePassword">
						<svg id="eyeIcon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
							<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
						</svg>
					</div>
				</div>

				<button type="submit" class="login-btn"><span style="position: relative; z-index: 1;">로그인</span></button>
			</form>

			<button type="button" class="search-btn" onclick="window.open('./search.php', 'window_name', 'width=430, height=600, location=no, status=no, scrollbars=yes');">
				승인번호 검색
			</button>

			<!-- Footer -->
			<div class="login-footer">
				<div class="contact-info">
					<span class="contact-badge">
						<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
							<path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
						</svg>
						1555-0985
					</span>
				</div>
				<p class="operation-hours">운영시간 09:00 ~ 18:00 (주말, 공휴일 제외)</p>
				<div class="security-badge">
					<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
						<path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
					</svg>
					<span>SSL 보안 연결</span>
				</div>
			</div>
		</div>
	</div>

	<script>
		// Password Toggle
		const togglePassword = document.getElementById('togglePassword');
		const passwordInput = document.getElementById('pw_box');
		const eyeIcon = document.getElementById('eyeIcon');

		togglePassword.addEventListener('click', function() {
			const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
			passwordInput.setAttribute('type', type);

			if (type === 'text') {
				eyeIcon.innerHTML = '<path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>';
			} else {
				eyeIcon.innerHTML = '<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>';
			}
		});

		// Input focus animation
		const inputs = document.querySelectorAll('.input-group input');
		inputs.forEach(input => {
			input.addEventListener('focus', function() {
				this.parentElement.classList.add('focused');
			});
			input.addEventListener('blur', function() {
				this.parentElement.classList.remove('focused');
			});
		});
	</script>
</body>
</html>
