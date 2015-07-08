<?php

/**
 * CognitoSync api
 *
 */
class Controller_V1_Background_User extends Controller
{
    //CognitoSync Dataset
    public function action_update()
    {
        $identity_id = Input::get('identity_id');
        $user_id     = Input::get('user_id');
        $username    = Input::get('username');
        $os          = Input::get('os');
        $model       = Input::get('model');
        $register_id = Input::get('register_id');

        $register_id_old  = Model_Device::get_register_id($user_id);
        $endpoint_arn_old = Model_Device::get_arn($user_id);


        if ($register_id == $register_id_old) {

            $endpoint_arn = $endpoint_arn_old;

        }else{

            $tmp = Model_Sns::delete_endpoint($endpoint_arn_old);
            $endpoint_arn = Model_Sns::post_endpoint(
                $user_id, $identity_id, $register_id, $os);
        }


        $tmp = Model_User::update_name($user_id, $username);
        $tmp = Model_Device::update_data(
            $user_id, $os, $model, $register_id, $endpoint_arn);

        $tmp = Model_Login::post_login($user_id);

        exit;
    }
}