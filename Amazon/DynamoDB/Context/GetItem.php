<?php

namespace Amazon\DynamoDB\Context;

class GetItem
{
    private $AttributesToGet;
    private $ConsistentRead;

    public function SetAttributes($attributes)
    {
        $this->AttributesToGet = $attributes;

        return $this;
    }

    public function SetConsistentRead($consistentRead)
    {
        $this->consistentRead = $consistentRead;

        return $this;
    }

    public function GetFormatted()
    {
        $parameters = array();

        $VerifyAndAddParam = function($param) use (&$parameters)
        {
            if( $this->$param != null )
            {
                $parameters[$param] = $this->$param;
            }
        };

        $VerifyAndAddParam('AttributesToGet');
        $VerifyAndAddParam('ConsistentRead');

        return $parameters;
    }
}

?>
