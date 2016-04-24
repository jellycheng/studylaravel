<?php
//实现ArrayAccess接口 场景： 1. 解决警告错误，2可以直接把对象当数组来用

header("Content-type: text/html; charset=utf-8");

class Jelly implements ArrayAccess {

    public function offsetSet($offset, $value) {
        echo 'Jelly类的 ' . __METHOD__ . "方法被调用{$offset}={$value}<br>";
    }
    
	public function offsetExists($var) {
        echo 'Jelly类的 ' . __METHOD__ . "方法被调用{$var}<br>";
        if ($var == "foobar") {
            return true;
        }
        return false;
    }
    
	public function offsetUnset($var) {
        echo 'Jelly类的 ' . __METHOD__ . "方法被调用{$var}<br>";
    }


    public function offsetGet($var) {
        echo 'Jelly类的 ' . __METHOD__ . "方法被调用{$var}<br>";
        return "val";
    }
}

$obj = new Jelly(); //实例化类对象，如果类是实现了ArrayAccess接口的则对象可以当数组来用
//isset($obj["foobar"]) 等价 $obj->offsetExists('foobar');
var_dump(isset($obj["foobar"])); //执行isset()方法会调用对象的offsetExists(接收的值是对象的key名如foobar)方法

echo "<br>";

var_dump(empty($obj["foobar"])); //执行empty()方法会调用offsetExists()方法，返回真则会再接着调用offsetGet()方法

echo "<br>";
echo $obj["foobaz"]; //会调用 offsetGet('foobaz')方法

echo "<br>";
unset($obj['123']); //会调用 offsetUnset('123')方法

echo "<br>";
$obj['hi'] = '456'; //会调用offsetSet('hi', '456')方法
