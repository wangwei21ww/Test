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

class LengthValidator extends BaseValidator
{
    public $charset = 'utf-8';

    public $min;

    public $max;

    // The length should to equal to length of is
    public $is;

    public $tooShort;

    public $tooLong;

    public $allowEmpty = true;

    public function validateAttribute($object, $attribute)
    {
        $value = $object->$attribute;
        if ($this->allowEmpty && $this->isEmpty($value)) {
            return;
        }

        if (!is_string($value)) {
            $this->addError($object, $attribute, '{attribute} is a invalid string.');
            return;
        }

        if ($this->charset !== false && function_exists('mb_strlen')) {
            $length = mb_strlen($value, $this->charset);
        } else {
            $length = strlen($value);
        }

        // begin for lesscloud
        if (($this->min !== null && $length < $this->min) or ($this->max !== null && $length > $this->max)) {
            $message = $this->message !== null ? $this->message : 'The {attribute} minimum is {min} characters, maximum is {max} characters';
            $this->addError($object, $attribute, $message, array('{min}' => $this->min,'{max}' => $this->max));
            return;
        }
        // end for lesscloud

        if ($this->min !== null && $length < $this->min) {
            $message = $this->tooShort !== null ? $this->tooShort : '{attribute} is too short (minimum is {min} characters).';
            $this->addError($object, $attribute, $message, array('{min}' => $this->min));
        }

        if ($this->max !== null && $length > $this->max) {
            $message = $this->tooLong !== null ? $this->tooLong : '{attribute} is too long (maximum is {max} characters).';
            $this->addError($object, $attribute, $message, array('{max}' => $this->max));
        }

        if ($this->is !== null && $length !== $this->is) {
            $message = $this->message !== null ? $this->message : '{attribute} is of the wrong length (should be {length} characters).';
            $this->addError($object, $attribute, $message, array('{length}' => $this->is));
        }
    }
}
