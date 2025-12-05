<?php
header('Content-Type: application/json; charset=utf-8');
$timestamp = time();
?>
{
  "name": "원성페이먼츠 판매자센터",
  "short_name": "원성페이먼츠",
  "description": "결제 관리, 정산 조회, 가맹점 관리를 위한 통합 솔루션",
  "start_url": "/",
  "display": "standalone",
  "background_color": "#050E3C",
  "theme_color": "#3B82F6",
  "orientation": "portrait-primary",
  "scope": "/",
  "lang": "ko-KR",
  "dir": "ltr",
  "categories": ["finance", "business"],
  "icons": [
    {
      "src": "/img/favicon.svg?v=<?php echo $timestamp; ?>",
      "sizes": "any",
      "type": "image/svg+xml",
      "purpose": "any maskable"
    },
    {
      "src": "/img/favicon.svg?v=<?php echo $timestamp; ?>",
      "sizes": "192x192 512x512",
      "type": "image/svg+xml",
      "purpose": "any"
    }
  ],
  "screenshots": [
    {
      "src": "/img/og_tag.png",
      "sizes": "1200x630",
      "type": "image/png",
      "form_factor": "wide"
    }
  ],
  "shortcuts": [
    {
      "name": "실시간 결제내역",
      "short_name": "결제내역",
      "description": "실시간 결제내역 조회",
      "url": "/?p=payment",
      "icons": [
        {
          "src": "/img/favicon.svg?v=<?php echo $timestamp; ?>",
          "sizes": "any"
        }
      ]
    },
    {
      "name": "정산조회",
      "short_name": "정산",
      "description": "정산 내역 조회",
      "url": "/?p=settlement",
      "icons": [
        {
          "src": "/img/favicon.svg?v=<?php echo $timestamp; ?>",
          "sizes": "any"
        }
      ]
    }
  ],
  "related_applications": [],
  "prefer_related_applications": false
}
