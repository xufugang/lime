<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of weixinBase
 * 微信API接口基础继承类
 * @author xlp
 */
class weixinBase {

    public static $appId = 'wxf37831b50cb3ad3c';
    public static $authType = 0;
    public static $appSecret = '54135102feaf29ef7a40b763f1146a67';
    public static $token = null;
    public static $errAppCode = 0;
    public static $errorMsg = 'no access';
    public static $accessTokenPre = 'token/weixin/access_token_';

    public function __construct($options = array()) {
        if ($options || !self::$appId || !self::$appSecret) {
            self::init($options);
        }
    }

    static public function init($options) {
        self::$appId = isset($options['appid']) ? $options['appid'] : '';
        self::$appSecret = isset($options['appsecret']) ? $options['appsecret'] : '';
        $api = isset($options['api']) ? $options['api'] : C('setting', 'accessToken_api');
        if ($api) {
            if (isHave($api['url']) && isHave($api['app_id']) && isHave($api['app_secret'])) {
                //走接口获取accessToken
                $data = getHttp($api['url'], array('app_id' => $api['app_id'], 'app_secret' => $api['app_secret']));
                $json = json_decode($data, true);
                if (!$json || $json['status'] != 1 || !isset($json['data']['access_token'])) {
                    saveLog('weixin/get_api_accesstoken', array($api, $data));
                    showError('抱歉，接口通讯失败');
                }
                self::$appId = $api['app_id'];
                self::$appSecret = $api['app_secret'];
                self::$token = $json['data']['access_token'];
            }
        }
        if (!self::$appId || !self::$appSecret) {
            $conf = C('weixin');
            if (isHave($conf['appid']) && isHave($conf['app_secret'])) {
                self::$appId = $conf['appid'];
                self::$appSecret = $conf['app_secret'];
            } else {
                showError('api Error:该微信号未授权');
            }
        }
    }

    static public function getAuthToken($isMust = false) {
        T('weixin/weixin.open');
        $openApi = new weixinOpenApi();
        $result = $openApi->get_authorizer_token(self::$appId, $isMust); //强制刷新accessToken
        if (!$result) {
            $err = $openApi->getErrorMsg();
            showError('授权获取错误：get_authorizer_token' . '[' . $err['errcode'] . ']' . $err['errmsg']);
        }
        self::$token = $result;
        return self::$token;
    }

    static public function getErrorMsg() {
        return self::$errorMsg;
    }

    /**
     * 删除验证数据
     * @param string $appid
     */
    static public function resetAuth() {
        self::$token = '';
        S(self::$accessTokenPre . self::$appId, null);
        //清除授权模式的accesstoken
        S('token/' . C('weixin_open', 'appId') . '/' . self::$appId . '/authorization_info', null);
        self::getAccessToken();
        return true;
    }

    /*
     * 检查是否需要重新获取accesstoken
     * 并记下日志
     */

    static public function checkAccessToken() {
        if (self::$errAppCode == 40001 || self::$errAppCode == 40014) {
            self::resetAuth();
            return true;
        }
        return false;
    }

    static public function getAccessToken() {
        if (self::$token) {
            return self::$token;
        }
        if (self::$authType == 1) {
            return self::getAuthToken(true);
        }
        self::$token = S(self::$accessTokenPre . self::$appId);
        if (!self::$token) {
            $res = getHttp('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . self::$appId . '&secret=' . self::$appSecret);
            $result = json_decode($res, true);
            if ($result && !isset($result['errcode'])) {
                S(self::$accessTokenPre . self::$appId, $result['access_token'], $result['expires_in'] - 100);
                self::$token = $result['access_token'];
            } else {
                self::$errAppCode = $result['errcode'];
                self::$errorMsg = 'getAccessToken:' . $result['errcode'] . '-' . $result['errmsg'];
                self::checkAccessToken();
                return false;
            }
        }
        return self::$token;
    }

    /**
     * 微信api不支持中文转义的json结构
     * @param array $arr
     */
    static public function json_encode($arr) {
        $parts = array();
        $is_list = false;
        $keys = array_keys($arr);
        $max_length = count($arr) - 1;
        if (($keys [0] === 0) && ($keys [$max_length] === $max_length )) {
            $is_list = true;
            for ($i = 0; $i < count($keys); $i++) {
                if ($i != $keys [$i]) {
                    $is_list = false;
                    break;
                }
            }
        }
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                if ($is_list) {
                    $parts [] = self::json_encode($value);
                } else {
                    $parts [] = '"' . $key . '":' . self::json_encode($value);
                }
            } else {
                $str = '';
                if (!$is_list) {
                    $str = '"' . $key . '":';
                }
                if (is_numeric($value) && $value < 2000000000) {
                    $str .= $value;
                } elseif ($value === false) {
                    $str .= 'false';
                } elseif ($value === true) {
                    $str .= 'true';
                } else {
                    $str .= '"' . addslashes($value) . '"';
                }
                $parts [] = $str;
            }
        }
        $json = implode(',', $parts);
        if ($is_list) {
            return '[' . $json . ']';
        }
        return '{' . $json . '}';
    }

}
