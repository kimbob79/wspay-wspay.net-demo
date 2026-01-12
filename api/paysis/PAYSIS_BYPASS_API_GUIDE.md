# 페이시스(Paysis) Bypass API 연동 가이드

## 개요

이 API는 IP/방화벽 제한으로 페이시스 API를 직접 호출할 수 없는 서버에서 우회 호출하기 위한 프록시 엔드포인트입니다.

---

## 엔드포인트

```
POST https://[도메인]/api/paysis/bypass.php
```

---

## 공통 요청 헤더

| 헤더 | 필수 | 설명 |
|------|:----:|------|
| `Content-Type` | O | `application/json` |
| `X-Bypass-Key` | O | Bypass 인증키 (발급받은 키 사용) |
| `X-Dal-Api-Key` | O | 페이시스 dal-api-key (가맹점별 발급) |

---

## 1. 결제 요청 (Pay)

### Request Body

```json
{
    "action": "pay",
    "data": {
        "ordNo": "주문번호",
        "mkey": "암호화키",
        "mid": "상점ID",
        "goodsAmt": "결제금액",
        "cardNo": "카드번호",
        "expireYymm": "유효기간",
        "quotaMon": "할부개월",
        "buyerNm": "구매자명",
        "goodsNm": "상품명",
        "hashKey": "해시키",
        "certPw": "비밀번호앞2자리",
        "certNo": "생년월일또는사업자번호"
    }
}
```

### 결제 요청 파라미터 상세

| 파라미터 | 필수 | 길이 | 설명 |
|----------|:----:|:----:|------|
| `ordNo` | O | 30 | 주문번호 (정확히 30자, 하이픈 없음) |
| `mkey` | O | 100 | 암호화 키 (페이시스 발급, hashKey 생성용) |
| `mid` | O | 10 | 상점 ID (페이시스 발급) |
| `goodsAmt` | O | - | 결제금액 (숫자 문자열, 예: "10000") |
| `cardNo` | O | 16 | 카드번호 (숫자만, 하이픈 없음) |
| `expireYymm` | O | 4 | 카드 유효기간 (YYMM 형식, 예: "2612") |
| `quotaMon` | O | 2 | 할부개월 (00=일시불, 02~12) |
| `buyerNm` | O | 30 | 구매자명 |
| `goodsNm` | O | 40 | 상품명 |
| `hashKey` | O | 64 | SHA256 해시키 |
| `certPw` | △ | 2 | 카드 비밀번호 앞 2자리 (구인증시 필수) |
| `certNo` | △ | 6/10 | 생년월일 6자리 또는 사업자번호 10자리 (구인증시 필수) |

> **△** : 구인증 결제 시 필수

### hashKey 생성 방법

```
hashKey = SHA256(mid + goodsAmt)
```

**PHP 예시:**
```php
$hashKey = hash('sha256', $mid . $goodsAmt);
```

**Python 예시:**
```python
import hashlib
hash_key = hashlib.sha256((mid + goods_amt).encode()).hexdigest()
```

### 주문번호(ordNo) 형식

- **길이**: 정확히 30자 (하이픈 없음)
- **형식**: `[OID][YYYYMMDD][HHMMSS][RANDOM12]`

| 구분 | 설명 | 길이 | 예시 |
|------|------|:----:|------|
| OID | 가맹점 식별코드 | 4 | `A7K3` |
| YYYYMMDD | 년월일 | 8 | `20250105` |
| HHMMSS | 시분초 | 6 | `143025` |
| RANDOM | 랜덤 영숫자 | 12 | `8F2C9E1B3D4A` |

**예시**: `A7K3202501051430258F2C9E1B3D4A`

---

## 2. 취소 요청 (Cancel)

### Request Body

```json
{
    "action": "cancel",
    "data": {
        "ordNo": "원거래주문번호",
        "mid": "상점ID",
        "orgTid": "원거래TID",
        "canAmt": "취소금액",
        "hashKey": "해시키"
    }
}
```

### 취소 요청 파라미터 상세

| 파라미터 | 필수 | 길이 | 설명 |
|----------|:----:|:----:|------|
| `ordNo` | O | 30 | 원거래 주문번호 |
| `mid` | O | 10 | 상점 ID |
| `orgTid` | O | - | 원거래 TID (승인 시 응답받은 값) |
| `canAmt` | O | - | 취소금액 (숫자 문자열) |
| `hashKey` | O | 64 | SHA256 해시키 |

### 취소 hashKey 생성 방법

```
hashKey = SHA256(mid + canAmt)
```

---

## 3. 응답

### 성공 응답 (결제)

```json
{
    "resCode": "0000",
    "resMsg": "정상처리",
    "tid": "거래고유번호",
    "appNo": "승인번호",
    "appDate": "승인일시",
    "vanIssCpCd": "발급사코드",
    "vanCpCd": "매입사코드",
    "receiptUrl": "https://wspay.net/receipt_keyin.php?pk_id=123"
}
```

> **receiptUrl**: 결제 성공 시 영수증 조회 URL이 함께 반환됩니다.

### 성공 응답 (취소)

```json
{
    "resCode": "0000",
    "resMsg": "정상처리",
    "tid": "취소거래고유번호",
    "canDate": "취소일시"
}
```

### 실패 응답

```json
{
    "resCode": "에러코드",
    "resMsg": "에러메시지"
}
```

---

## 4. 응답 코드

### 페이시스 응답 코드

| 코드 | 설명 |
|------|------|
| `0000` | 정상 처리 |
| `1001` | 파라미터 오류 |
| `1002` | 인증 실패 |
| `2001` | 카드번호 오류 |
| `2002` | 유효기간 오류 |
| `2003` | 한도 초과 |
| `2004` | 분실/도난 카드 |
| `2005` | 사용 정지 카드 |
| `3001` | 거래 거절 |
| `9999` | 시스템 오류 |

### Bypass 자체 에러 코드

| 코드 | HTTP | 설명 |
|------|:----:|------|
| `METHOD_NOT_ALLOWED` | 405 | POST 요청만 허용 |
| `IP_NOT_ALLOWED` | 403 | 허용되지 않은 IP |
| `AUTH_FAILED` | 401 | Bypass 인증키 불일치 |
| `MISSING_API_KEY` | 400 | X-Dal-Api-Key 헤더 누락 |
| `INVALID_JSON` | 400 | JSON 파싱 오류 |
| `MISSING_ACTION` | 400 | action 필드 누락 |
| `MISSING_DATA` | 400 | data 필드 누락 |
| `INVALID_ACTION` | 400 | action은 pay/cancel만 가능 |
| `CURL_ERROR` | 502 | 페이시스 API 통신 오류 |
| `PARSE_ERROR` | 502 | 페이시스 응답 파싱 오류 |

---

## 5. 예제 코드

### PHP (cURL)

```php
<?php
function callPaysisViaBypass($action, $data, $dalApiKey) {
    $url = 'https://[도메인]/api/paysis/bypass.php';
    $bypassKey = '발급받은_BYPASS_KEY';

    $requestBody = json_encode([
        'action' => $action,
        'data' => $data
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Bypass-Key: ' . $bypassKey,
        'X-Dal-Api-Key: ' . $dalApiKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return json_decode($response, true);
}

// 결제 예시
$mid = 'TEST_MID';
$amount = '10000';
$hashKey = hash('sha256', $mid . $amount);

$payData = [
    'ordNo' => 'A7K3202501051430258F2C9E1B3D4A',
    'mid' => $mid,
    'goodsAmt' => $amount,
    'cardNo' => '4000000000000001',
    'expireYymm' => '2612',
    'quotaMon' => '00',
    'buyerNm' => '홍길동',
    'goodsNm' => '테스트상품',
    'hashKey' => $hashKey
];

$result = callPaysisViaBypass('pay', $payData, 'YOUR_DAL_API_KEY');
print_r($result);
```

### Python (requests)

```python
import requests
import hashlib
import json

def call_paysis_via_bypass(action, data, dal_api_key):
    url = 'https://[도메인]/api/paysis/bypass.php'
    bypass_key = '발급받은_BYPASS_KEY'

    headers = {
        'Content-Type': 'application/json',
        'X-Bypass-Key': bypass_key,
        'X-Dal-Api-Key': dal_api_key
    }

    payload = {
        'action': action,
        'data': data
    }

    response = requests.post(url, json=payload, headers=headers, timeout=30, verify=False)
    return response.json()

# 결제 예시
mid = 'TEST_MID'
amount = '10000'
hash_key = hashlib.sha256((mid + amount).encode()).hexdigest()

pay_data = {
    'ordNo': 'A7K3202501051430258F2C9E1B3D4A',
    'mid': mid,
    'goodsAmt': amount,
    'cardNo': '4000000000000001',
    'expireYymm': '2612',
    'quotaMon': '00',
    'buyerNm': '홍길동',
    'goodsNm': '테스트상품',
    'hashKey': hash_key
}

result = call_paysis_via_bypass('pay', pay_data, 'YOUR_DAL_API_KEY')
print(result)
```

### Node.js (axios)

```javascript
const axios = require('axios');
const crypto = require('crypto');

async function callPaysisViaBypass(action, data, dalApiKey) {
    const url = 'https://[도메인]/api/paysis/bypass.php';
    const bypassKey = '발급받은_BYPASS_KEY';

    const response = await axios.post(url, {
        action: action,
        data: data
    }, {
        headers: {
            'Content-Type': 'application/json',
            'X-Bypass-Key': bypassKey,
            'X-Dal-Api-Key': dalApiKey
        },
        timeout: 30000
    });

    return response.data;
}

// 결제 예시
const mid = 'TEST_MID';
const amount = '10000';
const hashKey = crypto.createHash('sha256').update(mid + amount).digest('hex');

const payData = {
    ordNo: 'A7K3202501051430258F2C9E1B3D4A',
    mid: mid,
    goodsAmt: amount,
    cardNo: '4000000000000001',
    expireYymm: '2612',
    quotaMon: '00',
    buyerNm: '홍길동',
    goodsNm: '테스트상품',
    hashKey: hashKey
};

callPaysisViaBypass('pay', payData, 'YOUR_DAL_API_KEY')
    .then(result => console.log(result))
    .catch(err => console.error(err));
```

---

## 6. 주의사항

1. **인증키 보안**: `X-Bypass-Key`와 `X-Dal-Api-Key`는 절대 클라이언트(브라우저)에 노출되지 않도록 서버 사이드에서만 사용하세요.

2. **주문번호 유일성**: 동일한 주문번호로 중복 결제 시 오류가 발생합니다. 항상 유일한 주문번호를 생성하세요.

3. **타임아웃**: 결제 요청은 최대 30초가 소요될 수 있습니다. 클라이언트 타임아웃을 충분히 설정하세요.

4. **SSL 인증서**: 테스트 환경에서는 SSL 검증을 비활성화할 수 있으나, 운영 환경에서는 활성화를 권장합니다.

5. **IP 제한**: 허용된 IP에서만 호출 가능합니다. IP 추가가 필요하면 관리자에게 문의하세요.

---

## 7. 문의

연동 관련 문의사항은 담당자에게 연락바랍니다.
