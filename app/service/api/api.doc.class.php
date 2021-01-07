<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/**
 * Description of apiDoc
 * @class 接口文档生成类
 * @author 
 */
class apiDoc {

    /**
     * 获取文档信息
     */
    public static function show($str) {
        $target = '/\/\*[\s\S]*?\*\//';
        $result = array();
        $res = preg_match_all($target, $str, $result);
        if ($res) {
            $newResult = array();
            foreach ($result[0] as $k => $v) {
                if ($k == 0) {
                    $name = self::getClassName($v);
                    $action = self::getClassActionName($v);
                    if ($name && $action) {
                        $newResult['api_name'] = $name;
                        $newResult['api_action'] = $action;
                    }
                } else {
                    $path = self::getPath($v);
                    $item = array(
                        'title' => self::getTitle($v),
                        'path' => $path,
                        'desc' => self::getDesc($v),
                        'method' => self::getMethod($v),
                        'login' => self::getNeedLogin($v),
                        'param' => self::getParam($v),
                        'return' => self::getReturn($v),
                        'field' => self::getReturnField($v),
                        'notice' => self::getNotice($v),
                        'example' => self::getExample($v)
                    );
                    if ($item['title'] && $item['path']) {
                        $newResult['items'][($path ? $path : $k)] = $item;
                    }
                }
            }
            return $newResult;
        }
    }

    /**
     * 得到当前文档接口名称
     */
    public static function getClassName($str) {
        $target = '/@name\s(.*)\s/';
        $result = array();
        $res = preg_match($target, $str, $result);
        if ($res == 1) {
            return trim($result[1]);
        } else {
            return '';
        }
    }

    /**
     * 得到当前文档接口Action
     */
    public static function getClassActionName($str) {
        $target = '/@action\s(.*)\s/';
        $result = array();
        $res = preg_match($target, $str, $result);
        if ($res == 1) {
            return trim($result[1]);
        } else {
            return '';
        }
    }

    /*
     * 获取接口功能名称
     */

    public static function getTitle($str) {
        $target = '/@title\s(.*)\s/';
        $result = array();
        $res = preg_match($target, $str, $result);
        if ($res == 1) {
            return trim($result[1]);
        } else {
            return '';
        }
    }

    /*
     * 获取接口路径
     */

    public static function getPath($str) {
        $target = '/@path\s(.*)\s/';
        $result = array();
        $res = preg_match($target, $str, $result);
        if ($res == 1) {
            return trim($result[1]);
        } else {
            return '';
        }
    }

    /*
     * 获取接口说明
     */

    public static function getDesc($str) {
        $target = '/@desc\s(.*)\s/';
        $result = array();
        $res = preg_match($target, $str, $result);
        if ($res == 1) {
            return trim($result[1]);
        } else {
            return '';
        }
    }

    /*
     * 获取接口请求方式
     */

    public static function getMethod($str) {
        $target = '/@method\s(.*)\s/';
        $result = array();
        $res = preg_match($target, $str, $result);
        if ($res == 1) {
            return trim($result[1]);
        } else {
            return 'GET';
        }
    }

    /*
     * 获取接口是否需要登录
     */

    public static function getNeedLogin($str) {
        $target = '/@needlogin\s(.*)\s/';
        $result = array();
        $res = preg_match($target, $str, $result);
        if ($res == 1) {
            return $result[1] ? true : false;
        } else {
            return false;
        }
    }

    /*
     * 获取接口参数信息
     */

    public static function getParam($str) {
        $target = '/@param\s(.*)\s/';
        $result = array();
        $res = preg_match_all($target, $str, $result);
        if ($res) {
            $newResult = array();
            foreach ($result[1] as $v) {
                $newArr = explode(' ', $v);
                if (is_array($newArr) && $newArr && count($newArr) >= 3 && $newArr[0]) {
                    $newResult[] = array(
                        'name' => trim($newArr[0]), //字段名
                        'type' => trim($newArr[1]), //类型
                        'desc' => trim($newArr[2]), //描述
                        'need' => (isset($newArr[3]) && strtolower(trim($newArr[3])) == 'true' ? true : false), //是否必填字段
                        'value' => isset($newArr[4]) ? trim($newArr[4]) : '', //默认值
                    );
                }
            }
            return $newResult;
        } else {
            return array();
        }
    }

    /*
     * 获取接口返回数据
     */

    public static function getReturn($str) {
        $target = '/@return\s(.*)\s/';
        $result = array();
        $res = preg_match($target, $str, $result);
        if ($res == 1) {
            return trim($result[1]);
        } else {
            return '';
        }
    }

    /*
     * 获取接口返回字段信息
     */

    public static function getReturnField($str) {
        $target = '/@field\s(.*)\s/';
        $result = array();
        $res = preg_match_all($target, $str, $result);
        if ($res) {
            $newResult = array();
            foreach ($result[1] as $v) {
                $newArr = explode(' ', $v);
                if (is_array($newArr) && $newArr && count($newArr) == 2) {
                    $newResult[$newArr[0]] = trim($newArr[1]);
                }
            }
            return $newResult;
        } else {
            return array();
        }
    }

    /*
     * 获取接口返回说明
     */

    public static function getNotice($str) {
        $target = '/@notice\s(.*)\s/';
        $result = array();
        $res = preg_match($target, $str, $result);
        if ($res == 1) {
            return trim($result[1]);
        } else {
            return '';
        }
    }

    /*
     * 获取接口调用示例
     */

    public static function getExample($str) {
        $target = '/@example\s(.*)\s/';
        $result = array();
        $res = preg_match($target, $str, $result);
        if ($res == 1) {
            return trim($result[1]);
        } else {
            return '';
        }
    }

}
