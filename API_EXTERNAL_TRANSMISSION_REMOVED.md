# API 외부 전송 코드 삭제 기록

**삭제 일시**: <?php echo date("Y-m-d H:i:s"); ?>

이 파일은 API에서 외부 서버로 데이터를 전송하는 모든 코드를 삭제하기 전에 기록한 문서입니다.

---

## 1. api/paysis/index.php

### 삭제된 코드 (라인 37-75):
```php
$urls = [
    'http://redpay.kr/api/paysis/index.php',
    'https://pay.wnapay.net/paysis.do'
];

$data = array('gid' => $gid, 'vid' => $vid, 'mid' => $mid, ...);

$mh = curl_multi_init();
$handles = [];

foreach ($urls as $url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_multi_add_handle($mh, $ch);
    $handles[] = $ch;
}
do {
    $status = curl_multi_exec($mh, $active);
    if ($active) {
        curl_multi_select($mh);
    }
} while ($status === CURLM_CALL_MULTI_PERFORM || $active);
$results = [];
foreach ($handles as $ch) {
    $results[] = curl_multi_getcontent($ch);
}
foreach ($handles as $ch) {
    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}
curl_multi_close($mh);
```

### 삭제된 코드 (라인 320-385, 주석처리):
```php
$notification_url = 'https://api.wannapayments.kr/api/v1/payment/notification/mainpay';
// curl 전송 코드...
```

---

## 2. api/welcom/index.php

### 삭제된 코드 (라인 208-247):
```php
$urls = [
    'http://noti.payvery.kr/api/v2/noti/welcome',
    'http://redpay.kr/api/welcom/index.php',
    'https://pgapi.thegoodpay.co.kr/api/webhooks/wanna',
    'https://pay.wnapay.net/welcom.do'
];

$data = array('mid' => $mid, 'pay_type' => $pay_type, ...);

$mh = curl_multi_init();
$handles = [];

foreach ($urls as $url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_multi_add_handle($mh, $ch);
    $handles[] = $ch;
}
// ... 나머지 curl_multi 처리 코드
```

### 삭제된 코드 (라인 424, 주석처리):
```php
$notification_url = 'https://api.wannapayments.kr/api/v1/payment/notification/welcomepayments';
```

---

## 3. api/secta9ine/index.php

### 삭제된 코드 (라인 47-84):
```php
$urls = [
    'http://redpay.kr/api/secta9ine/index.php',
    'https://pay.wnapay.net/secta9ine.do'
];

$data = array('cmd' => $cmd, 'paymethod' => $paymethod, ...);

$mh = curl_multi_init();
// ... curl_multi 처리 코드
```

### 삭제된 코드 (라인 95-117): MBR 노티 전송
```php
$sql = "SELECT * FROM g5_noti WHERE nt_mbrno = '{$mbrNo}'";
$url_row = sql_fetch($sql);

if ($url_row['nt_id']) {
    $data = array(...);
    $url = $url_row['nt_url'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);

    $sql = " update g5_noti set lastupdate = '".G5_TIME_YMDHIS."' where nt_id = '{$url_row['nt_id']}' ";
    sql_query($sql);
}
```

### 삭제된 코드 (라인 162, 주석처리):
```php
$url = 'https://www.salesbilling.co.kr:3636/api/wanna/noti/tran';
```

### 삭제된 코드 (라인 448, 주석처리):
```php
$notification_url = 'https://api.wannapayments.kr/api/v1/payment/notification/mainpay';
```

---

## 4. api/secta9ine/noti.php

### 삭제된 코드 (라인 3-22): mbrNo == "114004"
```php
if($mbrNo == "114004") {
    $data = array(...);
    $url = 'http://www.notibstnpay.com/HTN_NOTI/sector_noti';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
}
```

### 삭제된 코드 (라인 24-51): mbrNo == "113737" || "114274"
```php
if($mbrNo == "113737" || $mbrNo == "114274") {
    $data = array(...);
    $url = 'https://repayapi.devfm.co.kr/repaydata/receive';

    $ch = curl_init();
    // ... curl 전송 코드
    curl_close($ch);
}
```

### 삭제된 코드 (라인 53-71): mbrNo == "117267" || "117185" || "117186" || "117362"
```php
if($mbrNo == "117267" || $mbrNo == "117185" || $mbrNo == "117186" || $mbrNo == "117362") {
    $data = array(...);
    $url = 'https://ss-pay.co.kr/api/stn/';

    $ch = curl_init();
    // ... curl 전송 코드
    curl_close($ch);
}
```

### 삭제된 코드 (라인 73-91): mbrNo == "114685" || "114725" || "114687"
```php
if($mbrNo == "114685" || $mbrNo == "114725" || $mbrNo == "114687") {
    $data = array(...);
    $url = 'https://api.mipay.im/api/PGNoti/Mainpay';

    $ch = curl_init();
    // ... curl 전송 코드
    curl_close($ch);
}
```

### 삭제된 코드 (라인 93-111): mbrNo == "117268" || "117434" || "117433" || "117432"
```php
if($mbrNo == "117268" || $mbrNo == "117434" || $mbrNo == "117433" || $mbrNo == "117432") {
    $data = array(...);
    $url = 'https://www.salesbilling.co.kr:3636/api/wanna/noti/tran';

    $ch = curl_init();
    // ... curl 전송 코드
    curl_close($ch);
}
```

---

## 5. api/korpay/index.php

### 삭제된 코드 (라인 68-85):
```php
$data = array('gid' => $gid, 'vid' => $vid, ...);
$url = 'http://redpay.kr/api/korpay/index.php';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$result = curl_exec($ch);
curl_close($ch);
```

### 삭제된 코드 (라인 304, 주석처리):
```php
$notification_url = 'https://api.wannapayments.kr/api/v1/payment/notification/korpay';
```

---

## 6. api/danal/index.php

### 삭제된 코드 (라인 99-116):
```php
$data = array('QUOTA' => $QUOTA, 'TRANTIME' => $TRANTIME, ...);
$url = 'http://redpay.kr/api/danal/index.php';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$result = curl_exec($ch);
curl_close($ch);
```

---

## 7. api/daou/index.php

### 삭제된 코드 (라인 30-48):
```php
$data = array('gid' => $gid, 'wTid' => $wTid, ...);
$url = 'http://redpay.kr/api/daou/index.php';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$result = curl_exec($ch);
curl_close($ch);
```

---

## 8. api/lucy/index.php

### 삭제된 코드 (라인 48-87, 주석처리):
```php
$urls = [
    'http://redpay.kr/api/lucy/index.php'
];

$data = array('mid' => $mid, 'order_no' => $order_no, ...);

$mh = curl_multi_init();
$handles = [];

foreach ($urls as $url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_multi_add_handle($mh, $ch);
    $handles[] = $ch;
}
// ... curl_multi 처리 코드
```

### 삭제된 코드 (라인 247, 주석처리):
```php
$notification_url = 'https://api.wannapayments.kr/api/v1/payment/notification/welcomepayments';
```

---

## 9. api/lib/functions.php

### 삭제 대상 (라인 4):
```php
$api_endpoint = 'https://wanpas.mycafe24.com/api/v1/secta/rcv-tran.php';
```

---

## 삭제 요약

### 외부 전송 URL 목록:
1. http://redpay.kr/api/paysis/index.php
2. https://pay.wnapay.net/paysis.do
3. https://api.wannapayments.kr/api/v1/payment/notification/mainpay
4. http://noti.payvery.kr/api/v2/noti/welcome
5. http://redpay.kr/api/welcom/index.php
6. https://pgapi.thegoodpay.co.kr/api/webhooks/wanna
7. https://pay.wnapay.net/welcom.do
8. https://api.wannapayments.kr/api/v1/payment/notification/welcomepayments
9. http://redpay.kr/api/secta9ine/index.php
10. https://pay.wnapay.net/secta9ine.do
11. http://www.notibstnpay.com/HTN_NOTI/sector_noti
12. https://repayapi.devfm.co.kr/repaydata/receive
13. https://ss-pay.co.kr/api/stn/
14. https://api.mipay.im/api/PGNoti/Mainpay
15. https://www.salesbilling.co.kr:3636/api/wanna/noti/tran
16. http://redpay.kr/api/korpay/index.php
17. https://api.wannapayments.kr/api/v1/payment/notification/korpay
18. http://redpay.kr/api/danal/index.php
19. http://redpay.kr/api/daou/index.php
20. http://redpay.kr/api/lucy/index.php
21. https://wanpas.mycafe24.com/api/v1/secta/rcv-tran.php

### 삭제 파일 목록:
- api/paysis/index.php
- api/welcom/index.php
- api/secta9ine/index.php
- api/secta9ine/noti.php
- api/korpay/index.php
- api/danal/index.php
- api/daou/index.php
- api/lucy/index.php

---

**참고**: 이 파일은 복구가 필요할 경우를 대비한 백업 문서입니다.
