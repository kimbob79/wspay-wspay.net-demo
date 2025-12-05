<?php
	include_once("./_common.php");

	$title1 = "대쉬보드";
	$title2 = "";

	include_once("./_head.php");
?>

	<style>
		.link_normal {
			display:inline-block;
			font-size:14px;
			line-height:26px;
			color:#2d50ff;
			letter-spacing:-0.2px;
			vertical-align:top;
			-webkit-transition:color 0.25s;
			transition:color 0.25s;
		}
		.group_viewcont .tit_viewcont.tit_type2 {
			padding-top:28px;
			font-size:30px;
			line-height:44px;
			font-family:'NotoSans DemiLight','Malgun Gothic','맑은 고딕','Apple SD Gothic Neo','돋움',dotum,sans-serif;
			letter-spacing:-0.9px;
			color:#111;
		}
		.group_viewcont .desc_viewcont.desc_type1 {
			font-size:14px;
			line-height:24px;
			letter-spacing:-0.2px;
		}
		.group_viewcont [class^='area'] {
			padding:0 50px 48px;
			padding-bottom:34px;
			background-color:#fff;
		}
		.group_viewcont.group_error {
			margin-left:64px;
			margin-right:64px;
			background-color:#f7f8f8;
		}
		.group_error .area_accountinfo {
			background-color:#f7f8f8;
		}
		.group_error .tit_viewcont.tit_type2 {
			padding-top:63px;
			padding-bottom:0;
			border-bottom:none;
		}
		.group_error .desc_viewcont.desc_type1 {
			padding-top:14px;
		}
		.group_error .set_btn {
			padding-bottom:0;
		}
		.set_btn {
			overflow:hidden;
			margin-top:50px;
		}
		.set_btn.set_type1 {
			margin-top:32px;
		}
		.btn_normalbig {
			display:block;
			width:100%;
			height:96px;
			padding:0 43px 0 35px;
			font-size:16px;
			line-height:96px;
			color:rgba(255,255,255,0.9);
			font-family:'NotoSans Medium','Malgun Gothic','맑은 고딕','Apple SD Gothic Neo','돋움',dotum,sans-serif;
			letter-spacing:-0.2px;
			background-color:#03166c;
			box-sizing:border-box;
			-webkit-transition:background-color 0.25s;
			transition:background-color 0.25s;
		}
		.btn_normalbig .ico_developers {
			display:inline-block !important;
			margin:-3px 8px 0 0;
			vertical-align:middle;
		}
		.btn_normalbig .txt_error {
			float:right;
			position:relative;
			z-index:1;
			margin-top:28px;
			font-size:30px;
			line-height:43px;
			color:#fff;
			font-family:'Compton Book','Malgun Gothic','맑은 고딕','Apple SD Gothic Neo','돋움',dotum,sans-serif;
			letter-spacing:-0.75px;
		}
		.btn_normalbig .txt_error:after {
			position:absolute;
			top:-14px;
			right:-25px;
			z-index:-1;
			width:68px;
			height:68px;
			background-color:#2c50fe;
			opacity:0.42;
			content:'';
		}
		.btn_normalbig:hover {
			background-color:#000a48;
		}
		.btn_bigtype2 {
			margin-bottom:16px;
			padding:16px;
			font-size:20px;
			line-height:66px;
			color:#111;
			font-family:'NotoSans DemiLight','Malgun Gothic','맑은 고딕','Apple SD Gothic Neo','돋움',dotum,sans-serif;
			letter-spacing:-0.5px;
			background-color:#fff;
		}
		.img_thumb {
			display:inline-block;
			vertical-align:top;
		}
		.btn_bigtype2 .img_thumb {
			margin-right:16px;
		}
		.btn_bigtype2 .ico_developers {
			float:right;
			width:15px;
			height:13px;
			margin:25px 17px 0 0;
		}
		.btn_bigtype2:hover {
			background-color:#fff;
			text-decoration:underline;
		}
		@media only screen and (max-width:828px) {
			.inner_view {
				width:auto;
				min-width:320px;
			}
			.group_viewcont {
				margin-top:16px;
				padding:0 16px;
			}
			.group_viewcont+.group_viewcont {
				margin-top:8px;
			}
			.group_viewcont [class^='area'] {
				padding:0 20px 30px;
			}
			.group_viewcont .tit_viewcont.tit_type2 {
				font-size:19px;
				line-height:28px;
				font-family:'NotoSans Medium','Malgun Gothic','맑은 고딕','Apple SD Gothic Neo','돋움',dotum,sans-serif;
				font-weight:400;
			}
			.group_error .tit_viewcont.tit_type2 {
				padding-top:36px;
			}
			.group_error .desc_viewcont.desc_type1 {
				padding-top:12px;
				line-height:23px;
			}
			.group_viewcont.group_error {
				margin-left:16px;
				margin-right:16px;
			}
			.set_btn.set_type1 {
				margin-top:25px;
			}
			.set_btn .btn_normalbig {
				height:55px;
				padding:0;
				line-height:55px;
				font-size:15px;
				text-align:center;
			}
			.set_btn .btn_normalbig:hover {
				background-color:#03166c;
			}
			.btn_normalbig .ico_developers {
				display:none !important;
			}
			.btn_normalbig .txt_error {
				display:none;
			}
			.btn_normalbig img {
				display:none !important;
			}
			.set_btn .btn_normalbig.btn_bigtype2:hover {
				background-color: #fff;
			}
		}
	</style>
	<div class="KDC_Body__root__CQLKv no_breadcrumb">
		<div class="KDC_Content__centerRoot__hn2rL inner_view">
			<div class="group_viewcont group_error">
				<div class="area_accountinfo">
					<h3 class="tit_viewcont tit_type2">페이지를 찾을 수 없습니다!</h3>
					<p class="desc_viewcont desc_type1">
						입력한 주소가 맞는지 다시 한 번 확인해주세요.
						<?php /*
						<span>
							도움이 필요하시면, 포럼(DevTalk)으로 문의해주세요.
							<a href="https://devtalk.kakao.com" target="_blank" class="link_normal link_front" style="margin-top:-1px">문의하기</a>
						</span>
						*/ ?>
					</p>
					<div class="set_btn set_type1">
						<a href="./" class="btn_normalbig"><span class="error_link">홈으로 이동하기</span><span class="txt_error">Page Not Found.</span></a>
					</div>
				</div>
			</div>
		</div>
	</div>

<?php
	include_once("./_tail.php");
?>