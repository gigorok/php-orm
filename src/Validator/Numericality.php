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
 * Class Numericality
 * @package ORM\Validator
 */
class Numericality extends Validator
{
    /**
     * @param \ORM\Model $record
     * @return bool|mixed
     */
    function validate(\ORM\Model $record)
    {
        return is_numeric($record->{$this->field});
    }

    /**
     * @return null|string
     */
    function getMessage()
    {
        if(is_null($this->message)) {
            return 'is not a number';
        }

        return $this->message;
    }

}