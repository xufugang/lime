<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

final class Config {

    const SDK_VER = '7.0.7';
    const BLOCK_SIZE = 4194304; //4*1024*1024 分块上传块大小，该参数为接口规格，不能修改
    const IO_HOST = 'http://iovip.qbox.me';            // 七牛源站Host
    const RS_HOST = 'http://rs.qbox.me';               // 文件元信息管理操作Host
    const RSF_HOST = 'http://rsf.qbox.me';              // 列举操作Host
    const API_HOST = 'http://api.qiniu.com';            // 数据处理操作Host

    private $upHost;                                    // 上传Host
    private $upHostBackup;                              // 上传备用Host

    public function __construct(Zone $z = null) {         // 构造函数，默认为zone0
        if ($z === null) {
            $z = Zone::zone0();
        }
        $this->upHost = $z->upHost;
        $this->upHostBackup = $z->upHostBackup;
    }

    public function getUpHost() {
        return $this->upHost;
    }

    public function getUpHostBackup() {
        return $this->upHostBackup;
    }

}

final class Auth {

    private $accessKey;
    private $secretKey;

    public function __construct($accessKey, $secretKey) {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
    }

    public function sign($data) {
        $hmac = hash_hmac('sha1', $data, $this->secretKey, true);
        return $this->accessKey . ':' . base64_urlSafeEncode($hmac);
    }

    public function signWithData($data) {
        $data = base64_urlSafeEncode($data);
        return $this->sign($data) . ':' . $data;
    }

    public function signRequest($urlString, $body, $contentType = null) {
        $url = parse_url($urlString);
        $data = '';
        if (array_key_exists('path', $url)) {
            $data = $url['path'];
        }
        if (array_key_exists('query', $url)) {
            $data .= '?' . $url['query'];
        }
        $data .= "\n";

        if ($body !== null && $contentType === 'application/x-www-form-urlencoded') {
            $data .= $body;
        }
        return $this->sign($data);
    }

    public function verifyCallback($contentType, $originAuthorization, $url, $body) {
        $authorization = 'QBox ' . $this->signRequest($url, $body, $contentType);
        return $originAuthorization === $authorization;
    }

    public function privateDownloadUrl($baseUrl, $expires = 3600) {
        $deadline = time() + $expires;

        $pos = strpos($baseUrl, '?');
        if ($pos !== false) {
            $baseUrl .= '&e=';
        } else {
            $baseUrl .= '?e=';
        }
        $baseUrl .= $deadline;

        $token = $this->sign($baseUrl);
        return "$baseUrl&token=$token";
    }

    public function uploadToken(
    $bucket, $key = null, $expires = 3600, $policy = null, $strictPolicy = true
    ) {
        $deadline = time() + $expires;
        $scope = $bucket;
        if ($key !== null) {
            $scope .= ':' . $key;
        }
        $args = array();
        $args = self::copyPolicy($args, $policy, $strictPolicy);
        $args['scope'] = $scope;
        $args['deadline'] = $deadline;
        $b = json_encode($args);
        return $this->signWithData($b);
    }

    /**
     * 上传策略，参数规格详见
     * http://developer.qiniu.com/docs/v6/api/reference/security/put-policy.html
     */
    private static $policyFields = array(
        'callbackUrl',
        'callbackBody',
        'callbackHost',
        'callbackBodyType',
        'callbackFetchKey',
        'returnUrl',
        'returnBody',
        'endUser',
        'saveKey',
        'insertOnly',
        'detectMime',
        'mimeLimit',
        'fsizeMin',
        'fsizeLimit',
        'persistentOps',
        'persistentNotifyUrl',
        'persistentPipeline',
    );
    private static $deprecatedPolicyFields = array(
        'asyncOps',
    );

    private static function copyPolicy(&$policy, $originPolicy, $strictPolicy) {
        if ($originPolicy === null) {
            return array();
        }
        foreach ($originPolicy as $key => $value) {
            if (in_array((string) $key, self::$deprecatedPolicyFields, true)) {
                throw new \InvalidArgumentException("{$key} has deprecated");
            }
            if (!$strictPolicy || in_array((string) $key, self::$policyFields, true)) {
                $policy[$key] = $value;
            }
        }
        return $policy;
    }

    public function authorization($url, $body = null, $contentType = null) {
        $authorization = 'QBox ' . $this->signRequest($url, $body, $contentType);
        return array('Authorization' => $authorization);
    }

}

final class Etag {

    private static function packArray($v, $a) {
        return call_user_func_array('pack', array_merge(array($v), (array) $a));
    }

    private static function blockCount($fsize) {
        return (($fsize + (Config::BLOCK_SIZE - 1)) / Config::BLOCK_SIZE);
    }

    private static function calcSha1($data) {
        $sha1Str = sha1($data, true);
        $err = error_get_last();
        if ($err !== null) {
            return array(null, $err);
        }
        $byteArray = unpack('C*', $sha1Str);
        return array($byteArray, null);
    }

    public static function sum($filename) {
        $fhandler = fopen($filename, 'r');
        $err = error_get_last();
        if ($err !== null) {
            return array(null, $err);
        }

        $fstat = fstat($fhandler);
        $fsize = $fstat['size'];
        if ((int) $fsize === 0) {
            fclose($fhandler);
            return array('Fto5o-5ea0sNMlW_75VgGJCv2AcJ', null);
        }
        $blockCnt = self::blockCount($fsize);
        $sha1Buf = array();

        if ($blockCnt <= 1) {
            array_push($sha1Buf, 0x16);
            $fdata = fread($fhandler, Config::BLOCK_SIZE);
            if ($err !== null) {
                fclose($fhandler);
                return array(null, $err);
            }
            list($sha1Code, ) = self::calcSha1($fdata);
            $sha1Buf = array_merge($sha1Buf, $sha1Code);
        } else {
            array_push($sha1Buf, 0x96);
            $sha1BlockBuf = array();
            for ($i = 0; $i < $blockCnt; $i++) {
                $fdata = fread($fhandler, Config::BLOCK_SIZE);
                list($sha1Code, $err) = self::calcSha1($fdata);
                if ($err !== null) {
                    fclose($fhandler);
                    return array(null, $err);
                }
                $sha1BlockBuf = array_merge($sha1BlockBuf, $sha1Code);
            }
            $tmpData = self::packArray('C*', $sha1BlockBuf);
            list($sha1Final, ) = self::calcSha1($tmpData);
            $sha1Buf = array_merge($sha1Buf, $sha1Final);
        }
        $etag = base64_urlSafeEncode(self::packArray('C*', $sha1Buf));
        return array($etag, null);
    }

}

final class Zone {

    public $upHost;
    public $upHostBackup;

    public function __construct($upHost, $upHostBackup) {
        $this->upHost = $upHost;
        $this->upHostBackup = $upHostBackup;
    }

    public static function zone0() {
        return new self('http://up.qiniu.com', 'http://upload.qiniu.com');
    }

    public static function zone1() {
        return new self('http://up-z1.qiniu.com', 'http://upload-z1.qiniu.com');
    }

}

if (!defined('QINIU_FUNCTIONS_VERSION')) {
    define('QINIU_FUNCTIONS_VERSION', Config::SDK_VER);

    /**
     * 计算文件的crc32检验码:
     *
     * @param $file string  待计算校验码的文件路径
     *
     * @return string 文件内容的crc32校验码
     */
    function crc32_file($file) {
        $hash = hash_file('crc32b', $file);
        $array = unpack('N', pack('H*', $hash));
        return sprintf('%u', $array[1]);
    }

    /**
     * 计算输入流的crc32检验码
     *
     * @param $data 待计算校验码的字符串
     *
     * @return string 输入字符串的crc32校验码
     */
    function crc32_data($data) {
        $hash = hash('crc32b', $data);
        $array = unpack('N', pack('H*', $hash));
        return sprintf('%u', $array[1]);
    }

    /**
     * 对提供的数据进行urlsafe的base64编码。
     *
     * @param string $data 待编码的数据，一般为字符串
     *
     * @return string 编码后的字符串
     * @link http://developer.qiniu.com/docs/v6/api/overview/appendix.html#urlsafe-base64
     */
    function base64_urlSafeEncode($data) {
        $find = array('+', '/');
        $replace = array('-', '_');
        return str_replace($find, $replace, base64_encode($data));
    }

    /**
     * 对提供的urlsafe的base64编码的数据进行解码
     *
     * @param string $str 待解码的数据，一般为字符串
     *
     * @return string 解码后的字符串
     */
    function base64_urlSafeDecode($str) {
        $find = array('-', '_');
        $replace = array('+', '/');
        return base64_decode(str_replace($find, $replace, $str));
    }

    /**
     * 计算七牛API中的数据格式
     *
     * @param $bucket 待操作的空间名
     * @param $key 待操作的文件名
     *
     * @return string  符合七牛API规格的数据格式
     * @link http://developer.qiniu.com/docs/v6/api/reference/data-formats.html
     */
    function entry($bucket, $key) {
        $en = $bucket;
        if (!empty($key)) {
            $en = $bucket . ':' . $key;
        }
        return base64_urlSafeEncode($en);
    }

    /**
     * array 辅助方法，无值时不set
     *
     * @param $array 待操作array
     * @param $key key
     * @param $value value 为null时 不设置
     *
     * @return array 原来的array，便于连续操作
     */
    function setWithoutEmpty(&$array, $key, $value) {
        if (!empty($value)) {
            $array[$key] = $value;
        }
        return $array;
    }

}

final class Error {

    public $url;
    public $response;

    public function __construct($url, $response) {
        $this->url = $url;
        $this->response = $response;
    }

    public function code() {
        return $this->response->statusCode;
    }

    public function getResponse() {
        return $this->response;
    }

    public function message() {
        return $this->response->error;
    }

}

final class Request {

    public $url;
    public $headers;
    public $body;
    public $method;

    public function __construct($method, $url, array $headers = array(), $body = null) {
        $this->method = strtoupper($method);
        $this->url = $url;
        $this->headers = $headers;
        $this->body = $body;
    }

}

/**
 * HTTP response Object
 */
final class Response {

    public $statusCode;
    public $headers;
    public $body;
    public $error;
    private $jsonData;
    public $duration;

    /** @var array Mapping of status codes to reason phrases */
    private static $statusTexts = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Reserved for WebDAV advanced collections expired proposal',
        426 => 'Upgrade required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    );

    /**
     * @param int $code 状态码
     * @param double $duration 请求时长
     * @param array $headers 响应头部
     * @param string $body 响应内容
     * @param string $error 错误描述
     */
    public function __construct($code, $duration, array $headers = array(), $body = null, $error = null) {
        $this->statusCode = $code;
        $this->duration = $duration;
        $this->headers = $headers;
        $this->body = $body;
        $this->error = $error;
        $this->jsonData = null;
        if ($error !== null) {
            return;
        }

        if ($body === null) {
            if ($code >= 400) {
                $this->error = self::$statusTexts[$code];
            }
            return;
        }
        if (self::isJson($headers)) {
            try {
                $jsonData = self::bodyJson($body);
                if ($code >= 400) {
                    $this->error = $body;
                    if ($jsonData['error'] !== null) {
                        $this->error = $jsonData['error'];
                    }
                }
                $this->jsonData = $jsonData;
            } catch (\InvalidArgumentException $e) {
                $this->error = $body;
                if ($code >= 200 && $code < 300) {
                    $this->error = $e->getMessage();
                }
            }
        } elseif ($code >= 400) {
            $this->error = $body;
        }
        return;
    }

    public function json() {
        return $this->jsonData;
    }

    private static function bodyJson($body) {
        return json_decode((string) $body, true, 512);
    }

    public function xVia() {
        $via = $this->headers['X-Via'];
        if ($via === null) {
            $via = $this->headers['X-Px'];
        }
        if ($via === null) {
            $via = $this->headers['Fw-Via'];
        }
        return $via;
    }

    public function xLog() {
        return $this->headers['X-Log'];
    }

    public function xReqId() {
        return $this->headers['X-Reqid'];
    }

    public function ok() {
        return $this->statusCode >= 200 && $this->statusCode < 300 && $this->error === null;
    }

    public function needRetry() {
        $code = $this->statusCode;
        if ($code < 0 || ($code / 100 === 5 and $code !== 579) || $code === 996) {
            return true;
        }
    }

    private static function isJson($headers) {
        return array_key_exists('Content-Type', $headers) &&
                strpos($headers['Content-Type'], 'application/json') === 0;
    }

}

final class Client {

    public static function get($url, array $headers = array()) {
        $request = new Request('GET', $url, $headers);
        return self::sendRequest($request);
    }

    public static function post($url, $body, array $headers = array()) {
        $request = new Request('POST', $url, $headers, $body);
        return self::sendRequest($request);
    }

    public static function multipartPost(
    $url, $fields, $name, $fileName, $fileBody, $mimeType = null, array $headers = array()
    ) {
        $data = array();
        $mimeBoundary = md5(microtime());

        foreach ($fields as $key => $val) {
            array_push($data, '--' . $mimeBoundary);
            array_push($data, "Content-Disposition: form-data; name=\"$key\"");
            array_push($data, '');
            array_push($data, $val);
        }

        array_push($data, '--' . $mimeBoundary);
        $mimeType = empty($mimeType) ? 'application/octet-stream' : $mimeType;
        $fileName = self::escapeQuotes($fileName);
        array_push($data, "Content-Disposition: form-data; name=\"$name\"; filename=\"$fileName\"");
        array_push($data, "Content-Type: $mimeType");
        array_push($data, '');
        array_push($data, $fileBody);

        array_push($data, '--' . $mimeBoundary . '--');
        array_push($data, '');

        $body = implode("\r\n", $data);
        $contentType = 'multipart/form-data; boundary=' . $mimeBoundary;
        $headers['Content-Type'] = $contentType;
        $request = new Request('POST', $url, $headers, $body);
        return self::sendRequest($request);
    }

    private static function userAgent() {
        $sdkInfo = "QiniuPHP/" . Config::SDK_VER;

        $systemInfo = php_uname("s");
        $machineInfo = php_uname("m");

        $envInfo = "($systemInfo/$machineInfo)";

        $phpVer = phpversion();

        $ua = "$sdkInfo $envInfo PHP/$phpVer";
        return $ua;
    }

    private static function sendRequest($request) {
        $t1 = microtime(true);
        $ch = curl_init();
        $options = array(
            CURLOPT_USERAGENT => self::userAgent(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => false,
            CURLOPT_CUSTOMREQUEST => $request->method,
            CURLOPT_URL => $request->url
        );

        // Handle open_basedir & safe mode
        if (!ini_get('safe_mode') && !ini_get('open_basedir')) {
            $options[CURLOPT_FOLLOWLOCATION] = true;
        }

        if (!empty($request->headers)) {
            $headers = array();
            foreach ($request->headers as $key => $val) {
                array_push($headers, "$key: $val");
            }
            $options[CURLOPT_HTTPHEADER] = $headers;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

        if (!empty($request->body)) {
            $options[CURLOPT_POSTFIELDS] = $request->body;
        }
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        $t2 = microtime(true);
        $duration = round($t2 - $t1, 3);
        $ret = curl_errno($ch);
        if ($ret !== 0) {
            $r = new Response(-1, $duration, array(), null, curl_error($ch));
            curl_close($ch);
            return $r;
        }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = self::parseHeaders(substr($result, 0, $header_size));
        $body = substr($result, $header_size);
        curl_close($ch);
        return new Response($code, $duration, $headers, $body, null);
    }

    private static function parseHeaders($raw) {
        $headers = array();
        $headerLines = explode("\r\n", $raw);
        foreach ($headerLines as $line) {
            $headerLine = trim($line);
            $kv = explode(':', $headerLine);
            if (count($kv) > 1) {
                $headers[$kv[0]] = trim($kv[1]);
            }
        }
        return $headers;
    }

    private static function escapeQuotes($str) {
        $find = array("\\", "\"");
        $replace = array("\\\\", "\\\"");
        return str_replace($find, $replace, $str);
    }

}

final class FormUploader {

    /**
     * 上传二进制流到七牛, 内部使用
     *
     * @param $upToken    上传凭证
     * @param $key        上传文件名
     * @param $data       上传二进制流
     * @param $params     自定义变量，规格参考
     *                    http://developer.qiniu.com/docs/v6/api/overview/up/response/vars.html#xvar
     * @param $mime       上传数据的mimeType
     * @param $checkCrc   是否校验crc32
     *
     * @return array    包含已上传文件的信息，类似：
     *                                              [
     *                                                  "hash" => "<Hash string>",
     *                                                  "key" => "<Key string>"
     *                                              ]
     */
    public static function put(
    $upToken, $key, $data, $config, $params, $mime, $checkCrc
    ) {
        $fields = array('token' => $upToken);
        if ($key === null) {
            $fname = 'filename';
        } else {
            $fname = $key;
            $fields['key'] = $key;
        }
        if ($checkCrc) {
            $fields['crc32'] = crc32_data($data);
        }
        if ($params) {
            foreach ($params as $k => $v) {
                $fields[$k] = $v;
            }
        }

        $response = Client::multipartPost($config->getUpHost(), $fields, 'file', $fname, $data, $mime);
        if (!$response->ok()) {
            return array(null, new Error($config->getUpHost(), $response));
        }
        return array($response->json(), null);
    }

    /**
     * 上传文件到七牛，内部使用
     *
     * @param $upToken    上传凭证
     * @param $key        上传文件名
     * @param $filePath   上传文件的路径
     * @param $params     自定义变量，规格参考
     *                    http://developer.qiniu.com/docs/v6/api/overview/up/response/vars.html#xvar
     * @param $mime       上传数据的mimeType
     * @param $checkCrc   是否校验crc32
     *
     * @return array    包含已上传文件的信息，类似：
     *                                              [
     *                                                  "hash" => "<Hash string>",
     *                                                  "key" => "<Key string>"
     *                                              ]
     */
    public static function putFile(
    $upToken, $key, $filePath, $config, $params, $mime, $checkCrc
    ) {

        $fields = array('token' => $upToken, 'file' => self::createFile($filePath, $mime));
        if ($key !== null) {
            $fields['key'] = $key;
        }
        if ($checkCrc) {
            $fields['crc32'] = crc32_file($filePath);
        }
        if ($params) {
            foreach ($params as $k => $v) {
                $fields[$k] = $v;
            }
        }
        $fields['key'] = $key;
        $headers = array('Content-Type' => 'multipart/form-data');
        $response = client::post($config->getUpHost(), $fields, $headers);
        if (!$response->ok()) {
            return array(null, new Error($config->getUpHost(), $response));
        }
        return array($response->json(), null);
    }

    private static function createFile($filename, $mime) {
        // PHP 5.5 introduced a CurlFile object that deprecates the old @filename syntax
        // See: https://wiki.php.net/rfc/curl-file-upload
        if (function_exists('curl_file_create')) {
            return curl_file_create($filename, $mime);
        }

        // Use the old style if using an older version of PHP
        $value = "@{$filename}";
        if (!empty($mime)) {
            $value .= ';type=' . $mime;
        }

        return $value;
    }

}

/**
 * 主要涉及了资源上传接口的实现
 *
 * @link http://developer.qiniu.com/docs/v6/api/reference/up/
 */
final class UploadManager {

    private $config;

    public function __construct(Config $config = null) {
        if ($config === null) {
            $config = new Config();
        }
        $this->config = $config;
    }

    /**
     * 上传二进制流到七牛
     *
     * @param $upToken    上传凭证
     * @param $key        上传文件名
     * @param $data       上传二进制流
     * @param $params     自定义变量，规格参考
     *                    http://developer.qiniu.com/docs/v6/api/overview/up/response/vars.html#xvar
     * @param $mime       上传数据的mimeType
     * @param $checkCrc   是否校验crc32
     *
     * @return array    包含已上传文件的信息，类似：
     *                                              [
     *                                                  "hash" => "<Hash string>",
     *                                                  "key" => "<Key string>"
     *                                              ]
     */
    public function put(
    $upToken, $key, $data, $params = null, $mime = 'application/octet-stream', $checkCrc = false
    ) {
        $params = self::trimParams($params);
        return FormUploader::put(
                        $upToken, $key, $data, $this->config, $params, $mime, $checkCrc
        );
    }

    /**
     * 上传文件到七牛
     *
     * @param $upToken    上传凭证
     * @param $key        上传文件名
     * @param $filePath   上传文件的路径
     * @param $params     自定义变量，规格参考
     *                    http://developer.qiniu.com/docs/v6/api/overview/up/response/vars.html#xvar
     * @param $mime       上传数据的mimeType
     * @param $checkCrc   是否校验crc32
     *
     * @return array    包含已上传文件的信息，类似：
     *                                              [
     *                                                  "hash" => "<Hash string>",
     *                                                  "key" => "<Key string>"
     *                                              ]
     */
    public function putFile(
    $upToken, $key, $filePath, $params = null, $mime = 'application/octet-stream', $checkCrc = false
    ) {
        $file = fopen($filePath, 'rb');
        if ($file === false) {
            throw new \Exception("file can not open", 1);
        }
        $params = self::trimParams($params);
        $stat = fstat($file);
        $size = $stat['size'];
        if ($size <= Config::BLOCK_SIZE) {
            $data = fread($file, $size);
            fclose($file);
            if ($data === false) {
                throw new \Exception("file can not read", 1);
            }
            return FormUploader::put(
                            $upToken, $key, $data, $this->config, $params, $mime, $checkCrc
            );
        }
        $up = new ResumeUploader(
                $upToken, $key, $file, $size, $params, $mime, $this->config
        );
        $ret = $up->upload();
        fclose($file);
        return $ret;
    }

    public static function trimParams($params) {
        if ($params === null) {
            return null;
        }
        $ret = array();
        foreach ($params as $k => $v) {
            $pos = strpos($k, 'x:');
            if ($pos === 0 && !empty($v)) {
                $ret[$k] = $v;
            }
        }
        return $ret;
    }

}

class QiNiu {

    private $auth;
    private $bucket;
    private $uploadMgr;
    public $error = '未知错误';

    public function __construct($bucket, $user, $pwd) {
        // 构建鉴权对象
        $this->auth = new Auth($user, $pwd);
        $this->bucket = $bucket;
        // 初始化 UploadManager 对象并进行文件的上传
        $this->uploadMgr = new UploadManager();
    }

    public function writeFile($file, $path, $policy = array()) {
        // 生成上传 Token
        $token = $this->auth->uploadToken($this->bucket . ':' . $file, null, 3600, $policy);
        // 调用 UploadManager 的 putFile 方法进行文件的上传
        list($ret, $err) = $this->uploadMgr->putFile($token, $file, $path);
        if ($err !== null) {
            $this->error = $err->response->error;
            return '';
        } else {
            return isset($ret['key']) ? $ret['key'] : '';
        }
    }

    public function changeMedia($file, $path) {
        //要进行转码的转码操作
        $fops = 'avthumb/mp3/ab/92k/ar/44100/acodec/libmp3lame';
        //可以对转码后的文件进行使用saveas参数自定义命名，当然也可以不指定文件会默认命名并保存在当间
        $savekey = base64_urlSafeEncode($this->bucket . ':' . $file);
        $policy = array(
            'persistentOps' => $fops . '|saveas/' . $savekey,
            'persistentPipeline' => 'shehui-media', //转码时使用的队列名称
        );
        $uptoken = $this->auth->uploadToken($this->bucket, null, 3600, $policy);
        list($ret, $err) = $this->uploadMgr->putFile($uptoken, $file, $path);
        if ($err !== null) {
            $this->error = $err->response->error;
            return '';
        } else {
            return isset($ret['key']) ? $ret['key'] : '';
        }
    }

    public function getErrMsg() {
        return $this->error;
    }

}
