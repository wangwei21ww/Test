<?php

namespace Less\Helper\VariableHandler;

use Less\Foundation\ClassLoader;

function ensureValue($array,$key,$defaultReturn=null,$ensureType=null,$ensureNotEmpty=false)
{
    $ensure = false;
    $ensureType = strtolower($ensureType);
    $func = 'is_'.$ensureType;
    if(isset($array[$key]) and $array[$key]!='')
    {
        $ensure = true;
    }else{
        $ensure = false;
    }
    if($ensure===true and in_array($ensureType,array('string','array')) and $func($array[$key]))
    {
        $ensure = true;
    }else{
        $ensure = false;
    }
    if($ensureNotEmpty===true)
    {
        if($ensureType=='string' and $ensure===true)
        {
            $ensure = $array[$key]!='' ? true : false;
        }

        if($ensureType=='array' and $ensure===true)
        {
            $ensure = $array[$key]!==[] ? true : false;
        }
    }
    return $ensure===true ? $array[$key] : $defaultReturn;
}