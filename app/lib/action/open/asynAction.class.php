<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/**
 * Description of asynAction
 * 异步操作
 * @TIME 2015-04-17
 * @author xlp
 */
ignore_user_abort(TRUE); //如果客户端断开连接，不会引起脚本abort.
set_time_limit(60); //取消脚本执行延时上限
setDebug();

class asynAction extends action {

    private $mod = null;
    private $arrData = array(); //获取的参数，全部转发给内部处理类

    function __construct() {
        parent::__construct();
        $this->mod = $this->_get('mod', '');
        if (!$this->checkSignature()) {
            $this->saveRunLog('数据校验错误');
        }
        $this->arrData = array('post' => $_POST);
    }

    /*
     * 默认控制器
     */

    public function index() {
        if (!$this->mod || !checkPath($this->mod)) {
            $this->saveRunLog('错误的模块:' . $this->mod);
        }
        $modName = $this->mod . 'Asyn';
        T('asyn/' . $modName);
        if (!class_exists($modName, false)) {
            $this->saveRunLog('错误的模块:' . $this->mod);
        }
        $obj = new $modName();
        $res = $obj->init($this->arrData);
//        $res = $modName::init($this->arrData);
        if ($res['log']) {
            $this->saveRunLog($res['log'], $res['status']);
        } else {
            $this->saveRunLog($res['info'], $res['status']);
        }
        unset($res['log']);
        echo json_encode($res);
    }

    public function _empty() {
        $this->saveRunLog('错误的调用方法');
    }

    private function getAuthData() {
        return var_export($this->arrData, true);
    }

    private function checkSignature() {
        $signature = isset($_GET['signature']) ? $_GET['signature'] : '';
        $timestamp = isset($_GET['timestamp']) ? $_GET['timestamp'] : '';
        $nonce = isset($_GET['nonce']) ? $_GET['nonce'] : '';
        $tmpArr = array(VCODE, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = sha1(implode($tmpArr));
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    private function saveRunLog($msg, $status = 0) {
//        saveLog('asyn/' . $this->mod, $msg);
        V('db/mongo');
        $db = mongoApi::getInstance();
        $db->table('asyncLog')->insert(array(
            'mod' => $this->mod,
            'url' => URL,
            'refer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
            'status' => $status,
            'msg' => var_export($msg, true),
            'data' => $this->getAuthData(), //操作的数据
            'infotime' => TIME
        ));
        return true;
    }

}
