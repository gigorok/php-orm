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
trait Validations
{
    /**
     * Validation errors
     *
     * @var Errors
     */
    protected $errors = null;

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
     * Custom validator
     * @param $prop
     * @param array $params
     * @param null $message
     */
    protected function validateCustom($prop, $params = [], $message = null)
    {
        $this->addValidator(new Validator\Custom($this, $prop, $params, $message));
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

    /**
     * Is valid instance?
     *
     * @return bool
     */
    public function isValid()
    {
        $this->validate();

        $this->runValidators();

        return (count($this->errors) === 0);
    }

    /**
     * Is invalid instance?
     *
     * @return bool
     */
    public function isInvalid()
    {
        return !$this->isValid();
    }

    /**
     * Add validation error
     *
     * @param string $error_msg
     * @param string|null $attribute
     */
    public function addError($error_msg, $attribute = null)
    {
        $this->errors->add($attribute, $error_msg);
    }

    /**
     * Return validation errors
     *
     * @return Errors
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get last error
     *
     * @return string
     */
    public function getLastError()
    {
        return end($this->errors->fullMessages());
    }

    /**
     * Validate current instance in child instances
     *
     * @return bool
     */
    protected function validate() { }

    /**
     * Initialize new record
     *
     * @param array $params
     * @return $this
     */
    public function initialize($params = [])
    {
        $this->errors = new Errors();

        return parent::initialize($params);
    }
}
