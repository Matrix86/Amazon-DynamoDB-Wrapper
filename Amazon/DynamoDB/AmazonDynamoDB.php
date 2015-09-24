<?php

namespace Amazon\DynamoDB;

require_once("Aws\aws-autoloader.php");

class AmazonDynamoDB
{
	private $client  = null;
	private $version = '2012-08-10';

	private $readCapacityUnit  = array();
	private $writeCapacityUnit = array();

	public function __construct($key, $secret, $region)
	{
		// if (!class_exists('\Aws\DynamoDb\DynamoDbClient')) {
        //     throw new \RuntimeException('AWS SDK is missing');
        // }

		$this->client = \Aws\DynamoDb\DynamoDbClient::factory(array(
			'credentials' => array(
				'key'    => $key,
				'secret' => $secret,
			),
			'region'  => $region,
			'version' => $this->version
		));
	}

	public function GetReadConsumedCapacityUnits($tableName = null)
	{
		if( $tableName == null )
		{
			$totalCapacityUnits = 0;

			foreach( $this->readCapacityUnit as $table => $value )
			{
				$totalCapacityUnits += $value;
			}

			return $totalCapacityUnits;
		}
		else
		{
			if( isset( $this->readCapacityUnit[$table] ) )
			{
				return $this->readCapacityUnit[$table];
			}
			else
			{
				return 0;
			}
		}
	}

	private function AddReadConsumedCapacityUnits($ConsumedCapacity)
	{
		if( isset( $this->readCapacityUnit[$ConsumedCapacity['TableName']] ) )
		{
			$this->readCapacityUnit[$ConsumedCapacity['TableName']] += $ConsumedCapacity['CapacityUnits'];
		}
		else
		{
			$this->readCapacityUnit[$ConsumedCapacity['TableName']] = $ConsumedCapacity['CapacityUnits'];
		}
	}

	public function GetWriteConsumedCapacityUnits($tableName = null)
	{
		if( $tableName == null )
		{
			$totalCapacityUnits = 0;

			foreach( $this->writeCapacityUnit as $table => $value )
			{
				$totalCapacityUnits += $value;
			}

			return $totalCapacityUnits;
		}
		else
		{
			if( isset( $this->writeCapacityUnit[$table] ) )
			{
				return $this->writeCapacityUnit[$table];
			}
			else
			{
				return 0;
			}
		}
	}

	private function AddWriteConsumedCapacityUnits($ConsumedCapacity)
	{
		if( isset( $this->writeCapacityUnit[$ConsumedCapacity['TableName']] ) )
		{
			$this->writeCapacityUnit[$ConsumedCapacity['TableName']] += $ConsumedCapacity['CapacityUnits'];
		}
		else
		{
			$this->writeCapacityUnit[$ConsumedCapacity['TableName']] = $ConsumedCapacity['CapacityUnits'];
		}
	}

	public function ClearConsumedCapacityUnits($tableName = null)
	{
		if( $tableName == null )
		{
			$this->writeCapacityUnit = array();
			$this->readCapacityUnit  = array();
		}
		else
		{
			if( isset( $this->writeCapacityUnit[$tableName] ) )
			{
				$this->writeCapacityUnit[$tableName] = 0;
			}

			if(isset($this->readCapacityUnit[$tableName]))
			{
				$this->readCapacityUnit[$tableName]  = 0;
			}
		}
	}

	private function populateAttributes(\Aws\Result $data)
    {
        if( isset( $data['Attributes'] ) )
		{
            $attributes = array();
            foreach( $data['Attributes'] as $name => $value )
			{
                list ($type, $value) = each($value);
                $attributes[$name] = new Attribute($value, $type);
            }

            return $attributes;
        }
		else
		{
            return null;
        }
    }

	public function AddItem( $item, Context\AddItem $context = null )
	{
		$table = $item->GetTable();

		if( empty($table) )
		{
			// Error
		}

		$attributes = array();
		foreach( $item as $name => $attribute )
		{
			if( $attribute->GetValue() !== "" )
			{
				$attributes[$name] = $attribute->GetFormatted();
			}
		}

		$ItemDescriptor = array(
			'TableName' => $table,
			'Item'      => $attributes
		);

		if( $context === null )
		{
			//! Create context to return Consumed Capacity
			$context = new Context\AddItem();
		}

		$ItemDescriptor += $context->GetFormatted();

		//var_dump($ItemDescriptor);exit();

		$response = $this->client->putItem($ItemDescriptor);

		$this->AddWriteConsumedCapacityUnits( floatval($response['ConsumedCapacityUnits']) );

		return $this->populateAttributes($response);
	}

	public function GetItem( $item, Context\GetItem $context = null )
	{
		$table = $item->GetTable();

		$attributes = array();

		foreach( $item as $name => $attribute )
		{
			if( $attribute->GetValue() !== "" )
			{
				$attributes[$name] = $attribute->GetFormatted();
			}
		}

		$ItemDescriptor = array(
            'TableName' => $table,
            'Key'       => $attributes
        );

		if( $context !== null )
		{
            $ItemDescriptor += $context->GetFormatted();
        }

		$response = $this->client->getItem($ItemDescriptor);

        $this->AddReadConsumedCapacityUnits( floatval($response['ConsumedCapacityUnits']) );

		if( isset($response['Item']) )
		{
            $item = new Item($table);
            $item->CreateItemFromDynamoDB($response['Item']);

            return $item;
        }
		else
		{
            return null;
        }
	}

	public function UpdateItem( $item, Context\UpdateItem $context )
	{
		$table = $item->GetTable();

		$keys = array();

		foreach( $item as $name => $key )
		{
			if( $key->GetValue() !== "" )
			{
				$keys[$name] = $key->GetFormatted();
			}
		}

		$UpdateItem = array(
            'TableName' => $table,
            'Key'       => $keys
        );

		$UpdateItem += $context->GetFormatted();

		//var_dump($UpdateItem);exit();

		$response = $this->client->updateItem($UpdateItem);

        $this->AddWriteConsumedCapacityUnits( floatval($response['ConsumedCapacityUnits']) );

		return $this->populateAttributes($response);
	}

	function DeleteItem()
	{
	}

	public function Query( Context\Query $context )
	{
		$table = $context->GetTable();
		$query = $context->GetFormatted();

		$items = array();
		$loop  = false;

		do
		{
			$response = $this->client->query($query);

	        $this->AddReadConsumedCapacityUnits( floatval($response['ConsumedCapacityUnits']) );

			if( isset($response['Items']) && !empty($response['Items']) )
			{
				foreach( $response['Items'] as $responseItem )
				{
	                $item = new Item($table);
	                $item->CreateItemFromDynamoDB($responseItem);

					$items[] = $item;
	            }
	        }

			if( isset($response['LastEvaluatedKey']) )
			{
				$context->SetExclusiveStartKey($response['LastEvaluatedKey']);
				$query = $context->GetFormatted();

				$loop = true;
			}
			else
			{
				$loop = false;
			}
		}
		while($loop);

		return $items;
	}

	public function Scan( Context\Scan $context )
	{
		$table = $context->GetTable();
		$query = $context->GetFormatted();

		$items = array();
		$loop  = false;

		do
		{
			$response = $this->client->scan($query);

	        $this->AddReadConsumedCapacityUnits( floatval($response['ConsumedCapacity']) );

			if( isset($response['Items']) && !empty($response['Items']) )
			{
				foreach( $response['Items'] as $responseItem )
				{
	                $item = new Item($table);
	                $item->CreateItemFromDynamoDB($responseItem);

					$items[] = $item;
	            }
	        }

			if( isset($response['LastEvaluatedKey']) )
			{
				$context->SetExclusiveStartKey($response['LastEvaluatedKey']);
				$query = $context->GetFormatted();

				$loop = true;
			}
			else
			{
				$loop = false;
			}
		}
		while($loop);

		return $items;
	}
}


?>
