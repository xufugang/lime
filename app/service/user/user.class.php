<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of user
 * 前端用户读取模块
 * @author xlp
 */
class user
{

    public static $userInfo = array('uid' => 0,  'username' => '', 'phone'=>'','status' => 0, 'headimg' => '');
    //public static $userInfo = array('uid' => 0, 'nickname' => '', 'village_id' => '', 'is_auth' => 0, 'resident_code' => '', 'resident_id' => '', 'status' => 0, 'headimgurl' => '');
    /**
     * 根据会员编号获取会员信息,获取详细信息时会缓存会员资料
     * @param int $uid 会员编号
     * @return array
     */
    static function getUserById($uid, $villageId)
    {
        $rs = D('member')->getUserInfoById($uid);
        if (!$rs) {
            return array();
        }
        $rs['openid'] = '';
        $bindsinfo = D('userBind')->field('keyid,village_id')->where(array('uid' => $uid, 'village_id' => $villageId))->find();
        if ($bindsinfo) {
            $rs['openid'] = $bindsinfo['keyid'];
        }
        $rs['village_id'] = $villageId;
        return $rs;
    }

    /* 获取当前登录用户信息
     * @param string 需要获取的字段
     * @return array or string
     */

    static function getLoginUser($field = '')
    {
        if (!self::$userInfo['uid']) {
            $auth = '';
            $uid = 0;
            if (myCookie('auth') && myCookie('saltkey')) {
                $auth = myCookie('auth');
            }
            if ($auth) {
                $auth = explode("\t", getDecode($auth, self::getAuthKey(myCookie('saltkey'))));
                list($uid, $residentCode, $villageId) = empty($auth) || count($auth) < 3 ? array(0, 0) : $auth;
            }
            if ($uid) {
                $user = self::getUserById($uid, $villageId);
                if ($user) {
                    self::$userInfo = $user;
                    self::$userInfo['resident_code'] = $residentCode;
                }
            }
        }
        return $field && isset(self::$userInfo[$field]) ? self::$userInfo[$field] : self::$userInfo;
    }

    /* 生成加密密钥
     * @param $saltkey string 随机加盐值
     * @return string
     */

    static function getAuthKey($saltkey)
    {
        return md5(C('System', 'vcode') . md5($saltkey));
    }

    /* 退出登录
     * @param 无
     * @return bool
     */

    static function setUserLoginOut($uid = 0)
    {
        myCookie('auth', null);
        myCookie('saltkey', null);
        $_COOKIE['auth'] = null;
        $_COOKIE['saltkey'] = null;
        self::$userInfo = array('uid' => 0, 'nickname' => '', 'village_id' => 0, 'is_auth' => 0, 'resident_code' => '', 'resident_id' => '', 'status' => 0);
        mySession('wx_openid', null);
        //删除第三方登陆绑定信息
        if ($uid) {
            D('member')->update(array('is_auth' => 0), array('uid' => $uid));
//            D('userBind')->deleteBindInfo('wx', $uid);
//            D('userBind')->deleteBindInfo('opwx', $uid);
        }
        return true;
    }

}
