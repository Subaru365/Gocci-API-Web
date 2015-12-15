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
            // 例外が発生する際、$resultには何が入っているのか
            $result = $client->getOpenIdTokenForDeveloperIdentity([
                'IdentityPoolId' => "$IdentityPoolId",
                'Logins' => [
                    "$provider" => "$token",
                ],
            ]);

            if (!$result) {
                return $result = [];
            } else {
                error_log('result IdentityId');
                error_log($result);
                return $result['IdentityId'];
            }
        } catch (Exception $e) {
            error_log('例外が発生しました');
            exit;
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