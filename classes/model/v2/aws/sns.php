<?php
/**
 * SNS AWS Model
 * @package    Gocci-Web
 * @version    3.0 <2015/11/25>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */
use Aws\Sns\SnsClient;

class Model_V2_Aws_Sns extends Model
{
    /**
     * @param Int    $user_id
     * @param Int    $register_id
     * @param String $os
     *
     * @return String $endpoint_arn
     */
    public static function postEndPoint($user_id, $register_id, $os)
    {
        // AWS SNSに端末を登録
        if ($brand[0] === 'android') {
            $endpoint_arn = self::postAndroid($user_id, $register_id);
        } else if ($brand[0] === 'iOS') {
            $endpoint_arn = self::postIOS($user_id, $register_id);
        } else {
            exit;
        }
        return $endpoint_arn;
    }

    /**
     * @param Int    $user_id
     * @param Int    $register_id
     *
     * @return Array $result
     */
    private static function postAndroid($user_id, $register_id)
    {
        $android_Arn = Config::get('_sns.android_ApplicationArn');

        $client = new SnsClient([
            'region'  => 'ap-northeast-1',
            'version' => '2010-03-31'
        ]);

        $result = $client->createPlatformEndpoint([
            'CustomUserData'         => 'user_id / ' . $user_id,
            'PlatformApplicationArn' => $android_Arn,
            'Token'                  => $register_id,
        ]);
        return $result['EndpointArn'];
    }

    /**
     * @param Int    $user_id
     * @param Int    $register_id
     *
     * @return Array $result
     */
    private static function postIOS($usr_id, $register_id)
    {
        $iOS_Arn = Config::get('_sns.iOS_ApplicationArn');

        $client = new SnsClient([
            'region'  => 'ap-northeast-1',
            'version' => '2010-03-31'
        ]);

        $result = $client->createPlatformEndpoint([
            'CustomUserData'         => 'user_id / ' . $user_id,
            'PlatformApplicationArn' => $iOS_Arn,
            'Token'                  => $register_id,
        ]);

        return $result['EndpointArn'];
    }

    /**
     * @param String $keyword
     * @param Int    $user_id
     * @param Int    $target_user_id
     */
    public static function postMessage($keyword, $user_id, $target_user_id)
    {
        $username   = Model_V2_DB_User::getName($user_id);
        $target_arn = Model_V2_DB_User::getArn($target_user_id);
        $message = $username . 'さんから' . $keyword . 'されました!';

        $client = new SnsClient([
            'region'  => 'ap-northeast-1',
            'version' => '2010-03-31'
        ]);
        $result = $client->publish([
            'Message'   => $message,
            'TargetArn' => $target_arn,
        ]);
    }

    /**
     * @param Int    $user_id
     * @param String $message
     */
    public static function postPublish($user_id, $message)
    {
        $target_an = Model_V2_DB_Device::getArn($user_id);

        $client = new SnsClient([
            'region'  => 'ap-northeast-1',
            'version' => '2010-03-31'
        ]);

        $result = $client->publish([
            'Message'   => $message,
            'TargetArn' => $target_arn,
        ]);
    }

    /**
     * @param String $endpoint_arn
     */
    public static function deleteEndPoint($endpoint_arn)
    {
        $client = new SnsClient([
            'region'  => 'ap-northeast-1',
            'version' => '2010-03-31'
        ]);

        $result = $client->deleteEndPoint([
            'EndpointArn' => $endpoint_arn,
        ]);
    }
}