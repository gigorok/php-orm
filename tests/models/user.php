<?php
/**
 * php-orm
 *
 * @author Igor Gonchar <gigorok@gmail.com>
 * @copyright 2014 Igor Gonchar
 */

class User extends \ORM\Model
{
    static $accessible = ['id', 'email', 'first_name', 'last_name', 'role_id'];

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
        return $this->hasOne('Account', 'accounts');
    }

    /**
     * @return \ORM\Model
     */
    function option()
    {
        return $this->hasOne('Option', 'options');
    }

    /**
     * @return bool
     */
    protected function validate()
    {
        if(!isset($this->role_id) || !is_numeric($this->role_id)) {
            $this->addError('Role ID must be numeric');
        }
    }
}