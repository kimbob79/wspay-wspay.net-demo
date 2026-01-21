<?php
	if(!$is_admin) { alert("관리자만 접속 가능합니다."); }
  
	$cf_1_subj = trim($_POST['cf_1_subj']);
	$cf_2_subj = trim($_POST['cf_2_subj']);
	$cf_3_subj = trim($_POST['cf_3_subj']);
	$cf_4_subj = trim($_POST['cf_4_subj']);
	$cf_5_subj = trim($_POST['cf_5_subj']);
	$cf_6_subj = trim($_POST['cf_6_subj']);
	$cf_7_subj = trim($_POST['cf_7_subj']);
	$cf_8_subj = trim($_POST['cf_8_subj']);
	$cf_9_subj = trim($_POST['cf_9_subj']);
	$cf_10_subj = trim($_POST['cf_10_subj']);
	$cf_11_subj = trim($_POST['cf_11_subj']);
	$cf_12_subj = trim($_POST['cf_12_subj']);
	$cf_13_subj = trim($_POST['cf_13_subj']);

	$cf_1    = trim($_POST['cf_1']);
	$cf_2    = trim($_POST['cf_2']);
	$cf_3    = trim($_POST['cf_3']);
	$cf_4    = trim($_POST['cf_4']);
	$cf_5    = trim($_POST['cf_5']);
	$cf_6    = trim($_POST['cf_6']);
	$cf_7    = trim($_POST['cf_7']);
	$cf_8    = trim($_POST['cf_8']);
	$cf_9    = trim($_POST['cf_9']);
	$cf_10    = trim($_POST['cf_10']);
	$cf_11    = trim($_POST['cf_11']);
	$cf_12    = trim($_POST['cf_12']);
	$cf_13    = trim($_POST['cf_13']);

	$sql = " update {$g5['config_table']}
				set cf_1_subj = '{$cf_1_subj}',
					cf_2_subj = '{$cf_2_subj}',
					cf_3_subj = '{$cf_3_subj}',
					cf_4_subj = '{$cf_4_subj}',
					cf_5_subj = '{$cf_5_subj}',
					cf_6_subj = '{$cf_6_subj}',
					cf_7_subj = '{$cf_7_subj}',
					cf_8_subj = '{$cf_8_subj}',
					cf_9_subj = '{$cf_9_subj}',
					cf_10_subj = '{$cf_10_subj}',
					cf_11_subj = '{$cf_11_subj}',
					cf_12_subj = '{$cf_12_subj}',
					cf_13_subj = '{$cf_13_subj}',
					cf_1 = '{$cf_1}',
					cf_2 = '{$cf_2}',
					cf_3 = '{$cf_3}',
					cf_4 = '{$cf_4}',
					cf_5 = '{$cf_5}',
					cf_6 = '{$cf_6}',
					cf_7 = '{$cf_7}',
					cf_8 = '{$cf_8}',
					cf_9 = '{$cf_9}',
					cf_10 = '{$cf_10}',
					cf_11 = '{$cf_11}',
					cf_12 = '{$cf_12}',
					cf_13 = '{$cf_13}' ";

	sql_query($sql);

	goto_url('./?p=adm_tid', false);
?>