# db/ - 새 DB 스키마 & 마이그레이션

기존 Gnuboard 기반 DB(`mpchosting`)를 정규화된 새 스키마로 전환하기 위한 SQL 파일들입니다.

## 파일 목록

| 파일 | 설명 | 실행 순서 |
|------|------|-----------|
| `schema_new.sql` | 새 테이블 41개 DDL (CREATE TABLE) | 1번째 |
| `migration.sql` | 기존 → 새 테이블 데이터 이관 INSERT | 2번째 (schema_new.sql 실행 후) |

---

## schema_new.sql - 테이블 구조 (41개)

### 도메인 A: 회원/계층

| 테이블 | 기존 테이블 | 설명 |
|--------|-------------|------|
| `members` | `g5_member` | 통합 회원 테이블 (mb_1~6 → parent_member_id + closure table) |
| `member_hierarchy_closure` | 신규 | 계층 관계 Closure Table (조상-후손 경로) |
| `member_documents` | `g5_member_file` | 회원 첨부서류 |

### 도메인 B: 디바이스/수수료

| 테이블 | 기존 테이블 | 설명 |
|--------|-------------|------|
| `pg_providers` | 신규 (enum→테이블) | PG사 마스터 (코페이, 다날, 페이시스 등) |
| `devices` | `g5_device` | POS/단말기 정보 |
| `device_fee_structure` | `g5_device.mb_1_fee~mb_6_fee` | 디바이스별 계층 수수료율 **(SCD2 이력 관리)** |

### 도메인 C: PG 설정

| 테이블 | 기존 테이블 | 설명 |
|--------|-------------|------|
| `pg_master_config` | `g5_manual_payment_config` | 대표가맹점 PG 설정 |
| `merchant_keyin_config` | `g5_member_keyin_config` | 가맹점별 Keyin 설정 |

### 도메인 D: 결제

| 테이블 | 기존 테이블 | 설명 |
|--------|-------------|------|
| `payments` | `g5_payment` | 결제 거래 (월별 파티셔닝) |
| `payment_fee_distribution` | `g5_payment.mb_1~6_fee/pay` | 결제별 수수료 분배 (24컬럼→행 정규화) |
| `pg_raw_notifications` | `g5_payment_k1` 등 8개 | PG NOTI 원본 통합 (월별 파티셔닝) |
| `keyin_payments` | `g5_payment_keyin` | 수기결제 |
| `url_payments` | `g5_url_payment` | URL결제 |
| `payment_memos` | `g5_payment_memo` | 결제 메모 |

### 도메인 E: Webhook/정산/SFTP

| 테이블 | 기존 테이블 | 설명 |
|--------|-------------|------|
| `webhook_configs` | `g5_member_webhook` | 웹훅 설정 |
| `webhook_history` | `g5_webhook_history` | 웹훅 발송 이력 |
| `settlement_batches` | 신규 | 정산 배치 단위 |
| `settlement_details` | 신규 | 정산 상세 내역 |
| `settlement_files` | `settle_settlement_files` | 정산 파일 |
| `settlement_exclusions` | `settle_exclude_mb3_list` | 정산 제외 목록 |
| `settlement_daily_summary` | 신규 | 일일 정산 요약 |
| `holidays` | `settle_holidays` | 공휴일 |
| `sftp_members` | `g5_sftp_member` | SFTP 차액정산 회원 |

### 도메인 F: 가상계좌

| 테이블 | 기존 테이블 | 설명 |
|--------|-------------|------|
| `va_agents` | `agent` + `agent_detail` | 대행사 |
| `va_merchants` | `mcht` + `mcht_detail` + `mcht_info` | 가맹점 |
| `va_accounts` | `vaccount` | 가상계좌 |
| `va_transactions` | `trx` | 거래 |
| `va_deposit_notifications` | `deposit_noti` | 입금 알림 |
| `va_fds` | `fds` | FDS 설정 |
| `va_fds_transactions` | `fds_trx` | FDS 거래 |
| `va_blacklist` | `vcnt_blacklist` | 블랙리스트 |

### 도메인 G: 기타

| 테이블 | 기존 테이블 | 설명 |
|--------|-------------|------|
| `notices` | `g5_write_notice` | 공지사항 |
| `qna` | `g5_write_qa` | 질문답변 |
| `metapos_stores` | `metapos_store` | MetaPOS 매장 |
| `metapos_store_changes` | `metapos_store_history` | 매장 변경 이력 |
| `metapos_payments` | `metapos_payment` | MetaPOS 결제 |
| `system_config` | `g5_config` | 시스템 설정 (KV) |
| `login_sessions` | `g5_login` | 로그인 세션 |
| `external_noti_config` | `g5_noti` | 외부 알림 설정 |
| `sms_config` | `sms5_config` | SMS 설정 |
| `sms_history` | `sms5_history` | SMS 발송 이력 |

---

## migration.sql - 데이터 이관 (8 Phase)

| Phase | 대상 | 주요 테이블 |
|-------|------|------------|
| 1 | 기반 테이블 | `holidays`, `system_config`, `sms_config` |
| 2 | 회원/계층 | `members`, `member_hierarchy_closure`, `member_documents` |
| 3 | 디바이스/PG 설정 | `devices`, `device_fee_structure`, `pg_master_config`, `merchant_keyin_config` |
| 4 | 결제 데이터 | `pg_raw_notifications`, `payments`, `payment_fee_distribution`, `keyin_payments`, `url_payments`, `payment_memos` |
| 5 | Webhook/정산/SFTP | `webhook_configs`, `webhook_history`, `settlement_files`, `settlement_exclusions`, `sftp_members` |
| 6 | 가상계좌 | `va_agents` ~ `va_blacklist` (8개) |
| 7 | 나머지 | `notices`, `qna`, `metapos_*`, `login_sessions`, `external_noti_config`, `sms_history` |
| 8 | 집계 | `settlement_daily_summary` 초기 데이터 생성 |

---

## 주요 설계 변경점

### 1. 계층 구조: 6컬럼 → Closure Table
- **기존**: `g5_member.mb_1` ~ `mb_6` (6개 컬럼에 상위 회원 ID 저장)
- **신규**: `member_hierarchy_closure` 테이블로 조상-후손 관계 정규화
- 깊이 제한 없는 유연한 계층 탐색 가능

### 2. 수수료: 6컬럼 → 행 정규화 + SCD2 이력
- **기존**: `g5_device.mb_1_fee` ~ `mb_6_fee` (디바이스당 6컬럼)
- **신규**: `device_fee_structure` 테이블로 행 정규화
- **SCD2 적용**: `effective_from` ~ `effective_to` 유효기간으로 수수료 변경 이력 보존
  - `effective_to = '9999-12-31'` → 현재 활성 수수료
  - 수수료 변경 시 기존 레코드 닫고(`effective_to` 갱신) 새 레코드 생성
  - `changed_by`, `changed_at`으로 변경 추적
  - `fee_config_id` → `payment_fee_distribution`에서 결제 시 적용된 수수료 버전 추적

### 3. PG NOTI: 8개 테이블 → 1개 통합
- **기존**: `g5_payment_k1`, `g5_payment_danal` 등 PG사별 8개 테이블
- **신규**: `pg_raw_notifications` 1개 테이블 + `raw_data` JSON 컬럼

### 4. 결제 수수료 분배: 24컬럼 → 행 정규화
- **기존**: `g5_payment.mb_1` ~ `mb_6`, `mb_1_fee` ~ `mb_6_fee`, `mb_1_pay` ~ `mb_6_pay`
- **신규**: `payment_fee_distribution` 테이블 (계층 레벨당 1행)

### 5. 파티셔닝
- `payments`: 년월 기반 RANGE 파티셔닝
- `pg_raw_notifications`: 년월 기반 RANGE 파티셔닝

---

## 실행 방법

```bash
# 1. 기존 DB 백업
mysqldump -u root -p mpchosting > backup_before_migration.sql

# 2. 새 스키마 생성
mysql -u root -p mpchosting < db/schema_new.sql

# 3. 데이터 마이그레이션
mysql -u root -p mpchosting < db/migration.sql
```

> **주의**: 반드시 백업 후 진행. `migration.sql`은 `schema_new.sql` 실행 후에 실행해야 합니다.
