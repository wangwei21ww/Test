<?php

namespace Less\Db;

use Less\Validator\ValidatorBuilder;

class Model
{
    protected $errors = [];

    protected $_attributes = [];

    public function addError($msg)
    {
        $this->errors[] = $msg;
    }

    public function getErrors()
    {
      return $this->errors;
    }

    public function __get($name)
    {
        if(isset($this->_attributes[$name])) {
            return $this->_attributes[$name];
        }
    }

    public function __set($name, $value)
    {
        if(in_array($name,$this->_attrs)) {
            $this->_attributes[$name] = $value;
        }
    }

    public function getAttributes()
    {
        return $this->_attributes;
    }

    public function setAttributes($attrs)
    {
        foreach ($attrs as $key => $value) {
            $this->_attributes[$key] = $value;
        }
    }


    /**
     * Validate the user input data
     * @return boolean
     */
    public function _validate()
    {
        $validator = new ValidatorBuilder;
        if(method_exists($this, 'rules')) {
            $validator->getValidators($this, $this->rules());
            if(\Less::getApp()->errors !== []) {
                return false;
            }
        }
        return true;
    }

    /**
    * The ORM relationship, that method defined the objects ActiveRecord relations.
    * You may need to override this method, That should contain the model relations.
    * @return array
    */
    public function relations()
    {
      return [];
    }

    /**
    * Return the find scope
    * @return array
    */
    public function scopes()
    {
      return [];
    }

    /**
    * Return default scope
    * @return array
    */
    public function defaultScope()
    {
      return [];
    }

    /**
    * Return the data process rules, you may need to override this method.
    * @return array
    */
    public function rules()
    {
      return [];
    }
}