<div class="gnb-info">
	<a href="./?p=member_form&mb_id=<?php echo $member['mb_id']; ?>&w=u"><?php echo $member['mb_nick']; ?></a>
	<?php if($is_guest) { ?>
	<a href="./?p=login">로그인</a>
	<?php } else { ?>
	<a href="./?p=logout">로그아웃</a>
	<?php } ?>
</div>