<?php
error_reporting(-1);

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

	public function action_remote()
	{

		// 新しい cURL リソースを作成します
		$ch = curl_init();

		// URL や他の適当なオプションを設定します
		curl_setopt($ch, CURLOPT_URL, "http://localhost/sns/push");
		curl_setopt($ch, CURLOPT_HEADER, 0);

		// URL を取得し、ブラウザに渡します
		curl_exec($ch);

		echo "start!";

		// cURL リソースを閉じ、システムリソースを解放します
		curl_close($ch);

	}

	public function action_push()
	{

		//$keyword    = $argv[1];
		//$user_name  = Model_User::get_name($argv[2]);
		$target_arn = 'arn:aws:sns:ap-northeast-1:318228258127:endpoint/GCM/gocci-android/d75c9dd6-0241-3b4b-9da8-f7f95fcf539d';


		$client = new SnsClient([
			'region'  => 'ap-northeast-1',
   			'version' => '2010-03-31'
		]);

		$result = $client->publish([
    		'Message' => '{"message":"コメント","badge":"4"}',
    		/*
    		'MessageAttributes' => [
        		'<String>' => [
            		//'BinaryValue' => <Psr\Http\Message\StreamableInterface>,
            		'DataType' => 'raw', // REQUIRED
            		//'StringValue' => '<string>',
    			],
			],
    		*/
    		//'MessageStructure' => '<string>',
   			//'Subject' => '<string>',
   			'TargetArn' => "$target_arn",
   			//'TopicArn' => '<string>',
		]);

	}

}

