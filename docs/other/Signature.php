<?php
namespace Jelly\Auth;

/**
 * 服务器签名算法类
 *
 * Class Signature
 * @package Ananzu\Auth
 */
class Signature {

    const APP_SECRET = '797z3it2mdh44weikz4x513irjq22pu9y292k246';

    /**
     * 生成签名
     *
     * @param array $param
     * @param array $data
     *
     * @return array
     */
    public static function makeSign(array $param, array $data) {
        ksort($data);
        return md5(
            sprintf(
                'data=%s&time=%s&apiSequence=%s&signFuncID=%s%s',
                json_encode($data),
                $param['time'],
                $param['apiSequence'],
                $param['signFuncID'],
                self::APP_SECRET
            )
        );
    }

    public static function verifySign(array $data) {
        return 0 === strcmp(
            self::makeSign(
                [
                    'signFuncID'  => $_GET['signFuncID'],
                    'time'   => $_GET['time'],
                    'apiSequence' => md5($_SERVER['REQUEST_URI']),
                ], $data), $_GET['signature']);
    }
}