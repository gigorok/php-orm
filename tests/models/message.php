<?php
/**
 * php-orm
 *
 * @author Igor Gonchar <gigorok@gmail.com>
 * @copyright 2014 Igor Gonchar
 */

class Message extends \ORM\Model
{
    static $validates = [
        'title' => ['length', ['maximum' => 20]]
    ];

    function users()
    {
        return $this->hasAndBelongsToMany('User');
    }
}