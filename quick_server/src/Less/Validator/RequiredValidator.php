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

class RequiredValidator extends BaseValidator
{
    public $requiredValue;

    public $strict = false;

    public function validateAttribute($object, $attribute)
    {
        $value = $object->$attribute;
        if ($this->requiredValue !== null) {
            if (!$this->strict && $value != $this->requiredValue || $this->strict && $value !== $this->requiredValue) {
                $message = $this->message !== null ? $this->message : "{attribute} must be {value}";
                $this->addError($object, $attribute, $message, array("{value}" => $this->requiredValue));
                return;
            }
        }
        if ($this->isEmpty($value, true)) {
            $message = $this->message !== null ? $this->message : "{attribute} cannot be blank";
            $this->addError($object, $attribute, $message);
        }
    }
}
