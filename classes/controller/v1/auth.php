<?php
/**
 * Auth Class
 * @package    Gocci-Web
 * @version    3.0 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Controller_V1_Auth extends Controller_V1_Base
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
            $username  = $obj->{'username'};
            $exp       = $obj->{'exp'};
            session::set('user_id', $user_id);
            session::set('username', $username);
            session::set('exp', $exp);
        } else {
            self::unauth();
            error_log('UnAuthorized Accsess..');
            exit;
        }
    }

    /**
     * SNS Login (Facebook)
     */
    public function action_login()
    {
        $keyword   = 'ログイン';
        $provider  = Input::get('provider'); // postに変えること！
        $token     = Input::get('token');    // postに変えること！

        try
        {
            if (empty($provider) && empty($token) || empty($provider) or empty($token) ) {
                error_log('paramがありません');
                self::error_json("UnAuthorized");
            }
            /* =============================== */
            $identity_id = Model_Cognito::get_identity_id($provider, $token);
            /* =============================== */

            $user_data   = Model_User::web_get_auth($identity_id);
            $user_id     = $user_data['user_id'];
            $username    = $user_data['username'];
            $profile_img = $user_data['profile_img'];
            $badge_num   = $user_data['badge_num'];
            $jwt = self::encode($user_id, $username);
            Model_Login::post_login($user_id);
            $user_hash_id = Hash_Id::create_user_hash($user_id);
            $data = [
                "user_id"     => $user_id,
                "user_hash_id"=> $user_hash_id,
                "username"    => $username,
                "profile_img" => $profile_img,
                "identity_id" => $identity_id,
                "badge_num"   => $badge_num,
            ];
            $base_data = self::base_template($api_code = "SUCCESS",
                $api_message = "Successful API request",
                $login_flag  =  1,
                $data, $jwt
            );
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
            error_log('Exception: ');
            error_log($e);
            exit;
        }
    }

   /**
    * SNS(Twitter)ログイン
    *
    */
    public function action_twitter_sign_in()
    {
        error_log('twitter_sign_inが叩かれました');
        // 1)フロントがget/access_tokenを叩き、access_tokenを取得
        // 2)取得したtokenをtwtterの認証URIにつけて叩く。
        // 3)サーバ側で、既に登録されているかどうかチェックしあればsuccess_jsonを吐き出す。以上

        // self::error_json("UnAuthorized"); // 強制
        // user_id取得
        // user_modelからtokenを取得
        $provider = "api.twitter.com";
        $token = input::get('token'); // test
        // $token = input::post('token');

        // jwt
        if (empty($provider) && empty($token) || empty($provider) or empty($token) ) {
            error_log('paramがありません');
            self::error_json("UnAuthorized");
        }
        error_log('identity_idを取得します');
        error_log('provider:');
        error_log($provider);
        error_log('token');
        error_log($token);

        $identity_id = Model_Cognito::get_identity_id($provider, $token);
        error_log('取得したidentity_id');
        error_log($identity_id);

        error_log('user_dataを取得します');
        $user_data   = Model_User::web_get_auth($identity_id);
        error_log('user_dataを取得しました');

        $user_id     = $user_data['user_id'];
        $username    = $user_data['username'];
        $profile_img = $user_data['profile_img'];
        $badge_num   = $user_data['badge_num'];

        error_log('jwtを取得します');
        $jwt = self::encode($user_id, $username);
        error_log('Loginします');
        Model_Login::post_login($user_id);
        $user_hash_id = Hash_Id::create_user_hash($user_id);
        $data = [
            "user_id"     => $user_id,
            "user_hash_id"=> $user_hash_id,
            "username"    => $username,
            "profile_img" => $profile_img,
            "identity_id" => $identity_id,
            "badge_num"   => $badge_num,
        ];
        error_log('dataの中身');
        error_log(print_r($data, true));
        $base_data = self::base_template($api_code = "SUCCESS",
            $api_message = "Successful API request",
            $login_flag  =  1,
            $data, $jwt
        );
        self::output_json($base_data);
        error_log('jsonを出力');

        // json返す。

        // 1. access_tokenを取得
        
        // 2. フロントにaccess_tokenを渡し、認証画面から
        // image/tokenを取得し、サーバ側で保持

        // 3. 認証画面完了後、サーバ側は
        // callback_urlのページ(gocci.me/reg/sigin_in)
        // に飛ばし、フロント側は、twitter/loginにtokneをPOST
        // header('Location: ' . );
        
        // echo self::CALLBACK_URL_TEST;
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
                $login_flag  =  1,
                $data, $jwt  = ""
            );
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
                $login_flag  =  1,
                $data, $jwt = ""
            );
            self::output_json($base_data);

       } catch (Exception $e) {
            error_log($e);
            exit;
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
                $user_hash_id = Hash_Id::create_user_hash($user_id);
                // JWT認証
                $jwt = self::encode($user_id, $username);

                $data = [
                    "user_id"     => $user_id,
                    "user_hash_id"=> $user_hash_id,
                    "username"    => $username,
                    "profile_img" => $profile_img,
                    "identity_id" => $identity_id,
                    "badge_num"   => $badge_num
                ];
                $base_data = self::base_template($api_code = "SUCCESS",
                    $api_message = "Successful API request",
                    $login_flag  =  1,$data, $jwt
                );
                self::output_json($base_data);
            }
        } catch (Exception $e) {
            // JWT Exception
            // Not access
            error_log('例外が発生しました. in auth/pass_login');
            error_log($e);
            exit;
        }
    }
    /**
     * CURLでtwitter apiを叩きます
     */
    /*
    public static function curl_req_twitter($token)
    {
        error_log('curl_req_twitterが呼ばれました');
        error_log('----↓↓↓↓↓↓↓----');

        $url = "http://test.web.api.gocci.me/v1/auth/twitter_sign_in/";
        $ch = curl_init();

        // postするデータの配列
        $account_data = array('token' => $token);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // POST送信
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($account_data));

        curl_exec($ch);
        error_log('----↑↑↑↑↑↑↑------');
        error_log('post完了');
    }
    */

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
        self::output_json($data);
    }

    public function action_test_login()
    {

    }
}
