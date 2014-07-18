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
    static $supported_validators = [
        'exclusion', 'inclusion', 'length',
        'format', 'numericality', 'presence',
        'uniqueness', 'custom'
    ];

    /**
     * Validation errors
     *
     * @var Errors
     */
    protected $errors = null;

    /**
     * Validation container
     * Instead of using validate method
     * @example
     *      [
     *       'type_id' => ['exclusion', ['in' => [1, 3]]],
     *       'type_id' => ['inclusion', ['in' => [1, 3]]],
     *       'title' => ['format', ['with' => '/\A[a-zA-Z]+\z/'], 'only allows letters'],
     *       'title' => ['length', ['minimum' => 3, 'maximum' => 10, 'is' => 5, 'in' => [3, 7]]],
     *       'type_id' => ['numericality', [], 'should be numeric'],
     *       'title' => ['presence', []],
     *       'title' => ['uniqueness'],
     *       'title' => ['custom', ['closure' => function($title) { return strlen($title) < 2; }], 'is invalid']
     *       ]
     *
     * @var array
     */
    static $validates = [];

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
     * @throws \ORM\Exception
     * @return bool
     */
    public function isValid()
    {
        $this->validate(); # for backward compatibility

        foreach(static::$validates as $field => $rule) {
            $validator_name = $rule[0];
            $validator_params = isset($rule[1]) ? $rule[1] : [];
            $message = isset($rule[2]) ? $rule[2] : null;

            if(!in_array($validator_name, self::$supported_validators)) {
                throw new \ORM\Exception('Invalid validator');
            }

            $class_name = '\\ORM\\Validator\\' . ucfirst($validator_name);

            $this->validateWith(new $class_name($this, $field, $validator_params, $message));
        }

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
    public function addError($error_msg, $attribute = 'base')
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
