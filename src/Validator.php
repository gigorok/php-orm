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
    protected $field;
    protected $value;
    protected $params = [];
    protected $message = null;

    function __construct($field, $value, $params = [], $message = null)
    {
        $this->field = $field;
        $this->value = $value;
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

//empty: "can't be empty"
//blank: "can't be blank"
//present: "must be blank"
//too_long: "is too long (maximum is %{count} characters)"
//too_short: "is too short (minimum is %{count} characters)"
//wrong_length: "is the wrong length (should be %{count} characters)"
//not_a_number: "is not a number"
//not_an_integer: "must be an integer"
//greater_than: "must be greater than %{count}"
//greater_than_or_equal_to: "must be greater than or equal to %{count}"
//equal_to: "must be equal to %{count}"
//less_than: "must be less than %{count}"
//less_than_or_equal_to: "must be less than or equal to %{count}"
//other_than: "must be other than %{count}"
//odd: "must be odd"
//even: "must be even"