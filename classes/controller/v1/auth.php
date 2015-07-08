<?php

header('Content-Type: application/json; charset=UTF-8');
error_reporting(-1);
/**
 * Auth api
 *
 *         //$time_start = microtime(true);
 * //debug
 *       //$timelimit = microtime(true) - $time_start;
 *       //echo '格納完了：' . $timelimit . ' seconds\r\n';
 */

class Controller_V1_Auth extends Controller
{


    //SNSサインイン
    public function action_sns()
    {
        $keyword     = 'SNS';
        $token       = 'none';

        $identity_id = Input::get('identity_id');
        $profile_img = Input::get('profile_img');

        $user_data   = Model_Cognito::get_data($identity_id);
        $model       = $user_data['Records'][0]['Value'];
        $os          = $user_data['Records'][1]['Value'];
        $register_id = $user_data['Records'][2]['Value'];
        $username    = $user_data['Records'][3]['Value'];


        $user_id     = Model_User::check_id($identity_id);


        //初回サインイン
        if (empty($user_id)) {

            $user_id = Model_User::get_next_id();

            $status  = Controller_V1_Auth::signup(
                $keyword, $user_id, $username, $profile_img,
                $os, $model, $register_id, $identity_id);


        //以前の利用履歴あり
        }else{

            $user_id = $user_id[0]['user_id'];


            //UserData Update 外部処理
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL,
            'http://localhost/v1/background/user/update/?' .
                'identity_id=' . "$identity_id" . '&' .
                'user_id='     . "$user_id"     . '&' .
                'username='    . "$username"    . '&' .
                'os='          . "$os"          . '&' .
                'model='       . "$model"       . '&' .
                'register_id=' . "$register_id"
            );
            curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);

            curl_exec($ch);
            curl_close($ch);


            $badge_num = Model_User::get_badge($user_id);

            $status = Controller_V1_Auth::success(
                $keyword, $user_id, $username,
                $profile_img, $identity_id, $badge_num, $token);
        }

        echo "$status";
    }


    //Guestサインイン
    public function action_guest()
    {
        $keyword     = 'Guest';
        $profile_img = 'none';
        $user_id     = Model_User::get_next_id();

        $username    = Input::get('username');
        $os          = Input::get('os');
        $model       = Input::get('model');
        $register_id = Input::get('register_id');


        $check_device = Model_Device::check_device($register_id);

        if (empty($check_device)) {


        }else{

            $old_data = Model_Device::get_old_data($register_id);
            $user_id_old = $old_data[0]['device_user_id'];
            $endpoint_arn= $old_data[0]['endpoint_arn'];

            $tmp = Model_Sns::delete_endpoint($endpoint_arn);
            $tmp = Model_Device::delete_device($user_id_old);
        }


        $cognito_data = Model_Cognito::post_data(
            $user_id, $username, $os, $model, $register_id);

        $identity_id = $cognito_data['IdentityId'];
        $token       = $cognito_data['Token'];


        $status = Controller_V1_Auth::signup(
            $keyword, $user_id, $username, $profile_img,
            $os, $model, $register_id, $identity_id, $token);

        echo "$status";
    }


    //ログイン
    public function action_welcome()
    {
        $keyword     = 'セッション';
        $token       = 'none';
        $identity_id = Input::get('identity_id');
        $sns_flag    = Input::get('sns_flag');

        $user_data   = Model_User::get_auth($identity_id);

        $user_id     = $user_data['user_id'];
        $username    = $user_data['username'];
        $profile_img = $user_data['profile_img'];
        $badge_num   = $user_data['badge_num'];

        if ($sns_flag == 0) {
            $token = Model_Cognito::get_token($user_id, $identity_id);
        }

        $login = Model_Login::post_login($user_id);

        $status = Controller_V1_Auth::success(
            $keyword, $user_id, $username,
            $profile_img, $identity_id, $badge_num, $token);

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


            $endpoint_arn = Model_Sns::post_endpoint(
                $user_id, $identity_id, $register_id, $os);


            //Device情報を登録
            $device = Model_Device::post_data(
                $user_id, $os, $model, $register_id, $endpoint_arn);

            //success出力へ
            $status = Controller_V1_Auth::success(
                $keyword, $user_id, $username,
                $profile_img, $identity_id, $badge_num, $token);
        }


        //データベース登録エラー
        catch(\Database_Exception $e)
        {
            //failed出力へ
            $status = Controller_V1_Auth::failed(
                $keyword, $user_id, $username,
                $profile_img, $identity_id, $badge_num, $token);

            error_log($e);
        }

        return $status;
    }


    //DBデータ入力成功
    private static function success(
        $keyword, $user_id, $username,
        $profile_img, $identity_id, $badge_num, $token)
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
    private static function failed(
        $keyword, $user_id, $username,
        $profile_img, $identity_id, $badge_num, $token)
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


        //初期化ユーザー
        if (empty($user_id)) {
            $user_id     = Model_User::get_next_id();

            //IdentityID取得
            $identity_id = Model_Cognito::post_data(
                $user_id, $username, $os, $model, $register_id);

            $status = Controller_V1_Auth::signup(
                $keyword, $user_id, $username, $profile_img,
                $os, $model, $register_id, $identity_id);


        //VIPユーザー
        }else{
            $user_id = $user_id[0]['user_id'];

            //IdentityID取得
            $identity_id = Model_Cognito::post_data(
                $user_id, $username, $os, $model, $register_id);


            try{

                $user_data = Model_User::update_data(
                    $user_id, $username, $profile_img, $identity_id);

                $endpoint_arn = Model_Sns::post_endpoint(
                    $user_id, $identity_id, $register_id);

                //Device情報を登録
                $device = Model_Device::update_data(
                    $user_id, $os, $model, $register_id, $endpoint_arn);

                //success出力へ
                $status = Controller_V1_Auth::success(
                    $keyword, $user_id, $username,
                    $profile_img, $identity_id, $badge_num);
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