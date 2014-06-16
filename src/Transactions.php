<?php
/**
* php-orm
*
* @author Igor Gonchar <gigorok@gmail.com>
* @copyright 2013 Igor Gonchar
*/

namespace ORM;

trait Transactions
{
    /**
     * Initiates a transaction
     *
     * @return bool
     */
    public static function beginTransaction()
    {
        return self::getDBO()->getPDO()->beginTransaction();
    }

    /**
     * Commits a transaction
     *
     * @return bool
     */
    public static function commit()
    {
        return self::getDBO()->getPDO()->commit();
    }

    /**
     * Rolls back a transaction
     *
     * @return bool
     */
    public static function rollback()
    {
        return self::getDBO()->getPDO()->rollBack();
    }

    /**
     * Checks if inside a transaction
     *
     * @return bool
     */
    public static function inTransaction()
    {
        return self::getDBO()->getPDO()->inTransaction();
    }
}