<?php

use Aws\Sns\SnsClient;
/**
*SNS
*/
class Model_Sns extends Model
{

	public static function post_android($user_id, $identity_id, $register_id)
	{
		$client = new SnsClient([
			'region'  => 'ap-northeast-1',
    		'version' => '2010-03-31'
		]);

		$result = $client->createPlatformEndpoint([
    		'CustomUserData' => 'user_id:' . "$user_id" . ' / ' . "$identity_id",
    		'PlatformApplicationArn' => 'arn:aws:sns:ap-northeast-1:318228258127:app/GCM/gocci-android',
    		'Token' => "$register_id",
    	]);

    	return $result['EndpointArn'];
	}


	public static function post_message($keyword, $user_id, $target_user_id)
	{

        $user_name  = Model_User::get_name($user_id);
        $target_arn = Model_Device::get_arn($target_user_id);


		$client = new SnsClient([
			'region'  => 'ap-northeast-1',
    		'version' => '2010-03-31'
		]);

		$result = $client->publish([
    		'Message'   => "$username" . 'さんから' . "$keyword" . 'されました。',
    		'TargetArn' => "$target_arn",
		]);
	}


}

