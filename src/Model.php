<?php
/**
* php-orm
*
* @author Igor Gonchar <gigorok@gmail.com>
* @copyright 2013 Igor Gonchar
*/

namespace ORM;

class Model extends Base
{
    /**
     * Add ValidationsCallbacks module supporting
     */
    use ValidationsCallbacks;

    /**
     * Add Associations module supporting
     */
    use Associations;

    /**
     * Add Transactions module supporting
     */
    use Transactions;

    /**
     * Add Schemas module supporting
     */
    use Schemas;

    /**
     * Add Dirty module supporting
     */
//    use Dirty;
}
