<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>
<meta property="og:type" content="website">
<meta property="og:title" content="PayApp">
<meta property="og:url" content="https://www.payapp.kr/z6iIq33">
<meta property="og:image" content="https://payapp.kr/new_home/img/meta_payapp_logo.png">
<meta property="og:image:width" content="800">
<meta property="og:image:height" content="400">
<meta property="og:description" content="페이앱테스트 PayApp TEST">
<meta property="og:site_name" content="페이앱 공식 홈페이지">
<title> PayApp </title>
<script src="./js/jquery-1.9.1.min.js"></script>
<script src="./js/jquery.mobile-1.3.0.min.js"></script>
<script type="text/javascript">
        $(document).on( "pagebeforechange", function( e, data ) {
            if ( typeof data.toPage === "string" ) {
                var u = $.mobile.path.parseUrl( data.toPage );
                if( u.hash == '#payment_select_pop' ){
                    if (!$('input#agreeTerm:checkbox').prop('checked')) {
                        alert('전자금융거래 이용약관에 동의 하세요.');
                        e.preventDefault();
                        return;
                    }
                    if (!$('input#agreePriv:checkbox').prop('checked')) {
                        alert('개인정보 처리방침에 동의 하세요.');
                        e.preventDefault();
                        return;
                    }
                                                                            }
            }
        });
        function payment_select(paytype){
            var f = document.frmNext;
            if( paytype == 'mobile' ){
                f.action = '/WEBPAY_M/z6CrqN5';
            }else if( paytype == 'cardca' ){
                f.action = '/WEBPAY_CKA/z6CrqN5';
            }else if( paytype == 'cardcn' ){
                f.action = '/WEBPAY_CKN/z6CrqN5';
            }else if( paytype == 'cardauth' ){
                f.action = '/WEBPAY_CA/z6CrqN5';
            }else if( paytype == 'cardpayco' ){
                f.action = '/WEBPAY_CP/z6CrqN5';
            }else if( paytype == 'cardsspay' ){
                f.action = '/WEBPAY_SSP/z6CrqN5';
            }else if( paytype == 'rbank' ){
                f.action = '/WEBPAY_RB/z6CrqN5';
            }else if( paytype == 'vbank' ){
                f.action = '/WEBPAY_VB/z6CrqN5';
            }else if( paytype == 'cardkakao' ){
                f.action = '/WEBPAY_KAO/z6CrqN5';
            }else if( paytype == 'naverpay' ){
                f.action = '/WEBPAY_NP/z6CrqN5';
            }else if( paytype == 'smilepay' ){
                f.action = '/WEBPAY_SMP/z6CrqN5';
            }else if( paytype == 'wechat' ){
                f.action = '/WEBPAY_WC/z6CrqN5';
            }else if( paytype == 'applepay' ){
                f.action = '/WEBPAY_AP/z6CrqN5';
            }
            f.submit();
        }
        $(document).on('pageinit',function(){
        });
        $(document).on("mobileinit", function(){
            //$.mobile.loadPage( "loadpage.html" );
            //$.mobile.ajaxEnabled = false;
            //$.mobile.ajaxLinksEnabled = false;
            //$.mobile.ajaxFormsEnabled = false;
        });
        $(function(){
            /*
            if (window.ApplePaySession) {
                $('.applepay').show();
            }
            */
            $('#frmNext').submit(function(e){
                return false;
            });
            $('.dialogPopBtn').on('click',function(){
                var dhash = $(this).attr('data-data');
                $.mobile.changePage(dhash,{role:'dialog',changeHash:false});
            }).css('cursor','pointer');
            $('.cancelbtn').on('click',function(){
                $.mobile.changePage('#page1',{});
            });
            $('.cancelbtn2').on('click',function(){
                $.mobile.changePage('#payment_select_pop',{changeHash:false});
            });
            $('.cancelbtn3').on('click',function(){
                $.mobile.changePage('#payment_select_card',{changeHash:false});
            });
            $('input#agreeCheckAll:checkbox').on('click',function () {
                $('input#agreeTerm,input#agreePriv,input#agreeMarket').prop('checked',$(this).prop('checked'));
            });
            $('input#agreeTerm,input#agreePriv').on('change',function(){
                var checked = $('input#agreeTerm').prop('checked') && $('input#agreePriv').prop('checked');
                $('input#agreeCheckAll:checkbox').prop('checked',checked);
            });
            $(':checkbox#show-donate')
                .on('change',function(){
                    $('#donate-table').toggle();
                });
        });
        $(function () {
            $('.cardevent-popup').on('click',function(){
                var popup = $(this).data('popup');
                $.mobile.changePage( popup, { transition: "slideup",role:'dialog',changeHash:false} );
            });
        });
    </script>
<link rel="stylesheet" href="./css/new_webpay.min.css">
</head>
<body class="smsWrap">
<div id="page1" class="bg-white" data-role="page" data-title="PayApp">
	<div data-role="header">
		<div class="logo ico">
			PAYAPP
		</div>
		<h1 class="pull-right">비대면 결제서비스 페이앱</h1>
	</div>
	<div data-role="content">
		<div class="content-wrap">
			<form action="" method="POST" name="frmNext" id="frmNext" onsubmit="return false;">
				<div class="panel-goods">
					<div class="inner">
						<div class="good-name">
							 PayApp TEST
						</div>
						<!-- //상품명 -->
						<div class="good-price">
							<span>결제금액<i class="ico ico-arrow-r"></i></span>
							<p>
								 1,000<em>원</em>
							</p>
						</div>
						<!-- //상품금액 -->
					</div>
					<div class="sell-name">
						<div class="inner">
							<dl class="clearfix">
								<dt>판매자</dt>
								<dd>페이앱테스트</dd>
							</dl>
							<dl class="clearfix">
								<dt>연락처</dt>
								<dd>010-7400-3866</dd>
							</dl>
						</div>
					</div>
					<!-- //상점정보 -->
					<!-- //그 외 상품 리스트 -->
					<div class="inner">
						<h3>판매자 메모</h3>
						<div class="memo-area">
							 페이앱 결제 테스트
						</div>
						<!-- //판매자메모 -->
						<!-- //남기실말씀 -->
						<!-- //주소입력 -->
						<!-- //후원금 영수증 발급 -->
						<!-- //부트페이 진행 시 -->
						<!-- //escrow -->
						<div class="all-check">
							<input type="checkbox" name="agreeCheckAll" id="agreeCheckAll" data-role="none">
							<label for="agreeCheckAll">
							<span><i></i>전체동의</span>
							</label>
						</div>
						<div class="agreement-wrap">
							<ul>
								<li>
								<label for="agreeTerm" class="input-chk">
								<input type="checkbox" name="agreeTerm" id="agreeTerm" value="1" data-role="none"><span>전자금융거래 이용약관</span>
								</label>
								<a href="./1.php" data-role="none" data-transition="slide" data-inline="true" data-mini="true" rel="external" data-ajax="false">
								보기 </a>
								</li>
								<li>
								<label for="agreePriv" class="input-chk">
								<input type="checkbox" name="agreePriv" id="agreePriv" value="1" data-role="none"><span>개인정보 처리방침 동의</span>
								</label>
								<a href="./2.php" data-role="none" data-transition="slide" data-inline="true" data-mini="true" rel="external" data-ajax="false">
								보기 </a>
								</li>
							</ul>
						</div>
						<!-- //약관동의 -->
					</div>
				</div>
				<!-- //panel-goods -->
				<div class="ui-grid-btn">
					<a class="dialogPopBtn btn btn-blue" data-data="#payment_select_pop" data-transition="pop" data-rel="dialog">
					다음 </a>
				</div>
			</form>
		</div>
	</div>
</div>
<div id="payment_select_pop" data-role="dialog" data-close-btn="none">
	<div data-role="header" class="bd-none">
		<a class="cancelbtn ico prev" data-role="button">취소</a>
	</div>
	<div data-role="content">
		<div class="panel-title">
			<h1>결제방식 선택</h1>
			<!--<p>상품 구매를 위한 결제 방법을 선택해주세요.</p>-->
		</div>
		<div class="content-wrap">
			<div class="inner pay-select">
				<a href="javascript:payment_select('cardauth');" class="card list-btn" rel="external">
				<p>
					<span>신용/체크카드 결제</span><br>
					<small>ISP/안심클릭</small>
				</p>
				</a><a href="javascript:payment_select('cardca');" class="check list-btn" rel="external">
				<p>
					신용/체크카드 결제<br>
					<small>카드번호 및 유효기간 입력</small>
				</p>
				</a><a href="#" class="calendar list-btn cardevent-popup" data-popup="#cardevent-popup">
				<p>
					무이자 할부 안내
				</p>
				</a>
			</div>
			<div class="ui-grid-btn">
				<a class="cancelbtn btn btn-blue" data-role="button">취소</a>
			</div>
		</div>
	</div>
</div>
<div id="payment_select_card" data-role="dialog" data-close-btn="none">
	<div data-role="header" class="bd-none">
		<a class="cancelbtn2 ico prev" data-role="button">취소</a>
	</div>
	<div data-role="content">
		<div class="panel-title">
			<h1>결제카드 선택</h1>
			<!--<p>승인 요청을 위한 결제 카드를 선택해주세요.</p>-->
		</div>
		<div class="content-wrap">
			<div class="inner">
				<a href="javascript:payment_select('cardauth');" class="card list-btn" rel="external">
				<p>
					<span>신용/체크카드 결제</span><br>
					<small>ISP/안심클릭</small>
				</p>
				</a><a href="javascript:payment_select('cardca');" class="check list-btn" rel="external">
				<p>
					신용/체크카드 결제<br>
					<small>카드번호 및 유효기간 입력</small>
				</p>
				</a><a href="#" class="calendar list-btn cardevent-popup" data-popup="#cardevent-popup">
				<p>
					무이자 할부 안내
				</p>
				</a>
			</div>
			<div class="ui-grid-btn">
				<a class="cancelbtn2 btn btn-blue" data-role="button">취소</a>
			</div>
		</div>
	</div>
</div>
<div id="payment_select_bank" data-role="dialog" data-close-btn="none">
	<div data-role="header" class="bd-none">
		<a class="cancelbtn2 ico prev" data-role="button">취소</a>
	</div>
	<div data-role="content">
		<div class="panel-title">
			<h1>무통장 결제</h1>
			<!--<p>승인 요청을 위한 이체 방법을 선택해주세요.</p>-->
		</div>
		<div class="content-wrap">
			<div class="inner">
			</div>
			<div class="ui-grid-btn">
				<a class="cancelbtn2 btn btn-blue" data-role="button">취소</a>
			</div>
		</div>
	</div>
</div>
<div id="cardevent-popup" class="bg-white" data-role="dialog" data-close-btn="none">
	<div data-role="header" class="bd-none">
		<a class="cancelbtn3 ico prev" data-role="button">닫기</a>
	</div>
	<div data-role="content">
		<div class="panel-title">
			<h1>무이자 행사 안내</h1>
		</div>
		<div class="content-wrap">
			<div class="inner">
				<!DOCTYPE html>
				<html>
				<head>
				<title>무이자 행사 안내</title>
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<meta charset="UTF-8">
				<style>
        @font-face {
            font-family: 'NanumBarunGothic';
            src: url('/fonts/NanumBarunGothic.eot');
            src: url('/fonts/NanumBarunGothic.eot?#iefix') format('embedded-opentype'),
            url('/fonts/NanumBarunGothic.woff') format('woff'),
            url('/fonts/NanumBarunGothic.ttf') format('truetype');
        }
        body {
            font-family: 'NanumBarunGothic','나눔바른고딕','NanumGothic','나눔고딕','malgun gothic','dotum','sans-serif';
            /*font-size: 1.2em;*/
            padding: 0;
            margin: 0;
        }
        * {
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box
        }
        :after, :before {
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box
        }
        .cardevent_wrap{padding:20px;font-size:14px;color:#666;word-break:keep-all}
        .cardevent_wrap .info_top{padding:15px;margin-bottom:10px;color:#333;border:1px solid #E0EBFC;background:#F5F9FF;text-align:center}
        .cardevent_wrap .info_top p:first-child{font-size:16px;margin:0 0 5px}
        /*.cardevent_wrap .info_top p:last-child{color:#333;margin:0}*/
        /*.cardevent_wrap .info_top p:last-child strong{color:#7FAAFF}*/
        /*.cardevent_wrap .info_top span{display:inline-block;padding:2px 5px;margin-right:3px;font-size:11px;border-radius:10px;background:#7FAAFF;color:#fff;vertical-align:top}*/
        .cardevent_wrap .info_txt{padding:15px;background:#F7F7F7}
        .cardevent_wrap .info_txt em{color:#333;font-weight:bold;font-style:normal}
        .cardevent_wrap .info_txt li{position:relative;padding:3px 0 3px 8px;font-size:13px;list-style:none;line-height:1.35}
        .cardevent_wrap .info_txt li::before{position:absolute;top:10px;left:0;content:'';width:3px;height:3px;background:#333}
        .cardevent_wrap .cardevent_tit{display:block;margin:20px 20px 10px 0;color:#333;font-weight:bold}
        .cardevent_wrap table{width:100%;color:#333;text-align:left;border-collapse:collapse;border-spacing:0;letter-spacing:-0.5px;font-size:13px}
        .cardevent_wrap table td{padding:12px 15px;border:1px solid #e0e0e0;line-height:18px}
        .cardevent_wrap table td:nth-of-type(1){color:#666;text-align:center}
        .cardevent_wrap table img{display:inline-block;margin:0 auto;height:30px;vertical-align:middle;padding-right:4px}
        .cardevent_wrap table p{margin:0 0 5px;font-size:13px;color: #f3504e}
        @media screen and (max-width: 375px) {
            .cardevent_wrap table td:nth-of-type(1){font-size:12px}
            .cardevent_wrap table img{display:block;padding:0;margin-bottom:3px}
        }
        .ban-img{
            margin: 20px auto 0px;
        }
        .ban-img a {
            margin-top:5px;
            display: block;
        }
        .ban-img img {
            width: 100%;
            height: auto;
            padding: 0;
        }
				</style>
				</head>
				<body>
				<form>
					<div class="cardevent_wrap">
						<div>
							<div class="info_top">
								<p>
									<strong>카드사별 1월 무이자 할부 안내</strong>
								</p>
								<p>
									<span>대상</span><strong>5만원 이상</strong> 결제 고객
								</p>
							</div>
							<ul class="info_txt">
								<li><em>제외 카드</em> : 사업장(법인,개인), 체크, 선불, 기프트카드는 제외됩니다.</li>
								<li><em>제외 가맹점</em> : 직계약 가맹점 및 상점부담무이자 가맹점, 특별 제휴 가맹점 등 일부 가맹점은 제외될 수 있습니다.</li>
								<li style="color:#f3504e"><em style="color:#f3504e">네이버페이/스마일페이</em> : 할부개월 선택 시 무이자할부 행사 안내가 있는 경우에만 할부 혜택이 제공됩니다.</li>
								<li><em>BC 유의사항</em> : 신한, KB, 하나, 우리, IBK, NH, SC, 대구, 부산, 경남, 씨티 BC 카드의 경우 BC카드 무이자 정책이 적용되며, 그 외 발급사 카드(광주, 전북, 제주, 씨티비자 등)의 경우 BC카드 무이자 정책이 아닌 각 발급사(은행사)의 무이자 정책이 적용됩니다.</li>
								<li><em>무이자 표기</em> : 카드 결제창에서 결제방식에 따라 "8개월 무이자" 식으로 "무이자"가 표기된 경우엔 해당 조건을 따르며, "무이자"가 표기되지 않더라도 행사조건을 충족하는 경우 무이자 이벤트가 적용됩니다.</li>
								<li><em>행사조건외 할부개월수 결제 시 유의사항</em> : 무이자/부분무이자조건 외의 개월수로 할부결제 하실 경우 모든 회차에 카드사 할부수수료가 청구됩니다.</li>
							</ul>
						</div>
						<strong class="cardevent_tit">무이자 행사</strong>
						<table>
						<colgroup><col style="width:35%" bgcolor="#f2f2f2"><col></colgroup>
						<tr>
							<td>
								<img src="./img/cardevent/lg_kb.png" alt="국민카드"> 국민카드
							</td>
							<td rowspan="6" style="text-align:center">
								2~3개월
							</td>
							<td rowspan="9" style="text-align:center">
								5만원 이상
							</td>
						</tr>
						<tr>
							<td>
								<img src="./img/cardevent/lg_sinhan.png" alt="신한카드"> 신한카드
							</td>
						</tr>
						<tr>
							<td>
								<img src="./img/cardevent/lg_samsung.png" alt="삼성카드"> 삼성카드
							</td>
						</tr>
						<tr>
							<td>
								<img src="./img/cardevent/lg_lotte.png" alt="롯데카드"> 롯데카드
							</td>
						</tr>
						<tr>
							<td>
								<img src="./img/cardevent/lg_hana.png" alt="하나카드"> 하나카드
							</td>
						</tr>
						<tr>
							<td>
								<img src="./img/cardevent/lg_hyundai.png" alt="현대카드"> 현대카드
							</td>
						</tr>
						<tr>
							<td>
								<img src="./img/cardevent/lg_nh.png" alt="농협카드"> 농협카드
							</td>
							<td style="text-align:center" rowspan="3">
								2~4개월
							</td>
						</tr>
						<tr>
							<td>
								<img src="./img/cardevent/lg_woori.png" alt="우리카드"> 우리카드
							</td>
						</tr>
						<tr>
							<td>
								<img src="./img/cardevent/lg_bc.png" alt="BC카드"> 비씨카드
							</td>
						</tr>
						</table>
						<span class="cardevent_tit">부분 무이자 행사</span>
						<table>
						<colgroup><col style="width:35%" bgcolor="#f2f2f2"><col></colgroup>
						<tr>
							<td>
								<img src="./img/cardevent/lg_kb.png" alt="국민카드"> 국민카드
							</td>
							<td>
								6개월: 1~3회차 고객부담<br>
								10개월: 1~5회차 고객부담
							</td>
						</tr>
						<tr>
							<td>
								<img src="./img/cardevent/lg_samsung.png" alt="삼성카드"> 삼성카드
							</td>
							<td>
								7개월: 1~3회차 고객부담<br>
								11개월: 1~5회차 고객부담
								<p style="margin:1px 0 0;color:#f3504e;font-size:12px;">
									<strong>(1.8 ~ 1.31까지)</strong>
								</p>
							</td>
						</tr>
						<tr>
							<td>
								<img src="./img/cardevent/lg_hana.png" alt="하나카드"> 하나카드
							</td>
							<td>
								6개월: 1~3회차 고객부담<br/>10개월: 1~4회차 고객부담<br/>12개월: 1~5회차 고객부담
							</td>
						</tr>
						<tr>
							<td>
								<img src="./img/cardevent/lg_woori.png" alt="우리카드"> 우리카드
							</td>
							<td>
								10개월: 1~3회차 고객부담<br/>12개월: 1~4회차 고객부담
							</td>
						</tr>
						<tr>
							<td>
								<img src="./img/cardevent/lg_sinhan.png" alt="신한카드"> 신한카드
							</td>
							<td>
								10개월: 1~4회차 고객부담<br/>12개월: 1~5회차 고객부담
							</td>
						</tr>
						<tr>
							<td>
								<img src="./img/cardevent/lg_bc.png" alt="BC카드"> 비씨카드
							</td>
							<td>
								<!--<p>ARS 사전등록 필요: 1899-5772</p>-->
								10개월: 1~3회차 고객부담<br/>12개월: 1~4회차 고객부담
							</td>
						</tr>
						<tr>
							<td>
								<img src="./img/cardevent/lg_nh.png" alt="농협카드"> 농협카드
							</td>
							<td>
								<!--<p>ARS 사전등록 필요 : 1644-2009</p>-->
								5~6개월: 1~2회차 고객부담<br/>7~10개월: 1~3회차 고객부담
							</td>
						</tr>
						</table>
						<div class="ban-img">
							<a href="https://junggopay.com/main" target="_blank" title="새창열림"><img src="./img/cardevent/img_junggopay.jpg" alt="페이앱이 보증하는 중고거래 카드결제 - 중고페이. 판매자 동의 필요 없이 내맘대로 중고거래 무이자 할부하기!"></a><a href="https://danbipay.com/main" target="_blank" title="새창열림"><img src="./img/cardevent/img_danbipay.jpg" alt="월세도 할부 결제 됩니다. 페이앱이 보증하는 월세카드결제 - 단비페이"></a>
						</div>
					</div>
				</form>
				</body>
				</html>
			</div>
			<div class="ui-grid-btn">
				<a class="cancelbtn3 btn btn-blue" data-role="button">닫기</a>
			</div>
		</div>
	</div>
</div>
</body>
</html>