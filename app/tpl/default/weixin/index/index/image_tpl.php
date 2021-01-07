<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'house-detail', //页面标示
    'pagename' => '视频', //当前页面名称
    'mycss' => array('weixin/swiper.min', 'weixin/style'), //加载的css样式表
    'myjs' => array('global/jquery.2.1.4.min', 'global/swiper.min'), //加载的js脚本
    'footerjs' => array(),
    'head' => true, //是否加载头部文件
    'wxjsapi' => true, //是否需要微信js接口
);
include getTpl('header', 'public');
?>

    <div class="swiper-container">
            <div class="swiper-wrapper">
                <?php foreach($rs as $k =>$v){?>
                <div class="swiper-slide"><img src="<?php echo getImgUrl($v['pic_url']);?>" width="100%"></div>
                <?php }?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
    </div>

<script src="//imgcache.qq.com/open/qcloud/video/vcplayer/TcPlayer.js"></script>
<script>
    var swiper = new Swiper('.swiper-container', {
	nextButton: '.swiper-button-next',
	prevButton: '.swiper-button-prev',
	slidesPerView: 1,
	loop: true,
	autoplay: 7000,
	autoplayDisableOnInteraction: false
});
$(function () {

    //loadData();
    //setInterval(loadData, 3000);
  
});

</script>
<!--<script type="text/javascript">
    (function(){
        
    })();
</script>-->
</body>
</html>

