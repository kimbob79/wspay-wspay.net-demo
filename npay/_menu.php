
						<li class="<?php if($p == "pay") { echo "on"; } ?>"><a href="./?p=pay">수기결제</a></li>
						<li class="<?php if($p == "url_list" or $p == "url_sms") { echo "on"; } ?>"><a href="./?p=url_list">SMS결제</a></li>
						<li class="<?php if($p == "list") { echo "on"; } ?>"><a href="./?p=list">결제내역</a></li>
						<?php /*
						<li class="<?php if($p == "list_old") { echo "on"; } ?>"><a href="./?p=list_old">과거 결제내역</a></li>
						*/ ?>
						<?php if($member['mb_level'] > 3) { ?>
						<li class="<?php if($p == "member_list") { echo "on"; } ?>"><a href="./?p=member_list">회원관리</a></li>
						<li class="<?php if($p == "pg_list") { echo "on"; } ?>"><a href="./?p=pg_list">PG관리</a></li>
						<li class="depth <?php if($p == "payment_original") { echo "on"; } ?>">
							<a href="javascript:;">결제원본</a>
							<ul class="sub">
								<li><a href="./?p=payment_original&pg_table=pay_payment_stn">섹타나인</a></li>
								<li><a href="./?p=payment_original&pg_table=pay_payment_k1">광원</a></li>
								<li><a href="./?p=payment_original&pg_table=pay_payment_welcom">웰컴</a></li>
								<li><a href="./?p=payment_original&pg_table=pay_payment_paysis">페이시스</a></li>
								<li><a href="./?p=payment_original&pg_table=pay_payment_danal">다날</a></li>
							</ul>
						</li>
						<?php } ?>
						<li class="depth <?php if($p == "board") { echo "on"; } ?>">
							<a href="javascript:;">고객센터</a>
							<ul class="sub">
								<li><a href="./?p=board&bo_table=notice">공지사항</a></li>
								<li><a href="./?p=board&bo_table=qa">문의</a></li>
								<?php /*
								<li><a href="./?p=payment_original&pg_table=pay_payment_paysis">페이시스</a></li>
								<li><a href="./?p=payment_original&pg_table=pay_payment_stn">섹타나인</a></li>
								<li><a href="./?p=payment_original&pg_table=pay_payment_danal">다날</a></li>
								*/ ?>
							</ul>
						</li>

						<?php /*
						<li class="<?php if($pp == "complaint") { echo "on"; } ?>"><a href="./?p=board&pp=complaint">민원관리</a></li>
						<li class="<?php if($pp == "notice") { echo "on"; } ?>"><a href="./?p=board&pp=notice">공지사항</a></li>
						<li class="<?php if($pp == "free") { echo "on"; } ?>"><a href="./?p=board&pp=free">자유게시판</a></li>
						*/ ?>
						<?php /*
						<li class="depth">
							<a href="javascript:;">관리</a>
							<ul class="sub">
								<li><a href="./?p=member_list">회원관리</a></li>
								<li><a href="./?p=pg_list">PG관리</a></li>
								<li><a href="./?pay_sms.php">원격결제</a></li>
								<li><a href="./?pay_write.php">수기결제</a></li>
								<li><a href="./?pay_camera.php">스캔결제</a></li>
								<li><a href="./?pay_touch.php">터치결제</a></li>
								<li><a href="./?pay_applepay.php">애플페이</a></li>
								<li><a href="./?pay_wechatpay.php">위챗페이</a></li>
								<li><a href="./?pay_qr.php">QR결제</a></li>
								<li><a href="./?pay_recurring.php">정기결제</a></li>
								<li><a href="./?pay_register.php">등록결제</a></li>
								<li><a href="./?pay_global.php">해외결제</a></li>
								<li><a href="./?pay_accounts.php">부계정</a></li>
							</ul>
						</li>
						*/ ?>
						<?php /*
						<li class="depth">
							<a href="javascript:;">결제방식</a>
							<ul class="sub">
								<li><a href="./?pay_simple.php">전자결제</a></li>
								<li><a href="./?pay_sms.php">원격결제</a></li>
								<li><a href="./?pay_write.php">수기결제</a></li>
								<li><a href="./?pay_camera.php">스캔결제</a></li>
								<li><a href="./?pay_touch.php">터치결제</a></li>
								<li><a href="./?pay_applepay.php">애플페이</a></li>
								<li><a href="./?pay_wechatpay.php">위챗페이</a></li>
								<li><a href="./?pay_qr.php">QR결제</a></li>
								<li><a href="./?pay_recurring.php">정기결제</a></li>
								<li><a href="./?pay_register.php">등록결제</a></li>
								<li><a href="./?pay_global.php">해외결제</a></li>
								<li><a href="./?pay_accounts.php">부계정</a></li>
							</ul>
						</li>
						<li class="on loadon">
							<a href="/homepage/bbs/bbs_faq.php">고객센터</a>
							<ul class="sub">
								<li><a href="/homepage/bbs/bbs_faq.php" class="on">자주묻는 질문</a></li>
								<li><a href="/homepage/bbs/bbs_notice.php">공지사항</a></li>
								<li><a href="/homepage/bbs/bbs_ask.php">문의사항</a></li>
								<li><a href="/homepage/bbs/bbs_use.php" >이용가이드</a></li>
							</ul>
						</li>
						<li class="depth">
							<a href="javascript:;">이용안내</a>
							<ul class="sub">
								<li><a href="/homepage/guide/guide1.php">비용안내</a></li>
								<li><a href="/homepage/guide/guide2.php">가입/결제받기</a></li>
								<li><a href="/homepage/guide/guide3.php">구비서류/심사</a></li>
								<li><a href="/homepage/guide/guide4.php">보증보험</a></li>
								<li><a href="/homepage/guide/guide5.php">정산안내</a></li>
							</ul>
						</li>
						<li class=""><a href="/homepage/reseller/reseller.php">영업안내</a></li>
						<li class=""><a href="/homepage/api/api.php">API 연동</a></li>
						<li class="depth">
							<a href="javascript:;">고객센터</a>
							<ul class="sub">
								<li><a href="/homepage/bbs/bbs_faq.php" class="on">자주묻는 질문</a></li>
								<li><a href="/homepage/bbs/bbs_notice.php">공지사항</a></li>
								<li><a href="/homepage/bbs/bbs_ask.php">문의사항</a></li>
								<li><a href="/homepage/bbs/bbs_use.php" >이용가이드</a></li>
							</ul>
						</li>
						*/ ?>