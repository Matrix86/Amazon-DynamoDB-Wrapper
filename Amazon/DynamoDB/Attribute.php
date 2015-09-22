<?php

namespace Amazon\DynamoDB;

class Attribute
{
	private $type;
	private $value;

	public function __construct($value, $type = null)
	{
		if($type == null)
		{
			$type = $this->TypeDetection($value);
		}

		$this->type  = $type;
		$this->value = $value;

		if( $type == 'M' )
		{
			$this->value = array();

			foreach( $value as $k => $v )
			{
				if( $v instanceof Attribute )
				{
					$this->value[$k] = $v;
				}
				else
				{
					$this->value[$k] = new Attribute($v);
				}
			}
		}
	}

	private function TypeDetection($value)
    {
        if( is_array($value) )
		{
            foreach( $value as $nam => $val )
			{
				if( is_numeric($nam) )
				{
					if( !is_numeric($val) )
					{
	                    return 'SS';
	                }
				}
				else {
					return 'M';
				}
            }

			return 'NS';
        }
		elseif( is_numeric($value) )
		{
            return 'N';
        }
		else
		{
            return 'S';
        }
    }

	public function GetValue()
    {
		return $this->value;
    }

	public function GetType()
    {
        return $this->type;
    }

	public function isArray()
    {
        return ( 'SS' === $this->type || 'NS' === $this->type || 'M' === $this->type );
    }

	public function GetFormatted()
    {
        if( $this->isArray() )
		{
			if('M' === $this->type)
			{
				$value = array_map( function ($val) { return $val->GetFormatted(); }, $this->GetValue() );
			}
			else
			{
            	$value = array_map( function ($val) { return strval($val); }, $this->GetValue() );
			}
        }
		else
		{
            $value = strval($this->getValue());
        }

        return array($this->getType() => $value);
    }
}

?>
