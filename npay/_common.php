<?php
	include_once('../gnu_module/common.php');
	/*
	if($member['mb_level'] < "3") {
		alert("회원만 가능합니다.");
	}
	*/

	// 한페이지에 보여줄 행, 현재페이지, 총페이지수, URL
	function get_paging_new($write_pages, $cur_page, $total_page, $url, $add="")
	{
		//$url = preg_replace('#&amp;page=[0-9]*(&amp;page=)$#', '$1', $url);
		$url = preg_replace('#(&amp;)?page=[0-9]*#', '', $url);
		$url .= substr($url, -1) === '?' ? 'page=' : '&amp;page=';
		$url = preg_replace('|[^\w\-~+_.?#=!&;,/:%@$\|*\'()\[\]\\x80-\\xff]|i', '', clean_xss_tags($url));

		$str = '';
		if ($cur_page > 1) {
			$str .= '<a href="'.$url.'1'.$add.'" class="btn-paging"><i class="ico ico-first"></i></a>'.PHP_EOL;
		}

		$start_page = ( ( (int)( ($cur_page - 1 ) / $write_pages ) ) * $write_pages ) + 1;
		$end_page = $start_page + $write_pages - 1;

		if ($end_page >= $total_page) $end_page = $total_page;

		if ($start_page > 1) $str .= '<a href="'.$url.($start_page-1).$add.'" class="btn-paging"><i class="ico ico-prev"></i></a>'.PHP_EOL;
		$str .= "<ol>";
		if ($total_page > 1) {
			for ($k=$start_page;$k<=$end_page;$k++) {
				if ($cur_page != $k)
					$str .= '<li><a href="'.$url.$k.$add.'">'.$k.'</a></li>'.PHP_EOL;
				else
					$str .= '<li class="active"><a href="'.$url.$k.$add.'">'.$k.'</a></li>'.PHP_EOL;
			}
		}
		$str .= "</ol>";

		if ($total_page > $end_page) $str .= '<a href="'.$url.($end_page+1).$add.'" class="btn-paging"><i class="ico ico-next"></i></a>'.PHP_EOL;

		if ($cur_page < $total_page) {
			$str .= '<a href="'.$url.$total_page.$add.'" class="btn-paging"><i class="ico ico-last"></i></a>'.PHP_EOL;
		}

		if ($str)
			return "<div class=\"paging\"><span class=\"pg\">{$str}</span></nav>";
		else
			return "";
	}
?>