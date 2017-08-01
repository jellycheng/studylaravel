<?php
namespace App\Lib;
/**
 * 扩展验证类
 */
class Validator extends \Illuminate\Validation\Validator {

    public function validateUint32($sAttribute, $mValue, $aParameters)
    {
        return filter_var($mValue, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 4294967295]]) !== false;
    }

    protected function replaceUint32($sMessage, $sAttribute, $sRule, $aParameters)
    {
        return $sMessage == 'validation.uint32' ? "{$sAttribute} 必须为32位无符号整型。" : str_replace(':attribute', $sAttribute, $sMessage);
    }

    public function validateInt32($sAttribute, $mValue, $aParameters)
    {
        return filter_var($mValue, FILTER_VALIDATE_INT, ['options' => ['min_range' => -2147493648, 'max_range' => 2147493647]]) !== false;
    }

    protected function replaceInt32($sMessage, $sAttribute, $sRule, $aParameters)
    {
        return $sMessage == 'validation.int32' ? "{$sAttribute} 必须为32位整型。" : str_replace(':attribute', $sAttribute, $sMessage);
    }

    public function validateIdCard($sAttribute, $mValue, $aParameters)
    {
        if (preg_match('~^[0-9]{15}$~', $mValue)) {
            return true;
        } elseif (preg_match('~^[0-9]{6}(?<date>[0-9]{8})[0-9]{3}[xX0-9]$~', $mValue, $aMatch)) {
            $iDate = intval($aMatch['date']);
            if ($iDate < 19000000 || $iDate > 25000000) {
                return false;
            }
            $aWeight  = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
            $sVerify  = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
            $aNumbers = str_split($mValue);
            $sLast    = strtoupper(array_pop($aNumbers));
            $iWeight  = 0;
            foreach ($aNumbers as $iIndex => $iNumber) {
                $iWeight += $iNumber * $aWeight[$iIndex];
            }
            return $sLast == $sVerify[$iWeight % 11];
        }
        return false;
    }

    protected function replaceIdCard($sMessage, $sAttribute, $sRule, $aParameters)
    {
        return $sMessage == 'validation.id_card' ? "{$sAttribute} 不是一个合法的身份证号。" : str_replace(':attribute', $sAttribute, $sMessage);
    }

    public function validateMobile($sAttribute, $mValue, $aParameters)
    {
        return 0 < preg_match('/^((1[3-9][0-9])|200)[0-9]{8}$/', $mValue);
    }

    protected function replaceMobile($sMessage, $sAttribute, $sRule, $aParameters)
    {
        return $sMessage == 'validation.mobile' ? "{$sAttribute} 必须是一个有效的手机号。" : str_replace(':attribute', $sAttribute, $sMessage);
    }

    public function validatePhone($sAttribute, $mValue, $aParameters)
    {
        return 0 < preg_match('/^(\d{3,4}-?)?\d{7,8}$/', $mValue);
    }

    protected function replacePhone($sMessage, $sAttribute, $sRule, $aParameters)
    {
        return $sMessage == 'validation.phone' ? "{$sAttribute} 必须是一个有效的电话号码。" : str_replace(':attribute', $sAttribute, $sMessage);
    }

    public function validateChinese($sAttribute, $mValue, $aParameters)
    {
        return 0 < preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $mValue);
    }

    protected function replaceChinese($sMessage, $sAttribute, $sRule, $aParameters)
    {
        return $sMessage == 'validation.chinese' ? "{$sAttribute} 必须是中文。" : str_replace(':attribute', $sAttribute, $sMessage);
    }

}

