<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'index', //页面标示
    'pagename' => '', //当前页面名称
    'keywords' => '', //关键字
    'description' => '', //描述
    'mycss' => array('admin/lib/activity/fonts/iconfont', 'admin/lib/activity/style'), //加载的css样式表
    'myjs' => array('admin/lib/clipboard.min'), //加载的js脚本
    'footerjs' => array(),
    'head' => true,
    'copyright' => true
);
include getTpl('header', 'public');
?>
    <div class="pd-20 main-content">
        <div class="title-page">直播详情</div>
        <div class="ac-detail-name">
            <div class="ac-name"><?php echo $rs['title']; ?></div>
        </div>

        <!-- 报名设置 -->
        <div class="mt-20">
            <div class="panel-gray">
                <div class="flex__cell">
                    <div class="label_cell">直播时间：</div>
                    <div><?php echo outTime($rs['start_time'], 2) . ' - ' . outTime($rs['end_time']); ?></div>
                </div>
                <div class="flex__cell">
                    <div class="label_cell">直播类型：</div>
                    <div>
                        <?php echo $rs['type'] == 1 ? '视频直播' : ($rs['type'] == 2 ? '图文直播' : '视频+图文'); ?>
                    </div>
                </div>
                <div class="flex__cell">
                    <div class="label_cell">直播频道：</div>
                    <div><?php echo $rs['channel']; ?></div>
                </div>
                <div class="flex__cell">
                    <div class="label_cell">直播员：</div>
                    <div><?php echo $rs['liver']; ?></div>
                </div>
                <div class="flex__cell">
                    <div class="label_cell">嘉宾：</div>
                    <div><?php echo $rs['guest']?$rs['guest']:'暂无'; ?></div>
                </div>
                <div class="flex__cell">
                    <div class="label_cell">观看人数：</div>
                    <div><?php echo $rs['look_num']?$rs['look_num']:0; ?></div>
                </div>
                <div class="flex__cell">
                    <div class="label_cell">评论数量：</div>
                    <div><?php echo $rs['comment_num']?$rs['comment_num']:0; ?></div>
                </div>
                <div class="flex__cell">
                    <div class="label_cell">推流地址：</div>
                    <div><?php echo $rs['rtmp_url']?$rs['rtmp_url'].'&nbsp;<a href="javascript:;" class="copy-qrcode-btn" data-clipboard-action="copy" data-clipboard-text="'.$rs['rtmp_url'].'"><span class="icon font_family icon-all_copy"></span></a>':'无'; ?></div>
                </div>
                <div class="flex__cell">
                    <div class="label_cell">播放地址：</div>
                    <div><?php echo $rs['play_url']?$rs['play_url'].'&nbsp;<a href="javascript:;" class="copy-qrcode-btn" data-clipboard-action="copy" data-clipboard-text="'.$rs['play_url'].'"><span class="icon font_family icon-all_copy"></span></a>':'无'; ?></div>
                </div>
                <div class="flex__cell">
                    <div class="label_cell">前端地址：</div>
                    <div><?php echo $rs['front_url']?$rs['front_url'].'&nbsp;<a href="javascript:;" class="copy-qrcode-btn" data-clipboard-action="copy" data-clipboard-text="'.$rs['front_url'].'"><span class="icon font_family icon-all_copy"></span></a>':'无'; ?></div>
                </div>

            </div>
        </div>
    </div>
<script>
    $(function(){
        var clipboard = new ClipboardJS('.copy-qrcode-btn');

        clipboard.on('success', function(e) {
            console.log(e);
            Msg.ok('已复制到剪贴板');
        });

        clipboard.on('error', function(e) {
            console.log(e);
        });
    });
</script>
    <!--主体 结束-->
<?php include getTpl('footer', 'public'); ?>