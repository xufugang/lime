<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of adminAction
 * 直播管理
 * @author xufg
 */
class channelAction extends commonAction
{

    public function __construct()
    {
        parent::__construct();
        parent::_checkLogin();
        parent::_checkValidity();
    }

    public function index()
    {
        $p = $this->_getid('p', 1);
        $rs = M('channel')->where(array('status' => 1))->page($p)->findAll();
        $total = M('channel')->getTotal();
        $this->assign(array('rs' => $rs, 'p' => $p, 'total' => $total));
        $this->display();
    }

    //添加发起人
    public function add()
    {
        $this->display();
    }

    //新增提交
    public function post()
    {
        $name = $this->_post('name');
        if (!trim($name)) {
            $this->JsonReturn('参数缺失');
        }
        $content = $this->_post('content');
        $insertdata = array();
        $insertdata['bizid'] = '2668';
        $insertdata['name'] = $name;
        $insertdata['content'] = $content ? $content : '';
        $insertdata['url'] = "rtmp://" . $this->setting['biZid'] . ".livepush.myqcloud.com/live/" . $this->setting['biZid'].'_'.$name . '?';
        $url = D('push')->getPlayUrl($this->setting['biZid'], $name);
        $insertdata['live_url'] = $url ? $url[2] : '';
        $insertdata['create_time'] = TIME;
        $isHave = M('channel')->where(array('name' => $name, 'status' => 1))->find();
        if ($isHave) {
            $this->JsonReturn('该频道已经存在');
        }
        if ($insertdata) {
            $bool = M('channel')->insert($insertdata);
            if ($bool) {
                $this->JsonReturn('添加成功', null, 1);
            }
        }
        $this->JsonReturn('添加失败');

    }

    public function edit()
    {
        $id = $this->_getid('id');
        if (!$id) {
            $this->JsonReturn('参数缺失');
        }
        $rs = M('channel')->where(array('id' => $id, 'status' => 1))->find();
        if (!$rs) {
            $this->JsonReturn('频道不存在或已删除');
        }
        $this->assign(array('rs' => $rs));
        $this->display();
    }

    //保存修改
    public function save()
    {
        $id = $this->_postid('id');
        $name = $this->_post('name');
        $content = $this->_post('content');
        if (!$id) {
            $this->JsonReturn('参数缺失');
        }
        $rs = M('channel')->where(array('id' => $id, 'status' => 1))->find();
        if (!$rs) {
            $this->JsonReturn('频道不存在或已删除');
        }
        $updata = array();
        $updata['name'] = $name;
        $updata['content'] = $content ? $content : '';
        if (trim($name) != $rs['name']) {
            $isHave = M('channel')->where(array('name' => $name, 'status' => 1))->find();
            if ($isHave) {
                $this->JsonReturn('该频道已经存在');
            }
        }
        if ($updata) {
            $bool = M('channel')->update($updata, array('id' => $id));
            if ($bool) {
                $this->JsonReturn('更新成功', null, 1);
            }
        }
        $this->JsonReturn('更新失败');

    }

    //删除
    public function delete()
    {
        $id = $this->_post('id');
        if (!$id) {
            $this->JsonReturn('参数缺失');
        }
        $rs = M('channel')->where(array('id' => $id, 'status' => 1))->find();
        if (!$rs) {
            $this->JsonReturn('频道不存在或者已经删除');
        }
        $bool = M('channel')->update(array('status' => 0), array('id' => $id));
        if ($bool) {
            $this->JsonReturn('删除成功', $bool, 1);
        }
        $this->JsonReturn('删除失败');

    }

    //获取省下面的城市，已经对应城市的直播员
    public function getcity()
    {
        $city_id = $this->_post('city_id');
        //直播员列表
        $liver = M('admin')->where(array('group_id' => 2, 'status' => 1, 'city_id' => $city_id))->findAll(false);
        $liverhtml = '';
        if ($liver) {
            foreach ($liver as $key => $val) {
                if (!$key) {
                    $liverhtml = $liverhtml . '<div class="radio-box">
                            <input type="radio" id="radio_auth_' . $val['id'] . '" name="liver"
                                   value="' . $val['id'] . '"
                                ' . "checked" . '>
                            <label for="radio_auth_' . $val['id'] . '">' . $val['name'] . '</label>
                        </div>';
                } else {
                    $liverhtml = $liverhtml . '<div class="radio-box">
                            <input type="radio" id="radio_auth_' . $val['id'] . '" name="liver"
                                   value="' . $val['id'] . '"
                                >
                            <label for="radio_auth_' . $val['id'] . '">' . $val['name'] . '</label>
                        </div>';
                }

            }
        }
        $this->JsonReturn('ok', $liverhtml, 1);
    }


}
