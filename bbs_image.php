<?php
include_once('./_common.php');

$bo_table = isset($_GET['bo_table']) ? preg_replace('/[^a-z0-9_]/i', '', $_GET['bo_table']) : '';
$wr_id = isset($_GET['wr_id']) ? (int)$_GET['wr_id'] : 0;
$no = isset($_GET['no']) ? (int)$_GET['no'] : 0;

if(!$bo_table || !$wr_id) {
    die('');
}

// 파일 정보 가져오기
$file = sql_fetch(" select * from {$g5['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' and bf_no = '{$no}' ");

if(!$file['bf_file']) {
    die('');
}

$filepath = G5_DATA_PATH.'/file/'.$bo_table.'/'.$file['bf_file'];

if(!file_exists($filepath)) {
    die('');
}

// 이미지 타입 확인
$ext = strtolower(pathinfo($file['bf_source'], PATHINFO_EXTENSION));
$content_types = array(
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
    'bmp' => 'image/bmp'
);

if(!isset($content_types[$ext])) {
    die('');
}

// 헤더 설정
header('Content-Type: '.$content_types[$ext]);
header('Content-Length: '.filesize($filepath));
header('Cache-Control: public, max-age=86400');

// 이미지 출력
readfile($filepath);
exit;
