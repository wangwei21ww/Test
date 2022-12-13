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

class RangeValidator extends BaseValidator
{
    public $range;

    public $strict = false;

    public $allowEmpty = true;

    public $caseSensitive = true;

    public $matchInRange = true;

    public function validateAttribute($object, $attribute)
    {
        $value = $object->$attribute;
        if ($this->allowEmpty && $this->isEmpty($value)) {
            return;
        }

        $function = 'in_array';
        if ($this->caseSensitive === false) {
            $function = "\Less\Helper\ArrayHelper\iin_array";
        }

        if(!is_string($value)) {
            throw new \RuntimeException("The validate item ".$attribute." must be a string");
        }

        if (is_array($this->range) && $function($value, $this->range, $this->strict) !== $this->matchInRange) {
            $message = $this->message !== null ? $this->message : '{attribute} is not in the list.';
            $this->addError($object, $attribute, $message);
        }
    }
}
