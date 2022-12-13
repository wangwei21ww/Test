<?php
/**
 * This file is part of the Less
 * @author Stephen Lee <stephen.lee@lesscloud.com>
 * @link https://lesscloud.com/
 * @license All copyright and license information, please visit the web page
 *           https://lesscloud.com/license
 * @version $Id: Validator.php 29 2011-10-04 05:22:03Z Stephen.Lee $
 */

namespace Less\Validator;

// use Less\Translation\Translator;

abstract class BaseValidator
{
    /**
     * When the validation is invalid,
     * that will be return the error message.
     * @var string
     */
    public $message;

    /**
     * Whether allow the attribute is empty or not.
     * @var boolean
     */
    public $allowEmpty = false;

    /**
     * Specified some scenario do not use the validation rules.
     * The isValidate is a callable parameter, that must to return a boolean,
     * `true` indicate to validate with the rules of the model, `false` indicate do not need to validate.
     * The isValidate defaults to true.
     * @var mixed
     */
    public $isValidate = true;

    abstract public function validateAttribute($object, $attribute);

    /**
     * Adding an error message to specify object
     * @param object $object
     * @param string $attribute
     * @param string message
     * @param array $placeholders
     */
    public function addError($object, $attribute, $message, array $placeholders = [])
    {
        if (is_callable($this->isValidate) and call_user_func($this->isValidate) === false) {
            return;
        }

        $labels = [];
        if (method_exists($object, 'attributeLabels')) {
            $labels = $object->attributeLabels();
        }
        if (isset($labels[$attribute])) {
            $placeholders['{attribute}'] = $labels[$attribute];
        } else {
            $placeholders['{attribute}'] = $attribute;
        }

        \Less::getApp()->addError($message, $attribute, 'public');
    }

    /**
     * The attribute is empty.
     * @param mixed $value the value of the attribute
     * @param boolean $trim
     * @return boolean
     */
    protected function isEmpty($value, $trim = false)
    {
        return $value === null || $value === [] || $value === '' || $trim && is_scalar($value) && trim($value) === '';
    }

    /**
     * Return the message translator
     * @return Translator
     */
    protected function getTranslator()
    {
        // return new Translator($this);
    }
}
