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
 * Class Custom
 * @package ORM\Validator
 */
class Custom extends Validator
{
    /**
     * @return mixed
     */
    function validate()
    {
        return call_user_func($this->params['closure'], $this->object->{$this->field});
    }

}