<?php
	error_reporting(E_ALL);
	ini_set("display_errors", 0);
	include("./_common.php");
	/*
	if(!$url_code) {
		if($mb_id != $member['mb_id']) {
			alert("로그인한 사람과 결제자가 다릅니다.");
			exit;
		}
	}
	*/

	if($is_admin) {
		$pay_product = "테스트 상품";
		$pay_price = "1004";
		$pay_pname = "테스트";
		$pay_phone = "010-1234-5678";
		$pay_email = "test@naver.com";
		$pay_cardnum = "4579-7362-5143-3035";
		$pay_installment = "0";
		$pay_MM = "02";
		$pay_YY = "28";
		$pay_password = "20";
		$pay_certify = "811211";
	}

	// 회원정보
	$mb = get_member($mb_id);
	
	// 회원 PG정보
	$row = sql_fetch(" select * from pay_payment where pay_id = '{$pay_id}' ");
	
	// PG정보
	$pg = sql_fetch(" select * from pay_pg where pg_id = '{$member_pg['pg_mid']}' ");

	if($member_pg['pg_use'] == '1') {
		alert_close("사용할 수 없는 결제모듈입니다.");
	}

	include_once("./_head.php");
	/*
	$mb_pass = rand(11111,999999);

	if($member['mb_id']) {
		if(!$is_admin) {
			if(!$mb_id) {
				$w = "u";
				$mb_id = $member['mb_id'];
			}
		}
	}

	if($mb_id) {
		$mb = get_member($mb_id);
		$mb_nick = $mb['mb_nick'];
		$mb_name = $mb['mb_name'];
		$mb_hp =explode('-' , $mb['mb_hp']);
		if(!$mb['mb_id']) {
			alert("잘못된 접근입니다.", G5_URL);
		}
		if(!$is_admin) {
			if($member['mb_id'] != $mb['mb_id']) {
				alert("잘못된 접근입니다.", G5_URL);
			}
		}
	}
	*/
?>

<script>

	function insert_bbs() {

		$("#btn1, #btn2").hide(); // 결제버튼 숨기기

		var pay_id= $("#pay_id").val(); // 결제 아이디
		var mb_id= $("#mb_id").val(); // 아이디


		$.ajax({
			url: "./cancel_update.php",
			type: "POST",
			async:'false',
			data: {
					'pay_id' : pay_id,
					'mb_id' : mb_id
			},
			success:function(data) {
				if(data == "0000") {
					alert('취소성공');
					opener.parent.location.reload();
					window.close();
				} else {
					alert(data);
					$("#btn1, #btn2").show();
				}
			},
			beforeSend:function() { // 이미지 보여주기 처리
				$('.wrap-loading').removeClass('display-none');
			},
			complete:function() { // 이미지 감추기 처리
				$('.wrap-loading').addClass('display-none');
			},
			error: function (request, status, error) {
				console.log("code: " + request.status)
				console.log("message: " + request.responseText)
				console.log("error: " + error);
				$("#btn1, #btn2").show();
			}
		});
		return false;

	}

	function go_close() {
		window.close();
	}

</script>
<style>
	table.table_pg {
		width:100%;
		border-collapse: collapse;
		text-align: left;
		line-height: 1.5;
		border: 1px solid #ddd;
		border-top: 1px solid #111;

	}
	table.table_pg th {
		width: 30%;
		padding: 10px;
		font-weight: normal;
		color: #369;
		border-bottom: 1px solid #ddd;
		background: #f3f6f7;
		font-size:11px;
	}
	table.table_pg td {
		width: 70%;
		padding: 10px;
		border-bottom: 1px solid #ddd;
	}
	#bbs .bbs-cont {padding: 1em 0 2em}
	.bbs-cont .inner {padding:0 10px}

</style>


<style>
.wrap-loading{ /*화면 전체를 어둡게 합니다.*/
	position: fixed;
	left:0;
	right:0;
	top:0;
	bottom:0;
	background: #fff;    /* ie */
	z-index: 9;
}
.wrap-loading div{ /*로딩 이미지*/
	position: fixed;
	top:50%;
	left:50%;
	margin-left: -100px;
	margin-top: -205px;
}
.display-none{ /*감추기*/
	display:none;
}
</style>
<div class="wrap-loading display-none">
	<div><img src="./img/loading_payment.gif" width="200" height="411"></div>
</div>

<section class="container" id="bbs">
	<section class="contents contents-bbs">
		<div class="top">
			<div class="inner">
				<table class="table_pg">
					<tr>
						<th>가맹점명</th>
						<td><?php echo $row['mb_name']; ?></td>
					</tr>
					<tr>
						<th>승인번호</th>
						<td><?php echo $row['card_num']; ?></td>
					</tr>
					<tr>
						<th>승인일시</th>
						<td><?php echo $row['card_sdatetime']; ?></td>
					</tr>
					<tr>
						<th>결제금액</th>
						<td><?php echo number_format($row['pay_price']); ?></td>
					</tr>
					<tr>
						<th>카드사</th>
						<td><?php echo $row['card_name']; ?></td>
					</tr>
					<tr>
						<th>할부</th>
						<td><?php if($row['pay_installment'] == 0) { echo "일시불"; } else { echo $row['pay_installment']."개월"; } ?></td>
					</tr>
					<tr>
						<th>카드사</th>
						<td><div style='color:red; font-weight:bold'>승인 취소시 돌이킬 수 없습니다.</div></td>
					</tr>
				</table>

			</div>
		</div>
		<div class="bbs-cont">
			<div class="inner">
				<div class="write-form">
					<form name="insert_bbs_form" method=post enctype="multipart/form-data" action="./payment_update.php" onsubmit="return false;">
						<input type="hidden" name="pay_id" id="pay_id" value="<?php echo $row['pay_id']; ?>">
						<input type="hidden" name="mb_id" id="mb_id" value="<?php echo $member['mb_id']; ?>">
					</form>
				</div>

				<div class="btn-center-block">
					<div class="btn-group btn-horizon">
						<div class="btn-table">
							<a class="btn btn-black btn-cell" id="btn1" onclick="insert_bbs()">취소승인</a>
							<a class="btn btn-black-line btn-cell" id="btn2" onclick="go_close();">취소</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</section>
<?php
	include_once("./_foot.php");
?>