<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of adminAction
 *
 * @author xufg
 */
class adminAction extends commonAction
{

    public function __construct()
    {
        parent::__construct();
        parent::_checkLogin();
        parent::_checkValidity();
    }

    //添加用户
    public function add()
    {
        $city = M('city')->where(array('pid' => 0))->findAll();
        $this->assign(array('city' => $city));
        $this->display();
    }

    //新增提交
    public function post()
    {
        $username = trim($this->_post('name'));
        $pwd = $this->_post('psw');
        $repwd = $this->_post('repsw');
        $validity_time = $this->_post('effective');
        $group_id = $this->_post('group_id');
        $status = $this->_post('status');
        $city_id = $this->_post('city');
        if (!trim($username) || empty($pwd)) {
            $this->JsonReturn('用户名和密码不能为空');
        }
        //二次密码是否匹配
        if ($pwd != $repwd) {
            $this->JsonReturn('二次输入的密码不匹配');
        }
        $insertdata = array();
        $insertdata['name'] = $username;
        $insertdata['login_ip'] = $_SERVER['REMOTE_ADDR'];
        $insertdata['validity_time'] = $validity_time ? strtotime($validity_time . '00:00:00') : 0;
        $insertdata['city_id'] = $city_id;
        $insertdata['group_id'] = $group_id;
        $insertdata['status'] = $status;
        $insertdata['psw'] = D('admin')->setUserPassword($pwd);
        $insertdata['create_time'] = time();
        $user = M('admin')->where(array('name' => $username, 'status' => array(1, 2)))->find();
        if ($user) {
            $this->JsonReturn('用户已存在');
        } else {
            $rs = M('admin')->insert($insertdata);
            if ($rs) {
                $this->JsonReturn('添加成功', $rs, 1);
            }
            $this->JsonReturn('添加失败');
        }

    }

    //删除
    public function del()
    {
        $id = $this->_post('id');
        if (!$id) {
            $this->JsonReturn('参数缺失');
        }
        if ($id == admin::$adminInfo['id']) {
            $this->JsonReturn('您不可以删除当前登陆账户');
        }
        $rs = M('admin')->where(array('id' => $id, 'status' => 1))->find();
        if (!$rs) {
            $this->JsonReturn('用户不存在或者已经删除');
        }
        $bool = M('admin')->update(array('status' => 0), array('id' => $id));
        if ($bool) {
            $this->JsonReturn('删除成功', $bool, 1);
        }
        $this->JsonReturn('删除失败');

    }

    //编辑
    public function edit()
    {

        $id = $this->_get('id');
        if (!$id) {
            $this->JsonReturn('参数缺失');
        }
        $rs = M('admin')->where(array('status' => array(1, 2), 'id' => $id))->find();
        if (!$rs) {
            $this->JsonReturn('用户不存在或已删除');
        }
        //所有城市信息
        $city = M('city')->where(array('pid' => 0))->findAll();
        //所在省份-城市信息
        $p = M('city')->where(array('city_id' => $rs['city_id']))->find();
        $clist = M('city')->where(array('pid' => $p['pid']))->findAll(false);
        $direct_city = array('110000', '120000', '310000', '500000');
        $cityArr = array();
        if ($clist) {
            foreach ($clist as $key => $val) {
                if (in_array($val['pid'], $direct_city)) {
                    $cityArr[] = $val;
                    break;
                } else {
                    $cityArr[] = $val;
                }
            }

        }

        $this->assign(array('rs' => $rs, 'city' => $city, 'p' => $p['pid'], 'clist' => $cityArr));
        $this->display();
    }

    //保存修改
    public function save()
    {
        //z($_POST);
        $id = $this->_post('id');
        if (empty($id)) {
            $this->JsonReturn('参数缺失');
        }
        $rs = M('admin')->where(array('id' => $id, 'status' => array(1, 2)))->find();
        if (empty($rs)) {
            $this->JsonReturn('账户不存在或已删除');
        }
        $username = trim($this->_post('name'));
        $pwd = $this->_post('psw');
        $repwd = $this->_post('repsw');
        $validity_time = $this->_post('effective');
        $group_id = $this->_post('group_id');
        $status = $this->_post('status');
        $city_id = $this->_post('city');
        if (!trim($username)) {
            $this->JsonReturn('用户名不能为空');
        }
        //二次密码是否匹配
        if (trim($pwd) && ($pwd != $repwd)) {
            $lenth = strlen($pwd);
            if ($lenth < 6 || $lenth > 20) {
                $this->JsonReturn('请输入6-20位长度的字符串');
            }
            $this->JsonReturn('两次输入的密码不匹配');
        }
        $insertdata = array();
        $insertdata['name'] = $username;
        $insertdata['validity_time'] = $validity_time ? strtotime($validity_time . '00:00:00') : 0;
        $insertdata['city_id'] = $city_id;
        $insertdata['group_id'] = $group_id;
        $insertdata['status'] = $status;
        $insertdata['psw'] = D('admin')->setUserPassword($pwd);
        if ($username != $rs['name']) {
            $user = M('admin')->where(array('name' => $username, 'status' => array(1, 2)))->find();
            if ($user) {
                $this->JsonReturn('用户已存在');
            }
        }
        $rs = M('admin')->update($insertdata, array('id' => $id));
        if ($rs) {
            $this->JsonReturn('修改成功', $rs, 1);
        }
        $this->JsonReturn('修改失败，您可能没有做修改');


    }

    //获取城市信息
    public function getcity()
    {
        $city_id = $this->_post('city_id');
        if (!$city_id) {
            $this->JsonReturn('省份不能为空');
        }
        $city = M('city')->where(array('pid' => $city_id))->order('city_id asc')->findAll();
        $inhtml = '';
        $direct_city = array('110000', '120000', '310000', '500000');
        foreach ($city as $key => $val) {
            if (in_array($val['pid'], $direct_city)) {
                if ($key) {
                    break;
                }
                $inhtml = $inhtml . '<option value=' . $val['city_id'] . ' >  ' . $val['name'] . ' </option>';

            } else {
                $inhtml = $inhtml . '<option value=' . $val['city_id'] . ' >  ' . $val['name'] . ' </option>';
            }

        }
        $this->JsonReturn('ok', $inhtml, 1);
    }


}
