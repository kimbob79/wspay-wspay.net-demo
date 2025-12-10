@echo off
REM ================================================================================
REM 프로젝트 파일 정리 스크립트
REM 작성일: 2025-12-10
REM
REM 주의: 이 스크립트는 파일을 영구적으로 삭제합니다.
REM       실행 전에 반드시 Git 커밋 또는 백업을 하세요!
REM ================================================================================

echo.
echo ========================================
echo   프로젝트 파일 정리 스크립트
echo ========================================
echo.
echo 이 스크립트는 다음 파일들을 삭제합니다:
echo - 테스트 파일 (6개)
echo - Old 버전 파일 (9개)
echo - 빈 파일/디렉토리
echo - CSS 백업 파일 (5개)
echo - pay/ 디렉토리 (3,402개 파일)
echo - ver2/ 디렉토리 (89개 파일)
echo.
echo 총 약 3,511개 파일이 삭제됩니다.
echo.
echo 계속하시겠습니까?
pause

echo.
echo 삭제를 시작합니다...
echo.

REM 테스트 파일 삭제
echo [1/8] 테스트 파일 삭제 중...
if exist test.php del test.php
if exist test2.php del test2.php
if exist test_table.php del test_table.php
if exist db_test.php del db_test.php
if exist phpversion.php del phpversion.php
if exist __temp.php del __temp.php

REM 빈 파일 삭제
echo [2/8] 빈 파일 삭제 중...
if exist list.php del list.php

REM 사용되지 않는 기능 파일 삭제
echo [3/8] 사용되지 않는 기능 파일 삭제 중...
if exist button.php del button.php
if exist login_user.php del login_user.php
if exist recalculation.php del recalculation.php
if exist write.php del write.php
if exist search.php del search.php

REM Old 버전 파일 삭제
echo [4/8] Old 버전 파일 삭제 중...
if exist cancel_payment_old.php del cancel_payment_old.php
if exist login_old.php del login_old.php
if exist receipt_old.php del receipt_old.php
if exist settlement_old.php del settlement_old.php
if exist sftp_payment_old.php del sftp_payment_old.php
if exist tid_fee_old.php del tid_fee_old.php
if exist main2.php del main2.php
if exist payment_copy.php del payment_copy.php
if exist login2.php del login2.php

REM CSS 백업 파일 삭제
echo [5/8] CSS 백업 파일 삭제 중...
if exist css\etc.css.bak del css\etc.css.bak
if exist css\mobile.css.bak del css\mobile.css.bak
if exist css\renewal.css del css\renewal.css
if exist css\renewal.css.2 del css\renewal.css.2
if exist css\receipt_re.css del css\receipt_re.css

REM 이미지 파일 삭제
echo [6/8] 잘못된 이미지 파일 삭제 중...
if exist img\favicon-192.png.html del img\favicon-192.png.html

REM 빈 디렉토리 삭제
echo [7/8] 빈 디렉토리 삭제 중...
if exist complaint rmdir /s /q complaint
if exist sftp_test rmdir /s /q sftp_test

REM 대용량 디렉토리 삭제
echo [8/8] 중복/이전 버전 디렉토리 삭제 중 (시간이 걸릴 수 있습니다)...
if exist pay rmdir /s /q pay
if exist ver2 rmdir /s /q ver2

echo.
echo ========================================
echo   정리 완료!
echo ========================================
echo.
echo 약 3,511개 파일이 삭제되었습니다.
echo.
echo 다음 단계:
echo 1. Git 상태 확인: git status
echo 2. Git 커밋: git add -A ^&^& git commit -m "불필요한 파일 정리"
echo.
pause
