<?php
use Aws\Sns\SnsClient;
/**
 * SNS Class
 * @package    Gocci-Web
 * @version    3.0 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */
class Model_Sns extends Model
{
    /**
     * @param Int    $user_id
     * @param Int    $register_id
     * @param String $os
     *
     * @return String $endpoint_arn
     */

    /**
     * @var Instance $client
     */
    private $client;

    /**
     * @var Instance $type
     */
    public $type;

    public static function post_endpoint($user_id, $register_id, $os)
    {
        // AWS SNSに端末を登録
        $brand = explode('_', $os);

        if ($brand[0] == 'android') {
            $endpoint_arn = self::post_android($user_id, $register_id);
        } elseif ($brand[0] == 'iOS') {
            $endpoint_arn = self::post_iOS($user_id, $register_id);
        } else {
            error_log('Model_Sns: endpoint_arn 未発行');
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
    private static function post_android($user_id, $register_id)
    {
        $android_Arn = Config::get('_sns.android_ApplicationArn');

        $client = new SnsClient([
            'region'  => 'ap-northeast-1',
            'version' => '2010-03-31'
        ]);

        $result = $client->createPlatformEndpoint([
            'CustomUserData' => 'user_id / ' . "$user_id",
            'PlatformApplicationArn' => "$android_Arn",
            'Token' => "$register_id",
        ]);
        return $result['EndpointArn'];
    }

    /**
     * @param Int    $user_id
     * @param Int    $register_id
     *
     * @return Array $result
     */
    private static function post_iOS($user_id, $register_id)
    {
        $iOS_Arn = Config::get('_sns.iOS_ApplicationArn');

        $client = new SnsClient([
            'region'  => 'ap-northeast-1',
            'version' => '2010-03-31'
        ]);

        $result = $client->createPlatformEndpoint([
            'CustomUserData' => 'user_id / ' . "$user_id",
            'PlatformApplicationArn' => "$iOS_Arn",
            'Token' => "$register_id",
        ]);

        return $result['EndpointArn'];
    }

    /**
     * @param String $keyword
     * @param Int    $user_id
     * @param Int    $target_user_id
     */
    public static function post_message($keyword, $user_id, $target_user_id)
    {
        $username  = Model_User::get_name($user_id);
        $target_arn = Model_Device::get_arn($target_user_id);
        // $message = "$username" . 'さんから' . "$keyword" . 'されました！';

        $message = [
            'type'     => $this->type,
            'id'       => $target_user_id,
            'username' => $username
        ];

        $client = new SnsClient([
            'region'  => 'ap-northeast-1',
            'version' => '2010-03-31'
        ]);
        $result = $client->publish([
            'Message'   => "$message",
            'TargetArn' => "$target_arn",
        ]);
    }

    /**
     * @param Int    $user_id
     * @param String $message
     */
    public static function post_publish($user_id, $message)
    {
        $target_arn = Model_Device::get_arn($user_id);

        $client = new SnsClient([
            'region'  => 'ap-northeast-1',
            'version' => '2010-03-31'
        ]);

        $result = $client->publish([
            'Message'   => "$message",
            'TargetArn' => "$target_arn",
        ]);
    }

    /**
     * @param String $endpoint_arn
     */
    public static function delete_endpoint($endpoint_arn)
    {
        $client = new SnsClient([
                'region'  => 'ap-northeast-1',
                'version' => '2010-03-31'
        ]);

        $result = $client->deleteEndpoint([
            'EndpointArn' => "$endpoint_arn",
        ]);
    }
}
