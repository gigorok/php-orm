<?php
/**
* php-orm
*
* @author Igor Gonchar <gigorok@gmail.com>
* @copyright 2013 Igor Gonchar
*/

namespace ORM;

/**
 * Class Connection
 * @package ORM
 */
class Connection
{
    protected $engine = 'mysql';
    protected $hostname = 'localhost';
    protected $port = 3306;
    protected $database = '';
    protected $username = 'root';
    protected $password = '';

    /**
     * @param $engine
     * @param $hostname
     * @param $port
     * @param $database
     * @param $username
     * @param $password
     */
    public function __construct($engine, $hostname, $port, $database, $username, $password)
    {
        $this->engine = $engine;
        $this->hostname = $hostname;
        $this->port = $port;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return \ORM\DBO
     * @throws \Exception
     */
    public function getDatabaseObject()
    {
        // get engine class
        switch($this->engine) {
            case \ORM\DBO\MySQL::$engine_name:
                $engine_class = '\ORM\DBO\MySQL';
                break;
            case \ORM\DBO\pgSQL::$engine_name:
                $engine_class = '\ORM\DBO\pgSQL';
                break;
            default:
                throw new \Exception('Database Engine is not defined properly');
        }

        return new $engine_class($this->hostname, $this->port, $this->database, $this->username, $this->password);
    }

}