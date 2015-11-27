<?php
/**
 * Login Trait
 * @package    Gocci-Web
 * @version    3.0 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

trait Auth {

    use GocciAPI;

    /**
     * @param String $provider
     * @param String $token
     */
    public static function sns_login($provider, $token)
    {
        try {
            if (empty($provider) && empty($token)
                || empty($provider) 
                || empty($token)) {
                GocciAPI::error_json("UnAuthorized");
            }
            $identity_id = Model_V2_DB_Cognito::get_identity_id($provider, $token);
            $user_data = Model_V2_DB_User::getAuth($identity_id);

            $user_id     = $user_data['user_id'];
            $username    = $user_data['username'];
            $profile_img = $user_data['profile_img'];
            $badge_num   = $user_data['badge_num'];

            $jwt = GocciAPI::encode($user_id, $username);
            Model_V2_DB_Login::postLogin($user_id);
            $data = [
                "user_id"     => $user_id,
                "username"    => $username,
                "profile_img" => $profile_img,
                "identity_id" => $identity_id,
                "badge_num"   => $badge_num,
            ];
            $base_data = GocciAPI::base_template($api_code = "SUCCESS",
                $api_message = "Successful API request",
                $login_flag = 1,
                $data,
                $jwt);
            GocciAPI::output_json($base_data);

        } catch (\Database_Exception $e) {
            var_dump($e);
            exit;
        }

    }

    public static function logout()
    {
        GocciAPI::get_jwt_token($uri=Uri::string(), $login_flag=1);
        $user_id = session::get('user_id');
        if (empty($user_id)) {
            $base_data = GocciAPI::base_template($api_code = "SUCCESS",
                $api_message = "UnAuthorized",
                $login_flag = 1,
                $data, $jwt);
        }

        try {
            Session::delete('user_id');
            Session::delete('username');
            Session::delete('exp');

            $data = [
                "message" => "ログアウトしました"
            ];
            $base_data = self::base_template($api_code = "SUCCESS",
            $api_message = "Successful API request",
            $login_flag = 1,
            $data,
            $jwt);

            GocciAPI::output_json($base_data);
        } catch (Exception $e) {
            var_dump($e); // debug
            exit;
        }
    }

    public static function pass_login()
    {
        GocciAPI::post_check();
        if (empty($username) && empty($password)
            || empty($username) 
            || empty($password)) {
            GocciAPI::error_signin($message = "usernameもしくはpasswordが入力されていません");
        }
        try {
            if (!empty($username) && !empty($password)) {
                $user_data = Model_V2_DB_User::check_pass($username, $password);
                $user_id     = $user_data[0]['user_id'];
                $profile_img = $user_data[0]['profile_img'];
                $identity_id = $user_data[0]['identity_id'];
                $badge_num   = $user_data[0]['badge_num'];

                Model_V2_DB_Login::postLogin($user_id);
                $jwt = GocciAPI::encode($user_id, $username);
                $data = [
                    "user_id"     => $user_id,
                    "username"    => $username,
                    "profile_img" => $profile_img,
                    "identity_id" => $identity_id,
                    "badge_num"   => $badge_num
                ];
                $base_data = GocciAPI::base_template($api_code = "SUCCESS",
                    $api_message = "Successful API request",
                    $login_flag = 1, 
                    $data,
                    $jwt);
                GocciAPI::output_json($base_data);
            }
        } catch (Exception $e) {
                var_dump($e); // debug
                exit;
        }
    }
}
