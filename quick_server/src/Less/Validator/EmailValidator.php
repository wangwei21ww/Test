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

class EmailValidator extends BaseValidator
{

    public $pattern = '/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/';

    public $fullPattern = '/^[^@]*<[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?>$/';

    public $allowName = false;

    public $checkMX = false;

    public $checkPort = false;

    public $allowEmpty = true;

    public function validateAttribute($object, $attribute)
    {
        $value = $object->$attribute;
        if ($this->allowEmpty && $this->isEmpty($value)) {
            return;
        }

        if (!$this->validateValue($value)) {
            $message = $this->message !== null ? $this->message : '{attribute} is not a valid email address.';
            $this->addError($object, $attribute, $message);
        }
    }

    public function validateValue($value)
    {
        $valid = is_string($value) && (preg_match($this->pattern, $value) || $this->allowName && preg_match($this->fullPattern, $value));
        if ($valid) {
            $domain = rtrim(substr($value, strpos($value, '@') + 1), '>');
        }

        if ($valid && $this->checkMX && function_exists('checkdnsrr')) {
            $valid = checkdnsrr($domain, 'MX');
        }

        if ($valid && $this->checkPort && function_exists('fsockopen')) {
            $valid = fsockopen($domain, 25) !== false;
        }

        return $valid;
    }
}
