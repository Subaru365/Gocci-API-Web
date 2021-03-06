<?php
/**
 * Auth Class API
 * @package    Gocci-Web
 * @version    2.0 - 2.5 <2015/11/18>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Controller_V2_Auth extends Controller_V2_Base
{
    /**
     * jwtがあるかどうかをcheckするメソッド
     * @var String $uri
     * @var String $login_flag
     */
    public static function get_jwt_token($uri="", $login_flag)
    {
        $jwt = @$_SERVER["HTTP_AUTHORIZATION"] ? @$_SERVER["HTTP_AUTHORIZATION"] : "";

        if(isset($jwt)) {
            $data      = self::decode($jwt);
            $user_data = session::get('data');
            $obj       = json_decode($user_data);
            if (empty($obj)) {
                 self::unauth();
            }
            $user_id   = $obj->{'user_id'};
            session::set('user_id', $user_id);
            $username  = $obj->{'username'};
            session::set('username', $username);
            $exp       = $obj->{'exp'};
            session::set('exp', $exp);
        } else {
            self::unauth();
            error_log('UnAuthorized Accsess..');
            exit;
        }
    }

    /**
     * SNS Login (Facebook/Twitter)
     */
    public function action_login()
    {
        $keyword   = 'ログイン';
        $provider  = Input::get('provider');
        $token     = Input::get('token');

        try
        {
            if (empty($provider) && empty($token) || empty($provider) or empty($token) ) {
                self::error_json("UnAuthorized");
            }
            $identity_id = Model_Cognito::get_identity_id($provider, $token);
            $user_data   = Model_User::web_get_auth($identity_id);
            $user_id     = $user_data['user_id'];
            $username    = $user_data['username'];
            $profile_img = $user_data['profile_img'];
            $badge_num   = $user_data['badge_num'];
            $jwt = self::encode($user_id, $username);
            Model_Login::post_login($user_id);
            $data = [
                "user_id"     => $user_id,
                "username"    => $username,
                "profile_img" => $profile_img,
                "identity_id" => $identity_id,
                "badge_num"   => $badge_num,
            ];
            $base_data = self::base_template($api_code = "SUCCESS",
                                $api_message = "Successful API request",
                                $login_flag =  1,
                                $data, $jwt);

            self::output_json($base_data);

        } catch(\Database_Exception $e) {
            self::failed(
                $keyword,
                $user_id,
                $username,
                $profile_img,
                $identity_id,
                $badge_num
            );
            error_log($e);
        }
    }

    /**
    * Logout メソッド
    *
    * @return string
    */
    public static function action_logout()
    {
        self::get_jwt_token($uri=Uri::string(), $login_flag=1);
        $user_id = session::get('user_id');
        if (empty($user_id)) {
           $base_data = self::base_template($api_code = "SUCCESS",
                                $api_message = "UnAuthorized",
                                $login_flag =  1,
                                $data, $jwt);
       }
        try {
            // ログアウトのためsessionデータ削除
            Session::delete('user_id');
            Session::delete('username');
            Session::delete('exp');

            $data = [
                "message" => "ログアウトしました"
            ];
            $base_data = self::base_template($api_code = "SUCCESS",
                                $api_message = "Successful API request",
                                $login_flag =  1,
                                $data, $jwt);

            self::output_json($base_data);
            error_log('ログアウトしました');

       } catch (Exception $e) {

       }
    }

    /**
     * username password Login
     */
    public function action_pass_login()
    {
        $username  = Input::post('username');
        $password  = Input::post('password');

        self::post_check();
        if (empty($username) && empty($password) || empty($username) or empty($password) ) {
            self::error_signin($message = "usernameもしくはpasswordが入力されていません");
        }
        try {
            if (!empty($username) && !empty($password)) {
                $user_data   = Model_User::check_pass($username, $password);
                $user_id     = $user_data[0]['user_id'];
                $profile_img = $user_data[0]['profile_img'];
                $identity_id = $user_data[0]['identity_id'];
                $badge_num   = $user_data[0]['badge_num'];
                Model_Login::post_login($user_id);
                // JWT認証
                $jwt = self::encode($user_id, $username);
                $data = [
                    "user_id"     => $user_id,
                    "username"    => $username,
                    "profile_img" => $profile_img,
                    "identity_id" => $identity_id,
                    "badge_num"   => $badge_num
                ];
                $base_data = self::base_template($api_code = "SUCCESS",
                                    $api_message = "Successful API request",
                                    $login_flag =  1,$data, $jwt);
                self::output_json($base_data);
            }
        } catch (Exception $e) {
            // JWT Exception
            // Not access
        }
    }
    /**
     * DBデータ入力エラー
     */
    private static function failed(
        $keyword,
        $user_id,
        $username,
        $profile_img,
        $identity_id,
        $badge_num
    )
    {
        $data = [
            'api_version' => 3.0,
            'api_uri'     => Uri::string(),
            'api_code'    => "Failed",
            'api_message' => $keyword . 'できませんでした。',
            'username'    => $username,
            'profile_img' => $profile_img,
            'identity_id' => $identity_id,
            'badge_num'   => $badge_num,
        ];
        Controller_V2_Base::output_json($data);
    }
}
