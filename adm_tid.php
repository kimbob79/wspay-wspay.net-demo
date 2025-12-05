<?php
	if(!$is_admin) { alert("관리자만 접속 가능합니다."); }
	$g5['title'] = 'TID 분리관리';

	if($member['mb_level'] <= $mb_level) { alert("권한이 없습니다."); }

	if($w == "u") {
		$mb = get_member($mb_id);

		if(!$mb['mb_id']) {
			alert("회원이 없거나 잘못된 접근입니다.");
		}

		// 사업자등록번호
		$mb_company_number =explode('-' , $mb['mb_7']);
		$mb_company_number1 = $mb_company_number[0];
		$mb_company_number2 = $mb_company_number[1];
		$mb_company_number3 = $mb_company_number[2];

		$mb_tel =explode('-' , $mb['mb_tel']);
		$mb_tel1 = $mb_tel[0];
		$mb_tel2 = $mb_tel[1];
		$mb_tel3 = $mb_tel[2];

		$mb_hp =explode('-' , $mb['mb_hp']);
		$mb_hp1 = $mb_hp[0];
		$mb_hp2 = $mb_hp[1];
		$mb_hp3 = $mb_hp[2];

		if(!$mb_level) { $mb_level = $mb['mb_level']; }
	}


	if(adm_sql_common) {
		$u_sql = " and mb_1 IN (".adm_sql_common.")";
	} else {
		$u_sql = " ";
		if($member['mb_level'] == 7) {
			$u_sql1 = " and mb_id = '{$member['mb_1']}' ";
			$u_sql2 = " and mb_2 = '{$member['mb_id']}' ";
		}
	}


	include_once("./_head.php");


//	echo $sql;
//	echo $total_pay;

?>
<script src="http://dmaps.daum.net/map_js_init/postcode.v2.js"></script>

<style>
/* 헤더 스타일 */
.tid-header {
	background: linear-gradient(135deg, #607d8b 0%, #78909c 100%);
	border-radius: 8px;
	padding: 12px 16px;
	margin-bottom: 10px;
	box-shadow: 0 2px 8px rgba(96, 125, 139, 0.2);
}
.tid-title {
	color: #fff;
	font-size: 16px;
	font-weight: 600;
	display: flex;
	align-items: center;
	gap: 8px;
	margin-bottom: 8px;
}
.tid-title i {
	font-size: 14px;
	opacity: 0.8;
}
.tid-notice {
	background: rgba(255, 255, 255, 0.1);
	border-radius: 6px;
	padding: 10px 12px;
	margin-top: 8px;
}
.tid-notice ul {
	list-style: none;
	padding: 0;
	margin: 0;
}
.tid-notice li {
	color: rgba(255, 255, 255, 0.9);
	font-size: 12px;
	line-height: 1.6;
	padding-left: 16px;
	position: relative;
}
.tid-notice li:before {
	content: "•";
	position: absolute;
	left: 0;
	color: rgba(255, 255, 255, 0.7);
}
</style>

<div class="tid-header">
	<div class="tid-title">
		<i class="fa fa-cog"></i>
		TID 분리관리
	</div>
	<div class="tid-notice">
		<ul>
			<li>이곳에 작성된 TID는 결제내역 등록시 TID가 나눠서 저장됩니다.</li>
			<li>엔터로 값을 구분합니다.</li>
		</ul>
	</div>
</div>


<form name="fmember" id="fmember" action="./?p=adm_tid_update" onsubmit="return fregisterform_submit(this);" method="post" enctype="multipart/form-data">





	<table class="table_view">
		<thead>
			<tr>
				<th style="width:10%">분류</th>
				<th>분할사용</th>
				<th>단독사용</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><strong>광원</strong></td>
				<td><textarea name="cf_1" style="width:150px; height:200px;"><?php echo $config['cf_1']; ?></textarea></td>
				<td></td>
			</tr>
			<tr>
				<td><strong>페이시스</strong></td>
				<td><textarea name="cf_2" style="width:150px; height:200px;"><?php echo $config['cf_2']; ?></textarea></td>
				<td></td>
			</tr>
			<tr>
				<td>
					<strong>섹타나인</strong><br><br>
					mbrNo : 그룹아이디<br>
					vanCatId : TID 입니다.<br><br>
					1. mbrNo 값을 분할하여 사용<br>
					2. mbrNo 값을 tid 처럼 사용<br>
					3. vanCatId 값을 tid로 사용 : 단말기<br>
				</td>
				<td>
				mbrNo 값을 분할하여 사용할경우<br>
				<textarea name="cf_3" style="width:150px; height:200px;"><?php echo $config['cf_3']; ?></textarea></td>
				<td>
				mbrNo 값을 tid 처럼 사용할경우<br>
				<textarea name="cf_4" style="width:150px; height:200px;"><?php echo $config['cf_4']; ?></textarea></td>
			</tr>
		</tbody>
	</table>

	<div style="padding:10px 0;">
		<input type="submit" value="저장" class="btn_submit btn" accesskey='s'>
	</div>
</form>







<script>
	/*
	// 본사 선택
	$("#mb_1").on("change",function(){
		var mb_1 = $(this).val();
		$.ajax({
			type: 'POST',
			url: './ajax/ajax.level8.php',
			data: { mb_1 : mb_1, mb_level : "7" },
			success: function(data) {
				$("#mb_2").html(data);
			}
		});
	});

	// 지사 선택
	$("#mb_2").on("change",function(){
		var mb_2 = $(this).val();
		$.ajax({
			type: 'POST',
			url: './ajax/ajax.level7.php',
			data: { mb_2 : mb_2, mb_level : "6" },
			success: function(data) {
				$("#mb_3").html(data);
			}
		});
	});

	// 총판 선택
	$("#mb_3").on("change",function(){
		var mb_3 = $(this).val();
		$.ajax({
			type: 'POST',
			url: './ajax/ajax.level6.php',
			data: { mb_3 : mb_3, mb_level : "5" },
			success: function(data) {
				$("#mb_4").html(data);
			}
		});
	});

	// 대리점 선택
	$("#mb_4").on("change",function(){
		var mb_4 = $(this).val();
		$.ajax({
			type: 'POST',
			url: './ajax/ajax.level5.php',
			data: { mb_4 : mb_4, mb_level : "4" },
			success: function(data) {
				$("#mb_5").html(data);
			}
		});
	});

	// 영업점 선택
	$("#mb_5").on("change",function(){
		var mb_5 = $(this).val();
		$.ajax({
			type: 'POST',
			url: './ajax/ajax.level6.php',
			data: { mb_5 : mb_5, mb_level : "3" },
			success: function(data) {
				$("#mb_6").html(data);
			}
		});
	});
	*/

	/*
	$("#l7").on("change",function(){
		var l7 = $(this).val();
		$.ajax({
			type: 'POST',
			url: './ajax/ajax.level7.php',
			data: { l7 : l7, mb_type : "7" },
			success: function(data) {
				if(data) {
					$("#model, #type").html('<li>← 선택해주세요</li>').val('');
				}
				$("#l7").html(data);
			}
		});
	});
	*/

	// 수정시에 업체선택 비활성
	$(document).ready(function() {
		<?php if($member['mb_type'] >= 2) { ?>
		$("#l2").prop('disabled',true);
		<?php } ?>
		<?php if($member['mb_type'] >= 3) { ?>
		$("#l3").prop('disabled',true);
		<?php } ?>
		<?php if($member['mb_type'] >= 4) { ?>
		$("#l4").prop('disabled',true);
		<?php } ?>
		<?php if($member['mb_type'] >= 5) { ?>
		$("#l5").prop('disabled',true);
		<?php } ?>
		<?php if($member['mb_type'] >= 6) { ?>
		$("#l6").prop('disabled',true);
		<?php } ?>
	});




	// 수정버튼 클릭시 다시 활성
	$(".submitok").on("click",function(){
		$("#l2, #l3, #l4, #l5, #l6").prop('disabled',false);
	});


	// submit 최종 폼체크
	function fregisterform_submit(f)
	{
		// 회원아이디 검사
		if (f.w.value == "") {
			var msg = reg_mb_id_check();
			if (msg) {
				alert(msg);
				f.mb_id.select();
				return false;
			}
		}

		if (f.w.value == "") {
			if (f.mb_password.value.length < 3) {
				alert("비밀번호를 3글자 이상 입력하십시오.");
				f.mb_password.focus();
				return false;
			}
		}

		if (f.mb_password.value != f.mb_password_re.value) {
			alert("비밀번호가 같지 않습니다.");
			f.mb_password_re.focus();
			return false;
		}

		if (f.mb_password.value.length > 0) {
			if (f.mb_password_re.value.length < 3) {
				alert("비밀번호를 3글자 이상 입력하십시오.");
				f.mb_password_re.focus();
				return false;
			}
		}

		// 이름 검사
		if (f.w.value=="") {
			if (f.mb_name.value.length < 1) {
				alert("이름을 입력하십시오.");
				f.mb_name.focus();
				return false;
			}

			/*
			var pattern = /([^가-힣\x20])/i;
			if (pattern.test(f.mb_name.value)) {
				alert("이름은 한글로 입력하십시오.");
				f.mb_name.select();
				return false;
			}
			*/
		}

		<?php if($w == '' && $config['cf_cert_use'] && $config['cf_cert_req']) { ?>
		// 본인확인 체크
		if(f.cert_no.value=="") {
			alert("회원가입을 위해서는 본인확인을 해주셔야 합니다.");
			return false;
		}
		<?php } ?>

		// 닉네임 검사
		if ((f.w.value == "") || (f.w.value == "u" && f.mb_nick.defaultValue != f.mb_nick.value)) {
			var msg = reg_mb_nick_check();
			if (msg) {
				alert(msg);
				f.reg_mb_nick.select();
				return false;
			}
		}

		// E-mail 검사
		if ((f.w.value == "") || (f.w.value == "u" && f.mb_email.defaultValue != f.mb_email.value)) {
			var msg = reg_mb_email_check();
			if (msg) {
				alert(msg);
				f.reg_mb_email.select();
				return false;
			}
		}

		<?php if (($config['cf_use_hp'] || $config['cf_cert_hp']) && $config['cf_req_hp']) {  ?>
		// 휴대폰번호 체크
		var msg = reg_mb_hp_check();
		if (msg) {
			alert(msg);
			f.reg_mb_hp.select();
			return false;
		}
		<?php } ?>

		if (typeof f.mb_icon != "undefined") {
			if (f.mb_icon.value) {
				if (!f.mb_icon.value.toLowerCase().match(/.(gif|jpe?g|png)$/i)) {
					alert("회원아이콘이 이미지 파일이 아닙니다.");
					f.mb_icon.focus();
					return false;
				}
			}
		}

		if (typeof f.mb_img != "undefined") {
			if (f.mb_img.value) {
				if (!f.mb_img.value.toLowerCase().match(/.(gif|jpe?g|png)$/i)) {
					alert("회원이미지가 이미지 파일이 아닙니다.");
					f.mb_img.focus();
					return false;
				}
			}
		}

		if (typeof(f.mb_recommend) != "undefined" && f.mb_recommend.value) {
			if (f.mb_id.value == f.mb_recommend.value) {
				alert("본인을 추천할 수 없습니다.");
				f.mb_recommend.focus();
				return false;
			}

			var msg = reg_mb_recommend_check();
			if (msg) {
				alert(msg);
				f.mb_recommend.select();
				return false;
			}
		}

		document.getElementById("btn_submit").disabled = "disabled";

		return true;
	}
</script>


<?php
	include_once("./_tail.php");
?>