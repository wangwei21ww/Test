<?php
/**
 * This file is part of the Less
 * @author Stephen Lee <stephen.lee@lesscloud.com>
 * @link https://lesscloud.com/
 * @license All copyright and license information, please visit the web page
 *           https://lesscloud.com/license
 * @version $Id: RegexValidator.php 21 2011-10-03 13:32:14Z Stephen.Lee $
 */

namespace Less\Validator;


use Less\Validator\BaseValidator;

class RegexValidator extends BaseValidator
{
    public $pattern;

    public $allowEmpty = true;

    public function validateAttribute($object, $attribute)
    {
        $value = $object->$attribute;
        if ($this->allowEmpty && $this->isEmpty($value)) {
            return;
        }
        if (!preg_match($this->pattern, $value)) {
            $message = $this->message !== null ? $this->message : '{attribute} is invalid.';
            $this->addError($object, $attribute, $message);
        }
    }
}
