<?php
/**
* php-orm
*
* @author Igor Gonchar <gigorok@gmail.com>
* @copyright 2013 Igor Gonchar
*/

namespace ORM;

trait Schemas
{
    /**
     * Get schema of instance
     *
     * @return string[]
     */
    public static function schema()
    {
        static $schema = [];

        if(empty($schema)) {
            $rs = self::getConnection()->getPDO()->query('SELECT * FROM ' . static::getTable() . ' LIMIT 0');
            for ($i = 0; $i < $rs->columnCount(); $i++) {
                $col = $rs->getColumnMeta($i);
                $schema[$col['name']] = $col['native_type'];
            }
        }

        return $schema;
    }

    /**
     * Get instance's properties
     *
     * @return array
     */
    public static function properties()
    {
        return array_keys(self::schema());
    }
}