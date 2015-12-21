<?php
/**
 * Base Class
 * @package    Gocci-Web
 * @version    3.0 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods:POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');
header('X-Content-Type-Options: nosniff');

abstract class Controller_V1_Base extends Controller
{
    /**
     * @var Array $base_data
     */
    public static $base_data = [];

    /**
     * @var String $status
     */
    public static $status;

    /**
     * @var Int $user_id
     */
    private $user_id;

    /**
     * @var Object $jwt_obj;
     */
    private $jwt_obj;

    const API_VERSION                     = "3.0";
    const ENV_PRO                         = "PRODUCTION";
    const ENV_DEV                         = "DEVELOPMENT";
    const SUCCESSFUL_API_REQUEST_MESSAGE  = "SUCCESSFUL_API_REQUEST_MESSAGE";
    const ERROR_API_REQUEST_MESSAGE       = "ERROR_API_REQUEST_MESSAGE";
    const API_MESSAGE_UNAUTHORIZED        = "UnAuthorized";
    const API_CODE_ERROR_ALREADY_REGISTER = "ERROR_ALREADY_REGISTER";

    public function before()
    {
        $this->session_check();
        $this->http_x_request_check();
        self::accessLog();
    }

    public function start_basic()
    {
        switch (true) {
            case !isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']):
            case $_SERVER['PHP_AUTH_USER'] !== 'gocci_web':
            case $_SERVER['PHP_AUTH_PW']   !== 'gocci_web':
                header('WWW-Authenticate: Basic realm="Enter username and password."');
                die('ログインが必要です');
        }
    }

    public function http_x_request_check()
    {
        // SCRIPT要素で埋め込まれないための対策
        if (! isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
            $_SERVER['HTTP_X_REQUEST_WITH'] !== 'XMLHttpRequest') {
            // Not Ajax Request
            // json output
        }
    }

    public function session_check()
    {
        if (session::get('user_id')) {
           self::set_user_id();
           $user_id = self::get_user_id();
           error_log($user_id);
        } else {
            $jwt = self::get_jwt();
            $this->obj = self::getJwtObject($jwt);
        }
    }

    public static function get_user_id()
    {
        return $this->user_id;
    }

    public static function get_jwt_obj()
    {
        return $this->jwt_obj;
    }

    public static function set_user_id()
    {
        $this->user_id = session::get('user_id');
    }

    public function get_input_data() {

        $input_data = array_merge(Input::get(), Input::post());

        try {
            if (!empty($input_data)) {

            }
        } catch (ErrorException $e) {
            die('Error');
        }
    }

    /**
     * API BASE_DATA TEMPLATE
     *
     * @param  string $api_code
     * @param  string $api_message
     * @param  string $api_data
     * @param  string $jwt
     * @return Array  $base_data
     */
    public static function base_template($api_code, $api_message, $login_flag, $api_data, $jwt)
    {
        $base_data  = [
            "api_version" => self::API_VERSION,
            "api_uri"     => Uri::string(),
            "api_code"    => $api_code,
            "api_message" => $api_message,
            "login_flag"  => $login_flag,
            "api_data"    => $api_data,
            "jwt"         => $jwt
        ];
        return $base_data;
    }

    /**
     * ACCESS LOG
     */
    public static function accessLog()
    {
        if (self::ENV_DEV === 'DEVELOPMENT') {
            $accessTime = date('Y-m-d H:i:s', strtotime("+ 9 hour"));
            $ip = $_SERVER["REMOTE_ADDR"];
            error_log('Access Time: ');
            error_log($accessTime);
            error_log('Access IP:' . $ip);
        }
    }

    /**
     * JSON_ENCODE TEMPLATE
     * @param  Array  $status
     * @return Object $status
     */
    public static function json_encode_template($status)
    {
        $status = json_encode(
            $status,
            JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT
        );
        return $status;
    }

    /**
     * DEBUG_JSON_ENCODE_TEMPLATE
     * @param  Array  $status
     * @return Object $status
     */
    public static function debug_json_encode_template($status)
    {
        $status = json_encode(
            $status,
            JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
        );
        return $status;
    }

    /**
     * POST CHECK
     */
    public static function post_check()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        } else {
            self::unauth();
        }
    }

    /**
     * @param String $uri
     * @param String $login_flag
     */
    public static function get_jwt_token($uri="", $login_flag)
    {
        $jwt = self::get_jwt();
        if(isset($jwt)) {
            self::setJwt($jwt);
        } else {
            self::unauth();
            error_log('UnAuthorized Accsess..');
            exit;
        }
     }

    /**
     * SEETER JWT
     * @param String $jwt
     */
    public static function setJwt($jwt)
    {
        $obj = self::runDeocd($jwt);
        if (empty($obj)) {
            self::unauth();
        }
        $user_id   = $obj->{'user_id'};
        session::set('user_id', $user_id);
        $username  = $obj->{'username'};
        session::set('username', $username);
        $exp       = $obj->{'exp'};
        session::set('exp', $exp);
    }

    /**
     * @return Object $obj
     */
    public static function runDeocd($jwt)
    {
        $data      = self::decode($jwt);
        $user_data = session::get('data');
        $obj       = json_decode($user_data);

        return $obj;
    }

    /**
     * DECODE
     * @param  String $jwt
     * @return Object $decoded
     */
    public static function decode($jwt)
    {
        $key = 'i_am_a_secret_key';
        try {
            $decoded = JWT::decode($jwt, $key, array('HS256'));
            $decoded = session::set('data', $decoded);
            error_log('decodedの中身を確認 by base decode');
            error_log($decoded);
        } catch (Exception $e){
            $decoded = "";
        }
        return $decoded;
    }

    /**
     * ENCODE (JWT CREATE)
     * @param  Int    $user_id
     * @param  String $username
     * @return string $jwt
     */
    public static function encode($user_id, $username)
    {
        $key   = 'i_am_a_secret_key';
        $exp = time() + 86400; // 24h
        $json  = [
            'user_id' => $user_id,
            'exp'     => $exp,
            'username'=> $username
        ];
        $json = json_encode($json);

        if ($json === NULL) {
            die("[Error]\n");
        }
        $jwt = JWT::encode($json, $key);

        return $jwt;
    }

    /**
     * CHECK IF THE JWT IS VALID
     * @param  String $exp
     * @return String $jwt
     */
    public static function check_jwtExp($exp)
    {
        $jwt = "";
        if (isset($exp) && (time() >= $exp)) {
            error_log('=jwtの有効期限切れ=');
            self::expired_token("Expired Token");
        } else {
            error_log('有効期限内です. jwtを更新します');
            $jwt = self::_refresh_token();
        }
        return $jwt;
    }

    /**
     * REFRESH TOKEN METHOD
     * @return String $jwt
     */
    public static function _refresh_token()
    {
        $user_id  = session::get('user_id');
        $username = session::get('usernaem');

        Session::delete('exp');
        $jwt = self::encode($user_id, $username);
        error_log('-*-*-*-*JWT was update!-*-*-*');
        return $jwt;
    }

    /**
     * NOT JWT UNAUTH
     * @param String $uri
     * @param String $login_flag
     */
    public static function unauth($uri="",$login_flag=0)
    {
        error_log('アクセス拒否 base unauth method.');
        error_log('jwtが存在しません');
        $status = [
            "api_version" => self::API_VERSION,
            "api_uri"     => Uri::string(),
            "api_code"    => 1,
            "api_message" => "UnAuthorized",
            "login_flag"  => $login_flag,
            "api_data"    => $obj = new stdClass()
        ];
        $status = self::json_encode_template($status);
        echo $status;
        exit;
    }

    /**
     * OUTPUT JSON
     * @param  Object $data
     */
    public static function output_json($api_data)
    {
        $json = self::json_encode_template($api_data);
        echo $json;
    }

    /**
     * DEBUG OUTPUT JSON
     * @param Object $data
     */
    public static function debug_output_json($api_data)
    {
        $json = self::debug_json_encode_template($api_data);
        echo $json;
    }

    /**
     * GET ALL HEADERS
     * @return Array $headers
     */
    public static function getallheaders()
    {
        $headers = '';
        foreach ($_SERVER as $name => $value)
        {
            if (substr($name, 0, 5) == 'HTTP_')
            {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    /**
     * NOT FOUND USER
     */
    public static function notfounduser()
    {
        $status = [
          'message' => 'Userが存在しません'
        ];
        $status = self::json_encode_template($status);
        echo $status;
        exit;
    }

    /**
     * NOT FOUND PAGE
     */
    public static function NotFoundPage()
    {
        $data = [
            "message" => "This page is not available. There is a problem with the link or there is a possibility that the page has been deleted. Return to the Gocci."
        ];
        $base_data = self::base_template($api_code = "NOTFOUND", 
          $api_message = "SUCCESS ful API request", 
          $login_flag =  1, 
          $data, $jwt = ""
        );
        $status = self::output_json($base_data);
    }

    /**
     * NOT ID
     */
    public static function notid()
    {
        $api_data = [
          'message' => 'usernameを入力してください'
        ];
        $base_data = self::base_template($api_code = 1,
                          $api_message = "Failed", 
                          $login_flag = 0, 
                          $api_data, 
                          $jwt = ""
        );
        $status    = self::json_encode_template($base_data);
        echo $status;
        exit;
    }

     /**
      * SUCCESS JSON OUTPUT
      * @return Object string
      */
    public static function success_json($keyword, $user_id, $username, $profile_img, $identity_id, $badge_num, $token,$message)
    {
          $api_data = [
               "user_id"     => $user_id,
               "username"    => $username,
               "profile_img" => $profile_img,
               "identity_id" => $identity_id,
               "badge_num"   => $badge_num,
               "jwt"         => $token,
               "login_flag"  => 1
          ];
          $status = [
              "api_version" => self::API_VERSION,
              "api_uri"     => Uri::string(),
              "api_code"    => "SUCCESS",
              "api_message" => "$message Successful API request",
              "api_data"    => $api_data
          ];
          $status = self::json_encode_template($status);
          echo $status;
          exit;
    }

    /**
     * ERROR SIGNIN
     */
    public static function error_signin($message)
    {
        $status = [
            "api_version" => self::API_VERSION,
            "api_uri"     => Uri::string(),
            "api_code"    => 'ERR_SGNIN',
            "api_message" => $message,
            "login_flag"  => 0,
            "api_data"    => $obj = new stdClass()
        ];
        $status = self::json_encode_template($status);
        echo $status;
        exit;
    }

    /**
     * ERROR JSON REGISTER
     */
    public static function error_register($message)
    {
        $status = [
            "api_version" => self::API_VERSION,
            "api_uri"     => Uri::string(),
            "api_code"    => 'ERROR_ALREADY_REGISTER',
            "api_message" => $message,
            "login_flag"  => 0,
            "api_data"    => $obj = new stdClass()
        ];
        $status = self::json_encode_template($status);
        echo $status;
        exit;
    }

    /**
     * ERROR JSON OUTPUT
     * @return Object $status
     */
    public static function error_json($message)
    {
        $api_message = "UnAuthorized";

        $status = [
            "api_version" => self::API_VERSION,
            "api_uri"     => Uri::string(),
            "api_code"    => $api_message, #"VALIDATION ERROR",
            "api_message" => $message,
            "login_flag"  => 0,
            "api_data"    => $obj = new stdClass()
        ];
        $status = self::json_encode_template($status);
        echo $status;
        exit;
    }

    /**
     * TIMELIEN TEMPLATE
     * @return Array $data
     */
    public static function timeline_template()
    {
        $user_id  = session::get('user_id');
        $username = session::get('username');
        $exp      = session::get('exp');
        $jwt      = self::check_jwtExp($exp);
        $sort_key = 'all';
        $limit    = 18;
        $option   = [
            'call'        => Input::get('call', 0),
            'order_id'    => Input::get('order_id', 0),
            'category_id' => Input::get('category_id', 0),
            'value_id'    => Input::get('value_id', 0),
            'lon'         => Input::get('lon', 0),
            'lat'         => Input::get('lat', 0)
        ];
        $data = Model_Post::get_data($user_id, $sort_key, 0, $option, $limit);

        for ($i = 0; $i<$limit; $i++) {
            $post_id = $data[$i]['post_id'];
            $Comment_data = Model_Comment::get_data($post_id);
            $hash_id = Hash_Id::video_hash($post_id);
            $data[$i]['hash_id']  = $hash_id;
            $data[$i] = [
                "post"     => $data[$i],
                "comments" => $Comment_data
            ];
        }
        return $data;
    }

    /**
     * USER TEMPLATE
     * @param  String $target_username
     * @param  Int    $limit
     * @param  String $sort_key
     * @return Array  $data
     */
    public static function user_template($target_username, $limit, $sort_key) {
        if (ctype_digit($target_username)) { $this->notid(); }

        $target_user_id = Model_User::get_id($target_username);
        $user_id        = session::get('user_id');
        $user_data      = Model_User::get_data($user_id, $target_user_id);
        $post_data      = Model_Post::get_data(
            $target_user_id, $sort_key, $target_user_id, $limit
        );
        for ($i = 0; $i<count($post_data); $i++) {
            $post_id = $post_data[$i]['post_id'];
            $Comment_data = Model_Comment::get_data($post_id);
            $post_data[$i] = [
                "post"     => $post_data[$i],
                "comments" => $Comment_data
            ];
        }
        $data = [
            "header" => $user_data,
            "posts"  => $post_data
        ];

        return $data;
    }

    /**
     * REST TEMPLATE
     * @param  Int   $user_id
     * @param  Int   $rest_id
     * @return Array $data
     */
    public static function rest_template($user_id, $rest_id, $sort_key) {
        $rest_data= Model_Restaurant::get_data($user_id, $rest_id);
        $rest_data['want_flag'] = Model_Want::get_flag($user_id, $rest_id);
        $rest_data['cheer_num'] = Model_Post::get_rest_cheer_num($rest_id);
        $post_data = Model_Post::get_data($user_id, $sort_key, $rest_id);

        $loop_num = count($post_data);

        for ($i = 0; $i<$loop_num; $i++) {
            $post_id = $post_data[$i]['post_id'];
            $Comment_data = Model_Comment::get_data($post_id);

            $post_data[$i] = [
                "post"     => $post_data[$i],
                "comments" => $Comment_data
            ];
        }
        if (empty($user_id)) {
            $login_flag = 0;
        } else {
            $login_flag = 1;
        }
        $data = [
            "header" => $rest_data,
            "posts"  => $post_data
        ];
        return $data;
    }

    /**
     * VIDEO TEMPLATE
     * @param  Int    $user_id
     * @param  String $hash_id
     * @return Array  $data
     */
    public static function video_template($user_id, $hash_id)
    {
        $sort_key= "all";
        $post_id = Model_Post::get_post_id($hash_id);

        $data = Model_Post::get_one_data($user_id, $limit=1, $post_id);
        for ($i = 0; $i<$limit; $i++) {
            $Comment_data = Model_Comment::get_data($post_id);
            $data[$i]['hash_id']  = $hash_id;
                $data[$i] = [
                    "post"     => $data[$i],
                    "comments" => $Comment_data
                ];
        }
        return $data;
    }

    /**
     * GET JWT METHOD
     * @return String $jwt
     */
    public static function get_jwt()
    {
        $jwt = @$_SERVER["HTTP_AUTHORIZATION"] ? @$_SERVER["HTTP_AUTHORIZATION"] : "";
        error_log('返すjwt: ');
        error_log($jwt);
        return $jwt;
    }

    /**
     * GET JWT OBJECT
     * @return Object $obj
     */
    public static function getJwtObject($jwt)
    {
        $data      = self::decode($jwt);
        $user_data = session::get('data');
        $obj       = json_decode($user_data);
        return $obj;
    }

    /**
     * EXPIRED TOKEN
     * @param  String $message
     */
    public static function expired_token($message)
    {
          $status = [
            "api_version" => self::API_VERSION,
            "api_uri"     => Uri::string(),
            "api_code"    => "Failed",
            "api_message" => $message,
            "login_flag"  => 2,
            "api_data"    => $obj = new stdClass()
          ];
          $status = self::json_encode_template($status);
          echo $status;
          exit;
    }
}