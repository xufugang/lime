<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/**
 * Description of CommonAction
 * 后台控制器父类
 * @author xlp
 */
//载入后台专用函数库
loadAppFile('content.fun');

class commonAction extends action
{

    //初始化全局信息
    function __construct()
    {
        parent::__construct();
        T('user/admin');
        //全局获取登录用户数据
        admin::getLoginUser();
        if (!isAjax()) {
            $this->getSysMenu(); //获取系统菜单
        }
        $this->setting = C('setting');
        if ($this->_getid('facebox') == 1) {
            $_SERVER['HTTP_X_REQUESTED_WITH'] = null;
        }
        //全局获取当前页面URL
        $this->assign(array(
            'adminInfo' => &admin::$adminInfo,
            'setting' => &$this->setting,
            'version' => C('system', 'admin_version'),
            'refer' => $this->_get('refer', urlencode(URL))));
    }

    /*
     * 生成系统菜单
     */

    private function getSysMenu()
    {
        if ($this->_checkLogin(true)) {
            $sysMenu = C('menu_group_1');
            if (!$sysMenu) {
                showError('缺少用户组菜单配置');
            }

            $this->assign(array('sysMenu' => $sysMenu));
            return true;
        }
        return false;
    }

    /*
     * 检查登录，用于必须登录的模块
     * @param 无
     * @return
     */

    protected function _checkLogin($returnBool = false)
    {
        if (admin::$adminInfo['id']) {
            return true;
        } else {
            if ($returnBool) {
                return false;
            } elseif (isAjax()) {
                return $this->JsonReturn('必须登录后才能进行此操作');
            } else {
                jumpTo(U('login/index'));
            }
        }
    }

    /*
     * 全局检测是否有权限是否过期
     */
    protected function _checkValidity()
    {
        $group = admin::$adminInfo['group_id'];
        if ($group == 2) {
            jumpTo(U('login/index'));
        }
        $validity_time = admin::$adminInfo['validity_time'];
        if ($validity_time > 0 && time() > $validity_time) {
            jumpTo(U('login/index'));
        }
    }

    /*
     * 检查是否管理员
     * @param 无
     * @return bool
     */

    protected function _checkIsAdmin()
    {
        if (admin::$adminInfo['isAdmin']) {
            return true;
        } else {
            return false;
        }
    }

    //检查操作权限
    protected function _checkAuth($arr, $returnBool = false)
    {
        if (in_array(admin::$adminInfo['groupid'], $arr)) {//合法操作
            return true;
        } else {
            return $returnBool ? false : showError('抱歉，您没有操作权限');
        }
    }

    protected function _getAdminName($id)
    {
        static $_admin = null;
        if (!$id) {
            return '';
        }
        if (!isset($_admin[$id])) {
            $_admin[$id] = D('admin')->where(array('id' => $id))->getField('username');
        }
        return $_admin[$id];
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

    //数组变为json串数据入库
    protected function arrayToJsonStr($arr)
    {
        return $arr && is_array($arr) ? json_encode($arr) : $arr;
    }

    //json串数据转换为数组输出
    protected function jsonStrToArray($json)
    {
        return $json ? json_decode($json, true) : array();
    }

    /*
     * 遍历数组取出符合条件的名称
     */

    protected function _getTipsGroupList($arr, $list)
    {
        $objData = array();
        if ($list) {
            $list = explode(',', trim($list, ','));
            foreach ($list as $val) {
                isset($arr[$val]) && $objData[] = $arr[$val];
            }
        }
        return $objData;
    }

    //设置系统配置信息
    protected function _setSysSetting($field, $arr = array())
    {
        $list = $this->_getSysDefaultSetting();
        $isHave = M('setting')->find();
        if ($isHave) {
            $isHave[$field] = isHave($isHave[$field]) ? json_decode($isHave[$field], true) : $list[$field];
            $isHave[$field] = array_merge($isHave[$field], $arr);
            return M('setting')->update(array($field => json_encode($isHave[$field])), '1=1');
        } else {
            $list = array_merge($list, array($field => $arr));
            foreach ($list as $k => $v) {
                $list[$k] = json_encode($v);
            }
            return M('setting')->insert($list);
        }
    }

    //读取用户配置信息
    protected function _getSysSetting($field = '*')
    {
        $setting = M('setting')->field($field)->find();
        if ($field == '*') {
            return $setting;
        } else {
            if ($setting) {
                $setting = json_decode($setting[$field], true);
            }
            $list = $this->_getSysDefaultSetting($field);
            foreach ($list as $key => $val) {
                if (!isset($setting[$key])) {
                    $setting[$key] = $val;
                }
            }
            return $setting;
        }
    }

    //默认设置
    protected function _getSysDefaultSetting($field = '')
    {
        $setting = $this->setting['sys_global_setting'];
        return $field && isset($setting[$field]) ? $setting[$field] : $setting;
    }

    protected function _getSysSettingInfo()
    {
        $sysSetting = F('weixin/sys_setting');
        if (!$sysSetting) {
            $sysSetting = $this->_getSysSetting('setting_info');
            //写入缓存
            F('weixin/sys_setting', $sysSetting);
        }
        return $sysSetting;
    }

    /*
     * 检查是否需要同步图片到云存储
     */

    protected function _sendImageToYunServer($uploadList)
    {
        $upConf = C('upload');
        if ($uploadList && $upConf['cdn'] == 'yun') {//开启云存储
            T('image/upyun');
            foreach ($uploadList as $key => $val) {
                try {
                    $upyun = new UpYun($upConf['yun']['bucket'], $upConf['yun']['user'], $upConf['yun']['pwd']);
                    $uploadList[$key]['savepath'] = $upConf['yun']['dir'] . ltrim($val['savepath'], $upConf['dir']);
//                z($uploadList[$key]['savepath']);
                    $upyun->writeFile('/' . $uploadList[$key]['savepath'], file_get_contents(ROOT . $val['savepath']), true); // 上传图片，自动创建目录
                } catch (Exception $e) {
//            echo $e->getCode().$e->getMessage();
                }
            }
        }
        return $uploadList;
    }

}
