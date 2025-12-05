<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (!defined('_INDEX_')) define('_INDEX_', true);




include_once(G5_THEME_MOBILE_PATH.'/head.php');
include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');
include_once(G5_LIB_PATH.'/popular.lib.php');

if($is_guest) {
	include_once(G5_BBS_PATH.'/login.php');
} else {
	include_once(G5_PATH.'/payment/list.php');
/*
$cu_money = $_POST['cu_money']*10000;
$cu_percent = $_POST['cu_percent'];
if($cu_money) {
	$cu_result = $cu_money * $cu_percent / 100;
	$sql = " insert into g5_calculation set cu_money = '{$cu_money}', cu_percent = '$cu_percent', cu_result = '{$cu_result}', datetime = '" . G5_TIME_YMDHIS . "' ";
	sql_query($sql);
	goto_url("./index.php");
}
?>

<div class="tbl_fbasic">
	<form method="post">
	<table class="tbl_form1" style="width:100%;">
		<tbody>
			<tr>
				<td>
					<input type="number" name="cu_money" value="" id="cu_money" class="frm_input" placeholder="만원 단위" required style="width: 70%;"> 만원
				</td>
				<td>
					<input type="text" name="cu_percent" id="cu_percent" class="frm_input" placeholder="수수료" required style="width: 70%;"> %
				</td>
				<td>
					<input type="submit" value="계산하기" class="btn_submit" accesskey='s' style="height:45px">
				</td>
			</tr>
			<tr><td colspan="3" style="height:5px;border-bottom:1px solid #ddd"></td></tr>
			<?php
				$sql = " select * from g5_calculation order by datetime desc limit 0, 20 ";
				$result = sql_query($sql);
				for ($i=0; $row=sql_fetch_array($result); $i++) {
			?>
			<tr>
				<td style="height:40px;">
					<?php echo number_format($row['cu_money']); ?>
				</td>
				<td>
					<?php echo $row['cu_percent']; ?>%
				</td>
				<td style="font-weight:bold">
					<?php echo number_format($row['cu_result']); ?>
				</td>
			</tr>
			<tr><td colspan="3" style="border-bottom:1px solid #ddd"></td></tr>
			<?php } ?>
		</tbody>
	</table>
	</form>
</div>



<?php
*/
}
?>


<?php
include_once(G5_THEME_MOBILE_PATH.'/tail.php');
?>