<?php

use Aws\CognitoIdentity\CognitoIdentityClient;
use Aws\CognitoSync\CognitoSyncClient;

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

        /*

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
    		'DatasetName' 	 => 'user_info',
    		'IdentityId'     => "$identity_id",
    		'IdentityPoolId' => "$IdentityPoolId",
    		'RecordPatches'  => [
        		[
            		'Key' => 'username',
            		'Op' => 'replace',
            		'SyncCount' => 0,
            		'Value' => "$username",
        		],
        		[
            		'Key' => 'os',
            		'Op' => 'replace',
            		'SyncCount' => 0,
            		'Value' => "$os",
        		],
        		[
            		'Key' => 'model',
            		'Op' => 'replace',
            		'SyncCount' => 0,
            		'Value' => "$model",
        		],
        		[
            		'Key' => 'register_id',
            		'Op' => 'replace',
            		'SyncCount' => 0,
            		'Value' => "$register_id",
        		],
		    ],
    		'SyncSessionToken' => "$sync_session_token",
		]);

*/

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

		return $result;
	}

}

