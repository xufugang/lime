//自定义插件函数
$(function () {
    $('a[rel*=pop]').facebox();
    $(document).on('click', ".openWinFull", function () {
        var that = $(this),
            title = that.data('title') || that.text(),
            url = that.data('href') || that.attr('href');
        openWinFull(title, url);
        return false;
    });
    $(document).on('click', ".openWinPop", function () {
        var that = $(this),
            title = that.data('title') || that.text(),
            url = that.data('href') || that.attr('href'),
            w = that.data('width') || 800,
            h = that.data('height') || 500;
        openWinPop(title, url, w, h)
        return false;
    });
    $(document).on('click', ".openWin", function () {
        var that = $(this), _href = that.data('href');
        if (_href) {
            var bStop = false;
            var bStopIndex = 0;
            var _href = _href;
            var _titleName = that.data("title");
            var topWindow = $(window.parent.document);
            var show_navLi = topWindow.find("#min_title_list li");
            show_navLi.each(function () {
                if ($(this).find('span').data("href") == _href) {
                    bStop = true;
                    bStopIndex = show_navLi.index($(this));
                    return false;
                }
            });
            if (!bStop) {
                creatIframe(_href, _titleName);
                min_titleList();
            } else {
                show_navLi.removeClass("active").eq(bStopIndex).addClass("active");
                var iframe_box = topWindow.find("#iframe_box");
                iframe_box.find(".show_iframe").hide().eq(bStopIndex).show().find("iframe").attr("src", _href);
            }
        }
    })
    $(".logout").on('click', function () {
        var that = $(this), _href = that.data('href');
        var index = layer.confirm('确认退出系统吗?', {btn: ['确认', '取消']}, function () {
            top.location = _href;
        }, function () {

        });
        return false;
    })
    //全选、取消
    $(document).on('click', '.check-all', function () {
        console.log('全选|取消');
        var checked = true;
        if ($(this).is(':checked')) {
            checked = true;
        } else {
            checked = false;
        }
        $(this).parent().parent().parent().parent().find("input[type='checkbox']").prop("checked", checked);
    });
})

//时间比较
function compareTime(startDate, endDate) {
    if (startDate.length > 0 && endDate.length > 0) {
        var startDateTemp = startDate.split(" ");
        var endDateTemp = endDate.split(" ");

        var arrStartDate = startDateTemp[0].split("-");
        var arrEndDate = endDateTemp[0].split("-");

        var arrStartTime = startDateTemp[1].split(":");
        var arrEndTime = endDateTemp[1].split(":");

        var allStartDate = new Date(arrStartDate[0], arrStartDate[1], arrStartDate[2], arrStartTime[0], arrStartTime[1], arrStartTime[2]);
        var allEndDate = new Date(arrEndDate[0], arrEndDate[1], arrEndDate[2], arrEndTime[0], arrEndTime[1], arrEndTime[2]);
        if ((allEndDate.getTime() - allStartDate.getTime()) >= 60000) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function openWinFull(title, url) {
    var index = layer.open({
        type: 2,
        title: title,
        content: url
    });
    layer.full(index);
}

function openWinPop(title, url, w, h) {
    w = w || 800;
    h = h || 500;
    layer_show(title, url, w, h);
}

//生成真实完整链接
function U(url, str) {
    if (url.indexOf("?") > 0) {
        return url + '&' + parseParam(str);
    } else {
        return url + '?' + parseParam(str);
    }
}

//将json转换成url参数串
function parseParam(param, key) {
    var key = key || null;
    var paramStr = "";
    if (param instanceof String || param instanceof Number || param instanceof Boolean) {
        paramStr += "&" + key + "=" + encodeURIComponent(param);
    } else {
        $.each(param, function (i) {
            var k = key == null ? i : key + (param instanceof Array ? "[" + i + "]" : "." + i);
            paramStr += '&' + parseParam(this, k);
        });
    }
    return paramStr.substr(1);
};

function z(astr, str2) {
    if (typeof astr == 'object') {
        console.dir(astr);
    } else {
        str2 = str2 || '';
        console.log(astr, str2);
    }
}