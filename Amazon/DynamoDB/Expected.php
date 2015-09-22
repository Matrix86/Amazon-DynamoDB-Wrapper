<?php

namespace Amazon\DynamoDB;

class Expected
{
	private $attributes = array();

	public function SetAttribute($name, $value, $type = null)
	{
		if( $value instanceof ExpectedAttribute )
		{
			$this->attributes[$name] = $value;
		}
		else
		{
			$this->attributes[$name] = new ExpectedAttribute($value, $type);
		}
	}
}

?>
