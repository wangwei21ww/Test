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

class ValidatorBuilder
{
    /**
     * Get all the validators
     * @return array
     */
    public function getValidators($object, array $rules)
    {
        foreach ($rules as $rule) {
            if (!is_array($rule) or !isset($rule[0])) {
                continue;
            }
            // if set the scenarios and the current method not in scenarios
            if(isset($rule['scenarios']) and is_array($rule['scenarios']) and !in_array($object->_method, $rule['scenarios'])) {
                continue;
            }

            $attributes = [];
            if (isset($rule[0]) and is_string($rule[0])) {
                $attributes = array_map("trim", explode(",", $rule[0]));
                $attributes = array_filter($attributes);
            }
            if (isset($rule[0]) and is_array($rule[0])) {
                $attributes = $rule[0];
            }

            if (!isset($rule[1])) {
                throw new \RuntimeException("The validator name is invalid.");
            }
            $validator = $rule[1];
            $params    = array_splice($rule, 2);

            foreach ($attributes as $attribute) {
                $this->createValidator($object, $attribute, $validator, $params);
            }
        }
    }

    /**
      * Create a validator by specified object and execute validation.
      * @param object $object
      * @param string $attribute
      * @param string $validatorName
      * @param array $params
      */
    public function createValidator($object, $attribute, $validatorName, array $params)
    {
        $validatorExist = true;
        $validator      = "Less\\Validator\\" . ucfirst($validatorName) . "Validator";

        if (!class_exists($validator)) {
            $validator      = ucfirst($validatorName) . "Validator";
            $validatorExist = false;
        }

        if ($validatorExist === false and !class_exists($validator)) {
            throw new \InvalidArgumentException("The validator ".$validator." cannot be found");
        }

        $instance = new $validator;
        foreach ($params as $property => $value) {
            $instance->$property = $value;
        }
        call_user_func_array(array($instance, 'validateAttribute'), array($object, $attribute));
    }
}
