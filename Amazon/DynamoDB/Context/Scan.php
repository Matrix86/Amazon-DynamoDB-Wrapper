<?php

namespace Amazon\DynamoDB\Context;

class Scan
{
    private $TableName = null;
    private $ConsistentRead = null;
    private $ExclusiveStartKey = null;
    private $ExpressionAttributeNames = null;
    private $ExpressionAttributeValues = null;
    private $FilterExpression = null;
    private $IndexName = null;
    private $Limit = null;
    private $ProjectionExpression = null;
    private $ReturnConsumedCapacity = "TOTAL";
    private $Segment = null;
    private $Select = null;
    private $TotalSegments = null;

    public function __construct($table)
    {
        $this->TableName = $table;
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

    public function GetTable()
    {
        return $this->TableName;
    }

    public function SetConsistentRead($bool)
    {
        if( is_bool($bool) )
        {
            $this->ConsistentRead = $bool;
        }
        else
        {
            $this->ConsistentRead = false;
        }
    }

    public function SetExclusiveStartKey($key)
    {
        $this->ExclusiveStartKey = $key;
    }

    //! A string that contains conditions that DynamoDB applies AFTER the
    //! Query operation, but before the data is returned to you.
    //! Items that do not satisfy the FilterExpression criteria are not returned.
    public function SetFilterExpression($expr)
    {
        $this->FilterExpression = $expr;
    }

    public function SetIndexName($name)
    {
        $this->IndexName = $name;
    }

    public function SetLimit($num)
    {
        if( is_numeric($num) )
        {
            $this->Limit = $num;
        }
        else
        {
            $this->Limit = null;
        }
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

    public function SetSelect($str)
    {
        switch($str)
        {
            case "ALL_ATTRIBUTES":
            case "SPECIFIC_ATTRIBUTES":
            case "COUNT":
                $this->Select = $str;
                break;

            default:
                $this->Select = null;
        }
    }

    public function SetSegment($num)
    {
        $maxValue = 999999;
        if( $this->TotalSegments !== null )
        {
            $maxValue = $this->TotalSegments;
        }

        if( is_numeric($num) && $num >= 0 && $num <= $maxValue )
        {
            $this->Segment = $num;

            return true;
        }
        else
        {
            $this->Segment = null;
            $this->TotalSegments = null;

            return false;
        }
    }

    public function SetTotalSegments($num)
    {
        if( is_numeric($num) && $num >= 1 && $num <= 1000000 )
        {
            $this->TotalSegments = $num;

            return true;
        }
        else
        {
            $this->Segment = null;
            $this->TotalSegments = null;

            return false;
        }
    }

    public function SetProjectionExpression($expr)
    {
        $this->ProjectionExpression = $expr;
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

        $VerifyAndAddParam('ConsistentRead');
        $VerifyAndAddParam('FilterExpression');
        $VerifyAndAddParam('IndexName');
        $VerifyAndAddParam('Limit');
        $VerifyAndAddParam('ProjectionExpression');
        $VerifyAndAddParam('ReturnConsumedCapacity');
        $VerifyAndAddParam('Select');
        $VerifyAndAddParam('TableName');
        $VerifyAndAddParam('ExclusiveStartKey');
        $VerifyAndAddParam('Segment');
        $VerifyAndAddParam('TotalSegments');


        return $parameters;
    }
}


?>
