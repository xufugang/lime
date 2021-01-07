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

              </span>
            <span class="r">
            <div class="text-l">
               <form id="form1" name="form1" method="get" action="<?php echo U('user/index'); ?>">
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
                    <th align="center">用户昵称</th>
                    <th align="center">直播标题</th>
                    <th align="center">观看时间</th>
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
                            <td><img src="<?php echo getImgUrl($val['haedimg']); ?>" width="50px" height="50px" ></td>
                            <td><?php echo $val['nickname']; ?></td>
                            <td><?php echo $val['title']; ?></td>
                            <td><?php echo outTime($val['create_time'], 1); ?></td>
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
    </script>
<?php
include getTpl('footer', 'public');
?>