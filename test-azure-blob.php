<?php
require_once('config.php');
require_once('AzureBlobClient.php');

$client = new AzureBlobClient(
    $ACCOUNT_NAME, $SECRET_KEY, $HOST, $BUCKET
);

echo "listBuckets " . $client->listBuckets() . "\n";
echo "createBucket " . ($client->createBucket() ? "TRUE" : "FALSE") . "\n";
echo "listBuckets " . $client->listBuckets() . "\n";

echo "exists " . ($client->exists('/testdata') ? "TRUE" : "FALSE") . "\n";
echo "putData " . ($client->putData('/testdata', 'Hello world!') ? "TRUE" : "FALSE") . "\n";
echo "getData " . $client->getData('/testdata') . "\n";
echo "exists " . ($client->exists('/testdata') ? "TRUE" : "FALSE") . "\n";
echo "deleteData " . ($client->deleteData('/testdata') ? "TRUE" : "FALSE") . "\n";
echo "exists " . ($client->exists('/testdata') ? "TRUE" : "FALSE") . "\n";
echo "putData1 " . ($client->putData('/test/work/data1', 'Hello world!') ? "TRUE" : "FALSE") . "\n";
echo "putData2 " . ($client->putData('/test/work/data2', 'Hello world!') ? "TRUE" : "FALSE") . "\n";
echo "putData3 " . ($client->putData('/test/work/data3', 'Hello world!') ? "TRUE" : "FALSE") . "\n";
echo "putData4 " . ($client->putData('/test/work/data4', 'Hello world!') ? "TRUE" : "FALSE") . "\n";

echo "deleteBucket " . ($client->deleleBuckets() ? "TRUE" : "FALSE") . "\n";
echo "listBuckets " . $client->listBuckets() . "\n";
