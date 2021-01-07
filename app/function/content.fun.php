<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * content分组自定义函数库
 */

/*
 * 根据status值来生成不同颜色的tag
 */

function getStatusStyle($status = 0) {
    $style = 'label label-default radius';
    switch ($status) {
        case 0:
            $style = 'label label-danger radius';
            break;
        case 1:
            $style = 'label label-success radius';
            break;
        case 2:
            $style = 'label label-warning radius';
            break;
        case 3:
            $style = 'label label-primary radius';
            break;
        case 4:
            $style = 'label label-secondary radius';
            break;
        default :
            $style = 'label label-default radius';
    }
    return $style;
}
