# URL 결제 API 가이드

원성페이먼츠 URL 결제 REST API 문서입니다.

## 개요

URL 결제 API를 통해 외부 시스템에서 결제 링크를 프로그래밍 방식으로 생성하고 관리할 수 있습니다.

### Base URL
```
https://gnushop.xyz/api/v1/
```

### 공통 사항

- **Content-Type**: `application/json` 또는 `application/x-www-form-urlencoded`
- **문자셋**: UTF-8
- **HTTP 메서드**: 각 엔드포인트에 명시된 메서드만 허용
- **CORS**: 모든 도메인 허용

---

## API 엔드포인트 목록

> **참고**: 서버가 Nginx를 사용하므로 직접 PHP 파일 경로로 접근해야 합니다.

| 엔드포인트 | 메서드 | 설명 |
|-----------|--------|------|
| `/api/v1/url_payment_create.php` | POST | URL 결제 생성 |
| `/api/v1/url_payment_list.php` | GET | URL 결제 목록 조회 |
| `/api/v1/url_payment_detail.php` | GET | URL 결제 상세 조회 |
| `/api/v1/url_payment_cancel.php` | POST | URL 결제 취소 |
| `/api/v1/url_payment_resend_sms.php` | POST | SMS 재발송 |
| `/api/v1/url_payment_pg_list.php` | GET | 가맹점 PG모듈 목록 조회 |

---

## 1. URL 결제 생성

고객에게 전송할 결제 URL을 생성합니다.

### Request

```
POST /api/v1/url_payment_create.php
Content-Type: application/json
```

### Parameters

| 파라미터 | 타입 | 필수 | 설명 |
|---------|------|------|------|
| mb_id | string | O | 가맹점 ID |
| mkc_id | integer | X | PG모듈 설정 ID (미지정시 첫 번째 PG 자동 선택) |
| goods_name | string | O | 상품명 |
| amount | integer | O | 결제금액 (원) |
| buyer_name | string | O | 구매자명 |
| buyer_phone | string | O | 구매자 연락처 |
| seller_name | string | X | 판매자명 (기본값: 가맹점명) |
| seller_phone | string | X | 판매자 연락처 (기본값: 가맹점 연락처) |
| goods_desc | string | X | 상품 설명 |
| memo | string | X | 관리용 메모 |
| expire_date | string | X | 만료일 YYYYMMDD (기본값: 내일) |
| expire_time | string | X | 만료시각 HH:MM (기본값: 23:00) |
| send_sms | string | X | SMS 발송 여부 Y/N (기본값: N) |

### Example Request

```json
{
  "mb_id": "merchant001",
  "goods_name": "테스트 상품",
  "amount": 50000,
  "buyer_name": "홍길동",
  "buyer_phone": "010-1234-5678",
  "send_sms": "Y"
}
```

### Example Response (성공)

```json
{
  "success": true,
  "message": "URL결제가 생성되었습니다.",
  "data": {
    "up_id": 123,
    "up_code": "ABC123xyz",
    "payment_url": "https://gnushop.xyz/pay/ABC123xyz",
    "amount": 50000,
    "goods_name": "테스트 상품",
    "buyer_name": "홍길동",
    "buyer_phone": "010-1234-5678",
    "seller_name": "테스트가맹점",
    "expire_datetime": "2025-01-30 23:00:59",
    "sms_sent": true,
    "sms_message": "SMS 발송 완료"
  }
}
```

### Error Responses

**가맹점에 PG모듈이 없는 경우:**
```json
{
  "success": false,
  "message": "가맹점에 등록된 수기결제 PG모듈이 없습니다. 관리자에게 문의하세요.",
  "data": {
    "mb_id": "merchant001",
    "help": "가맹점의 Keyin 설정이 필요합니다. (mkc_use=Y, mkc_status=active)"
  }
}
```

**존재하지 않는 가맹점:**
```json
{
  "success": false,
  "message": "존재하지 않는 가맹점입니다.",
  "data": {
    "mb_id": "invalid_merchant"
  }
}
```

**수기결제 미허용 가맹점:**
```json
{
  "success": false,
  "message": "수기결제가 허용되지 않은 가맹점입니다."
}
```

---

## 2. URL 결제 목록 조회

가맹점의 URL 결제 목록을 조회합니다.

### Request

```
GET /api/v1/url_payment_list.php?mb_id=merchant001&status=active&page=1&limit=20
```

### Parameters

| 파라미터 | 타입 | 필수 | 설명 |
|---------|------|------|------|
| mb_id | string | O | 가맹점 ID |
| status | string | X | 상태 필터 (active, used, expired, cancelled, all) |
| fr_date | string | X | 시작일 YYYYMMDD |
| to_date | string | X | 종료일 YYYYMMDD |
| page | integer | X | 페이지 번호 (기본값: 1) |
| limit | integer | X | 페이지당 개수 (기본값: 20, 최대: 100) |

### Example Response

```json
{
  "success": true,
  "message": "조회 완료",
  "data": {
    "total": 45,
    "page": 1,
    "limit": 20,
    "total_pages": 3,
    "items": [
      {
        "up_id": 123,
        "up_code": "ABC123xyz",
        "up_status": "active",
        "up_amount": 50000,
        "up_goods_name": "테스트 상품",
        "up_buyer_name": "홍길동",
        "up_buyer_phone": "010-1234-5678",
        "up_seller_name": "테스트가맹점",
        "up_expire_datetime": "2025-01-30 23:00:59",
        "up_paid_datetime": null,
        "up_sms_sent": "Y",
        "up_created_at": "2025-01-29 14:30:00",
        "pg_name": "페이시스",
        "payment_url": "https://gnushop.xyz/pay/ABC123xyz"
      }
    ]
  }
}
```

---

## 3. URL 결제 상세 조회

특정 URL 결제의 상세 정보를 조회합니다.

### Request

```
GET /api/v1/url_payment_detail.php?up_code=ABC123xyz
```

또는

```
GET /api/v1/url_payment_detail.php?up_id=123
```

### Parameters

| 파라미터 | 타입 | 필수 | 설명 |
|---------|------|------|------|
| up_code | string | △ | URL결제 코드 (up_code 또는 up_id 중 하나 필수) |
| up_id | integer | △ | URL결제 ID (up_code 또는 up_id 중 하나 필수) |

### Example Response

```json
{
  "success": true,
  "message": "조회 완료",
  "data": {
    "up_id": 123,
    "up_code": "ABC123xyz",
    "up_status": "used",
    "mb_id": "merchant001",
    "mkc_id": 5,
    "pg_name": "페이시스",
    "certi_type": "nonauth",
    "up_amount": 50000,
    "up_goods_name": "테스트 상품",
    "up_goods_desc": "상품 설명",
    "up_buyer_name": "홍길동",
    "up_buyer_phone": "010-1234-5678",
    "up_seller_name": "테스트가맹점",
    "up_seller_phone": "02-1234-5678",
    "up_expire_datetime": "2025-01-30 23:00:59",
    "up_memo": "",
    "up_sms_sent": "Y",
    "up_sms_count": 2,
    "up_sms_sent_datetime": "2025-01-29 14:35:00",
    "up_created_at": "2025-01-29 14:30:00",
    "payment_url": "https://gnushop.xyz/pay/ABC123xyz",
    "payment_info": {
      "pk_id": 456,
      "pk_app_no": "12345678",
      "pk_app_date": "2025-01-29 15:00:00",
      "pk_card_no_masked": "9410-****-****-1234",
      "pk_card_name": "신한카드",
      "up_paid_datetime": "2025-01-29 15:00:00"
    },
    "hierarchy": {
      "mb_1": "headquarter",
      "mb_2": "branch",
      "mb_3": "district",
      "mb_4": "agency",
      "mb_5": "shop",
      "mb_6": "merchant001",
      "mb_6_name": "테스트가맹점"
    }
  }
}
```

---

## 4. URL 결제 취소

미결제 상태의 URL 결제를 취소합니다.

> **주의**: 이미 결제 완료된 건은 이 API로 취소할 수 없습니다. 결제 취소는 별도 프로세스로 진행해야 합니다.

### Request

```
POST /api/v1/url_payment_cancel.php
Content-Type: application/json
```

### Parameters

| 파라미터 | 타입 | 필수 | 설명 |
|---------|------|------|------|
| up_code | string | △ | URL결제 코드 (up_code 또는 up_id 중 하나 필수) |
| up_id | integer | △ | URL결제 ID (up_code 또는 up_id 중 하나 필수) |

### Example Request

```json
{
  "up_code": "ABC123xyz"
}
```

### Example Response (성공)

```json
{
  "success": true,
  "message": "URL결제가 취소되었습니다.",
  "data": {
    "up_id": 123,
    "up_code": "ABC123xyz",
    "previous_status": "active",
    "current_status": "cancelled"
  }
}
```

### Error Response (결제 완료된 건)

```json
{
  "success": false,
  "message": "이미 결제 완료된 URL은 취소할 수 없습니다. 결제 취소는 별도로 진행해주세요.",
  "data": {
    "up_status": "used",
    "up_paid_datetime": "2025-01-29 15:00:00"
  }
}
```

---

## 5. SMS 재발송

활성 상태의 URL 결제에 대해 SMS를 재발송합니다.

### Request

```
POST /api/v1/url_payment_resend_sms.php
Content-Type: application/json
```

### Parameters

| 파라미터 | 타입 | 필수 | 설명 |
|---------|------|------|------|
| up_code | string | △ | URL결제 코드 (up_code 또는 up_id 중 하나 필수) |
| up_id | integer | △ | URL결제 ID (up_code 또는 up_id 중 하나 필수) |

### Example Request

```json
{
  "up_code": "ABC123xyz"
}
```

### Example Response (성공)

```json
{
  "success": true,
  "message": "SMS가 발송되었습니다.",
  "data": {
    "up_id": 123,
    "up_code": "ABC123xyz",
    "sms_count": 3,
    "buyer_phone": "010-1234-5678"
  }
}
```

---

## 6. 가맹점 PG모듈 목록 조회

가맹점에 등록된 수기결제 PG모듈 목록을 조회합니다.

URL 결제 생성 시 `mkc_id`를 지정하려면 이 API로 사용 가능한 PG 목록을 먼저 확인하세요.

### Request

```
GET /api/v1/url_payment_pg_list.php?mb_id=merchant001
```

### Parameters

| 파라미터 | 타입 | 필수 | 설명 |
|---------|------|------|------|
| mb_id | string | O | 가맹점 ID |

### Example Response

```json
{
  "success": true,
  "message": "조회 완료",
  "data": {
    "mb_id": "merchant001",
    "mb_nick": "테스트가맹점",
    "pg_count": 2,
    "pg_modules": [
      {
        "mkc_id": 5,
        "pg_name": "페이시스",
        "pg_code": "paysis",
        "certi_type": "nonauth",
        "certi_type_name": "비인증"
      },
      {
        "mkc_id": 8,
        "pg_name": "섹타나인",
        "pg_code": "stn",
        "certi_type": "auth",
        "certi_type_name": "구인증"
      }
    ]
  }
}
```

### PG모듈이 없는 경우

```json
{
  "success": true,
  "message": "조회 완료",
  "data": {
    "mb_id": "merchant001",
    "mb_nick": "테스트가맹점",
    "pg_count": 0,
    "pg_modules": []
  }
}
```

---

## 상태 코드 정리

### URL 결제 상태 (up_status)

| 상태 | 설명 |
|------|------|
| active | 활성 (결제 대기중) |
| used | 결제 완료 |
| expired | 만료됨 |
| cancelled | 취소됨 |

### HTTP 상태 코드

| 코드 | 설명 |
|------|------|
| 200 | 성공 |
| 400 | 잘못된 요청 (파라미터 오류, 상태 오류 등) |
| 404 | 리소스를 찾을 수 없음 |
| 405 | 허용되지 않은 HTTP 메서드 |
| 500 | 서버 내부 오류 |

---

## 에러 응답 형식

모든 에러는 다음 형식으로 반환됩니다:

```json
{
  "success": false,
  "message": "에러 메시지",
  "data": {
    "추가 정보": "값"
  }
}
```

---

## 연동 흐름 예시

### 1. 기본 흐름 (mkc_id 자동 선택)

```
1. POST /api/v1/url_payment_create.php
   - mb_id, goods_name, amount, buyer_name, buyer_phone 전송
   - mkc_id 미지정 시 첫 번째 활성 PG 자동 선택

2. 응답의 payment_url을 고객에게 전달 (SMS, 카카오톡 등)

3. 고객이 payment_url에서 결제 진행

4. GET /api/v1/url_payment_detail.php?up_code=XXX
   - 결제 상태 확인 (up_status가 'used'면 결제 완료)
```

### 2. PG모듈 지정 흐름

```
1. GET /api/v1/url_payment_pg_list.php?mb_id=merchant001
   - 사용 가능한 PG 목록 확인

2. POST /api/v1/url_payment_create.php
   - mkc_id에 원하는 PG의 mkc_id 지정

3. 이후 동일
```

---

## 주의사항

1. **PG모듈 미설정**: 가맹점에 수기결제 PG모듈이 등록되어 있지 않으면 URL 결제를 생성할 수 없습니다. 관리자에게 Keyin 설정을 요청하세요.

2. **만료 시간**: 만료일시가 지난 URL은 고객이 접근해도 결제가 불가능합니다.

3. **SMS 발송**: SMS 발송은 선택사항이며, 발송 실패해도 URL 결제 자체는 정상 생성됩니다.

4. **결제 취소**: 이 API의 취소 기능은 **미결제 URL을 비활성화**하는 것입니다. 실제 결제 완료된 건의 취소는 별도 결제 취소 프로세스를 따라야 합니다.

---

## 문의

- 기술 지원: support@wspay.net
- API 관련 문의: api@wspay.net
