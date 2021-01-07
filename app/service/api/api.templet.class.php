<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/**
 * Description of apiTemplet
 * @class 接口文档模板生成类
 * @author 
 */
class apiTemplet {

    /**
     * 获取文档信息
     */
    public static function api($action, $item) {
        $str = '##接口名称
' . $item['title'] . '
';
        if ($item['desc']) {
            $str.='##接口说明
' . $item['desc'] . '
';
        }
        $str.='##请求地址
' . $action . '/' . $item['path'] . '
##请求方式
' . strtoupper($item['method']) . '
##需要登录
' . ($item['login'] ? '是' : '否') . '
##请求参数
';
        if ($item['param']) {
            $str.='|参数名|类型|描述|必填|默认值
|:----:|:---:|:-----|:-----:|:---:|
';
            foreach ($item['param'] as $val) {
                $str.='|' . $val['name'] . ' |' . $val['type'] . '|' . $val['desc'] . '|' . ($val['need'] ? '是' : '否') . '|' . $val['value'] . '|
';
            }
            $str = trim($str) . '|';
        } else {
            $str.='无参数
';
        }
        $str.='
##返回数据
``` 
' . self::_format_json($item['return']) . '
```
';
        if ($item['field']) {
            $str.='##返回参数说明
|字段名|描述|
|:-----|:-----|
';
            foreach ($item['field'] as $key => $val) {
                $str.='|' . $key . ' |' . $val . '|
';
            }
        }
        if ($item['notice']) {
            $str.='> #### 注意
' . $item['notice'] . '

';
        }
        if ($item['example']) {
            $str.='##示例
' . $item['example'];
        }
        return $str;
    }

    /*
     * 获取数据表字典
     */

    public static function dict($item) {
        $str = '##数据表名

|字段名称|注释|类型|默认值|允许为空|
|:----|:-----|:---|:----:|:---:|
';
        foreach ($item as $val) {
            $str.='|' . $val['Field'] . '|' . $val['Comment'] . '|' . $val['Type'] . '|' . $val['Default'] . '|' . ($val['Null'] == 'NO' ? '否' : '是') . '|
';
        }
        $str.='
- 备注：';
        return $str;
    }

    public static function _format_json($json, $html = false) {
        $tabcount = 0;
        $result = '';
        $inquote = false;
        $ignorenext = false;
        if ($html) {
            $tab = "   ";
            $newline = "<br/>";
        } else {
            $tab = "\t";
            $newline = "\n";
        }
        for ($i = 0; $i < strlen($json); $i++) {
            $char = $json[$i];
            if ($ignorenext) {
                $result .= $char;
                $ignorenext = false;
            } else {
                switch ($char) {
                    case '{':
                        $tabcount++;
                        $result .= $char . $newline . str_repeat($tab, $tabcount);
                        break;
                    case '}':
                        $tabcount--;
                        $result = trim($result) . $newline . str_repeat($tab, $tabcount) . $char;
                        break;
                    case ',':
                        $result .= $char . $newline . str_repeat($tab, $tabcount);
                        break;
                    case '"':
                        $inquote = !$inquote;
                        $result .= $char;
                        break;
                    case '\\':
                        if ($inquote) {
                            $ignorenext = true;
                        }
                        $result .= $char;
                        break;
                    default:
                        $result .= $char;
                }
            }
        }
        return $result;
    }

}
