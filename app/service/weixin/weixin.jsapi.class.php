<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
T('weixin/weixin.base');

/**
 * Description of weixinJsApi
 * 微信JSAPI接口处理类
 * @author xlp
 */
class weixinJsApi extends weixinBase {

    const API_URL_PREFIX = 'https://api.weixin.qq.com/cgi-bin';
    const TICKET_URL = '/ticket/getticket?type=jsapi&';

    private $ticketPre = 'token/weixin/jsticket_';

    public function __construct($options = array()) {
        parent::__construct($options);
    }

    /*
     * jsAPI接口，获取签名参数
     */

    public function getSignPackage() {
        $jsapiTicket = $this->getJsApiTicket();
        $nonceStr = getRandInt();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=" . TIME . "&url=" . URL;
        $signPackage = array(
            'appId' => self::$appId,
            'nonceStr' => $nonceStr,
            'timestamp' => TIME,
            'url' => URL,
            'signature' => sha1($string),
            'rawString' => $string
        );
        return $signPackage;
    }

    public function weixinShare($url = '', $debug = false) {
        $jsapiTicket = $this->getJsApiTicket();
        $nonceStr = getRandInt();
        $jsApiList = array(
            'checkJsApi', 'onMenuShareTimeline', 'onMenuShareAppMessage',
            'onMenuShareQQ', 'onMenuShareWeibo', 'hideMenuItems', 'showMenuItems',
            'hideAllNonBaseMenuItem', 'showAllNonBaseMenuItem', 'translateVoice',
            'startRecord', 'stopRecord', 'onRecordEnd', 'playVoice', 'pauseVoice',
            'stopVoice', 'uploadVoice', 'downloadVoice', 'chooseImage', 'previewImage',
            'uploadImage', 'downloadImage', 'getNetworkType', 'openLocation',
            'getLocation', 'hideOptionMenu', 'showOptionMenu', 'closeWindow',
            'scanQRCode', 'chooseWXPay', 'openProductSpecificView', 'addCard',
            'chooseCard', 'openCard');
        $arr = array(
            'debug' => $debug,
            'appId' => self::$appId,
            'timestamp' => TIME,
            'nonceStr' => $nonceStr,
            'signature' => sha1("jsapi_ticket={$jsapiTicket}&noncestr={$nonceStr}&timestamp=" . TIME . "&url={$url}"),
            'jsApiList' => $jsApiList,
        );
        return $arr;
    }

    private function getJsApiTicket() {
        $this->jsTicket = S($this->ticketPre . self::$appId);
        if ($this->jsTicket) {
            return $this->jsTicket;
        }
        if (!self::$token && !parent::getAccessToken()) {
            return false;
        }
        $result = getHttp(self::API_URL_PREFIX . self::TICKET_URL . 'access_token=' . self::$token);
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (isset($json['errcode']) && $json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                self::$errAppCode = $json['errcode'];
                if (parent::checkAccessToken()) {
                    return $this->getJsApiTicket();
                }
                return false;
            }
            $this->jsTicket = $json['ticket'];
            S($this->ticketPre . self::$appId, $json['ticket'], $json['expires_in'] - 100);
        }
        return $this->ticketPre;
    }

}
