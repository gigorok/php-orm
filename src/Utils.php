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

}