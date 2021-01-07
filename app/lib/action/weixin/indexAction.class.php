<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/**
 * Description of indexAction
 * 微信首页
 * @TIME 2018-10-29
 * @author xlp
 */
class indexAction extends commonAction
{
    public $setting = '';

    function __construct()
    {
        parent::__construct();
        $this->setting = C('setting');

    }

    public function index()
    {

        $openid = user::$userInfo['openid'];
        if (!$openid) {
            $this->JsonReturn('参数错误', null, 0);
        }
        $rs = M('user')->where(array('openid' => $openid, 'status' => 1))->find();
        saveLog('sys/register',$rs);
        if (!$rs) {
            $this->JsonReturn('用户不存在或者已经删除', '', 4);
        }
        $showdata = array();
        $showdata['id'] = $rs['uid'];
        $showdata['headimg'] = $rs['headimg'] ? getImgUrl($rs['headimg']) : '';
        $showdata['username'] = $rs['username'];
        $showdata['phone'] = $rs['phone'];
        $showdata['user_type'] = $rs['user_type'];
        $showdata['work_unit'] = $rs['work_unit'];
        $showdata['referee_num'] = $rs['referee_num'];
        $showdata['referee'] = $rs['referee'];
        $this->assign(array('setting' => $this->setting));
        $this->display();

    }

    /* @title 会员中心
     * @path membercenter
     * @desc 会员中心
     * @method GET
     * @needlogin TRUE
     * @param uid int 授权登录后的id 是 无
     * @field headimg 用户头像
     * @field id 用户id
     * @field username 用户名
     * @field phone 手机号码
     * @field work_unit 工作单位
     * @field referee_num 推荐人数
     * @field referee 推荐人
     * @field user_type 用户类型：1-发起人，2-普通会员
     * @field banner banner图
     * @field left 会员中心左边，名称+链接
     * @field right 会员中心右边，名称+链接
     *
     */
    public function membercenter()
    {
        $openid = user::$userInfo['openid'];
        if (!$openid) {
            $this->JsonReturn('参数错误', null, 0);
        }
        $showdata = array();
        //banner图、协会简章、人员架构
        $banner = M('content')->where(array('id' => 1))->find();
        $showdata['banner'] = $banner['url'] ? getImgUrl($banner['url']) : '//' . $_SERVER['HTTP_HOST'] . '/saihong/statics/default/images/weixin/main-bg@2x.jpg';
        $xiehui = M('content')->where(array('id' => 2))->find();
        $showdata['left'] = array();
        $xh['name'] = $xiehui['name'];
        $xh['url'] = $xiehui['url'];
        $showdata['left'] = $xh;
        $jianzhang = M('content')->where(array('id' => 3))->find();
        $showdata['right'] = array();
        $jz['name'] = $jianzhang['name'];
        $jz['url'] = $jianzhang['url'];
        $showdata['right'] = $jz;
        $rs = M('user')->where(array('openid' => $openid, 'status' => 1))->find();
        if (!$rs['phone']) {
            $this->JsonReturn('用户不存在或者已经删除', $showdata, 4);
        }

        $showdata['id'] = $rs['uid'];
        $showdata['headimg'] = $rs['headimg'] ? getImgUrl($rs['headimg']) : '';
        $showdata['username'] = $rs['username'];
        $showdata['phone'] = $rs['phone'];
        $showdata['user_type'] = $rs['user_type'];
        $showdata['work_unit'] = $rs['work_unit'];
        $showdata['referee_num'] = $rs['referee_num'];
        $showdata['referee'] = $rs['referee'];


        $this->JsonReturn('查询成功', $showdata, 1);

    }

    /* @title 注册
     * @path register
     * @desc 获取手机验证码
     * @method POST
     * @needlogin TRUE
     * @param phone String 手机号 是 无
     * @param uid String 授权注册id 是 无
     * @param type int 类型：1-发起人注册，2-普通会员注册 是 无
     * @param code String 验证码 是 无
     * @param username String 用户名 是 无
     * @param work_unit String 工作单位 是 无
     * @param media_id String 图片 是 无
     * @param referee String 推荐人 是 无
     */
    public function register()
    {
        $uid = $this->_post('uid');
        $phone = $this->_post('phone');
        $type = $this->_post('type');//1-发起人注册，2-普通会员注册
        $username = $this->_post('username');
        $work_unit = $this->_post('work_unit');
        $referee = '';
        if ($type == 1) {
            $media_id = $this->_post('pic');
            if ($media_id) {
                $pic = $this->_getMedia($media_id);
            }
        } else {
            $referee = $this->_post('referee');
        }
        if (!trim($username)) {
            $this->JsonReturn('姓名不能为空');
        }
        if ($phone && !preg_match(C('system', 'regexp_phone'), $phone)) {
            $this->JsonReturn('请输入正确的手机号码');
        }

        if (!trim($work_unit)) {
            $this->JsonReturn('工作单位不能为空');
        }
        //是否存在推荐人
        if ($referee) {
            $isref = M('user')->where(array('username' => $referee, 'status' => 1))->find();
            if (!$isref) {
                $this->JsonReturn('推荐人不存在或者已经删除');
            }
        }
        $users = M('user')->where(array('username' => trim($username), 'status' => 1))->find();
        if ($users) {
            $this->JsonReturn('该用户名已存在');
        }
        //数据组装
        $insertdata = array();
        $insertdata['username'] = trim($username);
        $insertdata['phone'] = $phone;
        $insertdata['user_type'] = $type;
        $insertdata['work_unit'] = $work_unit;
        if ($type == 1) {
            $insertdata['headimg'] = $pic;
        } else {
            $insertdata['referee'] = $referee;
        }
        $insertdata['create_time'] = TIME;
        if ($insertdata) {
            $isexit = M('user')->where(array('phone' => $phone, 'status' => 1))->find();
            if ($isexit) {
                $this->JsonReturn('该手机号已经注册');
            }
            $id = M('user')->update($insertdata, array('openid' => user::$userInfo['openid']));
            //推荐人是否存在，存在即在推荐人数上+1
            if ($referee) {
                if ($isref) {
                    M('user')->where(array('uid' => $isref['uid']))->setInc('referee_num');
                }
            }

            if ($id) {
                $this->JsonReturn('注册成功', $id, 1);
            }
        }
        $this->JsonReturn('注册失败', 0, 1);

    }

    /* @title 修改手机号
     * @path changephone
     * @desc 修改绑定的手机号
     * @method POST
     * @needlogin TRUE
     * @param id int 用户id 是 无
     * @param phone String 手机号码 是 无
     * @param code String  验证码 是 无
     */
    public function changephone()
    {
        $id = $this->_get('id');
        $phone = $this->_get('phone');
        if (!$id || !$phone) {
            $this->JsonReturn('参数缺失');
        }
        $rs = M('user')->where(array('uid' => $id, 'status' => 1))->find();
        if ($rs['phone'] == $phone) {
            $this->JsonReturn('原手机和更换的手机号一致');
        }
        if (!$rs) {
            $this->JsonReturn('用户不存在或者已经删除');
        }
        if ($phone && !preg_match(C('system', 'regexp_phone'), $phone)) {
            $this->JsonReturn('请输入正确的手机号码');
        }

        $isexit = M('user')->where(array('phone' => $phone, 'status' => 1))->find();
        if ($isexit) {
            $this->JsonReturn('该手机号已经被绑定，请更换其他手机号');
        }
        $bool = M('user')->update(array('phone' => $phone), array('uid' => $id));
        if ($bool) {
            $this->JsonReturn('修改成功', 1, 1);
        } else {
            $this->JsonReturn('修改失败');
        }

    }

    //media_id获取图片源并上传本地服务器
    protected function _getMedia($media)
    {
        //处理图片
        T('weixin/weixin.api');
        $weixinMsgApi = new weixinMsgApi();
        if (!$media) {
            $this->JsonReturn('请上传图片');
        }
        $mediaBack = array();
        if (substr($media, -4) == ".jpg") {
            $mediaBack = $media;
        } else {
            $res = $weixinMsgApi->getMedia($media);
            if (!$res['status']) {
                $this->JsonReturn('上传不正确1');
            }
            $path = '/user/' . date('Y/md') . '/' . getRandInt(20, 0) . '.' . $res['type'];
            $pic = saveFile($path, $res['data']);
            if (!$pic['status']) {
                $this->JsonReturn('上传不正确2');
            }
            $mediaBack = $pic['url'];
        }
        return $mediaBack;
    }


}
