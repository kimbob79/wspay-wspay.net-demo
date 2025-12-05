<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>원성페이먼츠 판매자센터</title>


<link rel="image_src" href="./img/og_tag.png" />
<meta name="description" content="원성페이먼츠 판매자센터" />
<meta property="og:type"               content="website" />
<meta property="og:title"              content="원성페이먼츠 판매자센터" />
<meta property="og:description"        content="카드결제 정산 솔루션" />
<meta property="og:image"              content="./img/og_tag.png" />

<link rel="stylesheet" href="css/login.css?ver=<?php echo cssjs; ?>">
</head>


<body class="align">
	<div class="grid">
		<form action="./login_check.php" method="post" class="form login">
		<input type="hidden" name="url" value="<?php echo $p; ?>">
			<header class="login__header">
				<h3 class="login__title">WANNA</h3>
			</header>
			<div class="login__body">
				<div class="form__field">
					<input type="text" name="mb_id" placeholder="아이디" required>
				</div>
				<div class="form__field">
					<input type="password" name="mb_password" placeholder="비밀번호" required>
				</div>
			</div>
			<footer class="login__footer">
				<input type="submit" value="로그인">
				<p>
					<span class="icon icon--info">?</span><a href="./?p=logout">로그인 오류일때 클릭</a>
				</p>
			</footer>
		</form>
	</div>
</body>

</html>