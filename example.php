<?php

require("Amazon\DynamoDB\AutoLoader.php");

$key    = ""; 
$secret = ""; 
$region = "";

$dc = new \Amazon\DynamoDB\AmazonDynamoDB($key, $secret, $region);

// INSERT ITEM

$item = new \Amazon\DynamoDB\Item('TableName');
// Avoid Auto TypeDetection
$item->SetAttribute('id', '10222', 'S');

$item['dateday'] = '2015-09-14';
$item['IT'] = array(
    'counter1' => 2,
    'counter2' => 2,
    'counter3' => 1,
);

$item['EN'] = array(
    'counter1' => 2,
    'counter2' => 2,
    'counter3' => 3,
);

$item['test'] = array(1,2,3,4);

//$dc->AddItem($item);
echo "\n\n";

// GET ITEM
$itemToGet = new \Amazon\DynamoDB\Item('Daily_stats');
$itemToGet->SetAttribute('id', '10222', 'S');
$itemToGet['dateday'] = '2015-09-14';

$res = $dc->GetItem( $itemToGet );
echo "START\n";
echo "ID: ".$res['id']."\n";
echo "IT counter3 : ".$res['IT']['counter3']."\n";
echo "EN counter2 : ".$res['EN']['counter2']."\n";
echo "test 3 : ".$res['test'][3]."\n";



// UPDATE ITEM
$itemToUpdate = new \Amazon\DynamoDB\Item('Daily_stats');
$itemToUpdate->SetAttribute('id', '10222', 'S');
$itemToUpdate['dateday'] = '2015-09-14';

$updateCtx = new \Amazon\DynamoDB\Context\UpdateItem();
$updateCtx->SetSectionSet("IT.counter1 = IT.counter1 + 1, EN.counter2 = 0");
$updateCtx->SetSectionRemove("error");
$updateCtx->SetSectionAdd("prova 33");

$res = $dc->UpdateItem($itemToUpdate, $updateCtx);
echo "\n\n";


// QUERY
$query = new \Amazon\DynamoDB\Context\Query('Daily_stats');
$query->SetConsistentRead(true);
$query->SetLimit(3);
$query->SetKeyConditionExpression('id = "1201"', 'dateday BETWEEN "2015-09-13" AND "2015-09-18"');
$res = $dc->Query($query);

echo "Query results:\n";
foreach( $res as $item )
{

    echo "id: ".$item['id']."\n";
    echo "dateday: ".$item['dateday']."\n";
    echo "IT counter1 : ".$item['IT']['counter1']."\n";
    echo "EN counter1 : ".$item['EN']['counter1']."\n";
    echo "\n";
}

echo "\n\n";


// SCAN

$query = new \Amazon\DynamoDB\Context\Scan('Daily_stats');
$res = $dc->Scan($query);

echo "Scan results:\n";
foreach( $res as $item )
{

    echo "id: ".$item['id']."\n";
    echo "dateday: ".$item['dateday']."\n";
    echo "IT counter1 : ".$item['IT']['counter1']."\n";
    echo "EN counter1 : ".$item['EN']['counter1']."\n";
    echo "\n";
}

echo "\n\n";



?>
