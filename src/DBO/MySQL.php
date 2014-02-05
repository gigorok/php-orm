<?php
/**
 * php-orm
 *
 * @author Igor Gonchar <gigorok@gmail.com>
 * @copyright 2014 Igor Gonchar
 */
namespace ORM\DBO;

/**
 * Class MySQL
 * @package ORM\DBO
 */
class MySQL extends \ORM\DBO
{
    /**
     * @return string
     */
    protected function makeDsn()
	{
		return 'mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->dbname . ';charset=' . $this->charset;
	}
}
