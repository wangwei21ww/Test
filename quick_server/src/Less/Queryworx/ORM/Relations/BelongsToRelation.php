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

class BelongsToRelation extends BaseActiveRelation
{
    public function instantiate()
    {
        $relation = new $this->className;
        $result = $relation->findByPk(array($relation->getPrimaryKeyName()=>$this->model->{$this->foreignKey}));
        if($result===null and property_exists($this->model, 'throwAssociateException') and $this->model->throwAssociateException===true)
        {
            $condition = $relation->getPrimaryKeyName()." = ".$this->model->{$this->foreignKey};
            throw new \RuntimeException("The record ".$condition." cannot be found in model ".$this->className.".");
        }
        return $result;
    }
}