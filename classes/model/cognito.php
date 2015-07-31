<?php

use Aws\CognitoIdentity\CognitoIdentityClient;
use Aws\CognitoSync\CognitoSyncClient;

/**
* CognitoIdentity Model
*/
class Model_Cognito extends Model
{
    //public $IdentityPoolId = 'us-east-1:2ef43520-856b-4641-b4a1-e08dfc07f802';

    //IdentityID取得 DataSet [User_Info]
	public static function post_data($user_id, $username, $os, $model, $register_id)
	{
		$IdentityPoolId = 'us-east-1:2ef43520-856b-4641-b4a1-e08dfc07f802';

		$client = new CognitoIdentityClient([
			'region'  => 'us-east-1',
    		'version' => 'latest'
		]);

		//Identity_ID作成
		$result = $client->getOpenIdTokenForDeveloperIdentity([
    		'IdentityPoolId' => "$IdentityPoolId",
   			'Logins' => ['test.login.gocci'=> "$user_id",],
		]);
        $identity_id = $result['IdentityId'];

		//CognitoSync Dataset 外部処理
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,
            'http://localhost/v1/background/dataset/?' .
                'identity_id=' . "$identity_id" . '&' .
                'username='    . "$username"    . '&' .
                'os='          . "$os"          . '&' .
                'model='       . "$model"       . '&' .
                'register_id=' . "$register_id"
        );
        curl_exec($ch);
        curl_close($ch);

		return $result;
	}


	//identity_idからtokenを取得
	public static function get_token($user_id, $identity_id)
	{
		$client = new CognitoIdentityClient([
			'region'  => 'us-east-1',
    		'version' => 'latest'
		]);

        $result = $client->getOpenIdTokenForDeveloperIdentity([
            'IdentityId'     => "$identity_id",
            'IdentityPoolId' => 'us-east-1:2ef43520-856b-4641-b4a1-e08dfc07f802',
            'Logins'         => ['test.login.gocci' => "$user_id",],
        ]);

		return $result['Token'];
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
    		'IdentityPoolId' => 'us-east-1:2ef43520-856b-4641-b4a1-e08dfc07f802'
		]);

		return $result;
	}


	public static function dataset(
		$identity_id, $username, $os, $model, $register_id)
	{
		$client = new CognitoSyncClient([
            'region'  => 'us-east-1',
            'version' => 'latest'
        ]);

		//SyncSessionToken取得
        $result = $client->listRecords([
            'DatasetName'    => 'user_info',
            'IdentityId'     => "$identity_id",
            'IdentityPoolId' => 'us-east-1:2ef43520-856b-4641-b4a1-e08dfc07f802',
        ]);

        $sync_session_token = $result['SyncSessionToken'];


        //DataSet
        $result = $client->updateRecords([
            'DatasetName'    => 'user_info',
            'IdentityId'     => "$identity_id",
            'IdentityPoolId' => 'us-east-1:2ef43520-856b-4641-b4a1-e08dfc07f802',
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
	}


    public static function delete_identity_id($identity_id)
    {
        $client = new CognitoIdentityClient([
            'region'  => 'us-east-1',
            'version' => 'latest'
        ]);

        $result = $client->deleteIdentities([
            'IdentityIdsToDelete' => ["$identity_id"],
        ]);
    }
}

