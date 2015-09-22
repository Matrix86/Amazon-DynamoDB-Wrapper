<?php

namespace Amazon\DynamoDB\Context;

class UpdateItem
{
    private $ExpressionAttributeNames;
    private $ExpressionAttributeValues;
    private $UpdateExpression;
    private $ConditionExpression;

    private $ReturnValues;

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


    public function SetReturnValues($returnValues)
    {
        $this->ReturnValues = $returnValues;

        return $this;
    }

    public function __construct()
    {
        $this->UpdateExpression = "";
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

    //! SET var = 1, var1 = var1 + 2, var2 = "asd"
    public function SetSectionSet($expr)
    {
        if(preg_match_all( "/([^\s=]+)\s*=\s*([\"]?[^\",]+[\"]?)/i", $expr, $match ))
        {
            $this->UpdateExpression .= "SET ";

            $singleOp = array();

            $in = count($this->ExpressionAttributeNames);
            $iv = count($this->ExpressionAttributeValues);

            for( $i = 1; $i <= count($match[1]); $i++ )
            {
                $op = "";

                $name  = $match[1][$i-1];
                $value = $match[2][$i-1];

                $newname = $this->AddAttributeName($name);

                $op = $newname . " = ";

                if( $this->startsWith($value, "\"") && $this->endsWith($value, "\"") )
				{
                    $value = substr($value, 1, -1);

                    //! String Value
					$this->ExpressionAttributeValues[":val".++$iv] = new \Amazon\DynamoDB\Attribute($value, 'S');

                    $op .= ":val".$iv;
				}
				else if( is_numeric($value) )
                {
                    //! Numeric Value
                    $this->ExpressionAttributeValues[":val".++$iv] = new \Amazon\DynamoDB\Attribute($value, 'N');

                    $op .= ":val".$iv;
                }
                else
                {
                    $cleanValue = preg_replace('/\s+/', ' ', $value);

                    $line = explode( " ", $cleanValue );

                    $newname = $this->AddAttributeName($line[0]);

                    $op .= $newname." ";

                    if( isset($line[2]) )
                    {
                        $this->ExpressionAttributeValues[":val".++$iv] = new \Amazon\DynamoDB\Attribute($line[2], 'N');

                        $op .= $line[1]." :val".$iv;
                    }
                }

                $singleOp[] = $op;
            }

            for( $i = 0; $i < count($singleOp); $i++ )
            {
                $this->UpdateExpression .= $singleOp[$i];

                if( $i != count($singleOp) - 1 )
                {
                    $this->UpdateExpression .= ", ";
                }
                else
                {
                    $this->UpdateExpression .= " ";
                }
            }

        }
    }

    //! REMOVE #m.nestedField1, #m.nestedField2
    public function SetSectionRemove($expr)
    {
        $expr = str_replace(" ", "", $expr);
        $list = explode(",", $expr);

        $in = count($this->ExpressionAttributeNames);

        $listNum = count($list);
        if( $listNum == 0 )
        {
            return;
        }

        $this->UpdateExpression .= " REMOVE ";

        for( $i = 0; $i < $listNum; $i++ )
        {
            $var = $list[$i];

            if( !isset($this->ExpressionAttributeNames[$var]) )
            {
                $this->ExpressionAttributeNames[$var] = "#N".++$in;
            }

            $this->UpdateExpression .= $this->ExpressionAttributeNames[$var];

            if( $i != $listNum - 1 )
            {
                $this->UpdateExpression .= ", ";
            }
            else
            {
                $this->UpdateExpression .= " ";
            }
        }
    }

    //! ADD aNumber :val2, anotherNumber :val3
    public function SetSectionAdd($expr)
    {
        if(preg_match_all( "/([a-z0-9_\-\.]+)\s*([\"]?[^\",]+[\"]?)/i", $expr, $match ))
        {
            $this->UpdateExpression .= " ADD ";

            $singleOp = array();

            $in = count($this->ExpressionAttributeNames);
            $iv = count($this->ExpressionAttributeValues);

            for( $i = 1; $i <= count($match[1]); $i++ )
            {
                $op = "";

                $name = $match[1][$i-1];
                $value = $match[2][$i-1];

                $newname = $this->AddAttributeName($name);

                $op = $newname . " ";

                if( $this->startsWith($value, "\"") && $this->endsWith($value, "\"") )
				{
                    //! String Value
					$this->ExpressionAttributeValues[":val".++$iv] = new \Amazon\DynamoDB\Attribute($value, 'S');

                    $op .= ":val".$iv;
				}
				else if( is_numeric($value) )
                {
                    //! Numeric Value
                    $this->ExpressionAttributeValues[":val".++$iv] = new \Amazon\DynamoDB\Attribute($value, 'N');

                    $op .= ":val".$iv;
                }

                $singleOp[] = $op;
            }

            for( $i = 0; $i < count($singleOp); $i++ )
            {
                $this->UpdateExpression .= $singleOp[$i];

                if( $i != count($singleOp) - 1 )
                {
                    $this->UpdateExpression .= ", ";
                }
                else
                {
                    $this->UpdateExpression .= " ";
                }
            }

        }
    }

    //! DELETE aSet :val4
    public function SetSectionDelete($expr)
    {
        //! TODO:
        //! Deletes an element from a set.
        //! If a set of values is specified, then those values are subtracted from the old set.
        //! For example, if the attribute value was the set [a,b,c] and the DELETE action specifies [a,c],
        //! then the final attribute value is [b]. Specifying an empty set is an error.
        //! Important
        //! The DELETE action only supports set data types. In addition,
        //! DELETE can only be used on top-level attributes, not nested attributes.
        //! You can have many actions in a single expression,
        //! such as the following: SET a=:value1, b=:value2 DELETE :value3, :value4, :value5

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

       $parameters['UpdateExpression'] = $this->UpdateExpression;


       return $parameters;
    }

    public function SetConditionExpression($expr)
    {
        //! TODO:
        //! A condition that must be satisfied in order for a conditional update to succeed.
        //! An expression can contain any of the following:

        //! Functions: attribute_exists | attribute_not_exists | attribute_type | contains | begins_with | size
        //! These function names are case-sensitive.
        //! Comparison operators: = | <> | < | > | <= | >= | BETWEEN | IN
        //! Logical operators: AND | OR | NOT
        //! For more information on condition expressions, see Specifying Conditions in the Amazon DynamoDB Developer Guide.

        //! Note
        //! ConditionExpression replaces the legacy ConditionalOperator and Expected parameters.
        //! Type: String

        //! Required: No

        $this->ConditionExpression = $expr;
    }
}

?>
