
<?php

header("Content-type:text/html;charset=utf-8");
/**
 *  index.php XLPCMS 入口
 * @copyright			(C) 2005-2011 xlphp
 * @lastmodify			2014-06-26
 */
define('ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('IN_XLP', true);
define('APP_PATH', ROOT . 'app' . DIRECTORY_SEPARATOR);
define('XLPHP_PATH', dirname(ROOT) . DIRECTORY_SEPARATOR . 'xlphp' . DIRECTORY_SEPARATOR);
//调试模式
define('DEBUG', false);
require(XLPHP_PATH . 'base.php');
