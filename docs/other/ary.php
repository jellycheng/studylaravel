<?php
header("Content-type: text/html; charset=utf-8");

$data = array('hello'=>'helloval',
				'jeLly'=>'验证是否区分大小写'
				);
echo $data['jelly'] . '<br>';//报警告错误， 说明数组的key是区分大小写的

echo $data['jeLly'] . '<br>';
