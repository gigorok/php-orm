<?php
/**
* php-orm
*
* @author Igor Gonchar <gigorok@gmail.com>
* @copyright 2014 Igor Gonchar
*/

namespace ORM;

/**
 * Class Callbackable
 * @package ORM
 */
trait Callbackable
{
    /**
     * Call before save, create, update actions
     *
     * @return bool
     */
    protected function beforeSave()                 { return true; }

    /**
     * Call before save (if validation was permitted), create, update actions
     *
     * @return bool
     */
    protected function beforeValidation()           { return true; }

    /**
     * Call before update action
     *
     * @return bool
     */
    protected function beforeUpdate()               { return true; }

    /**
     * Call before create action
     *
     * @return bool
     */
    protected function beforeCreate()               { return true; }

    /**
     * Call before destroy action
     *
     * @return bool
     */
    protected function beforeDestroy()              { return true; }

    /**
     * Call after save (if validation was permitted), create, update actions
     *
     * @param $isValid bool Is object is valid
     * @return bool
     */
    protected function afterValidation($isValid)    { return $isValid; }

    /**
     * Call after save, create, update actions
     *
     * @param $isSaved bool
     * @return bool
     */
    protected function afterSave($isSaved)          { return $isSaved; }

    /**
     * Call after update action
     *
     * @param $isUpdated bool
     * @return bool
     */
    protected function afterUpdate($isUpdated)      { return $isUpdated; }

    /**
     * Call after create action
     *
     * @param $isCreated bool
     * @return bool
     */
    protected function afterCreate($isCreated)      { return $isCreated; }

    /**
     * Call after destroy action
     *
     * @param $isDestroyed bool
     * @return bool
     */
    protected function afterDestroy($isDestroyed)   { return $isDestroyed; }
}