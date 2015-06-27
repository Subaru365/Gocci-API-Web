<?php

use Aws\Sns\SnsClient;

$keyword    = $argv[1];
$user_name  = Model_User::get_name($argv[2]);
$target_arn = Model_Device::get_arn($argv[3]);


$client = new SnsClient([
	'region'  => 'ap-northeast-1',
   	'version' => '2010-03-31'
]);

$result = $client->publish([
    'Message' => "$username" . 'さんから'　. "$keyword" . 'されました！',
    'MessageAttributes' => [
        '<String>' => [
            'BinaryValue' => <Psr\Http\Message\StreamableInterface>,
            'DataType' => '<string>', // REQUIRED
            'StringValue' => '<string>',
    	],
	],
    'MessageStructure' => '<string>',
   	'Subject' => '<string>',
   	'TargetArn' => "$target_arn",
   	'TopicArn' => '<string>',
]);
