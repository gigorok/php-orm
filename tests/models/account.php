<?php
/**
 * php-orm
 *
 * @author Igor Gonchar <gigorok@gmail.com>
 * @copyright 2014 Igor Gonchar
 */

class Account extends \ORM\Model
{
    function user()
    {
        return $this->belongsTo('User');
    }

    static $table_name = 'accounts';
}