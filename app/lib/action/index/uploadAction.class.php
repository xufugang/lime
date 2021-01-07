<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of uploadAction
 *
 * @author xlp
 */
class uploadAction extends commonAction {

    function __construct() {
        parent::__construct();
    }

    public function index() {
        parent::_checkLogin();
        $id = $this->_get('id', 'img_url');
        $dir = $this->_get('path', '');
        if (!checkPath($dir)) {
            $dir = 'user';
        }
        $callback = $this->_get('callback', '');
        if (!$id) {
            showError('参数错误');
        }
        $upload_img_type = str_replace('|', ',', C('upload', 'pic_type'));
        $this->assign(array('upload_img_type' => $upload_img_type, 'id' => $id, 'callback' => $callback, 'dir' => $dir));
        $this->display('index');
    }
    function attach() {
        parent::_checkLogin();
        $id = $this->_get('id', 'img_url');
        $dir = $this->_get('path', '');
        if (!checkPath($dir)) {
            $dir = 'user';
        }
        $callback = $this->_get('callback', '');
        if (!$id) {
            showError('参数错误');
        }
        $upload_img_type = str_replace('|', ',', C('upload', 'pic_type') . '|' . C('upload', 'attach_type'));
        $this->assign(array('upload_img_type' => $upload_img_type, 'id' => $id, 'callback' => $callback, 'dir' => $dir));
        $this->display('index');
    }

    //flash插件上传图片
    public function flashpostpic() {
        parent::_checkLogin();
        $upData = array();
        $errMsg = '未知错误';
        if (!$_FILES) {
            echo json_encode(array('status' => 0, 'info' => '没有上传信息', 'data' => null));
            exit();
        } else {
            $file = current($_FILES);
            if (isHave($file['error'])) {
                $errMsg = $this->getUploadFileErrorMsg($file['error']);
                echo json_encode(array('status' => 0, 'info' => $errMsg, 'data' => null));
                exit();
            }
        }
        if (!$upData) {
            load('upload');
            $myUpload = new Myupload();
            $upload = $myUpload->upload('news');
            if ($upload) {
                $upload = parent::_sendImageToYunServer($upload); //同步图片到云存储
                $upData = $upload[0];
                $upData['savepath'] = str_replace('./', '', $upData['savepath']);
            } else {
                $errMsg = $myUpload->getErrorMsg();
            }
        }
        if ($upData) {
            echo json_encode(array('status' => 1, 'info' => 'ok', 'data' => array(
                    'fileUrl' => getImgUrl($upData['savepath']),
                    'fileName' => $upData['realname'],
                    'savePath' => $upData['savepath'],
                    'name' => str_replace('.' . getFileExt($upData['realname']), '', $upData['realname'])
            )));
        } else {
            echo json_encode(array('status' => 0, 'info' => $errMsg, 'data' => null));
        }
    }

    //编辑器上传插件
    public function editor() {
        $action = $this->_get('action');
        switch ($action) {
            case 'config':
                $uploadConfig = explode('|', C('upload', 'pic_type'));
                foreach ($uploadConfig as $k => $v) {
                    $uploadConfig[$k] = '.' . $v;
                }
                $data = array(
                    'imageActionName' => 'upload_image', /* 执行上传图片的action名称 */
                    'imageFieldName' => 'upfile', /* 提交的图片表单名称 */
                    'imageMaxSize' => 1 * 1024 * 1024, /* 上传大小限制，单位B */
                    'imageAllowFiles' => $uploadConfig, /* 上传图片格式显示 */
                    'imageCompressEnable' => true, /* 是否压缩图片,默认是true */
                    'imageCompressBorder' => 1000, /* 图片压缩最长边限制 */
                    'imageInsertAlign' => 'none', /* 插入的图片浮动方式 */
                    'imageUrlPrefix' => '', /* 图片访问路径前缀 */
                    'imageManagerActionName' => 'listimage', /* 执行图片管理的action名称 */
                    'imageManagerListSize' => 20, /* 每次列出文件数量 */
                    'imageManagerInsertAlign' => 'none', /* 插入的图片浮动方式 */
                    'imageManagerUrlPrefix' => '', //图片访问路径前缀
                );
                echo json_encode($data);
                break;
            case 'upload_image':
                $res = array(
                    'state' => '',
                    'url' => '',
                    'title' => '',
                    'original' => '',
                    'type' => '',
                    'size' => ''
                );
                if (parent::_checkLogin(true)) {
                    $upData = array();
                    $errMsg = '未知错误';
                    if (!$_FILES) {
                        $res['state'] = '没有上传信息';
                        echo json_encode($res);
                        exit();
                    } else {
                        $file = current($_FILES);
                        if (isHave($file['error'])) {
                            $res['state'] = $this->getUploadFileErrorMsg($file['error']);
                            echo json_encode($res);
                            exit();
                        }
                    }
                    if (!$upData) {
                        load('upload');
                        $myUpload = new Myupload();
                        $upload = $myUpload->upload('user/u' . admin::$adminInfo['id']);
                        if ($upload) {
                            $upload = parent::_sendImageToYunServer($upload); //同步图片到云存储
                            $upData = $upload[0];
                            $upData['savepath'] = str_replace('./', '', $upData['savepath']);
                        } else {
                            $errMsg = $myUpload->getErrorMsg();
                        }
                    }
                    if ($upData) {
                        $res = array(
                            'state' => 'SUCCESS',
                            'url' => getImgUrl($upData['savepath']),
                            'title' => $upData['realname'],
                            'original' => $upData['savepath'],
                            'type' => '.' . $upData['type'],
                            'size' => $upData['size']
                        );
                    } else {
                        $res['state'] = $errMsg;
                    }
                } else {
                    $res['state'] = '未授权操作';
                }
                echo json_encode($res);
                break;
            default :
                showError('未定义的操作');
        }
    }

    //普通上传
    public function up() {
        parent::_checkLogin();
        $id = $this->_post('id', 'img_url');
        $callback = $this->_post('callback', 0);
        $dir = $this->_post('path', '');
        if (!checkPath($dir)) {
            $dir = 'user';
        }
        $upData = '';
        if (isHave($_FILES['upimg']) && !isHave($_FILES['upimg']['error'])) {
            if (!$upData) {
                load('upload');
                $myUpload = new Myupload();
                $upload = $myUpload->upload($dir . '/u' . admin::$adminInfo['id']);
                if ($upload) {
                    $upload = parent::_sendImageToYunServer($upload); //同步图片到云存储
                    if (!$upload[0]) {
                        $errMsg = $myUpload->getErrorMsg();
                        echo "<script>try{window.parent.Msg.error('上传错误：" . $errMsg . "',function(){history.go(-1);});}catch(e){alert('上传错误：" . $errMsg . "');history.go(-1);};</script>";
                        exit();
                    }
                    $upData = $upload[0];
                    $upData['savepath'] = str_replace('./', '', $upData['savepath']);
                }
            }
            if (!$upData) {
                $errMsg = '上传文件失败';
                echo "<script>try{window.parent.Msg.error('上传错误：" . $errMsg . "',function(){history.go(-1);});}catch(e){alert('上传错误：" . $errMsg . "');history.go(-1);};</script>";
                exit();
            }
            if ($callback) {
                $arr = array(
                    'file_url' => getImgUrl($upData['savepath']),
                    'savepath' => $upData['savepath'],
                    'fsize' => changeFileSize($upData['size']),
                    'size' => $upData['size'],
                );
                echo "<script>window.parent.document.getElementById('" . $id . "').value='" . $upData['savepath'] . "';\r\nwindow.parent." . $callback . "(" . json_encode($arr) . ");\r\n";
            } else {
                echo "<script>window.parent.document.getElementById('" . $id . "').value='" . $upData['savepath'] . "';\r\n";
            }
            echo "window.location.href='" . U('upload/index', array('id' => $id, 'callback' => $callback, 'path' => $dir)) . "';</script>\r\n";
            exit();
        } else {
            $errMsg = $this->getUploadFileErrorMsg($_FILES['upimg']['error']);
            echo "<script>try{window.parent.Msg.error('上传错误：" . $errMsg . "',function(){history.go(-1);});}catch(e){alert('上传错误：" . $errMsg . "');history.go(-1);};</script>";
            exit();
        }
    }

    private function getUploadFileErrorMsg($error) {
        $errorCode = array(
            0 => '文件上传成功',
            1 => '上传的文件过大',
            2 => '上传文件过大',
            3 => '文件上传不完整',
            4 => '没有文件被上传'
        );
        return isset($errorCode[$error]) ? $errorCode[$error] . ',错误码：' . $error : '未知错误';
    }

}
