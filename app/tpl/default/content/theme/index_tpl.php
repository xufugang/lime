<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'index', //页面标示
    'pagename' => '主题管理', //当前页面名称
    'keywords' => '', //关键字
    'description' => '', //描述
    'mycss' => array('admin/lib/live/style'), //加载的css样式表
    'myjs' => array(''), //加载的js脚本
    'footerjs' => array(),
    'head' => true,
    'copyright' => true
);
include getTpl('header', 'public');
?>
    <div class=" main-content md-20 pl-20 pr-20">
        <div class="ac-filter-section">
            <span class="l">
            <!--添加链接-->
            <a href="<?php echo U('theme/add'); ?>" class="btn btn-success radius openWinPop">添加</a>
                <!--添加链接-->
                <!--分类组-->
            <div class="btn-group" style="display:inline-block; vertical-align:middle; margin-left:10px;">
                  <span><a class="btn btn-<?php if (!isHave($type)) {
                          echo 'primary';
                      } else {
                          echo 'default';
                      } ?>" href="<?php echo U('theme/index', getSearchUrl(array('type' => null, 'p' => 1))) ?>">全部</a></span>
                  <?php
                  foreach ($setting['rule_type'] as $key => $val) {
                      ?>
                      <span><a class="btn btn-<?php if ($type == ($key + 1)) {
                              echo 'primary';
                          } else {
                              echo 'default';
                          } ?>"
                               href="<?php echo U('theme/index', getSearchUrl(array('type' => ($key + 1), 'p' => 1))) ?>"><?php echo $val; ?></a></span>
                      <?php
                  }
                  ?>
            </div>
                <!--分类组-->
            </span>
            <span class="r">
            <!--搜索框-->
            <div class="text-l">
                <form id="form1" name="form1" method="get"
                      action="<?php echo U('theme/index', getSearchUrl(array('p' => 1, 'q' => null))); ?>">
                <input type="text" class="input-text" style="width:250px" placeholder="输入关键字..." id="q" name="q">
                 <button type="submit" class="btn btn-success radius" id="" name=""><i
                             class="Hui-iconfont">&#xe665;</i> 搜索</button>
                </form>
            </div>
                <!--搜索框-->
	        </span>
        </div>

        <div class="mt-20">
            <table class="table table-border table-no-bordered table-hover table-bg table-sort">
                <thead>
                <tr class="text-c">
                    <th align="center">主题</th>
                    <th align="center">副标题</th>
                    <th align="center">内容</th>
                    <th align="center">排序</th>
                    <th align="center">状态：2-下架，1-上架，0-删除</th>
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
                            <td><?php echo $val['title']; ?></td>
                            <td><?php echo $val['vice_title']; ?></td>
                            <td><?php echo $val['content']; ?></td>
                            <td><?php echo $val['px']; ?></td>
                            <td>
                                <span class="<?php echo getStatusStyle($val['status']); ?>"><?php echo $setting['status'][$val['status']]; ?></span>
                            </td>
                            <td class="td-manage manage-tools">
                                <a class="act-edit ml-15"
                                   href="<?php echo U('theme/edit', array('id' => $val['id'])); ?>">编辑</a>
                                <a class="ml-15 delete-info" rel="<?php echo $val['id']; ?>" href="javascript:;"
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
            var deleteUrl = '<?php echo U('theme/delete');?>';
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
            })
            $('.act-deal').click(function () {
                var id = $(this).data('id');
                var index = layer.open({
                    content: '请选择操作'
                    , btn: ['通过', '拒绝', '关闭']
                    , yes: function (index, layero) {
                        Msg.loading();
                        $.post('<?php echo U('theme/deal');?>', {"id": id, "do": 1}, function (res) {
                            Msg.ok('操作成功');
                            if (res.status == 1) {
                                $("#edit_status_id_" + id).html(res.data.html);
                            } else {
                                Msg.error(res.info);
                            }
                        }, 'json');
                        layer.close(index);
                    }, btn2: function (index, layero) {
                        Msg.loading();
                        $.post('<?php echo U('theme/deal');?>', {"id": id, "do": 2}, function (res) {
                            Msg.hide();
                            if (res.status == 1) {
                                $("#edit_status_id_" + id).html(res.data.html);
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
        });
    </script>
<?php
include getTpl('footer', 'public');
?>