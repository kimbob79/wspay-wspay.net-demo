-- ============================================================================
-- 원성페이먼츠 판매자센터 - 마이그레이션 SQL
-- 기존 DB(mpchosting) → 새 테이블 데이터 이관
-- 생성일: 2026-03-14
--
-- 실행 순서: schema_new.sql 실행 후 이 파일 실행
-- 주의: 반드시 트랜잭션 또는 배치 단위로 실행하고, 백업 후 진행
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';


-- ============================================================================
-- Phase 1: 기반 테이블 (의존성 없음)
-- ============================================================================

-- 1.1 pg_providers - 이미 schema_new.sql에서 INSERT 완료

-- 1.2 holidays ← settle_holidays
INSERT INTO holidays (holiday_date, holiday_name, holiday_type)
SELECT
    holiday_date,
    holiday_name,
    holiday_type
FROM settle_holidays
WHERE is_active = 1;

-- 1.3 system_config ← g5_config (필요한 설정만 추출)
-- g5_config는 단일 행 테이블이므로 주요 설정값만 KV로 분리
INSERT INTO system_config (config_key, config_value, description)
SELECT 'site_title', cf_title, '사이트 제목' FROM g5_config LIMIT 1;
INSERT IGNORE INTO system_config (config_key, config_value, description)
SELECT 'admin_email', cf_admin_email, '관리자 이메일' FROM g5_config LIMIT 1;
INSERT IGNORE INTO system_config (config_key, config_value, description)
SELECT 'use_email_certify', cf_use_email_certify, '이메일 인증 사용' FROM g5_config LIMIT 1;

-- 1.4 sms_config ← sms5_config
INSERT INTO sms_config (sender_number, updated_at)
SELECT cf_phone, cf_datetime FROM sms5_config LIMIT 1;


-- ============================================================================
-- Phase 2: 회원/계층
-- ============================================================================

-- 2.1 members ← g5_member
-- parent_member_id 계산: 자기 레벨 바로 위 mb_N 참조
INSERT INTO members (
    member_id, login_id, password_hash, name, company_name,
    email, phone, mobile, biz_reg_no,
    zipcode, address1, address2, address_jibeon,
    bank_name, account_number, account_holder, account_memo,
    hierarchy_level, parent_member_id,
    base_fee_rate, van_fee,
    is_keyin_allowed, is_keyin_popup, settle_type,
    sushian_store_id,
    status, original_level,
    last_login_at, last_login_ip,
    created_at, updated_at
)
SELECT
    mb_no,
    mb_id,
    mb_password,
    mb_name,
    COALESCE(NULLIF(mb_nick, ''), mb_name),
    NULLIF(mb_email, ''),
    NULLIF(mb_tel, ''),
    NULLIF(mb_hp, ''),
    NULLIF(mb_7, ''),
    CONCAT(NULLIF(mb_zip1, ''), NULLIF(mb_zip2, '')),
    NULLIF(mb_addr1, ''),
    NULLIF(mb_addr2, ''),
    NULLIF(mb_addr_jibeon, ''),
    NULLIF(mb_8, ''),
    NULLIF(mb_9, ''),
    NULLIF(mb_10, ''),
    NULLIF(mb_11, ''),
    mb_level,
    -- parent_member_id: 자기 레벨 바로 위의 mb_N 값으로 계산 (2차에서 업데이트)
    NULL,
    -- base_fee_rate: mb_homepage이 숫자 문자열이면 변환
    CASE
        WHEN mb_homepage REGEXP '^[0-9]+\\.?[0-9]*$' THEN CAST(mb_homepage AS DECIMAL(5,3))
        ELSE NULL
    END,
    COALESCE(mb_van_fee, 0),
    mb_mailling,
    mb_keyin_popup,
    COALESCE(mb_settle_gbn, 'N'),
    NULLIF(mb_sushian_id, ''),
    CASE
        WHEN mb_level = 1 THEN 'deleted'
        WHEN NULLIF(mb_leave_date, '') IS NOT NULL THEN 'deleted'
        WHEN NULLIF(mb_intercept_date, '') IS NOT NULL THEN 'suspended'
        ELSE 'active'
    END,
    CASE
        WHEN mb_sex != '' AND mb_sex REGEXP '^[0-9]+$' THEN CAST(mb_sex AS UNSIGNED)
        ELSE NULL
    END,
    CASE WHEN mb_today_login = '0000-00-00 00:00:00' THEN NULL ELSE mb_today_login END,
    NULLIF(mb_login_ip, ''),
    CASE WHEN mb_datetime = '0000-00-00 00:00:00' THEN NOW() ELSE mb_datetime END,
    NULL
FROM g5_member;

-- 2.1b parent_member_id 업데이트
-- 레벨 3 (가맹점) → 상위는 mb_5 (영업점, 레벨 4)
UPDATE members m
JOIN g5_member gm ON m.login_id = gm.mb_id
JOIN members parent ON parent.login_id = gm.mb_5
SET m.parent_member_id = parent.member_id
WHERE m.hierarchy_level = 3 AND gm.mb_5 != '';

-- 레벨 4 (영업점) → 상위는 mb_4 (대리점, 레벨 5)
UPDATE members m
JOIN g5_member gm ON m.login_id = gm.mb_id
JOIN members parent ON parent.login_id = gm.mb_4
SET m.parent_member_id = parent.member_id
WHERE m.hierarchy_level = 4 AND gm.mb_4 != '';

-- 레벨 5 (대리점) → 상위는 mb_3 (총판, 레벨 6)
UPDATE members m
JOIN g5_member gm ON m.login_id = gm.mb_id
JOIN members parent ON parent.login_id = gm.mb_3
SET m.parent_member_id = parent.member_id
WHERE m.hierarchy_level = 5 AND gm.mb_3 != '';

-- 레벨 6 (총판) → 상위는 mb_2 (지사, 레벨 7)
UPDATE members m
JOIN g5_member gm ON m.login_id = gm.mb_id
JOIN members parent ON parent.login_id = gm.mb_2
SET m.parent_member_id = parent.member_id
WHERE m.hierarchy_level = 6 AND gm.mb_2 != '';

-- 레벨 7 (지사) → 상위는 mb_1 (본사, 레벨 8)
UPDATE members m
JOIN g5_member gm ON m.login_id = gm.mb_id
JOIN members parent ON parent.login_id = gm.mb_1
SET m.parent_member_id = parent.member_id
WHERE m.hierarchy_level = 7 AND gm.mb_1 != '';

-- 레벨 8 (본사) → parent 없음 (최상위, 관리자 제외)


-- 2.2 member_hierarchy_closure 생성
-- Step 1: 자기 자신 (depth=0)
INSERT INTO member_hierarchy_closure (ancestor_id, descendant_id, depth)
SELECT member_id, member_id, 0 FROM members;

-- Step 2: 직속 부모 (depth=1)
INSERT INTO member_hierarchy_closure (ancestor_id, descendant_id, depth)
SELECT parent_member_id, member_id, 1
FROM members
WHERE parent_member_id IS NOT NULL;

-- Step 3: 재귀적으로 상위 관계 추가 (depth 2~6)
-- MariaDB에서는 반복 실행 필요 (새 행이 없을 때까지 최대 5회)
INSERT IGNORE INTO member_hierarchy_closure (ancestor_id, descendant_id, depth)
SELECT c1.ancestor_id, c2.descendant_id, c1.depth + c2.depth
FROM member_hierarchy_closure c1
JOIN member_hierarchy_closure c2 ON c1.descendant_id = c2.ancestor_id
WHERE c1.depth > 0 AND c2.depth > 0
AND NOT EXISTS (
    SELECT 1 FROM member_hierarchy_closure e
    WHERE e.ancestor_id = c1.ancestor_id AND e.descendant_id = c2.descendant_id
);

-- 2회차
INSERT IGNORE INTO member_hierarchy_closure (ancestor_id, descendant_id, depth)
SELECT c1.ancestor_id, c2.descendant_id, c1.depth + c2.depth
FROM member_hierarchy_closure c1
JOIN member_hierarchy_closure c2 ON c1.descendant_id = c2.ancestor_id
WHERE c1.depth > 0 AND c2.depth > 0
AND NOT EXISTS (
    SELECT 1 FROM member_hierarchy_closure e
    WHERE e.ancestor_id = c1.ancestor_id AND e.descendant_id = c2.descendant_id
);

-- 3회차
INSERT IGNORE INTO member_hierarchy_closure (ancestor_id, descendant_id, depth)
SELECT c1.ancestor_id, c2.descendant_id, c1.depth + c2.depth
FROM member_hierarchy_closure c1
JOIN member_hierarchy_closure c2 ON c1.descendant_id = c2.ancestor_id
WHERE c1.depth > 0 AND c2.depth > 0
AND NOT EXISTS (
    SELECT 1 FROM member_hierarchy_closure e
    WHERE e.ancestor_id = c1.ancestor_id AND e.descendant_id = c2.descendant_id
);

-- 4회차
INSERT IGNORE INTO member_hierarchy_closure (ancestor_id, descendant_id, depth)
SELECT c1.ancestor_id, c2.descendant_id, c1.depth + c2.depth
FROM member_hierarchy_closure c1
JOIN member_hierarchy_closure c2 ON c1.descendant_id = c2.ancestor_id
WHERE c1.depth > 0 AND c2.depth > 0
AND NOT EXISTS (
    SELECT 1 FROM member_hierarchy_closure e
    WHERE e.ancestor_id = c1.ancestor_id AND e.descendant_id = c2.descendant_id
);

-- 5회차
INSERT IGNORE INTO member_hierarchy_closure (ancestor_id, descendant_id, depth)
SELECT c1.ancestor_id, c2.descendant_id, c1.depth + c2.depth
FROM member_hierarchy_closure c1
JOIN member_hierarchy_closure c2 ON c1.descendant_id = c2.ancestor_id
WHERE c1.depth > 0 AND c2.depth > 0
AND NOT EXISTS (
    SELECT 1 FROM member_hierarchy_closure e
    WHERE e.ancestor_id = c1.ancestor_id AND e.descendant_id = c2.descendant_id
);


-- 2.3 member_documents ← g5_member_file
INSERT INTO member_documents (
    member_id, doc_type, doc_no, original_name, stored_name,
    file_size, created_at
)
SELECT
    m.member_id,
    CASE gf.bf_type
        WHEN 0 THEN 'other'
        WHEN 1 THEN 'id_card'
        WHEN 2 THEN 'biz_reg'
        WHEN 3 THEN 'bankbook'
        WHEN 4 THEN 'contract'
        WHEN 5 THEN 'seal'
        ELSE 'other'
    END,
    gf.bf_no,
    gf.bf_source,
    gf.bf_file,
    gf.bf_filesize,
    CASE WHEN gf.bf_datetime = '0000-00-00 00:00:00' THEN NOW() ELSE gf.bf_datetime END
FROM g5_member_file gf
JOIN members m ON m.login_id = gf.mb_id;


-- ============================================================================
-- Phase 3: 디바이스/PG 설정
-- ============================================================================

-- 3.1 devices ← g5_device
INSERT INTO devices (
    device_id, tid, merchant_id, pg_provider_id,
    device_type, cert_type, jungsan_type, open_date,
    agent_name, device_number, model, model_number,
    serial_number, usim, usim_number, history,
    sftp_mbrno, created_at, updated_at
)
SELECT
    gd.dv_id,
    gd.dv_tid,
    m.member_id,
    pp.pg_id,
    CASE
        WHEN gd.dv_type REGEXP '^[0-9]+$' THEN CAST(gd.dv_type AS UNSIGNED)
        ELSE 1
    END,
    NULLIF(gd.dv_certi, '0'),
    gd.dv_jungsan,
    CASE WHEN gd.dv_open_date = '0000-00-00' THEN NULL ELSE gd.dv_open_date END,
    NULLIF(gd.dv_agent, ''),
    NULLIF(gd.dv_number, ''),
    NULLIF(gd.dv_model, ''),
    NULLIF(gd.dv_model_number, ''),
    NULLIF(gd.dv_sn, ''),
    NULLIF(gd.dv_usim, ''),
    NULLIF(gd.dv_usim_number, ''),
    gd.dv_history,
    NULLIF(gd.sftp_mbrno, ''),
    gd.datetime,
    CASE WHEN gd.updatetime = '0000-00-00 00:00:00' THEN NULL ELSE gd.updatetime END
FROM g5_device gd
JOIN members m ON m.login_id = gd.mb_6
JOIN pg_providers pp ON pp.pg_id = gd.dv_pg;


-- 3.2 device_fee_structure ← g5_device의 mb_1~6 + mb_1_fee~6_fee
-- 레벨 8 (본사, mb_1)
INSERT INTO device_fee_structure (device_id, member_id, hierarchy_level, fee_rate, effective_from, effective_to, changed_at)
SELECT d.device_id, m.member_id, 8, gd.mb_1_fee, CURDATE(), '9999-12-31', NOW()
FROM g5_device gd
JOIN devices d ON d.device_id = gd.dv_id
JOIN members m ON m.login_id = gd.mb_1
WHERE gd.mb_1 != '' AND gd.mb_1 IS NOT NULL;

-- 레벨 7 (지사, mb_2)
INSERT INTO device_fee_structure (device_id, member_id, hierarchy_level, fee_rate, effective_from, effective_to, changed_at)
SELECT d.device_id, m.member_id, 7, gd.mb_2_fee, CURDATE(), '9999-12-31', NOW()
FROM g5_device gd
JOIN devices d ON d.device_id = gd.dv_id
JOIN members m ON m.login_id = gd.mb_2
WHERE gd.mb_2 != '' AND gd.mb_2 IS NOT NULL;

-- 레벨 6 (총판, mb_3)
INSERT INTO device_fee_structure (device_id, member_id, hierarchy_level, fee_rate, effective_from, effective_to, changed_at)
SELECT d.device_id, m.member_id, 6, gd.mb_3_fee, CURDATE(), '9999-12-31', NOW()
FROM g5_device gd
JOIN devices d ON d.device_id = gd.dv_id
JOIN members m ON m.login_id = gd.mb_3
WHERE gd.mb_3 != '' AND gd.mb_3 IS NOT NULL;

-- 레벨 5 (대리점, mb_4)
INSERT INTO device_fee_structure (device_id, member_id, hierarchy_level, fee_rate, effective_from, effective_to, changed_at)
SELECT d.device_id, m.member_id, 5, gd.mb_4_fee, CURDATE(), '9999-12-31', NOW()
FROM g5_device gd
JOIN devices d ON d.device_id = gd.dv_id
JOIN members m ON m.login_id = gd.mb_4
WHERE gd.mb_4 != '' AND gd.mb_4 IS NOT NULL;

-- 레벨 4 (영업점, mb_5)
INSERT INTO device_fee_structure (device_id, member_id, hierarchy_level, fee_rate, effective_from, effective_to, changed_at)
SELECT d.device_id, m.member_id, 4, gd.mb_5_fee, CURDATE(), '9999-12-31', NOW()
FROM g5_device gd
JOIN devices d ON d.device_id = gd.dv_id
JOIN members m ON m.login_id = gd.mb_5
WHERE gd.mb_5 != '' AND gd.mb_5 IS NOT NULL;

-- 레벨 3 (가맹점, mb_6)
INSERT INTO device_fee_structure (device_id, member_id, hierarchy_level, fee_rate, effective_from, effective_to, changed_at)
SELECT d.device_id, m.member_id, 3, gd.mb_6_fee, CURDATE(), '9999-12-31', NOW()
FROM g5_device gd
JOIN devices d ON d.device_id = gd.dv_id
JOIN members m ON m.login_id = gd.mb_6
WHERE gd.mb_6 != '' AND gd.mb_6 IS NOT NULL;


-- 3.3 pg_master_config ← g5_manual_payment_config
INSERT INTO pg_master_config (
    config_id, pg_provider_id, auth_type,
    api_key, mid, mkey,
    extra_config,
    is_active, memo, status,
    created_at, updated_at
)
SELECT
    mpc.mpc_id,
    pp.pg_id,
    mpc.mpc_type,
    NULLIF(mpc.mpc_api_key, ''),
    NULLIF(mpc.mpc_mid, ''),
    NULLIF(mpc.mpc_mkey, ''),
    -- PG사별 컬럼 → extra_config JSON
    CASE
        WHEN mpc.mpc_rootup_mid IS NOT NULL OR mpc.mpc_stn_mbrno IS NOT NULL OR mpc.mpc_winglobal_tid IS NOT NULL
        THEN JSON_OBJECT(
            'rootup_mid', mpc.mpc_rootup_mid,
            'rootup_tid', mpc.mpc_rootup_tid,
            'rootup_key', mpc.mpc_rootup_key,
            'stn_mbrno', mpc.mpc_stn_mbrno,
            'stn_apikey', mpc.mpc_stn_apikey,
            'winglobal_tid', mpc.mpc_winglobal_tid,
            'winglobal_apikey', mpc.mpc_winglobal_apikey
        )
        ELSE NULL
    END,
    CASE mpc.mpc_use WHEN 'Y' THEN 1 ELSE 0 END,
    mpc.mpc_memo,
    mpc.mpc_status,
    mpc.mpc_datetime,
    mpc.mpc_update
FROM g5_manual_payment_config mpc
JOIN pg_providers pp ON pp.pg_code = mpc.mpc_pg_code;


-- 3.4 merchant_keyin_config ← g5_member_keyin_config
INSERT INTO merchant_keyin_config (
    mkc_id, member_id, master_config_id,
    pg_provider_id, auth_type, api_key, mid, mkey,
    merchant_oid,
    cancel_allowed, duplicate_allowed, weekend_allowed,
    limit_once, limit_daily, limit_monthly, max_installment,
    time_start, time_end,
    is_active, status, created_at, updated_at
)
SELECT
    gmkc.mkc_id,
    m.member_id,
    gmkc.mpc_id,
    pp.pg_id,
    gmkc.mkc_type,
    NULLIF(gmkc.mkc_api_key, ''),
    NULLIF(gmkc.mkc_mid, ''),
    NULLIF(gmkc.mkc_mkey, ''),
    gmkc.mkc_oid,
    CASE gmkc.mkc_cancel_yn WHEN 'Y' THEN 1 ELSE 0 END,
    CASE gmkc.mkc_duplicate_yn WHEN 'Y' THEN 1 WHEN 'N' THEN 0 ELSE 0 END,
    CASE gmkc.mkc_weekend_yn WHEN 'Y' THEN 1 ELSE 0 END,
    gmkc.mkc_limit_once,
    gmkc.mkc_limit_daily,
    gmkc.mkc_limit_monthly,
    gmkc.mkc_max_installment,
    CASE
        WHEN gmkc.mkc_time_start IS NOT NULL THEN STR_TO_DATE(gmkc.mkc_time_start, '%H:%i')
        ELSE '00:00:00'
    END,
    CASE
        WHEN gmkc.mkc_time_end IS NOT NULL THEN STR_TO_DATE(gmkc.mkc_time_end, '%H:%i')
        ELSE '23:59:59'
    END,
    CASE gmkc.mkc_use WHEN 'Y' THEN 1 WHEN '1' THEN 1 ELSE 0 END,
    gmkc.mkc_status,
    gmkc.mkc_datetime,
    gmkc.mkc_update
FROM g5_member_keyin_config gmkc
JOIN members m ON m.login_id = gmkc.mb_id
LEFT JOIN pg_providers pp ON pp.pg_code = gmkc.mkc_pg_code;


-- ============================================================================
-- Phase 4: 결제 데이터 (가장 큰 테이블)
-- ============================================================================

-- 4.1 pg_raw_notifications ← 8개 PG NOTI 테이블 통합

-- 4.1a ← g5_payment_k1 (광원)
INSERT INTO pg_raw_notifications (
    pg_provider_id, raw_data,
    pg_trx_id, pg_approval_no, pg_amount, pg_cancel_yn, pg_tid,
    sync_status, sync_message, received_at
)
SELECT
    (SELECT pg_id FROM pg_providers WHERE pg_code = 'k1'),
    JSON_OBJECT(
        'mchtId', mchtId, 'trxId', trxId, 'tmnId', tmnId,
        'trxDate', trxDate, 'trxType', trxType, 'trackId', trackId,
        'authCd', authCd, 'issuer', issuer, 'acquirer', acquirer,
        'cardType', cardType, 'bin', bin, 'last4', last4,
        'installment', installment, 'amount', amount, 'rootTrxId', rootTrxId
    ),
    trxId,
    authCd,
    CAST(amount AS SIGNED),
    CASE WHEN trxType = 'CANCEL' THEN 'Y' ELSE 'N' END,
    tmnId,
    'success',
    NULL,
    COALESCE(datetime, NOW())
FROM g5_payment_k1;

-- 4.1b ← g5_payment_paysis (페이시스)
INSERT INTO pg_raw_notifications (
    pg_provider_id, raw_data,
    pg_trx_id, pg_approval_no, pg_amount, pg_cancel_yn, pg_tid,
    sync_status, sync_message, received_at
)
SELECT
    (SELECT pg_id FROM pg_providers WHERE pg_code = 'paysis'),
    JSON_OBJECT(
        'gid', gid, 'vid', vid, 'mid', mid, 'payMethod', payMethod,
        'appCardCd', appCardCd, 'cancelYN', cancelYN, 'tid', tid,
        'ediNo', ediNo, 'appDtm', appDtm, 'ccDnt', ccDnt,
        'amt', amt, 'remainAmt', remainAmt, 'buyerId', buyerId,
        'ordNm', ordNm, 'ordNo', ordNo, 'goodsName', goodsName,
        'appNo', appNo, 'quota', quota, 'cardNo', cardNo,
        'catId', catId, 'fnNm', fnNm
    ),
    gid,
    appNo,
    CAST(amt AS SIGNED),
    cancelYN,
    catId,
    COALESCE(sync_status, 'success'),
    sync_message,
    COALESCE(datetime, NOW())
FROM g5_payment_paysis;

-- 4.1c ← g5_payment_danal (다날)
INSERT INTO pg_raw_notifications (
    pg_provider_id, raw_data,
    pg_trx_id, pg_approval_no, pg_amount, pg_cancel_yn, pg_tid,
    sync_status, received_at
)
SELECT
    (SELECT pg_id FROM pg_providers WHERE pg_code = 'danal'),
    JSON_OBJECT(
        'CPID', CPID, 'O_TID', O_TID, 'TID', TID,
        'ORDERID', ORDERID, 'ITEMNAME', ITEMNAME, 'AMOUNT', AMOUNT,
        'TRANDATE', TRANDATE, 'TRANTIME', TRANTIME, 'CATID', CATID,
        'CARDNAME', CARDNAME, 'CARDNO', CARDNO, 'QUOTA', QUOTA,
        'CARDAUTHNO', CARDAUTHNO, 'TXTYPE', TXTYPE, 'CAT_ID', CAT_ID
    ),
    TID,
    CARDAUTHNO,
    CAST(AMOUNT AS SIGNED),
    CASE WHEN TXTYPE = 'CANCEL' THEN 'Y' ELSE 'N' END,
    COALESCE(NULLIF(CATID, ''), CAT_ID),
    'success',
    COALESCE(datetime, NOW())
FROM g5_payment_danal;

-- 4.1d ← g5_payment_korpay (코페이)
INSERT INTO pg_raw_notifications (
    pg_provider_id, raw_data,
    pg_trx_id, pg_approval_no, pg_amount, pg_cancel_yn, pg_tid,
    sync_status, received_at
)
SELECT
    (SELECT pg_id FROM pg_providers WHERE pg_code = 'korpay'),
    JSON_OBJECT(
        'gid', gid, 'vid', vid, 'mid', mid, 'payMethod', payMethod,
        'appCardCd', appCardCd, 'cancelYN', cancelYN, 'tid', tid,
        'ediNo', ediNo, 'appDtm', appDtm, 'ccDnt', ccDnt,
        'amt', amt, 'remainAmt', remainAmt, 'buyerId', buyerId,
        'ordNm', ordNm, 'ordNo', ordNo, 'goodsName', goodsName,
        'appNo', appNo, 'quota', quota, 'cardNo', cardNo,
        'catId', catId
    ),
    gid,
    appNo,
    CAST(amt AS SIGNED),
    cancelYN,
    catId,
    'success',
    COALESCE(datetime, NOW())
FROM g5_payment_korpay;

-- 4.1e ← g5_payment_welcom (웰컴)
INSERT INTO pg_raw_notifications (
    pg_provider_id, raw_data,
    pg_trx_id, pg_approval_no, pg_amount, pg_cancel_yn, pg_tid,
    sync_status, received_at
)
SELECT
    (SELECT pg_id FROM pg_providers WHERE pg_code = 'welcom'),
    JSON_OBJECT(
        'mid', mid, 'pay_type', pay_type, 'bank_code', bank_code,
        'transaction_flag', transaction_flag, 'order_no', order_no,
        'transaction_no', transaction_no, 'approval_ymdhms', approval_ymdhms,
        'cancel_ymdhms', cancel_ymdhms, 'amount', amount,
        'remain_amount', remain_amount, 'approval_no', approval_no,
        'card_sell_mm', card_sell_mm
    ),
    transaction_no,
    approval_no,
    CAST(amount AS SIGNED),
    CASE WHEN transaction_flag = '2' THEN 'Y' ELSE 'N' END,
    mid,
    'success',
    COALESCE(datetime, NOW())
FROM g5_payment_welcom;

-- 4.1f ← g5_payment_stn (섹타나인)
INSERT INTO pg_raw_notifications (
    pg_provider_id, raw_data,
    pg_trx_id, pg_approval_no, pg_amount, pg_cancel_yn, pg_tid,
    sync_status, sync_message, received_at
)
SELECT
    (SELECT pg_id FROM pg_providers WHERE pg_code = 'stn'),
    JSON_OBJECT(
        'cmd', cmd, 'paymethod', paymethod, 'payType', payType,
        'requestFlag', requestFlag, 'mbrRefNo', mbrRefNo, 'mbrNo', mbrNo,
        'refNo', refNo, 'tranDate', tranDate, 'tranTime', tranTime,
        'vanCatId', vanCatId, 'applNo', applNo, 'issueCompanyNo', issueCompanyNo,
        'acqCompanyNo', acqCompanyNo, 'cardNo', cardNo, 'installNo', installNo,
        'goodsName', goodsName, 'amount', amount, 'sid', sid
    ),
    refNo,
    applNo,
    CAST(amount AS SIGNED),
    CASE WHEN requestFlag = '2' THEN 'Y' ELSE 'N' END,
    vanCatId,
    COALESCE(sync_status, 'success'),
    sync_message,
    COALESCE(datetime, NOW())
FROM g5_payment_stn;

-- 4.1g ← g5_payment_daou (다우)
INSERT INTO pg_raw_notifications (
    pg_provider_id, raw_data,
    pg_trx_id, pg_approval_no, pg_amount, pg_cancel_yn, pg_tid,
    sync_status, sync_message, received_at
)
SELECT
    (SELECT pg_id FROM pg_providers WHERE pg_code = 'daou'),
    JSON_OBJECT(
        'mid', mid, 'tid', tid, 'trx_id', trx_id,
        'amount', amount, 'ord_num', ord_num, 'appr_num', appr_num,
        'item_name', item_name, 'buyer_name', buyer_name,
        'issuer', issuer, 'acquirer', acquirer,
        'card_num', card_num, 'installment', installment,
        'trx_dttm', trx_dttm, 'cxl_dttm', cxl_dttm,
        'is_cancel', is_cancel, 'ori_trx_id', ori_trx_id
    ),
    trx_id,
    appr_num,
    CAST(amount AS SIGNED),
    CASE WHEN is_cancel = 'Y' THEN 'Y' ELSE 'N' END,
    tid,
    COALESCE(sync_status, 'success'),
    sync_message,
    COALESCE(datetime, NOW())
FROM g5_payment_daou;

-- 4.1h ← g5_payment_routeup (루트업)
INSERT INTO pg_raw_notifications (
    pg_provider_id, raw_data,
    pg_trx_id, pg_approval_no, pg_amount, pg_cancel_yn, pg_tid,
    sync_status, sync_message, received_at
)
SELECT
    (SELECT pg_id FROM pg_providers WHERE pg_code = 'routeup'),
    JSON_OBJECT(
        'gid', gid, 'wTid', wTid, 'cardNm', cardNm,
        'cancelYN', cancelYN, 'tid', tid, 'ediNo', ediNo,
        'appDtm', appDtm, 'ccDnt', ccDnt, 'amt', amt,
        'ordNm', ordNm, 'goodsName', goodsName, 'appNo', appNo,
        'quota', quota, 'cardNo', cardNo, 'catId', catId, 'tmnId', tmnId
    ),
    gid,
    appNo,
    CAST(amt AS SIGNED),
    cancelYN,
    catId,
    COALESCE(sync_status, 'success'),
    sync_message,
    COALESCE(datetime, NOW())
FROM g5_payment_routeup;


-- 4.2 payments ← g5_payment
INSERT INTO payments (
    payment_id, trx_id, track_id, approval_no,
    pay_type, payment_method,
    amount, card_issuer, card_no_masked, installment,
    approved_at, cancelled_at,
    device_id, tid, pg_provider_id, pg_name, device_type, cert_type,
    merchant_id, merchant_name,
    receipt_data, sftp_mbrno, deposit,
    settle_secta, settle_paysis, settle_winglo, settle_kwon, settle_welcome,
    settle_yn, settle_ymd,
    memo_flag, created_at, updated_at
)
SELECT
    gp.pay_id,
    gp.trxid,
    NULLIF(gp.trackId, ''),
    NULLIF(gp.pay_num, ''),
    CASE gp.pay_type
        WHEN 'Y' THEN 'approval'
        WHEN 'N' THEN 'cancel'
        WHEN 'B' THEN 'partial_cancel'
        WHEN 'M' THEN 'net_cancel'
        WHEN 'X' THEN 'manual_cancel'
        ELSE 'approval'
    END,
    'card',
    gp.pay,
    NULLIF(gp.pay_card_name, '0'),
    NULLIF(gp.pay_card_num, '0'),
    gp.pay_parti,
    COALESCE(NULLIF(gp.pay_datetime, '0000-00-00 00:00:00'), NOW()),
    CASE WHEN gp.pay_cdatetime = '0000-00-00 00:00:00' THEN NULL ELSE gp.pay_cdatetime END,
    d.device_id,
    NULLIF(gp.dv_tid, ''),
    pp.pg_id,
    NULLIF(gp.pg_name, ''),
    CASE
        WHEN gp.dv_type REGEXP '^[0-9]+$' THEN CAST(gp.dv_type AS UNSIGNED)
        ELSE NULL
    END,
    CASE WHEN gp.dv_certi = 0 THEN NULL ELSE CAST(gp.dv_certi AS CHAR) END,
    m.member_id,
    NULLIF(gp.mb_6_name, ''),
    gp.pay_receipt,
    NULLIF(gp.sftp_mbrno, ''),
    gp.deposit,
    gp.pg_secta,
    gp.pg_paysis,
    gp.pg_winglo,
    gp.pg_kwon,
    gp.pg_welcome,
    gp.settle_yn,
    gp.settle_ymd,
    gp.memo,
    CASE WHEN gp.datetime = '0000-00-00 00:00:00' THEN NOW() ELSE gp.datetime END,
    CASE WHEN gp.updatetime = '0000-00-00 00:00:00' THEN NULL ELSE gp.updatetime END
FROM g5_payment gp
LEFT JOIN members m ON m.login_id = gp.mb_6
LEFT JOIN devices d ON d.tid = gp.dv_tid
LEFT JOIN pg_providers pp ON pp.pg_code = gp.pg_name;


-- 4.3 payment_fee_distribution ← g5_payment의 mb_1~6, mb_1_fee~6_fee, mb_1_pay~6_pay
-- 레벨 8 (본사, mb_1)
INSERT INTO payment_fee_distribution (payment_id, member_id, hierarchy_level, fee_rate, fee_amount)
SELECT p.payment_id, m.member_id, 8, gp.mb_1_fee, gp.mb_1_pay
FROM g5_payment gp
JOIN payments p ON p.payment_id = gp.pay_id
JOIN members m ON m.login_id = gp.mb_1
WHERE gp.mb_1 != '' AND gp.mb_1 IS NOT NULL;

-- 레벨 7 (지사, mb_2)
INSERT INTO payment_fee_distribution (payment_id, member_id, hierarchy_level, fee_rate, fee_amount)
SELECT p.payment_id, m.member_id, 7, gp.mb_2_fee, gp.mb_2_pay
FROM g5_payment gp
JOIN payments p ON p.payment_id = gp.pay_id
JOIN members m ON m.login_id = gp.mb_2
WHERE gp.mb_2 != '' AND gp.mb_2 IS NOT NULL;

-- 레벨 6 (총판, mb_3)
INSERT INTO payment_fee_distribution (payment_id, member_id, hierarchy_level, fee_rate, fee_amount)
SELECT p.payment_id, m.member_id, 6, gp.mb_3_fee, gp.mb_3_pay
FROM g5_payment gp
JOIN payments p ON p.payment_id = gp.pay_id
JOIN members m ON m.login_id = gp.mb_3
WHERE gp.mb_3 != '' AND gp.mb_3 IS NOT NULL;

-- 레벨 5 (대리점, mb_4)
INSERT INTO payment_fee_distribution (payment_id, member_id, hierarchy_level, fee_rate, fee_amount)
SELECT p.payment_id, m.member_id, 5, gp.mb_4_fee, gp.mb_4_pay
FROM g5_payment gp
JOIN payments p ON p.payment_id = gp.pay_id
JOIN members m ON m.login_id = gp.mb_4
WHERE gp.mb_4 != '' AND gp.mb_4 IS NOT NULL;

-- 레벨 4 (영업점, mb_5)
INSERT INTO payment_fee_distribution (payment_id, member_id, hierarchy_level, fee_rate, fee_amount)
SELECT p.payment_id, m.member_id, 4, gp.mb_5_fee, gp.mb_5_pay
FROM g5_payment gp
JOIN payments p ON p.payment_id = gp.pay_id
JOIN members m ON m.login_id = gp.mb_5
WHERE gp.mb_5 != '' AND gp.mb_5 IS NOT NULL;

-- 레벨 3 (가맹점, mb_6)
INSERT INTO payment_fee_distribution (payment_id, member_id, hierarchy_level, fee_rate, fee_amount)
SELECT p.payment_id, m.member_id, 3, gp.mb_6_fee, gp.mb_6_pay
FROM g5_payment gp
JOIN payments p ON p.payment_id = gp.pay_id
JOIN members m ON m.login_id = gp.mb_6
WHERE gp.mb_6 != '' AND gp.mb_6 IS NOT NULL;


-- 4.4 keyin_payments ← g5_payment_keyin
INSERT INTO keyin_payments (
    keyin_id, order_no, merchant_oid, member_id, mkc_id,
    pg_provider_id, pg_mid, auth_type,
    amount, installment, goods_name, buyer_name, buyer_phone, buyer_email,
    card_issuer, card_acquirer, card_no_masked,
    status, res_code, res_msg, approval_no, approval_date, pg_tid,
    cancel_amount, cancel_name, cancel_reason, cancel_date,
    request_data, response_data,
    operator_id, memo, created_at, updated_at
)
SELECT
    pk.pk_id,
    pk.pk_order_no,
    pk.pk_merchant_oid,
    m.member_id,
    pk.mkc_id,
    pp.pg_id,
    pk.pk_mid,
    pk.pk_auth_type,
    pk.pk_amount,
    pk.pk_installment,
    pk.pk_goods_name,
    pk.pk_buyer_name,
    pk.pk_buyer_phone,
    pk.pk_buyer_email,
    pk.pk_card_issuer,
    pk.pk_card_acquirer,
    pk.pk_card_no_masked,
    pk.pk_status,
    pk.pk_res_code,
    pk.pk_res_msg,
    pk.pk_app_no,
    pk.pk_app_date,
    pk.pk_tid,
    pk.pk_cancel_amount,
    pk.pk_cancel_name,
    pk.pk_cancel_reason,
    pk.pk_cancel_date,
    CASE
        WHEN pk.pk_request_data IS NOT NULL AND pk.pk_request_data != '' THEN pk.pk_request_data
        ELSE NULL
    END,
    CASE
        WHEN pk.pk_response_data IS NOT NULL AND pk.pk_response_data != '' THEN pk.pk_response_data
        ELSE NULL
    END,
    op.member_id,
    pk.pk_memo,
    pk.pk_created_at,
    pk.pk_updated_at
FROM g5_payment_keyin pk
JOIN members m ON m.login_id = pk.mb_id
LEFT JOIN pg_providers pp ON pp.pg_code = pk.pk_pg_code
LEFT JOIN members op ON op.login_id = pk.pk_operator_id;


-- 4.5 url_payments ← g5_url_payment
INSERT INTO url_payments (
    url_payment_id, short_code, member_id, mkc_id,
    amount, goods_name, goods_desc, buyer_name, buyer_phone,
    seller_name, seller_phone,
    expire_at, max_uses, use_count, status, memo,
    sms_sent, sms_sent_at, sms_count,
    keyin_payment_id, paid_at,
    operator_id, created_at, updated_at
)
SELECT
    up.up_id,
    up.up_code,
    m.member_id,
    up.mkc_id,
    up.up_amount,
    up.up_goods_name,
    up.up_goods_desc,
    up.up_buyer_name,
    up.up_buyer_phone,
    up.up_seller_name,
    up.up_seller_phone,
    up.up_expire_datetime,
    up.up_max_uses,
    up.up_use_count,
    up.up_status,
    up.up_memo,
    CASE up.up_sms_sent WHEN 'Y' THEN 1 ELSE 0 END,
    up.up_sms_sent_datetime,
    up.up_sms_count,
    up.pk_id,
    up.up_paid_datetime,
    op.member_id,
    up.up_created_at,
    up.up_updated_at
FROM g5_url_payment up
JOIN members m ON m.login_id = up.mb_id
LEFT JOIN members op ON op.login_id = up.up_operator_id;


-- 4.6 payment_memos ← g5_payment_memo
INSERT INTO payment_memos (
    memo_id, payment_id, author_id, author_name, content, ip_address, created_at
)
SELECT
    gpm.me_id,
    gpm.pay_id,
    m.member_id,
    gpm.mb_name,
    gpm.me_memo,
    NULLIF(gpm.ip, ''),
    CASE WHEN gpm.datetime = '0000-00-00 00:00:00' THEN NOW() ELSE gpm.datetime END
FROM g5_payment_memo gpm
JOIN members m ON m.login_id = gpm.mb_id;


-- ============================================================================
-- Phase 5: Webhook/정산/SFTP
-- ============================================================================

-- 5.1 webhook_configs ← g5_member_webhook
INSERT INTO webhook_configs (
    webhook_id, member_id, url, events,
    retry_count, retry_delay, timeout, status, memo,
    total_success, total_failed,
    last_success_at, created_at, updated_at
)
SELECT
    wh.wh_id,
    m.member_id,
    wh.wh_url,
    wh.wh_events,
    wh.wh_retry_count,
    wh.wh_retry_delay,
    wh.wh_timeout,
    wh.wh_status,
    wh.wh_memo,
    wh.wh_success_count,
    wh.wh_fail_count,
    wh.wh_last_success,
    wh.wh_reg_datetime,
    wh.wh_update_datetime
FROM g5_member_webhook wh
JOIN members m ON m.login_id = wh.mb_id;


-- 5.2 webhook_history ← g5_webhook_history
INSERT INTO webhook_history (
    history_id, webhook_id, member_id, payment_id,
    event_type, event_id, url, payload,
    http_status, response_body, response_time,
    retry_count, max_retry_count, status, error_message,
    created_at, updated_at
)
SELECT
    whh.whh_id,
    whh.wh_id,
    m.member_id,
    whh.pay_id,
    whh.whh_event_type,
    whh.whh_event_id,
    whh.whh_url,
    -- TEXT → JSON 변환 (유효한 JSON이 아닐 경우 문자열로 감싸기)
    CASE
        WHEN whh.whh_payload IS NOT NULL AND JSON_VALID(whh.whh_payload) THEN whh.whh_payload
        WHEN whh.whh_payload IS NOT NULL THEN JSON_QUOTE(whh.whh_payload)
        ELSE JSON_OBJECT('raw', '')
    END,
    whh.whh_http_status,
    whh.whh_response_body,
    whh.whh_response_time,
    whh.whh_retry_count,
    whh.whh_max_retry_count,
    CASE whh.whh_status
        WHEN 'timeout' THEN 'abandoned'
        ELSE whh.whh_status
    END,
    whh.whh_error_message,
    whh.whh_sent_datetime,
    whh.whh_completed_datetime
FROM g5_webhook_history whh
JOIN members m ON m.login_id = whh.mb_id;


-- 5.3 settlement_files ← settle_settlement_files
INSERT INTO settlement_files (
    file_id, file_type, file_name, stored_name, file_path,
    settle_date, upload_user, created_at
)
SELECT
    ssf.id,
    ssf.file_type,
    ssf.original_filename,
    ssf.stored_filename,
    ssf.file_path,
    ssf.settlement_date,
    ssf.upload_user,
    ssf.upload_date
FROM settle_settlement_files ssf;


-- 5.4 settlement_exclusions ← settle_exclude_mb3_list
INSERT INTO settlement_exclusions (member_id, reason, created_at)
SELECT
    m.member_id,
    sel.reason,
    COALESCE(sel.created_at, NOW())
FROM settle_exclude_mb3_list sel
JOIN members m ON m.login_id = sel.mb_3;


-- 5.5 sftp_members ← g5_sftp_member
INSERT INTO sftp_members (
    sftp_member_id, sftp_type, openmarket, biz_number, biz_type,
    biz_name, address, ceo_name, tel, email, website,
    mbr_no, error_code, memo, created_at, updated_at
)
SELECT
    sm.sm_id,
    CAST(sm.sm_type AS UNSIGNED),
    sm.sm_openmarket,
    sm.sm_bnumber,
    sm.sm_btype,
    sm.sm_bname,
    sm.sm_addr,
    sm.sm_ceo,
    sm.sm_tel,
    sm.sm_email,
    sm.sm_website,
    sm.sm_mbrno,
    sm.sm_error,
    sm.sm_memo,
    sm.datetime,
    sm.updatetime
FROM g5_sftp_member sm;


-- ============================================================================
-- Phase 6: 가상계좌 시스템
-- ============================================================================

-- 6.1 va_agents ← agent + agent_detail
INSERT INTO va_agents (
    agent_id, agent_name, depth, parent_agent_id,
    balance, withdraw_fee, status,
    biz_reg_no, ceo_name, address, address_detail,
    phone, email, identity_no, biz_type, biz_items,
    bank_code, account_number, account_holder, account_status, target_bank,
    created_at, updated_at
)
SELECT
    a.agent_idx,
    a.agent_name,
    a.depth,
    a.parent_agent_idx,
    a.agent_balance,
    a.agent_withdraw_fee,
    a.status,
    ad.agent_biz_reg,
    ad.agent_ceo,
    ad.agent_address,
    ad.agent_address_dtl,
    ad.agent_phone,
    ad.agent_email,
    ad.agent_identity,
    ad.agent_type,
    ad.agent_items,
    ad.agent_account_bank,
    ad.agent_account,
    ad.agent_account_holder,
    ad.agent_account_status,
    ad.agent_target_bank,
    ad.regdate,
    ad.uptdate
FROM agent a
LEFT JOIN agent_detail ad ON a.agent_idx = ad.agent_idx;


-- 6.2 va_merchants ← mcht + mcht_detail + mcht_info
INSERT INTO va_merchants (
    va_merchant_id, agent_id, merchant_name, status,
    balance, retention, collateral_amount, collateral_rate,
    hold_amount, withdraw_fee, limit_once,
    fds_status, access_ip,
    biz_reg_no, ceo_name, address,
    bank_code, account_number, account_holder, account_status, target_bank,
    telegram_config, va_config,
    created_at, updated_at
)
SELECT
    mc.mcht_idx,
    mc.agent_idx,
    mc.mcht_name,
    mc.status,
    mc.mcht_balance,
    mc.mcht_retention,
    mc.mcht_collateral_amount,
    mc.mcht_collateral_rate,
    mc.mcht_hold_amount,
    mc.mcht_withdraw_fee,
    mc.mcht_limit_once_amount,
    mc.mcht_fds_status,
    CONCAT_WS(',', mc.access_ip_1, mc.access_ip_2),
    md.mcht_biz_reg,
    md.mcht_ceo,
    CONCAT_WS(' ', md.mcht_address, md.mcht_address_dtl),
    md.mcht_account_bank,
    md.mcht_account,
    md.mcht_account_holder,
    md.mcht_account_status,
    md.mcht_target_bank,
    -- mcht_info telegram 설정 → JSON
    CASE
        WHEN mi.mcht_idx IS NOT NULL THEN JSON_OBJECT(
            'chat_id', mi.mcht_telegram_chat_id,
            'withdraw_noti', mi.mcht_telegram_withdraw_noti
        )
        ELSE NULL
    END,
    -- mcht_info 가상계좌 설정 → JSON
    CASE
        WHEN mi.mcht_idx IS NOT NULL THEN JSON_OBJECT(
            'deposit_fee', mi.mcht_vcnt_deposit_fee,
            'withdraw_fee', mi.mcht_vcnt_withdraw_fee,
            'withdraw_delay', mi.mcht_vcnt_withdraw_delay,
            'limit', mi.mcht_vcnt_limit,
            'auth_1won_check', mi.vcnt_auth_1won_check,
            'deposit_limit_day_amount', mi.vcnt_deposit_limit_day_amount,
            'deposit_limit_day_count', mi.vcnt_deposit_limit_day_count,
            'withdraw_limit_day_amount', mi.vcnt_withdraw_limit_day_amount,
            'withdraw_limit_day_count', mi.vcnt_withdraw_limit_day_count,
            'withdraw_limit_once_amount', mi.vcnt_withdraw_limit_once_amount
        )
        ELSE NULL
    END,
    md.regdate,
    md.uptdate
FROM mcht mc
LEFT JOIN mcht_detail md ON mc.mcht_idx = md.mcht_idx
LEFT JOIN mcht_info mi ON mc.mcht_idx = mi.mcht_idx;


-- 6.3 va_accounts ← vaccount
INSERT INTO va_accounts (
    account_id, va_merchant_id, status,
    real_bank, real_account, real_holder,
    virtual_bank, virtual_account, virtual_holder,
    phone, username,
    total_deposit, total_withdrawal,
    provider, created_at
)
SELECT
    vc.vcnt_idx,
    vc.mcht_idx,
    vc.status,
    vc.real_account_bank,
    vc.real_account,
    vc.real_account_holder,
    vc.vcnt_bank_name,
    vc.vcnt_account,
    vc.vcnt_account_name,
    vc.vcnt_phone,
    vc.vcnt_username,
    vc.vcnt_total_deposit_amount,
    vc.vcnt_total_withdrawal_amount,
    vc.provider,
    vc.regdate
FROM vaccount vc;


-- 6.4 va_transactions ← trx
INSERT INTO va_transactions (
    trx_id, trx_type, agent_id, va_merchant_id, account_id,
    amount, fixed_fee, deposit_balance,
    ht_account, ht_account_bank, ht_account_holder,
    settle_type, trx_status, raw_data,
    collateral_amount, retention, ip_address,
    trx_date, created_at, updated_at
)
SELECT
    t.trx_idx,
    t.trx_type,
    t.agent_idx,
    t.mcht_idx,
    t.vcnt_idx,
    t.amount,
    t.fixed_fee,
    t.mcht_deposit_balance,
    t.ht_account,
    t.ht_account_bank,
    t.ht_account_holder,
    t.settle_type,
    t.trx_status,
    t.raw_data,
    t.ht_collateral_amount,
    t.ht_retention,
    t.ip_address,
    t.trxdate,
    t.regdate,
    t.uptdate
FROM trx t;


-- 6.5 va_deposit_notifications ← deposit_noti
INSERT INTO va_deposit_notifications (
    noti_id, track_id, trx_id, status, trx_date, created_at
)
SELECT
    dn.noti_idx,
    dn.track_id,
    dn.trx_idx,
    dn.status,
    dn.trxdate,
    dn.regdate
FROM deposit_noti dn;


-- 6.6 va_fds ← fds
INSERT INTO va_fds (
    fds_id, va_merchant_id, status,
    fds_amount, hold_amount, additional_hold,
    fds_type, description, result, manager,
    start_date, created_at
)
SELECT
    f.fds_idx,
    f.mcht_idx,
    CASE f.status
        WHEN 'NORMAL' THEN 'NORMAL'
        WHEN 'READY' THEN 'READY'
        WHEN 'CLOSE' THEN 'CLOSE'
        ELSE 'READY'
    END,
    f.fds_amount,
    f.hold_amount,
    f.additional_hold_amount,
    f.fds_type,
    f.desc_cont,
    f.result_cont,
    f.fds_manager,
    f.startdate,
    f.regdate
FROM fds f;


-- 6.7 va_fds_transactions ← fds_trx
INSERT INTO va_fds_transactions (id, fds_id, trx_id)
SELECT fds_trx_idx, fds_idx, trx_idx FROM fds_trx;


-- 6.8 va_blacklist ← vcnt_blacklist
INSERT INTO va_blacklist (
    blacklist_id, name, deposit_account, deposit_bank,
    account_number, bank_code, birth, phone,
    name_ban, created_at
)
SELECT
    bl.blist_idx,
    bl.blist_name,
    bl.blist_deposit_account,
    bl.blist_deposit_bank,
    bl.blist_account,
    bl.blist_bank,
    bl.blist_birth,
    bl.blist_phone,
    bl.blist_name_ban,
    bl.regdate
FROM vcnt_blacklist bl;


-- ============================================================================
-- Phase 7: 나머지
-- ============================================================================

-- 7.1 notices ← g5_write_notice
INSERT INTO notices (
    notice_id, title, content, author_id, author_name,
    min_view_level, view_count, created_at
)
SELECT
    wn.wr_id,
    wn.wr_subject,
    wn.wr_content,
    m.member_id,
    wn.wr_name,
    CASE
        WHEN wn.wr_1 REGEXP '^[0-9]+$' THEN CAST(wn.wr_1 AS UNSIGNED)
        ELSE 3
    END,
    wn.wr_hit,
    CASE WHEN wn.wr_datetime = '0000-00-00 00:00:00' THEN NOW() ELSE wn.wr_datetime END
FROM g5_write_notice wn
JOIN members m ON m.login_id = wn.mb_id
WHERE wn.wr_is_comment = 0;


-- 7.2 qna ← g5_write_qa
-- 먼저 원글(질문)을 넣고, 그다음 답변(wr_parent > 0)을 넣음
INSERT INTO qna (
    qna_id, parent_id, member_id, member_name,
    title, content, is_secret, view_count, created_at
)
SELECT
    wq.wr_id,
    CASE WHEN wq.wr_parent > 0 AND wq.wr_parent != wq.wr_id THEN wq.wr_parent ELSE NULL END,
    m.member_id,
    wq.wr_name,
    wq.wr_subject,
    wq.wr_content,
    CASE WHEN FIND_IN_SET('secret', wq.wr_option) > 0 THEN 1 ELSE 0 END,
    wq.wr_hit,
    CASE WHEN wq.wr_datetime = '0000-00-00 00:00:00' THEN NOW() ELSE wq.wr_datetime END
FROM g5_write_qa wq
JOIN members m ON m.login_id = wq.mb_id
WHERE wq.wr_is_comment = 0
ORDER BY wq.wr_id ASC;


-- 7.3 metapos_stores ← metapos_store
INSERT INTO metapos_stores (
    store_id, store_uid, branch_uid, branch_name,
    store_name, biz_no, ceo_name, tel, mobile, address,
    store_data, status, created_at, updated_at
)
SELECT
    ms.ms_id,
    ms.st_uid,
    ms.br_uid,
    ms.br_name,
    ms.st_name,
    ms.st_biz_no,
    ms.st_ceo_nm,
    ms.st_tel,
    ms.st_hp,
    ms.st_addr,
    ms.st_data,
    CASE ms.st_use WHEN 'Y' THEN 'active' ELSE 'inactive' END,
    ms.created_at,
    ms.updated_at
FROM metapos_store ms;


-- 7.4 metapos_store_changes ← metapos_store_history
INSERT INTO metapos_store_changes (
    change_id, store_id, store_uid, change_type,
    changed_fields, before_data, after_data, created_at
)
SELECT
    msh.msh_id,
    msh.ms_id,
    msh.st_uid,
    msh.change_type,
    msh.changed_fields,
    msh.old_data,
    msh.new_data,
    msh.created_at
FROM metapos_store_history msh;


-- 7.5 metapos_payments ← metapos_payment
INSERT INTO metapos_payments (
    mp_id, store_id, branch_uid, branch_name, store_uid, store_name,
    sale_date, sale_seq, bill_no, bill_status, bill_amount,
    pay_amount, pay_method, pay_issuer, pay_card_no, pay_auth_number,
    pay_approved_at, payment_id, raw_data, created_at
)
SELECT
    mp.mp_id,
    ms.store_id,
    mp.br_uid,
    mp.br_name,
    mp.st_uid,
    mp.st_name,
    mp.sal_ymd,
    mp.sal_seq,
    mp.bill_no,
    mp.bill_status,
    mp.bill_amount,
    mp.pay_amount,
    mp.pay_method,
    mp.pay_issuer,
    mp.pay_card_no,
    mp.pay_auth_number,
    mp.pay_approved_at,
    mp.g5_pay_id,
    mp.raw_data,
    mp.created_at
FROM metapos_payment mp
LEFT JOIN metapos_stores ms ON ms.store_uid = mp.st_uid;


-- 7.6 login_sessions ← g5_login
INSERT INTO login_sessions (member_id, login_ip, location, login_url, login_at)
SELECT
    m.member_id,
    gl.lo_ip,
    gl.lo_location,
    gl.lo_url,
    CASE WHEN gl.lo_datetime = '0000-00-00 00:00:00' THEN NOW() ELSE gl.lo_datetime END
FROM g5_login gl
JOIN members m ON m.login_id = gl.mb_id;


-- 7.7 external_noti_config ← g5_noti
INSERT INTO external_noti_config (
    noti_config_id, category, mbr_no, target_url, memo,
    last_update, created_at, updated_at
)
SELECT
    gn.nt_id,
    gn.nt_category,
    gn.nt_mbrno,
    gn.nt_url,
    gn.nt_memo,
    gn.lastupdate,
    CASE WHEN gn.datetime = '0000-00-00 00:00:00' THEN NOW() ELSE gn.datetime END,
    gn.updatetime
FROM g5_noti gn;


-- 7.8 sms_history ← sms5_history
INSERT INTO sms_history (
    sms_id, receiver_number, receiver_name, message,
    status, result_code, result_log,
    created_at
)
SELECT
    sh.hs_no,
    sh.hs_hp,
    NULLIF(sh.hs_name, ''),
    NULLIF(sh.hs_memo, ''),
    sh.hs_flag,
    NULLIF(sh.hs_code, ''),
    NULLIF(sh.hs_log, ''),
    CASE WHEN sh.hs_datetime = '0000-00-00 00:00:00' THEN NOW() ELSE sh.hs_datetime END
FROM sms5_history sh;


-- ============================================================================
-- Phase 8: settlement_daily_summary 초기 데이터 생성
-- ============================================================================

-- settlement_daily_summary 초기 데이터 생성
INSERT INTO settlement_daily_summary (
    settle_date, tid, merchant_id,
    approved_count, approved_amount,
    cancelled_count, cancelled_amount,
    net_amount, total_fee, settlement_amount
)
SELECT
    DATE(p.approved_at) AS settle_date,
    p.tid,
    p.merchant_id,
    SUM(IF(p.pay_type = 'approval', 1, 0)),
    SUM(IF(p.pay_type = 'approval', p.amount, 0)),
    SUM(IF(p.pay_type != 'approval', 1, 0)),
    SUM(IF(p.pay_type != 'approval', p.amount, 0)),
    SUM(p.amount),
    COALESCE(SUM(pfd.fee_amount), 0),
    COALESCE(SUM(pfd.settlement_amount), 0)
FROM payments p
LEFT JOIN payment_fee_distribution pfd
    ON p.payment_id = pfd.payment_id AND pfd.hierarchy_level = 3
WHERE p.tid IS NOT NULL
GROUP BY DATE(p.approved_at), p.tid, p.merchant_id;


-- ============================================================================
-- Post-Migration: 검증 쿼리
-- ============================================================================

-- 고아 레코드 검증 (FK 대신 앱 레벨 검증)
SELECT 'payments: 존재하지 않는 merchant_id' AS check_name, COUNT(*) AS orphans
FROM payments p LEFT JOIN members m ON p.merchant_id = m.member_id
WHERE m.member_id IS NULL

UNION ALL
SELECT 'payments: 존재하지 않는 device_id', COUNT(*)
FROM payments p LEFT JOIN devices d ON p.device_id = d.device_id
WHERE p.device_id IS NOT NULL AND d.device_id IS NULL

UNION ALL
SELECT 'payment_fee_dist: 존재하지 않는 payment_id', COUNT(*)
FROM payment_fee_distribution pfd LEFT JOIN payments p ON pfd.payment_id = p.payment_id
WHERE p.payment_id IS NULL

UNION ALL
SELECT 'payment_memos: 존재하지 않는 payment_id', COUNT(*)
FROM payment_memos pm LEFT JOIN payments p ON pm.payment_id = p.payment_id
WHERE p.payment_id IS NULL;

-- 레코드 수 비교용 쿼리 (실행 결과로 검증)
SELECT 'members' AS tbl, COUNT(*) AS cnt FROM members
UNION ALL SELECT 'g5_member (원본)', COUNT(*) FROM g5_member
UNION ALL SELECT 'member_hierarchy_closure', COUNT(*) FROM member_hierarchy_closure
UNION ALL SELECT 'devices', COUNT(*) FROM devices
UNION ALL SELECT 'g5_device (원본)', COUNT(*) FROM g5_device
UNION ALL SELECT 'device_fee_structure', COUNT(*) FROM device_fee_structure
UNION ALL SELECT 'payments', COUNT(*) FROM payments
UNION ALL SELECT 'g5_payment (원본)', COUNT(*) FROM g5_payment
UNION ALL SELECT 'payment_fee_distribution', COUNT(*) FROM payment_fee_distribution
UNION ALL SELECT 'pg_raw_notifications', COUNT(*) FROM pg_raw_notifications
UNION ALL SELECT 'keyin_payments', COUNT(*) FROM keyin_payments
UNION ALL SELECT 'g5_payment_keyin (원본)', COUNT(*) FROM g5_payment_keyin
UNION ALL SELECT 'url_payments', COUNT(*) FROM url_payments
UNION ALL SELECT 'g5_url_payment (원본)', COUNT(*) FROM g5_url_payment
UNION ALL SELECT 'webhook_configs', COUNT(*) FROM webhook_configs
UNION ALL SELECT 'g5_member_webhook (원본)', COUNT(*) FROM g5_member_webhook
UNION ALL SELECT 'notices', COUNT(*) FROM notices
UNION ALL SELECT 'qna', COUNT(*) FROM qna
UNION ALL SELECT 'va_agents', COUNT(*) FROM va_agents
UNION ALL SELECT 'agent (원본)', COUNT(*) FROM agent
UNION ALL SELECT 'va_merchants', COUNT(*) FROM va_merchants
UNION ALL SELECT 'mcht (원본)', COUNT(*) FROM mcht
UNION ALL SELECT 'va_accounts', COUNT(*) FROM va_accounts
UNION ALL SELECT 'vaccount (원본)', COUNT(*) FROM vaccount
UNION ALL SELECT 'metapos_stores', COUNT(*) FROM metapos_stores
UNION ALL SELECT 'metapos_store (원본)', COUNT(*) FROM metapos_store
UNION ALL SELECT 'login_sessions', COUNT(*) FROM login_sessions
UNION ALL SELECT 'g5_login (원본)', COUNT(*) FROM g5_login;


SET FOREIGN_KEY_CHECKS = 1;
SET SQL_MODE = @OLD_SQL_MODE;
