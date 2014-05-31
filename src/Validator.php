<?php
/**
* php-orm
*
* @author Igor Gonchar <gigorok@gmail.com>
* @copyright 2013 Igor Gonchar
*/

namespace ORM;

abstract class Validator
{
    protected $object;
    protected $field;
    protected $params = [];
    protected $message = null;

    function __construct($object, $field, $params = [], $message = null)
    {
        $this->field = $field;
        $this->object = $object;
        $this->params = $params;
        $this->message = $message;
    }

    abstract function validate();

    public function getAttribute()
    {
        return $this->field;
    }

    public function getMessage()
    {
        if(is_null($this->message)) {
            return 'is invalid';
        }

        return $this->message;
    }
}
