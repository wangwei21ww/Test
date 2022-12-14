<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Less\Queryworx\Schema;

use Less\Queryworx\Command\DbCommand;
use Less\Queryworx\Connection\DbConnection;
use Less\Queryworx\Schema\TableSchema;

abstract class ColumnSchema
{
	public $name;
	public $type;
	public $range = null;
	public $allowNull;
	public $defaultValue;
	public $isPrimaryKey = false;
	public $isForeignKey = false;

	protected $rawMetadata = array();
	
	abstract public function isPrimaryKey(array $column,TableSchema $tableSchema);
	
	/**
	 * Get Column type.
	 * @param string $type
	 * @return mixed
	 */
	public function getColumnType($type)
	{
		$columnType = array(
			"integer"=>array("int","tinyint","smallint","mediumint","bigint"),
			"datetime"=>array("date","datetime","year","timestamp","time"),
			"boolean"=>array("bool"),
			"double"=>array("float","double","real"),
		);
		
		if(strpos($type," ")!==false or strpos($type,"(")!==false)
		{
			$type = str_replace(array(" ","("),array(":",":"),$type);
			$type = current(explode(":",$type));
		}
		
		$this->dbType = $type;
		
		foreach($columnType as $key=>$value)
		{
			if(in_array(strtolower($type),$value))
			{
				return $key;
			}
		}
		return 'string';
	}
	
	
	public function typecase($column,$value)
	{
		switch($column->type)
		{
			case 'string': return (string)$value;
			case 'integer': return (integer)$value;
			case 'boolean': return (boolean)$value;
			case 'double':
			default: return $value;
		}
	}
	
	/**
	 * Get the value length of the specifying column.
	 * @param string $type
	 * @return int
	 * @return null if cannot get the field length,that will return null
	 */
	public function getColumnLimit($type)
	{
		preg_match("~\d+~",$type,$range);
		if(isset($range[0]))
		{
			return $range[0];
		}
		return null;
	}

	public function getExtra()
	{
		return $this->rawMetadata['Extra'];
	}
}