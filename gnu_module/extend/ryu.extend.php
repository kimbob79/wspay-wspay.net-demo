<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가;



define('G5_URL_NEW', '/make/');

// 한페이지에 보여줄 행, 현재페이지, 총페이지수, URL
function get_paging_newz($write_pages, $cur_page, $total_page, $p, $url, $add="")
{
    //$url = preg_replace('#&amp;page=[0-9]*(&amp;page=)$#', '$1', $url);
    $url = preg_replace('#(&amp;)?page=[0-9]*#', '', $url);
	$url .= substr($url, -1) === '?' ? 'p=' : '&amp;p='.$p;
	$url .= substr($url, -1) === '?' ? 'page=' : '&amp;page=';

    $str = '';
    if ($cur_page > 1) {
        $str .= '<a href="'.$url.'1'.$add.'" class="button"><span class="mdi mdi-chevron-double-left"></span></a>'.PHP_EOL;
    }

    $start_page = ( ( (int)( ($cur_page - 1 ) / $write_pages ) ) * $write_pages ) + 1;
    $end_page = $start_page + $write_pages - 1;

    if ($end_page >= $total_page) $end_page = $total_page;

    if ($start_page > 1) $str .= '<a href="'.$url.($start_page-1).$add.'" class="button"><span class="mdi mdi-chevron-left"></span></a>'.PHP_EOL;

    if ($total_page > 1) {
        for ($k=$start_page;$k<=$end_page;$k++) {
            if ($cur_page != $k)
                $str .= '<a href="'.$url.$k.$add.'" class="button">'.$k.'</a>'.PHP_EOL;
            else
                $str .= '<a href="'.$url.$k.$add.'" class="button is-active">'.$k.'</a>'.PHP_EOL;
//                $str .= '<span class="sound_only">열린</span><strong class="pg_current">'.$k.'</strong><span class="sound_only">페이지</span>'.PHP_EOL;
        }
    }

    if ($total_page > $end_page) $str .= '<a href="'.$url.($end_page+1).$add.'" class="button"><span class="mdi mdi-chevron-right"></span></a>'.PHP_EOL;

    if ($cur_page < $total_page) {
        $str .= '<a href="'.$url.$total_page.$add.'" class="button"><span class="mdi mdi-chevron-double-right"></span></a>'.PHP_EOL;
    }

    if ($str)
        return '<div class="notification"><div class="level"><div class="level-left"><div class="level-item"><div class="buttons has-addons">'.$str.'</div></div></div><div class="level-right"><div class="level-item"><small>Page '.$cur_page.' of '.$total_page.'</small></div></div></div></div>';
    else
        return '';
}




// 한페이지에 보여줄 행, 현재페이지, 총페이지수, URL
function get_paging_news($write_pages, $cur_page, $total_page, $url, $add="")
{
    //$url = preg_replace('#&amp;page=[0-9]*(&amp;page=)$#', '$1', $url);
    $url = preg_replace('#(&amp;)?page=[0-9]*#', '', $url);
	$url .= substr($url, -1) === '?' ? 'page=' : '&amp;page=';

    $str = '';
    if ($cur_page > 1) {
        $str .= '<a href="'.$url.'1'.$add.'" class="arrow pprev"></a>'.PHP_EOL;
    }

    $start_page = ( ( (int)( ($cur_page - 1 ) / $write_pages ) ) * $write_pages ) + 1;
    $end_page = $start_page + $write_pages - 1;

    if ($end_page >= $total_page) $end_page = $total_page;

    if ($start_page > 1) $str .= '<a href="'.$url.($start_page-1).$add.'" class="arrow prev"></a>'.PHP_EOL;

    if ($total_page > 1) {
        for ($k=$start_page;$k<=$end_page;$k++) {
            if ($cur_page != $k)
                $str .= '<a href="'.$url.$k.$add.'">'.$k.'</a>'.PHP_EOL;
            else
                $str .= '<a href="'.$url.$k.$add.'" class="active">'.$k.'</a>'.PHP_EOL;
//                $str .= '<span class="sound_only">열린</span><strong class="pg_current">'.$k.'</strong><span class="sound_only">페이지</span>'.PHP_EOL;
        }
    }

    if ($total_page > $end_page) $str .= '<a href="'.$url.($end_page+1).$add.'" class="arrow next"></a>'.PHP_EOL;

    if ($cur_page < $total_page) {
        $str .= '<a href="'.$url.$total_page.$add.'" class="arrow nnext"></a>'.PHP_EOL;
    }

    if ($str)
        return '<div class="page_wrap"><div class="page_nation">'.$str.'</div></div>';
    else
        return '';
}



// 게시판 첨부파일 썸네일 삭제
function delete_member_thumbnail($mb_id, $file)
{
    if(!$mb_id || !$file)
        return;

    $fn = preg_replace("/\.[^\.]+$/i", "", basename($file));
    $files = glob(G5_DATA_PATH.'/member/'.$mb_id.'/thumb-'.$fn.'*');
    if (is_array($files)) {
        foreach ($files as $filename)
            unlink($filename);
    }
}


function get_member_filesize($size)
{
    //$size = @filesize(addslashes($file));
    if ($size >= 1048576) {
        $size = number_format($size/1048576, 1) . "M";
    } else if ($size >= 1024) {
        $size = number_format($size/1024, 1) . "K";
    } else {
        $size = number_format($size, 0) . "byte";
    }
    return $size;
}




// 게시글에 첨부된 파일을 얻는다. (배열로 반환)
function get_member_file($mb_id)
{
    global $g5, $qstr, $board;

    $file['count'] = 0;
    $sql = " select * from g5_member_file where mb_id = '$mb_id' order by bf_no ";
    $result = sql_query($sql);
    while ($row = sql_fetch_array($result))
    {
        $no = (int) $row['bf_no'];
        $bf_content = $row['bf_content'] ? html_purifier($row['bf_content']) : '';
        // member_download.php는 웹 루트에 있음
        $file[$no]['href'] = '/member_download.php?mb_id='.$mb_id.'&no='.$no;
        $file[$no]['download'] = $row['bf_download'];
        // 4.00.11 - 파일 path 추가
        $file[$no]['path'] = G5_DATA_URL.'/member/'.$mb_id;
        // 웹 루트 기준 상대 경로로 수정
        $file[$no]['direct_url'] = '/gnu_module/data/member/'.$mb_id.'/'.$row['bf_file'];
        $file[$no]['size'] = get_filesize($row['bf_filesize']);
        $file[$no]['datetime'] = $row['bf_datetime'];
        $file[$no]['source'] = addslashes($row['bf_source']);
        $file[$no]['bf_content'] = $bf_content;
        $file[$no]['content'] = get_text($bf_content);
        //$file[$no]['view'] = view_file_link($row['bf_file'], $file[$no]['content']);
        $file[$no]['view'] = view_file_link($row['bf_file'], $row['bf_width'], $row['bf_height'], $file[$no]['content']);
        $file[$no]['file'] = $row['bf_file'];
        $file[$no]['image_width'] = $row['bf_width'] ? $row['bf_width'] : 640;
        $file[$no]['image_height'] = $row['bf_height'] ? $row['bf_height'] : 480;
        $file[$no]['image_type'] = $row['bf_type'];
        $file[$no]['bf_fileurl'] = $row['bf_fileurl'];
        $file[$no]['bf_thumburl'] = $row['bf_thumburl'];
        $file[$no]['bf_storage'] = $row['bf_storage'];
        $file['count']++;
    }

    return run_replace('get_files', $file, 'member', $mb_id);
}

// 파일명 치환
function replace_filename2($name)
{
    @session_start();
    $ss_id = session_id();
    $usec = get_microtime();
    $file_path = pathinfo($name);
    $ext = $file_path['extension'];
    $return_filename = sha1($ss_id.$_SERVER['REMOTE_ADDR'].$usec); 
    if( $ext )
        $return_filename .= '.'.$ext;

    return $return_filename;
}

/*
// 게시글에 첨부된 파일을 얻는다. (배열로 반환)
function get_member_file($wr_id)
{
    global $g5, $qstr, $board;

    $file['count'] = 0;
    $sql = " select * from g5_member_file_old where bo_table = 'member' and wr_id = '$wr_id' order by bf_no ";
    $result = sql_query($sql);
    while ($row = sql_fetch_array($result))
    {
        $no = (int) $row['bf_no'];
        $bf_content = $row['bf_content'] ? html_purifier($row['bf_content']) : '';
        $file[$no]['href'] = G5_BBS_URL."/download.php?bo_table=$bo_table&amp;wr_id=$wr_id&amp;no=$no" . $qstr;
        $file[$no]['download'] = $row['bf_download'];
        // 4.00.11 - 파일 path 추가
        $file[$no]['path'] = G5_DATA_URL.'/member/'.$bo_table;
        $file[$no]['size'] = get_filesize($row['bf_filesize']);
        $file[$no]['datetime'] = $row['bf_datetime'];
        $file[$no]['source'] = addslashes($row['bf_source']);
        $file[$no]['bf_content'] = $bf_content;
        $file[$no]['content'] = get_text($bf_content);
        //$file[$no]['view'] = view_file_link($row['bf_file'], $file[$no]['content']);
        $file[$no]['view'] = view_file_link($row['bf_file'], $row['bf_width'], $row['bf_height'], $file[$no]['content']);
        $file[$no]['file'] = $row['bf_file'];
        $file[$no]['image_width'] = $row['bf_width'] ? $row['bf_width'] : 640;
        $file[$no]['image_height'] = $row['bf_height'] ? $row['bf_height'] : 480;
        $file[$no]['image_type'] = $row['bf_type'];
        $file[$no]['bf_fileurl'] = $row['bf_fileurl'];
        $file[$no]['bf_thumburl'] = $row['bf_thumburl'];
        $file[$no]['bf_storage'] = $row['bf_storage'];
        $file['count']++;
    }

    return run_replace('get_files', $file, $bo_table, $wr_id);
}
*/


function format_tel($tel) {
	// 숫자 외 문자 제거
	$tel = preg_replace('/[^0-9]/', '', $tel);
	// 하이픈 추가하여 리턴
	return preg_replace('/(^02.{0}|^01.{1}|^15.{2}|^16.{2}|^18.{2}|[0-9]{3})([0-9]+)([0-9]{4})/', '$1-$2-$3', $tel);
}


// sql_query()함수는 union이 사용이 불가능하여 새로운 함수로 대처 (gnuwiz)
function union_sql_query($sql, $error=G5_DISPLAY_SQL_ERROR, $link=null)
{
	global $g5;
	if(!$link)
		$link = $g5['connect_db'];
	// Blind SQL Injection 취약점 해결
	$sql = trim($sql);
	$sql = preg_replace("#^select.*from.*where.*`?information_schema`?.*#i", "select 1", $sql);
	if(function_exists('mysqli_query') && G5_MYSQLI_USE) {
		if ($error) {
			$result = @mysqli_query($link, $sql) or die("<p>$sql<p>" . mysqli_errno($link) . " : " .  mysqli_error($link) . "<p>error file : {$_SERVER['SCRIPT_NAME']}");
		} else {
			$result = @mysqli_query($link, $sql);
		}
	} else {
		if ($error) {
			$result = @mysql_query($sql, $link) or die("<p>$sql<p>" . mysql_errno() . " : " .  mysql_error() . "<p>error file : {$_SERVER['SCRIPT_NAME']}");
		} else {
			$result = @mysql_query($sql, $link);
		}
	}
	return $result;
}

// 쿼리를 실행한 후 결과값에서 한행을 얻는다.
function union_sql_fetch($sql, $error=G5_DISPLAY_SQL_ERROR, $link=null)
{
    global $g5;

    if(!$link)
        $link = $g5['connect_db'];

    $result = union_sql_query($sql, $error, $link);
    //$row = @sql_fetch_array($result) or die("<p>$sql<p>" . mysqli_errno() . " : " .  mysqli_error() . "<p>error file : $_SERVER['SCRIPT_NAME']");
    $row = sql_fetch_array($result);
    return $row;
}


// 회원 정보를 얻는다.
function get_member_new($mb_id, $fields='*', $is_cache=false)
{
    global $g5;
    
    if (preg_match("/[^0-9a-z_]+/i", $mb_id))
        return array();

    static $cache = array();

    $key = md5($fields);

    if( $is_cache && isset($cache[$mb_id]) && isset($cache[$mb_id][$key]) ){
        return $cache[$mb_id][$key];
    }

    $sql = " select mb_id, mb_password, mb_hp, mb_tel, mb_ip from g5_member WHERE mb_id = '{$mb_id}'  union  select mb_id, mb_password, mb_hp, mb_tel,mb_ip from g5_members WHERE mb_id = '{$mb_id}'";

    $cache[$mb_id][$key] = run_replace('get_member', union_sql_fetch($sql), $mb_id, $fields, $is_cache);

    return $cache[$mb_id][$key];
}





// 로그인 패스워드 체크
function login_password_check_new($mb, $pass, $hash)
{
    global $g5;

    $mb_id = isset($mb['mb_id']) ? $mb['mb_id'] : '';

    if(!$mb_id)
        return false;

    if(G5_STRING_ENCRYPT_FUNCTION === 'create_hash' && (strlen($hash) === G5_MYSQL_PASSWORD_LENGTH || strlen($hash) === 16)) {
        if( sql_password($pass) === $hash ){

            if( ! isset($mb['mb_password2']) ){
                $sql = "ALTER TABLE `{$g5['member_table']}` ADD `mb_password2` varchar(255) NOT NULL default '' AFTER `mb_password`";
                sql_query($sql);
            }
            
            $new_password = create_hash($pass);
            $sql = " update {$g5['member_table']} set mb_password = '$new_password', mb_password2 = '$hash' where mb_id = '$mb_id' ";
            sql_query($sql);
            return true;
        }
    }

    return check_password($pass, $hash);
}


// 경고메세지 출력후 창을 닫음
function alert_close_re($msg, $error=true)
{
    global $g5, $config, $member, $is_member, $is_admin, $board;
    
    run_event('alert_close_re', $msg, $error);

    $msg = strip_tags($msg, '<br>');

    $header = '';
    if (isset($g5['title'])) {
        $header = $g5['title'];
    }
    include_once(G5_BBS_PATH.'/alert_close_re.php');
    exit;
}


define('adm_sql_common',      "");