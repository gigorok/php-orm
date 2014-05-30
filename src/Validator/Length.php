<?php
/**
* php-orm
*
* @author Igor Gonchar <gigorok@gmail.com>
* @copyright 2013 Igor Gonchar
*/

namespace ORM\Validator;

use ORM\Validator;

class Length extends Validator
{
    public function validate()
    {
        if(isset($this->params['minimum']) && is_numeric($this->params['minimum'])) {
            return !(strlen($this->value) < $this->params['minimum']);
        }

        if(isset($this->params['maximum']) && is_numeric($this->params['maximum'])) {
            return !(strlen($this->value) > $this->params['maximum']);
        }

        if(isset($this->params['in']) && is_array($this->params['in'])) {
            return !(strlen($this->value) < $this->params['in'][0]) && !(strlen($this->value) > $this->params['in'][1]);
        }

        if(isset($this->params['is']) && is_numeric($this->params['is'])) {
            return (strlen($this->value) === $this->params['is']);
        }

        throw new \Exception('Invalid length constraint options');
    }

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
                return (strlen($this->value) < $this->params['in'][0] ? 'is too short' : 'is too long');
            }

            if(isset($this->params['is']) && is_numeric($this->params['is'])) {
                return 'is the wrong length';
            }

            return 'is invalid';

        }

        return $this->message;
    }

}