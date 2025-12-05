<?php
	error_reporting( E_ALL );
	ini_set( "display_errors", 1 );
// SFTP 서버 연결 정보
$sftp_host = '211.43.193.75';
$sftp_port = 22;
$sftp_user = 's8648801755';
$sftp_pass = 's8648801755';

// 로컬 파일과 원격 파일 경로
$local_file = '/path/to/local/file.txt';
$remote_file = '/path/to/remote/file.txt';

// SSH 연결
$connection = ssh2_connect($sftp_host, $sftp_port);
if (!$connection) {
    die('Connection failed');
}

// 인증
if (!ssh2_auth_password($connection, $sftp_user, $sftp_pass)) {
    die('Authentication failed');
}

// SFTP 세션 시작
$sftp = ssh2_sftp($connection);
if (!$sftp) {
    die('SFTP session startup failed');
}

// 파일 업로드
$sftp_stream = fopen("ssh2.sftp://$sftp$remote_file", 'w');
if (!$sftp_stream) {
    die('Could not open remote file: ' . $remote_file);
}

$data_to_send = file_get_contents($local_file);
if ($data_to_send === false) {
    die('Could not open local file: ' . $local_file);
}

if (fwrite($sftp_stream, $data_to_send) === false) {
    die('Could not send data from local file: ' . $local_file);
}

fclose($sftp_stream);
echo 'File uploaded successfully';
?>