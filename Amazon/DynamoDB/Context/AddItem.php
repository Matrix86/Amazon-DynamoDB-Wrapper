<?php

namespace Amazon\DynamoDB\Context;

class AddItem
{
    private $ReturnValues = null;
    private $ConditionExpression = null;
    private $ExpressionAttributeNames = null;
    private $ExpressionAttributeValues = null;
    private $ReturnConsumedCapacity = "TOTAL";

    public function __construct()
    {
        
    }

    private static function startsWith($haystack, $needle, $icase = true)
	{
        if($icase)
        {
            $haystack = strtolower($haystack);
            $needle   = strtolower($needle);
        }

	    // search backwards starting from haystack length characters from the end
	    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
	}

	private static function endsWith($haystack, $needle, $icase = true)
	{
        if($icase)
        {
            $haystack = strtolower($haystack);
            $needle   = strtolower($needle);
        }

	    // search forward starting from end minus needle length characters
	    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
	}

    private function AddAttributeName($attributeName)
    {
        $in = count($this->ExpressionAttributeNames);

        $name    = $attributeName;
        $subname = "";

        if( strstr($attributeName, ".") )
        {
            $names   = explode(".", $attributeName);

            $name    = $names[0];
            $subname = ".".$names[1];
        }

        if( !isset($this->ExpressionAttributeNames[$name]) )
        {
            $this->ExpressionAttributeNames[$name] = "#N".++$in;
        }

        return "#N".$in.$subname;
    }

    private function AddAttributeValue($value)
    {
        $iv = count($this->ExpressionAttributeValues);

        $retValue = "";

        if( $this->startsWith($value, "\"") && $this->endsWith($value, "\"") )
        {
            $value = substr($value, 1, -1);

            //! String Value
            $this->ExpressionAttributeValues[":val".++$iv] = new \Amazon\DynamoDB\Attribute($value, 'S');

            $retValue = ":val".$iv;
        }
        else if( is_numeric($value) )
        {
            //! Numeric Value
            $this->ExpressionAttributeValues[":val".++$iv] = new \Amazon\DynamoDB\Attribute($value, 'N');

            $retValue = ":val".$iv;
        }

        return $retValue;
    }

    public function SetReturnConsumedCapacity($str)
    {
        switch($str)
        {
            case "INDEXES":
            case "TOTAL":
            case "NONE":
                $this->ReturnConsumedCapacity = $str;
                break;

            default:
                $this->ReturnConsumedCapacity = null;
        }
    }

    public function SetReturnValues($returnValues)
    {
        $this->ReturnValues = $returnValues;

        return $this;
    }

    public function SetConditionExpression($expr)
    {
        //! TODO:
        //! Parse expression and extract Attribute Values and Names

        $this->ConditionExpression = $expr;
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

       if( $this->ExpressionAttributeNames !== null )
       {
           $ExpressionAttributeNames_inverse = array();

           foreach($this->ExpressionAttributeNames as $k => $v)
           {
               $ExpressionAttributeNames_inverse[$v] = $k;
           }

           $parameters['ExpressionAttributeNames'] = $ExpressionAttributeNames_inverse;
       }

       if( $this->ExpressionAttributeValues !== null )
       {
           $Attributes = array();

           foreach( $this->ExpressionAttributeValues as $k => $v )
           {
               $Attributes[$k] = $v->GetFormatted();
           }

           $parameters['ExpressionAttributeValues'] = $Attributes;
       }

       $VerifyAndAddParam('ReturnValues');
       $VerifyAndAddParam('ConditionExpression');

       return $parameters;
    }
}

?>
