<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of imageAsyn
 * 异步操作：抓取任务-保存图片
 * @author xlp
 * 返回结构体
 * array('status'=>1,'log'=>'','info'=>'');
 */
class imageAsyn {

    static public $objData = array();

    static public function init(&$objData) {
        self::$objData = $objData['post'];
        if (!isHave(self::$objData['url']) || !isHave(self::$objData['name'])) {
            self::msg('参数不完整');
        }
        $refer = isHave(self::$objData['refer']) ? self::$objData['refer'] : '';
        $imageData = getHttp(self::$objData['url'], array(), $refer);
        if ($imageData) {
            $saveSataus = saveFile(self::$objData['name'], $imageData);
            if ($saveSataus['status']) {
                return self::msg('ok', 1);
            }
        }
        saveLog('asyn/image', array('objData' => self::$objData));
        return self::msg('error', 0);
    }

    static public function msg($info = '', $status = 0) {
        if (isHave(self::$objData['uid'])) {
            $sendData = array(
                'status' => $status, //0 1 2
                'info' => $info
            );
            sendDataToSocket('msg', self::$objData['uid'], $sendData);
        }
        return array('status' => $status ? 1 : 0, 'log' => $info, 'info' => $info);
    }

}
