<?php if($is_admin) { ?>

<div class="KDC_Menu__menu__+kFHL">
	<h3 class="KDC_Menu__menu_label__OThVu">PAYMENT</h3>
	<ul class="KDC_Menu__menu_ul__YK1by">
		<li class="KDC_Menu__node__eqsWV <?php if($p == "payment") { echo "selected"; } ?>"><a href="./?p=payment">실시간 결제내역</a></li>
		<li class="KDC_Menu__node__eqsWV <?php if($p == "payment_k1") { echo "selected"; } ?>"><a href="./?p=payment_k1">결제원본 광원</a></li>
		<li class="KDC_Menu__node__eqsWV <?php if($p == "payment_welcom") { echo "selected"; } ?>"><a href="./?p=payment_welcom">결제원본 웰컴</a></li>
	</ul>
	<h3 class="KDC_Menu__menu_label__OThVu">SETTLEMENT</h3>
	<ul class="KDC_Menu__menu_ul__YK1by">
		<li class="KDC_Menu__node__eqsWV <?php if($p == "settlement") { echo "selected"; } ?>"><a href="./?p=settlement&MM=<?php echo date("n"); ?>&YYYY=<?php echo date("Y"); ?>">실시간 정산조회</a></li>
		<?php /*
		<li class="KDC_Menu__node__eqsWV <?php if($p == "settlement_xls") { echo "selected"; } ?>"><a href="./?p=settlement_xls">정산조회 엑셀</a></li>
		*/ ?>
	</ul>
	<h3 class="KDC_Menu__menu_label__OThVu">FEE</h3>
	<ul class="KDC_Menu__menu_ul__YK1by">
		<li class="KDC_Menu__node__eqsWV <?php if($p == "device_fee") { echo "selected"; } ?>"><a href="./?p=device_fee">수수료 관리</a></li>
	</ul>
</div>

<div class="KDC_Menu__menu__+kFHL">
	<h3 class="KDC_Menu__menu_label__OThVu">MEMBERSHIP</h3>
	<ul class="KDC_Menu__menu_ul__YK1by">
		<li class="KDC_Menu__node__eqsWV <?php if($p == "member_info") { echo "selected"; } ?>"><a href="./?p=member_info">접속정보</a></li>
		<li class="KDC_Menu__node__eqsWV <?php if($p == "member_table") { echo "selected"; } ?>"><a href="./?p=member_table">회원 테이블</a>
			<ul class="KDC_Menu__subMenu__nwjTu">
				<li class="KDC_Menu__node__eqsWV <?php if($p == "member" and $level == "8") { echo "selected"; } ?>"><a href="./?p=member&level=8">본사 관리</a></li>
				<li class="KDC_Menu__node__eqsWV <?php if($p == "member" and $level == "7") { echo "selected"; } ?>"><a href="./?p=member&level=7">지사 관리</a></li>
				<li class="KDC_Menu__node__eqsWV <?php if($p == "member" and $level == "6") { echo "selected"; } ?>"><a href="./?p=member&level=6">총판 관리</a></li>
				<li class="KDC_Menu__node__eqsWV <?php if($p == "member" and $level == "5") { echo "selected"; } ?>"><a href="./?p=member&level=5">대리점 관리</a></li>
				<li class="KDC_Menu__node__eqsWV <?php if($p == "member" and $level == "4") { echo "selected"; } ?>"><a href="./?p=member&level=4">영업점 관리</a></li>
				<li class="KDC_Menu__node__eqsWV <?php if($p == "member" and $level == "3") { echo "selected"; } ?>"><a href="./?p=member&level=3">가맹점 관리</a></li>
			</ul>
		</li>
	</ul>
</div>

<div class="KDC_Menu__menu__+kFHL">
	<h3 class="KDC_Menu__menu_label__OThVu">MPAY</h3>
	<ul class="KDC_Menu__menu_ul__YK1by">
		<li class="KDC_Menu__node__eqsWV"><a href="./?p=mpay">수기결제</a></li>
		<li class="KDC_Menu__node__eqsWV"><a href="./?p=mpay_list">수기결제 내역</a></li>
	</ul>
</div>

<?php } else { ?>

<div class="KDC_Menu__menu__+kFHL">
	<h3 class="KDC_Menu__menu_label__OThVu">PAYMENT</h3>
	<ul class="KDC_Menu__menu_ul__YK1by">
		<li class="KDC_Menu__node__eqsWV <?php if($p == "payment") { echo "selected"; } ?>"><a href="./?p=payment">실시간 결제내역</a></li>
	</ul>
	<h3 class="KDC_Menu__menu_label__OThVu">SETTLEMENT</h3>
	<ul class="KDC_Menu__menu_ul__YK1by">
		<li class="KDC_Menu__node__eqsWV <?php if($p == "settlement") { echo "selected"; } ?>"><a href="./?p=settlement&MM=<?php echo date("n"); ?>&YYYY=<?php echo date("Y"); ?>">실시간 정산조회</a></li>
	</ul>
	<h3 class="KDC_Menu__menu_label__OThVu">MEMBERSHIP</h3>
	<ul class="KDC_Menu__menu_ul__YK1by">
		<li class="KDC_Menu__node__eqsWV <?php if($p == "member_table") { echo "selected"; } ?>"><a href="./?p=member_table">회원 테이블</a></li>
	</ul>
</div>

<?php } ?>