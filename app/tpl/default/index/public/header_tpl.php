<?php
if (!defined('IN_XLP')||!isset($Document) || !is_array($Document)) {
    exit('Access Denied!');
}
if (isset($Document['head'])) {
	if ($Document['pagename']){
		$pageTitle=$Document['pagename'].'_'.WEB_TITLE;	
	}else{
		$pageTitle=	WEB_TITLE;
	}
	if ($Document['keywords']){
		$Document['keywords'].=',';	
	}
	if ($Document['description']){
		$Document['description'].=',';	
	}
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta name="renderer" content="webkit|ie-comp|ie-stand">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
<meta http-equiv="Cache-Control" content="no-siteapp" />
<link href="<?php echo CSS_PATH;?>admin/h-ui/css/h-ui.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo CSS_PATH;?>admin/h-ui.admin/css/h-ui.admin.css" rel="stylesheet" type="text/css" />
<link href="<?php echo CSS_PATH;?>admin/h-ui.admin/css/style.css" rel="stylesheet" type="text/css" />
<link href="<?php echo CSS_PATH;?>admin/lib/hui-iconfont-1.0.7/iconfont.css" rel="stylesheet" type="text/css" />
<link href="<?php echo CSS_PATH;?>admin/lib/icheck/css/icheck.css" rel="stylesheet" type="text/css" />
<?php
if (isHave($Document['skin'])) {
?>
<link rel="stylesheet" type="text/css" href="<?php echo PUBLIC_PATH; ?>skin/default/skin.css" id="skin" />
<?php
}
?>
<!--[if lt IE 9]>
<script src="<?php echo JS_PATH;?>admin/lib/helper/html5.js"></script>
<script src="<?php echo JS_PATH;?>admin/lib/helper/respond.min.js"></script>
<script src="<?php echo JS_PATH;?>admin/lib/helper/PIE_IE678.js"></script>
<![endif]-->
<title><?php echo $pageTitle;?></title>
<?php
    if ($Document['mycss']) {
        getCss($Document['mycss']);
    }
?>
<script src="<?php echo JS_PATH;?>admin/jquery.1.9.1.min.js"></script>
<script src="<?php echo JS_PATH;?>admin/msgbox.js"></script>
<?php
    if ($Document['myjs']) {
        getJs($Document['myjs']);
    }
}
?>
<script>
var MAIN_PATH='<?php echo WEB_URL; ?>';
var PUBLIC_URL='<?php echo PUBLIC_PATH; ?>';
</script>
</head>
<body>
