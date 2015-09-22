<?php

namespace Amazon\DynamoDB\Context;

class AddItem
{
    private $expected;
    private $returnValues;

    public function SetExpected(Expected $expected)
    {
        $this->expected = $expected;

        return $this;
    }

    public function SetReturnValues($returnValues)
    {
        $this->returnValues = $returnValues;

        return $this;
    }

    public function GetFormatted()
    {
       $parameters = array();

       $expected = $this->expected;

       if( $expected !== null )
       {
           $expectedParameters = array();

           foreach( $expected as $name => $attribute )
           {
               $expectedParameters[$name] = $attribute->GetFormatted();
           }

           $parameters['Expected'] = $expectedParameters;
       }

       $returnValues = $this->returnValues;

       if( $returnValues !== null )
       {
           $parameters['ReturnValues'] = $returnValues;
       }

       return $parameters;
    }
}

?>
