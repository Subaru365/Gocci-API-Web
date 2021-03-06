<?php
/**
 * Cognito Class
 * @package    Gocci-Web
 * @version    3.0 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

use Aws\CognitoIdentity\CognitoIdentityClient;
use Aws\CognitoSync\CognitoSyncClient;

class Model_Cognito extends Model
{
    use GocciAPI;
    /**
     * IdentityID取得 DataSet [User_Info]
     * @param  Int $user_id
     * @return Array $result
     */
    public static function post_data($user_id)
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

    /**
     * SNS連携
     * @param Int $user_id
     * @param Int $identity_id
     * @param String $provider
     * @param String $token
     *
     * @return Array $result
     */
    public static function post_sns($user_id, $identity_id, $provider, $token)
    {
        $cognito_data = Config::get('_cognito');

        $client = new CognitoIdentityClient([
            'region'  => 'us-east-1',
            'version' => 'latest'
        ]);
        error_log('identity_id');
        error_log($identity_id);

        error_log('post_sns内');
        $result = $client->getOpenIdTokenForDeveloperIdentity([
            'IdentityId'     => "$identity_id",
            'IdentityPoolId' => "$cognito_data[IdentityPoolId]",
            'Logins'         => [
                "$cognito_data[developer_provider]" => "$user_id",
                "$provider" => "$token",
            ],
        ]);
        error_log('returnします');
        return $result;
    }

    /**
     * Web SNS登録&連携
     * @param Int $user_id
     * @param String $provider
     * @param String $token
     *
     * @return Array $result
     */
    public static function post_web_sns($user_id, $provider, $token)
    {
        error_log('user_id in cognito: ');
        error_log($user_id);

        error_log('provider in cognito: ');
        error_log($provider);

        error_log('token in cognito: ');
        error_log($token);


        error_log('cognito_dataは');
        $cognito_data = Config::get('_cognito');

        // error_log('cognito_data:');
        /**
         * (
         * [IdentityPoolId] => us-east-1:b563cebf-1de2-4931-9f08-da7b4725ae35
         *  [developer_provider] => test.login.gocci
         * )
         *
         */
        // error_log(print_r($cognito_data, true));

        $client = new CognitoIdentityClient([
            'region'  => 'us-east-1',
            'version' => 'latest'
        ]);

        error_log('getOpenIdTokenForDeveloperIdentity呼びます');

        $result = $client->getOpenIdTokenForDeveloperIdentity([
            'IdentityPoolId' => "$cognito_data[IdentityPoolId]",
            'Logins'         => [
                "$cognito_data[developer_provider]" => "$user_id",
                "$provider" => "$token",
            ],
        ]);

        error_log('returnします');
        return $result;
    }
     /**
      * identity_idからtokenを取得
      * @param Int $user_id
      * @param Int $identity_id
      *
      * @return Array $result
      */
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

    /**
     * identity_id削除
     * @param Int $identity_id
     */
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

    /**
     * identity_id削除
     * @param Int $user_id
     * @param Int $identity_id
     * @param String $provider
     * @param String $token
     */
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

    /**
     * identity_id取得
     * @param String $provider
     * @param String $token
     *
     * @return Array $result
     */
    public static function get_identity_id($provider, $token)
    {
        error_log('Cognito Model内1');
        $IdentityPoolId = Config::get('_cognito.IdentityPoolId');
        error_log('IdentityPoolId: ');
        error_log($IdentityPoolId);
        error_log('Cognito Model内2');
        $client = new CognitoIdentityClient([
            'region'  => 'us-east-1',
            'version' => 'latest'
        ]);

        error_log('Cognito Model内3');

        try {



            $result = $client->getOpenIdTokenForDeveloperIdentity([
                'IdentityPoolId' => "$IdentityPoolId",
                'Logins' => [
                    "$provider" => "$token",
                ],
            ]);



            return $result['IdentityId'];
        } catch (Exception $e) {
            // $data = ["message" => "例外が発生しました"];
            error_log('例外が発生しました...');
            //error_log($e);
            // exit;
        }
    }

    /**
     * SNS連携
     * @param Int $user_id
     * @param String $provider
     * @param String $token
     * @param String $username
     * @param String $os
     * @param String $model
     * @param Int $register_id
     *
     * @return Array $result
     */
    public static function post_dev_sns(
        $user_id, $provider, 
        $token, $username, 
        $os, $model, $register_id)
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