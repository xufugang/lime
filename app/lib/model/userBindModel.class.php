<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of userBindModel
 * 用户第三方登陆模型
 * @author xlp
 */
class userBindModel extends model
{

    function __construct()
    {
        parent::__construct();
        $this->dbTable = 'user_bind';
    }

    //添加新帐号
    public function addBindInfo($uid = 0, $userInfo = array())
    {
        $err = '';
        if ($userInfo) {
            if ($uid) {
                $userInfo['uid'] = $uid;
            }
            if (!$userInfo['uid']) {
                $err = 'uid 不能为空';
            }
            if (!$err) {
                $objData = array();
                foreach ($userInfo['keyids'] as $key => $val) {
                    $isHaveBind = $this->getUidByKeyid($key, $val);
                    //已存在的绑定，修正绑定关系
                    if ($isHaveBind) {
                        $this->update(array('uid' => $userInfo['uid'], 'aid' => $userInfo['aid'], 'village_id' => $userInfo['village_id']), array('type' => $key, 'keyid' => $val));
                    } elseif ($val) {
                        $objData[] = array(
                            'uid' => $userInfo['uid'],
                            'aid' => $userInfo['aid'],
                            'village_id' => 0,
                            'type' => 'sh',
                            'keyid' => $val,
                            'refresh_time' => TIME
                        );
                    }
                }
            } else {
                return $this->callbackMsg($err);
            }
            if ($objData) {
                $this->insertAll($objData);
            }
            return $this->callbackMsg('ok', null, 1);
        }
        return $this->callbackMsg('没有获取到用户信息');
    }

    public function getUserInfoByKeyid($mod, $key)
    {
        return $this->where(array('type' => $mod, 'keyid' => $key))->find();
    }

    public function getUidByKeyid($mod, $key)
    {
        return $this->where(array('type' => $mod, 'keyid' => $key))->getField('uid');
    }

    public function getUidByKeyidAndVid($mod, $key, $vid)
    {
        return $this->where(array('type' => $mod, 'keyid' => $key, 'village_id' => $vid))->getField('uid');
    }

    public function getUserInfoByUid($mod, $uid)
    {
        return $this->where(array('uid' => $uid, 'type' => $mod))->find();
    }

    public function getKeyidByUid($mod, $uid)
    {
        return $this->where(array('uid' => $uid, 'type' => $mod))->getField('keyid');
    }

    public function getKeyidByVid($mod, $uid, $vid)
    {
        return $this->where(array('uid' => $uid, 'type' => $mod, 'village_id' => $vid))->getField('keyid');
    }

    //删除帐号信息
    public function deleteBindInfo($mod, $uid)
    {
        return $this->delete(array('uid' => $uid, 'type' => $mod));
    }

}
