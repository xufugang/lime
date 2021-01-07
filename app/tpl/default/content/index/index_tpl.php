<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'index', //页面标示
    'pagename' => '', //当前页面名称
    'keywords' => '', //关键字
    'description' => '', //描述
    'skin' => true,
    'mycss' => array('admin/lib/notifications/jquery.notifications.min'), //加载的css样式表
    'myjs' => array('admin/socket.io.1.3.7'), //加载的js脚本
    'footerjs' => array('admin/jquery.notifications.min'),
    'head' => true
);
include getTpl('header', 'public');
?>
<header class="navbar-wrapper">
    <div class="navbar navbar-fixed-top">
        <div class="container-fluid cl">
            <a class="logo navbar-logo f-l mr-10 hidden-xs" title="<?php echo WEB_TITLE; ?>"
               href="<?php echo U('index/index'); ?>"><?php echo WEB_TITLE; ?></a>
            <span class="logo navbar-slogan f-l mr-10 hidden-xs"><?php echo $version; ?></span>
            <a aria-hidden="false" class="nav-toggle Hui-iconfont visible-xs" href="javascript:;">&#xe667;</a>

            <nav id="Hui-userbar" class="nav navbar-nav navbar-userbar hidden-xs">
                <ul class="cl">
                    <li></li>
                    <li class="dropDown dropDown_hover"><a href="javascript:;"
                                                           class="dropDown_A"><?php echo $adminInfo['name']; ?> <i
                                    class="Hui-iconfont">&#xe6d5;</i></a>
                        <ul class="dropDown-menu menu radius box-shadow">
                            <?php foreach ($sysMenu['user'] as $v) {
                                if (isset($v['target']) && $v['target']) {
                                    switch ($v['target']) {
                                        case 'new':
                                            ?>
                                            <li><a target="_blank" href="<?php echo $v['href']; ?>"><i
                                                            class="Hui-iconfont"><?php echo $v['icon']; ?></i> <?php echo $v['title']; ?>
                                                </a></li>
                                            <?php
                                            break;
                                        case 'pop':
                                            ?>
                                            <li><a href="javascript:;" class="openWinPop"
                                                   data-href="<?php echo $v['href']; ?>"
                                                   data-title="<?php echo $v['title']; ?>"
                                                   data-width="<?php echo $v['width']; ?>"
                                                   data-height="<?php echo $v['height']; ?>"><i
                                                            class="Hui-iconfont"><?php echo $v['icon']; ?></i> <?php echo $v['title']; ?>
                                                </a></li>
                                            <?php
                                            break;
                                        case 'full':
                                            ?>
                                            <li><a href="javascript:;" class="openWinFull"
                                                   data-href="<?php echo $v['href']; ?>"
                                                   data-title="<?php echo $v['title']; ?>"><i
                                                            class="Hui-iconfont"><?php echo $v['icon']; ?></i> <?php echo $v['title']; ?>
                                                </a></li>
                                            <?php
                                            break;
                                        case 'logout':
                                            ?>
                                            <li><a href="javascript:;" class="logout"
                                                   data-href="<?php echo $v['href']; ?>"
                                                   data-title="<?php echo $v['title']; ?>"><i
                                                            class="Hui-iconfont"><?php echo $v['icon']; ?></i> <?php echo $v['title']; ?>
                                                </a></li>
                                            <?php
                                            break;
                                        case 'main':
                                            ?>
                                            <li><a href="javascript:;" class="main"
                                                   data-href="<?php echo $v['href']; ?>"
                                                   data-title="<?php echo $v['title']; ?>"><i
                                                            class="Hui-iconfont"><?php echo $v['icon']; ?></i> <?php echo $v['title']; ?>
                                                </a></li>
                                            <?php
                                            break;
                                    }
                                    ?>
                                <?php } else {
                                    ?>
                                    <li><a href="javascript:;" class="openWin" data-href="<?php echo $v['href']; ?>"
                                           data-title="<?php echo $v['title']; ?>"><i
                                                    class="Hui-iconfont"><?php echo $v['icon']; ?></i> <?php echo $v['title']; ?>
                                        </a></li>
                                    <?php
                                }
                            }
                            ?>
                        </ul>
                    </li>
                    <li>[<?php echo date('Y年m月d日'); ?>]</li>
                </ul>
            </nav>
        </div>
    </div>
</header>
<aside id="menu-aside" class="Hui-aside">
    <div class="menu_dropdown bk_2" id="left_menu_dropdown">
        <?php foreach ($sysMenu['main'] as $key=>$value) { ?>
            <dl id="menu-<?php echo $value['short']; ?>">
                <dt id="<?php echo $value['short']; ?>" style="color: <?php echo $value['short']=='admin'?'#03bb9b':'';  ?>"><?php echo $value['title']; ?></dt>
            </dl>
        <?php } ?>
    </div>
</aside>
<section class="Hui-article-box">
    <div id="iframe_box" class="Hui-article">
        <div class="show_iframe">
            <div style="display:none" class="loading"></div>
            <iframe scrolling="yes" id="mainframe" name="mainframe" frameborder="0"
                    src="<?php echo U('index/init'); ?>">
            </iframe>
        </div>
    </div>
</section>
<script>
    $(document).ready(function(){
        $('dt').off('mouseenter').unbind('mouseleave');
    });
    $("#menu-admin").click(function () {
        $("#menu-admin").css({"background-color":"#f7f7f7"});
        $("#admin").css({"color":"#03bb9b"});
        $("#menu-live").css({"background-color":""});
        $("#live").css({"color":""});
        $("#menu-channel").css({"background-color":""});
        $("#channel").css({"color":""});
        $("#menu-content").css({"background-color":""});
        $("#user").css({"color":""});
        $("#mainframe").attr("src","<?php echo U('index/init'); ?>");
    });
    $('#menu-live').click(function () {
        $("#menu-admin").css({"background-color":""});
        $("#admin").css({"color":""});
        $("#menu-live").css({"background-color":"#f7f7f7"});
        $("#live").css({"color":"#03bb9b"});
        $("#menu-channel").css({"background-color":""});
        $("#channel").css({"color":""});
        $("#menu-content").css({"background-color":""});
        $("#user").css({"color":""});
        $("#mainframe").attr("src","<?php echo U('live/index'); ?>");
    });
    $('#menu-channel').click(function () {
        $("#menu-admin").css({"background-color":""});
        $("#admin").css({"color":""});
        $("#menu-live").css({"background-color":""});
        $("#live").css({"color":""});
        $("#menu-channel").css({"background-color":"#f7f7f7"});
        $("#channel").css({"color":"#03bb9b"});
        $("#menu-content").css({"background-color":""});
        $("#user").css({"color":""});
        $("#mainframe").attr("src","<?php echo U('channel/index'); ?>");
    });
    $('#menu-user').click(function () {
        $("#menu-admin").css({"background-color":""});
        $("#admin").css({"color":""});
        $("#menu-live").css({"background-color":""});
        $("#live").css({"color":""});
        $("#menu-channel").css({"background-color":""});
        $("#channel").css({"color":""});
        $("#menu-content").css({"background-color":"#f7f7f7"});
        $("#user").css({"color":"#03bb9b"});
        $("#mainframe").attr("src","<?php echo U('user/index'); ?>");
    });
</script>
<?php include getTpl('footer', 'public'); ?>
