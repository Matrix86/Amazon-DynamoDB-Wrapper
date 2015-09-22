<?php

namespace Amazon\DynamoDB\Context;

class AddItem
{
    private $ReturnValues;

    public function SetReturnValues($returnValues)
    {
        $this->ReturnValues = $returnValues;

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

       $VerifyAndAddParam('ReturnValues');

       return $parameters;
    }
}

?>
