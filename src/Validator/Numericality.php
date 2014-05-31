<?php
/**
* php-orm
*
* @author Igor Gonchar <gigorok@gmail.com>
* @copyright 2013 Igor Gonchar
*/

namespace ORM\Validator;

use ORM\Validator;

class Numericality extends Validator
{
    function validate()
    {
        return is_numeric($this->object->{$this->field});
    }

    function getMessage()
    {
        if(is_null($this->message)) {
            return 'is not a number';
        }

        return $this->message;
    }

}