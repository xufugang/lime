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

    <div class="v-main">
        <div class="videoBox" id="video" style="">
            <div id="id_test_video" style="width:100%; height:auto;"></div>
        </div>
    </div>
    <div class="v-num1"><span id="num"></span></div>

<script src="//imgcache.qq.com/open/qcloud/video/vcplayer/TcPlayer.js"></script>
<script>
$(function () {
    var staturl = '<?php echo $staturl?>';
    var videoh=$(window).width()*0.56;
    var count = <?php echo $count?>;
    $('.v-main').height(videoh);
    $('.videoBox').height(videoh);

    //人数显示
    var u = navigator.userAgent;
    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1; //android终端
    if(isAndroid == true){
        $('.video').addClass('video-p');
        $('.v-num1').addClass('v-num2');
    }else{
        $('.video').removeClass('video-p');
        $('.v-num1').removeClass('v-num2');
    }
    var a=0;
    function loadData(){
        $.getJSON(staturl, function(re){
            if (re.status==1){
                var ct = parseInt(re.data.online_users) + parseInt(count);
                $("#num").text(ct);
                if(re.data.online_users>0){
                    a=re.data.online_users;
                }               
            }else{
                a = count;
                $("#num").text(a);
            }
        });
    }
    //loadData();
    //setInterval(loadData, 3000);
  
});

</script>
<script type="text/javascript">
    (function(){
        var liveurl = '<?php echo $url;?>';
//        console.log('执行了');
        function getParams(name) {
            var reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)', 'i');
//            console.log(reg)
            var r = window.location.search.substr(1).match(reg);
//            console.log(window.location.search.substr(1));
            if (r != null) {
                return decodeURIComponent(r[2]);
            }
            return null;
        }
        var rtmp = getParams('rtmp'),
            flv  = getParams('flv'),
            m3u8 = getParams('m3u8'),
            mp4  = getParams('mp4'),
            live = (getParams('live') == 'true' ? true : false),
            coverpic = getParams('coverpic'),
            width = getParams('width'),
            height = getParams('height'),
            autoplay = (getParams('autoplay') == 'true' ? true : false);
        /**
         * 视频类型播放优先级
         * mobile ：m3u8>mp4
         * PC ：RTMP>flv>m3u8>mp4
         */
        var options = {
            rtmp: rtmp,
            flv: flv,
			//m3u8: m3u8 || '	http://2668.liveplay.myqcloud.com/live/2668_61fea0e24a6511e791eae435c87f075e_550.m3u8',
            m3u8: m3u8 ||liveurl,
			//m3u8: m3u8 || 'http://1251132611.vod2.myqcloud.com/4126dd3evodtransgzp1251132611/8a592f8b9031868222950257296/f0.f240.m3u8',
            //m3u8_hd: m3u8 || 'http://1251132611.vod2.myqcloud.com/4126dd3evodtransgzp1251132611/8a592f8b9031868222950257296/f0.f230.m3u8',
           // m3u8_sd: m3u8 || 'http://1251132611.vod2.myqcloud.com/4126dd3evodtransgzp1251132611/8a592f8b9031868222950257296/f0.f220.m3u8',
            mp4 : mp4 ,
            coverpic: coverpic ,
            //autoplay: autoplay ? true : false,
            autoplay: true,
            live: true,
            width : '100%',
            height :'100%',
			controls:'system',
            wording: {
                2032: '请求视频失败，请检查网络',
                2048: '请求m3u8文件失败，可能是网络错误或者跨域问题'
            },
            listener: function (msg) {
                //console.log(msg.type);
            }
        };
        console.log(options.m3u8);
        var player=new TcPlayer('id_test_video',options);
		 console.log(player);
    })();
</script>
</body>
</html>

