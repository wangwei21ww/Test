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

class UrlValidator extends BaseValidator
{
    public $pattern = '/^(http|https):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)/i';

    public $allowEmpty = true;

    public function validateAttribute($object, $attribute)
    {
        $value = $object->$attribute;
        if ($this->allowEmpty && $this->isEmpty($value)) {
            return;
        }
        if (!$this->validateValue($value)) {
            $this->addError($object, $attribute, $this->message !== null ? $this->message : '{attribute} is not a valid URL.');
        }
    }

    public function validateValue($value)
    {
        return is_string($value) && preg_match($this->pattern, $value);
    }
}
