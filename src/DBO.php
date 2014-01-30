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
    protected $host = 'localhost';
    protected $port = '3306';
    protected $dbname = '';

	protected $username = 'root';
	protected $password = '';
	protected $charset = 'utf8';

    /** @var \PDO */
	protected $pdo = null;

    /**
     * @return \PDO
     */
    function getPDO()
    {
        if (!$this->isConnected()) $this->connect();

        return $this->pdo;
    }

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
	* @return bool we are connected
	*/
	function isConnected()
	{
		return ($this->pdo !== null);
	}

    /**
     * Run a statement on the database
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
	* Allow a value to be escaped, ready for insertion as a mysql parameter
	* Note: for usage as a value (rather than prepared statements), you MUST manually quote around
	* @param {string} value
	* @return {string} mysql safe value
	*/
	function escape($str) 
	{
		if (!$this->isConnected()) $this->connect();

		return $this->unquote_outer($this->pdo->quote((string)$str));
	}

	/**
	* Get a quick number of objects in a table
	* @param {string} table name
	* @return {integer} number of objects
	*/
	function numObjects($table, $field = [], $value = [])
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
	* Note: the id of the object is assumed to be in
	* @param {string} name of table
	* @param {object} new values for the object (can use an assoc array)
	* @return {boolean} object updated sucessfully
	*/
	function updateObject($table, array $obj, $primaryKey = 'id')
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

        //run the update on the object
        return $statement->execute($paramValues);
	}

    /**
     * Insert an object into a table
     * @param $table
     * @param $obj
     * @return bool
     * @throws \PDOException
     */
    function insertObject($table, array $obj, $primaryKey = 'id')
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

        for($i=1; $i<=count($objValues); $i++) {
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

	public function quote($key)
	{
		return '`' . $key . '`';
	}

	/**
	* Get a filtered list of objects from the database
	* @param {string} table name
	* @param {string} prepared query (optional)
	* @param {array} values to use in query (optional)
	* @param {int} offset (optional)
	* @param {int} number of objects to get (optional)
	* @param {string} return objects class (optional)
	* @return {array} list of objects
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
			while ($newObj = $statement->fetchObject($class)) {
				$results[] = $newObj;
			}
		}

		return $results;
	}

	function getObjectsQuery($query = null, $values = [], $class = 'stdClass')
	{
		if (!$this->isConnected()) $this->connect();

		$statement = $this->pdo->prepare($query);
		$statement->execute((array)$values);

		$results = [];

		if (is_object($statement)) {
			while ($newObj = $statement->fetchObject($class)) {
				$results[] = $newObj;
			}
		}

		return $results;
	}

    /**
     * Get a single object from the database
     * @param $table
     * @param $id
     * @param string $class
     * @return mixed
     */
    function getObject($table, $id, $class = 'stdClass', $primaryKey = 'id')
	{
        if (!$this->isConnected()) $this->connect();

        $statement = $this->pdo->prepare('SELECT * FROM ' . $this->quote($table) . ' WHERE ' . $this->quote($primaryKey) . ' = :id LIMIT 1;');
        $id = intval($id);
        $statement->bindParam(':id', $id);
        $statement->execute();
        return $statement->fetchObject($class);
	}

	function getObjectQuery($query = null, $values = [], $class = 'stdClass')
	{
		if (!$this->isConnected()) $this->connect();

		$statement = $this->pdo->prepare($query);
		if(!$statement->execute((array)$values)) {
            $errObj = $statement->errorInfo();

            //return false;
            throw new \PDOException($errObj[2]);
        }

		$resultObj = $statement->fetchObject($class);

		if ($resultObj === false) {
			return null;
		} else {
			return $resultObj;
		}
	}

	function findObjects($table, $field, $value, $sortField = 'id', $sortAsc = true, $limit = null, $offset = 0, $class = 'stdClass')
	{
		$table = $this->escape($table);
		if(is_array($field)) {
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
		} else {
			$field = $this->escape($field);
            if(is_null($value)) {
                $where = $this->quote($field) . ' IS NULL';
                unset($value);
            } else {
			    $where = $this->quote($field) . ' = ?';
            }
		}
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
	* @param {string} table name
	* @param {int} object id
	* @return {boolean} success
	*/
	function deleteObject($tableName, $id, $primaryKey = 'id')
	{
        $tableName = $this->escape($tableName);
        $field = $this->escape($primaryKey);
        $value = $id;

        return $this->runQuery('DELETE FROM '.$this->quote($tableName).' WHERE '.$this->quote($field).' = ?;', array($value));
    }

}
