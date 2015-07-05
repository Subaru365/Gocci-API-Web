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




    public function action_publish()
    {
        $keyword   = Input::get('keyword');
        $a_user_id = Inpur::get('a_user_id');
        $p_user_id = Input::get('p_user_id');

        $login_flag = Model_User::get_login($p_user_id);

            if ($check_login == '1') {

                //ログイン中
                $result = Model_Sns::post_message(
                    $keyword, $a_user_id, $p_user_id);


            }else{
                //ログアウト中
            }
        exit;
    }
}

