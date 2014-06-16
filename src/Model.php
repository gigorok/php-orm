<?php
/**
* php-orm
*
* @author Igor Gonchar <gigorok@gmail.com>
* @copyright 2013 Igor Gonchar
*/

namespace ORM;

/**
 * This class needs to realize chain calls because PHP is ugly
 * (chain calls is: ValidationCallbacks, Callbacks, Base)
 *
 * Class CallbackableModel
 * @package ORM
 */
abstract class CallbackableModel extends Base
{
    /**
     * Add Callbacks module supporting
     */
    use Callbacks;
}

class Model extends CallbackableModel
{
    use ValidationsCallbacks;

    /**
     * Add Validation module supporting
     */
    use Validations;

    /**
     * Add Associations module supporting
     */
    use Associations;

    /**
     * Add Transactions module supporting
     */
    use Transactions;
}
