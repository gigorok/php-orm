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
 * Class Presence
 * @package ORM\Validator
 */
class Presence extends Validator
{
    /**
     * @param \ORM\Model $record
     * @return bool|mixed
     */
    function validate(\ORM\Model $record)
    {
        return !(is_null($record->{$this->field}) || (trim($record->{$this->field}) === ''));
    }

    /**
     * @return null|string
     */
    function getMessage()
    {
        if(is_null($this->message)) {
            return 'can\'t be blank';
        }

        return $this->message;
    }
}