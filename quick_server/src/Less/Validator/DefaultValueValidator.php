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

class DefaultValueValidator extends BaseValidator
{
    public $value;

    public $setOnEmpty = true;

    public function validateAttribute($object, $attribute)
    {
        if (!$this->setOnEmpty) {
            $object->$attribute = $this->value;
        } else {
            $value = $object->$attribute;
            if ($value === null || $value === '') {
                $object->$attribute = $this->value;
            }

        }
    }
}
