<?php
	include_once('./_common.php');
	$row = sql_fetch(" select * from g5_payment where pay_id = ".$pay_id);
?>
<!doctype html>
<html lang="ko">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=0,maximum-scale=10,user-scalable=yes">
	<meta name="HandheldFriendly" content="true">
	<meta name="format-detection" content="telephone=no">
	<title>결제정보 복사</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<script src="/_engin/js/jquery-1.12.4.min.js?ver=2106185"></script>
	<style>
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}

		body {
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
			background: #f5f5f5;
			padding: 12px;
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.copy-container {
			background: #fff;
			border-radius: 10px;
			box-shadow: 0 4px 20px rgba(0,0,0,0.1);
			max-width: 500px;
			width: 100%;
			overflow: hidden;
		}

		.copy-header {
			background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
			color: #fff;
			padding: 14px;
			text-align: center;
		}

		.copy-header i {
			font-size: 24px;
			margin-bottom: 4px;
			display: block;
		}

		.copy-header h2 {
			font-size: 15px;
			font-weight: 600;
			margin: 0;
		}

		.copy-content {
			padding: 16px;
		}

		.info-item {
			display: flex;
			padding: 8px 0;
			border-bottom: 1px solid #f0f0f0;
		}

		.info-item:last-child {
			border-bottom: none;
		}

		.info-label {
			font-weight: 600;
			color: #666;
			min-width: 70px;
			flex-shrink: 0;
			font-size: 13px;
		}

		.info-value {
			color: #333;
			flex: 1;
			word-break: break-all;
			font-size: 13px;
		}

		.info-value.highlight {
			color: #3b82f6;
			font-weight: 600;
			font-size: 15px;
		}

		textarea#copy_content {
			position: absolute;
			left: -9999px;
			opacity: 0;
		}

		.copy-success {
			background: #10b981;
			color: #fff;
			text-align: center;
			padding: 10px;
			font-weight: 500;
			font-size: 13px;
			display: none;
			animation: slideDown 0.3s ease;
		}

		.copy-success.show {
			display: block;
		}

		@keyframes slideDown {
			from {
				transform: translateY(-100%);
				opacity: 0;
			}
			to {
				transform: translateY(0);
				opacity: 1;
			}
		}

		.button-container {
			padding: 0 16px 16px 16px;
		}

		.copy-button {
			width: 100%;
			background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
			color: #fff;
			border: none;
			border-radius: 6px;
			padding: 11px;
			font-size: 14px;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.3s ease;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 6px;
		}

		.copy-button:hover {
			transform: translateY(-2px);
			box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
		}

		.copy-button:active {
			transform: translateY(0);
		}

		.copy-button i {
			font-size: 16px;
		}

		@media (max-width: 768px) {
			body {
				padding: 10px;
			}

			.copy-content {
				padding: 14px;
			}

			.info-item {
				flex-direction: column;
				gap: 3px;
			}

			.info-label {
				min-width: auto;
			}
		}
	</style>
</head>
<body>

<div class="copy-container">
	<div class="copy-header">
		<i class="fa fa-copy"></i>
		<h2>결제정보 복사</h2>
	</div>

	<div class="copy-success" id="copy_ok">
		<i class="fa fa-check-circle"></i> 복사가 완료되었습니다
	</div>

	<div class="copy-content">
		<div class="info-item">
			<div class="info-label">가맹점명</div>
			<div class="info-value"><?php echo htmlspecialchars($row['mb_6_name']); ?></div>
		</div>
		<div class="info-item">
			<div class="info-label">결제코드</div>
			<div class="info-value"><?php echo htmlspecialchars($row['dv_tid']); ?></div>
		</div>
		<div class="info-item">
			<div class="info-label">결제금액</div>
			<div class="info-value highlight"><?php echo number_format($row['pay']); ?>원</div>
		</div>
		<div class="info-item">
			<div class="info-label">결제일시</div>
			<div class="info-value"><?php echo htmlspecialchars($row['pay_datetime']); ?></div>
		</div>
		<div class="info-item">
			<div class="info-label">승인번호</div>
			<div class="info-value"><?php echo htmlspecialchars($row['pay_num']); ?></div>
		</div>
	</div>

	<div class="button-container">
		<button id="copy_button" class="copy-button">
			<i class="fa fa-copy"></i>
			<span>복사하기 / 창닫기</span>
		</button>
	</div>

	<textarea id="copy_content" readonly>가맹점명 : <?php echo $row['mb_6_name']; ?>

결제코드 : <?php echo $row['dv_tid']; ?>

결제금액 : <?php echo number_format($row['pay']); ?>원
결제일시 : <?php echo $row['pay_datetime']; ?>

승인번호 : <?php echo $row['pay_num']; ?></textarea>
</div>

<script>
$("#copy_button").click(function() {
	var copyText = $("#copy_content").val();

	// Clipboard API 사용 (현대 브라우저)
	if (navigator.clipboard && window.isSecureContext) {
		navigator.clipboard.writeText(copyText).then(function() {
			$("#copy_ok").addClass('show');
			setTimeout(function() {
				window.close();
			}, 1000);
		}).catch(function() {
			fallbackCopy(copyText);
		});
	} else {
		fallbackCopy(copyText);
	}
});

function fallbackCopy(text) {
	// fallback: 임시 textarea 생성
	var tempTextarea = document.createElement('textarea');
	tempTextarea.value = text;
	tempTextarea.style.position = 'fixed';
	tempTextarea.style.left = '0';
	tempTextarea.style.top = '0';
	tempTextarea.style.opacity = '0';
	document.body.appendChild(tempTextarea);
	tempTextarea.focus();
	tempTextarea.select();

	try {
		document.execCommand('copy');
		$("#copy_ok").addClass('show');
		setTimeout(function() {
			window.close();
		}, 1000);
	} catch (err) {
		alert('복사에 실패했습니다. 직접 선택하여 복사해주세요.');
	}

	document.body.removeChild(tempTextarea);
}
</script>

</body>
</html>