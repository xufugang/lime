var register = {
    init: function (opts) {
        var that = this;
        var hash = '';
        var images = {
            localId: [],
            serverId: []
        };

        var upload = function (localIds) {
            if (localIds.length <= 0) {
                console.log('请先使用 chooseImage 选择图片');
                return;
            }
            var localId = localIds.pop();
            wx.uploadImage({
                localId: localId,
                success: function (res) {
                    images.serverId.push(res.serverId);
                    // 	if(localIds.length > 0){
                    // upload(localIds);
                    //   }
                    $('#img').val(res.serverId);
                    $('#uploaderFiles').html('<li class="weui-uploader__file"><img src="' + localId + '"><a href="javascript:;" class="weui-icon-cancel weui-uploader__del"></a></li>');
                    $('.weui-uploader__box').hide();
                },
                fail: function (res) {
                    $.alert(JSON.stringify(res));
                }
            });
        }

        var register = function (opt) {
            var $this = opt.btn || $('#btn_register');
            var $form = opt.form || $('#register_form');

            $.showLoading('请稍等...');
            $this.attr('disabled', 'true');
            that.post("register", $form.serialize()).then(function (res) {
                $.hideLoading();
                $this.removeAttr("disabled");
                if (res.status == '1') {
                    $.toast('注册成功', 'text');
                    that.getUserData();
                } else {
                    $.toast(res.info, "text");
                }
            }, function (err) {
                $this.removeAttr("disabled")
                $.hideLoading();
                $.alert('注册失败！请稍候再试');
            });
        }

        $(document).on('click', '.register-tabs>.tab-link', function (e) {
            //发起人、普通会员切换
            $(this).addClass('tab-on').siblings('.tab-link').removeClass('tab-on');
            $('#register_form').attr('class', $(this).data('value') + '-form');
            $('#register_type').val($(this).data('type'));
            return false;
        }).on('click', '.weui-vcode-btn', function (e) {
            //获取验证码
            var $this = $(this);
            var mod = $this.data('type') || '2'; //1：注册，2：校验

            that.getCode({'btn': $this, 'phone': $('#phone').val(), 'mod': mod, 'hash': hash}, function (res) {
                hash = res.hash;
            });
            return false;
        }).on('click', '.weui-uploader__input-box', function (e) {
            //上传时选择图片
            wx.ready(function () {
                wx.chooseImage({
                    count: 1, // 默认9
                    sizeType: ['compressed'], // 可以指定是原图还是压缩图，默认二者都有
                    sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
                    success: function (res) {
                        images.localId = res.localIds;
                        console.log('已选择 ' + res.localIds.length + ' 张图片');
                        upload(res.localIds);
                    }
                });
            });
            return false;
        }).on('click', '.weui-uploader__del', function (e) {
            //删除照片
            var $this = $(this);
            $.confirm({
                title: '提示',
                text: '确定删除该照片',
                onOK: function () {
                    $this.parent('.weui-uploader__file').remove();
                    $('.weui-uploader__box').show();
                },
                onCancel: function () {
                }
            });
            return false;
        }).on('click', '#btn_register', function (e) {
            //注册
            var $this = $(this);
            var $type = $('#register_type').val() || '1';

            var isError = false;
            var validator = new Validator('register_form', [
                {
                    name: 'username',
                    display: '请输入姓名|姓名太长了',
                    rules: 'required|max_length(12)'
                }, {
                    name: 'phone',
                    display: "请输入手机号码|手机号码格式不正确",
                    rules: 'required|is_phone'
                }, {
                    name: 'work_unit',
                    display: "请输入验工作单位",
                    rules: 'required'
                }, {
                    name: 'referee',
                    display: "请填写推荐人",
                    rules: $type == '2' ? 'required' : ''
                }, {
                    name: 'pic',
                    display: "请上传本人照片",
                    rules: $type == '1' ? 'required' : ''
                }], function (obj, evt) {
                if (obj.errors.length > 0) {
                    isError = true;
                    $.toast(obj.errors[0].message, "text");
                }
            })
            validator.validate();
            if (isError) return false;
            register({'btn': $this, 'form': $('#register_form')});
            return false;
        });

        that.getUserData();
    },
    /**
     * [getUserData 获取用户信息]
     * @return {[type]} [description]
     */
    getUserData: function (callback) {
        var that = this;
        var _callback = callback || function () {
        };

        $.showLoading('请稍等...');
        that.get("membercenter", {}).then(function (res) {
            if (1 === res.status) {
                var $data = res.data;
                var html = template("tpl_user", $data);
                var $tpl = $('#page').html(html);
            } else if (4 === res.status) {
                var $data = res.data;
                var html = template("tpl_register", $data);
                var $tpl = $('#page').html(html);
            } else {
                $.toast(res.info, "text");
            }
            $.hideLoading();
        }, function (err) {
            $.hideLoading();
            $.alert('数据请求失败！');
        });
    },
    /**
     * [getCode 获取验证码]
     * @param  {[type]} opts.btn  [获取验证码按钮]
     * @param  {[type]} opts.phone [手机号码]
     * @param  {[type]} opts.mod [1：注册，2：校验]
     * @param  {[type]} opts.hash [校验码]
     * @return {[type]}       [description]
     */
    getCode: function (opts, callback) {
        var that = this;
        var $this = opts.btn;
        var phone = opts.phone || '';
        var mod = opts.mod || 'reg';
        var hash = opts.hash || '';
        var _callback = callback || function () {
        };
        var reg = /^[1][3-8][0-9]{9}$/;
        var num = 60;

        if (phone == '') {
            $.toast('请输入您的手机号码', 'text');
            return false;
        }

        if (phone && !reg.test(phone)) {
            $.toast('您的手机号码格式不正确', 'text');
            return false;
        }

        $this.attr('disabled', 'true');
        that.post("verify/sms", {'phone': phone, 'mod': mod, '__hash__': hash}).then(function (res) {
            $.hideLoading();
            if (res.status == '1') {
                console.log('验证码获取成功');
                $.toast('发送成功', 'text');
                $this.text(num + 's后重新获取').attr('disabled', 'true');
                var timer = window.setInterval(function () {
                    num--;
                    if (num >= 0) {
                        $this.text(num + 's后重新获取');
                    } else {
                        window.clearInterval(timer);
                        $this.text('重新发送').removeAttr("disabled");
                    }
                }, 1000);
                _callback({'hash': res.data.hash});
            } else {
                $.toast(res.info, "text");
                $this.removeAttr("disabled");
            }
        }, function (err) {
            $this.removeAttr("disabled")
            $.hideLoading();
            $.alert('验证码获取失败！请稍候再试');
        });
    },
    get: function (url, params) {
        var opt = params || {};
        return this.handler('GET', url, opt);
    },
    post: function (url, params) {
        var opt = params || {};
        return this.handler('POST', url, opt);
    },
    handler: function (type, url, params) {
        var defer = $.Deferred();
        var base_url = API_URL;
        $.ajax({
            type: type,
            url: base_url + url,
            data: params,
            dataType: 'json',
            success: function (result) {
                defer.resolve(result);
            },
            error: function (xhr, type) {
                console.log(xhr, type);
                defer.reject()
            }
        });
        return defer.promise();
    },
    preventTouch: function () {
        document.ontouchmove = function (e) {
            e.preventDefault();
        }
    },
    defaultTouch: function () {
        document.ontouchmove = function (e) {
            return true;
        }
    }
}