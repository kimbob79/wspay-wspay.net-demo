<?php
/**
 * URL 결제 완료 페이지
 */
error_reporting(E_ALL);
ini_set("display_errors", 0);

// Gnuboard 프레임워크 로드
if(!defined('_GNUBOARD_')) define('_GNUBOARD_', true);

// gnu_module 또는 _engin 디렉토리 탐색
$_g5_path = file_exists(__DIR__.'/gnu_module/common.php')
    ? __DIR__.'/gnu_module/common.php'
    : __DIR__.'/_engin/common.php';
include_once($_g5_path);
unset($_g5_path);

$code = isset($_GET['code']) ? trim($_GET['code']) : '';

// 결제 정보 조회
$url_pay = sql_fetch("SELECT u.*, pk.pk_app_no, pk.pk_app_date, pk.pk_card_no_masked
                      FROM g5_url_payment u
                      LEFT JOIN g5_payment_keyin pk ON u.pk_id = pk.pk_id
                      WHERE u.up_code = '".sql_real_escape_string($code)."'
                        AND u.up_status = 'used'");

if(!$url_pay) {
    header('Location: /');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>결제 완료 - 원성페이먼츠</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
        .complete-card {
            background: #fff;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            animation: scaleIn 0.5s ease;
        }
        @keyframes scaleIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        .success-icon i {
            font-size: 40px;
            color: #fff;
        }
        .complete-title {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
        }
        .complete-subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 30px;
        }
        .payment-details {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            text-align: left;
            margin-bottom: 25px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-size: 13px;
            color: #666;
        }
        .detail-value {
            font-size: 13px;
            color: #333;
            font-weight: 600;
        }
        .detail-value.amount {
            font-size: 18px;
            color: #4caf50;
        }
        .close-button {
            display: inline-block;
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #4caf50 0%, #43a047 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        .close-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.4);
        }
        .brand-footer {
            margin-top: 25px;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>

<div class="complete-card">
    <div class="success-icon">
        <i class="fa fa-check"></i>
    </div>

    <h1 class="complete-title">결제가 완료되었습니다</h1>
    <p class="complete-subtitle">결제해 주셔서 감사합니다.</p>

    <div class="payment-details">
        <div class="detail-row">
            <span class="detail-label">상품명</span>
            <span class="detail-value"><?php echo htmlspecialchars($url_pay['up_goods_name']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">결제금액</span>
            <span class="detail-value amount"><?php echo number_format($url_pay['up_amount']); ?>원</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">승인번호</span>
            <span class="detail-value"><?php echo $url_pay['pk_app_no']; ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">카드번호</span>
            <span class="detail-value"><?php echo $url_pay['pk_card_no_masked']; ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">결제일시</span>
            <span class="detail-value"><?php echo date('Y-m-d H:i', strtotime($url_pay['up_paid_datetime'])); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">판매자</span>
            <span class="detail-value"><?php echo htmlspecialchars($url_pay['up_seller_name']); ?></span>
        </div>
    </div>

    <button type="button" class="close-button" onclick="closeWindow()">
        <i class="fa fa-check"></i> 확인
    </button>

    <div class="brand-footer">
        원성페이먼츠
    </div>
</div>

<script>
function closeWindow() {
    // 1. 먼저 window.close() 시도
    window.close();

    // 2. 창이 안 닫히면 (500ms 후에도 여전히 열려있으면)
    setTimeout(function() {
        // 카카오톡 인앱 브라우저 닫기 시도
        if (window.location.href.indexOf('kakaotalk') > -1 || navigator.userAgent.indexOf('KAKAOTALK') > -1) {
            window.location.href = 'kakaotalk://inappbrowser/close';
            return;
        }

        // 네이버 앱 브라우저 닫기 시도
        if (navigator.userAgent.indexOf('NAVER') > -1) {
            window.location.href = 'naversearchapp://inappbrowser/close';
            return;
        }

        // 일반 브라우저: 빈 페이지로 이동하거나 히스토리 뒤로가기
        if (window.history.length > 1) {
            window.history.back();
        } else {
            // 빈 about:blank 페이지로 이동
            window.location.href = 'about:blank';
        }
    }, 300);
}

// 페이지 로드 시 자동으로 창 닫기 버튼에 포커스
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.close-button').focus();
});
</script>

</body>
</html>
