<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * 上传配置文件
 */
return array(
    'dir' => 'upload', //上传目录
    'maxsize' => 3, //单位M
    'dirtype' => 3, //上传保存目录 1：Ymd、2：Y-m-d、3：Y/md、4：Y/m/d，默认为3
    'pic_type' => 'jpg|png|gif|jpeg',
    'attach_type' => 'mp3|wav|wma|ppt|zip|rar|txt|doc|docx|mp4|3gp|avi|mov|mpv|mpg',
    'text_type' => 'txt|doc|docx',
    'video_type' => 'mp4',
    'cdn' => '', //cdn使用哪个云服务
    'yun' => array(

    ),
    'qiniu' => array(//七牛云存储设置
        'bucket' => '',
        'user' => '',
        'pwd' => '',
        'dir' => 'qiniu',
        'url' => ''
    ),
    'thumb_size' => array(//缩略图尺寸,t:对应云储存上的缩略图版本
        array('w' => 400, 'h' => 260, 't' => '!p1'), //资讯列表页
        array('w' => 400, 'h' => 280, 't' => '!p2'), //土地资讯列表页、楼盘列表缩略图
        array('w' => 480, 'h' => 320, 't' => '!p3'), //土地资讯列表页、楼盘列表缩略图
        array('w' => 400, 'h' => 383, 't' => '!p4'), //户型图列表
    )
);
