<?php
include_once('./_common.php');
include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

//print_r2($_POST); exit;

/*
if (!$is_member && $config['cf_formmail_is_member'])
    alert_close('회원만 이용하실 수 있습니다.');
*/

if (!chk_captcha()) {
    alert('자동등록방지 숫자가 틀렸습니다.');
}

$fnick   = $_POST['con_name'];
$fmail   = $_POST['con_email'];
//$to      = $admin['mb_3'];
$to      = $config['cf_admin_email'];
$subject = $config['cf_title'].' CONTACT';
$content = "이름 : ".$_POST['con_name']."\n연락처 : ".$_POST['con_tel']."\n보낸날 : ".G5_TIME_YMDHIS."\n\n내용 : \n".$_POST['con_message'];
$type    = 2;

$file = array();
for ($i=1; $i<=$attach; $i++) {
    if ($_FILES['file'.$i]['name'])
        $file[] = attach_file($_FILES['file'.$i]['name'], $_FILES['file'.$i]['tmp_name']);
}

$content = stripslashes($content);
if ($type == 2) {
    $type = 1;
    $content = str_replace("\n", "<br>", $content);
}

// html 이면
if ($type) {
    $current_url = G5_URL;
    $mail_content = '<!doctype html><html lang="ko"><head><meta charset="utf-8"><title>메일보내기</title><link rel="stylesheet" href="'.$current_url.'/style.css"></head><body>'.$content.'</body></html>';
}
else
    $mail_content = $content;

mailer($fnick, $fmail, $to, $subject, $mail_content, $type, $file);

// 임시 첨부파일 삭제
if(!empty($file)) {
    foreach($file as $f) {
        @unlink($f['path']);
    }
}

//$html_title = $tmp_to . "님께 메일발송";
$html_title = '메일 발송중';
include_once(G5_PATH.'/head.sub.php');

alert('메일을 정상적으로 발송하였습니다.', G5_URL);

include_once(G5_PATH.'/tail.sub.php');
?>