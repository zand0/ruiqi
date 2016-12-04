<?php

class Validate {

    public static function isEmail($email) {
        return !empty($email) && preg_match(Tools::cleanNonUnicodeSupport('/^[a-z\p{L}0-9!#$%&\'*+\/=?^`{}|~_-]+[.a-z\p{L}0-9!#$%&\'*+\/=?^`{}|~_-]*@[a-z\p{L}0-9]+[._a-z\p{L}0-9-]*\.[a-z0-9]+$/ui'), $email);
    }

    public static function isMobilePhone($mobilePhone) {
        return preg_match("/^1[0-9]{10}$/", $mobilePhone);
    }
    
    public static function isTelPhone($tel){
        return preg_match("/^([0-9]{3,4}-)?[0-9]{7,8}$/", $tel);
    }

    public static function isChinese($data) {
        return preg_match("/^[\x{4e00}-\x{9fa5}a-zA-Z_]+$/u", $data);
    }

    public static function isUrl($url) {
        return preg_match('/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/', $url);
    }

    public static function isFloat($float) {
        return strval((float) $float) == strval($float);
    }

    public static function isUnsignedFloat($float) {
        return strval((float) $float) == strval($float) && $float >= 0;
    }

    public static function isInt($data) {
        return preg_match("/^-?[0-9]+$/u", $data);
    }

    public static function isGtZeroInt($int) {
        return preg_match('/^[1-9]+\d*$/', $int);
    }

    public static function isGetZeroInt($int) {
        return preg_match('/^(0{1}|([1-9]+\d*))$/', $int);
    }

    public static function isLtZeroInt($int) {
        return preg_match('/^-[1-9]+\d*$/', $int);
    }

    public static function isLetZeroInt($int) {
        return preg_match('/^(0{1}|(-[1-9]+\d*))$/', $int);
    }

    public static function isName($name) {
        return preg_match(Tools::cleanNonUnicodeSupport('/^[^!<>,;?=+()@#"°{}$%:]+$/u'), stripslashes($name));
    }

    public static function isPasswd($passwd, $size = 6) {
        return preg_match('/^[A-Za-z_0-9\-\+!@#\$%\^&*\(\)]{'.$size.',32}$/ui', $passwd);
    }

    public static function isDateFormat($date) {
        return (bool) preg_match('/^([0-9]{4})-((0?[0-9])|(1[0-2]))-((0?[0-9])|([1-2][0-9])|(3[01]))( [0-9]{2}:[0-9]{2}:[0-9]{2})?$/', $date);
    }

    public static function isDate($date) {
        if (!preg_match('/^([0-9]{4})-((0?[1-9])|(1[0-2]))-((0?[1-9])|([1-2][0-9])|(3[01]))( [0-9]{2}:[0-9]{2}:[0-9]{2})?$/ui', $date, $matches))
            return false;

        return checkdate(intval($matches[2]), intval($matches[5]), intval($matches[0]));
    }

    public static function isTimestamp($time) {
        //return ctype_digit($time) && $time <= 2147483647;
        return (int) $time > 0 && strtotime(date('Y-m-d H:i:s', $time)) === (int) $time;
    }

    public static function isBirthDate($date) {
        if (empty($date) || $date == '0000-00-00')
            return true;
        if (preg_match('/^([0-9]{4})-((?:0?[1-9])|(?:1[0-2]))-((?:0?[1-9])|(?:[1-2][0-9])|(?:3[01]))([0-9]{2}:[0-9]{2}:[0-9]{2})?$/', $date, $birth_date)) {
            if ($birth_date[1] > date('Y') && $birth_date[2] > date('m') && $birth_date[3] > date('d'))
                return false;

            return true;
        }

        return false;
    }
    public static function isEmpty($str){
        if($str === null || $str === false || trim($str) === '')
            return true;
        return false;
    }
    

}
