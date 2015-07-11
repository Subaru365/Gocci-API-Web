<?php

/**
*SNS
*/
class Controller_V1_Background_Sns extends Controller
{
    public function action_publish()
    {
        $keyword   = Input::get('keyword');
        $a_user_id = Input::get('a_user_id');
        $p_user_id = Input::get('p_user_id');

        $login_flag = Model_User::check_login($p_user_id);

            if ($login_flag == '1') {

                //ログイン中
                $result = Model_Sns::post_message(
                    $keyword, $a_user_id, $p_user_id);


            }else{
                //ログアウト中
            }
        exit;
    }
}

