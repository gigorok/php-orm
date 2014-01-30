<?php
/**
 * php-orm
 *
 * @author Igor Gonchar <gigorok@gmail.com>
 * @copyright 2014 Igor Gonchar
 */

class Option extends \ORM\Model
{
    static $accessible = ['user_id', 'name'];

    /**
     * @return bool
     */
    protected function validate()
    {
        // TODO: Implement validate() method.
    }

    static function getPrimaryKey()
    {
        return 'user_id';
    }

}