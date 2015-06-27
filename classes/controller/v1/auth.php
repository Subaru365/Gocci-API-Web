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
        $time_start = microtime(true);
        //

        $keyword     = 'SNS';
        $user_id     = Model_User::get_id();

        $identity_id = Input::get('identity_id');
        $profile_img = Input::get('profile_img');

        $user_data   = Model_Cognito::get_data($identity_id);
        $model       = $user_data['Records'][0]['Value'];
        $os          = $user_data['Records'][1]['Value'];
        $register_id = $user_data['Records'][2]['Value'];
        $username    = $user_data['Records'][3]['Value'];

        //debug
        $timelimit = microtime(true) - $time_start;
        echo '格納完了：' . $timelimit . ' seconds\r\n';
        //


        $status = Controller_V1_Auth::first_dataset(
            $keyword, $user_id, $username, $profile_img,
            $os, $model, $register_id, $identity_id);

        //debug
        $timelimit = microtime(true) - $time_start;
        echo '完了：' . $timelimit . ' seconds\r\n';

        echo "$status";
    }


    //Guestサインイン
    public function action_guest()
    {
        //debug
        $time_start = microtime(true);

        $keyword     = "Guest";
        $profile_img = 'none';
        $user_id     = Model_User::get_id();

        $username    = Input::get('username');
        $os          = Input::get('os');
        $model       = Input::get('model');
        $register_id = Input::get('register_id');


        $identity_id = Model_Cognito::post_data(
            $user_id, $username, $os, $model, $register_id);


/*
        //Cognito_Sync外部処理
        exec("nohup php '" . getcwd() . "/cognito/first_sync.php' " . "'" . "$identity_id" . "' '" . "$username" . "' '" . "$os" . "' '" . "$model" . "' '" . "$register_id" . "' > /dev/null &");
*/

        //debug
        $timelimit = microtime(true) - $time_start;
        echo '格納完了：' . $timelimit . ' seconds\r\n';


        $status = Controller_V1_Auth::first_dataset(
            $keyword, $user_id, $username, $profile_img,
            $os, $model, $register_id, $identity_id);

        //debug
        $timelimit = microtime(true) - $time_start;
        echo '完了：' . $timelimit . ' seconds\r\n';

        echo "$status";
    }


    public function action_login()
    {
        $identity_id = Input::get('identity_id');




    }


    public function action_logout()
    {
        //Model_put_active
    }


    //初回データ格納関数 (RDS, SNS)
    private static function first_dataset(
        $keyword, $user_id, $username, $profile_img,
        $os, $model, $register_id, $identity_id)
    {

        $badge_num = 0;

        try
        {
            //User情報を登録
            $profile_img = Model_User::post_data(
                $username, $profile_img, $identity_id);


            //AWS SNSに端末を登録
            $os = explode('_', $os);

            if ($os[0] == 'android') {
                $endpoint_arn = Model_Sns::post_android(
                    $user_id, $identity_id, $register_id);
            }
            elseif ($os[0] == 'iOS') {
                $endpoint_arn = Model_Sns::post_iOS(
                    $user_id, $identity_id, $register_id);
            }
            else{
                //Webかな？ 何もしない。
            }

            $os = implode('_', $os);


            //Device情報を登録
            $device = Model_Device::post_data(
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

        return $status;
    }


    //DBデータ入力成功
    private static function success($keyword,
        $user_id, $username, $profile_img, $identity_id, $badge_num)
    {
        $result = array(
            'code'        => 200,
            'username'    => "$username",
            'profile_img' => "$profile_img",
            'identity_id' => "$identity_id",
            'badge_num'   => "$badge_num",
            'message'     => "$keyword" . 'でログインしました。'
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
        $keyword, $username, $profile_img, $identity_id, $badge_num)
    {
        $result = array(
            'code'        => 401,
            'username'    => "$username",
            'profile_img' => "$profile_img",
            'identity_id' => "$identity_id",
            'badge_num'   => "$badge_num",
            'message'     => "$keyword" . 'でログインできませんでした。'
        );

        $status = json_encode(
            $result,
            JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
        );

        return $status;
    }

}