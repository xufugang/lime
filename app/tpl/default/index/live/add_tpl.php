<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'add-sign', //页面标示
    'pagename' => '添加直播', //当前页面名称
    'keywords' => '', //关键字
    'description' => '', //描述
    'mycss' => array('date/smoothness.min', 'admin/lib/activity/laydate', 'admin/lib/activity/fonts/iconfont', 'admin/lib/activity/style'), //加载的css样式表
    'myjs' => array('date/smoothness.min', 'admin/laydate', 'admin/iconfont'), //加载的js脚本
    'footerjs' => array('admin/lib/jquery.validate.1.14.0.min', './../ueditor_1.4/editor_config', './../ueditor_1.4/editor.min', './../ueditor_1.4/third-party/zeroclipboard/zeroclipboard.min'),
    'head' => true,
    'copyright' => true
);
include getTpl('header', 'public');
?>
    <article class="page-container">
        <div class="title-page">添加直播</div>
        <form class="form form-horizontal" id="form1">
            <!-- 基本设置 B -->
            <p class="form-title mt-30"><strong>基本设置<span class="c-red">(必填)</span></strong></p>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-2">直播标题：</label>
                <div class="formControls col-xs-4 col-sm-5">
                    <input type="text" class="input-text radius" value="" placeholder="直播标题" id="title" name="title">
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-2">直播类型：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <?php foreach ($setting['live_type'] as $key => $val) { ?>
                        <div class="radio-box">
                            <input type="radio" id="radio_type_<?php echo $key ?>" name="type"
                                   value="<?php echo $key ?>"
                                <?php echo $key == 1 ? 'checked' : ''; ?>>
                            <label for="radio_type_<?php echo $key ?>"><?php echo $val; ?></label>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="row cl" id="channel_show" style="display: none">
                <label class="form-label col-xs-4 col-sm-2">直播频道：</label>
                <div class="formControls col-xs-4 col-sm-4"> <span class="select-box" style="width:150px;">
                <select class="select" name="channel_id" id="channel_id" size="1">
                </select>
                </span>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-2">直播城市：</label>
                <div class="formControls col-xs-4 col-sm-4">  <span class="select-box" style="width:90px;">
                <select class="select" name="province" id="province" size="1">
                      <option value="-1">-请选择-</option>
                    <?php
                    foreach ($city as $key => $val) {
                        ?>
                        <option value="<?php echo $val['city_id']; ?>"><?php echo $val['name']; ?></option>
                        <?php
                    }
                    ?>
                </select>
			    </span>&nbsp;
                    <span class="select-box" style="width:90px;">
                    <select class="select" name="city" id="city" size="1">
                    <option value="-1">-请选择-</option>
                    </select>
                    </span>
                </div>
            </div>

            <div class="row cl" id="liver_show" style="display: none">
                <label class="form-label col-xs-4 col-sm-2">直播员：</label>
                <div class="formControls col-xs-4 col-sm-4" id="liverhtml">
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-2">直播时间：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <div class="input-date-item"><input type="text" class="input-text radius" id="test16" name="endtime"
                                                        placeholder="开始时间 - 结束时间"
                                                        lay-key="17" readonly="readonly"></div>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-2">用户评论：</label>
                <div class="formControls col-xs-4 col-sm-5">
                    <div class="radio-box">
                        <input type="radio" id="radio_auth_1" name="is_comment" value="2" checked>
                        <label for="radio_auth_1">开启</label>
                    </div>
                    <div class="radio-box">
                        <input type="radio" id="radio_auth_2" name="is_comment" value="1">
                        <label for="radio_auth_2">关闭</label>
                    </div>
                </div>
            </div>

            <div class="row cl" id="barrage_show">
                <label class="form-label col-xs-4 col-sm-2">直播弹幕：</label>
                <div class="formControls col-xs-4 col-sm-5">

                    <div class="radio-box">
                        <input type="radio" id="radio_barrage_2" name="is_barrage" value="2">
                        <label for="radio_auth_2">开启</label>
                    </div>
                    <div class="radio-box">
                        <input type="radio" id="radio_barrage_1" name="is_barrage" value="1" checked>
                        <label for="radio_auth_1">关闭</label>
                    </div>
                </div>
            </div>

            <div class="form-title mt-30"><strong>其他设置</strong><a href="javascript:;" class="more-set-btn"
                                                                  id="more_set_btn" data-value="section_more"><i
                            class="font_family"></i></a></div>
            <div class="info" id="section_more" style="display:none;">
                <div class="row cl" id="guest_show" style="display: none;">
                    <label class="form-label col-xs-4 col-sm-2">直播嘉宾：</label>
                    <div class="col-xs-4 col-sm-4">
                        <div class="custom-list-section" id="guest">
                        </div>
                    </div>
                </div>

                <div class="row cl" id="imgbox">
                    <label class="form-label col-xs-4 col-sm-2">顶部图片：</label>
                    <div class="formControls col-xs-4 col-sm-5">
                        <input type="hidden" class="upload-img-input" value="" id="pic_url" name="top_pic"
                               readonly="readonly">
                        <img src="#" class="big-img-show upload-img-show">
                        <div class="font_family upload-style-1">
                            <iframe name="myframe1" onload="iframeShow('myframe1')"
                                    src="<?php echo U('upload/index', array('id' => 'pic_url', 'callback' => 'picUploadCallback')); ?>"
                                    scrolling="no"
                                    frameborder="0"></iframe>
                        </div>
                    </div>
                </div>

                <div class="row cl" style="margin-top: 5px;">
                    <label class="form-label col-xs-4 col-sm-2"></label>
                    <div class="formControls col-xs-4 col-sm-5">
                        <p class="f-12 c-999">头图建议750px*550px，不超过500kb</p>
                    </div>
                </div>

                <div class="row cl" id="imgbox">
                    <label class="form-label col-xs-4 col-sm-2">渐变图：</label>
                    <div class="formControls col-xs-4 col-sm-5">
                        <input type="hidden" class="upload-img-input" value="" id="change_pic" name="change_pic"
                               readonly="readonly">
                        <img src="#" class="big-img-show upload-img-show"></img>
                        <div class="font_family upload-style-1">
                            <iframe name="myframe2" onload="iframeShow('myframe2')"
                                    src="<?php echo U('upload/index', array('id' => 'change_pic', 'callback' => 'picUploadCallback')); ?>"
                                    scrolling="no"
                                    frameborder="0"></iframe>
                        </div>
                    </div>
                </div>

                <div class="row cl" style="margin-top: 5px;">
                    <label class="form-label col-xs-4 col-sm-2"></label>
                    <div class="formControls col-xs-4 col-sm-5">
                        <p class="f-12 c-999">头图建议750px*550px，不超过500kb</p>
                    </div>
                </div>

                <div class="row cl show-news-content">
                    <label class="form-label col-xs-4 col-sm-2">直播说明：</label>
                    <div class="formControls col-md-8">
                        <script type="text/plain" id="editor" name="introduce"></script>
                        <textarea name="introduce" id="introduce" style="display:none"></textarea>
                    </div>
                </div>

                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-2">设定人数：</label>
                    <div class="formControls col-xs-4 col-sm-5">
                        <input type="text" class="input-text radius" value="" placeholder="" id="view_num"
                               name="view_num">
                    </div>
                </div>
                <div class="row cl" style="display: none">
                    <label class="form-label col-xs-4 col-sm-2">推流地址：</label>
                    <div class="formControls col-xs-4 col-sm-5">
                        <input type="text" class="input-text radius" value="" placeholder=""
                               id="rtmp_url"
                               name="rtmp_url">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-2">分享标题：</label>
                    <div class="formControls col-xs-4 col-sm-5">
                        <input type="text" class="input-text radius" value="" placeholder="" id="share_title"
                               name="share_title">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-2">分享内容：</label>
                    <div class="formControls col-xs-4 col-sm-5">
                        <input type="text" class="input-text radius" value="" placeholder="内容在30个字符以内"
                               id="share_content" name="share_content">
                    </div>
                </div>
                <div class="row cl" id="imgbox">
                    <label class="form-label col-xs-4 col-sm-2">分享图片：</label>
                    <div class="formControls col-xs-4 col-sm-5">
                        <input type="hidden" class="upload-img-input" value="" id="share_pic" name="share_pic"
                               readonly="readonly">
                        <img src="#" class="big-img-show upload-img-show">
                        <div class="font_family upload-style-1">
                            <iframe name="myframe3" onload="iframeShow('myframe3')"
                                    src="<?php echo U('upload/index', array('id' => 'share_pic', 'callback' => 'picUploadCallback')); ?>"
                                    scrolling="no"
                                    frameborder="0"></iframe>
                        </div>
                    </div>
                </div>

                <div class="row cl" style="margin-top: 5px;">
                    <label class="form-label col-xs-4 col-sm-2"></label>
                    <div class="formControls col-xs-4 col-sm-5">
                        <p class="f-12 c-999">请上传您的商品图片，标准尺寸80px*80px</p>
                    </div>
                </div>
            </div>


            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-2"></label>
                <div class="col-xs-4 col-sm-6 col-md-offset-1">
                    <input type="hidden" id="upurl"
                           value="<?php echo U("upload/index", array("id" => "vote_pic_url")); ?>">
                    <input class="btn btn-primary radius" type="submit" value="&nbsp;&nbsp;提交&nbsp;&nbsp;">
                </div>
            </div>
        </form>
    </article>
    <script>
        var UEDITOR_HOME_URL = '<?php echo LOCAL_PUBLIC_PATH;?>ueditor_1.4/',
            fixedImagePath = '',
            postPicUrl = '<?php echo U('upload/save');?>',
            editorUploadConfigUrl = '<?php echo U('upload/editor');?>';

        function ueditorUploadCallBack(data) {
            console.log(data);
        }

        $(function () {
            window.msg_editor = new UE.ui.Editor({
                toolbars: [
                    ["source", "undo", "redo", "bold", "italic", "underline", "forecolor", "backcolor", "justifyleft", "justifycenter", "justifyright", "justifyjustify", "indent", "insertunorderedlist", "insertorderedlist", "|", "searchreplace", "removeformat", "autotypeset", "paragraph", "fontfamily", "fontsize", "rowspacingtop", "lineheight", "inserttable", "insertimage", "emotion", "insertvideo"]
                ],
                initialFrameWidth: '90%',
                initialFrameHeight: 400,
            });

            window.msg_editor.render("editor");
        });
    </script>
    <script>
        function rstart() {
            console.log('res');
        }

        //图片上传回调（选项图上传完成回调）
        function picUploadCallback(re) {
            var $pic = $('.upload-img-input');
            $pic.each(function (index, el) {
                var $this = $(el);
                var val = $this.val();
                if (val.search("yun") != -1) {
                    if (val) $this.siblings('.upload-img-show').attr('src', "<?php $upConf = C('upload', 'yun');  echo $upConf['url']; ?>" + val).show();
                } else {
                    if (val) $this.siblings('.upload-img-show').attr('src', MAIN_PATH + val).show();
                }
                //if (val) $this.siblings('.upload-img-show').attr('src', imgUrl).show();
            });
        }

        function iframeShow(name) {
            //隐藏上传默认样式
            window.frames[name].document.getElementsByClassName("a-upload").item(0).className = "a-upload hide";
        }
    </script>
    <script>
        $(function () {
            //单选按钮，评论和弹幕
            $('input[type=radio][name=is_comment]').change(function () {
                if (this.value == '1') {
                    $("#barrage_show").hide();
                }
                else if (this.value == '2') {
                    $("#barrage_show").show();

                }
            });
            //省份选择
            $('#province').change(function () {
                $('#city').empty();
                var value = $(this).val();
                if (value < 0) {
                    var inhtml = "<option value=-1>-请选择-</option>";
                    $("#city").append(inhtml);
                    $("#liver_show").hide();
                    $("#guest_show").hide();
                    $("#liverhtml").empty();
                    $("#guest").empty();
                } else {
                    $.ajax({
                        url: "<?php echo U('admin/getcity'); ?>",
                        type: "post",
                        dataType: "json",
                        data: {"city_id": value},
                        success: function (result) {
                            if (result.status == 1) {
                                var inhtml = "<option value=-1>-请选择-</option>";
                                $("#city").append(inhtml);
                                $("#city").append(result.data);
                            }
                        }
                    });
                }
            });
            //城市改变，获取城市站下的直播员
            $('#city').change(function () {
                $('#liverhtml').empty();
                var value = $(this).val();
                if (value < 0) {
                    //var inhtml = "<option value=-1>-请选择-</option>";
                    //$("#city").append(inhtml);
                    $("#liver_show").hide();
                    $("#guest_show").hide();
                    $("#liverhtml").empty();
                    $("#guest").empty();
                } else {
                    $.ajax({
                        url: "<?php echo U('live/getcity'); ?>",
                        type: "post",
                        dataType: "json",
                        data: {"city_id": value},
                        success: function (result) {
                            if (result.status == 1) {
                                $("#liverhtml").append(result.data.liverhtml);
                                $("#guest").html(result.data.guesthtml);
                                if (result.data.liverhtml.length > 0) {
                                    $("#liver_show").show();
                                    $("#guest_show").show();
                                } else {
                                    $("#liver_show").hide();
                                    $("#guest_show").hide();
                                }
                                //ajax请求过来的需要重新渲染
                                $('#liverhtml input').iCheck({
                                    checkboxClass: 'icheckbox-blue',
                                    radioClass: 'iradio-blue',
                                    increaseArea: '20%'
                                });
                                $('#guest input').iCheck({
                                    checkboxClass: 'icheckbox-blue',
                                    radioClass: 'iradio-blue',
                                    increaseArea: '20%'
                                });
                            }
                        }
                    });
                }
            });
            //时间选择器
            laydate.render({
                elem: '#test16'
                , type: 'datetime'
                , range: '-'
                , format: 'yyyy/MM/dd HH:mm:ss'
                , done: function (value, date, endDate) {
                    if (value) {
                        $.post("<?php echo U('live/get_channel'); ?>", {"time": value}, function (result) {
                            Msg.hide();
                            if (result.status == 1) {
                                $('#channel_id').empty();
                                $('#channel_show').show();
                                $('#channel_id').append(result.data.innerhtml);
                                $('#rtmp_url').val(result.data.rtmp_url);
                            } else {
                                Msg.error(result.info);
                            }
                        }, 'json');
                    }

                }
            });
            //其他设置
            $(document).on('click', '#more_set_btn', function (e) {
                //更多设置展开、隐藏
                var $this = $(this);
                var f_id = $this.data('value');
                $this.hasClass('more-opened') ? $('#' + f_id).slideUp() && $this.removeClass('more-opened') : $('#' + f_id).slideDown() && $this.addClass('more-opened');
            });


            $('.radio-box input').iCheck({
                checkboxClass: 'icheckbox-blue',
                radioClass: 'iradio-blue',
                increaseArea: '20%'
            });

            //表单提交
            $("#form1").validate({
                rules: {
                    title: {
                        required: true,
                    },
                    province: {
                        required: true,
                    },
                    rtime: {
                        required: true,
                    },
                    share_content: {
                        maxlength: 30,
                    },
                    view_num: {
                        digits: true
                    }

                },
                onkeyup: false,
                focusCleanup: false,
                success: "valid",
                submitHandler: function (form) {
                    var str = [];
                    Msg.loading();
                    $.post('<?php echo U('live/post');?>', $("#form1").serialize(), function (result) {
                        Msg.hide();
                        if (result.status == 1) {
                            Msg.ok('操作成功', function () {
                                var index = parent.layer.getFrameIndex(window.name);
                                parent.location.replace(parent.location.href);
                                parent.layer.close(index);
                            });
                            return false;
                        } else {
                            Msg.error(result.info);
                        }
                    }, 'json');

                }
            });

        });
    </script>
<?php
include getTpl('footer', 'public');
?>