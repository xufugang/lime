<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/**
 * Description of indexAction
 * 默认首页
 * @author xlp
 */
class indexAction extends commonAction
{
    public $sysMuen = '';

    function __construct()
    {
        parent::__construct();
        $this->sysMuen = C('menu_group_1');
        parent::_checkLogin();
        parent::_checkValidity();
    }

    public function index()
    {
        if (!$this->_checkLogin(true)) {
            jumpTo(U('login/index'));
        }
        $this->display();
    }

    public function init()
    {
        $p = $this->_get('p');
        $q = $this->_get('q');
        $type = $this->_get('type');
        $where = array();
        $where['status'] = array(1, 2);
        $where['group_id[>]'] = 0;
        if ($type) {
            $where['group_id'] = $type;
        }
        if (trim($q)) {
            $where['LIKE'] = array('username' => parent::safeSearch($q));
        }
        $rs = M('admin')->where($where)->order('login_time desc')->page($p)->findAll();
        $total = M('admin')->getTotal();
        $this->assign(array('total' => $total, 'rs' => $rs, 'p' => $p, 'group_id' => admin::$adminInfo['group_id']));
        $this->display('init');
    }

}
