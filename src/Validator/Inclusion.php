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
 * Class Inclusion
 * @package ORM\Validator
 */
class Inclusion extends Validator
{
    /**
     * @return bool|mixed
     */
    function validate()
    {
        return in_array($this->object->{$this->field}, $this->params['in'], 1);
    }

    /**
     * @return null|string
     */
    function getMessage()
    {
        if(is_null($this->message)) {
            return 'is not included in the list';
        }

        return $this->message;
    }
}