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
     * @param $field
     * @param array $params
     * @param null $message
     */
    function __construct($field, $params = [], $message = null)
    {
        $this->field = $field;
        $this->params = $params;
        $this->message = $message;
    }

    /**
     * @param Model $record
     * @return mixed
     */
    abstract function validate(\ORM\Model $record);

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
