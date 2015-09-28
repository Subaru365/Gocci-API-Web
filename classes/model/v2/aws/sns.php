<?php

use Aws\Sns\SnsClient;
/**
*SNS
*/
class Model_Sns extends Model
{
	public static function set_endpoint($user_data)
	{
		//AWS SNSに端末を登録
        if ($user_data['os'] == 'android') {
            $endpoint_arn = self::set_android($user_data);

        }elseif ($user_data['os'] == 'iOS') {
			$endpoint_arn = self::set_iOS($user_data);

		}else{
            error_log("Model_Sns: $user_data['os']が不正です。");
            exit;
        }

        return $endpoint_arn;
	}


	private static function set_android($user_data)
	{
		$android_arn = Config::get('_sns.android_ApplicationArn');

		$client = new SnsClient([
			'region'  => 'ap-northeast-1',
    		'version' => '2010-03-31'
		]);

		$result = $client->createPlatformEndpoint([
    		'CustomUserData' => "user_id / $user_data['user_id']",
    		'PlatformApplicationArn' => "$android_arn",
    		'Token' => "$user_data['register_id']",
    	]);

    	return $result['EndpointArn'];
	}


	private static function set_iOS($user_data)
	{
		$iOS_arn = Config::get('_sns.iOS_ApplicationArn');

		$client = new SnsClient([
			'region'  => 'ap-northeast-1',
    		'version' => '2010-03-31'
		]);

		$result = $client->createPlatformEndpoint([
    		'CustomUserData' => "user_id / $user_data['user_id']",
    		'PlatformApplicationArn' => "$iOS_arn",
    		'Token' => "$user_data['register_id']",
    	]);

    	return $result['EndpointArn'];
	}


	public static function post_message($keyword, $user_id, $target_user_id)
	{
        $username  = Model_User::get_name($user_id);
        $target_arn = Model_Device::get_arn($target_user_id);

        $message = "$username" . 'さんから' . "$keyword" . 'されました！';

        $client = new SnsClient([
            'region'  => 'ap-northeast-1',
            'version' => '2010-03-31'
        ]);

        $result = $client->publish([
            'Message'   => "$message",
            'TargetArn' => "$target_arn",
        ]);
	}


	public static function post_publish($user_id, $message)
	{
		$target_arn = Model_Device::get_arn($user_id);

		$client = new SnsClient([
			'region'  => 'ap-northeast-1',
    		'version' => '2010-03-31'
		]);

		$result = $client->publish([
    		'Message'   => "$message",
    		'TargetArn' => "$target_arn",
		]);
	}


	public static function delete_endpoint($endpoint_arn)
	{
		$client = new SnsClient([
			'region'  => 'ap-northeast-1',
    		'version' => '2010-03-31'
		]);

		$result = $client->deleteEndpoint([
    		'EndpointArn' => "$endpoint_arn",
		]);
	}
}