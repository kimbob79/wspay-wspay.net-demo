<?php
include_once('./_common.php');

$bo_table = isset($_GET['bo_table']) ? preg_replace('/[^a-z0-9_]/i', '', $_GET['bo_table']) : '';
$wr_id = isset($_GET['wr_id']) ? (int)$_GET['wr_id'] : 0;
$no = isset($_GET['no']) ? (int)$_GET['no'] : 0;

if(!$bo_table || !$wr_id) {
    die('잘못된 접근입니다.');
}

// 파일 정보 가져오기
$file = sql_fetch(" select * from {$g5['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' and bf_no = '{$no}' ");

if(!$file['bf_file']) {
    die('파일이 존재하지 않습니다.');
}

$filepath = G5_DATA_PATH.'/file/'.$bo_table.'/'.$file['bf_file'];

if(!file_exists($filepath)) {
    die('파일을 찾을 수 없습니다.');
}

// 다운로드 카운트 증가
sql_query(" update {$g5['board_file_table']} set bf_download = bf_download + 1 where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' and bf_no = '{$no}' ");

// 파일명 인코딩
$filename = $file['bf_source'];
$filename = str_replace(' ', '_', $filename);

// 브라우저별 파일명 처리
if(preg_match('/MSIE|Trident|Edge/i', $_SERVER['HTTP_USER_AGENT'])) {
    $filename = urlencode($filename);
    $filename = str_replace('+', '%20', $filename);
} else {
    $filename = '"'.$filename.'"';
}

// 헤더 설정
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='.$filename);
header('Content-Transfer-Encoding: binary');
header('Content-Length: '.filesize($filepath));
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Expires: 0');

// 파일 출력
readfile($filepath);
exit;
