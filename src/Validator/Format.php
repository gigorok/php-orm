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
     * @param \ORM\Model $record
     * @return bool|mixed
     * @throws \ORM\Exception
     */
    function validate(\ORM\Model $record)
    {
        if(self::isInValidRegExp($this->params['with'])) {
            throw new \ORM\Exception("Regexp is invalid");
        }

        return preg_match($this->params['with'], $record->{$this->field}) == 1;
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
