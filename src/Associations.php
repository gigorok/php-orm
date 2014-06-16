<?php
/**
* php-orm
*
* @author Igor Gonchar <gigorok@gmail.com>
* @copyright 2013 Igor Gonchar
*/

namespace ORM;

trait Associations
{
    /**
     * Has And Belongs To Many association
     *
     * @param string $className
     * @param string $tableName
     * @param string $foreignKey
     * @param string $foreignKeyRelated
     * @return HasAndBelongsToMany
     */
    protected function hasAndBelongsToMany($className, $tableName = null, $foreignKey = null, $foreignKeyRelated = null)
    {
        if(is_null($tableName)) {
            $table1 = \Inflector\Inflector::tableize(get_class($this));
            $table2 = \Inflector\Inflector::tableize($className);
            $tables = [$table1, $table2];
            asort($tables);
            $tableName = implode('_', $tables);
        }

        return new HasAndBelongsToMany($this, $className, $tableName, $foreignKey, $foreignKeyRelated);
    }

    /**
     * Has Many association
     *
     * @param Model|string $className
     * @param null $foreign_key
     * @param string $sortField
     * @param bool $sortAsc
     * @return Model[]
     */
    protected function hasMany($className, $foreign_key = null, $sortField = '', $sortAsc = true)
    {
        if($sortField == '') {
            $sortField = $className::getPrimaryKey();
        }

        if(is_null($foreign_key)) {
            $foreign_key = static::getForeignKey();
        }

        return $className::findAll([$foreign_key], [$this->{static::getPrimaryKey()}], $sortField, $sortAsc);
    }

    /**
     * Has One association
     *
     * @param Model|string $class_name
     * @param null $foreign_key
     * @return Model|null
     */
    protected function hasOne($class_name, $foreign_key = null)
    {
        if(is_null($foreign_key)) {
            $foreign_key = static::getForeignKey();
        }

        return $class_name::findOne([$foreign_key], [$this->{static::getPrimaryKey()}]);
    }

    /**
     * Belongs To association
     *
     * @param Model|string $class_name
     * @param null $foreign_key
     * @return Model|null
     */
    protected function belongsTo($class_name, $foreign_key = null)
    {
        if(is_null($foreign_key)) {
            $foreign_key = strtolower($class_name) . '_id';
        }

        return $class_name::find(intval($this->$foreign_key));
    }
}