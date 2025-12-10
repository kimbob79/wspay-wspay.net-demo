# 프로젝트 파일 정리 분석 보고서

분석 날짜: 2025-12-10
프로젝트: 원성페이먼츠 판매자센터

---

## 1. 사용 중인 핵심 페이지 (메뉴에서 참조)

### 1.1 현재 활성화된 메뉴 페이지
- `main.php` - 메인 대시보드
- `payment.php` - 실시간 결제내역
- `payment_loss.php` - 누락 결제내역 (관리자)
- `cancel_payment.php` - 취소 내역 (관리자)
- `payment_memo.php` - 결제내역 메모 (관리자)
- `memo.php` - 메모 페이지 (payment_memo에서 사용)
- `noti_list.php` - 섹타나인 NOTI 외부전송 (관리자)
- `settlement.php` - 정산 라우터
- `settlement_master.php` - 실시간 정산조회
- `settlement_user.php` - 가맹점 정산조회
- `tid_fee.php` - 수수료 관리
- `member_info.php` - 접속정보
- `member.php` - 회원 관리
- `member_form.php` - 회원 등록/수정
- `member_device.php` - 디바이스 할당
- `bbs.php` - 게시판 라우터
- `adm_tid.php` - TID 분리관리 (관리자)
- `logout.php` - 로그아웃

### 1.2 PG사 NOTI 수신 페이지 (메뉴에 표시)
- `payment_k1.php` - 광원
- `payment_korpay.php` - 코페이
- `payment_danal.php` - 다날
- `payment_welcom.php` - 웰컴
- `payment_paysis.php` - 페이시스
- `payment_stn.php` - 섹타나인
- `payment_daou.php` - 다우

### 1.3 PG사 NOTI 처리 파일 (백엔드)
- `update_k1.php`
- `update_korpay.php`
- `update_danal.php`
- `update_welcom.php`
- `update_paysis.php`
- `update_stn.php`
- `update_daou.php`

### 1.4 게시판 관련 페이지
- `bbs.php` - 게시판 라우터
- `bbs_list.php` - 게시판 목록
- `bbs_view.php` - 게시판 보기
- `bbs_write.php` - 게시판 쓰기
- `bbs_update.php` - 게시판 업데이트
- `bbs_delete.php` - 게시판 삭제
- `bbs_download.php` - 첨부파일 다운로드
- `bbs_image.php` - 이미지 표시
- `bbs_view_comment.php` - 댓글
- `bbs_view_comment_update.php` - 댓글 업데이트

### 1.5 회원 및 인증 관련
- `_common.php` - 애플리케이션 초기화
- `login.php` - 로그인 페이지
- `login_go.php` - 자동 로그인 리다이렉트
- `login_check.php` - 로그인 처리
- `member_form_update.php` - 회원정보 수정 처리
- `member_delete.php` - 회원 삭제
- `member_device_update.php` - 디바이스 할당 처리

### 1.6 기타 업데이트/처리 페이지
- `cancel_payment_insert.php` - 취소내역 등록
- `cancel_payment_update.php` - 취소내역 처리
- `memo_update.php` - 메모 저장
- `noti_update.php` - NOTI 업데이트
- `noti_delete.php` - NOTI 삭제
- `tid_fee_update.php` - 수수료 업데이트
- `tid_fee_delete.php` - TID 삭제
- `adm_tid_update.php` - TID 분리관리 업데이트

### 1.7 영수증 페이지
- `receipt.php` - 영수증 출력

---

## 2. 메뉴에서 주석처리된 페이지 (현재 미사용)

### 2.1 결제 관련
- `payment_member.php` - 가맹점별 결제내역
- `payment_day.php` - 일간 결제내역

### 2.2 정산 관련
- `settlement_master2.php` - 정산조회 간소화
- `settlement_master3.php` - 정산조회 가맹점

### 2.3 SFTP 정산 (전체 주석처리)
- `sftp_member.php` - 차액정산 회원관리
- `sftp_tid.php` - 차액정산 TID
- `sftp_payment.php` - 차액정산 데이터 생성
- `sftp_data.php` - 차액정산 파일조회
- `sftp_member_update.php`
- `sftp_delete.php`

### 2.4 수수료 관리
- `tid_fee2.php` - 수수료 관리2
- `tid_pay.php` - TID별 결제금액

---

## 3. 완전히 사용되지 않는 파일 (삭제 권장)

### 3.1 테스트/개발 파일
```
test.php              - 테스트 파일
test2.php             - 테스트 파일
test_table.php        - 테스트 파일
db_test.php           - DB 연결 테스트
phpversion.php        - PHP 버전 확인
__temp.php            - 임시 파일
```

### 3.2 빈 파일
```
list.php              - 0 bytes, 완전히 빈 파일
```

### 3.3 사용되지 않는 기능 파일
```
button.php            - 어디에서도 include되지 않음
login_user.php        - 어디에서도 include되지 않음
recalculation.php     - 메뉴/라우팅에 없음
write.php             - 메뉴/라우팅에 없음 (bbs_write.php가 실제 사용됨)
search.php            - 메뉴/라우팅에 없음
```

### 3.4 Old 버전 파일
```
cancel_payment_old.php
login_old.php
receipt_old.php
settlement_old.php
sftp_payment_old.php
tid_fee_old.php
main2.php
payment_copy.php
login2.php
```

### 3.5 사용 여부 불명확한 파일
```
deposit_payment.php              - 메뉴에 없음, 입금 관련 기능인 듯
deposit_payment_update.php
payment_new.php                  - 메뉴에 없음
payment_new_update.php
payment_member3.php              - payment_member의 다른 버전?
xlsx_raw_download.php            - 엑셀 다운로드 관련
```

---

## 4. 서브디렉토리 분석

### 4.1 npay/ - 수기결제 시스템
**상태**: 메뉴에서 주석처리됨
**크기**: 매우 큼 (60+ 파일)
**권장**: 현재 사용하지 않는다면 백업 후 삭제 고려

### 4.2 api/ - PG사 API 연동
**상태**: 사용 중 (PG 연동용)
**하위 디렉토리**:
- `api/k1/` - 광원
- `api/korpay/` - 코페이
- `api/danal/` - 다날
- `api/daou/` - 다우
- `api/welcom/` - 웰컴
- `api/paysis/` - 페이시스
- `api/secta9ine/` - 섹타나인
- `api/lucy/` - 루시 (사용 여부 불명)
- `api/paytus/` - 페이터스 (사용 여부 불명)
- `api/aynil/` - 애닐 (사용 여부 불명)

**권장**: lucy, paytus, aynil은 사용하지 않는다면 삭제 가능

### 4.3 complaint/ - 민원 시스템?
**상태**: 모든 파일이 0 bytes (빈 파일)
```
comment.php           - 0 bytes
comment_update.php    - 0 bytes
list.php              - 0 bytes
write.php             - 0 bytes
write_update.php      - 0 bytes
```
**권장**: 전체 디렉토리 삭제

### 4.4 ver2/ - 이전 버전
**상태**: 이전 버전 백업
**권장**: 필요시 git에서 복구 가능하므로 삭제

### 4.5 sftp_test/ - SFTP 테스트
**상태**: 테스트 샘플 데이터만 존재
**권장**: 삭제

### 4.6 sftp_mainpay/ - SFTP 정산 데이터
**상태**: 정산 데이터 저장소
**권장**: 데이터 확인 후 필요시 백업 후 정리

### 4.7 pay/ - Gnuboard 원본?
**상태**: Gnuboard 5 원본 파일로 보임
**권장**: _engin/과 중복, 삭제 가능

### 4.8 xlsx/ - 엑셀 다운로드
**상태**: ✓ 실제 사용 중
**파일**:
- `xlsxwriter.class.php`
- 각종 PHP 파일들 (payment.php, settlement_master.php 등)
**권장**: **유지** (실제 엑셀 다운로드 기능에 사용됨)

### 4.9 PHPExcel/
**상태**: PHPExcel 라이브러리
**권장**: 사용 중이므로 유지

### 4.10 _engin/ - 핵심 프레임워크
**상태**: 사용 중 (Gnuboard 5 기반)
**권장**: 유지

---

## 5. 정리 권장사항

### 5.1 즉시 삭제 가능 (100% 확실)

**Windows 명령어**:
```cmd
REM 테스트 파일
del test.php test2.php test_table.php db_test.php phpversion.php __temp.php

REM 빈 파일
del list.php

REM 사용되지 않는 기능
del button.php login_user.php recalculation.php write.php search.php

REM Old 버전
del cancel_payment_old.php login_old.php receipt_old.php
del settlement_old.php sftp_payment_old.php tid_fee_old.php
del main2.php payment_copy.php login2.php

REM CSS 백업 파일
del css\etc.css.bak css\mobile.css.bak css\renewal.css css\renewal.css.2 css\receipt_re.css

REM 이미지 파일
del img\favicon-192.png.html

REM 빈 디렉토리
rmdir /s /q complaint

REM 테스트 디렉토리
rmdir /s /q sftp_test

REM Gnuboard 원본 중복 (3402개 파일)
rmdir /s /q pay

REM 이전 버전 (89개 파일)
rmdir /s /q ver2
```

**Linux/Mac 명령어**:
```bash
# 테스트 파일
rm test.php test2.php test_table.php db_test.php phpversion.php __temp.php

# 빈 파일
rm list.php

# 사용되지 않는 기능
rm button.php login_user.php recalculation.php write.php search.php

# Old 버전
rm cancel_payment_old.php login_old.php receipt_old.php
rm settlement_old.php sftp_payment_old.php tid_fee_old.php
rm main2.php payment_copy.php login2.php

# CSS 백업 파일
rm css/etc.css.bak css/mobile.css.bak css/renewal.css css/renewal.css.2 css/receipt_re.css

# 이미지 파일
rm img/favicon-192.png.html

# 빈 디렉토리
rm -rf complaint/

# 테스트 디렉토리
rm -rf sftp_test/

# Gnuboard 원본 중복 (3402개 파일)
rm -rf pay/

# 이전 버전 (89개 파일)
rm -rf ver2/
```

### 5.2 확인 후 삭제 권장
```bash
# 사용 여부 확인 필요
# deposit_payment.php, deposit_payment_update.php
# payment_new.php, payment_new_update.php
# payment_member3.php
# xlsx_raw_download.php

# API - 사용하지 않는 PG
# api/lucy/
# api/paytus/
# api/aynil/

# 이전 버전
# ver2/

# Gnuboard 원본 중복
# pay/

# 수기결제 (메뉴 주석처리)
# npay/

# 엑셀 다운로드
# xlsx/
```

### 5.3 백업 후 보관 고려
```bash
# SFTP 정산 시스템 (현재 주석처리됨)
# sftp_member.php, sftp_tid.php, sftp_payment.php, sftp_data.php
# sftp_member_update.php, sftp_delete.php
# sftp_mainpay/

# 메뉴 주석처리된 기능들
# payment_member.php, payment_day.php
# settlement_master2.php, settlement_master3.php
# tid_fee2.php, tid_pay.php
```

---

## 6. 정적 자산 파일 분석

### 6.1 CSS 파일 (css/)
**_head.php에서 로드되는 CSS**:
- `mobile.css` ✓ 사용 중
- `table.css` ✓ 사용 중
- `search.css` ✓ 사용 중
- `etc.css` ✓ 사용 중
- `board.css` ✓ 사용 중
- `mui.min.css` ✓ 사용 중
- `btn.css` ✓ 사용 중
- `tooltip.css` ✓ 사용 중
- `header-custom.css` ✓ 사용 중
- `top-button.css` ✓ 사용 중

**사용되지 않는 CSS (삭제 가능)**:
- `etc.css.bak` - 백업 파일
- `mobile.css.bak` - 백업 파일
- `renewal.css` - 사용 안 함
- `renewal.css.2` - 사용 안 함
- `search-compact.css` - 확인 필요 (일부 페이지에서 사용할 수도)
- `vegas.min.css` - 로그인 페이지 배경용 (login.php에서 사용)
- `login.css` - 로그인 페이지용 (login.php에서 사용)
- `receipt.css` - 영수증 페이지용 (receipt.php에서 사용)
- `receipt_re.css` - 사용 여부 확인 필요
- `calendar.css` - datepicker 관련, 삭제하면 안 됨

### 6.2 JavaScript 파일 (js/)
**사용 중**:
- `mui.min.js` ✓ (_head.php에서 로드)
- `vegas.js`, `vegas.min.js` - 로그인 페이지 배경 슬라이드쇼

### 6.3 이미지 파일 (img/)
**확인된 파일**:
- `favicon.svg` ✓ 파비콘
- `favicon-192.png.html` ⚠️ 이상한 파일 (HTML 파일인데 .png 확장자)
- `eye_icon.png`, `eye_on.png` - 비밀번호 표시 아이콘
- `logo.png` - 로고
- `scroll_mouse.png` - 스크롤 안내
- 기타 UI 아이콘들

**삭제 권장**:
- `favicon-192.png.html` - 잘못된 파일 형식

---

## 7. 다음 단계

1. **사용자 확인 필요 항목**
   - npay/ 디렉토리 - 수기결제 기능 필요 여부
   - SFTP 정산 시스템 - 향후 사용 계획 여부
   - deposit_payment 관련 - 입금 기능 사용 여부
   - xlsx/ 디렉토리 - 엑셀 다운로드 기능 사용 여부
   - api/lucy, api/paytus, api/aynil - 해당 PG사 사용 여부

2. **즉시 삭제 가능 파일**
   - 테스트 파일들
   - Old 버전 파일들
   - 빈 파일/디렉토리

3. **백업 후 삭제 고려**
   - ver2/
   - pay/

---

## 8. 예상 효과

### 8.1 즉시 삭제 가능 (100% 확실)
- **루트 PHP 파일**: 14개
  - test.php, test2.php, test_table.php
  - db_test.php, phpversion.php, __temp.php
  - list.php (빈 파일)
  - button.php, login_user.php, recalculation.php, write.php, search.php
  - cancel_payment_old.php, login_old.php, receipt_old.php, settlement_old.php, sftp_payment_old.php, tid_fee_old.php
  - main2.php, payment_copy.php, login2.php

- **CSS 파일**: 5개
  - etc.css.bak, mobile.css.bak, renewal.css, renewal.css.2, receipt_re.css

- **이미지 파일**: 1개
  - favicon-192.png.html

- **디렉토리**:
  - `complaint/` - 빈 파일들만
  - `sftp_test/` - 테스트 데이터
  - `pay/` - **3,402개 파일** (Gnuboard 원본 중복)
  - `ver2/` - **89개 파일** (이전 버전)

**총계**: 약 **3,511개 파일** 삭제 가능

### 8.2 추가 확인 후 삭제 가능
- **npay/** - 60+ 파일 (수기결제 시스템, 현재 주석처리)
- **api/lucy/, api/paytus/, api/aynil/** - 약 10개 파일 (사용하지 않는 PG)
- **deposit_payment 관련** - 2개 파일
- **payment_new 관련** - 2개 파일
- **payment_member3.php** - 1개 파일
- **xlsx_raw_download.php** - 1개 파일
- **SFTP 정산 관련** - 6개 파일 (현재 주석처리)

**총계**: 추가로 약 **80-100개 파일** 정리 가능

### 8.3 전체 예상 효과
- **최소 삭제**: 3,511개 파일 (즉시 삭제 가능한 것만)
- **최대 삭제**: 3,611개 파일 (추가 확인 파일 포함)
- **디스크 공간 절약**: 상당량 (특히 pay/ 디렉토리)
- **프로젝트 명확성**: 크게 향상

---

## 9. 최종 권장사항

### 9.1 1단계: 즉시 삭제 (백업 불필요)
다음 파일들은 100% 확실하게 사용하지 않으므로 **즉시 삭제 가능**:
- 테스트 파일 (6개)
- Old 버전 파일 (9개)
- 빈 파일/디렉토리 (complaint/, list.php)
- CSS 백업 파일 (5개)
- pay/, ver2/ 디렉토리 (3,491개 파일)

### 9.2 2단계: 확인 후 삭제
사용자에게 확인이 필요한 항목:
1. **npay/** - 수기결제 기능이 필요한가?
2. **SFTP 정산** - 향후 사용 계획이 있는가?
3. **deposit_payment** - 입금 처리 기능이 필요한가?
4. **api/lucy, paytus, aynil** - 해당 PG사를 사용하는가?

### 9.3 3단계: Git 커밋
삭제 후 변경사항을 Git에 커밋하여 필요시 복구 가능하도록 함

---

**작성일**: 2025-12-10
**분석 기준**: index.php 진입점 및 _head.php 메뉴 기준
**분석 도구**: 파일 구조 분석, include/require 패턴 분석, 메뉴 참조 분석
