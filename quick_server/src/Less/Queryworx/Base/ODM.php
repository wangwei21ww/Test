<?php

namespace Less\Queryworx\Base;

use \Less\Queryworx\Base\ObjectModel;

class ODM extends ObjectModel
{
    public $page = 0;
    protected $_count = 0;
    protected $_cursor;
    protected $_conditions = array();
    public $criteria = array();
    public $_oldAttributes = array();

    protected function defaultScope()
    {
        return array();
    }

    public function __construct()
    {
        parent::__construct();
    }

    protected function relations()
    {
        return array();
    }

    public function count(array $condition=array())
    {
        $condition = array_merge($condition,$this->_conditions);
        return $this->_collection->count($condition);
    }
    
    protected function query(array $condition=array(), $findAll=false, array $range=array(), array $sort=array())
    {
        $result = false;
        if($this->beforeFind())
        {
            $defaultScope = $this->defaultScope();
            $condition = array_merge($condition,$defaultScope);
            $this->_cursor = $this->_collection->find($condition);
            $this->_count = $this->count($condition);
            
            if($sort!==array())
            {
                $this->_cursor->sort($sort);
            }

            if($findAll===false)
            {
                $records = $this->_cursor->limit(1);
                $result = current($this->populateRecords($records));
            }else{
                $records = $this->_cursor->skip($range['page'])->limit($range['limit']);
                $result = $this->populateRecords($records);
            }
            $this->afterFind();
        }else{
            throw new \RuntimeException('The method beforeFind should be return to true.');
        }
        return $result;
    }

    protected function beforeFind()
    {
        return true;
    }

    protected function afterFind(){}

    public function findByPk(array $condition=array())
    {
        if(!isset($condition['_id']))
        {
            throw new \RuntimeException('The parameter _id must be provided.');
        }
        $id = $condition['_id'];
        $condition = array('_id' => $id);
        // $condition = array('_id' => new \MongoId($id));
        return $this->query($condition,false);
    }

    public function find(array $condition=array())
    {
        return $this->query($condition,false);
    }

    public function findAll(array $condition=array(),$limit=10, array $sort=array())
    {
        $range = $this->getPageLimit($limit);
        return $this->query($condition,true,$range,$sort);
    }

    /**
     * Find All data of matched.
     * @param string $key
     * @param array $inConditions
     * @param mixe $limit defautls to 10, if set it to `false`, it does not limit.
     * @param array $sort
     * @return array
     */
    public function findIn($key, array $inConditions=array(),$limit=10, array $sort=array())
    {
        $condition = array(
            $key => array('$in'=>$inConditions)
        );
        $range = $this->getPageLimit($limit);
        return $this->query($condition,true,$range,$sort);
    }

    protected function getPageLimit($limit=10)
    {
        $page = 1;
        if(isset($this->page))
        {
            $page = ((int)$this->page*$limit);
        }
        return array('page'=>$page,'limit'=>$limit);
    }

    protected function saveDOC($attributes,$options)
    {
        if($this->upsert===true and $this->criteria!==array())
        {
            $options = array_merge($options,array('upsert'=>true));
            unset($attributes["_id"]);
            $result = $this->_collection->update($this->criteria,array('$set'=>$attributes),$options);
        }else{
            $result = $this->_collection->save($attributes,$options);
        }
        return $result;
    }
    
    public function save($runValidation=true,$attributes=null,array $options=array())
    {
        $result = false;
        if($this->beforeSave())
        {
            $attributes = $attributes===null ? $this->getAttributes(false) : $attributes;
            
            if($runValidation===true)
            {
                if($this->validate($attributes)===true) 
                {
                    $result = $this->saveDOC($attributes,$options);
                }
            }else{
                $result = $this->saveDOC($attributes,$options);
            }

            if($result!==false)
            {
                $this->afterSave();
            }
        }
        return $result;
    }

    protected function beforeSave()
    {
        return true;
    }

    protected function afterSave(){}

    public function delete(array $condition=array(),array $options=array())
    {
        $result = false;
        if($this->beforeDelete())
        {
            $result = $this->_collection->remove($condition,$options);
            $this->afterDelete();
        }
        return $result;
    }

    public function deleteAll(array $conditions, array $options=array())
    {
        $result = array();
        foreach($conditions as $condition)
        {
            $result[] = $this->delete($condition,$options);
        }
        return $result;
    }

    protected function beforeDelete()
    {
        return true;
    }

    protected function afterDelete(){}
}