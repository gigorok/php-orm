<?php
/**
 * php-orm
 *
 * @author Igor Gonchar <gigorok@gmail.com>
 * @copyright 2014 Igor Gonchar
 */

class Message extends \ORM\Model
{
    static $accessible = ['title'];

    function users()
    {
        return $this->hasAndBelongsToMany('User');
    }
}