<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/**
 * Description of commonAction
 * 微信全局调用父类
 * @author xlp
 */
class commonAction extends action
{

    public $setting = null;
    public $token = '';
    public $uid = '';

    //初始化全局信息

    function __construct()
    {
        parent::__construct();
        $this->setting = C("setting");
        $this->token = $_SERVER['HTTP_TOKEN'];
        $this->uid = $_SERVER['HTTP_UID'];
        //验证agent
        $agent = getUserAgent();
        if ($agent == 'unknown') {
            //$this->JsonReturn('非法请求');
        }

    }


}
