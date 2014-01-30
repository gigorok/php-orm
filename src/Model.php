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
abstract class Model
{
    use Callbackable, Transactionable;

    /**
     * Persisted option
     *
     * @var bool
     */
    public $is_persisted = false;

    /**
     * Attributes
     *
     * @var array
     */
    private $attributes = [];

    /**
     * Schema
     *
     * @var array
     */
    private static $schema = [];

    /**
     * Accessible parameters
     *
     * @var array
     */
    public static $accessible = [];

    /**
     * Validation and database errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Get name of primary key
     *
     * @return string
     */
    static function getPrimaryKey()
    {
        return 'id';
    }

    /**
     * Get table name
     *
     * @return string
     */
    static function getTable()
    {
        return Inflector::tableize(get_called_class());
    }

    /**
     * Add validation error
     *
     * @param string $error_msg
     */
    public function addError($error_msg)
    {
        $this->errors[] = $error_msg;
    }

    /**
     * Return validation errors
     *
     * @return string[]
     */
    public function errorMessages()
    {
        return $this->errors;
    }

    /**
     * Get last error
     *
     * @return string
     */
    function errorMessage()
    {
        return end($this->errors);
    }

    /**
     * Dynamically finders
     *
     * @param $method
     * @param $params
     * @return $this|$this[]|null
     * @throws \Exception
     */
    public static function __callStatic($method, $params)
    {
        if(strpos($method, 'findAllBy') !== false) {
            $fields = explode('And', str_ireplace('findAllBy', '', $method));
            $class = get_called_class();

            foreach($fields as &$field) {
                $field = Inflector::underscore($field);
                if(!in_array($field, static::properties())) {
                    throw new \Exception("Property $field not found for class $class.");
                }
            }

            return static::findAll($fields, $params);
        }

        if(strpos($method, 'findBy') !== false) {
            $fields = explode('And', str_ireplace('findBy', '', $method));
            $class = get_called_class();

            foreach($fields as &$field) {
                $field = Inflector::underscore($field);
                if(!in_array($field, static::properties())) {
                    throw new \Exception("Property $field not found for class $class.");
                }
            }

            return static::findOne($fields, $params);
        }
    }

    /** @var \ORM\DBO */
    static $dbo = null;

    /**
     * Get DBO object
     *
     * @throws \Exception
     * @return \ORM\DBO
     */
    static function getDBO()
    {
        if(!self::$dbo) {
            throw new \Exception('DBO must be configured before');
        }

        return self::$dbo;
    }

    /**
     * Returns an array of all the attributes with their names as keys and the values of the attributes as values.
     *
     * @param bool $reload
     * @return array
     */
    function attributes($reload = false)
    {
        if($reload || empty($this->attributes)) {
            $rs = self::getDBO()->getPDO()->query('SELECT * FROM ' . static::getTable() . ' LIMIT 0');
            for ($i = 0; $i < $rs->columnCount(); $i++) {
                $col = $rs->getColumnMeta($i);
                $this->attributes[$col['name']] = isset($this->$col['name']) ? $this->$col['name'] : null;
            }
        }

        return $this->attributes;
    }

    /**
     * Get schema of instance
     *
     * @return string[]
     */
    static function schema()
    {
        if(empty(self::$schema)) {
            $rs = self::getDBO()->getPDO()->query('SELECT * FROM ' . static::getTable() . ' LIMIT 0');
            for ($i = 0; $i < $rs->columnCount(); $i++) {
                $col = $rs->getColumnMeta($i);
                self::$schema[$col['name']] = $col['native_type'];
            }
        }

        return self::$schema;
    }

    /**
     * Constructor
     *
     * @param array $params
     * @return \ORM\Model
     */
    function __construct($params = [])
    {
        $this->bind($params);
        return $this;
    }

    /**
     * Get foreign key name
     *
     * @return string
     */
    static function getForeignKey()
    {
        return Inflector::underscore(Inflector::singularize(get_called_class()) . '_id');
    }

    /**
     * Save current object to database
     *
     * @param bool $validate
     * @return bool
     */
    public function save($validate = true)
	{
        if(!$this->beforeSave()) { // If a beforeSave callback returns false, all the later callbacks and the associated action are cancelled.
            return false;
        }

		if($validate && !$this->performValidation()) { // call validation with callbacks
			return false;
		}
        try {
            if($this->isNew()) {
                $result = self::getDBO()->insertObject(static::getTable(), $this->attributes(), static::getPrimaryKey());
                $this->is_persisted = true;
            } else {
                $result = self::getDBO()->updateObject(static::getTable(), $this->attributes(), static::getPrimaryKey());
            }
        } catch(\Exception $e) {
            $this->addError($e->getMessage());
            $this->performAfterCallback('afterSave', false);
            return false;
        }
        if(is_numeric($result)) {
            $this->{static::getPrimaryKey()} = $result;
        }
        $this->performAfterCallback('afterSave', true);

		return $result;
	}

    /**
     * Is valid instance?
     *
     * @return bool
     */
    function isValid()
    {
        $this->validate();
        return (bool) !count($this->errors);
    }

    /**
     * Is invalid instance?
     *
     * @return bool
     */
    function isInvalid()
    {
        return !$this->isValid();
    }

    /**
     * Update objects with params
     *
     * @param $params
     * @return bool
     */
    function update($params)
    {
        $this->bind($params);

        if(!$this->beforeUpdate()) {
            return false;
        }

        $result = $this->save();

        $this->performAfterCallback('afterUpdate', $result);

        return $result;
    }

    /**
     * Bind only accessible parameters
     *
     * @param array $array
     * @return $this
     */
    function bind($array)
    {
        $c = get_called_class();
        foreach ($array as $key => $value) {
            if(in_array($key, $c::$accessible)) { // bind only accessible parameters
                if(is_array($value) || is_object($value)) {
                    $this->bind($value);
                } else {
                    $this->$key = $value;
                }
            }
        }

        return $this;
    }

    /**
     * Create object with $params
     *
     * @param $params
     * @return bool
     */
    function create($params)
    {
        $this->bind($params);

        if(!$this->beforeCreate()) {
            return false;
        }

        $result = $this->save();

        $this->performAfterCallback('afterCreate', $result);

        return $result;
    }

    /**
     * Returns true if the record is persisted, i.e. it's not a new record and it was not destroyed, otherwise returns false.
     *
     * @return bool
     */
    function isPersisted()
    {
        return $this->is_persisted;
    }

    /**
     * Returns true if this object hasn't been saved yet – that is, a record for the object doesn't exist in the data store yet; otherwise, returns false.
     *
     * @return bool
     */
    function isNew()
    {
        return !$this->isPersisted();
    }

    /**
     * Destroy object from database
     *
     * @return bool
     */
    function destroy()
    {
        if(!$this->beforeDestroy()) {
            return false;
        }

        if($this->isNew()) {
            return false;
        } else {
            $result = self::getDBO()->deleteObject(static::getTable(), $this->{static::getPrimaryKey()}, static::getPrimaryKey());
            if($result) {
                $this->is_persisted = false;
            }
        }

        $this->performAfterCallback('afterDestroy', $result);

        return $result;
    }

    /**
     * Validate current instance in child instances
     *
     * @return bool
     */
    abstract protected function validate();

    /**
     * @var bool
     */
    private $no_callbacks = false;

    /**
     * If an after callback returns false, all the later callbacks are cancelled.
     *
     * @param $callback string
     * @param $payload bool
     */
    private function performAfterCallback($callback, $payload)
    {
        if(!$this->no_callbacks) {
            if(!$this->$callback($payload)) {
                $this->no_callbacks = true;
            }
        }
    }

    /**
     * Perform validation
     *
     * @return bool
     */
    private function performValidation()
    {
        if(!$this->beforeValidation()) { // If the returning value of a beforeValidation callback can be evaluated to false, the process will be aborted and Model#save will return false.
            return false;
        }

        $isValid = $this->isValid();

        $this->performAfterCallback('afterValidation', $isValid);

        return $isValid;
    }

    /**
     * Find record by primary key
     *
     * @param $value
     * @return $this|bool
     */
    static function find($value)
    {
        $result = self::getDBO()->getObject(static::getTable(), $value, get_called_class(), static::getPrimaryKey());
        if($result) {
            $result->is_persisted = true;
        }
        return $result;
    }

    /**
     * Count records in table by conditions
     *
     * @param array $fields
     * @param array $values
     * @return mixed
     */
    static function count($fields = [], $values = [])
    {
        return self::getDBO()->numObjects(static::getTable(), $fields, $values);
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
    static function findAll($fields = [], $values = [], $sortField = '', $sortAsc = true, $limit = null, $offset = 0)
    {
        if($sortField == '') {
            $sortField = static::getPrimaryKey();
        }

        if(count($fields) == 0) {
            return self::all($sortField, $sortAsc, $limit, $offset);
        }

        $result = self::getDBO()->findObjects(static::getTable(), $fields, $values, $sortField, $sortAsc, $limit, $offset, get_called_class());
        $newResult = [];
        foreach($result as $c) {
            $c->is_persisted = true;
            $newResult[] = $c;
        }

        return $newResult;
    }

    /**
     * Search entries by condition string
     *
     * @example User::where("email = ? AND (is_subscribed = ? OR is_active = ?)", ['email@example.com', 1, 0])
     * @param string $whereStr
     * @param array $values
     * @return $this[]
     */
    static function where($whereStr, $values = [])
    {
        $query = "SELECT * FROM " . static::getTable() . " WHERE " . $whereStr;

        $result = self::getDBO()->getObjectsQuery($query, $values, get_called_class());
        $newResult = [];
        foreach($result as $c) {
            $c->is_persisted = true;
            $newResult[] = $c;
        }

        return $newResult;
    }

    /**
     * Find one row by conditions
     *
     * @param array $fields
     * @param array $values
     * @return bool|$this
     */
    static function findOne($fields = [], $values = [])
    {
        if(count($fields) > 0) {
            $result = self::getDBO()->findObject(static::getTable(), $fields, $values, get_called_class());
            if($result) {
                $result->is_persisted = true;
            }
            return $result;
        }

        return false;
    }

    /**
     * @param string $sortField
     * @param bool $sortAsc
     * @param null $limit
     * @param int $offset
     * @return mixed
     */
    static function all($sortField = '', $sortAsc = true, $limit = null, $offset = 0)
	{
        if($sortField == '') {
            $sortField = static::getPrimaryKey();
        }

        $result = self::getDBO()->getObjects(static::getTable(), $sortField, $sortAsc, $limit, $offset, get_called_class());
        $newResult = [];
        foreach($result as $c) {
            $c->is_persisted = true;
            $newResult[] = $c;
        }

        return $newResult;
	}

    /**
     * @param int $limit
     * @return $this|array
     */
    static function last($limit = 1)
	{
        $result = self::all(static::getPrimaryKey(), false, $limit);
        if($limit == 1 && is_array($result) && isset($result[0])) {
            return $result[0];
        } else {
            return $result;
        }
	}

    /**
     * This will return the first record
     *
     * @param int $limit
     * @return $this|$this[]
     */
    static function first($limit = 1)
    {
        $result = self::all(static::getPrimaryKey(), true, $limit);
        if($limit == 1 && is_array($result) && isset($result[0])) {
            return $result[0];
        } else {
            return $result;
        }
    }

    /**
     * @param $className
     * @param string $tableName
     * @param string $foreignKey
     * @param string $foreignKeyRelated
     * @return HasAndBelongsToMany
     */
    protected function hasAndBelongsToMany($className, $tableName = null, $foreignKey = null, $foreignKeyRelated = null)
    {
        if(is_null($tableName)) {
            $table1 = Inflector::tableize(get_class($this));
            $table2 = Inflector::tableize($className);
            $tables = [$table1, $table2];
            asort($tables);
            $tableName = implode('_', $tables);
        }

        return new HasAndBelongsToMany($this, $className, $tableName, $foreignKey, $foreignKeyRelated);
    }

    /**
     * @param \ORM|Model $className
     * @param string $sortField
     * @param bool $sortAsc
     * @return \ORM|Model[]
     */
    protected function hasMany($className, $sortField = '', $sortAsc = true)
    {
        if($sortField == '') {
            $sortField = $className::getPrimaryKey();
        }

        $foreignKey = static::getForeignKey();
        return $className::findAll([$foreignKey], [$this->{static::getPrimaryKey()}], $sortField, $sortAsc);
    }

    /**
     * @param $className
     * @return mixed
     */
    protected function hasOne($className)
    {
        $foreignKey = static::getForeignKey();
        return $className::findOne([$foreignKey], [$this->{static::getPrimaryKey()}]);
    }

    /**
     * @param $className
     * @return mixed
     */
    protected function belongsTo($className)
    {
        $foreignKey = strtolower($className) . '_id';
        return $className::find(intval($this->$foreignKey));
    }

    /**
     * @return array
     */
    static function properties()
    {
        return array_keys(self::schema());
    }
}
