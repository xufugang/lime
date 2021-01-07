<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of adminModel
 *
 * @author xufugang
 */
class adminModel extends model {

    function __construct() {
        parent::__construct();
        $this->dbTable = 'admin';
    }

    /*
    * 生成用户token
    */
    public function getUserToken($uid = 0)
    {
        $saltkey = getRandInt(8);
        $usHash = md5(USER_AGENT);
        $key = md5($uid . '_' . $usHash . '_' . md5(C('System', 'vcode') . md5($saltkey)));
        if($key){
            $this->update(array('create_time' => TIME, 'token' => $key), array('id' => $uid));
        }
        return $key;
    }

    /*
     * 根据ID获取管理员信息
     * @param $id int 管理员ID
     * @return array
     */

    public function getUserInfoById($id) {
        return $this->where(array('id' => $id))->find();
    }

    /*
     * 管理员登陆设置
     * @param $userInfo array 管理员信息数组
     * @example array('id'=>1,'psw'=>XXXXXX)
     * @param $remember int cookie有效时间
     * @param $saveLogin bool 是否更新登录信息
     * @return bool
     */

    public function setUserLogin($userInfo = array(), $remember = 0, $saveLogin = true) {
        if ($saveLogin) {
            $this->update("login_count=login_count+1,login_time='" . TIME . "'", array('id' => $userInfo['id']));
        }
        $saltkey = getRandStr(8);
        $auth = setEnocde($userInfo['id'] . "\t" . $userInfo['psw'], admin::getAuthKey($saltkey));

        myCookie('s_saltkey', $saltkey, $remember);
        myCookie('s_auth', $auth, $remember);
        return true;
    }

    /*
     * 删除管理员
     */

    public function delAdmin($id) {
        //获取管理员信息
        $admin = $this->getUserInfoById($id);
        if (!$admin || !isHave($admin['aid'])) {
            return false;
        }
        //需要删除信息的表
        $list = array(
            'admin' => 'aid',
        );
        foreach ($list as $k => $v) {
            M($k)->delete(array($v => $admin['aid']));
        }
        return true;
    }

    /*
     * 生成用户密码
     * @param $psw string 原始密码
     * @return string 加密处理后的密码
     */

    public function setUserPassword($psw = '') {
        return md5(md5($psw . substr($psw, 0, 2)));
    }

}
