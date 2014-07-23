<?php
/**
 * php-orm
 *
 * @author Igor Gonchar <gigorok@gmail.com>
 * @copyright 2014 Igor Gonchar
 */
namespace ORM\DBO;

class pgSQL extends \ORM\DBO
{
    public static $engine_name = 'pgsql';

	protected function makeDsn()
	{
		return "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname}";
	}

	public function quote($key)
	{
		return $key;
	}

    /**
     * Insert an object into a table
     * @param $table
     * @param array $obj
     * @param string $primaryKey
     * @param $pdo_types
     * @throws \PDOException
     * @return bool
     */
    function insert($table, array $obj, $primaryKey, $pdo_types)
    {
        $pdo_types = array_values($pdo_types);

        if (!$this->isConnected()) $this->connect();

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

        $sql = 'INSERT INTO ' . $this->quote($tableName) . ' (' . $preparedParamsStr . ') VALUES (' . $preparedValuesStr . ') RETURNING ' . $primaryKey . ';';

        $statement = $this->pdo->prepare($sql);

        for($i = 1; $i <= count($objValues); $i++) {
            $statement->bindParam($i, $objValues[$i-1], $pdo_types[$i-1]);
        }

        $result = $statement->execute();

        //run the update on the object
        if (!$result) {
            $errObj = $statement->errorInfo();

            throw new \PDOException($errObj[2]);
        }

        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        return $row[$primaryKey];
    }
}
