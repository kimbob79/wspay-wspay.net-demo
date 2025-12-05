<?php

	$row_sql = " select * from g5_board where bo_table = '{$bo_table}' ";
	$row_board = sql_fetch($row_sql);


	if($pm == "view") {
		include_once("board_view.php");
	} else if($pm == "write") {
		include_once("board_write.php");
	} else {
		include_once("board_list.php");
	}