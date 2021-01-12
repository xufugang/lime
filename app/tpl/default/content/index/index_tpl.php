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
<header class="navbar-wrapper" id="header-bar">
    <div class="navbar navbar-fixed-top">
        <div class="container-fluid cl">
            <a class="logo navbar-logo f-l mr-10 hidden-xs" title="<?php echo WEB_TITLE; ?>"
               href="<?php echo U('index/index', array('id' => $id)); ?>"><?php echo WEB_TITLE; ?></a><span
                    class="logo navbar-slogan f-l mr-10 hidden-xs"><?php echo $version; ?></span> <a aria-hidden="false"
                                                                                                     class="nav-toggle Hui-iconfont visible-xs"
                                                                                                     href="javascript:;">&#xe667;</a>
            <nav id="Hui-userbar" class="nav navbar-nav navbar-userbar hidden-xs">
                <ul class="cl">
                   <!-- <li><?php /*echo '操作手册'; */?></li>-->
                    <li class="dropDown dropDown_hover"><a href="javascript:;"
                                                           class="dropDown_A"><?php echo $adminInfo['username']; ?> <i
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
        <?php foreach ($sysMenu['main'] as $value) { ?>
            <dl id="menu-<?php echo $value['short']; ?>">
                <dt><?php echo $value['title']; ?></dt>
                <dd>
                    <ul>
                        <?php foreach ($value['item'] as $val) { ?>
                            <?php
                            if (isset($val['new']) && $val['new']) {
                                ?>
                                <li><a target="_blank" href="<?php echo $val['url']; ?>"
                                       data-title="<?php echo $val['title']; ?>"><?php echo $val['title']; ?></a></li>
                            <?php } else {
                                ?>
                                <li><a id="hash-<?php echo $val['short']; ?>" _href="<?php echo $val['url'].'?id='.$id; ?>"
                                       href="javascript:;" data-hash="<?php echo $val['short']; ?>"
                                       data-title="<?php echo $val['title']; ?>"><?php echo $val['title']; ?></a></li>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                </dd>
            </dl>
        <?php } ?>
    </div>
</aside>
<section class="Hui-article-box">
    <div id="iframe_box" class="Hui-article">
        <div class="show_iframe">
            <div style="display:none" class="loading"></div>
            <iframe scrolling="yes" id="mainframe" name="mainframe" frameborder="0"
                    src="<?php echo U('index/init'); ?>"></iframe>
        </div>
    </div>
</section>
<?php include getTpl('footer', 'public'); ?>
