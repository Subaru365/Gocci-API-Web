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

trait Login {

    use GocciAPI;

    /**
     * @param
     * @param
     * @param
     * @param
     */
    public static function sns_login($provider, $token)
    {
        try {
            if (empty($provider) && empty($token) 
              || empty($provider) || empty($token)) {
                GocciAPI::error_json('UnAuthorized');
            }
                $identity_id = Model_V2_AWS_Cognito::get_identity_id($provider, $token);
                $user_data = Model_V2_DB_User::getAuth($identity_id);
                $user_id     = $user_data['user_id'];
                $username    = $user_data['user_data'];
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
                $base_data = GocciAPI::base_template($api_code = "SUCCESS", $api_message = "Successful API request", $login_flag = 1, $data, $jwt);
                GocciAPI::output_json($base_data);
        } catch (\Database_Exception $e) {
            var_dump($e);
            exit;
        }
    }

}
