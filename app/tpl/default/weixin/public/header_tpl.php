<?php
if (!defined('IN_XLP')||!isset($Document) || !is_array($Document)) {
    exit('Access Denied!');
}
if (isset($Document['head'])) {
?>
<!DOCTYPE HTML>
<html>
<head>
<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
<meta name="apple-mobile-web-app-capable" content="yes"/>
<meta name="apple-mobile-web-app-status-bar-style" content="black"/>
<meta name="format-detection" content="telephone=no"/>
<?php if (isset($Document['nocache'])&&$Document['nocache']) {?>
<meta http-equiv="pragram" content="no-cache"> 
<meta http-equiv="cache-control" content="no-cache,must-revalidate"> 
<meta http-equiv="expires" content="0"> 
<?php }?>
<meta charset="utf-8">
<title><?php echo $Document['pagename']; ?></title>
<script>
var MAIN_PATH='<?php echo WEB_URL; ?>';
var public_url='<?php echo PUBLIC_PATH; ?>';
</script>
<?php
  if ($Document['mycss']) {
	  getCss($Document['mycss'],true);
  }
  if ($Document['myjs']) {
	  getJs($Document['myjs'],true);
  }
}
?>
</head>
<body>

