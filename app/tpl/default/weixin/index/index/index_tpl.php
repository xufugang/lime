<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'house-detail', //页面标示
    'pagename' => $title, //当前页面名称
    'mycss' => array('weixin/swiper.min', 'weixin/style'), //加载的css样式表
    'myjs' => array('global/jquery.2.1.4.min', 'global/swiper.min', 'admin/socket.io.1.3.7', 'global/echo.min', 'admin/msgbox', 'global/jquery.barrager', 'global/global'), //加载的js脚本
    'footerjs' => array(),
    'head' => true, //是否加载头部文件
    'wxjsapi' => true, //是否需要微信js接口
);
include getTpl('header', 'public');
?>
    <script>
        var dataForWeixin = {
            imgUrl: "<?php echo $sharepic; ?>",
            title: "<?php echo $sharetitle; ?>",
            desc: "<?php echo $sharecontent; ?>",
            link: '<?php echo $shareurl; ?>',
            callback: function() {
            },
            cancel: function() {
            }
        };
    </script>
<?php
 if (isset($Document['wxjsapi'])&&$Document['wxjsapi']){
?>
<script src="//res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
$.getJSON('<?php echo $setting['jsapi_url'];?>?callback=?', function(json){
    wx.config(json);
    wx.ready(function(){
        wxjsapiShare(dataForWeixin);
//          wx.hideAllNonBaseMenuItem();
    });
});
function wxjsapiShare(res) {
	var conf={title:res.title,desc:res.desc,link:res.link,imgUrl:res.imgUrl,type:"",dataUrl:"",
	success:function(result){res.callback(result);},
	cancel:function(result){res.cancel(result);}};wx.onMenuShareTimeline(conf);
	wx.onMenuShareAppMessage(conf);wx.onMenuShareQQ(conf);wx.onMenuShareWeibo(conf);
}
</script>
<?php
 }
?>

<script type="text/javascript">
    !function(e) {
        function n() {
            var w = document.documentElement.clientWidth;
            document.documentElement.style.fontSize = (w > 750 ? 750 : w) / 16 + "px", window.jQuery && window.jQuery(window).trigger("refreshBasesize")
        }
        var t = null;
        window.addEventListener("resize", function() {
            clearTimeout(t), t = setTimeout(n, 300)
        }, !1), n()
    }(window);</script>
<style>
    .cover{ 
        position:fixed; 
        left:0; 
        top:0; 
        width:100%; 
        height:100%; 
        z-index:999; 
        background-image: url('<?php echo getImgUrl($rs['show_pic']); ?>'); 
        background-repeat:no-repeat; 
        background-size:100% 100%;
    }
    .footer-text{text-align: center; color: #666; font-size: .541063rem; position: relative;}
    .footer-text:after,.footer-text:before{content: ''; position: absolute; top: 50%; width: 32%; height: 1px; border-top: 1px solid #ddd; -webkit-transform: scaleY(.5); -moz-transform: scaleY(.5); transform: scaleY(.5);}
    .footer-text:after{right:15px;}
    .footer-text:before{left:15px;}
	.section-tab .tab-link{ cursor:pointer;}
</style>
<body>
    <div id="load-panel" class="load-panel">
        <div class="spinner">
            <div class="bounce1"></div>
            <div class="bounce2"></div>
            <div class="bounce3"></div>
        </div>
    </div>
    <div class="container pt-2-5">
        <div class="cover" id="cover"></div>
        <div><img src="<?php echo getImgUrl($rs['pic_url']); ?>" width="100%"></div>
        <?php if (in_array($rs['type'], array(1, 2))) { ?>
            <div>   
                <?php if ($istype == 1) { ?>
                    <div class="video-box" id="video_box">
                        <div id="id_test_video" class="video-item"></div>
                        <?php if ($rs['is_msg'] == 1) { ?>
                            <a href="javascript:;" class="barrage-btn"></a>
                        <?php } ?>
                    </div>               
                    <div id="people" style=" text-align: right; color: #666; font-size: .541063rem; padding: 5px 0; padding-right: 15px;"><span>当前观看人数：<span id="num">0</span></span></div>                
                <?php } elseif ($istype == 2) { ?>
                    <div class="video-box" >
                        <iframe class="v-sp" frameborder="0" width="100%" height="100%" src="https://v.qq.com/iframe/player.html?<?php echo $vid ?>tiny=0&auto=0" allowfullscreen></iframe>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <div class="plr-15">
            <div class="title" ><?php echo $rs['title']; ?></div>
            <div class="content" id="content"></div>
            <input type="hidden" id="cntcode" value="<?php echo $rs['content']; ?>">
            <!-- Swiper B -->

            <!-- Swiper E -->
        </div>
        <?php if (in_array($rs['type'], array(1,2, 3))) { ?>
            <div class="section-tab">
                <?php if (in_array($rs['type'], array(2, 3))) { ?>
                <a href="javascript:;" class="tab-link selected-on" data-value='1'><span>图文直播</span></a>
                <?php } ?>

                    <?php if ($rs['is_feedback'] == 1) { ?>
                        <a href="javascript:;" class="tab-link <?php if (in_array($rs['type'], array(1))) {echo 'selected-on';} ?> feedback_tab" data-value='2'><span>评论列表<em id="feedct"><?php echo $feedct; ?></em></span></a>
                    <?php }
                 ?>
            </div>
            <!-- content-list B -->
            <div id="content_list" class="plr-15 content-list" >
                <!-- 评论List B -->


                <!-- 图文 list E -->
            </div>
            <!-- content-list E -->
            <div class="weui-infinite-scroll"><div class="infinite-preloader"></div>正在加载...</div>
            <div class="footer-text">腾果智汇出品</div>
    <?php } ?>
    </div>
    <input type="hidden" id="greysrc" value="<?php echo IMG_PATH . 'admin/blank.gif'; ?>">
    <input type="hidden" id="loadsrc" value="<?php echo IMG_PATH . 'admin/loading.gif'; ?>">
    <input type="hidden" id="start" value="<?php echo $msgct ?>">
    <input type="hidden" id="typest" value="1">

    <div id="footer" class="footer-com" <?php if (($rs['is_feedback'] != 1) && ($from == 'weixin')) {echo 'style="display: none;"';} ?>>
        <form id="form_1">
            <span class="icon-com"></span>
            <input type="text" name="fdcontent" id="fdcontent" class="input-com" placeholder="我来说一句">
            <input type="hidden"  id="rid" name="rid" value="<?php echo $rs['id']; ?>">
        </form>
        <button id="save_btn" class="btn">发布</button>
    </div>

    <div style="display: none;">
        <script type="text/javascript" src="//tajs.qq.com/stats?sId=30044994" charset="UTF-8"></script>
    </div>

    <script>
    var reconnectFailCount = 0, reconnectTimes = 3, showTip = true, authCode = '<?php echo $authCode; ?>';
    var $box = $('#video_box');
    // 连接服务端
    socket = io('<?php echo $getSocketUrl; ?>', {path: '<?php echo $getSocketPath; ?>socket.io'});
    console.log('链接socket');
    // 连接后登录
    socket.on('connect', function() {
        console.log('用户登陆');
        socket.emit('login', authCode);
    });
    //登陆成功
    socket.on('login_ok', function(rs) {
        console.log("登陆成功...", rs);
    });
    socket.on('login_fail', function(rs) {
        console.log("登陆失败...", rs);
    });
    socket.on('new_msg', function(rs) {
        if (rs.status == 1) {
            var data = rs.data;
            console.log(data);
            var img;
            var avatar = '';
            if (data.type == 1) {
                var typest = $("#typest").val();
                avatar = '<div class="tt-avatar" style="background-image: url(' + data.avatar + ')"></div>';
                if (data.img_status == 1) {
                    img = '<div class="tt-pic" data-url="' + data.image + '"><img width="100%" src="' + data.image + '" ></div>';
                } else {
                    img = '';
                }

                var cnt = '<div class="teletext-item" id="list_' + data.mid + '">' + avatar + '<div class="cont-side">' +
                        '<div class="tt-top"><span class="name">' + data.username + '</span><span class="time">' + data.add_time + '</span></div>' +
                        img + '<div class="tt-cont">' + data.msg + '</div></div></div>';

                if (typest == 1) {
                    $("#content_list").prepend(cnt);
                }
            } else if (data.type == 2) {
                $("#list_" + data.id).remove();
            } else if (data.type == 4) {

                var typest = $("#typest").val();
                avatar = '<div class="tt-avatar" style="background-image: url(' + data.avatar + ')"></div>';
                if (data.img_status == 1) {
                    img = '<div class="tt-pic" data-url="' + data.image + '"><img width="100%" src="' + data.image + '" ></div>';
                } else {
                    img = '';
                }

                var cnt = '<div class="teletext-item" id="list_' + data.mid + '">' + avatar + '<div class="cont-side">' +
                        '<div class="tt-top"><span class="name">' + data.username + '</span><span class="time">' + data.add_time + '</span></div>' +
                        img + '<div class="tt-cont">' + data.msg + '</div></div></div>';

                if (typest == 1) {
                    $("#list_" + data.mid).html(cnt);
                }

            } else if (data.type == 5) {
                var typest = $("#typest").val();
                avatar = '<div class="com-avatar"> <img src="' + data.avatar + '"></div>';
                var cnt = '<div class="comment-item" id="list_' + data.mid + '"><div class="com-info">' + avatar + '<div class="in-side" ><div class="com-name">' + data.username + '</div><div class="com-time">' + data.add_time + '</div>' +
                        '</div></div><div class="com-cont">' + data.msg + '</div></div>';
                console.log(typest);
                if (typest == 2) {
                    var ft = $("#feedct").text();
                    var total = parseInt(ft) + parseInt(1);
                    $("#feedct").text(total);
                    $("#content_list").prepend(cnt);
                }
//            //弹出框

                if (isBarrage)
                    $box.barrager({info: data.username + ': ' + data.msg});
            }
        } else {
            Msg.alert('操作失败');
        }
    });
    socket.on('connect_failed', function(o) {
        console.log("connect_failed to Server");
        Msg.alert("无法连接服务器...");
    });
    socket.on('error', function(o) {
        console.log("error");
        Msg.alert("服务器连接错误...");
    });
    socket.on('reconnecting', function(o) {
        reconnectFailCount++;
        if (reconnectFailCount >= reconnectTimes) {
            Msg.alert("与服务器通讯失败，请检查网络");
        }
    });
    socket.on('reconnect', function(o) {
        reconnectFailCount--;
    });
    </script>

    <script src="//imgcache.qq.com/open/qcloud/video/vcplayer/TcPlayer.js"></script>
    <script>
    $(function() {
        var picarray;
        var img = new Image();
        img.onload = function() {
            $('#load-panel').fadeOut(function() {
                $('#cover').fadeOut(6000, function() {
                    $('#video_box').fadeIn();
                });
            });
        };
        img.onerror = function() {
            console.log("img error!");
            $('#cover').hide();
        };
        img.src = '<?php echo getImgUrl($rs['show_pic']); ?>';
        //开头

        var staturl = '<?php echo $staturl ?>';
        var videoh = $(window).width() * 0.56;
//    var count = $("#setcount").val();
        var content = $("#cntcode").val();
        $('.v-main').height(videoh);
        $('.videoBox').height(videoh);
        $("#content").html(content);
        //人数显示

        var u = navigator.userAgent;
        var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1; //android终端

        if (isAndroid == true) {

            $('.video').addClass('video-p');
            $('.v-num1').addClass('v-num2');
        } else {

            $('.video').removeClass('video-p');
            $('.v-num1').removeClass('v-num2');
        }

        var a = 0;
        function loadData() {

            $.getJSON(staturl, function(re) {

                if (re.status == 1) {
//    count = $("#setcount").val();
                    console.log(re.data);
                    var ct = parseInt(re.data.online_users);
                    $("#num").text(ct);
					if(ct>0){
					    $("#people").show();
					}
                    if (re.data > 0) {
                        a = re.data;
                    }

                } else {
                    //    count = $("#setcount").val();
                    a = 0;
                    $("#num").text('loading...');
                    $("#people").hide();
                }

            });
        }
        loadData();
        setInterval(loadData, 5000);


        $(document).on('click', '.tt-pic', function() {
            var rid = $("#rid").val();
            var url = $(this).attr('data-url');
            $.post('<?php echo U('index/getMoreImg'); ?>', {'rid': rid}, function(result) {
                if (result.status == 1) {
                    console.log(result);
                    picarray = result.data;
                    console.log(picarray);
                    wx.previewImage({
                        current: url,
                        urls: picarray
                    });
                } else {
                    Msg.alert(result.info);
                }
            }, 'json');


        });
    });
    </script>
    <!-- Initialize Swiper -->
    <script>
        var getTeletextUrl = '<?php echo $getliveurl; ?>'; //获取图文
        var getCommentUrl = '<?php echo $getliveurl; ?>'; //获取评论
        var saveCommentUrl = '<?php echo $saveurl; ?>'; //提交评论
        var istype = '<?php echo $istype; ?>';
        var ismsg = '<?php echo $rs['is_msg'] ?>';
        var isBarrage = true;
        //判断是否安卓
        var u = navigator.userAgent;
        var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1; //android终端

        if (isAndroid == true) {
            isBarrage = false;
            $('.barrage-btn').remove();
        } else {
            isBarrage = true;
        }
        //判断后台控制
        if (istype == '1') {
            if (ismsg == '1') {
                isBarrage = true; //是否显示弹 幕
            } else {
                isBarrage = false;
            }

        } else {
            isBarrage = false; //是否显示弹幕
        }
        var getliveurl = '<?php echo $getliveurl ?>';
        var loading = false, page = 1, dataType = '1', isNoData = false, getDataUrl = getTeletextUrl;
        $(function() {
            var swiper = new Swiper('.swiper-container', {
                //pagination: '.swiper-pagination',
                paginationClickable: true,
                nextButton: '.swiper-button-next',
                prevButton: '.swiper-button-prev',
                paginationClickable: true,
                        // Disable preloading of all images
                        preloadImages: false,
                // Enable lazy loading
                lazyLoading: true
            });

            $('.section-tab').on('click', '.tab-link', function() {
                var $this = $(this);
                $this.addClass('selected-on').siblings('a').removeClass('selected-on');
                console.log($this.data('value') == '2');
                $("#typest").val($this.data('value'));
                if ($this.data('value') == '2') {//评论
                    $("#content_list").html('');
                    $('#footer').fadeIn() && $('#save_btn').removeAttr("disabled");
                    getDataUrl = getCommentUrl;
                } else {//图文
                    $('#footer').fadeOut();
                    getDataUrl = getTeletextUrl;
                }

                page = 1, isNoData = false, dataType = $this.data('value');
                getData(getDataUrl, page, dataType);
            });

            //评论框跳转
            $('#fdcontent').on('focus', function() {
                //$('.weui-infinite-scroll').hide();
                loading = true;
                //var h = $(document.body).height()+40;
                // console.log(h);
                //$('#footer').css({'position': 'relative', 'bottom': '0'});
                //window.scrollTo (0, h);
            }).on('blur', function() {
                //$('.weui-infinite-scroll').show();
                //$('#footer').css({'position': 'fixed', 'bottom': '0'});
                loading = false;
            });

            //是否显示弹幕
            $('.barrage-btn').on('click', function() {
                var $this = $(this);
                if ($this.hasClass('btn-off')) {
                    $this.removeClass('btn-off');
                    isBarrage = true;
                } else {
                    $this.addClass('btn-off');
                    isBarrage = false;
                    $('.barrage').remove();
                }
            });
            //提交评论
            $('#save_btn').on('click', function() {
                var $this = $(this);
                var content = $.trim($('#fdcontent').val());
                if (content == '') {
                    Msg.alert('请输入评论内容');
                    return false;
                }

                Msg.loading('请稍等...');
                $this.attr("disabled", "true");
                $.post(saveCommentUrl, $('#form_1').serialize(), function(re) {
                    Msg.hide();
                    $this.removeAttr("disabled");
                    if (re.status == '1') {
                        $('#form_1')[0].reset();
                        Msg.alert('评论成功');
						$(".feedback_tab").click();
                    } else {
                        Msg.alert(re.info);
                    }
                }, 'json');
            });

            //获取评论弹幕
            function getBarrage(url, p) {
                var rid = $('#rid').val();
                $.get(url, {'p': p, 'type': '2', 'rid': rid}, function(re) {
                    if (re.status == 1) {
                        var data = re.data.rs;
                        window.setInterval(function() {
                            var i = Math.round(data.length * Math.random());
                            if (isBarrage)
                                $box.barrager({info: data[i].username + ': ' + data[i].content});
                        }, 1000);
                    }
                }, 'json');
            }
            getBarrage(getCommentUrl, 1);

            function getData(url, p, type) {
                loading = true;
                var rid = $('#rid').val();
                $.get(getDataUrl, {'p': p, 'type': type, 'rid': rid}, function(re) {
                    loading = false;
                    if (re.status == 1) {
                        var data = re.data.rs;
                        var htmlArr = [];
                        $("#feedct").text(data.count);
                        for (var i = 0, l = data.length; i < l; i++) {
                            var _item = data[i];
                            if (type == '1') {
                                htmlArr.push('<div class="teletext-item" tid="' + _item.mid + '" id="list_' + _item.mid + '">');
                                htmlArr.push('<div class="tt-avatar" style="background-image: url(' + _item.avatar + ')"></div>');
                                htmlArr.push('<div class="cont-side">');
                                htmlArr.push('<div class="tt-top">');
                                htmlArr.push('<span class="name">' + _item.username + '</span>');
                                htmlArr.push('<span class="time">' + _item.add_time + '</span>');
                                htmlArr.push('</div>');
                                if (_item.image_status == '1') {
                                    var greysrc = $("#greysrc").val();
                                    var loadsrc = $("#loadsrc").val();
									console.log(_item.image.length);
//                                                console.log(loadsrc);
                                    //var imgsrc ='';
									for(var jj=0;jj<_item.image.length;jj++){
									    var imgsrc = '<img style="width:250px;background:url(' + loadsrc + ') 50% no-repeat; margin-bottom:10px;" src="' + greysrc + '"  data-echo="' + _item.image[jj] + '">';
										//console.log(imgsrc);
										htmlArr.push('<div class="tt-pic" data-url="' + _item.image[jj] + '">' + imgsrc + '</div>');
									}
                                    //var imgsrc = '<img style="width:250px;background:url(' + loadsrc + ') 50% no-repeat;" src="' + greysrc + '"  data-echo="' + _item.image + '">';
									//for(var i=0;i<_item.image.length;i++){
									    //htmlArr.push('<div class="tt-pic" data-url="' + _item.image[i] + '">' + imgsrc + '</div>');
									//}
                                    
                                }
                                htmlArr.push('<div class="tt-cont">' + _item.content + '</div>');
                                htmlArr.push('</div>');
                                htmlArr.push('</div>');
                            } else if (type == '2') {
                                $("#feedct").text(re.data.count);
                                htmlArr.push('<div class="comment-item" cid="' + _item.mid + '">');
                                htmlArr.push('<div class="com-info">');
                                htmlArr.push('<div class="com-avatar"><img src="' + _item.avatar + '"></div>');
                                htmlArr.push('<div class="in-side" >');
                                htmlArr.push('<div class="com-name">' + _item.username + '</div>');
                                htmlArr.push('<div class="com-time">' + _item.add_time + '</div>');
                                htmlArr.push('</div>');
                                htmlArr.push('</div>');
                                htmlArr.push('<div class="com-cont">' + _item.content + '</div>');
                                htmlArr.push('</div>');
                            }
                        }
                        var $list = $('#content_list');
                        page == 1 ? $list.html(htmlArr.join('')) : $list.append(htmlArr.join(''));
                        Echo.init({
                            offset: 200,
                            throttle: 500
                        });
                        page++;
                    } else {
//                            $("#content_list").html('');
                        isNoData = true;
                        p > 1 ? $('.weui-infinite-scroll').html('已显示全部') : $('.weui-infinite-scroll').html('暂无更多信息');
                    }
                }, 'json');
            }

            getData(getDataUrl, 1, '1');
            //加载更多
            $(document.body).infinite().on("infinite", function() {
                if (loading || isNoData)
                    return false;
                getData(getDataUrl, page, dataType);
            });


        });
    </script>


    <script type="text/javascript">
        $(function() {
            var liveurl = 'http:<?php echo $url; ?>';
            var istype = <?php echo $istype ?>;
            var $box = $('#video_box');
            //	    	window.setInterval(function(){
            //				if(isBarrage) $box.barrager({info: 'rs.username + rs.content '});
            //	    	}, 1000);

//        console.log('执行了');
            function getParams(name) {
                var reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)', 'i');
                console.log(reg);
                var r = window.location.search.substr(1).match(reg);
                console.log(window.location.search.substr(1));
                if (r != null) {
                    return decodeURIComponent(r[2]);
                }
                return null;
            }
            var rtmp = getParams('rtmp'),
                    flv = getParams('flv'),
                    m3u8 = getParams('m3u8'),
                    mp4 = getParams('mp4'),
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
                //m3u8: m3u8 ||'http://2668.liveplay.myqcloud.com/live/2668_textzhang.m3u8',
                //	            m3u8: m3u8 ||'http://2668.liveplay.myqcloud.com/live/2668_tengo_live1.m3u8',
                m3u8: m3u8 || liveurl,
                //m3u8: m3u8 || 'http://1251132611.vod2.myqcloud.com/4126dd3evodtransgzp1251132611/8a592f8b9031868222950257296/f0.f240.m3u8',
                //m3u8_hd: m3u8 || 'http://1251132611.vod2.myqcloud.com/4126dd3evodtransgzp1251132611/8a592f8b9031868222950257296/f0.f230.m3u8',
                // m3u8_sd: m3u8 || 'http://1251132611.vod2.myqcloud.com/4126dd3evodtransgzp1251132611/8a592f8b9031868222950257296/f0.f220.m3u8',
                mp4: mp4,
                coverpic: coverpic,
                //autoplay: autoplay ? true : false,
                autoplay: true,
                live: true,
                width: '100%',
                height: '100%',
                controls: 'system',
                wording: {
                    4: '直播已结束',
                    2032: '请求视频失败，请检查网络',
                    2048: '请求m3u8文件失败，可能是网络错误或者跨域问题'
                },
                listener: function(msg) {
                    //console.log(msg.type);
                }
            };
            console.log(options.m3u8);
            if (istype == 1) {
                var player = new TcPlayer('id_test_video', options);
            }
            //console.log(player);
        });
    </script>