<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of memberModel
 * 用户数据库模型
 * @author xlp
 */
class memberModel extends model {

    function __construct() {
        parent::__construct();
        $this->dbTable = 'user';
    }

    /*
     * 根据UID获取用户信息，用户信息会缓存
     * @param $uid int UID
     * @param $aid int AID
     * @param $type string 获取类型，基本（默认）、全部
     * @return array
     */

    public function getUserInfoById($uid, $info = 'base') {
        static $_userInfo = array();
        if (isset($_userInfo[$info][$uid])) {
            return $_userInfo[$info][$uid];
        }
        $rs = $this->where(array('uid' => $uid, 'status' => 1))->find();
        if (!$rs) {
            return false;
        }
        $_userInfo[$info][$uid] = $rs;
        return $rs;
    }

    /*
     * 添加帐户
     * @param $openid string 必须字段
     * @return int uid
     */

    public function addUser($data, $openId) {
        //创建新用户
        $objData = array();
        //获取字段内容
        $fieldList = $this->getTableFields();
        foreach ($fieldList['fields'] as $key => $val) {
            $objData[$key] = $val['value'];
        }
        //赋值操作
        $objData['create_time'] = TIME;
        $objData['headimg'] = $data['headimg'];
        $objData['openid'] = $openId;
        $objData['uid'] = $this->insert($objData);
        if ($objData['uid']) {
            //扫尾工作，完成用户房子登记和第三方登录信息添加
            if ($openId) {
                D('userBind')->addBindInfo($objData['uid'], array(
                    'uid' => $objData['uid'],
                    'aid' => 0,
                    'village_id' => $objData['village_id'],
                    'keyids' => array('hn' => $openId),
                    'refresh_time' => 0
                ));
            }
            return $this->callbackMsg('ok', $objData, 1);
        } else {
            return $this->callbackMsg('帐号添加失败');
        }
    }

    /*
     * 用户登录，用于网页中
     * @param $userInfo array 用户数据
     * @example array('uid' => $uid, 'aid' => $aid)
     * @return null
     */

    public function setUserLogin($userInfo = array(), $remember = 0) {
        T('user/user');
        $saltkey = getRandInt(8);
        $auth = setEnocde($userInfo['uid'] . "\t" . $userInfo['resident_code'] . "\t" . $userInfo['village_id'], user::getAuthKey($saltkey));
        myCookie('saltkey', $saltkey, $remember);
        myCookie('auth', $auth, $remember);
        return true;
    }

    /*
     * 检查用户是否存在
     */

    public function checkUserIsExists($value, $field = 'phone') {
        if ($field == 'name') {
            $where = array('name' => $value);
        } else {
            $where = array('phone' => $value);
        }
        return $this->where($where)->getField('uid');
    }

    public function checkUserIsAuth($uid) {
        $rs = $this->getUserInfoById($uid);
        return isHave($rs['is_auth']) ? true : false;
    }

    /*
     * 将用户标记为不可用状态
     */

    public function delUserByUid($uid) {
        return $this->update(array('status' => 0), array('uid' => $uid));
    }

    public function checkUserName($nickName = '') {
        if (!$nickName) {
            return true;
        }
        $sensitive = C('setting', 'sensitive_username');
        foreach ($sensitive as $val) {
            if (strExists($nickName, $val)) {
                return false;
            }
        }
        return true;
    }

}
