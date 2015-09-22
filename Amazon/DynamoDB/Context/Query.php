<?php

namespace Amazon\DynamoDB\Context;

class Query
{
    private $TableName = null;
    private $ConsistentRead = null;
    private $ExclusiveStartKey = null;
    private $ExpressionAttributeNames = null;
    private $ExpressionAttributeValues = null;
    private $FilterExpression = null;
    private $IndexName = null;
    private $KeyConditionExpression = null;
    private $Limit = null;
    private $ProjectionExpression = null;
    private $ReturnConsumedCapacity = "TOTAL";
    private $ScanIndexForward = null;
    private $Select = null;

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

    public function SetExclusiveStartKey()
    {
        //! TODO:
        //! The primary key of the first item that this operation will evaluate.
        //! Use the value that was returned for LastEvaluatedKey in the previous operation.
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

    //! The condition that specifies the key value(s) for items to be
    //! retrieved by the Query action.
    public function SetKeyConditionExpression($primaryCondition, $rangeCondition = null)
    {
        if(preg_match( "/([^\s=]+)\s*=\s*([\"]?[^\"]+[\"]?)/i", $primaryCondition, $match ))
        {
            if( isset($match[1]) && isset($match[2]) )
            {
                $newName  = $this->AddAttributeName($match[1]);
                $newValue = $this->AddAttributeValue($match[2]);

                $this->KeyConditionExpression = $newName." = ".$newValue;
            }
        }

        if( $rangeCondition !== null )
        {
            if( preg_match_all( "/([^\s=]+)\s*([<>=]{1,2})\s*([\"]?[^\"\s]+[\"]?)\s*(AND)?|([^\s=]+)\s*BETWEEN\s*([\"]?[^\"\s]+[\"]?)\s*AND\s*([\"]?[^\"\s]+[\"]?)/i", $rangeCondition, $match ) )
            {
                $matchCount = count($match[0]);

                for($i = 0; $i < $matchCount; $i++)
                {
                    if( !empty($match[1][$i]) )
                    {
                        $newName  = $this->AddAttributeName($match[1][$i]);
                        $operator = $match[2][$i];
                        $value    = $this->AddAttributeValue($match[3][$i]);

                        $operation = $newName." ".$operator." ".$value;
                    }
                    else if( !empty($match[5][$i]) )
                    {

                        $newName  = $this->AddAttributeName($match[5][$i]);
                        $value1   = $this->AddAttributeValue($match[6][$i]);
                        $value2   = $this->AddAttributeValue($match[7][$i]);

                        $operation = $newName." BETWEEN ".$value1." AND ".$value2;
                    }
                    else
                    {
                        continue;
                    }

                    $this->KeyConditionExpression .= " AND ".$operation;
                }
            }
        }
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

    public function SetScanIndexForward($bool)
    {
        if( is_bool($bool) )
        {
            $this->ScanIndexForward = $bool;
        }
        else
        {
            $this->ScanIndexForward = null;
        }
    }

    public function SetSelect($str)
    {
        switch($str)
        {
            case "ALL_ATTRIBUTES":
            case "ALL_PROJECTED_ATTRIBUTES":
            case "SPECIFIC_ATTRIBUTES":
            case "COUNT":
                $this->Select = $str;
                break;

            default:
                $this->Select = null;
        }
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
        $VerifyAndAddParam('KeyConditionExpression');
        $VerifyAndAddParam('Limit');
        $VerifyAndAddParam('ProjectionExpression');
        $VerifyAndAddParam('ReturnConsumedCapacity');
        $VerifyAndAddParam('ScanIndexForward');
        $VerifyAndAddParam('Select');
        $VerifyAndAddParam('TableName');

        return $parameters;
    }
}


?>
