<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Less\Queryworx\ORM\Relations;

use Less\Queryworx\Base\Model;

class BaseActiveRelation extends Model
{
	public $model;
	public $className;
	public $foreignKey;
	public $options;

	public function __set($name,$value)
	{
		$this->$name = $value;
	}
	
	public function __construct($model, $className, $foreignKey, array $options=array())
	{
		$this->model      = $model;
		$this->className  = $className;
		$this->foreignKey = $foreignKey;

		foreach($options as $name=>$value)
		{
			$this->{$name} = $value;
		}
	}
}