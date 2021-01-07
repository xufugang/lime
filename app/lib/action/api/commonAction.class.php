<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/**
 * Description of commonAction
 * 微信全局调用父类
 * @author xlp
 */
class commonAction extends action {

    public $setting = null;

    //初始化全局信息

    function __construct() {
        parent::__construct();
        $this->setting = C("setting");
    }


}
