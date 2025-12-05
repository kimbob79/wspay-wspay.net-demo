<?php
	if($pp) { $bo_table = $pp; }
?>

<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=Edge; chrome=1"/>
<meta content="yes" name="apple-mobile-web-app-capable"/>
<meta name="viewport" content="minimum-scale=1.0, width=device-width, maximum-scale=1, user-scalable=no"/>
<meta name="apple-mobile-web-app-title" content="심플페이">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="description" content="심플페이">
<meta property="og:url" content="http://www.simplepay.kr/">
<meta property="og:image" content="http://www.simplepay.kr/img/img_ori.png">
<meta property="og:title" content="심플페이">
<meta property="og:type" content="website">
<meta property="og:description" content="심플페이">
<meta property="og:site_name" content="심플페이">
<meta name="robots" content="index, follow">
<meta name="keywords" content="심플페이 결제">

<link rel="shortcut icon" href="../img/favicon.ico" type="image/x-icon/">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage" content="../img/favicon/ms-icon-144x144.png">
<meta name="theme-color" content="#ffffff">


<title>원성페이먼츠</title>
<?php /*
<link rel="stylesheet" type="text/css" href="/homepage/css/aos.css">
 */ ?>
<link rel="stylesheet" type="text/css" href="./css/reset.css?ver=<?php echo time(); ?>">
<link rel="stylesheet" type="text/css" href="./css/layout.css?ver=<?php echo time(); ?>">
<style>
.ui-datepicker-title select { padding: 0; outline: 0;}
#ui-datepicker-div { background: #fff; margin: 5px 0 0 0; padding-top: 25px; box-shadow: 10px 10px 40px rgb(0 0 0 / 10%); border: 1px solid #ddd; }
#ui-datepicker-div .ui-datepicker-header { position: relative; margin-left: 20px; margin-right: 20px; }
#ui-datepicker-div .ui-datepicker-prev { position: absolute; display: inline-block; font-size: 12px; top: -30px; left: 0; }
#ui-datepicker-div .ui-datepicker-next { position: absolute; display: inline-block; font-size: 12px; top: -30px; right: 0; }
#ui-datepicker-div .ui-datepicker-title { position: relative; clear: both; margin-top: 20px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
#ui-datepicker-div .ui-datepicker-title select { height: auto; width: calc( 50% - 15px); min-width: 90px; padding: 4px 12px; border-radius: 0; font-size: 1.2rem; border:1px solid #ddd }
#ui-datepicker-div .ui-datepicker-calendar { margin-left: 20px; margin-right: 20px; }
#ui-datepicker-div .ui-datepicker-calendar th,
#ui-datepicker-div .ui-datepicker-calendar td { width: 35px; height: 30px; text-align: center; line-height: 30px; font-size: 1.2rem; }
#ui-datepicker-div .ui-datepicker-calendar th:first-child > span,
#ui-datepicker-div .ui-datepicker-calendar td:first-child > a { color: #dc3545; }
#ui-datepicker-div .ui-datepicker-calendar th:last-child > span,
#ui-datepicker-div .ui-datepicker-calendar td:last-child > a { color: #007bff; }
#ui-datepicker-div .ui-datepicker-calendar .ui-state-active { padding: 3px 6px; background: #6f42c1; color: #fff; font-weight: 600; }
#ui-datepicker-div .ui-datepicker-buttonpane { margin-top: 10px; display: flex; border-top: 1px solid #ddd;}
#ui-datepicker-div .ui-datepicker-buttonpane > button { flex: 1 1 50%; max-width: 50%; text-align: center; padding: 0.8rem; font-size: 1.2rem; }
#ui-datepicker-div .ui-datepicker-buttonpane > button:first-child { order: 2; background: #dc3545; color: #fff; }
#ui-datepicker-div .ui-datepicker-buttonpane > button:last-child  { order: 1; }
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript" src="./js/slick.min.js"></script>
<script type="text/javascript" src="./js/common.min.js"></script>
<?php /*
<script type="text/javascript" src="https://payapp.kr/homepage/js/aos.js">
</script>
<script type="text/javascript" src="https://payapp.kr/homepage/js/jquery-waypoints.min.js"></script>
<script type="text/javascript" src="https://payapp.kr/homepage/js/inview.min.js"></script>
<script type="text/javascript" src="https://payapp.kr/homepage/js/counterup.min.js"></script>
 */ ?>
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body>