<?php
/**
* php-orm
*
* @author Igor Gonchar <gigorok@gmail.com>
* @copyright 2013 Igor Gonchar
*/

namespace ORM;

/**
 * Class Validator
 * @package ORM
 */
abstract class Validator
{
    /**
     * @var
     */
    protected $object;

    /**
     * @var
     */
    protected $field;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var null
     */
    protected $message = null;

    /**
     * @param $object
     * @param $field
     * @param array $params
     * @param null $message
     */
    function __construct($object, $field, $params = [], $message = null)
    {
        $this->field = $field;
        $this->object = $object;
        $this->params = $params;
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    abstract function validate();

    /**
     * @return mixed
     */
    public function getAttribute()
    {
        return $this->field;
    }

    /**
     * @return null|string
     */
    public function getMessage()
    {
        if(is_null($this->message)) {
            return 'is invalid';
        }

        return $this->message;
    }
}
