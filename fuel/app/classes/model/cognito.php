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


    public static function delete_identity_id($user_id, $identity_id, $provider, $token)
    {
        $developer_provider = Config::get('_cognito.developer_provider');

        $client = new CognitoIdentityClient([
            'region'  => 'us-east-1',
            'version' => 'latest'
        ]);

        $result = $client->unlinkIdentity([
            'IdentityId' => "$identity_id",
            'Logins' => ["$developer_provider" => "$user_id"],
            'LoginsToRemove' => ["$provider" => "$token"],
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
        return $result['IdentityId'];
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

        return $result;
    }
}