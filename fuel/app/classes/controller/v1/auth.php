<?php

//header('Content-Type: application/json; charset=UTF-8');
error_reporting(-1);
/**
 * Auth api
 *
 */


class Controller_V1_Auth extends Controller
{

/*
    public function action_before()
    {
        if(!Auth::check())
        {
            echo "ログインしましょうね〜(*^^)v";
        }
    }
*/

    /*
    public function action_signup()
    {

        $username = Input::get('username');
        $password = Input::get('password');
        $email    = Input::get('email');
        $group    = Input::get('group');
        $token_id = Input::get('token_id');


        if (empty($group)) {
            $group = 1;
        }


        try
        {
            // call Auth to create this user
            $created = \Auth::create_user($username, $password, $email, $group);

                // if a user was created succesfully
                if ($created)
                {
                    // inform the user
                    //\Messages::success(__('login.new-account-created'));

                    $status_ary = array(
                        'username'  => "$username",
                        'picture'   => 'OK',
                        'background'=> '',
                        'badge_num' => '0',
                        'message'   => '作成完了！Gocciへようこそ！'
                        );

                    echo json_encode($status_ary);
                }
                else
                {
                    // oops, creating a new user failed?
                    \Messages::error(__('login.account-creation-failed'));
                }
        }

            // catch exceptions from the create_user() call
            catch (\SimpleUserUpdateException $e)
            {
                // duplicate email address
                if ($e->getCode() == 2)
                {
                    //\Messages::error(__('login.email-already-exists'));
                    echo "登録されたメールアドレスです。";
                    $status_ary = array('code'=>'403','message' => 'email exists');
                    echo json_encode($status_ary);
                }

                // duplicate username
                elseif ($e->getCode() == 3)
                {
                    //\Messages::error(__('login.username-already-exists'));
                    $status_ary = array('code'=>'403','message' => 'username exists');
                echo json_encode($status_ary);
            }

            // this can't happen, but you'll never know...
            else
            {
                //\Messages::error($e->getMessage());
                echo "データ形式おかしいんじゃないの!？";
            }
        }

    }

    */

    //SNSサインイン
    public function action_sns()
    {
        $keyword     = 'SNS';
        $user_id     = Model_User::get_id();
        $identity_id = Input::get('identity_id');
        $profile_img = Input::get('profile_img');

        $user_data   = Model_Cognito::get_data($identity_id);
        $model       = $user_data[0];
        $os          = $user_data[1];
        $register_id = $user_data[2];
        $username    = $user_data[3];


        $status = Controller_V1_Auth::first_dataset(
            $keyword, $user_id, $username, $profile_img,
            $os, $model, $register_id, $identity_id);


        echo "$status";
    }


    //Guestサインイン
    public function action_guest()
    {
        $keyword     = "Guest";
        $user_id     = Model_User::get_id();

        $username    = Input::get('username');
        $profile_img = 'none';
        $os          = Input::get('os');
        $model       = Input::get('model');
        $register_id = Input::get('register_id');

        $identity_id = Model_Cognito::post_data(
            $user_id, $username, $os, $model, $register_id);


        $status = Controller_V1_Auth::first_dataset(
            $keyword, $user_id, $username, $profile_img,
            $os, $model, $register_id, $identity_id);


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
            //データベースにユーザー情報を登録
            $profile_img = Model_User::post_data(
                $user_id, $username, $profile_img,
                $os, $model, $register_id, $identity_id);


            //AWS SNSに端末を登録
            $os = explode('_', $os);

            if ($os[0] == 'android') {
                $sns = Model_Sns::post_android(
                    $user_id, $identity_id, $register_id);
            }
            elseif ($os[0] == 'iOS') {
                $sns = Model_Sns::post_iOS(
                    $user_id, $identity_id, $register_id);
            }
            else{
                //Webかな？ 何もしない。
            }

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