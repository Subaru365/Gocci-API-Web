<?php

use Aws\Sns\SnsClient;
/**
*未完成 Protocolってなんだ！？
*/
class Controller_Sns extends Controller
{
	public function action_index()
	{
		$client = new SnsClient([
			'region'  => 'ap-northeast-1',
    		'version' => '2010-03-31'
		]);


		$result = $client->createPlatformEndpoint([
    		'CustomUserData' => 'Akira Murata',
    		'PlatformApplicationArn' => 'arn:aws:sns:ap-northeast-1:318228258127:app/GCM/gocci-android',
    		'Token' => 'APA91bFQ__HTysJozZvxF3lw7zTyi-f6cpfuMq6jEkwIdXzZ6-hvUMBSOPqDzZ8BQrZaWHFzPmaIjbC8QWH2p77Nw0ehsCIAoxr0RpwwSqnqlEW3qUSBOuslGKdgKmokzrnDscfec1XmciVXDw-VNk1R5w2TzvHA7g',
    	]);
		/*

		$result = $client->subscribe([
    		'Endpoint' => "APA91bFQ__HTysJozZvxF3lw7zTyi-f6cpfuMq6jEkwIdXzZ6-hvUMBSOPqDzZ8BQrZaWHFzPmaIjbC8QWH2p77Nw0ehsCIAoxr0RpwwSqnqlEW3qUSBOuslGKdgKmokzrnDscfec1XmciVXDw-VNk1R5w2TzvHA7g",
    		'Protocol' => '',
    		'TopicArn' => 'arn:aws:sns:ap-northeast-1:318228258127:app/GCM/gocci-android',
		]);


	*/
	}


}

