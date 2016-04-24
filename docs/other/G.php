<?php

class G {

	protected static $iCurrentLoginUserid = 0; //当前的登录的用户id值

	public static function getCurLoginUserid() {

		return self::$iCurrentLoginUserid;

	}

	public static function setCurLoginUserid($iUserid) {
		self::$iCurrentLoginUserid = intval($iUserid);
	}

}

echo G::getCurLoginUserid();
G::setCurLoginUserid(1000);
echo G::getCurLoginUserid();
echo G::getCurLoginUserid();