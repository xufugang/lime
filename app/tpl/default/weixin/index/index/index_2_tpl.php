<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'house-detail', //页面标示
    'pagename' => $title, //当前页面名称
    'mycss' => array('weixin/swiper.min', 'weixin/style'), //加载的css样式表
    'myjs' => array('global/jquery.2.1.4.min', 'global/swiper.min','admin/socket.io.1.3.7','admin/msgbox'), //加载的js脚本
    'footerjs' => array(),
    'head' => true, //是否加载头部文件
    'wxjsapi' => true, //是否需要微信js接口
);
include getTpl('header', 'public');
?>
<!--<style>
.load-panel{position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100%; height: 100%; z-index: 10000; background-color:rgba(51,51,51,1);}
.spinner {
    margin: 100px auto 0;
    width: 70px;
    text-align: center;
    position:absolute; left:50%; top:50%; margin-left:-30px; margin-top:-50px;
}
.spinner > div {
    width: 12px;
    height: 12px;
    background-color: rgba(255,255,255,.8);
    border-radius: 100%;
    display: inline-block;
    -webkit-animation: bouncedelay 1.4s infinite ease-in-out;
    animation: bouncedelay 1.4s infinite ease-in-out;
    /* Prevent first frame from flickering when animation starts */
    -webkit-animation-fill-mode: both;
    animation-fill-mode: both;
}

.spinner .bounce1 {-webkit-animation-delay: -0.32s;animation-delay: -0.32s;}
.spinner .bounce2 {-webkit-animation-delay: -0.16s;animation-delay: -0.16s;}
@-webkit-keyframes bouncedelay {0%, 80%, 100% { -webkit-transform: scale(0.0) }40% { -webkit-transform: scale(1.0) }}
@keyframes bouncedelay {0%, 80%, 100% { transform: scale(0.0);-webkit-transform: scale(0.0);} 40% {transform: scale(1.0);-webkit-transform: scale(1.0);}}
/* loading e*/
</style>-->
</head>
<style>
.cover{ 
    position:fixed; 
    left:0; 
    top:0; 
    width:100%; 
    height:100%; 
    z-index:999; 
    background-image: url(/statics/default/images/default.png);
    background-repeat:no-repeat; 
    background-size:100% 100%;
}
</style>
<body>

<div id="load-panel" class="load-panel">
    <div class="spinner">
        <div class="bounce1"></div>
        <div class="bounce2"></div>
        <div class="bounce3"></div>
    </div>
</div>
<!--封面-->
<!--<div class="cover" id="cover"></div>-->

<div class="content">
    <div class="cover" id="cover"></div>
    
    <div class="nav"><img src="<?php echo getImgUrl($rs['pic_url']);?>" width="100%"></div>
    <?php if(in_array($rs['type'],array(1,2))){ ?>
    <!--视频  B-->
    <div class="video">
    	<div class="v-main">

        <div class="videoBox" id="video" style="">

            <div id="id_test_video" style="width:100%; height:auto;"></div>

        </div>

    </div>

    <div class="v-num1"><span id="num"></span></div>
    <input type="hidden" id="setcount" value="<?php echo $rs['live_count']?>">
    </div>
    <?php }?>
    <!--视频  E-->
    <div class="line"></div>
    <!--介绍  B-->
    <div class="intro">
    	<span class="i-tit"><?php echo $rs['title']?></span>
        <div id="content"></div>
        <input type="hidden" id="cntcode" value="<?php echo $rs['content'];?>">
    </div>
    <!--介绍  E-->
    <!--图片轮播  B-->
    <?php if(in_array($rs['type'],array(2,3))){ ?>
    <div class="atlas" style="height:180px;width: 90%;">
        <div class="swiper-container">
            <div class="swiper-wrapper">
                 <?php foreach($image as $k =>$v){?>
                    <div class="swiper-slide"><img src="<?php echo getImgUrl($v['pic_url']);?>" width="100%"></div>
                 <?php }?>
              
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div> 
        </div>
    </div>
    <?php }?>
    <!--图片轮播  E-->
    <!--时间轴 B-->
     <?php if(in_array($rs['type'],array(2,3))){ ?>
    <div class="time-title"><p>发布会精彩现场</p></div>
    <div class="time">
    	<ul id="msg">
            <?php if($msg){?>
            <?php foreach ($msg as $k=>$v){?>
            <li class="time-list" id="list_<?php echo $v['mid'];?>">
                <div class="t-left">
                    <div class="t-icon1"></div>
                    <div class="t-txt"><span class="t-t1"><?php echo substr(outTime($v['add_time'],1),10);?></span><span class="t-t2"><?php echo $v['address']?></span></div>
                    <div class="t-icon2"><span></span></div>
                </div>
                <div class="t-cont">
                    <p class="t-intr"><span class="author"><?php echo $v['username']?>:</span><?php echo $v['content'];?></p>  
                    <div class="t-pic"><img src="<?php echo getImgUrl(getThumb($v['pic_url'], 7));?>" width="100%"></div>
                </div>
            </li>
            <?php }}?>   
        </ul>
    </div>
     <?php }?>
    <!--时间轴 E-->
    <div class="line"></div>
    <p class="add" style="text-align:center;">腾果网络直播</p>
</div>

<script>//消息更新
$(function () {
    var reconnectFailCount =0,reconnectTimes=3,showTip=true,authCode='<?php echo $authCode;?>';
    // 连接服务端
    socket = io('<?php echo $getSocketUrl;?>', {path: '<?php echo $getSocketPath;?>socket.io'});
    // 连接后登录
    socket.on('connect', function(){
	console.log('用户登陆');
    	socket.emit('login',authCode);
    });
    //登陆成功
    socket.on('login_ok', function(rs) {
            console.log("登陆成功...",rs);
    });
    socket.on('login_fail', function(rs) {

//		console.log("登陆失败...",rs);
		Msg.error('登陆失败');

	});
    socket.on('new_msg', function(rs) {
		if(rs.status==1){
                    console.log(rs);
                    var data = rs.data;
                    var img;
                    if(data.type==1){
                        if(data.img_status==1){
                            img = '<div class="t-pic"><img src="'+data.image+'" width="100%"></div>';
                        }else{
                            img = '';
                        }
                        var cnt = '<li class="time-list" id="list_'+data.id+'"><div class="t-left"><div class="t-icon1"></div>'+
                            '<div class="t-txt"><span class="t-t1">'+data.add_time+'</span><span class="t-t2">'+data.address+'</span></div>'+
                            '<div class="t-icon2"><span></span></div></div>'+
                            '<div class="t-cont"><p class="t-intr"><span class="author">'+data.username+': </span>'+ data.msg+'</p>'+img+'</div></li>';            
                      $("#msg").prepend(cnt);
                    }else if(data.type==2){
                        $("list_"+data.id).remove();
                    }else if(data.type==4){
                        $("#setcount").val(data.count);
                    }
                }else{
                    Msg.error('操作失败');
                }
	});

    socket.on('connect_failed', function(o) {
		console.log("connect_failed to Server");
		Msg.error("无法连接服务器...");
	});
    socket.on('error', function(o) {
		console.log("error");
		Msg.error("服务器连接错误...");
	});
    socket.on('reconnecting', function (o) {
		reconnectFailCount++;
		if (reconnectFailCount >= reconnectTimes) {
			Msg.error("与服务器通讯失败，请检查网络");
		}
	});
    socket.on('reconnect', function (o) {
		reconnectFailCount--;
    });
});

</script>

<script src="//imgcache.qq.com/open/qcloud/video/vcplayer/TcPlayer.js"></script>
<script>
 
$(function () {
    var img = new Image();
	img.onload = function(){
		$('#load-panel').fadeOut(function(){
                    $('#cover').fadeOut(1200);
                    $('#video').delay(2500).fadeIn(0);
		});
	};  
    img.onerror = function(){console.lg("img error!")};  
    img.src="http://115.29.221.164/statics/default/images/default.png";
    
    //开头

    var staturl = '<?php echo $staturl?>';

    var videoh=$(window).width()*0.56;

    var count= $("#setcount").val();
    
    var content = $("#cntcode").val();

    $('.v-main').height(videoh);

    $('.videoBox').height(videoh);

    $("#content").html(content);

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
               count= $("#setcount").val();
               console.log('xxx');
               console.log(re.data);
               
                var ct = parseInt(re.data.online_users) + parseInt(count);

                $("#num").text(ct);

                if(re.data>0){

                    a=re.data;

                }               

            }else{
                count= $("#setcount").val();
                a = count;

                $("#num").text(a);

            }

        });

    }

    loadData();

    setInterval(loadData, 3000);

  

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

            m3u8: m3u8 ||liveurl,


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
<!--分享-->
<script>
var dataForWeixin = {
    imgUrl: "http://game.hzforall.com/h5/2017/0612ldkjz/images/share.jpg",
    title: "全球视频直播：伦敦科技周，EFC&G5品牌发布会",
    desc: "伦敦科技周，EFC&G5品牌发布会视频直播中",
    link: 'http://game.hzforall.com/h5/2017/0612ldkjz/index.html',
    callback: function(){},
    cancel:function(){}
};
</script>

<!--统计-->
<div style="display:none;">
<script type="text/javascript">
	function loadJScript() {
		var script = document.createElement("script");
		script.type = "text/javascript";
		script.src = "//tajs.qq.com/stats?sId=30044994";
		document.body.appendChild(script);
	}  
	window.onload = loadJScript;  //异步加载地图
</script>
</div>
<?php
include getTpl('footer', 'public');
?>
