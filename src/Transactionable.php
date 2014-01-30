<?php
/**
* php-orm
*
* @author Igor Gonchar <gigorok@gmail.com>
* @copyright 2014 Igor Gonchar
*/

namespace ORM;

/**
 * Class Transactionable
 * @package ORM
 */
trait Transactionable
{
    /**
     * Initiates a transaction
     *
     * @return bool
     */
    static function beginTransaction()
    {
        return Model::getDBO()->getPDO()->beginTransaction();
    }

    /**
     * Commits a transaction
     *
     * @return bool
     */
    static function commit()
    {
        return Model::getDBO()->getPDO()->commit();
    }

    /**
     * Rolls back a transaction
     *
     * @return bool
     */
    static function rollback()
    {
        return Model::getDBO()->getPDO()->rollBack();
    }

    /**
     * Checks if inside a transaction
     *
     * @return bool
     */
    static function inTransaction()
    {
        return Model::getDBO()->getPDO()->inTransaction();
    }
}