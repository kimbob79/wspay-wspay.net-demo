<?php
	if($member['mb_level'] <= 3) {
		alert("권한이 없습니다.");
	}
	$row = sql_fetch(" select * from pay_pg where pg_id = '{$pg_id}' ");
?>
<section class="container" id="bbs">
	<section class="contents contents-bbs">
		<div class="bbs-cont">
			<div class="inner">
				<div class="write-form">
					<form name="insert_bbs_form" method=post enctype="multipart/form-data" action="./?p=pg_update" onsubmit="return false;">
						<fieldset>
							<?php /*
							<legend class="sr-only">문의정보</legend>
							<div class="panel">
								<div class="panel-heading">
									<label for="" class="tit">그룹선택</label>
								</div>
								<div class="panel-body">
									<select name="mb_10" id="mb_10" class="form-control">
										<option value="1" <?php if($mb['mb_10'] == "1") { echo "selected"; } ?>>1번 그룹</option>
										<option value="2" <?php if($mb['mb_10'] == "2") { echo "selected"; } ?>>2번 그룹</option>
										<option value="3" <?php if($mb['mb_10'] == "3") { echo "selected"; } ?>>3번 그룹</option>
										<option value="4" <?php if($mb['mb_10'] == "4") { echo "selected"; } ?>>4번 그룹</option>
										<option value="5" <?php if($mb['mb_10'] == "5") { echo "selected"; } ?>>5번 그룹</option>
										<option value="6" <?php if($mb['mb_10'] == "6") { echo "selected"; } ?>>6번 그룹</option>
										<option value="7" <?php if($mb['mb_10'] == "7") { echo "selected"; } ?>>7번 그룹</option>
										<option value="8" <?php if($mb['mb_10'] == "8") { echo "selected"; } ?>>8번 그룹</option>
										<option value="9" <?php if($mb['mb_10'] == "9") { echo "selected"; } ?>>9번 그룹</option>
									</select>
								</div>
							</div>
							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">순서</label>
								</div>
								<div class="panel-body">
									<div class="panel-body2">
										<label class="input-chk"><input type="checkbox" name="mb_6" value="1" <?php if($mb['mb_6'] == "1") { echo "checked"; } ?>> <span>코페이(최대 <?php echo number_format(substr($config['cf_1'],0,-4)); ?>만원 / <?php echo $config['cf_2']; ?>개월) 결제가능</span></label>
									</div>
									<div class="panel-body2">
										<label class="input-chk"><input type="checkbox" class="form-control" name="mb_7" value="1" <?php if($mb['mb_7'] == "1") { echo "checked"; } ?>> <span>다날(최대 <?php echo number_format(substr($config['cf_3'],0,-4)); ?>만원 / <?php echo $config['cf_4']; ?>개월) 결제가능</span></label>
									</div>
									<div class="panel-body2">
										<label class="input-chk"><input type="checkbox" class="form-control" name="mb_8" value="1" <?php if($mb['mb_8'] == "1") { echo "checked"; } ?>> <span>웰컴(최대 <?php echo number_format(substr($config['cf_5'],0,-4)); ?>만원 / <?php echo $config['cf_6']; ?>개월) 결제가능</span></label>
									</div>
								</div>
							</div>
							*/ ?>
							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">순서</label>
								</div>
								<div class="panel-body">
									<input type="text" class="form-control" name="pg_sort" id="pg_sort" placeholder="2자리 숫자를 입력하시면 됩니다." value="<?php echo $row['pg_sort']; ?>" required>
								</div>
							</div>
							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">사용유무</label>
								</div>
								<div class="panel-body">
									<select name="pg_use" id="pg_use" class="form-control">
										<option value="0" <?php if($row['pg_use'] == "0") { echo "selected"; } ?>>사용</option>
										<option value="1" <?php if($row['pg_use'] == "1") { echo "selected"; } ?>>미사용</option>
									</select>
								</div>
							</div>
							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">PG사명</label>
								</div>
								<div class="panel-body">
									<input type="text" class="form-control" name="pg_name" id="pg_name" placeholder="PG사명" value="<?php echo $row['pg_name']; ?>" required>
								</div>
							</div>
							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">최대한도</label>
								</div>
								<div class="panel-body">
									<input type="text" class="form-control" name="pg_pay" id="pg_pay" placeholder="최대한도" value="<?php echo $row['pg_pay']; ?>" required>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">최대할부</label>
								</div>
								<div class="panel-body">
									<select name="pg_hal" id="pg_hal" class="form-control">
										<option value="1" <?php if($row['pg_hal'] == "1") { echo "selected"; } ?>>일시불</option>
										<option value="2" <?php if($row['pg_hal'] == "2") { echo "selected"; } ?>>2개월</option>
										<option value="3" <?php if($row['pg_hal'] == "3") { echo "selected"; } ?>>3개월</option>
										<option value="4" <?php if($row['pg_hal'] == "4") { echo "selected"; } ?>>4개월</option>
										<option value="5" <?php if($row['pg_hal'] == "5") { echo "selected"; } ?>>5개월</option>
										<option value="6" <?php if($row['pg_hal'] == "6") { echo "selected"; } ?>>6개월</option>
										<option value="7" <?php if($row['pg_hal'] == "7") { echo "selected"; } ?>>7개월</option>
										<option value="8" <?php if($row['pg_hal'] == "8") { echo "selected"; } ?>>8개월</option>
										<option value="9" <?php if($row['pg_hal'] == "9") { echo "selected"; } ?>>9개월</option>
										<option value="10" <?php if($row['pg_hal'] == "10") { echo "selected"; } ?>>10개월</option>
										<option value="11" <?php if($row['pg_hal'] == "11") { echo "selected"; } ?>>11개월</option>
										<option value="12" <?php if($row['pg_hal'] == "12") { echo "selected"; } ?>>12개월</option>
									</select>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">TID</label>
								</div>
								<div class="panel-body">
									<select name="pg_tid" id="pg_tid" class="form-control">
										<option value="0" <?php if($row['pg_tid'] == "0") { echo "selected"; } ?>>미사용</option>
										<option value="1" <?php if($row['pg_tid'] == "1") { echo "selected"; } ?>>사용</option>
									</select>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">KEY</label>
								</div>
								<div class="panel-body">
									<select name="pg_key" id="pg_key" class="form-control">
										<option value="0" <?php if($row['pg_key'] == "0") { echo "selected"; } ?>>미사용</option>
										<option value="1" <?php if($row['pg_key'] == "1") { echo "selected"; } ?>>사용</option>
									</select>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">MBR</label>
								</div>
								<div class="panel-body">
									<select name="pg_mbr" id="pg_mbr" class="form-control">
										<option value="0" <?php if($row['pg_mbr'] == "0") { echo "selected"; } ?>>미사용</option>
										<option value="1" <?php if($row['pg_mbr'] == "1") { echo "selected"; } ?>>사용</option>
									</select>
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">기타 키1</label>
								</div>
								<div class="panel-body">
									<input type="text" class="form-control" name="pg_key1" id="pg_key1" placeholder="기타 키1" value="<?php echo $row['pg_key1']; ?>">
								</div>
							</div>

							<div class="panel">
								<div class="panel-heading">
									<label for="title" class="tit">기타 키2</label>
								</div>
								<div class="panel-body">
									<input type="text" class="form-control" name="pg_key2" id="pg_key2" placeholder="기타 키2" value="<?php echo $row['pg_key1']; ?>">
								</div>
							</div>
						</fieldset>
						<input type=hidden name="chk_html" id="chk_html">
						<input type=hidden name="idx" id="idx" value="">
						<input type=hidden name="mode" id="mode" value="insert_bbs">
					</form>
				</div>
				<div class="btn-center-block">
					<div class="btn-group btn-horizon">
						<div class="btn-table">
							<a class="btn btn-black btn-cell" onclick="insert_bbs()">등록</a>
							<a class="btn btn-black-line btn-cell" onclick="go_cancel();">취소</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</section>