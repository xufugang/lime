<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'index', //页面标示
    'pagename' => 'graphic', //当前页面名称
    'keywords' => '', //关键字
    'description' => '', //描述
    'mycss' => array(), //加载的css样式表
    'myjs' => array(), //加载的js脚本
    'footerjs' => array(),
    'head' => true,
    'copyright' => true
);
include getTpl('header', 'public');
?>
    <nav class="breadcrumb"><a
                class="btn btn-success radius r mr-20" style="line-height:1.6em;margin-top:3px"
                href="javascript:location.replace(location.href);" title="刷新"><i class="Hui-iconfont">&#xe68f;</i></a>
    </nav>

    <!--主体 开始-->
    <div class="pd-20 main-content md-20">
        <div class="cl pd-5 bg-1 bk-gray">
        <span class="l">
        <!--添加链接-->
        <a href="<?php echo U('live/graphic_add',array('live_id'=>$live_id));?>" class="btn btn-success radius openWinPop"><i class="Hui-iconfont">&#xe600;</i> 添加</a>
        </span>
        </div>
        <div class="mt-20">
            <table class="table table-border table-bordered table-hover table-bg table-sort">
                <thead>
                <tr class="text-c">
                    <th align="center">序号</th>
                    <th align="center">内容</th>
                    <th align="center">图片</th>
                    <th align="center">发布人</th>
                    <th align="center">发布时间</th>
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
                            <td><?php echo $val['content']; ?></td>
                            <td><?php $piclist = json_decode($val['pic'],true); if($piclist){ ?><img src="<?php  echo $piclist? getImgUrl($piclist[0]) : '#'; ?>" width="50px" height="50px"><?php }?></td>
                            <td><?php echo $val['name']; ?></td>
                            <td><?php echo outTime($val['create_time']); ?></td>
                            <td class="td-manage manage-tools">
                                <a class="ml-5 set_top" rel="<?php echo $val['id']; ?>"
                                   data-type="<?php echo $val['is_top'] ? 1 : 2; ?>" href="javascript:;"
                                   title="置顶设置"><?php echo $val['is_top'] ? '取消置顶' : '置顶'; ?></a>
                                <a class="ml-5 set_up" rel="<?php echo $val['id']; ?>" href="javascript:;"
                                   title="上移设置">上移</a>
                                <a class="ml-5 set_down" rel="<?php echo $val['id']; ?>" href="javascript:;"
                                   title="下移设置">下移</a>
                                <a class="act-edit ml-20 openWinPop"
                                   href="<?php echo U('live/graphic_edit', array('id' => $val['id'])); ?>">编辑</a>
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
            //删除
            $('.delete-info').click(function () {
                var id = $(this).attr('rel');
                var index = layer.confirm('确认删除吗?', {btn: ['确认', '取消']}, function () {
                    Msg.loading();
                    $.post('<?php echo U('live/del_img');?>', {"id": id}, function (result) {
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
            //上移
            $('.set_up').click(function () {
                var id = $(this).attr('rel');
                var index = layer.confirm('是否上移改内容?', {btn: ['确认', '取消']}, function () {
                    Msg.loading();
                    $.post('<?php echo U('live/up_down');?>', {"id": id, "cz": 1}, function (result) {
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
            //下移
            $('.set_down').click(function () {
                var id = $(this).attr('rel');
                var index = layer.confirm('是否下移改内容?', {btn: ['确认', '取消']}, function () {
                    Msg.loading();
                    $.post('<?php echo U('live/up_down');?>', {"id": id, 'cz': 2}, function (result) {
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
                    $.post('<?php echo U('live/set_top');?>', {"id": id, "type": type}, function (result) {
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
    <!--主体 结束-->
<?php include getTpl('footer', 'public'); ?>