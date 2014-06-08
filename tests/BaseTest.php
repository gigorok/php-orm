<?php

/**
 * php-orm
 *
 * @author Igor Gonchar <gigorok@gmail.com>
 * @copyright 2014 Igor Gonchar
 */

class BaseTest extends \PHPUnit_Extensions_Database_TestCase
{
    function setUp()
    {
        $connection = new \ORM\Connection('mysql', DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS);
        \ORM\Model::establishConnection($connection);

        parent::setUp();
    }

    protected function getDataSet()
    {
        return new \PHPUnit_Extensions_Database_DataSet_YamlDataSet(
            implode(DIRECTORY_SEPARATOR, [__DIR__, "fixtures", "php_orm_test.yml"])
        );
    }

    /**
     * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        return $this->createDefaultDBConnection(new \PDO('mysql:dbname=' . DB_NAME . ';host=' . DB_HOST . ';port=' . DB_PORT, DB_USER, DB_PASS));
    }

}