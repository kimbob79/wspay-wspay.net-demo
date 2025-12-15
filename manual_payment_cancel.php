<?php
/**
 * 수기결제 취소 팝업
 * - 원거래 정보 표시
 * - 취소자명, 취소사유 입력
 * - AJAX로 manual_payment_api.php 호출
 */

include_once('./_common.php');

// 수기결제 권한 체크
if(!$is_admin && $member['mb_mailling'] != '1') {
    echo "<script>alert('수기결제 권한이 없습니다.');window.close();</script>";
    exit;
}

$pk_id = isset($_GET['pk_id']) ? intval($_GET['pk_id']) : 0;

if(!$pk_id) {
    echo "<script>alert('거래 정보가 없습니다.');window.close();</script>";
    exit;
}

// 거래 정보 조회
$payment_sql = "SELECT p.*, k.mkc_cancel_yn
                FROM g5_payment_keyin p
                LEFT JOIN g5_member_keyin_config k ON p.mkc_id = k.mkc_id
                WHERE p.pk_id = '{$pk_id}'";
$payment = sql_fetch($payment_sql);

if(!$payment) {
    echo "<script>alert('거래 정보를 찾을 수 없습니다.');window.close();</script>";
    exit;
}

// 권한 체크
if(!$is_admin && $payment['mb_id'] !== $member['mb_id']) {
    echo "<script>alert('해당 거래에 대한 권한이 없습니다.');window.close();</script>";
    exit;
}

// 취소 가능 여부 체크 (가맹점인 경우)
if(!$is_admin && $payment['mkc_cancel_yn'] !== 'Y') {
    echo "<script>alert('해당 설정은 취소가 허용되지 않습니다.');window.close();</script>";
    exit;
}

// 상태 체크
if($payment['pk_status'] !== 'approved' && $payment['pk_status'] !== 'partial_cancelled') {
    echo "<script>alert('승인된 거래만 취소할 수 있습니다.\\n현재 상태: {$payment['pk_status']}');window.close();</script>";
    exit;
}

// 취소 가능 금액
$remaining_amount = $payment['pk_amount'] - $payment['pk_cancel_amount'];

// 승인일시 포맷
$app_date_formatted = '';
if($payment['pk_app_date']) {
    $app_date_formatted = date('Y-m-d H:i:s', strtotime($payment['pk_app_date']));
}

// 상태 텍스트
$status_text = [
    'pending' => '대기',
    'approved' => '승인',
    'failed' => '실패',
    'cancelled' => '취소',
    'partial_cancelled' => '부분취소'
];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>수기결제 취소</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Malgun Gothic', '맑은 고딕', sans-serif;
            font-size: 13px;
            line-height: 1.5;
            background: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
        }
        .cancel-header {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
            color: #fff;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
            margin-bottom: 0;
        }
        .cancel-header h2 {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }
        .cancel-header h2 i {
            margin-right: 8px;
        }
        .cancel-body {
            background: #fff;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-top: none;
            border-radius: 0 0 8px 8px;
        }
        .info-section {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px dashed #e0e0e0;
        }
        .info-section:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        .info-section h3 {
            font-size: 14px;
            font-weight: 600;
            color: #555;
            margin-bottom: 12px;
        }
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        .info-row:last-child {
            margin-bottom: 0;
        }
        .info-label {
            width: 100px;
            color: #888;
            flex-shrink: 0;
        }
        .info-value {
            flex: 1;
            color: #333;
            font-weight: 500;
        }
        .info-value.amount {
            color: #1976d2;
            font-size: 16px;
            font-weight: 700;
        }
        .info-value.cancel-amount {
            color: #dc2626;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        .status-approved { background: #e8f5e9; color: #2e7d32; }
        .status-partial_cancelled { background: #fff3e0; color: #ef6c00; }

        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #555;
        }
        .form-group label .required {
            color: #dc2626;
            margin-left: 2px;
        }
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
            transition: border-color 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: #dc2626;
        }
        .form-control[readonly] {
            background: #f8f9fa;
            color: #666;
        }
        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-cancel {
            background: #dc2626;
            color: #fff;
        }
        .btn-cancel:hover {
            background: #b91c1c;
        }
        .btn-cancel:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .btn-close {
            background: #6b7280;
            color: #fff;
        }
        .btn-close:hover {
            background: #4b5563;
        }

        .warning-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 4px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }
        .warning-box p {
            color: #991b1b;
            font-size: 12px;
            margin: 0;
        }
        .warning-box p i {
            margin-right: 5px;
        }

        /* Loading */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .loading-overlay.show {
            display: flex;
        }
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid #fff;
            border-top-color: #dc2626;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="cancel-header">
            <h2><i class="fa fa-times-circle"></i> 수기결제 취소</h2>
        </div>
        <div class="cancel-body">
            <!-- 원거래 정보 -->
            <div class="info-section">
                <h3><i class="fa fa-credit-card"></i> 원거래 정보</h3>
                <div class="info-row">
                    <span class="info-label">주문번호</span>
                    <span class="info-value"><?php echo htmlspecialchars($payment['pk_order_no']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">가맹점</span>
                    <span class="info-value"><?php echo htmlspecialchars($payment['pk_mb_6_name']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">상품명</span>
                    <span class="info-value"><?php echo htmlspecialchars($payment['pk_goods_name']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">결제금액</span>
                    <span class="info-value amount"><?php echo number_format($payment['pk_amount']); ?>원</span>
                </div>
                <?php if($payment['pk_cancel_amount'] > 0): ?>
                <div class="info-row">
                    <span class="info-label">기취소금액</span>
                    <span class="info-value cancel-amount"><?php echo number_format($payment['pk_cancel_amount']); ?>원</span>
                </div>
                <?php endif; ?>
                <div class="info-row">
                    <span class="info-label">취소가능금액</span>
                    <span class="info-value amount"><?php echo number_format($remaining_amount); ?>원</span>
                </div>
                <div class="info-row">
                    <span class="info-label">승인번호</span>
                    <span class="info-value"><?php echo htmlspecialchars($payment['pk_app_no']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">승인일시</span>
                    <span class="info-value"><?php echo $app_date_formatted; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">현재상태</span>
                    <span class="info-value">
                        <span class="status-badge status-<?php echo $payment['pk_status']; ?>">
                            <?php echo $status_text[$payment['pk_status']] ?? $payment['pk_status']; ?>
                        </span>
                    </span>
                </div>
            </div>

            <!-- 취소 정보 입력 -->
            <div class="info-section">
                <h3><i class="fa fa-edit"></i> 취소 정보</h3>

                <div class="warning-box">
                    <p><i class="fa fa-exclamation-triangle"></i> 취소 후에는 되돌릴 수 없습니다. 신중하게 진행해 주세요.</p>
                </div>

                <form id="cancelForm">
                    <input type="hidden" name="action" value="cancel">
                    <input type="hidden" name="pk_id" value="<?php echo $pk_id; ?>">

                    <div class="form-group">
                        <label>취소금액 <span class="required">*</span></label>
                        <input type="text" name="cancel_amount" id="cancel_amount"
                               class="form-control" value="<?php echo number_format($remaining_amount); ?>"
                               placeholder="취소할 금액을 입력하세요">
                        <small style="color:#888;font-size:11px;">* 전액 취소가 기본값입니다. 부분취소 시 금액을 수정하세요.</small>
                    </div>

                    <div class="form-group">
                        <label>취소자명 <span class="required">*</span></label>
                        <input type="text" name="cancel_name" id="cancel_name"
                               class="form-control" value="<?php echo htmlspecialchars($member['mb_nick']); ?>"
                               placeholder="취소자명을 입력하세요" maxlength="12">
                    </div>

                    <div class="form-group">
                        <label>취소사유 <span class="required">*</span></label>
                        <textarea name="cancel_reason" id="cancel_reason"
                                  class="form-control" placeholder="취소 사유를 입력하세요" maxlength="200"></textarea>
                    </div>
                </form>
            </div>

            <div class="btn-group">
                <button type="button" class="btn btn-close" onclick="window.close();">닫기</button>
                <button type="button" class="btn btn-cancel" id="btnCancel">
                    <i class="fa fa-times-circle"></i> 취소 요청
                </button>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <script>
    $(function() {
        // 금액 입력 시 콤마 포맷
        $('#cancel_amount').on('input', function() {
            var value = $(this).val().replace(/[^0-9]/g, '');
            if(value) {
                $(this).val(Number(value).toLocaleString('ko-KR'));
            }
        });

        // 취소 요청
        $('#btnCancel').on('click', function() {
            var cancel_amount = parseInt($('#cancel_amount').val().replace(/[^0-9]/g, ''));
            var cancel_name = $.trim($('#cancel_name').val());
            var cancel_reason = $.trim($('#cancel_reason').val());
            var remaining = <?php echo $remaining_amount; ?>;

            if(!cancel_amount || cancel_amount <= 0) {
                alert('취소금액을 입력하세요.');
                $('#cancel_amount').focus();
                return;
            }
            if(cancel_amount > remaining) {
                alert('취소 가능 금액을 초과했습니다.\n(취소 가능: ' + remaining.toLocaleString() + '원)');
                $('#cancel_amount').focus();
                return;
            }
            if(!cancel_name) {
                alert('취소자명을 입력하세요.');
                $('#cancel_name').focus();
                return;
            }
            if(!cancel_reason) {
                alert('취소사유를 입력하세요.');
                $('#cancel_reason').focus();
                return;
            }

            var msg = '정말 취소하시겠습니까?\n\n';
            msg += '취소금액: ' + cancel_amount.toLocaleString() + '원\n';
            msg += '취소자: ' + cancel_name + '\n';
            msg += '사유: ' + cancel_reason;

            if(!confirm(msg)) {
                return;
            }

            $('#loadingOverlay').addClass('show');
            $('#btnCancel').prop('disabled', true);

            $.ajax({
                url: './manual_payment_api.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'cancel',
                    pk_id: <?php echo $pk_id; ?>,
                    cancel_amount: cancel_amount,
                    cancel_name: cancel_name,
                    cancel_reason: cancel_reason
                },
                success: function(response) {
                    $('#loadingOverlay').removeClass('show');
                    if(response.success) {
                        alert('취소가 완료되었습니다.');
                        if(window.opener && !window.opener.closed) {
                            window.opener.location.reload();
                        }
                        window.close();
                    } else {
                        alert('취소 실패: ' + response.message);
                        $('#btnCancel').prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    $('#loadingOverlay').removeClass('show');
                    alert('서버 오류가 발생했습니다.\n' + error);
                    $('#btnCancel').prop('disabled', false);
                }
            });
        });
    });
    </script>
</body>
</html>
