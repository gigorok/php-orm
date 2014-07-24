<?php
/**
* php-orm
*
* @author Igor Gonchar <gigorok@gmail.com>
* @copyright 2013 Igor Gonchar
*/

namespace ORM\Validator;

use ORM\Validator;

/**
 * Class Uniqueness
 * @package ORM\Validator
 */
class Uniqueness extends Validator
{
    /**
     * @param \ORM\Model $record
     * @return bool|mixed
     */
    function validate(\ORM\Model $record)
    {
        $class_name = $record::className();

        if($record->isPersisted()) {
            return count($class_name::where($this->field . " = ? AND " . $class_name::getPrimaryKey() . " != ?", [$record->{$this->field}, $record->{$class_name::getPrimaryKey()}])) === 0;
        } else {
            return count($class_name::where($this->field . " = ?", [$record->{$this->field}])) === 0;
        }
    }

    /**
     * @return null|string
     */
    public function getMessage()
    {
        if(is_null($this->message)) {
            return 'is not unique';
        }

        return $this->message;
    }

}