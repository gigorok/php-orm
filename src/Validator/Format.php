<?php
/**
* php-orm
*
* @author Igor Gonchar <gigorok@gmail.com>
* @copyright 2013 Igor Gonchar
*/

namespace ORM\Validator;

use ORM\Validator;

class Format extends Validator
{
    function validate()
    {
        if(self::isInValidRegExp($this->params['with'])) {
            throw new \Exception("Regexp is invalid");
        }

        return preg_match($this->params['with'], $this->value) == 1;
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