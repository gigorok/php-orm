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
 * Class Exclusion
 * @package ORM\Validator
 */
class Exclusion extends Validator
{
    /**
     * @param \ORM\Model $record
     * @return bool|mixed
     */
    function validate(\ORM\Model $record)
    {
        return !in_array($record->{$this->field}, $this->params['in'], 1);
    }

    /**
     * @return null|string
     */
    function getMessage()
    {
        if(is_null($this->message)) {
            return 'is reserved';
        }

        return $this->message;
    }
}