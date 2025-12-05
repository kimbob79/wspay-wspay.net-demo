<?php
	include_once("./_head.sub.php");
?>

<script type="text/javascript">
	function go_login(f) {
		if (f.mb_id.value == '') {
			alert('아이디를 입력하세요.');
			f.mb_id.focus();
			return false;
		}
		if (f.mb_password.value == '') {
			alert('비밀번호를 입력하세요.');
			f.mb_password.focus();
			return false;
		}
		return true;
	}
	$(function () {
		if ($('#mb_id').val() != '') {
			$('#mb_password').focus();
		} else {
			$('#mb_id').focus();
		}
	});
</script>

<div id="wrap" class="wraplogin">
	<section class="container" id="login">
		<form method="post" name="adminLogin" id="adminLogin" class="admin-login" action="./login_check.php" onsubmit="return go_login(this);">
			<div class="login-title">
				<h2>
					<a href="./"><span>WANNA</span> PAY</a>
				</h2>
				<p>편리한 결제 서비스</p>
			</div>
			<?php /*
			<ul class="tab-menu">
				<li class="active">
					<a href="/a/signIn">페이앱 판매자</a>
				</li>
				<li>
					<a href="/a/subidSignIn">페이앱 부계정</a>
				</li>
			</ul>
			*/ ?>
			<div class="login-group menu">
				<div class="login-input">
					<label for="mb_id" class="sr-only">아이디</label>
					<input type="text" class="form-control" id="mb_id" name="mb_id" placeholder="아이디" value="">
				</div>
				<div class="login-input">
					<label for="mb_password" class="sr-only">비밀번호</label>
					<input type="password" class="form-control" id="mb_password" name="mb_password" placeholder="비밀번호" value="">
				</div>
				<?php /*
				<div class="text-left check-group">
					<label for="chksave" class="input-chk">
						<input type="checkbox" name="chksave" id="chksave" value="1" ><span>아이디 저장</span>
					</label>
				</div>
				*/ ?>
				<button class="btn btn-block btn-blue" name="Submit" value="Login" type="submit">로그인</button>
				<?php /*
				<div class="btn-group">
					<button class="btn-md btn-black-line" type="button" id="app-android"><i class="ico ico-l ico-androad"></i>Google Play</button>
					<button class="btn-md btn-black-line" type="button" id="app-ios"><i class="ico ico-l ico-ios"></i>App Store</button>
				</div>
				<div class="link-group">
					<div class="pull-left">
						<a href="/a/findId">아이디찾기</a><span>|</span>
						<a href="/a/findPwd">비밀번호찾기</a>
					</div>
					<div class="pull-right">
						<a href="/a/seller_regist">회원가입</a>
					</div>
				</div>
				*/ ?>
			</div>
		</form>
	</section>
</div>
<!-- //wrap -->

</body>
</html>
