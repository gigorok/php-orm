<?php
/**
 * php-orm
 *
 * @author Igor Gonchar <gigorok@gmail.com>
 * @copyright 2014 Igor Gonchar
 */
namespace ORM;

/**
 * Class DBO
 * @package ORM
 */
abstract class DBO
{
    /**
     * Hostname
     *
     * @var string
     */
    protected $host = 'localhost';

    /**
     * Port
     *
     * @var string
     */
    protected $port = '3306';

    /**
     * Database name
     *
     * @var string
     */
    protected $dbname = '';

    /**
     * Username
     *
     * @var string
     */
    protected $username = 'root';

    /**
     * Password
     *
     * @var string
     */
    protected $password = '';

    /**
     * Charset
     *
     * @var string
     */
    protected $charset = 'utf8';

    /** @var \PDO */
	protected $pdo = null;

    /**
     * Get PHP PDO instance
     *
     * @return \PDO
     */
    function getPDO()
    {
        if (!$this->isConnected()) $this->connect();

        return $this->pdo;
    }

    /**
     * Constructor
     *
     * @param string $host
     * @param string $port
     * @param string $dbname
     * @param string $username
     * @param string $password
     * @param string $charset
     */
    public function __construct($host = null, $port = null, $dbname = null, $username = null, $password = null, $charset = null)
    {
        if (isset($host)) $this->host = $host;
        if (isset($port)) $this->port = $port;
        if (isset($dbname)) $this->dbname = $dbname;

        if (isset($username)) $this->username = $username;
        if (isset($password)) $this->password = $password;
        if (isset($charset))  $this->charset = $charset;
    }

	/**
	* Connect to the database
	* Call whenever a connection is needed to be made
	*/
	function connect() 
	{
		$dsn = $this->makeDsn();

		try {
			$this->pdo = new \PDO($dsn, $this->username, $this->password);
			$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		} catch (\PDOException $e) {
			die("Could not connect to database!" . $e->getMessage());
		}
	}

    /**
     * @return string
     */
    abstract protected function makeDsn();

    /**
     * Remove outer quotes from a string
     * @param $str
     * @return string
     */
    static function unquote_outer($str)
	{
		$len = strlen($str);

		if ($len>1) {
			if ($str[0] == "'" && $str[$len-1] == "'") {
				return substr($str, 1, -1);
			} else if ($str[0] == "'") {
				return substr($str, 1);
			} else if ($str[$len-1] == "'") {
				return substr($str, 0, -1);
			}
		} else if ($len>0) {
			if ($str[0] == "'") {
				return '';
			}
		}

		return $str;
	}

	/**
	 * Are we currently connected to the database
     *
	 * @return bool we are connected
	 */
	function isConnected()
	{
		return ($this->pdo !== null);
	}

    /**
     * Run a statement on the database
     *
     * @param $query
     * @param array $values
     * @return mixed
     */
    function runQuery($query, $values = [])
	{
		if (!$this->isConnected()) $this->connect();

		$statement = $this->pdo->prepare($query);

		return $statement->execute((array)$values); //this array cast allows single values to be used as the parameter
	}

    /**
     * Allow a value to be escaped
     *
     * @param $str
     * @return string
     */
    function escape($str)
	{
		if (!$this->isConnected()) $this->connect();

		return $this->unquote_outer($this->pdo->quote((string)$str));
	}

    /**
     * Get a quick number of objects in a table
     *
     * @param $table
     * @param array $field
     * @param array $value
     * @return string
     */
    function count($table, $field = [], $value = [])
	{
		if (!$this->isConnected()) $this->connect();

		$tableName = $this->escape($table);

        $where = [];
        foreach ($field as $key => $v) {
            $field[$key] = $this->escape($v);
            if(is_null($value[$key])) {
                $where[] = $this->quote($field[$key]) . ' IS NULL';
                unset($value[$key]);
            } else {
                $where[] = $this->quote($field[$key]) . ' = ?';
            }
        }
        $whereStr = (count($where) > 0) ? " WHERE " . implode(' AND ', $where) : "";

		$statement = $this->pdo->prepare('SELECT COUNT(*) as ' . $this->quote('num') . ' FROM ' . $this->quote($tableName) . $whereStr);

		$statement->execute($value);

		return $statement->fetchColumn();
	}

    /**
     * Update an object in a table with values given
     *
     * @param $table
     * @param array $obj
     * @param string $primaryKey
     * @return bool
     */
    function update($table, array $obj, $primaryKey)
	{
		if (!$this->isConnected()) $this->connect();

        $tableName = $this->escape($table);

		//we cannot update an object without an id specified so quit
		if (!isset($obj[$primaryKey])) {
			return false;
		}

		//get the objects id from the provided object and knock it off from the object so we don't try to update it
		$objId = $obj[$primaryKey];
		unset($obj[$primaryKey]);

		//TODO: validate given object parameters with that of the table (this validates parameters names)

		//formulate an update statement based on the object parameters
		$objParams = array_keys($obj);

		$preparedParamArr = [];
		foreach ($objParams as $objParam) {
			$preparedParamArr[] = $this->quote($this->escape($objParam)) . '=?';
		}

		$preparedParamStr = implode(',', $preparedParamArr);

		$statement = $this->pdo->prepare('UPDATE ' . $this->quote($tableName) . ' SET ' . $preparedParamStr . ' WHERE ' . $this->quote($primaryKey) . '=?;');

		//merge the parameters and values
		$paramValues = array_merge(array_values($obj), (array)$objId);

        for($i = 1; $i <= count($paramValues); $i++) {
            $statement->bindParam($i, $paramValues[$i-1]);
        }

        //run the update on the object
        return $statement->execute();
	}

    /**
     * Insert an object into a table
     *
     * @param $table
     * @param array $obj
     * @param string $primaryKey
     * @throws \PDOException
     * @return bool|string
     */
    function insert($table, array $obj, $primaryKey)
	{
		if (!$this->isConnected()) {
            $this->connect();
        }

        $tableName = $this->escape($table);

        if(is_null($obj[$primaryKey])) { // default primary key value handled by database
            unset($obj[$primaryKey]);
        }

		if (count($obj) < 1) {
			return true;
		}

		//formulate an update statement based on the object parameters
		$objValues = array_values($obj);

		$preparedParamsArr = [];
		foreach ($obj as $key => $value) {
			$preparedParamsArr[] = $this->quote($this->escape($key));
		}

		$preparedParamsStr = implode(', ', $preparedParamsArr);
		$preparedValuesStr = implode(', ', array_fill(0, count($objValues), '?'));

        $sql = 'INSERT INTO ' . $this->quote($tableName) . ' (' . $preparedParamsStr . ') VALUES (' . $preparedValuesStr . ');';

        $statement = $this->pdo->prepare($sql);

        for($i = 1; $i <= count($objValues); $i++) {
            $statement->bindParam($i, $objValues[$i-1]);
        }

        $result = $statement->execute();

		//run the update on the object
		if (!$result) {
			$errObj = $statement->errorInfo();

			throw new \PDOException($errObj[2]);
		}

		return $this->pdo->lastInsertId();
	}

    /**
     * @param $key
     * @return string
     */
    public function quote($key)
	{
		return '`' . $key . '`';
	}

    /**
     * Get a filtered list of objects from the database
     *
     * @param $tableName
     * @param string $sortField
     * @param bool $sortAsc
     * @param null $numRecords
     * @param int $offset
     * @param string $class
     * @return array
     */
    function getObjects($tableName, $sortField = 'id', $sortAsc = true, $numRecords = null, $offset = 0, $class = 'stdClass')
	{
		if (!$this->isConnected()) $this->connect();

		$sortStr = '';
		if (!$sortAsc) {
			$sortStr = 'DESC';
		}

		//we should escape all of the params that we need to
		$tableName = $this->escape($tableName);
		$sortField = $this->escape($sortField);

		if ($numRecords === null) {
			//get all (no limit)
			$statement = $this->pdo->query('SELECT * FROM '.$this->quote($tableName).' ORDER BY '.$this->quote($sortField).' '.$sortStr.';');
		} else {
			//get a limited range of objects
			$statement = $this->pdo->query('SELECT * FROM '.$this->quote($tableName).' ORDER BY '.$this->quote($sortField).' '.$sortStr.' LIMIT '.$numRecords.' OFFSET '.$offset.';');
		}

		$results = [];

		if (is_object($statement)) {
			while ($params = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $newObj = new $class($params);
                $newObj->is_persisted = true;
				$results[] = $newObj;
			}
		}

		return $results;
	}

    /**
     * @param null $query
     * @param array $values
     * @param string $class
     * @return array
     */
    function getObjectsQuery($query = null, $values = [], $class = 'stdClass')
	{
		if (!$this->isConnected()) $this->connect();

		$statement = $this->pdo->prepare($query);
		$statement->execute((array)$values);

		$results = [];

		if (is_object($statement)) {
            while ($params = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $newObj = new $class($params);
                $newObj->is_persisted = true;
                $results[] = $newObj;
            }
		}

		return $results;
	}

    /**
     * Get a single object from the database
     *
     * @param $table
     * @param $id
     * @param string $class
     * @param string $primaryKey
     * @return mixed
     */
    function getObject($table, $id, $class = 'stdClass', $primaryKey = 'id')
	{
        if (!$this->isConnected()) $this->connect();

        $statement = $this->pdo->prepare('SELECT * FROM ' . $this->quote($table) . ' WHERE ' . $this->quote($primaryKey) . ' = :id LIMIT 1;');
        $id = intval($id);
        $statement->bindParam(':id', $id);
        $statement->execute();

        $params = $statement->fetch(\PDO::FETCH_ASSOC);

        if($params === false) {
            return null;
        }

        return new $class($params);
	}

    /**
     * @param null $query
     * @param array $values
     * @param string $class
     * @return mixed|null
     * @throws \PDOException
     */
    function getObjectQuery($query = null, $values = [], $class = 'stdClass')
	{
		if (!$this->isConnected()) $this->connect();

		$statement = $this->pdo->prepare($query);
		if(!$statement->execute((array)$values)) {
            $errObj = $statement->errorInfo();

            //return false;
            throw new \PDOException($errObj[2]);
        }

        $params = $statement->fetch(\PDO::FETCH_ASSOC);

        if($params === false) {
            return null;
        }

		$resultObj = new $class($params);
        $resultObj->is_persisted = true;
        return $resultObj;
	}

    /**
     * @param $table
     * @param $field
     * @param $value
     * @param string $sortField
     * @param bool $sortAsc
     * @param null $limit
     * @param int $offset
     * @param string $class
     * @return array
     */
    function findObjects($table, $field, $value, $sortField = 'id', $sortAsc = true, $limit = null, $offset = 0, $class = 'stdClass')
	{
		$table = $this->escape($table);
        $where = [];
        foreach ($field as $key => $v) {
            $field[$key] = $this->escape($v);
            if(is_null($value[$key])) {
                $where[] = $this->quote($field[$key]) . ' IS NULL';
                unset($value[$key]);
            } else {
                $where[] = $this->quote($field[$key]) . ' = ?';
            }
        }
        $where = implode(' AND ', $where);
		$sortField = $this->escape($sortField);
		$sql = 'SELECT * FROM ' . $this->quote($table) . ' WHERE '.$where.' ORDER BY '.$this->quote($sortField).' ' . ($sortAsc ? '' : 'DESC');
        if(!is_null($limit)) {
            $sql .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
		if(!is_array($value)) {
			$value = array($value);
		}
		return $this->getObjectsQuery($sql, $value, $class);
	}

    /**
     * @param $table
     * @param $field
     * @param $value
     * @param string $class
     * @return mixed|null
     */
    function findObject($table, $field, $value, $class = 'stdClass')
	{
        $where = [];
        foreach ($field as $key => $v) {
            $field[$key] = $this->escape($v);
            if(is_null($value[$key])) {
                $where[] = $this->quote($field[$key]) . ' IS NULL';
                unset($value[$key]);
            } else {
                $where[] = $this->quote($field[$key]) . ' = ?';
            }
        }
        $where = implode(' AND ', $where);

		$sql = 'SELECT * FROM ' . $this->quote($table) . ' WHERE '.$where.' LIMIT 1;';

		return $this->getObjectQuery($sql, $value, $class);
	}

    /**
     * Delete an object from the database
     *
     * @param $tableName
     * @param $id
     * @param string $primaryKey
     * @return mixed
     */
    function deleteObject($tableName, $id, $primaryKey = 'id')
	{
        $tableName = $this->escape($tableName);
        $field = $this->escape($primaryKey);
        $value = $id;

        return $this->runQuery('DELETE FROM '.$this->quote($tableName).' WHERE '.$this->quote($field).' = ?;', array($value));
    }

    /**
     * Delete a list of objects from the database
     *
     * @param string $table
     * @param $field
     * @param $value
     * @return mixed
     */
    function deleteObjects($table, $field, $value)
    {
        $where = [];
        foreach ($field as $key => $v) {
            $field[$key] = $this->escape($v);
            if(is_null($value[$key])) {
                $where[] = $this->quote($field[$key]) . ' IS NULL';
                unset($value[$key]);
            } else {
                $where[] = $this->quote($field[$key]) . ' = ?';
            }
        }
        $whereStr = implode(' AND ', $where);

        $sql = 'DELETE FROM ' . $this->quote($table) . ' WHERE ' . $whereStr;

        return $this->runQuery($sql, $value);
    }

}
