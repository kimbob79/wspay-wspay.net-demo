-- ============================================================================
-- 원성페이먼츠 판매자센터 - 새 DB 스키마
-- 생성일: 2026-03-14
-- 대상 DB: mpchosting (MariaDB/InnoDB)
-- 총 41개 테이블 (payments, pg_raw_notifications 월별 파티셔닝 적용)
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- 도메인 A: 회원/계층 (Member & Hierarchy)
-- ============================================================================

-- A1. members - 통합 회원 테이블
-- 기존: g5_member
-- 변경: mb_homepage→base_fee_rate, mb_sex→original_level, mb_mailling→is_keyin_allowed,
--       mb_7→biz_reg_no, mb_8/9/10→bank_name/account_number/account_holder,
--       mb_1~6→parent_member_id + closure table
CREATE TABLE members (
    member_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    login_id        VARCHAR(50) NOT NULL COMMENT '로그인 아이디 (기존 mb_id)',
    password_hash   VARCHAR(255) NOT NULL COMMENT '기존 mb_password',
    name            VARCHAR(100) NOT NULL COMMENT '대표자명 (기존 mb_name)',
    company_name    VARCHAR(100) NOT NULL COMMENT '상호명/업체명 (기존 mb_nick)',
    email           VARCHAR(100) DEFAULT NULL,
    phone           VARCHAR(20) DEFAULT NULL COMMENT '전화번호 (기존 mb_tel)',
    mobile          VARCHAR(20) DEFAULT NULL COMMENT '휴대전화 (기존 mb_hp)',

    -- 사업자 정보 (기존 mb_7 오용 해소)
    biz_reg_no      VARCHAR(12) DEFAULT NULL COMMENT '사업자등록번호 (기존 mb_7)',

    -- 주소
    zipcode         VARCHAR(7) DEFAULT NULL COMMENT '기존 mb_zip1+mb_zip2',
    address1        VARCHAR(255) DEFAULT NULL COMMENT '기존 mb_addr1',
    address2        VARCHAR(255) DEFAULT NULL COMMENT '기존 mb_addr2',
    address_jibeon  VARCHAR(255) DEFAULT NULL COMMENT '기존 mb_addr_jibeon',

    -- 계좌 정보 (기존 mb_8,9,10 오용 해소)
    bank_name       VARCHAR(50) DEFAULT NULL COMMENT '은행명 (기존 mb_8)',
    account_number  VARCHAR(50) DEFAULT NULL COMMENT '계좌번호 (기존 mb_9)',
    account_holder  VARCHAR(50) DEFAULT NULL COMMENT '예금주명 (기존 mb_10)',
    account_memo    TEXT DEFAULT NULL COMMENT '계좌메모 (기존 mb_11)',

    -- 계층 정보
    hierarchy_level TINYINT UNSIGNED NOT NULL COMMENT '3=가맹점,4=영업점,5=대리점,6=총판,7=지사,8=본사,10=관리자',
    parent_member_id INT UNSIGNED DEFAULT NULL COMMENT '직속 상위 회원 ID (adjacency list)',

    -- 수수료 (기존 mb_homepage 오용 해소)
    base_fee_rate   DECIMAL(5,3) DEFAULT NULL COMMENT '기본 수수료율 (%)',
    van_fee         INT UNSIGNED DEFAULT 0 COMMENT 'VAN 수수료 (기존 mb_van_fee)',

    -- 플래그
    is_keyin_allowed TINYINT(1) DEFAULT 0 COMMENT '수기결제 허용 여부 (기존 mb_mailling)',
    is_keyin_popup   TINYINT(1) DEFAULT 1 COMMENT '수기결제창 사용 여부 (기존 mb_keyin_popup)',
    settle_type     CHAR(1) DEFAULT 'N' COMMENT '재정산 구분 (기존 mb_settle_gbn)',

    -- 외부 연동
    sushian_store_id VARCHAR(50) DEFAULT NULL COMMENT 'POS매장 매장 ID (기존 mb_sushian_id)',

    -- 상태/감사
    status          ENUM('active','suspended','deleted') DEFAULT 'active',
    original_level  TINYINT UNSIGNED DEFAULT NULL COMMENT '삭제 전 원래 레벨 (기존 mb_sex)',
    last_login_at   DATETIME DEFAULT NULL COMMENT '기존 mb_today_login',
    last_login_ip   VARCHAR(45) DEFAULT NULL COMMENT '기존 mb_login_ip',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 mb_datetime',
    updated_at      DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME DEFAULT NULL,

    UNIQUE KEY uk_login_id (login_id),
    KEY idx_hierarchy_level (hierarchy_level),
    KEY idx_parent (parent_member_id),
    KEY idx_status (status),
    KEY idx_company_name (company_name),
    KEY idx_last_login (last_login_at),
    CONSTRAINT fk_member_parent FOREIGN KEY (parent_member_id) REFERENCES members(member_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='통합 회원 (기존 g5_member)';


-- A2. member_hierarchy_closure - 계층 Closure Table
-- 기존: 없음 (신규)
-- 용도: mb_1~6 하드코딩 접근 제어 → JOIN 1줄로 대체
CREATE TABLE member_hierarchy_closure (
    ancestor_id     INT UNSIGNED NOT NULL COMMENT '조상 회원 ID',
    descendant_id   INT UNSIGNED NOT NULL COMMENT '자손 회원 ID',
    depth           TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '관계 깊이 (0=자기자신)',

    PRIMARY KEY (ancestor_id, descendant_id),
    KEY idx_descendant (descendant_id),
    KEY idx_depth (depth),
    CONSTRAINT fk_closure_ancestor FOREIGN KEY (ancestor_id) REFERENCES members(member_id) ON DELETE CASCADE,
    CONSTRAINT fk_closure_descendant FOREIGN KEY (descendant_id) REFERENCES members(member_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='회원 계층 Closure Table (접근 제어용)';


-- A3. member_documents - 회원 서류
-- 기존: g5_member_file
CREATE TABLE member_documents (
    doc_id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id       INT UNSIGNED NOT NULL,
    doc_type        ENUM('id_card','biz_reg','bankbook','contract','seal','other') NOT NULL COMMENT '문서 종류',
    doc_no          TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '같은 타입 내 순번 (기존 bf_no)',
    original_name   VARCHAR(255) NOT NULL COMMENT '기존 bf_source',
    stored_name     VARCHAR(255) NOT NULL COMMENT '기존 bf_file',
    file_size       INT UNSIGNED DEFAULT 0 COMMENT '기존 bf_filesize',
    file_width      INT UNSIGNED DEFAULT 0,
    file_height     INT UNSIGNED DEFAULT 0,
    download_count  INT UNSIGNED DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 bf_datetime',

    KEY idx_member (member_id),
    CONSTRAINT fk_doc_member FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='회원 서류 (기존 g5_member_file)';


-- ============================================================================
-- 도메인 B: 디바이스/TID
-- ============================================================================

-- B1. pg_providers - PG사 마스터 (devices FK 참조를 위해 먼저 생성)
-- 기존: 없음 (신규)
CREATE TABLE pg_providers (
    pg_id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pg_code         VARCHAR(20) NOT NULL COMMENT '내부 코드 (paysis, k1, danal, stn, daou, korpay, welcom, routeup)',
    pg_name         VARCHAR(50) NOT NULL COMMENT '표시명',
    pg_type         VARCHAR(20) DEFAULT NULL COMMENT 'PG 유형',
    api_base_url    VARCHAR(255) DEFAULT NULL,
    noti_endpoint   VARCHAR(255) DEFAULT NULL COMMENT 'NOTI 수신 엔드포인트',
    status          ENUM('active','inactive') DEFAULT 'active',
    sort_order      INT DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uk_pg_code (pg_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='PG사 마스터';


-- B2. devices - 디바이스 (TID)
-- 기존: g5_device
-- 변경: mb_1~6 제거, merchant_id FK로 대체
CREATE TABLE devices (
    device_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tid             VARCHAR(30) NOT NULL COMMENT '단말기 ID (기존 dv_tid)',
    merchant_id     INT UNSIGNED NOT NULL COMMENT '가맹점 (level=3) 회원 ID (기존 mb_6)',
    pg_provider_id  INT UNSIGNED NOT NULL COMMENT 'PG사 (기존 dv_pg 코드 → FK)',
    device_type     TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '1=일반, 2=수기 등 (기존 dv_type)',
    cert_type       VARCHAR(20) DEFAULT NULL COMMENT '인증 타입 (기존 dv_certi)',
    jungsan_type    INT DEFAULT NULL COMMENT '정산 구분 (기존 dv_jungsan)',
    open_date       DATE DEFAULT NULL COMMENT '개통일 (기존 dv_open_date)',
    agent_name      VARCHAR(50) DEFAULT NULL COMMENT '에이전트 (기존 dv_agent)',
    device_number   VARCHAR(20) DEFAULT NULL COMMENT '기기번호 (기존 dv_number)',
    model           VARCHAR(20) DEFAULT NULL COMMENT '모델 (기존 dv_model)',
    model_number    VARCHAR(20) DEFAULT NULL COMMENT '모델번호 (기존 dv_model_number)',
    serial_number   VARCHAR(255) DEFAULT NULL COMMENT '시리얼 (기존 dv_sn)',
    usim            VARCHAR(20) DEFAULT NULL COMMENT 'USIM (기존 dv_usim)',
    usim_number     VARCHAR(20) DEFAULT NULL COMMENT 'USIM 번호 (기존 dv_usim_number)',
    history         TEXT DEFAULT NULL COMMENT '이력 메모 (기존 dv_history)',
    sftp_mbrno      VARCHAR(20) DEFAULT NULL COMMENT 'SFTP 회원번호 (기존 sftp_mbrno)',
    status          ENUM('active','inactive','deleted') DEFAULT 'active',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 datetime',
    updated_at      DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '기존 updatetime',

    UNIQUE KEY uk_tid (tid),
    KEY idx_merchant (merchant_id),
    KEY idx_pg (pg_provider_id),
    KEY idx_status (status),
    KEY idx_sftp (sftp_mbrno),
    CONSTRAINT fk_device_merchant FOREIGN KEY (merchant_id) REFERENCES members(member_id),
    CONSTRAINT fk_device_pg FOREIGN KEY (pg_provider_id) REFERENCES pg_providers(pg_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='디바이스/단말기 (기존 g5_device)';


-- B3. device_fee_structure - 디바이스별 계층 수수료 구조
-- 기존: g5_device의 mb_1_fee~mb_6_fee 6컬럼
-- 변경: 행으로 정규화
CREATE TABLE device_fee_structure (
    fee_id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    device_id       INT UNSIGNED NOT NULL,
    member_id       INT UNSIGNED NOT NULL COMMENT '수수료 적용 대상 회원',
    hierarchy_level TINYINT UNSIGNED NOT NULL COMMENT '해당 회원의 계층 레벨',
    fee_rate        DECIMAL(5,3) NOT NULL DEFAULT 0.000 COMMENT '수수료율 (%)',

    -- SCD2 유효기간
    effective_from  DATE NOT NULL COMMENT '적용 시작일 (inclusive)',
    effective_to    DATE NOT NULL DEFAULT '9999-12-31' COMMENT '적용 종료일 (inclusive, 9999-12-31=현재 활성)',

    -- 변경 추적
    changed_by      INT UNSIGNED DEFAULT NULL COMMENT '변경 실행자 회원 ID',
    changed_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '레코드 생성 시각',

    UNIQUE KEY uk_device_member_period (device_id, member_id, effective_from),
    KEY idx_device (device_id),
    KEY idx_member (member_id),
    KEY idx_current (device_id, effective_to),
    KEY idx_effective_range (device_id, member_id, effective_to, effective_from),
    CONSTRAINT fk_fee_device FOREIGN KEY (device_id) REFERENCES devices(device_id) ON DELETE CASCADE,
    CONSTRAINT fk_fee_member FOREIGN KEY (member_id) REFERENCES members(member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='디바이스별 계층 수수료율 SCD2 이력 (기존 mb_1_fee~mb_6_fee 정규화)';


-- ============================================================================
-- 도메인 C: PG사 관리
-- ============================================================================

-- C1. pg_master_config - 대표가맹점 PG 설정
-- 기존: g5_manual_payment_config
-- 변경: PG사별 컬럼(mpc_rootup_*, mpc_stn_*, mpc_winglobal_*) → extra_config JSON
CREATE TABLE pg_master_config (
    config_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pg_provider_id  INT UNSIGNED NOT NULL,
    auth_type       VARCHAR(20) NOT NULL COMMENT 'nonauth/auth (기존 mpc_type)',

    -- 공통 인증 정보
    api_key         VARCHAR(100) DEFAULT NULL COMMENT '기존 mpc_api_key',
    mid             VARCHAR(50) DEFAULT NULL COMMENT '상점 ID (기존 mpc_mid)',
    mkey            VARCHAR(200) DEFAULT NULL COMMENT '암호화 키 (기존 mpc_mkey)',

    -- PG사별 추가 인증 정보 (JSON으로 확장성 확보)
    extra_config    JSON DEFAULT NULL COMMENT 'PG사별 추가 설정 (rootup_tid, stn_mbrno, winglobal_tid 등)',

    is_active       TINYINT(1) DEFAULT 1 COMMENT '기존 mpc_use',
    memo            TEXT DEFAULT NULL COMMENT '기존 mpc_memo',
    status          ENUM('active','deleted') DEFAULT 'active' COMMENT '기존 mpc_status',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 mpc_datetime',
    updated_at      DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '기존 mpc_update',

    KEY idx_pg (pg_provider_id),
    KEY idx_status (status),
    CONSTRAINT fk_pgconfig_pg FOREIGN KEY (pg_provider_id) REFERENCES pg_providers(pg_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='대표가맹점 PG 설정 (기존 g5_manual_payment_config)';


-- C2. merchant_keyin_config - 가맹점별 Keyin 설정
-- 기존: g5_member_keyin_config
-- 변경: mb_id varchar → member_id INT FK
CREATE TABLE merchant_keyin_config (
    mkc_id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id       INT UNSIGNED NOT NULL COMMENT '가맹점 회원 ID (기존 mb_id)',
    master_config_id INT UNSIGNED DEFAULT NULL COMMENT '대표가맹점 설정 ID (기존 mpc_id)',

    -- 개별 설정 시 사용
    pg_provider_id  INT UNSIGNED DEFAULT NULL COMMENT '기존 mkc_pg_code → FK',
    auth_type       VARCHAR(20) DEFAULT NULL COMMENT '기존 mkc_type',
    api_key         VARCHAR(100) DEFAULT NULL COMMENT '기존 mkc_api_key',
    mid             VARCHAR(50) DEFAULT NULL COMMENT '기존 mkc_mid',
    mkey            VARCHAR(200) DEFAULT NULL COMMENT '기존 mkc_mkey',

    -- OID
    merchant_oid    CHAR(4) DEFAULT NULL COMMENT '가맹점 OID (기존 mkc_oid)',

    -- 결제 제한
    cancel_allowed  TINYINT(1) DEFAULT 1 COMMENT '기존 mkc_cancel_yn',
    duplicate_allowed TINYINT(1) DEFAULT 0 COMMENT '기존 mkc_duplicate_yn',
    duplicate_limit INT UNSIGNED DEFAULT 0 COMMENT '일 중복결제 허용 횟수',
    weekend_allowed TINYINT(1) DEFAULT 1 COMMENT '기존 mkc_weekend_yn',
    limit_once      INT UNSIGNED DEFAULT 0 COMMENT '1회 한도 (기존 mkc_limit_once)',
    limit_daily     INT UNSIGNED DEFAULT 0 COMMENT '일일 한도 (기존 mkc_limit_daily)',
    limit_monthly   INT UNSIGNED DEFAULT 0 COMMENT '월 한도 (기존 mkc_limit_monthly)',
    max_installment TINYINT UNSIGNED DEFAULT 12 COMMENT '최대 할부 개월 (기존 mkc_max_installment)',
    time_start      TIME DEFAULT '00:00:00' COMMENT '기존 mkc_time_start',
    time_end        TIME DEFAULT '23:59:59' COMMENT '기존 mkc_time_end',

    is_active       TINYINT(1) DEFAULT 1 COMMENT '기존 mkc_use',
    memo            TEXT DEFAULT NULL,
    status          ENUM('active','deleted') DEFAULT 'active' COMMENT '기존 mkc_status',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 mkc_datetime',
    updated_at      DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '기존 mkc_update',

    KEY idx_member (member_id),
    KEY idx_master (master_config_id),
    KEY idx_status (status),
    UNIQUE KEY uk_oid (merchant_oid),
    CONSTRAINT fk_mkc_member FOREIGN KEY (member_id) REFERENCES members(member_id),
    CONSTRAINT fk_mkc_master FOREIGN KEY (master_config_id) REFERENCES pg_master_config(config_id) ON DELETE SET NULL,
    CONSTRAINT fk_mkc_pg FOREIGN KEY (pg_provider_id) REFERENCES pg_providers(pg_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='가맹점별 Keyin 설정 (기존 g5_member_keyin_config)';


-- ============================================================================
-- 도메인 D: 결제 (Payment)
-- ============================================================================

-- D1. payments - 통합 결제 테이블
-- 기존: g5_payment
-- 변경: 36개 계층 컬럼(mb_1~6, mb_1_name~6_name, mb_1_fee~6_fee, mb_1_pay~6_pay) 제거
CREATE TABLE payments (
    payment_id      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- 거래 식별
    trx_id          VARCHAR(100) NOT NULL COMMENT 'PG 거래 고유번호 (기존 trxid)',
    track_id        VARCHAR(100) DEFAULT NULL COMMENT '주문번호 (기존 trackId)',
    approval_no     VARCHAR(50) DEFAULT NULL COMMENT '승인번호 (기존 pay_num)',

    -- 거래 유형/상태
    pay_type        ENUM('approval','cancel','partial_cancel','net_cancel','manual_cancel') NOT NULL
                    COMMENT '기존 pay_type: Y→approval, N→cancel, B→partial_cancel, M→net_cancel, X→manual_cancel',
    payment_method  ENUM('card','keyin','url','other') NOT NULL DEFAULT 'card' COMMENT '결제수단',

    -- 금액
    amount          INT NOT NULL COMMENT '결제금액 (기존 pay, 취소시 음수)',

    -- 카드 정보
    card_issuer     VARCHAR(50) DEFAULT NULL COMMENT '발급사 (기존 pay_card_name)',
    card_acquirer   VARCHAR(50) DEFAULT NULL,
    card_no_masked  VARCHAR(30) DEFAULT NULL COMMENT '카드번호 마스킹 (기존 pay_card_num)',
    installment     TINYINT UNSIGNED DEFAULT 0 COMMENT '할부개월 (기존 pay_parti)',

    -- 일시
    approved_at     DATETIME NOT NULL COMMENT '승인 일시 (기존 pay_datetime)',
    cancelled_at    DATETIME DEFAULT NULL COMMENT '취소 일시 (기존 pay_cdatetime)',

    -- 디바이스/PG 연결
    device_id       INT UNSIGNED DEFAULT NULL,
    tid             VARCHAR(30) DEFAULT NULL COMMENT '단말기 ID (기존 dv_tid, 비정규화)',
    pg_provider_id  INT UNSIGNED DEFAULT NULL,
    pg_name         VARCHAR(20) DEFAULT NULL COMMENT 'PG사 코드 (기존 pg_name, 비정규화)',
    device_type     TINYINT UNSIGNED DEFAULT NULL COMMENT '디바이스 타입 (기존 dv_type, 비정규화)',
    cert_type       VARCHAR(20) DEFAULT NULL COMMENT '인증 타입 (기존 dv_certi, 비정규화)',

    -- 가맹점 (결제 시점 스냅샷)
    merchant_id     INT UNSIGNED NOT NULL COMMENT '가맹점 회원 ID (기존 mb_6)',
    merchant_name   VARCHAR(100) DEFAULT NULL COMMENT '가맹점명 스냅샷 (기존 mb_6_name)',

    -- 원거래 연결 (취소 시)
    original_payment_id BIGINT UNSIGNED DEFAULT NULL COMMENT '원거래 ID (기존 rootTrxId로 매핑)',

    -- 수기결제 연결
    keyin_payment_id INT UNSIGNED DEFAULT NULL,

    -- 정산 관련
    receipt_data    TEXT DEFAULT NULL COMMENT '영수증 데이터 (기존 pay_receipt)',
    sftp_mbrno      VARCHAR(20) DEFAULT NULL COMMENT 'SFTP 회원번호 (기존 sftp_mbrno)',
    deposit         INT DEFAULT NULL COMMENT '보증금 (기존 deposit)',

    -- 정산 PG별 플래그
    settle_secta    VARCHAR(1) DEFAULT NULL COMMENT '기존 pg_secta',
    settle_paysis   VARCHAR(1) DEFAULT NULL COMMENT '기존 pg_paysis',
    settle_winglo   VARCHAR(1) DEFAULT NULL COMMENT '기존 pg_winglo',
    settle_kwon     VARCHAR(1) DEFAULT NULL COMMENT '기존 pg_kwon',
    settle_welcome  VARCHAR(1) DEFAULT NULL COMMENT '기존 pg_welcome',
    settle_yn       VARCHAR(1) DEFAULT 'N' COMMENT '정산 완료 여부 (기존 settle_yn)',
    settle_ymd      VARCHAR(8) DEFAULT NULL COMMENT '정산 일자 (기존 settle_ymd)',

    -- 관리
    raw_noti_id     BIGINT UNSIGNED DEFAULT NULL COMMENT 'PG 원본 NOTI ID',
    memo_flag       INT DEFAULT NULL COMMENT '메모 여부 (기존 memo)',

    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 datetime',
    updated_at      DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '기존 updatetime',

    UNIQUE KEY uk_trx_id (trx_id, approved_at),
    KEY idx_approved_at (approved_at),
    KEY idx_merchant (merchant_id),
    KEY idx_tid (tid),
    KEY idx_approval_no (approval_no),
    KEY idx_pay_type (pay_type),
    KEY idx_pg (pg_provider_id),
    KEY idx_device_type (device_type),
    KEY idx_original (original_payment_id),
    KEY idx_card_no (card_no_masked),
    KEY idx_settle (settle_yn),
    -- 정산 집계용 복합 인덱스
    KEY idx_settle_query (merchant_id, approved_at, pay_type),
    KEY idx_tid_date (tid, approved_at)
    -- FK 제거됨 (파티셔닝 테이블 FK 불가, 앱 레벨 검증)
    -- 논리적 참조: merchant_id → members, device_id → devices, pg_provider_id → pg_providers, original_payment_id → payments
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='통합 결제 테이블 (기존 g5_payment)'
PARTITION BY RANGE (YEAR(approved_at) * 100 + MONTH(approved_at)) (
    PARTITION p_before_2025 VALUES LESS THAN (202501),
    PARTITION p_202501 VALUES LESS THAN (202502),
    PARTITION p_202502 VALUES LESS THAN (202503),
    PARTITION p_202503 VALUES LESS THAN (202504),
    PARTITION p_202504 VALUES LESS THAN (202505),
    PARTITION p_202505 VALUES LESS THAN (202506),
    PARTITION p_202506 VALUES LESS THAN (202507),
    PARTITION p_202507 VALUES LESS THAN (202508),
    PARTITION p_202508 VALUES LESS THAN (202509),
    PARTITION p_202509 VALUES LESS THAN (202510),
    PARTITION p_202510 VALUES LESS THAN (202511),
    PARTITION p_202511 VALUES LESS THAN (202512),
    PARTITION p_202512 VALUES LESS THAN (202601),
    PARTITION p_202601 VALUES LESS THAN (202602),
    PARTITION p_202602 VALUES LESS THAN (202603),
    PARTITION p_202603 VALUES LESS THAN (202604),
    PARTITION p_202604 VALUES LESS THAN (202605),
    PARTITION p_202605 VALUES LESS THAN (202606),
    PARTITION p_202606 VALUES LESS THAN (202607),
    PARTITION p_202607 VALUES LESS THAN (202608),
    PARTITION p_202608 VALUES LESS THAN (202609),
    PARTITION p_202609 VALUES LESS THAN (202610),
    PARTITION p_202610 VALUES LESS THAN (202611),
    PARTITION p_202611 VALUES LESS THAN (202612),
    PARTITION p_202612 VALUES LESS THAN (202701),
    PARTITION p_202701 VALUES LESS THAN (202702),
    PARTITION p_202702 VALUES LESS THAN (202703),
    PARTITION p_202703 VALUES LESS THAN (202704),
    PARTITION p_202704 VALUES LESS THAN (202705),
    PARTITION p_202705 VALUES LESS THAN (202706),
    PARTITION p_202706 VALUES LESS THAN (202707),
    PARTITION p_202707 VALUES LESS THAN (202708),
    PARTITION p_202708 VALUES LESS THAN (202709),
    PARTITION p_202709 VALUES LESS THAN (202710),
    PARTITION p_202710 VALUES LESS THAN (202711),
    PARTITION p_202711 VALUES LESS THAN (202712),
    PARTITION p_202712 VALUES LESS THAN (202801),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);


-- D2. payment_fee_distribution - 결제별 수수료 분배
-- 기존: g5_payment의 mb_1~6, mb_1_fee~6_fee, mb_1_pay~6_pay (24컬럼)
-- 변경: 행으로 정규화
CREATE TABLE payment_fee_distribution (
    dist_id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_id      BIGINT UNSIGNED NOT NULL,
    member_id       INT UNSIGNED NOT NULL COMMENT '수수료 수취 회원',
    hierarchy_level TINYINT UNSIGNED NOT NULL COMMENT '회원의 계층 레벨',
    fee_rate        DECIMAL(5,3) NOT NULL COMMENT '적용 수수료율 (%)',
    fee_amount      INT NOT NULL DEFAULT 0 COMMENT '수수료 금액 (기존 mb_N_pay)',
    settlement_amount INT DEFAULT NULL COMMENT '정산액 (가맹점만 해당)',
    fee_config_id   INT UNSIGNED DEFAULT NULL COMMENT '적용된 device_fee_structure.fee_id (수수료 버전 추적)',

    KEY idx_payment (payment_id),
    KEY idx_member (member_id),
    KEY idx_member_payment (member_id, payment_id),
    KEY idx_settle_cover (member_id, hierarchy_level, fee_rate, fee_amount, settlement_amount),
    KEY idx_fee_config (fee_config_id)
    -- FK 제거됨 (payments 파티셔닝으로 FK 불가, 앱 레벨 검증)
    -- 논리적 참조: payment_id → payments, member_id → members
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='결제별 계층 수수료 분배 (기존 mb_1~6_fee/pay 24컬럼 정규화)';


-- D3. pg_raw_notifications - PG NOTI 원본 통합
-- 기존: g5_payment_k1, g5_payment_danal, g5_payment_korpay, g5_payment_paysis,
--       g5_payment_welcom, g5_payment_stn, g5_payment_daou, g5_payment_routeup (8개)
-- 변경: 1개 테이블 + raw_data JSON
CREATE TABLE pg_raw_notifications (
    noti_id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pg_provider_id  INT UNSIGNED NOT NULL,
    raw_data        JSON NOT NULL COMMENT 'PG에서 수신한 원본 데이터 전체',

    -- 주요 필드 추출 (조회 성능용)
    pg_trx_id       VARCHAR(100) DEFAULT NULL COMMENT 'PG 거래번호',
    pg_approval_no  VARCHAR(50) DEFAULT NULL COMMENT 'PG 승인번호',
    pg_amount       INT DEFAULT NULL,
    pg_cancel_yn    CHAR(1) DEFAULT 'N',
    pg_tid          VARCHAR(50) DEFAULT NULL COMMENT 'PG가 전달한 단말기/CAT ID',

    -- 동기화 상태
    sync_status     ENUM('pending','success','failed','skipped') DEFAULT 'pending',
    sync_message    VARCHAR(255) DEFAULT NULL,
    payment_id      BIGINT UNSIGNED DEFAULT NULL COMMENT '연결된 payments.payment_id',

    received_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    processed_at    DATETIME DEFAULT NULL,

    KEY idx_pg (pg_provider_id),
    KEY idx_sync (sync_status),
    KEY idx_pg_trx (pg_trx_id),
    KEY idx_received (received_at),
    KEY idx_payment (payment_id)
    -- FK 제거됨 (파티셔닝 테이블 FK 불가, 앱 레벨 검증)
    -- 논리적 참조: pg_provider_id → pg_providers, payment_id → payments
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='PG NOTI 원본 통합 (기존 8개 PG별 테이블 통합)'
PARTITION BY RANGE (YEAR(received_at) * 100 + MONTH(received_at)) (
    PARTITION p_before_2025 VALUES LESS THAN (202501),
    PARTITION p_202501 VALUES LESS THAN (202502),
    PARTITION p_202502 VALUES LESS THAN (202503),
    PARTITION p_202503 VALUES LESS THAN (202504),
    PARTITION p_202504 VALUES LESS THAN (202505),
    PARTITION p_202505 VALUES LESS THAN (202506),
    PARTITION p_202506 VALUES LESS THAN (202507),
    PARTITION p_202507 VALUES LESS THAN (202508),
    PARTITION p_202508 VALUES LESS THAN (202509),
    PARTITION p_202509 VALUES LESS THAN (202510),
    PARTITION p_202510 VALUES LESS THAN (202511),
    PARTITION p_202511 VALUES LESS THAN (202512),
    PARTITION p_202512 VALUES LESS THAN (202601),
    PARTITION p_202601 VALUES LESS THAN (202602),
    PARTITION p_202602 VALUES LESS THAN (202603),
    PARTITION p_202603 VALUES LESS THAN (202604),
    PARTITION p_202604 VALUES LESS THAN (202605),
    PARTITION p_202605 VALUES LESS THAN (202606),
    PARTITION p_202606 VALUES LESS THAN (202607),
    PARTITION p_202607 VALUES LESS THAN (202608),
    PARTITION p_202608 VALUES LESS THAN (202609),
    PARTITION p_202609 VALUES LESS THAN (202610),
    PARTITION p_202610 VALUES LESS THAN (202611),
    PARTITION p_202611 VALUES LESS THAN (202612),
    PARTITION p_202612 VALUES LESS THAN (202701),
    PARTITION p_202701 VALUES LESS THAN (202702),
    PARTITION p_202702 VALUES LESS THAN (202703),
    PARTITION p_202703 VALUES LESS THAN (202704),
    PARTITION p_202704 VALUES LESS THAN (202705),
    PARTITION p_202705 VALUES LESS THAN (202706),
    PARTITION p_202706 VALUES LESS THAN (202707),
    PARTITION p_202707 VALUES LESS THAN (202708),
    PARTITION p_202708 VALUES LESS THAN (202709),
    PARTITION p_202709 VALUES LESS THAN (202710),
    PARTITION p_202710 VALUES LESS THAN (202711),
    PARTITION p_202711 VALUES LESS THAN (202712),
    PARTITION p_202712 VALUES LESS THAN (202801),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);


-- D4. keyin_payments - 수기결제 내역
-- 기존: g5_payment_keyin
-- 변경: pk_mb_1~6 제거, member_id FK로 대체
CREATE TABLE keyin_payments (
    keyin_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_no        VARCHAR(50) NOT NULL COMMENT '주문번호 (기존 pk_order_no)',
    merchant_oid    CHAR(4) DEFAULT NULL COMMENT '가맹점 OID (기존 pk_merchant_oid)',
    member_id       INT UNSIGNED NOT NULL COMMENT '가맹점 회원 ID (기존 mb_id)',
    mkc_id          INT UNSIGNED DEFAULT NULL COMMENT 'Keyin 설정 ID',

    -- PG 정보
    pg_provider_id  INT UNSIGNED DEFAULT NULL COMMENT '기존 pk_pg_code → FK',
    pg_mid          VARCHAR(50) DEFAULT NULL COMMENT '기존 pk_mid',
    auth_type       VARCHAR(20) DEFAULT NULL COMMENT '기존 pk_auth_type',

    -- 결제 정보
    amount          INT NOT NULL COMMENT '기존 pk_amount',
    installment     VARCHAR(2) DEFAULT '00' COMMENT '기존 pk_installment',
    goods_name      VARCHAR(100) DEFAULT NULL COMMENT '기존 pk_goods_name',
    buyer_name      VARCHAR(50) DEFAULT NULL COMMENT '기존 pk_buyer_name',
    buyer_phone     VARCHAR(20) DEFAULT NULL COMMENT '기존 pk_buyer_phone',
    buyer_email     VARCHAR(100) DEFAULT NULL COMMENT '기존 pk_buyer_email',

    -- 카드 정보
    card_issuer     VARCHAR(50) DEFAULT NULL COMMENT '기존 pk_card_issuer',
    card_acquirer   VARCHAR(50) DEFAULT NULL COMMENT '기존 pk_card_acquirer',
    card_no_masked  VARCHAR(20) DEFAULT NULL COMMENT '기존 pk_card_no_masked',

    -- 상태
    status          ENUM('pending','approved','failed','cancelled','partial_cancelled') DEFAULT 'pending' COMMENT '기존 pk_status',
    res_code        VARCHAR(10) DEFAULT NULL COMMENT '기존 pk_res_code',
    res_msg         VARCHAR(200) DEFAULT NULL COMMENT '기존 pk_res_msg',
    approval_no     VARCHAR(50) DEFAULT NULL COMMENT '기존 pk_app_no',
    approval_date   VARCHAR(20) DEFAULT NULL COMMENT '기존 pk_app_date',
    pg_tid          VARCHAR(100) DEFAULT NULL COMMENT '기존 pk_tid',

    -- 취소
    cancel_amount   INT DEFAULT 0 COMMENT '기존 pk_cancel_amount',
    cancel_name     VARCHAR(50) DEFAULT NULL COMMENT '기존 pk_cancel_name',
    cancel_reason   VARCHAR(200) DEFAULT NULL COMMENT '기존 pk_cancel_reason',
    cancel_date     VARCHAR(20) DEFAULT NULL COMMENT '기존 pk_cancel_date',

    -- 원본 데이터
    request_data    JSON DEFAULT NULL COMMENT '기존 pk_request_data',
    response_data   JSON DEFAULT NULL COMMENT '기존 pk_response_data',

    -- 관리
    operator_id     INT UNSIGNED DEFAULT NULL COMMENT '결제 진행 관리자 ID (기존 pk_operator_id)',
    memo            TEXT DEFAULT NULL COMMENT '기존 pk_memo',

    -- 연결
    payment_id      BIGINT UNSIGNED DEFAULT NULL COMMENT '연결된 payments 레코드',

    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 pk_created_at',
    updated_at      DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '기존 pk_updated_at',

    KEY idx_order_no (order_no),
    KEY idx_member (member_id),
    KEY idx_status (status),
    KEY idx_created (created_at),
    KEY idx_approval (approval_no),
    KEY idx_pg (pg_provider_id),
    CONSTRAINT fk_keyin_member FOREIGN KEY (member_id) REFERENCES members(member_id),
    CONSTRAINT fk_keyin_mkc FOREIGN KEY (mkc_id) REFERENCES merchant_keyin_config(mkc_id),
    CONSTRAINT fk_keyin_pg FOREIGN KEY (pg_provider_id) REFERENCES pg_providers(pg_id)
    -- FK 제거됨: payment_id → payments (파티셔닝 테이블 FK 불가, 앱 레벨 검증)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='수기결제 내역 (기존 g5_payment_keyin)';


-- D5. url_payments - URL 결제
-- 기존: g5_url_payment
-- 변경: up_mb_1~6 제거, member_id FK로 대체
CREATE TABLE url_payments (
    url_payment_id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    short_code      VARCHAR(9) NOT NULL COMMENT 'URL 코드 (기존 up_code)',
    member_id       INT UNSIGNED NOT NULL COMMENT '가맹점 회원 ID (기존 mb_id)',
    mkc_id          INT UNSIGNED DEFAULT NULL COMMENT 'Keyin 설정 ID',

    amount          INT NOT NULL COMMENT '기존 up_amount',
    goods_name      VARCHAR(100) DEFAULT NULL COMMENT '기존 up_goods_name',
    goods_desc      TEXT DEFAULT NULL COMMENT '기존 up_goods_desc',
    buyer_name      VARCHAR(50) DEFAULT NULL COMMENT '기존 up_buyer_name',
    buyer_phone     VARCHAR(20) DEFAULT NULL COMMENT '기존 up_buyer_phone',
    seller_name     VARCHAR(50) DEFAULT NULL COMMENT '기존 up_seller_name',
    seller_phone    VARCHAR(20) DEFAULT NULL COMMENT '기존 up_seller_phone',

    expire_at       DATETIME NOT NULL COMMENT '기존 up_expire_datetime',
    max_uses        INT DEFAULT 1 COMMENT '기존 up_max_uses',
    use_count       INT DEFAULT 0 COMMENT '기존 up_use_count',
    status          ENUM('active','used','expired','cancelled') DEFAULT 'active' COMMENT '기존 up_status',
    memo            TEXT DEFAULT NULL COMMENT '기존 up_memo',

    -- SMS
    sms_sent        TINYINT(1) DEFAULT 0 COMMENT '기존 up_sms_sent',
    sms_sent_at     DATETIME DEFAULT NULL COMMENT '기존 up_sms_sent_datetime',
    sms_count       INT DEFAULT 0 COMMENT '기존 up_sms_count',

    -- 결제 완료
    keyin_payment_id INT UNSIGNED DEFAULT NULL COMMENT '기존 pk_id',
    paid_at         DATETIME DEFAULT NULL COMMENT '기존 up_paid_datetime',

    -- 관리
    operator_id     INT UNSIGNED DEFAULT NULL COMMENT '기존 up_operator_id',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 up_created_at',
    updated_at      DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '기존 up_updated_at',

    UNIQUE KEY uk_code (short_code),
    KEY idx_member (member_id),
    KEY idx_status (status),
    KEY idx_expire (expire_at),
    KEY idx_created (created_at),
    CONSTRAINT fk_urlpay_member FOREIGN KEY (member_id) REFERENCES members(member_id),
    CONSTRAINT fk_urlpay_mkc FOREIGN KEY (mkc_id) REFERENCES merchant_keyin_config(mkc_id),
    CONSTRAINT fk_urlpay_keyin FOREIGN KEY (keyin_payment_id) REFERENCES keyin_payments(keyin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='URL 결제 링크 (기존 g5_url_payment)';


-- D6. payment_memos - 결제 메모
-- 기존: g5_payment_memo
CREATE TABLE payment_memos (
    memo_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_id      BIGINT UNSIGNED NOT NULL COMMENT '기존 pay_id',
    author_id       INT UNSIGNED NOT NULL COMMENT '작성자 회원 ID (기존 mb_id)',
    author_name     VARCHAR(50) DEFAULT NULL COMMENT '작성자명 (기존 mb_name)',
    content         TEXT NOT NULL COMMENT '기존 me_memo',
    ip_address      VARCHAR(45) DEFAULT NULL COMMENT '기존 ip',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 datetime',

    KEY idx_payment (payment_id),
    CONSTRAINT fk_memo_author FOREIGN KEY (author_id) REFERENCES members(member_id)
    -- FK 제거됨: payment_id → payments (파티셔닝 테이블 FK 불가, 앱 레벨 검증)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='결제 메모 (기존 g5_payment_memo)';


-- ============================================================================
-- 도메인 E: Webhook
-- ============================================================================

-- E1. webhook_configs - 웹훅 설정
-- 기존: g5_member_webhook
CREATE TABLE webhook_configs (
    webhook_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id       INT UNSIGNED NOT NULL COMMENT '가맹점 회원 ID (기존 mb_id)',
    url             VARCHAR(500) NOT NULL COMMENT '기존 wh_url',
    events          VARCHAR(100) DEFAULT 'approval' COMMENT '기존 wh_events',
    retry_count     TINYINT UNSIGNED DEFAULT 3 COMMENT '기존 wh_retry_count',
    retry_delay     INT UNSIGNED DEFAULT 60 COMMENT '재시도 간격 초 (기존 wh_retry_delay)',
    timeout         INT UNSIGNED DEFAULT 5 COMMENT 'HTTP 타임아웃 초 (기존 wh_timeout)',
    status          ENUM('active','inactive') DEFAULT 'active' COMMENT '기존 wh_status',
    memo            TEXT DEFAULT NULL COMMENT '기존 wh_memo',

    -- 통계
    total_sent      INT UNSIGNED DEFAULT 0,
    total_success   INT UNSIGNED DEFAULT 0 COMMENT '기존 wh_success_count',
    total_failed    INT UNSIGNED DEFAULT 0 COMMENT '기존 wh_fail_count',
    last_sent_at    DATETIME DEFAULT NULL,
    last_success_at DATETIME DEFAULT NULL COMMENT '기존 wh_last_success',

    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 wh_reg_datetime',
    updated_at      DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '기존 wh_update_datetime',

    UNIQUE KEY uk_member (member_id),
    KEY idx_status (status),
    CONSTRAINT fk_webhook_member FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='웹훅 설정 (기존 g5_member_webhook)';


-- E2. webhook_history - 웹훅 이력
-- 기존: g5_webhook_history
CREATE TABLE webhook_history (
    history_id      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    webhook_id      INT UNSIGNED NOT NULL COMMENT '기존 wh_id',
    member_id       INT UNSIGNED NOT NULL COMMENT '기존 mb_id',
    payment_id      BIGINT UNSIGNED DEFAULT NULL COMMENT '기존 pay_id',

    event_type      VARCHAR(30) NOT NULL COMMENT '기존 whh_event_type',
    event_id        VARCHAR(100) NOT NULL COMMENT '기존 whh_event_id',
    url             VARCHAR(500) NOT NULL COMMENT '기존 whh_url',
    payload         JSON NOT NULL COMMENT '기존 whh_payload (TEXT→JSON)',

    http_status     SMALLINT UNSIGNED DEFAULT NULL COMMENT '기존 whh_http_status',
    response_body   TEXT DEFAULT NULL COMMENT '기존 whh_response_body',
    response_time   INT UNSIGNED DEFAULT NULL COMMENT '응답시간 ms (기존 whh_response_time)',

    retry_count     TINYINT UNSIGNED DEFAULT 0 COMMENT '기존 whh_retry_count',
    max_retry_count TINYINT UNSIGNED DEFAULT 3 COMMENT '기존 whh_max_retry_count',
    status          ENUM('success','pending','failed','abandoned') DEFAULT 'pending' COMMENT '기존 whh_status (timeout→abandoned)',
    error_message   VARCHAR(500) DEFAULT NULL COMMENT '기존 whh_error_message',
    next_retry_at   DATETIME DEFAULT NULL,

    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 whh_sent_datetime',
    updated_at      DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '기존 whh_completed_datetime',

    KEY idx_webhook (webhook_id),
    KEY idx_member (member_id),
    KEY idx_payment (payment_id),
    KEY idx_status (status),
    KEY idx_next_retry (status, next_retry_at),
    KEY idx_created (created_at),
    CONSTRAINT fk_whh_webhook FOREIGN KEY (webhook_id) REFERENCES webhook_configs(webhook_id),
    CONSTRAINT fk_whh_member FOREIGN KEY (member_id) REFERENCES members(member_id)
    -- FK 제거됨: payment_id → payments (파티셔닝 테이블 FK 불가, 앱 레벨 검증)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='웹훅 전송 이력 (기존 g5_webhook_history)';


-- ============================================================================
-- 도메인 F: 정산 (Settlement)
-- ============================================================================

-- F1. settlement_batches - 정산 배치
-- 기존: 없음 (신규)
CREATE TABLE settlement_batches (
    batch_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    settle_date     DATE NOT NULL COMMENT '정산 대상 일자',
    batch_type      ENUM('daily','manual','re_settle') NOT NULL DEFAULT 'daily',
    status          ENUM('pending','processing','completed','failed') DEFAULT 'pending',
    total_amount    DECIMAL(15,2) DEFAULT 0,
    total_fee       DECIMAL(15,2) DEFAULT 0,
    total_settlement DECIMAL(15,2) DEFAULT 0,
    record_count    INT UNSIGNED DEFAULT 0,

    processed_at    DATETIME DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    KEY idx_date (settle_date),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='정산 배치';


-- F2. settlement_details - 정산 내역
-- 기존: settle_payment_trans
-- 변경: 계층 36컬럼 제거, FK 연결
CREATE TABLE settlement_details (
    detail_id       BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    batch_id        INT UNSIGNED NOT NULL,
    payment_id      BIGINT UNSIGNED NOT NULL,
    member_id       INT UNSIGNED NOT NULL COMMENT '정산 대상 회원',

    settle_amount   DECIMAL(12,2) NOT NULL COMMENT '정산 금액',
    fee_amount      DECIMAL(12,2) NOT NULL COMMENT '수수료 금액',
    net_amount      DECIMAL(12,2) NOT NULL COMMENT '순 정산액',

    settle_status   ENUM('pending','settled','excluded','cancelled') DEFAULT 'pending',
    settle_date     DATE NOT NULL,

    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    KEY idx_batch (batch_id),
    KEY idx_payment (payment_id),
    KEY idx_member (member_id),
    KEY idx_date (settle_date),
    KEY idx_member_date (member_id, settle_date),
    CONSTRAINT fk_sd_batch FOREIGN KEY (batch_id) REFERENCES settlement_batches(batch_id),
    CONSTRAINT fk_sd_member FOREIGN KEY (member_id) REFERENCES members(member_id)
    -- FK 제거됨: payment_id → payments (파티셔닝 테이블 FK 불가, 앱 레벨 검증)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='정산 상세 (기존 settle_payment_trans 정규화)';


-- F3. settlement_files - 정산 파일
-- 기존: settle_settlement_files
CREATE TABLE settlement_files (
    file_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    batch_id        INT UNSIGNED DEFAULT NULL,
    file_type       VARCHAR(30) NOT NULL COMMENT '기존 file_type enum 유지',
    file_name       VARCHAR(255) NOT NULL COMMENT '기존 original_filename',
    stored_name     VARCHAR(255) DEFAULT NULL COMMENT '기존 stored_filename',
    file_path       VARCHAR(500) NOT NULL COMMENT '기존 file_path',
    file_size       INT UNSIGNED DEFAULT 0,
    record_count    INT UNSIGNED DEFAULT 0,
    settle_date     DATE NOT NULL COMMENT '기존 settlement_date',
    upload_user     VARCHAR(50) DEFAULT NULL COMMENT '기존 upload_user',

    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 upload_date',

    KEY idx_batch (batch_id),
    KEY idx_date (settle_date),
    KEY idx_type (file_type),
    CONSTRAINT fk_sf_batch FOREIGN KEY (batch_id) REFERENCES settlement_batches(batch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='정산 파일 (기존 settle_settlement_files)';


-- F4. settlement_exclusions - 정산 제외 목록
-- 기존: settle_exclude_mb3_list
CREATE TABLE settlement_exclusions (
    excl_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id       INT UNSIGNED NOT NULL COMMENT '기존 mb_3 → member_id',
    reason          VARCHAR(200) DEFAULT NULL COMMENT '기존 reason',
    is_active       TINYINT(1) DEFAULT 1,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    KEY idx_member (member_id),
    CONSTRAINT fk_excl_member FOREIGN KEY (member_id) REFERENCES members(member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='정산 제외 가맹점 (기존 settle_exclude_mb3_list)';


-- F5. holidays - 공휴일
-- 기존: settle_holidays
CREATE TABLE holidays (
    holiday_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    holiday_date    DATE NOT NULL COMMENT '기존 holiday_date',
    holiday_name    VARCHAR(50) DEFAULT NULL COMMENT '기존 holiday_name',
    holiday_type    ENUM('legal','custom') NOT NULL DEFAULT 'legal' COMMENT '기존 holiday_type',

    UNIQUE KEY uk_date (holiday_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='공휴일 (기존 settle_holidays)';


-- ============================================================================
-- 도메인 G: SFTP 차액 정산
-- ============================================================================

-- G1. sftp_members - SFTP 차액정산 회원
-- 기존: g5_sftp_member
CREATE TABLE sftp_members (
    sftp_member_id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id       INT UNSIGNED DEFAULT NULL COMMENT '연결된 회원',

    sftp_type       TINYINT UNSIGNED DEFAULT 0 COMMENT '기존 sm_type (0=신규, 1=해지, 2=변경)',
    openmarket      VARCHAR(50) DEFAULT NULL COMMENT '기존 sm_openmarket',
    biz_number      VARCHAR(20) DEFAULT NULL COMMENT '기존 sm_bnumber',
    biz_type        VARCHAR(50) DEFAULT NULL COMMENT '기존 sm_btype',
    biz_name        VARCHAR(100) DEFAULT NULL COMMENT '기존 sm_bname',
    address         VARCHAR(255) DEFAULT NULL COMMENT '기존 sm_addr',
    ceo_name        VARCHAR(50) DEFAULT NULL COMMENT '기존 sm_ceo',
    tel             VARCHAR(20) DEFAULT NULL COMMENT '기존 sm_tel',
    email           VARCHAR(100) DEFAULT NULL COMMENT '기존 sm_email',
    website         VARCHAR(255) DEFAULT NULL COMMENT '기존 sm_website',
    mbr_no          VARCHAR(50) DEFAULT NULL COMMENT '기존 sm_mbrno',
    error_code      VARCHAR(50) DEFAULT NULL COMMENT '기존 sm_error',
    memo            TEXT DEFAULT NULL COMMENT '기존 sm_memo',

    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 datetime',
    updated_at      DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '기존 updatetime',

    KEY idx_member (member_id),
    CONSTRAINT fk_sftp_member FOREIGN KEY (member_id) REFERENCES members(member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='SFTP 차액정산 회원 (기존 g5_sftp_member)';


-- ============================================================================
-- 도메인 H: 가상계좌/출금 시스템
-- ============================================================================

-- H1. va_agents - 에이전트
-- 기존: agent + agent_detail (통합)
CREATE TABLE va_agents (
    agent_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id       INT UNSIGNED DEFAULT NULL COMMENT '연결된 판매자센터 회원',
    agent_name      VARCHAR(100) NOT NULL COMMENT '기존 agent.agent_name',
    depth           TINYINT UNSIGNED DEFAULT 0 COMMENT '기존 agent.depth',
    parent_agent_id INT UNSIGNED DEFAULT NULL COMMENT '기존 agent.parent_agent_idx',
    balance         DECIMAL(15,2) DEFAULT 0 COMMENT '기존 agent.agent_balance',
    withdraw_fee    DECIMAL(15,2) DEFAULT 0 COMMENT '기존 agent.agent_withdraw_fee',
    status          ENUM('NORMAL','READY','SUSPEND','CLOSE') DEFAULT 'READY' COMMENT '기존 agent.status',

    -- 사업자 정보 (기존 agent_detail)
    biz_reg_no      VARCHAR(50) DEFAULT NULL COMMENT '기존 agent_detail.agent_biz_reg',
    ceo_name        VARCHAR(50) DEFAULT NULL COMMENT '기존 agent_detail.agent_ceo',
    address         VARCHAR(255) DEFAULT NULL COMMENT '기존 agent_detail.agent_address',
    address_detail  VARCHAR(255) DEFAULT NULL COMMENT '기존 agent_detail.agent_address_dtl',
    phone           VARCHAR(50) DEFAULT NULL COMMENT '기존 agent_detail.agent_phone',
    email           VARCHAR(255) DEFAULT NULL COMMENT '기존 agent_detail.agent_email',
    identity_no     VARCHAR(255) DEFAULT NULL COMMENT '기존 agent_detail.agent_identity',
    biz_type        VARCHAR(255) DEFAULT NULL COMMENT '기존 agent_detail.agent_type',
    biz_items       VARCHAR(255) DEFAULT NULL COMMENT '기존 agent_detail.agent_items',

    -- 계좌 정보 (기존 agent_detail)
    bank_code       VARCHAR(10) DEFAULT NULL COMMENT '기존 agent_detail.agent_account_bank',
    account_number  VARCHAR(50) DEFAULT NULL COMMENT '기존 agent_detail.agent_account',
    account_holder  VARCHAR(50) DEFAULT NULL COMMENT '기존 agent_detail.agent_account_holder',
    account_status  ENUM('NORMAL','READY','SUSPEND','CLOSE','REJECT') DEFAULT NULL COMMENT '기존 agent_detail.agent_account_status',
    target_bank     VARCHAR(255) DEFAULT NULL COMMENT '기존 agent_detail.agent_target_bank',

    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 agent_detail.regdate',
    updated_at      DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '기존 agent_detail.uptdate',

    KEY idx_parent (parent_agent_id),
    KEY idx_member (member_id),
    KEY idx_status (status),
    CONSTRAINT fk_va_agent_parent FOREIGN KEY (parent_agent_id) REFERENCES va_agents(agent_id),
    CONSTRAINT fk_va_agent_member FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='가상계좌 에이전트 (기존 agent + agent_detail 통합)';


-- H2. va_merchants - 가상계좌 가맹점
-- 기존: mcht + mcht_detail + mcht_info (3개 → 1개 + JSON)
CREATE TABLE va_merchants (
    va_merchant_id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id       INT UNSIGNED DEFAULT NULL COMMENT '연결된 판매자센터 회원',
    agent_id        INT UNSIGNED NOT NULL COMMENT '기존 mcht.agent_idx',
    merchant_name   VARCHAR(100) NOT NULL COMMENT '기존 mcht.mcht_name',
    status          ENUM('NORMAL','READY','SUSPEND','CLOSE') DEFAULT 'READY' COMMENT '기존 mcht.status',
    balance         DECIMAL(15,2) DEFAULT 0 COMMENT '기존 mcht.mcht_balance',
    retention       DECIMAL(15,2) DEFAULT 0 COMMENT '기존 mcht.mcht_retention',
    collateral_amount DECIMAL(15,2) DEFAULT 0 COMMENT '기존 mcht.mcht_collateral_amount',
    collateral_rate DECIMAL(10,5) DEFAULT 0 COMMENT '기존 mcht.mcht_collateral_rate',
    hold_amount     DECIMAL(15,2) DEFAULT 0 COMMENT '기존 mcht.mcht_hold_amount',
    withdraw_fee    DECIMAL(15,2) DEFAULT 0 COMMENT '기존 mcht.mcht_withdraw_fee',
    limit_once      DECIMAL(15,2) DEFAULT 0 COMMENT '기존 mcht.mcht_limit_once_amount',
    fds_status      TINYINT(1) DEFAULT 0 COMMENT '기존 mcht.mcht_fds_status (bit→tinyint)',
    access_ip       TEXT DEFAULT NULL COMMENT '기존 mcht.access_ip_1 + access_ip_2 합침',

    -- 사업자/계좌 정보 (기존 mcht_detail)
    biz_reg_no      VARCHAR(255) DEFAULT NULL COMMENT '기존 mcht_detail.mcht_biz_reg',
    ceo_name        VARCHAR(255) DEFAULT NULL COMMENT '기존 mcht_detail.mcht_ceo',
    address         VARCHAR(255) DEFAULT NULL,
    bank_code       VARCHAR(10) DEFAULT NULL COMMENT '기존 mcht_detail.mcht_account_bank',
    account_number  VARCHAR(50) DEFAULT NULL COMMENT '기존 mcht_detail.mcht_account',
    account_holder  VARCHAR(50) DEFAULT NULL COMMENT '기존 mcht_detail.mcht_account_holder',
    account_status  ENUM('NORMAL','READY','SUSPEND','CLOSE','REJECT') DEFAULT NULL,
    target_bank     VARCHAR(255) DEFAULT NULL,

    -- 텔레그램/한도 (기존 mcht_info → JSON)
    telegram_config JSON DEFAULT NULL COMMENT '기존 mcht_info.mcht_telegram_* → JSON',
    va_config       JSON DEFAULT NULL COMMENT '기존 mcht_info.mcht_vcnt_* + vcnt_* → JSON',

    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 mcht_detail.regdate',
    updated_at      DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '기존 mcht_detail.uptdate',

    KEY idx_agent (agent_id),
    KEY idx_member (member_id),
    KEY idx_status (status),
    CONSTRAINT fk_vam_agent FOREIGN KEY (agent_id) REFERENCES va_agents(agent_id),
    CONSTRAINT fk_vam_member FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='가상계좌 가맹점 (기존 mcht + mcht_detail + mcht_info 통합)';


-- H3. va_accounts - 가상계좌
-- 기존: vaccount
CREATE TABLE va_accounts (
    account_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    va_merchant_id  INT UNSIGNED NOT NULL COMMENT '기존 mcht_idx',
    status          ENUM('NORMAL','READY','SUSPEND','CLOSE','FAIL','W_SUSPEND') DEFAULT 'READY' COMMENT '기존 status',
    real_bank       VARCHAR(255) DEFAULT NULL COMMENT '기존 real_account_bank',
    real_account    VARCHAR(255) DEFAULT NULL COMMENT '기존 real_account',
    real_holder     VARCHAR(255) DEFAULT NULL COMMENT '기존 real_account_holder',
    virtual_bank    VARCHAR(255) DEFAULT NULL COMMENT '기존 vcnt_bank_name',
    virtual_account VARCHAR(255) DEFAULT NULL COMMENT '기존 vcnt_account',
    virtual_holder  VARCHAR(255) DEFAULT NULL COMMENT '기존 vcnt_account_name',
    phone           VARCHAR(255) DEFAULT NULL COMMENT '기존 vcnt_phone',
    username        VARCHAR(255) DEFAULT NULL COMMENT '기존 vcnt_username',
    total_deposit   DECIMAL(15,2) DEFAULT 0 COMMENT '기존 vcnt_total_deposit_amount',
    total_withdrawal DECIMAL(15,2) DEFAULT 0 COMMENT '기존 vcnt_total_withdrawal_amount',
    provider        VARCHAR(255) DEFAULT NULL COMMENT '기존 provider',

    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 regdate',
    updated_at      DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    KEY idx_merchant (va_merchant_id),
    KEY idx_status (status),
    CONSTRAINT fk_va_account_merchant FOREIGN KEY (va_merchant_id) REFERENCES va_merchants(va_merchant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='가상계좌 (기존 vaccount)';


-- H4. va_transactions - 가상계좌 거래
-- 기존: trx
CREATE TABLE va_transactions (
    trx_id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    trx_type        VARCHAR(255) NOT NULL COMMENT '기존 trx_type',
    agent_id        INT UNSIGNED DEFAULT NULL COMMENT '기존 agent_idx',
    va_merchant_id  INT UNSIGNED DEFAULT NULL COMMENT '기존 mcht_idx',
    account_id      INT UNSIGNED DEFAULT NULL COMMENT '기존 vcnt_idx',
    amount          DECIMAL(15,2) NOT NULL COMMENT '기존 amount',
    fixed_fee       DECIMAL(10,2) DEFAULT 0 COMMENT '기존 fixed_fee',
    deposit_balance DECIMAL(15,2) DEFAULT 0 COMMENT '기존 mcht_deposit_balance',
    ht_account      VARCHAR(255) DEFAULT NULL COMMENT '기존 ht_account',
    ht_account_bank VARCHAR(255) DEFAULT NULL COMMENT '기존 ht_account_bank',
    ht_account_holder VARCHAR(255) DEFAULT NULL COMMENT '기존 ht_account_holder',
    settle_type     ENUM('AGENT_WITHDRAW','MCHT_WITHDRAW','VCNT_DEPOSIT','VCNT_WITHDRAW') NOT NULL COMMENT '기존 settle_type',
    trx_status      ENUM('READY','WAITING','DONE','FAIL') DEFAULT 'READY' COMMENT '기존 trx_status',
    raw_data        VARCHAR(255) DEFAULT NULL COMMENT '기존 raw_data',
    collateral_amount DECIMAL(15,2) DEFAULT NULL COMMENT '기존 ht_collateral_amount',
    retention       DECIMAL(15,2) DEFAULT NULL COMMENT '기존 ht_retention',
    ip_address      VARCHAR(255) DEFAULT NULL COMMENT '기존 ip_address',

    trx_date        DATETIME DEFAULT NULL COMMENT '기존 trxdate',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 regdate',
    updated_at      DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '기존 uptdate',

    KEY idx_agent (agent_id),
    KEY idx_merchant (va_merchant_id),
    KEY idx_account (account_id),
    KEY idx_status (trx_status),
    KEY idx_type (settle_type),
    KEY idx_created (created_at),
    CONSTRAINT fk_vatrx_agent FOREIGN KEY (agent_id) REFERENCES va_agents(agent_id),
    CONSTRAINT fk_vatrx_merchant FOREIGN KEY (va_merchant_id) REFERENCES va_merchants(va_merchant_id),
    CONSTRAINT fk_vatrx_account FOREIGN KEY (account_id) REFERENCES va_accounts(account_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='가상계좌 거래 (기존 trx)';


-- H5. va_deposit_notifications - 입금 통보
-- 기존: deposit_noti
CREATE TABLE va_deposit_notifications (
    noti_id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    track_id        VARCHAR(100) DEFAULT NULL COMMENT '기존 track_id',
    trx_id          BIGINT UNSIGNED DEFAULT NULL COMMENT '기존 trx_idx',
    status          TINYINT(1) DEFAULT NULL COMMENT '기존 status (bit→tinyint)',
    raw_data        JSON DEFAULT NULL,

    trx_date        DATETIME DEFAULT NULL COMMENT '기존 trxdate',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 regdate',

    KEY idx_trx (trx_id),
    KEY idx_track (track_id),
    CONSTRAINT fk_depnoti_trx FOREIGN KEY (trx_id) REFERENCES va_transactions(trx_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='입금 통보 (기존 deposit_noti)';


-- H6. va_fds - FDS (가상계좌)
-- 기존: fds
CREATE TABLE va_fds (
    fds_id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    va_merchant_id  INT UNSIGNED NOT NULL COMMENT '기존 mcht_idx',
    status          ENUM('NORMAL','READY','CLOSE') DEFAULT 'READY' COMMENT '기존 status',
    fds_amount      DECIMAL(15,2) DEFAULT 0 COMMENT '기존 fds_amount',
    hold_amount     DECIMAL(15,2) DEFAULT 0 COMMENT '기존 hold_amount',
    additional_hold DECIMAL(15,2) DEFAULT NULL COMMENT '기존 additional_hold_amount',
    fds_type        VARCHAR(30) NOT NULL COMMENT '기존 fds_type',
    description     VARCHAR(255) DEFAULT NULL COMMENT '기존 desc_cont',
    result          VARCHAR(255) DEFAULT NULL COMMENT '기존 result_cont',
    manager         VARCHAR(255) DEFAULT NULL COMMENT '기존 fds_manager',

    start_date      DATETIME DEFAULT NULL COMMENT '기존 startdate',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 regdate',
    updated_at      DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    KEY idx_merchant (va_merchant_id),
    KEY idx_status (status),
    CONSTRAINT fk_vafds_merchant FOREIGN KEY (va_merchant_id) REFERENCES va_merchants(va_merchant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='가상계좌 FDS (기존 fds)';


-- H7. va_fds_transactions - FDS 거래 연결
-- 기존: fds_trx
CREATE TABLE va_fds_transactions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fds_id          INT UNSIGNED NOT NULL COMMENT '기존 fds_idx',
    trx_id          BIGINT UNSIGNED NOT NULL COMMENT '기존 trx_idx',

    KEY idx_fds (fds_id),
    KEY idx_trx (trx_id),
    CONSTRAINT fk_vafds_trx_fds FOREIGN KEY (fds_id) REFERENCES va_fds(fds_id),
    CONSTRAINT fk_vafds_trx_trx FOREIGN KEY (trx_id) REFERENCES va_transactions(trx_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='FDS-거래 연결 (기존 fds_trx)';


-- H8. va_blacklist - 가상계좌 블랙리스트
-- 기존: vcnt_blacklist
CREATE TABLE va_blacklist (
    blacklist_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(255) DEFAULT NULL COMMENT '기존 blist_name',
    deposit_account VARCHAR(255) DEFAULT NULL COMMENT '기존 blist_deposit_account',
    deposit_bank    VARCHAR(255) DEFAULT NULL COMMENT '기존 blist_deposit_bank',
    account_number  VARCHAR(255) DEFAULT NULL COMMENT '기존 blist_account',
    bank_code       VARCHAR(255) DEFAULT NULL COMMENT '기존 blist_bank',
    birth           VARCHAR(255) DEFAULT NULL COMMENT '기존 blist_birth',
    phone           VARCHAR(255) DEFAULT NULL COMMENT '기존 blist_phone',
    name_ban        TINYINT(1) DEFAULT 0 COMMENT '기존 blist_name_ban (bit→tinyint)',
    is_active       TINYINT(1) DEFAULT 1,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 regdate',

    KEY idx_account (account_number(50)),
    KEY idx_name (name(50))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='가상계좌 블랙리스트 (기존 vcnt_blacklist)';


-- ============================================================================
-- 도메인 I: 게시판/공지
-- ============================================================================

-- I1. notices - 공지사항
-- 기존: g5_write_notice
CREATE TABLE notices (
    notice_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(255) NOT NULL COMMENT '기존 wr_subject',
    content         TEXT NOT NULL COMMENT '기존 wr_content',
    author_id       INT UNSIGNED NOT NULL COMMENT '기존 mb_id → FK',
    author_name     VARCHAR(255) DEFAULT NULL COMMENT '기존 wr_name',
    min_view_level  TINYINT UNSIGNED DEFAULT 3 COMMENT '최소 열람 레벨 (기존 wr_1)',
    is_pinned       TINYINT(1) DEFAULT 0,
    view_count      INT UNSIGNED DEFAULT 0 COMMENT '기존 wr_hit',
    status          ENUM('active','deleted') DEFAULT 'active',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 wr_datetime',
    updated_at      DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    KEY idx_author (author_id),
    KEY idx_status_created (status, created_at DESC),
    CONSTRAINT fk_notice_author FOREIGN KEY (author_id) REFERENCES members(member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='공지사항 (기존 g5_write_notice)';


-- I2. qna - Q&A
-- 기존: g5_write_qa
CREATE TABLE qna (
    qna_id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id       INT UNSIGNED DEFAULT NULL COMMENT '질문 ID (답변 시, 기존 wr_parent)',
    member_id       INT UNSIGNED NOT NULL COMMENT '기존 mb_id → FK',
    member_name     VARCHAR(255) DEFAULT NULL COMMENT '기존 wr_name',
    title           VARCHAR(255) NOT NULL COMMENT '기존 wr_subject',
    content         TEXT NOT NULL COMMENT '기존 wr_content',
    status          TINYINT UNSIGNED DEFAULT 0 COMMENT '0=미답변, 1=답변완료',
    is_secret       TINYINT(1) DEFAULT 0 COMMENT '기존 wr_option에 secret 포함 여부',
    view_count      INT UNSIGNED DEFAULT 0 COMMENT '기존 wr_hit',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 wr_datetime',

    KEY idx_member (member_id),
    KEY idx_parent (parent_id),
    KEY idx_status (status),
    CONSTRAINT fk_qna_member FOREIGN KEY (member_id) REFERENCES members(member_id),
    CONSTRAINT fk_qna_parent FOREIGN KEY (parent_id) REFERENCES qna(qna_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Q&A (기존 g5_write_qa)';


-- ============================================================================
-- 도메인 J: MetaPOS (POS매장)
-- ============================================================================

-- J1. metapos_stores - 매장
-- 기존: metapos_store
CREATE TABLE metapos_stores (
    store_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    store_uid       VARCHAR(50) NOT NULL COMMENT '외부 매장 UID (기존 st_uid)',
    branch_uid      VARCHAR(50) DEFAULT NULL COMMENT '브랜드 UID (기존 br_uid)',
    branch_name     VARCHAR(100) DEFAULT NULL COMMENT '브랜드명 (기존 br_name)',
    member_id       INT UNSIGNED DEFAULT NULL COMMENT '연결된 가맹점',
    store_name      VARCHAR(100) DEFAULT NULL COMMENT '기존 st_name',
    biz_no          VARCHAR(20) DEFAULT NULL COMMENT '기존 st_biz_no',
    ceo_name        VARCHAR(50) DEFAULT NULL COMMENT '기존 st_ceo_nm',
    tel             VARCHAR(50) DEFAULT NULL COMMENT '기존 st_tel',
    mobile          VARCHAR(50) DEFAULT NULL COMMENT '기존 st_hp',
    address         VARCHAR(255) DEFAULT NULL COMMENT '기존 st_addr',
    store_data      TEXT DEFAULT NULL COMMENT '매장 상세 정보 (기존 st_data)',
    status          ENUM('active','inactive') DEFAULT 'active' COMMENT '기존 st_use Y→active',

    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_uid (store_uid),
    KEY idx_member (member_id),
    KEY idx_branch (branch_uid),
    CONSTRAINT fk_metapos_member FOREIGN KEY (member_id) REFERENCES members(member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='MetaPOS 매장 (기존 metapos_store)';


-- J2. metapos_store_changes - 매장 변경 이력
-- 기존: metapos_store_history
CREATE TABLE metapos_store_changes (
    change_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    store_id        INT UNSIGNED NOT NULL COMMENT '기존 ms_id',
    store_uid       VARCHAR(50) DEFAULT NULL COMMENT '기존 st_uid',
    change_type     VARCHAR(20) NOT NULL COMMENT '기존 change_type',
    changed_fields  TEXT DEFAULT NULL COMMENT '기존 changed_fields',
    before_data     TEXT DEFAULT NULL COMMENT '기존 old_data',
    after_data      TEXT DEFAULT NULL COMMENT '기존 new_data',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    KEY idx_store (store_id),
    CONSTRAINT fk_metapos_change_store FOREIGN KEY (store_id) REFERENCES metapos_stores(store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='매장 변경 이력 (기존 metapos_store_history)';


-- J3. metapos_payments - MetaPOS 결제
-- 기존: metapos_payment
CREATE TABLE metapos_payments (
    mp_id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    store_id        INT UNSIGNED DEFAULT NULL COMMENT '기존 ms_id (st_uid로 매핑)',
    branch_uid      VARCHAR(50) DEFAULT NULL COMMENT '기존 br_uid',
    branch_name     VARCHAR(100) DEFAULT NULL COMMENT '기존 br_name',
    store_uid       VARCHAR(50) DEFAULT NULL COMMENT '기존 st_uid',
    store_name      VARCHAR(100) DEFAULT NULL COMMENT '기존 st_name',

    -- POS 결제 정보
    sale_date       VARCHAR(8) DEFAULT NULL COMMENT '기존 sal_ymd',
    sale_seq        INT DEFAULT NULL COMMENT '기존 sal_seq',
    bill_no         VARCHAR(50) DEFAULT NULL COMMENT '기존 bill_no',
    bill_status     VARCHAR(5) DEFAULT 'S' COMMENT '기존 bill_status',
    bill_amount     INT DEFAULT 0 COMMENT '기존 bill_amount',
    pay_amount      INT DEFAULT 0 COMMENT '기존 pay_amount',
    pay_method      VARCHAR(20) DEFAULT NULL COMMENT '기존 pay_method',
    pay_issuer      VARCHAR(50) DEFAULT NULL COMMENT '기존 pay_issuer',
    pay_card_no     VARCHAR(20) DEFAULT NULL COMMENT '기존 pay_card_no',
    pay_auth_number VARCHAR(20) DEFAULT NULL COMMENT '기존 pay_auth_number',
    pay_approved_at DATETIME DEFAULT NULL COMMENT '기존 pay_approved_at',

    -- 연결
    payment_id      BIGINT UNSIGNED DEFAULT NULL COMMENT '연결된 payments 레코드 (기존 g5_pay_id)',
    raw_data        TEXT DEFAULT NULL COMMENT '기존 raw_data',

    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    KEY idx_store (store_id),
    KEY idx_payment (payment_id),
    KEY idx_sale_date (sale_date),
    KEY idx_bill_no (bill_no),
    KEY idx_pay_method (pay_method),
    CONSTRAINT fk_mp_store FOREIGN KEY (store_id) REFERENCES metapos_stores(store_id)
    -- FK 제거됨: payment_id → payments (파티셔닝 테이블 FK 불가, 앱 레벨 검증)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='MetaPOS 결제 (기존 metapos_payment)';


-- ============================================================================
-- 도메인 K: 시스템/공통
-- ============================================================================

-- K1. system_config - 시스템 설정
-- 기존: g5_config
CREATE TABLE system_config (
    config_key      VARCHAR(50) PRIMARY KEY,
    config_value    TEXT DEFAULT NULL,
    description     VARCHAR(255) DEFAULT NULL,
    updated_at      DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='시스템 설정 Key-Value (기존 g5_config)';


-- K2. login_sessions - 로그인 세션/이력
-- 기존: g5_login
CREATE TABLE login_sessions (
    session_id      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id       INT UNSIGNED NOT NULL COMMENT '기존 mb_id → FK',
    login_ip        VARCHAR(45) NOT NULL COMMENT '기존 lo_ip (IPv6 대응)',
    user_agent      VARCHAR(500) DEFAULT NULL,
    location        TEXT DEFAULT NULL COMMENT '기존 lo_location',
    login_url       TEXT DEFAULT NULL COMMENT '기존 lo_url',
    login_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 lo_datetime',

    KEY idx_member (member_id),
    KEY idx_login_at (login_at),
    CONSTRAINT fk_session_member FOREIGN KEY (member_id) REFERENCES members(member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='로그인 이력 (기존 g5_login)';


-- K3. external_noti_config - 외부 NOTI 전송 설정
-- 기존: g5_noti
CREATE TABLE external_noti_config (
    noti_config_id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category        VARCHAR(50) DEFAULT NULL COMMENT '기존 nt_category',
    mbr_no          VARCHAR(255) DEFAULT NULL COMMENT '기존 nt_mbrno',
    target_url      VARCHAR(500) DEFAULT NULL COMMENT '기존 nt_url',
    memo            TEXT DEFAULT NULL COMMENT '기존 nt_memo',
    status          ENUM('active','inactive') DEFAULT 'active',
    last_update     DATETIME DEFAULT NULL COMMENT '기존 lastupdate',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 datetime',
    updated_at      DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '기존 updatetime',

    KEY idx_mbr (mbr_no(50))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='외부 NOTI 전송 설정 (기존 g5_noti)';


-- ============================================================================
-- 도메인 L: SMS
-- ============================================================================

-- L1. sms_config - SMS 설정
-- 기존: sms5_config
CREATE TABLE sms_config (
    config_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_number   VARCHAR(20) DEFAULT NULL COMMENT '기존 cf_phone',
    provider        VARCHAR(20) DEFAULT NULL,
    api_key         VARCHAR(255) DEFAULT NULL,
    config_data     JSON DEFAULT NULL,
    updated_at      DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '기존 cf_datetime'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='SMS 설정 (기존 sms5_config)';


-- L2. sms_history - SMS 전송 이력
-- 기존: sms5_history + sms5_write (통합)
CREATE TABLE sms_history (
    sms_id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_number   VARCHAR(20) DEFAULT NULL,
    receiver_number VARCHAR(255) NOT NULL COMMENT '기존 hs_hp',
    receiver_name   VARCHAR(30) DEFAULT NULL COMMENT '기존 hs_name',
    message         VARCHAR(255) DEFAULT NULL COMMENT '기존 hs_memo',
    sms_type        ENUM('SMS','LMS','MMS') DEFAULT 'SMS',
    status          TINYINT DEFAULT 0 COMMENT '전송 상태 (기존 hs_flag)',
    result_code     VARCHAR(255) DEFAULT NULL COMMENT '기존 hs_code',
    result_log      VARCHAR(255) DEFAULT NULL COMMENT '기존 hs_log',
    member_id       INT UNSIGNED DEFAULT NULL COMMENT '기존 mb_id',

    sent_at         DATETIME DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '기존 hs_datetime',

    KEY idx_receiver (receiver_number(20)),
    KEY idx_member (member_id),
    KEY idx_created (created_at),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='SMS 전송 이력 (기존 sms5_history + sms5_write 통합)';


-- ============================================================================
-- 정산 일별 요약 (필수, 크론으로 갱신)
-- ============================================================================

CREATE TABLE settlement_daily_summary (
    summary_id      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    settle_date     DATE NOT NULL,
    tid             VARCHAR(30) NOT NULL,
    merchant_id     INT UNSIGNED NOT NULL,
    approved_count  INT DEFAULT 0,
    approved_amount DECIMAL(15,2) DEFAULT 0,
    cancelled_count INT DEFAULT 0,
    cancelled_amount DECIMAL(15,2) DEFAULT 0,
    net_amount      DECIMAL(15,2) DEFAULT 0,
    total_fee       DECIMAL(12,2) DEFAULT 0,
    settlement_amount DECIMAL(12,2) DEFAULT 0,

    UNIQUE KEY uk_date_tid (settle_date, tid),
    KEY idx_merchant_date (merchant_id, settle_date),
    KEY idx_date (settle_date),
    KEY idx_tid_date (tid, settle_date),
    KEY idx_full_cover (merchant_id, settle_date, tid, approved_count, approved_amount,
                        cancelled_count, cancelled_amount, net_amount, total_fee, settlement_amount)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='정산 일별 요약 (크론으로 갱신)';


SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- PG사 마스터 초기 데이터
-- ============================================================================
INSERT INTO pg_providers (pg_code, pg_name, sort_order) VALUES
('k1',       '광원',     1),
('korpay',   '코페이',   2),
('danal',    '다날',     3),
('welcom',   '웰컴',     4),
('paysis',   '페이시스', 5),
('stn',      '섹타나인', 6),
('daou',     '다우',     7),
('routeup',  '루트업',   8);
