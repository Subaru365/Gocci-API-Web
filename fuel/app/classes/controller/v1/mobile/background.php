<?php
error_reporting(-1);
/**
 *
 *
 */

use Aws\Sns\SnsClient;

class Controller_V1_Mobile_Background extends Controller
{
    //CognitoSync Dataset
    public function action_dataset()
    {
        $identity_id = Input::get('identity_id');
        $username    = Input::get('username');
        $os          = Input::get('os');
        $model       = Input::get('model');
        $register_id = Input::get('register_id');

        Model_Cognito::dataset(
            $identity_id, $username, $os, $model, $register_id);
    }


    //SNS Push
    public function action_publish()
    {
        $keyword   = Input::get('keyword');
        $a_user_id = Input::get('a_user_id');
        $p_user_id = Input::get('p_user_id');

        $login_flag = Model_User::check_login($p_user_id);

        if ($login_flag == '1') {
        //ログイン中
            Model_Sns::post_message($keyword, $a_user_id, $p_user_id);
        }
    }


    //Post有効化
    public function action_post_publish()
    {
        $user_id = Input::get('user_id');
        $movie   = Input::get('movie');

        Model_Post::post_publish($movie);
        Model_Sns::post_complete($user_id);

        echo '確認ありがとう！';
    }
}