<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of admin
 * 前端用户读取模块
 * @author xlp
 */
class admin {

    public static $adminInfo = array('id' => 0, 'psw' => 0, 'username' => '', 'groupid' => 1);

    /**
     * 根据会员编号获取会员信息,获取详细信息时会缓存会员资料
     * @param int $aid 会员编号
     * @return array
     */
    static function getUserById($aid) {
        static $admins = array();
        if (!isset($admins[$aid]) || empty($admins[$aid])) {
            $admins[$aid] = D('admin')->getUserInfoById($aid);
        }
        return $admins[$aid];
    }

    /* 获取当前登录用户信息
     * @param string 需要获取的字段
     * @return array or string
     */

    static function getLoginUser($field = '', $getData = '') {
        if (!self::$adminInfo['id']) {
            $admin = array();
            if (!$getData) {
                $getData = array('auth' => myCookie('s_auth'), 'saltkey' => myCookie('s_saltkey'));
            }
            if ($getData['auth'] && $getData['saltkey']) {
                $getData['auth'] = explode("\t", getDecode($getData['auth'], self::getAuthKey($getData['saltkey'])));
                list($aid, $psw) = empty($getData['auth']) || count($getData['auth']) < 2 ? array(0, '') : $getData['auth'];
                if ($aid) {
                    $admin = self::getUserById($aid);
                }
                if ($admin && $admin['psw'] == $psw) {
                    self::$adminInfo = $admin;
                    //self::$adminInfo['isAdmin'] = $admin['groupid'] == 1 ? true : false; //是否管理员
                }
            }
        }
        return $field && isset(self::$adminInfo[$field]) ? self::$adminInfo[$field] : self::$adminInfo;
    }

    static function getAuthKey($saltkey) {
        return md5(C('System', 'vcode') . md5($saltkey));
    }

    static function setUserLoginOut() {
        myCookie('s_auth', null);
        myCookie('s_saltkey', null);
        $_COOKIE['s_auth'] = null;
        $_COOKIE['s_saltkey'] = null;
        self::$adminInfo = array('id' => 0, 'aid' => 0, 'username' => '', 'groupid' => 0);
        return true;
    }

}
