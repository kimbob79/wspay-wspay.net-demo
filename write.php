
				<section id="bo_w">
					<form name="fwrite" id="fwrite" action="https://theme.sir.kr/gnuboard55/bbs/write_update.php" onsubmit="return fwrite_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
					<input type="hidden" name="w" value="">
					<input type="hidden" name="bo_table" value="free">
					<input type="hidden" name="wr_id" value="0">
					<input type="hidden" name="sca" value="">
					<input type="hidden" name="sfl" value="">
					<input type="hidden" name="stx" value="">
					<input type="hidden" name="spt" value="">
					<input type="hidden" name="sst" value="">
					<input type="hidden" name="sod" value="">
					<input type="hidden" name="page" value="">
						
					<div class="form_inpt">
						<h2 class="sound_only">자유게시판 글쓰기</h2>
						
						<ul class="bo_w_info">
							<li class="wli_left">
								<div class="wli_tit">이름</div>
								<div class="wli_cnt">
									<label for="wr_name" class="sound_only">이름<strong>필수</strong></label>
									<input type="text" name="wr_name" value="" id="wr_name" required class="frm_input full_input required" maxlength="20" placeholder="이름">
								</div>
							</li>
							<li class="wli_left">
								<div class="wli_tit">비밀번호</div>
								<div class="wli_cnt">
									<label for="wr_password" class="sound_only">비밀번호<strong>필수</strong></label>
									<input type="password" name="wr_password" id="wr_password" required class="frm_input full_input required" maxlength="20" placeholder="비밀번호">
								</div>
							</li>
							<li class="wli_left">
								<div class="wli_tit">이메일</div>
								<div class="wli_cnt">	
									<label for="wr_email" class="sound_only">이메일</label>
									<input type="email" name="wr_email" value="" id="wr_email" class="frm_input full_input email" maxlength="100" placeholder="이메일">
								</div>
							</li>
							<li class="wli_left">
								<div class="wli_tit">홈페이지</div>
								<div class="wli_cnt">
									<label for="wr_homepage" class="sound_only">홈페이지</label>
									<input type="text" name="wr_homepage" value="" id="wr_homepage" class="frm_input full_input" placeholder="홈페이지">
								</div>
							</li>
							<li class="bo_w_tit">
								<div class="wli_tit">제목</div>
								<div class="wli_cnt">
									<label for="wr_subject" class="sound_only">제목<strong>필수</strong></label>
									<input type="text" name="wr_subject" value="" id="wr_subject" required class="frm_input required" placeholder="제목">
								</div>
							</li>
							<li class="bo_w_option">
								<div class="wli_tit"><span class="sound_only">글쓰기 옵션</span></div>
								<div class="wli_cnt">
									<span class="sound_only">옵션</span>
									<input type="checkbox" id="html" name="html" onclick="html_auto_br(this);" value="" >
									<label for="html">html</label>
								</div>
								<script>
								$(document).ready(function(){
									$("#notice").click(function(){
										$(".notice_ck").toggleClass("click_on");
									});
								
									$("#mail").click(function(){
										$(".mail_ck").toggleClass("click_off");
									});

									$("#secret").click(function(){
										$(".secret_ck").toggleClass("click_on");
									});
								
									$("input[type='checkbox']").each(function(){
										var name = $(this).attr('name');
										if($(this).prop("checked")) {
											$(this).siblings("label[for='"+name+"']").addClass("click_on");
										}
									});
								});
								</script>
							</li>
							<li>
								<div class="wli_tit"><span class="sound_only">내용</span></div>
								<div class="wli_cnt">
									<label for="wr_content" class="sound_only">내용<strong>필수</strong></label>
									<span class="sound_only">웹에디터 시작</span>
									<textarea id="wr_content" name="wr_content" class="" maxlength="65536" style="width:100%;height:300px"></textarea>
									<span class="sound_only">웹 에디터 끝</span>
								</div>
							</li>
							<li class="bo_w_link">
								<div class="wli_tit">링크</div>
								<div class="wli_cnt">
									<label for="wr_link1"><span class="sound_only">링크 #1</span></label>
									<input type="text" name="wr_link1" value="" id="wr_link1" class="frm_input wr_link" placeholder="링크를 입력해주세요.">
								</div>
							</li>
							<li class="bo_w_link">
								<div class="wli_tit">링크</div>
								<div class="wli_cnt">
									<label for="wr_link2"><span class="sound_only">링크 #2</span></label>
									<input type="text" name="wr_link2" value="" id="wr_link2" class="frm_input wr_link" placeholder="링크를 입력해주세요.">
								</div>
							</li>
							<li class="bo_w_flie write_div">
								<div class="wli_tit">파일첨부</div>
								<div class="file_wr wli_cnt">
									<label for="bf_file_1" class="lb_icon"><span class="sound_only">파일 #1</span></label>
									<input type="file" name="bf_file[]" id="bf_file_1" title="파일첨부 1 : 용량 1,048,576 바이트 이하만 업로드 가능" class="frm_file ">
								</div>
							</li>
							<li class="bo_w_flie write_div">
								<div class="wli_tit">파일첨부</div>
								<div class="file_wr wli_cnt">
									<label for="bf_file_2" class="lb_icon"><span class="sound_only">파일 #2</span></label>
									<input type="file" name="bf_file[]" id="bf_file_2" title="파일첨부 2 : 용량 1,048,576 바이트 이하만 업로드 가능" class="frm_file ">
								</div>
							</li>
						</ul>
					</div>
					
					<div class="bo_w_btn">
						<a href="https://theme.sir.kr/gnuboard55/bbs/board.php?bo_table=free" class="btn_cancel">취소</a>
						<button type="submit" id="btn_submit" class="btn_submit" accesskey="s">작성완료</button>
					</div>
					</form>

					<script>
					function html_auto_br(obj)
					{
						if (obj.checked) {
							result = confirm("자동 줄바꿈을 하시겠습니까?\n\n자동 줄바꿈은 게시물 내용중 줄바뀐 곳을<br>태그로 변환하는 기능입니다.");
							if (result) {
								obj.value = "html2";
							} else {
								obj.value = "html1";
							}

							$("label[for='html']").addClass('click_on');
						} else {
							obj.value = "";
							$("label[for='html']").removeClass('click_on');
						}
					}

					function fwrite_submit(f)
					{
						var wr_content_editor = document.getElementById('wr_content');
					if (!wr_content_editor.value) { alert("내용을 입력해 주십시오."); wr_content_editor.focus(); return false; }

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

						if (!chk_captcha()) return false;

						document.getElementById("btn_submit").disabled = "disabled";

						return true;
					}
					</script>
				</section>