<?php
if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description 青柠-个人中心相关接口
 * @name  liveAction
 * @action  home
 * @author xufg
 */
class homeAction extends commonAction
{
    function __construct()
    {
        parent::__construct();
        //校验来源agent
    }

    /* @title 注册
     * @path register
     * @desc 手机号注册
     * @method POST
     * @needlogin false
     * @param phone string 手机号码 true
     * @param code string 验证码 true
     * @field token token
     * @notice
     */
    public function register()
    {
        $phone = $this->_post('phone');
        $code = $this->_post('code');
        if (empty($code)) {
            $this->JsonReturn('请先输入验证码');
        }
        if (!preg_match("/^1[0123456789]{1}\d{9}$/", $phone)) {
            $this->JsonReturn('请输入正确的手机号');
        }
        //手机号是否存在
        $isHave = M('account')->where(['phone' => $phone])->find();
        if ($isHave) {
            $token = D('account')->getUserToken($isHave['id']);
            $this->JsonReturn('该手机号码已经注册啦', ['isPerson' => $isHave['nike_name'] ? 1 : 0,'token'=>$token,'uid'=>$isHave['id']], 1);
        }
        //验证验证码
        $lastCode = M('check_code')->where(['phone' => $phone, 'status' => 1])->order('create_time desc')->find();
        if (empty($lastCode)) {
            $this->JsonReturn('请先获取验证码');
        }
        //判断验证码是否过期（15*60s）
        if (time() + 15 * 60 <= $lastCode['over_time']) {
            $this->JsonReturn('验证码已过期');
        }
        if ($code != md5($lastCode['code'])) {
            $this->JsonReturn('验证码有误');
        }
        $bool = M('account')->insert(['phone' => $phone, 'user_name' => $phone, 'create_time' => time()]);
        if ($bool) {
            M('check_code')->where(['phone' => $phone])->update(['status' => 0]);
            $token = D('account')->getUserToken($bool);
            $this->JsonReturn('注册成功', ['token' => $token, 'uid' => $bool], 1);
        }
        $this->JsonReturn('验证码有误');
    }

    /* @title 获取验证码
     * @path getVCode
     * @desc 获取验证码
     * @method GET
     * @needlogin false
     * @param phone string 手机号码 true
     * @field code 验证码
     * @notice
     */
    public function getVcode()
    {
        $phone = $this->_get('phone');
        if (!preg_match("/^1[0123456789]{1}\d{9}$/", $phone)) {
            $this->JsonReturn('请输入正确的手机号');
        }
        $code = '9527';
        $content = '消息内容';
        M('check_code')->insert(['phone' => $phone, 'code' => $code, 'content' => $content, 'over_time' => TIME + 15 * 60, 'create_time' => TIME]);
        $this->JsonReturn('获取验证码成功', ['code' => $code], 1);
    }

    /* @title 初始化个人信息
     * @path initMsg
     * @desc 个人信息编辑
     * @method POST
     * @needlogin false
     * @param uid int uid true
     * @param token string token true
     * @param avatar string 头像（base64） false
     * @param back_img string 背景图（base64 false
     * @param nike_name string 昵称 false
     * @param signature string 个性签名 false
     * @param tag array 标签 false
     * @param sex int 性别：1-男，2-女，0-保密 false
     * @param birthday int 出生年月日 false
     * @param sex_preference int 性别偏好：0-不限，1-男，2-女 false
     * @notice
     */
    public function initMsg()
    {
        $uid = $this->_post('uid', 0);
        $token = $this->_post('token');
        if (empty($uid) || empty($token)) {
            $this->JsonReturn('缺少参数');
        }
        $this->validate_token($token);
        $avatar = $this->_post('avatar', '/');
        $back_img = $this->_post('back_img', '/');
        $nike_name = $this->_post('nike_name', '匿名用户');
        $signature = $this->_post('signature', '这个人很懒，什么也没有留下。');
        $tag = $this->_post('tag', []);
        $sex = $this->_post('sex', 0);
        $birthday = $this->_post('birthday', 0);
        $sex_preference = $this->_post('sex_preference', 0);
        if ($avatar != '/') {
            $avatar = $this->pictest($avatar);
        }
        if ($back_img != '/') {
            $back_img = $this->pictest($back_img);
        }
        if (count($tag) < 5) {
            $this->JsonReturn('至少选择五个标签哦~');
        }
        $bool = M('account')
            ->where(['id' => $uid])
            ->update(['avatar' => $avatar, 'back_img' => $back_img, 'nike_name' => $nike_name, 'signature' => $signature, 'tag_arr' => implode(',', $tag), 'sex' => $sex, 'birthday' => $birthday ? strtotime($birthday) : 0, 'sex_preference' => $sex_preference]);
        if ($bool) {
            $this->JsonReturn('成功', [], 1);
        }
        $this->JsonReturn('信息保存失败', [], 1);
    }

    /* @title 获取便签库信息
     * @path getLabel
     * @desc 获取便签库信息
     * @method GET
     * @needlogin false
     * @field id ID
     * @field name 名称
     * @notice
     */
    public function getLabel()
    {
        $data = [];
        $rs0 = M('tag_library')->where(['pid' => 0, 'status' => 1])->order('px desc,id asc')->select();
        $rs1 = M('tag_library')->where(['pid' => ['>', 0], 'status' => 1])->order('px desc,id asc')->select();
        if ($rs0) {
            foreach ($rs0 as $val) {
                $child['id'] = $val['id'];
                $child['name'] = $val['name'];
                $child['child'] = [];
                if ($rs1) {
                    foreach ($rs1 as $val1) {
                        $child1['id'] = $val1['id'];
                        $child1['name'] = $val1['name'];
                        $child['child'][] = $child1;
                    }
                }
                $data[] = $child;
            }
        }
        $this->JsonReturn('查询成功', $data, 1);
    }


    //验证token
    public function validate_token($token)
    {
        if (empty($token)) {
            $this->JsonReturn('无效token', null, 3);
        }
        $bool = M('account')->where(array('token' => $token))->find();
        if (!$bool) {
            $this->JsonReturn('token失效，请重新登陆', null, 3);
        }
    }

    //base64图片转换
    public function pictest($image)
    {
        $pic = $image;
        $matches = array();
        if ($pic && preg_match('/data:([^;]*);base64,(.*)/', $pic, $matches)) {
            //保存的文件名及路径
            $img = base64_decode($matches[2]);
            $size = strlen($img);
            if (!$size) {
                $this->JsonReturn('图片上传失败');
            }
            if ($size > (intval(C('upload', 'maxsize')) * 1024 * 1024)) {
                $this->JsonReturn('上传图片过大');
            }
            $randint = getRandInt(20, 0);
            $upInfo = saveFile('/uqun/' . date('Y/md') . '/' . $randint . '.jpg', $img);
            if ($upInfo['status']) {
                return $upInfo['url'];
            } else {
                return '';
            }
        } else {
            return '';
        }
    }


}