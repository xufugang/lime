<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'edit', //页面标示
    'pagename' => '编辑', //当前页面名称
    'keywords' => '', //关键字
    'description' => '', //描述
    'mycss' => array('admin/lib/live/style', 'admin/lib/live/fonts/iconfont'), //加载的css样式表
    'myjs' => array('admin/calendar', 'admin/lib/jquery.validate.1.14.0.min'),
    'footerjs' => array(),
    'head' => true,
    'copyright' => true
);
include getTpl('header', 'public');
?>
    <style>
        .upload-list-box {
            display: flex;
            display: -webkit-flex;
            position: relative;
            margin-right: 15px;
            flex-wrap: wrap;
        }

        .upload-list__item {
            flex: 0 0 auto;
            width: 81px;
            margin-right: 15px;
            margin-bottom: 15px;
            position: relative;
        }

        .upload-list__item .del-link {
            position: absolute;
            top: -10px;
            right: -5px;
            font-size: 20px;
            color: red;
        }

        .upload-list__item .del-link:hover {
            text-decoration: none;
        }
    </style>
    <article class="page-container">
        <form class="form form-horizontal" id="form1">

            <div class="row cl">
                <label class="form-label col-xs-1 col-md-1 col-sm-2"><span class="c-red">*</span>发布人：</label>
                <div class="formControls col-xs-3 col-md-3 col-sm-6"> <span class="select-box" style="width:150px;">
                    <select class="select" name="name" id="name" size="1">
                        <?php
                        foreach ($liver as $key => $val) {
                            ?>
                            <option value="<?php echo $val['id']; ?>" <?php if ($val['id'] == $sel) {
                                echo 'selected';
                            } ?>><?php echo $val['name']; ?></option>
                            <?php
                        }
                        ?>
                    </select>
			    </span>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-2">信息简介：</label>
                <div class="formControls col-xs-5 col-md-5 col-sm-5">
                    <textarea name="introduce" id="introduce" class="textarea" placeholder="" dragonfly="true"
                              onKeyUp="textarealength(this,300)"><?php echo $rs['content']; ?></textarea>
                    <p class="textarea-numberbar"><em
                                class="textarea-length"><?php echo mb_strlen($rs['content']) ? mb_strlen($rs['content']) : 0; ?></em>/300
                    </>
                </div>
            </div>

            <div class="row cl" id="imgbox">
                <label class="form-label col-xs-4 col-sm-2">图片：</label>
                <div class="formControls col-xs-4 col-sm-6 img_list">
                    <div id="upload_list" class="upload-list-box">
                        <?php $piclist = json_decode($rs['pic'], true);
                        if ($piclist) {
                            foreach ($piclist as $val) { ?>
                                <div class="upload-list__item">
                                    <a href="javascript:;" class="del-link icon Hui-iconfont Hui-iconfont-close2"></a>
                                    <input type="hidden" class="upload-img-input" value="<?php echo $val; ?>"
                                           name="pic[]"
                                           readonly="readonly">
                                    <img src="<?php echo $val ? getImgUrl($val) : '#' ?>"
                                         class="big-img-show upload-img-show"
                                         style="display:block">
                                </div>
                            <?php }
                        } ?>
                    </div>
                </div>

            </div>
            <div class="row cl" id="upload_img" style="display:<?php $piclist = json_decode($rs['pic'],true);echo count($piclist)>=9?'none':'block' ?>">
                <label class="form-label col-xs-4 col-sm-2">&nbsp;</label>
                <div class="formControls col-xs-4 col-sm-6">
                    <input type="hidden" class="upload-img-input" value="" id="headimg" name="headimg"
                           readonly="readonly">
                    <div class="font_family upload-style-1 up_pic">
                        <iframe name="myframe1" onload="iframeShow('myframe1')"
                                src="<?php echo U('upload/index', array('id' => 'headimg', 'callback' => 'picUploadCallback')); ?>"
                                scrolling="no"
                                frameborder="0">
                        </iframe>
                    </div>
                </div>
            </div>
            <div class="row cl" style="margin-top: 5px;">
                <label class="form-label col-xs-4 col-sm-2"></label>
                <div class="formControls col-xs-4 col-sm-5">
                    <p class="f-12 c-999">至多上传九张</p>
                </div>
            </div>
            <div class="row cl">
                <div class="col-md-9 col-md-offset-1 col-sm-offset-2">
                    <input class="btn btn-primary radius" type="submit" value="&nbsp;&nbsp;提交&nbsp;&nbsp;"/>
                </div>
            </div>
            <input name="id" type="hidden" value="<?php echo $rs['id']; ?>"/>
        </form>
    </article>
    <script>
        function iframeShow(name) {
            //隐藏上传默认样式
            window.frames[name].document.getElementsByClassName("a-upload").item(0).className = "a-upload hide";
        }

        //图片上传回调（选项图上传完成回调）
        function picUploadCallback(re) {
            var $pic = $('.upload-img-input');
            var img = "";
            $pic.each(function (index, el) {
                var $this = $(el);
                var val = $this.val();
                if (val.search("yun") != -1) {
                    img = '<div class="upload-list__item">' +
                        '<a href="javascript:;" class="del-link icon Hui-iconfont Hui-iconfont-close2"></a>' +
                        '<input type="hidden" class="upload-img-input" value="' + val + '"  name="pic[]"  readonly="readonly">' +
                        '<img src= "<?php $upConf = C('upload', 'yun');  echo $upConf['url']; ?>' + val + '" class="big-img-show upload-img-show"  style="display:block">' +
                        '</div>';
                } else {
                    img = '<div class="upload-list__item">' +
                        '<a href="javascript:;" class="del-link icon Hui-iconfont Hui-iconfont-close2"></a>' +
                        '<input type="hidden" class="upload-img-input" value="' + val + '"  name="pic[]"  readonly="readonly">' +
                        '<img src="' + MAIN_PATH + val + '" class="big-img-show upload-img-show"  style="display:block">' +
                        '</div>';
                }
            });
            $(".upload-list-box").prepend(img);
            //检查图片个数
            var len = $(".upload-list__item").length;
            if (len >= 9) {
                $("#upload_img").hide();
            }
        }

        $(function () {
            $('.skin-minimal input').iCheck({
                checkboxClass: 'icheckbox-blue',
                radioClass: 'iradio-blue',
                increaseArea: '20%'
            });
            //删除图片
            $(document).on('click', '.del-link', function () {
                $(this).parents('.upload-list__item').remove();
                var len = $(".upload-list__item").length;
                if (len < 9) {
                    $("#upload_img").show();
                }
            });

            $("#form1").validate({
                //验证字段
                onkeyup: false,
                focusCleanup: false,
                success: "valid",
                submitHandler: function (form) {
                    Msg.loading();
                    $.post('<?php echo U('live/graphic_save');?>', $("#form1").serialize(), function (res) {
                        Msg.hide();
                        if (res.status == 1) {
                            Msg.ok('编辑成功', function () {
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