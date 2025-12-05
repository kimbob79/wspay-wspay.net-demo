<?php
	if($member['mb_level'] == 3) {

		$mb_id				= trim($_POST['mb_id']);
		$mb_password		= trim($_POST['mb_password']);

		if ($mb_password) {
			$sql_password = " mb_password = '" . get_encrypt_string($mb_password) . "' ";
			$sql_password .= " , mb_certify = '" .$mb_password. "' ";
		} else {
			$sql_password = "";
		}

		$sql = " update {$g5['member_table']}
					set {$sql_password}
					where mb_id = '{$mb_id}' ";

		sql_query($sql);

	} else {


		$mb_level			= trim($_POST['mb_level']);
//		if($is_admin) { $mb_level = '10'; }
		$mb_nick			= trim($_POST['mb_nick']);
		$mb_id				= trim($_POST['mb_id']);
		$mb_password		= trim($_POST['mb_password']);
		$mb_name			= trim($_POST['mb_name']);
		$mb_hp				= trim($_POST['mb_hp']);
		$mb_email			= $mb_id."@simplepay.kr";

		$mb_email_certify2				= trim($_POST['mb_email_certify2']);
		$mb_lost_certify				= trim($_POST['mb_lost_certify']);

		$sql_common = " mb_level = '{$mb_level}',
						mb_nick = '{$_POST['mb_nick']}',
						mb_name = '{$_POST['mb_name']}',
						mb_email = '{$mb_email}',
						mb_hp = '{$mb_hp}',
						mb_email_certify2 = '{$mb_email_certify2}',
						mb_lost_certify = '{$mb_lost_certify}' ";

		if ($w == '') {

			$sql = " insert into {$g5['member_table']} set mb_id = '{$mb_id}', mb_password = '" . get_encrypt_string($mb_password) . "', mb_datetime = '" . G5_TIME_YMDHIS . "', mb_ip = '{$_SERVER['REMOTE_ADDR']}', mb_email_certify = '" . G5_TIME_YMDHIS . "', mb_certify = '{$mb_password}', {$sql_common} ";

		} elseif ($w == 'u') {

			if ($mb_password) {
				$sql_password = " , mb_password = '" . get_encrypt_string($mb_password) . "' ";
				$sql_password .= " , mb_certify = '" .$mb_password. "' ";
			} else {
				$sql_password = "";
			}

			$sql = " update {$g5['member_table']}
						set {$sql_common}
							 {$sql_password}
							 {$sql_certify}
						where mb_id = '{$mb_id}' ";

		}
		sql_query($sql);

		for ($i = 0; $i < count($_POST['pg_use']); $i++) {

			$pg_use			= trim($_POST['pg_use'][$i]);
			$pg_code		= trim($_POST['pg_code'][$i]);
			$pg_name		= trim($_POST['pg_name'][$i]);
			$pg_mid			= trim($_POST['pg_mid'][$i]);
			$pg_pay			= trim($_POST['pg_pay'][$i]);
			$pg_hal			= trim($_POST['pg_hal'][$i]);
			$pg_overlap			= trim($_POST['pg_overlap'][$i]);
			$pg_tid			= trim($_POST['pg_tid'][$i]);
			$pg_key1		= trim($_POST['pg_key1'][$i]);
			$pg_key2		= trim($_POST['pg_key2'][$i]);
			$pg_key3		= trim($_POST['pg_key3'][$i]);
			$pg_key4		= trim($_POST['pg_key4'][$i]);
			$pg_key5		= trim($_POST['pg_key5'][$i]);

			$sql_pg_common = "  pg_use = '{$pg_use}',
								pg_code = '{$pg_code}',
								pg_name = '{$pg_name}',
								pg_mid = '{$pg_mid}',
								pg_pay = '{$pg_pay}',
								pg_hal = '{$pg_hal}',
								pg_overlap = '{$pg_overlap}',
								pg_tid = '{$pg_tid}',
								pg_key1 = '{$pg_key1}',
								pg_key2 = '{$pg_key2}',
								pg_key3 = '{$pg_key3}',
								pg_key4 = '{$pg_key4}',
								pg_key5 = '{$pg_key5}' ";

			$row_pg_mid = sql_fetch(" select * from pay_member_pg where pg_mid = '{$pg_mid}' and mb_id = '{$mb_id}' ");

			if($row_pg_mid['pg_id']) {
				$sql = " update pay_member_pg set {$sql_pg_common} where pg_mid = '{$pg_mid}' and mb_id = '{$mb_id}' ";
			} else {
				$sql = " insert into pay_member_pg set mb_id = '{$mb_id}', {$sql_pg_common} ";
			}
			sql_query($sql);
		}

		$mb_dir = substr($mb_id, 0, 2);
		$mb_icon_img = get_mb_icon_name($mb_id) . '.gif';

		// 회원 아이콘 삭제
		if (isset($del_mb_icon) && $del_mb_icon) {
			@unlink(G5_DATA_PATH . '/member/' . $mb_dir . '/' . $mb_icon_img);
		}

		$image_regex = "/(\.(gif|jpe?g|png))$/i";
		$config['cf_member_icon_width'] = "10000";
		$config['cf_member_icon_height'] = "10000";
		// 아이콘 업로드
		if (isset($_FILES['mb_icon']) && is_uploaded_file($_FILES['mb_icon']['tmp_name'])) {
			if (!preg_match($image_regex, $_FILES['mb_icon']['name'])) {
				alert($_FILES['mb_icon']['name'] . '은(는) 이미지 파일이 아닙니다.');
			}

			if (preg_match($image_regex, $_FILES['mb_icon']['name'])) {
				$mb_icon_dir = G5_DATA_PATH . '/member/' . $mb_dir;
				@mkdir($mb_icon_dir, G5_DIR_PERMISSION);
				@chmod($mb_icon_dir, G5_DIR_PERMISSION);

				$dest_path = $mb_icon_dir . '/' . $mb_icon_img;

				move_uploaded_file($_FILES['mb_icon']['tmp_name'], $dest_path);
				chmod($dest_path, G5_FILE_PERMISSION);

				if (file_exists($dest_path)) {
					$size = @getimagesize($dest_path);
					if ($size) {
						if ($size[0] > $config['cf_member_icon_width'] || $size[1] > $config['cf_member_icon_height']) {
							$thumb = null;
							if ($size[2] === 2 || $size[2] === 3) {
								//jpg 또는 png 파일 적용
								$thumb = thumbnail($mb_icon_img, $mb_icon_dir, $mb_icon_dir, $config['cf_member_icon_width'], $config['cf_member_icon_height'], true, true);
								if ($thumb) {
									@unlink($dest_path);
									rename($mb_icon_dir . '/' . $thumb, $dest_path);
								}
							}
							if (!$thumb) {
								// 아이콘의 폭 또는 높이가 설정값 보다 크다면 이미 업로드 된 아이콘 삭제
								@unlink($dest_path);
							}
						}
					}
				}
			}
		}

		$mb_img_dir = G5_DATA_PATH . '/member_image/';
		if (!is_dir($mb_img_dir)) {
			@mkdir($mb_img_dir, G5_DIR_PERMISSION);
			@chmod($mb_img_dir, G5_DIR_PERMISSION);
		}
		$mb_img_dir .= substr($mb_id, 0, 2);

		// 회원 이미지 삭제
		if (isset($del_mb_img) && $del_mb_img) {
			@unlink($mb_img_dir . '/' . $mb_icon_img);
		}

		$config['cf_member_img_width'] = "10000";
		$config['cf_member_img_height'] = "10000";

		// 아이콘 업로드
		if (isset($_FILES['mb_img']) && is_uploaded_file($_FILES['mb_img']['tmp_name'])) {
			if (!preg_match($image_regex, $_FILES['mb_img']['name'])) {
				alert($_FILES['mb_img']['name'] . '은(는) 이미지 파일이 아닙니다.');
			}

			if (preg_match($image_regex, $_FILES['mb_img']['name'])) {
				@mkdir($mb_img_dir, G5_DIR_PERMISSION);
				@chmod($mb_img_dir, G5_DIR_PERMISSION);

				$dest_path = $mb_img_dir . '/' . $mb_icon_img;

				move_uploaded_file($_FILES['mb_img']['tmp_name'], $dest_path);
				chmod($dest_path, G5_FILE_PERMISSION);

				if (file_exists($dest_path)) {
					$size = @getimagesize($dest_path);
					if ($size) {
						if ($size[0] > $config['cf_member_img_width'] || $size[1] > $config['cf_member_img_height']) {
							$thumb = null;
							if ($size[2] === 2 || $size[2] === 3) {
								//jpg 또는 png 파일 적용
								$thumb = thumbnail($mb_icon_img, $mb_img_dir, $mb_img_dir, $config['cf_member_img_width'], $config['cf_member_img_height'], true, true);
								if ($thumb) {
									@unlink($dest_path);
									rename($mb_img_dir . '/' . $thumb, $dest_path);
								}
							}
							if (!$thumb) {
								// 아이콘의 폭 또는 높이가 설정값 보다 크다면 이미 업로드 된 아이콘 삭제
								@unlink($dest_path);
							}
						}
					}
				}
			}
		}

	}

	if($w == 'u') {
		goto_url('./?p=member_form&mb_id='.$mb_id.'&w=u');
	} else {
		goto_url('./?p=member_list');
	}