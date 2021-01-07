<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'login', //页面标示
    'pagename' => '', //当前页面名称
    'keywords' => '', //关键字
    'description' =>'', //描述
    'mycss' => array('admin/h-ui.admin/css/h-ui.login'), //加载的css样式表
    'myjs' => array('admin/socket.io.1.3.7','global/jquery.qrcode.min'), //加载的js脚本
	'footerjs'=>array(),
	'head'=>true
);
include getTpl('header', 'public');
?>
<div class="header"></div>
<div class="loginWraper">
  <!-- 新登陆框 B -->
  <div class="new-loginBox">
     <div class="logo-shehui"></div>
  	 <div class="row cl form-box">
        <div class="col-xs-7">
        <form id="loginform" class="form form-horizontal" action="" method="post">
          <div class="row cl">
            <label class="form-label col-xs-2"></label>
            <div class="formControls formUser col-xs-8">
              <input id="user" name="user" type="text" placeholder="输入帐户或者手机号码" class="input-text">
            </div>
          </div>
          <div class="row cl">
            <label class="form-label col-xs-2"></label>
            <div class="formControls formPass col-xs-8">
              <input id="password" name="password" type="password" placeholder="密码" class="input-text">
            </div>
          </div>
          <div class="row cl">
            <div class="formControls col-xs-8 col-xs-offset-3">
              <label for="remember">
              <input type="checkbox" name="remember" id="remember" value="1209600">使我保持登录状态</label>
            </div>
          </div>
          <div class="row cl">
            <div class="formControls col-xs-8 col-xs-offset-3">
              <input name="" type="submit" class="btn btn-success radius size-L" value="&nbsp;登&nbsp;&nbsp;&nbsp;&nbsp;录&nbsp;">
              <input name="" type="reset" class="btn btn-default radius size-L" value="&nbsp;取&nbsp;&nbsp;&nbsp;&nbsp;消&nbsp;" style="margin-left:20px">
              <input name="refer" type="hidden" value="<?php echo $refer;?>">
              <input name="key" type="hidden" value="<?php echo $key;?>">
              <input name="__hash__" id="__hash__" value="<?php echo $hash;?>" type="hidden"/>
            </div>
          </div>
        </form>
        </div>
  	 </div>
  </div>
  <!-- 新登陆框 E -->
</div>
<script>
    $(function(){
        $("#loginform").submit(function(){
            if (!$("#user").val()||!$("#password").val()){
                Msg.error('请输入帐号和密码');
                return false;
            }
            Msg.loading('正在登录，请稍候...');
            $.post('<?php echo U('login/ajaxlogin');?>',$(this).serialize(),function(result){
                if (result.status==1){
                    location.href=result.data.refer;
                }else{
                    Msg.error(result.info);
                }
            },'json');
            return false;
        })
    })
</script>
<?php include getTpl('footer', 'public');?>
