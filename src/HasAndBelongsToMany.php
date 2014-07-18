<?php
/**
 * php-orm
 *
 * @author Igor Gonchar <gigorok@gmail.com>
 * @copyright 2014 Igor Gonchar
 */

namespace ORM;
use \Inflector\Inflector as Inflector;

/**
 * Class HasAndBelongsToMany
 *
 * @package ORM
 */
class HasAndBelongsToMany
{
    /**
     * Name of related model
     *
     * @var string
     */
    private $className;

    /**
     * HABTM table name
     *
     * @var string
     */
    private $tableName;

    /**
     * Current object
     *
     * @var \ORM\Model
     */
    private $model;

    /**
     * @var string
     */
    private $foreignKey;

    /**
     * @var string
     */
    private $foreignKeyRelated;

    /**
     * Constructor
     *
     * @param \ORM\Model $model
     * @param string $className
     * @param string $tableName
     * @param string $foreignKey
     * @param string $foreignKeyRelated
     */
    function __construct($model, $className, $tableName, $foreignKey, $foreignKeyRelated)
    {
        $this->model = $model;
        $this->className = $className;
        $this->tableName = $tableName;

        $this->foreignKey = $foreignKey;
        $this->foreignKeyRelated = $foreignKeyRelated;
    }

    /**
     * @return string
     */
    function getForeignKey()
    {
        $model = $this->model;
        return $this->foreignKey ?: $model::getForeignKey();
    }

    /**
     * @return string
     */
    function getForeignKeyRelated()
    {
        $className = $this->className;
        return $this->foreignKeyRelated ?: $className::getForeignKey();
    }

    /**
     * Get related objects
     *
     * @param array $fields
     * @param array $values
     * @return \ORM\Model[]
     */
    function get($fields = [], $values = [])
    {
        $className = $this->className;
        $model = $this->model;

        $tableName = $className::getTable();
        $fk1 = $this->getForeignKeyRelated();
        $fk2 = $this->getForeignKey();

        // build conditions
        $conditions = [];
        foreach($fields as $field) {
            $conditions[] = "$tableName.$field = ?";
        }
        $conditionsStr = count($conditions) > 0 ? " AND " . implode(" AND ", $conditions) : "";
        //

        $pKey = $model::getPrimaryKey();

        $query = "  SELECT $tableName.* FROM $tableName
                    LEFT JOIN $this->tableName ON $tableName.$pKey = $this->tableName.$fk1
                    WHERE $this->tableName.$fk2 = ? $conditionsStr";

        return Model::getConnection()->getObjectsQuery($query, array_merge([$model->{$model::getPrimaryKey()}], $values), $className);
    }

    /**
     * Has related object with ID=$id
     *
     * @param \ORM\Model|int $payload
     * @throws \ORM\Exception
     * @return bool
     */
    function has($payload)
    {
        if(($payload instanceof Model) && isset($payload->{$payload::getPrimaryKey()}) && is_numeric($payload->{$payload::getPrimaryKey()})) {
            $id = $payload->{$payload::getPrimaryKey()};
        } else if(is_numeric($payload)){
            $id = $payload;
        } else {
            throw new \ORM\Exception('Payload ' . $payload . ' is invalid');
        }

        $model = $this->model;
        $pKey = $model::getPrimaryKey();

        return (bool)$this->pivot()->count([$this->getForeignKey(), $this->getForeignKeyRelated()], [$this->model->$pKey, $id]);
    }

    /**
     * Count of related objects
     *
     * @return int
     */
    function count()
    {
        $model = $this->model;
        $pKey = $model::getPrimaryKey();

        return intval($this->pivot()->count([$this->getForeignKey()], [$this->model->$pKey]));
    }

    /**
     * Insert and relate objects
     *
     * @param  array $rows
     * @return array
     */
    function insert(array $rows)
    {
        // normalize rows
        if(!is_array($rows)) {
            $rows = [$rows];
        }

        $result = [];

        foreach($rows as $row) {
            /** @var \ORM\Model $o */
            $o = new $this->className($row);
            $o->save();
            $result[] = $o->{$o::getPrimaryKey()};
            $this->attach($o->{$o::getPrimaryKey()});
        }

        return $result;
    }

    /**
     * Attach related object with ID=$id
     *
     * @param \ORM\Model|int $payload
     * @throws \ORM\Exception
     * @return bool
     */
    function attach($payload)
    {
        if(($payload instanceof Model) && isset($payload->{$payload::getPrimaryKey()}) && is_numeric($payload->{$payload::getPrimaryKey()})) {
            $id = $payload->{$payload::getPrimaryKey()};
        } else if(is_numeric($payload)){
            $id = $payload;
        } else {
            throw new \ORM\Exception('Payload ' . $payload . ' is invalid');
        }

        $pivot = $this->pivot();
        if($this->has($id)) {
            return true;
        }

        $model = $this->model;
        $pKey = $model::getPrimaryKey();

        $pivot->{$this->getForeignKey()} = $this->model->$pKey;
        $pivot->{$this->getForeignKeyRelated()} = $id;

        return (bool) $pivot->save();
    }

    /**
     * Synchronize objects
     *
     * @param array $ids
     * @return bool
     */
    function sync($ids = [])
    {
        if(!is_array($ids)) {
            $ids = [$ids];
        }
        // clear data
        $this->delete();
        // attach new ids
        foreach($ids as $id) {
            if(!$this->attach($id)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get Pivot model
     *
     * @return \ORM\Model
     */
    function pivot()
    {
        list($t1, $t2) = explode('_', $this->tableName);
        $t1 = Inflector::singularize($t1);
        $t2 = Inflector::singularize($t2);
        $className = Inflector::classify(implode('_', [$t1, $t2]));

        $fk1 = $this->getForeignKey();
        $fk2 = $this->getForeignKeyRelated();

        if(!class_exists($className, false)) {
            $code = <<<PIVOT
                class $className extends \ORM\Model
                {
                    static function getTable()
                    {
                        return '$this->tableName';
                    }

                    function validate()
                    {
                        if(!is_numeric(\$this->$fk1)) {
                            \$this->addError('$fk1 must be numeric');
                        }

                        if(!is_numeric(\$this->$fk2)) {
                            \$this->addError('$fk2 must be numeric');
                        }
                    }
                }
PIVOT;

            eval($code);
        }

        return new $className;
    }

    /**
     * Delete relations
     *
     * @param int|\ORM\Model $payload
     * @return bool
     */
    function delete($payload = null)
    {
        $id = null;
        if(($payload instanceof Model) && isset($payload->{$payload::getPrimaryKey()}) && is_numeric($payload->{$payload::getPrimaryKey()})) {
            $id = $payload->{$payload::getPrimaryKey()};
        } else if(is_numeric($payload)){
            $id = $payload;
        }

        $model = $this->model;
        $pKey = $model::getPrimaryKey();

        if(is_null($id)) { // remove all linked rows
            $sql = "DELETE FROM $this->tableName WHERE " . $this->getForeignKey() . ' = ?';

            return Model::getConnection()->runQuery($sql, [$this->model->$pKey]);
        } else {
            return $this->pivot()->findOne([$this->getForeignKey(), $this->getForeignKeyRelated()], [$this->model->$pKey, $id])->destroy();
        }

    }

}