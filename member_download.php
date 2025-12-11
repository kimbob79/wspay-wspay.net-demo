<?php
include_once('./_common.php');

// 파라미터 받기
$mb_id = isset($_GET['mb_id']) ? clean_xss_tags($_GET['mb_id']) : '';
$bf_no = isset($_GET['no']) ? (int)$_GET['no'] : 0;

// 로그인 체크
if (!$member['mb_id']) {
    alert('로그인이 필요합니다.');
}

// 파라미터 검증
if (!$mb_id || $bf_no < 0) {
    alert('잘못된 접근입니다.');
}

// 권한 체크 - 관리자이거나 본인의 파일만 다운로드 가능
if (!$is_admin && $member['mb_id'] != $mb_id) {
    // 하위 회원인지 체크
    $mb_check = get_member($mb_id);

    $allow = false;
    if ($member['mb_level'] >= 8 && $mb_check['mb_1'] == $member['mb_id']) {
        $allow = true;
    } else if ($member['mb_level'] >= 7 && $mb_check['mb_2'] == $member['mb_id']) {
        $allow = true;
    } else if ($member['mb_level'] >= 6 && $mb_check['mb_3'] == $member['mb_id']) {
        $allow = true;
    } else if ($member['mb_level'] >= 5 && $mb_check['mb_4'] == $member['mb_id']) {
        $allow = true;
    } else if ($member['mb_level'] >= 4 && $mb_check['mb_5'] == $member['mb_id']) {
        $allow = true;
    }

    if (!$allow) {
        alert('다운로드 권한이 없습니다.');
    }
}

// 파일 정보 조회
$sql = " select * from g5_member_file where mb_id = '$mb_id' and bf_no = '$bf_no' ";
$file = sql_fetch($sql);

if (!$file['bf_file']) {
    alert('파일이 존재하지 않습니다.');
}

// 파일 경로
$filepath = G5_DATA_PATH.'/member/'.$mb_id.'/'.$file['bf_file'];

if (!file_exists($filepath)) {
    alert('파일을 찾을 수 없습니다.');
}

// inline 파라미터 확인 (이미지 미리보기용)
$inline = isset($_GET['inline']) ? $_GET['inline'] : '';

// inline이 아닐 때만 다운로드 카운트 증가
if (!$inline) {
    $sql = " update g5_member_file set bf_download = bf_download + 1 where mb_id = '$mb_id' and bf_no = '$bf_no' ";
    sql_query($sql);
}

// 파일 다운로드 또는 inline 표시
$filename = $file['bf_source'];
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

// inline 모드이고 이미지 파일인 경우
if ($inline && in_array($ext, array('gif', 'jpg', 'jpeg', 'png', 'bmp', 'webp'))) {
    $mime_types = array(
        'gif' => 'image/gif',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'bmp' => 'image/bmp',
        'webp' => 'image/webp'
    );
    header("Content-Type: " . $mime_types[$ext]);
    header("Content-Disposition: inline; filename=\"$filename\"");
} else {
    // 일반 다운로드
    if (preg_match("/msie/i", $_SERVER['HTTP_USER_AGENT']) && preg_match("/5\.5/", $_SERVER['HTTP_USER_AGENT'])) {
        header("Content-Type: doesn/matter");
        header("Content-Disposition: filename=$filename");
        header("Content-Transfer-Encoding: binary");
    } else {
        header("Content-Type: file/unknown");
        header("Content-Disposition: attachment; filename=\"$filename\"");
    }
}

header("Pragma: no-cache");
header("Expires: 0");

// 파일 읽어서 출력
if (is_file($filepath)) {
    $fp = fopen($filepath, 'rb');

    // 파일 내용 출력
    if (!fpassthru($fp)) {
        fclose($fp);
    }
} else {
    alert('파일을 읽을 수 없습니다.');
}
?>
