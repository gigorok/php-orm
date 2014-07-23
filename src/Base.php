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
 * Class Model
 * @package ORM
 */
class Base
{
    /**
     * @var string|null
     */
    public static $primary_key = null;

    /**
     * @var string|null
     */
    public static $table_name = null;

    /**
     * @var \ORM\DBO
     */
    public static $connection = null;

    /**
     * Persisted option
     *
     * @var bool
     */
    public $is_persisted = false;

    /**
     * Attributes container
     * @var array
     */
    protected $attributes = [];

    /**
     * Establish connection
     *
     * @param $connection Connection connection params
     */
    public static function establishConnection(Connection $connection)
    {
        static::$connection = $connection->getInstance();
    }

    /**
     * Get name of primary key
     *
     * @return string
     */
    public static function getPrimaryKey()
    {
        return is_null(static::$primary_key) ? 'id' : static::$primary_key;
    }

    /**
     * Get property of instance magically
     *
     * @param string $property
     * @return string
     */
    public function __get($property)
    {
        if(in_array($property, array_keys($this->attributes()))) {
            return $this->$property = $this->attributes()[$property];
        } else {
            $class_name = static::className();
            trigger_error("Undefined property $property for $class_name", E_USER_NOTICE);
        }
    }

    /**
     * Get table name
     *
     * @return string
     */
    public static function getTable()
    {
        return is_null(static::$table_name) ? Inflector::tableize(static::className()) : static::$table_name;
    }

    /**
     * Get DBO object
     *
     * @throws \ORM\Exception
     * @return \ORM\DBO
     */
    public static function getConnection()
    {
        if(!static::$connection) {
            throw new \ORM\Exception('Connection is not configured properly');
        }

        return static::$connection;
    }

    /**
     * Save current object to database
     *
     * @return bool
     */
    public function save()
    {
        $attributes = $this->attributes(true);

        if($this->isNew()) {
            $result = self::getConnection()->insert(static::getTable(), $attributes, static::getPrimaryKey());

            if($result) {
                $this->{static::getPrimaryKey()} = $result;
            }
        } else {
            $result = self::getConnection()->update(static::getTable(), $attributes, static::getPrimaryKey());
        }

        $this->is_persisted = true;

        return $result;
    }

    /**
     * Returns an array of all the attributes with their names as keys and the values of the attributes as values.
     *
     * @param bool $reload
     * @return string[]
     */
    public function attributes($reload = false)
    {
        if($reload || empty($this->attributes)) {
            $table_name = static::getTable();
            $rs = self::getConnection()->getPDO()->query("SELECT * FROM {$table_name} LIMIT 0");
            for ($i = 0; $i < $rs->columnCount(); $i++) {
                $col = $rs->getColumnMeta($i);
                $this->attributes[$col['name']] = isset($this->$col['name']) ? $this->$col['name'] : null;
            }
        }

        return $this->attributes;
    }

    /**
     * Reload record
     *
     * @return $this
     * @throws \ORM\Exception
     */
    public function reload()
    {
        if($this->isNew()) {
            throw new \ORM\Exception('Record not found');
        }

        /** @var $object \ORM\Model */
        $object = $this->find($this->{self::getPrimaryKey()});

        foreach($object->attributes() as $attribute => $value) {
            $this->$attribute = $value;
        }

        return $this;
    }

    /**
     * Get class name
     *
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }

    /**
     * Constructor
     *
     * @param array $params
     * @return \ORM\Base
     */
    public function __construct($params = [])
    {
        return $this->initialize($params);
    }

    /**
     * Initialize new record, can be redefined with traits
     *
     * @param array $params
     * @return $this
     */
    public function initialize($params = [])
    {
        return $this->bind($params);
    }

    /**
     * Create object with $params
     *
     * @param [] $params
     * @return bool
     */
    public static function create($params)
    {
        /** @var $object \ORM\Model */
        $className = static::className();
        $object = new $className($params);
        $object->save();

        return $object;
    }

    /**
     * Get foreign key name
     *
     * @return string
     */
    public static function getForeignKey()
    {
        return Inflector::underscore(Inflector::singularize(static::className())) . '_' . static::getPrimaryKey();
    }

    /**
     * Update objects with params
     *
     * @param $params
     * @return bool
     */
    public function update($params)
    {
        return $this->bind($params)->save();
    }

    /**
     * Bind parameters
     *
     * @param array $array
     * @return $this
     */
    public function bind($array)
    {
        foreach ($array as $key => $value) {
            $this->$key = $value;
        }

        return $this;
    }

    /**
     * Destroy object from database
     *
     * @return bool
     */
    public function destroy()
    {
        if($this->isNew()) {
            return false;
        } else {
            $result = self::getConnection()->deleteObject(
                static::getTable(),
                $this->{static::getPrimaryKey()},
                static::getPrimaryKey()
            );
            if($result) {
                $this->is_persisted = false;
            }
        }

        return $result;
    }

    /**
     * Destroy objects by conditions
     *
     * @param array $fields
     * @param array $values
     * @return bool
     */
    public static function destroyBy($fields = [], $values = [])
    {
        return self::getConnection()->deleteObjects(
            static::getTable(),
            $fields,
            $values
        );
    }

    /**
     * Find record by primary key
     *
     * @param $value
     * @return $this|null
     */
    public static function find($value)
    {
        $result = self::getConnection()->getObject(
            static::getTable(),
            $value,
            static::className(),
            static::getPrimaryKey()
        );

        if($result) {
            $result->is_persisted = true;
        }

        return $result ?: null;
    }

    /**
     * Count records in table by conditions
     *
     * @param array $fields
     * @param array $values
     * @return int
     */
    public static function count($fields = [], $values = [])
    {
        return self::getConnection()->count(
            static::getTable(),
            $fields,
            $values
        );
    }

    /**
     * Find many instances by conditions
     *
     * @param array $fields
     * @param array $values
     * @param string $sortField
     * @param bool $sortAsc
     * @param null $limit
     * @param int $offset
     * @return $this[]
     */
    public static function findAll(
        $fields = [],
        $values = [],
        $sortField = '',
        $sortAsc = true,
        $limit = null,
        $offset = 0
    )
    {
        if($sortField == '') {
            $sortField = static::getPrimaryKey();
        }

        if(count($fields) == 0) {
            return self::all($sortField, $sortAsc, $limit, $offset);
        }

        return self::getConnection()->findObjects(
            static::getTable(),
            $fields,
            $values,
            $sortField,
            $sortAsc,
            $limit,
            $offset,
            static::className()
        );
    }

    /**
     * Search entries by condition string
     *
     * @example User::where("email = ? AND (is_subscribed = ? OR is_active = ?)", ['email@example.com', 1, 0])
     * @param string $whereStr
     * @param array $values
     * @param string $sortField
     * @param bool $sortAsc
     * @return $this[]
     */
    public static function where(
        $whereStr,
        $values = [],
        $sortField = 'id',
        $sortAsc = true
    )
    {
        $table_name = static::getTable();
        $direction = $sortAsc ? ' ASC' : ' DESC';

        $query = "SELECT * FROM {$table_name} WHERE {$whereStr} ORDER BY {$sortField} {$direction}";

        return self::getConnection()->getObjectsQuery(
            $query,
            $values,
            static::className()
        );
    }

    /**
     * Find one row by conditions
     *
     * @param array $fields
     * @param array $values
     * @return $this|null
     */
    public static function findOne($fields = [], $values = [])
    {
        if(count($fields) > 0) {
            return self::getConnection()->findObject(
                static::getTable(),
                $fields,
                $values,
                static::className()
            );
        }

        return null;
    }

    /**
     * Find or initialize by conditions
     *
     * @param array $fields
     * @param array $values
     * @return \ORM\Model|null
     */
    public static function findOrInitializeBy($fields = [], $values = [])
    {
        $result = self::findOne($fields, $values);

        if(!is_null($result)) {
            return $result;
        }
        // initialize new instance

        $class_name = static::className();
        /** @var \ORM\Model $new_instance */
        $new_instance = new $class_name();
        $new_instance->bind(array_combine($fields, $values));

        return $new_instance;
    }

    /**
     * Find or create by conditions
     *
     * @param array $fields
     * @param array $values
     * @return null|Model
     */
    public static function findOrCreateBy($fields = [], $values = [])
    {
        $result = self::findOne($fields, $values);

        if(!is_null($result)) {
            return $result;
        }
        // new instance

        $class_name = static::className();
        return $class_name::create(array_combine($fields, $values));
    }

    /**
     * @param string $sortField
     * @param bool $sortAsc
     * @param null $limit
     * @param int $offset
     * @return $this[]
     */
    public static function all($sortField = '', $sortAsc = true, $limit = null, $offset = 0)
	{
        if($sortField == '') {
            $sortField = static::getPrimaryKey();
        }

        return self::getConnection()->getObjects(
            static::getTable(),
            $sortField,
            $sortAsc,
            $limit,
            $offset,
            static::className()
        );
	}

    /**
     * Return n last records
     *
     * @param int $limit
     * @return $this|$this[]
     */
    public static function last($limit = 1)
	{
        $result = self::all(static::getPrimaryKey(), false, $limit);
        if($limit == 1 && is_array($result) && isset($result[0])) {
            return $result[0];
        } else {
            return $result;
        }
	}

    /**
     * Return n first records
     *
     * @param int $limit
     * @return $this|$this[]
     */
    public static function first($limit = 1)
    {
        $result = self::all(static::getPrimaryKey(), true, $limit);
        if($limit == 1 && is_array($result) && isset($result[0])) {
            return $result[0];
        } else {
            return $result;
        }
    }

    /**
     * Returns true if the record is persisted,
     * i.e. it's not a new record and it was not destroyed, otherwise returns false.
     *
     * @return bool
     */
    public function isPersisted()
    {
        return $this->is_persisted;
    }

    /**
     * Returns true if this object hasn't been saved yet – that is,
     * a record for the object doesn't exist in the data store yet; otherwise, returns false.
     *
     * @return bool
     */
    public function isNew()
    {
        return !$this->isPersisted();
    }
}
