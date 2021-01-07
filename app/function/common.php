<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * 全局自定义函数库
 */

/**
 * 获取图片的完整路径（用于多图片服务器处理路径）
 * @param $src string 图片路径
 * @return string 处理后的图片地址
 */
function getImgUrl($src = '') {
    if ($src) {
        if (strpos($src, 'yun/') === 0) {
            $upConf = C('upload', 'yun');
            return $upConf['url'] . ltrim($src, '/');
        } elseif (strpos($src, 'qiniu/') === 0) {
            $upConf = C('upload', 'qiniu');
            return $upConf['url'] . ltrim($src, '/');
        } elseif (strpos($src, 'statics/') === 0) {
            return C('system', 'main_path') . ltrim($src, '/');
        } elseif (strExists($src, 'http://')) {
            return $src;
        } else {
            return BASE_URL . $src;
        }
    }
    return BASE_URL . 'statics/default/images/default.png';
}

function getUser($uid, $field = 'user_name') {
    $user = D('member')->getUserInfoById($uid);
    if ($field) {
        return isset($user[$field]) ? (($field == 'nickname' && !$user[$field]) ? '没有昵称' : $user[$field]) : '';
    } else {
        return $user;
    }
}

/**
 * 快捷 HTTP 请求，支持简单GET和POST请求
 * @param $url string 请求地址
 * @param $data array POST请求数据
 * @param $opt 自定义参数
 * $headers = array(
  'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9) Gecko/2008052906 Firefox/3.0',
  'Referer: http://www.163.com',
  'Host:',
  'Origin:',
  'X-Requested-With:XMLHttpRequest'
  );
 * @return string content
 */
function getHttp($url, $data = array(), $opt = array()) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    if (strExists($url, 'https://')) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    }
    if ($opt && !is_array($opt)) {
        curl_setopt($curl, CURLOPT_REFERER, $opt);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36 MicroMessenger/6.5.2.501 NetType/WIFI WindowsWechat'); // 模拟用户使用的浏览器
    }
    if ($opt && is_array($opt)) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $opt);
    }
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    if ($data) {
        if (is_array($data)) {
            $p = http_build_query($data, '', '&');
        } else {
            $p = $data;
        }
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $p);
    }
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
    $tmpInfo = curl_exec($curl);
    if (curl_errno($curl)) {
        saveLog('http/error', 'Curl Error:url:' . $url . ' ,info:' . curl_error($curl));
    }
    curl_close($curl);
    return $tmpInfo;
}

/**
 * 获取访问者来源（移动端）
 * @param 无
 * @return string 用户客户端设备类型
 */
function getUserAgent() {
    $userAgent = 'unknown';
    $ua = strtolower(USER_AGENT);
    if ($ua) {
        if (strExists($ua, 'micromessenger')) {
            $userAgent = 'weixin';
        } elseif (strExists($ua, 'iphone') || strExists($ua, 'ipad')) {
            $userAgent = 'IOS';
        } elseif (strExists($ua, 'android')) {
            $userAgent = 'Android';
        }
    }
    return $userAgent;
}

/*
 * 生成或检查缩略图
 * @param $image string 原图路径
 * @param $width int 缩略图宽
 * @param $height int 缩略图高
 * @param $isCheck bool 是否仅仅是检查文件存在
 */

function getThumb($image, $thumbId = 0, $isCheck = false) {
    if (!$image) {
        return '';
    }
    $upConf = C('upload', 'thumb_size');
    if (!isset($upConf[$thumbId])) {
        $thumbId = 0;
    }
    if (strpos($image, 'yun/') === 0) {
        return $image . $upConf[$thumbId]['t'];
    } else {
        T('image/thumb');
        return thumb::init($image, $upConf[$thumbId]['w'], $upConf[$thumbId]['h'], $isCheck);
    }
}

/**
 * 调试函数
 */
function z($str, $exit = true) {
    echo '<pre>';
    print_r($str);
    echo '</pre>';
    if ($exit) {
        exit;
    }
}

/**
 * 格式化金额函数
 */
function priceFormat($price) {
    return number_format($price, 2, '.', '');
}

/*
 * 保存图片等上传资源
 * @param $filename 保存文件名
 * @param $data 数据流
 * @return array
 */

function saveFile($filename, $data, $isAvatar = false, $sync = false) {
    $upConf = C('upload');
    $fileInfo = array('status' => 0, 'size' => strlen($data), 'url' => $filename);
    //临时处理，检查是否由于又拍云导致失败率飙升
    if ($isAvatar && !$sync) {
        $upConf['cdn'] = '';
    }
    //临时处理，检查是否由于又拍云导致失败率飙升
    $localFilename = str_replace(array('./', '../'), '', $filename);
    if (stripos($localFilename, $upConf['dir']) !== 0) {
        $localFilename = $upConf['dir'] . '/' . ltrim($filename, '/');
    }
    //本地存储
    $localSave = 0;
    setDir(dirname($localFilename));
    if (file_put_contents(ROOT . $localFilename, $data)) {
        $localSave = 1;
    }
    if ($upConf['cdn'] && $localSave) {//开启云存储
        $cdn = $upConf['cdn'];
        $fileInfo['url'] = $isAvatar ? $filename : $upConf[$cdn]['dir'] . '/' . ltrim($filename, '/');
        switch ($upConf['cdn']) {
            case 'yun':
                T('image/upyun');
                $upyun = new UpYun($upConf[$cdn]['bucket'], $upConf[$cdn]['user'], $upConf[$cdn]['pwd']);
                $rsp = $upyun->writeFile('/' . $fileInfo['url'], $data, true);
                $fileInfo['status'] = 1;
                $fileInfo['width'] = $rsp['x-upyun-width'];
                $fileInfo['height'] = $rsp['x-upyun-height'];
                $fileInfo['type'] = strtolower($rsp['x-upyun-file-type']);
                break;
            case 'qiniu':
                T('image/qiniu');
                $qiniu = new Qiniu($upConf[$cdn]['bucket'], $upConf[$cdn]['user'], $upConf[$cdn]['pwd']);
                $rsp = $qiniu->writeFile($fileInfo['url'], $localFilename);
                if ($rsp) {
                    $fileInfo['status'] = 1;
                }
                break;
            default :
        }
    } else {//本地存储
        $fileInfo['status'] = 1;
        $fileInfo['url'] = $localFilename;
    }
    return $fileInfo;
}

/*
 * 异步操作处理
 * $mod string 操作模块
 * $postData array post数据
 * $cookieData array cookie数据
 * $isShowReturn bool 是否等待返回结果 默认 false
 * 
 */

function asynHttp($mod = '', $postData = array(), $cookieData = array(), $isShowReturn = false) {
    if (!$mod) {
        showError('缺少操作模块');
    }
    $html = '';
    $nonce = getRandInt(8);
    $timestamp = TIME;
    $tmpArr = array(VCODE, $timestamp, $nonce);
    sort($tmpArr, SORT_STRING);
    $signature = sha1(implode($tmpArr));
    $res = array('header' => '', 'body' => '');
    $urlArr = parse_url(BASE_URL);
    $hostname = $urlArr['host'];
    $port = 80;
    $errno = 0;
    $errstr = null;
    $requestPath = WEB_PATH . 'open/asyn/index?mod=' . $mod . '&nonce=' . $nonce .
            '&timestamp=' . $timestamp . '&signature=' . $signature;
    $fp = fsockopen($hostname, $port, $errno, $errstr, 30);
    if (!$fp) {
        $res['body'] = json_encode(array('status' => 0, 'info' => array('errno' => $errno, 'msg' => $errstr)));
        return $isShowReturn ? $res : false;
    }
    $method = 'GET';
    if (!empty($postData)) {
        $method = 'POST';
    }
    $header = "$method $requestPath HTTP/1.0\r\n";
    $header.="Host: $hostname\r\n";
    if (!empty($cookieData)) {
        $_cookie = strval(NULL);
        foreach ($cookieData as $k => $v) {
            $_cookie .= $k . '=' . $v . '; ';
        }
        $cookie_str = 'Cookie: ' . ($_cookie) . " \r\n"; //传递Cookie  
        $header .= $cookie_str;
    }
    if (!empty($postData)) {
        $_post = http_build_query($postData, '', '&');
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n"; //POST数据
        $header .= "Content-Length: " . strlen($_post) . "\r\n"; //POST数据的长度
        $header.="Connection: Close\r\n\r\n"; //长连接关闭
        $header .= $_post; //传递POST数据
    } else {
        $header.="Connection: Close\r\n\r\n"; //长连接关闭
    }
    fwrite($fp, $header);
    if ($isShowReturn) {
        while (!feof($fp)) {
            $html.=fgets($fp);
        }
        list($res['header'], $res['body']) = preg_split('/\r\n\r\n|\n\n|\r\r/', $html, 2);
    } else {
        usleep(10000);
    }
    fclose($fp);
    return $isShowReturn ? $res : true;
}

function tranTime($timeStr) {
    if (!checkNum($timeStr)) {
        return $timeStr;
    }
    $ftime = $timeStr;
    $rtime = date("H:i", $timeStr);
    $time = TIME - $timeStr;
    if ($time < 60) {
        $str = '刚刚';
    } elseif ($time < 60 * 60) {
        $min = floor($time / 60);
        $str = $min . '分钟前';
    } elseif ($time < 60 * 60 * 24) {
        $h = floor($time / (60 * 60));
        $str = $h . '小时前 ';
    } elseif ($time < 60 * 60 * 24 * 3) {
        $d = floor($time / (60 * 60 * 24));
        if ($d == 1) {
            $str = '昨天 ' . $rtime;
        } else {
            $str = '前天 ' . $rtime;
        }
    } else {
        $str = date('m-d H:i', $ftime);
    }
    return $str;
}

/**
 * $string 明文或密文
 * $operation 加密ENCODE或解密DECODE
 * $key 密钥
 * $expiry 密钥有效期
 */
function authcode($str, $operation = 'DECODE', $deKey = '', $expiry = 0) {
    $ckey_length = 4;
    $key = md5($deKey ? $deKey : 'UKywfere7hr74trhtrhrtwetre00');
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($str, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);
    $string = $operation == 'DECODE' ? base64_decode(substr($str, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($str . $keyb), 0, 16) . $str;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

/**
 * 推送数据到socket通道
 * @param $action string 动作名称
 * @param $to string 消息接受者 publish:全局群发
 * @return json 操作结果
 */
function sendDataToSocket($action, $to, $objData) {
    $sendData = authcode(json_encode(array('action' => $action, 'channel' => C('system', 'socket_channel'), 'to' => $to, 'data' => $objData)), 'ENCODE', C('system', 'socket_key'));
    $res = getHttp(C('system', 'set_socket_url'), array('msg' => $sendData));
    $rs = json_decode($res, true);
    if (isHave($rs['status'])) {
        return array('status' => 1, 'info' => 'ok', 'data' => $rs['data']);
    } else {
        return array('status' => 0, 'info' => (isset($rs['info']) ? $rs['info'] : $res), 'data' => isset($rs['data']) ? $rs['data'] : '');
    }
}

//生成省市区域列表缓存
function getCityGroupList() {
    static $CityListData = null;
    if ($CityListData) {
        return $CityListData;
    }
    $list = F('setting/city');
    if (!$list) {
        $rs = M('city')->field('city_id,pid,name')->findAll(false);
        $list = array();
        if ($rs) {
            foreach ($rs as $v) {
                $list[$v['city_id']] = $v;
            }
            F('setting/city', $list);
            $CityListData = $list;
        }
    }
    return $list;
}

function getCityNameById($cityId) {
    $list = getCityGroupList();
    return isset($list[$cityId]) ? $list[$cityId]['name'] : '';
}

//处理url参数
function getSearchUrl($arr = array()) {
    static $_url = array();
    if (!$_url) {
        $url = getUrlStrList(array(), true);
        unset($url['g'], $url['c'], $url['m']);
        $_url = $url;
    }
    $url = $_url;
    if ($arr) {
        foreach ($arr as $key => $val) {
            if (!is_null($val)) {
                $url[$key] = $val;
            } elseif (isset($url[$key])) {
                unset($url[$key]);
            }
        }
    }
    return $url;
}

/*
 * 回输入数组中某个单一列的值
 * @param $array 数组
 * @param $columnKey 需要返回值的列
 * @param $indexKey 作为返回数组的索引/键的列
 * @return $result 返回数组
 */
if (!function_exists('array_column')) {

    function array_column(array $array, $columnKey = null, $indexKey = null) {
        $result = array();
        foreach ($array as $arr) {
            if (!is_array($arr)) {
                continue;
            }
            if (is_null($columnKey)) {
                $value = $arr;
            } else {
                $value = $arr[$columnKey];
            }
            if (!is_null($indexKey)) {
                $key = $arr[$indexKey];
                $result[$key] = $value;
            } else {
                $result[] = $value;
            }
        }
        return $result;
    }

}

/*
 * 当前服务器内存缓存，只适合于当前程序上下文内容缓存使用
 * @param $name string 缓存键值名称
 * @param $value object 缓存内容，如果为 null表示清空缓存
 * @param $expire int 缓存过期时间
 * @return array
 */

function cacheData($name, $value = '', $expire = 0) {
    $channel = C('system', 'cookie');
    V('mCache/cache'); //缓存类
    if ('' !== $value) {
        if (is_null($value)) {
            return cacheApi::delete($channel['pre'] . $name);
        } else {
            return cacheApi::set($channel['pre'] . $name, $value, $expire);
        }
    }
    return cacheApi::get($channel['pre'] . $name);
}

/**
 * 全概率计算
 *
 * @param array $input array('a'=>0.5,'b'=>0.2,'c'=>0.4)
 * @param int $pow 小数点位数
 * @return array key
 */
function getRand($input, $pow = 3) {
    $much = pow(10, $pow);
    $max = array_sum($input) * $much;
    $rand = mt_rand(1, $max);
    $base = 0;
    $defaultRand = '';
    foreach ($input as $k => $v) {
        $min = $base * $much + 1;
        $max = ($base + $v) * $much;
        if ($min <= $rand && $rand <= $max) {
            return $k;
        } else {
            $base += $v;
        }
        if (!$defaultRand) {
            $defaultRand = $k;
        }
    }
    return $defaultRand;
}

/**
 * 多维数组排序
 *
 * @param $array array 原始多维数组
 * @param $keyid string 排序的键名
 * @param $order string 排序方式 desc:倒序，asc:顺序
 * @param $type string 排序类型 number 数字 string 字符串
 * @return array 排序完毕的数组
 */
function sortArray($array, $keyid, $order = 'desc', $type = 'number') {
    if (is_array($array) && $array) {
        $orderArr = array_column($array, $keyid);
        $order = ($order == 'asc') ? SORT_ASC : SORT_DESC;
        $type = ($type == 'number') ? SORT_NUMERIC : SORT_STRING;
        array_multisort($orderArr, $order, $type, $array);
    }
    return $array;
}

/**
 * 枚举数组
 *
 * @param $arr array 原始多维数组
 * @param $items array key数组
 * @param $split string 子项分割符号
 * @return string 整合完毕的字符串
 */
function showArrList($arr, $items, $split = ',') {
    $rs = array();
    if ($items && is_array($items)) {
        foreach ($items as $val) {
            if (isset($arr[$val])) {
                $rs[] = $arr[$val];
            }
        }
    }
    return implode($split, $rs);
}

/*
 * 调用页面片
 * @param $id int 页面片编号
 * @return string 页面片内容，html片段
 */

function getPageDetail($id) {
    $detail = F('page/detail_' . $id);
    if (!$detail) {
        $info = M('page')->where(array('id' => $id))->getField('content');
        if ($info) {
            F('page/detail_' . $id, $info);
            $detail = $info;
        }
    }
    return $detail;
}

/*
 * 生成栏目缓存
 */

function getCateCache($clear = false) {
    if ($clear) {
        F('setting/cate', null);
        $cate = null;
    } else {
        $cate = F('setting/cate');
    }
    if (!$cate) {
        $cate = M('cate')->field('id,name,sort,pid,path,depth,is_show')->where(array('is_del' => 0))->order('sort DESC')->select('id', false);
        F('setting/cate', $cate);
    }
    return $cate;
}

/*
 * 根据编号获取栏目名称
 */

function getCateNameById($id) {
    $cateList = getCateCache();
    return isset($cateList[$id]) ? $cateList[$id]['name'] : '';
}
