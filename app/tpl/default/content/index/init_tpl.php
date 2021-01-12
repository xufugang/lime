<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'init', //页面标示
    'pagename' => '', //当前页面名称
    'keywords' => '', //关键字
    'description' => '', //描述
    'mycss' => array('admin/lib/live/style'), //加载的css样式表
    'myjs' => array(), //加载的js脚本
    'footerjs' => array(),
    'head' => true,
    'copyright' => true
);
include getTpl('header', 'public');
?>
    <!--主体 开始-->
    <div class=" main-content md-20 pl-20 pr-20">
        <div class="ac-filter-section">
            <span class="l">
                <!--添加链接-->
                    <a href="<?php echo U('admin/add'); ?>" data-width="800" data-height="600"
                       class="btn btn-success radius openWinPop">添加账户</a>
                <!--添加链接-->
            </span>
            <span class="r">
                <!--搜索框-->
                <div class="text-l">
                    <form id="form1" name="form1" method="get" action="<?php echo U('index/init'); ?>">
                          <span class="select-box radius" style="width:150px;">
                            <select class="select" name="type" id="type" size="1">
                            <option value="0" <?php if (!$type) {
                                echo 'selected';
                            } ?>>全部</option>
                                <?php
                                foreach ($setting['user_type'] as $key => $val) {
                                    ?>
                                    <option value="<?php echo($key); ?>" <?php if (($key) == $type) {
                                        echo 'selected';
                                    } ?>><?php echo $val; ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                          </span>
                        <input type="text" class="input-text radius" style="width:250px" placeholder="输入关键字..." id="q"
                               name="q">
                        <button type="submit" class="btn btn-success radius" id="" name=""><i
                                    class="Hui-iconfont">&#xe665;</i> 搜索
                        </button>
                        </form>
                    </div>
                <!--搜索框-->
            </span>
        </div>
        <div class="mt-20">
            <table class="table table-border table-no-bordered table-hover table-bg table-sort">
                <thead>
                <tr class="text-c">
                    <th align="center">序号</th>
                    <th align="center">用户名</th>
                    <th align="center">登陆次数</th>
                    <th align="center">最后登陆的ip</th>
                    <th align="center">最后登陆时间</th>
                    <th align="center">有效截止时间</th>
                    <th align="center">组别</th>
                    <th align="center">状态</th>
                    <th align="center">操作</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if ($rs) {
                    ?>
                    <?php
                    foreach ($rs as $val) {
                        ?>
                        <tr class="text-c" id="list_detail_<?php echo $val['id']; ?>">
                            <td><?php echo $val['id']; ?></td>
                            <td><?php echo $val['name']; ?></td>
                            <td><?php echo $val['login_num']; ?></td>
                            <td><?php echo $val['login_ip']; ?></td>
                            <td><?php echo $val['last_login'] ? outTime($val['last_login']) : '-'; ?></td>
                            <td><?php echo $val['validity_time'] ? outTime($val['validity_time'], 2) : '长期有效'; ?></td>
                            <td><?php echo $val['group_id'] == 1 ? '<span class="label label-warning radius">管理员</span>' : '<span class="label label-primary radius">直播员</span>'; ?></td>
                            <td><?php echo $val['status'] == 1 ? '<span class="label label-success radius">正常</span>' : '<span class="label label-danger radius">冻结</span>'; ?></td>
                            <td class="td-manage manage-tools">
                                <a class="act-edit ml-20 openWinPop" data-height="500"
                                   href="<?php echo U('admin/edit', array('id' => $val['id'])); ?>">编辑</a>
                                <a class="ml-5 delete-info" rel="<?php echo $val['id']; ?>" href="javascript:;"
                                   title="删除">删除</a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="20" class="text-c">暂无数据</td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <div><?php echo page($total, $p, '', 20, 'p'); ?></div>
        </div>
    </div>
    <script>
        $(function () {
            var deleteUrl = '<?php echo U('admin/del');?>';
            $('.delete-info').click(function () {
                var id = $(this).attr('rel');
                var index = layer.confirm('确认删除吗?', {btn: ['确认', '取消']}, function () {
                    Msg.loading();
                    $.post(deleteUrl, {"id": id}, function (result) {
                        Msg.hide();
                        if (result.status == 1) {
                            Msg.ok('操作成功');
                            $("#list_detail_" + id).fadeOut();
                        } else {
                            Msg.error(result.info);
                        }
                    }, 'json');
                    layer.close(index);
                }, function () {

                });
                return false;
            });
        });

    </script>
    <!--主体 结束-->
<?php include getTpl('footer', 'public'); ?>