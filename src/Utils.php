<?php
/**
* php-orm
*
* @author Igor Gonchar <gigorok@gmail.com>
* @copyright 2013 Igor Gonchar
*/

namespace ORM;

class Utils
{
    public static function arrayWrap($param)
    {
        return is_array($param) ? $param : array($param);
    }

    public static function arrayHasKey(array $arr, $key)
    {
        return array_key_exists($key, $arr);
    }

    public static function arrayHasValue(array $arr, $value)
    {
        return in_array($value, array_values($arr), 1);
    }

    public static function arrayIsEmpty(array $arr)
    {
        return count($arr) == 0;
    }

    public static function arrayIsNotEmpty(array $arr)
    {
        return count($arr) > 0;
    }

}