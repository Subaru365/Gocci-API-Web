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
            $endpoint_arn = self::post_android(
            	$user_id, $identity_id, $register_id);

        }elseif ($brand[0] == 'iOS') {
			$endpoint_arn = self::post_iOS(
				$user_id, $identity_id, $register_id);

		}else{
            error_log('Model_Sns: endpoint_arn 未発行');
            exit;
        }

        return $endpoint_arn;
	}


	private static function post_android($user_id, $identity_id, $register_id)
	{
		$android_Arn = Config::get('_sns.android_ApplicationArn');

		$client = new SnsClient([
			'region'  => 'ap-northeast-1',
    		'version' => '2010-03-31'
		]);

		$result = $client->createPlatformEndpoint([
    		'CustomUserData' => 'user_id:' . "$user_id" . ' / ' . "$identity_id",
    		'PlatformApplicationArn' => "$android_Arn",
    		'Token' => "$register_id",
    	]);

    	return $result['EndpointArn'];
	}


	private static function post_iOS($user_id, $identity_id, $register_id)
	{
		$iOS_Arn = Config::get('_sns.iOS_ApplicationArn');

		$client = new SnsClient([
			'region'  => 'ap-northeast-1',
    		'version' => '2010-03-31'
		]);

		$result = $client->createPlatformEndpoint([
    		'CustomUserData' => 'user_id:' . "$user_id" . ' / ' . "$identity_id",
    		'PlatformApplicationArn' => "$iOS_Arn",
    		'Token' => "$register_id",
    	]);

    	return $result['EndpointArn'];
	}


	public static function post_message($keyword, $user_id, $target_user_id)
	{
        $user_name  = Model_User::get_name($user_id);
        $target_arn = Model_Device::get_arn($target_user_id);

        $message = "$username" . 'さんから' . "$keyword" . 'されました。';


		$client = new SnsClient([
			'region'  => 'ap-northeast-1',
    		'version' => '2010-03-31'
		]);

		$result = $client->publish([
    		'Message'   => "$message",
    		'TargetArn' => "$target_arn",
		]);
	}


	public static function post_complete($user_id)
	{
		$message = '投稿が完了しました。';
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