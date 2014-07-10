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
        return self::getConnection()->getPDO()->beginTransaction();
    }

    /**
     * Commits a transaction
     *
     * @return bool
     */
    public static function commit()
    {
        return self::getConnection()->getPDO()->commit();
    }

    /**
     * Rolls back a transaction
     *
     * @return bool
     */
    public static function rollback()
    {
        return self::getConnection()->getPDO()->rollBack();
    }

    /**
     * Checks if inside a transaction
     *
     * @return bool
     */
    public static function inTransaction()
    {
        return self::getConnection()->getPDO()->inTransaction();
    }

    /**
     * @param callable $closure
     * @return bool
     * @throws \Exception
     */
    public static function transaction(\Closure $closure)
    {
        static::beginTransaction();

        try {
            call_user_func($closure);

            static::commit();

        } catch(\Exception $e) {
            static::rollback();

            throw new \Exception($e);
        }

        return true;
    }
}