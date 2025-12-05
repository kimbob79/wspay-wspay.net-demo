<?php
	if($wr_id || $w == "u") {
		$write_table = $g5['write_prefix'] . $bo_table;
		$sql_common = " from {$write_table} ";
		$sql = "select * from {$write_table} where wr_id = '{$wr_id}'";
		$write = sql_fetch($sql);
	}

include_once(G5_EDITOR_LIB);
include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');

if (!$board['bo_table']) {
    alert('존재하지 않는 게시판입니다.......');
}

if (!$bo_table) {
    alert("bo_table 값이 넘어오지 않았습니다.\\nwrite.php?bo_table=code 와 같은 방식으로 넘겨 주세요.", G5_URL);
}

check_device($board['bo_device']);

$notice_array = explode(',', trim($board['bo_notice']));

if (!($w == '' || $w == 'u' || $w == 'r')) {
    alert('w 값이 제대로 넘어오지 않았습니다.');
}

if ($w == 'u' || $w == 'r') {
    if ($write['wr_id']) {
        // 가변 변수로 $wr_1 .. $wr_10 까지 만든다.
        for ($i=1; $i<=10; $i++) {
            $vvar = "wr_".$i;
            $$vvar = $write['wr_'.$i];
        }
    } else {
        alert("글이 존재하지 않습니다.\\n삭제되었거나 이동된 경우입니다.", G5_URL);
    }
} else if ($w == '') { // 게시글 입력시에도 $wr_1 ~ $wr_10 변수 사용시 오류 나오지 않도록 가변변수 생성  (다온테마님,210806)
    for ($i=1; $i<=10; $i++) {
        $vvar = "wr_".$i;
        $$vvar = '';
    }
}

run_event('bbs_write', $board, $wr_id, $w);

if ($w == '') {
    if ($wr_id) {
        alert('글쓰기에는 \$wr_id 값을 사용하지 않습니다.', G5_BBS_URL.'/board.php?bo_table='.$bo_table);
    }

    if ($member['mb_level'] < $board['bo_write_level']) {
        if ($member['mb_id']) {
            alert('글을 쓸 권한이 없습니다.');
        } else {
            alert("글을 쓸 권한이 없습니다.\\n회원이시라면 로그인 후 이용해 보십시오.", G5_BBS_URL.'/login.php?'.$qstr.'&amp;url='.urlencode($_SERVER['SCRIPT_NAME'].'?bo_table='.$bo_table));
        }
    }

    // 음수도 true 인것을 왜 이제야 알았을까?
    if ($is_member) {
        $tmp_point = ($member['mb_point'] > 0) ? $member['mb_point'] : 0;
        if ($tmp_point + $board['bo_write_point'] < 0 && !$is_admin) {
            alert('보유하신 포인트('.number_format($member['mb_point']).')가 없거나 모자라서 글쓰기('.number_format($board['bo_write_point']).')가 불가합니다.\\n\\n포인트를 적립하신 후 다시 글쓰기 해 주십시오.');
        }
    }

    $title_msg = '글쓰기';
} else if ($w == 'u') {
    // 김선용 1.00 : 글쓰기 권한과 수정은 별도로 처리되어야 함
    //if ($member['mb_level'] < $board['bo_write_level']) {
    if($member['mb_id'] && $write['mb_id'] === $member['mb_id']) {
        ;
    } else if ($member['mb_level'] < $board['bo_write_level']) {
        if ($member['mb_id']) {
            alert('글을 수정할 권한이 없습니다.');
        } else {
            alert('글을 수정할 권한이 없습니다.\\n\\n회원이시라면 로그인 후 이용해 보십시오.', G5_BBS_URL.'/login.php?'.$qstr.'&amp;url='.urlencode($_SERVER['SCRIPT_NAME'].'?bo_table='.$bo_table));
        }
    }

    $len = strlen($write['wr_reply']);
    if ($len < 0) $len = 0;
    $reply = substr($write['wr_reply'], 0, $len);

    // 원글만 구한다.
    $sql = " select count(*) as cnt from {$write_table}
                where wr_reply like '{$reply}%'
                and wr_id <> '{$write['wr_id']}'
                and wr_num = '{$write['wr_num']}'
                and wr_is_comment = 0 ";
    $row = sql_fetch($sql);
    if ($row['cnt'] && !$is_admin)
        alert('이 글과 관련된 답변글이 존재하므로 수정 할 수 없습니다.\\n\\n답변글이 있는 원글은 수정할 수 없습니다.');

    // 코멘트 달린 원글의 수정 여부
    $sql = " select count(*) as cnt from {$write_table}
                where wr_parent = '{$wr_id}'
                and mb_id <> '{$member['mb_id']}'
                and wr_is_comment = 1 ";
    $row = sql_fetch($sql);
    if ($board['bo_count_modify'] && $row['cnt'] >= $board['bo_count_modify'] && !$is_admin)
        alert('이 글과 관련된 댓글이 존재하므로 수정 할 수 없습니다.\\n\\n댓글이 '.$board['bo_count_modify'].'건 이상 달린 원글은 수정할 수 없습니다.');

    $title_msg = '글수정';
} else if ($w == 'r') {
    if ($member['mb_level'] < $board['bo_reply_level']) {
        if ($member['mb_id'])
            alert('글을 답변할 권한이 없습니다.');
        else
            alert('답변글을 작성할 권한이 없습니다.\\n\\n회원이시라면 로그인 후 이용해 보십시오.', G5_BBS_URL.'/login.php?'.$qstr.'&amp;url='.urlencode($_SERVER['SCRIPT_NAME'].'?bo_table='.$bo_table));
    }

    $tmp_point = isset($member['mb_point']) ? $member['mb_point'] : 0;
    if ($tmp_point + $board['bo_write_point'] < 0 && !$is_admin)
        alert('보유하신 포인트('.number_format($member['mb_point']).')가 없거나 모자라서 글답변('.number_format($board['bo_comment_point']).')가 불가합니다.\\n\\n포인트를 적립하신 후 다시 글답변 해 주십시오.');

    //if (preg_match("/[^0-9]{0,1}{$wr_id}[\r]{0,1}/",$board['bo_notice']))
    if (in_array((int)$wr_id, $notice_array))
        alert('공지에는 답변 할 수 없습니다.');

    //----------
    // 4.06.13 : 비밀글을 타인이 열람할 수 있는 오류 수정 (헐랭이, 플록님께서 알려주셨습니다.)
    // 코멘트에는 원글의 답변이 불가하므로
    if ($write['wr_is_comment'])
        alert('정상적인 접근이 아닙니다.');

    // 비밀글인지를 검사
    if (strstr($write['wr_option'], 'secret')) {
        if ($write['mb_id']) {
            // 회원의 경우는 해당 글쓴 회원 및 관리자
            if (!($write['mb_id'] === $member['mb_id'] || $is_admin))
                alert('비밀글에는 자신 또는 관리자만 답변이 가능합니다.');
        } else {
            // 비회원의 경우는 비밀글에 답변이 불가함
            if (!$is_admin)
                alert('비회원의 비밀글에는 답변이 불가합니다.');
        }
    }
    //----------

    // 게시글 배열 참조
    $reply_array = &$write;

    // 최대 답변은 테이블에 잡아놓은 wr_reply 사이즈만큼만 가능합니다.
    if (strlen($reply_array['wr_reply']) == 10)
        alert('더 이상 답변하실 수 없습니다.\\n\\n답변은 10단계 까지만 가능합니다.');

    $reply_len = strlen($reply_array['wr_reply']) + 1;
    if ($board['bo_reply_order']) {
        $begin_reply_char = 'A';
        $end_reply_char = 'Z';
        $reply_number = +1;
        $sql = " select MAX(SUBSTRING(wr_reply, {$reply_len}, 1)) as reply from {$write_table} where wr_num = '{$reply_array['wr_num']}' and SUBSTRING(wr_reply, {$reply_len}, 1) <> '' ";
    } else {
        $begin_reply_char = 'Z';
        $end_reply_char = 'A';
        $reply_number = -1;
        $sql = " select MIN(SUBSTRING(wr_reply, {$reply_len}, 1)) as reply from {$write_table} where wr_num = '{$reply_array['wr_num']}' and SUBSTRING(wr_reply, {$reply_len}, 1) <> '' ";
    }
    if ($reply_array['wr_reply']) $sql .= " and wr_reply like '{$reply_array['wr_reply']}%' ";
    $row = sql_fetch($sql);

    if (!$row['reply'])
        $reply_char = $begin_reply_char;
    else if ($row['reply'] == $end_reply_char) // A~Z은 26 입니다.
        alert('더 이상 답변하실 수 없습니다.\\n\\n답변은 26개 까지만 가능합니다.');
    else
        $reply_char = chr(ord($row['reply']) + $reply_number);

    $reply = $reply_array['wr_reply'] . $reply_char;

    $title_msg = '글답변';

    $write['wr_subject'] = 'Re: '.$write['wr_subject'];
}

// 그룹접근 가능
if (!empty($group['gr_use_access'])) {
    if ($is_guest) {
        alert("접근 권한이 없습니다.\\n\\n회원이시라면 로그인 후 이용해 보십시오.", 'login.php?'.$qstr.'&amp;url='.urlencode($_SERVER['SCRIPT_NAME'].'?bo_table='.$bo_table));
    }

    if ($is_admin == 'super' || $group['gr_admin'] === $member['mb_id'] || $board['bo_admin'] === $member['mb_id']) {
        ; // 통과
    } else {
        // 그룹접근
        $sql = " select gr_id from {$g5['group_member_table']} where gr_id = '{$board['gr_id']}' and mb_id = '{$member['mb_id']}' ";
        $row = sql_fetch($sql);
        if (!$row['gr_id'])
            alert('접근 권한이 없으므로 글쓰기가 불가합니다.\\n\\n궁금하신 사항은 관리자에게 문의 바랍니다.');
    }
}

// 본인확인을 사용한다면
if ($board['bo_use_cert'] != '' && $config['cf_cert_use'] && !$is_admin) {
    // 인증된 회원만 가능
    if ($is_guest) {
        alert('이 게시판은 본인확인 하신 회원님만 글쓰기가 가능합니다.\\n\\n회원이시라면 로그인 후 이용해 보십시오.', G5_BBS_URL.'/login.php?wr_id='.$wr_id.$qstr.'&amp;url='.urlencode(get_pretty_url($bo_table, $wr_id, $qstr)));
    }

    if (strlen($member['mb_dupinfo']) == 64 && $member['mb_certify']) { // 본인 인증 된 계정 중에서 di로 저장 되었을 경우에만
        goto_url(G5_BBS_URL."/member_cert_refresh.php?url=".urlencode(get_pretty_url($bo_table, $wr_id, $qstr)));
    }

    if ($board['bo_use_cert'] == 'cert' && !$member['mb_certify']) {            
        alert('이 게시판은 본인확인 하신 회원님만 글쓰기가 가능합니다.\\n\\n회원정보 수정에서 본인확인을 해주시기 바랍니다.', G5_URL);
    }

    if ($board['bo_use_cert'] == 'adult' && !$member['mb_adult']) {
        alert('이 게시판은 본인확인으로 성인인증 된 회원님만 글읽기가 가능합니다.\\n\\n현재 성인인데 글읽기가 안된다면 회원정보 수정에서 본인확인을 다시 해주시기 바랍니다.', G5_URL);
    }
}

// 글자수 제한 설정값
if ($is_admin || $board['bo_use_dhtml_editor'])
{
    $write_min = $write_max = 0;
}
else
{
    $write_min = (int)$board['bo_write_min'];
    $write_max = (int)$board['bo_write_max'];
}

$g5['title'] = ((G5_IS_MOBILE && $board['bo_mobile_subject']) ? $board['bo_mobile_subject'] : $board['bo_subject']).' '.$title_msg;

$is_notice = false;
$notice_checked = '';
if ($is_admin && $w != 'r') {
    $is_notice = true;

    if ($w == 'u') {
        // 답변 수정시 공지 체크 없음
        if ($write['wr_reply']) {
            $is_notice = false;
        } else {
            if (in_array((int)$wr_id, $notice_array)) {
                $notice_checked = 'checked';
            }
        }
    }
}

$is_html = false;
if ($member['mb_level'] >= $board['bo_html_level'])
    $is_html = true;

$is_secret = $board['bo_use_secret'];

$is_mail = false;
if ($config['cf_email_use'] && $board['bo_use_email'])
    $is_mail = true;

$recv_email_checked = '';
if ($w == '' || strstr($write['wr_option'], 'mail'))
    $recv_email_checked = 'checked';

$is_name     = false;
$is_password = false;
$is_email    = false;
$is_homepage = false;
if ($is_guest || ($is_admin && $w == 'u' && $member['mb_id'] !== $write['mb_id'])) {
    $is_name = true;
    $is_password = true;
    $is_email = true;
    $is_homepage = true;
}

$is_category = false;
$category_option = '';
if ($board['bo_use_category']) {
    $ca_name = "";
    if (isset($write['ca_name']))
        $ca_name = $write['ca_name'];
    $category_option = get_category_option($bo_table, $ca_name);
    $is_category = true;
}

$is_link = false;
if ($member['mb_level'] >= $board['bo_link_level']) {
    $is_link = true;
}

$is_file = false;
if ($member['mb_level'] >= $board['bo_upload_level']) {
    $is_file = true;
}

$is_file_content = false;
if ($board['bo_use_file_content']) {
    $is_file_content = true;
}

$file_count = (int)$board['bo_upload_count'];

$name     = "";
$email    = "";
$homepage = "";
if ($w == "" || $w == "r") {
    if ($is_member) {
        if (isset($write['wr_name'])) {
            $name = get_text(cut_str(stripslashes($write['wr_name']),20));
        }
        $email = get_email_address($member['mb_email']);
        $homepage = get_text(stripslashes($member['mb_homepage']));
    }
}

$html_checked   = "";
$html_value     = "";
$secret_checked = "";

if ($w == '') {
    $password_required = 'required';
} else if ($w == 'u') {
    $password_required = '';

    if (!$is_admin) {
        if (!($is_member && $member['mb_id'] === $write['mb_id'])) {
            if (!check_password($wr_password, $write['wr_password'])) {
                $is_wrong = run_replace('invalid_password', false, 'write', $write);
                if(!$is_wrong) alert('비밀번호가 틀립니다.');
            }
        }
    }

    $name = get_text(cut_str(stripslashes($write['wr_name']),20));
    $email = get_email_address($write['wr_email']);
    $homepage = get_text(stripslashes($write['wr_homepage']));

    for ($i=1; $i<=G5_LINK_COUNT; $i++) {
        $write['wr_link'.$i] = get_text($write['wr_link'.$i]);
        $link[$i] = $write['wr_link'.$i];
    }

    if (strstr($write['wr_option'], 'html1')) {
        $html_checked = 'checked';
        $html_value = 'html1';
    } else if (strstr($write['wr_option'], 'html2')) {
        $html_checked = 'checked';
        $html_value = 'html2';
    }

    if (strstr($write['wr_option'], 'secret')) {
        $secret_checked = 'checked';
    }

    $file = get_file($bo_table, $wr_id);
    if($file_count < $file['count']) {
        $file_count = $file['count'];
    }

    for($i=0;$i<$file_count;$i++){
        if(! isset($file[$i])) {
            $file[$i] = array('file'=>null, 'source'=>null, 'size'=>null, 'bf_content' => null);
        }
    }

} else if ($w == 'r') {
    if (strstr($write['wr_option'], 'secret')) {
        $is_secret = true;
        $secret_checked = 'checked';
    }

    $password_required = "required";

    for ($i=1; $i<=G5_LINK_COUNT; $i++) {
        $write['wr_link'.$i] = get_text($write['wr_link'.$i]);
    }
}

set_session('ss_bo_table', $bo_table);
set_session('ss_wr_id', $wr_id);

$subject = "";
if (isset($write['wr_subject'])) {
    $subject = str_replace("\"", "&#034;", get_text(cut_str($write['wr_subject'], 255), 0));
}

$content = '';
if ($w == '') {
    $content = html_purifier($board['bo_insert_content']);
} else if ($w == 'r') {
    if (!strstr($write['wr_option'], 'html')) {
        $content = "\n\n\n &gt; "
                 ."\n &gt; "
                 ."\n &gt; ".str_replace("\n", "\n> ", get_text($write['wr_content'], 0))
                 ."\n &gt; "
                 ."\n &gt; ";

    }
} else {
    $content = get_text($write['wr_content'], 0);
}

$upload_max_filesize = number_format($board['bo_upload_size']) . ' 바이트';

$width = $board['bo_table_width'];
if ($width <= 100)
    $width .= '%';
else
    $width .= 'px';

$captcha_html = '';
$captcha_js   = '';
$is_use_captcha = ((($board['bo_use_captcha'] && $w !== 'u') || $is_guest) && !$is_admin) ? 1 : 0;

if ($is_use_captcha) {
    $captcha_html = captcha_html();
    $captcha_js   = chk_captcha_js();
}

$is_dhtml_editor = false;
$is_dhtml_editor_use = false;
$editor_content_js = '';
if(!is_mobile() || defined('G5_IS_MOBILE_DHTML_USE') && G5_IS_MOBILE_DHTML_USE)
    $is_dhtml_editor_use = true;

// 모바일에서는 G5_IS_MOBILE_DHTML_USE 설정에 따라 DHTML 에디터 적용
if ($config['cf_editor'] && $is_dhtml_editor_use && $board['bo_use_dhtml_editor'] && $member['mb_level'] >= $board['bo_html_level']) {
    $is_dhtml_editor = true;

    if ( $w == 'u' && (! $is_member || ! $is_admin || $write['mb_id'] !== $member['mb_id']) ){
        // kisa 취약점 제보 xss 필터 적용
        $content = get_text(html_purifier($write['wr_content']), 0);
    }

    if(is_file(G5_EDITOR_PATH.'/'.$config['cf_editor'].'/autosave.editor.js'))
        $editor_content_js = '<script src="'.G5_EDITOR_URL.'/'.$config['cf_editor'].'/autosave.editor.js"></script>'.PHP_EOL;
}
$editor_html = editor_html('wr_content', $content, $is_dhtml_editor);
$editor_js = '';
$editor_js .= get_editor_js('wr_content', $is_dhtml_editor);
$editor_js .= chk_editor_js('wr_content', $is_dhtml_editor);

// 임시 저장된 글 수
$autosave_count = autosave_count($member['mb_id']);



?>
<section class="container" id="bbs">
	<section class="contents contents-bbs">
		<?php /*
		<div class="top">
			<div class="inner">
				<div class="heading-tit pull-left">
					<h2>고객센터</h2>
					<ul>
						<li>1800 - 3772</li>
						<li>평일 09:00 ~ 18:00</li>
					</ul>
				</div>
			</div>
		</div>
		*/ ?>

		<div class="bbs-cont">
			<div class="inner">
				<form name="fwrite" id="fwrite" action="./board_write_update.php" onsubmit="return fwrite_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off" style="width:<?php echo $width; ?>">
				<input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>">
				<input type="hidden" name="w" value="<?php echo $w ?>">
				<input type="hidden" name="p" value="<?php echo $p ?>">
				<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
				<input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
				<input type="hidden" name="sca" value="<?php echo $sca ?>">
				<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
				<input type="hidden" name="stx" value="<?php echo $stx ?>">
				<input type="hidden" name="spt" value="<?php echo $spt ?>">
				<input type="hidden" name="sst" value="<?php echo $sst ?>">
				<input type="hidden" name="sod" value="<?php echo $sod ?>">
				<input type="hidden" name="page" value="<?php echo $page ?>">
				<div class="write-form">
					<?php /*
					<ul class="keyword tab-menu">
						<li class="active" rel="tab1"><a href="#">판매자 회원이신 경우</a></li>
						<li rel="tab2"><a href="#">결제고객(구매자)이신 경우</a></li>
					</ul>
					<div class="noti-box">
						<h4>문의 전 안내사항<span class="view-btn"><em class="open-btn">보기</em><em class="close-btn">닫기</em></span></h4>
						<ul class="tab-content" id="tab1">
							<li>
							<span class="quest">가입 후 해야할 일이 있나요?</span>
							<p class="reply">
								가입 후 결제 사용은 바로 가능하나, 계약서 및 구비서류를 발송해주셔야 정산이 가능합니다.<br>
								계약서는 <b class="text-red">페이앱 홈페이지 > 이용안내 > 구비서류/심사</b>페이지에서 출력할 수 있으며, 작성 날인 하여 <b class="text-underline">payapp@udid.co.kr</b> 이메일로 접수해주시기 바랍니다.
							</p>
							</li>
							<li>
							<span class="quest">계약서를 이메일로 발송했는데 언제 처리되나요?</span>
							<p class="reply">
								계약서는 발송해주신 순서대로 서류심사팀에서 확인되어 완료까지는 <b><span class="text-red">최대 일주일</span> 정도 소요</b>될 수 있습니다. 완료 또는 수정 사항이 있을 경우 유선이나 문자, 메일로 연락드리니 조금만 기다려 주시기 바랍니다.
							</p>
							</li>
							<li>
							<span class="quest">정산은 언제 되나요?</span>
							<p class="reply">
								<b>결제일로부터 <span class="text-red">D+5</span></b>(영업일기준) 가 정산일 입니다. 계약서 완료 후 정산이 가능하니 참고하시기 바랍니다.
							</p>
							</li>
							<li>
							<span class="quest">왜 정산 불가인가요?</span>
							<p class="reply">
								계약서가 완료되지 않았거나, 보증보험 증권 등 여러가지 사유가 있을 수 있기 때문에 오른쪽 아래 <b class="text-red">채팅상담</b>으로 문의주시거나, <b class="text-red">1800-3772</b>로 연락하시는 게 가장 빠르고 정확합니다.
							</p>
							</li>
						</ul>
						<ul class="tab-content" id="tab2">
							<li>
							<span class="quest">결제 내역 확인이 필요하세요?</span>
							<span class="quest">판매자와 연락이 안 됩니다. 취소하고 싶은데 민원을 어떻게 접수하죠?</span>
							<span class="quest">판매자와 결제 취소에 대한 분쟁이 있습니다. 민원을 어떻게 접수하죠?</span>
							<p class="reply reply-lg">
								<b><a class="text-orange" href="/popup_pay_new/popup/pay_view1.html" onclick="return openWindow(this, {name:'payappHistory',width:420,height:630,center:true,scrollbars:true})">결제 내역 조회</a></b> 메뉴에서 내역 조회가 가능하며 결제 내역 조회 결과 화면에 표시되는 <b class="text-red">[민원접수] 기능으로 온라인 민원신청이 가능</b>합니다.<br>
								 온라인 민원 신청 시 페이앱 접수와 판매점에 대한 통보가 자동으로 이뤄지며, 페이앱 담당자가 지속 체크하여 민원 해결을 적극적으로 돕습니다.
							</p>
							</li>
						</ul>
					</div>
					*/
					//echo $sql;
					?>

						<input type="hidden" name="p" value="<?php echo $p; ?>">
						<input type="hidden" name="pp" value="<?php echo $pp; ?>">
						<input type="hidden" name="pm" value="<?php echo $pm; ?>">
						<input type="hidden" name="wr_id" value="<?php echo $wr_id; ?>">
						<input type="hidden" name="w" value="<?php echo $w; ?>">
						<fieldset>
							<legend class="sr-only">문의정보</legend>
							<?php if($row_board['bo_use_category'] == '1') { ?>
							<div class="panel">
								<div class="panel-heading">
									<label for="" class="tit">분류</label>
								</div>
								<div class="panel-body">
									<select class="form-control" name="bbsType" id="bbsType">
										<option value="">문의 유형을 선택하세요.</option>
										<option value="300">일반문의</option>
										<option value="500">연동문의</option>
									</select>
								</div>
							</div>
							<?php } ?>
							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">제목</label>
								</div>
								<div class="panel-body">
									<input type="text" class="form-control" name="wr_subject" id="wr_subject" placeholder="제목을 입력해주세요." value="<?php echo $write['wr_subject']; ?>">
								</div>
							</div>
							<div class="panel">
								<div class="panel-heading">
									<label for="contents" class="tit">내용</label>
								</div>
								<div class="panel-body">
									<?php if($write_min || $write_max) { ?>
									<!-- 최소/최대 글자 수 사용 시 -->
									<p id="char_count_desc">이 게시판은 최소 <strong><?php echo $write_min; ?></strong>글자 이상, 최대 <strong><?php echo $write_max; ?></strong>글자 이하까지 글을 쓰실 수 있습니다.</p>
									<?php } ?>
									<?php echo $editor_html; // 에디터 사용시는 에디터로, 아니면 textarea 로 노출 ?>
									<?php if($write_min || $write_max) { ?>
									<!-- 최소/최대 글자 수 사용 시 -->
									<div id="char_count_wrap"><span id="char_count"></span>글자</div>
									<?php } ?>
									<?php /*
									<textarea name="contents" id="contents" cols="30" rows="10" placeholder="내용을 입력해주세요."><?php echo $row['wr_content']; ?></textarea>
									*/ ?>
								</div>
							</div>
							<div class="panel">
								<div class="panel-heading">
									<label for="" class="tit">파일첨부</label>
								</div>
								<div class="panel-body">
									<input type="file" id="file_file" name="file_file" class="input-file">
									<div class="input-group">
										<input type="text" name="file_file" id="" class="form-control form-upload" placeholder="" disabled>
										<a role="button" href="javascript:;" class="input-group-addon btn-md btn-gray-line upload-field">파일 선택</a>
									</div>
									<div class="guide-text">
										※ 2MB를 초과 할 수 없습니다.
									</div>
								</div>
							</div>
						</fieldset>
				</div>
				<div class="btn-center-block">
					<div class="btn-group btn-horizon">
						<div class="btn-table">
							<button type="submit" id="btn_submit" accesskey="s" class="btn_submit btn btn-black btn-cell">작성완료</button>
							<a class="btn btn-black-line btn-cell" onclick="go_cancel();">취소</a>
						</div>
					</div>
				</div>
				</form>
			</div>
		</div>
	</section>
</section>




    <script>
    <?php if($write_min || $write_max) { ?>
    // 글자수 제한
    var char_min = parseInt(<?php echo $write_min; ?>); // 최소
    var char_max = parseInt(<?php echo $write_max; ?>); // 최대
    check_byte("wr_content", "char_count");

    $(function() {
        $("#wr_content").on("keyup", function() {
            check_byte("wr_content", "char_count");
        });
    });

    <?php } ?>
    function html_auto_br(obj)
    {
        if (obj.checked) {
            result = confirm("자동 줄바꿈을 하시겠습니까?\n\n자동 줄바꿈은 게시물 내용중 줄바뀐 곳을<br>태그로 변환하는 기능입니다.");
            if (result)
                obj.value = "html2";
            else
                obj.value = "html1";
        }
        else
            obj.value = "";
    }

    function fwrite_submit(f)
    {
        <?php echo $editor_js; // 에디터 사용시 자바스크립트에서 내용을 폼필드로 넣어주며 내용이 입력되었는지 검사함   ?>

        var subject = "";
        var content = "";
        $.ajax({
            url: g5_bbs_url+"/ajax.filter.php",
            type: "POST",
            data: {
                "subject": f.wr_subject.value,
                "content": f.wr_content.value
            },
            dataType: "json",
            async: false,
            cache: false,
            success: function(data, textStatus) {
                subject = data.subject;
                content = data.content;
            }
        });

        if (subject) {
            alert("제목에 금지단어('"+subject+"')가 포함되어있습니다");
            f.wr_subject.focus();
            return false;
        }

        if (content) {
            alert("내용에 금지단어('"+content+"')가 포함되어있습니다");
            if (typeof(ed_wr_content) != "undefined")
                ed_wr_content.returnFalse();
            else
                f.wr_content.focus();
            return false;
        }

        if (document.getElementById("char_count")) {
            if (char_min > 0 || char_max > 0) {
                var cnt = parseInt(check_byte("wr_content", "char_count"));
                if (char_min > 0 && char_min > cnt) {
                    alert("내용은 "+char_min+"글자 이상 쓰셔야 합니다.");
                    return false;
                }
                else if (char_max > 0 && char_max < cnt) {
                    alert("내용은 "+char_max+"글자 이하로 쓰셔야 합니다.");
                    return false;
                }
            }
        }

        <?php echo $captcha_js; // 캡챠 사용시 자바스크립트에서 입력된 캡챠를 검사함  ?>

        document.getElementById("btn_submit").disabled = "disabled";

        return true;
    }
    </script>