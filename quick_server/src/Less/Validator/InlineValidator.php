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

class InlineValidator extends BaseValidator
{
    public $method;

    public $params;

    public function validateAttribute($object, $attribute)
    {
        $method = $this->method;
        if ($object->$method($attribute, $this->params) !== true) {
            $message = 'The {attribute} validation failure.';
            $message = $this->message != "" ? $this->message : $message;
            $this->addError($object, $attribute, $message);
        }
    }
}
