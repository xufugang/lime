<div id="loader1"></div>
<div id="loader2"></div>
<script src="<?php echo JS_PATH;?>admin/facebox.js"></script>
<script src="<?php echo JS_PATH;?>admin/global.js?v=2017062801"></script>
<script src="<?php echo JS_PATH;?>admin/lib/layer/layer.2.1.js"></script>
<script src="<?php echo JS_PATH;?>admin/lib/jquery.icheck.min.js"></script>
<script src="<?php echo JS_PATH;?>admin/h-ui.js"></script> 
<script src="<?php echo JS_PATH;?>admin/h-ui.admin.js"></script> 
<?php
 if (isset($Document['footerjs'])&&$Document['footerjs']){
	getJs($Document['footerjs']);
 }
?>
<?php
 if (isset($Document['copyright'])&&$Document['copyright']){
?>
<?php
 }
 ?>
</body>
</html>