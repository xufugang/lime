<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of adminAction
 *
 * @author xufg
 */
class liveAction extends commonAction
{

    public function __construct()
    {
        parent::__construct();
        parent::_checkLogin();
        parent::_checkValidity();
    }

    //测试
    public  function test(){
        $this->display();
    }

    public function index()
    {
        $p = $this->_getid('p', 1);
        $q = $this->_get('q');
        $live_status = $this->_get('user_type');
        $where = 'a.status=1';
        if (trim($q)) {
            $where = $where . " and a.title like '%" . trim($q) . "%'";
        }
        if ($live_status) {
            $where = $where . ' and a.live_status=' . $live_status;
        }
        $rs = M('live')->query('select a.*,b.name,d.name as liver from __TABLE__ as a LEFT JOIN __PRE__channel as b on a.channel_id=b.id LEFT JOIN  __PRE__admin as d on a.liver_id=d.id WHERE ' . $where . ' order by create_time desc limit ' . (($p - 1) * 20) . ',20');
        $row = M('live')->query('select id from __TABLE__ as a  WHERE ' . $where);
        $this->assign(array('rs' => $rs, 'total' => count($row), 'live_status' => $live_status));
        $this->display();
    }

    //直播详情
    public function detail()
    {
        $id = $this->_getid('id');
        if (!$id) {
            $this->JsonReturn('参数缺失');
        }
        $rs = M('live')->where(array('id' => $id, 'status' => 1))->find();
        if (!$rs) {
            $this->JsonReturn('直播不存在或者已经删除');
        }
        $channel = M('channel')->where(array('id' => $rs['channel_id']))->find();
        $rs['channel'] = $channel ? $channel['name'] : '暂无';
        //直播员
        $liver = M('admin')->where(array('id' => $rs['liver_id']))->find();
        $rs['liver'] = $liver ? $liver['name'] : '暂无';
        //嘉宾
        $guest = M('admin')->where(array('id' => explode(',', $rs['guest_list'])))->findAll();
        $rs['guest'] = $guest ? implode(',', array_column($guest, 'name')) : '暂无';
        $this->assign(array('rs' => $rs));
        $this->display();
    }

    //添加发起人
    public function add()
    {
        //城市列表
        $city = M('city')->where(array('pid' => 0))->findAll(false);
        //直播员
        $liver = M('admin')->field('id,name')->where(array('status' => 1, 'group_id' => 2))->findAll(false);
        $this->assign(array('city' => $city, 'liver' => $liver));
        $this->display();
    }

    //生成推流地址
    public function get_push_url()
    {
        $id = $this->_postid('id');
        if (!$id) {
            $this->JsonReturn('参数缺失', null, 1);
        }
        $channel = M('channel')->where(array('status' => 1, 'id' => $id))->getField('name');
        $time = time() + 24 * 60 * 60;
        $url = D('push')->getPushUrl($this->setting['biZid'], $channel, $this->setting['security_key'], date("Y-m-d H:i:i", $time));
        $this->JsonReturn('ok', $url, 1);
    }

    //根据直播时间获得可用的直播频道
    public function get_channel()
    {
        $time = $this->_post('time');
        if (!$time) {
            $this->JsonReturn('参数缺失');
        }
        $date_time = explode("-", $time);
        if (isset($date_time[1])) {
            if (time() > strtotime($date_time[1])) {
                $this->JsonReturn('直播结束时间必须大于当前时间');
            }
        }
        $wheres = array();
        $wheres['AND'] = array('status' => 1, array('OR' => array('start_time[<>]' => array(strtotime($date_time[0]), strtotime($date_time[1])), 'end_time[<>]' => array(strtotime($date_time[0]), strtotime($date_time[1]) + 24 * 60 * 60))), 'live_status' => array(1, 2));
        $rs = M('live')->where($wheres)->group('channel_id')->findAll();
        $channel = M('channel')->field('id,name')->where(array('status' => 1))->findAll();
        if ($rs) {
            $rs = array_column($rs, 'channel_id');
            foreach ($channel as $key => $val) {
                if (in_array($val['id'], $rs)) {
                    unset($channel[$key]);
                }
            }
        }
        if (empty($channel)) {
            $this->JsonReturn('该时间段无可用频道');
        }
        $channel = array_values($channel);//重置数组索引，从0开始
        $data['innerhtml'] = '';
        $data['rtmp_url'] = '';
        if ($channel) {
            foreach ($channel as $k => $v) {
                if ($k) {
                    $data['innerhtml'] = $data['innerhtml'] . '<option value="' . $v['id'] . '">' . $v['name'] . '</option>';
                } else {
                    $data['innerhtml'] = $data['innerhtml'] . '<option value="' . $v['id'] . '" selected>' . $v['name'] . '</option>';
                    $time = time() + 24 * 60 * 60;
                    $url = D('push')->getPushUrl($this->setting['biZid'], $v['name'], $this->setting['security_key'], date("Y-m-d H:i:i", $time));
                    $data['rtmp_url'] = $url;
                }
            }
        }
        $this->JsonReturn('ok', $data, 1);
    }

    //新增提交
    public function post()
    {
        $title = $this->_post('title');
        if (!$title) {
            $this->JsonReturn('标题不能为空');
        }
        $type = $this->_postid('type', 1);
        $channel_id = $this->_post('channel_id');

        $city = $this->_post('city');
        if ((!$city) || $city < 0) {
            $this->JsonReturn('请选择直播城市');
        }
        $liver = $this->_post('liver');
        if (!$liver) {
            $this->JsonReturn('该城市下没有直播员');
        }
        //直播时间处理
        $rtime = $this->_post('endtime');
        $guest = $this->_post('guest');
        if ($guest) {
            if (in_array($liver, $guest)) {
                $this->JsonReturn('嘉宾名单中不要包含直播员');
            }
        }
        $start = 0;
        $end = 0;
        if (!empty($rtime)) {
            $rtime = explode('-', $rtime);
            $start = $rtime[0];
            if (!$start) {
                $this->JsonReturn('请选择直播开始时间');
            }
            $end = $rtime[1];
            if (!$end) {
                $this->JsonReturn('请选择直播结束时间');
            }
            if (strtotime($start) > strtotime($end)) {
                $this->JsonReturn('直播结束时间必须大于开始时间');
            }
            if (time() > strtotime($end)) {
                $this->JsonReturn('直播结束时间必须大于当前时间');
            }
        } else {
            $this->JsonReturn('请选择直播时间');
        }
        if (!$channel_id) {
            $this->JsonReturn('该时间段无可用频道');
        }

        //设定人数必须是整数
        $view_num = $this->_post('view_num');
        //数据组装
        $insertData = array();
        $insertData['title'] = $title;
        $insertData['type'] = $type;
        $insertData['channel_id'] = $channel_id;
        $insertData['city_id'] = $city;
        $insertData['liver_id'] = $liver;
        $insertData['guest_list'] = $guest ? implode(',', $guest) : '';
        $insertData['start_time'] = strtotime($start);
        $insertData['end_time'] = strtotime($end);
        //非必填项
        $insertData['is_comment'] = $this->_post('is_comment') ? $this->_post('is_comment') - 1 : '';
        $insertData['is_barrage'] = $this->_post('is_barrage') ? $this->_post('is_barrage') - 1 : '';
        $insertData['top_pic'] = $this->_post('top_pic');
        $insertData['change_pic'] = $this->_post('change_pic');
        $insertData['introduce'] = parent::_postContent('introduce');
        $insertData['view_num'] = $view_num ? $view_num : 0;
        //生成推流地址
        $url = D('push')->getPushUrl($this->setting['biZid'], $channel_id, $this->setting['security_key'], date("Y-m-d H:i:i", strtotime($end) + 24 * 60 * 60));
        $insertData['rtmp_url'] = $url;
        //获取播放地址
        $channel = M('channel')->where(array('status' => 1, 'id' => $channel_id))->getField('name');
        $channel_list = D('push')->getPlayUrl($this->setting['biZid'], $channel);
        $insertData['play_url'] = isset($channel_list[2]) ? $channel_list[2] : '';
        $insertData['share_title'] = $this->_post('share_title');
        $insertData['share_content'] = $this->_post('share_content');
        $insertData['share_pic'] = $this->_post('share_pic');
        $insertData['create_time'] = TIME;
        $insertData['update_time'] = TIME;
        if ($insertData) {
            $bool = M('live')->insert($insertData);
            if ($bool) {
                $front_url = "//" . $_SERVER['HTTP_HOST'] . '/live/weixin/index/index?id=' . $bool;
                M('live')->update(array('front_url' => $front_url), array('id' => $bool));
                $this->JsonReturn('添加成功', null, 1);
            }
        }
        $this->JsonReturn('添加失败');
    }

    //编辑
    public function edit()
    {
        $id = $this->_get('id');
        if (!$id) {
            $this->JsonReturn('参数缺失');
        }
        $rs = M('live')->where(array('status' => 1, 'id' => $id))->find();
        if (!$rs) {
            $this->JsonReturn('直播不存在或已删除');
        }
        //当前时间可用频道列表
        $wheres = array();
        $wheres['AND'] = array('status' => 1, array('OR' => array('start_time[<>]' => array($rs['start_time'], $rs['end_time']), 'end_time[<>]' => array($rs['start_time'], $rs['end_time']))), 'live_status' => array(1, 2));
        $have = M('live')->where($wheres)->group('channel_id')->findAll();
        $channel = M('channel')->field('id,name')->where(array('status' => 1))->findAll();
        if ($have) {
            $have = array_column($have, 'channel_id');
            foreach ($channel as $key => $val) {
                if (in_array($val['id'], $have) && $rs['channel_id'] != $val['id']) {
                    unset($channel[$key]);
                }
            }
        }

        //所有城市信息
        $city = M('city')->where(array('pid' => 0))->findAll(false);
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
        //直播员
        $where['AND'] = array('group_id' => 2, 'status' => 1, 'OR' => array('validity_time' => 0, 'validity_time[>=]' => TIME), 'city_id' => $rs['city_id']);
        $liver = M('admin')->field('id,name')->where($where)->findAll(false);

        $this->assign(array('rs' => $rs, 'city' => $city, 'channel' => $channel, 'liver' => $liver, 'p' => $p['pid'], 'clist' => $cityArr));
        $this->display();
    }

    //保存修改
    public function save()
    {
        $id = $this->_postid('id');
        if (!$id) {
            $this->JsonReturn('参数缺失');
        }
        $rs = M('live')->where(array('id' => $id, 'status' => 1))->find();
        if (!$rs) {
            $this->JsonReturn('直播不存在或者已经删除');
        }
        $title = $this->_post('title');
        if (!$title) {
            $this->JsonReturn('标题不能为空');
        }
        $type = $this->_postid('type', 1);
        $channel_id = $this->_post('channel_id');

        $city = $this->_post('city');
        if ((!$city) || ($city < 0)) {
            $this->JsonReturn('城市不能为空');
        }
        $liver = $this->_post('liver');
        if (!$liver) {
            $this->JsonReturn('该城市下没有直播员');
        }
        //嘉宾
        $guest = $this->_post('guest');
        if ($guest) {
            if (in_array($liver, $guest)) {
                $this->JsonReturn('嘉宾名单中不要包含直播员');
            }
        }
        //直播时间处理
        $rtime = $this->_post('endtime');
        $start = 0;
        $end = 0;
        if (!empty($rtime)) {
            $rtime = explode('-', $rtime);
            $start = $rtime[0];
            if (!$start) {
                $this->JsonReturn('请选择直播开始时间');
            }
            $end = $rtime[1];
            if (!$end) {
                $this->JsonReturn('请选择直播结束时间');
            }
            if (strtotime($start) > strtotime($end)) {
                $this->JsonReturn('直播结束时间必须大于开始时间');
            }
            if (time() > strtotime($end)) {
                $this->JsonReturn('直播结束时间必须大于当前时间');
            }
        } else {
            $this->JsonReturn('请选择直播时间');
        }

        if (!$channel_id) {
            $this->JsonReturn('请选择直播频道');
        }
        //设定人数必须是整数
        $view_num = $this->_post('view_num');
        if ($view_num) {
            if (!floor($view_num) == $view_num) {
                $this->JsonReturn('设定人数必须是整数');
            }
        }
        //数据组装
        $insertData = array();
        $insertData['title'] = $title;
        $insertData['type'] = $type;
        $insertData['channel_id'] = $channel_id;
        $insertData['city_id'] = $city;
        $insertData['liver_id'] = $liver;
        $insertData['guest_list'] = $guest ? implode(',', $guest) : '';
        $insertData['start_time'] = strtotime($start);
        $insertData['end_time'] = strtotime($end);
        //非必填项
        $insertData['is_comment'] = $this->_post('is_comment') ? $this->_post('is_comment') - 1 : '';
        $insertData['is_barrage'] = $this->_post('is_barrage') ? $this->_post('is_barrage') - 1 : '';
        $insertData['top_pic'] = $this->_post('top_pic');
        $insertData['change_pic'] = $this->_post('change_pic');
        $insertData['introduce'] = parent::_postContent('introduce');
        $insertData['view_num'] = $view_num ? $view_num : 0;
        if ($channel_id != $rs['channel_id']) {
            $url = D('push')->getPushUrl($this->setting['biZid'], $channel_id, $this->setting['security_key'], date("Y-m-d H:i:i", strtotime($end) + 24 * 60 * 60));
            $insertData['rtmp_url'] = $url;
        }
        if ($rs['rtmp_url'] != $this->_post('rtmp_url')) {
            $channel = M('channel')->where(array('status' => 1, 'id' => $channel_id))->getField('name');
            $channel_list = D('push')->getPlayUrl($this->setting['biZid'], $channel);
            $insertData['play_url'] = isset($channel_list[2]) ? $channel_list[2] : '';
        }
        $insertData['share_title'] = $this->_post('share_title');
        $insertData['share_content'] = $this->_post('share_content');
        $insertData['share_pic'] = $this->_post('share_pic');
        $insertData['update_time'] = TIME;
        if ($insertData) {
            $bool = M('live')->update($insertData, array('id' => $id));
            if ($bool) {
                $this->JsonReturn('编辑成功', null, 1);
            }
        }
        $this->JsonReturn('编辑失败');

    }

    //删除
    public function del()
    {
        $id = $this->_post('id');
        if (!$id) {
            $this->JsonReturn('参数缺失');
        }
        $rs = M('live')->where(array('id' => $id, 'status' => 1))->find();
        if (!$rs) {
            $this->JsonReturn('用户不存在或者已经删除');
        }
        $bool = M('live')->update(array('status' => 0), array('id' => $id));
        if ($bool) {
            $this->JsonReturn('删除成功', $bool, 1);
        }
        $this->JsonReturn('删除失败');

    }

    //图文管理
    public function graphic()
    {
        $p = $this->_getid('p', 1);
        $id = $this->_getid('id');
        if (!$id) {
            $this->JsonReturn('参数缺失');
        }
        $isHave = M('live')->where(array('status' => 1, 'id' => $id))->find();
        if (!$isHave) {
            $this->JsonReturn('该直播不存在或已删除');
        }
        $rs = M('imgtext')->where(array('live_id' => $id, 'status' => 1))->order('is_top desc,px desc')->page($p)->findAll();
        $total = M('imgtext')->getTotal();
        $this->assign(array('rs' => $rs, 'total' => $total, 'p' => $p, 'live_id' => $id));
        $this->display();
    }

    //新增图文信息
    public function graphic_add()
    {
        $id = $this->_getid('live_id');
        if (!$id) {
            $this->JsonReturn('参数缺失');
        }
        $isHave = M('live')->where(array('status' => 1, 'id' => $id))->find();
        if (!$isHave) {
            $this->JsonReturn('该直播不存在或已删除');
        }
        $liver = array();
        if ($isHave) {
            $where['AND'] = array('group_id' => 2, 'status' => 1, 'OR' => array('validity_time' => 0, 'validity_time[>=]' => TIME), 'city_id' => $isHave['city_id']);
            $liver = M('admin')->where($where)->findAll();
        }
        $this->assign(array('id' => $id, 'liver' => $liver));
        $this->display();
    }

    //图文新增提交
    public function graphic_post()
    {
        $id = $this->_postid('id');
        if (!$id) {
            $this->JsonReturn('参数缺失');
        }
        $isHave = M('live')->where(array('status' => 1, 'id' => $id))->find();
        if (!$isHave) {
            $this->JsonReturn('该直播不存在或已删除');
        }
        $live_id = $id;
        $name = $this->_post('name');
        if (!$name) {
            $this->JsonReturn('发布人不能为空');
        }
        $liver = M('admin')->where(array('id' => $name))->find();
        $introduce = $this->_post('introduce');
        $pic = $this->_post('pic');
        $insertdata = array();
        $insertdata['live_id'] = $live_id;
        $insertdata['pic'] = $pic ? json_encode($pic) : json_encode(array());
        $insertdata['name'] = $liver ? $liver['name'] : '';
        $insertdata['content'] = $introduce;
        $insertdata['create_time'] = TIME;
        $bool = M('imgtext')->insert($insertdata);
        if ($bool) {
            M('imgtext')->update(array('px' => $bool), array('id' => $bool));
            $this->JsonReturn('成功', null, 1);
        }
        $this->JsonReturn('失败');

    }

    //图文编辑
    public function graphic_edit()
    {
        $id = $this->_getid('id');
        if (!$id) {
            $this->JsonReturn('参数缺失');
        }
        $rs = M('imgtext')->where(array('id' => $id, 'status' => 1))->find();
        if (!$rs) {
            $this->JsonReturn('该图文信息不存在或已删除');
        }
        //直播人员列表
        $city = M('live')->where(array('id' => $rs['live_id'], 'status' => 1))->find();
        $liver = array();
        if ($city) {
            $where['AND'] = array('group_id' => 2, 'status' => 1, 'OR' => array('validity_time' => 0, 'validity_time[>=]' => TIME), 'city_id' => $city['city_id']);
            $liver = M('admin')->where($where)->findAll();
        }
        $this->assign(array('rs' => $rs, 'sel' => $city['city_id'], 'liver' => $liver));
        $this->display();
    }

    //图文新增保存
    public function graphic_save()
    {
        $id = $this->_postid('id');
        if (!$id) {
            $this->JsonReturn('参数缺失');
        }
        $isHave = M('imgtext')->where(array('status' => 1, 'id' => $id))->find();
        if (!$isHave) {
            $this->JsonReturn('该图文信息不存在或已删除');
        }
        $name = $this->_post('name');
        if (!$name) {
            $this->JsonReturn('发布人不能为空');
        }
        $liver = M('admin')->where(array('id' => $name))->find();
        $introduce = $this->_post('introduce');
        $pic = $this->_post('pic');
        $insertdata = array();
        $insertdata['pic'] = $pic ? json_encode($pic) : json_encode(array());
        $insertdata['name'] = $liver ? $liver['name'] : '';
        $insertdata['content'] = $introduce;
        $bool = M('imgtext')->update($insertdata, array('id' => $id));
        if ($bool) {
            $this->JsonReturn('成功', null, 1);
        }
        $this->JsonReturn('失败');

    }

    //删除图文
    public function del_img()
    {
        $id = $this->_getid('id');
        if (!$id) {
            $this->JsonReturn('参数缺失');
        }
        $rs = M('imgtext')->where(array('status' => 1, 'id' => $id))->find();
        if (!$rs) {
            $this->JsonReturn('该图文信息不存在或已删除');
        }
        $bool = M('imgtext')->update(array('status' => 0), array('id' => $id));
        if ($bool) {
            $this->JsonReturn('删除成功', null, 1);
        }
        $this->JsonReturn('失败');
    }

    //上下移动
    public function up_down()
    {
        $id = $this->_getid('id');
        $cz = $this->_getid('cz', 1);
        if (!$id) {
            $this->JsonReturn('参数缺失');
        }
        $rs = M('imgtext')->where(array('status' => 1, 'id' => $id))->find();
        if (!$rs) {
            $this->JsonReturn('该图文信息不存在或已删除');
        }
        if ($cz == 1) {
            $mall = M('imgtext')->where(array('status' => 1, 'px[>]' => $rs['px'], 'live_id' => $rs['live_id']))->order('px asc')->find();
            if (!$mall) {
                $this->JsonReturn('已经是第一条了');
            }
        } else {
            $mall = M('imgtext')->where(array('status' => 1, 'px[<]' => $rs['px'], 'live_id' => $rs['live_id']))->order('px desc')->find();
            if (!$mall) {
                $this->JsonReturn('已经是最后一条了');
            }

        }
        $tab = M('');
        try {
            $tab->begin();
            $bool1 = M('imgtext')->update(array('px' => $mall['px']), array('id' => $id));
            $bool2 = M('imgtext')->update(array('px' => $rs['px']), array('id' => $mall['id']));
            if ($bool1 && $bool2) {
                $tab->commit();
                $this->JsonReturn('ok', null, 1);
            }
        } catch (Exception $ex) {
            $tab->rollback();
        }
        $this->JsonReturn('失败');
    }

    //消息置顶
    public function set_top()
    {
        $id = $this->_postid('id');
        $type = $this->_postid('type');
        if (!$id) {
            $this->JsonReturn('参数缺失');
        }
        $isHave = M('imgtext')->where(array('status' => 1, 'id' => $id))->find();
        if (!$isHave) {
            $this->JsonReturn('该图文信息不存在或已删除');
        }
        //置顶
        if ($type == 2) {
            if ($isHave['is_top']) {
                $this->JsonReturn('已经置顶了该信息');
            }
            $tab = M('');
            try {
                $tab->begin();
                $bool1 = M('imgtext')->update(array('is_top' => 0), array('live_id' => $isHave['live_id']));
                $bool2 = M('imgtext')->update(array('is_top' => 1), array('id' => $isHave['id']));
                if ($bool1 || $bool2) {
                    $tab->commit();
                    $this->JsonReturn('成功', null, 1);
                }
            } catch (Exception $ex) {
                $tab->rollback();
                $this->JsonReturn('失败');
            }

        } else {
            if (!$isHave['is_top']) {
                $this->JsonReturn('已经取消置顶了该信息');
            }
            $bool2 = M('imgtext')->update(array('is_top' => 0), array('id' => $isHave['id']));
            if ($bool2) {
                $this->JsonReturn('成功', null, 1);
            }
            $this->JsonReturn('失败');
        }

    }

    //评论管理
    public function comment()
    {
        $id = $this->_getid('id');
        $p = $this->_getid('p', 1);
        $q = $this->_post('q');
        $type = $this->_getid('type', 0);
        if (!$id) {
            $this->JsonReturn('参数缺失');
        }
        $isHave = M('live')->where(array('id' => $id, 'status' => 1))->find();
        if (!$isHave) {
            $this->JsonReturn('该直播不存在或已删除');
        }
        $where = 'status=1';
        if (trim($q)) {
            $where = $where . " and (name like '%" . trim($q) . "%' or content like '%" . trim($q) . "%')";
        }
        if ($type) {
            $where = $where . " and examin_status=" . ($type - 1);
        }
        $rs = M('comment')->where($where)->findAll();
        $total = M('comment')->getTotal();
        $this->assign(array('rs' => $rs, 'p' => $p, 'total' => $total, 'live_id' => $id, 'type' => $type));
        $this->display();
    }

    //评论置顶
    public function comment_top()
    {
        $id = $this->_postid('id');
        $type = $this->_postid('type');
        if (!$id) {
            $this->JsonReturn('参数缺失');
        }
        $isHave = M('comment')->where(array('status' => 1, 'id' => $id))->find();
        if (!$isHave) {
            $this->JsonReturn('该评论不存在或已删除');
        }
        //置顶
        if ($type == 2) {
            if ($isHave['is_top']) {
                $this->JsonReturn('已经置顶了该信息');
            }
            $tab = M('');
            try {
                $tab->begin();
                $bool1 = M('comment')->update(array('is_top' => 0), array('live_id' => $isHave['live_id']));
                $bool2 = M('comment')->update(array('is_top' => 1, 'update_time' => TIME), array('id' => $isHave['id']));
                if ($bool1 || $bool2) {
                    $tab->commit();
                    $this->JsonReturn('成功', null, 1);
                }
            } catch (Exception $ex) {
                $tab->rollback();
                $this->JsonReturn('失败');
            }

        } else {
            if (!$isHave['is_top']) {
                $this->JsonReturn('已经取消置顶了该信息');
            }
            $bool2 = M('comment')->update(array('is_top' => 0), array('id' => $isHave['id']));
            if ($bool2) {
                $this->JsonReturn('成功', null, 1);
            }
            $this->JsonReturn('失败');
        }

    }

    //评论审核
    public function comment_exam()
    {
        $id = $this->_postid('id');
        $do = $this->_postid('do');
        if (!$id) {
            $this->JsonReturn('参数缺失');
        }
        $isHave = M('comment')->where(array('status' => 1, 'id' => $id))->find();
        if (!$isHave) {
            $this->JsonReturn('该评论不存在或已删除');
        }
        if ($do == 1) {
            if ($isHave['examin_status'] == $do) {
                $this->JsonReturn('该评论已审核通过');
            }
            $bool = M('comment')->update(array('examin_status' => $do), array('id' => $id));
        } else {
            if ($isHave['examin_status'] == $do) {
                $this->JsonReturn('该评论已审核拒绝');
            }
            $bool = M('comment')->update(array('examin_status' => $do), array('id' => $id));
        }
        if ($bool) {
            $this->JsonReturn('成功', null, 1);
        }
        $this->JsonReturn('失败');
    }

    //删除评论
    public function comment_del()
    {
        $id = $this->_getid('id');
        if (!$id) {
            $this->JsonReturn('参数缺失');
        }
        $isHave = M('comment')->where(array('id' => $id, 'status' => 1))->find();
        if (!$isHave) {
            $this->JsonReturn('该评论不存在或已删除');
        }
        $bool = M('comment')->update(array('status' => 0), array('id' => $id));
        if ($bool) {
            $this->JsonReturn('成功', null, 1);
        }
        $this->JsonReturn('失败');
    }

    //视频
    public function video()
    {
        $id = $this->_get('id');
        $type = $this->_get('type');
        if (!$id) {
            $this->JsonReturn('ID错误', null, 0);
        }
        $isHave = M('live')->where(array('id' => $id, 'status' => 1))->find();
        if (!$isHave) {
            $this->JsonReturn('该直播不存在或已删除');
        }
        $url = $isHave['play_url'];
        if ($type) {
            $url = $isHave['front_url'];
        }
        $this->assign(array('url' => $url));
        $this->display();
    }


    //获取省下面的城市，已经对应城市的直播员
    public function getcity()
    {
        $city_id = $this->_post('city_id');
        //直播员列表
        $wheres = array();
        $wheres['AND'] = array('group_id' => 2, 'status' => 1, 'city_id' => $city_id, 'OR' => array('validity_time' => 0, 'validity_time[>=]' => time()));
        $liver = M('admin')->where($wheres)->findAll(false);
        $data['liverhtml'] = '';
        $data['guesthtml'] = '';
        if ($liver) {
            foreach ($liver as $key => $val) {
                //直播员列表
                if (!$key) {
                    $data['liverhtml'] = $data['liverhtml'] . '<div class="radio-box">
                            <input type="radio" id="radio_auth_' . $val['id'] . '" name="liver"
                                   value="' . $val['id'] . '"
                                ' . "checked" . '>
                            <label for="radio_auth_' . $val['id'] . '">' . $val['name'] . '</label>
                        </div>';
                } else {
                    $data['liverhtml'] = $data['liverhtml'] . '<div class="radio-box">
                            <input type="radio" id="radio_auth_' . $val['id'] . '" name="liver"
                                   value="' . $val['id'] . '"
                                >
                            <label for="radio_auth_' . $val['id'] . '">' . $val['name'] . '</label>
                        </div>';
                }
                //嘉宾复选框
                $data['guesthtml'] = $data['guesthtml'] . '  <div class="radio-box">
                                <input type="checkbox" id="check_1_' . $val['id'] . '" name="guest[]" value="' . $val['id'] . '">
                                <label for="check_1_' . $val['id'] . '">' . $val['name'] . '</label>
                            </div>';

            }
        }
        $this->JsonReturn('ok', $data, 1);
    }


}
