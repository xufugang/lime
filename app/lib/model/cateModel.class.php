<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * 生成
 */
class cateModel extends Model {

    function __construct() {
        parent::__construct();
        $this->dbTable = 'cate';
    }

    public function add($proId, $pid, $name, $sort = 0, $isShow = 1, $reset = true) {
        $data = array(
            'pro_id' => $proId,
            'name' => $name,
            'pid' => $pid,
            'sort' => $sort,
            'is_show' => $isShow,
        );
        if (!$data['name']) {
            $data['name'] = '未命名';
        }
        $cateId = $this->insert($data);
        if ($cateId && $reset) {
            $this->reset($proId);
        }
        return $cateId;
    }

    //重新处理类目表关系
    public function reset($proId) {
        $status = 0;
        $this->rs = $this->field('id,name,pid')->where(array('is_del' => 0, 'pro_id' => $proId))->select('id');
//        echo '共更新：' . M('ste_cate')->getTotal() . ' 条数据<br/>';
        if ($this->rs) {
            foreach ($this->rs as $val) {
                $path = $this->getParent($val['id']);
                $pathStr = implode(',', $path);
                $pathCount = count($path) - 1;
                $update = array(
                    'path' => $pathStr,
                    'depth' => $pathCount > 3 ? 3 : $pathCount
                );
                $status = $this->update($update, array('id' => $val['id']));
//                echo '更新：' . $val['name'] . ' -path:' . $pathStr . '，depth:' . $pathCount . ' 状态:' . ($status ? 'OK' : '无改变') . ' <br/>';
            }
        }
        return $status;
    }

    //递归获取父类
    private function getParent($id) {
        $ParentList = array(0);
        if (isset($this->rs[$id])) {
            if ($this->rs[$id]['pid']) {
                $ParentList = $this->getParent($this->rs[$id]['pid']);
            }
        }
        $ParentList[] = $id;
        return $ParentList;
    }

}
