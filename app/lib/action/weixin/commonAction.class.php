<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/**
 * Description of commonAction
 * 微信全局调用父类
 * @author xlp
 */
class commonAction extends action
{

    public $setting = null;
    public $vid;

    //初始化全局信息
    function __construct()
    {
        parent::__construct();
        $this->setting = C('setting');
        T('user/user');
        user::getLoginUser();
        if (!user::$userInfo['uid']) {
            $this->userLogin();
        }
        $this->assign(array('userInfo' => user::$userInfo, 'setting' => $this->setting));
    }

    protected function userLogin()
    {
        //如果没有登录，首先完成社荟帐号注册检查
        if (!$this->_checkLogin(true)) {
            if (isAjax()) {
                $this->JsonReturn('帐号登录信息丢失');
            }
            mySession('jump_info', array('url' => URL));
            $this->_getAuthUrl(U('weixin/callback/login'), 'all');
        }
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
            return $returnBool ? false : $this->_showSharePage();
        }
    }

    //检查访问来源
    protected function _checkUserAgent()
    {
        if (getUserAgent() == 'unknown') {
            if (isAjax()) {
                return $this->JsonReturn('403 Forbidden');
            } else {
                $this->_showSharePage();
            }
        }
    }

    protected function _showSharePage()
    {
        showError('请在微信端打开');
    }

    //检查用户状态
    protected function _checkUserStatus($returnBool = true)
    {
        if ($this->_checkLogin(true) && user::$userInfo['status'] == 1) {
            return true;
        } else {
            return $returnBool ? false : showError('抱歉，您的帐号状态暂时无法进行此操作');
        }
    }

    /*
     * 检查HASH
     */

    protected function checkHash()
    {
        if (!formCheck()) {
            $this->JsonReturn('数据校验失败');
        } else {
            return true;
        }
    }

    protected function _getVillageInfo($vid)
    {
        $rs = M('village')->where(array('vid' => $vid))->find();
        return $rs;
    }

    //处理搜索关键字
    protected function safeSearch($str)
    {
        return str_replace(array('_', '%', "'", '"'), array('\_', '\%', '', ''), trim($str)); // 把 '_','%'过滤掉;
    }

    //接受无过滤字段内容
    protected function _postContent($field = '', $val = '', $fun = 'stripslashes')
    {
        return htmlspecialchars_decode($this->_post($field, $val, $fun), ENT_QUOTES);
    }

    //清理过滤
    protected function _clearFilter($content = '', $val = '', $fun = 'stripslashes')
    {
        return $content ? htmlspecialchars_decode($fun($content), ENT_QUOTES) : $val;
    }

    //添加上传到云
    protected function _sendImageToYunServer($uploadList)
    {
        $upConf = C('upload');

        if ($uploadList && $upConf['cdn']) {//开启云存储
            $cdn = $upConf['cdn'];
            switch ($upConf['cdn']) {
                case 'yun':
                    T('image/upyun');
                    $upyun = new UpYun($upConf[$cdn]['bucket'], $upConf[$cdn]['user'], $upConf[$cdn]['pwd']);
                    foreach ($uploadList as $key => $val) {
                        $uploadList[$key]['savepath'] = $upConf[$cdn]['dir'] . ltrim($val['savepath'], $upConf['dir']);
                        is_file($val['savepath']) && $upyun->writeFile('/' . $uploadList[$key]['savepath'], file_get_contents(ROOT . $val['savepath']), true); // 上传图片，自动创建目录
                    }
                    break;
                case 'qiniu':
                    T('image/qiniu');
                    $qiniu = new Qiniu($upConf[$cdn]['bucket'], $upConf[$cdn]['user'], $upConf[$cdn]['pwd']);
                    foreach ($uploadList as $key => $val) {
                        $uploadList[$key]['savepath'] = $upConf[$cdn]['dir'] . ltrim($val['savepath'], $upConf['dir']);
                        is_file($val['savepath']) && $qiniu->writeFile($uploadList[$key]['savepath'], $val['savepath']); // 上传图片
                    }
                    break;
                default :
            }
        }
        return $uploadList;
    }

    /*
     * 生成跳转授权链接
     * $url 回调地址
     * $mod all、base  all:授权获取头像昵称，base:静默授权
     */

    protected function _getAuthUrl($url = '', $mod = 'all')
    {
        $checkStr = 'nonce=' . TIME . '&sign=' . md5(TIME . VCODE . '_' . getUserIp());
        if (strExists($url, '?')) {
            $callBackUrl = $url . '&' . $checkStr;
        } else {
            $callBackUrl = $url . '?' . $checkStr;
        }
        saveLog('sys/error', $this->setting['shehui_auth_api_url'] . '?mod=' . $mod . '&redirect_uri=' . base64_encode($callBackUrl));
        jumpTo($this->setting['shehui_auth_api_url'] . '?mod=' . $mod . '&redirect_uri=' . base64_encode($callBackUrl));
    }

    /*
     * 检查小区是否已授权
     */

    protected function _checkVliiageIsAuth($vid)
    {
        $isAuth = M('weixin_house')->field('id')->where(array('village_id' => $vid))->find() ? true : false;
        if (!$isAuth) {
            return false;
        }
        return M('village')->field('vid,title,wyid')->where(array('vid' => $vid))->find();
    }

    protected function _checkUserBindVillage($vid)
    {
        $checkOpenid = D('userBind')->getKeyidByVid('wx', user::$userInfo['uid'], $vid);
        if ($checkOpenid) {
            user::$userInfo['village_id'] = $vid;
            user::$userInfo['openid'] = $checkOpenid;
            D('member')->setUserLogin(array('uid' => user::$userInfo['uid'], 'last_login_time' => TIME, 'village_id' => $vid), 60 * 60 * 24 * 7);
            $this->assign(array('userInfo' => user::$userInfo));
            return true;
        } else {
            return false;
        }
    }

}
