<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'index', //页面标示
    'pagename' => '管理', //当前页面名称
    'keywords' => '', //关键字
    'description' => '', //描述
    'mycss' => array(), //加载的css样式表
    'myjs' => array('admin/calendar'), //加载的js脚本
    'footerjs' => array(),
    'head' => true,
    'copyright' => true
);
include getTpl('header', 'public');
?>
    <div class="pd-20 main-content md-20">
        <div class="cl pd-5 bg-1 bk-gray">
        <span class="l">
        <!--添加链接-->
            <div class="btn-group">
                <a class="btn btn-<?php echo $type == 0 ? 'primary' : 'default'; ?> radius"
                   href="<?php echo U('live/comment', array('type' => 0, 'id' => $live_id)); ?>">全部</a>
                <a class="btn btn-<?php echo $type == 1 ? 'primary' : 'default'; ?> radius"
                   href="<?php echo U('live/comment', array('type' => 1, 'id' => $live_id)); ?>">未审核</a>
                <a class="btn btn-<?php echo $type == 2 ? 'primary' : 'default'; ?> radius"
                   href="<?php echo U('live/comment', array('type' => 2, 'id' => $live_id)); ?>">已审核</a>
            </div>
            <!--添加链接-->
            <!--分类组-->
        </span>
            <span class="r">
        <div class="text-l">
            <form id="form1" name="form1" method="post"
                  action="<?php echo U('live/comment', array('id' => $live_id)); ?>">
                <input type="text" class="input-text" style="width:250px" placeholder="输入关键字..." id="q" name="q">
                <button type="submit" class="btn btn-success radius" id="" name=""><i
                            class="Hui-iconfont">&#xe665;</i> 搜索</button>
            </form>
        </div>
            </span>
        </div>

        <div class="mt-20">
            <table class="table table-border table-bordered table-hover table-bg table-sort">
                <thead>
                <tr class="text-c">
                    <th align="center">序号</th>
                    <th align="center">头像</th>
                    <th align="center">评论人</th>
                    <th align="center">评论内容</th>
                    <th align="center">发布时间</th>
                    <th align="center">审核状态</th>
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
                            <td><img src="<?php echo $val['head_img'] ? getImgUrl($val['head_img']) : '#'; ?>" width="50px" height="50px"></td>
                            <td><?php echo $val['name']; ?></td>
                            <td><?php echo $val['content']; ?></td>
                            <td><?php echo outTime($val['create_time'], 3); ?></td>
                            <td><?php if ($val['examin_status']) {
                                    echo $val['examin_status'] == 1 ? '<span class="label label-success radius">通过</span>' : '<span class="label label-warning radius">拒绝</span>';
                                } else {
                                    echo '<span class="label label-default radius">未审核</span>';
                                } ?></td>
                            <td class="td-manage manage-tools">
                                <a class="ml-5 set_top" rel="<?php echo $val['id']; ?>"
                                   data-type="<?php echo $val['is_top'] ? 1 : 2; ?>" href="javascript:;"
                                   title="置顶设置"><?php echo $val['is_top'] ? '取消置顶' : '置顶'; ?></a>
                                <a class="act-deal ml-10" rel="<?php echo $val['id']; ?>"
                                   href="javascript:;">审核</a>
                                <a class="ml-10 delete-info" rel="<?php echo $val['id']; ?>" href="javascript:;"
                                   title="删除">删除</a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="20" align="center">
                            <div class="text-c">暂无数据</div>
                        </td>
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
            var deleteUrl = '<?php echo U('live/comment_del');?>';
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
            $('.act-deal').click(function () {
                var id = $(this).attr('rel');
                var index = layer.open({
                    content: '请选择操作'
                    , btn: ['通过', '拒绝', '关闭']
                    , yes: function (index, layero) {
                        Msg.loading();
                        $.post('<?php echo U('live/comment_exam');?>', {"id": id, "do": 1}, function (res) {
                            Msg.ok('操作成功');
                            if (res.status == 1) {
                                window.location.reload(true);
                            } else {
                                Msg.error(res.info);
                            }
                        }, 'json');
                        layer.close(index);
                    }, btn2: function (index, layero) {
                        Msg.loading();
                        $.post('<?php echo U('live/comment_exam');?>', {"id": id, "do": 2}, function (res) {
                            Msg.hide();
                            if (res.status == 1) {
                                window.location.reload(true);
                            } else {
                                Msg.error(res.info);
                            }
                        }, 'json');
                        layer.close(index);
                    }, btn3: function (index, layero) {
                        layer.close(index);
                        return false;
                    }
                    , cancel: function () {
                        layer.close(index);
                        return false;
                    }
                });
            });
            //置顶
            $('.set_top').click(function () {
                var id = $(this).attr('rel');
                var type = $(this).data('type');
                var tips = '';
                if(type==1){
                    tips='是否取消置顶?';
                }else {
                    tips ='是否置顶?';
                }
                var index = layer.confirm(tips, {btn: ['确认', '取消']}, function () {
                    Msg.loading();
                    $.post('<?php echo U('live/comment_top');?>', {"id": id, "type": type}, function (result) {
                        Msg.hide();
                        if (result.status == 1) {
                            Msg.ok('操作成功');
                            window.location.reload(true);
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
<?php
include getTpl('footer', 'public');
?>