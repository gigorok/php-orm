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
        \ORM\Model::$dbo = new \ORM\DBO\MySQL(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS);

        User::$accessible = ['id', 'email', 'first_name', 'last_name', 'role_id'];

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