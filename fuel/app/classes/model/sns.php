<?php

use Aws\Sns\SnsClient;
/**
*SNS
*/
class Model_Sns extends Model
{

	public static function post_endpoint($user_id, $identity_id, $register_id, $os)
	{
		//AWS SNSに端末を登録
        $brand = explode('_', $os);

        if ($brand[0] == 'android') {
            $endpoint_arn = Model_Sns::post_android(
            $user_id, $identity_id, $register_id);

        }elseif ($brand[0] == 'iOS') {
			$endpoint_arn = Model_Sns::post_iOS(
				$user_id, $identity_id, $register_id);

		}else{
            //Webかな？
            $endpoint_arn = 'none';
            error_log('Model_Sns: endpoint_arn 未発行');
        }

        return $endpoint_arn;
	}


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


	public static function post_iOS($user_id, $identity_id, $register_id)
	{
		$client = new SnsClient([
			'region'  => 'ap-northeast-1',
    		'version' => '2010-03-31'
		]);

		$result = $client->createPlatformEndpoint([
    		'CustomUserData' => 'user_id:' . "$user_id" . ' / ' . "$identity_id",
    		'PlatformApplicationArn' => 'arn:aws:sns:ap-northeast-1:318228258127:app/APNS_SANDBOX/gocci-iOS-dev',
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

