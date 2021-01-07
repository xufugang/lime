<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'video', //页面标示
    'pagename' => '视频', //当前页面名称
    'keywords' => '', //关键字
    'description' => '', //描述
    'mycss' => array('date/smoothness.min', 'admin/lib/activity/fonts/iconfont', 'admin/lib/activity/style'), //加载的css样式表
    'myjs' => array('global/jquery.qrcode.min','admin/lib/clipboard.min'), //加载的js脚本
    'footerjs' => array(),
    'head' => true,
    'copyright' => true
);
include getTpl('header', 'public');
?>
    <style>
        .show-pop-content {
            overflow-y: scroll;
            overflow-x: hidden;
            table-layout: fixed;
            word-wrap: break-word;
            word-break: break-all;
        }
    </style>
    <div class="table-responsive show-pop-content">
        <div id="data-matrix" style="margin:10px auto; width: 240px;"></div>
        <div class="ta-c pb-10">打开&nbsp;<strong>微信</strong>&nbsp;扫描二维码去分享</div>
        <div class="qrcode-text-item"><?php  echo $url;?></div>
        <div class="ta-c pb-10 pt-10"><a href="javascript:;" class="copy-qrcode-btn" data-clipboard-action="copy" data-clipboard-text="<?php  echo $url;?>"><span class="icon font_family icon-all_copy"></span>&nbsp;一键复制</a></div>
        <!--<p>链接地址：<?php /*echo $url; */?></p>-->
    </div>
    <script>
        $(function () {
            $('#data-matrix').qrcode({width: 200, height: 200, text: '<?php  echo $url;?>'}).show();

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
<?php
include getTpl('footer', 'public');
?>