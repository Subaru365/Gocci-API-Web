<?php

use Aws\CognitoIdentity\CognitoIdentityClient;
use Aws\CognitoSync\CognitoSyncClient;

//header('Content-Type: application/json; charset=UTF-8');
/**
*
*/
class Controller_Cognito extends Controller
{
	public function action_guest()
	{

		$username	  = Input::get('username');
		$device_os 	  = Input::get('os');
		$device_model = Input::get('model');
		$device_token = Input::get('token');

		$user_id = Model_user::post_guest(
			"$username", "$device_os", "$device_model", "$device_token");


		$client = new CognitoIdentityClient([
			'region'  => 'us-east-1',
    		'version' => 'latest'
		]);

		$result = $client->getOpenIdTokenForDeveloperIdentity([
    		'IdentityPoolId' => 'us-east-1:a8cc1fdb-92b1-4586-ba97-9e6994a43195',
   			'Logins' => ['login.inase.gocci'=> "$user_id",],
		]);



		//print_r($result);
		//us-east-1:e69b1194-01ee-4917-8a94-646a4fb88500
		//us-east-1:00b7c24e-bc59-413a-a2fa-8ba88d473ae1


		//us-east-1:443794bb-7e0d-4110-9789-a6e26ffe7f0c

	}

	public function action_sns()
	{
		$identity_id = Input::get('id');

		$client = new CognitoSyncClient([
			'region'  => 'us-east-1',
    		'version' => 'latest'
		]);

		$result = $client->listRecords([
    		'DatasetName' 	 => 'user_info',
    		'IdentityId' 	 => "$id",
    		'IdentityPoolId' => 'us-east-1:a8cc1fdb-92b1-4586-ba97-9e6994a43195'
		]);


		$user_id  = $result['Records']['0']['Value'];
		$username = $result['Records']['1']['Value'];
		//$hoge	  = $result['Records']['3']['Value'];

		//echo "$user_id";

	}

	public function action_index()
	{
		phpinfo();
	}


}

