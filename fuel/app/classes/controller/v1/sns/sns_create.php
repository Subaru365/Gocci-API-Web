<?php
use Aws\Sns\SnsClient;
/**
*SNS
*/

$client = new SnsClient([
	'region'  => 'ap-northeast-1',
	'version' => '2010-03-31'
]);

$result = $client->createPlatformEndpoint([
	'CustomUserData' => 'user_id:' . "$user_id" . ' / ' . "$identity_id",
	'PlatformApplicationArn' => 'arn:aws:sns:ap-northeast-1:318228258127:app/GCM/gocci-android',
	'Token' => "$register_id",
]);


$result = Model_Devices::add_arn($result['EndpointArn']);

