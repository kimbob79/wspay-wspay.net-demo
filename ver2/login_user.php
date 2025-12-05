<?php

/* 로그인 기능 추가 */
if($is_admin){
	$mb = get_member($mb_id);
	/*
	if($member['mb_type'] == '2') { // PG일경우
		if($mb['mb_pid2'] != $member['mb_id']) {
			alert("해당 회원은 존재하지 않거나 하위그룹이 아닙니다.");
		}
	} else if($member['mb_type'] == '3') { // 본사일경우
		if($mb['mb_pid3'] != $member['mb_id']) {
			alert("해당 회원은 존재하지 않거나 하위그룹이 아닙니다.");
		}
	}
	*/

	if($mb){
		set_session('ss_user_mb_id', get_session('ss_mb_id'));
		set_session('ss_user_mb_key', get_session('ss_mb_key'));
		set_session('ss_user_redir', $PHP_SELF);

		set_session('ss_mb_id', $mb['mb_id']);
		set_session('ss_mb_key', md5($mb['mb_datetime'] . get_real_client_ip() . $_SERVER['HTTP_USER_AGENT']));
		if(function_exists('update_auth_session_token')) update_auth_session_token($mb['mb_datetime']);

		alert($mb['mb_id']." 아이디로 로그인합니다.", './?p='.$url);
	}
	else {
		alert('존재하지 않는 회원입니다.');
	}
	exit;
}
/* 로그인 기능 추가 */

?>