<?php

use Aws\CognitoIdentity\CognitoIdentityClient;
use Aws\CognitoSync\CognitoSyncClient;

/**
* CognitoIdentity Model
*/
class Model_Cognito extends Model
{
    //IdentityID取得 DataSet [User_Info]
	public static function post_data($user_id, $username, $os, $model, $register_id)
	{
		$cognito_data = Config::get('_cognito');

        $client = new CognitoIdentityClient([
            'region'  => 'us-east-1',
            'version' => 'latest'
        ]);

        $result = $client->getOpenIdTokenForDeveloperIdentity([
            'IdentityPoolId' => "$cognito_data[IdentityPoolId]",
            'Logins' => [
                "$cognito_data[developer_provider]" => "$user_id",
            ],
        ]);

        $identity_id = $result['IdentityId'];

		//CognitoSync Dataset 外部処理
        // $ch = curl_init();

        // curl_setopt($ch, CURLOPT_URL,
        //     'http://localhost/v1/mobile/background/dataset/'
        //         .'?identity_id='. "$identity_id"
        //         .'&username='   . "$username"
        //         .'&os='         . "$os"
        //         .'&model='      . "$model"
        //         .'&register_id='. "$register_id"
        // );
        // curl_exec($ch);
        // curl_close($ch);

		return $result;
	}


    //SNS連携
    public static function post_sns($user_id, $identity_id, $provider, $token)
    {
        $cognito_data = Config::get('_cognito');

        $client = new CognitoIdentityClient([
            'region'  => 'us-east-1',
            'version' => 'latest'
        ]);

        $result = $client->getOpenIdTokenForDeveloperIdentity([
            'IdentityId'     => "$identity_id",
            'IdentityPoolId' => "$cognito_data[IdentityPoolId]",
            'Logins'         => [
                "$cognito_data[developer_provider]" => "$user_id",
                "$provider" => "$token",
            ],
        ]);

        return $result;
    }


	//identity_idからtokenを取得
	public static function get_token($user_id, $identity_id)
	{
        $cognito_data = Config::get('_cognito');

		$client = new CognitoIdentityClient([
			'region'  => 'us-east-1',
    		'version' => 'latest'
		]);

        $result = $client->getOpenIdTokenForDeveloperIdentity([
            'IdentityId'     => "$identity_id",
            'IdentityPoolId' => "$cognito_data[IdentityPoolId]",
            'Logins'         => [
                "$cognito_data[developer_provider]" => "$user_id",
            ],
        ]);

		return $result['Token'];
	}


    //DataSet
	public static function dataset(
		$identity_id, $username, $os, $model, $register_id)
	{
        $IdentityPoolId = Config::get('_cognito.IdentityPoolId');

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


        $result = $client->updateRecords([
            'DatasetName'    => 'user_info',
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



    //=========================================================================//


    //identity_id取得
    public static function get_identity_id($provider, $token)
    {
        $IdentityPoolId = Config::get('_cognito.IdentityPoolId');

        $client = new CognitoIdentityClient([
            'region'  => 'us-east-1',
            'version' => 'latest'
        ]);

        $result = $client->getOpenIdTokenForDeveloperIdentity([
            'IdentityPoolId' => "$IdentityPoolId",
            'Logins' => [
                "$provider" => "$token",
            ],
        ]);
        return $result['identity_id'];
    }


    //SNS連携
    public static function post_dev_sns(
        $user_id, $provider, $token, $username, $os, $model, $register_id)
    {
        $cognito_data = Config::get('_cognito');

        $client = new CognitoIdentityClient([
            'region'  => 'us-east-1',
            'version' => 'latest'
        ]);

        $result = $client->getOpenIdTokenForDeveloperIdentity([
            'IdentityPoolId' => "$cognito_data[IdentityPoolId]",
            'Logins'         => [
                "$cognito_data[developer_provider]" => "$user_id",
                "$provider" => "$token",
            ],
        ]);

        $identity_id = $result['IdentityId'];

        //CognitoSync Dataset 外部処理
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,
            'http://localhost/v1/mobile/background/dataset/'
                .'?identity_id='. "$identity_id"
                .'&username='   . "$username"
                .'&os='         . "$os"
                .'&model='      . "$model"
                .'&register_id='. "$register_id"
        );
        curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}