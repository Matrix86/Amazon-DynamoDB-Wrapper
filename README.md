# Amazon-DynamoDB-Wrapper
## Another Amazon DynamoDB PHP Wrapper For AWS SDK V3


This is an object wrapper for Amazon DynamoDB SDK. It helps to manipulate the items that you can use with DynamoDB.

##Getting Started
###Include the library

```php
require("Amazon\DynamoDB\AutoLoader.php");
```

###Create Connection
```php
$dc = new \Amazon\DynamoDB\AmazonDynamoDB($key, $secret, $region);
```

###Manage tables
>Work in progress

###Add item
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

###Get item
```php
$item = new \Amazon\DynamoDB\Item('TABLENAME');
$item['primary'] = 3;
$item['range'] = 1;

$res = $dc->GetItem( $item );

```
