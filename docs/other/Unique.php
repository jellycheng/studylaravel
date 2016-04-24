<?php
/**
 * Author: jelly <42282367@qq.com>
 * Date: 2015-8-27
 * Desc: 产生唯一值
 */

namespace Ananzu\Security;

class Unique
{
    


    /**
     * 根据干扰值，产生唯一值,干扰值尽量唯一
     * 返回md5之后的32个字符
     * @param string $annoyance 如果是充值就传pay+userid， 如果是合同订单支付传order+userid，如果是im则传im+userid 如im123
     * @return string
     */
    public static function get($annoyance)
    {
        $str = $annoyance . microtime(true) . self::randStr();
        return md5($str);
    }

	public static function randStr($length=6) {

		$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
	
	}
    
}

