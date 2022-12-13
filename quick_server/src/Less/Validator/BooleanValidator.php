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

class BooleanValidator extends BaseValidator
{
    public $trueValue = '1';

    public $falseValue = '0';

    public $strict = false;

    public $allowEmpty = true;

    public function validateAttribute($object, $attribute)
    {
        $value = $object->$attribute;
        if ($this->allowEmpty && $this->isEmpty($value)) {
            return;
        }

        if (!$this->strict && $value != $this->trueValue && $value != $this->falseValue
            || $this->strict && $value !== $this->trueValue && $value !== $this->falseValue) {
            $message = $this->message !== null ? $this->message : '{attribute} must be either {true} or {false}.';
            $this->addError($object, $attribute, $message, array("{attribute}" => $attribute, '{true}' => $this->trueValue, '{false}' => $this->falseValue));
        }
    }
}
