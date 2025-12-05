<?php
	include_once('./_common.php');
	$payment_row = sql_fetch(" select * from g5_payment where pay_id = ".$pay_id);
?>
<!doctype html>
<html lang="ko">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=0,maximum-scale=10,user-scalable=yes">
	<meta name="HandheldFriendly" content="true">
	<meta name="format-detection" content="telephone=no">
	<title>결제 메모 관리</title>
	<link rel="stylesheet" href="./css/mobile.css?ver=<?php echo time(); ?>">
	<link rel="stylesheet" href="./css/btn.css?ver=<?php echo time(); ?>">
	<link rel="stylesheet" href="/_engin/js/font-awesome/css/font-awesome.min.css">
	<script src="/_engin/js/jquery-1.12.4.min.js?ver=2106185"></script>
	<style>
		:root {
			--primary-color: #3b82f6;
			--primary-dark: #2563eb;
			--primary-light: #60a5fa;
			--dark-bg: #1a1d29;
			--dark-bg-2: #2d3142;
			--success-color: #10b981;
			--danger-color: #ef4444;
			--text-dark: #1e293b;
			--text-gray: #64748b;
			--text-light: #94a3b8;
			--border-color: #e2e8f0;
			--bg-light: #f8fafc;
		}

		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}

		body {
			font-family: 'Pretendard', 'Malgun Gothic', sans-serif;
			background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
			min-height: 100vh;
			padding: 20px;
		}

		.memo-container {
			max-width: 900px;
			margin: 0 auto;
		}

		/* 헤더 */
		.memo-header {
			background: linear-gradient(135deg, var(--dark-bg) 0%, var(--dark-bg-2) 100%);
			color: white;
			padding: 24px;
			border-radius: 16px 16px 0 0;
			box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
		}

		.memo-header h1 {
			font-size: 20px;
			font-weight: 700;
			margin-bottom: 16px;
			display: flex;
			align-items: center;
			gap: 10px;
		}

		.memo-header h1 i {
			color: var(--primary-light);
		}

		/* 결제 정보 카드 */
		.payment-info {
			background: rgba(255, 255, 255, 0.1);
			border-radius: 12px;
			padding: 16px;
			backdrop-filter: blur(10px);
		}

		.info-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
			gap: 12px;
		}

		.info-item {
			display: flex;
			align-items: center;
			gap: 8px;
		}

		.info-label {
			font-size: 12px;
			color: var(--text-light);
			font-weight: 600;
		}

		.info-value {
			font-size: 14px;
			font-weight: 600;
			color: white;
		}

		.info-value.highlight {
			color: var(--primary-light);
			font-size: 16px;
		}

		/* 메모 입력 폼 */
		.memo-form-section {
			background: white;
			padding: 24px;
			border-left: 1px solid var(--border-color);
			border-right: 1px solid var(--border-color);
		}

		.form-label {
			display: block;
			font-size: 14px;
			font-weight: 600;
			color: var(--text-dark);
			margin-bottom: 8px;
		}

		.memo-textarea {
			width: 100%;
			height: 120px;
			padding: 12px;
			border: 2px solid var(--border-color);
			border-radius: 12px;
			font-size: 14px;
			font-family: inherit;
			resize: vertical;
			transition: all 0.2s ease;
		}

		.memo-textarea:focus {
			outline: none;
			border-color: var(--primary-color);
			box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
		}

		.memo-textarea::placeholder {
			color: var(--text-light);
		}

		.form-actions {
			margin-top: 16px;
			text-align: right;
		}

		.btn-submit {
			background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
			color: white;
			border: none;
			padding: 12px 32px;
			border-radius: 10px;
			font-size: 14px;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.2s ease;
			box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
		}

		.btn-submit:hover {
			transform: translateY(-2px);
			box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
		}

		.btn-submit:active {
			transform: translateY(0);
		}

		.btn-submit i {
			margin-right: 6px;
		}

		/* 메모 리스트 */
		.memo-list-section {
			background: white;
			padding: 24px;
			border-radius: 0 0 16px 16px;
			border: 1px solid var(--border-color);
			box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
		}

		.memo-list-title {
			font-size: 16px;
			font-weight: 700;
			color: var(--text-dark);
			margin-bottom: 16px;
			display: flex;
			align-items: center;
			gap: 8px;
		}

		.memo-list-title i {
			color: var(--primary-color);
		}

		.memo-table {
			width: 100%;
			border-collapse: collapse;
		}

		.memo-table thead {
			background: var(--bg-light);
		}

		.memo-table th {
			padding: 12px;
			text-align: left;
			font-size: 12px;
			font-weight: 600;
			color: var(--text-gray);
			text-transform: uppercase;
			letter-spacing: 0.5px;
			border-bottom: 2px solid var(--border-color);
		}

		.memo-table tbody tr {
			border-bottom: 1px solid var(--border-color);
			transition: background 0.2s ease;
		}

		.memo-table tbody tr:hover {
			background: var(--bg-light);
		}

		.memo-table td {
			padding: 16px 12px;
			font-size: 14px;
			color: var(--text-dark);
		}

		.memo-table td.author {
			font-weight: 600;
			color: var(--primary-color);
		}

		.memo-table td.content {
			line-height: 1.6;
		}

		.memo-table td.date {
			color: var(--text-gray);
			font-size: 12px;
			white-space: nowrap;
		}

		.empty-state {
			text-align: center;
			padding: 48px 20px;
			color: var(--text-light);
		}

		.empty-state i {
			font-size: 48px;
			margin-bottom: 16px;
			opacity: 0.3;
		}

		.empty-state p {
			font-size: 14px;
		}

		/* 반응형 */
		@media screen and (max-width: 768px) {
			body {
				padding: 12px;
			}

			.memo-header {
				padding: 20px;
			}

			.memo-header h1 {
				font-size: 18px;
			}

			.info-grid {
				grid-template-columns: 1fr;
				gap: 10px;
			}

			.memo-form-section,
			.memo-list-section {
				padding: 16px;
			}

			.memo-table {
				font-size: 12px;
			}

			.memo-table th,
			.memo-table td {
				padding: 10px 8px;
			}

			.btn-submit {
				width: 100%;
			}
		}

		@media screen and (max-width: 600px) {
			.memo-table thead {
				display: none;
			}

			.memo-table tbody tr {
				display: block;
				margin-bottom: 16px;
				border: 1px solid var(--border-color);
				border-radius: 8px;
				padding: 12px;
			}

			.memo-table td {
				display: block;
				padding: 8px 0;
				border: none;
			}

			.memo-table td::before {
				content: attr(data-label);
				display: block;
				font-size: 11px;
				font-weight: 600;
				color: var(--text-gray);
				text-transform: uppercase;
				margin-bottom: 4px;
			}
		}
	</style>
</head>
<body>

<div class="memo-container">
	<!-- 헤더 -->
	<div class="memo-header">
		<h1><i class="fa fa-sticky-note-o"></i> 결제 메모</h1>
		<div class="payment-info">
			<div class="info-grid">
				<div class="info-item">
					<div>
						<div class="info-label">가맹점명</div>
						<div class="info-value"><?php echo $payment_row['mb_6_name']; ?></div>
					</div>
				</div>
				<div class="info-item">
					<div>
						<div class="info-label">결제코드</div>
						<div class="info-value"><?php echo $payment_row['dv_tid']; ?></div>
					</div>
				</div>
				<div class="info-item">
					<div>
						<div class="info-label">결제금액</div>
						<div class="info-value highlight"><?php echo number_format($payment_row['pay']); ?>원</div>
					</div>
				</div>
				<div class="info-item">
					<div>
						<div class="info-label">결제일시</div>
						<div class="info-value"><?php echo $payment_row['pay_datetime']; ?></div>
					</div>
				</div>
				<div class="info-item">
					<div>
						<div class="info-label">승인번호</div>
						<div class="info-value"><?php echo $payment_row['pay_num']; ?></div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- 메모 입력 폼 -->
	<div class="memo-form-section">
		<form name="fmemoform" action="./memo_update.php" onsubmit="return fmemoform_submit(this);" method="post" autocomplete="off">
			<input type="hidden" name="mb_id" value="<?php echo $member['mb_id']; ?>">
			<input type="hidden" name="pay_id" value="<?php echo $pay_id; ?>">

			<label class="form-label"><i class="fa fa-pencil"></i> 새 메모 작성</label>
			<textarea name="memo" class="memo-textarea" placeholder="메모 내용을 입력하세요..." required></textarea>

			<div class="form-actions">
				<button type="submit" class="btn-submit">
					<i class="fa fa-save"></i> 메모 등록
				</button>
			</div>
		</form>
	</div>

	<!-- 메모 리스트 -->
	<div class="memo-list-section">
		<h2 class="memo-list-title"><i class="fa fa-list"></i> 메모 내역</h2>

		<?php
			$sql = " select * from g5_payment_memo where pay_id = '{$pay_id}' order by datetime desc ";
			$result = sql_query($sql);
			$memo_count = sql_num_rows($result);

			if($memo_count > 0) {
		?>
		<table class="memo-table">
			<thead>
				<tr>
					<th style="width: 120px;">작성자</th>
					<th>메모내용</th>
					<th style="width: 180px;">작성일시</th>
				</tr>
			</thead>
			<tbody>
				<?php
					for ($i=0; $row=sql_fetch_array($result); $i++) {
				?>
				<tr>
					<td class="author" data-label="작성자"><?php echo $row['mb_name']; ?></td>
					<td class="content" data-label="메모내용"><?php echo nl2br(htmlspecialchars($row['me_memo'])); ?></td>
					<td class="date" data-label="작성일시"><?php echo $row['datetime']; ?></td>
				</tr>
				<?php
					}
				?>
			</tbody>
		</table>
		<?php
			} else {
		?>
		<div class="empty-state">
			<i class="fa fa-inbox"></i>
			<p>등록된 메모가 없습니다.<br>첫 번째 메모를 작성해보세요.</p>
		</div>
		<?php
			}
		?>
	</div>
</div>

</body>
</html>