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
        return $this->belongsTo('User', 'users');
    }

    static function getTable()
    {
        return 'accounts';
    }

    /**
     * @return bool
     */
    protected function validate()
    {
        // TODO: Implement validate() method.
    }
}