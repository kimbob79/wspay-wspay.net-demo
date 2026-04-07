# 코페이 노티 연동 API 가이드

---

## 노티 설명

1. 가맹점에서 응답서버를 준비하셔아 합니다.
2. 응답서버 URL를 메일로 영업담당자에게 요청
3. 결제내역을 POST 방식으로 전달합니다.
4. 노티 수신확인은 HTTP STATUS로 확인합니다. (200:수신, 이외 미수신)
5. 미수신 일경우 2분간격으로 5회 재전송 실시합니다.
6. 인코딩 UTF-8입니다.
7. 연동문의는 영업담당자에게 문의
8. 기술지원 (PG개발팀 : dev@korpay.com , 070-7012-1447)

---

## 노티 연동

가맹점에서 응답서버를 준비하시면 해당주소로 결제내역을 POST 방식으로 전달합니다.

### 정보
연동문의는 영업담당자에게 문의하셔야 합니다.

---

## 파라미터 정보

### 전달 파라미터

| 파라미터 | 타입 | 구분 | 설명 |
|----------|------|------|------|
| tid | String | Unique / 단말기, 온라인 / 30byte | 거래고유번호 (ex : ktest5561m01032012021713340481) |
| otid | String | Unique / 단말기, 온라인 / 30byte | 원거래 거래고유번호 (부분취소시 사용) |
| mid | String | 단말기, 온라인 / 10byte | 상점 ID (KORPAY제공 상점 MID) |
| gid | String | 단말기, 온라인 / 10byte | 상점 GID |
| vid | String | 단말기, 온라인 / 10byte | 총판 VID |
| payMethod | String | 단말기, 온라인 | 결제수단 (CARD: 카드결제 고정) |
| appCardCd | String | 단말기, 온라인 | 발급사코드/ 카드코드/ 은행코드/ 상품권사코드/ 휴대폰코드 |
| cancelYN | String | 단말기, 온라인 | 취소구분 (Y: 취소, N: 승인) |
| ediNo | String | 단말기, 온라인 | VAN거래고유번호 (ex : 160737115186) |
| appDtm | String | 단말기, 온라인 | 승인일 (yyyyMMddHHmmss) |
| ccDnt | String | 단말기, 온라인 | 취소일 (cancelYN == Y, yyyyMMddHHmmss) |
| amt | String | 단말기, 온라인 | 금액 (amt > 0) |
| buyerId | String | 단말기, 온라인 | 구매자 ID |
| ordNm | String | 단말기, 온라인 | 구매자명 |
| ordNo | String | Unique / 단말기, 온라인 | 주문번호 (12016120230411100300) |
| goodsName | String | 단말기, 온라인 | 상품명 |
| appNo | String | 단말기, 온라인 | 카드사 승인번호(12345678) |
| quota | String | 단말기, 온라인 | 할부개월 (00 : 일시불, 01 : 1개월...) |
| notiDnt | String | 단말기, 온라인 | Noti 통보일 (CurrentTimeMillis 형식) |
| cardNo | String | 단말기, 온라인 | 카드번호 (12345678****1234) |
| catId | String | 단말기, 온라인 | 단말기 CAT_ID (단말기 터미널 아이디, 온라인 거래 일때는 가맹점 MID) |
| connCd | String | 단말기, 온라인 | 단말기/수기결제 구분 (0003: 오프라인 0005: 수기결제) |
| tPhone | String | 단말기 | phone 번호 입력 사항 (단말기에서 올라오는 전화번호) |
| remainAmt | String | 단말기 | 잔액 (승인 , 전체취소시에는 “0” 부분취소시에는 잔액) |
| fnNm | String | 온라인 | 카드사/은행/입금은행/이동통신사 이름 |
| acqCardCd | String | 단말기, 온라인 | 매입사코드 |
| usePointAmt | String | 온라인 | 카드사 사용포인트 (카드 포인트) |
| vacntNo | String | 온라인 | 가상계좌 번호 (구매자 가상계좌번호) |
| socHpNo | String | 온라인 | 휴대폰번호 (구매자 휴대폰번호) |
| charSet | String | 온라인 | 1 고정 |
| hashStr | String | 온라인 | 해쉬키 (SHA256.encoding(mid + ediDate + amt+ mKey)) |
| ediDate | String | 온라인 | Noti 통보일 (CurrentTimeMillis 형식) |
| lmtDay | String | 온라인 | 예약필드 |
| resultCd | String | 온라인 | 수기결제 상태값 (수기결재 3001 : 승인 , 2001: 취소 (cancelYN 으로 대체 요망) ) |
| cashCrctFlg | String | 온라인 | 현금영수증 발행여부 (Y/N, 현재 사용 안함) |

---

## 노티 발송 예시

### 단말기 승인샘플

```bash
curl --location --request POST '수신받을 노티주소' --header 'Content-Type: application/x-www-form-urlencoded' --data-urlencode "gid=test11110g" --data-urlencode "remainAmt=0" --data-urlencode "cancelYN=N" --data-urlencode "mid=ktest6111m" --data-urlencode "amt=1000" --data-urlencode "appNo=30059295" --data-urlencode "ccDnt=" --data-urlencode "buyerId=" --data-urlencode "cardNo=12345678****123*" --data-urlencode "tid=ktest6111m01032304111003000874" --data-urlencode "otid=ktest6111m01032304111003000874" --data-urlencode "vid=ctest0001a" --data-urlencode "tPhone=" --data-urlencode "ordNm=" --data-urlencode "catId=1234567890" --data-urlencode "connCd=0003" --data-urlencode "ordNo=12016120230411100300" --data-urlencode "ediNo=2023041110C1359126" --data-urlencode "payMethod=CARD" --data-urlencode "quota=00" --data-urlencode "appDtm=20230411100300" --data-urlencode "goodsName=1234567890" --data-urlencode "appCardCd=02" --data-urlencode "acqCardCd=02" --data-urlencode "notiDnt=20230411101512"
```

---

(이하 취소/온라인 샘플도 동일하게 포함됨 - 전체 유지)

