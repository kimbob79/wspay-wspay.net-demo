<?php

	// 디렉토리 조회
	function get_dir_list($dirname){
		$result_array = array();
		$handle = opendir($dirname);
		while ($file = readdir($handle)) {
			if($file == '.'||$file == '..') continue;
			if (is_dir($dirname.$file)) $result_array[$file] = $file;
		}
		closedir($handle);
		rsort($result_array);
		return $result_array;
	}


	function getFiles($path) {
		$arrData = array();
		$results = scandir($path);
		foreach ($results as $result) {
			if ($result === '.' or $result === '..') continue;
			if (!is_dir($path . '/' . $result)) { //디렉토리가 아니면
				$data = array();
				$path_parts = pathinfo($path . '/' . $result);
				$data["dirname"] = iconv("euc-kr","utf-8",$path_parts['dirname']); //파일경로 단, 파일이름은 포함하지 않음, 한글깨짐 방지
				$data["basename"] = iconv("euc-kr","utf-8",$path_parts['basename']); //파일이름
				$data["extension"] = iconv("euc-kr","utf-8",$path_parts['extension']); //확장자.
				$data["mtime"] =  date("Y-m-d H:i:s.", filemtime($path . '/' . $result)); //파일 수정일
				$data["ctime"] =  date("Y-m-d H:i:s.", filectime($path . '/' . $result)); //파일 생성일
				$data["filesize"] =  filesize($path . '/' . $result); //파일 크기, byte단위
				$data["filename"] = iconv("euc-kr","utf-8",$path_parts['filename']); // since PHP 5.2.0
				$arrData[] = $data;
			}
		}
		return $arrData;
	}
?>

<div class="index_menu">
	<ul class="shortcut">
		<li class="sc_current"><a>차액정산 파일조회</a></li>
		<li class="sc_visit">
			<aside id="visit">
				<ul>
					<li></li>
				</ul>
			</aside>
		</li>
	</ul>
</div>

<form action="./xlsx/payment.php" id="frm_xlsx" method="post">
<input type="hidden" name="xlsx_sql" value="<?php echo $xlsx_sql; ?>">
<input type="hidden" name="p" value="<?php echo $p; ?>">
</form>

<div class="m_board_scroll">
	<div class="m_table_wrap" style="padding-bottom:115px;">
		<p class="txt_ex_scroll"></p>
		<table class="table_list td_pd" style="max-width:500px">
			<?php
				if($forder) {
					$files = scandir("./sftp_mainpay/".$forder, 1);
			?>
			<thead>
				<tr>
					<th>파일명</th>
					<th style="width:200px;">생성/수정일</th>
				</tr>
			</tbody>
			<tbody>
				<?php
					for($i=0; $i<count($files); $i++) {

						$filee = "./sftp_mainpay/".$forder."/".$files[$i];

						$creationTime = filectime($filee);
						$creationTime = date('Y-m-d H:i:s', $creationTime);

						if($files[$i] == "..") { continue; }
						if($files[$i] == ".") { continue; }

						echo "<tr><td style='text-align:left'><a href='./sftp_mainpay/".$forder."/".$files[$i]."' target='blank''>".$files[$i]."</td><td>".$creationTime."</a></td></tr>";
					}
				?>
			</tbody>
			<tfoot>
				<tr>
					<th colspan="2"><a href='./?p=sftp_data'>뒤로가기</a></th>
				</tr>
			</tfoot>
			<?php
				} else {
			?>
			<thead>
				<tr>
					<th>날짜</th>
				</tr>
			</tbody>
			<?php
				$sftp_data = get_dir_list("./sftp_mainpay/");
			?>
			<tbody>
			<?php
				for($i=0; $i<count($sftp_data); $i++) {
					$dater = substr($sftp_data[$i],0,2);
					$dater .= "-";
					$dater .= substr($sftp_data[$i],2,2);
					$dater .= "-";
					$dater .= substr($sftp_data[$i],4,2);
					echo "<tr><td><a href='./?p=".$p."&forder=".$sftp_data[$i]."'>".$dater."</a></td></tr>";
				}
			?>
			</tbody>
			<?php
				}
			?>
		</table>
	</div>
</div>