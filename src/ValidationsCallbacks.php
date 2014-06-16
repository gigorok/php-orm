<?php
/**
* php-orm
*
* @author Igor Gonchar <gigorok@gmail.com>
* @copyright 2013 Igor Gonchar
*/

namespace ORM;

trait ValidationsCallbacks
{
    /**
     * Call before save (if validation was permitted), create, update actions
     *
     * @return bool
     */
    protected function beforeValidation()           { return true; }

    /**
     * Call after save (if validation was permitted), create, update actions
     *
     * @param $isValid bool Is object is valid
     * @return bool
     */
    protected function afterValidation($isValid)    { return $isValid; }

    /**
     * Save current object to database
     *
     * @param bool $validate
     * @return bool
     */
    public function save($validate = true)
    {
        if($validate) {

            if(!$this->beforeValidation()) { // If the returning value of a beforeValidation callback can be evaluated to false, the process will be aborted and Model#save will return false.
                return false;
            }

            $isValid = $this->isValid();

            $this->performAfterCallback('afterValidation', $isValid);

            if(!$isValid) {
                return false;
            }
        }

        return parent::save();
    }
}