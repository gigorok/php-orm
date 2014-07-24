<?php
/**
* php-orm
*
* @author Igor Gonchar <gigorok@gmail.com>
* @copyright 2013 Igor Gonchar
*/

namespace ORM\Validator;

use ORM\Validator;

/**
 * Class Length
 * @package ORM\Validator
 */
class Length extends Validator
{
    protected $record = null;

    /**
     * @param \ORM\Model $record
     * @return bool|mixed
     * @throws \ORM\Exception
     */
    public function validate(\ORM\Model $record)
    {
        $this->record = $record;

        if(isset($this->params['minimum']) && is_numeric($this->params['minimum'])) {
            return !(strlen($record->{$this->field}) < $this->params['minimum']);
        }

        if(isset($this->params['maximum']) && is_numeric($this->params['maximum'])) {
            return !(strlen($record->{$this->field}) > $this->params['maximum']);
        }

        if(isset($this->params['in']) && is_array($this->params['in'])) {
            return !(strlen($record->{$this->field}) < $this->params['in'][0]) && !(strlen($record->{$this->field}) > $this->params['in'][1]);
        }

        if(isset($this->params['is']) && is_numeric($this->params['is'])) {
            return (strlen($record->{$this->field}) === $this->params['is']);
        }

        throw new \ORM\Exception('Invalid length constraint options');
    }

    /**
     * @return null|string
     */
    public function getMessage()
    {
        if(is_null($this->message)) {
            if(isset($this->params['minimum']) && is_numeric($this->params['minimum'])) {
                return 'is too short';
            }

            if(isset($this->params['maximum']) && is_numeric($this->params['maximum'])) {
                return 'is too long';
            }

            if(isset($this->params['in']) && is_array($this->params['in'])) {
                return (strlen($this->record->{$this->field}) < $this->params['in'][0] ? 'is too short' : 'is too long');
            }

            if(isset($this->params['is']) && is_numeric($this->params['is'])) {
                return 'is the wrong length';
            }

            return 'is invalid';

        }

        return $this->message;
    }

}