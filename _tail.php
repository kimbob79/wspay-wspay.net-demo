
			</div>
			<?php /*
			<div class="conri <?php if($p != "main" and !$bo_table) { ?>right<?php } ?>">

				<aside id="popular">
					<h2>월별 결제현황</h2>
					<div class="desc_toolcont">

						<table class="table_list td_pd">
							<thead>
								<tr>
									<th scope="col" style="width:5%;">월</th>
									<th scope="col">총승인</th>
									<th scope="col" style="width:18%;">수기</th>
									<th scope="col" style="width:18%;">수기취소</th>
									<th scope="col" style="width:18%;">단말기</th>
									<th scope="col" style="width:18%;">단말기취소</th>
								</tr>
							</thead>
							<tbody>
								<?php
									if($member['mb_level'] == 10) {

										if(adm_sql_common) {
											$adm_sql = " mb_1 IN (".adm_sql_common.")";
										} else {
											$adm_sql = " (1)";
										}

									} else if($member['mb_level'] == 8) {
										$adm_sql = " mb_1 = '{$member['mb_id']}'";
									} else if($member['mb_level'] == 7) {
										$adm_sql = " mb_2 = '{$member['mb_id']}'";
									} else if($member['mb_level'] == 6) {
										$adm_sql = " mb_3 = '{$member['mb_id']}'";
									} else if($member['mb_level'] == 5) {
										$adm_sql = " mb_4 = '{$member['mb_id']}'";
									} else if($member['mb_level'] == 4) {
										$adm_sql = " mb_5 = '{$member['mb_id']}'";
									} else if($member['mb_level'] == 3) {
										$adm_sql = " mb_6 = '{$member['mb_id']}'";
									}


									$sql = " SELECT *, MONTH(`datetime`) AS `date`,
													sum(if(dv_type = '2' and pay_type = 'Y', pay, null)) as orderprices,
													sum(if(dv_type = '1' and pay_type = 'Y', pay, null)) as orderpriced,
													sum(if(pay_type = 'N', pay, null)) as cancelprice
												from g5_payment
												where ".$adm_sql."  GROUP BY date order by datetime desc";


									$sql = "SELECT DATE_FORMAT(`datetime`,'%Y-%m') m, 
											sum(if(dv_type = '2' and pay_type = 'Y', pay, null)) as sugis,
											sum(if(dv_type = '2' and pay_type = 'N', pay, null)) as sugic,
											sum(if(dv_type = '1' and pay_type = 'Y', pay, null)) as dans,
											sum(if(dv_type = '1' and pay_type = 'N', pay, null)) as danc
											FROM g5_payment
											where ".$adm_sql."   GROUP BY m order by m desc";

									$result = sql_query($sql);
									//echo $sql."<br><br>";
									for ($i = 0; $mons = sql_fetch_array($result); $i++) {
									$sums = $mons['sugis'] + $mons['dans'] + $mons['sugic'] + $mons['danc'];
									$mons['orders'] = (int)$mons['orderprices'];
									$mons['orderd'] = (int)$mons['orderpriced'];
									$mons['cancel'] = (int)$mons['cancelprice'];
									$month = substr($mons['m'],2,5);
								?>
								<tr>
									<td style="text-align:center;"><?php echo str_replace("-", "/", $month); ?></td>
									<td style="text-align:right;font-weight:bold;"><?php echo number_format($sums); ?></div>
									<td style="text-align:right"><?php echo number_format($mons['sugis']); ?></td>
									<td style="text-align:right"><?php echo number_format($mons['sugic']); ?></td>
									<td style="text-align:right"><?php echo number_format($mons['dans']); ?></td>
									<td style="text-align:right"><?php echo number_format($mons['danc']); ?></td>
								</tr>

								<?php
								}
								?>

							</tbody>
						</table>

					</div>
				</aside>
				<?php if($is_admin) { ?>
				<div class="notice">
					<h2><a href="#">공지사항</a></h2>
					<ul>
						<li>
							<a href="#">공지사항1<span class="cnt_cmt">1</span></a>
						</li>
						<li>
							<a href="#">공지사항1<span class="cnt_cmt">1</span></a>
						</li>
						<li>
							<a href="#">공지사항1<span class="cnt_cmt">1</span></a>
						</li>
						<li>
							<a href="#">공지사항1<span class="cnt_cmt">1</span></a>
						</li>
						<li>
							<a href="#">공지사항1<span class="cnt_cmt">1</span></a>
						</li>
					</ul>
				</div>
				<?php } ?>
			</div>
			*/ ?>
		</div>
		<!-- 상단으로 이동하기 버튼 -->
		<a href="#" class="btn_gotop">
			<div class="btn_gotop_inner">
				<i class="fa fa-chevron-up"></i>
				<span class="btn_gotop_text">TOP</span>
			</div>
		</a>
		<footer id="footer" class="mob_mft">
			<style>
			.footer-wrapper {
				background: #f5f5f5;
				border-top: 1px solid #e0e0e0;
				padding: 0;
				margin-top: 20px;
			}
			.footer-toggle {
				display: flex;
				align-items: center;
				justify-content: center;
				gap: 8px;
				padding: 12px 20px;
				cursor: pointer;
				transition: background 0.2s;
			}
			.footer-toggle:hover {
				background: #eee;
			}
			.footer-toggle .company-name {
				color: #666;
				font-size: 13px;
				font-weight: 500;
				display: flex;
				align-items: center;
				gap: 6px;
			}
			.footer-toggle .company-name i {
				font-size: 12px;
				color: #888;
			}
			.footer-toggle .toggle-arrow {
				color: #999;
				font-size: 10px;
				transition: transform 0.3s;
			}
			.footer-toggle.active .toggle-arrow {
				transform: rotate(180deg);
			}
			.footer-info {
				display: none;
				background: #fff;
				padding: 15px 20px;
				border-top: 1px solid #eee;
			}
			.footer-info-inner {
				display: flex;
				flex-wrap: wrap;
				justify-content: center;
				gap: 20px;
			}
			.footer-info-item {
				display: flex;
				align-items: center;
				gap: 6px;
				color: #888;
				font-size: 12px;
			}
			.footer-info-item i {
				color: #aaa;
				font-size: 11px;
				width: 14px;
				text-align: center;
			}
			.footer-info-item span {
				color: #555;
				font-weight: 500;
			}
			.footer-copyright {
				text-align: center;
				padding: 10px;
				background: #eee;
				color: #999;
				font-size: 10px;
			}
			@media (max-width: 600px) {
				.footer-info-inner {
					flex-direction: column;
					align-items: center;
					gap: 10px;
				}
			}
			</style>
			<div class="footer-wrapper">
				<div class="footer-toggle" id="footerToggle">
					<span class="company-name"><i class="fa fa-building"></i>원성페이먼츠 사업자 정보</span>
					<i class="fa fa-chevron-down toggle-arrow"></i>
				</div>
				<div class="footer-info" id="footerInfo">
					<div class="footer-info-inner">
						<div class="footer-info-item">
							<i class="fa fa-id-card"></i>
							사업자등록번호 <span>596-88-02642</span>
						</div>
						<div class="footer-info-item">
							<i class="fa fa-phone"></i>
							고객센터 <span>1555-0985</span>
						</div>
						<div class="footer-info-item">
							<i class="fa fa-envelope"></i>
							이메일 <span>wsgpay@naver.com</span>
						</div>
					</div>
				</div>
				<div class="footer-copyright">
					© <?php echo date('Y'); ?> WONSUNG PAYMENTS. All rights reserved.
				</div>
			</div>
		</footer>
	</div>
</div>



<script>
//푸터
$('#footerToggle').on('click', function(){
	$(this).toggleClass('active');
	if($(this).hasClass('active')){
		$('#footerInfo').slideDown(200);
	} else {
		$('#footerInfo').slideUp(200);
	}
});
//탑버튼
$(window).scroll(function(){
	if ($(this).scrollTop() > 300){
		$('.btn_gotop').fadeIn(500).css('display', 'flex');
	} else{
		$('.btn_gotop').fadeOut(500);
	}
});
$('.btn_gotop').click(function(){
	$('html, body').animate({scrollTop:0},600, 'swing');
	return false;
});
/*
$(".m_board_scroll").on("scroll",function(){
	$(this).find(".txt_ex_scroll").remove();
});
$(".m_board_scroll .txt_ex_scroll").on("click",function(){
	$(this).remove();
});
*/
function set_date(today) {
	<?php
		$date_term = date('w', G5_SERVER_TIME) - 1;
		$week_term = $date_term + 7;
		$last_term = strtotime(date('Y-m-01', G5_SERVER_TIME));
	?>
	if (today == "오늘") {
		document.getElementById("fr_date").value = "<?php echo date('Ymd'); ?>";
		document.getElementById("to_date").value = "<?php echo date('Ymd'); ?>";
		document.getElementById("day").value = "1";
	} else if (today == "어제") {
		document.getElementById("fr_date").value = "<?php echo date('Ymd', G5_SERVER_TIME - 86400); ?>";
		document.getElementById("to_date").value = "<?php echo date('Ymd', G5_SERVER_TIME - 86400); ?>";
		document.getElementById("day").value = "2";
	} else if (today == "이번주") {
		document.getElementById("fr_date").value = "<?php echo date('Ymd', strtotime('-'.$date_term.' days', G5_SERVER_TIME)); ?>";
		document.getElementById("to_date").value = "<?php echo date('Ymd', G5_SERVER_TIME); ?>";
		document.getElementById("day").value = "3";
	} else if (today == "이번달") {
		document.getElementById("fr_date").value = "<?php echo date('Ym01', G5_SERVER_TIME); ?>";
		document.getElementById("to_date").value = "<?php echo date('Ymd', G5_SERVER_TIME); ?>";
		document.getElementById("day").value = "4";
	} else if (today == "지난주") {
		document.getElementById("fr_date").value = "<?php echo date('Ymd', strtotime('-'.$week_term.' days', G5_SERVER_TIME)); ?>";
		document.getElementById("to_date").value = "<?php echo date('Ymd', strtotime('-'.($week_term - 6).' days', G5_SERVER_TIME)); ?>";
		document.getElementById("day").value = "5";
	} else if (today == "지난달") {
		document.getElementById("fr_date").value = "<?php echo date('Ym01', strtotime('-1 Month', $last_term)); ?>";
		document.getElementById("to_date").value = "<?php echo date('Ymt', strtotime('-1 Month', $last_term)); ?>";
		document.getElementById("day").value = "6";
	} else if (today == "전체") {
		document.getElementById("fr_date").value = "all";
		document.getElementById("to_date").value = "all";
	}
	$(this).parents().filter("form").submit();
}

function receiptPopup(pay_id) {
	PopupCenter('/receipt.php?pay_id='+pay_id, 'receipt_popup', 420, 830, {toolbar:0, resizable:0, location:0, menubar:0, status:0});
}

function receiptPopup2(ref_no, tran_date) {
	PopupCenter('https://cp.mainpay.co.kr/salesReceipt/salesReceipt_popup.do?ref_no='+ref_no+'&tran_date='+tran_date, 'receipt_popup2', 480, 830, {toolbar:0, resizable:0, location:0, menubar:0, status:0});
}

function recalculation(pay_id) {
	PopupCenter('./recalculation.php?pay_id='+pay_id, 'recalculation', 500, 300, {toolbar:0, resizable:0, location:0, menubar:0, status:0});
}

function update_k1(pg_id) {
	PopupCenter('./update_k1.php?pg_id='+pg_id, 'update_k1', 500, 300, {toolbar:0, resizable:0, location:0, menubar:0, status:0});
}

function update_korpay(pg_id) {
	PopupCenter('./update_korpay.php?pg_id='+pg_id, 'update_korpay', 500, 300, {toolbar:0, resizable:0, location:0, menubar:0, status:0});
}

function update_danal(pg_id) {
	PopupCenter('./update_danal.php?pg_id='+pg_id, 'update_danal', 500, 300, {toolbar:0, resizable:0, location:0, menubar:0, status:0});
}

function update_welcom(pg_id) {
	PopupCenter('./update_welcom.php?pg_id='+pg_id, 'update_welcom', 500, 300, {toolbar:0, resizable:0, location:0, menubar:0, status:0});
}

function update_paysis(pg_id) {
	PopupCenter('./update_paysis.php?pg_id='+pg_id, 'update_welcom', 500, 300, {toolbar:0, resizable:0, location:0, menubar:0, status:0});
}

function update_stn(pg_id) {
	PopupCenter('./update_stn.php?pg_id='+pg_id, 'update_welcom', 500, 300, {toolbar:0, resizable:0, location:0, menubar:0, status:0});
}

function update_routeup(pg_id) {
	PopupCenter('./update_routeup.php?pg_id='+pg_id, 'update_routeup', 500, 300, {toolbar:0, resizable:0, location:0, menubar:0, status:0});
}

function update_daou(pg_id) {
	PopupCenter('./update_daou.php?pg_id='+pg_id, 'update_daou', 500, 300, {toolbar:0, resizable:0, location:0, menubar:0, status:0});
}

function payment_copy(pay_id) {
	PopupCenter('/payment_copy.php?pay_id='+pay_id, 'payment_copy', 450, 550, {toolbar:0, resizable:0, location:0, menubar:0, status:0});
}
 
function payment_memo(pay_id) {
	PopupCenter('/memo.php?pay_id='+pay_id, 'payment_memo', 600, 400, {toolbar:0, resizable:0, location:0, menubar:0, status:0});
}

function sftp_data(date) {
	PopupCenter('./sftp_mainpay/send_data.php?date='+date, 'payment_memo', 600, 400, {toolbar:0, resizable:0, location:0, menubar:0, status:0});
}

function send_member(mbr) {
	PopupCenter('./sftp_mainpay/send_member.php?mbr='+mbr, 'payment_memo', 600, 400, {toolbar:0, resizable:0, location:0, menubar:0, status:0});
}



function deposit_payment(pay_id, deposit) {
	PopupCenter('./deposit_payment.php?pay_id='+pay_id+'&deposit='+deposit, 'deposit_payment', 500, 450, {toolbar:0, resizable:0, location:0, menubar:0, status:0});
}



function cancel_payment(pay_id, ca_type) {

	if(ca_type == "insert") { // 취소신청
		PopupCenter('./cancel_payment_insert.php?pay_id='+pay_id, 'cancel_payment', 500, 450, {toolbar:0, resizable:0, location:0, menubar:0, status:0});
	} else if(ca_type == "success") { // 취소완료 / 대기
		if (confirm("취소 상태를 변경하시겠습니까?")) {
			PopupCenter('./cancel_payment_update.php?ca_type=success&ca_id='+pay_id, 'cancel_payment', 500, 450, {toolbar:0, resizable:0, location:0, menubar:0, status:0});
		}
	} else if(ca_type == "deposit") { // 입금완료 / 대기
		if (confirm("입금 상태를 변경하시겠습니까?")) {
			PopupCenter('./cancel_payment_update.php?ca_type=deposit&ca_id='+pay_id, 'cancel_payment', 500, 450, {toolbar:0, resizable:0, location:0, menubar:0, status:0});
		}
	} else if(ca_type == "delete") { // 삭제
		PopupCenter('./cancel_payment_update.php?ca_type=delete&ca_id='+pay_id, 'cancel_payment', 500, 450, {toolbar:0, resizable:0, location:0, menubar:0, status:0});
	}

}

function PopupCenter(url, title, w, h, opts) {
	var _innerOpts = '';
	if(opts !== null && typeof opts === 'object' ){
		for (var p in opts ) {
			if (opts.hasOwnProperty(p)) {
				_innerOpts += p + '=' + opts[p] + ',';
			}
		}
	}
	var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
	var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;
	var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
	var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
	var left = ((width / 2) - (w / 2)) + dualScreenLeft;
	var top = ((height / 2) - (h / 2)) + dualScreenTop;
	var newWindow = window.open(url, title, _innerOpts + ' width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
	if (window.focus) {
		newWindow.focus();
	}
}

$(function(){
	$("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yymmdd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });
});

$("#expansion").click(function() {
	$(this).parents().filter("form").submit();
});

$("#page_count, #dv_type, #dv_certi").change(function() {
	$(this).parents().filter("form").submit();
});

$("#xlsx").click(function() {
	$("#frm_xlsx").submit();
	return false;
});


var win_zip = function(frm_name, frm_zip, frm_addr1, frm_addr2, frm_addr3, frm_jibeon) {
    if(typeof daum === 'undefined'){
        alert("다음 우편번호 postcode.v2.js 파일이 로드되지 않았습니다.");
        return false;
    }

    // 핀치 줌 현상 제거
    var vContent = "width=device-width,initial-scale=1.0,minimum-scale=0,maximum-scale=10";
    $("#meta_viewport").attr("content", vContent + ",user-scalable=no");

    var zip_case = 1;   //0이면 레이어, 1이면 페이지에 끼워 넣기, 2이면 새창

    var complete_fn = function(data){
        // 팝업에서 검색결과 항목을 클릭했을때 실행할 코드를 작성하는 부분.

        // 각 주소의 노출 규칙에 따라 주소를 조합한다.
        // 내려오는 변수가 값이 없는 경우엔 공백('')값을 가지므로, 이를 참고하여 분기 한다.
        var fullAddr = ''; // 최종 주소 변수
        var extraAddr = ''; // 조합형 주소 변수

        // 사용자가 선택한 주소 타입에 따라 해당 주소 값을 가져온다.
        if (data.userSelectedType === 'R') { // 사용자가 도로명 주소를 선택했을 경우
            fullAddr = data.roadAddress;

        } else { // 사용자가 지번 주소를 선택했을 경우(J)
            fullAddr = data.jibunAddress;
        }

        // 사용자가 선택한 주소가 도로명 타입일때 조합한다.
        if(data.userSelectedType === 'R'){
            //법정동명이 있을 경우 추가한다.
            if(data.bname !== ''){
                extraAddr += data.bname;
            }
            // 건물명이 있을 경우 추가한다.
            if(data.buildingName !== ''){
                extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
            }
            // 조합형주소의 유무에 따라 양쪽에 괄호를 추가하여 최종 주소를 만든다.
            extraAddr = (extraAddr !== '' ? ' ('+ extraAddr +')' : '');
        }

        // 우편번호와 주소 정보를 해당 필드에 넣고, 커서를 상세주소 필드로 이동한다.
        var of = document[frm_name];

        of[frm_zip].value = data.zonecode;

        of[frm_addr1].value = fullAddr;
        of[frm_addr3].value = extraAddr;

        if(of[frm_jibeon] !== undefined){
            of[frm_jibeon].value = data.userSelectedType;
        }
        
        setTimeout(function(){
            $("#meta_viewport").attr("content", vContent);
            of[frm_addr2].focus();
        } , 100);
    };

    switch(zip_case) {
        case 1 :    //iframe을 이용하여 페이지에 끼워 넣기
            var daum_pape_id = 'daum_juso_page'+frm_zip,
                element_wrap = document.getElementById(daum_pape_id),
                currentScroll = Math.max(document.body.scrollTop, document.documentElement.scrollTop);
            if (element_wrap == null) {
                element_wrap = document.createElement("div");
                element_wrap.setAttribute("id", daum_pape_id);
                element_wrap.style.cssText = 'display:none;border:1px solid;left:0;width:100%;height:300px;margin:5px 0;position:relative;-webkit-overflow-scrolling:touch;';
                element_wrap.innerHTML = '<img src="//i1.daumcdn.net/localimg/localimages/07/postcode/320/close.png" id="btnFoldWrap" style="cursor:pointer;position:absolute;right:0px;top:-21px;z-index:1" class="close_daum_juso" alt="접기 버튼">';
                jQuery('form[name="'+frm_name+'"]').find('input[name="'+frm_addr1+'"]').before(element_wrap);
                jQuery("#"+daum_pape_id).off("click", ".close_daum_juso").on("click", ".close_daum_juso", function(e){
                    e.preventDefault();
                    $("#meta_viewport").attr("content", vContent);
                    jQuery(this).parent().hide();
                });
            }

            new daum.Postcode({
                oncomplete: function(data) {
                    complete_fn(data);
                    // iframe을 넣은 element를 안보이게 한다.
                    element_wrap.style.display = 'none';
                    // 우편번호 찾기 화면이 보이기 이전으로 scroll 위치를 되돌린다.
                    document.body.scrollTop = currentScroll;
                },
                // 우편번호 찾기 화면 크기가 조정되었을때 실행할 코드를 작성하는 부분.
                // iframe을 넣은 element의 높이값을 조정한다.
                onresize : function(size) {
                    element_wrap.style.height = size.height + "px";
                },
                maxSuggestItems : 6,
                width : '100%',
                height : '100%'
            }).embed(element_wrap);

            // iframe을 넣은 element를 보이게 한다.
            element_wrap.style.display = 'block';
            break;
        case 2 :    //새창으로 띄우기
            new daum.Postcode({
                oncomplete: function(data) {
                    complete_fn(data);
                }
            }).open();
            break;
        default :   //iframe을 이용하여 레이어 띄우기
            var rayer_id = 'daum_juso_rayer'+frm_zip,
                element_layer = document.getElementById(rayer_id);
            if (element_layer == null) {
                element_layer = document.createElement("div");
                element_layer.setAttribute("id", rayer_id);
                element_layer.style.cssText = 'display:none;border:5px solid;position:fixed;width:300px;height:460px;left:50%;margin-left:-155px;top:50%;margin-top:-235px;overflow:hidden;-webkit-overflow-scrolling:touch;z-index:10000';
                element_layer.innerHTML = '<img src="//i1.daumcdn.net/localimg/localimages/07/postcode/320/close.png" id="btnCloseLayer" style="cursor:pointer;position:absolute;right:-3px;top:-3px;z-index:1" class="close_daum_juso" alt="닫기 버튼">';
                document.body.appendChild(element_layer);
                jQuery("#"+rayer_id).off("click", ".close_daum_juso").on("click", ".close_daum_juso", function(e){
                    e.preventDefault();
                    $("#meta_viewport").attr("content", vContent);
                    jQuery(this).parent().hide();
                });
            }

            new daum.Postcode({
                oncomplete: function(data) {
                    complete_fn(data);
                    // iframe을 넣은 element를 안보이게 한다.
                    element_layer.style.display = 'none';
                },
                maxSuggestItems : 6,
                width : '100%',
                height : '100%'
            }).embed(element_layer);

            // iframe을 넣은 element를 보이게 한다.
            element_layer.style.display = 'block';
    }
}
</script>
<!-- } 로그인 전 아웃로그인 끝 -->

<!-- ie6,7에서 사이드뷰가 게시판 목록에서 아래 사이드뷰에 가려지는 현상 수정 -->
<!--[if lte IE 7]>
<script>
$(function() {
    var $sv_use = $(".sv_use");
    var count = $sv_use.length;

    $sv_use.each(function() {
        $(this).css("z-index", count);
        $(this).css("position", "relative");
        count = count - 1;
    });
});
</script>
<![endif]-->

<!-- 페이지 로딩 완료 처리 -->
<script>
$(window).on('load', function() {
	var $loader = $('#page-loader');
	$loader.addClass('loaded');
	setTimeout(function() {
		$loader.addClass('hidden');
	}, 400);
});
// 페이지 이동 시 로딩바 다시 표시
$(window).on('beforeunload', function() {
	var $loader = $('#page-loader');
	$loader.removeClass('loaded hidden');
	$loader.find('.loader-bar').css('animation', 'loader-progress 1.5s ease-out forwards, loader-shimmer 1s linear infinite');
});
</script>

</body>
</html>
