<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'index', //页面标示
    'pagename' => '赛虹物业', //当前页面名称
    'keywords' => '', //关键字
    'description' => '', //描述
    'mycss' => array('weixin/weui.min', 'weixin/jquery-weui.min', 'weixin/style'), //加载的css样式表
    'myjs' => array('weixin/jquery.2.1.4.min', 'weixin/jquery.weui.min', 'weixin/template-web', 'weixin/validator', 'weixin/register'), //加载的js脚本
    'footerjs' => array(),
    'head' => true,
    'copyright' => true
);
include getTpl('header', 'public');
?>
<script type="text/javascript">
    var API_URL = 'http://192.168.17.29/shqwy/weixin/register/';//'http://event.51jhome.com/saihong/weixin/register/';
    var dataForWeixin = {
        share_title: "赛虹桥物业管理系统",
        share_desc: "",
        share_url: "http://192.168.17.29/shqwy/weixin/register/index",
        share_image: "",
        callback: function () {
        },
        cancel: function () {
        }
    };
</script>
<style>
    .weui-uploader__input-box{cursor: pointer;}
    .weui-uploader__files img{display: block; width: 100%; height: 100%; object-fit: cover;}
</style>
<body>
<div id="page">
    <div class="page-backgroud pb-80">
        <div class="user-section">
            <div class="avater-item"></div>
            <div class="weui-cells weui-cells_form register_form radius-top">
                <div class="weui-cell">
                    <div class="weui-cell__hd"><label class="weui-label"><span
                                    class="icon-label icon-man"></span><span>姓名：</span></label></div>
                    <div class="weui-cell__bd"></div>
                </div>
                <div class="weui-cell weui-cell_vcode">
                    <div class="weui-cell__hd"><label class="weui-label"><span class="icon-label icon-phone"></span><span>手机号码：</span></label>
                    </div>
                    <div class="weui-cell__bd"></div>
                </div>
                <div class="weui-cell">
                    <div class="weui-cell__hd"><label class="weui-label"><span class="icon-label icon-house"></span><span>工作单位：</span></label>
                    </div>
                    <div class="weui-cell__bd"></div>
                </div>
                <div class="weui-cell">
                    <div class="weui-cell__hd"><label class="weui-label"><span class="icon-label icon-mans"></span><span>推荐人：</span></label>
                    </div>
                    <div class="weui-cell__bd"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 用户信息 -->
<script id="tpl_user" type="text/html">
    <div id="page" class="page-backgroud pb-80" style="{{banner ? 'background-image: url(' + banner + ');' : ''}}">
        <div class="user-section">
            <div class="avater-item"><img src="{{headimg}}"></div>

            <div class="weui-cells weui-cells_form register_form radius-top">
                <div class="weui-cell">
                    <div class="weui-cell__hd"><label class="weui-label"><span class="icon-label icon-man"></span><span>姓名：</span></label>
                    </div>
                    <div class="weui-cell__bd">
                        <div>{{username}}</div>
                    </div>
                </div>
                <div class="weui-cell weui-cell_vcode">
                    <div class="weui-cell__hd"><label class="weui-label"><span
                                    class="icon-label icon-phone"></span><span>手机号码：</span></label></div>
                    <div class="weui-cell__bd">
                        <div>{{phone}}</div>
                    </div>
                    <!-- <div class="weui-cell__ft">
                        <button class="weui-vcode-btn phone-change">修改号码</button>
                    </div> -->
                </div>
                <div class="weui-cell">
                    <div class="weui-cell__hd"><label class="weui-label"><span
                                    class="icon-label icon-house"></span><span>工作单位：</span></label></div>
                    <div class="weui-cell__bd">
                        <div>{{work_unit}}</div>
                    </div>
                </div>
                {{ if user_type == 1}}
                <div class="weui-cell">
                    <div class="weui-cell__hd"><label class="weui-label"><span
                                    class="icon-label icon-mans"></span><span>推荐人数：</span></label></div>
                    <div class="weui-cell__bd">
                        <div>{{referee_num}}人</div>
                    </div>
                </div>
                {{ else }}
                <div class="weui-cell">
                    <div class="weui-cell__hd"><label class="weui-label"><span
                                    class="icon-label icon-mans"></span><span>推荐人：</span></label></div>
                    <div class="weui-cell__bd">
                        <div>{{referee}}</div>
                    </div>
                </div>
                {{ /if }}
            </div>
        </div>
    </div>
    <div class="weui-tabbar">
        <a href="{{left.url || 'javascript:;'}}" class="weui-tabbar__item">
            <div class="weui-tabbar__icon icon-menu-1"></div>
            <p class="weui-tabbar__label">{{left.name || '协会简章'}}</p>
        </a>
        <a href="javascript:;" class="weui-tabbar__item weui-bar__item--on">
            <div class="weui-tabbar__icon icon-menu-2"></div>
            <p class="weui-tabbar__label">会员中心</p>
        </a>
        <a href="{{right.url || 'javascript:;'}}" class="weui-tabbar__item">
            <div class="weui-tabbar__icon icon-menu-3"></div>
            <p class="weui-tabbar__label">{{right.name || '人员架构'}}</p>
        </a>
    </div>
    <div class="weui-tabbar">
        <a href="{{left.url || 'javascript:;'}}" class="weui-tabbar__item">
            <div class="weui-tabbar__icon icon-menu-1"></div>
            <p class="weui-tabbar__label">{{left.name || '协会简章'}}</p>
        </a>
        <a href="javascript:;" class="weui-tabbar__item weui-bar__item--on">
            <div class="weui-tabbar__icon icon-menu-2"></div>
            <p class="weui-tabbar__label">会员中心</p>
        </a>
        <a href="{{right.url || 'javascript:;'}}" class="weui-tabbar__item">
            <div class="weui-tabbar__icon icon-menu-3"></div>
            <p class="weui-tabbar__label">{{right.name || '人员架构'}}</p>
        </a>
    </div>
</script>

<script id="tpl_register" type="text/html">
    <div id="page" class="page-backgroud pb-80" style="{{banner ? 'background-image: url(' + banner + ');' : ''}}">
        <div class="register-section pt-130">
            <div class="register-tabs">
                <a href="javascript:;" class="tab-link tab-on" data-value="initiator" data-type="1">发起人注册</a>
                <a href="javascript:;" class="tab-link" data-value="ordinary" data-type="2">普通会员注册</a>
            </div>
            <div class="weui-cells weui-cells_form register_form">
                <form id="register_form" name="register_form" class="initiator-form" method="post" action="#"
                      onsubmit="return false">
                    <div class="weui-cell">
                        <div class="weui-cell__hd"><label class="weui-label"><span
                                        class="icon-label icon-man"></span><span>姓名：</span></label></div>
                        <div class="weui-cell__bd">
                            <input class="weui-input" type="text" name="username" id="username" placeholder=""
                                   data-placeholder="请输入姓名">
                        </div>
                    </div>
                    <div class="weui-cell weui-cell_vcode">
                        <div class="weui-cell__hd"><label class="weui-label"><span class="icon-label icon-phone"></span><span>手机号码：</span></label>
                        </div>
                        <div class="weui-cell__bd">
                            <input class="weui-input" type="tel" name="phone" id="phone" placeholder=""
                                   data-placeholder="请输入手机号码">
                        </div>
                        <!-- <div class="weui-cell__ft">
                            <button class="weui-vcode-btn" data-type="1">获取验证码</button>
                        </div> -->
                    </div>
                    <!-- <div class="weui-cell">
                        <div class="weui-cell__hd"><label class="weui-label"><span class="icon-label icon-shield"></span><span>验证码：</span></label></div>
                        <div class="weui-cell__bd">
                              <input class="weui-input" type="number" name="code" id="code" pattern="[0-9]*" placeholder="" data-placeholder="请输入验证码">
                        </div>
                    </div> -->
                    <div class="weui-cell">
                        <div class="weui-cell__hd"><label class="weui-label"><span class="icon-label icon-house"></span><span>工作单位：</span></label>
                        </div>
                        <div class="weui-cell__bd">
                            <input class="weui-input" type="text" name="work_unit" id="work_unit" placeholder=""
                                   data-placeholder="请输入工作单位">
                        </div>
                    </div>
                    <div class="weui-cell recom-cell">
                        <div class="weui-cell__hd"><label class="weui-label"><span
                                        class="icon-label icon-mans"></span><span>推荐人：</span></label></div>
                        <div class="weui-cell__bd">
                            <input class="weui-input" type="text" name="referee" id="referee" placeholder=""
                                   data-placeholder="请填写推荐人">
                        </div>
                    </div>
                    <div class="weui-cell uploader-cell">
                        <div class="weui-cell__hd"><label class="weui-label"><span
                                        class="icon-label icon-camera"></span><span>本人照片：</span></label></div>
                        <div class="weui-cell__bd">
                            <ul class="weui-uploader__files" id="uploaderFiles"></ul>
                            <div class="weui-uploader__box">
                                <div class="weui-uploader__input-box">
                                    <!-- <input id="uploaderInput" class="weui-uploader__input" type="file" accept="image/*" multiple=""> -->
                                    <input type="hidden" class="weui-input" name="pic" id="img"
                                           data-placeholder="请上传本人照片">
                                </div>
                                <div class="uploader-tips">上传一张本人正面照，不超过2M</div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-40">
                        <input type="hidden" class="weui-input" name="type" id="register_type" value="1">
                        <button class="weui-btn btn-blue" id="btn_register">立即注册</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="weui-tabbar">
        <a href="{{left.url || 'javascript:;'}}" class="weui-tabbar__item">
            <div class="weui-tabbar__icon icon-menu-1"></div>
            <p class="weui-tabbar__label">{{left.name || '协会简章'}}</p>
        </a>
        <a href="javascript:;" class="weui-tabbar__item weui-bar__item--on">
            <div class="weui-tabbar__icon icon-menu-2"></div>
            <p class="weui-tabbar__label">会员中心</p>
        </a>
        <a href="{{right.url || 'javascript:;'}}" class="weui-tabbar__item">
            <div class="weui-tabbar__icon icon-menu-3"></div>
            <p class="weui-tabbar__label">{{right.name || '人员架构'}}</p>
        </a>
    </div>
</script>
<script type="text/javascript">
    $(function () {
        register.init();
    });
</script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.4.0.js"></script>
<script>
    var dataForWeixin = {};
    $.getJSON('http://192.168.17.29/shqwy/weixin/share/config?callback=?', function (json) {
        wx.config(json);
        wx.ready(function () {
            wxjsapiShare(dataForWeixin);
        });
    });

    function wxjsapiShare(res) {
        var conf = {
            title: res.share_title, desc: res.share_desc, link: res.share_url, imgUrl: res.share_image, type: "", dataUrl: "",
            success: function (result) {
                res.callback(result);
            },
            cancel: function (result) {
                res.cancel(result);
            }
        };
        wx.onMenuShareTimeline(conf);
        wx.onMenuShareAppMessage(conf);
        wx.onMenuShareQQ(conf);
        wx.onMenuShareWeibo(conf);
    }
</script>
<script src="//cdn.bootcss.com/eruda/1.4.2/eruda.min.js"></script>
</body>
