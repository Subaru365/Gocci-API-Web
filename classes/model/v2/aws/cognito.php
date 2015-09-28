<?php
/**
* CognitoIdentity Model
*/

use Aws\CognitoIdentity\CognitoIdentityClient;
use Aws\CognitoSync\CognitoSyncClient;

class Model_V2_Aws_Cognito extends Model
{
    private static function set_client()
    {
        $client = new CognitoIdentityClient
        ([
            'region'    => 'us-east-1',
            'version'   => 'latest'
        ]);
        return $client;
    }


    //IdentityID新規発行
	public static function set_data()
	{
        $client = self::client();
		$config = Config::get('_cognito');

        $result = $this->client->getOpenIdTokenForDeveloperIdentity
        ([
            'IdentityPoolId'    => "$config[IdentityPoolId]",
            'Logins'            => [
                "$config[developer_provider]" => session::get('user_id'),
            ]
        ]);
		return $result;
	}


    //identity_idからtokenを取得
    public static function get_token($identity_id)
    {
        $client = self::set_client();
        $config = Config::get('_cognito');

        $result = $client->getOpenIdTokenForDeveloperIdentity([
            'IdentityId'        => "$identity_id",
            'IdentityPoolId'    => "$config[IdentityPoolId]",
            'Logins'            => [
                "$config[developer_provider]" => session::get('user_id'),
            ]
        ]);
        return $result['Token'];
    }


    //=========================================================================//

    //SNS連携
    public static function post_sns($user_id, $identity_id, $provider, $token)
    {
        $cognito_data = Config::get('_cognito');

        $client = new CognitoIdentityClient([
            'region'    => 'us-east-1',
            'version'   => 'latest'
        ]);

        $result = $client->getOpenIdTokenForDeveloperIdentity([
            'IdentityId'        => "$identity_id",
            'IdentityPoolId'    => "$cognito_data[IdentityPoolId]",
            'Logins'            => [
                "$cognito_data[developer_provider]" => "$user_id",
                "$provider" => "$token",
            ],
        ]);

        return $result;
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


    public static function delete_sns($user_id, $identity_id, $provider, $token)
    {
        $developer_provider = Config::get('_cognito.developer_provider');

        $client = new CognitoIdentityClient([
            'region'  => 'us-east-1',
            'version' => 'latest'
        ]);

        $result = $client->unlinkIdentity([
            'IdentityId' => "$identity_id",
            'Logins' => ["$provider" => "$token"],
            'LoginsToRemove' => ["$provider"],
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