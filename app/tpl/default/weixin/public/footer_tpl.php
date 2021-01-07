<?php
 if (isset($Document['wxjsapi'])&&$Document['wxjsapi']){
?>
<script src="//res.wx.qq.com/open/js/jweixin-1.2.0.js"></script>
<script>
$.getJSON('<?php echo $setting['jssdk_url'];?>', function(json){
    wx.config(json);
    wx.ready(function(){
        wxjsapiShare(dataForWeixin);
    });
});
function wxjsapiShare(res) {
	var conf={title:res.title,desc:res.desc,link:res.link,imgUrl:res.imgUrl,type:"",dataUrl:"",
	success:function(result){res.callback(result);},
	cancel:function(result){res.cancel(result);}};wx.onMenuShareTimeline(conf);
	wx.onMenuShareAppMessage(conf);wx.onMenuShareQQ(conf);wx.onMenuShareWeibo(conf);
}
</script>
<?php
 }
?>
<?php
 if (isset($Document['footerjs'])&&$Document['footerjs']){
	getJs($Document['footerjs'],true);
 }
?>
<?php
if (!isHave($Document['noStat'])){
?>
<div style="display:none">

</div>
<?php }?>
<script>
    var _mtac = {};
    (function() {
        var mta = document.createElement("script");
        mta.src = "//pingjs.qq.com/h5/stats.js?v2.0.4";
        mta.setAttribute("name", "MTAH5");
        mta.setAttribute("sid", "500632711");

        var s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(mta, s);
    })();
</script>
</body>
</html>