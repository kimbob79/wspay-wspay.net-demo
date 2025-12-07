<?php
	if(!$is_admin) {
		alert("관리자만 삭제할 수 있습니다.");
	}

	if(!$id) {
		alert("삭제할 글이 지정되지 않았습니다.");
	}

	// 글 존재 여부 확인
	$row = sql_fetch(" select * from {$write_table} where wr_id = '{$id}' ");
	if(!$row['wr_id']) {
		alert("존재하지 않는 글입니다.");
	}

	// 글 삭제
	sql_query(" delete from {$write_table} where wr_id = '{$id}' ");

	// 새글 테이블에서도 삭제
	sql_query(" delete from {$g5['board_new_table']} where bo_table = '{$bo_table}' and wr_id = '{$id}' ");

	// 첨부파일 삭제
	$file_path = G5_DATA_PATH.'/file/'.$bo_table;
	$result = sql_query(" select bf_file from {$g5['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$id}' ");
	while($file_row = sql_fetch_array($result)) {
		if($file_row['bf_file'] && file_exists($file_path.'/'.$file_row['bf_file'])) {
			@unlink($file_path.'/'.$file_row['bf_file']);
		}
	}
	sql_query(" delete from {$g5['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$id}' ");

	// 게시판 글 수 감소
	sql_query(" update {$g5['board_table']} set bo_count_write = bo_count_write - 1 where bo_table = '{$bo_table}' and bo_count_write > 0 ");

	alert("삭제되었습니다.", "./?p=bbs&t=".$t."&page=".$page);