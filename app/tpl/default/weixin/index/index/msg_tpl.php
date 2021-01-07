<?php

if (!defined('IN_XLP')) {

    exit('Access Denied');

}

$Document = array(
    'pageid' => 'msg', //页面标示
    'pagename' => '视频', //当前页面名称
    'mycss' => array('msg/swiper.min', 'msg/style','admin/h-ui.admin/css/style','admin/lib/hui-iconfont-1.0.7/iconfont'), //加载的css样式表
    'myjs' => array('global/jquery.2.1.4.min', 'global/swiper.min','admin/socket.io.1.3.7','admin/msgbox'), //加载的js脚本
    'footerjs' => array(),

    'head' => true, //是否加载头部文件

    'wxjsapi' => true, //是否需要微信js接口

);

include getTpl('header', 'public');

?>
   <style>
   .t-intr{ padding-right:15px; }
   .author{ color:#666; font-size:14px; display:block; }
   .delete {
       border:1px solid;
       color:#CCCCCC;
       width:60px;
       text-align: center;
       background-color:#BE8C78;
       margin-left: 15px;
       border-radius:5px;
       margin-bottom: 10px;
       cursor:pointer;
    }
   </style>
   <ul id="msg">

       <?php if($rs){?>

        <?php foreach ($rs as $k=>$v){?>
            <li class="time-list" id="list_<?php echo $v['mid'];?>">
                <div class="t-left">

                    <div class="t-icon1"></div>
                    <div class="t-txt"><span class="t-t1"><?php echo substr(outTime($v['add_time'],1),10);?></span><span class="t-t2"><?php echo $v['address']?></span></div>
                    <div class="t-icon2"><span></span></div>

                </div>

                <div class="t-cont">
                    <p class="t-intr"><span class="author"><?php echo $v['username']?>:</span><?php echo $v['content'];?></p>  
                    <?php 
					if($v['pic_url']){
					$pic_url = json_decode($v['pic_url'], true);
                    if ($pic_url) {
                        foreach ($pic_url as $pv) {
                            //$rs[$k]['image'][] = getImgUrl($pv);
					?>
                    <div class="t-pic"><img src="<?php echo getImgUrl(getThumb($pv, 7));?>" width="100%"></div>
                    <?php
                        }
                    } else {
                        //$rs[$k]['image'][] = getImgUrl($v['pic_url']);
                    ?>
                    <div class="t-pic"><img src="<?php echo getImgUrl(getThumb($v['pic_url'], 7));?>" width="100%"></div>
                    <?php
                    }
					?>
                    
                    <?php 
					
					}
					?>
		    <?php if( $type == 1 ){ ?>
                    <div class="delete">
                         <a class="del" rel="<?php echo $v['mid'];?>" style="color:white;font-weight: blod;">删除</a>
                    </div>
                    <?php }?>
                </div>

            </li>
       <?php }}?>         
    </ul>
<!--<iframe id="c_iframe"  height="0" width="0"  src="http://game.hzforall.com/h5/2017/0627guangyu/agent.html" style="display:none" ></iframe>
<script type="text/javascript">
        (function autoHeight(){
            var b_width = Math.max(document.body.scrollWidth,document.body.clientWidth);
            var b_height = Math.max(document.body.scrollHeight,document.body.clientHeight);
            var c_iframe = document.getElementById("c_iframe");
            c_iframe.src = c_iframe.src + "#" + b_width + "|" + b_height;  // 这里通过hash传递b.htm的宽高
        })();
</script>-->

<?php

if ($getSocketUrl){

?>
<script>
    $(function(){
        var deleteUrl = '<?php echo $deleteurl?>';
        var token = '<?php echo $token;?>';

        $(document).on('click','.del',function(){

            var id = $(this).attr('rel');
   
            $.post(deleteUrl,{"id":id,'token':token},function(result){

					Msg.hide();

					if (result.status==1){

						Msg.ok('删除成功',function(){
                                                    $("#list_"+id).remove();
                                                },1000);		

					}else{

						Msg.error(result.info);

					}

				},'json');
                                return false;
        
        });

		
        
    })
</script>
<script>

$(function () {
    var mtype='<?php echo $type;?>';
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
                        var deldiv;
                        if(mtype==1){
                            deldiv = '<div style="border:1px solid;color:#CCCCCC;width:60px;text-align: center;background-color:#BE8C78;margin-left: 15px;border-radius:5px;margin-bottom: 10px;cursor:pointer;"><a class="del" rel="'+ data.id +'" style="color:white;font-weight: blod;">删除</a></div>';
                        }else{
                            deldiv = ' ';
                        }
                        
                        var cnt = '<li class="time-list" id="list_'+data.id+'"><div class="t-left"><div class="t-icon1"></div>'+
                            '<div class="t-txt"><span class="t-t1">'+data.add_time+'</span><span class="t-t2">'+data.address+'</span></div>'+
                            '<div class="t-icon2"><span></span></div></div>'+
                            '<div class="t-cont"><p class="t-intr"><span class="author">'+data.username+': </span>'+ data.msg+'</p>'+img+deldiv;            
                      $("#msg").prepend(cnt);
                    }else if(data.type==2){
                        $("list_"+data.id).remove();
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

		//console.log("reconnecting："+reconnectFailCount);

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

<?php }?>

</body>

</html>



