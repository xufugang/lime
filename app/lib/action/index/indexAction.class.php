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
        $authCode = authcode(json_encode(array(
            'channel' => C('system', 'socket_channel'), //频道
            'uid' => admin::$adminInfo['id'], //用户UID
            'qtime' => TIME, //请求时间
        )), 'ENCODE', C('system', 'socket_key'));
        $socketUrl = parse_url(C('system', 'get_socket_url'));
        $getSocketUrl = (isset($socketUrl['scheme']) ? $socketUrl['scheme'] : 'ws') . '://' . (isset($socketUrl['host']) ? $socketUrl['host'] : '');
        $getSocketPath = isset($socketUrl['path']) ? $socketUrl['path'] : '/';
        $this->assign(array('authCode' => $authCode, 'getSocketUrl' => $getSocketUrl, 'getSocketPath' => $getSocketPath, 'sysMenu' => $this->sysMuen));
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
            $where['LIKE'] = array('name' => parent::safeSearch($q));
        }
        $rs = M('admin')->where($where)->order('create_time desc')->page($p)->findAll();
        $total = M('admin')->getTotal();
        $this->assign(array('total' => $total, 'rs' => $rs, 'p' => $p, 'group_id' => admin::$adminInfo['group_id']));
        $this->display('init');
    }

}
