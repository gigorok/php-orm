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
 * Class Format
 * @package ORM\Validator
 */
class Format extends Validator
{
    /**
     * @return bool|mixed
     * @throws \ORM\Exception
     */
    function validate()
    {
        if(self::isInValidRegExp($this->params['with'])) {
            throw new \ORM\Exception("Regexp is invalid");
        }

        return preg_match($this->params['with'], $this->object->{$this->field}) == 1;
    }

    /**
     * Check regexp function
     * @param $reg_exp
     * @return bool
     */
    private static function isInValidRegExp($reg_exp)
    {
        return @preg_match($reg_exp, null) === false;
    }
}