<?php
/**
 * Auth api
 *
 * // $time_start = microtime(true);
 * // debug
 * // $timelimit = microtime(true) - $time_start;
 * // echo '格納完了：' . $timelimit . ' seconds\r\n';
 *
 */

class Controller_V2_Mobile_Auth extends Controller
{
    private static function get_input()
    {
        $user_data = array_merge (Input::get(), Input::post());
        return $user_data;
    }


    public static function action_signup()
    {
        //Input $user_data is [username, os, model, register_id]
        $user_data = Input::get();
        Model_V2_Validation::check_signup($user_data);

        $user_data = Model_v2_Router::create_user($user_data);

        Controller_V2_Mobile_Base::output_success($user_data);
    }


    public static function action_login()
    {
        //Input $user_data is [identity_id]
        $user_data = Input::get();
        Model_V2_Validation::check_login($user_data);

        $user_data = Model_V2_Router::login($user_data['identity_id']);

        Controller_V2_Mobile_Base::output_success($user_data);
    }


    public static function action_sns_login()
    {
        //Input $user_data is [identity_id, os, model, register_id]
        $user_data = array_merge (Input::get(), Input::post());
        Model_V2_Validation::check_sns_login($user_data);

        $user_data = Model_V2_Router::login($user_data['identity_id']);

        Controller_V2_Mobile_Base::output_success($user_data);
    }


    public static function action_pass_login()
    {
        //Input $user_data is [username, pass, os, model, register_id]
        $user_data = array_merge (Input::get(), Input::post());
        Model_V2_Validation::check_pass_login($user_data);

        $user_data = Model_V2_Router::pass_login($user_data['identity_id']);

        Controller_V2_Mobile_Base::output_success($user_data);
    }


    public static function action_device_refresh()
    {
        //Input $user_data is [$register_id]
        $user_data = self::get_input();

        // $old_endpoint_arn = Model_Device::get_arn($user_id);
        // Model_Sns::delete_endpoint($old_endpoint_arn);

        // $new_endpoint_arn = Model_Sns::post_endpoint($user_id, $register_id, $os);
        // Model_Device::update_data($user_id, $os, $model, $register_id, $new_endpoint_arn);

    }
}