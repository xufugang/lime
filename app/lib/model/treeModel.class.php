<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * 生成树形
 */
class treeModel extends Model {

    function __construct() {
        parent::__construct();
    }

    public function genSelectOption($tree, $selected = '', $down = 1) {
        static $options = '';
        foreach ($tree as $v) {
            $space = str_repeat('&emsp;&emsp;', $v['depth'] - $down > 0 ? $v['depth'] - $down > 0 : 0);

            if ($selected == $v['id']) {
                $options .= "<option value='{$v['id']}' selected>$space{$v['name']}</option>";
            } else {
                $options .= "<option value='{$v['id']}'>$space{$v['name']}</option>";
            }

            if (isset($v['son'])) {
                $this->genSelectOption($v['son'], $selected);
            }
        }
        return $options;
    }

    //数组转树
    public function genTree($items) {
        $tree = array();
        foreach ($items as $v) {
            if (isset($items[$v['pid']])) {
                $items[$v['pid']]['son'][] = &$items[$v['id']];
            } else {
                $tree[] = &$items[$v['id']];
            }
        }
        return $tree;
    }

    /*
     * 从类目树中获取全部子类
     */

    public function getSubs($categorys, $catId = 0, $hasSelf = true, $level = 1) {
        static $_id = array();
        $subs = array();
        if ($categorys) {
            foreach ($categorys as $item) {
                if ($hasSelf && $item['id'] == $catId) {
                    $item['level'] = $level;
                    $subs[] = $item;
                    $_id[] = $item['id'];
                }
                if ($item['pid'] == $catId) {
                    $item['level'] = $level + 1;
                    $subs[] = $item;
                    $_id[] = $item['id'];
                    $arr = $this->getSubs($categorys, $item['id'], false, $level + 2);
                    $subs = array_merge($subs, $arr['items']);
                }
            }
        }
        return array('list' => $_id, 'items' => $subs);
    }

}
