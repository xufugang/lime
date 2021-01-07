<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'index', //页面标示
    'pagename' => '管理', //当前页面名称
    'keywords' => '', //关键字
    'description' =>'', //描述
    'mycss' => array(), //加载的css样式表
    'myjs' => array('admin/calendar'), //加载的js脚本
    'footerjs'=>array(),
    'head'=>true,
    'copyright'=>true
);
include getTpl('header', 'public');
?>
    <div class="pd-20 main-content md-20">
        <!-- <div class="cl pd-5 bg-1 bk-gray">
      <span class="l">
        <a href="<?php /*echo U('channel/add');*/?>" class="btn btn-success radius openWinPop"><i class="Hui-iconfont">&#xe600;</i> 添加</a>
        </span>
        </div>-->

        <div class="mt-20">
            <table class="table table-border table-bordered table-hover table-bg table-sort">
                <thead>
                <tr class="text-c">
                    <th align="center">序号</th>
                    <th align="center">名称</th>
                    <th align="center">内容</th>
                    <th align="center">生成URL</th>
                    <th align="center">创建时间</th>
                    <!--<th align="center">操作</th>-->
                </tr>
                </thead>
                <tbody>
                <?php
                if ($rs){
                    ?>
                    <?php
                    foreach($rs as $val){
                        ?>
                        <tr class="text-c" id="list_detail_<?php echo $val['id'];?>">
                            <td><?php echo $val['id'];?></td>
                            <td><?php echo $val['name'];?></td>
                            <td><?php echo $val['content'];?></td>
                            <td><?php echo $val['url'];?></td>
                            <td><?php echo outTime($val['create_time'],1);?></td>
                          <!--  <td class="td-manage manage-tools">
                                <a class="act-edit ml-10 openWinPop" href="<?php /*echo U('channel/edit',array('id'=>$val['id']));*/?>">编辑</a>
                                <a class="ml-10 delete-info" rel="<?php /*echo $val['id'];*/?>" href="javascript:;" title="删除">删除</a>
                            </td>-->
                        </tr>
                        <?php
                    }
                }else{
                    ?>
                    <tr>
                        <td colspan="20" align="center"><div class="text-c">暂无数据</div></td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <div><?php echo page($total,$p,'',20,'p');?></div>
        </div>
    </div>
    <script>
        $(function(){
            var deleteUrl='<?php echo U('channel/delete');?>';
            $('.delete-info').click(function(){
                var id=$(this).attr('rel');
                var index=layer.confirm('确认删除吗?',{btn: ['确认','取消']}, function(){
                    Msg.loading();
                    $.post(deleteUrl,{"id":id},function(result){
                        Msg.hide();
                        if (result.status==1){
                            Msg.ok('操作成功');
                            $("#list_detail_"+id).fadeOut();
                        }else{
                            Msg.error(result.info);
                        }
                    },'json');
                    layer.close(index);
                }, function(){

                });
                return false;
            })
        });
    </script>
<?php
include getTpl('footer', 'public');
?>