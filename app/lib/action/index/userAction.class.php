<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of adminAction
 * 直播管理
 * @author xufg
 */
class userAction extends commonAction
{

    public function __construct()
    {
        parent::__construct();
        parent::_checkLogin();
        parent::_checkValidity();
    }

    public function index()
    {
        $p = $this->_getid('p', 1);
        $q = $this->_get('q');
        $where = 'a.status=1';
        if (trim($q)) {
            $where = $where . " and b.title like '" . trim($q) . "'";
        }
        $rs = M('look_log')->query('select a.*,b.title from __TABLE__ as a LEFT JOIN __PRE__live as b on a.live_id=b.id WHERE ' . $where . ' limit ' . (($p - 1) * 20) . ',20');
        M('look_log')->where(array('status' => 1))->findAll();
        $total = M('look_log')->getTotal();
        $this->assign(array('rs' => $rs, 'p' => $p, 'total' => $total));
        $this->display();
    }

}
