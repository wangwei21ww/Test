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

// use Less\Queryworx\ORM\ActiveRecord;

class ExistValidator extends BaseValidator
{
    public $validateExist = true;
    public $object;
    public $condition = [];

    public function validateAttribute($object, $attribute)
    {
        $cloneModel = clone $object;
        $attributeValue = $cloneModel->$attribute;
        if ($cloneModel instanceof ActiveRecord) {
            if ($cloneModel->getIsNewRecord()) {
                if(!isset($this->condition[$attribute])) {
                    $this->condition[$attribute] = $attributeValue;
                }
                $condition = $this->conditionArrayToString($this->condition);
                $values = array_values($this->condition);

                $result = $cloneModel->exists($condition, $values);
            } else {
                $pk     = $cloneModel->getPrimaryKeyName();

                if(!isset($this->condition[$attribute])) {
                    $this->condition[$attribute] = $attributeValue;
                }
                $condition = $this->conditionArrayToString($this->condition);
                $values = array_values($this->condition);
                $values[] = $cloneModel->$pk;
                $result = $cloneModel->exists($condition . ' AND ' . $pk . ' != ? ', $values);
            }
        }

        if ($result !== $this->validateExist) {
            $defaultMessage = $result === false ? '{attribute} does not exist.' : '{attribute} has been exists.';
            $message = $this->message !== null ? $this->message : $defaultMessage;
            $this->addError($cloneModel, $attribute, $message);
        }
    }


    protected function conditionArrayToString(array $condition)
    {
        $string = [];
        foreach ($condition as $key => $value) {
            $string[] = $key.' = ? ';
        }
        return implode(' AND ', $string);
    }
}
