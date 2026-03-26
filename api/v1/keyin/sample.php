<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Keyin API 테스트</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: -apple-system, 'Malgun Gothic', sans-serif; background: #f0f0f0; padding: 20px; }
.container { max-width: 900px; margin: 0 auto; }
h1 { font-size: 20px; color: #393E46; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
h1 span { background: #FFD369; color: #393E46; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 700; }
.tabs { display: flex; gap: 4px; margin-bottom: 0; }
.tab { padding: 10px 20px; background: #ddd; border: none; border-radius: 8px 8px 0 0; cursor: pointer; font-size: 14px; font-weight: 600; color: #666; }
.tab.active { background: #fff; color: #393E46; }
.panel { display: none; background: #fff; border-radius: 0 8px 8px 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.panel.active { display: block; }
.form-row { display: flex; gap: 12px; margin-bottom: 12px; }
.form-group { flex: 1; }
.form-group label { display: block; font-size: 12px; font-weight: 600; color: #666; margin-bottom: 4px; }
.form-group input, .form-group select { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; }
.form-group input:focus, .form-group select:focus { outline: none; border-color: #FFD369; }
.form-group.full { flex: 1 1 100%; }
.btn { padding: 12px 24px; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; }
.btn-pay { background: #393E46; color: #FFD369; width: 100%; margin-top: 8px; }
.btn-pay:hover { background: #4a5058; }
.btn-list { background: #7b1fa2; color: #fff; width: 100%; margin-top: 8px; }
.btn-list:hover { background: #6a1b9a; }
.result-area { margin-top: 16px; }
.result-area h3 { font-size: 14px; font-weight: 600; color: #333; margin-bottom: 8px; }
.result-box { background: #1e1e1e; color: #d4d4d4; padding: 16px; border-radius: 8px; font-family: 'Consolas', 'Monaco', monospace; font-size: 13px; white-space: pre-wrap; word-break: break-all; max-height: 400px; overflow-y: auto; line-height: 1.6; }
.result-box .key { color: #9cdcfe; }
.result-box .string { color: #ce9178; }
.result-box .number { color: #b5cea8; }
.result-box .bool { color: #569cd6; }
.result-box .null { color: #569cd6; }
.status-badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; margin-left: 8px; }
.status-badge.success { background: #e8f5e9; color: #2e7d32; }
.status-badge.fail { background: #ffebee; color: #c62828; }
.status-badge.loading { background: #fff3e0; color: #e65100; }
.info-box { background: #FFF8E1; border: 1px solid #FFD369; border-radius: 6px; padding: 12px; margin-bottom: 16px; font-size: 12px; color: #666; line-height: 1.6; }
.info-box code { background: #f5f5f5; padding: 1px 4px; border-radius: 3px; font-family: monospace; color: #333; }
.separator { border: none; border-top: 1px dashed #e0e0e0; margin: 16px 0; }
@media (max-width: 768px) {
    .form-row { flex-direction: column; gap: 8px; }
    body { padding: 10px; }
}
</style>
</head>
<body>
<div class="container">
    <h1>Keyin API <span>SAMPLE</span></h1>

    <div class="info-box">
        <strong>API Endpoint</strong><br>
        결제: <code>POST /api/v1/keyin/pay.php</code><br>
        조회: <code>GET /api/v1/keyin/list.php</code><br>
        헤더: <code>X-API-Key</code>, <code>X-TID</code>, <code>Content-Type: application/json</code>
    </div>

    <!-- 인증 정보 (공통) -->
    <div style="background:#fff; border-radius:8px; padding:16px; margin-bottom:12px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
        <div style="font-size:13px; font-weight:600; color:#393E46; margin-bottom:10px;">인증 정보</div>
        <div class="form-row">
            <div class="form-group" style="flex:2;">
                <label>API Key (X-API-Key)</label>
                <input type="text" id="api_key" placeholder="ssp-xxxxxxxxxxxx...">
            </div>
            <div class="form-group">
                <label>TID (X-TID)</label>
                <input type="text" id="tid" placeholder="MID 또는 TID">
            </div>
        </div>
        <div class="form-row" style="margin-bottom:0;">
            <div class="form-group">
                <label>API URL (기본: 현재 서버)</label>
                <input type="text" id="api_url" value="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/api/v1/keyin">
            </div>
        </div>
    </div>

    <!-- 탭 -->
    <div class="tabs">
        <button class="tab active" onclick="switchTab('pay')">결제 테스트</button>
        <button class="tab" onclick="switchTab('list')">내역 조회</button>
    </div>

    <!-- 결제 탭 -->
    <div class="panel active" id="panel-pay">
        <div class="form-row">
            <div class="form-group">
                <label>결제금액 *</label>
                <input type="number" id="amount" value="1000" min="100">
            </div>
            <div class="form-group">
                <label>할부개월</label>
                <select id="installment">
                    <option value="00">일시불</option>
                    <option value="02">2개월</option>
                    <option value="03">3개월</option>
                    <option value="06">6개월</option>
                    <option value="12">12개월</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>카드번호 *</label>
                <input type="text" id="card_no" placeholder="1234567890123456" maxlength="16">
            </div>
            <div class="form-group" style="flex:0 0 120px;">
                <label>유효기간 (YYMM) *</label>
                <input type="text" id="expire_yymm" placeholder="2612" maxlength="4">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>상품명 *</label>
                <input type="text" id="goods_name" value="테스트상품">
            </div>
            <div class="form-group">
                <label>구매자명 *</label>
                <input type="text" id="buyer_name" value="홍길동">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>구매자 전화번호</label>
                <input type="text" id="buyer_phone" placeholder="01012345678">
            </div>
            <div class="form-group">
                <label>구매자 이메일 (윈글로벌 필수)</label>
                <input type="text" id="buyer_email" placeholder="test@example.com">
            </div>
        </div>

        <hr class="separator">
        <div style="font-size:12px; color:#888; margin-bottom:8px;">구인증 (선택)</div>
        <div class="form-row">
            <div class="form-group">
                <label>카드 비밀번호 앞 2자리</label>
                <input type="password" id="cert_pw" placeholder="**" maxlength="2">
            </div>
            <div class="form-group">
                <label>주민번호 앞6자리 / 사업자번호 10자리</label>
                <input type="text" id="cert_no" placeholder="990101 또는 1234567890" maxlength="10">
            </div>
        </div>

        <button class="btn btn-pay" onclick="doPayment()">결제 요청</button>

        <div class="result-area" id="pay-result" style="display:none;">
            <h3>응답 <span class="status-badge" id="pay-status"></span></h3>
            <div class="result-box" id="pay-response"></div>
        </div>
    </div>

    <!-- 조회 탭 -->
    <div class="panel" id="panel-list">
        <div class="form-row">
            <div class="form-group">
                <label>시작일 (YYYYMMDD)</label>
                <input type="text" id="fr_date" value="<?php echo date('Ymd'); ?>" maxlength="8">
            </div>
            <div class="form-group">
                <label>종료일 (YYYYMMDD)</label>
                <input type="text" id="to_date" value="<?php echo date('Ymd'); ?>" maxlength="8">
            </div>
            <div class="form-group">
                <label>상태</label>
                <select id="status">
                    <option value="">전체</option>
                    <option value="approved">승인</option>
                    <option value="failed">실패</option>
                    <option value="pending">대기</option>
                    <option value="cancelled">취소</option>
                </select>
            </div>
            <div class="form-group" style="flex:0 0 80px;">
                <label>페이지</label>
                <input type="number" id="page" value="1" min="1">
            </div>
        </div>

        <button class="btn btn-list" onclick="doList()">조회</button>

        <div class="result-area" id="list-result" style="display:none;">
            <h3>응답 <span class="status-badge" id="list-status"></span></h3>
            <div class="result-box" id="list-response"></div>
        </div>
    </div>
</div>

<script>
function switchTab(tab) {
    document.querySelectorAll('.tab').forEach(function(t) { t.classList.remove('active'); });
    document.querySelectorAll('.panel').forEach(function(p) { p.classList.remove('active'); });
    document.getElementById('panel-' + tab).classList.add('active');
    event.target.classList.add('active');
}

function syntaxHighlight(json) {
    if (typeof json !== 'string') json = JSON.stringify(json, null, 2);
    json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function(match) {
        var cls = 'number';
        if (/^"/.test(match)) {
            if (/:$/.test(match)) { cls = 'key'; } else { cls = 'string'; }
        } else if (/true|false/.test(match)) { cls = 'bool'; }
        else if (/null/.test(match)) { cls = 'null'; }
        return '<span class="' + cls + '">' + match + '</span>';
    });
}

function setStatus(id, success) {
    var el = document.getElementById(id);
    if (success === null) { el.className = 'status-badge loading'; el.textContent = '요청중...'; }
    else if (success) { el.className = 'status-badge success'; el.textContent = 'SUCCESS'; }
    else { el.className = 'status-badge fail'; el.textContent = 'FAIL'; }
}

function doPayment() {
    var apiKey = document.getElementById('api_key').value.trim();
    var tid = document.getElementById('tid').value.trim();
    var baseUrl = document.getElementById('api_url').value.trim();

    if (!apiKey || !tid) { alert('API Key와 TID를 입력하세요.'); return; }

    var data = {
        amount: parseInt(document.getElementById('amount').value) || 0,
        goods_name: document.getElementById('goods_name').value.trim(),
        buyer_name: document.getElementById('buyer_name').value.trim(),
        buyer_phone: document.getElementById('buyer_phone').value.trim(),
        buyer_email: document.getElementById('buyer_email').value.trim(),
        card_no: document.getElementById('card_no').value.trim(),
        expire_yymm: document.getElementById('expire_yymm').value.trim(),
        installment: document.getElementById('installment').value
    };

    var certPw = document.getElementById('cert_pw').value.trim();
    var certNo = document.getElementById('cert_no').value.trim();
    if (certPw) data.cert_pw = certPw;
    if (certNo) data.cert_no = certNo;

    document.getElementById('pay-result').style.display = 'block';
    setStatus('pay-status', null);
    document.getElementById('pay-response').innerHTML = '요청중...';

    fetch(baseUrl + '/pay.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-API-Key': apiKey,
            'X-TID': tid
        },
        body: JSON.stringify(data)
    })
    .then(function(res) { return res.json(); })
    .then(function(json) {
        setStatus('pay-status', json.success);
        document.getElementById('pay-response').innerHTML = syntaxHighlight(json);
    })
    .catch(function(err) {
        setStatus('pay-status', false);
        document.getElementById('pay-response').textContent = 'Error: ' + err.message;
    });
}

function doList() {
    var apiKey = document.getElementById('api_key').value.trim();
    var tid = document.getElementById('tid').value.trim();
    var baseUrl = document.getElementById('api_url').value.trim();

    if (!apiKey || !tid) { alert('API Key와 TID를 입력하세요.'); return; }

    var params = new URLSearchParams({
        fr_date: document.getElementById('fr_date').value.trim(),
        to_date: document.getElementById('to_date').value.trim(),
        status: document.getElementById('status').value,
        page: document.getElementById('page').value,
        limit: 20
    });

    document.getElementById('list-result').style.display = 'block';
    setStatus('list-status', null);
    document.getElementById('list-response').innerHTML = '요청중...';

    fetch(baseUrl + '/list.php?' + params.toString(), {
        method: 'GET',
        headers: {
            'X-API-Key': apiKey,
            'X-TID': tid
        }
    })
    .then(function(res) { return res.json(); })
    .then(function(json) {
        setStatus('list-status', json.success);
        document.getElementById('list-response').innerHTML = syntaxHighlight(json);
    })
    .catch(function(err) {
        setStatus('list-status', false);
        document.getElementById('list-response').textContent = 'Error: ' + err.message;
    });
}
</script>
</body>
</html>
