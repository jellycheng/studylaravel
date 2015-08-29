<?php

//客户端先实现密码加密后传给服务器，服务器再进行加密存入库  (适合支付密码，普通账户登录密码，修改密码，注册密码等)
function encryptPassword($password)
{//客户端加密算法
	$sTmp1 = md5($password);
	$sTmp2 = strrev($sTmp1) . 'paf';
	$sResult = md5($sTmp2);

	return $sResult;
}

#比较密码是否正确页是用该算法
function encryptPasswordDb($password) {
	//根据加密后的串，再进行加密入库


}

echo encryptPassword('aaz123456');
