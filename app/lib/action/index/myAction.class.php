<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of myAction
 *
 * @author xlp
 */
class myAction extends commonAction
{

    function __construct()
    {
        parent::__construct();
        parent::_checkLogin();
    }

    public function index()
    {
        $this->edit();
    }

    public function info()
    {
        $this->assign(array('rs' => admin::$adminInfo));
        $this->display();
    }

    public function edit()
    {
        $this->display();
    }

    function savepsw()
    {
        $original = $this->_post('original_psw');
        if (!$original || !$this->_post('psw') || !$this->_post('repsw')) {
            $this->JsonReturn('参数缺失');
        }
        $rs = M('admin')->where(array('id' => admin::$adminInfo['id'], 'status' => 1))->find();
        if ($rs) {
            if ( D('admin')->setUserPassword($original) != $rs['pwd']) {
                $this->JsonReturn('原始密码有误');
            }
        } else {
            $this->JsonReturn('用户不存在或已删除');
        }
        $objData = array(
            'psw' => $this->_post('psw', '')
        );
        //内容规则检查
        T('content/validate');
        $validate = array(
            array('psw', 'min_length', '密码长度必须大于6位', 6),
        );
        if (!validate::check($validate, $objData)) {
            $this->JsonReturn(validate::getError());
        }
        if ($objData['psw'] != $this->_post('repsw')) {
            $this->JsonReturn('两次密码不一致，请检查');
        }
        if ($objData['psw']) {
            $psw = D('admin')->setUserPassword($objData['psw']);
            if ($psw != admin::$adminInfo['psw']) {
                D('admin')->update(array('pwd' => $psw), array('id' => admin::$adminInfo['id']));
                D('admin')->setUserLogin(array('id' => admin::$adminInfo['id'], 'psw' => $psw), 0, false);
                $this->JsonReturn('操作成功', null, 1);
            }else{
                $this->JsonReturn('密码未变更', null, 0);
            }
        }
        $this->JsonReturn('密码未变更', null, 0);
    }

}
