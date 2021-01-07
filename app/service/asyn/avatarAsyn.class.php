<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of avatarAsyn
 * 异步操作：用户头像保存
 * @author xlp
 * 返回结构体
 * array('status'=>1,'log'=>'','info'=>'');
 */
class avatarAsyn {

    static public function init(&$objData) {
        if (!isHave($objData['post']['uid']) || (!isHave($objData['post']['imgUrl']) && !isHave($objData['post']['imgData']))) {
            return array('status' => 0, 'log' => '参数不完整', 'info' => '参数不完整');
        }
        //读取图像数据
        $content = isHave($objData['post']['imgData']) ? base64_decode($objData['post']['imgData']) : getHttp($objData['post']['imgUrl'], array(), array(
                    'Referer:https://mp.weixin.qq.com/wiki/doc/api/index.html',
                    'User-Agent:Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.87 Safari/537.36'
        ));
        $res = false;
        $info = '';
        //保存头像
        if ($content && setAvatar($objData['post']['uid'], $content, true)) {
            //更新数据库头像状态
            D('member')->update(array('avatar_status' => 1), array('uid' => $objData['post']['uid']));
//            !$res && $info = '状态未变更';
            $res = true;
        } else {
            $info = '头像数据读取失败';
        }
        //返回处理结果
        if ($res) {
            return array('status' => 1, 'log' => '', 'info' => 'ok');
        } else {
            return array('status' => 0, 'log' => $info, 'info' => $info);
        }
    }

}
