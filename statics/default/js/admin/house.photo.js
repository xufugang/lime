$(function () {
    var $ = jQuery,    // just in case. Make sure it's not an other libaray.
        $wrap = $('#uploader'),
        // 状态栏，包括进度和控制按钮
        $statusBar = $wrap.find('.statusBar'),
        // 文件总体选择信息。
        $info = $statusBar.find('.info'),
        // 上传按钮
        $upload = $wrap.find('.uploadBtn'),
        // 没选择文件之前的内容。
        $placeHolder = $wrap.find('.placeholder'),
        // 总体进度条
        $progress = $statusBar.find('.progress').hide(),
        // 添加的文件数量
        fileCount = 0,
        // 添加的文件总大小
        fileSize = 0,
        // 优化retina, 在retina下这个值是2
        ratio = window.devicePixelRatio || 1,
        // 缩略图大小
        thumbnailWidth = 110 * ratio,
        thumbnailHeight = 110 * ratio,
        // 可能有pedding, ready, uploading, confirm, done.
        state = 'pedding',
        // 所有文件的进度信息，key为file id
        percentages = {},
        supportTransition = (function () {
            var s = document.createElement('p').style,
                r = 'transition' in s ||
                    'WebkitTransition' in s ||
                    'MozTransition' in s ||
                    'msTransition' in s ||
                    'OTransition' in s;
            s = null;
            return r;
        })(),
        // WebUploader实例
        uploader;
    // 图片容器
    $queue = $wrap.find('.queueList').find(".filelist");
    if ($queue.length <= 0) {
        $queue = $('<ul class="filelist"></ul>').appendTo($wrap.find('.queueList'));
    } else {
        $queue.find('li').each(function (key, val) {
            var $liHas = $(this), $btns = $liHas.find('.file-panel');
            $liHas.on('mouseenter', function () {
                $btns.stop().animate({height: 30});
            });
            $liHas.on('mouseleave', function () {
                $btns.stop().animate({height: 0});
            });
            $btns.on('click', 'span', function () {
                $liHas.remove();
                init();
            });
        });
        $(window).on('load', function () {
            $("#dndArea").hide();
            $info.hide();
            $queue.show();
        })
    }
    if (!WebUploader.Uploader.support()) {
        Msg.alert('Web Uploader 不支持您的浏览器！如果你使用的是IE浏览器，请尝试升级 flash 播放器');
        throw new Error('WebUploader does not support the browser you are using.');
    }
    // 实例化
    uploader = WebUploader.create({
        pick: {
            id: '#filePicker',
            label: '点击选择图片'
        },
        dnd: '#uploader .queueList',
        paste: document.body,

        accept: {
            title: 'Images',
            extensions: 'gif,jpg,jpeg,png',
            mimeTypes: 'image/jpg,image/jpeg,image/gif,image/png'
        },
        // swf文件路径
        swf: webUploaderSetting.swf,
        disableGlobalDnd: true,
        chunked: true,
        server: webUploaderSetting.server,
        fileNumLimit: webUploaderSetting.fileNumLimit,
        fileSizeLimit: webUploaderSetting.fileSingleSizeLimit * webUploaderSetting.fileNumLimit,    // 200 M
        fileSingleSizeLimit: webUploaderSetting.fileSingleSizeLimit
    });

    // 添加“添加文件”的按钮，
    uploader.addButton({
        id: '#filePicker2',
        label: '继续添加'
    });

    // 当有文件添加进来时执行，负责view的创建
    function addFile(file) {

        var $li = $('<li id="' + file.id + '">' +
            '<p class="title">' + file.name + '</p>' +
            '<p class="imgWrap"></p>' +
            '<p class="progress"><span></span></p>' +
            '</li>'),

            $btns = $('<div class="file-panel">' +
                '<span class="cancel">删除</span>' +
                '<span class="rotateRight">向右旋转</span>' +
                '<span class="rotateLeft">向左旋转</span></div>').appendTo($li),
            $prgress = $li.find('p.progress span'),
            $wrap = $li.find('p.imgWrap'),
            $info = $('<p class="error"></p>'),

            showError = function (code) {
                switch (code) {
                    case 'exceed_size':
                        text = '文件大小超出';
                        break;

                    case 'interrupt':
                        text = '上传暂停';
                        break;

                    default:
                        text = '上传失败，请重试';
                        break;
                }

                $info.show().text(text).appendTo($li);
            };

        if (file.getStatus() === 'invalid') {
            showError(file.statusText);
        } else {
            // @todo lazyload
            $wrap.text('预览中');
            uploader.makeThumb(file, function (error, src) {
                if (error) {
                    $wrap.text('不能预览');
                    return;
                }
                var img = $('<img class="thumb" src="' + src + '">');
                $wrap.empty().append(img);
            }, thumbnailWidth, thumbnailHeight);

            percentages[file.id] = [file.size, 0];
            file.rotation = 0;
        }
        file.on('statuschange', function (cur, prev) {
            if (prev === 'progress') {
                $prgress.hide().width(0);
            } else if (prev === 'queued') {
                $li.off('mouseenter mouseleave');
                $btns.remove();
            }
            // 成功
            if (cur === 'error' || cur === 'invalid') {
                console.log(file.statusText);
                showError(file.statusText);
                percentages[file.id][1] = 1;
            } else if (cur === 'interrupt') {
                showError('interrupt');
            } else if (cur === 'queued') {
                percentages[file.id][1] = 0;
            } else if (cur === 'progress') {
                $info.remove();
                $prgress.css('display', 'block');
            } else if (cur === 'complete') {
                var fileName = file.name;
                fileName = fileName.substring(0, fileName.lastIndexOf("."));
                var thumb = $("#" + file.id).find('.thumb').attr('src');
                var tpl = '<input value="" name="id[' + file.id + ']" type="hidden"/><div class="f-l l-pic">' +
                    '<p class="imgWrap"><img src="' + thumb + '"></p>' +
                    '<p class="progress"><span style="display: none; width: 0px;"></span></p>' +
                    '<span class="success"></span><input id="' + file.id + '_upload_ok" type="hidden" value="" name="' + webUploaderSetting.fieldName + '[' + file.id + ']"/>' +
                    '<div class="file-panel"><span class="cancel" title="删除">删除</span></div>' +
                    '</div><div class="f-r r-text">' +
                    '<div class="row cl show_photo_name">' +
                    '<label class="form-label col-xs-2 col-md-2 col-sm-3"><span class="c-red">*</span>名称：</label>' +
                    '<div class="formControls col-xs-8 col-md-8 col-sm-7">' +
                    '<input type="text" name="name[' + file.id + ']" placeholder="" class="input-text" value="' + fileName + '" />' +
                    ' </div>' +
                    '</div>' +
                    '<div class="row cl show_photo_info">' +
                    '<label class="form-label col-xs-2 col-md-2 col-sm-3">面积：</label>' +
                    '<div class="formControls col-xs-2 col-md-2 col-sm-3 pr-5 f-l">' +
                    '<input type="text" name="acreage[' + file.id + ']" placeholder="" class="input-text" value="" />' +
                    '</div>' +
                    '<span class="input-unit f-l">㎡</span>' +
                    ' </div>' +
                    '<div class="row cl show_photo_info">' +
                    '<label class="form-label col-xs-2 col-md-2 col-sm-3">户型：</label>' +
                    '<div class="formControls col-xs-2 col-md-2 col-sm-2 pr-5 f-l">' +
                    '<input type="text" name="room_counts[' + file.id + ']" placeholder="室" class="input-text" value="" />' +
                    '</div>' +
                    '<span class="input-unit f-l">室</span>' +
                    '<div class="formControls col-xs-2 col-md-2 col-sm-2 pr-5 f-l">' +
                    '<input type="text" name="hall_counts[' + file.id + ']" placeholder="厅" class="input-text" value="" />' +
                    '</div>' +
                    '<span class="input-unit f-l">厅</span>' +
                    '<div class="formControls col-xs-2 col-md-2 col-sm-2 f-l">' +
                    '<input type="text" name="toilet_counts[' + file.id + ']" placeholder="卫" class="input-text" value="" />' +
                    '</div>' +
                    '<span class="input-unit f-l">卫</span>' +
                    '</div>' +
                    '</div>';
                $li = $("#" + file.id);
                $li.html(tpl);
                var type = parseInt($('#type').val());
                if ($.inArray(type, show_photo_info_type) == -1) {
                    $li.find(".show_photo_info").hide();
                    $li.find(".show_photo_name").find("label").addClass('col-md-3').removeClass('col-md-2')
                }
                $btns = $li.find('.file-panel');
                $li.on('mouseenter', function () {
                    $btns.stop().animate({height: 30});
                });
                $li.on('mouseleave', function () {
                    $btns.stop().animate({height: 0});
                });
                $btns.on('click', 'span', function () {
                    $li.remove();
                    init();
                });
            }
            $li.removeClass('state-' + prev).addClass('state-' + cur);
        });
        $li.on('mouseenter', function () {
            $btns.stop().animate({height: 30});
        });
        $li.on('mouseleave', function () {
            $btns.stop().animate({height: 0});
        });
        $btns.on('click', 'span', function () {
            var index = $(this).index(),
                deg;
            switch (index) {
                case 0:
                    uploader.removeFile(file);
                    return;

                case 1:
                    file.rotation += 90;
                    break;

                case 2:
                    file.rotation -= 90;
                    break;
            }
            if (supportTransition) {
                deg = 'rotate(' + file.rotation + 'deg)';
                $wrap.css({
                    '-webkit-transform': deg,
                    '-mos-transform': deg,
                    '-o-transform': deg,
                    'transform': deg
                });
            } else {
                $wrap.css('filter', 'progid:DXImageTransform.Microsoft.BasicImage(rotation=' + (~~((file.rotation / 90) % 4 + 4) % 4) + ')');
            }

        });
        $li.appendTo($queue);
    }

    // 负责view的销毁
    function removeFile(file) {
        var $li = $('#' + file.id);
        delete percentages[file.id];
        updateTotalProgress();
        $li.off().find('.file-panel').off().end().remove();
        init();
    }

    function updateTotalProgress() {
        var loaded = 0,
            total = 0,
            spans = $progress.children(),
            percent;
        $.each(percentages, function (k, v) {
            total += v[0];
            loaded += v[0] * v[1];
        });
        percent = total ? loaded / total : 0;
        spans.eq(0).text(Math.round(percent * 100) + '%');
        spans.eq(1).css('width', Math.round(percent * 100) + '%');
        updateStatus();
    }

    function init() {
        z($queue.find('li').length);
        if ($queue.find('li').length <= 0) {
            $("#dndArea").removeClass("element-invisible").show();
            $statusBar.hide();
        }
    }

    function updateStatus() {
        var text = '', stats;
        if (state === 'ready') {
            text = '已选中' + fileCount + '张图片，共' +
                WebUploader.formatSize(fileSize) + '。<span class="red">注：拖动文件可排序</span>';
        } else if (state === 'confirm') {
            stats = uploader.getStats();
            if (stats.uploadFailNum) {
                text = '已成功上传' + stats.successNum + '张照片至XX相册，' +
                    stats.uploadFailNum + '张照片上传失败，<a class="retry" href="#">重新上传</a>失败图片或<a class="ignore" href="#">忽略</a>'
            }
        } else {
            stats = uploader.getStats();
            text = '共' + fileCount + '张（' +
                WebUploader.formatSize(fileSize) +
                '），已上传' + stats.successNum + '张';
            if (stats.uploadFailNum) {
                text += '，失败' + stats.uploadFailNum + '张';
            }
        }
        $info.show().html(text);
    }

    uploader.on('uploadSuccess', function (file, response) {
        console.log('uploadSuccess', file.id);
        if (response.state != 'SUCCESS') {
            Msg.error(response.state);
            return false
        }
        $('#' + file.id + '_upload_ok').val(response.original);
    });

    function setState(val) {
        var file, stats;
        if (val === state) {
            return;
        }
        $upload.removeClass('state-' + state);
        $upload.addClass('state-' + val);
        state = val;
        switch (state) {
            case 'pedding':
                $placeHolder.removeClass('element-invisible');
                $queue.parent().removeClass('filled');
                $queue.hide();
                $statusBar.addClass('element-invisible');
                uploader.refresh();
                break;
            case 'ready':
                $placeHolder.addClass('element-invisible');
                $('#filePicker2').removeClass('element-invisible');
                $queue.parent().addClass('filled');
                $queue.show();
                $statusBar.removeClass('element-invisible');
                uploader.refresh();
                break;
            case 'uploading':
                $('#filePicker2').addClass('element-invisible');
                $progress.show();
                $upload.text('暂停上传');
                break;
            case 'paused':
                $progress.show();
                $upload.text('继续上传');
                break;
            case 'confirm':
                $progress.hide();
                $('#filePicker2').removeClass('element-invisible');
                $upload.text('开始上传');
                stats = uploader.getStats();
                if (stats.successNum && !stats.uploadFailNum) {
                    setState('finish');
                    return;
                }
                break;
            case 'finish':
                stats = uploader.getStats();
                if (stats.successNum) {
                    Msg.ok('上传成功');
                } else {
                    // 没有成功的图片，重设
                    state = 'done';
                    location.reload();
                }
                break;
        }
        updateStatus();
    }

    uploader.onUploadProgress = function (file, percentage) {
        var $li = $('#' + file.id), $percent = $li.find('.progress span');
        $percent.css('width', percentage * 100 + '%');
        percentages[file.id][1] = percentage;
        updateTotalProgress();
    };

    uploader.onFileQueued = function (file) {
        fileCount++;
        fileSize += file.size;
        if (fileCount === 1) {
            $placeHolder.addClass('element-invisible');
            $statusBar.show();
        }
        addFile(file);
        setState('ready');
        updateTotalProgress();
    };

    uploader.onFileDequeued = function (file) {
        fileCount--;
        fileSize -= file.size;
        if (!fileCount) {
            setState('pedding');
        }
        removeFile(file);
        updateTotalProgress();
    };

    uploader.on('all', function (type) {
        var stats;
        switch (type) {
            case 'uploadFinished':
                setState('confirm');
                break;
            case 'startUpload':
                setState('uploading');
                break;
            case 'stopUpload':
                setState('paused');
                break;
        }
    });

    uploader.onError = function (code) {
        console.log(code);
        var errMsg = '';
        switch (code) {
            case 'Q_EXCEED_NUM_LIMIT':
                errMsg = '超出上传数量限制';
                break;
            case 'F_EXCEED_SIZE':
                errMsg = '上传文件过大';
                break;
            case 'Q_EXCEED_SIZE_LIMIT':
                errMsg = '上传文件过大';
                break;
            case 'Q_TYPE_DENIED':
                errMsg = '不支持的上传类型';
                break;
            case 'F_DUPLICATE':
                errMsg = '文件已在队列中';
                break;
            default:
                errMsg = code;
        }
        Msg.error('错误: ' + errMsg);
    };

    $upload.on('click', function () {
        if ($(this).hasClass('disabled')) {
            return false;
        }
        if (state === 'ready') {
            uploader.upload();
        } else if (state === 'paused') {
            uploader.upload();
        } else if (state === 'uploading') {
            uploader.stop();
        }
    });

    $info.on('click', '.retry', function () {
        uploader.retry();
    });
    $info.on('click', '.ignore', function () {
        Msg.alert('todo');
    });
    $upload.addClass('state-' + state);
    updateTotalProgress();
    $wrap.DDSort({
        target: 'li',
        ignore: '',
        skey: 'img',
        top: 0,
        floatStyle: {
            'position': 'fixed',
            'border': '1px solid #ccc',
            'background-color': '#fff'
        },
        down: function (e) {
            console.log(e)
        }
    });
});