<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of loginAction 用户登录
 *
 * @author xlp
 */
class loginAction extends commonAction
{

    function __construct()
    {
        parent::__construct();
    }

    public function index()
    {

        if ($this->_checkLogin(true)) {
            jumpTo(U('index/index'));
        }
        $refer = $this->_get('refer', '');
        if (!$refer) {
            $refer = U('index/index');
        } else {
            $refer = urldecode($refer);
        }
        $pageUid = getRandInt(6);
        $key = md5('user_login_info_' . getRandInt(10));
        $authCode = authcode(json_encode(array(
            'channel' => C('system', 'socket_channel'), //频道
            'uid' => $pageUid, //用户UID
            'qtime' => TIME, //请求时间
        )), 'ENCODE', C('system', 'socket_key'));

        $data = array(
            'action' => 'login',
            'data' => array(
                'id' => 0,
                'username' => '',
                'city_id' => 0,
                'notice_uid' => $pageUid,
                'refer' => $refer, //登陆回跳地址
                'is_auth' => 0
            )
        );
        cacheData($key, $data, 60 * 5);
        $socketUrl = parse_url(C('system', 'get_socket_url'));
        $getSocketUrl = (isset($socketUrl['scheme']) ? $socketUrl['scheme'] : 'ws') . '://' . (isset($socketUrl['host']) ? $socketUrl['host'] : '');
        $getSocketPath = isset($socketUrl['path']) ? $socketUrl['path'] : '/';
        $this->assign(array('authCode' => $authCode, 'key' => $key, 'getSocketUrl' => $getSocketUrl, 'getSocketPath' => $getSocketPath, 'hash' => formHash(), 'refer' => $refer));
        $this->display('index');


        /*$refer = U('index/index');
        $this->assign(array('hash' => formHash(), 'refer' => $refer));
        $this->display();*/
    }

    public function ajaxlogin()
    {
        if (!formCheck()) {
            $this->JsonReturn('表单提交有误，请刷新页面再试');
        }
        $refer = urldecode($this->_post('refer'));
        //接受参数
        $user = $this->_post('user');
        $pass = $this->_post('password');
        $remember = $this->_postid('remember', 0);
        if (!$user || !$pass) {
            $this->JsonReturn('帐号或密码为空');
        }
        $rs = M('admin')->where(array('name' => $user, 'status' => array(1, 2)))->find();
        if ($rs['status'] == 2) {
            $this->JsonReturn('您的账号已被冻结');
        }
        if (!$rs) {
            $this->JsonReturn('账号不存在或者已删除');
        } else {
            //直播员不允许登录
            if ($rs['group_id'] == 2) {
                $this->JsonReturn('未授权');
            }
            //时效性检测
            if ($rs['validity_time'] > 0 && time() > $rs['validity_time']) {
                $this->JsonReturn('账号已过有效期');
            }
            if ($rs['psw'] == D('admin')->setUserPassword($pass)) {
                //修改登录信息
                D('admin')->setUserLogin($rs, $remember);
                $this->JsonReturn('ok', array('aid' => $rs['id'], 'refer' => $refer), 1);
            } else {
                $this->JsonReturn('密码有误');
            }

        }

    }

    public function logout()
    {
        admin::setUserLoginOut();
        jumpTo(U('login/index'));
    }

}
