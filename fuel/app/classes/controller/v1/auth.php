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
        $password = Input::get('pass');
        $email    = Input::get('email');
        $group    = Input::get('group');


        if (empty($group)) {
            $group = 1;
        }

        //$user_id = Auth::create_user($username, $password, $email, $group);

        try
        {
            // call Auth to create this user
            $created = \Auth::create_user($username, $password, $email, $group);

                // if a user was created succesfully
                if ($created)
                {
                    // inform the user
                    //\Messages::success(__('login.new-account-created'));
                    $status_ary = array('code'=>'200','message' => 'OK');
                    echo json_encode($status_ary);

                    // and go back to the previous page, or show the
                    // application dashboard if we don't have any
                    // \Response::redirect_back('dashboard');
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


        /*if(Auth::create_user($username, $password, $email, $group) == false){

            echo "登録失敗";
        }
*/

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
        Auth::logout();
    }

}