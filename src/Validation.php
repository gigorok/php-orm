<?php
/**
* php-orm
*
* @author Igor Gonchar <gigorok@gmail.com>
* @copyright 2013 Igor Gonchar
*/

namespace ORM;

/**
 * Class Validation
 * @package ORM
 */
trait Validation
{
    /**
     * Validators container
     * @var Validator[]
     */
    private $validators = [];

    /**
     * Passes the record to a separate class for validation
     * @param Validator $validator
     */
    public function validateWith(Validator $validator)
    {
        $this->addValidator($validator);
    }

    /**
     * Presence validator
     * @param $prop
     * @param array $params
     * @param string|null $message
     */
    protected function validatePresence($prop, $params = [], $message = null)
    {
        $this->addValidator(new Validator\Presence($this, $prop, $params, $message));
    }

    /**
     * Format validator
     * @param $prop
     * @param array $params
     * @param string|null $message
     */
    protected function validateFormat($prop, $params, $message = null)
    {
        $this->addValidator(new Validator\Format($this, $prop, $params, $message));
    }

    /**
     * Inclusion validator
     * @param $prop
     * @param $params
     * @param null $message
     */
    protected function validateInclusion($prop, $params, $message = null)
    {
        $this->addValidator(new Validator\Inclusion($this, $prop, $params, $message));
    }

    /**
     * Exclusion validator
     * @param $prop
     * @param $params
     * @param null $message
     */
    protected function validateExclusion($prop, $params, $message = null)
    {
        $this->addValidator(new Validator\Exclusion($this, $prop, $params, $message));
    }

    /**
     * Numericality validator
     * @param $prop
     * @param array $params
     * @param null $message
     */
    protected function validateNumericality($prop, $params = [], $message = null)
    {
        $this->addValidator(new Validator\Numericality($this, $prop, $params, $message));
    }

    /**
     * Length validator
     * @param $prop
     * @param $params
     * @param null $message
     */
    protected function validateLength($prop, $params, $message = null)
    {
        $this->addValidator(new Validator\Length($this, $prop, $params, $message));
    }

    /**
     * Uniqueness validator
     * @param $prop
     * @param $params
     * @param null $message
     */
    protected function validateUniqueness($prop, $params = [], $message = null)
    {
        $this->addValidator(new Validator\Uniqueness($this, $prop, $params, $message));
    }

    /**
     * Run validators
     */
    protected function runValidators()
    {
        foreach($this->validators as $validator) {
            if($validator->validate() === false) {
                /** @var $this Model */
                $this->addError($validator->getMessage(), $validator->getAttribute());
            }
        }
    }

    /**
     * Add validator to collection
     * @param Validator $validator
     */
    private function addValidator(Validator $validator)
    {
        $this->validators[] = $validator;
    }
}
