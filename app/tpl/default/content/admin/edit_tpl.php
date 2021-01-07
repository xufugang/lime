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
                <label class="form-label col-xs-1 col-md-1 col-sm-2"><span class="c-red">*</span>用户名：</label>
                <div class="formControls col-xs-3 col-md-3 col-sm-5 pr-5">
                    <input type="text" class="input-text" value="<?php echo $rs['name']; ?>" placeholder="" id="name"
                           name="name"/>
                    <br/>
                    <small class="grey"></small>
                </div>
                <span class="input-unit"></span>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-1 col-md-1 col-sm-2">密码：</label>
                <div class="formControls col-xs-3 col-md-3 col-sm-4 pr-5">
                    <input type="password" class="input-text" value="" placeholder="不填表示不做修改" id="psw"
                           name="psw"/>
                    <br/>
                    <small class="grey"></small>
                </div>
                <span class="input-unit"></span>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-1 col-md-1 col-sm-2">确认密码：</label>
                <div class="formControls col-xs-3 col-md-3 col-sm-4 pr-5">
                    <input type="password" class="input-text" value="" placeholder=""
                           id="repsw" name="repsw"/>
                    <br/>
                    <small class="grey"></small>
                </div>
                <span class="input-unit"></span>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-1 col-md-1 col-sm-2">有效期：</label>
                <div class="formControls col-xs-3 col-md-3 col-sm-3 pr-5">
                    <input type="text" class="input-text" placeholder="为空表示长期有效"
                           value="<?php echo $rs['validity_time'] > 0 ? date("Y-m-d", $rs['validity_time']) : ''; ?>"
                           name="effective" id="effective" onClick="new Calendar().show(this);" readonly/>
                    <br/>
                    <small class="grey"></small>
                </div>
                <span class="input-unit"></span>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-1 col-md-1 col-sm-2"><span class="c-red">*</span>组别：</label>
                <div class="formControls col-xs-3 col-md-3 col-sm-6"> <span class="select-box" style="width:150px;">
			<select class="select" name="group_id" id="group_id" size="1">
            <?php
            foreach ($setting['user_type'] as $key => $val) {
                ?>
                <option value="<?php echo $key; ?>" <?php if ($key == $rs['group_id']) {
                    echo 'selected';
                } ?>><?php echo $val; ?></option>
                <?php
            }
            ?>
			</select>
			</span>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-4  col-sm-2"><span class="c-red">*</span>城市：</label>
                <div class="formControls col-xs-3 col-md-3 col-sm-6"> <span class="select-box" style="width:90px;">
			<select class="select" name="province" id="province" size="1">
                  <option value="-1">-请选择-</option>
                <?php
                foreach ($city as $key => $val) {
                    ?>
                    <option value="<?php echo $val['city_id']; ?>" <?php echo $val['city_id'] == $p ? 'selected' : ''; ?>><?php echo $val['name']; ?></option>
                    <?php
                }
                ?>
			</select>
			</span>
                    <span class="select-box" style="width:90px;">
			<select class="select" name="city" id="city" size="1">
              <?php
              foreach ($clist as $k => $v) {
                  ?>
                  <option value="<?php echo $v['city_id']; ?>" <?php echo $v['city_id'] == $rs['city_id'] ? 'selected' : ''; ?>><?php echo $v['name']; ?></option>
                  <?php
              }
              ?>
			</select>
			</span>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-1 col-md-1 col-sm-2">状态：</label>
                <div class="formControls col-md-9 skin-minimal">
                    &nbsp;&nbsp;&nbsp;
                    <?php
                    foreach ($setting['status'] as $key => $val) {
                        ?>
                        <div class="radio-box">
                            <input name="status" type="radio" value="<?php echo $key; ?>"
                                   id="status-<?php echo $key; ?>" <?php if ($key == $rs['status']) {
                                echo 'checked';
                            } ?>/>
                            <label for="status-<?php echo $key; ?>"><?php echo $val; ?></label>
                        </div>
                        <?php
                    }
                    ?>
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
                        minlength: 2,
                        maxlength: 20
                    }
                },
                onkeyup: false,
                focusCleanup: false,
                success: "valid",
                submitHandler: function (form) {
                    Msg.loading();
                    $.post('<?php echo U('admin/save');?>', $("#form1").serialize(), function (res) {
                        Msg.hide();
                        if (res.status == 1) {
                            Msg.ok('修改成功', function () {
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

            //城市选择
            $('#province').change(function () {
                $('#city').empty();
                var value = $(this).val();
                if (value < 0) {
                    var inhtml = "<option value=-1>-请选择-</option>";
                    $("#city").append(inhtml);
                } else {
                    $.ajax({
                        url: "<?php echo U('admin/getcity'); ?>",
                        type: "post",
                        dataType: "json",
                        data: {"city_id": value},
                        success: function (result) {
                            if (result.status == 1) {
                                $("#city").append(result.data);
                            }
                        }
                    });
                }


            })
        });
    </script>
<?php
include getTpl('footer', 'public');
?>