<?php
//url前缀 对应命名空间
header("Content-type: text/html; charset=utf-8");


#$rawUrl = "/1.0//login/index";
$rawUrl = "/admin/ain.php?a=admin"; //原url
$url = trim($rawUrl, '\\\/'); //处理之后的url

$namespaceSuffix = '';//命名空间后缀
$param = array('/^1.0([\/]+|[\/]?$)/i'=>'v1_0', 
				'/^admin([\/]+|[\/]?$)/i'=>'vadmin\\abc',
				'/正则匹配url前缀/i'=>'命名空间');
foreach($param as $k0=>$v0) {
	if(preg_match($k0, $url)) {
		$namespaceSuffix = $v0;
		echo "匹配，命名空间={$namespaceSuffix}<br>" ;
		$url = preg_replace($k0, '', $url);
		echo "去掉前缀之后的url，{$namespaceSuffix}，用于后续分析<br>";
		//匹配到第1个就不要往后匹配了
		break;
	} else {
		echo "not ok<br>";
	}
}

