<?php

namespace Amazon\DynamoDB\Context;

class GetItem
{
    private $attributes;
    private $consistentRead;

    public function SetAttributes($attributes)
    {
        $this->attributes = $attributes;

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

        $attributes = $this->attributes;

        if( $attributes !== null )
        {
            $parameters['AttributesToGet'] = $attributes;
        }

        $consistentRead = $this->consistentRead;
        if( $consistentRead !== null )
        {
            $parameters['ConsistentRead'] = $consistentRead;
        }
        
        return $parameters;
    }
}

?>
