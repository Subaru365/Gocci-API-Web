<?php

use Aws\Sns\SnsClient;

/**
*
*/
class Controller_Test extends Controller
{


	public static function action_index()
	{
		$message = 'テスト通知しました';
		$target_arn = Model_Device::get_arn(408);

		self::push($target_arn, $message);
	}

	public static function push($endpointArn, $alert)
	{

		$client = new SnsClient([
			'region'  => 'ap-northeast-1',
    		'version' => '2010-03-31'
		]);

		$client->publish(array(

        	'TargetArn' => $endpointArn,
        	'MessageStructure' => 'json',

        	'Message' => json_encode(array
	        	(
		        	'APNS_SANDBOX' => json_encode(array
		          	(
		                'aps' => array(
		                    'alert' => $alert,
		                    'sound' => 'default',
		                    'badge' => 1
		            ),
		            // カスタム
		         	//'custom_text' => "$message",
		        	))
		    	)
		    )
        ));

	}
}
