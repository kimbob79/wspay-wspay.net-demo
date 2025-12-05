
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>원성페이먼츠 로그인</title>
<link rel="image_src" href="./img/og_tag.png" />
<meta name="description" content="PASSGO" />
<meta property="og:type" content="website" />
<meta property="og:title" content="PASSGO" />
<meta property="og:description" content="PASSGO 정산 솔루션" />
<meta property="og:image" content="./img/og_tag.png" />
<link rel="stylesheet" href="css/renewal.css">
<link rel="stylesheet" href="css/vegas.min.css"  type="text/css">
<script src="https://code.jquery.com/jquery-3.4.1.js"></script>
<script src="js/vegas.min.js"></script>
<script src="vendor/bootstrap/js/popper.js"></script>
<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="vendor/select2/select2.min.js"></script>
<script src="vendor/tilt/tilt.jquery.min.js"></script>
</head>
<body class="align">
	<div class="grid re_grid">
		<div class="re_login_wrap">
			<div class="login_box_re">
				<div class="logo_box">
					<h2>
						<img src="./img/logo_re_img.png" alt="원성페이먼츠">
						가맹점관리자 솔루션
					</h2>
				</div>
				<div class="login_content">
						<div class="top">
							<div class="id top_text">
								<input type="text" placeholder="아이디" required>
								<span class="line"></span>
							</div>
							<div class="pw top_text">
								<input type="password" id="pw_box" placeholder="패스워드" required>
								<span class="line"></span>
								<div class="icon pass_icon"><span></span></div>	
							</div>	
							<div class="login_btn"><input type="submit" value="로그인"></div>
							<div class="login_ck">
								<div class="auto">
									<input type="checkbox" name="autock" id="autock">
									<label for="autock">자동 로그인</label>
								</div>
								<div class="lost_text"><a class="point">ID찾기</a> 또는 <a class="point">비밀번호 찾기</a></div>
							</div>
						</div>
						<div class="bottom">
							<div class="info_text"><span>가맹문의 및 제휴문의(1555-0985)</span><p>/</p>운영시간 09:00 ~ 18:00 (주말, 공휴일 제외)</div>
						</div>
				</div>
			</div>
			<div class="re_login_bg"></div>
		</div>
	</div>

	<script type="text/javascript">
		$(function(){
			// 비밀번호 표시
			$('.pass_icon > span').on('click', function(){
				$('.pw').toggleClass('active');

				if($('.pw').hasClass('active') == true){
					$('#pw_box').attr('type', 'text');
					$('.pass_icon').addClass('on');
				} else {
					$('#pw_box').attr('type', 'password');
					$('.pass_icon').removeClass('on');				
				}
			});
		});
		//백그라운드
		$(function () {
			$("body").vegas({
				transition: "random",
				shuffle: true,
				overlay: true,
				timer: true,
				delay: 3000,
				slides: [
					{ src: "./img/bdbg_1.jpg" },
					{ src: "./img/bdbg_2.jpg" },
					{ src: "./img/bdbg_3.jpg" },
					{ src: "./img/bdbg_4.jpg" },
				]
			});
		});
	</script>
</body>
</html>