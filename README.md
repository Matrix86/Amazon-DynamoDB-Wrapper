# Amazon-DynamoDB-Wrapper
## Another Amazon DynamoDB PHP Wrapper For AWS SDK V3


This is an object wrapper for Amazon DynamoDB SDK. It helps to manipulate the items that you can use with DynamoDB.

## Getting Started
### Include the library

```php
require("Amazon\DynamoDB\AutoLoader.php");
```

### Create Connection
```php
$dc = new \Amazon\DynamoDB\AmazonDynamoDB($key, $secret, $region);
```

### Manage tables
> Work in progress

### Add item
```php
$item = new \Amazon\DynamoDB\Item('TABLENAME');
$item['numericAttribute'] = 3;
$item['stringAttribute'] = '3';
$item['NumericListAttribute'] = array(
  1,2,3
);
$item['StringListAttribute'] = array(
  'a', 'b', 'c'
);

$item['MapAttribute'] = array(
  'a' => 2,
  'b' => 'string'
);

//! Setting Condition Expression
$ctx = new \Amazon\DynamoDB\Context\AddItem();
$ctx->SetConditionExpression("attribute_not_exists('numericAttribute')");

$dc->AddItem($item)
```

### Get item
```php
$item = new \Amazon\DynamoDB\Item('TABLENAME');
$item['primary'] = 3;
$item['range'] = 1;

$res = $dc->GetItem( $item );

```

### Update item
```php
$item = new \Amazon\DynamoDB\Item('TABLENAME');
$item['primary'] = 3;
$item['range'] = 1;

$updateCtx = new \Amazon\DynamoDB\Context\UpdateItem();

//! Section Set allows you to update data with the values that you specify
$updateCtx->SetSectionSet("var = var + 1, var2 = 0");

//! Section Remove allows you to remove an attribute to the item
$updateCtx->SetSectionRemove("var3");

//! Section add allows you to add a value to a numeric attribute or set
$updateCtx->SetSectionAdd("var4 1");

//! Set the return values
$updateCtx->SetReturnValues("ALL_OLD");

$res = $dc->UpdateItem( $item, $updateCtx );

```

From DynamoDB documentation
>Use ReturnValues if you want to get the item attributes as they appeared either before or after they were updated. For UpdateItem, the valid values are:
>
* NONE - If ReturnValues is not specified, or if its value is NONE, then nothing is returned. (This setting is the default for ReturnValues.)
* ALL_OLD - If UpdateItem overwrote an attribute name-value pair, then the content of the old item is returned.
* UPDATED_OLD - The old versions of only the updated attributes are returned.
* ALL_NEW - All of the attributes of the new version of the item are returned.
* UPDATED_NEW - The new versions of only the updated attributes are returned.


### Delete item
> Work in progress

### Perform a query
```php
$item = new \Amazon\DynamoDB\Context\Query('TABLENAME');
$query->SetConsistentRead(true);
$query->SetKeyConditionExpression('id = "1201"', 'dateday BETWEEN "2015-09-13" AND "2015-09-18"');
$res = $dc->Query($query);

```

### Perform a scan
```php
$query = new \Amazon\DynamoDB\Context\Scan('TABLENAME');
$res = $dc->Scan($query);
```

### Get Consumed Capacity
```php
//! Get the Consumed capacity units used for read all tables
$dc->GetReadConsumedCapacityUnits();

//! Get the Consumed capacity units used for read a single table
$dc->GetReadConsumedCapacityUnits('TABLENAME');

//! Get the Consumed capacity units used for write on all tables
$dc->GetWriteConsumedCapacityUnits();

//! Get the Consumed capacity units used for write on a single table
$dc->GetWriteConsumedCapacityUnits('TABLENAME');

//! Reset All the consumed Capacity Units
$dc->ClearConsumedCapacityUnits();

//! Reset the consumed Capacity Units on a specific table
$dc->ClearConsumedCapacityUnits('TABLENAME');
```

