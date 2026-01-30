<?php
/**
 * URL 결제 공개 페이지 (비로그인 접근 가능)
 * URL: /pay/{code} -> url_payment_public.php?code={code}
 */

// 기본 설정 로드
error_reporting(E_ALL);
ini_set("display_errors", 0);

// Gnuboard 프레임워크 로드 (로그인 불필요한 공개 페이지)
if(!defined('_GNUBOARD_')) define('_GNUBOARD_', true);

// gnu_module 또는 _engin 디렉토리 탐색
$_g5_path = file_exists(__DIR__.'/gnu_module/common.php')
    ? __DIR__.'/gnu_module/common.php'
    : __DIR__.'/_engin/common.php';
include_once($_g5_path);
unset($_g5_path);

// URL 코드
$code = isset($_GET['code']) ? trim($_GET['code']) : '';

// 코드 형식 검증 (9자리 영숫자)
if(!preg_match('/^[A-Za-z0-9]{9}$/', $code)) {
    die_with_message("유효하지 않은 결제 링크입니다.", "잘못된 URL");
}

// DB에서 URL 결제 정보 조회
$url_pay = sql_fetch("SELECT u.*, m.mb_nick as merchant_name,
                             mkc.mkc_pg_code, mkc.mkc_pg_name, mkc.mkc_type, mkc.mpc_id,
                             mpc.mpc_pg_code, mpc.mpc_pg_name, mpc.mpc_type,
                             COALESCE(mkc.mkc_api_key, mpc.mpc_api_key) as api_key,
                             COALESCE(mkc.mkc_mid, mpc.mpc_mid) as mid,
                             COALESCE(mkc.mkc_mkey, mpc.mpc_mkey) as mkey
                      FROM g5_url_payment u
                      LEFT JOIN g5_member m ON u.mb_id = m.mb_id
                      LEFT JOIN g5_member_keyin_config mkc ON u.mkc_id = mkc.mkc_id
                      LEFT JOIN g5_manual_payment_config mpc ON mkc.mpc_id = mpc.mpc_id
                      WHERE u.up_code = '".sql_real_escape_string($code)."'");

if(!$url_pay) {
    die_with_message("존재하지 않는 결제 링크입니다.", "결제 링크 오류");
}

// 상태 검증 - 결제 완료된 경우 완료 페이지 표시
if($url_pay['up_status'] == 'used') {
    // 결제 정보 조회
    $payment_info = sql_fetch("SELECT * FROM g5_payment_keyin WHERE pk_id = '".$url_pay['pk_id']."'");
    show_payment_complete($url_pay, $payment_info);
    exit;
} else if($url_pay['up_status'] == 'expired') {
    die_with_message("유효기간이 만료된 결제 링크입니다.", "만료된 링크");
} else if($url_pay['up_status'] == 'cancelled') {
    die_with_message("취소된 결제 링크입니다.", "취소된 링크");
}

// 유효기간 검증
if(strtotime($url_pay['up_expire_datetime']) < time()) {
    sql_query("UPDATE g5_url_payment SET up_status = 'expired' WHERE up_id = '".$url_pay['up_id']."'");
    die_with_message("유효기간이 만료되었습니다.\n만료일시: ".date('Y-m-d H:i', strtotime($url_pay['up_expire_datetime'])), "만료된 링크");
}

// PG 정보
$pg_code = $url_pay['mpc_pg_code'] ?: $url_pay['mkc_pg_code'];
$pg_name = $url_pay['mpc_pg_name'] ?: $url_pay['mkc_pg_name'];
$auth_type = $url_pay['mpc_type'] ?: $url_pay['mkc_type'];
$is_auth = ($auth_type == 'auth'); // 구인증 여부

// 결제 완료 페이지 표시 함수
function show_payment_complete($url_pay, $payment_info) {
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>결제 완료 - Sunshine Pay</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Noto Sans KR', sans-serif;
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .complete-container {
            max-width: 400px;
            width: 100%;
        }
        .brand-header {
            text-align: center;
            padding: 15px 0;
            margin-bottom: 15px;
        }
        .logo-main {
            font-family: 'Exo 2', sans-serif;
            font-size: 22px;
            font-weight: 800;
            color: #FFB300;
            letter-spacing: 1.5px;
            text-shadow: 0 2px 8px rgba(255, 179, 0, 0.3);
        }
        .logo-pay { color: #1a1d29; }
        .receipt {
            width: 100%;
            background: #fff;
            border: 1px solid #ddd;
            padding: 24px 20px;
            border-radius: 12px;
            font-size: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .receipt-header {
            text-align: center;
            padding-bottom: 16px;
            border-bottom: 1px dashed #ccc;
            margin-bottom: 16px;
        }
        .success-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
        }
        .success-icon i { font-size: 28px; color: #fff; }
        .receipt-header h1 {
            font-size: 18px;
            font-weight: 700;
            color: #2e7d32;
            margin-bottom: 4px;
        }
        .receipt-header .en {
            font-size: 10px;
            color: #999;
            letter-spacing: 1px;
        }
        .receipt .status {
            display: inline-block;
            margin-top: 10px;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            background: #e8f5e9;
            color: #2e7d32;
        }
        .receipt .total-box {
            text-align: center;
            padding: 16px 0;
            border-bottom: 1px dashed #ccc;
            margin-bottom: 16px;
        }
        .receipt .total-label { font-size: 11px; color: #888; margin-bottom: 6px; }
        .receipt .total-amount {
            font-size: 28px;
            font-weight: 800;
            color: #2e7d32;
        }
        .receipt .total-amount span { font-size: 14px; }
        .receipt .section {
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px dotted #e0e0e0;
        }
        .receipt .section:last-of-type { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .receipt .section-title {
            font-size: 12px;
            font-weight: 700;
            color: #2e7d32;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .receipt .section-title i { font-size: 11px; opacity: 0.8; }
        .receipt .section-title .en { font-size: 9px; color: #999; font-weight: 400; margin-left: 4px; }
        .receipt .row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 12px;
        }
        .receipt .row .label { color: #666; }
        .receipt .row .value { color: #333; font-weight: 500; text-align: right; max-width: 180px; word-break: break-all; }
        .receipt .row .value.highlight { color: #2e7d32; font-weight: 700; }
        .receipt .footer {
            margin-top: 16px;
            padding-top: 12px;
            border-top: 1px dashed #ccc;
            text-align: center;
        }
        .receipt .legal {
            font-size: 10px;
            color: #999;
            line-height: 1.5;
            margin-bottom: 8px;
        }
        .receipt .btn-area {
            display: flex;
            gap: 10px;
            margin-top: 16px;
        }
        .receipt .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
        }
        .receipt .btn-primary { background: linear-gradient(135deg, #4caf50 0%, #43a047 100%); color: #fff; }
        .receipt .btn-primary:hover { box-shadow: 0 4px 15px rgba(76, 175, 80, 0.4); }
        @media (max-width: 480px) {
            body { padding: 12px; }
            .receipt { padding: 20px 16px; }
            .receipt .total-amount { font-size: 24px; }
        }
    </style>
</head>
<body>
<div class="complete-container">
    <div class="brand-header">
        <span class="logo-main">Sunshine <span class="logo-pay">Pay</span></span>
    </div>
    <div class="receipt">
        <div class="receipt-header">
            <div class="success-icon"><i class="fa fa-check"></i></div>
            <h1>결제가 완료되었습니다</h1>
            <div class="en">PAYMENT COMPLETED</div>
            <div class="status">승인완료</div>
        </div>
        <div class="total-box">
            <div class="total-label">결제금액</div>
            <div class="total-amount"><?php echo number_format($url_pay['up_amount']); ?><span>원</span></div>
        </div>
        <div class="section">
            <div class="section-title"><i class="fa fa-shopping-cart"></i>결제정보<span class="en">Payment</span></div>
            <div class="row">
                <span class="label">승인번호</span>
                <span class="value highlight"><?php echo $payment_info['pk_app_no'] ?: '-'; ?></span>
            </div>
            <div class="row">
                <span class="label">상품명</span>
                <span class="value"><?php echo htmlspecialchars($url_pay['up_goods_name']); ?></span>
            </div>
            <div class="row">
                <span class="label">카드번호</span>
                <span class="value"><?php echo $payment_info['pk_card_no_masked'] ?: '-'; ?></span>
            </div>
            <div class="row">
                <span class="label">할부</span>
                <span class="value"><?php echo ($payment_info['pk_installment'] == '00' || !$payment_info['pk_installment']) ? '일시불' : $payment_info['pk_installment'].'개월'; ?></span>
            </div>
            <div class="row">
                <span class="label">결제일시</span>
                <span class="value"><?php echo date('Y-m-d H:i', strtotime($url_pay['up_paid_datetime'])); ?></span>
            </div>
        </div>
        <div class="section">
            <div class="section-title"><i class="fa fa-store"></i>판매자정보<span class="en">Seller</span></div>
            <div class="row">
                <span class="label">판매자</span>
                <span class="value"><?php echo htmlspecialchars($url_pay['up_seller_name']); ?></span>
            </div>
            <?php if($url_pay['up_seller_phone']) { ?>
            <div class="row">
                <span class="label">연락처</span>
                <span class="value"><?php echo htmlspecialchars($url_pay['up_seller_phone']); ?></span>
            </div>
            <?php } ?>
        </div>
        <div class="footer">
            <div class="legal">본 거래는 URL결제(비대면 키인결제)로 처리되었습니다.<br>결제 관련 문의는 판매자에게 연락해주세요.</div>
            <div class="btn-area">
                <button type="button" class="btn btn-primary" onclick="closeWindow()">
                    <i class="fa fa-check"></i> 확인
                </button>
            </div>
        </div>
    </div>
</div>
<script>
function closeWindow() {
    window.close();
    setTimeout(function() {
        if (navigator.userAgent.indexOf('KAKAOTALK') > -1) {
            window.location.href = 'kakaotalk://inappbrowser/close';
            return;
        }
        if (navigator.userAgent.indexOf('NAVER') > -1) {
            window.location.href = 'naversearchapp://inappbrowser/close';
            return;
        }
        if (window.history.length > 1) {
            window.history.back();
        } else {
            window.location.href = 'about:blank';
        }
    }, 300);
}
</script>
</body>
</html>
<?php
}

// 에러 메시지 출력 함수
function die_with_message($message, $title = "알림") {
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $title; ?> - 원성페이먼츠</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .error-card { background: #fff; border-radius: 12px; padding: 40px 30px; text-align: center; max-width: 400px; width: 100%; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .error-icon { font-size: 60px; margin-bottom: 20px; }
        .error-title { font-size: 20px; font-weight: 600; color: #333; margin-bottom: 10px; }
        .error-message { font-size: 14px; color: #666; line-height: 1.6; white-space: pre-line; }
        .error-card.expired .error-icon { color: #ff9800; }
        .error-card.used .error-icon { color: #4caf50; }
        .error-card.cancelled .error-icon { color: #f44336; }
        .error-card.invalid .error-icon { color: #9e9e9e; }
    </style>
</head>
<body>
    <div class="error-card invalid">
        <div class="error-icon">⚠️</div>
        <div class="error-title"><?php echo htmlspecialchars($title); ?></div>
        <div class="error-message"><?php echo htmlspecialchars($message); ?></div>
    </div>
</body>
</html>
<?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>결제 - <?php echo htmlspecialchars($url_pay['up_goods_name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Noto Sans KR', sans-serif;
            background: linear-gradient(135deg, #e0e0e0 0%, #f5f5f5 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .payment-container {
            max-width: 450px;
            margin: 0 auto;
        }
        .brand-header {
            text-align: center;
            padding: 15px 0;
            margin-bottom: 15px;
        }
        .brand-logo {
            display: inline-block;
        }
        .logo-main {
            font-family: 'Exo 2', sans-serif;
            font-size: 22px;
            font-weight: 800;
            color: #FFB300;
            letter-spacing: 1.5px;
            line-height: 1;
            text-shadow: 0 2px 8px rgba(255, 179, 0, 0.3);
        }
        .logo-pay {
            color: #1a1d29;
        }
        .payment-info-card {
            background: linear-gradient(135deg, #009688 0%, #00acc1 100%);
            border-radius: 12px;
            padding: 16px;
            color: #fff;
            margin-bottom: 12px;
            box-shadow: 0 4px 15px rgba(0, 150, 136, 0.3);
        }
        .payment-info-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }
        .merchant-name {
            font-size: 12px;
            opacity: 0.85;
        }
        .amount {
            font-size: 22px;
            font-weight: 700;
        }
        .amount small {
            font-size: 13px;
            font-weight: 400;
        }
        .goods-name {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .goods-desc {
            font-size: 12px;
            line-height: 1.4;
            opacity: 0.9;
            background: rgba(255,255,255,0.1);
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 8px;
        }
        .expire-info {
            font-size: 11px;
            opacity: 0.8;
            padding-top: 8px;
            border-top: 1px solid rgba(255,255,255,0.2);
        }
        .payment-form-card {
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            border: 1px solid #e8e8e8;
        }
        .form-section-title {
            font-size: 14px;
            font-weight: 700;
            color: #333;
            margin-bottom: 12px;
            padding: 10px 14px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-section-title i { color: #009688; }
        .form-group {
            margin-bottom: 12px;
        }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #333;
            margin-bottom: 6px;
        }
        .form-input {
            width: 100%;
            height: 46px;
            padding: 0 14px;
            font-size: 14px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            transition: all 0.2s;
            box-sizing: border-box;
        }
        .form-input:focus {
            outline: none;
            border-color: #009688;
        }
        .form-row {
            display: flex;
            gap: 10px;
            align-items: stretch;
        }
        .form-row .form-group {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }
        .form-row .form-group .form-label {
            flex: 0 0 auto;
        }
        select.form-input {
            height: 46px;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23009688' stroke-width='3'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
        }
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 8px;
            cursor: pointer;
        }
        .checkbox-wrapper input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #009688;
        }
        .checkbox-label {
            font-size: 13px;
            color: #333;
            cursor: pointer;
        }
        .checkbox-label a {
            color: #009688;
            text-decoration: none;
        }
        .pay-button {
            width: 100%;
            padding: 16px;
            font-size: 16px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #009688 0%, #00796b 100%);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            margin-top: 15px;
            transition: all 0.3s;
        }
        .pay-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 150, 136, 0.4);
        }
        .pay-button:disabled {
            background: #bdbdbd;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 12px;
            padding: 10px;
            background: #e8f5e9;
            border-radius: 8px;
            font-size: 12px;
            color: #2e7d32;
        }
        .secure-badge i { color: #4caf50; }
        /* 로딩 오버레이 */
        .wrap-loading {
            position: fixed;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            background: rgba(255,255,255,0.95);
            z-index: 10001;
            display: none;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        .wrap-loading.show { display: flex; }
        .wrap-loading .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #e0e0e0;
            border-top: 4px solid #009688;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .wrap-loading .loading-text {
            margin-top: 20px;
            font-size: 14px;
            color: #666;
            text-align: center;
        }

        /* 결제 확인 모달 */
        .payment-confirm-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            justify-content: center;
            align-items: center;
            padding: 15px;
        }
        .payment-confirm-overlay.show { display: flex; }
        .payment-confirm-modal {
            background: #fff;
            border-radius: 12px;
            width: 100%;
            max-width: 360px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            animation: modalSlideIn 0.3s ease;
        }
        .payment-confirm-header {
            background: linear-gradient(135deg, #009688 0%, #00796b 100%);
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        .payment-confirm-header i {
            font-size: 32px;
            margin-bottom: 8px;
            display: block;
        }
        .payment-confirm-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }
        .payment-confirm-body {
            padding: 20px;
            background: #fff;
        }
        .payment-confirm-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .payment-confirm-item:last-child { border-bottom: none; }
        .payment-confirm-item .label {
            color: #666;
            font-size: 13px;
        }
        .payment-confirm-item .value {
            color: #333;
            font-size: 13px;
            font-weight: 500;
            text-align: right;
            max-width: 180px;
            word-break: break-all;
        }
        .payment-confirm-item.total {
            margin-top: 8px;
            padding-top: 12px;
            border-top: 2px solid #e0e0e0;
            border-bottom: none;
        }
        .payment-confirm-item.total .label {
            color: #333;
            font-size: 14px;
            font-weight: 600;
        }
        .payment-confirm-item.total .value {
            color: #009688;
            font-size: 20px;
            font-weight: 700;
        }
        .payment-confirm-footer {
            display: flex;
            gap: 10px;
            padding: 16px 20px 20px;
            background: #f8f9fa;
        }
        .payment-confirm-btn {
            flex: 1;
            padding: 14px 16px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .payment-confirm-btn.cancel {
            background: #f5f5f5;
            color: #666;
        }
        .payment-confirm-btn.cancel:hover { background: #eee; }
        .payment-confirm-btn.confirm {
            background: linear-gradient(135deg, #009688 0%, #00796b 100%);
            color: #fff;
        }
        .payment-confirm-btn.confirm:hover {
            box-shadow: 0 4px 15px rgba(0, 150, 136, 0.4);
        }

        /* 에러 모달 */
        .error-modal-overlay {
            position: fixed;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 10000;
            display: none;
            justify-content: center;
            align-items: center;
            padding: 15px;
        }
        .error-modal-overlay.show { display: flex; }
        .error-modal {
            width: 100%;
            max-width: 340px;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            animation: modalSlideIn 0.3s ease;
        }
        .error-modal-header {
            background: linear-gradient(135deg, #e53935 0%, #ef5350 100%);
            padding: 24px 20px;
            text-align: center;
        }
        .error-modal-header i {
            font-size: 40px;
            color: #fff;
            margin-bottom: 10px;
        }
        .error-modal-header h3 {
            color: #fff;
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }
        .error-modal-body {
            padding: 24px 20px;
            text-align: center;
        }
        .error-modal-message {
            font-size: 14px;
            color: #333;
            line-height: 1.6;
            word-break: keep-all;
        }
        .error-modal-footer {
            padding: 16px 20px 20px;
            display: flex;
            gap: 10px;
        }
        .error-modal-footer button {
            flex: 1;
            padding: 14px 16px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .error-modal-footer .btn-retry {
            background: linear-gradient(135deg, #009688 0%, #00796b 100%);
            color: #fff;
        }
        .error-modal-footer .btn-retry:hover {
            box-shadow: 0 4px 15px rgba(0, 150, 136, 0.4);
        }
        .error-modal-footer .btn-close {
            background: #f5f5f5;
            color: #666;
        }
        .error-modal-footer .btn-close:hover { background: #eee; }

        /* 영수증/성공 모달 */
        .receipt-overlay {
            position: fixed;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 10000;
            display: none;
            justify-content: center;
            align-items: center;
            padding: 15px;
        }
        .receipt-overlay.show { display: flex; }
        .receipt-modal {
            width: 100%;
            max-width: 340px;
            max-height: 90vh;
            overflow-y: auto;
            background: #fff;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 12px;
            font-size: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            animation: modalSlideIn 0.3s ease;
        }
        .receipt-modal-header {
            text-align: center;
            padding-bottom: 14px;
            border-bottom: 1px dashed #ccc;
            margin-bottom: 14px;
        }
        .receipt-modal-header .success-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
        }
        .receipt-modal-header .success-icon i { font-size: 24px; color: #fff; }
        .receipt-modal-header h1 {
            font-size: 16px;
            font-weight: 700;
            color: #2e7d32;
            margin-bottom: 2px;
        }
        .receipt-modal-header .en {
            font-size: 9px;
            color: #999;
            letter-spacing: 1px;
        }
        .receipt-modal .status {
            display: inline-block;
            margin-top: 8px;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            background: #e8f5e9;
            color: #2e7d32;
        }
        .receipt-modal .total-box {
            text-align: center;
            padding: 14px 0;
            border-bottom: 1px dashed #ccc;
            margin-bottom: 14px;
        }
        .receipt-modal .total-label { font-size: 10px; color: #888; margin-bottom: 4px; }
        .receipt-modal .total-amount {
            font-size: 26px;
            font-weight: 800;
            color: #2e7d32;
        }
        .receipt-modal .total-amount span { font-size: 14px; }
        .receipt-modal .section {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dotted #e0e0e0;
        }
        .receipt-modal .section:last-of-type { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .receipt-modal .section-title {
            font-size: 11px;
            font-weight: 700;
            color: #2e7d32;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .receipt-modal .section-title i { font-size: 10px; opacity: 0.7; }
        .receipt-modal .section-title .en { font-size: 9px; color: #999; font-weight: 400; margin-left: 4px; }
        .receipt-modal .row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 11px;
        }
        .receipt-modal .row .label { color: #666; }
        .receipt-modal .row .value { color: #333; font-weight: 500; text-align: right; max-width: 180px; word-break: break-all; }
        .receipt-modal .row .value.highlight { color: #2e7d32; font-weight: 700; }
        .receipt-modal .footer {
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px dashed #ccc;
            text-align: center;
        }
        .receipt-modal .legal {
            font-size: 9px;
            color: #999;
            line-height: 1.5;
            margin-bottom: 8px;
        }
        .receipt-modal .btn-area {
            display: flex;
            gap: 8px;
            margin-top: 14px;
        }
        .receipt-modal .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            text-decoration: none;
        }
        .receipt-modal .btn-primary { background: linear-gradient(135deg, #4caf50 0%, #43a047 100%); color: #fff; }
        .receipt-modal .btn-primary:hover { box-shadow: 0 4px 12px rgba(76, 175, 80, 0.4); }
        /* 에러 스타일 */
        .form-input.error {
            border-color: #f44336 !important;
            background-color: #fff8f7 !important;
        }
        .form-input.error:focus {
            box-shadow: 0 0 0 3px rgba(244, 67, 54, 0.1);
        }
        .field-error-msg {
            color: #f44336;
            font-size: 11px;
            margin-top: 4px;
            display: none;
        }
        .field-error-msg.show {
            display: block;
        }
        .checkbox-wrapper.error {
            border: 1px solid #f44336;
            background: #fff8f7;
        }
        .checkbox-wrapper.error .checkbox-label {
            color: #f44336;
        }
        /* 카드 입력 섹션 - 카드 위에 직접 입력 */
        .card-input-section {
            margin-bottom: 20px;
        }
        .card-visual {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            border-radius: 20px;
            padding: 24px 20px 20px;
            color: #fff;
            position: relative;
            overflow: hidden;
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.4),
                0 0 0 1px rgba(255,255,255,0.1) inset;
            min-height: 220px;
        }
        .card-visual::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 60%);
            pointer-events: none;
        }
        .card-visual::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -30%;
            width: 80%;
            height: 80%;
            background: radial-gradient(circle, rgba(0,150,136,0.15) 0%, transparent 60%);
            pointer-events: none;
        }
        .card-visual-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }
        .card-chip {
            width: 50px;
            height: 38px;
            background: linear-gradient(135deg, #ffd700 0%, #daa520 50%, #ffd700 100%);
            border-radius: 8px;
            position: relative;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .card-chip::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 6px;
            right: 6px;
            height: 1px;
            background: rgba(0,0,0,0.25);
        }
        .card-chip::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 6px;
            bottom: 6px;
            width: 1px;
            background: rgba(0,0,0,0.25);
        }
        .card-chip-inner {
            position: absolute;
            top: 8px;
            left: 8px;
            right: 8px;
            bottom: 8px;
            border: 1px solid rgba(0,0,0,0.15);
            border-radius: 3px;
        }
        .card-brand {
            font-size: 24px;
            opacity: 0.95;
            transition: all 0.3s;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        /* 카드번호 입력 영역 */
        .card-number-input-area {
            position: relative;
            z-index: 2;
            margin-bottom: 18px;
        }
        .card-number-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 2px;
            opacity: 0.6;
            margin-bottom: 6px;
            display: block;
        }
        .card-number-input {
            width: 100%;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 10px;
            padding: 14px 16px;
            font-family: 'Courier New', 'SF Mono', monospace;
            font-size: 18px;
            letter-spacing: 2px;
            color: #fff;
            transition: all 0.3s;
            backdrop-filter: blur(10px);
        }
        .card-number-input::placeholder {
            color: rgba(255,255,255,0.4);
            letter-spacing: 4px;
        }
        .card-number-input:focus {
            outline: none;
            border-color: rgba(0,150,136,0.8);
            background: rgba(255,255,255,0.18);
            box-shadow: 0 0 20px rgba(0,150,136,0.3);
        }

        /* 카드 하단 정보 영역 */
        .card-visual-footer {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 12px;
            position: relative;
            z-index: 2;
        }
        .card-field {
            position: relative;
        }
        .card-field-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            opacity: 0.5;
            margin-bottom: 4px;
            display: block;
        }
        .card-field-input {
            width: 100%;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 13px;
            color: #fff;
            transition: all 0.3s;
            text-align: center;
            appearance: none;
            -webkit-appearance: none;
        }
        .card-field-input::placeholder {
            color: rgba(255,255,255,0.35);
        }
        .card-field-input:focus {
            outline: none;
            border-color: rgba(0,150,136,0.7);
            background: rgba(255,255,255,0.15);
        }
        /* 셀렉트 박스 화살표 */
        .card-field-select {
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.5)' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 14px;
            padding-right: 28px;
            cursor: pointer;
        }
        .card-field-select option {
            background: #1a1a2e;
            color: #fff;
        }

        /* 유효기간 인라인 */
        .expiry-inline {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .expiry-inline select {
            flex: 1;
            min-width: 0;
            padding: 10px 24px 10px 8px;
            text-align: left;
            background-position: right 6px center;
            background-size: 12px;
        }
        .expiry-separator {
            color: rgba(255,255,255,0.5);
            font-size: 14px;
        }

        /* 할부 선택 (카드 아래) */
        .installment-row {
            background: #fff;
            border-radius: 12px;
            padding: 14px 16px;
            margin-top: 12px;
            border: 1px solid #e8e8e8;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .installment-row label {
            font-size: 13px;
            font-weight: 600;
            color: #333;
            white-space: nowrap;
        }
        .installment-row select {
            flex: 1;
            padding: 10px 14px;
            font-size: 14px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background: #f8f9fa;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23009688' stroke-width='3'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
        }
        .installment-row select:focus {
            outline: none;
            border-color: #009688;
        }

        /* 카드 포커스 효과 */
        .card-visual.card-focused {
            box-shadow:
                0 25px 70px rgba(0, 0, 0, 0.5),
                0 0 0 2px rgba(0,150,136,0.5),
                0 0 30px rgba(0,150,136,0.2);
            transform: translateY(-2px);
        }
        .card-visual {
            transition: all 0.3s ease;
        }

        /* 카드 입력 에러 스타일 */
        .card-number-input.error,
        .card-field-input.error {
            border-color: rgba(244, 67, 54, 0.8) !important;
            background: rgba(244, 67, 54, 0.15) !important;
        }

        @media (max-width: 480px) {
            .card-visual {
                padding: 20px 16px 16px;
                border-radius: 16px;
                min-height: 200px;
            }
            .card-chip {
                width: 42px;
                height: 32px;
            }
            .card-number-input {
                font-size: 15px;
                letter-spacing: 1px;
                padding: 12px 12px;
            }
            .card-visual-footer {
                gap: 8px;
            }
            .card-field-input {
                padding: 8px 10px;
                font-size: 12px;
            }
            .card-field-label {
                font-size: 7px;
            }
            .card-brand {
                font-size: 20px;
            }
        }
        .terms-link {
            color: #009688;
            text-decoration: underline;
            font-size: 12px;
            margin-left: 5px;
        }
        .terms-link:hover {
            color: #00796b;
        }
        /* 약관 모달 */
        .terms-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 10000;
            align-items: center;
            justify-content: center;
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
            max-height: 80vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease;
        }
        @keyframes modalSlideIn {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .terms-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 24px;
            background: linear-gradient(135deg, #009688 0%, #00acc1 100%);
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
        .terms-modal-close {
            width: 36px;
            height: 36px;
            border: none;
            background: rgba(255,255,255,0.2);
            color: #fff;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: all 0.2s;
        }
        .terms-modal-close:hover {
            background: rgba(255,255,255,0.4);
            transform: rotate(90deg);
        }
        .terms-modal-body {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
            font-size: 13px;
            line-height: 1.7;
            color: #333;
        }
        .terms-modal-body h2 {
            font-size: 15px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 20px 0 10px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #009688;
        }
        .terms-modal-body h2:first-child { margin-top: 0; }
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
        .terms-modal-body li { margin: 4px 0; }
        .terms-modal-body .term-section {
            margin-bottom: 20px;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #009688;
        }
        .terms-modal-body .term-highlight {
            background: #e0f2f1;
            padding: 12px 16px;
            border-radius: 8px;
            margin: 12px 0;
            border: 1px solid #80cbc4;
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
            background: linear-gradient(135deg, #009688 0%, #00796b 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .terms-modal-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 150, 136, 0.4);
        }
        @media (max-width: 480px) {
            body { padding: 8px; }
            .brand-header {
                padding: 10px 0;
                margin-bottom: 10px;
            }
            .logo-main {
                font-size: 20px;
            }
            /* 결제 정보 영역 컴팩트화 */
            .payment-info-card {
                padding: 12px;
                margin-bottom: 8px;
                border-radius: 10px;
            }
            .payment-info-header {
                margin-bottom: 4px;
            }
            .merchant-name {
                font-size: 11px;
            }
            .amount {
                font-size: 20px;
            }
            .amount small {
                font-size: 11px;
            }
            .goods-name {
                font-size: 13px;
                margin-bottom: 6px;
            }
            .goods-desc {
                font-size: 11px;
                padding: 8px;
                margin-bottom: 6px;
            }
            .expire-info {
                font-size: 10px;
                padding-top: 6px;
            }
            /* 카드 영역 */
            .card-input-section {
                margin-bottom: 12px;
            }
            .card-visual {
                padding: 16px 14px 14px;
                min-height: 180px;
            }
            .card-chip {
                width: 38px;
                height: 28px;
            }
            .card-number-input {
                font-size: 14px;
                letter-spacing: 1px;
                padding: 10px 12px;
            }
            .card-number-label {
                font-size: 8px;
                margin-bottom: 4px;
            }
            .card-number-input-area {
                margin-bottom: 14px;
            }
            .card-visual-footer {
                gap: 10px;
            }
            .card-field-label {
                font-size: 7px;
                margin-bottom: 3px;
            }
            .card-field-input {
                padding: 8px 10px;
                font-size: 12px;
            }
            .expiry-inline select {
                padding: 8px 20px 8px 6px;
                font-size: 12px;
                background-position: right 4px center;
                background-size: 10px;
            }
            .expiry-separator {
                font-size: 12px;
            }
            /* 폼 영역 컴팩트화 */
            .payment-form-card {
                padding: 14px;
            }
            .form-section-title {
                font-size: 13px;
                padding: 8px 12px;
                margin-bottom: 10px;
            }
            .form-group {
                margin-bottom: 10px;
            }
            .form-label {
                font-size: 12px;
                margin-bottom: 4px;
            }
            .form-input {
                height: 42px;
                padding: 0 12px;
                font-size: 13px;
            }
            select.form-input {
                height: 42px;
            }
            .form-row {
                gap: 8px;
            }
            .form-row .form-group {
                margin-bottom: 10px;
                display: block;
            }
            .form-row .form-group .form-label {
                flex: none;
            }
            /* 약관 영역 */
            .checkbox-wrapper {
                padding: 10px;
                margin-bottom: 6px;
            }
            .checkbox-label {
                font-size: 12px;
            }
            .checkbox-wrapper input[type="checkbox"] {
                width: 16px;
                height: 16px;
            }
            /* 결제 버튼 */
            .pay-button {
                padding: 14px;
                font-size: 15px;
                margin-top: 12px;
            }
            .secure-badge {
                padding: 8px;
                margin-top: 10px;
                font-size: 11px;
            }
            .terms-modal {
                width: 95%;
                max-height: 90vh;
                border-radius: 12px;
            }
            .terms-modal-header {
                padding: 14px 18px;
                border-radius: 12px 12px 0 0;
            }
            .terms-modal-title { font-size: 16px; }
            .terms-modal-body { padding: 16px; }
            .terms-modal-footer {
                padding: 12px 16px;
                border-radius: 0 0 12px 12px;
            }
        }
    </style>
</head>
<body>

<!-- 로딩 오버레이 -->
<div class="wrap-loading" id="loadingOverlay">
    <div class="spinner"></div>
    <div class="loading-text">결제 처리 중입니다.<br>잠시만 기다려주세요.</div>
</div>

<!-- 결제 확인 모달 -->
<div class="payment-confirm-overlay" id="paymentConfirmOverlay">
    <div class="payment-confirm-modal">
        <div class="payment-confirm-header">
            <i class="fa fa-credit-card"></i>
            <h3>결제 확인</h3>
        </div>
        <div class="payment-confirm-body">
            <div class="payment-confirm-item">
                <span class="label">상품명</span>
                <span class="value" id="confirmGoodsName">-</span>
            </div>
            <div class="payment-confirm-item">
                <span class="label">구매자</span>
                <span class="value" id="confirmBuyerName">-</span>
            </div>
            <div class="payment-confirm-item">
                <span class="label">카드번호</span>
                <span class="value" id="confirmCardNo">-</span>
            </div>
            <div class="payment-confirm-item">
                <span class="label">할부</span>
                <span class="value" id="confirmInstallment">-</span>
            </div>
            <div class="payment-confirm-item total">
                <span class="label">결제금액</span>
                <span class="value" id="confirmAmount">-</span>
            </div>
        </div>
        <div class="payment-confirm-footer">
            <button type="button" class="payment-confirm-btn cancel" onclick="closePaymentConfirm()">취소</button>
            <button type="button" class="payment-confirm-btn confirm" onclick="executePayment()">
                <i class="fa fa-lock"></i> 결제하기
            </button>
        </div>
    </div>
</div>

<!-- 에러 모달 -->
<div class="error-modal-overlay" id="errorModalOverlay">
    <div class="error-modal">
        <div class="error-modal-header">
            <i class="fa fa-times-circle"></i>
            <h3>결제 실패</h3>
        </div>
        <div class="error-modal-body">
            <div class="error-modal-message" id="errorModalMessage">오류가 발생했습니다.</div>
        </div>
        <div class="error-modal-footer">
            <button type="button" class="btn-retry" onclick="closeErrorModal()"><i class="fa fa-refresh"></i> 다시 시도</button>
            <button type="button" class="btn-close" onclick="closeErrorModalAndClose()"><i class="fa fa-times"></i> 닫기</button>
        </div>
    </div>
</div>

<!-- 결제완료 영수증 모달 -->
<div class="receipt-overlay" id="receiptOverlay">
    <div class="receipt-modal">
        <div class="receipt-modal-header">
            <div class="success-icon"><i class="fa fa-check"></i></div>
            <h1>결제가 완료되었습니다</h1>
            <div class="en">PAYMENT COMPLETED</div>
            <div class="status">승인완료</div>
        </div>
        <div class="total-box">
            <div class="total-label">결제금액</div>
            <div class="total-amount"><span id="receiptAmount">0</span><span>원</span></div>
        </div>
        <div class="section">
            <div class="section-title"><i class="fa fa-shopping-cart"></i>결제정보<span class="en">Payment</span></div>
            <div class="row">
                <span class="label">승인번호</span>
                <span class="value highlight" id="receiptAppNo">-</span>
            </div>
            <div class="row">
                <span class="label">상품명</span>
                <span class="value" id="receiptGoodsName">-</span>
            </div>
            <div class="row">
                <span class="label">카드번호</span>
                <span class="value" id="receiptCardNo">-</span>
            </div>
            <div class="row">
                <span class="label">할부</span>
                <span class="value" id="receiptInstallment">일시불</span>
            </div>
            <div class="row">
                <span class="label">결제일시</span>
                <span class="value" id="receiptDateTime">-</span>
            </div>
        </div>
        <div class="section">
            <div class="section-title"><i class="fa fa-user"></i>구매자정보<span class="en">Buyer</span></div>
            <div class="row">
                <span class="label">구매자명</span>
                <span class="value" id="receiptBuyerName">-</span>
            </div>
        </div>
        <div class="footer">
            <div class="legal">본 거래는 URL결제(비대면 키인결제)로 처리되었습니다.</div>
            <div class="btn-area">
                <a href="javascript:void(0);" class="btn btn-primary" onclick="closeReceiptAndRedirect()">
                    <i class="fa fa-check"></i> 확인
                </a>
            </div>
        </div>
    </div>
</div>

<div class="payment-container">
    <div class="brand-header">
        <div class="brand-logo">
            <span class="logo-main">Sunshine <span class="logo-pay">Pay</span></span>
        </div>
    </div>

    <div class="payment-info-card">
        <div class="payment-info-header">
            <div class="merchant-name"><?php echo htmlspecialchars($url_pay['up_seller_name']); ?></div>
            <div class="amount"><?php echo number_format($url_pay['up_amount']); ?><small>원</small></div>
        </div>
        <div class="goods-name"><?php echo htmlspecialchars($url_pay['up_goods_name']); ?></div>
        <?php if($url_pay['up_goods_desc']) { ?>
        <div class="goods-desc"><?php echo nl2br(htmlspecialchars($url_pay['up_goods_desc'])); ?></div>
        <?php } ?>
        <div class="expire-info">
            <i class="fa fa-clock-o"></i> <?php echo date('Y.m.d H:i', strtotime($url_pay['up_expire_datetime'])); ?>까지
        </div>
    </div>

    <div class="payment-form-card">
        <form id="paymentForm" method="post" action="/url_payment_api.php">
            <input type="hidden" name="up_id" value="<?php echo $url_pay['up_id']; ?>">
            <input type="hidden" name="up_code" value="<?php echo $url_pay['up_code']; ?>">

            <!-- 카드 입력 영역 - 카드 위에 직접 입력 -->
            <div class="card-input-section">
                <div class="card-visual">
                    <div class="card-visual-header">
                        <div class="card-chip"><div class="card-chip-inner"></div></div>
                        <div class="card-brand" id="cardBrand">
                            <i class="fa fa-credit-card"></i>
                        </div>
                    </div>

                    <!-- 카드번호 직접 입력 -->
                    <div class="card-number-input-area">
                        <span class="card-number-label">Card Number</span>
                        <input type="text" name="card_no" id="card_no" class="card-number-input" placeholder="0000 0000 0000 0000" maxlength="19" required oninput="handleCardInput(this)" inputmode="numeric" autocomplete="cc-number">
                    </div>

                    <!-- 카드 하단: 유효기간, 할부 -->
                    <div class="card-visual-footer">
                        <div class="card-field">
                            <span class="card-field-label">Expires</span>
                            <div class="expiry-inline">
                                <select name="expire_mm" id="expire_mm" class="card-field-input card-field-select" required>
                                    <option value="">MM</option>
                                    <?php for($m = 1; $m <= 12; $m++) { ?>
                                    <option value="<?php echo sprintf('%02d', $m); ?>"><?php echo sprintf('%02d', $m); ?></option>
                                    <?php } ?>
                                </select>
                                <span class="expiry-separator">/</span>
                                <select name="expire_yy" id="expire_yy" class="card-field-input card-field-select" required>
                                    <option value="">YY</option>
                                    <?php for($y = date('y'); $y <= date('y') + 15; $y++) { ?>
                                    <option value="<?php echo sprintf('%02d', $y); ?>"><?php echo sprintf('%02d', $y); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="card-field">
                            <span class="card-field-label">Installment</span>
                            <select name="installment" class="card-field-input card-field-select" required>
                                <option value="00">일시불</option>
                                <?php if($url_pay['up_amount'] >= 50000) { ?>
                                <?php for($i = 2; $i <= 12; $i++) { ?>
                                <option value="<?php echo sprintf('%02d', $i); ?>"><?php echo $i; ?>개월</option>
                                <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <?php if($is_auth) { ?>
            <div class="form-section-title">
                <i class="fa fa-user"></i> 본인인증 정보
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">비밀번호 앞 2자리</label>
                    <input type="password" name="cert_pw" class="form-input" placeholder="**" maxlength="2" required>
                </div>
                <div class="form-group">
                    <label class="form-label">생년월일/사업자번호</label>
                    <input type="text" name="cert_no" class="form-input" placeholder="YYMMDD 또는 사업자번호" maxlength="10" required>
                </div>
            </div>
            <?php } ?>

            <div class="form-section-title">
                <i class="fa fa-user"></i> 결제자 정보
            </div>

            <div class="form-group">
                <label class="form-label">결제자명</label>
                <input type="text" name="buyer_name" class="form-input" placeholder="이름" value="<?php echo htmlspecialchars($url_pay['up_buyer_name']); ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">연락처<?php echo in_array($pg_code, ['rootup', 'stn', 'winglobal']) ? '' : ' <span style="color:#999;font-size:11px;">(선택)</span>'; ?></label>
                    <input type="text" name="buyer_phone" id="buyer_phone" class="form-input" placeholder="010-0000-0000" value="<?php echo htmlspecialchars($url_pay['up_buyer_phone']); ?>" <?php echo in_array($pg_code, ['rootup', 'stn', 'winglobal']) ? 'required' : ''; ?>>
                </div>
                <?php if($pg_code == 'winglobal') { ?>
                <div class="form-group">
                    <label class="form-label">이메일</label>
                    <input type="email" name="buyer_email" id="buyer_email" class="form-input" placeholder="email@example.com" required>
                </div>
                <?php } ?>
            </div>

            <div class="form-section-title">
                <i class="fa fa-file-text-o"></i> 약관동의
            </div>

            <label class="checkbox-wrapper">
                <input type="checkbox" name="agree_all" id="agree_all">
                <span class="checkbox-label"><strong>전체 동의</strong></span>
            </label>

            <label class="checkbox-wrapper">
                <input type="checkbox" name="agree_term" id="agree_term" required>
                <span class="checkbox-label">
                    이용약관 동의 (필수)
                    <a href="#" class="terms-link" onclick="openTermsModal('terms', event); return false;">(보기)</a>
                </span>
            </label>

            <label class="checkbox-wrapper">
                <input type="checkbox" name="agree_privacy" id="agree_privacy" required>
                <span class="checkbox-label">
                    개인정보 처리방침 동의 (필수)
                    <a href="#" class="terms-link" onclick="openTermsModal('privacy', event); return false;">(보기)</a>
                </span>
            </label>

            <button type="submit" class="pay-button" id="payButton">
                <i class="fa fa-lock"></i> <?php echo number_format($url_pay['up_amount']); ?>원 결제하기
            </button>

            <div class="secure-badge">
                <i class="fa fa-shield"></i> SSL 암호화로 안전하게 결제됩니다
            </div>
        </form>
    </div>
</div>

<!-- 약관 모달 -->
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
        <div class="terms-modal-body" id="termsModalBody"></div>
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
    <li>회사는 다음의 서비스를 제공합니다.
        <ul>
            <li>신용카드 결제 서비스</li>
            <li>수기결제(비인증/인증) 서비스</li>
            <li>결제 취소 및 환불 처리 서비스</li>
            <li>기타 회사가 정하는 전자결제 관련 서비스</li>
        </ul>
    </li>
    <li>회사는 서비스의 내용을 변경하는 경우, 변경 내용과 적용일자를 명시하여 사전에 공지합니다.</li>
</ol>

<h2>제5조 (서비스 이용)</h2>
<ol>
    <li>서비스 이용은 회사의 서비스 사용 승낙 후 가능합니다.</li>
    <li>서비스 이용시간은 회사의 업무상 또는 기술상 불가능한 경우를 제외하고 연중무휴 1일 24시간으로 합니다.</li>
    <li>회사는 시스템 점검, 긴급 복구 등의 사유로 서비스를 일시 중단할 수 있으며, 이 경우 사전에 공지합니다.</li>
</ol>

<h2>제6조 (이용자의 의무)</h2>
<ol>
    <li>이용자는 다음 각 호의 행위를 하여서는 안 됩니다.
        <ul>
            <li>타인의 카드정보를 도용하거나 허위 정보를 입력하는 행위</li>
            <li>회사의 서비스 운영을 방해하는 행위</li>
            <li>관련 법령에 위반되는 부정한 결제 행위</li>
            <li>기타 회사가 정한 이용 규칙을 위반하는 행위</li>
        </ul>
    </li>
    <li>이용자는 본인의 결제 정보가 도용되었거나 부정 사용되었음을 인지한 경우, 즉시 회사 또는 해당 카드사에 통보하여야 합니다.</li>
    <li>이용자는 관련 법령, 본 약관, 이용안내 및 서비스와 관련하여 회사가 공지한 사항을 준수하여야 합니다.</li>
</ol>

<h2>제7조 (결제 처리 및 승인)</h2>
<ol>
    <li>결제 요청 시 이용자가 입력한 카드정보를 바탕으로 카드사에 승인을 요청합니다.</li>
    <li>카드사의 승인이 완료되면 결제가 정상 처리된 것으로 간주합니다.</li>
    <li>결제 승인이 거부된 경우, 회사는 이용자에게 그 사유를 안내합니다.</li>
</ol>

<h2>제8조 (결제 취소 및 환불)</h2>
<ol>
    <li>결제 취소는 가맹점 또는 회사를 통해 요청할 수 있습니다.</li>
    <li>취소 및 환불 처리는 카드사 정책에 따라 처리되며, 처리 기간은 카드사별로 상이할 수 있습니다.</li>
    <li>부분 취소가 가능한 경우, 잔여 금액에 대해서만 취소가 진행됩니다.</li>
</ol>

<h2>제9조 (개인정보보호)</h2>
<p>회사는 이용자의 개인정보를 「개인정보보호법」 및 관련 법령에 따라 보호하며, 개인정보의 수집, 이용, 보관, 파기에 관한 사항은 별도의 개인정보처리방침에 따릅니다.</p>

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
        <li>결제 내역 조회 서비스 제공</li>
        <li>거래 확인 및 정산 처리</li>
        <li>결제 관련 고객 문의 응대</li>
    </ul>
</div>

<div class="term-section">
    <h3>2. 서비스 개선 및 품질 향상</h3>
    <ul>
        <li>서비스 이용 현황 분석</li>
        <li>시스템 안정성 및 보안 강화</li>
        <li>부정 이용 방지 및 모니터링</li>
    </ul>
</div>

<div class="term-section">
    <h3>3. 법적 의무 이행</h3>
    <ul>
        <li>전자금융거래법에 따른 거래기록 보관</li>
        <li>세법에 따른 세무 신고</li>
        <li>관계 법령에 따른 자료 제출</li>
    </ul>
</div>

<h2>제2조 (수집하는 개인정보의 항목)</h2>
<p>회사는 결제 서비스 제공을 위해 다음의 개인정보를 수집합니다.</p>

<table>
    <tr>
        <th>구분</th>
        <th>수집 항목</th>
        <th>수집 목적</th>
    </tr>
    <tr>
        <td>필수</td>
        <td>카드번호, 유효기간, 결제금액</td>
        <td>결제 승인 처리</td>
    </tr>
    <tr>
        <td>필수</td>
        <td>구매자명, 연락처</td>
        <td>결제 확인 및 CS 응대</td>
    </tr>
    <tr>
        <td>선택</td>
        <td>카드 비밀번호 앞2자리, 생년월일</td>
        <td>본인인증 결제 시 인증</td>
    </tr>
</table>

<h2>제3조 (개인정보의 보유 및 이용 기간)</h2>
<p>회사는 개인정보 수집 및 이용 목적이 달성된 후에는 해당 정보를 지체 없이 파기합니다. 단, 다음의 정보에 대해서는 관련 법령에 따라 명시된 기간 동안 보관합니다.</p>

<table>
    <tr>
        <th>보관 정보</th>
        <th>보관 기간</th>
        <th>근거 법령</th>
    </tr>
    <tr>
        <td>전자금융거래 기록</td>
        <td>5년</td>
        <td>전자금융거래법</td>
    </tr>
    <tr>
        <td>계약 또는 청약철회 기록</td>
        <td>5년</td>
        <td>전자상거래법</td>
    </tr>
    <tr>
        <td>대금결제 및 재화공급 기록</td>
        <td>5년</td>
        <td>전자상거래법</td>
    </tr>
    <tr>
        <td>소비자 불만 또는 분쟁처리 기록</td>
        <td>3년</td>
        <td>전자상거래법</td>
    </tr>
</table>

<h2>제4조 (개인정보의 제3자 제공)</h2>
<p>회사는 원칙적으로 이용자의 개인정보를 제3자에게 제공하지 않습니다. 다만, 다음의 경우에는 예외로 합니다.</p>
<ol>
    <li>이용자가 사전에 동의한 경우</li>
    <li>결제 처리를 위해 카드사, 금융결제원 등에 필요한 정보를 전달하는 경우</li>
    <li>법령의 규정에 의거하거나, 수사 목적으로 법령에 정해진 절차와 방법에 따라 수사기관의 요구가 있는 경우</li>
</ol>

<h2>제5조 (개인정보 처리의 위탁)</h2>
<p>회사는 결제 서비스 제공을 위해 다음과 같이 개인정보 처리를 위탁하고 있습니다.</p>

<table>
    <tr>
        <th>수탁업체</th>
        <th>위탁 업무</th>
    </tr>
    <tr>
        <td>PG사 (결제대행사)</td>
        <td>결제 승인 및 취소 처리</td>
    </tr>
    <tr>
        <td>카드사</td>
        <td>카드 결제 승인</td>
    </tr>
</table>

<h2>제6조 (이용자의 권리와 행사 방법)</h2>
<p>이용자는 언제든지 다음의 개인정보 보호 관련 권리를 행사할 수 있습니다.</p>
<ol>
    <li>개인정보 열람 요구</li>
    <li>오류 등이 있을 경우 정정 요구</li>
    <li>삭제 요구</li>
    <li>처리정지 요구</li>
</ol>
<p>권리 행사는 회사에 서면, 전화, 이메일 등으로 연락하시면 지체 없이 조치하겠습니다.</p>

<h2>제7조 (개인정보의 안전성 확보 조치)</h2>
<p>회사는 개인정보의 안전성 확보를 위해 다음과 같은 조치를 취하고 있습니다.</p>
<ol>
    <li><strong>관리적 조치:</strong> 내부관리계획 수립, 정기적 직원 교육</li>
    <li><strong>기술적 조치:</strong> 개인정보 암호화, 접근권한 관리, 보안프로그램 설치</li>
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
    - 이메일: privacy@wspay.net
</div>

<h2>제9조 (권익침해 구제방법)</h2>
<p>이용자는 개인정보침해로 인한 구제를 받기 위하여 개인정보분쟁조정위원회, 한국인터넷진흥원 개인정보침해신고센터 등에 분쟁해결이나 상담 등을 신청할 수 있습니다.</p>

<h2>제10조 (고지의 의무)</h2>
<p>현 개인정보처리방침의 내용 추가, 삭제 및 수정이 있을 경우에는 시행일 7일 전부터 회사 홈페이지 또는 서비스 화면을 통해 공지합니다.</p>

<div class="term-highlight">
    <strong>시행일자</strong><br>
    본 개인정보처리방침은 2025년 12월 10일부터 시행됩니다.<br>
    최종 개정일: 2025년 12월 10일
</div>
</div>

<script>
// PG 설정 정보
var pgConfig = {
    pg_code: '<?php echo $pg_code; ?>',
    is_auth: <?php echo $is_auth ? 'true' : 'false'; ?>,
    phone_required: <?php echo in_array($pg_code, ['rootup', 'stn', 'winglobal']) ? 'true' : 'false'; ?>,
    email_required: <?php echo ($pg_code == 'winglobal') ? 'true' : 'false'; ?>
};

// 카드번호 포맷팅 및 비주얼 업데이트
function formatCardNumber(input) {
    var value = input.value.replace(/[^0-9]/g, '');
    var formatted = '';
    for(var i = 0; i < value.length && i < 16; i++) {
        if(i > 0 && i % 4 === 0) formatted += ' ';
        formatted += value[i];
    }
    input.value = formatted;
}

// 카드 입력 핸들러
function handleCardInput(input) {
    formatCardNumber(input);
    detectCardType(input.value);
}

// 카드 타입 감지
function detectCardType(cardNumber) {
    var num = cardNumber.replace(/\s/g, '');
    var cardType = '';
    var iconHtml = '<i class="fa fa-credit-card"></i>';

    if(/^4/.test(num)) {
        cardType = 'visa';
        iconHtml = '<span style="font-weight:700;font-style:italic;color:#fff;text-shadow:0 0 10px rgba(255,255,255,0.5);">VISA</span>';
    } else if(/^5[1-5]/.test(num) || /^2[2-7]/.test(num)) {
        cardType = 'mastercard';
        iconHtml = '<span style="font-weight:700;color:#ff5f00;">Master</span><span style="color:#f79e1b;">Card</span>';
    } else if(/^3[47]/.test(num)) {
        cardType = 'amex';
        iconHtml = '<span style="font-weight:700;color:#6cc4ee;">AMEX</span>';
    } else if(/^9[0-9]/.test(num)) {
        cardType = 'domestic';
        iconHtml = '<span style="font-weight:600;color:#4dd0e1;">국내카드</span>';
    } else if(/^3[068]/.test(num)) {
        cardType = 'diners';
        iconHtml = '<span style="font-weight:700;color:#64b5f6;">Diners</span>';
    } else if(/^6/.test(num)) {
        cardType = 'union';
        iconHtml = '<span style="font-weight:700;color:#ef5350;">UnionPay</span>';
    }

    var $brand = $('#cardBrand');

    if(cardType) {
        $brand.html(iconHtml);
    } else {
        $brand.html('<i class="fa fa-credit-card"></i>');
    }
}

// 카드 입력 필드 포커스 효과
$(document).ready(function() {
    $('.card-number-input, .card-field-input').on('focus', function() {
        $(this).closest('.card-visual').addClass('card-focused');
    }).on('blur', function() {
        $(this).closest('.card-visual').removeClass('card-focused');
    });
});

// 에러 표시 함수
function showFieldError(fieldSelector, message) {
    var $field = $(fieldSelector);
    $field.addClass('error');

    // 에러 메시지 요소가 없으면 생성
    var $errorMsg = $field.siblings('.field-error-msg');
    if($errorMsg.length === 0) {
        $errorMsg = $('<div class="field-error-msg"></div>');
        $field.after($errorMsg);
    }
    $errorMsg.text(message).addClass('show');
    $field.focus();
}

// 에러 초기화 함수
function clearFieldError(fieldSelector) {
    var $field = $(fieldSelector);
    $field.removeClass('error');
    $field.siblings('.field-error-msg').removeClass('show');
}

// 모든 에러 초기화
function clearAllErrors() {
    $('.form-input, .card-number-input, .card-field-input').removeClass('error');
    $('.field-error-msg').removeClass('show');
    $('.checkbox-wrapper').removeClass('error');
}

// 전체 동의
$('#agree_all').on('change', function() {
    var checked = $(this).prop('checked');
    $('#agree_term, #agree_privacy').prop('checked', checked);
});

$('#agree_term, #agree_privacy').on('change', function() {
    var allChecked = $('#agree_term').prop('checked') && $('#agree_privacy').prop('checked');
    $('#agree_all').prop('checked', allChecked);
    // 에러 상태 해제
    $(this).closest('.checkbox-wrapper').removeClass('error');
});

// 입력 필드 변경 시 에러 상태 해제
$('.form-input, .card-number-input, .card-field-input').on('input change', function() {
    $(this).removeClass('error');
    $(this).siblings('.field-error-msg').removeClass('show');
});

// 폼 제출
$('#paymentForm').on('submit', function(e) {
    e.preventDefault();
    clearAllErrors();

    var cardNo = $('#card_no').val().replace(/[^0-9]/g, '');
    var expireMM = $('select[name="expire_mm"]').val();
    var expireYY = $('select[name="expire_yy"]').val();
    var installment = $('select[name="installment"]').val();
    var buyerName = $('input[name="buyer_name"]').val();
    var buyerPhone = $('input[name="buyer_phone"]').val();

    // 카드번호 검증
    if(!cardNo) {
        showFieldError('#card_no', '카드번호를 입력하세요');
        return false;
    }
    if(cardNo.length < 15 || cardNo.length > 16) {
        showFieldError('#card_no', '카드번호 15~16자리를 정확히 입력하세요');
        return false;
    }

    // 유효기간 검증
    if(!expireMM) {
        showFieldError('select[name="expire_mm"]', '유효기간 월을 선택하세요');
        return false;
    }
    if(!expireYY) {
        showFieldError('select[name="expire_yy"]', '유효기간 년도를 선택하세요');
        return false;
    }

    // 할부 검증
    if(!installment && installment !== '00') {
        showFieldError('select[name="installment"]', '할부개월을 선택하세요');
        return false;
    }

    <?php if($is_auth) { ?>
    // 구인증: 본인인증 정보 체크
    var certPw = $('input[name="cert_pw"]').val();
    var certNo = $('input[name="cert_no"]').val();

    if(!certPw) {
        showFieldError('input[name="cert_pw"]', '카드 비밀번호 앞 2자리를 입력하세요');
        return false;
    }
    if(certPw.length !== 2) {
        showFieldError('input[name="cert_pw"]', '비밀번호 2자리를 입력하세요');
        return false;
    }
    if(!certNo) {
        showFieldError('input[name="cert_no"]', '생년월일 또는 사업자번호를 입력하세요');
        return false;
    }
    if(certNo.length !== 6 && certNo.length !== 10) {
        showFieldError('input[name="cert_no"]', '생년월일 6자리 또는 사업자번호 10자리를 입력하세요');
        return false;
    }
    <?php } ?>

    // 결제자 정보 검증
    if(!buyerName) {
        showFieldError('input[name="buyer_name"]', '결제자명을 입력하세요');
        return false;
    }

    // PG사별 휴대전화 필수 여부 체크
    // - 루트업(rootup), 섹타나인(stn), 윈글로벌(winglobal): 휴대전화 필수
    // - 페이시스(paysis) 등: 휴대전화 선택
    if(pgConfig.phone_required) {
        // 휴대전화 필수 PG
        if(!buyerPhone) {
            showFieldError('#buyer_phone', '연락처를 입력하세요 (필수)');
            return false;
        }
        var phoneRegex = /^(01[0-9]{1}-?[0-9]{3,4}-?[0-9]{4}|01[0-9]{8,11})$/;
        if(!phoneRegex.test(buyerPhone.replace(/-/g, ''))) {
            showFieldError('#buyer_phone', '연락처를 정확히 입력하세요');
            return false;
        }
    } else {
        // 휴대전화 선택 PG - 입력된 경우에만 형식 검증
        if(buyerPhone) {
            var phoneRegex = /^(01[0-9]{1}-?[0-9]{3,4}-?[0-9]{4}|01[0-9]{8,11})$/;
            if(!phoneRegex.test(buyerPhone.replace(/-/g, ''))) {
                showFieldError('#buyer_phone', '연락처를 정확히 입력하세요');
                return false;
            }
        }
    }

    // 윈글로벌: 이메일 필수
    if(pgConfig.email_required) {
        var buyerEmail = $('#buyer_email').val();
        if(!buyerEmail) {
            showFieldError('#buyer_email', '이메일 주소를 입력하세요');
            return false;
        }
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if(!emailRegex.test(buyerEmail)) {
            showFieldError('#buyer_email', '올바른 이메일 형식을 입력하세요');
            return false;
        }
    }

    // 약관 동의 검증
    if(!$('#agree_term').prop('checked')) {
        $('#agree_term').closest('.checkbox-wrapper').addClass('error');
        $('#agree_term').focus();
        return false;
    }
    if(!$('#agree_privacy').prop('checked')) {
        $('#agree_privacy').closest('.checkbox-wrapper').addClass('error');
        $('#agree_privacy').focus();
        return false;
    }

    // 결제 확인 모달 표시
    showPaymentConfirm();
    return false;
});

// 결제 확인 모달 표시
function showPaymentConfirm() {
    var cardNo = $('#card_no').val().replace(/[^0-9]/g, '');
    var cardNoMasked = cardNo.length >= 10 ? cardNo.substr(0, 6) + '****' + cardNo.substr(-4) : '****';
    var installment = $('select[name="installment"]').val();
    var installmentText = (installment == '00' || !installment) ? '일시불' : installment + '개월';

    $('#confirmGoodsName').text('<?php echo addslashes(htmlspecialchars($url_pay['up_goods_name'])); ?>');
    $('#confirmBuyerName').text($('input[name="buyer_name"]').val());
    $('#confirmCardNo').text(cardNoMasked);
    $('#confirmInstallment').text(installmentText);
    $('#confirmAmount').text('<?php echo number_format($url_pay['up_amount']); ?>원');

    $('#paymentConfirmOverlay').addClass('show');
    $('body').css('overflow', 'hidden');
}

// 결제 확인 모달 닫기
function closePaymentConfirm() {
    $('#paymentConfirmOverlay').removeClass('show');
    $('body').css('overflow', '');
}

// 실제 결제 실행
function executePayment() {
    closePaymentConfirm();

    // 로딩 표시
    $('#loadingOverlay').addClass('show');
    $('#payButton').prop('disabled', true).text('결제 처리 중...');

    $.ajax({
        url: '/url_payment_api.php',
        type: 'POST',
        dataType: 'json',
        data: $('#paymentForm').serialize(),
        success: function(res) {
            $('#loadingOverlay').removeClass('show');

            if(res.success) {
                // 영수증 모달에 데이터 설정
                var cardNo = $('#card_no').val().replace(/[^0-9]/g, '');
                var cardNoMasked = cardNo.length >= 10 ? cardNo.substr(0, 6) + '****' + cardNo.substr(-4) : '****';
                var installment = $('select[name="installment"]').val();
                var installmentText = (installment == '00' || !installment) ? '일시불' : installment + '개월';
                var now = new Date();
                var dateStr = now.getFullYear() + '-' +
                    String(now.getMonth() + 1).padStart(2, '0') + '-' +
                    String(now.getDate()).padStart(2, '0') + ' ' +
                    String(now.getHours()).padStart(2, '0') + ':' +
                    String(now.getMinutes()).padStart(2, '0');

                $('#receiptAmount').text('<?php echo number_format($url_pay['up_amount']); ?>');
                $('#receiptAppNo').text(res.data.app_no || '-');
                $('#receiptGoodsName').text('<?php echo addslashes(htmlspecialchars($url_pay['up_goods_name'])); ?>');
                $('#receiptCardNo').text(cardNoMasked);
                $('#receiptInstallment').text(installmentText);
                $('#receiptDateTime').text(dateStr);
                $('#receiptBuyerName').text($('input[name="buyer_name"]').val());

                // 영수증 모달 표시
                $('#receiptOverlay').addClass('show');
                $('body').css('overflow', 'hidden');
            } else {
                // 에러 모달 표시
                showErrorModal(res.message || '결제에 실패했습니다.');
                $('#payButton').prop('disabled', false).html('<i class="fa fa-lock"></i> <?php echo number_format($url_pay['up_amount']); ?>원 결제하기');
            }
        },
        error: function(xhr, status, error) {
            $('#loadingOverlay').removeClass('show');
            showErrorModal('서버 오류가 발생했습니다.\n잠시 후 다시 시도해주세요.');
            $('#payButton').prop('disabled', false).html('<i class="fa fa-lock"></i> <?php echo number_format($url_pay['up_amount']); ?>원 결제하기');
        }
    });
}

// 에러 모달 표시
function showErrorModal(message) {
    $('#errorModalMessage').html(message.replace(/\n/g, '<br>'));
    $('#errorModalOverlay').addClass('show');
    $('body').css('overflow', 'hidden');
}

// 에러 모달 닫기 - 다시 시도
function closeErrorModal() {
    $('#errorModalOverlay').removeClass('show');
    $('body').css('overflow', '');
}

// 에러 모달 닫기 - 닫기
function closeErrorModalAndClose() {
    $('#errorModalOverlay').removeClass('show');
    $('body').css('overflow', '');

    // 창 닫기 시도
    window.close();

    setTimeout(function() {
        if (navigator.userAgent.indexOf('KAKAOTALK') > -1) {
            window.location.href = 'kakaotalk://inappbrowser/close';
            return;
        }
        if (navigator.userAgent.indexOf('NAVER') > -1) {
            window.location.href = 'naversearchapp://inappbrowser/close';
            return;
        }
        if (window.history.length > 1) {
            window.history.back();
        }
    }, 300);
}

// 영수증 모달 닫기 - 결제 완료 페이지로 이동
function closeReceiptAndRedirect() {
    // 창 닫기 시도
    window.close();

    setTimeout(function() {
        // 카카오톡 인앱 브라우저
        if (navigator.userAgent.indexOf('KAKAOTALK') > -1) {
            window.location.href = 'kakaotalk://inappbrowser/close';
            return;
        }
        // 네이버 앱 브라우저
        if (navigator.userAgent.indexOf('NAVER') > -1) {
            window.location.href = 'naversearchapp://inappbrowser/close';
            return;
        }
        // 창이 안닫히면 결제완료 페이지로 이동
        location.href = '/pay/<?php echo $url_pay['up_code']; ?>';
    }, 300);
}

// 약관 모달 함수
function openTermsModal(type, e) {
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
});
</script>

</body>
</html>
