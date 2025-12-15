# CLAUDE.md

이 파일은 Claude Code (claude.ai/code)가 이 저장소의 코드를 작업할 때 참고하는 가이드입니다.

## 프로젝트 개요

**원성페이먼츠 판매자센터** - Gnuboard 5 프레임워크를 기반으로 한 결제 가맹점 관리 시스템입니다. 한국 PG사들의 결제 거래, 정산, 다단계 가맹점 계층 구조를 관리합니다.

## 아키텍처

### 핵심 프레임워크 구조

- **엔진 디렉토리**: `_engin/` - 수정된 Gnuboard 5 코어 프레임워크
  - `common.php` - 코어 초기화, 보안 필터, 전역 함수
  - `config.php` - 시스템 상수 및 경로 정의
  - `dbconfig.php` - 데이터베이스 연결 설정
  - `theme/ryu/` - 테마 시스템

### 진입점 (Entry Points)

- **index.php** - 메인 애플리케이션 라우터
  - 인증 확인 (비로그인 사용자는 로그인 페이지로 리다이렉트)
  - `_common.php`를 include하여 프레임워크 초기화
  - `?p=` 파라미터로 페이지 라우팅 (예: `?p=payment`, `?p=settlement`)
  - `p` 파라미터 값에 해당하는 PHP 파일 로드
  - HTTPS 강제 리다이렉트

- **_common.php** - 애플리케이션 레벨 초기화
  - `_engin/common.php` include
  - `mb_level == 10`일 때 관리자 플래그 설정
  - HTTPS 강제 적용

- **_head.php** - 페이지 헤더, 네비게이션, HTML 구조
- **_tail.php** - 페이지 푸터 및 닫는 태그

### 사용자 계층 시스템

회원 및 디바이스 테이블에 저장된 7단계 계층 구조:

1. **Level 10**: 관리자 - 전체 시스템 접근 권한
2. **Level 8**: 본사 - mb_1
3. **Level 7**: 지사 - mb_2
4. **Level 6**: 총판 - mb_3
5. **Level 5**: 대리점 - mb_4
6. **Level 4**: 영업점 - mb_5
7. **Level 3**: 가맹점 - mb_6

각 레벨은 자신의 하위 레벨 데이터만 조회/관리할 수 있습니다. SQL WHERE 절에서 `mb_1` ~ `mb_6` 필드를 체크하여 접근 제어를 시행합니다.

### 데이터베이스 테이블

코드베이스에서 참조되는 주요 테이블:

- **g5_member** - 계층 레벨이 포함된 사용자 계정
- **g5_payment** - 결제 거래 레코드
- **g5_device** - TID와 수수료 구조가 포함된 POS/단말기 정보
- **g5_payment_[게이트웨이]** - 게이트웨이별 거래 데이터 (예: `g5_payment_danal`, `g5_payment_korpay`)

g5_device의 수수료 구조 필드:
- `mb_1_fee` ~ `mb_6_fee` - 각 계층 레벨별 수수료율
- `dv_tid` - 단말기 ID
- `dv_pg` - PG사 식별자
- `dv_type` - 디바이스 타입
- `dv_certi` - 인증 타입

## 페이지 모듈

모든 페이지 모듈은 루트 디렉토리에 `{모듈명}.php` 패턴으로 위치:

### 결제 관리
- **payment.php** - 실시간 결제내역 목록
- **payment_member.php** - 가맹점별 결제내역
- **payment_loss.php** - 누락 결제내역 (관리자 전용)
- **payment_day.php** - 일간 결제내역 (관리자 전용)
- **payment_memo.php** - 결제내역 메모 (관리자 전용)
- **cancel_payment.php** - 취소 내역

### PG사 연동
- **payment_[게이트웨이].php** - 각 PG사별 NOTI 수신:
  - `payment_k1.php` - 광원
  - `payment_korpay.php` - 코페이
  - `payment_danal.php` - 다날
  - `payment_welcom.php` - 웰컴
  - `payment_paysis.php` - 페이시스
  - `payment_stn.php` - 섹타나인
  - `payment_daou.php` - 다우

- **update_[게이트웨이].php** - NOTI 데이터 처리 및 DB 업데이트
  - 승인(`TXTYPE="BILL"`)과 취소(`TXTYPE="CANCEL"`) 모두 처리
  - 디바이스 수수료 구조를 기반으로 계층별 수수료 분배 계산
  - TID(단말기 ID)를 통해 거래 연결

### 정산 관리
- **settlement.php** - 레벨에 따라 settlement_master.php 또는 settlement_user.php로 라우팅
- **settlement_master.php** - 레벨 4 이상 정산 조회
- **settlement_master2.php** - 정산조회 간소화 버전 (관리자)
- **settlement_master3.php** - 가맹점별 정산조회 (관리자)
- **settlement_user.php** - 레벨 3 가맹점 정산 조회

### SFTP 정산 시스템
- **sftp_member.php** - 차액정산 회원관리
- **sftp_tid.php** - 차액정산 TID 관리
- **sftp_payment.php** - 차액정산 데이터 생성
- **sftp_data.php** - 생성된 정산파일 조회

### TID/수수료 관리
- **tid_fee.php** - 디바이스/TID 수수료 관리
- **tid_fee2.php** - 수수료 관리 대체 인터페이스 (관리자)
- **tid_pay.php** - TID별 결제금액 (관리자)
- **tid_fee_update.php** - 수수료 구조 업데이트

### 회원 관리
- **member.php** - 레벨별 회원 관리 (`?p=member&level=X`로 접근)
- **member_info.php** - 로그인/접속 정보
- **member_device.php** - 디바이스 할당
- **member_form.php** - 회원 등록/수정 폼

### 게시판 시스템
- **bbs.php** - 게시판 라우터 (Gnuboard 게시판 시스템 사용)
- `bo_table` 파라미터와 함께 `_engin/bbs/board.php`로 라우팅
- 게시판 테이블: `notice` (공지사항), `qa` (질문답변)

### 수기결제 (Keyin)
- **manual_payment.php** - 페이시스 수기결제 (비인증/구인증)
- **manual_payment_config.php** - 수기 대표가맹점 설정 (관리자 전용)
- **member_keyin_config.php** - 가맹점별 Keyin 설정
- **npay/** - 수기결제 입력 시스템 서브디렉토리

#### 가맹점 OID (Merchant OID)
대표가맹점 설정 사용 시, 동일한 PG 설정을 공유하는 가맹점들을 구분하기 위해 고유한 OID가 자동 생성됨.

- **형식**: 4자리 (첫자리 A-Z, 나머지 3자리 영숫자)
- **예시**: `A7K3`, `B2XP`, `C9M4`
- **저장 위치**: `g5_member_keyin_config.mkc_oid` (UNIQUE KEY)
- **생성 시점**: 대표가맹점 설정 사용 시에만 자동 생성 (개별설정 시 NULL)

#### 주문번호 형식 (Order Number Format)
UUID 스타일의 4-4-4-4 형식:
```
[OID]-[YYMM]-[HHMM]-[SSRR]
```

| 세그먼트 | 설명 | 예시 |
|---------|------|------|
| OID | 가맹점 OID (4자리) | `A7K3` |
| YYMM | 년월 (2자리+2자리) | `2512` (2025년 12월) |
| HHMM | 시분 (2자리+2자리) | `1430` (14시 30분) |
| SSRR | 초+랜덤 (2자리+2자리) | `52K7` |

**주문번호 예시**: `A7K3-2512-1430-52K7`

**PHP 생성 코드 예시**:
```php
function generate_order_number($merchant_oid) {
    $yymm = date('ym');           // 2512
    $hhmm = date('Hi');           // 1430
    $ss = date('s');              // 52
    $rand = strtoupper(substr(md5(microtime()), 0, 2)); // K7
    return "{$merchant_oid}-{$yymm}-{$hhmm}-{$ss}{$rand}";
}
// 결과: A7K3-2512-1430-52K7
```

#### 페이시스 수기결제 API 설정값
페이시스 수기결제 연동 시 다음 3개 값을 서버에 설정해야 함 (TID/가맹점별 고정값):

| 파라미터 | 설명 | 최대길이 | 비고 |
|---------|------|---------|------|
| **dal-api-key** | API KEY | 32자 | HTTP Header로 전송 |
| **mid** | 상점 ID | 10자 | 페이시스에서 발급 |
| **mkey** | 암호화 키 | 100자 | hashKey 생성용 |

- **API Endpoint**: `https://dalgate/api/v1/manual/pay` (POST)
- **hashKey 생성**: `sha256(mid + goodAmt)` - mid와 승인금액을 연결하여 SHA-256 해시

#### 비인증 vs 구인증 차이점
- **비인증**: 카드정보만으로 결제 (dal-api-key, mid, mkey + 카드정보)
- **구인증**: 본인인증 정보 추가 필요
  - `certPw`: 카드 비밀번호 앞 2자리
  - `certNo`: 주민번호 앞 6자리 또는 사업자등록번호 10자리

#### 페이시스 주문번호 규칙
- **필수 길이**: 정확히 30자 (하이픈 없음)
- **형식**: `XXXXYYYYMMDDHHMMSSRRRRRRRRRRRR`
- **예시**: `A7K3202512151733478F2C9E1B3D4A`

| 세그먼트 | 설명 | 길이 |
|---------|------|------|
| OID | 가맹점 OID | 4자 |
| YYYYMMDD | 년월일 | 8자 |
| HHMMSS | 시분초 | 6자 |
| RRRRRRRRRRRR | 랜덤 영숫자 | 12자 |
| **합계** | | **30자** |

#### 페이시스 API 필수 파라미터
| 파라미터 | 설명 | 비고 |
|---------|------|------|
| ordNo | 주문번호 | **정확히 30자** |
| mkey | 암호화 키 | 필수 |
| mid | 상점 ID | 필수 |
| goodsAmt | 결제금액 | 숫자 문자열 |
| cardNo | 카드번호 | 숫자만 |
| expireYymm | 유효기간 | YYMM 형식 |
| quotaMon | 할부개월 | 00=일시불 |
| buyerNm | 구매자명 | |
| goodsNm | 상품명 | |
| hashKey | 해시키 | sha256(mid+goodsAmt) |

### 메모 시스템
- **memo.php** - 결제내역 메모 관리 (pay_id 파라미터 필요)
  - 결제 정보 표시 (가맹점명, 결제코드, 금액, 승인번호 등)
  - 메모 작성 폼 (textarea)
  - 메모 내역 테이블 (작성자, 내용, 날짜)
  - XSS 방지: `nl2br(htmlspecialchars())` 사용
- **memo_update.php** - 메모 저장 처리
  - g5_payment_memo 테이블에 INSERT

## 공통 패턴

### 날짜 필터링
페이지들은 `fr_date`, `to_date` 파라미터 사용:
```php
if(!$fr_date) { $fr_date = date("Ymd"); }
if(!$to_date) { $to_date = date("Ymd"); }
```

### 접근 제어 패턴
```php
if($is_admin) {
    // 관리자 전용 SQL 또는 전체 접근
    if(adm_sql_common) {
        $adm_sql = " mb_1 IN (".adm_sql_common.")";
    } else {
        $adm_sql = " (1)";
    }
} else if($member['mb_level'] == 8) {
    $adm_sql = " mb_1 = '{$member['mb_id']}'";
} else if($member['mb_level'] == 7) {
    $adm_sql = " mb_2 = '{$member['mb_id']}'";
}
// ... 각 레벨별로 계속
```

### 검색 파라미터 패턴
대부분의 목록 페이지 지원:
- `sfl` - 검색 필드
- `stx` - 검색어
- `sst` - 정렬 필드
- `sod` - 정렬 순서 (asc/desc)
- `page` - 페이지네이션

## 프론트엔드 스택

### CSS 파일 (css/)
- **mobile.css** - 모바일 반응형 스타일
- **table.css** - 데이터 테이블 스타일
- **search.css** - 검색 폼 스타일
- **search-compact.css** - 컴팩트 검색 폼 스타일 (결제/정산 페이지용)
- **board.css** - 게시판 스타일
- **btn.css** - 버튼 스타일
- **header-custom.css** - 헤더 커스텀 디자인
- **top-button.css** - 스크롤 TOP 버튼 스타일
- **mui.min.css** - Material UI 프레임워크

### JavaScript
- **jQuery 1.12.4** - 주요 JS 라이브러리
- **jQuery UI** - 날짜 선택용 Datepicker
- **mui.min.js** - Material UI 컴포넌트
- **vegas.js** - 배경 슬라이드쇼 (로그인 페이지용)

### UI 컴포넌트
- 사이드 드로어 네비게이션 (햄버거 메뉴)
- 계층 레벨용 드롭다운 필터
- jQuery UI 기반 날짜 범위 선택기
- mui.overlay를 사용한 모달 오버레이
- Font Awesome 아이콘 시스템

### 페이지별 디자인 색상 구분
각 주요 페이지는 고유한 그라디언트 색상으로 헤더를 구분:
- **payment.php** - 블루 (#3b82f6 → #2563eb) - 실시간 결제내역
- **cancel_payment.php** - 레드 (#dc2626 → #ef4444) - 취소내역
- **settlement_master.php** - 퍼플 (#7b1fa2 → #8e24aa) - 실시간 정산조회
- **member_info.php** - 오렌지 (#ff6f00 → #ff8f00) - 회원 접속정보
- **member.php** - 그린 (#4caf50 → #66bb6a) - 가맹점 관리
- **adm_tid.php** - 블루그레이 (#607d8b → #78909c) - TID 분리관리

### 페이지 헤더/검색 UI 패턴
모든 주요 페이지는 일관된 디자인 패턴 사용:

1. **헤더 구조**:
```css
.{page}-header {
    background: linear-gradient(135deg, {color1} 0%, {color2} 100%);
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 10px;
}
```

2. **검색 영역 구조**:
```css
.{page}-search {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 10px 12px;
}
```

3. **공통 스타일 요소**:
   - 날짜 입력: `width: 90px`, `border-radius: 4px`, `background: #f8f9fa`
   - 검색 버튼: 페이지 주색상, `border-radius: 4px`, hover 효과
   - 엑셀 버튼: 녹색 (#2e7d32), Font Awesome `fa-file-excel-o` 아이콘
   - accent-color로 라디오/체크박스 색상 통일

## 개발 참고사항

### 보안 고려사항
- common.php의 `sql_escape_string()`을 통한 SQL 인젝션 방지
- 전역 변수 보호 (`$_GET['_POST']` 공격 방지)
- _common.php에서 HTTPS 강제
- `htmlspecialchars()`, `strip_tags()`를 통한 입력 값 정제

### 데이터베이스 접근
- Gnuboard의 `sql_query()`, `sql_fetch()` 함수 직접 사용
- ORM 없음 - 전체적으로 Raw SQL 쿼리 사용
- `_engin/dbconfig.php`에서 연결 설정

### PG사 NOTI 처리 흐름
1. PG사가 `payment_[게이트웨이].php`로 POST/GET 전송
2. 데이터가 `g5_payment_[게이트웨이]` 테이블에 저장
3. `update_[게이트웨이].php`에서 데이터 처리:
   - g5_device에서 TID 존재 여부 검증
   - 각 계층 레벨별 수수료 분배 계산
   - g5_payment 메인 테이블 insert/update
   - 취소 처리 (TID 앞에 'c' 붙임, 금액 음수로 설정)

### 파일 구조
- 루트 디렉토리에 모든 페이지 모듈 (100개 이상의 PHP 파일)
- `_engin/` - 코어 프레임워크 (수정된 Gnuboard 5)
- `css/`, `js/`, `img/`, `font/` - 정적 자산
- `lib/` - 추가 라이브러리 (대부분 비어있음)
- `PHPExcel/` - 엑셀 내보내기 기능
- `vendor/` - Composer 의존성 (______pmadm0501 서브디렉토리)

### 레거시 서브디렉토리
- `_old/`, `_old2/` - 이전 버전
- `______pmadm0501/` - phpMyAdmin 설치

## 주요 상수

`_engin/config.php`에 정의:
- `G5_BBS_URL` - 게시판 시스템 URL
- `G5_ADMIN_DIR` - 관리자 디렉토리 ('adm')
- `G5_DATA_DIR` - 데이터 디렉토리 ('data')
- `G5_PLUGIN_DIR` - 플러그인 디렉토리 ('plugin')
- 날짜 형식: 파라미터는 "Ymd", DB datetime은 "Y-m-d H:i:s"
- 타임존: "Asia/Seoul"

## 코드베이스 작업 가이드

### 결제 또는 정산 기능 수정 시:
1. 필요한 계층 레벨 접근 패턴 확인
2. 날짜 필터링 및 검색을 위한 기존 SQL 패턴 따르기
3. 수수료 계산이 6개 계층 레벨 모두 고려하는지 확인
4. 접근 제어 검증을 위해 다양한 사용자 레벨(3-10)로 테스트
5. PG사 연동은 `payment_[게이트웨이].php`와 `update_[게이트웨이].php` 모두 수정 필요

### 새 페이지 모듈 추가 시:
1. 루트 디렉토리에 `{모듈명}.php` 생성
2. index.php 라우팅을 통해 include (파일이 존재하면 자동)
3. 페이지 헤더용 `$title1`, `$title2` 변수 설정
4. `_head.php` 사이드바 섹션에 네비게이션 메뉴 항목 추가
   - Font Awesome 아이콘 사용 (예: `<i class="fa fa-icon-name"></i>`)
   - 활성 상태: `<?php if($p == "page_name") { echo "on"; } ?>`
5. `$member['mb_level']` 기반 적절한 접근 제어 적용
6. 페이지별 고유 색상으로 헤더/검색 영역 스타일 적용

### UI 디자인 수정 시:
1. **일관성 유지**: 기존 페이지들의 디자인 패턴 참고
2. **색상 선택**: 각 페이지는 고유한 그라디언트 색상 사용
3. **Font Awesome**: SVG 이미지 대신 Font Awesome 아이콘 사용
4. **반응형**: 모바일 대응 미디어 쿼리 포함 (`@media (max-width: 768px)`)
5. **Inline CSS vs 외부 파일**:
   - 페이지 전용 스타일은 inline `<style>` 태그 사용
   - 공통 스타일은 `css/` 디렉토리에 별도 파일로 분리

### 데이터 조회 시 주의사항:
- 날짜 검색 시 항상 시간 범위 포함: `'{$fr_dates} 00:00:00' and '{$to_dates} 23:59:59'`
- 계층별 필터링은 `mb_1` ~ `mb_6` 컬럼 사용
- 정렬 및 검색 파라미터는 SQL 인젝션 방지 처리 후 사용
- 페이지네이션은 `$page` 변수와 `$rows` (페이지당 행 수) 사용
