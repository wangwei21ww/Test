<?php
/**
 * This file is part of the Less
 * @author Stephen Lee <stephen.lee@lesscloud.com>
 * @link https://lesscloud.com/
 * @license All copyright and license information, please visit the web page
 *           https://lesscloud.com/license
 * @version $Id$
 */

namespace Less\Validator;


use Less\Validator\BaseValidator;

class CompareValidator extends BaseValidator
{
    public $compareAttribute;

    public $compareValue;

    public $strict = false;

    public $allowEmpty = false;

    public $operator = '=';

    public function validateAttribute($object, $attribute)
    {
        $value = $object->$attribute;
        if ($this->allowEmpty && $this->isEmpty($value)) {
            return;
        }

        if ($this->compareValue !== null) {
            $compareTo = $compareValue = $this->compareValue;
        } else {
            $compareAttribute = $this->compareAttribute === null ? $attribute . '_repeat' : $this->compareAttribute;
            $compareValue     = $object->$compareAttribute;
            $compareTo        = $object->getAttributeLabel($compareAttribute);
        }

        switch ($this->operator) {
            case '=':
            case '==':
                if (($this->strict && $value !== $compareValue) || (!$this->strict && $value != $compareValue)) {
                    $message = $this->message !== null ? $this->message : '{attribute} must be repeated exactly.';
                    $this->addError($object, $attribute, $message, array('{compareAttribute}' => $compareTo));
                }
                break;
            case '!=':
                if (($this->strict && $value === $compareValue) || (!$this->strict && $value == $compareValue)) {
                    $message = $this->message !== null ? $this->message : '{attribute} must not be equal to "{compareValue}".';
                    $this->addError($object, $attribute, $message, array('{compareAttribute}' => $compareTo, '{compareValue}' => $compareValue));
                }
                break;
            case '>':
                if ($value <= $compareValue) {
                    $message = $this->message !== null ? $this->message : '{attribute} must be greater than "{compareValue}".';
                    $this->addError($object, $attribute, $message, array('{compareAttribute}' => $compareTo, '{compareValue}' => $compareValue));
                }
                break;
            case '>=':
                if ($value < $compareValue) {
                    $message = $this->message !== null ? $this->message : '{attribute} must be greater than or equal to "{compareValue}".';
                    $this->addError($object, $attribute, $message, array('{compareAttribute}' => $compareTo, '{compareValue}' => $compareValue));
                }
                break;
            case '<':
                if ($value >= $compareValue) {
                    $message = $this->message !== null ? $this->message : '{attribute} must be less than "{compareValue}".';
                    $this->addError($object, $attribute, $message, array('{compareAttribute}' => $compareTo, '{compareValue}' => $compareValue));
                }
                break;
            case '<=':
                if ($value > $compareValue) {
                    $message = $this->message !== null ? $this->message : '{attribute} must be less than or equal to "{compareValue}".';
                    $this->addError($object, $attribute, $message, array('{compareAttribute}' => $compareTo, '{compareValue}' => $compareValue));
                }
                break;
            default:
                throw new \RuntimeException(strtr('Invalid operator "{operator}".', array('{operator}' => $this->operator)));
        }
    }
}
