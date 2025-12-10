#!/bin/bash
################################################################################
# 프로젝트 파일 정리 스크립트
# 작성일: 2025-12-10
#
# 주의: 이 스크립트는 파일을 영구적으로 삭제합니다.
#       실행 전에 반드시 Git 커밋 또는 백업을 하세요!
################################################################################

echo ""
echo "========================================"
echo "  프로젝트 파일 정리 스크립트"
echo "========================================"
echo ""
echo "이 스크립트는 다음 파일들을 삭제합니다:"
echo "- 테스트 파일 (6개)"
echo "- Old 버전 파일 (9개)"
echo "- 빈 파일/디렉토리"
echo "- CSS 백업 파일 (5개)"
echo "- pay/ 디렉토리 (3,402개 파일)"
echo "- ver2/ 디렉토리 (89개 파일)"
echo ""
echo "총 약 3,511개 파일이 삭제됩니다."
echo ""
read -p "계속하시겠습니까? (y/n): " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]
then
    echo "취소되었습니다."
    exit 1
fi

echo ""
echo "삭제를 시작합니다..."
echo ""

# 테스트 파일 삭제
echo "[1/8] 테스트 파일 삭제 중..."
rm -f test.php test2.php test_table.php db_test.php phpversion.php __temp.php

# 빈 파일 삭제
echo "[2/8] 빈 파일 삭제 중..."
rm -f list.php

# 사용되지 않는 기능 파일 삭제
echo "[3/8] 사용되지 않는 기능 파일 삭제 중..."
rm -f button.php login_user.php recalculation.php write.php search.php

# Old 버전 파일 삭제
echo "[4/8] Old 버전 파일 삭제 중..."
rm -f cancel_payment_old.php login_old.php receipt_old.php
rm -f settlement_old.php sftp_payment_old.php tid_fee_old.php
rm -f main2.php payment_copy.php login2.php

# CSS 백업 파일 삭제
echo "[5/8] CSS 백업 파일 삭제 중..."
rm -f css/etc.css.bak css/mobile.css.bak css/renewal.css css/renewal.css.2 css/receipt_re.css

# 이미지 파일 삭제
echo "[6/8] 잘못된 이미지 파일 삭제 중..."
rm -f img/favicon-192.png.html

# 빈 디렉토리 삭제
echo "[7/8] 빈 디렉토리 삭제 중..."
rm -rf complaint/ sftp_test/

# 대용량 디렉토리 삭제
echo "[8/8] 중복/이전 버전 디렉토리 삭제 중 (시간이 걸릴 수 있습니다)..."
rm -rf pay/ ver2/

echo ""
echo "========================================"
echo "  정리 완료!"
echo "========================================"
echo ""
echo "약 3,511개 파일이 삭제되었습니다."
echo ""
echo "다음 단계:"
echo "1. Git 상태 확인: git status"
echo "2. Git 커밋: git add -A && git commit -m '불필요한 파일 정리'"
echo ""
