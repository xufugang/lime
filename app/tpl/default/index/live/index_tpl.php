<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'index', //页面标示
    'pagename' => '', //当前页面名称
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
    <!--主体 开始-->
    <div class="pd-20 main-content md-20">
        <div class="cl pd-5 bg-1 bk-gray">
    <span class="l">
	<!--添加链接-->
          <a data-href="<?php echo U('live/add'); ?>" data-width="900" data-height="500"
             class="btn btn-success radius openWinFull" data-title="添加直播" href="javascript:;"><i class="Hui-iconfont">&#xe600;</i>&nbsp;添加直播</a>
        <!--添加链接-->
    </span>
            <span class="r">
	<!--搜索框-->
	<div class="text-l">
		<form id="form1" name="form1" method="get" action="<?php echo U('live/index'); ?>">
            <span class="select-box" style="width:100px;">
			<select class="select" name="user_type" id="user_type" size="1">
             <option value="0" <?php if (!$live_status) {
                 echo 'selected';
             } ?>>全部</option>
                <?php
                foreach ($setting['live_status'] as $key => $val) {
                    ?>
                    <option value="<?php echo $key; ?>" <?php if ($key == $live_status) {
                        echo 'selected';
                    } ?>><?php echo $val; ?></option>
                    <?php
                }
                ?>
			</select>
			</span>
		 <input type="text" class="input-text" style="width:250px" placeholder="输入关键字..." id="q" name="q">
		 <button type="submit" class="btn btn-success radius" id="" name=""><i
                     class="Hui-iconfont">&#xe665;</i> 搜索</button>
		</form>
	</div>
                <!--搜索框-->
	</span>
        </div>
        <div class="mt-20">
            <table class="table table-border table-bordered table-hover table-bg table-sort">
                <thead>
                <tr class="text-c">
                    <th align="center">序号</th>
                    <th align="center">标题</th>
                    <th align="center">频道号</th>
                    <th align="center">直播员</th>
                    <th align="center">直播类型</th>
                    <th align="center">直播起止时间</th>
                    <th align="center">状态</th>
                    <th align="center">图文管理</th>
                    <th align="center">评论</th>
                    <th align="center">观看地址</th>
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
                            <td><?php echo $val['title']; ?></td>
                            <td><?php echo $val['name']; ?></a></td>
                            <td><?php echo $val['liver']; ?></td>
                            <td><?php echo $val['type']==1?'视频':($val['type']==2?'图文':'视频+图文'); ?></a></td>
                            <td><?php echo date("Y-m-d H:i", $val['start_time']) . '-' . date("Y-m-d H:i", $val['end_time']); ?></td>
                            <td><?php echo $val['live_status'] == 1 ? '<span class="label label-default radius">未开始</span>' : ($val['live_status'] == 2 ? '<span class="label label-success radius">直播中</span>' : '<span class="label label-warning radius">已结束</span>'); ?></td>
                            <td> <?php if ($val['type'] > 1) { ?><a
                                    data-href="<?php echo U('live/graphic', array('id' => $val['id'])); ?>"
                                    class="openWinFull" data-title="图文管理(<?php echo $val['title'];?>)" href="javascript:;">图文管理</a><?php } ?>
                            </td>
                            <td><a data-href="<?php echo U('live/comment', array('id' => $val['id'])); ?>"
                                   class="openWinFull" data-title="评论管理(<?php echo $val['title'];?>)" href="javascript:;">评论管理</a></td>
                            <td><a data-href="<?php echo U('live/video', array('id' => $val['id'])); ?>"
                                   data-width="900" data-height="500"
                                   class="openWinPop" data-title="视频" href="javascript:;">视频</a>&nbsp;<a
                                        data-href="<?php echo U('live/video', array('id' => $val['id'],'type'=>'view')); ?>"
                                        data-width="900" data-height="500"
                                        class="openWinPop" data-title="网页"
                                        href="javascript:;">网页</a></td>
                            <td class="td-manage manage-tools">
                                <a class="act-edit ml-20 openWinFull"
                                   href="<?php echo U('live/detail', array('id' => $val['id'])); ?>">详情</a>
                                <a class="act-edit ml-20 openWinFull"
                                   href="<?php echo U('live/edit', array('id' => $val['id'])); ?>">编辑</a>
                                <a class="ml-5 delete-info" rel="<?php echo $val['id']; ?>" href="javascript:;"
                                   title="删除">&nbsp;删除</a>
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
            var deleteUrl = '<?php echo U('live/del');?>';
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