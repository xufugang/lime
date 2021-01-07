<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'upload-index', //页面标示
    'pagename' => '上传文件', //当前页面名称
    'keywords' => '', //关键字
    'description' =>'', //描述
    'mycss' => array(), //加载的css样式表
    'myjs' => array(), //加载的js脚本
	'footerjs'=>array(),
    'head' => true, //加载头部文件
);
include getTpl('header', 'public');
?>
<body>
<style type="text/css">
<!--
*{margin:0;padding:0;background:#fff;font:12px Verdana}
.a-upload{padding:4px 10px;height:20px;line-height:20px;position:relative;cursor:pointer;color:#FFF;background:#006AE8;border:1px solid #006AE8;border-radius:4px;overflow:hidden;display:inline-block;text-decoration:none}
.a-upload input{position:absolute;font-size:100px;right:0;top:0;opacity:0;filter:alpha(opacity=0);cursor:pointer}
.a-upload:hover{color:#fff;background:#006AE8;border-color:#006AE8;text-decoration:none}
-->
</style>
<!--[if IE]>
<style type="text/css">
.input{border:1px solid #718da6;}
</style>
<![endif]-->
<form action="<?php echo U('upload/up');?>" method="post" enctype="multipart/form-data" name="upform" onSubmit="return checkform();">
<a href="javascript:;" class="a-upload"><input name="upimg" id="file" type="file"/>点击上传文件</a>
  <input name="Submit" type="submit" id="sub-ok" value="" style="display:none" />
  <input type="hidden" name="id" id="id" value="<?php echo $id ?>">
  <input type="hidden" name="callback" id="callback" value="<?php echo $callback; ?>">
  <input type="hidden" name="path" id="path" value="<?php echo $dir; ?>">
</form>
<script>
    parent.Msg.hide();
    function $$(sID) {
        return document.getElementById(sID);
    }
    var allow_file_type='<?php echo $upload_img_type; ?>';
    var field = $$("file");
	field.onchange=function(){
		parent.Msg.loading('正在上传...');
		$$("sub-ok").click();
	}
    function getFileExtension(filePath) { //v1.0
        fileName = ((filePath.indexOf('/') > -1) ? filePath.substring(filePath.lastIndexOf('/')+1,filePath.length) : filePath.substring(filePath.lastIndexOf('\\')+1,filePath.length));
//        console.log(fileName.substring(fileName.lastIndexOf('.')+1,fileName.length));
        return fileName.substring(fileName.lastIndexOf('.')+1,fileName.length);
    }
    function checkFileUpload(extensions) { //v1.0
        console.log(extensions.toLowerCase().indexOf(getFileExtension(field.value).toLowerCase()));
        if (extensions.toLowerCase().indexOf(getFileExtension(field.value).toLowerCase()) == -1) {
            parent.Msg.error('这种文件类型不允许上传!');
            field.focus();
            return false;
        }
        return true;
    }
    function checkform(){
        if (field.value == '') {
            parent.Msg.error('文件框中必须保证已经有文件被选中!');
            field.focus();
            return false;
        }
        if (allow_file_type){
            return checkFileUpload(allow_file_type);
        }
    }
</script>
</body>
</html>