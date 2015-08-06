<?php

header('Content-Type: application/json; charset=UTF-8');
error_reporting(-1);
/**
 * Auth api
 *
 */

class Controller_V1_Web_Auth extends Controller
{



    // // サインアップ
    // public function action_signup()
    // {
    //     $keyword     = 'サインアップ';
    //     $badge_num   = 0;
    //     $user_id     = Model_User::get_next_id();
    //     $username    = Input::get('username');
    //     $os          = Input::get('os');
    //     $model       = Input::get('model');
    //     $register_id = Input::get('register_id');

    //     try
    //     {
    //         Model_User::check_name($username);
    //         Model_Device::check_register_id($register_id);

    //         $cognito_data = Model_Cognito::post_data(
    //             $user_id,
    //             $username,
    //             $os,
    //             $model,
    //             $register_id
    //         );

    //         $identity_id  = $cognito_data['IdentityId'];
    //         $token        = $cognito_data['Token'];

    //         $profile_img  = Model_User::post_data($username, $identity_id);

    //         $endpoint_arn = Model_Sns::post_endpoint(
    //             $user_id, $identity_id, $register_id, $os);

    //         Model_Device::post_data(
    //             $user_id, $os, $model, $register_id, $endpoint_arn);

    //         self::success(
    //             $keyword,
    //             $user_id,
    //             $username,
    //             $profile_img,
    //             $identity_id,
    //             $badge_num,
    //             $token
    //         );
    //     }

    //     // データベース登録エラー
    //     catch(\Database_Exception $e)
    //     {
    //         self::failed(
    //             $keyword,
    //             $user_id,
    //             $username,
    //             $profile_img,
    //             $identity_id,
    //             $badge_num,
    //             $token
    //         );

    //         error_log($e);
    //     }
    // }



    // ログイン
    public function action_login()
    {
        $keyword     = 'ログイン';
        $provider    = Input::get('provider');
        $token       = Input::get('token');

        try
        {
            $identity_id = Model_Cognito::get_identity_id($provider, $token);
            $user_data   = Model_User::get_auth($identity_id);
            $user_id     = $user_data['user_id'];
            $username    = $user_data['username'];
            $profile_img = $user_data['profile_img'];
            $badge_num   = $user_data['badge_num'];

            Model_Login::post_login($user_id);

            self::success(
                $keyword,
                $user_id,
                $username,
                $profile_img,
                $identity_id,
                $badge_num
            );

            $sort_key  = 'all';
            $data = Model_Post::get_data($user_id, $sort_key, $sort_key);
        }

        // データベース登録エラー
        catch(\Database_Exception $e)
        {
            self::failed(
                $keyword,
                $user_id,
                $username,
                $profile_img,
                $identity_id,
                $badge_num
            );

            error_log($e);
        }
    }

    // DBデータ入力成功
    private static function success(
        $keyword,
        $user_id,
        $username,
        $profile_img,
        $identity_id,
        $badge_num
    )
    {
        $data = [
            'code'        => 200,
            'message'     => "$keyword" . 'しました。',
            'user_id'     => "$user_id",
            'username'    => "$username",
            'profile_img' => "$profile_img",
            'identity_id' => "$identity_id",
            'badge_num'   => "$badge_num",
        ];

        Controller_V1_Mobile_Base::output_json($data);
        session::set('user_id', $user_id);
    }


    // DBデータ入力エラー
    private static function failed(
        $keyword,
        $user_id,
        $username,
        $profile_img,
        $identity_id,
        $badge_num
    )
    {
        $data = [
            'code'        => 401,
            'message'     => "$keyword" . 'できませんでした。',
            'username'    => "$username",
            'profile_img' => "$profile_img",
            'identity_id' => "$identity_id",
            'badge_num'   => "$badge_num",
        ];

        Controller_V1_Mobile_Base::output_json($data);
    }
}
