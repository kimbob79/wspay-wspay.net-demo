<?php
include_once('./_common.php');

// 관리자만 접근 가능
if(!$is_admin) {
    alert("관리자만 접근할 수 있습니다.");
}

$title1 = "URL결제";
$title2 = "URL결제 생성";

// 선택된 가맹점 ID
$selected_mb_id = isset($_GET['selected_mb_id']) ? $_GET['selected_mb_id'] : '';

// Keyin 설정이 등록되고 수기결제가 허용된 가맹점 목록 조회
$keyin_members_sql = "SELECT DISTINCT m.mb_id, m.mb_nick, m.mb_name, m.mb_hp
    FROM {$g5['member_table']} m
    INNER JOIN g5_member_keyin_config k ON m.mb_id = k.mb_id
    WHERE m.mb_level = 3 AND m.mb_mailling = '1' AND k.mkc_use = 'Y' AND k.mkc_status = 'active'
    ORDER BY m.mb_nick";
$keyin_members = sql_query($keyin_members_sql);

// 선택된 가맹점의 Keyin 설정 조회
$keyin_configs = array();
if($selected_mb_id) {
    $keyin_sql = "SELECT k.*, m.mpc_pg_code as master_pg_code, m.mpc_pg_name as master_pg_name, m.mpc_type as master_type
        FROM g5_member_keyin_config k
        LEFT JOIN g5_manual_payment_config m ON k.mpc_id = m.mpc_id
        WHERE k.mb_id = '".sql_real_escape_string($selected_mb_id)."' AND k.mkc_use = 'Y' AND k.mkc_status = 'active'
        ORDER BY k.mkc_id ASC";
    $keyin_result = sql_query($keyin_sql);
    while($row = sql_fetch_array($keyin_result)) {
        // 대표가맹점 설정 사용시 master 값 사용
        if($row['mpc_id']) {
            $row['display_name'] = $row['master_pg_name'];
            $row['certi_type'] = $row['master_type'];
            $row['pg_code'] = $row['master_pg_code'];
        } else {
            $row['display_name'] = $row['mkc_pg_name'];
            $row['certi_type'] = $row['mkc_type'];
            $row['pg_code'] = $row['mkc_pg_code'];
        }
        $keyin_configs[] = $row;
    }

    // 가맹점 정보 조회
    $selected_member = sql_fetch("SELECT mb_nick, mb_hp FROM {$g5['member_table']} WHERE mb_id = '".sql_real_escape_string($selected_mb_id)."'");
}

include_once('./_head.php');
?>

<style>
.url-form-wrap {
    max-width: 600px;
    margin: 0 auto;
    padding: 15px;
}

.url-form-header {
    background: linear-gradient(135deg, #009688 0%, #00acc1 100%);
    border-radius: 8px;
    padding: 14px 20px;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.url-form-header h2 {
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* 가맹점/PG 선택 영역 */
.select-area {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 15px;
    border: 1px solid #e0e0e0;
}

.select-area h3 {
    font-size: 15px;
    font-weight: 700;
    color: #333;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #009688;
    display: flex;
    align-items: center;
    gap: 8px;
}

.select-area h3 i {
    color: #009688;
}

.select-area p {
    font-size: 12px;
    color: #888;
    margin-bottom: 10px;
}

.merchant-select-box select {
    width: 100%;
    height: 44px;
    padding: 0 40px 0 14px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23009688' d='M6 8L1 3h10z'/%3E%3C/svg%3E") no-repeat right 14px center;
    appearance: none;
    cursor: pointer;
}

.merchant-select-box select:focus {
    outline: none;
    border-color: #009688;
}

/* PG 모듈 리스트 */
.pg-module-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 12px;
}

.pg-module-item {
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 14px 16px;
    cursor: pointer;
    transition: all 0.2s;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.pg-module-item:hover {
    border-color: #009688;
    background: #f0fdf4;
}

.pg-module-item.selected {
    border-color: #009688;
    background: linear-gradient(135deg, #e0f2f1 0%, #b2dfdb 100%);
}

.pg-module-name {
    font-size: 14px;
    font-weight: 600;
    color: #333;
}

.pg-module-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 11px;
    font-weight: 600;
}

.pg-module-badge.nonauth {
    background: linear-gradient(135deg, #FF9800, #F57C00);
    color: #fff;
}

.pg-module-badge.auth {
    background: linear-gradient(135deg, #2196F3, #1976d2);
    color: #fff;
}

.no-data-msg {
    text-align: center;
    padding: 30px 0;
    color: #999;
    font-size: 13px;
}

.no-data-msg i {
    display: block;
    font-size: 28px;
    margin-bottom: 10px;
    color: #ddd;
}

/* 폼 테이블 */
.form-table-wrap {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 15px;
}

.form-table {
    width: 100%;
    border-collapse: collapse;
}

.form-table th,
.form-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    font-size: 13px;
    text-align: left;
    vertical-align: middle;
}

.form-table th {
    background: #f8f9fa;
    width: 110px;
    font-weight: 600;
    color: #333;
    white-space: nowrap;
}

.form-table td {
    background: #fff;
}

.form-table tr:last-child th,
.form-table tr:last-child td {
    border-bottom: none;
}

.form-table .section-title {
    background: #e0f2f1;
    padding: 10px 15px;
    font-weight: 600;
    color: #00796b;
    font-size: 13px;
}

.form-table .section-title i {
    margin-right: 6px;
}

.required {
    color: #e53935;
    margin-left: 2px;
}

/* 입력 요소 */
.frm-input {
    width: 100%;
    height: 38px;
    padding: 0 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 13px;
    box-sizing: border-box;
    background: #fff;
}

.frm-input:focus {
    border-color: #009688;
    outline: none;
}

.frm-input.w-half {
    width: calc(50% - 5px);
}

.frm-input.w-150 {
    width: 150px;
}

select.frm-input {
    cursor: pointer;
    padding-right: 30px;
    appearance: none;
    background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M6 8L1 3h10z'/%3E%3C/svg%3E") no-repeat right 10px center;
}

textarea.frm-input {
    height: auto;
    min-height: 70px;
    padding: 10px 12px;
    resize: vertical;
    line-height: 1.5;
}

/* 인라인 그룹 */
.input-inline {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.input-inline .frm-input {
    flex: 1;
    min-width: 120px;
}

.input-inline span {
    color: #666;
    font-size: 12px;
}

/* 체크박스 */
.chk-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    background: #f5f5f5;
    border-radius: 6px;
    cursor: pointer;
}

.chk-wrap input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #009688;
    cursor: pointer;
}

.chk-wrap label {
    cursor: pointer;
    font-size: 13px;
    color: #333;
    margin: 0;
}

/* 버튼 영역 */
.btn-area {
    padding: 20px 15px;
    background: #f8f9fa;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: center;
    gap: 10px;
}

.btn-submit {
    height: 44px;
    padding: 0 40px;
    background: linear-gradient(135deg, #009688 0%, #00796b 100%);
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-submit:hover {
    background: linear-gradient(135deg, #00796b 0%, #004d40 100%);
    transform: translateY(-1px);
}

.btn-submit:disabled {
    background: #bdbdbd;
    cursor: not-allowed;
    transform: none;
}

.btn-cancel {
    height: 44px;
    padding: 0 30px;
    background: #fff;
    color: #666;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.btn-cancel:hover {
    background: #f5f5f5;
    color: #333;
}

/* 반응형 */
@media (max-width: 600px) {
    .url-form-wrap {
        padding: 10px;
    }

    .form-table th,
    .form-table td {
        display: block;
        width: 100%;
        padding: 8px 12px;
    }

    .form-table th {
        background: #f0f0f0;
        border-bottom: none;
        padding-bottom: 5px;
    }

    .form-table td {
        padding-top: 5px;
    }

    .input-inline {
        flex-direction: column;
        align-items: stretch;
    }

    .input-inline .frm-input {
        width: 100%;
    }

    .frm-input.w-150 {
        width: 100%;
    }

    .btn-area {
        flex-direction: column;
    }

    .btn-submit, .btn-cancel {
        width: 100%;
    }

    .pg-module-item {
        padding: 12px 14px;
    }
}
</style>

<div class="url-form-wrap">
    <div class="url-form-header">
        <h2><i class="fa fa-plus-circle"></i> URL결제 생성</h2>
    </div>

    <!-- 가맹점 선택 -->
    <div class="select-area">
        <h3><i class="fa fa-users"></i> 가맹점 선택</h3>
        <p>URL결제를 생성할 가맹점을 선택하세요 <span style="color:#999;">(수기결제 허용 + Keyin 설정된 가맹점만 표시)</span></p>
        <form method="get" id="merchantSelectForm">
            <input type="hidden" name="p" value="url_payment_form">
            <div class="merchant-select-box">
                <select name="selected_mb_id" onchange="this.form.submit()">
                    <option value="">가맹점을 선택하세요</option>
                    <?php while($km = sql_fetch_array($keyin_members)) { ?>
                    <option value="<?php echo $km['mb_id']; ?>" <?php if($selected_mb_id == $km['mb_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($km['mb_nick']); ?> (<?php echo $km['mb_hp']; ?>)
                    </option>
                    <?php } ?>
                </select>
            </div>
        </form>
    </div>

    <?php if($selected_mb_id) { ?>
    <!-- PG 모듈 선택 -->
    <div class="select-area" <?php if(count($keyin_configs) == 1) echo 'style="display:none;"'; ?>>
        <h3><i class="fa fa-list"></i> PG 모듈 선택</h3>
        <?php if(empty($keyin_configs)) { ?>
        <div class="no-data-msg">
            <i class="fa fa-exclamation-triangle"></i>
            등록된 Keyin 설정이 없습니다.
        </div>
        <?php } else { ?>
        <p>결제에 사용할 PG 모듈을 선택하세요</p>
        <div class="pg-module-list">
            <?php
            $first = true;
            foreach($keyin_configs as $config) {
                $badge_class = $config['certi_type'] == 'auth' ? 'auth' : 'nonauth';
                $badge_text = $config['certi_type'] == 'auth' ? '구인증' : '비인증';
            ?>
            <div class="pg-module-item<?php echo $first ? ' selected' : ''; ?>"
                 onclick="selectPgModule(this, <?php echo $config['mkc_id']; ?>)"
                 data-mkc-id="<?php echo $config['mkc_id']; ?>">
                <div class="pg-module-name"><?php echo htmlspecialchars($config['display_name']); ?></div>
                <span class="pg-module-badge <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span>
            </div>
            <?php
                $first = false;
            }
            ?>
        </div>
        <?php } ?>
    </div>

    <?php if(!empty($keyin_configs)) { ?>
    <!-- URL결제 생성 폼 -->
    <form id="urlPaymentForm" method="post" action="./?p=url_payment_process">
        <input type="hidden" name="action" value="create">
        <input type="hidden" name="mb_id" value="<?php echo htmlspecialchars($selected_mb_id); ?>">
        <input type="hidden" name="mkc_id" id="mkc_id" value="<?php echo $keyin_configs[0]['mkc_id']; ?>">

        <div class="form-table-wrap">
            <table class="form-table">
                <!-- 상품 정보 -->
                <tr>
                    <td colspan="2" class="section-title"><i class="fa fa-shopping-cart"></i> 상품 정보</td>
                </tr>
                <tr>
                    <th>상품명 <span class="required">*</span></th>
                    <td>
                        <input type="text" name="goods_name" class="frm-input" placeholder="상품명을 입력하세요" required>
                    </td>
                </tr>
                <tr>
                    <th>결제금액 <span class="required">*</span></th>
                    <td>
                        <div class="input-inline">
                            <input type="text" name="amount" id="amount" class="frm-input w-150" placeholder="0" required oninput="formatNumber(this)">
                            <span>원</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>상품설명</th>
                    <td>
                        <textarea name="goods_desc" class="frm-input" placeholder="상품에 대한 설명 (선택사항)"></textarea>
                    </td>
                </tr>
                <tr>
                    <th>비고</th>
                    <td>
                        <textarea name="memo" class="frm-input" placeholder="관리용 메모 (선택사항)"></textarea>
                    </td>
                </tr>

                <!-- 판매자/구매자 정보 -->
                <tr>
                    <td colspan="2" class="section-title"><i class="fa fa-users"></i> 판매자 / 구매자 정보</td>
                </tr>
                <tr>
                    <th>판매자 <span class="required">*</span></th>
                    <td>
                        <div class="input-inline">
                            <input type="text" name="seller_name" class="frm-input" placeholder="판매자명" value="<?php echo htmlspecialchars($selected_member['mb_nick']); ?>" required>
                            <input type="text" name="seller_phone" class="frm-input" placeholder="연락처 (선택)" value="<?php echo htmlspecialchars($selected_member['mb_hp']); ?>">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>구매자 <span class="required">*</span></th>
                    <td>
                        <div class="input-inline">
                            <input type="text" name="buyer_name" class="frm-input" placeholder="구매자명" required>
                            <input type="text" name="buyer_phone" class="frm-input" placeholder="연락처 010-0000-0000" required>
                        </div>
                    </td>
                </tr>

                <!-- 유효기간 -->
                <tr>
                    <td colspan="2" class="section-title"><i class="fa fa-clock-o"></i> 유효기간 설정</td>
                </tr>
                <tr>
                    <th>만료일시 <span class="required">*</span></th>
                    <td>
                        <div class="input-inline">
                            <input type="text" name="expire_date" id="expire_date" class="frm-input w-150" placeholder="YYYYMMDD" required readonly>
                            <select name="expire_time" class="frm-input w-150" required>
                                <?php for($h = 0; $h < 24; $h++) { ?>
                                <option value="<?php echo sprintf('%02d', $h); ?>:00" <?php if($h == 23) echo 'selected'; ?>><?php echo sprintf('%02d', $h); ?>:00</option>
                                <?php } ?>
                            </select>
                            <span>까지</span>
                        </div>
                    </td>
                </tr>

                <!-- SMS 발송 -->
                <tr>
                    <td colspan="2" class="section-title"><i class="fa fa-envelope"></i> SMS 발송</td>
                </tr>
                <tr>
                    <th>SMS 발송</th>
                    <td>
                        <div class="chk-wrap">
                            <input type="checkbox" name="send_sms" id="send_sms" value="Y" checked>
                            <label for="send_sms">등록 후 즉시 SMS 발송</label>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- 버튼 -->
            <div class="btn-area">
                <button type="submit" class="btn-submit"><i class="fa fa-check"></i> URL결제 생성</button>
                <a href="./?p=url_payment" class="btn-cancel">취소</a>
            </div>
        </div>
    </form>
    <?php } ?>
    <?php } else { ?>
    <!-- 가맹점 미선택 시 안내 -->
    <div class="select-area">
        <div class="no-data-msg">
            <i class="fa fa-info-circle" style="color:#009688;"></i>
            먼저 가맹점을 선택해주세요.
        </div>
    </div>
    <?php } ?>
</div>

<script>
$(function() {
    // 날짜 선택기
    $('#expire_date').datepicker({
        dateFormat: 'yymmdd',
        changeMonth: true,
        changeYear: true,
        minDate: 0
    });

    // 기본 만료일 (내일)
    var tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    var expDate = tomorrow.getFullYear() + ('0' + (tomorrow.getMonth() + 1)).slice(-2) + ('0' + tomorrow.getDate()).slice(-2);
    $('#expire_date').val(expDate);

    // 폼 제출
    $('#urlPaymentForm').on('submit', function(e) {
        var amount = $('#amount').val().replace(/,/g, '');
        if(!amount || parseInt(amount) <= 0) {
            alert('결제금액을 입력해주세요.');
            $('#amount').focus();
            e.preventDefault();
            return false;
        }
    });
});

// PG 모듈 선택
function selectPgModule(el, mkcId) {
    $('.pg-module-item').removeClass('selected');
    $(el).addClass('selected');
    $('#mkc_id').val(mkcId);
}

// 숫자 포맷팅 (콤마)
function formatNumber(input) {
    var value = input.value.replace(/[^0-9]/g, '');
    if(value) {
        input.value = parseInt(value).toLocaleString();
    } else {
        input.value = '';
    }
}
</script>

<?php
include_once('./_tail.php');
?>
