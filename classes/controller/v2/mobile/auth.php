<?php
header('Content-Type: application/json; charset=UTF-8');

/**
 * Auth api
 *
 * // $time_start = microtime(true);
 * // debug
 * // $timelimit = microtime(true) - $time_start;
 * // echo '格納完了：' . $timelimit . ' seconds\r\n';
 *
 */

class Controller_V2_Mobile_Auth extends Controller
{
    public function before()
    {
        $user_data = Input::get();
    }

    public function action_signup()
    {
        //$user_data[username, os, model, register_id]
        Model_V2_Validation::check_signup($user_data);

        $user_data['user_id'] = Model_V2_Db_User::get_user_id_next());

        $cognito_data = Model_V2_Aws_Cognito::set_data($user_data['user_id']);
        $user_data['identity_id']   = $cognito_data['IdentityId'];
        $user_data['token']         = $cognito_data['Token'];

        $user_data['profile_img']   = Model_V2_Db_User::set_data($user_data);
        $user_data['endpoint_arn']  = Model_V2_Aws_Sns::set_endpoint($user_data);

        Model_Device::post_data($user_id, $os, $model, $register_id, $endpoint_arn);

        self::success($user_data);
    }


    // ログイン
    public function action_login()
    {
        Model_Validation::check_login($user_data);

        try
        {
            $user_data   = Model_User::get_auth($identity_id);
            $user_id     = $user_data['user_id'];
            $username    = $user_data['username'];
            $profile_img = $user_data['profile_img'];
            $badge_num   = $user_data['badge_num'];

            $token = Model_Cognito::get_token($user_id, $identity_id);

            Model_Login::post_login($user_id);
            self::success($keyword, $user_id, $username, $profile_img, $identity_id, $badge_num, $token);
        }

        // データベース登録エラー
        catch(\Database_Exception $e)
        {
            self::failed($keyword, $user_id, $username, $profile_img, $identity_id, $badge_num);
            error_log($e);
        }
    }


    public static function action_sns_login()
    {
        $keyword     = 'ログイン';
        $identity_id = Input::get('identity_id');
        $os          = Input::get('os');
        $model       = Input::get('model');
        $register_id = Input::get('register_id');

        try
        {
            $user_data   = Model_User::get_auth($identity_id);
            $user_id     = $user_data['user_id'];
            $username    = $user_data['username'];
            $profile_img = $user_data['profile_img'];
            $badge_num   = $user_data['badge_num'];


            $token = Model_Cognito::get_token($user_id, $identity_id);

            $old_endpoint_arn = Model_Device::get_arn($user_id);
            Model_Sns::delete_endpoint($old_endpoint_arn);

            $new_endpoint_arn = Model_Sns::post_endpoint($user_id, $register_id, $os);
            Model_Device::update_data($user_id, $os, $model, $register_id, $new_endpoint_arn);

            Model_Login::post_login($user_id);
            self::success($keyword, $user_id, $username, $profile_img, $identity_id, $badge_num, $token);
        }

        // データベース登録エラー
        catch(\Database_Exception $e)
        {
            self::failed($keyword, $user_id, $username, $profile_img, $identity_id, $badge_num);
            error_log($e);
        }
    }


    public static function action_pass_login()
    {
        try
        {
            if (!empty($pass)) {
                $user_data   = Model_User::check_pass($username, $pass);
                $user_id     = $user_data[0]['user_id'];
                $profile_img = $user_data[0]['profile_img'];
                $identity_id = $user_data[0]['identity_id'];
                $badge_num   = $user_data[0]['badge_num'];


                $token = Model_Cognito::get_token($user_id, $identity_id);

                $old_endpoint_arn = Model_Device::get_arn($user_id);
                Model_Sns::delete_endpoint($old_endpoint_arn);

                $new_endpoint_arn = Model_Sns::post_endpoint($user_id, $register_id, $os);
                Model_Device::update_data($user_id, $os, $model, $register_id, $new_endpoint_arn);

                Model_Login::post_login($user_id);
                self::success($keyword, $user_id, $username, $profile_img, $identity_id, $badge_num, $token);


            }else{
                Controller_V1_Mobile_Base::output_none();
                error_log('パスワード未入力です');
            }
        }

        // データベース登録エラー
        catch(\Database_Exception $e)
        {
            self::failed($keyword, $user_id, $username, $profile_img, $identity_id, $badge_num);
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
        $badge_num,
        $token
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
            'token'       => "$token"
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
        $badge_num,
        $token
    )
    {
        $data = [
            'code'        => 401,
            'message'     => "$keyword" . 'できませんでした。',
            'username'    => "$username",
            'profile_img' => "$profile_img",
            'identity_id' => "$identity_id",
            'badge_num'   => "$badge_num"
        ];

        Controller_V1_Mobile_Base::output_json($data);
    }