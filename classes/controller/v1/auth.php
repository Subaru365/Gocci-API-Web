<?php

header('Content-Type: application/json; charset=UTF-8');
error_reporting(-1);
/**
 * Auth api
 *
 */

class Controller_V1_Auth extends Controller
{


    //SNSサインイン
    public function action_sns()
    {
        //debug
        //$time_start = microtime(true);


        $keyword     = 'SNS';
        $identity_id = Input::get('identity_id');
        $profile_img = Input::get('profile_img');

        $user_id     = Model_User::get_id();

        $user_data   = Model_Cognito::get_data($identity_id);
        $model       = $user_data['Records'][0]['Value'];
        $os          = $user_data['Records'][1]['Value'];
        $register_id = $user_data['Records'][2]['Value'];
        $username    = $user_data['Records'][3]['Value'];

        //debug
        //$timelimit = microtime(true) - $time_start;
        //echo '格納完了：' . $timelimit . ' seconds\r\n';


        $status = Controller_V1_Auth::signup(
            $keyword, $user_id, $username, $profile_img,
            $os, $model, $register_id, $identity_id);

        //debug
        //$timelimit = microtime(true) - $time_start;
        //echo '完了：' . $timelimit . ' seconds\r\n';

        echo "$status";
    }


    //Guestサインイン
    public function action_guest()
    {
        //debug
        //$time_start = microtime(true);


        $keyword     = 'Guest';
        $profile_img = 'none';
        $user_id     = Model_User::get_id();

        $username    = Input::get('username');
        $os          = Input::get('os');
        $model       = Input::get('model');
        $register_id = Input::get('register_id');


        $cognito_data  = Model_Cognito::post_data(
            $user_id, $username, $os, $model, $register_id);

        $identity_id = $cognito_data['IdentityId'];
        $token       = $cognito_data['Token'];

        //debug
        //$timelimit = microtime(true) - $time_start;
        //echo '格納完了：' . $timelimit . ' seconds\r\n';


        $status = Controller_V1_Auth::signup(
            $keyword, $user_id, $username, $profile_img,
            $os, $model, $register_id, $identity_id, $token);

        //debug
        //$timelimit = microtime(true) - $time_start;
        //echo '完了：' . $timelimit . ' seconds\r\n';

        echo "$status";
    }


    //ログイン
    public function action_welcome()
    {
        $keyword     = 'セッション';
        //$token       = 'none';
        $identity_id = Input::get('identity_id');

        $user_data   = Model_User::get_auth($identity_id);

        $user_id     = $user_data['user_id'];
        $username    = $user_data['username'];
        $profile_img = $user_data['profile_img'];
        $badge_num   = $user_data['badge_num'];

        $token       = Model_Cognito::get_token($user_id, $identity_id);

        $login = Model_Login::post_login($user_id);

        $status = Controller_V1_Auth::success($keyword, $user_id,
            $username, $profile_img, $identity_id, $badge_num, $token);

        echo "$status";
    }


    //初回データ格納関数 (RDS, SNS)
    private static function signup(
        $keyword, $user_id, $username, $profile_img,
        $os, $model, $register_id, $identity_id, $token = 'none')
    {

        $badge_num = 0;

        try
        {
            $profile_img = Model_User::post_data(
                $username, $profile_img, $identity_id);

            //$device_check = Model_Device::check_id($register_id);


            //AWS SNSに端末を登録
            $brand = explode('_', $os);

            if ($brand[0] == 'android') {
                $endpoint_arn = Model_Sns::post_android(
                    $user_id, $identity_id, $register_id);
            }
            elseif ($brand[0] == 'iOS') {
                $endpoint_arn = Model_Sns::post_iOS(
                    $user_id, $identity_id, $register_id);
            }
            else{
                //Webかな？ 何もしない。
            }

            //Device情報を登録
            $device = Model_Device::post_data(
                $user_id, $os, $model, $register_id, $endpoint_arn);

            //success出力へ
            $status = Controller_V1_Auth::success($keyword, $user_id,
                $username, $profile_img, $identity_id, $badge_num, $token);
        }


        //データベース登録エラー
        catch(\Database_Exception $e)
        {
            //failed出力へ
            $status = Controller_V1_Auth::failed($keyword, $user_id,
                $username, $profile_img, $identity_id, $badge_num, $token);

            error_log($e);
        }

        return $status;
    }


    //DBデータ入力成功
    private static function success($keyword, $user_id,
        $username, $profile_img, $identity_id, $badge_num, $token)
    {
        $result = array(
            'code'        => 200,
            'user_id'     => "$user_id",
            'username'    => "$username",
            'profile_img' => "$profile_img",
            'identity_id' => "$identity_id",
            'badge_num'   => "$badge_num",
            'message'     => "$keyword" . 'でログインしました。',
            'token'       => "$token"
        );

        $status = json_encode(
            $result,
            JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
        );

        session::set('user_id', $user_id);

        return $status;
    }


    //DBデータ入力エラー
    private static function failed($keyword, $user_id,
        $username, $profile_img, $identity_id, $badge_num, $token)
    {
        $result = array(
            'code'        => 401,
            'username'    => "$username",
            'profile_img' => "$profile_img",
            'identity_id' => "$identity_id",
            'badge_num'   => "$badge_num",
            'message'     => "$keyword" . 'でログインできませんでした。',
            'token'       => "$token"
        );

        $status = json_encode(
            $result,
            JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
        );

        return $status;
    }




    //Conversion
    //==========================================================================//

    public function action_conversion()
    {
        $keyword     = '顧客様';

        $username    = Input::get('username');
        $profile_img = Input::get('profile_img');

        $os          = Input::get('os');
        $model       = Input::get('model');
        $register_id = Input::get('register_id');

        $user_id     = Model_User::check_conversion($username);


        //初期ユーザー
        if (empty($user_id)) {
            $user_id     = Model_User::get_id();

            //IdentityID取得
            $identity_id = Model_Cognito::post_data(
                $user_id, $username, $os, $model, $register_id);

            $status = Controller_V1_Auth::signup(
                $keyword, $user_id, $username, $profile_img,
                $os, $model, $register_id, $identity_id);


        //VIPユーザー
        }else {

            //IdentityID取得
            $identity_id = Model_Cognito::post_data(
                $user_id, $username, $os, $model, $register_id);


            try{

                $badge_num = 0;

                $user_data = Model_User::post_data(
                    $username, $profile_img, $identity_id);

                //AWS SNSに端末を登録
                $brand = explode('_', $os);

                if ($brand[0] == 'android') {
                    $endpoint_arn = Model_Sns::post_android(
                        $user_id, $identity_id, $register_id);
                }
                elseif ($brand[0] == 'iOS') {
                    $endpoint_arn = Model_Sns::post_iOS(
                        $user_id, $identity_id, $register_id);
                }
                else{
                    //Webかな？ 何もしない。
                }

                //Device情報を登録
                $device = Model_Device::update_data(
                    $user_id, $os, $model, $register_id, $endpoint_arn);

                //success出力へ
                $status = Controller_V1_Auth::success($keyword,
                    $user_id, $username, $profile_img, $identity_id, $badge_num);
            }

            //データベース登録エラー
            catch(\Database_Exception $e)
            {
                //failed出力へ
                $status = Controller_V1_Auth::failed(
                    $keyword, $username, $profile_img, $identity_id, $badge_num);

                error_log($e);
            }
        }
        echo "$status";
    }


}