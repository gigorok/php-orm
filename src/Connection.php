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
    /**
     * @var string
     */
    protected $engine = 'mysql';

    /**
     * @var string
     */
    protected $hostname = 'localhost';

    /**
     * @var int
     */
    protected $port = 3306;

    /**
     * @var string
     */
    protected $database = '';

    /**
     * @var string
     */
    protected $username = 'root';

    /**
     * @var string
     */
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
     * @throws \ORM\Exception
     */
    public function getInstance()
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
                throw new \ORM\Exception('Database Engine is not supporting');
        }

        return new $engine_class($this->hostname, $this->port, $this->database, $this->username, $this->password);
    }

}