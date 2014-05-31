<?php
/**
* php-orm
*
* @author Igor Gonchar <gigorok@gmail.com>
* @copyright 2013 Igor Gonchar
*/

namespace ORM\Validator;

use ORM\Validator;

class Uniqueness extends Validator
{
    function validate()
    {
        /** @var $class_name \ORM|Model */
        $class_name = get_class($this->object);

        if($this->object->isPersisted()) {
            return count($class_name::where($this->field . " = ? AND " . $class_name::getPrimaryKey() . " != ?", [$this->object->{$this->field}, $this->object->{$class_name::getPrimaryKey()}])) === 0;
        } else {
            return count($class_name::where($this->field . " = ?", [$this->object->{$this->field}])) === 0;
        }
    }

    public function getMessage()
    {
        if(is_null($this->message)) {
            return 'is not unique';
        }

        return $this->message;
    }

}