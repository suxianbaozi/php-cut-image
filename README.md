php-cut-image
=============

自动图片裁剪，用于解决图片变形，生成缩略图
用法:
<?php
$cut = new ImageCut();
$cut->set_image('1.jpg');//path可以通过参数传过来
$cut->show(200,200);//随意设置宽高，可以通过参数传过来，
?>
