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

class NumberValidator extends BaseValidator
{
    public $integerOnly = false;

    public $allowEmpty = true;

    public $max;

    public $min;

    public $tooBig;

    public $tooSmall;

    public function validateAttribute($object, $attribute)
    {
        $value = $object->$attribute;
        if ($this->allowEmpty && $this->isEmpty($value)) {
            return;
        }

        if ($this->integerOnly) {
            if (!preg_match('/^\s*[+-]?\d+\s*$/', "$value")) {
                $message = $this->message !== null ? $this->message : '{attribute} must be an integer.';
                $this->addError($object, $attribute, $message);
            }
        } else {
            if (!preg_match('/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/', "$value")) {
                $message = $this->message !== null ? $this->message : '{attribute} must be a number.';
                $this->addError($object, $attribute, $message);
            }
        }
        if ($this->min !== null && $value < $this->min) {
            $message = $this->tooSmall !== null ? $this->tooSmall : '{attribute} is too small (minimum is {min}).';
            $this->addError($object, $attribute, $message, array('{min}' => $this->min));
        }
        if ($this->max !== null && $value > $this->max) {
            $message = $this->tooBig !== null ? $this->tooBig : '{attribute} is too big (maximum is {max}).';
            $this->addError($object, $attribute, $message, array('{max}' => $this->max));
        }
    }
}
