-- URL 결제 테이블
-- 생성일: 2026-01-29

CREATE TABLE IF NOT EXISTS `g5_url_payment` (
  `up_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'URL 결제 ID',
  `up_code` varchar(9) NOT NULL COMMENT '짧은 URL 코드 (9자리 영숫자)',
  `mb_id` varchar(50) NOT NULL COMMENT '가맹점 아이디',
  `mkc_id` int(11) DEFAULT NULL COMMENT 'Keyin 설정 ID',
  `up_amount` int(11) NOT NULL COMMENT '결제 금액',
  `up_goods_name` varchar(100) DEFAULT NULL COMMENT '상품명',
  `up_goods_desc` text COMMENT '상품설명',
  `up_buyer_name` varchar(50) DEFAULT NULL COMMENT '구매자명',
  `up_buyer_phone` varchar(20) DEFAULT NULL COMMENT '구매자 연락처',
  `up_seller_name` varchar(50) DEFAULT NULL COMMENT '판매자명',
  `up_seller_phone` varchar(20) DEFAULT NULL COMMENT '판매자 연락처',
  `up_expire_datetime` datetime NOT NULL COMMENT '유효기간',
  `up_max_uses` int(11) DEFAULT 1 COMMENT '최대 사용 횟수 (1=1회용, 0=무제한)',
  `up_use_count` int(11) DEFAULT 0 COMMENT '사용 횟수',
  `up_status` enum('active','used','expired','cancelled') DEFAULT 'active' COMMENT '상태',
  `up_memo` text COMMENT '관리 메모',

  -- SMS 발송 정보
  `up_sms_sent` char(1) DEFAULT 'N' COMMENT 'SMS 발송 여부',
  `up_sms_sent_datetime` datetime DEFAULT NULL COMMENT 'SMS 발송 일시',
  `up_sms_count` int(11) DEFAULT 0 COMMENT 'SMS 발송 횟수',

  -- 결제 완료 정보
  `pk_id` int(11) DEFAULT NULL COMMENT '결제 거래 ID (g5_payment_keyin)',
  `up_paid_datetime` datetime DEFAULT NULL COMMENT '결제 완료 일시',

  -- 계층 구조 (가맹점 정보 복사)
  `up_mb_1` varchar(50) DEFAULT NULL COMMENT '본사',
  `up_mb_2` varchar(50) DEFAULT NULL COMMENT '지사',
  `up_mb_3` varchar(50) DEFAULT NULL COMMENT '총판',
  `up_mb_4` varchar(50) DEFAULT NULL COMMENT '대리점',
  `up_mb_5` varchar(50) DEFAULT NULL COMMENT '영업점',
  `up_mb_6` varchar(50) DEFAULT NULL COMMENT '가맹점',
  `up_mb_6_name` varchar(50) DEFAULT NULL COMMENT '가맹점명',

  -- 관리 정보
  `up_operator_id` varchar(50) DEFAULT NULL COMMENT '등록자 ID (관리자)',
  `up_created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '등록일시',
  `up_updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일시',

  PRIMARY KEY (`up_id`),
  UNIQUE KEY `idx_code` (`up_code`),
  KEY `idx_mb_id` (`mb_id`),
  KEY `idx_status` (`up_status`),
  KEY `idx_expire` (`up_expire_datetime`),
  KEY `idx_created` (`up_created_at`),
  KEY `idx_mb_1` (`up_mb_1`),
  KEY `idx_mb_2` (`up_mb_2`),
  KEY `idx_mb_3` (`up_mb_3`),
  KEY `idx_mb_4` (`up_mb_4`),
  KEY `idx_mb_5` (`up_mb_5`),
  KEY `idx_mb_6` (`up_mb_6`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='URL 결제 링크';
