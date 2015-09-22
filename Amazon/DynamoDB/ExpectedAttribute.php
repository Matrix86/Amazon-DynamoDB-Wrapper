<?php

namespace Amazon\DynamoDB;

class ExpectedAttribute
{
    private $exists;
    private $value;

    public function __construct($value = null, $type = null)
    {
        $this->exists = null;
        $this->value  = null;

        if( is_bool($value) )
        {
            $this->exists = $value;
        }
        else if( $value instanceof Attribute )
        {
            $this->value = $value;
        }
        else
        {
            $this->value = new Attribute($value, $type);
        }
    }

    public function GetExists()
    {
        return $this->exists;
    }

    public function GetValue()
    {
        return $this->value;
    }

    public function GetFormatted()
    {
        $exists = $this->GetExists();
        $value  = $this->GetValue();

        $condition = array();
        if( isset($exists) )
        {
           $condition['Exists'] = $exists;
        }

        if( isset($value) )
        {
            $condition['Value'] = $value->GetFormatted();
        }

        return $condition;
    }
}


?>
