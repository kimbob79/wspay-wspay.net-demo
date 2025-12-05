
<!DOCTYPE html>
<html class="ui-mobile"><head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <meta property="og:type" content="website">
    <meta property="og:title" content="PayApp">
    <meta property="og:url" content="https://www.payapp.kr/L/z3iLp7">
    <meta property="og:image" content="https://payapp.kr/new_home/img/meta_payapp_logo.png">
    <meta property="og:image:width" content="800">
    <meta property="og:image:height" content="400">
    <meta property="og:description" content="유유소프트">
    <meta property="og:site_name" content="페이앱 공식 홈페이지">
	<title>PayApp</title>
	<script src="./js/jquery-1.9.1.min.js"></script>

<script src="./js/jquery.mobile-1.3.0.min.js"></script>
    <link rel="stylesheet" href="./css/pay.css">
	<script type="text/javascript">
		$(document).bind( "pagebeforechange", function( e, data ) {
		});
		$(document).bind('pageinit',function(){
        });
        $(document).bind("mobileinit", function(){
            $.mobile.ajaxEnabled = false;
        });
        $(function() {
            $('a#payment_sms,a#payment_call')
                .css('cursor', 'pointer')
                .on('click', function () {
                    var f = $(document.frmNext);
                                                if ($('#input_goodname', f).val() == '') {
                                alert('결제하실 상품명을 입력하세요.');
                                return;
                            }
                                                            if ($('#input_price', f).val() == '') {
                                    alert('결제하실 금액을 입력하세요.');
                                    return;
                                }
                                                    if ($('#mem_phone', f).val() == '' || $('#mem_phone', f).val() == '0101234XXXX') {
                        alert('휴대전화 번호를 입력하세요.');
                        return;
                    }
                                        if (!$('input#agreeTerm', f).prop('checked')) {
                        alert('이용약관에 동의하세요.');
                        return;
                    }
                    if (!$('input#agreePriv', f).prop('checked')) {
                        alert('개인정보 처리방침에 동의하세요.');
                        return;
                    }
                    if ($(this).attr('id') == 'payment_sms') {
                        f.attr('action', 'https://www.payapp.kr/L/z3iLp7?SMSPAGE').get(0).submit();
                    } else {
                        f.attr('action', 'https://www.payapp.kr/L/z3iLp7?PAGELO').get(0).submit();
                    }
                });
            $('#input_price').on('keyup input', function () {
                var f = $(document.frmNext);
                                    var num = $('#input_price', f).val().replace(/[^0-9]/g, ''),
                        n = '',
                        idx = 0;
                    for (var i = 0; i < num.length; i += 3) {
                        idx = num.length - (3 + i);
                        n = num.substr((idx < 0 ? 0 : idx), 3 + (idx < 0 ? idx : 0)) + (n == '' ? n : ',' + n);
                    }
                    $('#input_price', f).val(n);
                            });
            $('#price', $(document.forms[0])).on('change',function () {
                if ($(this).val() == 'input_price') {
                    $('#input_price').parent().parent().show();
                } else {
                    $('#input_price').parent().parent().hide();
                }
            });
                                    $('input#agreeCheckAll:checkbox').on('click', function () {
                $('input#agreeTerm,input#agreePriv').prop('checked', $(this).prop('checked'));
            });
            $('input#agreeTerm,input#agreePriv').on('change', function () {
                var checked = $('input#agreeTerm').prop('checked') &&
                    $('input#agreePriv').prop('checked');
                $('input#agreeCheckAll').prop('checked', checked);
            });
        });
		function popwinclose(){
            window.close();
		}
	</script>
</head>
<body class="smsWrap ui-mobile-viewport ui-overlay-c">
    <div id="page1" class="bg-white ui-page ui-body-c ui-page-active" data-role="page" data-title="PayApp" data-url="page1" tabindex="0" style="min-height: 1280px;">

        <div data-role="header" class="ui-header ui-bar-a" role="banner">
            <div class="logo ico">PAYAPP</div>
            <h1 class="pull-right ui-title" role="heading" aria-level="1">비대면 결제서비스 페이앱</h1>
        </div>

		<div data-role="content" class="ui-content" role="main">
            <div class="content-wrap">

                                <!-- //tab -->

                <form action="" method="POST" name="frmNext" id="frmNext" onsubmit="return false;">
				<input type="hidden" name="L" id="L" value="KRW">

                    <div class="panel-goods">
                        <div class="inner">
                            <div class="good-name">
                                                                                                            <div class="ui-input-text ui-shadow-inset ui-corner-all ui-btn-shadow ui-body-c ui-mini"><input type="text" name="input_goodname" id="input_goodname" value="" placeholder="상품명 입력" data-role="text" data-mini="true" class="form-control ui-input-text ui-body-c"></div>
                                                                                                </div>
                            <!-- //상품명 -->

                            <div class="good-price">
                                <span>결제금액<i class="ico ico-arrow-r"></i></span>
                                <p>
                                                                                    </p><div class="form-inline">
                                                    <input type="text" pattern="[0-9,]*" name="input_price" id="input_price" placeholder="결제금액을 입력하세요." value="" data-role="none" data-mini="true" class="form-control">
                                                    <span class="won">원</span>
                                                </div>
                                                                                <p></p>
                            </div>
                            <!-- //상품금액 -->
                        </div>

                        <div class="sell-name">
                            <div class="inner">
                                <dl class="clearfix">
                                    <dt>상점명</dt>
                                    <dd>
                                        유유소프트                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <!-- //상점정보 -->

                        <div class="prod-wrap">
                            <div class="inner">
                                <h3>상품 정보</h3>
                                                                                                                                </div>
                        </div>
                        <!-- //그 외 상품 리스트 -->

                        <div class="inner">
                            <h3>연락처</h3>
                                                            <div class="ui-input-text ui-shadow-inset ui-corner-all ui-btn-shadow ui-body-c ui-mini"><input type="text" pattern="[0-9]*" name="mem_phone" placeholder="0101234XXXX" id="mem_phone" maxlength="11" data-role="text" data-mini="true" class="form-control ui-input-text ui-body-c"></div>
                                                        <!-- //구매자 연락처 -->

                                                        <!-- //주소입력 -->

                                                                                                <div class="description">
                                        <p><i class="ico ico-mark-ex"></i>수기결제는 "결제요청 SMS발송" 버튼을 눌러주세요.</p>
                                    </div>
                                                                                        <!-- //수기결제 안내 -->

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
                                            <input type="checkbox" name="agreeTerm" id="agreeTerm" value="1" data-role="none"> <span>페이앱 이용약관 동의</span>
                                        </label>
                                        <a href="https://www.payapp.kr/L/z3iLp7?AGREEMENT&amp;L=KRW" data-role="none" data-transition="slide" data-inline="true" data-mini="true" rel="external" data-ajax="false">
                                            보기                                        </a>
                                    </li>
                                    <li>
                                        <label for="agreePriv" class="input-chk">
                                            <input type="checkbox" name="agreePriv" id="agreePriv" value="1" data-role="none"> <span>개인정보 처리방침 동의</span>
                                        </label>
                                        <a href="https://www.payapp.kr/L/z3iLp7?AGREEMENT2&amp;L=KRW" data-role="none" data-transition="slide" data-inline="true" data-mini="true" rel="external" data-ajax="false">
                                            보기                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <!-- //약관동의 -->

                        </div>
                    </div>
                    <!-- //panel-goods -->

                                            <div class="ui-grid-btn">
                            <a id="payment_sms" class="btn btn-gray ui-link" style="cursor: pointer;">결제요청 SMS발송</a>
                            <a id="payment_call" class="btn btn-blue ui-link" style="cursor: pointer;">바로결제</a>
                            <!--<a href="javascript:popwinclose()" class="btn btn-gray"></a>-->
                        </div>
                    			    </form>
            </div>
		</div>
    </div>



<div class="ui-loader ui-corner-all ui-body-a ui-loader-default"><span class="ui-icon ui-icon-loading"></span><h1>loading</h1></div></body><whale-quicksearch translate="no"></whale-quicksearch></html>