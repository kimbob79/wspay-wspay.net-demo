<?php
	if($t == "notice") {
		$title2 = "공지사항";
	} else if($t == "qa") {
		$title2 = "질문답변";
	}

	if($v == "view") {
		include_once("./bbs_view.php");
	} else if($v == "write") {
		include_once("./bbs_write.php");
	} else if($v == "update") {
		include_once("./bbs_update.php");
	} else if($v == "delete") {
		include_once("./bbs_delete.php");
	} else {
		include_once("./bbs_list.php");
	}