<?php
/**
 * php-orm
 *
 * @author Igor Gonchar <gigorok@gmail.com>
 * @copyright 2014 Igor Gonchar
 */

class Role extends \ORM\Model
{
    function users()
    {
        return $this->hasMany('User');
    }

    /**
     * @return bool
     */
    protected function validate()
    {
        // TODO: Implement validate() method.
    }
}