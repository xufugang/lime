<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * 自定义系统配置文件，将覆盖默认的配置信息
 *
 */
return array(
    'title' => '青柠', //网站标题
    'city_name' => '杭州',
    'city_lat' => '30.276987', //百度地图纬度
    'city_lng' => '120.16976', //百度地图经度
    //'map_ak'=>'FpoKT7mIbKkEbC8u87xOYmyPoC6UClZa',
    'socket_channel' => 330000, //多城市站，避免消息串号，需要区分不同的城市
    'default_province_id' => 330000, //默认省份
    'default_city_id' => 330100, //默认城市
    //'set_socket_url' => 'http://182.254.146.114:4141',
    //'get_socket_url' => 'wss://socket.zjtengo.com/shehui/',
    //'socket_key' => 'iugefefggHIyio7ht8d',
    'admin_version' => 'v1.0',
    'main_url' => '', //主路径
    'main_path' => '',
    'regexp_phone' => '/^1[34578][0-9]{9}$/',
    'cookie' => //设置cookie
    array(
        'pre' => 'dev_', //COOKIE前缀
        'path' => '/', //COOKIE作用路径
        'domain' => '', //COOKIE作用域
    ),
    'time_zone' => 'PRC', //设置区时
    'vcode' => 'ytuyiqwrwriocvhhi@kl', //密钥
    'skin' => 'default', //当前主题
    'c' => 'index', //默认控制器名称
    'm' => 'index', //默认模型
    'g' => 'index', //当前分组
    'default_group' => 'index', //默认分组
    'group_list' => array('content', 'open', 'test','api','weixin'), //分组列表
    'path_mod' => 1, //路由模式，1 path_info，2 普通
    'delimiter' => '/', //分隔符号，建议为 "/"or "-" or "_"
    'postfix' => '.html', //URL后缀
    'hide_index' => true, //是否隐藏 index.php，需要配置服务器
    'filter' => 'htmlspecialchars', //POST,GET 默认过滤函数
    'gzip' => false, //开启GZIP压缩模式
    'autoload_action' => array(
        'index' => 'common',
        'api' => 'common',
        'content' => 'common',
        'weixin' => 'common'
    ), //自动加载的控制器
    'tmp_cache_type' => 'file', //F、S函数保存临时文件的类型 file or redis，redis时需要在config/db下指定redis的配置参数
//    'url_rule' => array('house/detail/id' => 'house', 'index/index' => 'index','news/detail/id'=>'news'), //自定义路由规则
);
