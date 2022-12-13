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

class HasOneRelation extends BaseActiveRelation
{
    public function instantiate()
    {
        $relation = new $this->className;
        $result = $relation->find($this->conditions,$this->values);
        if($result===null and property_exists($this->model, 'throwAssociateException') and $this->model->throwAssociateException===true)
        {
            throw new \RuntimeException("The record ".$conditions." cannot be found in model ".$this->className.".");
        }
        return $result;
    }
}