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


    public function action_sns()
    {
        $identity_id = Input::get('id');
    }



    public function action_signin()
    {
        $username = Input::get('username');
        $password = Input::get('pass');

        if (Auth::login($username, $password)) {
            echo "Success!";
            //$user_id = Model_Post::get_data($username);

        }else{
            echo "ログインできません。";
        }
    }


    public function action_signout()
    {
        //Model_put_active
        Auth::logout();
    }

}