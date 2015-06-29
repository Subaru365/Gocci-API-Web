<?php

use Aws\Sns\SnsClient;
/**
*SNS
*/
class Controller_V1_Background_Sns extends Controller
{

    //android デバイス登録
    public static function action_post_android($user_id, $identity_id, $register_id)
    {
        $client = new SnsClient([
            'region'  => 'ap-northeast-1',
            'version' => '2010-03-31'
        ]);

        $result = $client->createPlatformEndpoint([
            'CustomUserData'
                => 'user_id:' . "$user_id" . ' / ' . "$identity_id",

            'PlatformApplicationArn'
                => 'arn:aws:sns:ap-northeast-1:318228258127:app/GCM/gocci-android',

            'Token'
                => "$register_id",
        ]);

        return $result['EndpointArn'];
    }




    public function action_push()
    {
        $keyword   = Input::get('keyword');
        $a_user_id = Input::get('a_user_id');
        $p_user_id = Input::get('p_user_id');


        $user_name  = Model_User::get_name($a_user_id);
        $target_arn = Model_Device::get_arn($p_user_id);


        $client = new SnsClient([
            'region'  => 'ap-northeast-1',
            'version' => '2010-03-31'
        ]);

        $result = $client->publish([
            'Message'   => "$username" . 'さんから' . "$keyword" . 'されました。',
            'TargetArn' => "$target_arn",
        ]);

        exit;
    }
}

