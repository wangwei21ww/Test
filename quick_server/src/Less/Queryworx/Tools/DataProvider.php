<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Less\Queryworx\Tools;

use Less\Queryworx\Base\Model;
use Less\Queryworx\Command\DbCriteria;
use Less\Queryworx\Command\CommandBuilder;
use Less\Exception\HttpException;

class DataProvider extends CommandBuilder
{
	public $pageSize = 10;
	public $object;
	public $criteriaParams=array('fields'=>'*');
	public $values = array();
	public $enableDefaultScope = true;

	/**
	 * The data row number.
	 */ 
	public $count;
	
	public function __construct(Model $model,array $params=array())
	{
		parent::__construct($model);
		$this->model = $model;
		foreach($params as $property=>$value)
		{
			if(property_exists($this,$property))
				$this->{$property} = $value;
		}
		$this->criteria = $this->createDbCriteria();
		foreach($this->criteriaParams as $property=>$value)
		{
			$this->criteria->{$property} = $value;
		}
	}
	
	public function getData($currentPage=1)
	{
		$offset = $currentPage===1 ? 0 : ($currentPage-1)*$this->pageSize;
		
		if($offset>=0)
		{
			$this->criteria->limit  = $this->pageSize;
		}
		
		if($currentPage>=1)
		{
			$this->criteria->offset = $offset;
		}
		
		if($this->enableDefaultScope===true and ($defaultScope = $this->model->defaultScope())!==array())
		{
			$this->criteria->setPlaceholders($defaultScope);
		}
		
		$this->command->setSql($this->criteria);
		
		$this->bindValues($this->command,$this->values);
		
		$isAjaxRequest = \Lightworx::getApplication()->request->isAjaxRequest();
		if(($data = $this->model->query($this->command,true))===array() and $isAjaxRequest===false)
		{
			return array();
		}
		return $data;
	}
	
	/**
	 * Get the pages number
	 * @param integer
	 */
	public function getPageCount($condition='',array $params=array())
	{
		$this->count = $this->model->count($condition,$params);
		return ceil((int)$this->count/(int)$this->pageSize);
	}
}