<?php

namespace Amazon\DynamoDB;

class Item implements \ArrayAccess, \IteratorAggregate
{
	private $table;
	private $attributes = array();


	public function __construct($table)
	{
		$this->table = $table;
	}

	public function GetTable()
	{
		return $this->table;
	}

	public function SetAttribute($name, $value, $type = null)
    {
        if( $value instanceof Attribute )
		{
            $this->attributes[$name] = $value;
        }
		else
		{
            $this->attributes[$name] = new Attribute($value, $type);
        }
    }

	public function CreateItemFromDynamoDB( array $data )
    {
        foreach( $data as $name => $content )
		{
            list( $type, $value ) = each($content);

			if( $type == 'M' )
			{
				$res = array();

				foreach( $value as $k => $v )
				{
					list( $_type, $_value ) = each($v);

					$res[$k] = new Attribute($_value, $_type);
				}

				$this->SetAttribute( $name, $res, $type );
			}
			else
			{
				$this->SetAttribute( $name, $value, $type );
			}
        }
    }

	public function offsetExists($offset)
    {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet($offset)
    {
		if( isset( $this->attributes[$offset] ) )
		{
			if( $this->attributes[$offset]->GetType() == 'M' )
			{
				$res = array();

				foreach( $this->attributes[$offset]->GetValue() as $k => $v )
				{
					$res[$k] = $v->GetValue();
				}

				return $res;
			}
		}
		else
		{
			return null;
		}

        return ( isset( $this->attributes[$offset] ) ? $this->attributes[$offset]->GetValue() : null );
    }

    public function offsetSet($offset, $value)
    {
        if ( $offset == null )
		{
            // Error
			return;
        }

        $this->SetAttribute($offset, $value);
    }

    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

	public function getIterator()
    {
        return new \ArrayIterator($this->attributes);
    }
}

?>
