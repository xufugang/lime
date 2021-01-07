<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'edit', //页面标示
    'pagename' => '编辑用户', //当前页面名称
    'keywords' => '', //关键字
    'description' =>'', //描述
    'mycss' => array(), //加载的css样式表
    'myjs' => array('admin/calendar','admin/lib/jquery.validate.1.14.0.min'), //加载的js脚本
	'footerjs'=>array(),
	'head'=>true
);
include getTpl('header', 'public');
?>
<article class="page-container">
	<form class="form form-horizontal" id="form1">
        <div class="row cl">
            <label class="form-label col-xs-3 col-sm-3">密码：</label>
            <div class="formControls col-xs-5 col-sm-4">
                <input type="password" class="input-text" autocomplete="off" value="" placeholder="密码" id="original_psw" name="original_psw">
            </div>
        </div>
    	<div class="row cl">
		<label class="form-label col-xs-3 col-sm-3">新密码：</label>
		<div class="formControls col-xs-5 col-sm-4">
			<input type="password" class="input-text" autocomplete="off" value="" placeholder="新密码" id="psw" name="psw">
		</div>
	</div>
	<div class="row cl">
		<label class="form-label col-xs-3 col-sm-3">确认密码：</label>
		<div class="formControls col-xs-5 col-sm-4">
			<input type="password" class="input-text" autocomplete="off"  placeholder="确认新密码" id="repsw" name="repsw">
		</div>
	</div>
	<div class="row cl">
		<div class="col-xs-8 col-sm-9 col-xs-offset-4 col-sm-offset-3">
			<button type="submit" class="btn btn-success radius"><i class="Hui-iconfont">&#xe632;</i> 保存</button>
		</div>
	</div>
	</form>
</article>
<script>
$(function(){
	$("#form1").validate({
		rules:{
            original_psw:{
                minlength: 6,
                maxlength: 20,
                required:true,
            },
			psw:{
                minlength: 6,
                maxlength: 20,
				required:true,
			},
			repsw:{
				required:true,
				equalTo: "#psw",
			}
		},
		onkeyup:false,
		focusCleanup:true,
		success:"valid",
		submitHandler:function(form){
			Msg.loading();
			$.post('<?php echo U('my/savepsw');?>',$("#form1").serialize(),function(result){
				Msg.hide();
				if (result.status==1){
				  Msg.ok('操作成功',function(){
					var index = parent.layer.getFrameIndex(window.name);
					parent.location.replace(parent.location.href);
					parent.layer.close(index);	  
				 },1500);
			  }else{
				  Msg.error(result.info);
			  }
			},'json');
		}
	});
});
</script> 
<?php
include getTpl('footer', 'public');
?>