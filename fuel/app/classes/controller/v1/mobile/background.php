<?php
error_reporting(-1);
/**
 *
 *
 */

use Aws\Sns\SnsClient;

class Controller_V1_Mobile_Background extends Controller
{
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
        $message = '投稿が完了しました！';

        $user_id = Input::get('user_id');
        $movie   = Input::get('movie');

        Model_Post::post_publish($movie);
        Model_Sns::post_publish($user_id, $message);

        echo '確認ありがとう！';
    }


    //Post有効化
    public function action_post_reject()
    {
        $message = 'ごめんなさい！料理の動画を撮って下さい。';

        $user_id = Input::get('user_id');
        $movie   = Input::get('movie');

        Model_Post::post_reject($movie);
        Model_Sns::post_publish($user_id, $message);

        echo '拒否しました。確認ありがとう！';
    }


    //Register_id更新
    public function action_update_register_id()
    {
        $user_id     = Input::get('user_id');
        $register_id = Input::get('register_id');
        $os          = Input::get('os');

        $old_endpoint_arn = Model_Device::get_arn($user_id);
        Model_Sns::delete_endpoint($old_endpoint_arn);

        $new_endpoint_arn = Model_Sns::post_endpoint($user_id, $register_id, $os);
        Model_Device::update_register_id($user_id, $register_id, $new_endpoint_arn);

        echo '端末情報を更新しました。';
    }

}