<?php
/**
* php-orm
*
* @author Igor Gonchar <gigorok@gmail.com>
* @copyright 2013 Igor Gonchar
*/

namespace ORM\Validator;

use ORM\Validator;

class Presence extends Validator
{
    function validate()
    {
        return !(is_null($this->value) && (trim($this->value) === ''));
    }

    function getMessage()
    {
        if(is_null($this->message)) {
            return 'can\'t be blank';
        }

        return $this->message;
    }
}