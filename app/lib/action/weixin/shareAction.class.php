<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

class shareAction extends action {

    private $refer = array();

    function __construct() {
        $this->refer = array('tengo.com', 'hzforall.com');
        parent::__construct();
    }

    public function index() {
        $id = $this->getid('id', 0);
        if (!$id) {
            showError('参数丢失');
        }
        $rs = M('share')->where(array('id' => $id))->find();
        if (!$rs) {
            showError('记录不存在');
        }
        M('share')->where(array('id' => $id))->setInc('hits_counts', 1);
        if (user::$userInfo['uid'] && user::$userInfo['village_id'] == $rs['village_id']) {
            jumpTo($rs['url']);
        } else {
            jumpTo($rs['hn_url']);
        }
    }

    public function config() {
        $url = $this->_get('url');
        if (!$this->_checkRefer($url)) {
            $this->JsonReturn('访问错误');
        }
        $debug = $this->_getid('debug', 0);
        $callback = $this->_get('callback');
        if (!$url) {
            $url = $_SERVER['HTTP_REFERER'];
        } else {
            $url = urldecode($url);
        }
        T('weixin/weixin.jsapi');
        $conf = C('weixin');
        $signPackage = new weixinJsApi(array('appid' => $conf['appid'], 'appsecret' => $conf['app_secret']));
        $json = $signPackage->weixinShare($url, $debug);
        if ($callback) {
            echo $callback . '(' . json_encode($json) . ')';
        } else {
            $this->JsonReturn('ok', $json, 1);
        }
    }

    /*
     * 更新分享数据
     */

    public function save() {
        $type = $this->_post('type');
        $id = $this->_postid('id');
        if ($id) {
            $rs = M('share')->where(array('id' => $id))->find();
            if ($rs) {
                $up = array();
                if ($type == 1) {
                    $up['py_counts'] = $rs['py_counts'] + 1;
                } elseif ($type == 2) {
                    $up['pyq_counts'] = $rs['pyq_counts'] + 1;
                } elseif ($type == 3) {
                    $up['other_counts'] = $rs['other_counts'] + 1;
                }
                if ($up) {
                    M('share')->update($up, array('id' => $id));
                    $this->JsonReturn('ok', null, 1);
                }
            }
            $this->JsonReturn('不存在该链接数据', null, 0);
        } else {
            $this->JsonReturn('参数错误', null, 0);
        }
    }

    private function _checkRefer($url = '') {
        if (!$url && !isset($_SERVER['HTTP_REFERER'])) {
            return false;
        }
        if (!$url) {
            $url = $_SERVER['HTTP_REFERER'];
        }
        foreach ($this->refer as $val) {
            if (strExists($url, $val)) {
                return true;
            }
        }
        return false;
    }

}
