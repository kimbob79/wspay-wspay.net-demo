<?php
//	if($error == "y") {
		error_reporting(E_ALL);
		ini_set("display_errors", 1);
//	}

	include_once('./_common.php');

	if($admin_login == "login"){
		echo "a";
		$mb = get_member($login_us_id);
		if($mb){
			//관리자 정보 저장하기
			set_session('ss_admin_mb_id', get_session('ss_mb_id'));
			set_session('ss_admin_mb_key', get_session('ss_mb_key'));
			set_session('ss_admin_redir', $PHP_SELF);

			// 회원아이디 세션 생성
			set_session('ss_mb_id', $mb['mb_id']);
			// FLASH XSS 공격에 대응하기 위하여 회원의 고유키를 생성해 놓는다. 관리자에서 검사함 - 110106
			set_session('ss_mb_key', md5($mb['mb_datetime'] . get_real_client_ip() . $_SERVER['HTTP_USER_AGENT']));
			if(function_exists('update_auth_session_token')) update_auth_session_token($mb['mb_datetime']);

			alert($mb['mb_nick']." 아이디로 로그인합니다.", "./");
		}
		else {
			alert('존재하지 않는 회원입니다.');
		}
		exit;
	}


	/*
	if($cssname) {
		set_cookie('cssname', $cssname, 86400*365);
		alert($cssname." 모드로 변경");
	}
	*/
	
	$cssnames = get_cookie('cssname');
	
//	echo "<span style='color:#fff'>".$member['mb_id']."_테스트</span>".time();

	if($member['mb_level'] < 3) {

		include_once("./login.php");

	} else {

		if(!$fr_date) { $fr_date = date("Y-m-d"); }
		if(!$to_date) { $to_date = date("Y-m-d"); }

		function format_phone($phone) {
			$phone = preg_replace("/[^0-9]/", "", $phone);
			$length = strlen($phone);
			switch($length){
				case 11 :
					return preg_replace("/([0-9]{3})([0-9]{4})([0-9]{4})/", "$1-$2-$3", $phone);
					break;
				case 10:
					return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1-$2-$3", $phone);
					break;
				default :
					return $phone;
					break;
			}
		}

		function format_number($number) {
			$number = str_replace('-', '', $number);
			if (preg_match("/([0-9]{4})([0-9]{4})([0-9]{4})([0-9]{4})/", $number)) { // 카드번호
				return preg_replace("/([0-9]{4})([0-9]{4})([0-9]{4})([0-9]{4})/", "\\1-\\2-\\3-\\4", $number);
			}
			else if (preg_match("/([0-9]{3})([0-9]{4})([0-9]{4})/", $number)) { // 휴대폰번호
				return preg_replace("/([0-9]{3})([0-9]{4})([0-9]{1,4})/", "\\1-\\2-\\3", $number);
			}
			else if (preg_match("/(0[0-9]{1,2})([0-9]{3})([0-9]{4})/", $number)) { // 일반번호
				return preg_replace("/([0-9]{2,3})([0-9]{3})([0-9]{4})/", "\\1-\\2-\\3", $number);
			}
			else if (preg_match("/([0-9]{3})([0-9]{2})([0-9]{5})/", $number)) { // 사업자번호
				return preg_replace("/([0-9]{3})([0-9]{2})([0-9]{5})/", "\\1-\\2-\\3", $number);
			}
			else {
				return $number;
			}
		}

		/*
		if(!$p && $is_admin) {
//			$p = "member_table";
			$p = "main";
		} else {
			if(!$p || $p == "main") {
				$p = "main";
			}
		}
		*/

		if(!$p || $p == "list") {
			$p = "list";
		}

	//	echo $p;

		include_once("./_head.php");
		include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
		if($t) {
			$write_table = $g5['write_prefix'] . $t; // 게시판 테이블 전체이름
		}
		if(is_file("./".$p.".php")){
			include_once("./".$p.".php");
		} else {
			echo "<div style='height:500px; text-align:center; line-height:500px'>잘못된 접근입니다</div>";
		}
		include_once("./_foot.php");
	}
?>