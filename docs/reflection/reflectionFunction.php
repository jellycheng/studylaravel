<?php
/**
 * 反射方法示例
 */

//无参数
function abc() {

    return 123;
}

function abc1($a=4) {

    return $a;
}

function abc2(xyz $x, $a=5) {

    return $a;
}

class xyz {
    public function a() {

    }
}

//$callback='方法名'或者闭包
$callback = 'abc';
demo1($callback);
demo1('abc1');
demo1('abc2');

function demo1($callback) {
    $function = new ReflectionFunction($callback);
    $i = $function->getNumberOfParameters();
    if ($i == 0)
    {//获取参数个数
        echo '无参数' . PHP_EOL;
        return null;
    }
    echo '参数个数: ' . $i . PHP_EOL;

    $expected = $function->getParameters()[0];//获取第1个参数,返回ReflectionParameter对象

    if ( ! $expected->getClass())
    {//
        echo "第1个参数不是类对象" . PHP_EOL;
        return null;
    }
    $clsName = $expected->getClass()->name; //获取类名
    echo '第1个参数是类对象,类名: '. $clsName . PHP_EOL;
    return $clsName;
}


