<?php

require('/var/www/gocci/fuel/app/vendor/aws/aws-sdk-php/src/Aws/CognitoSync');

//use Aws\CognitoSync\CognitoSyncClient;

$identity_poolid = 'us-east-1:a8cc1fdb-92b1-4586-ba97-9e6994a43195';

$identity_id = $argv[1];
$username    = $argv[2];
$os          = $argv[3];
$model       = $argv[4];
$register_id = $argv[5];


$client = new CognitoSyncClient([
	'region'  => 'us-east-1',
	'version' => 'latest'
]);


//SyncSessionToken取得
$result = $client->listRecords([
    'DatasetName'    => 'user_info',
	'IdentityId'     => "$identity_id",
    'IdentityPoolId' => "$identity_poolid",
]);

$sync_session_token = $result['SyncSessionToken'];


//DataSet
$result = $client->updateRecords([
    'DatasetName' 	 => 'user_info',
    'IdentityId'     => "$identity_id",
    'IdentityPoolId' => "$identity_poolid",
    'RecordPatches'  => [
        [
            'Key'       => 'username',
            'Op'        => 'replace',
            'SyncCount' => 0,
            'Value'     => "$username",
        ],
        [
            'Key'       => 'os',
            'Op'        => 'replace',
        	'SyncCount' => 0,
            'Value'     => "$os",
        ],
        [
            'Key'       => 'model',
            'Op'        => 'replace',
            'SyncCount' => 0,
            'Value'     => "$model",
        ],
        [
            'Key'       => 'register_id',
            'Op'        => 'replace',
            'SyncCount' => 0,
            'Value'     => "$register_id",
        ],
	],
    'SyncSessionToken' => "$sync_session_token",
]);

exit;
