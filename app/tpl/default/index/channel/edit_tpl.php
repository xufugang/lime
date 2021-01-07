<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'edit', //页面标示
    'pagename' => '编辑', //当前页面名称
    'keywords' => '', //关键字
    'description' => '', //描述
    'mycss' => array(), //加载的css样式表
    'myjs' => array('admin/calendar', 'admin/lib/jquery.validate.1.14.0.min'),
    'footerjs' => array(),
    'head' => true,
    'copyright' => true
);
include getTpl('header', 'public');
?>
    <article class="page-container">
        <form class="form form-horizontal" id="form1">

            <div class="row cl">
                <label class="form-label col-xs-1 col-md-1 col-sm-2"><span class="c-red">*</span>名称：</label>
                <div class="formControls col-xs-3 col-md-3 col-sm-6 pr-5">
                    <input type="text" class="input-text" value="<?php echo $rs['name']; ?>" placeholder="" id="name" name="name"/>
                    <br/>
                    <small class="grey">格式：数字或英文字符，特殊符号只支持_。20个字符内</small>
                </div>
                <span class="input-unit"></span>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-1 col-md-1 col-sm-2">内容：</label>
                <div class="formControls col-xs-3 col-md-3 col-sm-6 pr-5">
                    <textarea name="content" id="content" class="textarea" placeholder="频道备注......100个字符以内" dragonfly="true"
                              onKeyUp="textarealength(this,100)"><?php echo $rs['content']; ?></textarea>
                    <p class="textarea-numberbar"><em class="textarea-length">0</em>/100</p>
                </div>
            </div>

            <div class="row cl">
                <div class="col-md-9 col-md-offset-1 col-sm-offset-2">
                    <input type="hidden" name="id" value="<?php echo $rs['id']; ?>">
                    <input class="btn btn-primary radius" type="submit" value="&nbsp;&nbsp;提交&nbsp;&nbsp;"/>
                </div>
            </div>
        </form>
    </article>
    <script>
        $(function () {
            $('.skin-minimal input').iCheck({
                checkboxClass: 'icheckbox-blue',
                radioClass: 'iradio-blue',
                increaseArea: '20%'
            });
            $("#form1").validate({
                //验证字段
                rules: {
                    name: {
                        required: true,
                        minlength:2,
                        maxlength:20
                    }
                },
                onkeyup: false,
                focusCleanup: false,
                success: "valid",
                submitHandler: function (form) {
                    Msg.loading();
                    $.post('<?php echo U('channel/save');?>', $("#form1").serialize(), function (res) {
                        Msg.hide();
                        if (res.status == 1) {
                            Msg.ok('更新成功', function () {
                                //关闭弹出层并刷新页面
                                var index = parent.layer.getFrameIndex(window.name);
                                parent.location.replace(parent.location.href);
                                parent.layer.close(index);
                            }, 1500);
                        } else {
                            Msg.error(res.info);
                        }
                    }, 'json');
                }
            });
        });
    </script>
<?php
include getTpl('footer', 'public');
?>