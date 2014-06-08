<?php
/**
 * php-orm
 *
 * @author Igor Gonchar <gigorok@gmail.com>
 * @copyright 2014 Igor Gonchar
 */

class User extends \ORM\Model
{
    /**
     * @return \ORM\HasAndBelongsToMany
     */
    function messages()
    {
        return $this->hasAndBelongsToMany('Message');
    }

    /**
     * @return \ORM\Model
     */
    function role()
    {
        return $this->belongsTo('Role');
    }

    /**
     * @return \ORM\Model
     */
    function account()
    {
        return $this->hasOne('Account');
    }

    /**
     * @return \ORM\Model
     */
    function option()
    {
        return $this->hasOne('Option');
    }

    /**
     * @return bool
     */
    protected function validate()
    {
    }
}