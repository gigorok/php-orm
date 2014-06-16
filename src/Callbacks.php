<?php
/**
* php-orm
*
* @author Igor Gonchar <gigorok@gmail.com>
* @copyright 2013 Igor Gonchar
*/

namespace ORM;

trait Callbacks
{
    /**
     * @var bool
     */
    protected $no_callbacks = false;

    /**
     * If an after callback returns false, all the later callbacks are cancelled.
     *
     * @param $callback string
     * @param $payload bool
     */
    protected function performAfterCallback($callback, $payload)
    {
        if(!$this->no_callbacks) {
            if(!$this->$callback($payload)) {
                $this->no_callbacks = true;
            }
        }
    }

    /**
     * Call before save, create, update actions
     *
     * @return bool
     */
    protected function beforeSave()                 { return true; }

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

    /**
     * Save current object to database
     *
     * @return bool
     */
    public function save()
    {
        if(!$this->beforeSave()) { // If a beforeSave callback returns false, all the later callbacks and the associated action are cancelled.
            return false;
        }

        if($this->isNew() && !$this->beforeCreate()) { // beforeCreate callback should be run only for new records
            return false;
        }

        $result = parent::save();

        if($this->isNew()) { // afterCreate callback only for new records
            $this->performAfterCallback('afterCreate', true);
        }

        $this->performAfterCallback('afterSave', true);

        return $result;
    }

    /**
     * Destroy object from database
     *
     * @return bool
     */
    public function destroy()
    {
        if(!$this->beforeDestroy()) {
            return false;
        }

        $result = parent::destroy();

        $this->performAfterCallback('afterDestroy', $result);

        return $result;
    }

    /**
     * Update objects with params
     *
     * @param $params
     * @return bool
     */
    public function update($params)
    {
        if(!$this->beforeUpdate()) {
            return false;
        }

        $result = parent::update($params);

        $this->performAfterCallback('afterUpdate', $result);

        return $result;
    }
}