<?php

use Aws\CognitoIdentity\CognitoIdentityClient;
use Aws\CognitoSync\CognitoSyncClient;

//header('Content-Type: application/json; charset=UTF-8');
/**
*
*/
class Model_Cognito extends Model
{

	//IdentityID取得 ユーザーデータをDataSet
	public static function post_data($user_id, $username, $os, $model, $register_id)
	{
		$IdentityPoolId = 'us-east-1:a8cc1fdb-92b1-4586-ba97-9e6994a43195';

		$client = new CognitoIdentityClient([
			'region'  => 'us-east-1',
    		'version' => 'latest'
		]);


		//Identity_ID作成
		$result = $client->getOpenIdTokenForDeveloperIdentity([
    		'IdentityPoolId' => "$IdentityPoolId",
   			'Logins' => ['login.inase.gocci'=> "$user_id",],
		]);

		$identity_id = $result['IdentityId'];


		//----------------------------------------------------------//

		$client = new CognitoSyncClient([
			'region'  => 'us-east-1',
    		'version' => 'latest'
		]);


		//SyncSessionToken取得
		$result = $client->listRecords([
    		'DatasetName'    => 'user_info',
    		'IdentityId'     => "$identity_id",
    		'IdentityPoolId' => "$IdentityPoolId",
		]);

		$sync_session_token = $result['SyncSessionToken'];


		//DataSet
		$result = $client->updateRecords([
    		//'ClientContext' => '<string>',
    		//'DeviceId' 	 => '<string>',
    		'DatasetName' 	 => 'user_info',
    		'IdentityId'     => "$identity_id",
    		'IdentityPoolId' => "$IdentityPoolId",
    		'RecordPatches'  => [
        		[
            		//'DeviceLastModifiedDate' => 'YYYY-mm-dd HH:ii:ss',
            		'Key' => 'username',
            		'Op' => 'replace',
            		'SyncCount' => 0,
            		'Value' => "$username",
        		],
        		[
        			//'DeviceLastModifiedDate' => 'YYYY-mm-dd HH:ii:ss',
            		'Key' => 'os',
            		'Op' => 'replace',
            		'SyncCount' => 0,
            		'Value' => "$os",
        		],
        		[
        			//'DeviceLastModifiedDate' => 'q',
            		'Key' => 'model',
            		'Op' => 'replace',
            		'SyncCount' => 0,
            		'Value' => "$model",
        		],
        		[
        			//'DeviceLastModifiedDate' => '2015',
            		'Key' => 'register_id',
            		'Op' => 'replace',
            		'SyncCount' => 0,
            		'Value' => "$register_id",
        		],
		    ],
    		'SyncSessionToken' => "$sync_session_token",
		]);


		return $identity_id;
	}



	//DataSetからユーザー情報を取得
	public static function get_data($identity_id)
	{

		$client = new CognitoSyncClient([
			'region'  => 'us-east-1',
    		'version' => 'latest'
		]);

		$result = $client->listRecords([
    		'DatasetName' 	 => 'user_info',
    		'IdentityId' 	 => "$identity_id",
    		'IdentityPoolId' => 'us-east-1:a8cc1fdb-92b1-4586-ba97-9e6994a43195'
		]);

		for ($i=0; $i < 4; $i++) {
			$user_data[$i] = $result['Records']["$i"]['Value'];
		}

		return $user_data;

	}


	public function action_index()
	{
		phpinfo();
	}


}

