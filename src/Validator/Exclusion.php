<?php
/**
* php-orm
*
* @author Igor Gonchar <gigorok@gmail.com>
* @copyright 2013 Igor Gonchar
*/

namespace ORM\Validator;

use ORM\Validator;

class Exclusion extends Validator
{
    function validate()
    {
        return !in_array($this->object->{$this->field}, $this->params['in'], 1);
    }

    function getMessage()
    {
        if(is_null($this->message)) {
            return 'is reserved';
        }

        return $this->message;
    }
}