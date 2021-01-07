<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
T('weixin/weixin.base');

class weixinMsgApi extends weixinBase {

    public $errCode = 0;
    public $errmsg = '';

    const API_URL_PREFIX = 'https://api.weixin.qq.com/cgi-bin';

    public function __construct($options = array()) {
        parent::__construct($options);
    }

    /*
     * 获取永久素材
     */

    public function getMaterialMedid($media_id) {
        if (!self::$token && !parent::getAccessToken()) {
            return false;
        }
        $data = array(
            'media_id' => $media_id
        );
        $res = $this->downloadWeixinFile(self::API_URL_PREFIX . '/material/get_material?access_token=' . self::$token, parent::json_encode($data));
        $fileExt = '';
        $size = isset($res['header']['download_content_length']) ? $res['header']['download_content_length'] : strlen($res['body']);
        if (isset($res['header']['content_type'])) {
            $extmatches = explode('/', $res['header']['content_type']);
            if (strExists($extmatches[1], ';')) {
                $extmatches = explode(';', $extmatches[1]);
                $fileExt = $extmatches[0];
            } else {
                $fileExt = $extmatches[1];
            }
        } else {
            $res['header']['content_type'] = '';
        }
        $contentType = $res['header']['content_type'];
        switch ($fileExt) {
            case 'x-speex-with-header-byte':
                $fileExt = 'speex';
                break;
            case 'mpeg4':
                $fileExt = 'mp4';
                break;
            case 'amr':
                $fileExt = 'amr';
                break;
            case 'jpeg':case 'pjpeg':
                $fileExt = 'jpg';
                break;
            case 'gif':
                $fileExt = 'gif';
                break;
            case 'x-png':case 'png':
                $fileExt = 'png';
                break;
            case 'json':case 'plain'://接口失败
                $json = json_decode($res['body'], true);
                if (!$json || (isset($json['errcode']) && $json['errcode'])) {
                    $this->setErrorInfo($json);
                    if (parent::checkAccessToken()) {
                        return $this->getMaterialMedid($media_id);
                    }
                    $info = $json['errcode'] == 40007 ? '文件已过期，无法下载' : self::$errorMsg;
                } else {
                    $info = '未知错误';
                }
                return array('status' => 0, 'info' => $info, 'msg' => $res['body']);
        }
        if ($fileExt) {
            return array('status' => 1, 'content_type' => $contentType, 'type' => $fileExt, 'data' => $res['body'], 'size' => $size);
        } else {
            return array('status' => 0, 'info' => '未知文件类型，无法保存', 'msg' => '');
        }
    }

    /**
     * 根据媒体文件ID获取媒体文件(临时素材)
     * @param string $media_id 媒体文件id
     * @return raw data
     */
    public function getMedia($media_id) {
        if (!$media_id || (!self::$token && !parent::getAccessToken())) {
            return false;
        }
        $res = $this->downloadWeixinFile(self::API_URL_PREFIX . '/media/get?access_token=' . self::$token . '&media_id=' . $media_id);
        $size = isset($res['header']['download_content_length']) ? $res['header']['download_content_length'] : strlen($res['body']);
        $fileExt = '';
        if (isset($res['header']['content_type'])) {
            $extmatches = explode('/', $res['header']['content_type']);
            if (strExists($extmatches[1], ';')) {
                $extmatches = explode(';', $extmatches[1]);
                $fileExt = $extmatches[0];
            } else {
                $fileExt = $extmatches[1];
            }
        } else {
            $res['header']['content_type'] = '';
        }
        $contentType = $res['header']['content_type'];
        switch ($fileExt) {
            case 'x-speex-with-header-byte':
                $fileExt = 'speex';
                break;
            case 'mpeg4':
                $fileExt = 'mp4';
                break;
            case 'amr':
                $fileExt = 'amr';
                break;
            case 'jpeg':case 'pjpeg':
                $fileExt = 'jpg';
                break;
            case 'gif':
                $fileExt = 'gif';
                break;
            case 'x-png':case 'png':
                $fileExt = 'png';
                break;
            case 'json':case 'plain'://接口失败
                $json = json_decode($res['body'], true);
                if (!$json || (isset($json['errcode']) && $json['errcode'])) {
                    $this->setErrorInfo($json);
                    if (parent::checkAccessToken()) {
                        return $this->getMedia($media_id);
                    }
                    $info = $json['errcode'] == 40007 ? '文件已过期，无法下载' : self::$errorMsg;
                } else {
                    $info = '未知错误';
                }
                return array('status' => 0, 'info' => $info, 'msg' => $res['body']);
            default :
                saveLog('weixin/api_down', array('$media_id' => $media_id, '$res' => $res));
        }
        if ($fileExt) {
            return array('status' => 1, 'content_type' => $contentType, 'type' => $fileExt, 'data' => $res['body'], 'size' => $size);
        } else {
            return array('status' => 0, 'info' => '未知文件类型，无法保存', 'msg' => '');
        }
    }

    /*
     * 上传媒体文件到微信服务器
     * @param $filePath string 服务器文件相对路径
     * @param $uploadType string 存储类型 
     * temp：上传到临时素材库，有效期3天，
     * image：上传到图文素材库，不占用素材库中图片数量的5000个的限制，仅支持jpg/png
     * material：永久素材
     * @return array
     */

    public function uploadMedia($filePath, $uploadType = 'temp') {
        switch ($uploadType) {
            case 'temp':
                $mime = array(
                    'bmp' => array('type' => 'image/x-ms-bmp', 'filetype' => 'image'),
                    'jpg' => array('type' => 'image/jpeg', 'filetype' => 'image'),
                    'jpeg' => array('type' => 'image/jpeg', 'filetype' => 'image'),
                    'gif' => array('type' => 'image/gif', 'filetype' => 'image'),
                    'png' => array('type' => 'image/png', 'filetype' => 'image')
                );
                $uploadUrl = '/media/upload?access_token=';
                break;
            case 'image':
                $mime = array(
                    'jpg' => array('type' => 'image/jpeg', 'filetype' => 'image'),
                    'jpeg' => array('type' => 'image/jpeg', 'filetype' => 'image'),
                    'png' => array('type' => 'image/png', 'filetype' => 'image'),
                    'gif' => array('type' => 'image/jpeg', 'filetype' => 'image'),
                );
                $uploadUrl = '/media/uploadimg?access_token=';
                break;
            case 'material':
                $mime = array(
                    'jpg' => array('type' => 'image/jpeg', 'filetype' => 'image'),
                    'jpeg' => array('type' => 'image/jpeg', 'filetype' => 'image'),
                    'png' => array('type' => 'image/png', 'filetype' => 'image')
                );
                $uploadUrl = '/material/add_material?access_token=';
                break;
        }
        $fileExt = getFileExt($filePath);
        $fileRealPath = realpath($filePath);
        $fileType = isset($mime[$fileExt]['filetype']) ? $mime[$fileExt]['filetype'] : $mime['jpg']['filetype'];
        if (!in_array($fileExt, array_keys($mime))) {
            return array('status' => 0, 'msg' => '仅允许上传以下格式：' . implode('、', array_keys($mime)));
        }
        if (!$fileRealPath || !in_array($fileType, array('image', 'voice', 'video', 'thumb'))) {
            return array('status' => 0, 'msg' => '文件不存在或者类型不正确');
        }
        if (!self::$token && !parent::getAccessToken()) {
            return array('status' => 0, 'msg' => 'token失效');
        }
        $fileInfo = array(
            'path' => $fileRealPath,
            'filename' => basename($filePath),
            'content-type' => $mime[$fileExt]['type'], //文件类型
            'filelength' => filesize($filePath)//图文大小
        );
//        z(array($fileType,$fileRealPath,$fileInfo));
        $result = $this->uploadWeixinFile(self::API_URL_PREFIX . $uploadUrl . self::$token . '&type=' . $fileType, $fileInfo);
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (isset($json['errcode']) && $json['errcode'])) {
                $this->setErrorInfo($json);
                if (parent::checkAccessToken()) {
                    return $this->uploadMedia($filePath);
                }
                return array('status' => 0, 'msg' => $this->errMsg);
            }
            $json['status'] = 1;
            $json['path'] = $filePath;
            $json['size'] = changeFileSize($fileInfo['filelength']);
            $json['ext'] = $fileExt;
            return $json;
        }
        return array('status' => 0, 'msg' => $result);
    }

//上传图文素材
    public function updateNews($data) {
        if (!self::$token && !parent::getAccessToken()) {
            return array('status' => 0, 'msg' => 'token失效');
        }
        $result = getHttp(self::API_URL_PREFIX . '/material/add_news?access_token=' . self::$token, parent::json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (isset($json['errcode']) && $json['errcode'])) {
                $errList = array(45166 => '公众号权限不足，正文中不能使用链接'); //已知的错误友好提示
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                if (isset($errList[$json['errcode']])) {
                    self::$errorMsg = $json['errcode'] . '-' . $errList[$json['errcode']];
                } else {
                    self::$errorMsg = $json['errcode'] . '-' . $json['errmsg'];
                }
                self::$errAppCode = $json['errcode'];
                if (parent::checkAccessToken()) {
                    return $this->updateNews($data);
                }
                return array('status' => 0, 'msg' => $this->errMsg);
            }
            $json['status'] = 1;
            return $json;
        }
        return array('status' => 0, 'msg' => $result);
    }

//上传图文消息素材（群发用）
    public function uploadNews($data) {
        if (!self::$token && !parent::getAccessToken()) {
            return array('status' => 0, 'msg' => 'token失效');
        }
        $result = getHttp(self::API_URL_PREFIX . '/media/uploadnews?access_token=' . self::$token, parent::json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (isset($json['errcode']) && $json['errcode'])) {
                $this->setErrorInfo($json);
                if (parent::checkAccessToken()) {
                    return $this->uploadNews($data);
                }
                return array('status' => 0, 'msg' => $this->errMsg);
            }
            $json['status'] = 1;
            return $json;
        }
        return array('status' => 0, 'msg' => $result);
    }

    /**
     * 创建二维码ticket
     * @param int $scene_id 自定义追踪id
     * @param int $type 0:临时二维码；1:永久二维码(此时expire参数无效)
     * @param int $expire 临时二维码有效期，最大为1800秒
     * @return array('ticket'=>'qrcode字串','expire_seconds'=>1800)
     */
    public function getQRCode($scene_id, $type = 0, $expire = 1800) {
        if (!self::$token && !parent::getAccessToken()) {
            return false;
        }
        $data = array(
            'action_name' => $type ? 'QR_LIMIT_SCENE' : 'QR_SCENE',
            'expire_seconds' => $expire,
            'action_info' => array('scene' => array('scene_id' => $scene_id))
        );
        if ($type == 1) {
            unset($data['expire_seconds']);
        }
        $result = getHttp(self::API_URL_PREFIX . '/qrcode/create?access_token=' . self::$token, parent::json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (isset($json['errcode']) && $json['errcode'])) {
                $this->setErrorInfo($json);
                if (parent::checkAccessToken()) {
                    return $this->getQRCode($scene_id, $type, $expire);
                }
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 获取二维码图片
     * @param string $ticket 传入由getQRCode方法生成的ticket参数
     * @return string url 返回http地址
     */
    public function getQRUrl($ticket) {
        return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $ticket;
    }

    /**
     * 发送客服消息
     * @param array $data 消息结构{"touser":"OPENID","msgtype":"news","news":{...}}
     * @return boolean|array
     */
    public function sendCustomMessage($data) {
        if (!self::$token && !parent::getAccessToken()) {
            return false;
        }
        $result = getHttp(self::API_URL_PREFIX . '/message/custom/send?access_token=' . self::$token, parent::json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (isset($json['errcode']) && $json['errcode'])) {
                $this->setErrorInfo($json);
                if (parent::checkAccessToken()) {
                    return $this->sendCustomMessage($data);
                }
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 发送群发消息
     * @param array $data 消息结构
     * @example array('touser'=>array("oR5Gjjl_eiZoUpGozMo7dbBJ362A", "oR5Gjjo5rXlMUocSEXKT7Q5RQ63Q"),'msgtype'=>'text','text'=>array('content'=>'hello from boxer.'));
     * {"touser": ["oR5Gjjl_eiZoUpGozMo7dbBJ362A", "oR5Gjjo5rXlMUocSEXKT7Q5RQ63Q" ], "msgtype": "text", "text": { "content": "hello from boxer."}}
     * @return boolean|array
     * @example  {"errcode":0,"errmsg":"send job submission success","msg_id":34182}
     */
    public function sendMassMessage($data) {
        if (!self::$token && !parent::getAccessToken()) {
            return false;
        }
        $result = getHttp(self::API_URL_PREFIX . '/message/mass/sendall?access_token=' . self::$token, parent::json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (isset($json['errcode']) && $json['errcode'])) {
                $errList = array(
                    45166 => '公众号权限不足，正文中不能使用链接',
                    48003 => '公众号无群发权限，原因：1、公众号未认证；2、首次使用群发，需要在公众平台群发功能菜单下点击同意按钮，同意腾讯的群发协议',
                    45028 => '公众号群发次数不足，原因：1、公众号没有群发权限（未认证）；2、本月群发次数已用尽'
                ); //已知的错误友好提示
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                if (isset($errList[$json['errcode']])) {
                    self::$errorMsg = $json['errcode'] . '-' . $errList[$json['errcode']];
                } else {
                    self::$errorMsg = $json['errcode'] . '-' . $json['errmsg'];
                }
                self::$errAppCode = $json['errcode'];
                if (parent::checkAccessToken()) {
                    return $this->sendMassMessage($data);
                }
                return array('status' => 0, 'msg' => $this->errMsg);
            }
            $json['status'] = 1;
            return $json;
        }
        return array('status' => 0, 'msg' => $result);
    }

    /*
     * 发送模板消息
     */

    public function sendTplMessage($data) {
        if (!self::$token && !parent::getAccessToken()) {
            return false;
        }
        $result = getHttp(self::API_URL_PREFIX . '/message/template/send?access_token=' . self::$token, parent::json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (isset($json['errcode']) && $json['errcode'])) {
                $this->setErrorInfo($json);
                if (parent::checkAccessToken()) {
                    return $this->sendTplMessage($data);
                }
                return false;
            }
            return $json;
        }
        return false;
    }

    //获取模板ID
    public function addTpl($templateIdShort) {
        if (!self::$token && !parent::getAccessToken()) {
            return false;
        }
        $result = getHttp(self::API_URL_PREFIX . '/template/api_add_template?access_token=' . self::$token, parent::json_encode(array('template_id_short' => $templateIdShort)));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (isset($json['errcode']) && $json['errcode'])) {
                $this->setErrorInfo($json);
                if (parent::checkAccessToken()) {
                    return $this->addTpl($templateIdShort);
                }
                return false;
            }
            return $json;
        }
        return false;
    }

    //预览群发消息
    public function previewMassMessage($data) {
        if (!self::$token && !parent::getAccessToken()) {
            return false;
        }
        $result = getHttp(self::API_URL_PREFIX . '/message/mass/preview?access_token=' . self::$token, parent::json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (isset($json['errcode']) && $json['errcode'])) {
                $this->setErrorInfo($json);
                if (parent::checkAccessToken()) {
                    return $this->previewMassMessage($data);
                }
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 删除群发消息
     * 只有已经发送成功的消息才能删除删除消息只是将消息的图文详情页失效，已经收到的用户，还是能在其本地看到消息卡片。 另外，删除群发消息只能删除图文消息和视频消息，其他类型的消息一经发送，无法删除。
     * @param msgid int 群发消息msgid
     * @return array
     */
    public function delMassMessage($msgid) {
        if (!self::$token && !parent::getAccessToken()) {
            return false;
        }
        $result = getHttp(self::API_URL_PREFIX . '/message/mass/delete?access_token=' . self::$token, parent::json_encode(array('msg_id' => $msgid)));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (isset($json['errcode']) && $json['errcode'])) {
                $this->setErrorInfo($json);
                if (parent::checkAccessToken()) {
                    return $this->delMassMessage($msgid);
                }
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 获取关注者详细信息
     * @param string $openid
     * @return array
     */
    public function getUserInfo($openid) {
        if (!self::$token && !parent::getAccessToken()) {
            return false;
        }
        $result = getHttp(self::API_URL_PREFIX . '/user/info?access_token=' . self::$token . '&openid=' . $openid);
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (isset($json['errcode']) && $json['errcode'])) {
                $this->setErrorInfo($json);
                if (parent::checkAccessToken()) {
                    return $this->getUserInfo($openid);
                }
                return false;
            }
            return $json;
        }
        return false;
    }

    /*
     * 生成短连接
     * @param string 要生成短连接的url
     * @return string
     */

    public function getShortUrl($url) {
        if (!self::$token && !parent::getAccessToken()) {
            return false;
        }
        $result = getHttp(self::API_URL_PREFIX . '/shorturl?access_token=' . self::$token, parent::json_encode(array('action' => 'long2short', 'long_url' => $url)));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (isset($json['errcode']) && $json['errcode'])) {
                $this->setErrorInfo($json);
                if (parent::checkAccessToken()) {
                    return $this->getShortUrl($url);
                }
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 批量获取关注用户列表
     * @param unknown $next_openid
     */
    public function getUserList($next_openid = '') {
        if (!self::$token && !parent::getAccessToken()) {
            return false;
        }
        $result = getHttp(self::API_URL_PREFIX . '/user/get?access_token=' . self::$token . '&next_openid=' . $next_openid);
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (isset($json['errcode']) && $json['errcode'])) {
                $this->setErrorInfo($json);
                if (parent::checkAccessToken()) {
                    return $this->getUserList($next_openid);
                }
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 获取用户标签列表
     * @return boolean|array
     */
    public function getTags() {
        if (!self::$token && !parent::getAccessToken()) {
            return false;
        }
        $result = getHttp(self::API_URL_PREFIX . '/tags/get?access_token=' . self::$token);
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (isset($json['errcode']) && $json['errcode'])) {
                $this->setErrorInfo($json);
                if (parent::checkAccessToken()) {
                    return $this->getTags();
                }
                return false;
            }
            return $json;
        }
        return false;
    }

    //获取群发状态
    public function getMassStatus($msgId) {
        if (!self::$token && !parent::getAccessToken()) {
            return false;
        }
        $result = getHttp(self::API_URL_PREFIX . '/message/mass/get?access_token=' . self::$token, parent::json_encode(array('msg_id' => $msgId)));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (isset($json['errcode']) && $json['errcode'])) {
                $this->setErrorInfo($json);
                if (parent::checkAccessToken()) {
                    return $this->getMassStatus($msgId);
                }
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 获取用户分组列表
     * @return boolean|array
     */
    public function getGroup() {
        if (!self::$token && !parent::getAccessToken()) {
            return false;
        }
        $result = getHttp(self::API_URL_PREFIX . '/groups/get?access_token=' . self::$token);
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (isset($json['errcode']) && $json['errcode'])) {
                $this->setErrorInfo($json);
                if (parent::checkAccessToken()) {
                    return $this->getGroup();
                }
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 新增自定分组
     * @param string $name 分组名称
     * @return boolean|array
     */
    public function createGroup($name) {
        if (!self::$token && !parent::getAccessToken()) {
            return false;
        }
        $data = array(
            'group' => array('name' => $name)
        );
        $result = getHttp(self::API_URL_PREFIX . '/groups/create?access_token=' . self::$token, parent::json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (isset($json['errcode']) && $json['errcode'])) {
                $this->setErrorInfo($json);
                if (parent::checkAccessToken()) {
                    return $this->createGroup($name);
                }
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 更改分组名称
     * @param int $groupid 分组id
     * @param string $name 分组名称
     * @return boolean|array
     */
    public function updateGroup($groupid, $name) {
        if (!self::$token && !parent::getAccessToken()) {
            return false;
        }
        $data = array(
            'group' => array('id' => $groupid, 'name' => $name)
        );
        $result = getHttp(self::API_URL_PREFIX . '/groups/update?access_token=' . self::$token, parent::json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (isset($json['errcode']) && $json['errcode'])) {
                $this->setErrorInfo($json);
                if (parent::checkAccessToken()) {
                    return $this->updateGroup($groupid, $name);
                }
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 移动用户分组
     * @param int $groupid 分组id
     * @param string $openid 用户openid
     * @return boolean|array
     */
    public function updateGroupMembers($groupid, $openid) {
        if (!self::$token && !parent::getAccessToken()) {
            return false;
        }
        $data = array(
            'openid' => $openid,
            'to_groupid' => $groupid
        );
        $result = getHttp(self::API_URL_PREFIX . '/groups/members/update?access_token=' . self::$token, parent::json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (isset($json['errcode']) && $json['errcode'])) {
                $this->setErrorInfo($json);
                if (parent::checkAccessToken()) {
                    return $this->updateGroupMembers($groupid, $openid);
                }
                return false;
            }
            return $json;
        }
        return false;
    }

    /*
     * 微信服务器下载文件
     * @param $url string 处理后的完整路径
     */

    private function downloadWeixinFile($url, $data = array()) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_NOBODY, 0);    //只取body头
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if ($data) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        $package = curl_exec($curl);
        $httpinfo = curl_getinfo($curl);
        curl_close($curl);
        return array('header' => $httpinfo, 'body' => $package);
    }

    /*
     * 微信服务器上传文件
     */

    private function uploadWeixinFile($url, $data = array()) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        if (class_exists('CURLFile')) {//兼容5.5.0以上版本
            if (defined('CURLOPT_SAFE_UPLOAD')) {
                curl_setopt($curl, CURLOPT_SAFE_UPLOAD, false);
            }
            $data['media'] = new CURLFile($data['path'], $data['content-type'], $data['filename']);
//            curl_setopt($curl, CURLOPT_INFILESIZE, (Int) $data['form-data']['filelength']); //这句非常重要，告诉远程服务器，文件大小
        } else {
            $data['media'] = '@' . ($data['path']);
        }
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    private function setErrorInfo($json) {
        $this->errCode = $json['errcode'];
        $this->errMsg = $json['errmsg'];
        T('weixin/weixin.error');
        self::$errorMsg = weixinErrorApi::getErrorMessage($json['errcode'], $json['errmsg']);
        self::$errAppCode = $json['errcode'];
        return true;
    }

}
