<?php
/**
 * 데모 결제 데이터 자동 생성기
 * - 오늘 날짜 g5_payment 데이터가 없으면 자동 생성
 * - g5_payment ~70건 + g5_payment_keyin ~15건 + metapos_payment ~15건 = ~100건
 * - 각 PG별 NOTI 테이블에도 동기화
 * - 약 10% 취소건 포함
 */

if (!defined('_GNUBOARD_')) exit;

function demo_generate_today_data() {
    $today = date('Y-m-d');

    // 이미 오늘 데이터가 있으면 스킵
    $check = sql_fetch("SELECT COUNT(*) as cnt FROM g5_payment WHERE pay_datetime >= '{$today} 00:00:00' AND pay_datetime <= '{$today} 23:59:59'");
    if ($check['cnt'] > 0) return;

    // 디바이스 목록 (계층 정보 포함)
    $devices = [];
    $result = sql_query("SELECT * FROM g5_device WHERE mb_6 != '' ORDER BY RAND() LIMIT 50");
    while ($row = sql_fetch_array($result)) {
        $devices[] = $row;
    }
    if (empty($devices)) return;

    // metapos 매장 목록
    $stores = [];
    $result_s = sql_query("SELECT * FROM metapos_store ORDER BY RAND()");
    while ($row = sql_fetch_array($result_s)) {
        $stores[] = $row;
    }

    // 기본 데이터 풀
    $amounts = [1000, 2000, 3000, 5000, 7000, 8000, 10000, 12000, 15000, 18000, 20000, 25000, 30000, 35000, 40000, 45000, 50000, 70000, 100000, 150000];
    $card_names = ['신한', '국민', '비씨', '하나', '삼성', '현대', '롯데', 'NH농협', '우리'];
    $pg_names = ['paysis', 'routeup', 'daou', 'stn'];
    $goods = ['프리미엄커피', '수제버거세트', '시그니처라떼', '과일주스', '치킨텐더', '비빔밥정식', '김치찌개', '스테이크세트', '파스타', '피자', '샐러드', '아메리카노', '카페모카', '생맥주', '디저트세트'];
    $pay_methods = ['카드'];
    $ymd = date('Ymd');
    $ymd_dash = $today;

    // ──────────────────────────────
    // 1. g5_payment 생성 (~70건, 약 7건 취소)
    // ──────────────────────────────
    $pay_count = 70;
    $cancel_count = 7;
    $fds_count = 5; // FDS 이상건 5건
    $inserted_payments = [];

    for ($i = 0; $i < $pay_count; $i++) {
        $dev = $devices[array_rand($devices)];
        $amount = $amounts[array_rand($amounts)];
        $card = $card_names[array_rand($card_names)];
        $pg = $pg_names[array_rand($pg_names)];
        $is_cancel = ($i >= $pay_count - $cancel_count);

        // 시간 랜덤 (09:00 ~ 21:00)
        $hour = str_pad(rand(9, 21), 2, '0', STR_PAD_LEFT);
        $min = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
        $sec = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
        $pay_datetime = "{$ymd_dash} {$hour}:{$min}:{$sec}";
        $app_dtm = "{$ymd}{$hour}{$min}{$sec}";

        $pay_type = $is_cancel ? 'N' : 'Y';
        $pay_val = $is_cancel ? -$amount : $amount;

        // FDS 이상건 플래그 (처음 5건 중 랜덤)
        $is_fds = (!$is_cancel && $i < $fds_count);
        $pp_limit_3m = ($is_fds && rand(0, 1)) ? 'Y' : '';
        $pp_limit_5m = ($is_fds && $pp_limit_3m != 'Y') ? 'Y' : ($is_fds && rand(0, 1) ? 'Y' : '');

        // 수수료 계산
        $fee1 = round($dev['mb_1_fee'], 3);
        $fee2 = round($dev['mb_2_fee'], 3);
        $fee3 = round($dev['mb_3_fee'], 3);
        $fee4 = round($dev['mb_4_fee'], 3);
        $fee5 = round($dev['mb_5_fee'], 3);
        $fee6 = round($dev['mb_6_fee'], 3);
        $sign = $is_cancel ? -1 : 1;
        $pay1 = round($amount * $fee1 / 100) * $sign;
        $pay2 = round($amount * $fee2 / 100) * $sign;
        $pay3 = round($amount * $fee3 / 100) * $sign;
        $pay4 = round($amount * $fee4 / 100) * $sign;
        $pay5 = round($amount * $fee5 / 100) * $sign;
        $pay6 = round($amount * $fee6 / 100) * $sign;

        $card_no = str_pad(rand(1000, 9999), 4, '0') . '00******' . rand(0, 9) . '*';
        $pay_num = str_pad(rand(10000000, 99999999), 8, '0');
        $trxid = 'DEMOTRX' . str_pad(rand(1000000, 9999999), 10, '0', STR_PAD_LEFT);
        $trackId = str_pad(rand(10000000, 99999999), 8, '0') . $app_dtm;

        $sql = "INSERT INTO g5_payment SET
            pay_type = '{$pay_type}',
            pay = '{$pay_val}',
            pay_num = '{$pay_num}',
            trxid = '{$trxid}',
            trackId = '{$trackId}',
            rootTrxId = '',
            pay_datetime = '{$pay_datetime}',
            pay_cdatetime = '0000-00-00 00:00:00',
            pay_parti = 0,
            pay_card_name = '{$card}',
            pay_card_num = '{$card_no}',
            pay_receipt = '',
            cday = '0000-00-00',
            mb_1 = '{$dev['mb_1']}', mb_1_name = '{$dev['mb_1_name']}', mb_1_fee = '{$fee1}', mb_1_pay = '{$pay1}',
            mb_2 = '{$dev['mb_2']}', mb_2_name = '{$dev['mb_2_name']}', mb_2_fee = '{$fee2}', mb_2_pay = '{$pay2}',
            mb_3 = '{$dev['mb_3']}', mb_3_name = '{$dev['mb_3_name']}', mb_3_fee = '{$fee3}', mb_3_pay = '{$pay3}',
            mb_4 = '{$dev['mb_4']}', mb_4_name = '{$dev['mb_4_name']}', mb_4_fee = '{$fee4}', mb_4_pay = '{$pay4}',
            mb_5 = '{$dev['mb_5']}', mb_5_name = '{$dev['mb_5_name']}', mb_5_fee = '{$fee5}', mb_5_pay = '{$pay5}',
            mb_6 = '{$dev['mb_6']}', mb_6_name = '{$dev['mb_6_name']}', mb_6_fee = '{$fee6}', mb_6_pay = '{$pay6}',
            dv_type = '{$dev['dv_type']}',
            dv_certi = '{$dev['dv_certi']}',
            dv_tid = '{$dev['dv_tid']}',
            dv_tid_ori = '',
            sftp_mbrno = '',
            sftp_nurock = '0000-00-00',
            pg_name = '{$pg}',
            memo = 0,
            deposit = 0,
            updatetime = '0000-00-00 00:00:00',
            datetime = '{$pay_datetime}',
            pp_limit_3m = '{$pp_limit_3m}',
            pp_limit_5m = '{$pp_limit_5m}'";
        sql_query($sql);
        $new_pay_id = sql_insert_id();

        // NOTI 테이블에도 INSERT
        $cancelYN = $is_cancel ? 'Y' : 'N';
        $goods_name = $goods[array_rand($goods)];
        $catId = 'DEMOTID' . str_pad(rand(10000, 99999), 5, '0');
        $ordNo = 'DEMOORD' . str_pad($new_pay_id, 10, '0', STR_PAD_LEFT);
        $ediNo = 'DEMOEDI' . str_pad($new_pay_id, 8, '0', STR_PAD_LEFT);
        $mid = 'DM' . str_pad(rand(1, 99), 3, '0', STR_PAD_LEFT);

        if ($pg == 'paysis') {
            sql_query("INSERT INTO g5_payment_paysis SET
                mid='{$mid}', payMethod='CARD', cancelYN='{$cancelYN}',
                tid='DEMOTRX" . str_pad($new_pay_id, 8, '0', STR_PAD_LEFT) . "',
                ediNo='{$ediNo}', appDtm='{$app_dtm}', amt='{$amount}',
                ordNo='{$ordNo}', appNo='{$pay_num}', quota='00',
                notiDnt='{$app_dtm}', cardNo='{$card_no}', catId='{$catId}',
                connCD='0003', datetime='{$pay_datetime}', fnNm='{$card}',
                goodsName='{$goods_name}', sync_status='success'");
        } else if ($pg == 'routeup') {
            sql_query("INSERT INTO g5_payment_routeup SET
                mid='{$mid}', tid='{$catId}',
                trx_id='DEMOTRX" . str_pad($new_pay_id, 8, '0', STR_PAD_LEFT) . "',
                ord_num='{$ordNo}', appr_num='{$pay_num}',
                amount='{$amount}', card_num='{$card_no}',
                item_name='{$goods_name}', buyer_name='데모구매자',
                is_cancel='" . ($is_cancel ? '1' : '0') . "',
                datetime='{$pay_datetime}', sync_status='success'");
        } else if ($pg == 'daou') {
            sql_query("INSERT INTO g5_payment_daou SET
                gid='DEMOGID" . str_pad($new_pay_id, 5, '0', STR_PAD_LEFT) . "',
                tid='DEMOTRX" . str_pad($new_pay_id, 8, '0', STR_PAD_LEFT) . "',
                ediNo='{$ediNo}', appNo='{$pay_num}', amt='{$amount}',
                cardNo='{$card_no}', catId='{$catId}', goodsName='{$goods_name}',
                cancelYN='{$cancelYN}', datetime='{$pay_datetime}', sync_status='success'");
        } else if ($pg == 'stn') {
            sql_query("INSERT INTO g5_payment_stn SET
                mbrRefNo='{$ordNo}', refNo='DTRX" . str_pad($new_pay_id, 8, '0', STR_PAD_LEFT) . "',
                applNo='{$pay_num}', amount='{$amount}',
                cardNo='{$card_no}', goodsName='{$goods_name}',
                cmd='" . ($is_cancel ? 'CANCEL' : 'BILL') . "',
                datetime='{$pay_datetime}', sync_status='success'");
        }

        $inserted_payments[] = ['pay_id' => $new_pay_id, 'amount' => $amount, 'is_cancel' => $is_cancel];
    }

    // ──────────────────────────────
    // 2. g5_payment_keyin 생성 (~15건, 2건 취소)
    // ──────────────────────────────
    $keyin_count = 15;
    $keyin_cancel = 2;

    // keyin 설정된 가맹점 조회
    $keyin_configs = [];
    $result_k = sql_query("SELECT * FROM g5_member_keyin_config WHERE mkc_use = 'Y' ORDER BY RAND() LIMIT 15");
    while ($row = sql_fetch_array($result_k)) {
        $keyin_configs[] = $row;
    }

    for ($i = 0; $i < min($keyin_count, count($keyin_configs)); $i++) {
        $kc = $keyin_configs[$i];
        $amount = $amounts[array_rand($amounts)];
        $card = $card_names[array_rand($card_names)];
        $goods_name = $goods[array_rand($goods)];
        $is_cancel = ($i >= $keyin_count - $keyin_cancel);
        $status = $is_cancel ? 'cancelled' : 'approved';

        $hour = str_pad(rand(9, 21), 2, '0', STR_PAD_LEFT);
        $min = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
        $sec = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
        $created_at = "{$ymd_dash} {$hour}:{$min}:{$sec}";
        $app_date = "{$ymd}{$hour}{$min}{$sec}";

        $oid = $kc['mkc_oid'] ? $kc['mkc_oid'] : 'D' . str_pad($kc['mkc_id'], 3, '0', STR_PAD_LEFT);
        $order_no = $oid . date('YmdHis') . strtoupper(substr(md5(microtime() . $i), 0, 12));
        $order_no = substr($order_no, 0, 30);

        $card_no = str_pad(rand(1000, 9999), 4, '0') . '********' . str_pad(rand(1000, 9999), 4, '0');
        $pay_num = str_pad(rand(10000000, 99999999), 8, '0');
        $pk_tid = substr($kc['mkc_mid'] ?: 'DEMO_MID', 0, 10) . '0101' . substr($app_date, 0, 14) . str_pad(rand(1000, 9999), 4, '0');
        $pk_tid = substr($pk_tid, 0, 30);

        $buyer_no = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        sql_query("INSERT INTO g5_payment_keyin SET
            pk_order_no = '{$order_no}',
            pk_merchant_oid = '{$oid}',
            mb_id = '{$kc['mb_id']}',
            mkc_id = '{$kc['mkc_id']}',
            pk_pg_code = '{$kc['mkc_pg_code']}',
            pk_pg_name = '{$kc['mkc_pg_name']}',
            pk_mid = '" . ($kc['mkc_mid'] ?: 'DEMO_MID') . "',
            pk_auth_type = '{$kc['mkc_type']}',
            pk_amount = '{$amount}',
            pk_installment = '00',
            pk_goods_name = '{$goods_name}',
            pk_buyer_name = '데모구매자{$buyer_no}',
            pk_buyer_phone = '010" . str_pad(rand(10000000, 99999999), 8, '0') . "',
            pk_buyer_email = 'demo{$buyer_no}@example.com',
            pk_card_issuer = '{$card}',
            pk_card_acquirer = '{$card}',
            pk_card_no_masked = '{$card_no}',
            pk_status = '{$status}',
            pk_res_code = '0000',
            pk_res_msg = '" . ($is_cancel ? '취소 완료' : '카드 결제 성공') . "',
            pk_app_no = '{$pay_num}',
            pk_app_date = '{$app_date}',
            pk_tid = '{$pk_tid}',
            pk_cancel_amount = " . ($is_cancel ? $amount : 0) . ",
            pk_cancel_name = " . ($is_cancel ? "'데모취소자'" : "NULL") . ",
            pk_cancel_reason = " . ($is_cancel ? "'데모 취소 테스트'" : "NULL") . ",
            pk_cancel_date = " . ($is_cancel ? "'{$created_at}'" : "NULL") . ",
            pk_mb_6_name = '데모가맹점',
            pk_request_data = '{\"masked\": true, \"demo\": true}',
            pk_response_data = '{\"masked\": true, \"demo\": true}',
            pk_operator_id = 'admin',
            pk_created_at = '{$created_at}',
            pk_updated_at = '{$created_at}'");
    }

    // ──────────────────────────────
    // 3. metapos_payment 생성 (~15건, 1건 취소)
    // ──────────────────────────────
    if (!empty($stores)) {
        $meta_count = 15;
        $meta_cancel = 1;

        for ($i = 0; $i < $meta_count; $i++) {
            $store = $stores[array_rand($stores)];
            $amount = $amounts[array_rand($amounts)];
            $card = $card_names[array_rand($card_names)];
            $is_cancel = ($i >= $meta_count - $meta_cancel);

            $hour = str_pad(rand(9, 21), 2, '0', STR_PAD_LEFT);
            $min = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
            $sec = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
            $paid_at = "{$ymd_dash} {$hour}:{$min}:{$sec}";
            $ordered_at = date('Y-m-d H:i:s', strtotime($paid_at) - rand(30, 300));
            $bill_status = $is_cancel ? 'C' : 'S';

            $sal_seq = 1000 + $i;
            $bill_no = date('ymd') . '-0101-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
            $vat = round($amount / 11);
            $card_no = str_pad(rand(1000, 9999), 4, '0') . '************';
            $auth_no = str_pad(rand(10000000, 99999999), 8, '0');

            sql_query("INSERT INTO metapos_payment SET
                br_uid = '{$store['br_uid']}',
                br_name = '{$store['br_name']}',
                st_uid = '{$store['st_uid']}',
                st_name = '{$store['st_name']}',
                sal_ymd = '{$ymd}',
                sal_seq = '{$sal_seq}',
                bill_sal_seq = '01',
                pos_uid = '001',
                pos_name = '{$store['st_name']}',
                bill_no = '{$bill_no}',
                bill_table_no = '" . str_pad(rand(1, 30), 3, '0', STR_PAD_LEFT) . "',
                bill_status = '{$bill_status}',
                bill_amount = '{$amount}',
                bill_vat = '{$vat}',
                bill_discount = 0,
                bill_ordered_at = '{$ordered_at}',
                bill_paid_at = '{$paid_at}',
                pay_price = '" . ($amount - $vat) . "',
                pay_vat = '{$vat}',
                pay_amount = '{$amount}',
                pay_method = '카드',
                pay_issuer = '{$card}카드',
                pay_card_no = '{$card_no}',
                pay_auth_number = '{$auth_no}',
                pay_card_month = 0,
                pay_approved_at = '{$paid_at}',
                pay_cash_id = '',
                raw_data = '{\"masked\": true, \"demo\": true}',
                created_at = NOW()");
        }
    }
}

// 실행
demo_generate_today_data();
