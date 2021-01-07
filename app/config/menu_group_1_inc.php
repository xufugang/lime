<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * 后台菜单配置文件-管理员
 */
return array(
    'quick' => array(
    ),
    'user' => array(
        array(
            'title' => '修改密码',
            'href' => U('my/edit'),
            'icon' => '&#xe63f;',
            'target' => 'pop', //pop full
            'width' => 500,
            'height' => 300
        ),
        array(
            'title' => '退出',
            'href' => U('login/logout'),
            'icon' => '&#xe706;',
            'target' => 'logout', //pop full
            'width' => 0,
            'height' => 0
        ),
    ),
    'main' => array(
        'admin' => array('title' => '账号管理', 'url' => '', 'icon' => '&#xe625;', 'short' => 'admin', 'item' =>
            array(
            )
        ),
        'user' => array('title' => '用户管理', 'url' => '', 'icon' => '&#xe616;', 'short' => 'user', 'item' =>
            array(
            )
        ),
    )
);
