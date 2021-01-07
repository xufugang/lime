<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/**
 * Description of cmuAction
 * 社荟系统创建用户帐号
 * @author xlp
 */
class callbackAction extends action
{

    function __construct()
    {
        parent::__construct();
    }

    /*
     * 系统帐号授权授权回调
     */

    public function login()
    {
        $nonce = $this->_getid('nonce', 0);
        $sign = $this->_get('sign');
        if (md5($nonce . VCODE . '_' . getUserIp()) != $sign || (TIME - $nonce) > 300) {
            showError('回调授权失败，请返回上一页');
        }
        $avatar = $this->_get('headimgurl', '');
        $openid = $this->_get('openid');
        $unionid = $this->_get('unionid');
        if (!$openid) {
            showError('授权有误');
        }
        $jumpInfo = mySession('jump_info');
        if (!$jumpInfo) {
            mySession('jump_info', array('url' => 'http://192.168.17.29/shqwy/weixin/register/index'));
            $jumpInfo = mySession('jump_info');
            //showError('返回地址有误');
        }
        saveLog('sys/mysession',$jumpInfo);
        $unionInfo = D('userBind')->getUidByKeyid('sh', $unionid);
        $uid = D('userBind')->getUidByKeyid('sh', $openid);
        //$user = M()->where(array(''))->find();
        if (!$uid) {
            //纯新注册
            if (!$unionInfo) {
                //先注册社荟帐号
                $objData = array(
                    'openid' => $openid,
                    'headimg' => $avatar
                );
                //添加用户信息
                $uid = D('member')->addUser($objData, $openid);
                if ($uid['data']['uid']) {
                    $data = array(
                        'uid' => $uid['data']['uid'],
                        'aid' => 0,
                        'village_id' => 0,
                        'keyids' => array('sh' => $unionid),
                        'refresh_time' => 0
                    );
                    D('userBind')->addBindInfo($uid, $data);
                }
                //加上sh openid
            } else {
                $data = array(
                    'uid' => $unionInfo,
                    'aid' => 0,
                    'village_id' => 0,
                    'keyids' => array('sh' => $openid),
                    'refresh_time' => 0
                );
                D('userBind')->addBindInfo($unionInfo, $data);
            }
        } else {
            //要迁移数据
            /*  if ($unionInfo) {
                  if ($unionInfo != $uid) {
                      M("user")->update(array('status' => 0), array('uid' => $unionInfo));
                      M("user_bind")->update(array('uid' => $uid), array("type" => 'sh', "uid" => $unionInfo));
                      asynHttp('userReset', array('olduid' => $unionInfo, 'newuid' => $uid));
                      D('member')->setUserLogin(array('uid' => $uid, 'last_login_time' => TIME, 'village_id' => 8064), 0);
                  }
                  //加上opwx openid
              } else {
                  $data = array(
                      'uid' => $uid,
                      'aid' => 0,
                      'village_id' => 0,
                      'keyids' => array('sh' => $unionid),
                      'refresh_time' => 0
                  );
                  D('userBind')->addBindInfo($uid, $data);
              }*/
            //D('member')->update(array('username' => $nickName), array('uid' => $uid));
            //asynHttp('avatar', array('uid' => $uid, 'imgUrl' => $avatar));
        }
        //完成社荟帐号登录，注意，此处登录的village_id为0；表示不在任何小区
        $this->goback($uid, 0, $jumpInfo);
    }

    /*
     * 蜂鸟系统获取用户信息
     */

    public function wy()
    {
        $wyId = $this->_getid('wyid');
        if (!$wyId) {
            showError('参数缺失');
        }
        jumpTo('https://wx.cmu.qq.com/cp/' . $wyId . '/jump.do?actionCode=tg10y25r');
    }

    /*
     * 蜂鸟系统获取用户信息
     */

    public function index()
    {
        $openid = $this->_get('openId');
        $wyId = $this->_getid('wyId');
        if (!$openid) {
            $this->wy();
        }
        $jumpInfo = mySession('jump_info');
        if (!$jumpInfo) {
            showError('返回地址有误');
        }
        T('user/user');
        user::getLoginUser();
        if (!$this->_checkLogin(true)) {
            showError('访问不正确');
        }
        //先检查对应的小区及openid帐号是否存在
        $villageInfo = M('village')->field('vid,title')->where(array('wyid' => $wyId))->find();
        if (!$villageInfo) {
            showError('小区不存在');
        }
        $checkOpenid = D('userBind')->getKeyidByVid('wx', user::$userInfo['uid'], $villageInfo['vid']);
        //如果用户已存在，则简化操作，直接完成登录
        if ($checkOpenid) {
            if ($checkOpenid == $openid) {
//                showError('用户信息存在异常，请核查');
                $this->goback(user::$userInfo['uid'], $villageInfo['vid'], $jumpInfo);
            } else {
                D('userBind')->delete(array('uid' => user::$userInfo['uid'], 'village_id' => $villageInfo['vid'], 'type' => 'wx'));
            }
        }
        //如果用户不存在，首先核查用户身份，再注册
        T('cmu/weixin');
        $api = new cmuApi();
        cmuApi::$isDebug = false;
        $res = $api->getUser($openid, $wyId);
        $userInfo = array();
        if (isHave($res[0])) {
            $arr = array('name' => 'user_name', 'mobile' => 'phone', 'sex' => 'sex');
            foreach ($arr as $key => $val) {
                if (isHave($res[0][$key])) {
                    $userInfo[$val] = $res[0][$key];
                }
            }
        }
        if (!$userInfo) {
            showError('用户信息核查失败');
        }
        //将用户信息添加到userbind表
        $data = array(
            'uid' => user::$userInfo['uid'],
            'aid' => 0,
            'village_id' => $villageInfo['vid'],
            'keyids' => array('wx' => $openid),
            'refresh_time' => 0,
            'avatar' => ''
        );
        D('userBind')->addBindInfo(user::$userInfo['uid'], $data);
        //更新用户信息
        if ($userInfo) {
            D('member')->update($userInfo, array('uid' => user::$userInfo['uid']));
        }
        $this->goback(user::$userInfo['uid'], $villageInfo['vid'], $jumpInfo);
    }

    private function goback($uid, $villageId, $jumpInfo)
    {
        D('member')->setUserLogin(array('uid' => $uid, 'last_login_time' => TIME, 'village_id' => $villageId, 'resident_code' => 0), 60 * 60 * 24 * 7);
        mySession('jump_info', null);
        jumpTo($jumpInfo['url']);
    }

    /*
     * 检查登录，用于必须登录的模块
     * @param 无
     * @return
     */

    protected function _checkLogin($returnBool = false)
    {
        if (user::$userInfo['uid']) {
            return true;
        } else {
            return $returnBool ? false : showError('用户登录信息获取失败');
        }
    }

    /*
     * 退出登录
     */

    function logout()
    {
        myCookie('test', 1);
        $_COOKIE['test'] = 1;
        T('user/user');
        user::getLoginUser();
        user::setUserLoginOut(user::$userInfo['uid']);
        exit("退出成功");
        //jumpTo(U('weixin/login/index'));
    }

}
