<?php
	include_once('./_common.php');

	/* 기존아이디로 로그인 */
	if(get_session('ss_admin_mb_id')){
		$mb = get_member(get_session('ss_admin_mb_id'));
		if(get_session('ss_mb_id') && get_session('ss_admin_mb_id')!= get_session('ss_mb_id')){
			set_session('ss_mb_id', get_session('ss_admin_mb_id'));
			set_session('ss_mb_key', get_session('ss_user_mb_key'));
			if(function_exists('update_auth_session_token')) update_auth_session_token($mb['mb_datetime']);
			alert('기존 아이디로 로그인합니다.', './?p='.$url);
			exit;
		}
	}
	/* 기존아이디로 로그인 */


	if(function_exists('social_provider_logout')){
		social_provider_logout();
	}

	// 이호경님 제안 코드
	session_unset(); // 모든 세션변수를 언레지스터 시켜줌
	session_destroy(); // 세션해제함

	// 자동로그인 해제 --------------------------------
	set_cookie('ck_mb_id', '', 0);
	set_cookie('ck_auto', '', 0);
	// 자동로그인 해제 end --------------------------------

	goto_url("./");